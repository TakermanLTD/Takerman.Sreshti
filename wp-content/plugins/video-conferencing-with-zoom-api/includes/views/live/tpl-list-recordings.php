<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$host_id = isset( $_GET['host_id'] ) ? $_GET['host_id'] : null;
?>
<div class="wrap">
    <h2><?php _e( "Recordings", "speed-dating-with-zoom" ); ?></h2>
    <p style="padding: 20px;" class="vczapi-notification vczapi-error"><strong><?php _e( "The maximum range can be a month. If no value is provided for this field, the default will be current date. For example, if you make the API request on June 30, 2020, without providing the “from” parameter, by default the value of ‘from’ field will be “2020-05-30” and the value of the ‘to’ field will be “2020-06-30”.", "speed-dating-with-zoom" ); ?></strong></p>
	<?php
	video_conferencing_zoom_api_show_like_popup();
	zvc_recordings()->get_hosts( $host_id, true );
	?>
    <div class="zvc_listing_table">
        <table id="zvc_meetings_list_table" class="display" width="100%">
            <thead>
            <tr>
                <th class="zvc-text-left"><?php _e( 'Meeting ID', 'speed-dating-with-zoom' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Topic', 'speed-dating-with-zoom' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Duration', 'speed-dating-with-zoom' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Recorded', 'speed-dating-with-zoom' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Size', 'speed-dating-with-zoom' ); ?></th>
                <th class="zvc-text-left"><?php _e( 'Action', 'speed-dating-with-zoom' ); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php
			if ( ! empty( $recordings ) && ! empty( $recordings->meetings ) ) {
				foreach ( $recordings->meetings as $recording ) {
					?>
                    <tr>
                        <td><?php echo $recording->id; ?></td>
                        <td><?php echo $recording->topic; ?></td>
                        <td><?php echo $recording->duration; ?></td>
                        <td><?php echo date( 'F j, Y, g:i a', strtotime( $recording->start_time ) ); ?></td>
                        <td><?php echo vczapi_filesize_converter( $recording->total_size ); ?></td>
                        <td>
							<?php if ( ! empty( $recording->recording_files ) ) { ?>
                                <a href="#TB_inline?width=600&height=550&inlineId=recording-<?php echo $recording->id; ?>" class="thickbox">View
                                    Recordings</a>
                                <div id="recording-<?php echo $recording->id; ?>" style="display:none;">
									<?php foreach ( $recording->recording_files as $files ) { ?>
                                        <ul class="zvc-inside-table-wrapper zvc-inside-table-wrapper-<?php echo $files->id; ?>">
                                            <li><strong><?php _e( 'File Type', 'speed-dating-with-zoom' ); ?>
                                                    :</strong> <?php echo $files->file_type; ?></li>
                                            <li><strong><?php _e( 'File Size', 'speed-dating-with-zoom' ); ?>
                                                    :</strong> <?php echo vczapi_filesize_converter( $files->file_size ); ?></li>
                                            <li><strong><?php _e( 'Play', 'speed-dating-with-zoom' ); ?>:</strong>
                                                <a href="<?php echo $files->play_url; ?>" target="_blank"><?php _e( 'Play', 'speed-dating-with-zoom' ); ?></a>
                                            </li>
                                            <li><strong><?php _e( 'Download', 'speed-dating-with-zoom' ); ?>:</strong>
                                                <a href="<?php echo $files->download_url; ?>" target="_blank"><?php _e( 'Download', 'speed-dating-with-zoom' ); ?></a>
                                            </li>
                                        </ul>
									<?php } ?>
                                </div>
							<?php } else {
								echo "N/A";
							} ?>
                        </td>
                    </tr>
					<?php
				}
			}
			?>
            </tbody>
        </table>
    </div>
</div>
