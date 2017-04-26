<?php



class Zume_Group_Address extends BP_Group_Extension {

    var $enable_nav_item = true;
    var $enable_create_step = true;
    var $enable_edit_item = false;

    public function __construct() {
        global $bp;

        $this->has_caps = true;

        /* Group API Extension Properties */
        $this->name = __( 'Zume Group Address', 'zume' );
        $this->slug = 'zume_group_address';

        /* Set as early in the order as possible */
        $this->create_step_position = 42;
        $this->nav_item_position = 71;

        /* Generic check access */
        if ( $this->has_caps == false ) {
            $this->enable_create_step = false;
            $this->enable_edit_step = false;
        }

        $this->enable_nav_item = $this->enable_nav_item();
        $this->enable_create_step = $this->enable_create_step();
    }

    /**
     * Display the group tab.
     *
     * @param int $group_id Available only on BP 2.2+.
     */
    function display( $group_id = null ) {
        global $bp;

        if ( BP_INVITE_ANYONE_SLUG == $bp->current_action && isset( $bp->action_variables[0] ) && 'send' == $bp->action_variables[0] ) {
            if ( !check_admin_referer( 'groups_send_invites', '_wpnonce_send_invites' ) )
                return false;

            // Send the invites.
            groups_send_invites( $bp->loggedin_user->id, $bp->groups->current_group->id );

            do_action( 'groups_screen_group_invite', $bp->groups->current_group->id );

            // Hack to imitate bp_core_add_message, since bp_core_redirect is giving me such hell
            echo '<div id="message" class="updated"><p>' . __( 'Group invites sent.', 'invite-anyone' ) . '</p></div>';
        }

        invite_anyone_create_screen_content('invite');
    }

    function create_screen( $group_id = null ) {
        global $bp;

        /* If we're not at this step, go bye bye */
        if ( !bp_is_group_creation_step( $this->slug ) )
            return false;

        invite_anyone_create_screen_content( 'create' );

        wp_nonce_field( 'groups_create_save_' . $this->slug );
    }

    function create_screen_save( $group_id = null ) {
        global $bp;

        /* Always check the referer */
        check_admin_referer( 'groups_create_save_' . $this->slug );

        /* Set method and save */
        if ( bp_group_has_invites() )
            $this->has_invites = true;
        $this->method = 'create';
        $this->save( $group_id );
    }

    function save( $group_id = null ) {
        global $bp;

        if ( null === $group_id ) {
            $group_id = bp_get_current_group_id();
        }

        /* Set error redirect based on save method */
        if ( $this->method == 'create' ) {
            $redirect_url = $bp->loggedin_user->domain . $bp->groups->slug . '/create/step/' . $this->slug;
        } else {
            $group = groups_get_group( array( 'group_id' => $group_id ) );
            $redirect_url = bp_get_group_permalink( $group ) . '/admin/' . $this->slug;
        }

        groups_send_invites( $bp->loggedin_user->id, $group_id );

        if ( $this->has_invites )
            bp_core_add_message( __( 'Group invites sent.', 'invite-anyone' ) );
        else
            bp_core_add_message( __( 'Group created successfully.', 'invite-anyone' ) );
    }

    /**
     * Should the group creation step be included?
     *
     * @since 1.2
     */
    public function enable_create_step() {
        $options = invite_anyone_options();
        return ! empty( $options['group_invites_enable_create_step'] ) && $options['group_invites_enable_create_step'] === 'yes';
    }

    function enable_nav_item() {
        global $bp;

        // Group-specific settings always override
        if ( ! bp_groups_user_can_send_invites() ) {
            return false;
        }

        if ( invite_anyone_group_invite_access_test() == 'anyone' )
            return true;
        else
            return false;
    }

    function widget_display() {}
}
bp_register_group_extension( 'Zume_Group_Address' );