<?php
/**
 * Understrap Child Theme functions and definitions
 *
 * @package UnderstrapChild
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;



/**
 * Removes the parent themes stylesheet and scripts from inc/enqueue.php
 */
function understrap_remove_scripts() {
	wp_dequeue_style( 'understrap-styles' );
	wp_deregister_style( 'understrap-styles' );

	wp_dequeue_script( 'understrap-scripts' );
	wp_deregister_script( 'understrap-scripts' );
}
add_action( 'wp_enqueue_scripts', 'understrap_remove_scripts', 20 );



/**
 * Enqueue our stylesheet and javascript file
 */
function theme_enqueue_styles() {

	// Get the theme data.
	$the_theme = wp_get_theme();

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	// Grab asset urls.
	$theme_styles  = "/css/child-theme{$suffix}.css";
	$theme_scripts = "/js/child-theme{$suffix}.js";

	wp_enqueue_style( 'child-understrap-styles', get_stylesheet_directory_uri() . $theme_styles, array(), $the_theme->get( 'Version' ) );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'child-understrap-scripts', get_stylesheet_directory_uri() . $theme_scripts, array(), $the_theme->get( 'Version' ), true );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );



/**
 * Load the child theme's text domain
 */
function add_child_theme_textdomain() {
	load_child_theme_textdomain( 'understrap-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'add_child_theme_textdomain' );



/**
 * Overrides the theme_mod to default to Bootstrap 5
 *
 * This function uses the `theme_mod_{$name}` hook and
 * can be duplicated to override other theme settings.
 *
 * @param string $current_mod The current value of the theme_mod.
 * @return string
 */
function understrap_default_bootstrap_version( $current_mod ) {
	return 'bootstrap5';
}
add_filter( 'theme_mod_understrap_bootstrap_version', 'understrap_default_bootstrap_version', 20 );



/**
 * Loads javascript for showing customizer warning dialog.
 */
function understrap_child_customize_controls_js() {
	wp_enqueue_script(
		'understrap_child_customizer',
		get_stylesheet_directory_uri() . '/js/customizer-controls.js',
		array( 'customize-preview' ),
		'20130508',
		true
	);
}
add_action( 'customize_controls_enqueue_scripts', 'understrap_child_customize_controls_js' );

add_action('vczapi_html_after_meeting_details', 'html_after_meeting_details');
function html_after_meeting_details()
{
    global $zoom;
    if (!empty($zoom['api']->timezone)) {
        $post_meta = get_post_meta(get_the_ID());
?>
        <div class="dpn-zvc-sidebar-content-list vczapi-ages-wrap">
            <span><strong><?php _e('Възраст между', 'speed-dating-with-zoom'); ?>:</strong></span>
            <span class="vczapi-single-meeting-places-male">
                <?php echo $post_meta['min_ages'][0]; ?> и <?php echo $post_meta['max_ages'][0]; ?> години
            </span>
        </div>
        <div class="dpn-zvc-sidebar-content-list vczapi-places-wrap">
            <span><strong><?php _e('Налични места', 'speed-dating-with-zoom'); ?>:</strong></span>
            <span class="vczapi-single-meeting-places-male">
                <?php echo $post_meta['Male'][0]; ?> мъже - <?php echo $post_meta['Female'][0]; ?> жени
            </span>
        </div>
    <?php }
}

add_action('woocommerce_thankyou', 'custom_woocommerce_auto_complete_order');
function custom_woocommerce_auto_complete_order($order_id)
{
    if (!$order_id) {
        return;
    }

    global $wpdb;
    $order = wc_get_order($order_id);
    $order->update_status('completed');
}

add_filter('woocommerce_billing_fields', 'ts_unrequire_wc_phone_field');
function ts_unrequire_wc_phone_field($fields)
{
    $fields['billing_state']['required'] = false;
    $fields['billing_city']['required'] = false;
    return $fields;
}

add_filter('woocommerce_default_address_fields', 'override_default_address_fields', 999);

function override_default_address_fields($address_fields)
{
    $address_fields['state']['required'] = false;
    $address_fields['state']['label'] = "Град";
    $address_fields['state']['placeholder'] = "Град";
    return $address_fields;
}

add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields');
function custom_override_checkout_fields($fields)
{
    $fields['billing']['billing_first_name']['label'] = 'Име';
    $fields['billing']['billing_last_name']['label'] = 'Фамилия';
    $fields['billing']['billing_state']['label'] = 'Град';
    $fields['billing']['billing_city']['label'] = 'Град';
    $fields['billing']['billing_country']['label'] = 'Държава';
    $fields['billing']['billing_email']['label'] = 'Имейл';
    $fields['billing']['billing_phone']['label'] = 'Телефон';
    $fields['billing']['billing_first_name']['placeholder'] = 'Име';
    $fields['billing']['billing_last_name']['placeholder'] = 'Фамилия';
    $fields['billing']['billing_state']['placeholder'] = 'Град';
    $fields['billing']['billing_city']['placeholder'] = 'Град';
    $fields['billing']['billing_country']['placeholder'] = 'Държава';
    $fields['billing']['billing_email']['placeholder'] = 'Имейл';
    $fields['billing']['billing_phone']['placeholder'] = 'Телефон';

    $fields['shipping']['shipping_first_name']['label'] = 'Име';
    $fields['shipping']['shipping_last_name']['label'] = 'Фамилия';
    $fields['shipping']['shipping_state']['label'] = 'Град';
    $fields['shipping']['shipping_city']['label'] = 'Град';
    $fields['shipping']['shipping_country']['label'] = 'Държава';
    $fields['shipping']['shipping_first_name']['placeholder'] = 'Име';
    $fields['shipping']['shipping_last_name']['placeholder'] = 'Фамилия';
    $fields['shipping']['shipping_state']['placeholder'] = 'Град';
    $fields['shipping']['shipping_city']['placeholder'] = 'Град';
    $fields['shipping']['shipping_country']['placeholder'] = 'Държава';

    $fields['account']['account_username']['label'] = 'Потребителско име';
    $fields['account']['account_password']['label'] = 'Парола';
    $fields['account']['account_password-2']['label'] = 'Потвърди парола';
    $fields['account']['account_username']['placeholder'] = 'Потребителско име';
    $fields['account']['account_password']['placeholder'] = 'Парола';
    $fields['account']['account_password-2']['placeholder'] = 'Потвърди парола';

    $fields['order']['order_comments']['label'] = 'Бележка';
    $fields['order']['order_comments']['placeholder'] = 'Бележка';

    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_city']);

    unset($fields['shipping']['shipping_company']);
    unset($fields['shipping']['shipping_address_1']);
    unset($fields['shipping']['shipping_address_2']);
    unset($fields['shipping']['shipping_postcode']);
    unset($fields['shipping']['shipping_city']);

    return $fields;
}

// START - Meetings tab
add_filter('um_account_page_default_tabs_hook', 'meetings_tab_in_um', 100);
function meetings_tab_in_um($tabs)
{
    $tabs[800]['meetings']['icon'] = 'um-icon-calendar';
    $tabs[800]['meetings']['title'] = 'Срещи';
    $tabs[800]['meetings']['custom'] = true;
    return $tabs;
}

add_action('um_account_tab_meetings', 'um_account_tab_meetings');
function um_account_tab_meetings($info)
{
    global $ultimatemember;
    extract($info);

    $output = $ultimatemember->account->get_tab_output('meetings');
    if ($output) {
        echo $output;
    }
}

add_filter('um_account_content_hook_meetings', 'um_account_content_hook_meetings');
function um_account_content_hook_meetings($output)
{
    ob_start();
    ?>
    <div class="um-field">
        <!-- <link rel="stylesheet" href="../../wp-content/plugins/woocommerce/assets/css/woocommerce.css" type="text/css"> -->
        <style>
            .woocommerce-orders-table {
                border: 1px solid rgba(0, 0, 0, .1);
                margin: 0 -1px 24px 0;
                text-align: left;
                width: 100%;
                border-collapse: separate;
                border-radius: 5px;
            }

            .woocommerce-orders-table__cell {
                border-top: 1px solid rgba(0, 0, 0, .1);
                padding: 9px 12px;
                vertical-align: middle;
                line-height: 1.5em;
            }

            .woocommerce-orders-table__header {
                font-weight: 700;
                padding: 9px 12px;
                line-height: 1.5em;
            }

            .um-account-tab-meetings .um-col-alt {
                visibility: collapse;
            }
        </style>
        <?php include 'wp-content/plugins/vczapi-woocommerce-addon/templates/frontend/meeting-list.php'; ?>
    </div>
<?php
    $output .= ob_get_contents();
    ob_end_clean();
    return $output;
}
// END - Meetiings tab