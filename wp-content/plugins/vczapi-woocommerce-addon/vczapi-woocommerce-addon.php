<?php
/**
 * @link              http://www.deepenbajracharya.com.np
 * @since             1.0.0
 * @package           Zoom Integration for WooCommerce and Bookings
 *
 * Plugin Name:       Zoom Integration for WooCommerce
 * Plugin URI:        https://www.codemanas.com/downloads/zoom-meetings-for-woocommerce/
 * Description:       Seamless integration into WooCommerce products if enabled.
 * Version:           2.6.13
 * Author:            CodeManas
 * Author URI:        https://www.codemanas.com/
 * Text Domain:       vczapi-woocommerce-addon
 * Requires at least: 4.8
 * WC requires at least: 3.0.0
 * WC tested up to: 4.0.0
 * Domain Path:       /languages
 */

// Block direct access to the main plugin file.
defined( 'ABSPATH' ) or die( 'No script kiddies !' );

if ( ! defined( 'VZAPI_WOOCOMMERCE_ADDON_PLUGIN' ) ) {
	define( 'VZAPI_WOOCOMMERCE_ADDON_PLUGIN', 'Zoom Integration for WooCommerce' );
}

if ( ! defined( 'VZAPI_WOOCOMMERCE_ADDON_PLUGIN_VERSION' ) ) {
	define( 'VZAPI_WOOCOMMERCE_ADDON_PLUGIN_VERSION', '2.6.13' );
}

if ( ! defined( 'VZAPI_WOOCOMMERCE_ADDON_DIR_PATH' ) ) {
	define( 'VZAPI_WOOCOMMERCE_ADDON_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'VZAPI_WOOCOMMERCE_ADDON_DIR_URI' ) ) {
	define( 'VZAPI_WOOCOMMERCE_ADDON_DIR_URI', plugin_dir_url( __FILE__ ) );
}

function vczapi_woocommerce_addon_plugin_version_required() {
	?>
    <div class="error">
        <p><a href="https://wordpress.org/plugins/video-conferencing-with-zoom-api/">Video Conferencing with Zoom Version plugin</a> version
            <strong>3.6.20/strong> or greater is required for <strong><?php echo VZAPI_WOOCOMMERCE_ADDON_PLUGIN; ?></strong> to function properly.
                Please update the plugin <a href="<?php echo admin_url( '/plugins.php' ); ?>">from plugins page</a> to version <strong>3.6.20/strong> or
                    greater.</p>
    </div>
	<?php
}

function vczapi_woocommerce_addon_admin_error_notice() {
	$message = sprintf( esc_html__( 'This plugin requires PHP 5.6+ to run properly. Please contact your hosting company and ask them to update the PHP version of your site to at least PHP 5.6+ Your current version of PHP: %2$s%1$s%3$s', 'vczapi-woo-addon' ), phpversion() );

	printf( '<div class="notice notice-error"><p>%1$s</p></div>', wp_kses_post( $message ) );
}

function vczapi_woocommerce_addon_error() {
	?>
    <div class="error">
        <p><strong><?php echo VZAPI_WOOCOMMERCE_ADDON_PLUGIN; ?> is inactive.</strong> <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>
            must be active to work. Please install or activate WooCommerce first.</p>
    </div>
	<?php
}

function vczapi_woocommerce_addon_disable_this() {
	?>
    <div class="error">
        <p>You must disable <strong><?php echo VZAPI_WOOCOMMERCE_ADDON_PLUGIN; ?></strong> plugin in order to use <strong>Zoom Integration for
                WooCommerce and Bookings</strong> ==> This plugin contains all features provided by
            <strong><?php echo VZAPI_WOOCOMMERCE_ADDON_PLUGIN; ?></strong> as well as extra booking features.</p>
    </div>
	<?php
}

function vczapi_woocommerce_addon_load_textdomain() {
	load_plugin_textdomain( 'vczapi-woocommerce-addon', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'vczapi_woocommerce_addon_load_textdomain' );

if ( ! in_array( 'video-conferencing-with-zoom-api/video-conferencing-with-zoom-api.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action( 'admin_notices', 'vczapi_woocommerce_addon_notice' );
	function vczapi_woocommerce_addon_notice() {
		?>
        <div class="error">
            <p>Free version of <strong>Video Conferencing with Zoom</strong> is required for
                <strong><?php echo VZAPI_WOOCOMMERCE_ADDON_PLUGIN; ?></strong> plugin to function. Please Activate or Download from
                <a href="https://wordpress.org/plugins/video-conferencing-with-zoom-api/">WordPress repository</a> or search <strong>"Video
                    Conferencing with Zoom API"</strong> in <a href="<?php echo admin_url( 'plugin-install.php' ) ?>">plugin page</a>.</p>
        </div>
		<?php
	}
} else {
	$plugin_data = false;
	if ( is_admin() && ! function_exists( 'get_plugin_data' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . 'video-conferencing-with-zoom-api/video-conferencing-with-zoom-api.php' );
	}

	require_once VZAPI_WOOCOMMERCE_ADDON_DIR_PATH . 'vendor/autoload.php';

	// Check for presence of core dependencies
	if ( version_compare( phpversion(), '5.6', '<' ) ) {
		add_action( 'admin_notices', array( $this, 'vczapi_woocommerce_addon_admin_error_notice' ) );
	} else if ( ! empty( $plugin_data ) && version_compare( $plugin_data['Version'], '3.6.20', '<' ) ) {
		add_action( 'admin_notices', 'vczapi_woocommerce_addon_plugin_version_required' );
	} else {
		require_once VZAPI_WOOCOMMERCE_ADDON_DIR_PATH . 'woo-dependencies.php';
		if ( ! vczapi_woocommerce_addon_active() ) {
			add_action( 'admin_notices', 'vczapi_woocommerce_addon_error' );

			return;
		}

		add_action( 'plugins_loaded', 'Codemanas\ZoomWooCommerceAddon\Bootstrap::get_instance', 100 );
	}

	register_activation_hook( __FILE__, 'Codemanas\ZoomWooCommerceAddon\Bootstrap::activate' );
	register_activation_hook( __FILE__, 'Codemanas\ZoomWooCommerceAddon\CronHandlers::activate_cron' );
	if ( ! \Codemanas\ZoomWooCommerceAddon\Helper::check_is_booking_addon_active() ) {
		//don't deactivate if booking  addon is still active
		register_deactivation_hook( __FILE__, 'Codemanas\ZoomWooCommerceAddon\CronHandlers::deactivate_cron' );
	}
}