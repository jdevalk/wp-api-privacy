# WP API Privacy

The default WordPress installation from wordpress.org automatically transmits extraneous information via various HTTP calls that occur in the admin. Some of this data may be cause for concern from a privacy perspective. 

This plugin seeks to limit that information, attempting to further protect your privacy in the process. Simply install this plugin and activate it, and various aspects of WordPress that 
are questionable from a privacy perspective will be modified.  

## Modifications Made 

Default outgoing HTTP requests to third-party services like the plugin and theme update mechanism at WordPress.org contains site and version information in the User-Agent header.  For example, all requests contain your website name in the form of http://mysite.com, and a version string such as 6.6, giving third-parties detailed information about your site.  Combining this information with your IP address (which all servers can determine from incoming requests), provides the recipient with potentially intrusive insight into every website using the WordPress platform. 

Once active, the plugin strips can be configured to strip this information so requests do not contain information about the domain name that requested them or which version of WordPress it was using.  Some API calls, such as the ones to the plugin listings, also contain a version parameter to filter the associated list of plugins - these are left in.

### Plugin And Theme Data

When a default WordPress installation contains WordPress.org requests information about plugin and theme updates, it sends detailed information about every plugin and theme on your WordPress site, including all the plugin and theme headers available.  This occurs even for private plugins or themes, or plugins and themes that are not hosted on WordPress.org.

After activation, any plugins or themes that update from third-party repositories (as indicated by the *Update URI* in the plugin header) will be filtered on all outbound requests.

### Core Requests

When WordPress attempts to do a core software update, it sends along detailed information such as your site URL, how many users you have, how many blogs you have, your MySQL version, your PHP version, the type of server you have (i.e Mac, Linux, Windows, etc) and all the PHP extensions you have on your site.  This information can also be selectively filtered to only provide what's absolutey essential to the WordPress API servers.

## Installation

You can install the package either via a ZIP file, or by using composer.  Please note, this plugin is still in active development - please don't install it on any production sites, but feel free to test it on development or less essential sites to help provide feedback. 

### ZIP File

Navigate to the "Releases" [section in the sidebar](https://github.com/wp-privacy/wp-api-privacy/releases/latest), and click on the latest release.  Inside the release you will see a ZIP file that looks like 
*wp-api-privacy.zip*.  Simply download that file and then use the WordPress plugin installer in the admin panel to add it.

### Composer

You can add the plugin to your website using Composer.  First navigate to your main WordPress directory. 

The execute the command:
```
composer require wp-privacy/wp-api-privacy
```

This will install the plugin to your wp-content/plugins directory.  Once done, navigate to your plugins page in the WordPress admin panel and activate the plugin.

### Future Updates

The plugin will automatically fetch updates via the WordPress admin from this Github repository using the WordPress update mechanism (you will be notified in the admin when an update 
is available).

## Verification

After installing the plugin, you can also use the "HTTP Requests Manager" plugin to verify the user-agent field has been changed to "WordPress/Private", and that the plugin information
is stripped of any plugins hosted off-site.

