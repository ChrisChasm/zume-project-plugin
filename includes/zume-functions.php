<?php
/**
 * Functions used in the Zúme implementation
 *
 * @since 0.1
 * @author  Chasm Solutions
 */

/* Require Authentication for Zúme */
function zume_force_login() {

    // Pages that should not be redirected. Add to array exception pages.
    $exception_pages = array(
        'Home',
        'Register',
        'Activate',
        'Complete',
        'Overview',
        'About',
        'Resources',
    );
    foreach($exception_pages as $page) {
        if(is_page($page)) {
            return;
        }
    }

    // Otherwise, if user is not logged in redirect to login
    if (!is_user_logged_in()) {
        auth_redirect();
    }

    // If user is logged in, check that key plugins exist for the course.
    if (! class_exists('Zume_Course') ) {
        echo 'Zúme Course plugin is disabled or otherwise unreachable. Please, check with administrator to verify course availability.';
        return;
    }

    // Check if BuddyPress plugin is active
    if (! buddypress() ) {
        echo 'Buddypress plugin is disabled or otherwise unreachable. Please, check with administrator to verify course availability.';
        return;
    }
}


/*
 * Removing confusing or unnecissary admin menu items for Coaches.
 *
 */
add_action( 'admin_init', 'zume_remove_coach_menu_pages' );
function zume_remove_coach_menu_pages() {

    global $user_ID;

    if ( current_user_can( 'coach' ) ) {
        remove_menu_page( 'options-general.php' );
        remove_menu_page( 'tools.php' );
        remove_menu_page( 'admin.php?page=theme_my_login' );
        remove_menu_page( 'theme_my_login' );
        remove_menu_page('bp-emails-customizer-redirect' );
        remove_menu_page('admin.php?page=activity-log-settings' );
    }
}



/*
 * Redirects logged on users from home page requests to dashboard.
 *
 */
function zume_dashboard_redirect () {
    global $post;
    if ( is_user_logged_in() && $post->post_name == 'home') {
        wp_redirect( home_url('/dashboard') );
    }
}
add_action('template_redirect', 'zume_dashboard_redirect');

/**
 * Queries the Steplog table and finds the highest session completed
 * @since 0.1
 * @return integer
 */
function zume_group_highest_session_completed ($group_id) {
    global $wpdb;

    $where_query = 'group-'.$group_id.'-step-complete%';
    $querystr =  "SELECT MAX(post_excerpt) as completed FROM $wpdb->posts WHERE post_type = 'steplog' AND post_name LIKE '$where_query'";

    $result = $wpdb->get_results($querystr, ARRAY_A);

    return (int) $result[0]['completed'];
}

/**
 * Gets the session number for the next session of the group.
 * @since 0.1
 * @return integer
 */
function zume_group_next_session ($group_id) {
    $highest_session = zume_group_highest_session_completed($group_id);
    return (int) $highest_session + 1;
}


// BuddyPress Group Creation Modifications
add_action('bp_before_create_group_content_template', 'zume_create_group_content');
function zume_create_group_content () {
    echo '<h2 class="center padding-bottom">Create Group</h2>';
}


function zume_add_next_session_link () {
    global $wp_admin_bar, $bp;
    // Add the top-level Group Admin button.

}


// Remove admin bar on the front end.
add_filter('show_admin_bar', '__return_false');


/*
 * Zúme Invite Page Content
 * contains tailored content for the user to select the kind of invitation they want to make.
 */
function zume_invite_page_content ( $content ) {
    if ( is_page( 'zume-invite' ) ) {

         require_once ('templates/zume-invites.php');
         zume_page_content_zume_invites ();

    }
    return $content;
}
add_filter( 'the_content', 'zume_invite_page_content');


