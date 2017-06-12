<?php

/**
 * User Groups Taxonomies
 *
 * @package Plugins/Users/Groups/Taxonomy
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register default user group taxonomies
 *
 * This function is hooked onto WordPress's `init` action and creates two new
 * `Disciple_Tools_User_Taxonomy` objects for user "groups" and "types". It can be unhooked
 * and these taxonomies can be replaced with your own custom ones.
 *
 * @since 0.1.4
 */
function disciple_tools_register_default_user_group_taxonomy() {
	new Disciple_Tools_User_Taxonomy( 'user-group', 'users/group', array(
		'singular' => __( 'Team',  'disciple-tools' ),
		'plural'   => __( 'Teams', 'disciple-tools' )
	) );
}

/**
 * Register default user group taxonomies
 *
 * This function is hooked onto WordPress's `init` action and creates two new
 * `Disciple_Tools_User_Taxonomy` objects for user "groups" and "types". It can be unhooked
 * and these taxonomies can be replaced with your own custom ones.
 *
 * @since 0.1.4
 */
function disciple_tools_register_default_user_type_taxonomy() {
	new Disciple_Tools_User_Taxonomy( 'user-type',  'users/type',  array(
		'singular' => __( 'Type',  'disciple-tools' ),
		'plural'   => __( 'Types', 'disciple-tools' )
	) );
}
