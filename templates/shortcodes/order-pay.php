<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

?>
<?php $book = HB_Booking::instance( $booking_id ); ?>

<?php $currency_symbol = hb_get_currency_symbol( $book->currency ); ?>

<?php if ( $book->get_status() !== 'completed' ): ?>
	<?php do_action( 'hotel_booking_order_pay_before' ); ?>
	<h3><?php printf( __( 'Booking ID: %s', 'tp-hotel-booking' ), hb_format_order_number($booking_id) ) ?></h3>
	<p>
		<strong><?php _e('Payment status: ') ?></strong>
		<?php printf( '%s', ucfirst( $book->get_status() ) ) ?>
	</p>
	<p>
		<strong><?php _e('Booking Date: ') ?></strong>
		<?php printf( '%s', get_the_date( '', $book->id ) ) ?>
	</p>
	<p>
		<strong><?php _e( 'Payment Method: ', 'tp-hotel-booking' ) ?></strong>
		<?php printf( '%s', $book->method_title ) ?>
	</p>
	<p>
		<strong><?php _e( 'Total: ', 'tp-hotel-booking' ) ?></strong>
		<?php printf( '%s', hb_format_price( hb_booking_total( $booking_id ), $currency_symbol ) ) ?>
	</p>
	<p>
		<strong><?php _e( 'Advance Payment: ', 'tp-hotel-booking' ) ?></strong>
		<?php printf( '%s', hb_format_price( $book->advance_payment, $currency_symbol ) ) ?>
	</p>
	<?php do_action( 'hotel_booking_order_pay_after' ); ?>
<!--_hb_advance_payment-->
<?php else: ?>

	<h3><?php printf( __('%s was pay completed', 'tp-hotel-booking'), $book->get_booking_number() ) ?></h3>

<?php endif; ?>