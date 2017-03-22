<?php

/**
 * Zume_Helper
 *
 * @class Zume_Helper
 * @version	0.1
 * @since 0.1
 * @package	Disciple_Tools
 * @author Chasm.Solutions
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Zume_Helper {

    /**
     * The single instance of Zume_Helper.
     * @var 	object
     * @access  private
     * @since 	0.1
     */
    private static $_instance = null;

    /**
     * Main Zume_Helper Instance
     *
     * Ensures only one instance of Zume_Helper is loaded or can be loaded.
     *
     * @since 0.1
     * @static
     * @return Zume_Helper instance
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

    } // End __construct()



}