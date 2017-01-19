<?php
/**
 * This file defines a converter from an entity {@link WP_Post} to a JSON-LD array.
 *
 * @since   3.8.0
 * @package Wordlift
 */

/**
 * Define the {@link Wordlift_Entity_To_Jsonld_Converter} class.
 *
 * @since 3.8.0
 */
class Wordlift_Post_To_Jsonld_Converter {

	/**
	 * The JSON-LD context.
	 *
	 * @since 3.8.0
	 */
	const CONTEXT = 'http://schema.org';

	/**
	 * A {@link Wordlift_Entity_Type_Service} instance.
	 *
	 * @since  3.8.0
	 * @access protected
	 * @var \Wordlift_Entity_Type_Service $entity_type_service A {@link Wordlift_Entity_Type_Service} instance.
	 */
	protected $entity_type_service;

	/**
	 * A {@link Wordlift_Entity_Service} instance.
	 *
	 * @since  3.8.0
	 * @access protected
	 * @var \Wordlift_Entity_Service $entity_type_service A {@link Wordlift_Entity_Service} instance.
	 */
	protected $entity_service;

	/**
	 * A {@link Wordlift_Property_Getter} instance.
	 *
	 * @since  3.8.0
	 * @access private
	 * @var \Wordlift_Property_Getter $property_getter A {@link Wordlift_Property_Getter} instance.
	 */
	private $property_getter;

	/**
	 * A {@link Wordlift_Log_Service} instance.
	 *
	 * @since  3.10.0
	 * @access private
	 * @var Wordlift_Log_Service $log A {@link Wordlift_Log_Service} instance.
	 */
	private $log;

	/**
	 * Wordlift_Entity_To_Jsonld_Converter constructor.
	 *
	 * @since 3.8.0
	 *
	 * @param \Wordlift_Entity_Type_Service $entity_type_service
	 * @param \Wordlift_Entity_Service      $entity_service
	 * @param \Wordlift_Property_Getter     $property_getter
	 */
	public function __construct( $entity_type_service, $entity_service, $property_getter ) {

		$this->entity_type_service = $entity_type_service;
		$this->entity_service      = $entity_service;
		$this->property_getter     = $property_getter;

		// Set a reference to the logger.
		$this->log = Wordlift_Log_Service::get_logger( 'Wordlift_Entity_To_Jsonld_Converter' );
	}

	/**
	 * Convert the provided {@link WP_Post} to a JSON-LD array. Any entity reference
	 * found while processing the post is set in the $references array.
	 *
	 * @since 3.8.0
	 *
	 * @param WP_Post $post       The {@link WP_Post} to convert.
	 *
	 * @param array   $references An array of entity references.
	 *
	 * @return array A JSON-LD array.
	 */
	public function convert( $post, &$references = array() ) {

		// Get the entity @type.
		$type = 'post' === $post->post_type ? 'BlogPosting' : 'Article';

		// Get the entity @id.
		$id = $this->entity_service->get_uri( $post->ID );

		// Get the entity name.
		$name = $post->post_title;

		// Get the configured type custom fields.
		$fields = $type['custom_fields'];

		// get author
		$author = get_the_author_meta('display_name',$post->post_author);
		
		// Prepare the response.
		$jsonld = array(
			'@context'    => self::CONTEXT,
			'@id'         => $id,
			'@type'       => $type,
			'headline'        => $name,
			'description' => $this->get_excerpt( $post ),
			'author'      => array ('@type' => 'Person', 'name' => $author),
			'datePublished' => get_post_time( 'Y-m-d\TH:i', true, $post, false ),
			'dateModified' => get_the_modified_time('Y-m-d\TH:i',$post),
		);
		
		// insert publisher
		$configuration = Wordlift_Configuration_Service::get_instance();
		$publisher_id  = $configuration->get_publisher_id();

		// do not try to add publisher if plugin setup is not completed
		if ( $publisher_id ) {

			$type = Wordlift_Entity_Type_Service::get_instance()->get( $publisher_id );
			$logo = get_the_post_thumbnail_url( $publisher_id, 'full' );

			$publisher_post = get_post( $publisher_id );
			$publisher_name = $publisher_post->post_title;

			$jsonld['publisher'] = array(
				'@type'	=> str_replace('http://schema.org/','',Wordlift_Entity_Type_Service::get_instance()->get($publisher_id)),
				'name' 	=> $publisher_name, 
				'logo' 	=> array('url' => $logo),
				);
		}
		
		// Set the image URLs if there are images.
		$images = wl_get_image_urls( $post->ID );
		if ( 0 < count( $images ) ) {
			$jsonld['image'] = $images;
		}

		// Set a reference to use in closures.
		$converter = $this;

		// Try each field on the entity.
		foreach ( $fields as $key => $value ) {

			// Get the predicate.
			$name = $this->relative_to_context( $value['predicate'] );

			// Get the value, the property service will get the right extractor
			// for that property.
			$value = $this->property_getter->get( $post->ID, $key );

			if ( 0 === count( $value ) ) {
				continue;
			}

			// Map the value to the property name.
			// If we got an array with just one value, we return that one value.
			// If we got a Wordlift_Property_Entity_Reference we get the URL.
			$jsonld[ $name ] = $this->make_one( array_map( function ( $item ) use ( $converter, &$references ) {

				if ( $item instanceof Wordlift_Property_Entity_Reference ) {

					$url          = $item->getURL();
					$references[] = $url;

					return array( "@id" => $url );
				}

				return $converter->relative_to_context( $item );
			}, $value ) );

		}

		return $this->post_process( $jsonld );
	}

	/**
	 * If the provided value starts with the schema.org context, we remove the schema.org
	 * part since it is set with the '@context'.
	 *
	 * @since 3.8.0
	 *
	 * @param string $value The property value.
	 *
	 * @return string The property value without the context.
	 */
	public function relative_to_context( $value ) {

		return 0 === strpos( $value, self::CONTEXT . '/' ) ? substr( $value, strlen( self::CONTEXT ) + 1 ) : $value;
	}

	/**
	 * If the provided array of values contains only one value, then one single
	 * value is returned, otherwise the original array is returned.
	 *
	 * @since  3.8.0
	 * @access private
	 *
	 * @param array $value An array of values.
	 *
	 * @return mixed|array A single value or the original array.
	 */
	private function make_one( $value ) {

		return 1 === count( $value ) ? $value[0] : $value;
	}

	/**
	 * Post process the generated JSON to reorganize values which are stored as 1st
	 * level in WP but are really 2nd level.
	 *
	 * @since 3.8.0
	 *
	 * @param array $jsonld An array of JSON-LD properties and values.
	 *
	 * @return array The array remapped.
	 */
	private function post_process( $jsonld ) {

		foreach ( $jsonld as $key => $value ) {
			if ( 'streetAddress' === $key || 'postalCode' === $key || 'addressLocality' === $key || 'addressRegion' === $key || 'addressCountry' === $key || 'postOfficeBoxNumber' === $key ) {
				$jsonld['address']['@type'] = 'PostalAddress';
				$jsonld['address'][ $key ]  = $value;
				unset( $jsonld[ $key ] );
			}

			if ( 'latitude' === $key || 'longitude' === $key ) {
				$jsonld['geo']['@type'] = 'GeoCoordinates';
				$jsonld['geo'][ $key ]  = $value;
				unset( $jsonld[ $key ] );
			}
		}

		return $jsonld;
	}

	/**
	 * Get the excerpt for the provided {@link WP_Post}.
	 *
	 * @since 3.8.0
	 *
	 * @param WP_Post $post The {@link WP_Post}.
	 *
	 * @return string The excerpt.
	 */
	private function get_excerpt( $post ) {

		// Temporary pop the previous post.
		$original = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;

		// Setup our own post.
		setup_postdata( $GLOBALS['post'] = &$post );

		$excerpt = get_the_excerpt( $post );

		// Restore the previous post.
		if ( null !== $original ) {
			setup_postdata( $GLOBALS['post'] = $original );
		}

		// Finally return the excerpt.
		return html_entity_decode( $excerpt );
	}

}
