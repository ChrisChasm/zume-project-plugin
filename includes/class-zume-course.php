<?php

/**
 * Zume Course Core
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
     * Zume Pre Content Load
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




        /*** PROCESS INPUTS ***/

        /**
         *
         * First check if a change has been made to the active group selection
         * or if a new group session is being requested from the dashboard.
         *
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

            // Load Zume content with variables
            $this->content_loader($zume_session, $new_group_id );
        }
        /**
         *
         * Second check if there is no active group associated with the the user or
         * a request has been made by the users to switch the active group
         *
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

                // Load Zume content with variables
                $this->content_loader($zume_session, $new_group_id );
            }



            /**
             *
             * Last, pull current active group from user meta and load content according to active group.
             *
             *
             */
        } else {

            $new_group_id = get_user_meta($user_id, $meta_key, true);

            // Load Zume content with variables
            $this->content_loader($zume_session, $new_group_id );
        }

        /**
         * Create switch group link
         *
         */
        // Check for number of groups
        $user_groups = bp_get_user_groups( bp_loggedin_user_id(), array( 'is_admin' => null, 'is_mod' => null, ) );

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
	 * Zume Content Loader
	 * @return mixed
	 */
	public function content_loader ( $session = '1', $group_id ) {

	    // Check for highest session completed and redirect
        $next_session = zume_group_next_session($group_id);
        if ($session > $next_session || $session == 'overview') {

            if($session == 'overview') { $session = 1;}

            echo $this->zume_sessions_overview($group_id, $next_session, $session);

        } else {
            // Select content
            switch ($session) {
                case '1':
                    echo $this->zume_session_1_content($group_id);
                    break;
                case '2':
                    echo $this->zume_session_2_content($group_id);
                    break;
                case '3':
                    echo $this->zume_session_3_content($group_id);
                    break;
                case '4':
                    echo $this->zume_session_4_content($group_id);
                    break;
                case '5':
                    echo $this->zume_session_5_content($group_id);
                    break;
                case '6':
                    echo $this->zume_session_6_content($group_id);
                    break;
                case '7':
                    echo $this->zume_session_7_content($group_id);
                    break;
                case '8':
                    echo $this->zume_session_8_content($group_id);
                    break;
                case '9':
                    echo $this->zume_session_9_content($group_id);
                    break;
                case '10':
                    echo $this->zume_session_10_content($group_id);
                    break;
            }
        }
	}

	/**
	 * Enqueue scripts and styles
	 * @return mixed
	 */
	public function zume_scripts_enqueue () {
		wp_register_script( 'jquery-steps', plugin_dir_url(__FILE__) . 'js/jquery.steps.js', array('jquery'), NULL, true );
		wp_register_style( 'zume-course', plugin_dir_url(__FILE__) . 'css/zume-course.css', false, NULL, 'all' );

		wp_enqueue_script( 'jquery-steps' );
		wp_enqueue_style( 'zume-course' );

//		wp_enqueue_script( 'bootstrap-js' );
//		wp_enqueue_style( 'bootstrap-css' );
//		wp_register_script( 'bootstrap-js', '://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'), NULL, true );
//		wp_register_style( 'bootstrap-css', '://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', false, NULL, 'all' );
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
     * Pulls the content from the pages database
     * @return string
     */
    public function zume_session_overview_content ($session) {

	    $session_title = 'Session ' . $session . ' Overview';
        $page_object = get_page_by_title( $session_title, OBJECT, 'page' );


        if (! empty($page_object) || ! empty($page_object->post_content)) {
            $page_content = (string) $page_object->post_content;
            echo $page_content;
        }
        else {
            print 'Please republish "'.$session_title.'" with content for this section in the pages administration area.';
        }
    }

    /**
     * Zume Overview
     * @return mixed
     */
    public function zume_sessions_overview ($group_id, $next_session, $session) {
        ?>
        <h2 class="center padding-bottom">Sessions Overview</h2>

        <script>
            jQuery(document).ready(function () {

                jQuery("#overview").steps({
                    // Disables the finish button (required if pagination is enabled)
                    enableFinishButton: false,
                    // Disables the next and previous buttons (optional)
                    enablePagination: false,
                    // Enables all steps from the begining
                    enableAllSteps: true,
                    // Removes the number from the title
//                    titleTemplate: "#title#",
                    startIndex: <?php echo $session - 1;?>,
                    headerTag: "h3",
                    bodyTag: "section",
                    transitionEffect: "fade",
                    autoFocus: true
                });
            });

        </script>

        <div id="overview">
            <h3></h3>
            <section>
                <h3>Session 1 Overview</h3>
                <?php print $this->zume_session_overview_content (1) ?>
            </section>

            <h3></h3>
            <section>
                <h3>Session 2 Overview</h3>
                <?php print $this->zume_session_overview_content (2) ?>
            </section>

            <h3></h3>
            <section>
                <h3>Session 3 Overview</h3>
                <?php print $this->zume_session_overview_content (3) ?>
            </section>

            <h3></h3>
            <section>
                <h3>Session 4 Overview</h3>
                <?php print $this->zume_session_overview_content (4) ?>
            </section>

            <h3></h3>
            <section>
                <h3>Session 5 Overview</h3>
                <?php print $this->zume_session_overview_content (5) ?>
            </section>

            <h3></h3>
            <section>
                <h3>Session 6 Overview</h3>
                <?php print $this->zume_session_overview_content (5) ?>
            </section>

            <h3></h3>
            <section>
                <h3>Session 7 Overview</h3>
                <?php print $this->zume_session_overview_content (6) ?>
            </section>

            <h3></h3>
            <section>
                <h3>Session 8 Overview</h3>
                <?php print $this->zume_session_overview_content (8) ?>
            </section>

            <h3></h3>
            <section>
                <h3>Session 9 Overview</h3>
                <?php print $this->zume_session_overview_content (9) ?>
            </section>

            <h3></h3>
            <section>
                <h3>Session 10 Overview</h3>
                <?php print $this->zume_session_overview_content (10) ?>
            </section>

        </div>

        <div class="callout">
            <p class="center padding-bottom">Your next session is <strong>Session <?php echo $next_session; ?></strong> for your <strong><?php echo $this->zume_get_group_name($group_id); ?></strong> </p>
            <p class="center"><a href="<?php echo home_url("/zume-training/") . "?id=" . $next_session ?>" class="button large">Start Your Next Session</a></p>
        </div>


        <?php
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
        $html .=    'onStepChanged: function (event, currentIndex) {
                       
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
                    }
                    
                    '; // end html block



        $html .= '    });
                    });
        
                </script>
                '; // end html block

        return $html;
    }

    /**
     * Zume Session 1
     * @return mixed
     */
    public function zume_session_1_content ($group_id) {
        $session_number = 1;
        ?>

        <?php echo $this->jquery_steps($group_id, $session_number); ?>

        <div class="row"><div class="small-centered columns center" style="color: #ccc;">Session 1</div></div>
            <div id="session1-<?php echo $group_id; ?>">

            <h3></h3>
            <section>
                <!-- Step Title -->
                <div class="row block">
                    <div class="step-title">
                        <?php echo __('WELCOME TO ZÚME', 'ZUME_DOMAIN'); ?>
                    </div> <!-- step-title -->
                </div> <!-- row -->
                <!-- Activity Block  -->
                <div class="row block single">
                    <div class="activity-description well"><?php echo __('DOWNLOAD', 'ZUME_DOMAIN'); ?><br><br><?php echo __('You will be able to follow along on a digital PDF for this session, but please make sure that each member of your group has a printed copy of the materials for future sessions.', 'ZUME_DOMAIN'); ?>
                    </div>
                    <div class="activity-description">
                        <a href="https://s3.amazonaws.com/zume/zume-guidebook-7229020000.pdf" class="btn btn-large next-step zume-purple uppercase bg-white font-zume-purple big-btn btn-wide" target="_blank"><i class="glyphicon glyphicon-download-alt"></i> <span> HANDBOOK</span></a>
                    </div>
                </div> <!-- row -->

            </section>

            <h3></h3>
            <section>
                <!-- Step Title -->
                <div class="row block">
                    <div class="step-title"><?php echo __('PRAYER (5min)', 'ZUME_DOMAIN'); ?></div> <!-- step-title -->
                </div> <!-- row -->
                <!-- Activity Block  -->
                <div class="row block single">
                    <div class="activity-description well"><?php echo __('GROUP PRAYER', 'ZUME_DOMAIN'); ?><br><br><?php echo __('Begin with prayer. Spiritual insight and transformation is not possible without the Holy Spirit. n/n/ Take time as a group to invite Him to guide you over this session. ', 'ZUME_DOMAIN'); ?>
                    </div>
                </div> <!-- row -->
            </section>

            <h3></h3>
            <section>
                <!-- Step Title -->
                <div class="row block">
                    <div class="step-title">
                        <?php echo __('WATCH AND DISCUSS (15min)', 'ZUME_DOMAIN'); ?>
                    </div> <!-- step-title -->
                </div> <!-- row -->

                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title"><?php echo __('WATCH', 'ZUME_DOMAIN'); ?></div>
                    <div class="activity-description"><?php echo __('God uses ordinary people doing simple things to make a big impact. Watch this video on how God works.', 'ZUME_DOMAIN'); ?></div>
                </div> <!-- row -->

                <!-- Video block -->
                <div class="row block">
                    <!--<script src="//fast.wistia.com/embed/medias/fe3w7ebpl4.jsonp" async></script>
                    <script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
                    <div class="wistia_embed wistia_async_fe3w7ebpl4" >&nbsp;</div>-->
                    <img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
                </div> <!-- row -->

                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title"> DISCUSS </div>
                    <div class="activity-description">If Jesus intended every one of His followers to obey His Great Commission, why do so few actually make disciples?</div>
                </div> <!-- row -->


            </section>

            <h3></h3>
            <section>
                <!-- Step Title -->
                <div class="row block">
                    <div class="step-title">
                        WATCH AND DISCUSS (15min)
                    </div> <!-- step-title -->
                </div> <!-- row -->

                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title">WATCH</div>
                    <div class="activity-description">What is a disciple? And how do you make one? How do you teach a follower of Jesus to do what He told us in His Great Commission - to obey all of His commands?</div>
                </div> <!-- row -->

                <!-- Video block -->
                <div class="row block">
                    <!--<script src="//fast.wistia.com/embed/medias/pzq41gvam6.jsonp" async></script>
                    <script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
                    <div class="wistia_embed wistia_async_pzq41gvam6" >&nbsp;</div>-->
                    <img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
                </div> <!-- row -->

                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title">DISCUSS</div>
                    <div class="activity-description">
                        <ol class="rectangle-list"><li><span>When you think of a church, what comes to mind?</span></li>
                            <li><span>What's the difference between that picture and what's described in the video as a "Simple Church"?</span></li>
                            <li><span>Which one do you think would be easier to multiply and why?</span></li></ol>
                    </div>
                </div> <!-- row -->



            </section>


            <h3></h3>
            <section>
                <!-- Step Title -->
                <div class="row block">
                    <div class="step-title">
                        WATCH AND DISCUSS (15min)
                    </div> <!-- step-title -->
                </div> <!-- row -->

                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title">WATCH</div>
                    <div class="activity-description">We breathe in. We breathe out. We're alive. Spiritual Breathing is like that, too.</div>
                </div> <!-- row -->

                <!-- Video block -->
                <div class="row block">
                    <!--<script src="//fast.wistia.com/embed/medias/67sh299w6m.jsonp" async></script>
                    <script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
                    <div class="wistia_embed wistia_async_67sh299w6m" >&nbsp;</div>-->
                    <img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
                </div> <!-- row -->

                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title">DISCUSS</div>
                    <div class="activity-description">
                        <ol class="rectangle-list"><li>Why is it essential to learn to hear and recognize God's voice?</li>
                            <li>Is hearing and responding to the Lord really like breathing? Why or why not?</li>
                        </ol>
                    </div>
                </div> <!-- row -->
            </section>


            <h3></h3>
            <section>
                <div class="row block">
                    <div class="step-title">
                        LISTEN AND READ ALONG (3min)
                    </div> <!-- step-title -->
                </div> <!-- row -->

                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title">READ</div>
                    <div class="activity-description">S.O.A.P.S. BIBLE READING<br><br>Hearing from God regularly is a key element in our personal relationship with Him, and in our ability to stay obediently engaged in what He is doing around us.<br><br>Find the "S.O.A.P.S. Bible Reading" section in your Zúme Guidebook and listen to the audio overview.</div>
                </div> <!-- row -->

                <!-- Video block -->
                <div class="row block">
                    <!--<script src="//fast.wistia.com/embed/medias/i5fwo662go.jsonp" async></script>
                    <script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
                    <div class="wistia_embed wistia_async_i5fwo662go" >&nbsp;</div>-->
                    <img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
                </div> <!-- row -->


            </section>


            <h3></h3>
            <section>
                <div class="row block">
                    <div class="step-title">
                        LISTEN AND READ ALONG (3min)
                    </div> <!-- step-title -->
                </div> <!-- row -->
                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title">READ</div>
                    <div class="activity-description">ACCOUNTABILITY GROUPS<br><br>The Bible tells us that every follower of Jesus will one day be held accountable for what we do and say and think. Accountability Groups are a great way to get ready!<br><br>Find the "Accountability Groups" section in your Zúme Guidebook, and listen to the audio below.</div>
                </div> <!-- row -->
                <!-- Video block -->
                <div class="row block">
                    <!--<script src="//fast.wistia.com/embed/medias/1zl3h2clam.jsonp" async></script>
                    <script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
                    <div class="wistia_embed wistia_async_1zl3h2clam" >&nbsp;</div>-->
                    <img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
                </div> <!-- row -->
            </section>


            <h3></h3>
            <section>
                <!-- Step Title -->
                <div class="row block">
                    <div class="step-title">
                        PRACTICE (45min)
                    </div> <!-- step-title -->
                </div> <!-- row -->
                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title"><span>BREAK UP</span></div>
                    <div class="activity-description"><br>Break into groups of two or three people of the same gender.<br><br></div>
                </div> <!-- row -->
                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title"><span>SHARE</span></div>
                    <div class="activity-description">Spend the next 45 minutes working together through Accountability Questions - List 2 in the "Accountability Groups" section of your Zúme Guidebook. <br><br><a href="https://s3.amazonaws.com/zume/zume-guidebook-7229020000.pdf" class="btn btn-large next-step zume-purple uppercase bg-white font-zume-purple big-btn btn-wide" target="_blank"><i class="glyphicon glyphicon-download-alt"></i> <span> HANDBOOK</span></a></div>
                </div> <!-- row -->
            </section>


            <h3></h3>
            <section>
                <!-- Step Title -->
                <div class="row block">
                    <div class="step-title">
                        LOOKING FORWARD
                    </div> <!-- step-title -->
                    <div class="center">Congratulations! You've completed Session 1. Below are next steps to take in preparation for the next session. </div>
                </div> <!-- row -->

                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title">OBEY</div>
                    <div class="activity-description">Begin practicing the S.O.A.P.S. Bible reading between now and your next meeting. Focus on Matthew 5-7, read it at least once a day. Keep a daily journal using the S.O.A.P.S. format.</div>
                </div> <!-- row -->
                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title">SHARE</div>
                    <div class="activity-description">Spend time asking God who He might want you to start an Accountability Group with using the tools you've learned in this session. Share this person’s name with the group before you go, and make plans to reach out to them this week.</div>
                </div> <!-- row -->
                <!-- Activity Block  -->
                <div class="row block">
                    <div class="activity-title">PRAY</div>
                    <div class="activity-description">Pray that God helps you be obedient to Him and invite Him to work in you and those around you!</div>
                </div> <!-- row -->
            </section>
            </div>

        <?php
    }


	/**
	 * Zume Session 2
	 * @return mixed
	 */
	public function zume_session_2_content ($group_id) {
        $session_number = 2;
        ?>

        <?php echo $this->jquery_steps($group_id, $session_number); ?>

		<div class="row "><div class="small-centered columns" style="text-align: center; color: #ccc;">Session 2</div></div>
        <div id="session<?php echo $session_number . "-" . $group_id; ?>">
			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WELCOME BACK!
					</div> <!-- step-title -->
				</div> <!-- row -->

				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>DOWNLOAD</span></div>
					<div class="activity-description">Does everyone have a printed copy of the Zúme Guidebook? If not, please be sure that someone can download the Guidebook and that everyone has access to some paper and a pen or pencil.
						<br><br>
						<a href="https://s3.amazonaws.com/zume/zume-guidebook-7229020000.pdf" class="btn btn-large next-step zume-purple uppercase bg-white font-zume-purple big-btn btn-wide" target="_blank"><i class="glyphicon glyphicon-download-alt"></i> <span> HANDBOOK</span></a>
					</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>CHECK-IN</span></div>
					<div class="activity-description">Before we get started, let's take some time to check-in.<br><br>At the end of our last session, everyone in your group was challenged in two ways: <br><br>
						<ol><li>You were asked to begin practicing the S.O.A.P.S. Bible reading method and keeping a daily journal.</li><li>You were encouraged to reach out to someone about starting an Accountability Group.</li></ol> Take a few moments to see how your group did this week.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRAY</span></div>
					<div class="activity-description">Ask if anyone in the group has specific needs they'd like the group to pray for. Ask someone to pray and ask God to help in the areas the group shared. Be sure to thank God that He promises in His Word to listen and act when His people pray. And, as always, ask God's Holy Spirit to lead your time, together.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WATCH AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->

				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">If we want to make disciples who multiply - spiritual producers and not just consumers - then we need to learn and share four main ways God makes everyday followers more like Jesus:<br><br>
						<ul><li>Prayer</li>
							<li>God's Word</li>
							<li>Living as God's People</li>
							<li>Persecution and Suffering</li></ul></div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/degdhfsycm.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_degdhfsycm" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->

				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description"><ol><li>Of the four areas detailed above (prayer, God's Word, etc.), which ones do you already practice?</li><li> Which ones do you feel unsure about?</li><li> How ready do you feel when it comes to training others?</li></ol></div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<div class="row block">
					<div class="step-title">
						LISTEN AND READ ALONG (2min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">READ</div>
					<div class="activity-description">ZÚME TOOLKIT - PRAYER CYCLE<br><br>
						The Bible tells us that prayer is our chance to speak to and hear from the same God who created us!<br><br>Find the "Prayer Cycle" section in your Zúme Guidebook, and listen to the audio below.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/1995yry849.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_1995yry849" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						PRACTICE (60min)
					</div> <!-- step-title -->
					<div class="center">Practice the Prayer Cycle for 60 minutes. </div>
				</div> <!-- row -->

				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">LEAVE</div>
					<div class="activity-description">Spend the next 60 minutes in prayer individually, using the exercises in "The Prayer Cycle" section of the Zúme Guidebook as a guide.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">RETURN</div>
					<div class="activity-description">Set a time for the group to return and reconnect. Be sure to add a few extra minutes for everyone to both find a quiet place to pray and to make their way back to the group.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description">
						<ol><li>What is your reaction to spending an hour in prayer?</li>
							<li>How do you feel?</li>
							<li>Did you learn or hear anything?</li>
							<li>What would life be like if you made this kind of prayer a regular habit?</li>
						</ol>
					</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- LISTEN AND READ ALONG -->
				<div class="row block">
					<div class="step-title">
						LISTEN AND READ ALONG (3min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">READ</div>
					<div class="activity-description">ZÚME TOOLKIT - LIST OF 100<br><br>God has already given us the relationships we need to “Go and make disciples.” These are our family, friends, neighbors, co-workers and classmates - people we’ve known all our lives or maybe just met.<br><br>
						Being good stewards of these relationships is the first step in multiplying disciples. Start by making a list.<br><br>
						Find the "List of 100" section in your Zúme Guidebook, and listen to the audio below.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/pzcavp72zy.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_pzcavp72zy" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Single column template -->
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">PROJECT (30min)</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block single">
					<div class="activity-description well well-lg">CREATE YOUR OWN LIST OF 100<br><br>Have everyone in your group take the next 30 minutes to fill out their own inventory of relationships using the form in the "List of 100" section in your Zúme Guidebook. 
					</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING FORWARD
					</div> <!-- step-title -->
					<div class="center">Congratulations on finishing Session 2! Below are next steps to take in preparation for the next session.</div>
				</div> <!-- row -->

				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">OBEY</div>
					<div class="activity-description">Spend time this week praying for 5 people from your List of 100 that you marked as an "Unbeliever" or "Unknown." Ask God to prepare their hearts to be open to His story.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">SHARE</div>
					<div class="activity-description">Think about what you have heard and learned in this session, and ask God who He wants you to share it with. Share this person’s name with the group before you go.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">PRAY</div>
					<div class="activity-description">Pray that God help you be obedient to Him and invite Him to work in you and those around you!</div>
				</div> <!-- row -->
			</section>
		</div>

		<?php
	}

	/**
	 * Zume Session 3
	 * @return mixed
	 */
	public function zume_session_3_content ($group_id) {
        $session_number = 3;
        ?>

        <?php echo $this->jquery_steps($group_id, $session_number); ?>
		<div class="row "><div class="small-centered columns" style="text-align: center; color: #ccc;">Session 3</div></div>
        <div id="session<?php echo $session_number . "-" . $group_id; ?>">
			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING BACK
					</div> <!-- step-title -->
					<div class="center">Welcome back to Zúme Training!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>CHECK-IN</span></div>
					<div class="activity-description">Before we get started, let's take some time to check-in.<br><br>At the end of our last session, everyone in your group was challenged in two ways: <br><br>
						<ol><li>You were asked to pray for 5 people from your List of 100 that you marked as an "Unbeliever" or "Unknown."</li><li>You were encouraged to share how to make a List of 100 with someone.</li></ol> Take a few moments to see how your group did this week.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRAY</span></div>
					<div class="activity-description">Pray and thank God for the results and invite His Holy Spirit to lead your time together.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>OVERVIEW</span></div>
					<div class="activity-description">In this session, we’ll learn how God’s Spiritual Economy works and how God invests more in those who are faithful with what they've already been given. We’ll also learn two more tools for making disciples - sharing God’s Story from Creation to Judgement and Baptism.<br><br>Then, when you're ready, let's get started!</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>

				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WATCH AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">In this broken world, people feel rewarded when they take, when they receive and when they gain more than those around them. But God's Spiritual Economy is different - God invests more in those who are faithful with what they've already been given.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/63g4lcmbjf.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_63g4lcmbjf" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description">What are some differences you see between God's Spiritual Economy and our earthly way of getting things done?</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>

				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						READ AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">READ</div>
					<div class="activity-description">Jesus said -- “you will receive power when the Holy Spirit comes upon you. And you will be my witnesses, telling people about me everywhere--in Jerusalem, throughout Judea, in Samaria, and to the ends of the earth.”<br><br>
						Jesus believed in His followers so much, He trusted them to tell His story. Then He sent them around the world to do it. Now, He’s sending us.<br><br>
						There’s no one “best way” to tell God’s story (also called The Gospel), because the best way will depend on who you’re sharing with. Every disciple should learn to tell God’s Story in a way that’s true to scripture and connects with the audience they’re sharing with.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description"><ol><li>What comes to mind when you hear God's command to be His "witness" and to tell His story?</li><li> Why do you think Jesus chose ordinary people instead of some other way to share His Good News?</li> <li>What would it take for you to feel more comfortable sharing God's Story?</li></ol></div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>

				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WATCH AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">One way to share God’s Good News is by telling God’s Story from Creation to Judgement - from the beginning of humankind all the way to the end of this age.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/0qq5iq8b2i.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_0qq5iq8b2i" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description"><ol><li>What do you learn about mankind from this story?</li><li>What do you learn about God?</li><li>Do you think it would be easier or harder to share God's Story by telling a story like this?</li></ol></div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						PRACTICE (45min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block single">
					<div class="activity-description well">PRACTICE SHARING GOD'S STORY<br><br>Break into groups of two or three people and spend the next 45 minutes practicing telling God's Story using the Activity instructions on page 13 of your Zúme Guidebook.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>

				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						READ AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">READ</div>
					<div class="activity-description">ZÚME TOOLKIT - BAPTISM<br><br>
						Jesus said -- “go and make disciples of all nations, BAPTIZING them in the name of the Father and of the Son and of the Holy Spirit…”<br><br>
						Find the "Baptism" section in your Zúme Guidebook, and listen to the audio below.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/v8p5mbpdp5.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_v8p5mbpdp5" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description"><ol><li>Have you ever baptized someone? </li><li>Would you even consider it?</li><li> If the Great Commission is for every follower of Jesus, does that mean every follower is allowed to baptize others? Why or why not?</li></ol></div>
				</div> <!-- row -->
				<div class="row block single"><div class="activity-description well">IMPORTANT REMINDER - Have you been baptized? If not, then we encourage you to plan this before even one more session of this training. Invite your group to be a part of this important day when you celebrate saying "yes" to Jesus.</div></div>
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING FORWARD
					</div> <!-- step-title -->
					<div class="center">Congratulations on finishing Session 3! Below are next steps to take in preparation for the next session. </div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">OBEY</div>
					<div class="activity-description">Spend time this week practicing God's Story, and then share it with at least one person from your List of 100 that you marked as "Unbeliever" or "Unknown."</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">SHARE</div>
					<div class="activity-description">Think about a few things you have heard and learned in this session, and ask God who He wants you to share it with. Share this person’s name with the group before you go.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">PRAY</div>
					<div class="activity-description">Pray that God help you be obedient to Him and invite Him to work in you and those around you!</div>
				</div> <!-- row -->
				<div class="row block single"><div class="activity-description well">IMPORTANT REMINDER - Your group will be celebrating the Lord's Supper next session. Be sure to remember the supplies (bread and wine / juice).</div></div>
			</section>
		</div>

		<?php
	}

	/**
	 * Zume Session 4
	 * @return mixed
	 */
	public function zume_session_4_content ($group_id) {
        $session_number = 1;
        ?>

        <?php echo $this->jquery_steps($group_id, $session_number); ?>
		<div class="row "><div class="small-centered columns" style="text-align: center; color: #ccc;">Session 4</div></div>
        <div id="session<?php echo $session_number . "-" . $group_id; ?>">
			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING BACK
					</div> <!-- step-title -->
					<div class="center">Welcome back to Zúme Training!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>CHECK-IN</span></div>
					<div class="activity-description">At the end of our last session, everyone in your group was challenged in two ways.<br><br>
						<ol><li>you were asked to share God’s Story with at least one person from your List of 100 that you marked as "Unbeliever" or "Unknown."</li><li>you were encouraged to train someone to use the Creation to Judgement story (or some other way to share God’s Story) with someone. </li></ol>Take a few moments to see how your group did this week.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRAY</span></div>
					<div class="activity-description">Pray and thank God for the results and invite His Holy Spirit to lead your time together.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>OVERVIEW</span></div>
					<div class="activity-description">In this session, we'll learn how God's plan is for every follower to multiply! We’ll discover how disciples multiply far and fast when they start to see where God’s Kingdom isn’t. And, we'll learn another great tool for inviting others into God's family is as simple as telling our story.<br><br>Then, when you're ready, let's get started!</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- LISTEN AND READ ALONG -->

				<div class="row block">
					<div class="step-title">
						LISTEN AND READ ALONG (3min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">READ</div>
					<div class="activity-description">ZÚME TOOLKIT - 3-MINUTE TESTIMONY<br><br>As followers of Jesus, we are “witnesses" for Him, because we “testify” about the impact Jesus has had on our lives. Your story of your relationship with God is called your Testimony. It's powerful, and it's something no one can share better than you.<br><br>Find the 3-Minute Testimony section in your Zúme Guidebook, and listen to the audio below.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/kwhpgugafp.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_kwhpgugafp" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						PRACTICE (45min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block single">
					<div class="activity-description well">PRACTICE SHARING YOUR TESTIMONY<br><br>Break into groups of two or three and and spend the next 45 minutes practicing sharing your Testimony using the Activity instructions on page 15 of your Zúme Guidebook.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>

				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WATCH AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">What is God's greatest blessing for His children? Making disciples who multiply! <br><br>What if you could learn a simple pattern for making not just one follower of Jesus but entire spiritual families who multiply for generations to come?</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/qbfpcb1ta8.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_qbfpcb1ta8" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description"><ol><li>Is this the pattern you were taught when you first began to follow Jesus? If not, what was different? </li><li>After you came to faith, how long was it before you began to disciple others?</li><li> What do you think would happen if new followers started sharing and discipling others, immediately?</li></ol></div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WATCH AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->

				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">Have you ever stopped to think about where God's Kingdom... isn't?<br><br>Have you ever visited a home or a neighborhood or even a city where it seemed as if God was just... missing? These are usually the places where God wants to work the most.</div>
				</div> <!-- row -->

				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/aii2k283nk.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_aii2k283nk" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->

				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description">
						<ol><li>Who are you more comfortable sharing with - people you already know or people you haven't met, yet? </li>
							<li>Why do you think that is? </li>
							<li>How could you get better at sharing with people you're less comfortable with?</li>
						</ol>
					</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<div class="row block">
					<div class="step-title">
						LISTEN AND READ ALONG (3min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">READ</div>
					<div class="activity-description">ZÚME TOOLKIT - THE LORD'S SUPPER<br><br>Jesus said - “I am the living bread that came down from heaven. Whoever eats this bread will live forever. This bread is my flesh, which I will give for the life of the world.”<br><br>Find "The Lord's Supper" section in your Zúme Guidebook, and listen to the audio below.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/t3xr5w43av.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_t3xr5w43av" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						PRACTICE (10min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block single">
					<div class="activity-description well">PRACTICE THE LORD'S SUPPER<br><br>Spend the next 10 minutes celebrating The Lord's Supper with your group using the pattern on page 15 of your Zúme Guidebook.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING FORWARD
					</div> <!-- step-title -->
					<div class="center">Congratulations on finishing Session 4! Below are next steps to take in preparation for the next session. </div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">OBEY</div>
					<div class="activity-description">Spend time this week practicing your 3-Minute Testimony, and then share it with at least one person from your List of 100 that you marked as "Unbeliever" or "Unknown."</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">SHARE</div>
					<div class="activity-description">Think about what you have heard and learned in this session, and ask God who He wants you to share it with. Share this person’s name with the group before you go.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">PRAY</div>
					<div class="activity-description">Pray that God help you be obedient to Him and invite Him to work in you and those around you!</div>
				</div> <!-- row -->
			</section>

		</div>

		<?php
	}

	/**
	 * Zume Session 5
	 * @return mixed
	 */
	public function zume_session_5_content ($group_id) {
        $session_number = 1;
        ?>

        <?php echo $this->jquery_steps($group_id, $session_number); ?>
		<div class="row "><div class="small-centered columns" style="text-align: center; color: #ccc;">Session 5</div></div>
        <div id="session<?php echo $session_number . "-" . $group_id; ?>">
			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING BACK
					</div> <!-- step-title -->
					<div class="center">Welcome back to Zúme Training!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>CHECK-IN</span></div>
					<div class="activity-description">At the end of our last session, everyone in your group was challenged in two ways. First,  Second, <br><br>
						<ol><li>You were asked to share your 3-Minute Testimony with at least one person on your List of 100.</li><li>You were encouraged to train someone else with the 3-Minute Testimony tool. </li></ol>Take a few moments to see how your group did this week.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRAY</span></div>
					<div class="activity-description">Pray and thank God for the results and invite His Holy Spirit to lead your time together.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>OVERVIEW</span></div>
					<div class="activity-description">In this session, we’ll learn how Prayer Walking is a powerful way to prepare a neighborhood for Jesus, and we'll learn a simple but powerful pattern for prayer that will help us meet and make new disciples along the way.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<div class="row block">
					<div class="step-title">
						LISTEN AND READ ALONG (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">READ</div>
					<div class="activity-description">ZÚME TOOLKIT - PRAYER WALKING<br><br>Prayer Walking is a simple way to obey God’s command to pray for others. And it's just what it sounds like - praying to God while walking around!<br><br>Find the "Prayer Walking" section in your Zúme Guidebook, and listen to the audio below.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/ltxoicq440.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_ltxoicq440" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						PRACTICE (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block single">
					<div class="activity-description well">PRACTICE THE B.L.E.S.S. PRAYER<br><br>Break into groups of two or three and spend the next 15 minutes practicing the B.L.E.S.S. Prayer using the pattern on page 17 of your Zúme Guidebook. Practice praying the 5 areas of the B.L.E.S.S. Prayer for someone AND practice how you would train others to understand and use the B.L.E.S.S. Prayer, too.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						PRACTICE (60-90min)
					</div> <!-- step-title -->
					<div class="center">Practice Prayer Walking for 60-90 minutes.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">ACTIVITY</div>
					<div class="activity-description">Break into groups of two or three and go out into the community to practice Prayer Walking. <br><br>Choosing a location can be as simple as walking outside from where you are now, or you could plan to go to a specific destination. <br><br>Go as God leads, and plan on spending 60-90 minutes on this activity.</div>
				</div> <!-- row -->
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING FORWARD
					</div> <!-- step-title -->
					<div class="center">This sessions ends with a prayer walking activity, so here are guides for the next session before you head out!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">OBEY</div>
					<div class="activity-description">Spend time this week practicing Prayer Walking by going out alone or with a small group at least once.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">SHARE</div>
					<div class="activity-description">Spend time asking God who He might want you to share the Prayer Walking tool with before your group meets again. Share this person’s name with the group before you go.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">PRAY</div>
					<div class="activity-description">Before you go out on your Prayer Walking activity, be sure to pray with your group to end your time together. Thank God that He loves the lost, the last and the least - including us! Ask Him to prepare your heart and the heart of those you'll meet during your walk to be open to His work.</div>
				</div> <!-- row -->
			</section>
		</div>

		<?php
	}

	/**
	 * Zume Session 6
	 * @return mixed
	 */
	public function zume_session_6_content ($group_id) {
        $session_number = 1;
        ?>

        <?php echo $this->jquery_steps($group_id, $session_number); ?>
		<div class="row "><div class="small-centered columns" style="text-align: center; color: #ccc;">Session 6</div></div>
        <div id="session<?php echo $session_number . "-" . $group_id; ?>">
			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING BACK
					</div> <!-- step-title -->
					<div class="center">Welcome back to Zúme Training!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>CHECK-IN</span></div>
					<div class="activity-description">At the end of our last session, everyone in your group was challenged in two ways: <br><br>
						<ol><li>You were asked to spend some time Prayer Walking</li><li>You were encouraged to share the Prayer Walking tool with someone else.</li></ol> Take a few moments to see how your group did this week.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRAY</span></div>
					<div class="activity-description">Pray and thank God for the results, ask Him to help us when we find it hard to obey, and invite His Holy Spirit to lead your time together.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>OVERVIEW</span></div>
					<div class="activity-description">In this session, we’ll learn how God uses faithful followers - even if they're brand new - much more than ones with years of knowledge and training who just won't obey. And we'll get a first look at a way to meet together that helps disciples multiply even faster.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WATCH AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">When we help multiply disciples, we need to make sure we're reproducing the right things. It's important what disciples know - but it's much more important what they DO with what they know.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/yk0i0eserm.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_yk0i0eserm" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description">Think about God's commands that you already know. How "faithful" are you in terms of obeying and sharing those things?</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<div class="row block">
					<div class="step-title">
						LISTEN AND READ ALONG (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">READ</div>
					<div class="activity-description">ZÚME TOOLKIT - 3/3 GROUPS FORMAT<br><br>Jesus said -- “Where two or three have gathered together in My name, I am there in their midst.”<br><br>Find the "3/3 Group Format" section in your Zúme Guidebook, and listen to the audio below.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/xnhyl1o17z.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_xnhyl1o17z" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description">
						<ol><li>Did you notice any differences between a 3/3 Group and a Bible Study or Small Group you've been a part of (or have heard about) in the past? If so, how would those differences impact the group? </li>
							<li>Could a 3/3 Group be considered a Simple Church? Why or why not?</li>
						</ol>
					</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						MODEL 3/3 GROUP
					</div> <!-- step-title -->
				</div> <!-- row -->

				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">A 3/3 Group is a way for followers of Jesus to meet, pray, learn, grow, fellowship and practice obeying and sharing what they've learned. In this way a 3/3 Group is not just a small group but a Simple Church.<BR><BR> In the following video, you'll see a model 3/3 Group meet together and practice this format.<br><br>Find the "3/3 Groups Format" section in your Zúme Guidebook, and watch the video below.</div>
				</div> <!-- row -->

				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/s4shprhr4l.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_s4shprhr4l" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING FORWARD
					</div> <!-- step-title -->
					<div class="center">Congratulations on finishing Session 6! Below are next steps to take in preparation for the next session. </div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">OBEY</div>
					<div class="activity-description">Spend time this week practicing Faithfulness by obeying and sharing at least one of God's commands that you already know.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">SHARE</div>
					<div class="activity-description">Think about what you have heard and learned about faithfulness in this session, and ask God who He wants you to share it with. Share this person’s name with the group before you go.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">PRAY</div>
					<div class="activity-description">Thank God for His Faithfulness - for fulfilling every promise He's ever made. Ask Him to help you and your group become even more Faithful to Him.</div>
				</div> <!-- row -->
			</section>
		</div>

		<?php
	}

	/**
	 * Zume Session 7
	 * @return mixed
	 */
	public function zume_session_7_content ($group_id) {
        $session_number = 1;
        ?>

        <?php echo $this->jquery_steps($group_id, $session_number); ?>
		<div class="row "><div class="small-centered columns" style="text-align: center; color: #ccc;">Session 7</div></div>
        <div id="session<?php echo $session_number . "-" . $group_id; ?>">
			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING BACK
					</div> <!-- step-title -->
					<div class="center">Welcome back to Zúme Training!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>CHECK-IN</span></div>
					<div class="activity-description">At the end of our last session, everyone in your group was challenged in two ways: <br><br><ol><li>You were asked to practice Faithfulness by obeying and sharing one of God's commands.</li><li>You were encouraged to share the importance of Faithfulness with someone else.</li></ol> Take a few moments to see how your group did this week.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRAY</span></div>
					<div class="activity-description">Pray and thank God for the group's commitment to faithfully following Jesus and invite God's Holy Spirit to lead your time together.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>OVERVIEW</span></div>
					<div class="activity-description">In this session, we’ll learn a Training Cycle that helps disciples go from one to many and turns a mission into a movement. We'll also practice the 3/3 Groups Format and learn how the way you meet can impact the way you multiply.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WATCH AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">Have you ever learned how to ride a bicycle? Have you ever helped someone else learn? If so, chances are you already know the Training Cycle.<br><br>Find the "Training Cycle" section in your Zúme Guidebook. When you're ready, watch this video, and then discuss the questions below.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/ziw8qxj7zj.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_ziw8qxj7zj" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description"><ol><li>Have you ever been a part of a Training Cycle?</li><li> Who did you train? Or who trained you? </li><li>Could the same person be at different parts of the Training Cycle while learning different skills? </li><li>What would it look like to train someone like that?</li></ol></div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						PRACTICE AND DISCUSS (90min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRACTICE</span></div>
					<div class="activity-description">Have your entire group spend the next 90 minutes practicing the 3/3 Groups Format using the pattern on pages 19-20 in your Zúme Guidebook.<br><br>
						<ul><li>LOOK BACK - Use last week's Session Challenges to practice "Faithfulness" in the Look Back section.</li>
							<li>LOOK UP - Use Mark 5:1-20 as your group's reading passage and answer questions 1-4 during the Look Up section.</li>
							<li>LOOK FORWARD - Use questions 5, 6, and 7 in the Look Forward section to develop how you will Obey, Train and Share.</li></ul><br>
						REMEMBER - Each section should take about 1/3 (or 30 minutes) of your practice time.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>DISCUSS</span></div>
					<div class="activity-description"><ol><li>What did you like best about the 3/3 Group? Why?</li><li> What was the most challenging? Why?</li></ol></div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING FORWARD
					</div> <!-- step-title -->
					<div class="center">Congratulations on finishing Session 7! Below are next steps to take in preparation for the next session. </div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">OBEY</div>
					<div class="activity-description">Spend time this week obeying, training, and sharing based on the commitments you've made during your 3/3 Group practice.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">SHARE</div>
					<div class="activity-description">Pray and ask God who He wants you to share the 3/3 Group format with before your group meets again. Share this person’s name with the group before you go.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">PRAY</div>
					<div class="activity-description">Thank God that He loves us enough to invite us into His most important work - growing His family!</div>
				</div> <!-- row -->
			</section>
		</div>

		<?php
	}

	/**
	 * Zume Session 8
	 * @return mixed
	 */
	public function zume_session_8_content ($group_id) {
        $session_number = 1;
        ?>

        <?php echo $this->jquery_steps($group_id, $session_number); ?>

		<div class="row "><div class="small-centered columns" style="text-align: center; color: #ccc;">Session 8</div></div>
        <div id="session<?php echo $session_number . "-" . $group_id; ?>">
			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING BACK
					</div> <!-- step-title -->
					<div class="center">Welcome back to Zúme Training!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>CHECK-IN</span></div>
					<div class="activity-description">Before we get started, let's take some time to check-in.<br><br>At the end of our last session, everyone in your group was challenged in two ways: <br><br>
						<ol><li>You were asked to practice obeying, training, and sharing based on your commitments during 3/3 Group practice.</li><li>You were encouraged to share the 3/3 Group format with someone else.</li></ol> Take a few moments to see how your group did this week.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRAY</span></div>
					<div class="activity-description">Pray and thank God for giving your group the energy, the focus and the faithfulness to come so far in this training. Ask God to have His Holy Spirit remind everyone in the group that we can do nothing without Him!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>OVERVIEW</span></div>
					<div class="activity-description">In this session, we’ll learn how Leadership Cells prepare followers in a short time to become leaders for a lifetime. We'll learn how serving others is Jesus' strategy for leadership. And we'll spend time practicing as a 3/3 Group, again.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WATCH AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">Jesus said - “Whoever wishes to become great among you shall be your servant.”
						<br><br>
						Jesus radically reversed our understanding of leadership by teaching us that if we feel called to lead, then we are being called to serve. A Leadership Cell is a way someone who feels called to lead can develop their leadership by practicing serving.
						<br><br>
						Find the "Leadership Cells" section in your Zúme Guidebook. When you're ready, watch and discuss this video.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/lnr64mh2bg.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_lnr64mh2bg" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description"><ol><li>Is there a group of followers of Jesus you know that are already meeting or would be willing to meet and form a Leadership Cell to learn Zúme Training?</li><li> What would it take to bring them together?</li></ol></div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						PRACTICE (90min)
					</div> <!-- step-title -->
					<div class="center">Practice a 3/3 group session.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRACTICE</span></div>
					<div class="activity-description">Have your entire group spend the next 90 minutes practicing the 3/3 Groups Format using the pattern on pages 19-20 in your Zúme Guidebook.<br><br>
						<ul><li>LOOK BACK - Use last session’s Obey, Train, and Share challenges to check-in with each other.</li>
							<li>LOOK UP - Use Acts 2:42-47 as your group’s reading passage and answer questions 1- 4.</li>
							<li>LOOK FORWARD - Use questions 5, 6, and 7 to develop how you will Obey, Train and Share.</li></ul><br>
						REMEMBER - Each section should take about 1/3 (or 30 minutes) of your practice time.</div>
				</div> <!-- row -->

			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING FORWARD
					</div> <!-- step-title -->
					<div class="center">Congratulations! You've completed Session 8. Below are next steps to take in preparation for the next session. </div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">OBEY</div>
					<div class="activity-description">Spend time again this week obeying, sharing, and training based on the commitments you've made during this session's 3/3 Group practice.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">SHARE</div>
					<div class="activity-description">Pray and ask God who He wants you to share the Leadership Cell tool with before your group meets again. Share this person’s name with the group before you go.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">PRAY</div>
					<div class="activity-description">Thank God for sending Jesus to show us that real leaders are real servants. Thank Jesus for showing us the greatest service possible is giving up our own lives for others.</div>
				</div> <!-- row -->
			</section>
		</div>

		<?php
	}

	/**
	 * Zume Session 9
	 * @return mixed
	 */
	public function zume_session_9_content ($group_id) {
        $session_number = 1;
        ?>

        <?php echo $this->jquery_steps($group_id, $session_number); ?>

		<div class="row "><div class="small-centered columns" style="text-align: center; color: #ccc;">Session 9</div></div>
        <div id="session<?php echo $session_number . "-" . $group_id; ?>">
			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING BACK
					</div> <!-- step-title -->
					<div class="center">Welcome back to Zúme Training!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>CHECK-IN</span></div>
					<div class="activity-description">Before we get started, let's take some time to check-in.<br><br>At the end of our last session, everyone in your group was challenged in two ways: <br><br>
						<ol><li>You were asked to practice Obeying, Training, and Sharing based on your commitments during last session's 3/3 Group practice.</li><li>You were encouraged to share the Leadership Cells tool with someone else.</li></ol> Take a few moments to see how your group did this week.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRAY</span></div>
					<div class="activity-description">Pray and thank God that His ways are not our ways and His thoughts are not our thoughts. Ask Him to give each member of your group the mind of Christ - always focused on His Father's work. Ask the Holy Spirit to lead your time together and make it the best session yet.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>OVERVIEW</span></div>
					<div class="activity-description">In this session, we’ll learn how linear patterns hold back kingdom growth and how Non-Sequential thinking helps you multiply disciples. We'll discover how much time matters in disciple-making and how to accelerate our Pace. We’ll learn how followers of Jesus can be a Part of Two Churches to help turn faithful, spiritual families into a growing city-wide body of believers. Finally, we'll learn how a simple 3-Month Plan can focus our efforts and multiply our effectiveness in growing God's family exponentially.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WATCH AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">When people think about disciples multiplying, they often think of it as a step-by-step process. The problem with that is - that's not how it works best!</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/1rydt7j3ds.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_1rydt7j3ds" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description"><ol><li>What is the most exciting idea you heard in this video? Why?</li><li> What is the most challenging idea? Why?</li></ol></div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WATCH AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">Multiplying matters and multiplying quickly matters even more. Pace matters because where we all spend our eternity - an existence that outlasts time - is determined in the very short time we call “life."</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/42tm77n9aq.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_42tm77n9aq" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description"><ol><li>Why is pace important?</li><li> What do you need to change in your thinking, your actions, or your attitude to be better aligned with God's priority for pace? </li><li>What is one thing you can do starting this week that will make a difference?</li></ol></div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WATCH AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">Jesus taught us that we are to stay close - to live as a small, spiritual family, to love and give our lives to one another, to celebrate and suffer -- together. However, Jesus also taught us to leave our homes and loved ones behind and be willing to go anywhere - and everywhere - to share and start new spiritual families.<br><br>So how can we do both?<br><br>When you're ready, watch the video below and discuss the question that follows.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/nna7r761vo.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_nna7r761vo" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description">What are some advantages of maintaining a consistent spiritual family that gives birth to new ones that grow and multiply instead of continually growing a family and splitting it in order to grow?</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						PROJECT (30min)
					</div> <!-- step-title -->
					<div class="center">Create a 3-month plan.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>OVERVIEW</span></div>
					<div class="activity-description">In His Bible, God says, "I know the plans I have for you, plans to prosper you and not to harm you, plans to give you hope and a future."<br><br>
						God makes plans, and He expects us to make plans, too. He teaches us through His Word and His work to look ahead, see a better tomorrow, make a plan for how to get there, and then prepare the resources we'll need on the way.<br><br>
						A 3-Month Plan is a tool you can use to help focus your attention and efforts and keep them aligned with God's priorities for making disciples who multiply.<br><br>
						Spend the next 30 minutes praying over, reading through, and then completing the commitments listed in the 3-Month Plan section in your Zúme Guidebook.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRAY</span></div>
					<div class="activity-description">Ask God what He specifically wants you to do with the basic disciple-making tools and techniques you have learned over these last 9 sessions. Remember His words about Faithfulness.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>LISTEN</span></div>
					<div class="activity-description">Take at least 10 minutes to be as quiet as possible and listen intently to what God has to say and what He chooses to reveal. Make an effort to hear His voice. </div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>COMPLETE</span></div>
					<div class="activity-description">Use the rest of your time to complete the 3-Month Plan worksheet. You do not have to commit to every item, and there is room for other items not already on the list. Do your best to align your commitments to what you have heard God reveal to you about His will.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						ACTIVITY (30min)
					</div> <!-- step-title -->
					<div class="center">Share your plan and plan as a team.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">SHARE</div>
					<div class="activity-description">IN GROUPS OF TWO OR THREE (15 minutes)<br><br>Take turns sharing your 3-Month Plans with each other. Take time to ask questions about things you might not understand about plans and how the others will meet their commitments. Ask them to do the same for you and your plan.<br><br>Find a training partner(s) that is willing to check in with you to report on progress and challenges and ask questions after 1, 2, 3, 4, 6, 8 and 12 weeks. Commit to doing the same for them.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description">IN YOUR FULL TRAINING GROUP (15 minutes)<br><br>Discuss and Develop a group plan for starting at least two (2) new 3/3 Groups or Zúme Training Groups in your area. Remember, your goal is start Simple Churches that multiply. 3/3 Groups and Zúme Training Groups are two ways to do that.<br><br>Discuss and Decide whether these new Groups will be connected to an existing local church or network or whether you’ll start a new network out of your Zúme Training Group.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">CONNECT</div>
					<div class="activity-description">CONNECT WITH YOUR COACH<br><br>Make sure all group members know how to contact the Zúme Coach that’s been assigned to your group in case anyone has questions or needs more training. Remember to share your 3-Month Plan with your Coach, so they understand your goals.<br><br>Discuss any other locations where members of your group could help launch new 3/3 Groups or Zúme Training Groups.<br><br>Be sure to pray as a group and ask God for His favor to bring about all the good work possible out of these plans and commitments.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING FORWARD
					</div> <!-- step-title -->
					<div class="center">Congratulations! You've completed Session 9. </div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">You may not know it, but you now have more practical training on starting simple churches and making disciples who multiply than many pastors and missionaries around the world!<br><br> Watch the following video and celebrate all that you've learned!</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/h3znainxm9.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_h3znainxm9" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">OBEY</div>
					<div class="activity-description">Set aside time on your calendar each week to continue work on your 3-Month Plan, and plan check-ins with your training partner at the end of week 1, 2, 3, 4, 6, 8, and 12. Each time you're together, ask about their results and share yours, making sure you're both working through your plans.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">SHARE</div>
					<div class="activity-description">Pray and ask God if He wants any (or all!) in your group to start a Zúme Training Group.<br><br>
						Be sure to pray with your group before you end your time together. Thank God that He has created and gifted you with exactly the right talents to make a difference in His kingdom. Ask Him for wisdom to use the strengths He has given you and to find other followers who help cover your weaknesses. Pray that He would make you fruitful and multiply - this was His plan from the very beginning!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">PRAY</div>
					<div class="activity-description">Pray that God help you be obedient to Him and invite Him to work in you and those around you!</div>
				</div> <!-- row -->
			</section>
		</div>

		<?php
	}

	/**
	 * Zume Session 10
	 * @return mixed
	 */
	public function zume_session_10_content ($group_id) {
        $session_number = 10;
        ?>

        <?php echo $this->jquery_steps($group_id, $session_number); ?>

		<div class="row "><div class="small-centered columns" style="text-align: center; color: #ccc;">Session 10</div></div>
        <div id="session<?php echo $session_number . "-" . $group_id; ?>">
			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						LOOKING BACK
					</div> <!-- step-title -->
					<div class="center">Welcome back to Zúme Training!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>CHECK-IN</span></div>
					<div class="activity-description">Before we get started, let's take some time to check-in.<br><br>At the end of our last session, everyone in your group was challenged in two ways: <br><br>
						<ol><li>You were asked to prayerfully consider continuing as an ongoing spiritual family committed to multiplying disciples. </li><li>You were encouraged to share Zúme Training by launching a Leadership Cell of future Zúme Training leaders.</li></ol>Take a few moments to see how your group has been doing with these items and their 3-Month Plans since you've last met.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRAY</span></div>
					<div class="activity-description">Pray and thank God that He is faithful to complete His good work in us. Ask Him to give your group clear heads and open hearts to the great things He wants to do in and through you. Ask the Holy Spirit to lead your time together and thank Him for His faithfulness, too. He got you through!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>OVERVIEW</span></div>
					<div class="activity-description">In this advanced training session, we’ll take a look at how we can level-up our Coaching Strengths with a quick checklist assessment. We’ll learn how Leadership in Networks allows a growing group of small churches to work together to accomplish even more. And we’ll learn how to develop Peer Mentoring Groups that take leaders to a whole new level of growth.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						ACTIVITY (10min)
					</div> <!-- step-title -->
					<div class="center">Assess yourself using the coaching checklist.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">ASSESS</div>
					<div class="activity-description">The Coaching Checklist is a powerful tool you can use to quickly assess your own strengths and vulnerabilities when it comes to making disciples who multiply. It's also a powerful tool you can use to help others - and others can use to help you.<br><br>
						Find the Coaching Checklist section in your Zúme Guidebook, and take this quick (5-minutes or less) self-assessment:<br><br>
						<ol><li>Read through the Disciple Training Tools in the far left column of the Checklist.</li>
							<li>Mark each one of the Training Tools, using the following method:
								<ul><li> If you're unfamiliar or don't understand the Tool - check the BLACK column</li>
									<li>If you're somewhat familiar but still not sure about the Tool - check the RED column</li>
									<li>If you understand and can train the basics on the Tool - check the YELLOW column</li>
									<li>If you feel confident and can effectively train the Tool - check the GREEN column</li></ul></li></ol>
					</div>
				</div> <!-- row -->

				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description"><ol><li>Which Training Tools did you feel you would be able to train well?</li><li>Which ones made you feel vulnerable as a trainer?</li><li> Are there any Training Tools that you would add or subtract from the Checklist? Why?</li></ol></div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block single">
					<div class="activity-description well">REMEMBER - Be sure to share your Coaching Checklist results with your Zúme Coach and/or your training partner or other mentor. If you're helping coach or mentor someone, share this tool to help assess areas which areas need your attention and training.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						WATCH AND DISCUSS (15min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">WATCH</div>
					<div class="activity-description">What happens to churches as they grow and start new churches that start new churches? How do they stay connected and live life together as an extended, spiritual family? They become a network!</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/h9bg4ij6hs.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_h9bg4ij6hs" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">DISCUSS</div>
					<div class="activity-description">Are there advantages when networks of simple churches are connected by deep, personal relationships? What are some examples that come to mind?</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<div class="row block">
					<div class="step-title">
						LISTEN AND READ ALONG (3min)
					</div> <!-- step-title -->
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title">READ</div>
					<div class="activity-description">ZÚME TOOLKIT - PEER MENTORING GROUPS<br><br>Making disciples who make disciples means making leaders who make leaders. How do you develop stronger leaders? By teaching them how to love one another better. Peer Mentoring Groups help leaders love deeper.<br><br>Find the Peer Mentoring Groups section in your Zúme Guidebook, and listen to the audio below.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/82s2l4gpq8.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_82s2l4gpq8" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						PRACTICE (60min)
					</div> <!-- step-title -->
					<div class="center">Practice peer mentoring groups. Spend the next 60 minutes practicing the Peer Mentoring Groups format. Find the Peer Mentoring Groups section in your Zúme Training Guide, and follow these steps.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>GROUPS</span></div>
					<div class="activity-description">Break into groups of two or three and work through the 3/3 sections of the Peer Mentoring Group format. Peer Mentoring is something that happens once a month or once a quarter and takes some time for the whole group to participate, so you will not have time for everyone to experience the full mentoring process in this session. </div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>PRACTICE</span></div>
					<div class="activity-description">To practice, choose one person in your group to be the "mentee" for this session and have the other members spend time acting as Peer Mentors by working through the suggested questions list and providing guidance and encouragement for the Mentee's work.<br><br>

						By the time you're finished, everyone should have a basic understanding of asking and answering.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block single">
					<div class="activity-description well">REMEMBER - Spend time studying the Four Fields Diagnostic Diagram and Generational Map in the Peer Mentoring Groups section of your Zúme Training Guide. Make sure everyone in your group has a basic understanding of these tools before asking the suggested questions.</div>
				</div> <!-- row -->
			</section>

			<!-- Step -->
			<h3></h3>
			<section>
				<!-- Step Title -->
				<div class="row block">
					<div class="step-title">
						YOU HAVE COMPLETED ZÚME TRAINING!
					</div> <!-- step-title -->
					<div class="center">Congratulations! You've completed Session 10 - Advanced Training. It's time to celebrate... and get to work!</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>WATCH</span></div>
					<div class="activity-description">You and your group are now ready to take leadership to a new level! Here are a few more steps to help you KEEP growing!<br><br>Watch the following video and then read through the steps below.</div>
				</div> <!-- row -->
				<!-- Video block -->
				<div class="row block">
					<!--<script src="//fast.wistia.com/embed/medias/h3znainxm9.jsonp" async></script>
					<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
					<div class="wistia_embed wistia_async_h3znainxm9" >&nbsp;</div>-->
					<img src="<?php echo plugin_dir_url(__FILE__); ?>img/video-placeholder.png" />
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>LEARN</span></div>
					<div class="activity-description">Find additional information on some of the multiplication concepts at <a href="http://metacamp.org/multiplication-concepts/" target="_blank">http://metacamp.org/multiplication-concepts/</a> or ask questions about specific resources at <a href="mailto:info@zumeproject.com" target="_blank">info@zumeproject.com</a>.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>SHARE</span></div>
					<div class="activity-description">You can put what you know to work is by helping spread the word about Zúme Training and inviting others to go through training, too. We make it easy to invite friends through email, Facebook, Twitter, Snapchat and other social sites, but we can’t invite your friends for you. </div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>COACHES</span></div>
					<div class="activity-description">As part of Zúme Training, you have a live coach standing by to answer any questions you might have or to help you take simple steps as you get started. Be sure to connect with them. That’s what they’re there for.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>REGIONS</span></div>
					<div class="activity-description">One of the ways you can put what you know to work is by becoming a county coordinator, that is someone who can help connect groups as they get started in your area. If you’re the kind of person who likes to help people go and grow, this might be a way God can use your gifts to do even more. Let us know by sending an email to <a href="mailto:info@zumeproject.com" target="_blank">info@zumeproject.com</a>.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>LANGUAGE</span></div>
					<div class="activity-description">As Zúme Training grows, sessions will soon be available in 34 more languages. As we bring those trainings online, we’ll send you information on people in your neighborhood that speak those languages, so you can share something that’s built just for them. You can help fund the translation of the Zúme Training into additional languages by donating at <a href="https://big.life/donate/" target="_blank">https://big.life/donate</a> and designating the gift for the Zúme Project with a note about the language you would like to fund.</div>
				</div> <!-- row -->
				<!-- Activity Block  -->
				<div class="row block">
					<div class="activity-title"><span>NEIGHBORS</span></div>
					<div class="activity-description">We are working with <a href="http://www.mappingcenter.org/" target="_blank">http://www.mappingcenter.org</a> to try to provide you with free information on the residents within your census tract in order to help you more effectively reach it. "Stay tuned" for more information. If you do not have relationships within your census tract and are looking for ways to connect with your neighbors, you might consider the Mapping Your Neighborhood program for disaster preparedness.  You can find information and downloadable resources at <a href="http://mil.wa.gov/emergency-management-division/preparedness/map-your-neighborhood" target="_blank">http://mil.wa.gov/emergency-management-division/preparedness/map-your-neighborhood</a>.</div>
				</div> <!-- row --><br><br>
				<h1>We can't wait to hear what God does in and through your life!</h1>

				<h1>Thank you for being a part of Zúme Training!</h1>
			</section>
		</div>

		<?php
	}



}