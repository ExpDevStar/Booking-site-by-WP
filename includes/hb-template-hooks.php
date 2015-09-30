<?php

add_action( 'hb_before_search_result', 'hb_enqueue_lightbox_assets' );
add_action( 'hb_lightbox_assets_lightbox2', 'hb_lightbox_assets_lightbox2' );
add_action( 'hb_lightbox_assets_fancyBox', 'hb_lightbox_assets_fancyBox' );

add_action( 'hb_wrapper_start', 'hb_display_message' );

// single-room.php hook template
add_action( 'hotel_booking_before_main_content', 'hotel_booking_before_main_content' );
add_action( 'hotel_booking_after_main_content', 'hotel_booking_after_main_content' );
add_action( 'hotel_booking_sidebar', 'hotel_booking_sidebar' );
//thumbnail
add_action('hotel_booking_loop_room_thumbnail', 'hotel_booking_loop_room_thumbnail');
// title
add_action('hotel_booking_loop_room_title', 'hotel_booking_room_title' );
add_action('hotel_booking_single_room_title', 'hotel_booking_room_title' );
// price display
add_action('hotel_booking_loop_room_price', 'hotel_booking_loop_room_price');
// pagination
add_action('hotel_booking_after_shop_loop', 'hotel_booking_after_shop_loop' );
// gallery
add_action('hotel_booking_single_room_gallery', 'hotel_booking_single_room_gallery' );
// room details
add_action('hotel_booking_single_room_infomation', 'hotel_booking_single_room_infomation' );
// room related
add_action( 'hotel_booking_after_single_product', 'hotel_booking_single_room_related' );
add_action('hotel_booking_single_room_infomation', 'hotel_booking_single_room_infomation' );
add_filter( 'body_class', 'hb_body_class' );
//add_filter( 'post_class', 'wc_product_post_class', 20, 3 );

// add_action( 'pre_get_posts', 'custom_num_room' );
// function custom_num_room( $query )
// {
	// if( $query->get( 'post_type' ) === 'hb_room' )
	// {
	// 	$query->set( 'posts_per_page', 5 );
	// }
	// return $query;
// }