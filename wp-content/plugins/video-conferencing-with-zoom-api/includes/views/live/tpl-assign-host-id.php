<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
    <h2><?php _e( "Assign Zoom Host Users to your WordPress Users", "speed-dating-with-zoom" ); ?></h2>
    <div id="message" class="notice notice-warning">
        <p>
            <strong><?php _e( 'This section allows you to assign "Zoom" host to your users from WordPress. If you add a WordPress user to a Zoom Host from here then at the meeting creation that user will not see list of other host on his/her side except for administrator.', 'speed-dating-with-zoom' ); ?>
                !!!</strong></p>
    </div>
    <div class="message">
		<?php
		$message = self::get_message();
		if ( isset( $message ) && ! empty( $message ) ) {
			echo $message;
		}
		?>
    </div>

    <div class="zvc_listing_table">
        <form action="" method="POST">
			<?php wp_nonce_field( '_zoom_assign_hostid_nonce_action', '_zoom_assign_hostid_nonce' ); ?>
            <table id="vczapi-get-host-users-wp" class="display">
                <thead>
                <tr>
                    <th style="text-align:left;"><?php _e( 'ID', 'speed-dating-with-zoom' ); ?></th>
                    <th style="text-align:left;"><?php _e( 'Email', 'speed-dating-with-zoom' ); ?></th>
                    <th style="text-align:left;"><?php _e( 'Full Name', 'speed-dating-with-zoom' ); ?></th>
                    <th style="text-align:left;"><?php _e( 'Host ID', 'speed-dating-with-zoom' ); ?></th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
            <p class="submit"><input type="submit" name="saving_host_id" class="button button-primary" value="<?php _e( 'Save', 'speed-dating-with-zoom' ); ?>"></p>
        </form>
    </div>
</div>
