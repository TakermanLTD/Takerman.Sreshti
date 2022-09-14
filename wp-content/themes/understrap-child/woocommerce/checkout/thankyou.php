<?php

/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.7.0
 */

defined('ABSPATH') || exit;
?>

<div class="woocommerce-order">

	<?php
	if ($order) :
		global $wpdb;
		global $current_user_id;
		um_fetch_user($current_user_id);

		foreach ($order->get_items() as $item_id => $item) {
			$zoom_meeting_id = get_post_meta($item->get_product_id(), '_vczapi_zoom_post_id', true);
			$zoom_meeting = get_post_meta($zoom_meeting_id);
			$gender = um_user('gender');
			$gender_val = $zoom_meeting[$gender][0];
			$wpdb->update(
				'lkd_postmeta',
				array('meta_value' => $gender_val - 1),
				array(
					'post_id' => $zoom_meeting_id,
					'meta_key' => $gender
				)
			);
		}

		do_action('woocommerce_before_thankyou', $order->get_id());
	?>

		<?php if ($order->has_status('failed')) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e('За съжаление поръчката ви не може да бъде продължена, тъй като банката отказа транзакцията. Моля опитайте отново.', 'woocommerce'); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay"><?php esc_html_e('Купи', 'woocommerce'); ?></a>
				<?php if (is_user_logged_in()) : ?>
					<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="button pay"><?php esc_html_e('Акаунт', 'woocommerce'); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters('woocommerce_thankyou_order_received_text', esc_html__('Благодаря ви! Поръчката ви е приета.', 'woocommerce'), $order); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																											?></p>

			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

				<li class="woocommerce-order-overview__order order">
					<?php esc_html_e('Номер на поръчка:', 'woocommerce'); ?>
					<strong><?php echo $order->get_order_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
							?></strong>
				</li>

				<li class="woocommerce-order-overview__date date">
					<?php esc_html_e('Дата:', 'woocommerce'); ?>
					<strong><?php echo wc_format_datetime($order->get_date_created()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
							?></strong>
				</li>

				<?php if (is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email()) : ?>
					<li class="woocommerce-order-overview__email email">
						<?php esc_html_e('Имейл:', 'woocommerce'); ?>
						<strong><?php echo $order->get_billing_email(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
								?></strong>
					</li>
				<?php endif; ?>

				<li class="woocommerce-order-overview__total total">
					<?php esc_html_e('Всичко:', 'woocommerce'); ?>
					<strong><?php echo $order->get_formatted_order_total(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
							?></strong>
				</li>

				<?php if ($order->get_payment_method_title()) : ?>
					<li class="woocommerce-order-overview__payment-method method">
						<?php esc_html_e('Начин на плащане:', 'woocommerce'); ?>
						<strong><?php echo wp_kses_post($order->get_payment_method_title()); ?></strong>
					</li>
				<?php endif; ?>

			</ul>

		<?php endif; ?>

		<?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>

		<div class="woocommerce-order-overview__payment-method method text-center">
			<strong>
				<a href="http://www.google.com/calendar/render?
						action=TEMPLATE
						&text=Онлайн среща
						&dates=<?php echo date_format(date_create($zoom_meeting["_meeting_start_date"][0]), "Ymd\\THi00\\Z") ?>/<?php echo date_format(date_create($zoom_meeting["_meeting_start_date"][0])->add(new DateInterval("PT1H")), "Ymd\\THi00\\Z") ?>
						&details=Можете да се влезете в срещата от тук: <?php echo $zoom_meeting["_meeting_zoom_join_url"][0] ?>
						&location=<?php echo $zoom_meeting["_meeting_zoom_join_url"][0] ?>
						&trp=false					
						&sprop=
						&sprop=name:" target="_blank" rel="nofollow">Google <i class="fa fa-calendar" aria-hidden="true"></i></a>
		</div>

		<?php do_action('woocommerce_thankyou', $order->get_id()); ?>

	<?php else : ?>
		<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
			<?php
			echo apply_filters('woocommerce_thankyou_order_received_text', esc_html__('Благодарим ви! Поръчката е приета.', 'woocommerce'), null); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			?>
		</p>
	<?php endif; ?>

</div>