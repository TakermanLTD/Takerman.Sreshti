<?php
/**
 * The template for displaying shortcode join links
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/join-links.php
 *
 * @author Deepen Bajracharya
 * @created_on 02/19/2020
 * @since 3.1.2
 * @modified 3.3.1
 */

global $meetings;

if ( ! empty( $meetings['join_uri'] ) ) {
	?>
    <tr>
        <td><?php _e( 'Влез чрез Zoom', 'speed-dating-with-zoom' ); ?></td>
        <td>
            <a class="btn-join-link-shortcode" target="_blank" href="<?php echo esc_url( $meetings['join_uri'] ); ?>" title="Влез чрез Zoom"><?php _e( 'Join', 'speed-dating-with-zoom' ); ?></a>
        </td>
    </tr>
<?php } ?>

<?php if ( ! empty( $meetings['browser_url'] ) ) { ?>
    <tr>
        <td><?php _e( 'Влез чрез браузър', 'speed-dating-with-zoom' ); ?></td>
        <td>
            <a class="btn-join-link-shortcode" target="_blank" href="<?php echo esc_url( $meetings['browser_url'] ); ?>" title="Влез чрез браузър"><?php _e( 'Join', 'speed-dating-with-zoom' ); ?></a>
        </td>
    </tr>
<?php } ?>