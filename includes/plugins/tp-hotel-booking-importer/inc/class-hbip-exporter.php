<?php
/**
 * @Author: ducnvtt
 * @Date:   2016-04-25 11:26:10
 * @Last Modified by:   ducnvtt
 * @Last Modified time: 2016-04-29 16:13:34
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class HBIP_Exporter {

	public function __construct() {

		/* export submit */
		add_action( 'admin_init', array( $this, 'export' ) );
	}

	public function export() {
		/* verify nonce */
		if ( ! isset( $_POST['hbtool_export'] ) || ! wp_verify_nonce( $_POST['hbtool_export'], 'hbtool_export' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$args = array(
				'export'	=> isset( $_POST[ 'export' ] ) ? $_POST[ 'export' ] : 'all'
			);

		/* export */
		$this->export_tables( $args ); exit();
	}

	/* export */
	function export_tables( $args = array() ) {

		/**
		 * Fires at the beginning of an export, before any headers are sent.
		 *
		 * @since 2.3.0
		 *
		 * @param array $args An array of export arguments.
		 */
		do_action( 'hotel_booking_tool_export', $args );

		$sitename = sanitize_key( get_bloginfo( 'name' ) );
		if ( ! empty( $sitename ) ) {
			$sitename .= '.';
		}
		$date = date( 'Y-m-d' );
		$filename = $sitename . 'hotel_data.' . $date . '.xml';

		/* get rooms */
		$users = hbip_get_rooms( $args );
		/**
		 * Filter the export filename.
		 *
		 * @since 4.4.0
		 *
		 * @param string $wp_filename The name of the file for download.
		 * @param string $sitename    The site name.
		 * @param string $date        Today's date, formatted.
		 */
		$filename = apply_filters( 'hb_tool_export_filename', $filename, $sitename, $date );

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

		ob_start();
		echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n";
	    /* Print comments */
	    echo "<!-- This is a export of the Hotel Booking Tables -->\n";
	    echo "<!-- (Created by Importer addon, support TP Hotel Booking. Power by @Thimpress.com ) -->\n";
	    echo "<!--  (Optional) Bookings, Rooms, Capacities, Pricing Plans, Addtion Packages, Block Special Date-->\n";
	    /* End print comments */
	$room_ids = hbip_get_rooms( true ); /* get room ids */
	$booking_ids = hbip_get_books( true ); /* get booking ids */
	$user_ids = hbip_get_users( $room_ids, $booking_ids ); /* get user ids */
	unset( $room_ids, $booking_ids );
    ?>

<rss version="2.0" xmlns:hb="http://thimpress.com/">
	<channel>
		<hb:siteurl><?php echo get_option( 'siteurl' ); ?></hb:siteurl>
	<!-- users -->
	<?php if ( in_array( $args['export'], array( 'all', 'users' ) ) ) : foreach ( $user_ids as $user_id ) : if ( $user = get_userdata( $user_id ) ) : ?>
		<hb:user>
			<hb:user_id><?php echo absint( $user->ID ); ?></hb:user_id>
			<hb:user_login><?php echo hbip_cdata( $user->user_login ); ?></hb:user_login>
			<hb:user_email><?php echo hbip_cdata( $user->user_email ); ?></hb:user_email>
			<hb:user_pass><?php echo hbip_cdata( $user->user_pass ); ?></hb:user_pass>
			<hb:user_nicename><?php echo hbip_cdata( $user->user_nicename ); ?></hb:user_nicename>
			<hb:user_display_name><?php echo hbip_cdata( $user->display_name ); ?></hb:user_display_name>
			<hb:user_status><?php echo hbip_cdata( $user->first_name ); ?></hb:user_status>
			<hb:user_first_name><?php echo hbip_cdata( $user->first_name ); ?></hb:user_first_name>
			<hb:user_last_name><?php echo hbip_cdata( $user->last_name ); ?></hb:user_last_name>
			<?php $user_metas = hbip_get_user_metas( $user_id ); ?>
			<?php if ( $user_metas ) : foreach ( $user_metas as $meta ) : ?>
			<hb:meta>
				<hb:meta_key><?php echo hbip_cdata( $meta->meta_key ) ?></hb:meta_key>
				<hb:meta_value><?php echo hbip_cdata( $meta->meta_value ) ?></hb:meta_value>
			</hb:meta>
			<?php endforeach;  endif; ?>
		</hb:user>
	<?php endif; endforeach; unset( $user_ids ); endif; ?>
	<!-- end users -->

	<!-- terms -->
	<?php if ( in_array( $args['export'], array( 'all', 'taxonomies' ) ) && $terms = hbip_get_room_taxonomies() ) : foreach ( $terms as $term ) : ?>
		<hb:term>
		<!-- term details -->
		<?php foreach ( $term as $key => $val ) : if ( ! in_array( $key, array( 'count', 'filter' ) ) ) : ?>
			<hb:<?php echo $key ?>><?php echo hbip_cdata( $val ) ?></hb:<?php echo $key ?>>
		<?php endif; endforeach; ?>
		<!-- term meta -->
		<?php if ( $term_metas = hbip_get_term_metas( $term->term_id ) ) : foreach( $term_metas as $meta ) : ?>
		<hb:meta>
			<hb:meta_key><?php echo hbip_cdata( $meta->meta_key ) ?></hb:meta_key>
			<hb:meta_value><?php echo hbip_cdata( $meta->meta_value ) ?></hb:meta_value>
		</hb:meta>
		<?php endforeach; endif; ?>
		<!-- end term meta -->
		</hb:term>
	<?php endforeach; endif; unset( $taxonomies ); ?>
	<!-- end terms -->

	<!-- attachments -->
	<?php if ( in_array( $args['export'], array( 'all', 'rooms' ) ) && $attachments = hbip_get_attachments() ) : foreach ( $attachments as $attachment ) : ?>
		<hb:attachment>
		<?php $upload_dir = wp_upload_dir(); foreach ( $attachment as $k => $v ) : ?>
			<?php if ( $k === 'guid' && strpos( $v, site_url() ) === false && preg_match( '/[0-9]{4}\/[0-9]{2}\/[^.*]+\.[jpeg|jpg|png]*$/i', $v, $match ) ) : ?>
				<?php
					$guid = trailingslashit( $upload_dir['baseurl'] ) . $match[0];
				?>
				<hb:<?php echo $k ?>><?php echo hbip_cdata( $guid ) ?></hb:<?php echo $k ?>>
			<?php else: ?>
				<hb:<?php echo $k ?>><?php echo hbip_cdata( $v ) ?></hb:<?php echo $k ?>>
			<?php endif; ?>
		<?php endforeach; ?>
		<!-- attach meta -->
		<?php if ( $metas = hbip_get_post_metas( $attachment->ID ) ) : foreach ( $metas as $meta ) : ?>
			<hb:meta>
				<hb:meta_key><?php echo hbip_cdata( $meta->meta_key ) ?></hb:meta_key>
				<hb:meta_value><?php echo hbip_cdata( $meta->meta_value ) ?></hb:meta_value>
			</hb:meta>
		<?php endforeach; endif; ?>
		<!-- end attach meta -->
		</hb:attachment>
	<?php endforeach; unset( $attachments ); endif; ?>
	<!-- end attachments -->

	<!-- rooms -->
	<?php if ( in_array( $args['export'], array( 'all', 'rooms' ) ) && $rooms = hbip_get_rooms() ) : foreach ( $rooms as $room ) : ?>
		<hb:room>
		<?php foreach ( $room as $k => $v ) : ?>
			<hb:<?php echo $k ?>><?php echo hbip_cdata( $v ) ?></hb:<?php echo $k ?>>
		<?php endforeach; ?>
		<!-- room meta -->
		<?php if ( $metas = hbip_get_post_metas( $room->ID ) ) : foreach ( $metas as $meta ) : ?>
			<hb:meta>
				<hb:meta_key><?php echo hbip_cdata( $meta->meta_key ) ?></hb:meta_key>
				<hb:meta_value><?php echo hbip_cdata( $meta->meta_value ) ?></hb:meta_value>
			</hb:meta>
		<?php endforeach; endif; ?>
		<!-- end room meta -->
		<!-- room taxonomy -->
		<?php if ( $terms = wp_get_post_terms( $room->ID, array( 'hb_room_type' ) ) ) : foreach ( $terms as $term ) : ?>
			<hb:term><?php echo hbip_cdata( $term->term_id ); ?></hb:term>
		<?php endforeach; endif; ?>
		<!-- end room taxonomy -->

		</hb:room>
	<?php endforeach; endif; unset( $rooms ) ?>
	<!-- end rooms -->

	<!-- extra rooms -->
	<?php if ( in_array( $args['export'], array( 'all', 'packages' ) ) && $extras = hbip_get_extra_rooms() ) : foreach ( $extras as $extra ) : ?>
		<hb:extra>
		<?php foreach ( $extra as $k => $v ) : ?>
			<hb:<?php echo $k ?>><?php echo hbip_cdata( $v ) ?></hb:<?php echo $k ?>>
		<?php endforeach; ?>
		<!-- extra meta -->
		<?php if ( $metas = hbip_get_post_metas( $extra->ID ) ) : foreach ( $metas as $meta ) : ?>
			<hb:meta>
				<hb:meta_key><?php echo hbip_cdata( $meta->meta_key ) ?></hb:meta_key>
				<hb:meta_value><?php echo hbip_cdata( $meta->meta_value ) ?></hb:meta_value>
			</hb:meta>
		<?php endforeach; endif; ?>
		<!-- end extra meta -->
		</hb:extra>
	<?php endforeach; endif; unset( $extras ); ?>
	<!-- end extra rooms -->

	<!-- blocked rooms -->
	<?php if ( in_array( $args['export'], array( 'all', 'blocks' ) ) && $blockeds = hbip_get_blocked_rooms() ) : foreach ( $blockeds as $blocked ) : ?>
		<hb:blocked>
		<?php foreach ( $blocked as $k => $v ) : ?>
			<hb:<?php echo $k ?>><?php echo hbip_cdata( $v ) ?></hb:<?php echo $k ?>>
		<?php endforeach; ?>
		<!-- blocked meta -->
		<?php if ( $metas = hbip_get_post_metas( $blocked->ID ) ) : foreach ( $metas as $meta ) : ?>
			<hb:meta>
				<hb:meta_key><?php echo hbip_cdata( $meta->meta_key ) ?></hb:meta_key>
				<hb:meta_value><?php echo hbip_cdata( $meta->meta_value ) ?></hb:meta_value>
			</hb:meta>
		<?php endforeach; endif; ?>
		<!-- end blocked meta -->
		<!-- end blocked rooms -->
		</hb:blocked>
	<?php endforeach; endif; unset( $blockeds ); ?>
	<!-- end rooms -->

	<!-- bookings -->
	<?php if ( in_array( $args['export'], array( 'all', 'bookings' ) ) && $bookings = hbip_get_books() ) : foreach ( $bookings as $booking ) : ?>
		<!-- loop single booking -->
		<hb:booking>
	<?php foreach ( $booking as $k => $v ) : ?>
		<hb:<?php echo $k ?>><?php echo hbip_cdata( $v ) ?></hb:<?php echo $k ?>>
	<?php endforeach; ?>
		<!-- book meta -->
		<?php if ( $metas = hbip_get_post_metas( $booking->ID ) ) : foreach ( $metas as $meta ) : ?>
			<hb:meta>
				<hb:meta_key><?php echo hbip_cdata( $meta->meta_key ) ?></hb:meta_key>
				<hb:meta_value><?php echo hbip_cdata( $meta->meta_value ) ?></hb:meta_value>
			</hb:meta>
		<?php endforeach; endif; ?>
		<!-- end book meta -->
		</hb:booking>
		<!-- loop single booking -->
	<?php endforeach; endif; unset( $bookings ); ?>
	<!-- end bookings -->

	<!-- coupons -->
	<?php if ( in_array( $args['export'], array( 'all', 'bookings' ) ) && $coupons = hbip_get_coupons() ) : foreach ( $coupons as $coupon ) : ?>
		<!-- loop single coupon -->
		<hb:coupon>
	<?php foreach ( $coupon as $k => $v ) : ?>
		<hb:<?php echo $k ?>><?php echo hbip_cdata( $v ) ?></hb:<?php echo $k ?>>
	<?php endforeach; ?>
		<!-- book meta -->
		<?php if ( $metas = hbip_get_post_metas( $coupon->ID ) ) : foreach ( $metas as $meta ) : ?>
			<hb:meta>
				<hb:meta_key><?php echo hbip_cdata( $meta->meta_key ) ?></hb:meta_key>
				<hb:meta_value><?php echo hbip_cdata( $meta->meta_value ) ?></hb:meta_value>
			</hb:meta>
		<?php endforeach; endif; ?>
		<!-- end book meta -->
		</hb:coupon>
		<!-- loop single coupon -->
	<?php endforeach; endif; unset( $bookings ); ?>
	<!-- end coupons -->

	<!-- order items -->
	<?php if ( in_array( $args['export'], array( 'all', 'bookings' ) ) && $order_items = hbip_get_order_items() ) : foreach ( $order_items as $order_item ) : ?>
		<!-- loop order item -->
		<hb:order>
		<?php foreach ( $order_item as $k => $v ) : ?>
			<hb:<?php echo $k ?>><?php echo hbip_cdata( $v ) ?></hb:<?php echo $k ?>>
		<?php endforeach; ?>
		<?php if ( $order_metas = hbip_get_order_itemmetas( $order_item->order_item_id ) ) : foreach ( $order_metas as $meta ) : ?>
			<hb:meta>
				<hb:meta_key><?php echo hbip_cdata( $meta->meta_key ) ?></hb:meta_key>
				<hb:meta_value><?php echo hbip_cdata( $meta->meta_value ) ?></hb:meta_value>
			</hb:meta>
		<?php endforeach; endif; ?>
		</hb:order>
		<!-- end loop order item -->
	<?php endforeach; endif; unset( $order_items ); ?>
	<!-- end order items -->

	<!-- pricing plan -->
	<?php if ( in_array( $args['export'], array( 'all', 'pricing' ) ) && $pricings = hbip_get_pricings() ) : foreach ( $pricings as $pricing ) : ?>
		<!-- loop pricing item -->
		<hb:pricing>
		<?php foreach ( $pricing as $k => $v ) : ?>
			<hb:<?php echo $k ?>><?php echo hbip_cdata( $v ) ?></hb:<?php echo $k ?>>
		<?php endforeach; ?>
		</hb:pricing>
		<!-- end loop pricing item -->
	<?php endforeach; endif; unset( $pricings ); ?>
	<!-- end pricing plans -->

	<?php do_action( 'hotel_booking_export_footer' ); ?>
	</channel>
</rss>
    <?php
    	echo trim( ob_get_clean() );
	}

}

new HBIP_Exporter();
