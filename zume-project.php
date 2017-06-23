<?php
/*
Plugin Name: Zúme Project
Plugin URI: http://example.org/my-plugin/
Description: Private Plugin for the Zúme Project
Version: 1.1.1
Requires at least: WordPress 2.9.1 / BuddyPress 1.2
Tested up to: WordPress 7.4 / BuddyPress 1.2
License: GNU/GPL 3
Author: Chris Chasm
Author URI: http://chasm.solutions
*/

define('ZUME_DOMAIN', 'zume_project');
define('ZUME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/* Only load code that needs BuddyPress to run once BP is loaded and initialized. */
function zume_init() {

    require_once ('includes/class-zume-course.php');
    $zume_course = Zume_Course::instance();

    require_once ('includes/class-zume-overview.php');
    $zume_overview = Zume_Overview::instance();

    // Loads configuration functions for the zume site.
    require_once ('includes/zume-functions.php');
    require_once ('includes/class-zume-dashboard.php');
    require_once ('includes/functions-group-address.php'); // loads the group address meta fields

}
add_action( 'bp_include', 'zume_init' );

function bp_loaded_function(){

	require_once ('includes/group_creation.php');
}

add_action('bp_init', 'bp_loaded_function');

/* If you have code that does not need BuddyPress to run, then add it here. */




/*
 * API routes
 */
require_once ('includes/rest-api.php');
$zume_rest = Zume_REST_API::instance();

//require_once ('api/zume-api.php');
//// Function to register our new routes from the controller.
//function prefix_register_zume_rest_routes() {
//    $zume_route = new Zume_Custom_Route();
//    $zume_route->register_routes();
//}
//add_action( 'rest_api_init', 'prefix_register_zume_rest_routes' );
/* End API routes */


require_once ('includes/steplog-post-type.php');
$steplog = Zume_Steplog::instance();


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



