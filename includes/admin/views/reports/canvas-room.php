<?php
	$hb_report_room = HB_Report_Room::instance();
	$hb_report_room->series();
?>
<div id="tp-hotel-booking-chart-container">
	<div id="tp-hotel-booking-canvas-chart"></div>
</div>

<script type="text/javascript">
	(function($){
		$('#tp-hotel-booking-canvas-chart').highcharts({
	            chart: {
	            type: 'column'
	        },
	        title: {
	            text: 'Stacked column chart'
	        },
	        xAxis: {
	            type: 'datetime'
	        },
	        yAxis: {
	            min: 0,
	            title: {
	                text: 'Total fruit consumption'
	            },
	            stackLabels: {
	                enabled: true,
	                style: {
	                    fontWeight: 'bold',
	                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
	                }
	            }
	        },
	        legend: {
	            align: 'right',
	            x: -30,
	            verticalAlign: 'top',
	            y: 25,
	            floating: true,
	            backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
	            borderColor: '#CCC',
	            borderWidth: 1,
	            shadow: false
	        },
	        tooltip: {
	            formatter: function () {
	                return '<b>' + this.x + '</b><br/>' +
	                    this.series.name + ': ' + this.y + '<br/>' +
	                    'Total: ' + this.point.stackTotal;
	            }
	        },
	        plotOptions: {
	            column: {
	                stacking: 'normal',
	                dataLabels: {
	                    enabled: true,
	                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
	                    style: {
	                        textShadow: '0 0 3px black'
	                    }
	                }
	            }
	        },
	        series: [{
	            name: 'John',
	            data: [5, 3, 4, 7, 2]
	        }, {
	            name: 'Jane',
	            data: [2, 2, 3, 2, 1]
	        }, {
	            name: 'Joe',
	            data: [3, 4, 4, 2, 5]
	        }]
	    });
	})(jQuery);
</script>