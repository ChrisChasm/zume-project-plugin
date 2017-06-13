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
				//send welcome to group email
				$args = array(
					'tokens' => array (
						'group.name'    => $group->name,
						'group.url'     => esc_url(bp_get_group_permalink($group))
					)
				);
				bp_send_email('member_automatically_added_to_group', get_current_user_id(), $args);
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
		'post_content'  => __( "Congratulations! You are now part of the Zúme group &quot;<a href=\"{{{group.url}}}\">{{group.name}}</a>&quot;", 'zume_project' ),  // HTML email content.
		'post_excerpt'  => __( "Congratulations! You are now part of the Zúme group \"{{group.name}}\" \n\nTo view the group, visit: {{{group.url}}}", 'zume_project' ),  // Plain text email content.
		'post_status'   => 'publish',
		'post_type' => bp_get_email_post_type() // this is the post type for emails
	);

	// Insert the email post into the database
	$post_id = wp_insert_post( $my_post );

	if ( $post_id ) {
		// add our email to the taxonomy term 'post_received_comment'
		// Email is a custom post type, therefore use wp_set_object_terms

		$tt_ids = wp_set_object_terms( $post_id, 'member_automatically_added_to_group', bp_get_email_tax_type() );
		foreach ( $tt_ids as $tt_id ) {
			$term = get_term_by( 'term_taxonomy_id', (int) $tt_id, bp_get_email_tax_type() );
			wp_update_term( (int) $term->term_id, bp_get_email_tax_type(), array(
				'description' => 'A member is automatically added to a group',
			) );
		}
	}

}
add_action( 'bp_core_install_emails', 'automatically_added_to_group' );

function invite_to_group_email() {

	// Do not create if it already exists and is not in the trash
	$post_exists = post_exists( '[{{{site.name}}}] Zume Invitation.' );

	if ( $post_exists != 0 && get_post_status( $post_exists ) == 'publish' )
		return;

	$post_content = '
Hello from Zúme Project!

One of your friends just invited you to start an exciting journey -- a journey into learning how to make disciples who make disciples.

Your friend, {{inviter.name}}, would like you to join their Zúme training group, &quot;{{group.name}}&quot;.

Go <a href="{{group.sign_up}}">here</a> to accept your invitation. After you click on this link, it will ask you to create an account. Then you will be joined to your group.

When you, your friend who invited you and at least two other people are gathered together, you can begin going through the Zúme training.

Join the movement of ordinary people who God could use to change the world.
';
	$post_exerpt = '
Hello from Zúme Project!

One of your friends just invited you to start an exciting journey -- a journey into learning how to make disciples who make disciples.

Your friend, {{inviter.name}}, would like you to join their Zúme training group, "{{group.name}}".

Go {{group.sign_up}} to accept your invitation. After you click on this link, it will ask you to create an account. Then you will be joined to your group.

When you, your friend who invited you and at least two other people are gathered together, you can begin going through the Zúme training.

Join the movement of ordinary people who God could use to change the world.
	';


	// Create post object
	$my_post = array(
		'post_title'    => __( '[{{{site.name}}}] Zume Invitation.', 'zume_project' ),
		'post_content'  => $post_content,  // HTML email content.
		'post_excerpt'  => $post_exerpt,
		'post_status'   => 'publish',
		'post_type' => bp_get_email_post_type() // this is the post type for emails
	);

	// Insert the email post into the database
	$post_id = wp_insert_post( $my_post );

	if ( $post_id ) {
		// add our email to the taxonomy term 'post_received_comment'
		// Email is a custom post type, therefore use wp_set_object_terms

		$tt_ids = wp_set_object_terms( $post_id, 'invite_to_group_email', bp_get_email_tax_type() );
		foreach ( $tt_ids as $tt_id ) {
			$term = get_term_by( 'term_taxonomy_id', (int) $tt_id, bp_get_email_tax_type() );
			wp_update_term( (int) $term->term_id, bp_get_email_tax_type(), array(
				'description' => 'Invite to a group by email',
			) );
		}
	}

}
add_action( 'bp_core_install_emails', 'invite_to_group_email' );




/**
 * Parses email addresses, comma-separated or line-separated, into an array
 *
 * @package Invite Anyone
 * @since 0.8.8
 *
 * @param str $address_string The raw string from the input box
 * @return array $emails An array of addresses
 */
function invite_by_email_parse_addresses( $address_string ) {

	$emails = array();

	// First, split by line breaks
	$rows = explode( "\n", $address_string );

	// Then look through each row to split by comma
	foreach( $rows as $row ) {
		$row_addresses = explode( ',', $row );

		// Then walk through and add each address to the array
		foreach( $row_addresses as $row_address ) {
			$row_address_trimmed = trim( $row_address );

			// We also have to make sure that the email address isn't empty
			if ( ! empty( $row_address_trimmed ) && ! in_array( $row_address_trimmed, $emails ) )
				$emails[] = $row_address_trimmed;
		}
	}

	return apply_filters( 'invite_anyone_parse_addresses', $emails, $address_string );
}

function group_invite_by_email() {
//	@todo verify nonce
	update_option( "save_group_email", $_POST );
	$group = groups_get_group($_POST["group_id"]);
	if ( isset( $_POST["invite_by_email_addresses"] ) ) {
		$addresses = invite_by_email_parse_addresses($_POST["invite_by_email_addresses"]);
		update_option("group_invite_addresses", $addresses);

		foreach ($addresses as $address){
			$args = array(
				'tokens' => array (
					'group.name'    => $group->name,
					'inviter.name'  => $_POST["inviter_name"],
					'group.sign_up'   => $_POST["sing_up_url"]
				)
			);
			bp_send_email('member_automatically_added_to_group', get_current_user_id(), $args);
		}
	}
	return wp_redirect($_POST["_wp_http_referer"]);
}


add_action("admin_post_group_invite_by_email", "group_invite_by_email");