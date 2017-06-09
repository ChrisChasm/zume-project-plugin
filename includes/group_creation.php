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