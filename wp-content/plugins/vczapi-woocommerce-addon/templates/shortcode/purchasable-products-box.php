<?php
global $zoom_products;
if ( ! empty( $zoom_products ) && ! is_object( $zoom_products ) && ! ( $zoom_products instanceof \WP_Query ) ) {
	return;
}
?>
<div class="vczapi-purchasable-meetings">
    <div class="vczapi-purchasable-meetings--items">
		<?php
		if ( $zoom_products->have_posts() ) {
			while ( $zoom_products->have_posts() ):
				$zoom_products->the_post();
				$meeting_id      = get_the_ID();
				$meeting_details = get_post_meta( $meeting_id, '_meeting_fields', true );
				?>

                <div class="vczapi-purchasable-meetings--item">
                    <div class="vczapi-purchasable-meetings--item__image">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail();
						} else {
							echo '<img src="' . VZAPI_WOOCOMMERCE_ADDON_DIR_URI . '/assets/images/zoom-placeholder.png" alt="Placeholder Image">';
						}
						?>
                    </div><!--vczapi-purchasable-meetings--item__image-->
                    <div class="vczapi-purchasable-meetings--item__details">
                        <h2><?php the_title(); ?></h2>
                        <div class="vczapi-purchasable-meetings--item__details__meta">
                            <div class="hosted-by meta">
                                <strong>Hosted By:</strong> <span><?php echo get_the_author(); ?></span>
                            </div>
                            <div class="start-date meta">
                                <strong><?php _e( 'Start', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                                <span><?php echo date( 'F j, Y @ g:i a', strtotime( $meeting_details['start_date'] ) ); ?>
                            </div>
                            <div class="timezone meta">
                                <strong><?php _e( 'Start', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                                <span><?php echo $meeting_details['timezone']; ?>
                            </div>
                        </div>
                        <a href="<?php echo esc_url( get_the_permalink() ) ?>" class="btn">See More</a>
                    </div><!--vczapi-purchasable-meetings--item__details-->
                </div><!--vczapi-purchasable-meetings--item-->
			<?php
			endwhile;
		} else {
			?>
            <p><?php _e( 'No upcoming meetings.', 'vczapi-woocommerce-addon' ); ?></p>
			<?php
		}
		?>
    </div><!--items-->
    <div class="vczapi-purchasable-meetings--pagination">
		<?php $this->pagination( $zoom_products ); ?>
    </div>
	<?php
	wp_reset_postdata();
	?>
</div><!--vczapi-purchasable-meetings-->