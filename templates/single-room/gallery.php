<?php
/**
 * The Template for displaying all single products.
 *
 * Override this template by copying it to yourtheme/tp-hotel-booking/templates/single-product.php
 *
 * @author 		ThimPress
 * @package 	tp-hotel-booking/templates
 * @version     1.1.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $hb_room;
$galeries = $hb_room->get_galleries( false );
?>

<?php if( $galeries ): ?>
	<div class="hb_room_gallery camera_wrap camera_emboss" id="camera_wrap_<?php the_ID() ?>">
		<?php foreach ( $galeries as $key => $gallery ): ?>
		    <div data-thumb="<?php echo esc_url( $gallery['thumb'] ); ?>" data-src="<?php echo esc_url($gallery['src']); ?>"></div>
		<?php endforeach; ?>
	</div>

	<script type="text/javascript">
		(function($){
			"use strict";
			$(document).ready(function(){
				$('#camera_wrap_<?php the_ID() ?>').camera({
					height: '470px',
					loader: 'none',
					pagination: false,
					thumbnails: true
				});
			});
		})(jQuery);
	</script>
<?php endif; ?>