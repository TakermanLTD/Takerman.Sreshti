<?php


namespace Codemanas\ZoomWooCommerceAddon;


class WPML {
	public static $_instance = null;

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();

		}

		return self::$_instance;
	}

	public function __construct() {
		/**
		 * When translating main product
		 */
		add_action( 'wcml_before_sync_product', [ $this, 'sync_product_data' ], 10, 2 );

		add_filter( 'wcml_js_lock_fields_ids', [ $this, 'lock_fields_ids' ] );
	}

	public function lock_fields_ids( $ids ) {
		$ids = array_merge( $ids, [ '_vczapi_enable_zoom_link', '_vczapi_zoom_post_id' ] );

		return $ids;
	}

	/**
	 * @param $original_product_id   - original product ID in base language
	 * @param $translated_product_id - translated product ID
	 */
	public function sync_product_data( $original_product_id, $translated_product_id ) {
		$zoom_enabled = get_post_meta( $original_product_id, '_vczapi_enable_zoom_link', true );
		if ( $zoom_enabled ) {
			$zoom_post_id = get_post_meta( $original_product_id, '_vczapi_zoom_post_id', true );
			update_post_meta( $translated_product_id, '_vczapi_enable_zoom_link', $zoom_enabled );
			update_post_meta( $translated_product_id, '_vczapi_zoom_post_id', $zoom_post_id );

		} else {
			delete_post_meta( $translated_product_id, '_vczapi_enable_zoom_link' );
			delete_post_meta( $translated_product_id, '_vczapi_zoom_post_id' );
		}

	}

}