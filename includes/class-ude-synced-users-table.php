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
          'singular' => 'ude_synced_user', // Singular name of the table (e.g., for one item).
          'plural'   => 'ude_synced_users', // Plural name of the table.
          'ajax'     => false, // Set to true if you want to enable AJAX functionality.
      ) );// Initialize columns manually.
       // Manually set column headers.
    $this->_column_headers = array(
      $this->get_columns(), // Columns.
      array(), // Hidden columns.
      array(), // Sortable columns.
  );

  // Debug: Log column headers.
  error_log( 'Column headers set in constructor: ' . print_r( $this->_column_headers, true ) );
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
      $columns = array(
          'cb'           => '<input type="checkbox" />', // Add a checkbox for bulk selection.
          'user_id'      => 'User ID',
          'first_name'   => 'First Name',
          'last_name'    => 'Last Name',
          'email'        => 'Email',
          'phone'        => 'Phone',
          'country'      => 'Country',
          'last_sync'    => 'Last Sync',
      );

      return $columns;
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
      global $wpdb;
  
      $table_name = $wpdb->prefix . 'ude_user_data';
  
      // Number of items to display per page.
      $per_page = 100;
  
      // Get the current page number from the request.
      $current_page = $this->get_pagenum();
  
      // Calculate the offset for the SQL query.
      $offset = ( $current_page - 1 ) * $per_page;
  
      // Get the total number of items in the table.
      $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
  
      // Fetch the items for the current page.
      $this->items = $wpdb->get_results(
          $wpdb->prepare(
              "SELECT * FROM $table_name LIMIT %d OFFSET %d",
              $per_page,
              $offset
          ),
          ARRAY_A
      );
  
      // Debug: Inspect column info from WP_List_Table.
      list( $columns, $hidden ) = $this->get_column_info();
      error_log( 'Columns from get_column_info: ' . print_r( $columns, true ) );
  
      // Set up pagination.
      $this->set_pagination_args( array(
          'total_items' => $total_items, // Total number of items.
          'per_page'    => $per_page,   // Items per page.
          'total_pages' => ceil( $total_items / $per_page ), // Total number of pages.
      ) );
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
