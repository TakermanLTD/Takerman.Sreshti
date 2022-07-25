<?php
// include 'footer.php'
/**
 * The template for displaying product content in the single-meeting.php template
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/single-meetings.php.
 *
 * @author Tanyo Ivanov.
 * @created_on 11/19/19
 * @updated 3.7.1
 */

defined('ABSPATH') || exit;

use Codemanas\ZoomWooCommerceAddon\DataStore;

/**
 * Hook: vczoom_before_single_meeting.
 */
do_action('vczoom_before_single_meeting');

if (post_password_required()) {
	echo get_the_password_form(); // WPCS: XSS ok.

	return;
}

/**
 *  Hook: vczoom_before_content
 */
do_action('vczoom_before_content');
?>
<style>
	.green {
		background-color: lightgreen;
	}

	.red {
		background-color: lightcoral;
	}
</style>
<div class="vczapi-wrap dpn-zvc-single-content-wrapper dpn-zvc-single-content-wrapper-<?php echo get_the_ID(); ?>" id="dpn-zvc-single-content-wrapper-<?php echo get_the_id(); ?>">
	<div class="vczapi-col-8">
		<?php
		/**
		 *  Hook: vczoom_single_content_left
		 *
		 * @video_conference_zoom_featured_image - 10
		 * @video_conference_zoom_main_content - 20
		 */
		do_action('vczoom_single_content_left');

		if (is_user_logged_in()) {
			global $wpdb;
			global $current_user;
			$isAdmin = get_the_author_meta('ID') == $current_user->ID;
			$meetingStartTime = new DateTime(get_post_meta(get_the_ID())["_meeting_start_date"][0]);
			$now = new DateTime(current_time('mysql'), $meetingStartTime->getTimezone());
			$isTimeoutStarted = $now > $meetingStartTime; // TODO: Change the less than sign to greather than sign on release
			$isTimeoutEnded = $meetingStartTime->modify('+1 day') < $now; // TODO: Before release be sure it is working
			$showJoinButton = $meetingStartTime->modify('-30 minutes') < $now; // TODO: Change the less than sign to greather than sign on release

			if (isset($_POST["logged_user_id"]) && !$isTimeoutEnded) {
				foreach ($_POST as $key => $value) {
					if (str_starts_with($key, 'choice_user_')) {
						$choice_user_id = substr($key, 12);
						$isExisting = $wpdb->get_var("
						SELECT count(*) FROM lkd_tak_user_choices WHERE 
						logged_user_id={$_POST['logged_user_id']} AND
						choice_user_id={$choice_user_id} AND
						meeting_id={$_POST['meeting_id']}") != 0;

						if ($isExisting) {
							$wpdb->update('lkd_tak_user_choices', array(
								'logged_user_id' => $_POST['logged_user_id'],
								'choice_user_id' => $choice_user_id,
								'choice_id' => $value,
								'meeting_id' => $_POST['meeting_id'],
								'time' => current_time('mysql')
							), array(
								'logged_user_id' => $_POST['logged_user_id'],
								'choice_user_id' => $choice_user_id,
								'meeting_id' => $_POST['meeting_id']
							));
						} else {
							$wpdb->insert('lkd_tak_user_choices', array(
								'logged_user_id' => $_POST['logged_user_id'],
								'choice_user_id' => $choice_user_id,
								'choice_id' => $value,
								'meeting_id' => $_POST['meeting_id'],
								'time' => current_time('mysql')
							));
						}
					}
				}
			}

			if (
				$isTimeoutStarted &&
				array_key_exists('_vczapi_zoom_product_id', get_post_meta(get_the_ID())) &&
				wc_customer_bought_product('', get_current_user_id(), get_post_meta(get_the_ID())["_vczapi_zoom_product_id"][0])
			) {
				$meeting_id = get_post_meta(get_the_ID())["_vczapi_zoom_product_id"][0]; ?>
				<div class="text-center justify-content-center">
					<h1 class="heading">Гласувай</h1>
					<span class="text">Списък със присъстващи</span>
					<form enctype="multipart/form-data" id="attendees" action="" method="POST">
						<fieldset>
							<input type="hidden" name="logged_user_id" value="<?php echo $current_user->ID ?>" />
							<input type="hidden" name="meeting_id" value="<?php echo $meeting_id ?>" />
							<?php
							$order_ids = DataStore::orders_ids_from_a_product_id($meeting_id);
							foreach ($order_ids as $order_id) {
								$order = wc_get_order($order_id);
								$userId = $order->get_customer_id();
								$choice_user = get_user_by('ID', $userId);

								if (!method_exists($order, 'get_edit_order_url') || $userId == $current_user->ID) {
									continue;
								}

								$theirAnswer = $wpdb->get_row(
									"SELECT choice_id FROM lkd_tak_user_choices 
									WHERE meeting_id={$meeting_id}
									AND choice_user_id = {$userId}"
								);

								$theirAnswerVerbal = "Приятел";

								if ($theirAnswer != null && $theirAnswer != "") {
									if ($theirAnswer->choice_id == '1') $theirAnswerVerbal = "Да";
									if ($theirAnswer->choice_id == '2') $theirAnswerVerbal = "Не";
									if ($theirAnswer->choice_id == '3') $theirAnswerVerbal = "Приятел";
								}


								$mineAnswer = $wpdb->get_row(
									"SELECT choice_id FROM lkd_tak_user_choices 
									WHERE meeting_id={$meeting_id}
									AND choice_user_id = {$current_user->ID}"
								);

								$isAMatch = false;
								if ($theirAnswer && $mineAnswer) {
									$isAMatch = $theirAnswer->choice_id == '1' && $mineAnswer->choice_id == '1';
								}
							?>
								<table class="table table-stripped text-center <?php echo $isAMatch && $isTimeoutEnded ? "green" : "" ?>">
									<tr>
										<td>
											<a href="http://sreshti/account-2/" target="_blank">
												<img width="100" height="100" class="image img img-thumbnail" src="<?php echo get_avatar_url($current_user->ID); ?>" />
											</a>
											<br />
											Аз
										</td>
										<td>
											<?php
											$choice_types = $wpdb->get_results("SELECT id, value FROM {$wpdb->prefix}tak_choice_types");

											foreach ($choice_types as $choice_type) {
												$choice_user_id = "choice_user_" . $userId;
												$is_checked = false;

												$db_choice_users = $wpdb->get_results(
													"SELECT * from lkd_tak_user_choices 
													WHERE logged_user_id = {$current_user->ID} 
													AND meeting_id={$meeting_id}
													AND choice_user_id = {$userId}"
												);

												if (count($db_choice_users) == 0 && $choice_type->id == 3) {
													$is_checked = true;
												} else {
													foreach ($db_choice_users as $db_choice_user) {
														$is_checked = $db_choice_user->choice_id == $choice_type->id;
													}
												}

												$mineAnswerVerbal = 'Приятел';

												if ($choice_type->value != null && $choice_type->value != "") {
													if ($choice_type->id == '1') $mineAnswerVerbal = "Да";
													if ($choice_type->id == '2') $mineAnswerVerbal = "Не";
													if ($choice_type->id == '3') $mineAnswerVerbal = "Приятел";
												}
											?>
												<label>
													<input type="radio" <?php if ($isTimeoutEnded || $isAdmin) {
																			echo "disabled";
																		} ?> <?php if ($is_checked) {
																					echo "checked";
																				} ?> value="<?php echo $choice_type->id ?>" name="<?php echo $choice_user_id ?>" />
													<?php echo $mineAnswerVerbal ?>
												</label>
											<?php } ?>
										</td>
										<td>
											<?php
											um_fetch_user($userId);

											if ($isTimeoutEnded) {
												echo "<label><input disabled checked type='radio' />{$theirAnswerVerbal}</label>";

												if ($isAMatch) {
													// $subject = urlencode('Здравей');
													// $message = urlencode('');
													$link = BP_Better_Messages()->functions->get_link() . '?new-message&to=' . um_user('nickname');

													echo "<br/><a href='{$link}' target='_blank'><button type='button' class='btn btn-primary'><i class='fa fa-commenting'></i></button></a>";

													$facebook = um_user('facebook');
													if ($facebook) {
														echo "<a href='{$facebook}' target='_blank'><button type='button' class='btn btn-primary'><i class='fa fa-facebook-official'></i></button></a>";
													}

													$instagram = um_user('instagram');
													if ($instagram) {
														echo "<a href='{$instagram}' target='_blank'><button type='button' class='btn btn-primary'><i class='fa fa-instagram'></i></button></a>";
													}

													if (um_user('billing_phone')) {
														echo "<br><a href='tel:" . um_user('billing_phone') . "'>" . um_user('billing_phone') . "</a>";
													}
												}
											}
											?>
										</td>
										<td>
											<a <?php echo $isAMatch || $isAdmin ? "href='/user/{$userId}'" : get_avatar_url($userId) ?> target="_blank">
												<img width="100" height="100" class="image img img-thumbnail" src="<?php echo get_avatar_url($userId); ?>" />
												<br />
												<?php echo um_user('display_name'); ?>
											</a>
										</td>
									</tr>
								</table>
							<?php } ?>
						</fieldset>
						<button <?php if ($isTimeoutEnded) echo "disabled" ?> class="btn btn-primary text-center" type="submit">Запази</button>
					</form>
				</div>
			<?php } else if ($showJoinButton) {
				$meeting_join_url = get_post_meta(get_the_ID())["_meeting_zoom_join_url"][0];
			?>
				<div class="text-center justify-content-center">
					<a href="<?php echo $meeting_join_url ?>" target="_blank">
						<button class="btn btn-lg btn-primary">ВЛЕЗ В СРЕЩАТА</button>
					</a>
				</div>
			<?php } else { ?>
				<p class="text-center">Ако сте закупили вход ще можете да се включите <strong>30 минути</strong> преди срещата.</span>
			<?php }
		} ?>
	</div>
	<div class="vczapi-col-4">
		<div class="dpn-zvc-sidebar-wrapper">
			<?php
			/**
			 *  Hook: vczoom_single_content_left
			 *
			 * @video_conference_zoom_featured_image - 10
			 * @video_conference_zoom_main_content - 20
			 */
			do_action('vczoom_single_content_left');
			?>
		</div>
		<div class="dpn-zvc-sidebar-wrapper">
			<div class="dpn-zvc-sidebar-wrapper">
				<?php
				/**
				 *  Hook: vczoom_single_content_right
				 *
				 * @video_conference_zoom_countdown_timer - 10
				 * @video_conference_zoom_meeting_details - 20
				 * @video_conference_zoom_meeting_join - 30
				 *
				 */
				do_action('vczoom_single_content_right');
				?>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>

	<?php
	/**
	 *  Hook: vczoom_after_content
	 */
	do_action('vczoom_after_content');

	/**
	 * Hook: video_conference_zoom_before_single_meeting.
	 */
	do_action('video_conference_zoom_after_single_meeting');
	?>