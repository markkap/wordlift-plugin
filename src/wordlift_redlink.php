<?php
/**
 * This file contains functions related to Redlink.
 */

/**
 * Get the Redlink SPARQL Update URL.
 */
function wordlift_redlink_sparql_update_url() {

	// get the configuration.
	$dataset_id = wl_configuration_get_redlink_dataset_name();
	$app_key    = wl_configuration_get_redlink_key();

	// construct the API URL.
	return wl_configuration_get_api_url() . "/data/" . $dataset_id . "/sparql/update?key=" . $app_key;
}

/**
 * Get the Redlink dataset reindex url.
 * @return string The Redlink dataset reindex url.
 */
function wordlift_redlink_reindex_url() {

	// get the configuration.
	$dataset_id = wl_configuration_get_redlink_dataset_name();
	$app_key    = wl_configuration_get_redlink_key();

	// construct the API URL.
	return wl_configuration_get_api_url() . "/data/" . $dataset_id . "/release?key=" . $app_key;
}

/**
 * Get the Redlink URL to delete a dataset data (doesn't delete the dataset itself).
 * @return string
 */
function rl_empty_dataset_url() {

	// get the configuration.
	$dataset_id = wl_configuration_get_redlink_dataset_name();
	$app_key    = wl_configuration_get_redlink_key();

	// construct the API URL.
	$url = sprintf( '%s/data/%s?key=%s', wl_configuration_get_api_url(), $dataset_id, $app_key );

	return $url;
}

function rl_sparql_select_url() {

	// get the configuration.
	$dataset_id = wl_configuration_get_redlink_dataset_name();
	$app_key    = wl_configuration_get_redlink_key();

	// construct the API URL.
	$url = sprintf( '%s/data/%s/sparql/select?key=%s', wl_configuration_get_api_url(), $dataset_id, $app_key );

	return $url;
}

/**
 * Empty the dataset bound to this WordPress install.
 * @return WP_Response|WP_Error A WP_Response in case of success, otherwise a WP_Error.
 */
function rl_empty_dataset() {

	// TODO: re-enable, but as of Dec 2014 the call is too slow.
	return;

	// Get the empty dataset URL.
	$url = rl_empty_dataset_url();

	// Prepare the request.
	$args = array_merge_recursive( unserialize( WL_REDLINK_API_HTTP_OPTIONS ), array(
		'method' => 'DELETE'
	) );

	// Send the request.
	return wp_remote_request( $url, $args );
}

/**
 * Count the number of triples in the dataset.
 * @return array|WP_Error|null An array if successful, otherwise WP_Error or NULL.
 */
function rl_count_triples() {

	// Set the SPARQL query.
	$sparql = 'SELECT (COUNT(DISTINCT ?s) AS ?subjects) (COUNT(DISTINCT ?p) AS ?predicates) (COUNT(DISTINCT ?o) AS ?objects) ' .
	          'WHERE { ?s ?p ?o }';

	// Send the request.
	$response = rl_sparql_select( $sparql, 'text/csv' );

	// Remove the key from the query.
	$scrambled_url = preg_replace( '/key=.*$/i', 'key=<hidden>', rl_sparql_select_url() );

	// Return the error in case of failure.
	if ( is_wp_error( $response ) || 200 !== (int) $response['response']['code'] ) {

		$body = ( is_wp_error( $response ) ? $response->get_error_message() : $response['body'] );

		wl_write_log( "rl_count_triples : error [ url :: $scrambled_url ][ response :: " );
		wl_write_log( "\n" . var_export( $response, true ) );
		wl_write_log( "][ body :: " );
		wl_write_log( "\n" . $body );
		wl_write_log( "]" );

		return $response;
	}

	// Get the body.
	$body = $response['body'];

	// Get the values.
	$matches = array();
	if ( 1 === preg_match( '/(\d+),(\d+),(\d+)/im', $body, $matches ) && 4 === count( $matches ) ) {

		// Return the counts.
		return array(
			'subjects'   => (int) $matches[1],
			'predicates' => (int) $matches[2],
			'objects'    => (int) $matches[3]
		);
	}

	// No digits found in the response, return null.
	wl_write_log( "rl_count_triples : unrecognized response [ body :: $body ]" );

	return null;
}

/**
 * Execute the provided query against the SPARQL SELECT Redlink end-point and return the response.
 *
 * @param string $query A SPARQL query.
 * @param string $accept The mime type for the response format (default = 'text/csv').
 *
 * @return WP_Response|WP_Error A WP_Response instance in successful otherwise a WP_Error.
 */
function rl_sparql_select( $query, $accept = 'text/csv' ) {

	// Get the SPARQL SELECT URL.
	$url = rl_sparql_select_url();

	// Prepare the SPARQL statement by prepending the default namespaces.
	$sparql = rl_sparql_prefixes() . "\n" . $query;

	// Prepare the request.
	$args = array_merge_recursive( unserialize( WL_REDLINK_API_HTTP_OPTIONS ), array(
		'headers' => array(
			'Accept' => $accept
		),
		'body'    => array(
			'query' => $sparql
		)
	) );

	// Send the request.
	return wp_remote_post( $url, $args );
}

/**
 * Execute a query on Redlink.
 *
 * @since 3.0.0
 *
 * @uses wl_queue_sparql_update_query to queue a query if query buffering is on.
 *
 * @param string $query The query to execute.
 * @param bool $queue Whether to queue the update.
 *
 * @return bool True if successful otherwise false.
 */
function rl_execute_sparql_update_query( $query, $queue = WL_ENABLE_SPARQL_UPDATE_QUERIES_BUFFERING ) {

	// Get the calling function for debug purposes.
	$callers          = debug_backtrace();
	$calling_function = $callers[1]['function'];
	wl_write_log( "[ calling function :: $calling_function ][ queue :: " . ( $queue ? 'true' : 'false' ) . ' ]' );

	// Queue the update query.
	if ( $queue ) {
		wl_queue_sparql_update_query( $query );

		return true;
	}

	// Get the update end-point.
	$url = wordlift_redlink_sparql_update_url();

	// Prepare the request.
	$args = array_merge_recursive( unserialize( WL_REDLINK_API_HTTP_OPTIONS ), array(
		'method'  => 'POST',
		'headers' => array(
			'Accept'       => 'application/json',
			'Content-type' => 'application/sparql-update; charset=utf-8'
		),
		'body'    => $query
	) );

	// Send the request.
	$response = wp_remote_post( $url, $args );

	// Remove the key from the query.
	if ( ! WP_DEBUG ) {
		$scrambled_url = preg_replace( '/key=.*$/i', 'key=<hidden>', $url );
	} else {
		$scrambled_url = $url;
	}

	// If an error has been raised, return the error.
	if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {

		$body = ( is_wp_error( $response ) ? $response->get_error_message() : $response['body'] );

		wl_write_log( "rl_execute_sparql_update_query : error [ url :: $scrambled_url ][ args :: " );
		wl_write_log( "\n" . var_export( $args, true ) );
		wl_write_log( "[ response :: " );
		wl_write_log( "\n" . var_export( $response, true ) );
		wl_write_log( "][ body :: " );
		wl_write_log( "\n" . $body );
		wl_write_log( "]" );

		return false;
	}

	wl_write_log( "rl_execute_sparql_query [ url :: $scrambled_url ][ response code :: " . $response['response']['code'] . " ][ query :: " );
	wl_write_log( "\n" . $query );
	wl_write_log( "]" );

	return true;
}
