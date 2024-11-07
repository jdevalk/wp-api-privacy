<?php

namespace WP_Privacy\WP_API_Privacy;

class ApiPrivacy {
    private const CACHE_TIME = ( 60 * 15 ); // 15 minutes
    private const USER_AGENT = 'WordPress/Private';
    private const GITHUB_TAG_CACHE_KEY = 'wp_privacy_github_tag';

    private static $instance = null;

    protected function __construct() {}

    public function init() {
        add_filter( 'http_request_args', array( $this, 'modifyUserAgent' ), 0 );
        
        $this->_checkForUpdate();
    }

    public function modifyUserAgent( $params ) {
        if ( isset( $params[ 'user-agent' ] ) ) {
            $params[ 'user-agent' ] = ApiPrivacy::USER_AGENT;
        }

        return $params;
    }

    private function _checkForUpdate() {
        $githubTagData = get_transient( ApiPrivacy::GITHUB_TAG_CACHE_KEY );
        if ( !$githubTagData ) {
            $result = wp_remote_get( 'https://api.github.com/repos/wp-privacy/wp-api-privacy/releases' );
            if ( !is_wp_error( $result ) ) {
                $githubTagData = json_decode( wp_remote_retrieve_body( $result ) );
               
                set_transient( ApiPrivacy::GITHUB_TAG_CACHE_KEY, $githubTagData, ApiPrivacy::CACHE_TIME );
            } 
        }

        if ( $githubTagData ) {
            // We have a list of releases
        }
    }

    static function instance() {
        if ( self::$instance == null ) {
            self::$instance = new ApiPrivacy();
        }
        
        return self::$instance;
    }
}