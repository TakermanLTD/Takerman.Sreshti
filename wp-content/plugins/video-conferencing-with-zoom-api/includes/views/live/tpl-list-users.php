<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

video_conferencing_zoom_api_show_like_popup();

$users = video_conferencing_zoom_api_get_user_transients();
?>
<div class="wrap">
    <h2><?php _e( "Users", "speed-dating-with-zoom" ); ?></h2>
    <a href="?post_type=zoom-meetings&page=zoom-video-conferencing-list-users&flush=true"><?php _e( 'Flush User Cache', 'speed-dating-with-zoom' ); ?></a>
     / <a href="?post_type=zoom-meetings&page=zoom-video-conferencing-list-users&status=pending"><?php _e( 'Check Pending Users', 'speed-dating-with-zoom' ); ?></a>
    <div class="message">
		<?php
		$message = self::get_message();
		if ( isset( $message ) && ! empty( $message ) ) {
			echo $message;
		}
		?>
    </div>
    <p><?php echo video_conferencing_zoom_api_pagination_next( $users ) . ' ' . video_conferencing_zoom_api_pagination_prev( $users ); ?></p>

    <div class="zvc_listing_table">
        <table id="zvc_users_list_table" class="display" width="100%">
            <thead>
            <tr>
                <th class="zvc-text-left"><?php _e( 'SN', 'speed-dating-with-zoom' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'User ID', 'speed-dating-with-zoom' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Email', 'speed-dating-with-zoom' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Name', 'speed-dating-with-zoom' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Created On', 'speed-dating-with-zoom' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Last Login', 'speed-dating-with-zoom' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Last Client', 'speed-dating-with-zoom' ); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php
			$count = 1;
			if ( ! empty( $users ) ) {
				foreach ( $users as $user ) {
					?>
                    <tr>
                        <td><?php echo $count ++; ?></td>
                        <td><?php echo $user->id; ?></td>
                        <td><?php echo $user->email; ?></td>
                        <td><?php echo $user->first_name . ' ' . $user->last_name; ?></td>
                        <td><?php echo ! empty( $user->created_at ) ? date( 'F j, Y, g:i a', strtotime( $user->created_at ) ) : "N/A"; ?></td>
                        <div id="zvc_getting_user_info" style="display:none;">
                            <div class="zvc_getting_user_info_content"></div>
                        </div>
                        <td><?php echo ! empty( $user->last_login_time ) ? date( 'F j, Y, g:i a', strtotime( $user->last_login_time ) ) : "N/A"; ?></td>
                        <td><?php echo ! empty( $user->last_client_version ) ? $user->last_client_version : "N/A"; ?></td>
                    </tr>
					<?php
				}
			}
			?>
            </tbody>
        </table>
    </div>
</div>
