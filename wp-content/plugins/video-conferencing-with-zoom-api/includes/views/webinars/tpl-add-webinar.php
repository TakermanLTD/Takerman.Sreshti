<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Check if any transient by name is available
$users = video_conferencing_zoom_api_get_user_transients();
?>
<div class="wrap">
    <h1><?php _e( 'Add a Webinar', 'speed-dating-with-zoom' ); ?></h1>
    <div class="message">
		<?php
		$message = self::get_message();
		if ( isset( $message ) && ! empty( $message ) ) {
			echo $message;
		}
		?>
    </div>
	<?php video_conferencing_zoom_api_show_api_notice(); ?>

    <a href="edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-webinars<?php echo isset( $_GET['host_id'] ) ? '&host_id=' . esc_html( $_GET['host_id'] ) : false; ?>"><?php _e( 'Back to selected host Webinars list', 'speed-dating-with-zoom' ); ?></a>

    <form action="" method="POST" class="zvc-meetings-form">
		<?php wp_nonce_field( '_zoom_add_meeting_nonce_action', '_zoom_add_meeting_nonce' ); ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="meetingTopic"><?php _e( 'Webinar Topic *', 'speed-dating-with-zoom' ); ?></label></th>
                <td>
                    <input type="text" name="meetingTopic" size="100" required class="regular-text">
                    <p class="description" id="meetingTopic-description"><?php _e( 'Webinar topic. (Required).', 'speed-dating-with-zoom' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="meetingAgenda"><?php _e( 'Webinar Agenda', 'speed-dating-with-zoom' ); ?></label></th>
                <td>
                    <input type="text" name="agenda" class="regular-text">
                    <p class="description" id="meetingTopic-description"><?php _e( 'Webinar Description.', 'speed-dating-with-zoom' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="userId"><?php _e( 'Webinar Host *', 'speed-dating-with-zoom' ); ?></label></th>
                <td>
                    <select name="userId" required class="zvc-hacking-select">
                        <option value=""><?php _e( 'Select a Host', 'speed-dating-with-zoom' ); ?></option>
						<?php foreach ( $users as $user ): ?>
                            <option value="<?php echo $user->id; ?>" <?php echo isset( $_GET['host_id'] ) && $_GET['host_id'] == $user->id ? 'selected' : null; ?>><?php echo $user->first_name . ' ( ' . $user->email . ' )'; ?></option>
						<?php endforeach; ?>
                    </select>
                    <p class="description" id="userId-description"><?php _e( 'This is host ID for the meeting (Required).', 'speed-dating-with-zoom' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="start_date"><?php _e( 'Start Date/Time *', 'speed-dating-with-zoom' ); ?></label></th>
                <td>
                    <input type="text" name="start_date" id="datetimepicker" required class="regular-text">
                    <p class="description" id="start_date-description"><?php _e( 'Starting Date and Time of the Webinar (Required).', 'speed-dating-with-zoom' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="timezone"><?php _e( 'Timezone', 'speed-dating-with-zoom' ); ?></label></th>
                <td>
					<?php $tzlists = zvc_get_timezone_options(); ?>
                    <select id="timezone" name="timezone" class="zvc-hacking-select">
						<?php foreach ( $tzlists as $k => $tzlist ) { ?>
                            <option value="<?php echo $k; ?>"><?php echo $tzlist; ?></option>
						<?php } ?>
                    </select>
                    <p class="description" id="timezone-description"><?php _e( 'Webinar Timezone', 'speed-dating-with-zoom' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="duration"><?php _e( 'Duration', 'speed-dating-with-zoom' ); ?></label></th>
                <td>
                    <input type="number" name="duration" class="regular-text">
                    <p class="description" id="duration-description"><?php _e( 'Webinar duration (minutes). (optional)', 'speed-dating-with-zoom' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="password"><?php _e( 'Webinar Password', 'speed-dating-with-zoom' ); ?></label></th>
                <td>
                    <input type="text" name="password" class="regular-text" maxlength="10" data-maxlength="9">
                    <p class="description" id="email-description"><?php _e( 'Password to join the meeting. Password may only contain the following characters: [a-z A-Z 0-9]. Max of 10 characters.( Leave blank for auto generate )', 'speed-dating-with-zoom' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="option_host_video"><?php _e( 'Host Video', 'speed-dating-with-zoom' ); ?></label></th>
                <td>
                    <p class="description" id="option_host_video-description">
                        <input type="checkbox" name="option_host_video" value="1" class="regular-text"><?php _e( 'Start video when host join meeting.', 'speed-dating-with-zoom' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="option_panelist_video"><?php _e( 'Panelists Video', 'speed-dating-with-zoom' ); ?></label></th>
                <td>
                    <p class="description" id="option_panelist_video-description">
                        <input type="checkbox" name="option_panelist_video" value="1" class="regular-text"><?php _e( 'Start video when panelists join meeting.', 'speed-dating-with-zoom' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="option_hd_video"><?php _e( 'HD Video', 'speed-dating-with-zoom' ); ?></label></th>
                <td>
                    <p class="description" id="option_hd_video-description">
                        <input type="checkbox" name="option_hd_video" value="1" class="regular-text"><?php _e( 'Defaults to HD video.', 'speed-dating-with-zoom' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="option_auto_recording"><?php _e( 'Auto Recording', 'speed-dating-with-zoom' ); ?></label></th>
                <td>
                    <select id="option_auto_recording" name="option_auto_recording">
                        <option value="none"><?php _e( 'No Recordings', 'speed-dating-with-zoom' ); ?></option>
                        <option value="local"><?php _e( 'Local', 'speed-dating-with-zoom' ); ?></option>
                        <option value="cloud"><?php _e( 'Cloud', 'speed-dating-with-zoom' ); ?></option>
                    </select>
                    <p class="description" id="option_auto_recording_description"><?php _e( 'Set what type of auto recording feature you want to add. Default is none.', 'speed-dating-with-zoom' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="settings_alternative_hosts"><?php _e( 'Alternative Hosts', 'speed-dating-with-zoom' ); ?></label></th>
                <td>
                    <select name="alternative_host_ids[]" multiple class="zvc-hacking-select">
                        <option value=""><?php _e( 'Select a Host', 'speed-dating-with-zoom' ); ?></option>
						<?php foreach ( $users as $user ): ?>
                            <option value="<?php echo $user->id; ?>"><?php echo $user->first_name . ' ( ' . $user->email . ' )'; ?></option>
						<?php endforeach; ?>
                    </select>
                    <p class="description" id="settings_alternative_hosts"><?php _e( 'Alternative hosts IDs. Multiple value separated by comma.', 'speed-dating-with-zoom' ); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" name="create_meeting" class="button button-primary" value="<?php _e( 'Create Webinar', 'speed-dating-with-zoom' ); ?>"></p>
    </form>
</div>