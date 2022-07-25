<?php

namespace Codemanas\ZoomWooCommerceAddon;

class Helper {

	public static function is_plugin_active( $plugin ) {
		$active = false;
		// check for plugin using plugin name
		if ( in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$active = true;
		}

		return $active;
	}

	public static function check_is_booking_addon_active() {
		return self::is_plugin_active( 'vczapi-woo-addon/vczapi-woo-addon.php' );
	}

	/**
	 * Set Cache Helper
	 *
	 * @param $user_id
	 * @param $key
	 * @param $value
	 * @param bool $time_in_secods
	 *
	 * @return bool
	 */
	public static function set_user_cache( $user_id, $key, $value, $time_in_secods = false ) {
		if ( ! $user_id ) {
			return false;
		}
		update_user_meta( $user_id, $key, $value );
		update_user_meta( $user_id, $key . '_expiry_time', time() + $time_in_secods );
	}

	/**
	 * Get Cache Data
	 *
	 * @param $user_id
	 * @param $key
	 *
	 * @return bool|mixed
	 */
	public static function get_user_cache( $user_id, $key ) {
		$expiry = get_user_meta( $user_id, $key . '_expiry_time', true );
		if ( ! empty( $expiry ) && $expiry > time() ) {
			return get_user_meta( $user_id, $key, true );
		} else {
			update_user_meta( $user_id, $key, '' );
			update_user_meta( $user_id, $key . '_expiry_time', '' );

			return false;
		}
	}
}