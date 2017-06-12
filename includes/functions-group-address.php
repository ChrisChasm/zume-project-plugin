<?php
/**
 * Custom functions that add the group address meta data to the buddy press
 * @source https://codex.buddypress.org/plugindev/how-to-edit-group-meta-tutorial/
 * @since 0.1
 */


/**
 * Gets the group meta data
 * @param string $meta_key
 * @return mixed
 */
function custom_field($meta_key='') {
    //get current group id and load meta_key value if passed. If not pass it blank
    return groups_get_groupmeta( bp_get_group_id(), $meta_key) ;
}

/**
 * Markup for the edit section of group details
 */
function group_edit_fields_markup() {
    global $bp, $wpdb;
    ?>

    <label for="address">Address (required)</label>
    <input id="address" type="text" name="address" value="<?php echo custom_field('address'); ?>" required/>

    <label for="city">City (required)</label>
    <input id="city" type="text" name="city" value="<?php echo custom_field('city'); ?>" required/>

    <label for="state">State (required)</label>
    <input id="state" type="text" name="state" value="<?php echo custom_field('state'); ?>" required/>

    <label for="zip">Zip (required)</label>
    <input id="zip" type="text" name="zip" value="<?php echo custom_field('zip'); ?>" required/>

    <?php
}

/**
 * Markup for the create step of the group details
 */
function group_create_fields_markup() {
    global $bp, $wpdb;
    ?>

    <label for="address">Address (required)</label>
    <input id="address" type="text" name="address" value="" required/>

    <label for="city">City (required)</label>
    <input id="city" type="text" name="city" value="" required/>

    <label for="state">State (required)</label>
    <input id="state" type="text" name="state" value="" required/>

    <label for="zip">Zip (required)</label>
    <input id="zip" type="text" name="zip" value="" required/>

    <?php
}


/**
 * @param $group_id
 */
function group_header_fields_save($group_id)
{
    global $bp, $wpdb;
    $plain_fields = array(
        'address', 'city', 'state', 'zip', 'country'
    );
    foreach ($plain_fields as $field) {
        $key = $field;
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            groups_update_groupmeta($group_id, $field, $value);
        }
    }
}

add_filter( 'bp_after_group_details_creation_step', 'group_create_fields_markup' );
add_filter( 'bp_after_group_details_admin', 'group_edit_fields_markup' );
add_action( 'groups_group_details_edited', 'group_header_fields_save' );
add_action( 'groups_created_group',  'group_header_fields_save' );









// TODO: NOTES FOR FUTURE USE

// Using wp-content/plugins/buddypress/bp-templates/bp-legacy/buddypress/groups/create.php
// And using wp-content/plugins/buddypress/bp-themes/bp-default/groups/create.php


//add_action('bp_after_group_invites_creation_step', 'zume_after_invitation_explaination');
//add_action('bp_before_group_invites_creation_step', 'zume_before_invitation_explaination');
//
//function zume_before_invitation_explaination () {
//    print '<p>This is a Zúme explaination before the invitation process.</p>';
//}
//
//function zume_after_invitation_explaination () {
//    print 'This is a Zúme explaination after the invitation process.';
//}

//function zume_after_group_description () {
//    print '<label>Address (required)</label><input type="text" name="group_address" required/>';
//    print '<label>City (required)</label><input type="text" name="group_city" required/>';
//    print '<label>State (required)</label><input type="text" name="group_state" required/>';
//    print '<label>Zip (required)</label><input type="text" name="group_zip" required/>';
//}
//add_action('bp_after_group_details_creation_step', 'zume_after_group_description');
