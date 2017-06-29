<?php

/**
 * Zume Coaches Class
 *
 * @class Zume_Coaches
 * @version	0.1
 * @since 0.1
 * @package	Zume
 * @author Chasm.Solutions & Kingdom.Training
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Zume_Coaches {

    /**
     * Zume_Coaches The single instance of Zume_Coaches.
     * @var 	object
     * @access  private
     * @since 	0.1
     */
    private static $_instance = null;

    /**
     * Main Zume_Coaches Instance
     *
     * Ensures only one instance of Zume_Coaches is loaded or can be loaded.
     *
     * @since 0.1
     * @static
     * @return Zume_Coaches instance
     */
    public static function instance () {
        if ( is_null( self::$_instance ) )
            self::$_instance = new self();
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     * @access  public
     * @since   0.1
     */
    public function __construct () {

//        // Register coach role
//        if(!$this->role_exists( 'coach' )) {
//            $this->create_coach_role();
//        }

        if(user_can(get_current_user_id(), 'coach_tools')) {
            // Add coach tab
            require_once ('class-coaches-group-stats.php');
            require_once ('class-coaches-unassigned-groups.php');
            require_once ('class-coaches-list.php');
            add_action( 'admin_menu', array( $this, 'load_admin_menu_item' ) );
        }




    } // End __construct()

    /**
     * Create the coach role if it is not created.
     */
    private function create_coach_role () {
        add_role( 'coach', 'Coach',
            array(
                'add_users' => true,
                'edit_users' => true,
                'edit_others_steplogs' => true,
                'edit_steplogs' => true,
                'edit_users_higher_level' => true,
                'promote_users' => true,
                'delete_posts' => true,
                'edit_posts' => true,
                'read' => true,
                'promote_users_higher_level' => true,
                'promote_users_to_higher_level' => true,
                'publish_steplogs' => true,
                'read_private_steplogs' => true,
                'remove_users' => true,
                'coach_tools' => true,
            ) );
    }

    private function role_exists( $role ) {

        if( ! empty( $role ) ) {
            return $GLOBALS['wp_roles']->is_role( $role );
        }

        return false;
    }

    /**
     * Function called by the hourly cron job that assigns coaches to groups who have at least 4 people and no currently assigned coach.
     */
    public static function check_for_groups_needing_assignment () {
        global $wpdb;
        // Find all groups that have 4 members and do not have an assigned coach
        //SELECT group_id FROM wp_bp_groups_groupmeta WHERE meta_key != 'assigned_to' AND meta_key = 'total_member_count' AND meta_value >= '4' Group BY group_id
        $groups = $wpdb->get_results("SELECT group_id, count(*) as total FROM $wpdb->bp_groups_groupmeta WHERE (meta_key = 'total_member_count' AND meta_value >= '4' )
	OR (meta_key = 'assigned_to' AND meta_value = 'dispatch') Group BY group_id HAVING total >= 2", ARRAY_A);

        foreach($groups as $group) {
            // Get group location
            $group_id = $group['group_id'];
            $group_meta = groups_get_groupmeta( $group_id);

            $states = ''; //TODO left off matching coach to location via the teams feature.


            // Check if location matches coach county
            // Check if multiple coaches and assign to coach with least groups.

            // Check if location matches coach state
            // Check if multiple coaches and assign to coach with least groups.

            // Default to global coach location
            // Check if multiple coaches and assign to coach with least groups.
        }

        // Notify coach

        return true;
    }



    /**
     * Load Admin menu into Settings
     */
    public function load_admin_menu_item () {
        add_menu_page( 'Coach', 'Coach Tools', 'read', 'coach_tools', array($this, 'page_content'), 'dashicons-admin-users', '30' );
    }

    /**
     * Builds the tab bar
     * @since 0.1
     */
    public function page_content() {


        if ( !current_user_can( 'coach_tools' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        /**
         * Begin Header & Tab Bar
         */
        if (isset($_GET["tab"])) {$tab = $_GET["tab"];} else {$tab = 'group_stats';}

        $tab_link_pre = '<a href="admin.php?page=coach_tools&tab=';
        $tab_link_post = '" class="nav-tab ';

        $html = '<div class="wrap">
            <h2>Coach Tools</h2>
            <h2 class="nav-tab-wrapper">';

        $html .= $tab_link_pre . 'group_stats' . $tab_link_post;
        if ($tab == 'group_stats' || !isset($tab)) {$html .= 'nav-tab-active';}
        $html .= '">Group Stats</a>';

        $html .= $tab_link_pre . 'unassigned' . $tab_link_post;
        if ($tab == 'unassigned' ) {$html .= 'nav-tab-active';}
        $html .= '">Unassigned Groups</a>';

        $html .= $tab_link_pre . 'coach_list' . $tab_link_post;
        if ($tab == 'coach_list' ) {$html .= 'nav-tab-active';}
        $html .= '">Coach List</a>';

        $html .= '</h2>';

        echo $html; // Echo tabs

        $html = '';
        // End Tab Bar

        /**
         * Begin Page Content
         */
        switch ($tab) {

            case "coach_list":
                $list_class = new Zume_Coaches_List();
                if( isset($_POST['s']) ){
                    $list_class->prepare_items($_POST['s']);
                } else {
                    $list_class->prepare_items();
                }
                ?>
                <div class="wrap">
                    <div id="icon-users" class="icon32"></div>
                    <h2>Active Coaches</h2>
                    <form method="post">
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                        <?php $list_class->search_box('Search Table', 'your-element-id'); ?>
                    </form>
                    <?php $list_class->display(); ?>
                </div>
                <?php
                break;
            case "unassigned":
                    $list_class = new Zume_Unassigned_Groups_List();
                    if( isset($_POST['s']) ){
                        $list_class->prepare_items($_POST['s']);
                    } else {
                        $list_class->prepare_items();
                    }
                    ?>
                    <div class="wrap">
                        <div id="icon-users" class="icon32"></div>
                        <h2>Unassigned Groups</h2>
                        <form method="post">
                            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                            <?php $list_class->search_box('Search Table', 'your-element-id'); ?>
                        </form>
                        <?php $list_class->display(); ?>
                    </div>
                    <?php
                break;
            default:
                $list_class = new Zume_Group_Stats_List();
                if( isset($_POST['s']) ){
                    $list_class->prepare_items($_POST['s']);
                } else {
                    $list_class->prepare_items();
                }
                ?>
                <div class="wrap">
                    <div id="icon-users" class="icon32"></div>
                    <h2>Group Stats</h2><?php  print '<pre>'; print_r(groups_get_groupmeta( '4')); print '</pre>'; ?>
                    <form method="post">
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                        <?php $list_class->search_box('Search Table', 'your-element-id'); ?>
                    </form>
                    <?php $list_class->display(); ?>
                </div>
                <?php
                break;
        }

        $html .= '</div>'; // end div class wrap

        echo $html; // Echo contents
    }

    public function assignment_cron () {

        // if group has no coach

        // if county coach exists
        // search for groups without assigned_coach key

        // assign coach logic


    }

}