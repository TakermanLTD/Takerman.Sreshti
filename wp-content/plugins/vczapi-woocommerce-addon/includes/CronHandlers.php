<?php

namespace Codemanas\ZoomWooCommerceAddon;

use Codemanas\ZoomWooCommerceAddon\DataStore as Datastore;

/**
 * Class CronHandlers
 *
 * Handle Cron Events here for Custom triggers
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.1.0
 * @package Codemanas\ZoomWooCommerceAddon
 */
class CronHandlers {

	private static $cron_index = 'hourly';
	public $interval = 60 * 60; //1 hour

	public function __construct() {
		add_action( 'vczapi_meeting_events_cron', array( $this, 'execute_cron' ) );
	}

	/**
	 * Execute cron script
	 *
	 * @throws \Exception
	 */
	public function execute_cron() {
		$orders = $this->get_unexpired_products_with_customer_order_details();
		if ( empty( $orders ) ) {
			return;
		}

		//Hook after cron is ran
		do_action( 'vczapi_before_cron_run', $orders );
		$email_settings = get_option( 'vczapi_meeting_reminder_email_settings' );
		//default needs to be 24 hours
		$email_settings = ! empty( $email_settings ) ? $email_settings : [ 'disable_reminder' => false, 'email_schedule' => [ '24_hours_before' ] ];
		$is_disabled    = ( $email_settings['disable_reminder'] == 'on' ) ? true : false;
		if ( ! $is_disabled ) {
			$debug_log_data = [];
			foreach ( $orders as $product_id => $order ) {
				$current_time = new \DateTime( 'now', new \DateTimeZone( $order['timezone'] ) );
				$already_sent = get_post_meta( $product_id, '_vczapi_cron_run_order_one_day', true );
				$already_sent = ! empty( $already_sent ) ? $already_sent : [];

				$start_date = new \DateTime( $order['start_date'], new \DateTimeZone( $order['timezone'] ) );

				/**
				 * @todo verify that this works as intended if filters are applied
				 *  e.g. 1_hour_before = -1 hour  should send email 1 hour before meeting
				 */
				$when_to_send_notifications = apply_filters( 'vczapi_wc_reminder_notification_time',
					[
						'24_hours_before' => [
							'time_to_check' => '-1 day',
							'mail_template' => 'per_day'
						],
						'3_hours_before'  => [
							'time_to_check' => '-3 hours',
							'mail_template' => '3_hours'
						],
					]
				);

				if ( ! empty( $email_settings['email_schedule'] ) ) {
					foreach ( $email_settings['email_schedule'] as $notification_schedule_key ) {

						$send_notification = $when_to_send_notifications[ $notification_schedule_key ];

						$send_notification_time = new \DateTime( $order['start_date'] . $send_notification['time_to_check'], new \DateTimeZone( $order['timezone'] ) );

						if ( ! isset( $already_sent[ $notification_schedule_key ] ) && $start_date >= $current_time && $send_notification_time <= $current_time ) {
							$already_sent[ $notification_schedule_key ] = true;
							$this->send_email( $order, $send_notification['mail_template'] );
							do_action( 'vczapi_wc_on_reminder_sent_' . $notification_schedule_key, $order );
							update_post_meta( $product_id, '_vczapi_cron_run_order_one_day', $already_sent );
							if ( isset( $email_settings['enable_log'] ) && ! empty( $email_settings['enable_log'] ) ) {
								$date                                   = date( 'Y-m-d h:m a', strtotime( 'now' ) );
								$meeting_id                             = get_post_meta( $product_id, '_vczapi_zoom_post_id', true );
								$debug_log_data[ $meeting_id ][ $date ] = $order['customer_details'];

							}
						}
					}
				}
				if ( isset( $email_settings['enable_log'] ) && ! empty( $email_settings['enable_log'] ) ) {
					$uploads_folder = wp_get_upload_dir();

					if ( ! is_dir( $uploads_folder['basedir'] . '/vczapi-wc-logs/' ) ) {
						//Create our directory if it does not exist
						mkdir( $uploads_folder['basedir'] . '/vczapi-wc-logs/' );
						file_put_contents( $uploads_folder['basedir'] . '/vczapi-wc-logs/.htaccess', 'deny from all' );
						file_put_contents( $uploads_folder['basedir'] . '/vczapi-wc-logs/index.html', '' );
					}

					if ( ! empty( $debug_log_data ) ) {
						file_put_contents( $uploads_folder['basedir'] . '/vczapi-wc-logs/reminder-email.txt', var_export( $debug_log_data, true ), FILE_APPEND );
					}

				}
			}

		}

		//Hook after cron is ran
		do_action( 'vczapi_after_cron_run', $orders );
	}

	/**
	 * Add cron when plugin is activated
	 */
	public static function activate_cron() {
		if ( ! wp_next_scheduled( 'vczapi_meeting_events_cron' ) ) {
			wp_schedule_event( time(), self::$cron_index, 'vczapi_meeting_events_cron' );
		}
	}

	/**
	 * Remove crons on plugin deactivation
	 */
	public static function deactivate_cron() {
		$timestamp = wp_next_scheduled( 'vczapi_meeting_events_cron' );
		wp_unschedule_event( $timestamp, 'vczapi_meeting_events_cron' );
	}

	/**
	 * Get list of orders
	 */
	public function get_unexpired_products_with_customer_order_details() {

		$dateTimeZoneUTC = new \DateTimeZone( 'UTC' );
		$time_now_utc    = new \DateTime( 'now', $dateTimeZoneUTC );
		//to get all future meetings at least upto 2 dayys into the future
		//this will limit all the retrieved posts
		$future_time = new \DateTime( '+2 day', $dateTimeZoneUTC );

		$meta_query = [
			'relation' => 'AND',
			[
				'key'     => '_meeting_field_start_date_utc',
				'value'   => $time_now_utc->format( 'Y-m-d H:i:s' ),
				'compare' => '>=',
				'type'    => 'DATETIME'
			],
			[
				'key'     => '_meeting_field_start_date_utc',
				'value'   => $future_time->format( 'Y-m-d H:i:s' ),
				'compare' => '<=',
				'type'    => 'DATETIME'
			],
			[
				'key'     => '_meeting_fields_woocommerce',
				'value'   => sprintf( '"enable_woocommerce";s:2:"%s";', 'on' ),
				'compare' => 'LIKE'
			],
		];

		//Get All meetings which are 3 days early
		$data     = array();
		$meetings = Datastore::get_all_meetings( $meta_query );
		foreach ( $meetings as $meeting ) {
			$product_id     = get_post_meta( $meeting->ID, '_vczapi_zoom_product_id', true );
			$author         = get_userdata( $meeting->post_author );
			$author_name    = ! empty( $author->first_name ) ? $author->first_name . ' ' . $author->last_name : $author->display_name;
			$meeting_fields = get_post_meta( $meeting->ID, '_meeting_fields', true );
			$start_url      = get_post_meta( $meeting->ID, '_meeting_zoom_start_url', true );
			$join_url       = get_post_meta( $meeting->ID, '_meeting_zoom_join_url', true );
			$meeting_id     = get_post_meta( $meeting->ID, '_meeting_zoom_meeting_id', true );

			if ( ! empty( $product_id ) ) {
				$data[ $product_id ] = array(
					'title'      => $meeting->post_title,
					'author'     => $author_name,
					'host_email' => $author->user_email,
					'start_date' => $meeting_fields['start_date'],
					'timezone'   => $meeting_fields['timezone'],
					'start_uri'  => $start_url,
					'join_uri'   => $join_url,
					'meeting_id' => $meeting_id,
					'post_id'    => $meeting->ID
				);

				$order_ids = Datastore::get_orders_ids_by_product_id( $product_id );
				if ( ! empty( $order_ids ) ) {
					foreach ( $order_ids as $order_id ) {
						$order                                                       = wc_get_order( $order_id );
						$data[ $product_id ]['customer_details'][ $order->get_id() ] = array(
							'customer_id'        => $order->get_customer_id(),
							'billing_email'      => $order->get_billing_email(),
							'billing_first_name' => $order->get_billing_first_name(),
							'billing_last_name'  => $order->get_billing_last_name()
						);
					}
				}
			}
		}

		return $data;
	}

	/**
	 * After data is received send email to users.
	 *
	 * @param $order
	 */
	private function send_email( $order, $type = 'per_day' ) {

		$site_url   = site_url( '/' );
		$site_title = get_bloginfo( 'name' );
		$site_email = apply_filters( 'vczapi_woocommerce_change_sent_email', get_bloginfo( 'admin_email' ) );
		$year       = date( 'Y' );

		//Ready for email
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		if ( true == apply_filters( 'vczapi_woocommerce_modify_header_from_reminder_email', false ) ) {
			$headers[] = 'From: ' . $site_title . ' <' . $site_email . '>' . "\r\n";
		}

		// WPML Integration Starts
		// https://wpml.org/documentation/support/sending-emails-with-wpml/
		if ( function_exists( 'icl_object_id' ) ) {
			$email = $site_email;
			do_action( 'wpml_switch_language_for_email', $email );
		}

		/*******
		 * **********
		 * ********** Send Email to Hosts
		 * **********
		 */
		if ( $type === "per_day" ) {

			ob_start();
			$file = TemplateOverrides::get_template( array( 'emails/html/host-invite-twentyfourhours.php' ) );
			include $file;
			$email_template = ob_get_clean();
			$subject        = apply_filters( 'vczapi_cron_host_subject_per_day', sprintf( __( 'Your meeting %s is going to start in a day.', 'vczapi-woocommerce-addon' ), $order['title'] ), $order );

		} else if ( $type == '3_hours' ) {

			ob_start();
			$file = TemplateOverrides::get_template( array( 'emails/html/host-invite-threehours.php' ) );
			include $file;
			$email_template = ob_get_clean();
			$subject        = apply_filters( 'vczapi_cron_host_subject_three_hours', sprintf( __( 'Your meeting %s is going to start in three hours.', 'vczapi-woocommerce-addon' ), $order['title'] ), $order );

		}

		$search_strings = array(
			'{site_title}',
			'{site_url}',
			'{name}',
			'{meeeting_time}',
			'{meeting_topic}',
			'{meeting_timezone}',
			'{meeting_start_link}',
			'{meeting_id}',
			'{year}'
		);
		$search_strings = apply_filters( 'vczapi_host_email_strings', $search_strings );

		$replace_string = array(
			$site_title,
			$site_url,
			$order['author'],
			date( 'F j, Y, g:i a', strtotime( $order['start_date'] ) ),
			$order['title'],
			$order['timezone'],
			$order['start_uri'],
			$order['meeting_id'],
			$year
		);
		$replace_string = apply_filters( 'vczapi_host_email_strings_replace', $replace_string );

		$body = str_replace( $search_strings, $replace_string, $email_template );

		wp_mail( $order['host_email'], $subject, $body, $headers );

		if ( function_exists( 'icl_object_id' ) ) {
			do_action( 'wpml_reset_language_after_mailing' );
		}
		// WPML Integration Ends


		/*******
		 * **********
		 * ********** Send to Customers
		 * **********
		 */

		// WPML Integration Starts
		if ( function_exists( 'icl_object_id' ) ) {
			foreach ( $order['customer_details'] as $key => $value ) {
				$buyer_info_key = $key;
			}
			$email = $order['customer_details'][ $buyer_info_key ]['billing_email'];
			do_action( 'wpml_switch_language_for_email', $email );
		}

		if ( $type === "per_day" ) {

			ob_start();
			$file = TemplateOverrides::get_template( array( 'emails/html/meeting-invite-twentyfourhours.php' ) );
			include $file;
			$email_template_participants = ob_get_clean();
			$subject_particpant          = apply_filters( 'vczapi_cron_participant_subject_per_day', sprintf( __( 'Info: %s is going to start in a day.', 'vczapi-woocommerce-addon' ), $order['title'] ) );

		} else {

			ob_start();
			$file = TemplateOverrides::get_template( array( 'emails/html/meeting-invite-threehours.php' ) );
			include $file;
			$email_template_participants = ob_get_clean();
			$subject_particpant          = apply_filters( 'vczapi_cron_participant_subject_per_day', sprintf( __( 'Info: %s is going to start in three hours.', 'vczapi-woocommerce-addon' ), $order['title'] ) );

		}

		$search_strings_participants = array(
			'{site_title}',
			'{site_url}',
			'{meeeting_time}',
			'{meeting_topic}',
			'{meeting_timezone}',
			'{meeting_join_link}',
			'{meeting_id}',
			'{year}',
			'{meeting_browser_join_link}'
		);
		$search_strings_participants = apply_filters( 'vczapi_customer_email_strings', $search_strings_participants );
		$zoom_details                = get_post_meta( $order['post_id'], '_meeting_zoom_details', true );
		$join_via_browser            = '';

		if ( is_object( $zoom_details ) && isset( $zoom_details->password ) ) {
			$join_via_browser = vczapi_get_browser_join_shortcode( $order['meeting_id'], $zoom_details->password, true );
		}

		$browser_join_link           = vczapi_get_browser_join_shortcode( $order['meeting_id'], false, true );
		$replace_string_participants = array(
			$site_title,
			$site_url,
			date( 'F j, Y, g:i a', strtotime( $order['start_date'] ) ),
			$order['title'],
			$order['timezone'],
			$order['join_uri'],
			$order['meeting_id'],
			$year,
			$join_via_browser
		);
		$replace_string_participants = apply_filters( 'vczapi_customer_email_strings_replace', $replace_string_participants );

		$body_participants  = str_replace( $search_strings_participants, $replace_string_participants, $email_template_participants );
		$total_participants = ! empty( $order['customer_details'] ) ? $order['customer_details'] : false;
		//$email_settings     = get_option( 'vczapi_meeting_reminder_email_settings' );


		if ( $total_participants ) {
			foreach ( $total_participants as $total_participant ) {
				wp_mail( $total_participant['billing_email'], $subject_particpant, $body_participants, $headers );
			}
		}

		if ( function_exists( 'icl_object_id' ) ) {
			do_action( 'wpml_reset_language_after_mailing' );
		}
		// WPML Integration Ends

	}
}