<?php

namespace WP_Privacy\WP_API_Privacy;

require_once( 'github-updater.php' );

class ApiPrivacy extends GithubUpdater {
    protected $slug;

    private static $instance = null;

    protected function __construct() {
        // initialize the updater
        parent::__construct( 
            'wp-privacy/wp-api-privacy.php',
            'wp-privacy',
            'wp-api-privacy',
            'main'
        );
    }

    public function init() {
        add_filter( 'http_request_args', array( $this, 'modifyUserAgent' ), 0 );

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