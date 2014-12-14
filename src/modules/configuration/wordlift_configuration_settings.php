<?php

/**
 * Set a configuration option.
 *
 * @param string $settings The configuration settings group.
 * @param string $key The setting name.
 * @param string $value The setting value.
 */
function wl_configuration_set( $settings, $key, $value ) {

	$options         = get_option( $settings );
	$options         = isset( $options ) ? $options : array();
	$options[ $key ] = $value;
	update_option( $settings, $options );
}


/**
 * Get the configured WordLift key.
 *
 * @since 3.0.0
 *
 * @return string The configured WordLift key or an empty string.
 */
function wl_configuration_get_key() {

	$options = get_option( 'wl_general_settings' );

	return $options['key'];
}


/**
 * Set the WordLift key.
 *
 * @since 3.0.0
 *
 * @param string $value The WordLift key.
 */
function wl_configuration_set_key( $value ) {

	wl_configuration_set( 'wl_general_settings', 'key', $value );

}

/**
 * Get the *Entity Display As* configuration setting.
 *
 * @since 3.0.0
 *
 * @return string It returns 'index' to display pages as indexes or 'page' to display them as pages.
 */
function wl_configuration_get_entity_display_as() {

	$options = get_option( 'wl_general_settings' );

	return ( empty( $options['entity_display_as'] ) ? 'index' : $options['entity_display_as'] );
}

/**
 * Set the *Entity Display As* setting.
 *
 * @since 3.0.0
 *
 * @param string $value Either *index* to display the entities as a list of links or *page* to display the entity page.
 */
function wl_configuration_set_entity_display_as( $value ) {

	wl_configuration_set( 'wl_general_settings', 'entity_display_as', $value );

}

/**
 * Get the *Enable Color Coding* configuration setting.
 *
 * @since 3.0.0
 *
 * @return bool Whether color coding should be enabled or not.
 */
function wl_configuration_get_enable_color_coding() {

	$options = get_option( 'wl_general_settings' );

	return ( ! empty( $options['enable_color_coding'] ) );

}

/**
 * Set the *Enable Color Coding* configuration setting.
 *
 * @since 3.0.0
 *
 * @param bool $value True or false.
 */
function wl_configuration_set_enable_color_coding( $value ) {

	wl_configuration_set( 'wl_general_settings', 'enable_color_coding', $value );
}

/**
 * Get the *Site Language* configuration setting.
 *
 * @since 3.0.0
 *
 * @return string It returns the two-letter code of the site language.
 */
function wl_configuration_get_site_language() {

	$options = get_option( 'wl_general_settings' );

	return ( empty( $options['site_language'] ) ? 'en' : $options['site_language'] );
}

/**
 * Set the *Site Language* configuration setting.
 *
 * @since 3.0.0
 *
 * @param string $value The two-letter language code.
 */
function wl_configuration_set_site_language( $value ) {

	wl_configuration_set( 'wl_general_settings', 'site_language', $value );
}

/**
 * Get the API URL.
 *
 * @since 3.0.0
 *
 * @return string Get the API URL.
 */
function wl_configuration_get_api_url() {

	$options = get_option( 'wl_advanced_settings' );

	return ( empty( $options['api_url'] ) ? '' : $options['api_url'] );

}

/**
 * Set the API URL.
 *
 * @since 3.0.0
 *
 * @param string $value The API URL.
 */
function wl_configuration_set_api_url( $value ) {

	wl_configuration_set( 'wl_advanced_settings', 'api_url', $value );
}

/**
 * Get the Redlink application key.
 *
 * @since 3.0.0
 *
 * @return string The Redlink application key.
 */
function wl_configuration_get_redlink_key() {

	$options = get_option( 'wl_advanced_settings' );

	return ( empty( $options['redlink_key'] ) ? '' : $options['redlink_key'] );

}

/**
 * Set the Redlink application key.
 *
 * @param 3.0.0
 *
 * @param string $value The Redlink application key.
 */
function wl_configuration_set_redlink_key( $value ) {

	wl_configuration_set( 'wl_advanced_settings', 'redlink_key', $value );
}

/**
 * Get the Redlink user id.
 *
 * @since 3.0.0
 *
 * @return string The Redlink user id.
 */
function wl_configuration_get_redlink_user_id() {

	$options = get_option( 'wl_advanced_settings' );

	return ( empty( $options['redlink_user_id'] ) ? '' : $options['redlink_user_id'] );

}

/**
 * Set the Redlink user id.
 *
 * @since 3.0.0
 *
 * @param string $value The Redlink user id.
 */
function wl_configuration_set_redlink_user_id( $value ) {

	wl_configuration_set( 'wl_advanced_settings', 'redlink_user_id', $value );
}

/**
 * Get the Redlink dataset name.
 *
 * @since 3.0.0
 *
 * @return string The Redlink dataset name.
 */
function wl_configuration_get_redlink_dataset_name() {

	$options = get_option( 'wl_advanced_settings' );

	return ( empty( $options['redlink_dataset_name'] ) ? '' : $options['redlink_dataset_name'] );
}


/**
 * Set the Redlink dataset name.
 *
 * @since 3.0.0
 *
 * @param string $value The Redlink dataset name.
 */
function wl_configuration_set_redlink_dataset_name( $value ) {

	wl_configuration_set( 'wl_advanced_settings', 'redlink_dataset_name', $value );
}


/**
 * Get the Redlink dataset URI.
 *
 * @since 3.0.0
 *
 * @return string The Redlink dataset URI.
 */
function wl_configuration_get_redlink_dataset_uri() {

	$options = get_option( 'wl_advanced_settings' );

	return ( empty( $options['redlink_dataset_uri'] ) ? '' : $options['redlink_dataset_uri'] );
}


/**
 * Set the Redlink dataset URI.
 *
 * @since 3.0.0
 *
 * @param string $value The Redlink dataset URI.
 */
function wl_configuration_set_redlink_dataset_uri( $value ) {

	wl_configuration_set( 'wl_advanced_settings', 'redlink_dataset_uri', $value );
}

/**
 * Get the Redlink application name.
 *
 * @since 3.0.0
 *
 * @return string The Redlink application name.
 */
function wl_configuration_get_redlink_application_name() {

	$options = get_option( 'wl_advanced_settings' );

	return ( empty( $options['redlink_application_name'] ) ? '' : $options['redlink_application_name'] );
}


/**
 * Set the Redlink application name (once called the Analysis name).
 *
 * @since 3.0.0
 *
 * @param string $value The Redlink application name.
 */
function wl_configuration_set_redlink_application_name( $value ) {

	wl_configuration_set( 'wl_advanced_settings', 'redlink_application_name', $value );
}