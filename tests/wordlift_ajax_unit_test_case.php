<?php

/**
 * @deprecated use {@link Wordlift_Ajax_Unit_Test_Case}.
 * Class WL_Ajax_UnitTestCase
 * Extend WP_Ajax_UnitTestCase
 * @see        https://codesymphony.co/wp-ajax-plugin-unit-testing/
 * This class it's used to override the protected method _handleAjax
 * in order to let the method accept an optional parameter that can be passed to the callback
 * Useful to inject php//input fake data into the the callbacks environment
 */
abstract class WL_Ajax_UnitTestCase extends WP_Ajax_UnitTestCase {

	protected function _handleAjax( $action, $http_raw_data = NULL ) {

		// Start output buffering
		ini_set( 'implicit_flush', FALSE );
		ob_start();

		// Build the request
		$_POST['action'] = $action;
		$_GET['action']  = $action;
		$_REQUEST        = array_merge( $_POST, $_GET );

		// Call the hooks
		do_action( 'admin_init' );
		do_action( 'wp_ajax_' . $_REQUEST['action'], $http_raw_data );

		// Save the output
		$buffer = ob_get_clean();
		if ( ! empty( $buffer ) ) {
			$this->_last_response = $buffer;
		}

	}

}
