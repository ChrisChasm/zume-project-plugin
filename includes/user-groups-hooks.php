<?php

/**
 * User Groups Hooks
 *
 * @package Plugins/Users/Groups/Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Register the default taxonomies
add_action( 'init', 'disciple_tools_register_default_user_group_taxonomy' );
//add_action( 'init', 'disciple_tools_register_default_user_type_taxonomy'  ); // TODO: Enabling this will give user groups a types category. Remove if not neccissary for MVP

// Enqueue assets
add_action( 'admin_head', 'disciple_tools_groups_admin_assets' );

// WP User Profiles
add_filter( 'disciple_tools_profiles_sections', 'disciple_tools_groups_add_profile_section' );
