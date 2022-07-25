<?php

namespace Codemanas\ZoomWooCommerceAddon;

/**
 * Class ProductVendors
 *
 * Requires FREE version 3.3.2 or above
 *
 * @since 2.1.0
 * @package Codemanas\ZoomWooCommerceAddon
 */
class ProductVendors {

	/**
	 * Hold my message
	 * @var $message
	 */
	public static $message;

	/**
	 * Instance holder
	 * @var null
	 */
	public static $instance = null;

	/**
	 * @return ProductVendors|null
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private $zoom_users = false;

	/**
	 * ProductVendors constructor.
	 * @since 2.1.0
	 */
	public function __construct() {
		$allow_vendor_to_create_meetings = apply_filters( 'vczapi_wcpv_allow_vendor_to_create_meetings', true );
		if ( $allow_vendor_to_create_meetings ) {
			//Reference: https://3.7designs.co/blog/2014/08/restricting-access-to-custom-post-types-using-roles-in-wordpress/
			//http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types
			add_filter( 'vczapi_cpt_capabilities_type', [ $this, 'change_product_capability_type' ] );
			add_filter( 'vczapi_cpt_menu_position', [ $this, 'change_menu_position' ] );
			add_filter( 'vczapi_cpt_meta_cap', [ $this, 'map_meta_cap' ] );

//			add_action( 'show_user_profile', [ $this, 'user_profile_fields' ] );
			add_action( 'edit_user_profile', [ $this, 'user_profile_fields' ] );
			add_action( 'personal_options_update', [ $this, 'save_profile_fields' ] );
			add_action( 'edit_user_profile_update', [ $this, 'save_profile_fields' ] );

			add_filter( 'vczapi_before_fields_admin', [ $this, 'meeting_host' ] );

			add_action( 'admin_menu', [ $this, 'menu' ] );
		}
	}

	/**
	 * Change Capability Type
	 * @return string
	 * @since 2.1.0
	 */
	public function change_product_capability_type() {
		//Reference: https://3.7designs.co/blog/2014/08/restricting-access-to-custom-post-types-using-roles-in-wordpress/
		return 'product';
	}

	/**
	 * Change Menu position
	 * @return int
	 * @since 2.1.0
	 */
	public function change_menu_position() {
		return 10;
	}

	public function map_meta_cap() {
		return true;
	}

	/**
	 * Get Zoom Users
	 *
	 * @return bool|array
	 */
	private function get_zoom_users() {
		$this->zoom_users = video_conferencing_zoom_api_get_user_transients();

		return $this->zoom_users;
	}

	/**
	 * Add Zoom Fields to user
	 *
	 * @param $user
	 */
	public function user_profile_fields( $user ) {
		$vendor = \WC_Product_Vendors_Utils::is_vendor( $user->ID );
		//Check if user has role of vendor
		if ( $vendor ) {
			$zoom_usrs   = $this->get_zoom_users();
			$user_hostID = get_user_meta( $user->ID, 'user_zoom_hostid', true );
			?>
            <h3><?php _e( "Zoom Details", "vczapi-woocommerce-addon" ); ?></h3>
            <table class="form-table">
                <tr>
                    <th><label for="user-profile-zoom-host-id"><?php _e( "Link Zoom Account", "vczapi-woocommerce-addon" ); ?></label></th>
                    <td>
						<?php if ( ! empty( $zoom_usrs ) ) { ?>
                            <select class="wc-search-zoom-users" name="zoom_host_id" style="width:300px;">
                                <option value=""><?php _e( 'Not a Host', 'vczapi-woocommerce-addon' ); ?></option>
								<?php foreach ( $zoom_usrs as $zoom_usr ) { ?>
                                    <option value="<?php echo $zoom_usr->id; ?>" <?php ! empty( $user_hostID ) ? selected( $user_hostID, $zoom_usr->id ) : false; ?>><?php echo ! empty( $zoom_usr->first_name ) ? $zoom_usr->first_name . ' ' . $zoom_usr->last_name : $zoom_usr->email; ?></option>
								<?php } ?>
                            </select>
						<?php } else {
							esc_html_e( 'No host ID found. Might be your API connection with Zoom is not correct ? Correct it from Zoom Meetings > Settings page.', 'vczapi-woocommerce-addon' );
						} ?>
                        <p class="description"><?php esc_html_e( 'This host is related to Zoom Account. So, any meetings created by this vendor will be linked to the assigned host in Zoom.', 'vczapi-woocommerce-addon' ); ?></p>
                    </td>
                </tr>
            </table>
			<?php
		}
	}

	/**
	 * Save
	 *
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function save_profile_fields( $user_id ) {
		$vendor = \WC_Product_Vendors_Utils::is_vendor( $user_id );
		if ( ! $vendor ) {
			return false;
		}

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		$zoom_host_id = filter_input( INPUT_POST, 'zoom_host_id' );
		update_user_meta( $user_id, 'user_zoom_hostid', $zoom_host_id );
	}

	/**
	 * Choose a Meeting HOST
	 *
	 * @param $post
	 *
	 * @return bool
	 */
	public function meeting_host( $post ) {
		global $current_screen;

		if ( $current_screen->id === "zoom-meetings" && $current_screen->post_type === "zoom-meetings" ) {
			$user_id = get_current_user_id();
			$vendor  = \WC_Product_Vendors_Utils::is_vendor( $user_id );
			if ( ! $vendor ) {
				return false;
			}

			$host_id = get_user_meta( $user_id, 'user_zoom_hostid', true );
			if ( ! empty( $host_id ) ) {
				add_filter( 'vczapi_admin_show_alternative_host_selection', [ $this, 'hide_host_selection' ] );
				add_filter( 'vczapi_admin_show_host_selection', [ $this, 'hide_host_selection' ] );
				echo '<input type="hidden" name="userId" value="' . esc_attr( $host_id ) . '">';
			}
		}
	}

	/**
	 * Disable host selection for vendors
	 * @return bool
	 */
	public function hide_host_selection() {
		return false;
	}

	/**
	 * Show Menu
	 */
	public function menu() {
		add_submenu_page( 'edit.php?post_type=product', __( 'Zoom Vendors', 'vczapi-woocommerce-addon' ), __( 'Zoom Vendors', 'vczapi-woocommerce-addon' ), 'manage_options', 'zoom-vendors-page', array(
			$this,
			'zoom_vendor_relation'
		) );
	}

	/**
	 * Zoom Vendor relationship menu
	 */
	public function zoom_vendor_relation() {
		$zoom_usrs = $this->get_zoom_users();
		$this->save_zoom_vendor_relation();
		?>
        <div class="wrap">
            <h2><?php _e( "Assign Host ID", "vczapi-woocommerce-addon" ); ?></h2>
            <div class="message">
				<?php
				$message = self::get_message();
				if ( isset( $message ) && ! empty( $message ) ) {
					echo $message;
				}
				?>
            </div>
            <div class="notice notice-warning">
                <p><?php _e( 'This section allows you to link your "Zoom" users to your Vendors. After you assign a Vendor with Zoom Account. Vendor won\'t have the option to select a host when creating a meeting.', 'vczapi-woocommerce-addon' ); ?></p>
            </div>

            <form action="" method="POST">
				<?php wp_nonce_field( '_save_vendors_zoom_act', '_save_vendors_zoom' ); ?>
                <table id="vczapi-wc-vendors-zoom-users" class="wp-list-table widefat fixed striped posts">
                    <thead>
                    <tr>
                        <th style="text-align: left;"><?php _e( 'Email', 'vczapi-woocommerce-addon' ); ?></th>
                        <th style="text-align: left;"><?php _e( 'Name', 'vczapi-woocommerce-addon' ); ?></th>
                        <th width="30%" style="text-align: left;"><?php _e( 'Host ID', 'vczapi-woocommerce-addon' ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php
					$paged         = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
					$wp_user_query = new \WP_User_Query( array(
						'number'   => 20,
						'paged'    => $paged,
						'role__in' => array(
							'wc_product_vendors_manager_vendor',
							'wc_product_vendors_admin_vendor',
							'wc_product_vendors_pending_vendor'
						)
					) );
					$users         = $wp_user_query->get_results();
					$max_number    = count( $users );
					if ( ! empty( $users ) ) {
						foreach ( $users as $user ) {
							$user_hostID = get_user_meta( $user->ID, 'user_zoom_hostid', true );
							?>
                            <tr>
                                <td><?php echo $user->user_email; ?></td>
                                <td><?php echo empty( $user->first_name ) ? $user->display_name : $user->first_name . ' ' . $user->last_name; ?></td>
                                <td>
									<?php if ( ! empty( $zoom_usrs ) ) { ?>
                                        <select class="wc-search-zoom-users" name="zoom_host_id[<?php echo $user->ID; ?>]" style="width:300px;">
                                            <option value=""><?php _e( 'Not a Host', 'vczapi-woocommerce-addon' ); ?></option>
											<?php foreach ( $zoom_usrs as $zoom_usr ) { ?>
                                                <option value="<?php echo $zoom_usr->id; ?>" <?php ! empty( $user_hostID ) ? selected( $user_hostID, $zoom_usr->id ) : false; ?>><?php echo ! empty( $zoom_usr->first_name ) ? $zoom_usr->first_name . ' ' . $zoom_usr->last_name : $zoom_usr->email; ?></option>
											<?php } ?>
                                        </select>
									<?php } else {
										esc_html_e( 'No host ID found. Might be your API connection with Zoom is not correct ? Correct it from Zoom Meetings > Settings page.', 'vczapi-woocommerce-addon' );
									} ?>
                                </td>
                            </tr>
							<?php
						}
					} ?>
                    </tbody>
                </table>

                <div class="tablenav bottom">
                    <div class="tablenav-pages">
						<?php
						if ( $max_number > 20 ) {
							echo paginate_links( array(
								'base'    => add_query_arg( 'paged', '%#%' ),
								'format'  => '?paged=%#%',
								'total'   => $wp_user_query->total_users,
								'current' => max( 1, $paged ),
							) );
						}
						?>
                    </div>
                </div>
                <p class="submit"><input type="submit" name="saving_host_id" class="button button-primary" value="Save"></p>
            </form>
        </div>
		<?php
	}

	/**
	 * Save Zoom Vendor Relation
	 */
	public function save_zoom_vendor_relation() {
		if ( ! isset( $_POST['saving_host_id'] ) ) {
			return;
		}

		check_admin_referer( '_save_vendors_zoom_act', '_save_vendors_zoom' );
		$host_ids = filter_input( INPUT_POST, 'zoom_host_id', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		foreach ( $host_ids as $k => $host_id ) {
			update_user_meta( $k, 'user_zoom_hostid', $host_id );
		}

		self::set_message( 'updated', __( "Saved !", "vczapi-woocommerce-addon" ) );
	}

	static function get_message() {
		return self::$message;
	}

	static function set_message( $class, $message ) {
		self::$message = '<div class=' . $class . '><p>' . $message . '</p></div>';
	}
}