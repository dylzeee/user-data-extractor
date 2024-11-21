<?php
/**
 * Uninstall script for User Data Extractor plugin.
 *
 * This script removes the custom database table and any options or transients related to the plugin.
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Define the table name.
$table_name = $wpdb->prefix . 'ude_user_data';

// Drop the custom database table.
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

// Optionally, delete any plugin-specific options or transients (if used).
delete_transient( 'ude_last_sync_count' );
