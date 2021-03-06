<?php
/**
 * Wordlift_Configuration_Service class.
 *
 * The {@link Wordlift_Configuration_Service} class provides helper functions to get configuration parameter values.
 *
 * @link       https://wordlift.io
 *
 * @package    Wordlift
 * @since      3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get WordLift's configuration settings stored in WordPress database.
 *
 * @since 3.6.0
 */
class Wordlift_Configuration_Service {

	/**
	 * The entity base path option name.
	 *
	 * @since 3.6.0
	 */
	const ENTITY_BASE_PATH_KEY = 'wl_entity_base_path';

	/**
	 * The skip wizard (admin installation wizard) option name.
	 *
	 * @since 3.9.0
	 */
	const SKIP_WIZARD = 'wl_skip_wizard';

	/**
	 * WordLift's key option name.
	 *
	 * @since 3.9.0
	 */
	const KEY = 'key';

	/**
	 * WordLift's configured language option name.
	 *
	 * @since 3.9.0
	 */
	const LANGUAGE = 'site_language';

	/**
	 * The publisher entity post ID option name.
	 *
	 * @since 3.9.0
	 */
	const PUBLISHER_ID = 'publisher_id';

	/**
	 * The Wordlift_Configuration_Service's singleton instance.
	 *
	 * @since  3.6.0
	 *
	 * @access private
	 * @var \Wordlift_Configuration_Service $instance Wordlift_Configuration_Service's singleton instance.
	 */
	private static $instance;

	/**
	 * Create a Wordlift_Configuration_Service's instance.
	 *
	 * @since 3.6.0
	 */
	public function __construct() {

		self::$instance = $this;

	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 3.6.0
	 *
	 * @return \Wordlift_Configuration_Service
	 */
	public static function get_instance() {

		return self::$instance;
	}

	/**
	 * Get a configuration given the option name and a key. The option value is
	 * expected to be an array.
	 *
	 * @since 3.6.0
	 *
	 * @param string $option  The option name.
	 * @param string $key     A key in the option value array.
	 * @param string $default The default value in case the key is not found (by default an empty string).
	 *
	 * @return mixed The configuration value or the default value if not found.
	 */
	private function get( $option, $key, $default = '' ) {

		$options = get_option( $option, array() );

		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
	}

	/**
	 * Set a configuration parameter.
	 *
	 * @since 3.9.0
	 *
	 * @param string $option Name of option to retrieve. Expected to not be SQL-escaped.
	 * @param string $key    The value key.
	 * @param mixed  $value  The value.
	 */
	private function set( $option, $key, $value ) {

		$values         = get_option( $option );
		$values         = isset( $values ) ? $values : array();
		$values[ $key ] = $value;
		update_option( $option, $values );

	}

	/**
	 * Get the entity base path, by default 'entity'.
	 *
	 * @since 3.6.0
	 *
	 * @return string The entity base path.
	 */
	public function get_entity_base_path() {

		return $this->get( 'wl_general_settings', self::ENTITY_BASE_PATH_KEY, 'entity' );
	}

	/**
	 * Get the entity base path.
	 *
	 * @since 3.9.0
	 *
	 * @param string $value The entity base path.
	 */
	public function set_entity_base_path( $value ) {

		$this->set( 'wl_general_settings', self::ENTITY_BASE_PATH_KEY, $value );
	}

	/**
	 * Whether the installation skip wizard should be skipped.
	 *
	 * @since 3.9.0
	 *
	 * @return bool True if it should be skipped otherwise false.
	 */
	public function is_skip_wizard() {

		return $this->get( 'wl_general_settings', self::SKIP_WIZARD, FALSE );
	}

	/**
	 * Set the skip wizard parameter.
	 *
	 * @since 3.9.0
	 *
	 * @param bool $value True to skip the wizard. We expect a boolean value.
	 */
	public function set_skip_wizard( $value ) {

		$this->set( 'wl_general_settings', self::SKIP_WIZARD, $value === TRUE );

	}

	/**
	 * Get WordLift's key.
	 *
	 * @since 3.9.0
	 *
	 * @return WordLift's key or an empty string if not set.
	 */
	public function get_key() {

		return $this->get( 'wl_general_settings', self::KEY, '' );
	}

	/**
	 * Set WordLift's key.
	 *
	 * @since 3.9.0
	 *
	 * @param string $value WordLift's key.
	 */
	public function set_key( $value ) {

		$this->set( 'wl_general_settings', self::KEY, $value );
	}

	/**
	 * Get WordLift's configured language, by default 'en'.
	 *
	 * Note that WordLift's language is used when writing strings to the Linked Data dataset, not for the analysis.
	 *
	 * @since 3.9.0
	 *
	 * @return string WordLift's configured language code ('en' by default).
	 */
	public function get_language_code() {

		return $this->get( 'wl_general_settings', self::LANGUAGE, 'en' );
	}

	/**
	 * Set WordLift's language code, used when storing strings to the Linked Data dataset.
	 *
	 * @since 3.9.0
	 *
	 * @param string $value WordLift's language code.
	 */
	public function set_language_code( $value ) {

		$this->set( 'wl_general_settings', self::LANGUAGE, $value );

	}

	/**
	 * Get the publisher entity post id.
	 *
	 * The publisher entity post id points to an entity post which contains the data for the publisher used in schema.org
	 * Article markup.
	 *
	 * @since 3.9.0
	 *
	 * @return int|NULL The publisher entity post id or NULL if not set.
	 */
	public function get_publisher_id() {

		return $this->get( 'wl_general_settings', self::PUBLISHER_ID, NULL );
	}

	/**
	 * Set the publisher entity post id.
	 *
	 * @since 3.9.0
	 *
	 * @param int $value The publisher entity post id.
	 */
	public function set_publisher_id( $value ) {

		$this->set( 'wl_general_settings', self::PUBLISHER_ID, $value );

	}

}
