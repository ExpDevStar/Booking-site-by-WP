<?php
$settings = HB_Settings::instance();
$stripe = $settings->get('offline-payment');
$stripe = wp_parse_args(
    $stripe,
    array(
        'enable'    => 'off'
    )
);

$field_name = $settings->get_field_name('offline-payment');
?>
<table class="form-table">
    <tr>
        <th><?php _e( 'Enable', 'tp-hotel-booking' );?></th>
        <td>
            <input type="hidden" name="<?php echo $field_name;?>[enable]" value="off" />
            <input type="checkbox" name="<?php echo $field_name;?>[enable]" <?php checked( $stripe['enable'] == 'on' ? 1 : 0, 1 );?> value="on" />
        </td>
    </tr>
</table>