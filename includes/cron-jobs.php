<?php
/**
 * Schedule coach assignment cron job
 */

/**
 * Schedule the coach assignment cron job on activation
 */
function zume_coach_activation() {
    if (! wp_next_scheduled ( 'assign_coaches_hourly' )) {
        wp_schedule_event(time(), 'hourly', 'assign_coaches_hourly');
    }
}
register_activation_hook(__FILE__, 'zume_coach_activation');


/**
 * Run the coach assignment process
 */
function assign_coaches_hourly() {
   Zume_Coaches::check_for_groups_needing_assignment();
}
add_action('my_hourly_event', 'do_this_hourly');


/**
 * Remove cron job on deactivation
 */
function zume_coach_deactivation() {
    wp_clear_scheduled_hook('assign_coaches_hourly');
}
register_deactivation_hook(__FILE__, 'zume_coach_deactivation');