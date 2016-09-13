<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function hb_register_web_hook( $key, $param ) {
    if ( !$key ) {
        return;
    }
    if ( empty( $GLOBALS['tp-hotel-booking']['web_hooks'] ) ) {
        $GLOBALS['tp-hotel-booking']['web_hooks'] = array();
    }
    $GLOBALS['tp-hotel-booking']['web_hooks'][$key] = $param;
    do_action( 'hb_register_web_hook', $key, $param );
}

function hb_get_web_hooks() {
    $web_hooks = empty( $GLOBALS['tp-hotel-booking']['web_hooks'] ) ? array() : (array) $GLOBALS['tp-hotel-booking']['web_hooks'];
    return apply_filters( 'hb_web_hooks', $web_hooks );
}

function hb_get_web_hook( $key ) {
    $web_hooks = hb_get_web_hooks();
    $web_hook = empty( $web_hooks[$key] ) ? false : $web_hooks[$key];
    return apply_filters( 'hb_web_hook', $web_hook, $key );
}

function hb_process_web_hooks() {
    // Grab registered web_hooks
    $web_hooks = hb_get_web_hooks();
    $web_hooks_processed = false;
    // Loop through them and init callbacks

    foreach ( $web_hooks as $key => $param ) {
        if ( !empty( $_REQUEST[$param] ) ) {
            $web_hooks_processed = true;
            $request_scheme = is_ssl() ? 'https://' : 'http://';
            $requested_web_hook_url = untrailingslashit( $request_scheme . $_SERVER['HTTP_HOST'] ) . $_SERVER['REQUEST_URI']; //REQUEST_URI includes the slash
            $parsed_requested_web_hook_url = parse_url( $requested_web_hook_url );
            $required_web_hook_url = add_query_arg( $param, '1', trailingslashit( get_site_url() ) ); //add the slash to make sure we match
            $parsed_required_web_hook_url = parse_url( $required_web_hook_url );
            $web_hook_diff = array_diff_assoc( $parsed_requested_web_hook_url, $parsed_required_web_hook_url );

            if ( empty( $web_hook_diff ) ) { //No differences in the requested webhook and the required webhook
                do_action( 'hb_web_hook_' . $param, $_REQUEST );
            } else {
                
            }
            break; //we can stop processing here... no need to continue the foreach since we can only handle one webhook at a time
        }
    }
    if ( $web_hooks_processed ) {
        do_action( 'hb_web_hooks_processed' );
        wp_die( __( 'TP Hotel Booking webhook process Complete', 'tp-hotel-booking' ), __( 'TP Hotel Booking webhook process Complete', 'tp-hotel-booking' ), array( 'response' => 200 ) );
    }
}

add_action( 'wp', 'hb_process_web_hooks' );
