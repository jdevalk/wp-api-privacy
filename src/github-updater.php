<?php

namespace WP_Privacy\WP_API_Privacy;

class GitHubUpdater {
    private const CACHE_TIME = ( 60 * 15 ); // 15 minutes
    private const USER_AGENT = 'WordPress/Private';

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
            $this->checkForUpdate();
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

    private function checkForUpdate() {
        $headerData = $this->getHeaderInfo();
        $releaseInfo = $this->getReleaseInfo();

        if ( $headerData && $releaseData ) {
            $this->updateInfo = new \stdClass;
            $this->updateInfo->version = $headerData[ 'Stable' ];
            $this->updateInfo->cache_key = $this->githubBranch . '_' . $this->$this->cacheModifier;

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