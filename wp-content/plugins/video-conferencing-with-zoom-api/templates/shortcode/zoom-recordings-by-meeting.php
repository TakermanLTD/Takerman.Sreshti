<?php
/**
 * The Template for displaying list of recordings via meeting ID
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/zoom-recordings-by-meeting.php.
 *
 * @package     Speed Dating with Zoom/Templates
 * @version     3.5.0
 */

global $zoom_recordings, $zoom_recordings_is_downloadable;
$total_size = 0;
$table_body = '';

foreach ( $zoom_recordings as $zoom_recording ) {
	$total_size += $zoom_recording->total_size;
	ob_start();
	foreach ( $zoom_recording->recording_files as $recording ) {
		if ( $recording->file_type !== "MP4" ) {
			break;
		}
		?>
        <tr>
            <td data-sort="<?php echo strtotime( $recording->recording_start ); ?>"><?php echo vczapi_dateConverter( $recording->recording_start, $zoom_recording->timezone ); ?></td>
            <td data-sort="<?php echo strtotime( $recording->recording_end ); ?>"><?php echo vczapi_dateConverter( $recording->recording_end, $zoom_recording->timezone ); ?></td>
            <td><?php echo vczapi_filesize_converter( $recording->file_size ); ?></td>
            <td>
                <a href="<?php echo $recording->play_url; ?>" target="_blank"><?php _e( 'Play', 'speed-dating-with-zoom' ); ?></a>
				<?php if ( $zoom_recordings_is_downloadable ) { ?>
                    <a href="<?php echo $recording->download_url; ?>" target="_blank"><?php _e( 'Download', 'speed-dating-with-zoom' ); ?></a>
				<?php } ?>
            </td>

        </tr>
		<?php
	}
	$table_body .= ob_get_clean();
}
?>
<div class="vczapi-recordings-meeting-id-description">
    <ul>
        <li><strong><?php _e( 'Meeting ID', 'speed-dating-with-zoom' ); ?>:</strong> <?php echo $zoom_recordings[0]->id; ?></li>
        <li><strong><?php _e( 'Topic', 'speed-dating-with-zoom' ); ?>:</strong> <?php echo $zoom_recordings[0]->topic; ?></li>
        <li><strong><?php _e( 'Total Size', 'speed-dating-with-zoom' ); ?>:</strong> <?php echo vczapi_filesize_converter( $total_size ); ?></li>
        <li>
            <a href="<?php echo add_query_arg( [ 'flush_cache' => 'yes' ], get_the_permalink() ) ?>"><?php _e( 'Check for latest' ); ?></a>
        </li>
    </ul>
</div>
<table id="vczapi-recordings-list-table" class="vczapi-recordings-list-table-meeting-id vczapi-user-meeting-list">
    <thead>
    <tr>
        <th><?php _e( 'Start Date', 'speed-dating-with-zoom' ); ?></th>
        <th><?php _e( 'End Date', 'speed-dating-with-zoom' ); ?></th>
        <th><?php _e( 'Size', 'speed-dating-with-zoom' ); ?></th>
        <th><?php _e( 'Action', 'speed-dating-with-zoom' ); ?></th>
    </tr>
    </thead>
    <tbody>
	<?php
	echo $table_body;
	?>
    </tbody>
</table>