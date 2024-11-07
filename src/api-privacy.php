<?php

namespace WP_Privacy\WP_API_Privacy;

class ApiPrivacy {
    private const USER_AGENT = 'WordPress/Private';

    private static $instance = null;

    protected function __construct() {}

    public function init() {
        add_filter( 'http_request_args', array( $this, 'modifyUserAgent' ), 0 );
        add_filter( 'core_version_check_query_args', array( $this, 'apiQueryParams' ), 0 );
    }

    public function apiQueryParams( $request ) {
        print_r( $request );
        die;
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