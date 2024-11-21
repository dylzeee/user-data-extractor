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
add_action( 'admin_init', array( 'UDE_Admin_UI', 'register_settings' ) );

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
    $batch_size = 1000; // Number of rows per batch.

    // Set CSV headers for download.
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="synced_users.csv"' );

    // Open PHP output stream.
    $output = fopen( 'php://output', 'w' );

    // Write the CSV header row.
    $csv_headers = array( 'User ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Country', 'Last Sync' );
    fputcsv( $output, $csv_headers );

    $offset = 0;

    do {
        // Fetch a batch of rows.
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_id, first_name, last_name, email, phone, country, last_sync FROM $table_name LIMIT %d OFFSET %d",
                $batch_size,
                $offset
            ),
            ARRAY_A
        );

        // Break if no more rows are available.
        if ( empty( $rows ) ) {
            break;
        }

        // Write each row to the CSV.
        foreach ( $rows as $row ) {
            fputcsv( $output, $row );
        }

        // Increment the offset for the next batch.
        $offset += $batch_size;

        // Clear memory after processing a batch.
        unset( $rows );
        gc_collect_cycles();
    } while ( true );

    fclose( $output );
    exit;
}


if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'ude sync_users', 'ude_sync_users_cli' );
}

/**
 * WP-CLI command to sync users.
 */
function ude_sync_users_cli() {
    UDE_Sync::sync_users_data();
    WP_CLI::success( 'User sync completed successfully!' );
}


