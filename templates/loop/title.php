<?php
/**
 * Product loop title
 *
 * @author  ThimPress
 * @package Tp-hotel-booking/Templates
 * @version 1.1.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="title">
	<h4>
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h4>
</div>
