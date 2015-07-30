<?php
/*
Plugin Name: TP Hotel Booking
Plugin URI: http://thimpress.com/learnpress
Description: [Description here]
Author: ThimPress
Version: 0.9
Author URI: http://thimpress.com
*/

define( 'HB_FILE', __FILE__ );
define( 'HB_PLUGIN_PATH', dirname( __FILE__ ) );
define( 'HB_VERSION', '0.9' );
/**
 * Class TP_Hotel_Booking
 */
class TP_Hotel_Booking{
    /**
     * Hold the instance of main class
     *
     * @var object
     */
    protected static $_instance = null;

    /**
     * Plugin path
     *
     * @var string
     */
    protected $_plugin_path = null;

    /**
     * Plugin URL
     *
     * @var string
     */
    protected $_plugin_url = null;

    /**
     * Construction
     */
    function __construct(){
        if( self::$_instance ) return;
        $this->includes();
        $this->load_text_domain();

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Include a file
     *
     * @param string
     * @param bool
     */
    function _include( $file, $root = true ){
        if( $root ){
            $file = $this->plugin_path( $file );
        }
        require_once $file;
    }

    /**
     * Get the full path of a file
     *
     * @param string
     * @return string
     */
    function locate( $file ){
        return $this->_plugin_path . '/' . $file;
    }

    /**
     * Includes common files and libraries
     */
    function includes(){
        if( is_admin() ) {
            $this->_include( 'includes/admin/class-hb-admin-menu.php' );
            $this->_include( 'includes/class-hb-meta-box.php' );
            $this->_include( 'includes/admin/hb-admin-functions.php' );
        }else{
            $this->_include( 'includes/hb-template-functions.php' );
            $this->_include( 'includes/class-hb-shortcodes.php' );
        }
        $this->_include( 'includes/class-hb-post-types.php' );
        $this->_include( 'includes/hb-functions.php' );
        $this->_include( 'includes/class-hb-settings.php' );
    }

    /**
     * Get the path of the plugin with sub path
     *
     * @param string $sub
     * @return string
     */
    function plugin_path( $sub = '' ){
        if( ! $this->_plugin_path ) {
            $this->_plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
        }
        return $this->_plugin_path . '/' . $sub;
    }

    /**
     * Get the url of the plugin with sub path
     *
     * @param string $sub
     * @return string
     */
    function plugin_url( $sub = '' ){
        if( ! $this->_plugin_url ) {
            $this->_plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
        }
        return $this->_plugin_url . '/' . $sub;
    }

    /**
     * Load language for the plugin
     */
    function load_text_domain(){
        $locale = get_locale();
        $dir    = $this->plugin_path( 'languages' );
        $mofile = "{$dir}{$locale}.mo";

        // In themes/plugins/mu-plugins directory
        load_textdomain( 'tp-hotel-booking', $mofile );
    }

    /**
     * Enqueue assets for the plugin
     */
    function enqueue_assets(){
        wp_register_script( 'jquery-ui-datepicker', $this->plugin_url( 'includes/assets/js/jquery.ui.datepicker.min.js' ), array( 'jquery' ) );
        if( is_admin() ){
            wp_register_style( 'tp-admin-hotel-booking', $this->plugin_url( 'includes/assets/css/admin.tp-hotel-booking.css' ) );
        }else{
            wp_register_style( 'tp-hotel-booking', $this->plugin_url( 'includes/assets/css/hotel-booking.css' ) );
            wp_register_script( 'tp-hotel-booking', $this->plugin_url( 'includes/assets/js/hotel-booking.js' ), array( 'jquery', 'jquery-ui-datepicker' ) );
        }

        if( is_admin() ) {
            wp_enqueue_style( 'tp-admin-hotel-booking' );
        }else{
            wp_enqueue_style( 'tp-hotel-booking' );
            wp_enqueue_script( 'tp-hotel-booking' );
        }
    }

    /**
     * Create an instance of the plugin if it is not created
     *
     * @static
     * @return object|TP_Hotel_Booking
     */
    static function instance(){
        if( ! self::$_instance ){
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}

$GLOBALS['tp_hotel_booking'] = TP_Hotel_Booking::instance();