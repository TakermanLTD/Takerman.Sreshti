<?php

namespace Codemanas\ZoomWooCommerceAddon\Admin;

/**
 * Class WooCommerce class
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomWooBookingAddon
 */
class ProductType {

	/**
	 * Build the instance
	 */
	public function __construct() {
		add_filter( 'product_type_selector', array( $this, 'add_type' ) );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_tab' ), 99 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'product_tab_content' ) );
		add_action( 'woocommerce_process_product_meta_zoom_meeting', array( $this, 'save_meta' ) );
		#add_action( 'woocommerce_single_product_summary', array( $this, 'template' ), 60 );
	}

	/**
	 * Advanced Type
	 *
	 * @param $types
	 *
	 * @return mixed
	 */
	public function add_type( $types ) {
		$types['zoom_meeting'] = __( 'Zoom Meeting', 'vczapi-woocommerce-addon' );

		return $types;
	}

	/**
	 * Remove unnecessary product tabs.
	 *
	 * @param array $tabs
	 *
	 * @return mixed
	 */
	public function add_product_tab( $tabs ) {
		if ( ! empty( $tabs['shipping']['class'] ) ) {
			array_push( $tabs['shipping']['class'], 'hide_if_zoom_meeting' );
		}
		if ( ! empty( $tabs['attribute']['class'] ) ) {
			array_push( $tabs['attribute']['class'], 'hide_if_zoom_meeting' );
		}
		if ( ! empty( $tabs['advanced']['class'] ) ) {
			array_push( $tabs['advanced']['class'], 'hide_if_zoom_meeting' );
		}

		$tabs['zoom_meeting'] = array(
			'label'  => __( 'Pricing', 'vczapi-woocommerce-addon' ),
			'target' => 'vczapi_zoom_pricings',
			'class'  => array(
				'show_if_zoom_meeting'
			),
		);

		return $tabs;
	}

	/**
	 * Render contents for the selected tab
	 */
	public function product_tab_content() {
		?>
        <div id='vczapi_zoom_pricings' class='panel woocommerce_options_panel'>
            <div class='options_group'>
				<?php
				woocommerce_wp_text_input( array(
					'id'          => '_vczapi_woo_addon_product_price',
					'label'       => __( 'Price', 'vczapi-woocommerce-addon' ),
					'default'     => '1',
					'desc_tip'    => 'true',
					'data_type'   => 'price',
					'description' => __( 'Enter Meeting Price.', 'vczapi-woocommerce-addon' ),
				) );
				?>
                <p>
                    <strong><?php _e( 'Please avoid creating any product with this product type because this will not create any zoom meetings.', 'vczapi-woocommerce-addon' ); ?></strong>
                </p>
            </div>
        </div>
		<?php
	}

	/**
	 * Save Meta Data
	 *
	 * @param $post_id
	 */
	public function save_meta( $post_id ) {
		$price = sanitize_text_field( filter_input( INPUT_POST, '_vczapi_woo_addon_product_price' ) );
		update_post_meta( $post_id, '_vczapi_woo_addon_product_price', $price );
	}

	/**
	 * Get Custom Template
	 */
	public function template() {
		global $product;
		if ( 'zoom_meeting' == $product->get_type() ) {
			$template_path = VZAPI_WOOCOMMERCE_ADDON_DIR_PATH . 'templates/';
			// Load the template
			wc_get_template( 'single-product/add-to-cart/zoom_meeting.php',
				'',
				'',
				trailingslashit( $template_path ) );
		}
	}
}