<?php

/**
 * User Groups Hooks
 *
 * @package Plugins/Users/Groups/Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Register the default taxonomies
add_action( 'init', 'zume_register_default_user_group_taxonomy' );
//add_action( 'init', 'zume_register_default_user_type_taxonomy'  ); // TODO: Enabling this will give user groups a types category. Remove if not neccissary for MVP

// Enqueue assets
add_action( 'admin_head', 'zume_groups_admin_assets' );

// WP User Profiles
add_filter( 'zume_profiles_sections', 'zume_groups_add_profile_section' );
