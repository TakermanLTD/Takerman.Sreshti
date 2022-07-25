<?php

namespace Codemanas\ZoomWooCommerceAddon;

/**
 * Class DataStore
 *
 * Handle the getters and setters - More needs to be done. Simple execution right now.
 *
 * @author Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since 1.0.0
 * @package Codemanas\ZoomWooCommerceAddon
 */
class DataStore {

	/**
	 * Get join via browser Link
	 *
	 * @param $post_id
	 * @param $password
	 * @param $meeting_id
	 *
	 * @return string
	 */
	public static function get_browser_join_link( $post_id, $password, $meeting_id ) {
		//@TODO - this might be troublesome changing join via browser text if user wants to change on woocommerce order details and emails alone
		//things to check are how many users would need this - and - what can be done to fix it.
		return vczapi_get_browser_join_links( $post_id, $meeting_id, $password );
	}

	/**
	 * Get Zoom Product type by Product ID
	 *
	 * @param $product_id
	 *
	 * @return bool
	 */
	public static function get_zoom_product_type( $product_id ) {
		$product   = ! empty( $product_id ) ? wc_get_product( $product_id ) : false;
		$zoom_link = get_post_meta( $product_id, '_vczapi_enable_zoom_link', true );
		if ( ( ! empty( $product ) && $product->get_type() === 'zoom_meeting' ) || ! empty( $zoom_link ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get All meetings or with meta query filter
	 *
	 * @param array $meta_query
	 *
	 * @return \WP_Post[]
	 */
	public static function get_all_meetings( $meta_query = array() ) {
		$args = array(
			'post_type'      => 'zoom-meetings',
			'posts_per_page' => - 1
		);

		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query;
		}

		$meetings = get_posts( $args );

		return $meetings;
	}

	/**
	 * Get All orders IDs for a given product ID.
	 *
	 * @param integer $product_id (required)
	 * @param array $order_status (optional) Default is 'wc-completed'
	 *
	 * @return array
	 */
	public static function get_orders_ids_by_product_id( $product_id, $order_status = array( 'wc-completed', 'wc-processing' ) ) {
		global $wpdb;

		$results = $wpdb->get_col( "
	        SELECT order_items.order_id
	        FROM {$wpdb->prefix}woocommerce_order_items as order_items
	        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
	        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
	        WHERE posts.post_type = 'shop_order'
	        AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
	        AND order_items.order_item_type = 'line_item'
	        AND order_item_meta.meta_key = '_product_id'
	        AND order_item_meta.meta_value = '$product_id'
	    " );

		return $results;
	}

	/**
	 * Get orders IDS from a product ID
	 *
	 * @param $product_id
	 * @since 2.1.5
	 *
	 * @return array
	 */
	static function orders_ids_from_a_product_id( $product_id ) {
		global $wpdb;

		$orders_statuses = "'wc-completed', 'wc-processing'";

		# Get All defined statuses Orders IDs for a defined product ID (or variation ID)
		return $wpdb->get_col( "
        SELECT DISTINCT woi.order_id
        FROM {$wpdb->prefix}woocommerce_order_itemmeta as woim, 
             {$wpdb->prefix}woocommerce_order_items as woi, 
             {$wpdb->prefix}posts as p
        WHERE  woi.order_item_id = woim.order_item_id
        AND woi.order_id = p.ID
        AND p.post_status IN ( $orders_statuses )
        AND woim.meta_key IN ( '_product_id', '_variation_id' )
        AND woim.meta_value LIKE '$product_id'
        ORDER BY woi.order_item_id DESC"
		);
	}
}