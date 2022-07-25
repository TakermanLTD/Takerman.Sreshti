<?php

namespace Codemanas\ZoomWooCommerceAddon;

/**
 * Class Bootstrap
 *
 * Bootstrap our plugin
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomWooCommerceAddon
 */
class Bootstrap {

	public static $item_id = 1986;
	public static $store_url = 'https://www.codemanas.com';
	public static $options_page = 'edit.php?post_type=zoom-meetings&page=woocommerce&tab=license';
	private static $license = '_vczapi_woocommerce_addon_license';

	private static $_instance = null;

	private $key_validate;

	private $plugin_settings;

	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Bootstrap constructor.
	 */
	public function __construct() {
		$this->key_validate    = trim( get_option( self::$license ) );
		$this->plugin_settings = get_option( '_vczapi_woocommerce_settings' );

		$this->load();

		add_action( 'admin_init', array( $this, 'updater' ), 1 );
		add_action( 'admin_init', array( $this, 'load_admin' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
	}

	static function activate() {
		//Check if WooCommerce Exists
		if ( ! is_plugin_active( 'video-conferencing-with-zoom-api/video-conferencing-with-zoom-api.php' ) and current_user_can( 'activate_plugins' ) ) {
			$exit_msg = __( "This Plugin requires Video Conferencing with zoom api plugin to work. Please install it first to use this.", "vczapi-woocommerce-addon" );
			wp_die( $exit_msg );
		}

		//Create rewrite endpoint
		add_rewrite_endpoint( 'wc-zoom-meetings', EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'wc-zoom-recordings', EP_ROOT | EP_PAGES );
		flush_rewrite_rules();
	}

	/**
	 * Load Admin Classes
	 */
	public function load_admin() {
		if ( ! empty( $this->key_validate ) ) {
			//Normal WooCommerce Product
			$this->autowire( Admin\WooCommerceTable::class );
			$this->autowire( Admin\ZoomMetaBox::class );
		}
	}

	/**
	 * Load Dependencies
	 */
	public function load() {
		if ( ! empty( $this->key_validate ) ) {
			$this->autowire( TemplateOverrides::class );
			$this->autowire( Admin\ProductType::class );
			Admin\WooCommerceZoomConnection::get_instance();
			Orders::get_instance();

			if ( vczapi_wc_product_vendors_addon_active() ) {
				ProductVendors::get_instance();
			}

			$this->autowire( CronHandlers::class );
			Cart::get_instance();
			$this->autowire( Shortcode::class );

			if ( class_exists( 'SitePress' ) ) {
				WPML::get_instance();
			}
		}

		$this->autowire( Admin\Activator::class );
		$this->autowire( Admin\Settings::class );
	}

	/**
	 * Dependency Injection Process
	 *
	 * @param $obj
	 *
	 * @since  1.0.2
	 * @author Deepen Bajracharya
	 */
	private function autowire( $obj ) {
		new $obj;
	}

	/**
	 * Updater
	 */
	public static function updater() {
		$license_key = trim( get_option( self::$license ) );
		$updater     = new Updater( self::$store_url, VZAPI_WOOCOMMERCE_ADDON_DIR_PATH . 'vczapi-woocommerce-addon.php', array(
			'version' => VZAPI_WOOCOMMERCE_ADDON_PLUGIN_VERSION,
			'license' => $license_key,
			'author'  => 'Deepen Bajracharya',
			'item_id' => self::$item_id,
			'beta'    => false,
		) );

		$updater->check();
	}

	/**
	 * Enqueue Scripts in frontend side
	 */
	public function scripts() {
		wp_register_script( 'vczapi-woocommerce-script', VZAPI_WOOCOMMERCE_ADDON_DIR_URI . 'assets/frontend/js/scripts.min.js', array( 'jquery' ), VZAPI_WOOCOMMERCE_ADDON_PLUGIN_VERSION, true );
		wp_enqueue_style( 'vczapi-woocommerce-style', VZAPI_WOOCOMMERCE_ADDON_DIR_URI . 'assets/frontend/css/style.min.css', false, VZAPI_WOOCOMMERCE_ADDON_PLUGIN_VERSION );
	}
}