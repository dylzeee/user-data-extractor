<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Defines the table for displaying synced users.
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class UDE_Synced_Users_Table extends WP_List_Table {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( array(
            'singular' => 'user',   // Singular name for one item.
            'plural'   => 'users',  // Plural name for multiple items.
            'ajax'     => false,    // No AJAX for this table.
        ) );
    }

    /**
     * Retrieve the list of synced users.
     *
     * @return array List of users from the custom table.
     */
    private function get_users_data() {
      global $wpdb;
      $table_name = $wpdb->prefix . 'ude_user_data';
  
      // Get sorting options from the request.
      $orderby = ! empty( $_REQUEST['orderby'] ) ? sanitize_sql_orderby( $_REQUEST['orderby'] ) : 'last_sync';
      $order   = ! empty( $_REQUEST['order'] ) && in_array( strtolower( $_REQUEST['order'] ), array( 'asc', 'desc' ) )
          ? strtoupper( $_REQUEST['order'] )
          : 'DESC';
  
      // Build the query with sorting options.
      $query = "SELECT * FROM $table_name ORDER BY $orderby $order";
  
      return $wpdb->get_results( $query, ARRAY_A );
    }
  

    /**
     * Defines the columns of the table.
     *
     * @return array Column headers.
     */
    public function get_columns() {
      return array(
          'cb'           => '<input type="checkbox" />', // Add a checkbox for bulk selection.
          'user_id'      => 'User ID',
          'first_name'   => 'First Name',
          'last_name'    => 'Last Name',
          'email'        => 'Email',
          'phone'        => 'Phone',
          'country'      => 'Country',
          'last_sync'    => 'Last Sync',
      );
    }

    /**
     * Renders the checkbox for a single row.
     *
     * Each checkbox is associated with the row's ID for bulk actions.
     *
     * @param array $item The current row's data.
     * @return string HTML for the checkbox.
     */
    public function column_cb( $item ) {
      return sprintf(
          '<input type="checkbox" name="ids[]" value="%d" />',
          $item['id'] // Use the row's primary key (ID) as the checkbox value.
      );
    }

    /**
     * Defines sortable columns for the table.
     *
     * This method specifies which columns can be sorted and sets their default sorting order.
     * The sorting parameters (e.g., column name and order) are passed via the query string
     * and handled when preparing the items.
     *
     * @return array An associative array of sortable columns. Each key is the column name,
     *               and the value is an array with the actual database column and default sort order.
     */
    public function get_sortable_columns() {
      return array(
          'first_name' => array( 'first_name', true ), // Sort by first name (default ASC).
          'last_name'  => array( 'last_name', true ),  // Sort by last name.
          'email'      => array( 'email', true ),      // Sort by email address.
          'last_sync'  => array( 'last_sync', true ),  // Sort by last sync timestamp.
      );
    }


    /**
     * Prepares the items to display in the table.
     */
    public function prepare_items() {
      $columns  = $this->get_columns(); // Get table columns.
      $hidden   = array(); // No hidden columns.
      $sortable = $this->get_sortable_columns(); // Get sortable columns.
  
      // Set up the column headers for the table.
      $this->_column_headers = array( $columns, $hidden, $sortable );
  
      // Retrieve the table data.
      $this->items = $this->get_users_data(); // Fetch users for display.
    }

    /**
     * Defines available bulk actions.
     *
     * @return array List of bulk actions.
     */
    public function get_bulk_actions() {
      return array(
          'delete' => 'Delete', // Add a "Delete" bulk action.
      );
    }

    /**
     * Renders a single column for a row.
     *
     * @param array  $item        The current item.
     * @param string $column_name The current column name.
     *
     * @return string The content of the column.
     */
    public function column_default( $item, $column_name ) {
        return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
    }
}
