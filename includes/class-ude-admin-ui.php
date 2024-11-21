<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Handles the admin interface for the User Data Extractor plugin.
 */
class UDE_Admin_UI {

    /**
     * Initializes the admin UI hooks.
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
    }

    /**
     * Adds the plugin's admin menu.
     */
    public static function add_admin_menu() {
        add_menu_page(
            'User Data Extractor',            // Page title.
            'User Data Extractor',            // Menu title.
            'manage_options',                 // Capability required to access.
            'ude-admin-page',                 // Menu slug.
            array( __CLASS__, 'render_admin_page' ), // Callback to render the page.
            'dashicons-database',             // Icon for the menu.
            80                                // Position in the admin menu.
        );
    }

    /**
     * Renders the admin page.
     */
    public static function render_admin_page() {
      if ( isset( $_POST['action'] ) ) {
        error_log( 'Bulk action detected: ' . sanitize_text_field( $_POST['action'] ) );
    }
      // Handle bulk delete action.
      if ( isset( $_POST['action'] ) && $_POST['action'] === 'delete' ) {
        if ( ! empty( $_POST['ids'] ) && is_array( $_POST['ids'] ) ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'ude_user_data';

            // Sanitize and delete the selected rows.
            $ids = array_map( 'intval', $_POST['ids'] ); // Ensure IDs are integers.
            $ids_placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $table_name WHERE id IN ($ids_placeholders)",
                    $ids
                )
            );

            echo '<div class="notice notice-success is-dismissible"><p>Selected users have been deleted.</p></div>';
        }
      }

      // Fetch summary data.
      $summary = UDE_Database::get_summary();
      $total_users = $summary['total_users'];
      $last_sync = $summary['last_sync'];
  
      // Check for a recent sync result.
      $last_sync_count = get_transient( 'ude_last_sync_count' );
      if ( $last_sync_count !== false ) {
          ?>
          <div class="notice notice-success is-dismissible">
              <p><?php echo esc_html( $last_sync_count ); ?> users were successfully synced.</p>
          </div>
          <?php
          delete_transient( 'ude_last_sync_count' );
      }
  
      ?>
      <div class="wrap">
        <h1>User Data Extractor</h1>

        <h2>Summary</h2>
        <ul>
            <li><strong>Total Users Synced:</strong> <?php echo esc_html( $total_users ); ?></li>
            <li><strong>Last Sync:</strong> <?php echo esc_html( $last_sync ? $last_sync : 'No syncs yet' ); ?></li>
        </ul>

        <!-- Sync and Export Buttons -->
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline;">
            <?php wp_nonce_field( 'ude_sync_action', 'ude_sync_nonce' ); ?>
            <input type="hidden" name="action" value="ude_sync_action">
            <input type="hidden" name="ude_action" value="sync_users_data">
            <button type="submit" class="button button-primary">Sync User Data</button>
        </form>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline;">
            <?php wp_nonce_field( 'ude_export_csv', 'ude_export_nonce' ); ?>
            <input type="hidden" name="action" value="ude_export_csv">
            <button type="submit" class="button button-secondary">Export to CSV</button>
        </form>

        <hr>

        <!-- Bulk Actions Form -->
        <form method="post">
            <?php wp_nonce_field( 'ude_bulk_action', 'ude_bulk_nonce' ); ?>
            <?php
            $table = new UDE_Synced_Users_Table();
            $table->prepare_items();
            $table->display(); // Display the table without the "Synced Users" heading.
            ?>
        </form>
      </div>

      <?php
    }
  
  
  

  
}
