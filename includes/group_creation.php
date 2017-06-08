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
if (isset($_GET["zume-group-id"])){
	$_SESSION["zume_group_id"] = $_GET["zume-group-id"];
	setcookie("zume_group_id", $_GET["zume-group-id"], time()+86400*7);
}
if (isset($_SESSION["zume_group_id"])){
	$group = groups_get_group((int)$_SESSION["zume_group_id"]);
	$_SESSION["group"] = $group;
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
		};
	}
}

function set_default_privacy($group_id){
	groups_edit_group_settings($group_id, false, "private", "admins");
}

add_action( 'groups_created_group',  'set_default_privacy' );

