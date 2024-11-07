<?php
/* 
    Copyright (C) 2024 by Duane Storey - All Rights Reserved
    You may use, distribute and modify this code under the
    terms of the GPLv3 license.
 */

namespace WP_Privacy\WP_API_Privacy;

require_once( 'github-updater.php' );

class ApiPrivacy extends GithubUpdater {
    private const USER_AGENT = 'WordPress/Private';

    private static $instance = null;

    protected function __construct() {
        // set up our user-agent filter
        add_filter( 'http_request_args', array( $this, 'modifyUserAgent' ), 0 );

        // initialize the updater
        parent::__construct( 
            'wp-api-privacy/wp-api-privacy.php',
            'wp-privacy',
            'wp-api-privacy',
            'main'
        );
    }

    public function init() {
        // for the future
    }

    public function modifyUserAgent( $params ) {
        if ( isset( $params[ 'user-agent' ] ) ) {
            $params[ 'user-agent' ] = ApiPrivacy::USER_AGENT;
        }

        return $params;
    }

    static function instance() {
        if ( self::$instance == null ) {
            self::$instance = new ApiPrivacy();
        }
        
        return self::$instance;
    }
}