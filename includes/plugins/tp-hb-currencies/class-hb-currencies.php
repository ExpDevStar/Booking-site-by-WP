<?php

require_once dirname( __FILE__ ) . '/class-hb-currencies-settings.php';
require_once dirname( __FILE__ ) . '/class-hb-currencies-storage.php';

/**
 * class switch currency.
 */
class HB_SW_Curreny
{

	/**
	 * allow multi - currency
	 * @var boolean
	 */
	public $_is_multi = false;

	/**
	 * __constructor
	 */
	public function __construct( )
	{
		add_action( 'admin_init', array( $this, 'init' ) );
		add_filter( 'hotel_booking_currency_aggregator', array( $this, 'aggregator' ) );

		$settings = HB_SW_Curreny_Setting::instance();
		if ( $settings->get( 'enable' ) ) {
			// include file
			$this->includes();
			add_action( 'widgets_init', array( $this, 'register_widgets' ) );

			/**
			 * if is multi currency is true
			 * do all action in frontend
			 */
			add_filter( 'hb_currency', array( $this, 'switch_currencies' ), 99 );
			add_filter( 'hotel_booking_price_switcher', array( $this, 'switch_price' ) );

			add_action( 'plugins_loaded', array( $this, 'set_currency' ) );
			add_action( 'qtranslate_init_language', array( $this, 'qtranslate' ) );
			// cookie check wpml;
			add_filter( 'icl_current_language', array( $this, 'wpml_switcher' ) );

			// transaction object
			add_filter( 'hotel_booking_checkout_booking_info', array( $this, 'generate_booking_info' ) );
			/**
			 * enqueue scripts
			 */
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
			// add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		}
	}

	/**
	 * enqueue script
	 * @return null
	 */
	public function enqueue()
	{
		wp_enqueue_script( 'tp-hb-currencies', TP_HB_CURRENCY_URI . '/assets/js/tp-hb-currencies.min.js', 'jquery', HB_VERSION, true );
	}

	public function admin_enqueue() {
		// wp_register_script( 'tp-admin-hotel-booking-tokenize-js', TP_HB_CURRENCY_URI . '/assets/js/jquery.tokenize.min.js' );
  //       wp_register_style( 'tp-admin-hotel-booking-tokenize-css', TP_HB_CURRENCY_URI . '/assets/css/jquery.tokenize.min.css' );
  //       wp_enqueue_script( 'tp-admin-hotel-booking-tokenize-js' );
  //   	wp_enqueue_style( 'tp-admin-hotel-booking-tokenize-css' );
	}

	/**
	 * default currency
	 */
	public function set_currency()
	{
		$storage = HB_SW_Curreny_Storage::instance();

		if( isset( $_GET['currency'] ) && $_GET['currency'] ) {
			$storage->set( 'currency', sanitize_text_field( $_GET['currency'] ) );
		}
	}

	/**
	 * [qtranslate switch language]
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function qtranslate( $params )
	{
		if( ! isset( $params['language'] ) ) return;

		$currency_countries = hb_sw_currency_countries();

		$lang = strtoupper( $params['language'] );
		if( array_key_exists( $lang, $currency_countries ) )
		{
			$currency = strtoupper( $currency_countries[ $lang ] );
			if( $currency )
			{
				$storage = HB_SW_Curreny_Storage::instance();
				$storage->set( 'currency', $currency );
			}
		}
	}

	public function wpml_switcher( $lag )
	{
		$storage = HB_SW_Curreny_Storage::instance();
		$currency_countries = hb_sw_currency_countries();
		$country = strtoupper( $lag );
		if( array_key_exists( $country, $currency_countries ) )
		{
			$currency = strtoupper( $currency_countries[ $country ] );
			if( $currency )
			{
				$storage = HB_SW_Curreny_Storage::instance();
				$storage->set( 'currency', $currency );
			}
		}
		return $lag;
	}

	/**
	 * add action, filter
	 * @return null
	 */
	public function init()
	{
		/**
		 * generate settings in admin panel
		 */
		if( is_admin() )
		{
			add_filter( 'hb_admin_settings_tabs', array( $this, 'setting_tab' ), 100 );
			add_action( 'hb_admin_settings_tab_currencies', array( $this, 'admin_settings' ) );

		}
	}

	public function includes()
	{
		require_once dirname( __FILE__ ) . '/functions.php' ;
		require_once dirname( __FILE__ ) . '/class-hb-abstract-shortcode.php' ;
		require_once dirname( __FILE__ ) . '/shortcodes/class-hb-shortcode-currency-switcher.php' ;
		require_once dirname( __FILE__ ) . '/widgets/class-hb-widget-currency-switch.php' ;
	}

	/**
	 * register widget
	 * @return null
	 */
	public function register_widgets()
	{
		register_widget( 'HB_Widget_Currency_Switch' );
	}

	/**
	 * switch currency
	 * @param  string key $currency
	 * @return string key
	 */
	public function switch_currencies( $currency )
	{
		$settings = HB_SW_Curreny_Setting::instance();
		$storage = HB_SW_Curreny_Storage::instance();
		if( $this->_is_multi = $settings->get('is_multi_currency', false) )
		{
			do_action( 'hb_before_currencies_switcher' );

			$currency = apply_filters( 'hb_currencies_switcher', $storage->get( 'currency' ) );

			do_action( 'hb_after_currencies_switcher' );
		}
		return $currency;
	}

	/**
	 * switch price
	 * @param  numberic $price
	 * @return numberic
	 */
	public function switch_price ( $price )
	{
		$settings = HB_SW_Curreny_Setting::instance();
		$storage = HB_SW_Curreny_Storage::instance();

		$default_currency = $settings->_detault_currency;

		$current_currency = $storage->get( 'currency' );

		$rate = $storage->get_rate( $default_currency, $current_currency );

		return (float)$price * $rate;
	}

	/**
	 * generate transaction payment object
	 * @param  object $transaction [description]
	 * @return object              [description]
	 */
	public function generate_booking_info( $booking_info )
	{
	    global $hb_settings;
	    $default_curreny = $hb_settings->get( 'currency', 'USD' );
	    $payment_currency = hb_get_currency();
		// booking meta data
        $booking_info['_hb_payment_currency'] 		= apply_filters( 'hotel_booking_payment_current_currency', $payment_currency );
        $booking_info['_hb_payment_currency_rate'] 	= (float)apply_filters( 'hotel_booking_payment_currency_rate', $default_curreny, $payment_currency );

        return $booking_info;
	}

	/**
	 * generate aggregator
	 * @param  array $aggregators
	 * @return array
	 */
	public function aggregator( $aggregators )
	{
		$aggregators[ 'yahoo' ] = 'http://finance.yahoo.com';
		$aggregators[ 'google' ] = 'http://google.com/finance';

		return $aggregators;
	}

	/**
	 * admin setting tab hook
	 * @param  array $tabs
	 * @return array
	 */
	function setting_tab( $tabs )
	{
		$tabs['currencies'] = __( 'Currency', 'tp-hotel-booking' );
		return $tabs;
	}

	/**
	 * admin setting
	 * @return null
	 */
	function admin_settings()
	{
		require_once dirname( __FILE__ ) . '/settings/settings.php' ;
	}

}
new HB_SW_Curreny();