<?php
/**
 * The Template for joining meeting via browser
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/join-web-browser.php.
 *
 * @package    Speed Dating with Zoom/Templates
 * @since      3.0.0
 * @modified   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $zoom;
global $current_user;

if ( video_conference_zoom_check_login() ) {
	if ( ! empty( $zoom['api']->state ) && $zoom['api']->state === "ended" ) {
		echo "<h3>" . __( 'Срещата беше завършена от домакинът.', 'speed-dating-with-zoom' ) . "</h3>";
		die;
	}

	/**
	 * Trigger before the content
	 */
	do_action( 'vczoom_jbh_before_content', $zoom );
	?>
    <div id="vczapi-zoom-browser-meeting" class="vczapi-zoom-browser-meeting-wrapper">
        <div id="vczapi-zoom-browser-meeting--container">
			<?php
			$bypass_notice = apply_filters( 'vczapi_api_bypass_notice', false );
			if ( ! $bypass_notice ) {
				?>
                <div class="vczapi-zoom-browser-meeting--info">
					<?php if ( ! is_ssl() ) { ?>
                        <p style="line-height: 1.5;">
                            <strong style="color:red;"><?php _e( '!!!ВНИМАНИЕ!!!: ', 'speed-dating-with-zoom' ); ?></strong><?php _e(
								'Браузърът не може да намери валиден SSL сертификат. Не можете да се включите във срещите без SSL сертификат. Моля инсталирайте сертификат, за да продължите.', 'speed-dating-with-zoom' ); ?>
                        </p>
					<?php } ?>
                    <div class="vczapi-zoom-browser-meeting--info__browser"></div>
                </div>
			<?php } ?>
            <form class="vczapi-zoom-browser-meeting--meeting-form" id="vczapi-zoom-browser-meeting-join-form" action="">
				<?php $full_name = ! empty( $current_user->first_name ) ? $current_user->first_name . ' ' . $current_user->last_name : $current_user->display_name; ?>
                <div class="form-group">
                    <input type="text" name="display_name" id="vczapi-jvb-display-name" value="<?php echo esc_attr( $full_name ); ?>" placeholder="Името ти тук" class="form-control" required>
                </div>
				<?php
				$hide_email = get_option( 'zoom_api_hide_in_jvb' );
				if ( empty( $hide_email ) ) {
					if ( ! empty( $current_user ) && ! empty( $current_user->user_email ) ) {
						?>
                        <input type="hidden" name="display_email" id="vczapi-jvb-email" value="<?php echo esc_attr($current_user->user_email); ?>">
						<?php
					} else {
						?>
                        <div class="form-group">
                            <input type="email" name="display_email" id="vczapi-jvb-email" value="<?php echo esc_attr($current_user->user_email); ?>" placeholder="Имейла ти тук" class="form-control">
                        </div>
					<?php }
				}

				if ( ! isset( $_GET['pak'] ) && ! empty( $zoom['password'] ) ) { ?>
                    <div class="form-group">
                        <input type="password" name="meeting_password" id="meeting_password" value="" placeholder="Парола" class="form-control" required>
                    </div>
					<?php
				}

				$bypass_lang = apply_filters( 'vczapi_api_bypass_lang', false );
				if ( ! $bypass_lang ) {
					$default_jvb_lang = get_option( 'zoom_api_default_lang_jvb' );
					if ( ! empty( $default_jvb_lang ) && $default_jvb_lang !== "all" ) {
						?>
                        <input name="meeting-lang" id="meeting_lang" type="hidden" value="<?php echo esc_html( $default_jvb_lang ); ?>">
						<?php
					} else {
						?>
                        <div class="form-group">
                            <select id="meeting_lang" name="meeting-lang" class="form-control">
                                <option value="en-US">English</option>
                                <option value="de-DE">German Deutsch</option>
                                <option value="es-ES">Spanish Español</option>
                                <option value="fr-FR">French Français</option>
                                <option value="jp-JP">Japanese 日本語</option>
                                <option value="pt-PT">Portuguese Portuguese</option>
                                <option value="ru-RU">Russian Русский</option>
                                <option value="zh-CN">Chinese 简体中文</option>
                                <option value="zh-TW">Chinese 繁体中文</option>
                                <option value="ko-KO">Korean 한국어</option>
                                <option value="vi-VN">Vietnamese Tiếng Việt</option>
                                <option value="it-IT">Italian italiano</option>
                            </select>
                        </div>
						<?php
					}
				}
				?>
                <button type="submit" class="btn btn-primary" id="vczapi-zoom-browser-meeting-join-mtg">
					<?php _e( 'Влез', 'speed-dating-with-zoom' ); ?>
                </button>
            </form>
        </div>
    </div>
	<?php
	/**
	 * Trigger before the content
	 */
	do_action( 'vczoom_jbh_after_content' );
} else {
	echo "<h3>" . __( 'Нямате права, за да видите страницата. Ако искате да продължите, свържете се със Администратора.', 'speed-dating-with-zoom' ) . "</h3>";
	die;
}
