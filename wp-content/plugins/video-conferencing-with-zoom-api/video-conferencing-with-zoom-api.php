<?php

/**
 * @link              https://www.takerman.net
 * @since             1.0.0
 * @package           Speed Dating with Zoom
 *
 * Plugin Name:       Speed Dating with Zoom
 * Plugin URI:        https://wordpress.org/plugins/speed-dating-with-zoom/
 * Description:       Speed Dating with Zoom Meetings and Webinars plugin provides you with great functionality of managing Zoom meetings, Webinar scheduling options, and users directly from your WordPress dashboard.
 * Version:           3.8.17
 * Author:            Tanyo Ivanov
 * Author URI:        https://www.tanyoivanov.net
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       speed-dating-with-zoom
 * Requires PHP:      7.0
 * Domain Path:       /languages
 * Requires at least: 5.5.0
 */

global $jal_db_version;
$jal_db_version = '3.8.18';

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
	exit;
}

define('ZVC_PLUGIN_FILE', __FILE__);
define('ZVC_PLUGIN_SLUG', 'speed-dating-with-zoom');
define('ZVC_PLUGIN_VERSION', '3.8.17');
define('ZVC_ZOOM_WEBSDK_VERSION', '2.0.1');
define('ZVC_PLUGIN_AUTHOR', 'https://tanyoivanov.net');
define('ZVC_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
define('ZVC_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
define('ZVC_PLUGIN_ADMIN_ASSETS_URL', ZVC_PLUGIN_DIR_URL . 'assets/admin');
define('ZVC_PLUGIN_PUBLIC_ASSETS_URL', ZVC_PLUGIN_DIR_URL . 'assets/public');
define('ZVC_PLUGIN_VENDOR_ASSETS_URL', ZVC_PLUGIN_DIR_URL . 'assets/vendor');
define('ZVC_PLUGIN_VIEWS_PATH', ZVC_PLUGIN_DIR_PATH . 'includes/views');
define('ZVC_PLUGIN_INCLUDES_PATH', ZVC_PLUGIN_DIR_PATH . 'includes');
define('ZVC_PLUGIN_IMAGES_PATH', ZVC_PLUGIN_DIR_URL . 'assets/images');
define('ZVC_PLUGIN_LANGUAGE_PATH', trailingslashit(basename(ZVC_PLUGIN_DIR_PATH)) . 'languages/');
define('ZVC_PLUGIN_ABS_NAME', plugin_basename(__FILE__));

// the main plugin class
require_once ZVC_PLUGIN_INCLUDES_PATH . '/Bootstrap.php';

add_action('plugins_loaded', 'Codemanas\VczApi\Bootstrap::instance', 99);
register_activation_hook(__FILE__, 'Codemanas\VczApi\Bootstrap::activate');
register_deactivation_hook(__FILE__, 'Codemanas\VczApi\Bootstrap::deactivate');
register_activation_hook(__FILE__, 'jal_install');
register_activation_hook(__FILE__, 'jal_install_data');

function jal_install()
{
	global $wpdb;
	global $jal_db_version;
	$charset_collate = $wpdb->get_charset_collate();

	// Create the user_choice table
	$tbl_user_choices = $wpdb->prefix . "tak_user_choices";
	$user_choices_sql = "CREATE TABLE $tbl_user_choices (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		logged_user_id mediumint(9) NOT NULL,
		choice_user_id mediumint(9) NOT NULL,
		choice_id mediumint(9) NOT NULL,
		meeting_id mediumint(9) NOT NULL,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	// Create the choice types table
	$tbl_choice_types = $wpdb->prefix . "tak_choice_types";
	$choice_types_sql = "CREATE TABLE $tbl_choice_types (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		value text NOT NULL,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($user_choices_sql);
	dbDelta($choice_types_sql);
	add_option('jal_db_version', $jal_db_version);
}

function jal_install_data()
{
	global $wpdb;
	$tbl_choice_types = $wpdb->prefix . "tak_choice_types";

	if ($wpdb->get_var('SELECT count(*) FROM lkd_tak_choice_types WHERE id=1') == 0) {
		$wpdb->insert($tbl_choice_types, array('value' => 'Yes', 'time' => current_time('mysql')));
	}

	if ($wpdb->get_var('SELECT count(*) FROM lkd_tak_choice_types WHERE id=2') == 0) {
		$wpdb->insert($tbl_choice_types, array('value' => 'No', 'time' => current_time('mysql')));
	}

	if ($wpdb->get_var('SELECT count(*) FROM lkd_tak_choice_types WHERE id=3') == 0) {
		$wpdb->insert($tbl_choice_types, array('value' => 'Friend', 'time' => current_time('mysql')));
	}
}

function speed_dating_update_db_check()
{
	global $jal_db_version;
	if (get_site_option('jal_db_version') != $jal_db_version) {
		jal_install();
	}
	update_option("jal_db_version", $jal_db_version);
}
add_action('plugins_loaded', 'speed_dating_update_db_check');
