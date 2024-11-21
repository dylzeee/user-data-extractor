<?php
/**
 * Plugin Name: User Data Extractor
 * Description: Consolidates user data into a custom database table and allows exporting as CSV.
 * Version: 1.0
 * Author: Dylan Jackson
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'UDE_PLUGIN_VERSION', '1.0' );
define( 'UDE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include required files.
require_once UDE_PLUGIN_DIR . 'includes/class-ude-database.php';
require_once UDE_PLUGIN_DIR . 'includes/class-ude-admin-ui.php';
require_once UDE_PLUGIN_DIR . 'includes/class-ude-sync.php';
require_once UDE_PLUGIN_DIR . 'includes/class-ude-synced-users-table.php';

// Register activation hook.
register_activation_hook( __FILE__, array( 'UDE_Database', 'create_custom_table' ) );

// Initialize the plugin.
add_action( 'plugins_loaded', 'ude_plugin_init' );
// Handle the form submission to sync user data.
add_action( 'admin_post_ude_sync_action', 'ude_handle_sync_request' );

function ude_plugin_init() {
    // Load plugin files.
    UDE_Admin_UI::init();
    UDE_Sync::init();
}

function ude_handle_sync_request() {
    if ( isset( $_POST['ude_action'] ) && $_POST['ude_action'] === 'sync_users_data' ) {
        // Verify the nonce.
        if ( ! isset( $_POST['ude_sync_nonce'] ) || ! wp_verify_nonce( $_POST['ude_sync_nonce'], 'ude_sync_action' ) ) {
            wp_die( 'Invalid nonce. Please refresh the page and try again.' );
        }

        // Perform the sync operation.
        UDE_Sync::sync_users_data();

        // Redirect back to the admin page with a success message.
        wp_redirect( add_query_arg( 'ude_sync_status', 'success', admin_url( 'admin.php?page=ude-admin-page' ) ) );
        exit;
    }
}

add_action( 'admin_post_ude_export_csv', 'ude_handle_export_csv' );

/**
 * Handles the Export to CSV action.
 */
function ude_handle_export_csv() {
  // Verify the nonce for security.
  if ( ! isset( $_POST['ude_export_nonce'] ) || ! wp_verify_nonce( $_POST['ude_export_nonce'], 'ude_export_csv' ) ) {
      wp_die( 'Invalid nonce. Please refresh the page and try again.' );
  }

  global $wpdb;
  $table_name = $wpdb->prefix . 'ude_user_data';

  // Fetch all columns from the table.
  $columns = $wpdb->get_results( "SHOW COLUMNS FROM $table_name", ARRAY_A );
  $csv_headers = array_column( $columns, 'Field' ); // Get column names as headers.

  // Query all data from the table.
  $users = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );

  // Output the CSV file.
  header( 'Content-Type: text/csv; charset=utf-8' );
  header( 'Content-Disposition: attachment; filename="synced_users.csv"' );

  $output = fopen( 'php://output', 'w' );

  // Add the headers to the CSV.
  fputcsv( $output, $csv_headers );

  // Add the data rows to the CSV.
  foreach ( $users as $user ) {
      fputcsv( $output, $user ); // Output all fields dynamically.
  }

  fclose( $output );
  exit;
}


