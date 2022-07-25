<?php
/**
 * WC Detection
 */
if ( ! function_exists( 'vczapi_woocommerce_addon_active' ) ) {
	function vczapi_woocommerce_addon_active() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}
}

if ( ! function_exists( 'vczapi_wc_product_vendors_addon_active' ) ) {
	function vczapi_wc_product_vendors_addon_active() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		return in_array( 'woocommerce-product-vendors/woocommerce-product-vendors.php', $active_plugins ) || array_key_exists( 'woocommerce-product-vendors/woocommerce-product-vendors.php', $active_plugins );
	}
}

if ( ! function_exists( 'vczapi_recurring_addon_active' ) ) {
	function vczapi_recurring_addon_active() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		return in_array( 'vczapi-pro/vczapi-pro.php', $active_plugins ) || array_key_exists( 'vczapi-pro/vczapi-pro.php', $active_plugins );
	}
}

if ( vczapi_woocommerce_addon_active() ) {
	add_action( 'init', 'vczapi_woocommerce_addon_product_type_register' );
	function vczapi_woocommerce_addon_product_type_register() {
		if ( class_exists( 'WC_Product_Zoom_Meeting' ) ) {
			return;
		}

		class WC_Product_Zoom_Meeting extends \WC_Product {
			private $product_type;

			public function __construct( $product ) {
				$this->product_type = 'zoom_meeting';
				parent::__construct( $product );
			}

			public function get_type() {
				return 'zoom_meeting';
			}
		}
	}
}