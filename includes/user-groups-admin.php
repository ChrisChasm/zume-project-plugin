<?php

/**
 * User Groups Admin
 *
 * @package Plugins/Users/Groups/Admin
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Tweak admin styling for a user groups layout
 *
 * @since 0.1.4
 */
function disciple_tools_groups_admin_assets() {
	$ver = '0.1';

	wp_enqueue_style( 'disciple_tools_groups', plugin_dir_url(__DIR__) . 'includes/css/user-groups.css', false, $ver, false );
}


/**
 * Add new section to User Profiles
 *
 * @since 0.1.9
 *
 * @param array $sections
 */
function disciple_tools_groups_add_profile_section( $sections = array() ) {

	// Copy for modifying
	$new_sections = $sections;

	// Add the "Activity" section
	$new_sections['groups'] = array(
		'id'    => 'groups',
		'slug'  => 'groups',
		'name'  => esc_html__( 'Groups', 'disciple_tools' ),
		'cap'   => 'edit_profile',
		'icon'  => 'dashicons-groups',
		'order' => 90
	);

	// Filter & return
	return apply_filters( 'disciple_tools_groups_add_profile_section', $new_sections, $sections );
}


/*
 * Display the buddypress user name instead of the wp one.
 */

function get_bp_display_name(){
	global $members_template;
	return  xprofile_get_field_data(1, $members_template->member->id);
}

add_filter( 'bp_get_group_member_name', "get_bp_display_name" );
