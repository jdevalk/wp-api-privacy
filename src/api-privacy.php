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
        
        if ( current_user_can( 'update_plugins' ) ) {
            $this->_checkForUpdate();
        }
    }

    public function modifyUserAgent( $params ) {
        if ( isset( $params[ 'user-agent' ] ) ) {
            $params[ 'user-agent' ] = ApiPrivacy::USER_AGENT;
        }

        return $params;
    }

    private function _getHeaderInfo() {
        $result = wp_remote_get( 'https://raw.githubusercontent.com/wp-privacy/wp-api-privacy/refs/heads/main/wp-api-privacy.php' );
        if( $result ) {
            if ( !is_wp_error( $result ) ) {
                $body = wp_remote_retrieve_body( $result );
                if ( $body ) {
                    if ( preg_match_all( '#[\s]+(.*): (.*)#', $body, $matches ) ) {
                        print_r( $matches ); die;
                    }
                }
            }
        }
    }

    private function _checkForUpdate() {
        $githubTagData = get_transient( ApiPrivacy::GITHUB_TAG_CACHE_KEY );
        if ( !$githubTagData ) {
            $result = wp_remote_get( 'https://api.github.com/repos/wp-privacy/wp-api-privacy/releases' );
            if ( !is_wp_error( $result ) ) {
                $githubTagData = json_decode( wp_remote_retrieve_body( $result ) );
               
                if ( $githubTagData ) {
                    set_transient( ApiPrivacy::GITHUB_TAG_CACHE_KEY, $githubTagData, ApiPrivacy::CACHE_TIME );
                }
            } 
        }

        if ( $githubTagData ) {
            // We have a list of releases
        }

        $this->_getHeaderInfo();
    }

    static function instance() {
        if ( self::$instance == null ) {
            self::$instance = new ApiPrivacy();
        }
        
        return self::$instance;
    }
}