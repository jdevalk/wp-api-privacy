### Version 1.1.8 - Nov 11th, 2024

A few minor changes in this release:
- German and Chinese languages updated (thanks)
- Modified method for stripping information from core update requests (thanks Matt Radford )

### Version 1.1.7 - Nov 10th, 2024

- fixed issue with tag comparison
- added check for main branch only updates

### Version 1.1.6 - Nov 10th, 2024

- Added: New settings for the user-agent to add a unique hash instead of removing the URL completely
- Added: a CHANGELOG.md to support Git Updater
- Added: additional banner assets for Git Updater

### Version 1.1.5 - Nov 9th, 2024

- Added: German translations (thanks to Udo Meisen)
- Added: New setting to control User-Agent for non wordpress.org calls
- Added: Settings link in the plugins list
- Previously added: Chinese translations (thanks to Alex Lion)


### Version 1.1.4 - Nov 9th, 2024

Working on improvements for the Github update mechanism

### Version 1.1.3 - Nov 9th, 2024

The following changes were made:
- Fixed: critical error when visiting the network admin in multi-site
- Added: basic statistics on admin panel options page

### Version 1.1.2 - Nov 9th, 2024

The composer.json was updated to list the project as a 'wordpress-plugin'.  It can now be installed into wp-content/plugins using 'composer require wp-privacy/wp-api-privacy'

### Version 1.1.0 - Nov 9th, 2024

- Added a new settings page with configurable settings
- Added new POT file for translating strings

### Version 1.0.3 - Nov 8th, 2024

Fixed a bug related to theme mismatch (thanks [Craig Riley](https://github.com/craigrileyuk)).  Added ability to force-look for an update via the the WordPress admin.  Starting initial work on settings page and internationalization.

### Version 1.0.2 - Nov 8th, 2024

This release fixes a bug where some plugins with Update URIs defined on wordpress.org wouldn't update anymore. It also adds additional privacy filtering on WordPress API core, theme, and plugin API calls.

### Version 1.0.1 - Nov 7th, 2024

Any plugins that indicate they are hosted off-site using the "Update URI" header in the plugin file will no longer report data to WordPress.org during update checks.  There is no reason to pass this data on as the updates are not provided by WordPress.org

### Version 1.0.0 - Nov 7th, 2024

Removes home site URL from the user-agent header to all outgoing web requests that use the WordPress HTTP API.

