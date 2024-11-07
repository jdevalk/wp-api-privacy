# WP API Privacy

The default WordPress installation from wordpress.org automatically transmits personal information such as your website name via various HTTP calls that occur the admin.  

The current information we've found includes:
- The full public URL for your website (i.e. http://mysite.com)
- The version number of your WordPress installation

Combining this information with your IP address (which all servers can determine from incoming requests), provides the recipient with potentially intrusive insight into every website using the WordPress platform. 

This plugin seeks to limit that information, attempting to further protect your privacy in the process. Simply install this plugin and activate it, and your website URL and WordPress version number will be stripped from outgoing API requests from your website.  Some API calls, such as the ones to the plugin listings, also contain a version parameter to filter 
the associated list of plugins - these are left in (but your website URL is still stripped).

## Installation

You can install the package either via a ZIP file, or by using composer.

### ZIP File

Navigate to the "Releases" [section in the sidebar](https://github.com/wp-privacy/wp-api-privacy/releases/latest), and click on the latest release.  Inside the release you will see a ZIP file that looks like 
*wp-api-privacy-1.x.x.zip*.  Simply download that file and then use the WordPress plugin installer in the admin panel to add it.

### Composer

You can add the plugin to your website using Composer.  First navigate to your main WordPress plugins folder, typically located at *wp-content/plugins*. 

The execute the command:
```
composer create-project wp-privacy/wp-api-privacy
```

Then navigate to your plugins page in the WordPress admin panel and activate the plugin

### Future Updates

The plugin will automatically fetch updates via the WordPress admin from this Github repository using the WordPress update mechanism (you will be notified in the admin when an update 
is available).

## Verification

After installing the plugin, you can also use the "HTTP Requests Manager" plugin to verify the user-agent field has been changed to "WordPress/Private"

