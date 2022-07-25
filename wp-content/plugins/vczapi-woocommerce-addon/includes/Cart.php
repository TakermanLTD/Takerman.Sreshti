<?php

namespace Codemanas\ZoomWooCommerceAddon;

use Codemanas\ZoomWooCommerceAddon\DataStore as DataStore;

/**
 * Class Integration
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.1.0
 * @package Codemanas\ZoomWooCommerceAddon
 */
class Cart {

	/**
	 * Instance property
	 *
	 * @var null
	 */
	public static $instance = null;

	/**
	 * Instance object
	 *
	 * @return Cart|null
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Build the instance
	 */
	public function __construct() {
		add_action( 'wp_loaded', [ $this, 'meeting_join_config' ] );

		add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'add_to_cart_validation' ], 10, 3 );
		add_filter( 'woocommerce_update_cart_validation', [ $this, 'cart_page_validation' ], 1, 4 );
		add_filter( 'woocommerce_cart_item_name', [ $this, 'cart_meeting_details' ], 10, 3 );

		add_action( 'woocommerce_zoom_meeting_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
	}

	/**
	 * Enable or disable default functionality by overriding from this plugin.
	 */
	public function meeting_join_config() {
		if ( wp_doing_ajax() ) {
			$post_id = sanitize_text_field( absint( filter_input( INPUT_POST, 'post_id' ) ) );
			if ( vczapi_check_author( $post_id ) ) {
				return;
			}

			$type       = sanitize_text_field( filter_input( INPUT_POST, 'type' ) );
			$fields     = get_post_meta( $post_id, '_meeting_fields_woocommerce', true );
			$product_id = get_post_meta( $post_id, '_vczapi_zoom_product_id', true );

			global $current_user;
			//Allow only if customer has bought or is the owner of the product.
			if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id ) ) {
				return;
			}

			if ( ! empty( $fields['enable_woocommerce'] ) && $type === "page" ) {
				remove_action( 'vczoom_meeting_join_links', 'video_conference_zoom_meeting_join_link', 10 );
				add_action( 'vczoom_meeting_join_links', array( $this, 'add_purchase_link' ) );
			}
		}
	}

	/**
	 * Generate Add to Cart Button
	 *
	 * Show Buy now button if product is not yet purchased.
	 * Show join links if product is already purchased.
	 */
	public function add_purchase_link( $zoom_meeting ) {
		$post_id    = filter_input( INPUT_POST, 'post_id' );
		$product_id = get_post_meta( $post_id, '_vczapi_zoom_product_id', true );
		$product    = wc_get_product( $product_id );
		?>
        <div class="dpn-zvc-sidebar-box">
            <div class="join-links">
				<?php if ( ! empty( $product ) && $product->get_type() == 'zoom_meeting' ) { ?>
                    <a class="btn btn-join-link" href="<?php echo esc_url( wc_get_checkout_url() . '?add-to-cart=' . $product->get_id() ); ?>"><?php echo apply_filters( 'vczapi_buy_now_text', __( 'Buy Now for', 'vczapi-woocommerce-addon' ) ) . ' ' . wc_price( $product->get_price() ); ?></a>
				<?php } else if ( ! empty( $product ) && $product->get_type() != 'zoom_meeting' ) {
					?>
                    <a class="btn btn-join-link" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>"><?php echo apply_filters( 'vczapi_buy_now_text', __( 'Buy Now', 'vczapi-woocommerce-addon' ) ); ?></a>
					<?php
				} else { ?>
                    <p><?php _e( 'This product is not valid for purchase.', 'vczapi-woocommerce-addon' ); ?></p>
				<?php } ?>
            </div>
        </div>
		<?php
	}

	/**
	 * Validate if quantity is greater than 1 or is a product already added.
	 *
	 * @param $passed
	 * @param $product_id
	 * @param $quantity
	 *
	 * @return bool
	 */
	public function add_to_cart_validation( $passed, $product_id, $quantity ) {
		$type = DataStore::get_zoom_product_type( $product_id );
		if ( $type ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$cart_product_id = $cart_item['product_id'];
				if ( $cart_product_id == $product_id ) {
					wc_add_notice( sprintf( __( 'You have already added this meeting in your cart ! %s', 'vczapi-woocommerce-addon' ), '<a href="' . wc_get_cart_url() . '" tabindex="1" class="button wc-forward">View cart</a>' ), 'error' );
					$passed = false;
					break;
				}
			}
		}

		return $passed;
	}

	/**
	 * Validate Zoom Meeting quantity count on cart page
	 *
	 * @param $passed
	 * @param $cart_item_key
	 * @param $values
	 * @param $quantity
	 *
	 * @return bool
	 */
	public function cart_page_validation( $passed, $cart_item_key, $values, $quantity ) {
		$product_id = $values['product_id'];
		$type       = DataStore::get_zoom_product_type( $product_id );
		if ( $type && $quantity > 1 ) {
			wc_add_notice( __( 'Zoom Meeting product quantity cannot be greater than 1 !', 'vczapi-woocommerce-addon' ), 'error' );
			$passed = false;
		}

		return $passed;
	}

	/**
	 * Show extra details in cart page
	 *
	 * @param $name
	 * @param $cart_item
	 * @param $cart_item_key
	 *
	 * @return string
	 */
	public function cart_meeting_details( $name, $cart_item, $cart_item_key ) {
		$product_id = $cart_item['product_id'];
		$post_id    = get_post_meta( $product_id, '_vczapi_zoom_post_id', true );
		if ( ! empty( $post_id ) ) {
			$meeting_details = get_post_meta( $post_id, '_meeting_fields', true );
			$zoom_details    = get_post_meta( $post_id, '_meeting_zoom_details', true );
			$users           = video_conferencing_zoom_api_get_user_transients();
			$host_name       = false;
			if ( ! empty( $zoom_details ) && is_object( $zoom_details ) ) {
				if ( ! empty( $users ) ) {
					foreach ( $users as $user ) {
						if ( $zoom_details->host_id === $user->id ) {
							$host_name = esc_html( $user->first_name . ' ' . $user->last_name );
							$host_name = ! empty( $host_name ) ? $host_name : $user->email;
							break;
						}
					}
				}

				$add_name = sprintf(
					'<p style="margin-top:10px;"><strong>' . __( 'Host', 'vczapi-woocommerce-addon' ) . ':</strong><br>%s</p><p style="margin-top:10px;"><strong>' . __( 'Time', 'vczapi-woocommerce-addon' ) . ':</strong><br>%s</p><p><strong>' . __( 'Timezone', 'vczapi-woocommerce-addon' ) . ':</strong><br>%s</p>',
					$host_name,
					! empty( $zoom_details->start_time ) ? vczapi_dateConverter( $zoom_details->start_time, $zoom_details->timezone, 'F j, Y, g:i a', true ) : '',
					esc_html( $meeting_details['timezone'] )
				);
				$name     .= apply_filters( 'vczapi_woocommerce_cart_meeting_details', $add_name, $cart_item, $cart_item_key );
			}
		}

		return $name;
	}
}