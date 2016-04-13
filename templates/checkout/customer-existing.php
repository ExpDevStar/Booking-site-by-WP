<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

?>
<?php if ( ! is_user_logged_in() ) : ?>

    <div class="hb-order-existing-customer" data-label="<?php esc_attr_e( '-Or-', 'tp-hotel-booking' ); ?>">
        <div class="hb-col-padding hb-col-border">
            <h4><?php _e( 'Existing customer?', 'tp-hotel-booking' ); ?></h4>
            <ul class="hb-form-table">
                <li class="hb-form-field">
                    <label class="hb-form-field-label"><?php _e( 'Email', 'tp-hotel-booking' ); ?></label>
                    <div class="hb-form-field-input">
                        <input type="email" name="existing-customer-email" value="<?php echo esc_attr( TP_Hotel_Booking::instance()->cart->customer_email ); ?>" placeholder="<?php _e( 'Your email here', 'tp-hotel-booking' ); ?>" />
                    </div>
                </li>
                <li>
                    <button type="button" id="fetch-customer-info"><?php _e( 'Apply', 'tp-hotel-booking' ); ?></button>
                </li>
            </ul>
        </div>
    </div>

<?php endif; ?>