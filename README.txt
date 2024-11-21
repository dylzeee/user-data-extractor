=== User Data Extractor ===
Contributors: Dylan Jackson
Tags: export, csv, users, data, WooCommerce
Requires at least: 5.6
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin that consolidates user data into a custom database table and provides options to sync, manage, and export it as CSV.

== Description ==

User Data Extractor allows WordPress administrators to:
* Sync user data from WordPress and WooCommerce into a custom database table.
* Manage the data using an interactive admin interface.
* Export all synced data to a CSV file.
* Delete selected rows using bulk actions.

This plugin is ideal for WooCommerce store managers and developers who need a structured way to consolidate and extract user data for reporting, analysis, or migration.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the "User Data Extractor" admin page under "Tools" to sync, manage, and export user data.

== Frequently Asked Questions ==

= Does this plugin work with WooCommerce? =
Yes! It supports extracting WooCommerce user meta fields like billing and shipping details.

= Can I schedule automatic syncing? =
Scheduled syncing will be added in future updates.

= What fields are exported to CSV? =
All fields stored in the custom database table are exported.

= Does uninstalling the plugin remove all data? =
Yes, uninstalling the plugin will delete the custom database table and any plugin-specific transients.


== Future Updates ==

* Scheduled syncing using WP-Cron.
* Advanced filtering and sorting options for the admin table.
* Search functionality for synced user data.
* Integration with third-party plugins to extract additional user meta fields.
* Improved performance for handling large user datasets.
* Support for exporting data in additional formats (e.g., JSON, XML).
* Role-based access control for managing who can sync, view, and export data.
* Localization support for translating the plugin into multiple languages.


== Screenshots ==

1. Admin interface showing synced user data.
2. Summary section with Sync and Export buttons.
3. Example of CSV file output.

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
Initial release with sync, manage, and export features.

