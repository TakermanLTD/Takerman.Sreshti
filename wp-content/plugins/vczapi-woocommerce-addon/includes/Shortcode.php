<?php

namespace Codemanas\ZoomWooCommerceAddon;

/**
 * Class Shortcode
 *
 * @package Codemanas\ZoomWooCommerceAddon
 * @since   2.0.2
 */
class Shortcode {

	/**
	 * @var string
	 */
	public $post_type = 'zoom-meetings';

	/**
	 * Shortcode constructor.
	 */
	public function __construct() {
		add_shortcode( 'vczapi_wc_show_purchasable_meetings', array( $this, 'show_meetings' ) );
		add_shortcode( 'vczapi_wc_show_purchased_recordings', array( $this, 'show_recordings' ) );

		add_action( 'wp_ajax_nopriv_get_author_recordings', [ $this, 'get_recordings' ] );
		add_action( 'wp_ajax_get_author_recordings', [ $this, 'get_recordings' ] );
		add_action( 'wp_ajax_nopriv_clear_purchased_recording_cache', [ $this, 'clear_recordings_cache' ] );
		add_action( 'wp_ajax_clear_purchased_recording_cache', [ $this, 'clear_recordings_cache' ] );
	}

	/**
	 * Clear Recordings Cache
	 */
	public function clear_recordings_cache() {
		$current_user_id = get_current_user_id();
		Helper::set_user_cache( $current_user_id, '_vczapi_wc_purchased_recordings', '' );
		wp_die();
	}

	/**
	 * Get Recordings ajax call
	 */
	public function get_recordings() {
		$current_user_id   = get_current_user_id();
		$recordings        = array();
		$cached_recordings = Helper::get_user_cache( $current_user_id, '_vczapi_wc_purchased_recordings' );
		if ( ! empty( $cached_recordings ) ) {
			$result = $cached_recordings;
		} else {
			$orders = get_posts( array(
				'posts_per_page' => - 1,
				'meta_key'       => '_customer_user',
				'meta_value'     => $current_user_id,
				'post_type'      => wc_get_order_types(),
				'post_status'    => array( 'wc-completed', 'wc-processing', 'processing', 'completed' ), // new wc orders have wc- appended to woocommerce statuses we should consider using wc_get_order_statuses() or something that gets completed and processing status only
			) );

			if ( ! empty( $orders ) ) {
				foreach ( $orders as $order ) {
					$item  = wc_get_order( $order->ID );
					$items = $item->get_items();
					if ( ! empty( $items ) ) {
						foreach ( $items as $item ) {
							$product_id_arr[] = $item->get_product_id();
						}
					}
				}
			}

			if ( ! empty( $product_id_arr ) ) {
				$product_ids = array_unique( $product_id_arr );
				foreach ( $product_ids as $product_id ) {
					$zoom_meeting_post_id      = get_post_meta( $product_id, '_vczapi_zoom_post_id', true );
					$zoom_meeting_or_recording = get_post_meta( $product_id, '_vczapi_meeting_or_recording', true );
					//added by digamber for purchased recordings
					if ( $zoom_meeting_or_recording == 'recording' ) {
						$meetingUUIDS = get_post_meta( $product_id, 'vczapi_wc_recordings_for_product', true );
						foreach ( $meetingUUIDS as $meetingUUID ) {
							$check_recording = json_decode( zoom_conference()->recordingsByMeeting( $meetingUUID ) );
							if ( ! isset( $check_recording->code ) ) {
								$recordings[ $meetingUUID ] = $check_recording;
							}
						}
					} else if ( ! empty( $zoom_meeting_post_id ) ) {
						#$meeting_details = get_post_meta( $zoom_meeting_post_id, '_meeting_zoom_details', true ); //Just kept here if incase needed in future
						$meeting_id = get_post_meta( $zoom_meeting_post_id, '_meeting_zoom_meeting_id', true );
						//Get Past instances for the meeting first
						$all_past_meetings = json_decode( zoom_conference()->getPastMeetingDetails( $meeting_id ) );
						if ( ! empty( $all_past_meetings->meetings ) && ! isset( $all_past_meetings->code ) ) {
							//loop through all instance of past / completed meetings and get recordings
							foreach ( $all_past_meetings->meetings as $meeting ) {
								$check_recording = json_decode( zoom_conference()->recordingsByMeeting( $meeting->uuid ) );
								if ( ! isset( $check_recording->code ) ) {
									$recordings[ $meeting->uuid ] = $check_recording;
								}
							}
						} else {
							$check_recording = json_decode( zoom_conference()->recordingsByMeeting( $meeting_id ) );
							if ( ! isset( $check_recording->code ) ) {
								$recordings[ $meeting_id ] = $check_recording;
							}
						}
					}
				}
			}

			if ( ! empty( $recordings ) ) {
				Helper::set_user_cache( $current_user_id, '_vczapi_wc_purchased_recordings', $recordings, 86400 );
			}

			$result = $recordings;
		}

		if ( ! empty( $result ) ) {
			$response = array();
			foreach ( $result as $res ) {
				$response[] = array(
					'title'          => $res->topic,
					'start_date'     => apply_filters( 'vczapi_wc_recording_start_time', vczapi_dateConverter( $res->start_time, $res->timezone ), $res->start_time, $res->timezone ),
					'meeting_id'     => $res->id,
					'total_size'     => vczapi_filesize_converter( $res->total_size ),
					'view_recording' => '<a href="javascript:void(0);" data-downloadable="' . apply_filters( 'vczapi_woocommerce_recording_downloadable', 1 ) . '" class="vczapi-wc-view-recording" data-recording-id="' . $res->uuid . '">' . __( 'View Recordings', 'video-conferencing-with-zoom-api' ) . '</a><div class="vczapi-modal"></div>',
				);
			}

			wp_send_json_success( $response );
		} else {
			wp_send_json_error( false );
		}

		wp_die();
	}

	/**
	 * Show Recordings template
	 */
	public function show_recordings() {
		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.css', false, VZAPI_WOOCOMMERCE_ADDON_PLUGIN_VERSION );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.js', array( 'jquery' ), VZAPI_WOOCOMMERCE_ADDON_PLUGIN_VERSION, true );
		wp_enqueue_script( 'vczapi-woocommerce-script' );
		$output = apply_filters( 'vczapi_woocommerce_addon_localize_frontend', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'loading' => __( 'Loading recordings.. Please wait..', 'video-conferencing-with-zoom-api' )
		) );
		wp_localize_script( 'vczapi-woocommerce-script', 'vczapi_wc_addon', $output );

		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable-responsive' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-dt-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-responsive-js' );
		TemplateOverrides::get_template( [ 'frontend/recordings-list.php' ], true );
	}

	/**
	 * Show purchasable meetings based on timezone and category
	 *
	 * @param $atts
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function show_meetings( $atts ) {
		$atts = shortcode_atts( array(
			'per_page'      => 1,
			'category'      => '',
			'type'          => 'boxed',
			'order'         => 'DESC',
			'upcoming_only' => 'no'
		), $atts, 'vczapi_wc_show_purchasable_meetings' );
		if ( is_front_page() ) {
			$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}

		$args = array(
			'post_type'      => $this->post_type,
			'posts_per_page' => $atts['per_page'],
			'post_status'    => 'publish',
			'paged'          => $paged,
			'orderby'        => 'meta_value',
			'meta_key'       => '_meeting_field_start_date_utc',
			'order'          => $atts['order'],
			'meta_query'     => array(
				array(
					'key'     => '_vczapi_zoom_product_id',
					'value'   => '',
					'compare' => '!=',
				),
			)
		);
		if ( $atts['upcoming_only'] === "yes" ) {
			$args['meta_query'][] = array(
				'key'     => '_meeting_field_start_date_utc',
				'value'   => vczapi_dateConverter( 'now', 'UTC', 'Y-m-d H:i:s', false ),
				'type'    => 'DATETIME',
				'compare' => '>='
			);
		}

		if ( ! empty( $atts['category'] ) ) {
			$category          = array_map( 'trim', explode( ',', $atts['category'] ) );
			$args['tax_query'] = [
				[
					'taxonomy' => 'zoom-meeting',
					'field'    => 'slug',
					'terms'    => $category,
					'operator' => 'IN'
				]
			];
		}

		$query                     = apply_filters( 'vczapi_wc_purchasable_products_query_args', $args );
		$purchasable_zoom_products = new \WP_Query( $query );
		$GLOBALS['zoom_products']  = $purchasable_zoom_products;
		$content                   = '';

		ob_start();
		if ( $atts['type'] === "boxed" ) {
			include VZAPI_WOOCOMMERCE_ADDON_DIR_PATH . 'templates/shortcode/purchasable-products-box.php';
		} else {
			$tpl = TemplateOverrides::get_template( array( 'shortcode/purchasable-products-list.php' ) );
			include $tpl;
		}
		$content .= ob_get_clean();

		return $content;
	}

	/**
	 * Pagination
	 *
	 * @param $query
	 */
	public static function pagination( $query ) {
		$big = 999999999999999;
		if ( is_front_page() ) {
			$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}
		echo paginate_links( array(
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, $paged ),
			'total'   => $query->max_num_pages
		) );
	}
}