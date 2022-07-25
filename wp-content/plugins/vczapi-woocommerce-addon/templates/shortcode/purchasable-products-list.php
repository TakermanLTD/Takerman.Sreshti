<?php
/**
 * Override this template by copying this file to youtheme/zoom-woocommerce-addon/shortcode/purchasable-products-list.php
 *
 * @created on March30, 2020
 * CodeManas
 */

global $zoom_products;
if ( ! empty( $zoom_products ) && ! is_object( $zoom_products ) && ! ( $zoom_products instanceof \WP_Query ) ) {
	return;
}
?>
<div class="vczapi-purchasable-meetings">
    <table class="vczapi-purchasable-meetings--table">
        <thead>
        <tr>
            <td><?php _e( 'Meeting Title', 'vczapi-woocommerce-addon' ); ?></td>
            <td><?php _e( 'Host', 'vczapi-woocommerce-addon' ); ?></td>
            <td><?php _e( 'Start Date', 'vczapi-woocommerce-addon' ); ?></td>
            <td><?php _e( 'Timezone', 'vczapi-woocommerce-addon' ); ?></td>
            <td><?php _e( 'View', 'vczapi-woocommerce-addon' ); ?></td>
        </tr>
        </thead>
		<?php
		if ( $zoom_products->have_posts() ) {
			while ( $zoom_products->have_posts() ):
				$zoom_products->the_post();
				$meeting_id      = get_the_ID();
				$meeting_details = get_post_meta( $meeting_id, '_meeting_fields', true );
				?>
                <tr class="vczapi-purchasable-meetings--table__row">
                    <td><?php the_title(); ?></td>
                    <td><?php echo get_the_author(); ?></td>
                    <td><?php echo date( 'F j, Y @ g:i a', strtotime( $meeting_details['start_date'] ) ); ?></td>
                    <td><?php echo $meeting_details['timezone']; ?></td>
                    <td><a href="<?php echo esc_url( get_the_permalink() ) ?>" class="btn"><?php _e( 'Details', 'vczapi-woocommerce-addon' ); ?></a>
                    </td>
                </tr>
			<?php
			endwhile;
		} else {
			?>
            <tr>
                <td colspan="5"><?php _e( 'No upcoming meetings.', 'vczapi-woocommerce-addon' ); ?></td>
            </tr>
			<?php
		}
		?>
    </table><!--items-->
    <div class="vczapi-purchasable-meetings--pagination">
		<?php $this->pagination( $zoom_products ); ?>
    </div>
	<?php
	wp_reset_postdata();
	?>
</div>
