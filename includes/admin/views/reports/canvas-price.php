<?php
	$hb_report = HB_Report_Price::instance();
?>
<div id="tp-hotel-booking-chart-container">
	<div id="tp-hotel-booking-canvas-chart"></div>
</div>

<script type="text/javascript">
	(function($){
		var options = {
	            chart: {
	                zoomType: 'x'
	            },
	            title: {
	                text: "<?php echo esc_js( $hb_report->_title ) ?>"
	            },
	            subtitle: {
	                text: document.ontouchstart === undefined ?
	                        "<?php _e('Click and drag in the plot area to zoom in', 'tp-hotel-booking') ?>" : "<?php _e('Pinch the chart to zoom in', 'tp-hotel-booking') ?>"
	            },
	            xAxis: {
	                type: 'datetime'
	            },
	            yAxis: {
	                title: {
	                    text: '<?php echo esc_js( ucfirst($hb_report->_chart_type) ) ?>'
	                },
	                min: 0
	            },
	            legend: {
	                enabled: false
	            },
	            tooltip: {
		            headerFormat: '<b>{point.x:%e. %b}</b><br>',
		            pointFormat: '<b><?php _e( "Total", "tp-hotel-booking" ) ?>:</b> {point.y:,.2f} <?php echo hb_get_currency() ?>'
		        },
	            plotOptions: {
	                area: {
	                    fillColor: {
	                        linearGradient: {
	                            x1: 0,
	                            y1: 0,
	                            x2: 0,
	                            y2: 1
	                        },
	                        stops: [
	                            [0, Highcharts.getOptions().colors[0]],
	                            [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
	                        ]
	                    },
	                    marker: {
	                        radius: 2
	                    },
	                    lineWidth: 1,
	                    states: {
	                        hover: {
	                            lineWidth: 1
	                        }
	                    },
	                    threshold: null
	                }
	            },

	            series: <?php echo json_encode( $hb_report->series() ) ?>
	        };

	        <?php if( $hb_report->chart_groupby === 'month' ): ?>

	        	options.xAxis.labels = {
	        		formatter: function () {
			            return Highcharts.dateFormat("%b", this.value);
			        }
	        	}
	        <?php endif; ?>

		$('#tp-hotel-booking-canvas-chart').highcharts(options);
	})(jQuery);
</script>