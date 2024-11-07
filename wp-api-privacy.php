<?php
/*
    Plugin Name: WP API Privacy
    Plugin URI: https://github.com/wpprivacy/wp-api-privacy
    Description: This plugin strips potentially identifying information from outbound requests to the WordPress.org API
    Author: Duane Storey
    Author URI: https://duanestorey.com
*/

namespace WP_Privacy\WP_API_Privacy;

require_once( dirname( __FILE__ ) . '/src/api-privacy.php' );

function initialize_privacy( $params ) {
    ApiPrivacy::instance()->init();
}

add_filter( 'plugins_loaded', __NAMESPACE__ . '\initialize_privacy', 0 );
