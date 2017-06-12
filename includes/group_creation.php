<?php

//
//function skip_used($status, $email){
//
//
////	if user already exists
////	add to group auto
//	if ($status == "used"){
//		$status = "skip";
//	}
//	return  $status;
//
//}
//
//add_filter("invite_anyone_validate_email", "skid_used");


/**
 * Automatically add people to a group
 */
//example url zume.dev/?zume-group-id=16
//
if (!session_id()) {
    session_start();
}
if (isset($_GET["group-id"]) && isset($_GET["zgt"])){
	$_SESSION["zume_group_id"] = $_GET["group-id"];
	$_SESSION["zume_group_token"] = $_GET["zgt"];
}



if (isset($_SESSION["zume_group_id"]) && isset($_SESSION["zume_group_token"])){
	$group = groups_get_group((int)$_SESSION["zume_group_id"]);
	$group_token = groups_get_groupmeta($group->id, "group_token");
	if (isset($group_token) && $group_token == $_SESSION["zume_group_token"]){
		if (!groups_is_user_member(get_current_user_id(), $_SESSION["zume_group_id"]) && $group->creator_id){
			$_SESSION["test"] = groups_invite_user(array(
				"user_id"=>get_current_user_id(),
				"group_id" => $_SESSION["zume_group_id"],
				"inviter_id" => $group->creator_id,
				"is_confirmed" => true
				)
			);
			if (groups_accept_invite(get_current_user_id(), $_SESSION["zume_group_id"])){
				$_SESSION["zume_group_id"] = "";
				$_SESSION["zume_group_token"] = "";
			};
		}
	}
}

function generate_string($length) {

	$chars = "0123456789abcdefghijklmnopqrstuvwxyz";
	$string = "";

        for ($i = 0; $i < $length; $i++) {
	  $string .= $chars[mt_rand(0, strlen($chars) - 1)];
	}

	return $string;
}

function set_group_defaults($group_id){
	groups_edit_group_settings($group_id, false, "private", "admins");
	if (!groups_get_groupmeta($group_id, "group_token")){
		$token = generate_string(10);
		groups_update_groupmeta($group_id, "group_token",$token);
	}
}

add_action( 'groups_created_group',  'set_group_defaults' );



function automatically_added_to_group() {

	// Do not create if it already exists and is not in the trash
	$post_exists = post_exists( '[{{{site.name}}}] Added to Group.' );

	if ( $post_exists != 0 && get_post_status( $post_exists ) == 'publish' )
		return;

	// Create post object
	$my_post = array(
		'post_title'    => __( '[{{{site.name}}}] Added to Group.', 'zume_project' ),
		'post_content'  => __( "Congratulations! You are now part of the Zume group &quot;<a href=\"{{{group.url}}}\">{{group.name}}</a>&quot;", 'zume_project' ),  // HTML email content.
		'post_excerpt'  => __( "Congratulations! You are now part of the Zume group \"{{group.name}}\" \n\nTo view the group, visit: {{{group.url}}}", 'zume_project' ),  // Plain text email content.
		'post_status'   => 'publish',
		'post_type' => bp_get_email_post_type() // this is the post type for emails
	);

	// Insert the email post into the database
	$post_id = wp_insert_post( $my_post );

	if ( $post_id ) {
		// add our email to the taxonomy term 'post_received_comment'
		// Email is a custom post type, therefore use wp_set_object_terms

		$tt_ids = wp_set_object_terms( $post_id, 'post_received_comment', bp_get_email_tax_type() );
		foreach ( $tt_ids as $tt_id ) {
			$term = get_term_by( 'term_taxonomy_id', (int) $tt_id, bp_get_email_tax_type() );
			wp_update_term( (int) $term->term_id, bp_get_email_tax_type(), array(
				'description' => 'A member is automatically added to a group',
			) );
		}
	}

}
add_action( 'bp_core_install_emails', 'automatically_added_to_group' );