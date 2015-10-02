<?php

/**
 * Class HB_Comments
 *
 * Handle actions for comments and reviews
 */
class HB_Comments{

    /**
     * Constructor
     */
    function __construct(){
        add_action( 'comment_post', array( __CLASS__, 'add_comment_rating' ), 1 );
        add_action( 'hotel_booking_single_room_before_tabs_content_hb_room_reviews', 'comments_template' );
        add_filter( 'comments_template', array( __CLASS__, 'load_comments_template' ) );
        // details title tab
        add_action( 'hotel_booking_single_room_after_tabs_hb_room_reviews', array( __CLASS__, 'comments_count' ) );
        add_filter('hotel_booking_single_room_infomation_tabs', array( __CLASS__, 'addTabReviews' ));
    }

    /**
     * Load template for room reviews if it is enable
     */
    function comments_template(){
        if( comments_open() ){
            comments_template();
        }
    }

    /**
     * Load template for reviews if we found a file in theme/plugin directory
     *
     * @param string $template
     * @return string
     */
    static function load_comments_template( $template ){
        if ( get_post_type() !== 'hb_room' ) {
            return $template;
        }

        $check_dirs = array(
            trailingslashit( get_stylesheet_directory() ) . 'tp-hotel-booking',
            trailingslashit( get_template_directory() ) . 'tp-hotel-booking',
            trailingslashit( get_stylesheet_directory() ),
            trailingslashit( get_template_directory() ),
            trailingslashit( TP_Hotel_Booking::instance()->plugin_path( 'templates/' ) )
        );

        foreach ( $check_dirs as $dir ) {
            if ( file_exists( trailingslashit( $dir ) . 'single-room-reviews.php' ) ) {
                return trailingslashit( $dir ) . 'single-room-reviews.php';
            }
        }
    }

    /**
     * Add comment rating
     *
     * @param int $comment_id
     */
    public static function add_comment_rating( $comment_id ) {
        if ( isset( $_POST['rating'] ) && 'hb_room' === get_post_type( $_POST['comment_post_ID'] ) ) {
            if ( ! $_POST['rating'] || $_POST['rating'] > 5 || $_POST['rating'] < 0 ) {
                return;
            }
            // save comment rating
            add_comment_meta( $comment_id, 'rating', (int) esc_attr( $_POST['rating'] ), true );

            // save post meta arveger_rating
            $comment = get_comment( $comment_id );

            $postID = $comment->comment_post_ID;

            $room = HB_Room::instance( $postID );
            $averger_rating = $room->average_rating();

            $old_rating = get_post_meta( $postID, 'arveger_rating', true );
            $old_modify = get_post_meta( $postID, 'arveger_rating_last_modify', true );
            if( $old_rating )
            {
                update_post_meta( $postID, 'arveger_rating', $averger_rating );
                update_post_meta( $postID, 'arveger_rating_last_modify', time() );
            }
            else
            {
                add_post_meta( $postID, 'arveger_rating', $averger_rating );
                add_post_meta( $postID, 'arveger_rating_last_modify', time() );
            }
        }
    }

    static function comments_count()
    {
        global $hb_room;
        echo '<span class="comment-count">(' . $hb_room->get_review_count() . ')</span>';
    }

    static function addTabReviews( $tabsInfo )
    {
        if( ! comments_open() )
            return $tabsInfo;

        $tabsInfo[] = array(
            'id'        => 'hb_room_reviews',
            'title'     => __( 'Reviews', 'tp-hotel-booking' ),
            'content'   => ''
        );

        return $tabsInfo;
    }
}

new HB_Comments();