<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$users = video_conferencing_zoom_api_get_user_transients();
?>
<div class="wrap">
    <h3><?php _e( "Sync your Live Zoom Meetings to your site", "speed-dating-with-zoom" ); ?></h3>
    <div class="vczapi-notification">
        <p><?php _e( "This allows you to sync your live meetings from your Zoom Account to this site directly. Synced meetings will be inside Zoom Meeting > All Meetings page.", "speed-dating-with-zoom" ); ?></p>
        <p><?php _e( "Currently, you can only sync scheduled meetings.", "speed-dating-with-zoom" ); ?></p>
    </div>

    <div class="vczapi-sync-admin-wrapper">
		<?php if ( !vczapi_pro_version_active() ) { ?>
            <form action="" method="POST">
                <label><?php _e( "Choose a Zoom User", "speed-dating-with-zoom" ); ?></label> : <select class="vczapi-sync-user-id zvc-hacking-select">
                    <option value=""><?php _e( 'Select a User', 'speed-dating-with-zoom' ); ?></option>
					<?php foreach ( $users as $user ) { ?>
                        <option value="<?php echo $user->id; ?>"><?php echo $user->first_name . ' ( ' . $user->email . ' )'; ?></option>
					<?php } ?>
                </select>
            </form>

            <div class="vczapi-status-notification"></div>
            <div class="results"></div>
		<?php } ?>
		<?php do_action( 'vczapi_admin_after_sync_html', $users ); ?>
    </div>
</div>
