<?php

/**
 * The template for displaying content of archive page meetings
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/content-meeting.php.
 *
 * @author Deepen
 * @since 3.0.0
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $zoom;

if (!vczapi_pro_version_active() && vczapi_pro_check_type($zoom['api']->type) || empty($zoom) || !empty($zoom['api']->code)) {
    return;
}
$post_meta = get_post_meta(get_the_ID());
// var_dump($post_meta);
?>
<div class="vczapi-col-3 vczapi-pb-3">
    <div class="vczapi-list-zoom-meetings--item card">
        <?php if (has_post_thumbnail()) { ?>
            <div class="vczapi-list-zoom-meetings--item__image card-img-top" style="margin-bottom: 0px;">
                <a href="<?php echo esc_url(get_the_permalink()) ?>"><?php the_post_thumbnail(); ?></a>
            </div>
            <!--Image End-->
        <?php } ?>
        <div class="vczapi-list-zoom-meetings--item__details card-body bg-light">
            <a href="<?php echo esc_url(get_the_permalink()) ?>" class="vczapi-list-zoom-title-link card-title card-link">
                <h3><?php the_title(); ?></h3>
            </a>
            <div class="vczapi-list-zoom-meetings--item__details__meta card-text">
                <div class="ages meta">
                    <span><strong><?php _e('Години:', 'speed-dating-with-zoom'); ?></strong> <?php echo apply_filters('vczapi_ages', $post_meta['min_ages'][0]); ?> до <?php echo apply_filters('vczapi_ages', $post_meta['max_ages'][0]); ?></span>
                </div>
                <div class="slots meta">
                    <span><strong><?php _e('Места:', 'speed-dating-with-zoom'); ?></strong> <?php echo apply_filters('vczapi_slots', $post_meta['Male'][0]); ?> мъже - <?php echo apply_filters('vczapi_slots', $post_meta['Female'][0]); ?> жени</span>
                </div>
                <?php
                if (vczapi_pro_version_active() && vczapi_pro_check_type($zoom['api']->type)) {
                    $type      = !empty($zoom['api']->type) ? $zoom['api']->type : false;
                    $timezone  = !empty($zoom['api']->timezone) ? $zoom['api']->timezone : false;
                    $occurence = !empty($zoom['api']->occurrences) ? $zoom['api']->occurrences : false;

                    if (!empty($occurence)) {
                        $start_time = Codemanas\ZoomPro\Helpers::get_latest_occurence_by_type($type, $timezone, $occurence);
                ?>
                        <div class="start-date meta">
                            <strong><?php _e('Следваща', 'speed-dating-with-zoom'); ?>:</strong>
                            <span><?php echo vczapi_dateConverter($start_time, $timezone, 'F j, Y @ g:i a'); ?></span>
                        </div>
                    <?php
                    } else {
                    ?>
                        <div class="start-date meta">
                            <strong><?php _e('Започва във', 'speed-dating-with-zoom'); ?>:</strong>
                            <span><?php echo $post_meta['_meeting_start_date'] ?> бг време</span>
                        </div>
                    <?php
                    }
                    ?>
                    <div class="start-date meta">
                        <strong><?php _e('Тип', 'speed-dating-with-zoom'); ?>:</strong>
                        <span><?php _e('Повтаряща се', 'speed-dating-with-zoom'); ?></span>
                    </div>
                <?php
                } else {
                    $dateTime = new DateTime($post_meta['_meeting_start_date'][0]);
                    $startTime = $dateTime->format('d M H:i');
                ?>
                    <div class="start-date meta">
                        <span><strong><?php _e('Започва', 'speed-dating-with-zoom') ?>:</strong> <?php echo $startTime . " " . $zoom['api']->timezone ?></span>
                    </div>
                <?php } ?>
            </div>
            <!-- <a href="<?php echo esc_url(get_the_permalink()) ?>" class="btn vczapi-btn-link"><?php _e('See More', 'speed-dating-with-zoom'); ?></a> -->
        </div>
        <!--Details end-->
    </div>
    <!--List item end-->
</div>