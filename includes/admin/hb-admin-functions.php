<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Common function for admin side
 */
/**
 * Define default tabs for settings
 *
 * @return mixed
 */
function hb_admin_settings_tabs(){
    return apply_filters( 'hb_admin_settings_tabs', array() );
}

/**
 * Admin translation text
 * @return mixed
 */
function hb_admin_i18n(){
    $i18n = array(
        'confirm_remove_pricing_table'  => __( 'Are you sure you want to remove this pricing table?', 'tp-hotel-booking' ),
        'empty_pricing_plan_start_date' => __( 'Select start date for plan', 'tp-hotel-booking'),
        'empty_pricing_plan_start_end'  => __( 'Select end date for plan', 'tp-hotel-booking'),
        'filter_error'                  => __( 'Please select date range and filter type', 'tp-hotel-booking' ),
        'date_time_format'              => hb_date_time_format_js(),
        'monthNames'                    => hb_month_name_js(),
        'monthNamesShort'               => hb_month_name_short_js(),
        'select_user'                   => __( 'Enter user login.', 'tp-hotel-booking' ),
        'select_room'                   => __( 'Enter room name.', 'tp-hotel-booking' ),
        'select_coupon'                 => __( 'Enter coupon code.', 'tp-hotel-booking' ),
    );
    return apply_filters( 'hb_admin_i18n', $i18n );
}

function hb_add_meta_boxes(){
    HB_Meta_Box::instance(
        'room_settings',
        array(
            'title'             => __( 'Room Settings', 'tp-hotel-booking' ),
            'post_type'         => 'hb_room',
            'meta_key_prefix'   => '_hb_',
            'priority'          => 'high'
        ),
        array()
    )->add_field(
        array(
            'name'      => 'num_of_rooms',
            'label'     => __( 'Quantity', 'tp-hotel-booking' ),
            'type'      => 'number',
            'std'       => '100',
            'desc'      => __( 'The number of rooms', 'tp-hotel-booking' ),
            'min'       => 1,
            'max'       => 100
        ),
        array(
            'name'      => 'room_capacity',
            'label'     => __( 'Number of adults', 'tp-hotel-booking' ),
            'type'      => 'select',
            'options'   => hb_get_room_capacities(
                array(
                    'map_fields' => array(
                        'term_id'   => 'value',
                        'name'      => 'text'
                    )
                )
            )
        ),
        array(
            'name'      => 'max_child_per_room',
            'label'     => __( 'Max child per room', 'tp-hotel-booking' ),
            'type'      => 'number',
            'std'       => 0,
            'min'       => 0,
            'max'       => 100
        ),
        array(
            'name'      => 'room_addition_information',
            'label'     => __( 'Addition Information', 'tp-hotel-booking' ),
            'type'      => 'textarea',
            'std'       => '',
            'editor'    => true
        )
    );

    // coupon meta box
    HB_Meta_Box::instance(
        'coupon_settings',
        array(
            'title'             => __( 'Coupon Settings', 'tp-hotel-booking' ),
            'post_type'         => 'hb_coupon',
            'meta_key_prefix'   => '_hb_',
            'context'           => 'normal',
            'priority'          => 'high'
        ),
        array()
    )->add_field(
        array(
            'name'      => 'coupon_description',
            'label'     => __( 'Description', 'tp-hotel-booking' ),
            'type'      => 'textarea',
            'std'       => ''
        ),
        array(
            'name'      => 'coupon_discount_type',
            'label'     => __( 'Discount type', 'tp-hotel-booking' ),
            'type'      => 'select',
            'std'       => '',
            'options'   => array(
                'fixed_cart' => __( 'Cart discount', 'tp-hotel-booking' ),
                'percent_cart' => __( 'Cart % discount', 'tp-hotel-booking' )
            )
        ),
        array(
            'name'      => 'coupon_discount_value',
            'label'     => __( 'Discount value', 'tp-hotel-booking' ),
            'type'      => 'number',
            'std'       => '',
            'min'       => 0,
            'step'      => 0.1
        ),
        array(
            'name'      => 'coupon_date_from',
            'label'     => __( 'Validate from', 'tp-hotel-booking' ),
            'type'      => 'datetime',
            'filter' => 'hb_meta_box_field_coupon_date'
        ),
        array(
            'name'      => 'coupon_date_from_timestamp',
            'label'     => '',
            'type'      => 'hidden'
        ),
        array(
            'name'      => 'coupon_date_to',
            'label'     => __( 'Validate until', 'tp-hotel-booking' ),
            'type'      => 'datetime',
            'filter' => 'hb_meta_box_field_coupon_date'
        ),
        array(
            'name'      => 'coupon_date_to_timestamp',
            'label'     => '',
            'type'      => 'hidden'
        ),
        array(
            'name'      => 'minimum_spend',
            'label'     => __( 'Minimum spend', 'tp-hotel-booking' ),
            'type'      => 'number',
            'desc'      => __( 'This field allows you to set the minimum subtotal needed to use the coupon.', 'tp-hotel-booking' ),
            'min'       => 0,
            'step'      => 0.1
        ),
        array(
            'name'      => 'maximum_spend',
            'label'     => __( 'Maximum spend', 'tp-hotel-booking' ),
            'type'      => 'number',
            'desc'      => __( 'This field allows you to set the maximum subtotal allowed when using the coupon.', 'tp-hotel-booking' ),
            'min'       => 0,
            'step'      => 0.1
        ),
        array(
            'name'      => 'limit_per_coupon',
            'label'     => __( 'Usage limit per coupon', 'tp-hotel-booking' ),
            'type'      => 'number',
            'desc'      => __( 'How many times this coupon can be used before it is void.', 'tp-hotel-booking' ),
            'min'       => 0
        ),
        array(
            'name'      => 'used',
            'label'     => __( 'Used', 'tp-hotel-booking' ),
            'type'      => 'label',
            'filter'    => 'hb_meta_box_field_coupon_used'
        )
    );

    HB_Meta_Box::instance(
        'gallery_settings',
        array(
            'title'             => __( 'Gallery Settings', 'tp-hotel-booking' ),
            'post_type'         => 'hb_room',
            'meta_key_prefix'   => '_hb_', // meta key prefix,
            'priority'          => 'high'
            // 'callback'  => 'hb_add_meta_boxes_gallery_setings' // callback arg render meta form
        ),
        array()
    )->add_field(
        array(
            'name'      => 'gallery',
            'type'      => 'gallery'
        )
    );
}
add_action( 'admin_init', 'hb_add_meta_boxes', 50 );

add_action( 'hb_booking_status_changed', 'hb_booking_status_completed_action', 10, 3 );
if ( ! function_exists( 'hb_booking_status_completed_action' ) ) {
    function hb_booking_status_completed_action( $booking_id, $old_status, $new_status ) {
        if ( $coupons = get_post_meta( $booking_id, '_hb_coupon_id' ) ) {
            if ( ! $coupons ) {
                return;
            }
            foreach ( $coupons as $coupon ) {
                $usage_count = get_post_meta( $coupon, '_hb_usage_count', true );
                if ( strpos( $new_status, 'completed' ) == 0 ) {
                    $usage_count++;
                } else {
                    if ( $usage_count > 0 ) {
                        $usage_count--;
                    } else {
                        $usage_count = 0;
                    }
                }
                update_post_meta( $coupon, '_hb_usage_count', $usage_count );
            }
        }
    }
}

add_action( 'admin_init', 'hb_admin_init_metaboxes', 50 );
if ( ! function_exists( 'hb_admin_init_metaboxes' ) ) {
    function hb_admin_init_metaboxes() {
        $metaboxes = array(
                new HB_Admin_Metabox_Booking_Details(), // booking details
                new HB_Admin_Metabox_Booking_Items(), // booking items
                new HB_Admin_Metabox_Booking_Actions(), // booking actions
                new HB_Admin_Metabox_Room_Price() // room price
            );
        return apply_filters( 'hb_admin_init_metaboxes', $metaboxes );
    }
}

/**
 * Custom booking list in admin
 *
 *
 * @param  [type] $default
 * @return [type]
 */
function hb_booking_table_head( $default ) {
    unset($default['author']);
    unset($default['date']);
    $default['customer']            = __( 'Customer', 'tp-hotel-booking' );
    $default['booking_date']        = __( 'Date', 'tp-hotel-booking' );
    $default['check_in_date']       = __( 'Check in', 'tp-hotel-booking' );
    $default['check_out_date']      = __( 'Check out', 'tp-hotel-booking' );
    $default['total']               = __( 'Total', 'tp-hotel-booking' );
    $default['title']               = __( 'Booking Order', 'tp-hotel-booking' );
    $default['status']              = __( 'Status', 'tp-hotel-booking' );
    return $default;
}
add_filter('manage_hb_booking_posts_columns', 'hb_booking_table_head');

/**
 * Retrieve information for listing in booking list
 *
 * @param  string
 * @param  int
 * @return mixed
 */
function hb_manage_booking_column( $column_name, $post_id ) {
    $booking = HB_Booking::instance( $post_id );
    $echo = array();
    $status = get_post_status( $post_id );
    switch ( $column_name ){
        case 'booking_id':
            $echo[] = hb_format_order_number( $post_id );
            break;
        case 'customer':
            $echo[] = hb_get_customer_fullname( $post_id, true );
            $echo[] = $booking->user_id && ( $user = get_userdata( $booking->user_id ) ) ? sprintf( '<br /><strong><small><a href="%s">%s</a></small></strong>', get_edit_user_link( $booking->user_id ), $user->user_login ) : '';
            break;
        case 'total':
            global $hb_settings;
            $total      = $booking->total();
            $currency   = $booking->payment_currency;
            if( ! $currency ) {
                $currency = $booking->currency;
            }
            $total_with_currency = hb_format_price( $total, hb_get_currency_symbol( $currency ) );

            $echo[] = $total_with_currency;
            if( $method = hb_get_user_payment_method( $booking->method ) ) {
                $echo[] = sprintf( __( '<br />(<small>%s</small>)', 'tp-hotel-booking' ), $method->description );
            }
            // display paid
            if( $status === 'hb-processing' )
            {
                $advance_payment =  $booking->advance_payment;
                $advance_settings = $booking->advance_payment_setting;
                if( ! $advance_settings ) {
                    $advance_settings = $hb_settings->get( 'advance_payment', 50 );
                }

                if ( floatval($total) !== floatval( $advance_payment ) ) {
                    $echo[] = sprintf(
                        __( '<br />(<small class="hb_advance_payment">Charged %s = %s</small>)', 'tp-hotel-booking' ),
                        $advance_settings . '%',
                        hb_format_price( $advance_payment, hb_get_currency_symbol( $currency ) )
                    );
                }
            }
            // end display paid
            do_action( 'hb_manage_booing_column_total', $post_id, $total, $total_with_currency );
            break;
        case 'booking_date':
            echo date( hb_get_date_format(), strtotime( get_post_field( 'post_date', $post_id ) ) );
            break;
        case 'check_in_date':
            if ( $booking->check_in_date ) {
                echo date( hb_get_date_format(), $booking->check_in_date );
            }
            break;
        case 'check_out_date':
            if( $booking->check_out_date ) {
                echo date( hb_get_date_format(), $booking->check_out_date );
            }
            break;
        case 'status':
            $link = '<a href="'. esc_attr( get_edit_post_link( $post_id ) ) . '">' . hb_get_booking_status_label( $post_id ) . '</a>';
            // $link = '<a href="'. admin_url('admin.php?page=hb_booking_details&id='. $post_id) . '">' . hb_get_booking_status_label( $post_id ) . '</a>';
            $echo[] = '<span class="hb-booking-status ' . $status . '">' . $link . '</span>';
    }
    echo apply_filters( 'hotel_booking_booking_total', sprintf( '%s', implode('', $echo) ), $column_name, $post_id );
}
add_action( 'manage_hb_booking_posts_custom_column', 'hb_manage_booking_column', 10, 2 );

function hb_request_query( $vars = array() ){
    global $typenow, $wp_query, $wp_post_statuses;

    if ( 'hb_booking' === $typenow ) {
        // Status
        if ( ! isset( $vars['post_status'] ) ) {
            $post_statuses = hb_get_booking_statuses();

            foreach ( $post_statuses as $status => $value ) {
                if ( isset( $wp_post_statuses[ $status ] ) && false === $wp_post_statuses[ $status ]->show_in_admin_all_list ) {
                    unset( $post_statuses[ $status ] );
                }
            }

            $vars['post_status'] = array_keys( $post_statuses );
        }
    }
    return $vars;
}
add_filter( 'request', 'hb_request_query' );

add_action( 'restrict_manage_posts', 'hb_booking_restrict_manage_posts' );
/**
 * First create the dropdown
 *
 * @return void
 */
function hb_booking_restrict_manage_posts(){
    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }

    //only add filter to post type you want
    if ('hb_booking' == $type){
        //change this to the list of values you want to show
        //in 'label' => 'value' format
        $from           = hb_get_request( 'date-from' );
        $from_timestamp = hb_get_request( 'date-from-timestamp' ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
        $to             = hb_get_request( 'date-to' );
        $to_timestamp   = hb_get_request( 'date-to-timestamp' ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
        $filter_type    = hb_get_request( 'filter-type' );

        $filter_types = apply_filters(
            'hb_booking_filter_types',
            array(
                'booking-date'      => __( 'Booking date', 'tp-hotel-booking' ),
                'check-in-date'     => __( 'Check-in date', 'tp-hotel-booking' ),
                'check-out-date'    => __( 'Check-out date', 'tp-hotel-booking' )
            )
        );

        ?>
        <span><?php _e( 'Date Range', 'tp-hotel-booking' ); ?></span>
        <input type="text" id="hb-booking-date-from" class="hb-date-field" value="<?php echo esc_attr( $from ); ?>" name="date-from" readonly placeholder="<?php _e( 'From', 'tp-hotel-booking' ); ?>" />
        <input type="hidden" value="<?php echo esc_attr( $from_timestamp ); ?>" name="date-from-timestamp" />
        <input type="text" id="hb-booking-date-to" class="hb-date-field" value="<?php echo esc_attr( $to ); ?>" name="date-to" readonly placeholder="<?php _e( 'To', 'tp-hotel-booking' ); ?>" />
        <input type="hidden" value="<?php echo esc_attr( $to_timestamp ); ?>" name="date-to-timestamp" />
        <select name="filter-type">
            <option value=""><?php _e( '---Filter By---', 'tp-hotel-booking' ); ?></option>
            <?php foreach( $filter_types as $slug => $text ){?>
            <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug == $filter_type ); ?>><?php echo esc_html( $text ); ?></option>
            <?php } ?>
        </select>
        <?php
    }
}

add_filter( 'parse_query', 'hb_booking_filter' );
/**
 * if submitted filter by post meta
 *
 * @param  (wp_query object) $query
 *
 * @return Void
 */
function hb_booking_filter( $query ){
    global $pagenow;
    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = sanitize_text_field( $_GET['post_type'] );
    }
    if ( 'hb_booking' == $type && is_admin() && $pagenow =='edit.php' && isset($_GET['filter_by_checkin_date']) && $_GET['filter_by_checkin_date'] != '') {
        $query->query_vars['meta_key'] = '_hb_check_in_date';
        $query->query_vars['meta_value'] = sanitize_text_field( $_GET['filter_by_checkin_date'] );
    }
    if ( 'hb_booking' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['filter_by_checkout_date']) && $_GET['filter_by_checkout_date'] != '') {
        //$query->query_vars['meta_key'] = '_hb_check_out_date';
        //$query->query_vars['meta_value'] = sanitize_text_field( $_GET['filter_by_checkout_date'] );
    }
}

function hb_edit_post_change_title_in_list() {
    add_filter( 'the_title', 'hb_edit_post_new_title_in_list', 100, 2 );
}
add_action( 'admin_head-edit.php', 'hb_edit_post_change_title_in_list' );

function hb_edit_post_new_title_in_list( $title, $post_id ){
    global $post_type;
    if( $post_type == 'hb_customer' ) {
        $title = hb_get_title_by_slug( get_post_meta( $post_id, '_hb_title', true ) );
        $first_name = get_post_meta($post_id, '_hb_first_name', true);
        $last_name = get_post_meta($post_id, '_hb_last_name', true);
        $customer_name = sprintf('%s %s %s', $title ? $title : 'Cus.', $first_name, $last_name);
        $title = $customer_name;
    }elseif( $post_type == 'hb_booking' ) {
        $title = hb_format_order_number( $post_id );
    }
    return $title;
}

function hb_admin_js_template(){
?>
    <script type="text/html" id="tmpl-room-type-gallery">
    <tr id="room-gallery-{{data.id}}" class="room-gallery">
        <td colspan="{{data.colspan}}">
            <div class="hb-room-gallery">
                <ul>
                    <# jQuery.each(data.gallery, function(){ var attachment = this; #>
                        <li class="attachment">
                            <div class="attachment-preview">
                                <div class="thumbnail">
                                    <div class="centered">
                                        <img src="{{attachment.src}}" alt="">
                                        <input type="hidden" name="hb-gallery[{{data.id}}][gallery][]" value="{{attachment.id}}" />
                                    </div>
                                </div>
                            </div>
                            <a class="dashicons dashicons-trash" title="<?php _e( 'Remove this image', 'tp-hotel-booking' ); ?>"></a>
                        </li>
                    <# }); #>
                    <li class="attachment add-new">
                        <div class="attachment-preview">
                            <div class="thumbnail">
                                <div class="dashicons-plus dashicons">
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <input type="hidden" name="hb-gallery[{{data.id}}][id]" value="{{data.id}}" />
        </td>
    </tr>
    </script>
    <script type="text/html" id="tmpl-room-type-attachment">
        <li class="attachment">
            <div class="attachment-preview">
                <div class="thumbnail">
                    <div class="centered">
                        <img src="{{data.src}}" alt="">
                        <input type="hidden" name="hb-gallery[{{data.gallery_id}}][gallery][]" value="{{data.id}}" />
                    </div>
                </div>
            </div>
            <a class="dashicons dashicons-trash" title="<?php _e( 'Remove this image', 'tp-hotel-booking' ); ?>"></a>
        </li>
    </script>
<?php
}

add_action( 'admin_print_scripts', 'hb_admin_js_template' );

function hb_meta_box_coupon_date( $value, $field_name, $meta_box_name ){
    if( in_array( $field_name, array( 'coupon_date_from', 'coupon_date_to' ) ) && $meta_box_name == 'coupon_settings' ){
        $value = strtotime( $value );
    }
    return $value;
}
add_filter( 'hb_meta_box_update_meta_value', 'hb_meta_box_coupon_date', 10, 3 );

function hb_meta_box_field_coupon_date( $value ){
    if( intval( $value ) ) {
        return date( hb_get_date_format(), $value);
    }
    return $value;
}

function hb_meta_box_field_coupon_used( $value ){
    global $post;
    return intval( get_post_meta( $post->ID, '_hb_usage_count', true ) );
}

if ( ! function_exists( 'hb_get_rooms' ) )
{
    /**
     * get all of post have post type hb_room
     */
    function hb_get_rooms()
    {
        $args = array(
                'post_type'         => 'hb_room',
                'posts_per_page'    => -1,
                'order'             => 'ASC',
                'orderby' => 'title'
            );

        return get_posts( $args );
    }
}

add_action( 'hb_booking_detail_update_meta_box', 'hb_booking_detail_update_meta_box', 10, 3 );
function hb_booking_detail_update_meta_box( $k, $vl, $post_id ) {
    if( get_post_type( $post_id ) !== 'hb_booking' ) {
        return;
    }

    if ( $k !== '_hb_booking_status' ) {
        return;
    }

    $status = sanitize_text_field( $vl );

    remove_action( 'save_post', array( 'HB_Admin_Metabox_Booking_Details', 'update' ) );

    $book = HB_Booking::instance( $post_id );
    $book->update_status( $status );

    add_action( 'save_post', array( 'HB_Admin_Metabox_Booking_Details', 'update' ) );
}

add_action( 'hb_update_meta_box_gallery_settings', 'hb_update_meta_box_gallery' );
if ( ! function_exists( 'hb_update_meta_box_gallery' ) ) {
    function hb_update_meta_box_gallery( $post_id ) {
        if( get_post_type() !== 'hb_room' ) {
            return;
        }

        if ( empty( $_POST['_hb_gallery'] ) ) {
            update_post_meta( $post_id, '_hb_gallery', array() );
        }
    }
}

if ( is_admin() ) {
    function hb_remove_revolution_slider_meta_boxes() {

        remove_meta_box( 'mymetabox_revslider_0', 'hb_room', 'normal' );
        remove_meta_box( 'mymetabox_revslider_0', 'hb_booking', 'normal' );
        // remove_meta_box( 'mymetabox_revslider_0', 'hb_customer', 'normal' );
        remove_meta_box( 'mymetabox_revslider_0', 'hb_coupon', 'normal' );
        remove_meta_box( 'submitdiv', 'hb_booking', 'side' );
    }

    add_action( 'do_meta_boxes', 'hb_remove_revolution_slider_meta_boxes' );
}
