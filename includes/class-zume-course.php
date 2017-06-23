<?php

/**
 * Zúme Course Core
 *
 * @class Disciple_Tools_Admin_Menus
 * @version	0.1
 * @since 0.1
 * @package	Disciple_Tools
 * @author Chasm.Solutions & Kingdom.Training
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Zume_Course {

	/**
	 * Disciple_Tools_Admin_Menus The single instance of Disciple_Tools_Admin_Menus.
	 * @var 	object
	 * @access  private
	 * @since 	0.1
	 */
	private static $_instance = null;

	/**
	 * Main Zume_Course Instance
	 *
	 * Ensures only one instance of Disciple_Tools_Admin_Menus is loaded or can be loaded.
	 *
	 * @since 0.1
	 * @static
	 * @return Zume_Course instance
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
		add_action( 'wp_enqueue_scripts', array($this, 'zume_scripts_enqueue' ) );
	} // End __construct()

    /**
     * Zúme Pre Content Load
     * @access  public
     * @since   0.1
     */
    public function zume_pre_content_load () {

        /*** VARIABLES ***/

        // Set variables
        $user_id = get_current_user_id();
        $meta_key = 'zume_active_group';

        // Set variable for session
        if ( isset( $_GET['id']) ) {
            $zume_session = $_GET['id'];
        }
        else { $zume_session = '1'; }


        /**
         * First check if a change has been made to the active group selection
         * or if a new group session is being requested from the dashboard.
         */
        if(! empty($_POST[$meta_key]) || isset( $_GET['group_id'])) {

            if ( isset( $_GET['group_id']) ) {
                $new_group_id =  $_GET['group_id'];
            } else {
                $new_group_id = $_POST[$meta_key];
            }


            // Update or Add meta value with new_group_id
            if ( get_user_meta($user_id, $meta_key, true) ) {
                update_user_meta($user_id, $meta_key, $new_group_id);
            } else {
                add_user_meta( $user_id, $meta_key, $new_group_id, true );
            }

            // Load Zúme content with variables
            $this->content_loader($zume_session, $new_group_id );
        }
        /**
         * Second check if there is no active group associated with the the user or
         * a request has been made by the users to switch the active group
         */
        elseif ( (! get_user_meta($user_id, $meta_key, true) ) || isset($_GET['switch_zume_group'])  ) {

            // Get user memberships
            $user_groups = bp_get_user_groups( bp_loggedin_user_id(), array( 'is_admin' => null, 'is_mod' => null, ) );

            // Check to select group
            if ( count( $user_groups ) > 1 ) {

                echo 'More than one group<br>';
                echo '<form action=""  method="POST" >Which group do you prefer?<br>';

                foreach ($user_groups as $agroup) {

                    // Get group name from group id
                    $group_id = $agroup->group_id;
                    $group = groups_get_group( $group_id ); // gets group object
                    $group_name = $group->name;

                    // Create radio button
                    echo '<div class="radio">';
                    echo '<label><input type="radio" name="'. $meta_key .'" value="'.$group_id.'">'.$group_name.' </label>';
                    echo '</div>';

                }

                echo '<button type="submit" class="btn button">Select</button>';
                echo '</form>';

                return;

            } else {
                $new_group_id = '';

                foreach ($user_groups as $agroup) {
                    $new_group_id = $agroup->group_id;
                }

                // Update or Add meta value with new_group_id
                if ( get_user_meta($user_id, $meta_key, true) ) {
                    update_user_meta($user_id, $meta_key, $new_group_id);
                } else {
                    add_user_meta( $user_id, $meta_key, $new_group_id, true );
                }

                // Load Zúme content with variables
                $this->content_loader($zume_session, $new_group_id );
            }

            /**
             * Last, pull current active group from user meta and load content according to active group.
             */
        } else {

            $new_group_id = get_user_meta($user_id, $meta_key, true);

            // Load Zúme content with variables
            $this->content_loader($zume_session, $new_group_id );
        }

        /**
         * Create switch group link
         */
        $user_groups = bp_get_user_groups( bp_loggedin_user_id(), array( 'is_admin' => null, 'is_mod' => null, ) ); // Check for number of groups

        // Check to select group
        if ( count( $user_groups ) > 1 ) {
            if (get_user_meta($user_id, $meta_key, true)) {

                $group_id = get_user_meta($user_id, $meta_key, true);
                $group = groups_get_group($group_id); // gets group object
                $group_name = $group->name;

                echo '<div class="row columns"><div class="small-centered center"><br><br>(' . $group_name . ')<br><a href="' . get_permalink() . '?id=' . $zume_session . '&switch_zume_group=true" >switch group</a></div></div>';
            }
        }
    }

	/**
	 * Zúme Content Loader
	 * @return mixed
	 */
	public function content_loader ( $session = '1', $group_id ) {

	    // Check for highest session completed and redirect
        $next_session = zume_group_next_session($group_id);
        if (! is_null($next_session) && $session > $next_session ) {
            $session = $next_session;
        }

        echo $this->zume_course_loader($session, $group_id);

	}

    /**
     * Pulls the content from the pages database
     * @return string
     */
    public function zume_course_loader ($session, $group_id) {

        $session_title = 'Session ' . $session . ' Course';
        $page_object = get_page_by_title( $session_title, OBJECT, 'page' );

        $session = (int) $session;
        $group_id = (int) $group_id;

        $prev_link = null;
        $next_link = null;
        if ($session > 1) {
            $prev_link = '?id=' . ($session - 1) . '&group_id=' . $group_id;
        }
        $group_next_session = zume_group_next_session($group_id);
        if (! is_null($group_next_session) && ($session + 1) <= $group_next_session) {
            $next_link = '?id=' . ($session + 1) . '&group_id=' . $group_id;
        }

        if (! empty($page_object) || ! empty($page_object->post_content)) {

            $session_title = "Session $session";
            if ($session == 10) {
                $session_title = "Session 10 — Advanced Training";
            }

            $html = '';
            $html .= $this->jquery_steps($group_id, $session);
            $html .= '<div class="row columns center">';
            if (! is_null($prev_link)) {
                $html .= '<a href="' . esc_attr($prev_link) . '" title="Previous session"><span class="chevron chevron--left"><span>Previous session</span></span></a> ';
            }
            $html .= '<h2 style="color: #21336A; display: inline">' . $session_title . '</h2>';
            if (! is_null($next_link)) {
                $html .= ' <a href="' . esc_attr($next_link) . '" title="Next session"><span class="chevron chevron--right"><span>Next session</span></span></a>';
            }
            $html .= '</div>';
            $html .= '<br><div id="session'.$session.'-'.$group_id .'" class="course-steps">';

            if(zume_group_highest_session_completed ($group_id) < $session) { $html .= $this->attendance_step($group_id, $session); } // add attendance as the first step

            $html .= $page_object->post_content.'';
            $html .= '</div>';

            $html .= '<div class="js-group-info" data-group-permalink="' . esc_attr(bp_get_group_permalink(groups_get_group($group_id))) . '"></div>';

            return $html;
        }
        else {
            return 'Please republish "'.$session_title.'" with content for this section in the pages administration area.';
        }
    }

	/**
	 * Enqueue scripts and styles
	 * @return mixed
	 */
	public function zume_scripts_enqueue () {
		wp_register_script( 'jquery-steps', plugin_dir_url(__FILE__) . 'js/jquery.steps.js', array('jquery'), NULL, true );
		wp_register_style( 'zume-course', plugin_dir_url(__FILE__) . 'css/zume-course.css', false, NULL, 'all' ); // Relocated into the _main.scss theme file

		wp_enqueue_script( 'jquery-steps' );
		wp_enqueue_style( 'zume-course' );
	}

    /**
     * Get the name of a group by a supplied group id
     * @return string
     */
	public function zume_get_group_name ($group_id) {
        $group = groups_get_group($group_id); // gets group object
        return $group->name;
    }

    /**
     * Jquery Steps with configuration
     * @return mixed
     */
	public function jquery_steps ($group_id, $session_number) {

	    // Create variables
	    $visited = true;
	    $completed = false;
	    $last_step = null;

        $root =  home_url("/wp-json/");

        $nonce = wp_create_nonce( 'wp_rest' );
        $dashboard_complete = home_url("/dashboard/") ;
        $dashboard_complete_next = home_url("/zume-training/") . '?group_id=' . $group_id . '&id=' . $session_number . '&wp_nonce=' . $nonce;
        $success =  __( 'Session Complete! Congratulations!', 'zume' );
		$failure =  __( 'Could not track your progress. Yikes. Tell us and we will tell our geeks to get on it!', 'zume' );

        // Get list of members attending the group
        $group_members_result = groups_get_group_members( $args = array('group_id' => $group_id, 'exclude_admins_mods' => false) );
        $group_members = array();
        foreach(  $group_members_result['members'] as $member ) {
            $group_members[] = $member->ID;
        }
//        $group_members_ids = implode(", ", $group_members);
        // end Get list of members

		// Create Javascript HTML
        $html = '';
        $html .= '<script>
                    jQuery(document).ready(function() {
                        jQuery("';

        $html .= '#session'.$session_number.'-'. $group_id; // Create selector

        $html .= '").steps({
                    headerTag: "h3",
                    bodyTag: "section",
                    transitionEffect: "fade",
                    saveState: true,
                    autofocus: true,';

        if ($completed) { $html .= 'enableAllSteps: true,'; }
        elseif ($visited && $last_step != null) { $html .= 'startIndex: '. $last_step . ','; }

        // Fire record creation on step change
        $html .=    'onStepChanging: function (event, currentIndex, newIndex) {
                       
                       if (currentIndex === 0) { /* check attendance requirement */
                            var n = jQuery( "input:checked" ).length;
                            if ( n < 4 ) {
                            return false;
                            }
                       }
                       return true;
                       
                    },
                    
                    '; // end html block

        // Fire record creation on step change
        $html .=    'onStepChanged: function (event, currentIndex, priorIndex) {
        
                        if (currentIndex === 1 && priorIndex === 0) { /* record attendance */
                            
                            var members = '.json_encode($group_members).';
                            var session = \''.$session_number.'\';
                            var group_id = \''. $group_id . '\';
                        
                            var data = {
                                members: members,
                                session: session,
                                group_id: group_id
                            };
                        
                            jQuery.ajax({
                            method: "POST",
                            url: \''. $root .'\' + \'zume/v1/attendance/log\',
                            data: data,
                            beforeSend: function ( xhr ) {
                                xhr.setRequestHeader( \'X-WP-Nonce\', \''.$nonce.'\' );
                            },
                            fail : function( response ) {
                                console.log( response );
                                alert( \''.$failure.'\' );
                            }
                
                        });
                        }
                       
                       var title = "Group-" + "'. $group_id. '" + " Step-" + currentIndex + " Session-" + "'. $session_number . '" ;
                       var status = \'publish\';
                       
                       var data = {
                            title: title,
                            status: status
                        }; 
                       
                       jQuery.ajax({
                            method: "POST",
                            url: \''. $root .'\' + \'wp/v2/steplog\',
                            data: data,
                            beforeSend: function ( xhr ) {
                                xhr.setRequestHeader( \'X-WP-Nonce\', \''.$nonce.'\' );
                            },
                            fail : function( response ) {
                                console.log( response );
                                alert( \''.$failure.'\' );
                            }
                
                        });
                    },
                    
                    '; // end html block

        // Fire a session completed record creation
        $html .= '  onFinishing: function (event, currentIndex) {

                       var title = "Group-" + "'. $group_id. '" + " Step-Complete" + " Session-" + "'. $session_number . '" ;
                       var excerpt = "'. $session_number . '";
                       var status = \'publish\';
                       
                       var data = {
                            title: title,
                            excerpt: excerpt,
                            status: status
                        }; 
                       
                       jQuery.ajax({
                            method: "POST",
                            url: \''. $root .'\' + \'wp/v2/steplog\',
                            data: data,
                            beforeSend: function ( xhr ) {
                                xhr.setRequestHeader( \'X-WP-Nonce\', \''.$nonce.'\' );
                            },
                            success : function( response ) {
                                
                                window.location.replace("' . $dashboard_complete . '"); 
                            },
                            fail : function( response ) {
                                console.log( response );
                                alert( \''.$failure.'\' );
                            }
                
                        });
                    },
                    
                    '; // end html block

        $html .= "  titleTemplate: '<span class=\"number\">#index#</span> #title#'";



        $html .= '    });
                    });
        
                </script>
                '; // end html block

        return $html;
    }

    public function attendance_step ($group_id, $session) {

	    $html = '';
        $html .= '<h3></h3>
                    <section>

                    <div class="row block">
                        <div class="step-title">WHO\'S WITH YOU?</div> <!-- step-title -->
                    </div> <!-- row -->
                    <!-- Activity Block  -->
                    <div class="row "><div class="small-12 medium-6 small-centered columns">
                        ';

        $html .= $this->get_attendance_list($group_id, $session);

        $html .= '</div></div> <!-- row --> </section>';

        return $html;
    }

    public function get_attendance_list($group_id, $session) {
        $html = '';
        $html .= '<style>
                    li.attendance-list {padding:10px;}
                    li#count {text-align:center;}
        </style>';

        if ( bp_group_has_members( array('group_id' => $group_id, 'group_role' => array('admin', 'mod', 'member')) ) ) :
            $html .= '<ul id="attendance-list" style="list-style-type: none;">';


        while ( bp_group_members() ) : bp_group_the_member();

                $html .= '<li class="attendance-list"><div class="switch" style="width:100px; float:right;">
                          <input class="switch-input" id="member-'.bp_get_group_member_id().'" type="checkbox" name="'. bp_get_group_member_id() .'">
                          <label class="switch-paddle" for="member-'.bp_get_group_member_id().'">
                            <span class="show-for-sr">' . bp_get_group_member_name() . '</span>
                          </label>
                          </div>' . bp_get_group_member_name() . '</li>';


                endwhile;
            $html .= '<li id="count"></li>';
            $html .= '</ul>';
        endif;

        $html .= "  <script>
  
                        jQuery(document).ready(function () {
                            
                            var countChecked = function() {
                                var n = jQuery( \"input:checked\" ).length;
                                
                                if( n < 4 ) { 
                                    var missing = 4 - n;  
                                    if (missing === 1) {
                                        jQuery( '#count' ).text( missing + ' more is needed!' );
                                    } else {
                                        jQuery( '#count' ).text( missing + ' more are needed!' );
                                    }
                                } else {
                                    jQuery( '#count' ).text( '' );
                                }
                            };
                            countChecked();
 
                            jQuery( \"input[type=checkbox]\" ).on( \"click\", countChecked );
                            
                        });
    
                    </script>
        ";

	    return $html;

    }

}
