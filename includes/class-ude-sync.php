<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Handles user data syncing for the User Data Extractor plugin.
 */
class UDE_Sync {

    /**
       * Initializes the UDE_Sync class.
       */
      public static function init() {
        // Add any hooks or filters specific to this class here.
    }

    /**
     * Syncs all users' data into the custom database table.
     */
    public static function sync_users_data() {
        global $wpdb;
    
        $table_name = $wpdb->prefix . 'ude_user_data';
        $batch_size = 1000; // Process 1,000 users at a time.
        $offset = 0;
    
        do {
            // Fetch a batch of users.
            $users = get_users( array(
                'number' => $batch_size,
                'offset' => $offset,
            ) );
    
            // Break if no more users are available.
            if ( empty( $users ) ) {
                break;
            }
    
            foreach ( $users as $user ) {
                // Get user details.
                $user_id    = $user->ID;
                $first_name = get_user_meta( $user_id, 'first_name', true );
                $last_name  = get_user_meta( $user_id, 'last_name', true );
                $email      = $user->user_email;
    
                // WooCommerce-specific fields.
                $phone            = get_user_meta( $user_id, 'billing_phone', true );
                $billing_address  = self::get_full_address( $user_id, 'billing' );
                $shipping_address = self::get_full_address( $user_id, 'shipping' );
                $country          = get_user_meta( $user_id, 'billing_country', true );
                $state            = get_user_meta( $user_id, 'billing_state', true );
                $postal_code      = get_user_meta( $user_id, 'billing_postcode', true );
    
                // Meta data.
                $meta_data = json_encode( array(
                    'roles'      => $user->roles,
                    'registered' => $user->user_registered,
                ) );
    
                // Insert or update the user data in the database.
                $wpdb->replace(
                    $table_name,
                    array(
                        'user_id'         => $user_id,
                        'first_name'      => $first_name,
                        'last_name'       => $last_name,
                        'email'           => $email,
                        'phone'           => $phone,
                        'billing_address' => $billing_address,
                        'shipping_address'=> $shipping_address,
                        'country'         => $country,
                        'state'           => $state,
                        'postal_code'     => $postal_code,
                        'meta_data'       => $meta_data,
                        'last_sync'       => current_time( 'mysql' ),
                    ),
                    array(
                        '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                    )
                );
            }
    
            // Increase the offset to process the next batch.
            $offset += $batch_size;
    
        } while ( true );
    }    
  
  

    /**
     * Helper function to construct full address.
     *
     * @param int    $user_id User ID.
     * @param string $type Address type ('billing' or 'shipping').
     * @return string Full address.
     */
    private static function get_full_address( $user_id, $type ) {
        $address_1 = get_user_meta( $user_id, "{$type}_address_1", true );
        $address_2 = get_user_meta( $user_id, "{$type}_address_2", true );
        $city      = get_user_meta( $user_id, "{$type}_city", true );

        $full_address = trim( "{$address_1}, {$address_2}, {$city}" );

        return $full_address;
    }
}
