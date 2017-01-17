$(document).ready(function() {
    !verboseBuild || console.log('-- starting proton.graphsStats build');
    
    proton.graphsStats.build();
});

proton.graphsStats = {
	build: function () {
		// Morris Charts
		!(Morris && $('.graph').length) || proton.graphsStats.drawCharts();

		setTimeout(function() {
			proton.graphsStats.liveStats();
		}, 2000);

		!verboseBuild || console.log('            proton.graphsStats build DONE');
	},
	randomNum : function (from,to) {
		return Math.floor(Math.random()*(to-from+1)+from);
	},
	statChange : false,
	liveStats : function () {
		if (proton.graphsStats.statChange){
			clearTimeout(proton.graphsStats.statChange);
		}
		var likeOrBuy = proton.graphsStats.randomNum(0,5);
		if (likeOrBuy < 4)
			$('#like-count').text(parseInt($('#like-count').text()) + 1);
		else
			$('#buy-count').text(parseInt($('#buy-count').text()) + 1);

		proton.graphsStats.statChange = setTimeout(proton.graphsStats.liveStats, 500 * proton.graphsStats.randomNum(1,4));
	},
	graph : {},
	redrawCharts : function () {
		!verboseBuild || console.log('            proton.graphsStats.redrawCharts()');

		$.each(proton.graphsStats.graph, function(index, val) {
			this.redraw();
		});
	},
	drawCharts : function () {
		!verboseBuild || console.log('            proton.graphsStats.drawCharts()');

		if($('#myfirstchart').length)
		proton.graphsStats.graph.Line = Morris.Line({
			// ID of the element in which to draw the chart.
			element: 'myfirstchart',
			// Chart data records -- each entry in this array corresponds to a point on
			// the chart.
			data: [
			{ year: '2008', value: 20 },
			{ year: '2009', value: 10 },
			{ year: '2010', value: 5 },
			{ year: '2011', value: 5 },
			{ year: '2012', value: 20 }
			],
			// The name of the data record attribute that contains x-values.
			xkey: 'year',
			// A list of names of data record attributes that contain y-values.
			ykeys: ['value'],
			// Labels for the ykeys -- will be displayed when you hover over the
			// chart.
			labels: ['Value']
		});

		if($('#hero-donut').length)
		proton.graphsStats.graph.Donut = Morris.Donut({
		    element: 'hero-donut',
		    data: [
		      {label: 'Jam', value: 25 },
		      {label: 'Frosted', value: 40 },
		      {label: 'Custard', value: 25 },
		      {label: 'Sugar', value: 10 }
		    ],
		    formatter: function (y) { return y + "%" }
		});

		if($('#hero-bar').length)
		proton.graphsStats.graph.Bar = Morris.Bar({
		    element: 'hero-bar',
		    data: [
		      {device: 'iPhone', geekbench: 136},
		      {device: 'iPhone 3G', geekbench: 137},
		      {device: 'iPhone 3GS', geekbench: 275},
		      {device: 'iPhone 4', geekbench: 380},
		      {device: 'iPhone 4S', geekbench: 655},
		      {device: 'iPhone 5', geekbench: 1571}
		    ],
		    xkey: 'device',
		    ykeys: ['geekbench'],
		    labels: ['Geekbench'],
		    barRatio: 0.4,
		    xLabelAngle: 35,
		    hideHover: 'auto'
		});

		if($('#hero-area').length)
		proton.graphsStats.graph.Area = Morris.Area({
		    element: 'hero-area',
		    data: [
		      {period: '2010 Q1', iphone: 2666, ipad: null, itouch: 2647},
		      {period: '2010 Q2', iphone: 2778, ipad: 2294, itouch: 2441},
		      {period: '2010 Q3', iphone: 4912, ipad: 1969, itouch: 2501},
		      {period: '2010 Q4', iphone: 3767, ipad: 3597, itouch: 5689},
		      {period: '2011 Q1', iphone: 6810, ipad: 1914, itouch: 2293},
		      {period: '2011 Q2', iphone: 5670, ipad: 4293, itouch: 1881},
		      {period: '2011 Q3', iphone: 4820, ipad: 3795, itouch: 1588},
		      {period: '2011 Q4', iphone: 15073, ipad: 5967, itouch: 5175},
		      {period: '2012 Q1', iphone: 10687, ipad: 4460, itouch: 2028},
		      {period: '2012 Q2', iphone: 8432, ipad: 5713, itouch: 1791}
		    ],
		    xkey: 'period',
		    ykeys: ['iphone', 'ipad', 'itouch'],
		    labels: ['iPhone', 'iPad', 'iPod Touch'],
		    pointSize: 2,
		    hideHover: 'auto'
		});
	}
}