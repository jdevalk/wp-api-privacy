<?php
/* 
    Copyright (C) 2024 by Duane Storey - All Rights Reserved
    You may use, distribute and modify this code under the
    terms of the GPLv3 license.
 */

namespace WP_Privacy\WP_API_Privacy;

require_once( PRIVACY_PATH_SRC . '/github-updater.php' );
require_once( PRIVACY_PATH_SRC . '/settings.php' );

class ApiPrivacy extends GithubUpdater {
    private const USER_AGENT = 'WordPress/Private';

    private static $instance = null;

    protected $settings = null;

    protected function __construct() {
        $this->settings = new Settings();

        // set up our user-agent filter
        add_filter( 'http_request_args', array( $this, 'modifyUserAgent' ), 0, 2 );
        add_filter( 'rest_prepare_user', array( $this, 'modifyRestUser' ), 10, 3 );
        add_action( 'http_api_curl', array( $this, 'modifyCurl' ), 10, 3 );

        // initialize the updater
        parent::__construct( 
            'wp-api-privacy/wp-api-privacy.php',
            'wp-privacy',
            'wp-api-privacy',
            'main'
        );
    }

    public function init() {
        $this->settings->init();
    }

    public function getSetting( $name ) {
        return $this->settings->getSetting( $name );
    }

    public function modifyCurl( $handle, $params, $url ) {
        if ( $handle ) {
            if ( $this->getSetting( 'disable_https' ) ) {
                $url = str_replace( 'https://', 'http://', $url );
            }

            if ( $this->getSetting( 'strip_core_data' ) && strpos( $url, 'api.wordpress.org/core/version-check' ) !== false ) {
                $urlData = parse_url( $url );
                if ( $urlData[ 'query' ] ) {
                    $queryData = explode( '&', $urlData[ 'query' ] );
                    $newQueryData = [];

                    foreach( $queryData as $value ) {
                        if ( ( strpos( $value, 'extensions' ) !== false ) || ( strpos( $value, 'platform_flags' ) !== false ) ) {
                            continue;
                        }

                        $params = explode( '=', $value );

                        switch( $params[ 0 ] ) {
                            case 'version':
                            case 'php':
                            case 'locale':
                                $newQueryData[] = $value;
                                break;
                            case 'mysql':
                            case 'blogs':
                            case 'users':
                            case 'multisite_enabled':
                            case 'initial_db_version':
                                break;
                            default:
                                $newQueryData[] = $value;
                                break;
                        }

                        $queryData = implode( '&', $newQueryData );
                    }

                    $url = $urlData[ 'scheme' ] . '://' . $urlData[ 'host' ] . $urlData[ 'path' ] . '?' . $queryData;
                }

                curl_setopt( $handle, CURLOPT_URL, $url ); 
            }
        }

        return $handle;
    }

    public function modifyRestUser( $response, $user, $request ) {
        if ( $this->getSetting( 'strip_user_logins' ) && isset( $response->data ) && isset( $response->data[ 'slug' ] ) ) {
            unset( $response->data[ 'slug' ] );
        }

        return $response;
    }

    public function modifyUserAgent( $params, $url ) {
        // Remove site URL from user agent as this is a privacy issue
        if ( $this->getSetting( 'strip_user_agent' ) && isset( $params[ 'user-agent' ] ) ) {
            $params[ 'user-agent' ] = ApiPrivacy::USER_AGENT;
        }
   
        // Remove plugins hosted off-site, nobody needs to know these - for now this just uses the 'Update URI' parameter
        if ( $this->getSetting( 'strip_plugins' ) && strpos( $url, 'wordpress.org/plugins/update-check/' ) !== false ) {
            $decodedJson = json_decode( $params[ 'body' ][ 'plugins'] );
            if ( $decodedJson ) {
                // check for plugin info
                if ( $decodedJson->plugins ) {
                    $toRemove = [];
                    $toKeep = [];
                    foreach( $decodedJson->plugins as $name => $plugin ) {
                        if ( isset( $plugin->UpdateURI ) && !empty( $plugin->UpdateURI ) ) {
                            // don't remove ones hosted on wordpress.org
                            if ( strpos( $plugin->UpdateURI, 'wordpress.org' ) === false ) {
                                $toRemove[] = $name;
                                continue;
                            }
                        }

                        $toKeep[] = $name;
                    }

                    foreach( $toRemove as $remove ) {
                        unset( $decodedJson->plugins->$remove );                        
                    }

                    $decodedJson->active = $toKeep;
                }
                $params[ 'body' ][ 'plugins' ] = json_encode( $decodedJson );
            }
        } else if ( $this->getSetting( 'strip_themes' ) && strpos( $url, 'wordpress.org/themes/update-check/' ) !== false ) { 
            $decodedJson = json_decode( $params[ 'body' ][ 'themes'] );
            if ( $decodedJson ) {
                // check for theme info
                if ( $decodedJson->themes ) {
                    $toRemove = [];
                    foreach( $decodedJson->themes as $name => $theme ) {
                        if ( isset( $theme->UpdateURI ) && !empty( $theme->UpdateURI ) ) {
                            // don't remove ones hosted on wordpress.org
                            if ( strpos( $theme->UpdateURI, 'wordpress.org' ) === false ) {
                                $toRemove[] = $name;
                            }
                        }
                    }

                    foreach( $toRemove as $remove ) {
                        unset( $decodedJson->themes->$remove );                        
                    }
                }
                $params[ 'body' ][ 'themes' ] = json_encode( $decodedJson );    
            }    
        } if ( $this->getSetting( 'strip_core_headers' ) && strpos( $url, 'api.wordpress.org/core/version-check' ) !== false ) {
            if ( isset( $params[ 'headers' ] ) ) {
                if ( isset( $params[ 'headers' ][ 'wp_install' ] ) ) {
                    unset( $params[ 'headers' ][ 'wp_install' ] );
                }

                if ( isset( $params[ 'headers' ][ 'wp_blog' ] ) ) {
                    unset( $params[ 'headers' ][ 'wp_blog' ] );
                }
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
