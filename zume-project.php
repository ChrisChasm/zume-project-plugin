<?php
/*
Plugin Name: Zume Project
Plugin URI: http://example.org/my-plugin/
Description: Private Plugin for the Zume Project
Version: 1.0
Requires at least: WordPress 2.9.1 / BuddyPress 1.2
Tested up to: WordPress 7.4 / BuddyPress 1.2
License: GNU/GPL 3
Author: Chris Chasm
Author URI: http://chasm.solutions
GitHub Plugin URI: https://github.com/ChasmSolutions/zume-project-plugin
GitHub Branch:    master
*/

define('ZUME_DOMAIN', 'zume_project');
define('ZUME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/* Only load code that needs BuddyPress to run once BP is loaded and initialized. */
function zume_init() {

    require_once ('includes/class-zume-course.php');
    $zume_course = Zume_Course::instance();

    require_once ('api/record-steps.php');

    // Loads configuration functions for the zume site.
    require_once ('includes/zume-functions.php');

    require_once ('includes/class-zume-dashboard.php');

    require_once ('includes/class-zume-extend-group.php');

}
add_action( 'bp_include', 'zume_init' );

/* If you have code that does not need BuddyPress to run, then add it here. */



/*
 * API routes
 */
require_once ('api/zume-api.php');
// Function to register our new routes from the controller.
function prefix_register_zume_rest_routes() {
    $zume_route = new Zume_Custom_Route();
    $zume_route->register_routes();
}
add_action( 'rest_api_init', 'prefix_register_zume_rest_routes' );
/* End API routes */


require_once ('includes/steplog-post-type.php');
$steplog = Zume_Steplog::instance();


require_once ('includes/class-zume-helper.php');
$helper = Zume_Helper::instance();



/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function ZumeProject_i18n_init() {
	$pluginDir = dirname(plugin_basename(__FILE__));
	load_plugin_textdomain('zume_project', false, $pluginDir . '/languages/');
}

// Initialize i18n
add_action('plugins_loadedi','ZumeProject_i18n_init');



