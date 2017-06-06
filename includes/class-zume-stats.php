<?php
/**
 * Zume_Stats
 *
 * @class Zume_Stats
 */

class Zume_Stats{
	/**
	 * Zume_Stats The single instance of Zume_Stats.
	 * @var 	object
	 * @access  private
	 * @since 	0.1
	 */
	private static $_instance = null;

	/**
	 * Main Zume_Stats Instance
	 *
	 * Ensures only one instance of Zume_Stats is loaded or can be loaded.
	 *
	 * @since 0.1
	 * @static
	 * @return Zume_Stats instance
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
//		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_buddypress_styles_to_zume' ) );
	} // End __construct()


	public function enqueue_buddypress_styles_to_zume () {

//		wp_register_style( 'zume_stats_style', ZUME_PLUGIN_URL . '/includes/css/zume-stats.css' );
//		wp_enqueue_style( 'zume_stats_stylesheet', ZUME_PLUGIN_URL . '/includes/css/zume-stats.css');
	}

	/**
	 * get the number of verified users
	 * @return mixed
	 */
	public function get_user_count(){
		$users = count_users();
		return $users["total_users"];
	}

	public function get_groups(){

	}
}