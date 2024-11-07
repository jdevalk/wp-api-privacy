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
        add_filter( 'http_request_args', array( $this, 'modifyUserAgent' ), 0, 2 );

        // initialize the updater
        parent::__construct( 
            'wp-api-privacy/wp-api-privacy.php',
            'wp-privacy',
            'wp-api-privacy',
            'main'
        );
    }

    public function init() {
    }

    public function modifyUserAgent( $params, $url ) {
        // Remove site URL from user agent as this is a privacy issue
        if ( isset( $params[ 'user-agent' ] ) ) {
            $params[ 'user-agent' ] = ApiPrivacy::USER_AGENT;
        }
   
        // Remove plugins hosted off-site, nobody needs to know these - for now this just uses the Plugin URI parameter
        if ( strpos( $url, 'wordpress.org/plugins/update-check/' ) !== false ) {
            $decodedJson = json_decode( $params[ 'body' ][ 'plugins'] );
            if ( $decodedJson ) {
                // check for plugin info
                if ( $decodedJson->plugins ) {
                    $toRemove = [];
                    foreach( $decodedJson->plugins as $name => $plugin ) {
                        if ( isset( $plugin->UpdateURI ) && !empty( $plugin->UpdateURI ) ) {
                            $toRemove[] = $name;
                        }
                    }

                    foreach( $toRemove as $remove ) {
                        unset( $decodedJson->plugins->$remove );                        
                    }

                    $decodedJson->active = array_diff( $decodedJson->active, $toRemove );
                }
                
                $params[ 'body' ][ 'plugins' ] = json_encode( $decodedJson );
            }
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