<?php

/**
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/fragments/image.php.
 *
 * @author      Deepen Bajracharya (CodeManas)
 * @created     3.0.0
 */
?>

<div class="deepn-zvc-single-featured-img">
	<?php
	$zoom_details = get_post_meta(get_the_id(), '_meeting_zoom_details', true);
	$meeting_time_start = new DateTime(get_post_meta(get_the_id())['_meeting_start_date'][0]);
	$isTimeoutEnded = $meeting_time_start->modify('+1 day') > new DateTime(current_time('mysql'), $meeting_time_start->getTimezone()); // TODO: Before release be sure it is working

	if ($isTimeoutEnded) {
		echo do_shortcode('[zoom_join_via_browser meeting_id="' . $zoom_details->id . '" title="' . $zoom_details->topic . '" login_required="no" help="yes" height="478px" disable_countdown="yes" passcode="' . $zoom_details->password . '" webinar="no"]');
	} else {
		the_post_thumbnail();
	}
	?>
</div>