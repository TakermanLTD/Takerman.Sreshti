<?php

namespace Codemanas\ZoomWooCommerceAddon\Admin;

use Codemanas\ZoomWooCommerceAddon\Admin\Activator as Activator;
use Codemanas\ZoomWooCommerceAddon\Helper;

/**
 * Class VideoConferencingZoomSettings
 *
 * @since   1.0.0
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @package Codemanas\ZoomWooCommerceAddon\Admin
 */
class Settings {

	private $settings_page_hook;
	private $email_reminder_times = [];
	private $booking_addon_active = false;

	public function __construct() {
		$this->booking_addon_active = Helper::check_is_booking_addon_active();

		$this->email_reminder_times = [
			'24_hours_before' => __( '24 hours before meeting', 'vczapi-woocommerce-addon' ),
			'3_hours_before'  => __( '3 hours before meeting', 'vczapi-woocommerce-addon' )
		];

		if ( ! $this->booking_addon_active ) {
			add_action( 'admin_init', array( $this, 'save_plugin_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'admin_menu', array( $this, 'submenu' ) );
			add_action( 'vczapi_admin_settings', array( $this, 'render_tabs' ) );
		} else {
			//when Bookings addon is active show the license tab here
			//all other settings have been overwritten via booking plugin
			add_action( 'vzczpi-woo-addon-settings-content', [ $this, 'add_license_form' ] );
		}


		add_filter( 'plugin_action_links', array( $this, 'action_link' ), 10, 2 );
	}

	public function add_license_form( $active_tab ) {
		if ( $active_tab == 'license' ) {
			$activate = new Activator();
			$activate->show_license_form( 'Zoom for WooCommerece Integration' );
		}

	}

	/**
	 * @param $current_value
	 * @param $saved_array
	 *
	 * @return bool|string
	 */
	public function checked_in_array( $current_value, $saved_array ) {

		if ( ! is_array( $saved_array ) ) {
			return false;
		}

		if ( in_array( $current_value, $saved_array ) ) {
			return 'checked="checked"';
		}

		return false;
	}

	/**
	 * Save Plugin Settings
	 */
	public function save_plugin_settings() {
		if ( isset( $_POST['vczapi_save_email_field'] ) ) {
			$email_settings_nonce = filter_input( INPUT_POST, 'vczapi_email_settings_nonce' );
			if ( ! wp_verify_nonce( $email_settings_nonce, 'vczapi_verify_email_settings' ) ) {
				return;
			}
			$disable_email_reminder = filter_input( INPUT_POST, 'vczapi_disable_meeting_reminder_email' );
			$reminder_when          = filter_input( INPUT_POST, 'meeting-reminder-time', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$data                   = array(
				'disable_reminder' => $disable_email_reminder,
				'email_schedule'   => $reminder_when,
				'enable_log'       => filter_input( INPUT_POST, 'vczapi-enable-debug-log' )
			);
			update_option( 'vczapi_meeting_reminder_email_settings', $data );
		}

		if ( isset( $_POST['vczapi_save_general_field'] ) ) {
			update_option( '_vczapi_woocommerce_disable_browser_join', sanitize_text_field( filter_input( INPUT_POST, 'vczapi_disable_browser_join_links' ) ) );
			$hide_purchased_recordings = filter_input( INPUT_POST, 'vczapi_wc_disable_purchased_recordings' );
			update_option( 'vczapi_wc_hide_purchased_recordings', sanitize_text_field( $hide_purchased_recordings ) );
			$hide_completed_meetings = filter_input( INPUT_POST, 'vczapi_wc_hide_completed_meetings' );
			update_option( 'vczapi_wc_hide_completed_meetings', sanitize_text_field( $hide_completed_meetings ) );
		}
	}

	/**
	 * Render Tab Contents
	 */
	public function render_tabs() {
		$tab        = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
		$active_tab = isset( $tab ) ? $tab : 'general';
		?>
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo add_query_arg( array( 'tab' => 'general' ) ); ?>" class="nav-tab <?php echo ( 'general' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
				<?php esc_html_e( 'General', 'vczapi-woocommerce-addon' ); ?>
            </a>
            <a href="<?php echo add_query_arg( array( 'tab' => 'documentation' ) ); ?>" class="nav-tab <?php echo ( 'documentation' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
				<?php esc_html_e( 'Documentation', 'vczapi-woocommerce-addon' ); ?>
            </a>
            <a href="<?php echo add_query_arg( array( 'tab' => 'license' ) ); ?>" class="nav-tab <?php echo ( 'license' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
				<?php esc_html_e( 'Licensing', 'vczapi-woocommerce-addon' ); ?>
            </a>
            <a href="<?php echo add_query_arg( array( 'tab' => 'email' ) ); ?>" class="nav-tab <?php echo ( 'email' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
				<?php esc_html_e( 'Email', 'vczapi-woocommerce-addon' ); ?>
            </a>

        </h2>
		<?php

		if ( 'general' === $active_tab ) {
			$join_via_browser          = get_option( '_vczapi_woocommerce_disable_browser_join' );
			$hide_purchased_recordings = get_option( 'vczapi_wc_hide_purchased_recordings' );
			$hide_completed_meetings   = get_option( 'vczapi_wc_hide_completed_meetings' );
			?>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th>
                            <label for="vczapi_disable_browser_join_links">
								<?php _e( 'Disable Browser Join Links', 'vczapi-woocommerce-addon' ); ?>
                            </label>
                        </th>
                        <td>
                            <input type="checkbox" name="vczapi_disable_browser_join_links" id="vczapi_disable_browser_join_links" <?php checked( 'on', $join_via_browser ) ?>>
                            <span class="description">
		                        <?php _e( 'Check this box if you want to disable join via browser links in your email and checkout pages.', 'vczapi-woocommerce-addon' ); ?>
	                        </span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="vczapi_wc_disable_purchased_recordings">
								<?php _e( 'Hide Purchased Recordings', 'vczapi-woocommerce-addon' ); ?>
                            </label>
                        </th>
                        <td>
                            <input type="checkbox" name="vczapi_wc_disable_purchased_recordings" id="vczapi_wc_disable_purchased_recordings" <?php checked( 'on', $hide_purchased_recordings ) ?>>
                            <span class="description">
		                        <?php _e( 'This option hides recordings list from my-account section in frontend. Recordings are show when a meeting is purchased and has recordings related to that meeting.', 'vczapi-woocommerce-addon' ); ?>
	                        </span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="vczapi_wc_hide_completed_meetings">
								<?php _e( 'Hide Completed Meeting Links', 'vczapi-woocommerce-addon' ); ?>
                            </label>
                        </th>
                        <td>
                            <input type="checkbox" name="vczapi_wc_hide_completed_meetings" id="vczapi_wc_hide_completed_meetings" <?php checked( 'on', $hide_completed_meetings ) ?>>
                            <span class="description">
		                        <?php _e( 'This option hides Meeting Links from my accounts > meetings and orders, one day after the meeting has completed/was scheduled to be held.', 'vczapi-woocommerce-addon' ); ?>
	                        </span>
                        </td>
                    </tr>
                </table>
                <input title="Save Form" type="submit" class="button button-primary button-large" name="vczapi_save_general_field" value="<?php _e( 'Save', 'vczapi-woocommerce-addon' ) ?>">
            </form>
			<?php
		} else if ( 'documentation' === $active_tab ) {
			?>
            <section class="vczapi-shortcode-section">
                <h3>Basic Documentation</h3>

                <p>For more information, please visit this page <a href="https://zoom.codemanas.com/woocommerce/" target="_blank">here.</a></p>

                <div class="vczapi-shortcode-body-section">
                    <h3>Overriding Meeting Cronjob Emails:</h3>
                    <p>These are the emails which are sent when a meeting is purchased from a single meeting page. Customer and Admin both will
                        receive cron emails before 24 hours of the meeting time. You can easily overwrite this email from your themes folder.</p>
                    <ol>
                        <li><strong>3 hours cron Host Email:</strong> Copy from vczapi-woo-addon/templates/emails/html/host-invite-threehours.html to
                            yourtheme/zoom-woocommerce-addon/emails/html/host-invite-threehours.html
                        </li>
                        <li><strong>24 hour cron Host Email:</strong> Copy from
                            vczapi-woo-addon/templates/emails/html/host-invite-twentyfourhours.html to
                            yourtheme/zoom-woocommerce-addon/emails/html/host-invite-twentyfourhours.html
                        </li>
                        <li><strong>3 hours cron Customer Email:</strong> Copy from
                            vczapi-woo-addon/templates/emails/html/meeting-invite-threehours.html to
                            yourtheme/zoom-woocommerce-addon/emails/html/meeting-invite-threehours.html
                        </li>
                        <li><strong>24 hours cron Customer Email:</strong> Copy from
                            vczapi-woo-addon/templates/emails/html/meeting-invite-twentyfourhours.html to
                            yourtheme/zoom-woocommerce-addon/emails/html/meeting-invite-twentyfourhours.html
                        </li>
                    </ol>
                </div>
            </section>
			<?php
		} else if ( 'license' == $active_tab ) {
			$activate = new Activator();
			$activate->show_license_form();
		} else if ( 'email' == $active_tab ) {
			$email_settings = get_option( 'vczapi_meeting_reminder_email_settings' );
			$email_settings = ! empty( $email_settings )
				? $email_settings
				: [
					'disable_reminder' => false,
					'email_schedule'   => [ '24_hours_before' ],
					'enable_log'       => null
				];
			if ( empty ( $email_settings['email_schedule'] ) ) {
				$email_settings['email_schedule'] = [ '24_hours_before' ];
			}
			?>
            <form method="post" action="">
				<?php wp_nonce_field( 'vczapi_verify_email_settings', 'vczapi_email_settings_nonce' ) ?>
                <table class="form-table">
                    <tr>
                        <th>
                            <label for="vczapi_disable_meeting_reminder_email">
								<?php _e( 'Disable Meeting Reminder', 'vczapi-woocommerce-addon' ); ?>
                            </label>
                        </th>
                        <td>
                            <input type="checkbox" name="vczapi_disable_meeting_reminder_email" id="vczapi_disable_meeting_reminder_email" <?php checked( 'on', $email_settings['disable_reminder'], true ) ?>>
                            <span class="description">
		                        <?php _e( 'Check this box to disable Meeting Reminder, by default the e-mail will be sent 24 hours before the meeting', 'vczapi-woocommerce-addon' ); ?>
	                        </span>
                        </td>
                    </tr>
                    <tr id="meeting-reminder-time-section" style="<?php echo ( $email_settings['disable_reminder'] == 'on' ) ? 'display:none;' : '' ?>">
                        <th><?php _e( 'Meeting Reminder Email Schedule', 'vczapi-woocommerce-addon' ); ?></th>
                        <td>
							<?php
							/* @todo sync this with cron so the meeting defined via filter work as expected
							 * at present it wont work
							 */
							$meeting_schedules = apply_filters( 'vczapi_wc_email_meeting_reminder_times', $this->email_reminder_times );
							foreach ( $meeting_schedules as $key => $label ):
								?>
                                <label>
                                    <input type="checkbox" name="meeting-reminder-time[]" id="meeting-reminder-time[<?php echo $key; ?> ]" value="<?php echo $key; ?>" <?php echo $this->checked_in_array( $key, $email_settings['email_schedule'] ) ?>>
									<?php echo $label; ?>
                                </label><br>
							<?php
							endforeach;
							?>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="vczapi-enable-debug-log"><?php _e( 'Enable Mail Debug Log', 'vczapi-woocommerce-addons' ); ?></label></th>
                        <td>
                            <input id="vczapi-enable-debug-log" name="vczapi-enable-debug-log" type="checkbox" <?php if ( isset( $email_settings['enable_log'] ) ) {
								checked( 'on', $email_settings['enable_log'] );
							}
							?>></td>
                    </tr>
                </table>
                <input title="Save Form" type="submit" name="vczapi_save_email_field" class="button button-primary button-large" value="<?php _e( 'Save', 'vczapi-woocommerce-addon' ) ?>">
            </form>
			<?php
		}
	}

	/**
	 * Add Sub menu to main menu
	 */
	public function submenu() {
		$this->settings_page_hook = add_submenu_page( 'edit.php?post_type=zoom-meetings', __( 'WooCommerce', 'vczapi-woocommerce-addon' ), __( 'WooCommerce', 'vczapi-woocommerce-addon' ), 'manage_options', 'woocommerce', array(
			$this,
			'woocommerce_options_render'
		) );
	}

	/**
	 * Render WooCommerce settings page
	 */
	public function woocommerce_options_render() {
		?>
        <div class="wrap">
            <h1><?php _e( "WooCommerce Settings", "vczapi-woo-addon" ); ?></h1>
			<?php do_action( 'vczapi_admin_settings' ); ?>
        </div>
		<?php
	}

	/**
	 * Enqueue Admin Scripts
	 *
	 * @param $hook
	 */
	public function scripts( $hook ) {
		global $current_screen;
		if ( $hook === "toplevel_page_woocommerce" || $current_screen->id === "product" || $current_screen->id === "zoom-meetings" ) {
			wp_enqueue_style( 'vczapi-wooaddon-style', VZAPI_WOOCOMMERCE_ADDON_DIR_URI . 'assets/backend/css/style.min.css', false, '1.0.0' );
		}

		if ( $current_screen->id === "zoom-meetings" ) {
			wp_enqueue_script( 'vczapi-wooaddon-script', VZAPI_WOOCOMMERCE_ADDON_DIR_URI . 'assets/backend/js/script.min.js', array( 'jquery' ), '1.0.0', true );
		}

		if ( $hook == $this->settings_page_hook ) {
			$tab = filter_input( INPUT_GET, 'tab' );
			if ( $tab == 'email' ) {
				wp_enqueue_script( 'vczapi-wc-settings-js', VZAPI_WOOCOMMERCE_ADDON_DIR_URI . 'assets/backend/js/woo-admin-settings.js', [ 'jquery' ], '1.0.0', true );
			}
		}
	}

	/**
	 * Show settings menu in plugin page.
	 *
	 * @param $actions
	 * @param $plugin_file
	 *
	 * @return array
	 */
	function action_link( $actions, $plugin_file ) {
		if ( 'vczapi-woocommerce-addon/vczapi-woocommerce-addon.php' == $plugin_file ) {
			$settings = array( 'settings' => '<a href="' . admin_url( 'edit.php?post_type=zoom-meetings&page=woocommerce' ) . '">' . __( 'Configure', 'vczapi-woocommerce-addon' ) . '</a>' );

			$actions = array_merge( $settings, $actions );
		}

		return $actions;
	}
}