<?php

namespace Codemanas\ZoomWooCommerceAddon\Admin;

use Codemanas\ZoomWooCommerceAddon\DataStore;

/**
 * Class ZoomMetaBox
 *
 * Handler for meta box in zoom meeting section
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.1.0
 * @package Codemanas\ZoomWooCommerceAddon\Admin
 */
class ZoomMetaBox {

	/**
	 * Build the instance
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post_zoom-meetings', array( $this, 'save_metabox' ), 11, 2 );
		add_action( 'save_post_zoom-meetings', array( $this, 'reminder_email_maybe_reset' ), 0, 2 );

		//Delete post hook
		add_action( 'before_delete_post', array( $this, 'delete' ) );
	}

	public function reminder_email_maybe_reset( $post_id, $post ) {
		$nonce_name   = isset( $_POST['_zvc_nonce'] ) ? $_POST['_zvc_nonce'] : '';
		$nonce_action = '_zvc_meeting_save';

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}
		$product_id = get_post_meta( $post_id, '_vczapi_zoom_product_id', true );

		//if we don't have a product linked there is no use in doing this - bail early
		if ( empty( $product_id ) ) {
			return;
		}

		$start_date = filter_input( INPUT_POST, 'start_date' );
		$timezone   = filter_input( INPUT_POST, 'timezone' );

		$prev_start_date = get_post_meta( $post_id, '_meeting_field_start_date_utc', true );

		$meeting_date_time = new \DateTime( $start_date, new \DateTimeZone( $timezone ) );
		$meeting_date_time->setTimezone( new \DateTimeZone( 'UTC' ) );

		if ( $prev_start_date != $meeting_date_time->format( 'Y-m-d H:i:s' ) ) {
			delete_post_meta( $product_id, '_vczapi_cron_run_order_one_day' );
		}

	}

	/**
	 * Adds the meta box.
	 */
	public function add_metabox() {
		add_meta_box( 'vczapi-woocommerce-integration-meta', __( 'WooCommerce Integration', 'vczapi-woocommerce-addon' ), array(
			$this,
			'rendor_sidebox'
		), 'zoom-meetings', 'side' );
		add_meta_box( 'vczapi-woocommerce-integration-orders', __( 'Orders', 'vczapi-woocommerce-addon' ), array(
			$this,
			'rendor_registrants'
		), 'zoom-meetings', 'normal', 'low' );

		if ( isset( $_GET['post'] ) ) {
			$post_id    = $_GET['post'];
			$meeting_id = get_post_meta( $post_id, '_vczapi_zoom_post_id', true );
			$enabled    = get_post_meta( $post_id, '_vczapi_enable_zoom_link', true );
			if ( ! empty( $meeting_id ) && ! empty( $enabled ) ) {
				add_meta_box( 'vczapi-woocommerce-integration-orders', __( 'Orders', 'vczapi-woocommerce-addon' ), array(
					$this,
					'rendor_registrants'
				), 'product', 'normal', 'high' );
			}
		}
	}

	/**
	 * Render Meta box html fields
	 *
	 * @param $post
	 */
	public function rendor_sidebox( $post ) {
		$fields     = get_post_meta( $post->ID, '_meeting_fields_woocommerce', true );
		$product_id = get_post_meta( $post->ID, '_vczapi_zoom_product_id', true );
		$product    = wc_get_product( $product_id );
		?>
        <style>
            #vczapi-zoom-metabox-content .form-content {
                margin: 10px 0;
            }

            #vczapi-zoom-metabox-content .form-content input[type=number] {
                margin-top: 10px;
            }

            #vczapi-zoom-metabox-content p {
                padding: 0;
                margin: 10px 0;
            }
        </style>
        <div id="vczapi-zoom-metabox-content">
            <div class="form-content">
                <label><?php _e( 'Enable Purchase ?', 'vczapi-woocommerce-addon' ); ?></label>
                <input type="checkbox" name="option_enable_purchase_zoom" <?php echo ! empty( $fields ) && ! empty( $fields['enable_woocommerce'] ) ? 'checked' : false; ?> class="vczapi-enable-woocommerce-purchase regular-text">
				<?php if ( $product === false || ( ! empty( $product ) && $product->get_type() == 'zoom_meeting' ) ) { ?>
                    <p class="description"><?php _e( 'Enabling this will create a new WooCommerce product linked with this post which will be purchasable by customers.', 'vczapi-woocommerce-addon' ); ?></p>
                    <p class="description" style="color:red;"><?php _e( 'NOTE: Avoid checking this option if you want to sell this meeting from your WooCommerce shop page.', 'vczapi-woocommerce-addon' ); ?></p>
				<?php } else { ?>
                    <p class="description">
						<?php _e( 'This Meeting has been linked with product, to change pricing - please change product options. You can change time and host from this post though.', 'vczapi-woocommerce-addon' ); ?>
                    </p>
				<?php } ?>
            </div>
			<?php if ( $product === false || ( ! empty( $product ) && $product->get_type() == 'zoom_meeting' ) ) { ?>
                <div class="form-content show-on-checked" <?php echo ! empty( $fields ) && ! empty( $fields['enable_woocommerce'] ) ? false : 'style="display: none;"'; ?>>
                    <label><?php _e( 'Regular Price', 'vczapi-woocommerce-addon' ); ?></label>
                    <input type="number" step="0.01" name="option_product_cost" value="<?php echo ! empty( $fields ) && ! empty( $fields['cost'] ) ? esc_html( $fields['cost'] ) : 1; ?>" placeholder="30" class="regular-text" required>
                </div>
				<?php
			}

			if ( $product != false && ! empty( $fields['enable_woocommerce'] ) ) {
				?>
                <p><strong><?php echo _e( 'Product Linked with', 'vczapi-woocommerce-addon' ); ?>: </strong><br><strong>ID
                        #</strong><a target="_blank" href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>" title="<?php echo esc_html( $product->get_title() ); ?>"><?php echo esc_html( $product->get_id() ); ?></a><br>
                    <strong>Product:</strong> <?php echo esc_html( $product->get_title() ); ?></p>
			<?php } ?>
        </div>
		<?php
	}

	/**
	 * Show registrants for the meeting
	 *
	 * @param $post
	 */
	public function rendor_registrants( $post ) {
		if ( $post->post_type !== "product" ) {
			$product_id = get_post_meta( $post->ID, '_vczapi_zoom_product_id', true );
		} else {
			$product_id = $post->ID;
		}

		$order_ids = DataStore::orders_ids_from_a_product_id( $product_id );
		if ( ! empty( $order_ids ) ) {
			wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-js' );
			?>
            <table id="vczapi-wc-meeting-registrants-dtable" class="vczapi-wc-meeting-registrants-table vczapi-data-table">
                <thead>
                <tr>
                    <th><?php _e( 'Order', 'vczapi-woocommerce-addon' ); ?> #</th>
                    <th><?php _e( 'Billing Email', 'vczapi-woocommerce-addon' ); ?></th>
                    <th><?php _e( 'Billing First Name', 'vczapi-woocommerce-addon' ); ?></th>
                    <th><?php _e( 'Billing Last Name', 'vczapi-woocommerce-addon' ); ?></th>
                    <th><?php _e( 'Order Date', 'vczapi-woocommerce-addon' ); ?></th>
                </tr>
                </thead>
                <tbody>
				<?php
				foreach ( $order_ids as $order_id ) {
					$order = wc_get_order( $order_id );
					if ( ! method_exists( $order, 'get_edit_order_url' ) ) {
						continue;
					}
					?>
                    <tr>
                        <td>
                            <a target="_blank" href="<?php echo $order->get_edit_order_url(); ?>"><?php _e( 'View Order', 'vczapi-woocommerce-addon' ); ?></a>
                        </td>
                        <td><?php echo $order->get_billing_email(); ?></td>
                        <td><?php echo $order->get_billing_first_name(); ?></td>
                        <td><?php echo $order->get_billing_last_name(); ?></td>
                        <td><?php echo wc_format_datetime( $order->get_date_paid(), 'F j, Y @ g:i a' ); ?></td>
                    </tr>
					<?php
				}
				?>
                </tbody>
            </table>
			<?php
		} else {
			echo "<p>" . __( 'No bookings for this meeting so far.', 'vczapi-woocommerce-addon' ) . "</p>";
		}
	}

	/**
	 * When saving with if woocommerce is enabled created woocommerce product and link here.
	 *
	 * @param $post_id
	 * @param $post
	 *
	 * @return void|\WP_Error
	 * @throws \WC_Data_Exception
	 */
	public function save_metabox( $post_id, $post ) {
		$nonce_name   = isset( $_POST['_zvc_nonce'] ) ? $_POST['_zvc_nonce'] : '';
		$nonce_action = '_zvc_meeting_save';

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		$enable_woocommerce = sanitize_text_field( filter_input( INPUT_POST, 'option_enable_purchase_zoom' ) );
		$cost               = sanitize_text_field( filter_input( INPUT_POST, 'option_product_cost' ) );
		$start_date         = sanitize_text_field( filter_input( INPUT_POST, 'start_date' ) );
		$settings           = array(
			'enable_woocommerce' => $enable_woocommerce,
			'cost'               => $cost,
		);

		update_post_meta( $post_id, '_meeting_fields_woocommerce', $settings );
		update_post_meta( $post_id, '_meeting_start_date', $start_date );

		//WooCommerce product is enabled
		if ( ! empty( $enable_woocommerce ) ) {
			$this->create_product( $post, $settings );
		} else {
			$product_id = get_post_meta( $post_id, '_vczapi_zoom_product_id', true );
			WooCommerceZoomConnection::unlink_product( $product_id, $post_id );
		}
	}

	/**
	 * Create or Update product
	 *
	 * @param $post
	 * @param $settings
	 *
	 * @throws \WC_Data_Exception
	 */
	private function create_product( $post, $settings ) {
		$old_product_id    = get_post_meta( $post->ID, '_vczapi_zoom_product_id', true );
		$featured_image_id = get_post_thumbnail_id( $post );
		if ( ! empty( $old_product_id ) ) {
			$objProduct = wc_get_product( $old_product_id );
			if ( $objProduct->get_type() === "zoom_meeting" ) {
				$objProduct->set_catalog_visibility( 'hidden' );
				$objProduct->set_name( $post->post_title );
				$objProduct->set_status( $post->post_status );
				$cost = ( isset( $settings['cost'] ) && is_numeric( $settings['cost'] ) ) ? $settings['cost'] : 1;
				$objProduct->set_price( $cost );
				$objProduct->set_regular_price( $cost );
				$objProduct->set_description( $post->post_content );
				$objProduct->set_virtual( true );
				$objProduct->set_sold_individually( true );
			}
		} else {
			$objProduct = new \WC_Product();
			//IF product is new then only set is as Zoom Meeting Type else leave it as it is.
			$objProduct->set_catalog_visibility( 'hidden' );
			$objProduct->set_name( $post->post_title );
			$objProduct->set_status( $post->post_status );
			$cost = ! empty( $settings['cost'] ) ? $settings['cost'] : 1;
			$objProduct->set_price( $cost );
			$objProduct->set_regular_price( $cost );
			$objProduct->set_description( $post->post_content );
			$objProduct->set_virtual( true );
			$objProduct->set_sold_individually( true );
			do_action( 'vczapi_before_new_zoom_product_saved', $objProduct );
		}

		do_action( 'vczapi_before_zoom_product_saved', $objProduct );
		//Save Product
		$product_id = $objProduct->save();
		if ( ! empty( $product_id ) ) {
			if ( ! empty( $featured_image_id ) && true == apply_filters( 'vczapi_wc_sync_featured_image', true ) ) {
				set_post_thumbnail( $product_id, $featured_image_id );
			}
			update_post_meta( $product_id, '_vczapi_enable_zoom_link', 'yes' );
			update_post_meta( $product_id, '_vczapi_woo_addon_product_price', $cost );
			update_post_meta( $product_id, '_vczapi_zoom_post_id', $post->ID );
			update_post_meta( $post->ID, '_vczapi_zoom_product_id', $product_id );
			if ( empty( $old_product_id ) ) {
				wp_set_object_terms( $product_id, 'zoom_meeting', 'product_type' );
			}
		}
	}

	/**
	 * Delete product if a meeting is deleted.
	 *
	 * @param $post_id
	 */
	function delete( $post_id ) {
		// We check if the global post type isn't ours and just return
		global $post_type;
		if ( $post_type != 'zoom-meetings' ) {
			return;
		}

		$product_id = get_post_meta( $post_id, '_vczapi_zoom_product_id', true );
		//Delete linked product if product id exists
		if ( ! empty( $product_id ) ) {
			wp_delete_post( $product_id );
		}
	}
}