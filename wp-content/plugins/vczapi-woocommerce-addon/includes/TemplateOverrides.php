<?php

namespace Codemanas\ZoomWooCommerceAddon;

/**
 * Class TemplateOverrides
 *
 * Helps overriding default Woo Templates
 *
 * @author Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @package Codemanas\ZoomWooCommerceAddon
 * @since 1.0.0
 */
class TemplateOverrides {

	/**
	 * Get desired template
	 *
	 * @param $template_names
	 * @param bool $load
	 * @param bool $require_once
	 *
	 * @return bool|string
	 */
	static function get_template( $template_names, $load = false, $require_once = true ) {
		if ( ! is_array( $template_names ) ) {
			return '';
		}

		$located         = false;
		$this_plugin_dir = VZAPI_WOOCOMMERCE_ADDON_DIR_PATH;
		foreach ( $template_names as $template_name ) {
			if ( file_exists( STYLESHEETPATH . '/zoom-woocommerce-addon/' . $template_name ) ) {
				$located = STYLESHEETPATH . '/zoom-woocommerce-addon/' . $template_name;
				break;
			} elseif ( file_exists( TEMPLATEPATH . '/zoom-woocommerce-addon/' . $template_name ) ) {
				$located = TEMPLATEPATH . '/zoom-woocommerce-addon/' . $template_name;
				break;
			} elseif ( file_exists( $this_plugin_dir . 'templates/' . $template_name ) ) {
				$located = $this_plugin_dir . 'templates/' . $template_name;
				break;
			}
		}

		if ( $load && ! empty( $located ) ) {
			load_template( $located, $require_once );
		}

		return $located;
	}
}