<table id="vczapi-show-meetings-list-table" class="vczapi-user-meeting-list">
    <thead>
    <tr>
        <th><?php _e( 'Topic', 'speed-dating-with-zoom' ); ?></th>
        <th><?php _e( 'Meeting Status', 'speed-dating-with-zoom' ); ?></th>
        <th><?php _e( 'Start Time', 'speed-dating-with-zoom' ); ?></th>
        <th><?php _e( 'Timezone', 'speed-dating-with-zoom' ); ?></th>
        <th><?php _e( 'Actions', 'speed-dating-with-zoom' ); ?></th>
    </tr>
    </thead>
    <tbody>
	<?php
	if ( ! empty( $args ) ) {
		foreach ( $args as $meeting ) {
			$meeting->password = ! empty( $meeting->password ) ? $meeting->password : false;
			$meeting_status    = '';
			if ( ! empty( $meeting->status ) ) {
				switch ( $meeting->status ) {
					case 0;
						$meeting_status = '<img src="' . ZVC_PLUGIN_IMAGES_PATH . '/2.png" style="width:14px;" title="Not Started" alt="Not Started">';
						break;
					case 1;
						$meeting_status = '<img src="' . ZVC_PLUGIN_IMAGES_PATH . '/3.png" style="width:14px;" title="Completed" alt="Completed">';
						break;
					case 2;
						$meeting_status = '<img src="' . ZVC_PLUGIN_IMAGES_PATH . '/1.png" style="width:14px;" title="Currently Live" alt="Live">';
						break;
					default;
						break;
				}
			} else {
				$meeting_status = "N/A";
			}

			echo '<td>' . $meeting->topic . '</td>';
			echo '<td>' . $meeting_status . '</td>';
			echo '<td>' . vczapi_dateConverter( $meeting->start_time, $meeting->timezone, 'F j, Y, g:i a' ) . '</td>';
			echo '<td>' . $meeting->timezone . '</td>';
			echo '<td><div class="view">
<a href="' . $meeting->join_url . '" rel="permalink" target="_blank">' . __( 'Join via App', 'speed-dating-with-zoom' ) . '</a></div><div class="view">' . vczapi_get_browser_join_shortcode( $meeting->id, $meeting->password, false, ' / ' ) . '</div></td>';
			echo '</tr>';
		}
	}
	?>
    </tbody>
</table>