<?php
/*
    Plugin Name: WP API Privacy
    Plugin URI: https://github.com/wpprivacy/wp-api-privacy
    Banner: https://github.com/wp-privacy/wp-api-privacy/blob/main/assets/banner.jpg?raw=true
    Description: Strips potentially identifying information from outbound requests to the WordPress.org API
    Author: Duane Storey
    Author URI: https://duanestorey.com
    Version: 0.0.4
    Requires PHP: 6.0
    Requires at least: 6.0
    Tested up to: 6.6
    Update URI: https://github.com/wpprivacy/wp-api-privacy
    Stable: 0.0.4
*/

namespace WP_Privacy\WP_API_Privacy;

define( 'PRIVACY_VERSION', '0.0.4' );

require_once( dirname( __FILE__ ) . '/src/api-privacy.php' );

function initialize_privacy( $params ) {
    ApiPrivacy::instance()->init();
}

add_filter( 'plugins_loaded', __NAMESPACE__ . '\initialize_privacy', 0 );
