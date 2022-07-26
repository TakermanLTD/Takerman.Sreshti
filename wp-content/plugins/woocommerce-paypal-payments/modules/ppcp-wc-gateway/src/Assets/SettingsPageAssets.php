<?php
/**
 * Register and configure assets provided by this module.
 *
 * @package WooCommerce\PayPalCommerce\WcGateway\Assets
 */

declare(strict_types=1);

namespace WooCommerce\PayPalCommerce\WcGateway\Assets;

use WooCommerce\PayPalCommerce\Subscription\Helper\SubscriptionHelper;

/**
 * Class SettingsPageAssets
 */
class SettingsPageAssets {

	/**
	 * The URL of this module.
	 *
	 * @var string
	 */
	private $module_url;

	/**
	 * The assets version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * The subscription helper.
	 *
	 * @var SubscriptionHelper
	 */
	protected $subscription_helper;

	/**
	 * Assets constructor.
	 *
	 * @param string             $module_url The url of this module.
	 * @param string             $version                            The assets version.
	 * @param SubscriptionHelper $subscription_helper The subscription helper.
	 */
	public function __construct( string $module_url, string $version, SubscriptionHelper $subscription_helper ) {
		$this->module_url          = $module_url;
		$this->version             = $version;
		$this->subscription_helper = $subscription_helper;
	}

	/**
	 * Register assets provided by this module.
	 */
	public function register_assets() {
		add_action(
			'admin_enqueue_scripts',
			function() {
				if ( ! is_admin() || wp_doing_ajax() ) {
					return;
				}

				if ( ! $this->is_paypal_payment_method_page() ) {
					return;
				}

				$this->register_admin_assets();
			}
		);

	}

	/**
	 * Check whether the current page is PayPal payment method settings.
	 *
	 * @return bool
	 */
	private function is_paypal_payment_method_page(): bool {

		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		$tab     = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
		$section = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING );

		if ( ! 'woocommerce_page_wc-settings' === $screen->id ) {
			return false;
		}

		return 'checkout' === $tab && 'ppcp-gateway' === $section;
	}

	/**
	 * Register assets for admin pages.
	 */
	private function register_admin_assets() {
		wp_enqueue_script(
			'ppcp-gateway-settings',
			trailingslashit( $this->module_url ) . 'assets/js/gateway-settings.js',
			array(),
			$this->version,
			true
		);

		// Intent is configured with Authorize and Capture Virtual-Only Orders is not set.
		wp_localize_script(
			'ppcp-gateway-settings',
			'PayPalCommerceGatewaySettings',
			array(
				'is_subscriptions_plugin_active' => $this->subscription_helper->plugin_is_active(),
			)
		);
	}
}
