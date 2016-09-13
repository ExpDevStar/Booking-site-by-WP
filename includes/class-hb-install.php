<?php

/**
 * @Author: ducnvtt
 * @Date:   2016-03-28 16:31:22
 * @Last Modified by:   ducnvtt
 * @Last Modified time: 2016-04-13 13:26:20
 */
if ( !defined( 'ABSPATH' ) ) {
    exit();
}

class HB_Install {

    static $upgrade = array();

    // install hook
    static function install() {

        if ( !defined( 'HB_INSTALLING' ) ) {
            define( 'HB_INSTALLING', true );
        }

        self::$upgrade = apply_filters( 'hotel_booking_upgrade_file_vesion', array(
            '1.1.5.1' => 'admin/update/hotel_booking-upgrade_1.1.5.1.php'
                ) );

        global $wpdb;
        if ( is_multisite() ) {
            // store the current blog id
            $current_blog = $wpdb->blogid;
            // Get all blogs in the network and activate plugin on each one
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach ( $blog_ids as $blog_id ) {
                // each blog
                switch_to_blog( $blog_id );

                self::do_install();

                // restore
                restore_current_blog();
            }
        } else {
            self::do_install();
        }
    }

    static function do_install() {

        // create pages
        self::create_pages();

        // create update options
        self::create_options();

        // create term default. Eg: Room Capacities
        // self::create_terms();
        // create tables
        self::create_tables();

        // upgrade database
        self::upgrade_database();

        update_option( 'hotel_booking_version', HB_VERSION );
    }

    // upgrade database
    static function upgrade_database() {
        hotel_booking_set_table_name();
        $version = get_option( 'hotel_booking_version', false );
        foreach ( self::$upgrade as $ver => $update ) {
            if ( !$version || version_compare( $version, $ver, '<' ) ) {
                include_once $update;
            }
        }
    }

    // create options default
    static function create_options() {
        if ( !class_exists( 'HB_Admin_Settings' ) ) {
            TP_Hotel_Booking::instance()->_include( 'includes/admin/class-hb-admin-settings.php' );
        }

        $settings_pages = HB_Admin_Settings::get_settings_pages();

        foreach ( $settings_pages as $setting ) {
            $options = $setting->get_settings();
            foreach ( $options as $option ) {
                if ( isset( $option['id'], $option['default'] ) ) {
                    if ( !get_option( $option['id'], false ) ) {
                        update_option( $option['id'], $option['default'] );
                    }
                }
            }
        }
    }

    // create page. Eg: hotel-checkout, hotel-cart
    static function create_pages() {
        if ( !function_exists( 'hb_create_page' ) ) {
            TP_Hotel_Booking::instance()->_include( 'includes/admin/hb-admin-functions.php' );
            TP_Hotel_Booking::instance()->_include( 'includes/hb-functions.php' );
        }

        $pages = array();
        if ( !hb_get_page_id( 'cart' ) || !get_post( hb_get_page_id( 'cart' ) ) ) {
            $pages['cart'] = array(
                'name' => _x( 'hotel-cart', 'Page Slug', 'tp-hotel-booking' ),
                'title' => _x( 'Hotel Cart', 'Page Title', 'tp-hotel-booking' ),
                'content' => '[' . apply_filters( 'hotel_booking_cart_shortcode_tag', 'hotel_booking_cart' ) . ']'
            );
        }

        if ( !hb_get_page_id( 'checkout' ) || !get_post( hb_get_page_id( 'checkout' ) ) ) {
            $pages['checkout'] = array(
                'name' => _x( 'hotel-checkout', 'Page Slug', 'tp-hotel-booking' ),
                'title' => _x( 'Hotel Checkout', 'Page Title', 'tp-hotel-booking' ),
                'content' => '[' . apply_filters( 'hotel_booking_checkout_shortcode_tag', 'hotel_booking_checkout' ) . ']'
            );
        }

        if ( !hb_get_page_id( 'search' ) || !get_post( hb_get_page_id( 'search' ) ) ) {
            $pages['search'] = array(
                'name' => _x( 'hotel-search', 'Page Slug', 'tp-hotel-booking' ),
                'title' => _x( 'Hotel Booking Search', 'Page Title', 'tp-hotel-booking' ),
                'content' => '[' . apply_filters( 'hotel_booking_search_shortcode_tag', 'hotel_booking' ) . ']'
            );
        }

        if ( !hb_get_page_id( 'account' ) || !get_post( hb_get_page_id( 'account' ) ) ) {
            $pages['account'] = array(
                'name' => _x( 'hotel-account', 'Page Slug', 'tp-hotel-booking' ),
                'title' => _x( 'Hotel Account', 'Page Title', 'tp-hotel-booking' ),
                'content' => '[' . apply_filters( 'hotel_booking_account_shortcode_tag', 'hotel_booking_account' ) . ']'
            );
        }

        if ( !hb_get_page_id( 'terms' ) || !get_post( hb_get_page_id( 'terms' ) ) ) {
            $pages['terms'] = array(
                'name' => _x( 'hotel-term-condition', 'Page Slug', 'tp-hotel-booking' ),
                'title' => _x( 'Terms and Conditions ', 'Page Title', 'tp-hotel-booking' ),
                'content' => apply_filters( 'hotel_booking_terms_content', 'Something notices' )
            );
        }

        if ( $pages && function_exists( 'hb_create_page' ) ) {
            foreach ( $pages as $key => $page ) {
                $pageId = hb_create_page( esc_sql( $page['name'] ), 'hotel_booking_' . $key . '_page_id', $page['title'], $page['content'], !empty( $page['parent'] ) ? hb_get_page_id( $page['parent'] ) : ''  );
                hb_settings()->set( $key . '_page_id', $pageId );
            }
        }
    }

    // create terms default for system
    static function create_terms() {
        if ( !class_exists( 'HB_Post_Types' ) ) {
            TP_Hotel_Booking::instance()->_include( 'includes/class-hb-post-types.php' );
        }

        // register taxonomies
        HB_Post_Types::register_taxonomies();

        $taxonomies = array(
            'hb_room_capacity' => array(
                'double' => array(
                    'hb_max_number_of_adults' => 2
                ),
                'single' => array(
                    'hb_max_number_of_adults' => 1,
                    'alias_of' => 2
                )
            )
        );

        // insert term
        foreach ( $taxonomies as $taxonomy => $terms ) {
            foreach ( $terms as $term => $term_op ) {
                if ( !get_term_by( 'slug', sanitize_title( $term ), $taxonomy ) ) {
                    $term = wp_insert_term( $term, $taxonomy, array() );

                    if ( !is_wp_error( $term ) ) {
                        foreach ( $term_op as $k => $v ) {
                            add_term_meta( $term['term_id'], $k, $v, true );
                        }
                    }
                }
            }
        }
    }

    // create tables. Eg: booking_items
    static function create_tables() {
        self::schema();
    }

    // do create table
    static function schema() {
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $charset_collate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . 'hotel_booking_order_items';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) != $table ) {

            // order items
            $sql = "
				CREATE TABLE IF NOT EXISTS {$wpdb->prefix}hotel_booking_order_items (
					order_item_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					order_item_name longtext NOT NULL,
					order_item_type varchar(255) NOT NULL,
					order_item_parent bigint(20) NULL,
					order_id bigint(20) unsigned NOT NULL,
					UNIQUE KEY order_item_id (order_item_id),
					PRIMARY KEY  (order_item_id)
				) $charset_collate;
			";
            dbDelta( $sql );
        }

        $table = $wpdb->prefix . 'hotel_booking_order_itemmeta';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) != $table ) {

            // order item meta
            $sql = "
				CREATE TABLE IF NOT EXISTS {$wpdb->prefix}hotel_booking_order_itemmeta (
					meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					hotel_booking_order_item_id bigint(20) unsigned NOT NULL,
					meta_key varchar(255) NULL,
					meta_value longtext NULL,
					UNIQUE KEY meta_id (meta_id),
					PRIMARY KEY  (meta_id),
					KEY hotel_booking_order_item_id(hotel_booking_order_item_id),
					KEY meta_key(meta_key)
				) $charset_collate;
			";
            dbDelta( $sql );
        }

        $table = $wpdb->prefix . 'hotel_booking_plans';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) != $table ) {

            // pricing tables
            $sql = "
				CREATE TABLE IF NOT EXISTS {$wpdb->prefix}hotel_booking_plans (
					plan_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					room_id bigint(20) unsigned NOT NULL,
					start_time timestamp NULL,
					end_time timestamp NULL,
					pricing longtext NULL,
					UNIQUE KEY plan_id (plan_id),
					PRIMARY KEY  (plan_id)
				) $charset_collate;
			";
            dbDelta( $sql );
        }
    }

    // delete table when delete blog
    static function delete_tables( $tables ) {
        global $wpdb;
        $tables[] = $wpdb->prefix . 'hotel_booking_order_items';
        $tables[] = $wpdb->prefix . 'hotel_booking_order_itemmeta';
        $tables[] = $wpdb->prefix . 'hotel_booking_plans';
        return $tables;
    }

    // create new table when create new blog multisite
    static function create_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        $plugin = basename( HB_PLUGIN_PATH ) . '/' . basename( HB_PLUGIN_PATH ) . '.php';
        if ( is_plugin_active_for_network( $plugin ) ) {
            // switch to current blog
            switch_to_blog( $blog_id );

            self::create_tables( true );

            // restore
            restore_current_blog();
        }
    }

}
