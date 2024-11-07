<?php

namespace WP_Privacy\WP_API_Privacy;

class GitHubUpdater {
    private const CACHE_TIME = ( 60 * 1 ); // 15 minutes

    protected $pluginSlug = null;
    protected $githubUser = null;
    protected $githubProject = null;
    protected $githubBranch = null;
    protected $gibhubMainPhp = null;
    protected $githubTagApi = null;

    protected $tagCacheKey = null;
    protected $headerCacheKey = null;
    protected $cacheModifier = null;

    protected $updateInfo = null;

    public function __construct( $pluginSlug, $githubUser, $githubProject, $githubBranch = 'main' ) {
        $this->pluginSlug = $pluginSlug;
        $this->githubUser = $githubUser;
        $this->githubProject = $githubProject;
        $this->githubBranch = $githubBranch;

        if ( $this->hasValidInfo() && current_user_can( 'update_plugins' ) ) {
            $this->setupGithubUrls();
            $this->setupTransientKeys();
            $this->deleteTransients();
            $this->checkForUpdate();

            add_filter( 'plugins_api', [ $this, 'handlePluginInfo' ], 20, 3 );
            add_filter( 'site_transient_update_plugins', [ $this, 'handleUpdate' ] );
        }
    }

    public function handlePluginInfo( $response, $action, $params ) {
        if ( 'plugin_information' !== $action ) {
            return $response;
        }

        if ( empty( $params->slug ) || basename( $this->pluginSlug, '.php' ) !== $params->slug ) {
            return $response;
        }

        if ( $this->updateInfo ) {
            $response = new \stdClass();

            $response->name           = $this->updateInfo->name;
            $response->slug           = $this->updateInfo->description;
            $response->version        = $this->updateInfo->version;
            $response->compatible     = $this->updateInfo->testedUpTo;
            $response->requires       = $this->updateInfo->requires;
            $response->author         = $this->updateInfo->author;
            $response->author_profile = $this->updateInfo->authorUri;
            $response->homepage       = $this->updateInfo->pluginUri;
            $response->download_link  = $this->updateInfo->updateUrl;
            $response->requires_php   = $this->updateInfo->requiresPhp;
            $response->last_updated   = $this->updateInfo->updatedAt;

            $response->sections = [
                'description'  => $this->updateInfo->description,
                'changelog'    => $this->updateInfo->changeLog
            ];

            $response->banners = [
                'high' => 'https://images.pexels.com/photos/164425/pexels-photo-164425.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2'
            ];

        }


          

        return $response;        
    }

    public function handleUpdate( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        if ( $this->updateInfo ) {
            if ( 
                version_compare( PRIVACY_VERSION, $this->updateInfo->version, '<' ) && 
                version_compare( $this->updateInfo->requires, get_bloginfo( 'version' ), '<=' ) && 
                version_compare( $this->updateInfo->requiresPhp, PHP_VERSION, '<' ) 
            ) {
                $response              = new \stdClass;

                $response->slug        = basename( $this->pluginSlug, '.php' );
                $response->plugin      = $this->pluginSlug;
                $response->new_version = $this->updateInfo->version;
                $response->tested      = $this->updateInfo->testedUpTo;
                $response->package     = $this->updateInfo->updateUrl;

                $transient->response[ $response->plugin ] = $response;
            }

            return $transient;
        }
    }

    protected function hasValidInfo() {
        return ( $this->pluginSlug && $this->githubUser && $this->githubProject && $this->githubBranch );
    }

    protected function setupTransientKeys() {
        $this->cacheModifier = md5( $this->pluginSlug );

        $this->tagCacheKey = 'wp_priv_tag_' . $this->cacheModifier;
        $this->headerCacheKey = 'wp_priv_hdr_' . $this->cacheModifier;
    }

    private function setupGithubUrls() {
        $this->gibhubMainPhp = 'https://raw.githubusercontent.com/' . $this->githubUser . '/' . $this->githubProject .
             '/refs/heads/' . $this->githubBranch . '/' . basename( $this->pluginSlug );

        $this->githubTagApi = 'https://api.github.com/repos/' . $this->githubUser . '/' . $this->githubProject . '/releases';
    }
    
    private function generateChangeLog( $releaseInfo ) {
        $changeLog = '';
        foreach( $releaseInfo as $release ) {
            $changeLog .= '<strong>' . $release->tag_name .  '-' . $release->name . '</strong>';
            $changeLog .= '<p>' . $release->body . '</p>';
        }

        return $changeLog;
    }

    private function checkForUpdate() {
        $headerData = $this->getHeaderInfo();
        $releaseInfo = $this->getReleaseInfo();

        if ( $headerData && $releaseInfo ) {
            $latestVersion = $headerData[ 'stable' ];

            if ( $latestVersion ) {
                foreach( $releaseInfo as $release ) {
                    if ( $release->tag_name = $latestVersion ) {
                        // found
                        $this->updateInfo = new \stdClass;

                        $this->updateInfo->requires = $headerData[ 'requires at least' ];
                        $this->updateInfo->testedUpTo = $headerData[ 'tested up to' ];
                        $this->updateInfo->requiresPhp = $headerData[ 'requires php' ];
                        $this->updateInfo->name = $headerData[ 'plugin name' ];
                        $this->updateInfo->pluginUri = $headerData[ 'plugin uri' ];
                        $this->updateInfo->description = $headerData[ 'description' ];
                        $this->updateInfo->author = $headerData[ 'author' ];
                        $this->updateInfo->authorUri = $headerData[ 'author uri' ];
                        $this->updateInfo->updatedAt = date( 'Y-m-d', strtotime( $release->published_at ) );
                        $this->updateInfo->changeLog = $this->generateChangeLog( $releaseInfo );

                        $this->updateInfo->version = $latestVersion;
                        $this->updateInfo->updateUrl = $release->zipball_url;

                        break;
                    }
                }           
            }
        }
    }    

    private function deleteTransients() {
        if ( $this->hasValidInfo() ) {
            delete_transient( $this->tagCacheKey );
            delete_transient( $this->headerCacheKey );
        }
    }

    private function getReleaseInfo() {
        $githubTagData = get_transient( $this->tagCacheKey );
        if ( !$githubTagData ) {
            $result = wp_remote_get( $this->githubTagApi );
            if ( !is_wp_error( $result ) ) {
                $githubTagData = json_decode( wp_remote_retrieve_body( $result ) );
               
                if ( $githubTagData ) {
                    set_transient( $this->tagCacheKey, $githubTagData, GitHubUpdater::CACHE_TIME );
                    delete_transient( $this->headerCacheKey );
                }
            } 
        }

        return $githubTagData;
    }

    private function getHeaderInfo() {
        $headerData = get_transient( $this->headerCacheKey );
        if ( !$headerData ) {
            $result = wp_remote_get( $this->gibhubMainPhp );
            if( $result ) {
                if ( !is_wp_error( $result ) ) {
                    $body = wp_remote_retrieve_body( $result );
                    if ( $body ) {
                        if ( preg_match_all( '#[\s]+(.*): (.*)#', $body, $matches ) ) {
                            $headers = [];

                            for ( $i = 0; $i < count( $matches[ 0 ] ); $i++ ) {
                                $headers[ strtolower( $matches[ 1 ][ $i ] ) ] = $matches[ 2 ][ $i ];
                            }

                            $headerData = $headers;
                        }

                        set_transient( $this->headerCacheKey, $headerData, GitHubUpdater::CACHE_TIME );
                    }
                }
            }      
        }

        return $headerData;
    }    
}