<?php

/**
 * Report Class
 */
class HB_Report_Price extends HB_Report
{
	public $_title;

	public $_chart_type = 'price';

	public $_start_in;

	public $_end_in;

	public $chart_groupby;

	public $_range_start;
	public $_range_end;

	public $_range;

	public $_query_results = null;

	static $_instance = array();

	public function __construct( $range = null )
	{
		if( ! $range ) return;

		$this->_range = $range;

		if( isset( $_GET['tab'] ) && $_GET['tab'] )
			$this->_chart_type = sanitize_text_field( $_GET['tab'] );

		$this->calculate_current_range( $this->_range );

		$this->_title = sprintf( __( 'Chart in %s to %s', 'tp-hotel-booking' ), $this->_start_in, $this->_end_in );

		$this->_query_results = $this->getOrdersItems();
		add_action( 'admin_init', array( $this, 'export_csv' ) );
	}

	/**
	 * get all post have post_type = hb_booking
	 * completed > start and < end
	 * @return object
	 */
	public function getOrdersItems()
	{

		$transient_name = 'tp_hotel_booking_charts_query' . $this->_chart_type. '_' . $this->chart_groupby . '_' . $this->_range . '_' . $this->_start_in . '_' . $this->_end_in;

		if ( false === ( $results = get_transient( $transient_name ) ) ) {

			global $wpdb;

			/**
			 * pll is completed date
			 * ptt is total of booking quantity
			 */
			if( $this->chart_groupby === 'day' )
			{
				$total = $wpdb->prepare("
						(
							SELECT SUM(ptt.meta_value) AS total FROM `$wpdb->posts` pb
							INNER JOIN `$wpdb->postmeta` AS pbl ON pb.ID = pbl.post_id AND pbl.meta_key = %s
							INNER JOIN `$wpdb->postmeta` AS ptt ON pb.ID = ptt.post_id AND ptt.meta_key = %s
							WHERE pb.post_type = %s
							AND DATE(pbl.meta_value) >= %s AND DATE(pbl.meta_value) <= %s
							AND DATE(pbl.meta_value) = completed_date
						)
						", '_hb_booking_payment_completed', '_hb_total', 'hb_booking', $this->_start_in, $this->_end_in
					);

				$query = $wpdb->prepare("
						(
							SELECT DATE(pm.meta_value) AS completed_date,
							{$total} AS total
							FROM `$wpdb->posts` AS p
							INNER JOIN `$wpdb->postmeta` AS pm ON p.ID = pm.post_id AND pm.meta_key = %s
							WHERE p.post_type = %s
							AND p.post_status = %s
							AND DATE(pm.meta_value) >= %s AND DATE(pm.meta_value) <= %s
							GROUP BY completed_date
						)
						", '_hb_booking_payment_completed', 'hb_booking', 'hb-completed', $this->_start_in, $this->_end_in
					);
			}
			else
			{
				$total = $wpdb->prepare("
						(
							SELECT SUM(ptt.meta_value) AS total FROM `$wpdb->posts` pb
							INNER JOIN `$wpdb->postmeta` AS pbl ON pb.ID = pbl.post_id AND pbl.meta_key = %s
							INNER JOIN `$wpdb->postmeta` AS ptt ON pb.ID = ptt.post_id AND ptt.meta_key = %s
							WHERE pb.post_type = %s
							AND MONTH(pbl.meta_value) >= MONTH(%s) AND MONTH(pbl.meta_value) <= MONTH(%s)
							AND MONTH(pbl.meta_value) = completed_date
						)
						", '_hb_booking_payment_completed', '_hb_total', 'hb_booking', $this->_start_in, $this->_end_in
					);

				$query = $wpdb->prepare("
						(
							SELECT MONTH(pm.meta_value) AS completed_date, DATE(pm.meta_value) AS completed_time,
							{$total} AS total
							FROM `$wpdb->posts` AS p
							INNER JOIN `$wpdb->postmeta` AS pm ON p.ID = pm.post_id AND pm.meta_key = %s
							WHERE p.post_type = %s
							AND p.post_status = %s
							AND MONTH(pm.meta_value) >= MONTH(%s) AND MONTH(pm.meta_value) <= MONTH(%s)
							GROUP BY completed_date
						)
						", '_hb_booking_payment_completed', 'hb_booking', 'hb-completed', $this->_start_in, $this->_end_in
					);
			}

			$results = $wpdb->get_results( $query );
			set_transient( $transient_name, $results, 12 * HOUR_IN_SECONDS );
		}

		return $results;
	}

	public function series()
	{
		$transient_name = 'tp_hotel_booking_charts_' . $this->_chart_type . '_' . $this->chart_groupby . '_' . $this->_range . '_' . $this->_start_in . '_' . $this->_end_in;

		if ( false === ( $chart_results = get_transient( $transient_name ) ) ) {
			$default = new stdClass;
			$default->name = '';
			$default->type = 'area';

			$default->data = $this->parseData( $this->_query_results );

			$chart_results = array( $default );

			set_transient( $transient_name, $chart_results, 12 * HOUR_IN_SECONDS );
		}

		return apply_filters( 'tp_hotel_booking_charts', $chart_results );
	}

	public function parseData( $results )
	{
		$data = array();
		$excerpts = array();

		foreach ( $results as $key => $item ) {

			if( $this->chart_groupby === 'day' )
			{
				$excerpts[ (int)date("z", strtotime($item->completed_date)) ] = $item->completed_date;
				$keyr = strtotime($item->completed_date); // timestamp
				/**
				 * compare 2015-10-30 19:50:50 => 2015-10-30. not use time
				 */
				$data[ $keyr ] = array(
						strtotime( date('Y-m-d', $keyr ) ) * 1000,
						(float)$item->total
					);
			}
			else
			{
				$keyr = strtotime( date( 'Y-m-1', strtotime($item->completed_time) ) ); // timestamp of first day month in the loop
				$excerpts[ (int)date("m", strtotime($item->completed_time)) ] = date( 'Y-m-d', $keyr );
				$data[ $keyr ] = array(
						strtotime( date('Y-m-1', $keyr ) ) * 1000,
						(float)$item->total
					);
			}
		}

		$range = $this->_range_end - $this->_range_start;

		$cache = $this->_start_in;
		for( $i = 0; $i <= $range; $i++ )
		{
			$reg = $this->_range_start + $i;

			if( ! array_key_exists( $reg, $excerpts) )
			{
				if( $this->chart_groupby === 'day' )
				{
					$key = strtotime( $this->_start_in ) + 24 * 60 * 60 * $i;
					$data[ $key ] = array(
						(float)strtotime( date('Y-m-d', $key ) ) * 1000,
						0
					);
				}
				else
				{

					$cache = date( "Y-$reg-01", strtotime( $cache ) ); // cache current month in the loop

					$data[ strtotime($cache) ] = array(
						(float)strtotime( date('Y-m-1', strtotime($cache) ) ) * 1000,
						0
					);
				}
			}
		}

		sort($data);

		$results = array();

		foreach ($data as $key => $da) {
			$results[] = $da;
		}
		return $results;
	}

	public function export_csv()
	{
		if( ! isset( $_POST ) )
			return;

		if( ! isset( $_POST['tp-hotel-booking-report-export'] ) ||
			! wp_verify_nonce( $_POST['tp-hotel-booking-report-export'], 'tp-hotel-booking-report-export' ) )
			return;

		if( ! isset( $_POST['tab'] ) || sanitize_file_name( $_POST['tab'] ) !== $this->_chart_type )
			return;

		$inputs = $this->parseData( $this->_query_results );
		$column = array(
				__( 'Date/Time', 'tp-hotel-booking' )
			);
		$data = array(
				__( 'Earnings', 'tp-hotel-booking' )
			);
		foreach ($inputs as $key => $input) {
			if( $this->chart_groupby === 'day' )
			{
				if( isset( $input[0], $input[1] ) )
					$time = $input[0] / 1000;

				$column[] = date( 'Y-m-d', $time );
				$data[] = number_format($input[1], 2, '.', ',') .' '. hb_get_currency();
			}
			else
			{
				if( isset( $input[0], $input[1] ) )
					$time = $input[0] / 1000;

				$column[] = date( 'F. Y', $time );
				$data[] = number_format($input[1], 2, '.', ',') .' '. hb_get_currency();
			}
		}

		$column = apply_filters( 'tp_hotel_booking_export_report_price_column', $column );
		$data = apply_filters( 'tp_hotel_booking_export_report_price_data', $data );

		$filename = 'tp_hotel_export_'.$this->_chart_type.'_'.$this->_start_in.'_to_'. $this->_end_in . '.csv';
		header('Content-Type: application/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$filename);
		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'w');

		// output the column headings
		fputcsv($output, $column);

		fputcsv( $output, $data );

		fpassthru($output);
		die();
	}

	public function date_format( $date = '' )
	{
		if( $this->chart_groupby === 'day' )
		{
			if( $date != (int)$date || is_string($date) )
				$date = strtotime($date);
			return date( 'F j, Y', $date );
		}
		else
		{
			return date( 'F. Y', strtotime( date( 'Y-'.$date.'-1', time() ) ) );
		}
	}

	static function instance( $range = null )
	{
		if( ! $range && ! isset( $_GET['range'] ) )
			$range = '7day';

		if( ! $range && isset( $_GET['range'] ) )
			$range = $_GET['range'];

		if( ! empty( self::$_instance[ $range ] ) )
			return self::$_instance[ $range ];

		return new self( $range );
	}

}

if( !isset($_REQUEST['tab']) || $_REQUEST['tab'] === 'price' )
{
	$GLOBALS['hb_report'] = HB_Report_Price::instance();
}