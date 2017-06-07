<?php

/**
 * Disciple Tools Admin Menus
 *
 * @class Disciple_Tools_Admin_Menus
 * @version	0.1
 * @since 0.1
 * @package	Disciple_Tools
 * @author Chasm.Solutions & Kingdom.Training
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Zume_Overview {

    /**
     * Zume_Overview The single instance of Zume_Overview.
     * @var 	object
     * @access  private
     * @since 	0.1
     */
    private static $_instance = null;

    /**
     * Main Zume_Overview Instance
     *
     * Ensures only one instance of Zume_Overview is loaded or can be loaded.
     *
     * @since 0.1
     * @static
     * @return Zume_Overview instance
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
     * Enqueue scripts and styles
     * @return mixed
     */
    public function zume_scripts_enqueue () {
        wp_register_script( 'jquery-steps', plugin_dir_url(__FILE__) . 'js/jquery.steps.js', array('jquery'), NULL, true );
        wp_register_style( 'zume-course', plugin_dir_url(__FILE__) . 'css/zume-course.css', false, NULL, 'all' );

        wp_enqueue_script( 'jquery-steps' );
        wp_enqueue_style( 'zume-course' );
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
     * Zume Overview: Primary content section
     * @return mixed
     */
    public function zume_sessions_overview ($session = 1) {
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
                <?php print $this->zume_session_overview_content (6) ?>
            </section>

            <h3></h3>
            <section>
                <h3>Session 7 Overview</h3>
                <?php print $this->zume_session_overview_content (7) ?>
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

        <?php if(is_user_logged_in()) {$this->next_session_block(); } ?>


        <?php

    }

    public function next_session_block () {
        $next_session = '';
        $group_id = '';
        $group_name = Zume_Course::instance()->zume_get_group_name($group_id);
        ?>
        <div class="callout">
            <p class="center padding-bottom"><strong>Session <?php echo $next_session; ?></strong> is the next session for <strong><?php echo $group_name;  ?></strong> </p>
            <p class="center"><a href="<?php echo home_url("/zume-training/") . "?id=" . $next_session ?>" class="button large">Start Your Next Session</a></p>
        </div>
        <?php
    }

}