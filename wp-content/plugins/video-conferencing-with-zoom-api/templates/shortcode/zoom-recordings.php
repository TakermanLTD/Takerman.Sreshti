<?php
/**
 * The Template for displaying list of recordings via Host ID
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/zoom-recordings.php.
 *
 * @package    Speed Dating with Zoom/Templates
 * @version     3.5.0
 */

global $zoom_recordings;
?>
    <div class="vczapi-recordings-range-selector-wrap">
        <form action="" class="vczapi-recording-range-selector" method="GET">
            <label><?php _e( 'Select a month to filter:', 'speed-dating-with-zoom' ); ?></label>
            <input type="text" name="date" id="vczapi-check-recording-date" class="vczapi-check-recording-date" value="<?php echo isset( $_GET['date'] ) ? esc_html( $_GET['date'] ) : date( 'F Y' ); ?>"/> <input type="submit" name="fetch_recordings" value="<?php _e( 'Check', 'speed-dating-with-zoom' ); ?>">
        </form>
    </div>
    <table id="vczapi-recordings-list-table" class="vczapi-recordings-list-table">
        <thead>
        <tr>
            <th><?php _e( 'Meeting ID', 'speed-dating-with-zoom' ); ?></th>
            <th><?php _e( 'Topic', 'speed-dating-with-zoom' ); ?></th>
            <th><?php _e( 'Duration', 'speed-dating-with-zoom' ); ?></th>
            <th><?php _e( 'Recorded', 'speed-dating-with-zoom' ); ?></th>
            <th><?php _e( 'Size', 'speed-dating-with-zoom' ); ?></th>
            <th><?php _e( 'Action', 'speed-dating-with-zoom' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php
		$use_meeting_id = apply_filters( 'vczapi_zoom_recordings_shortcodeby_meeting_id', false );
		foreach ( $zoom_recordings->meetings as $recording ) {
			if ( $use_meeting_id ) {
				$recording_uuid = $recording->id;
			} else {
				$recording_uuid = urlencode( $recording->uuid );
			}
			?>
            <tr>
                <td><?php echo $recording->id; ?></td>
                <td><?php echo $recording->topic; ?></td>
                <td><?php echo $recording->duration; ?></td>
                <td data-sort="<?php echo strtotime( $recording->start_time ); ?>"><?php echo vczapi_dateConverter( $recording->start_time, $recording->timezone ); ?></td>
                <td><?php echo vczapi_filesize_converter( $recording->total_size ); ?></td>
                <td>
                    <a href="javascript:void(0);" class="vczapi-view-recording" data-recording-id="<?php echo $recording_uuid; ?>"><?php _e( 'View Recordings', 'speed-dating-with-zoom' ); ?></a>
                    <div class="vczapi-modal"></div>
                </td>
            </tr>
			<?php
		}
		?>
        </tbody>
    </table>

<?php
if ( ! empty( $zoom_recordings ) ) {
	vczapi_zoom_api_paginator( $zoom_recordings, 'recordings' );
}
?>