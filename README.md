# wp-api-private

The default WordPress installation from wordpress.org automatically transmits personal information such as your website name via API various API calls in the admin.  

The current information we've found includes:
- The full public URL for your website (i.e. http://mysite.com)
- The version number of your WordPress installation

Combining this information with your IP address (which all servers can determine from incoming requests), provides WordPress.org with potentially intrusive insight into every website using WordPress.  

This plugin seeks to limit that information, protecting your privacy in the process. Simply install this plugin and activate it, and your website URL and WordPress version number will be stripped from outgoing API requests from your website.

