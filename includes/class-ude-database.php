<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Handles database operations for the User Data Extractor plugin.
 */
class UDE_Database {

    /**
     * Creates the custom database table on plugin activation.
     */
    public static function create_custom_table() {
      global $wpdb;
  
      // Define the table name.
      $table_name = $wpdb->prefix . 'ude_user_data';
      $charset_collate = $wpdb->get_charset_collate();
  
      // SQL to create the table.
      $sql = "CREATE TABLE $table_name (
          id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          user_id BIGINT(20) UNSIGNED NOT NULL,
          first_name VARCHAR(255) NOT NULL,
          last_name VARCHAR(255) NOT NULL,
          email VARCHAR(255) NOT NULL,
          phone VARCHAR(20) NULL,
          billing_address TEXT NULL,
          shipping_address TEXT NULL,
          country VARCHAR(100) NULL,
          state VARCHAR(100) NULL,
          postal_code VARCHAR(20) NULL,
          meta_data LONGTEXT NULL,
          last_sync DATETIME NULL, /* New column to store the last sync timestamp */
          PRIMARY KEY (id),
          UNIQUE KEY user_id (user_id)
      ) $charset_collate;";
  
      // Include the WordPress dbDelta function.
      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  
      // Execute the SQL statement.
      dbDelta( $sql );
    }

    /**
     * Retrieves summary data for the admin page.
     *
     * @return array Summary data including total users and last sync time.
     */
    public static function get_summary() {
      global $wpdb;

      $table_name = $wpdb->prefix . 'ude_user_data';

      // Get the total number of users.
      $total_users = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

      // Get the most recent sync timestamp.
      $last_sync = $wpdb->get_var( "SELECT MAX(last_sync) FROM $table_name" );

      return array(
          'total_users' => intval( $total_users ),
          'last_sync'   => $last_sync,
      );
    }

  
}
