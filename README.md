# WP API Privacy

The default WordPress installation from wordpress.org automatically transmits personal information such as your website name via API various API calls in the admin.  

The current information we've found includes:
- The full public URL for your website (i.e. http://mysite.com)
- The version number of your WordPress installation

Combining this information with your IP address (which all servers can determine from incoming requests), provides WordPress.org with potentially intrusive insight into every website using WordPress.  

This plugin seeks to limit that information, protecting your privacy in the process. Simply install this plugin and activate it, and your website URL and WordPress version number will be stripped from outgoing API requests from your website.

## Installation

You can install the package either via a ZIP file, or by using composer.

### ZIP File

Navigate to the "Releases" [section in the sidebar](https://github.com/wp-privacy/wp-api-privacy/releases), and click on the latest release.  Inside the release you will see a ZIP file that looks like 
*wp-api-privacy-1.x.x.zip*.  Simply download that and use the WordPress plugin installer in the admin panel to add it.

### Composer

You can add the plugin to your website using Composer.  First navigate to your main WordPress plugins folder, typically located at *wp-content/plugins*. 

The execute the command:

```
composer create-project wp-privacy/wp-api-privacy
```

The navigate to your plugins page in the WordPress admin panel and activate the plugin
