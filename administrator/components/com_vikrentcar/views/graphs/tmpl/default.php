<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') or die('Restricted access');

$bookings = $this->bookings;
$arr_cars = $this->arr_cars;
$fromts = $this->fromts;
$tots = $this->tots;
$pstatsmode = $this->pstatsmode;
$arr_months = $this->arr_months;
$arr_channels = $this->arr_channels;
$arr_countries = $this->arr_countries;
$arr_totals = $this->arr_totals;
$tot_cars_units = $this->tot_cars_units;

JHTML::_('behavior.tooltip');
JHTML::_('behavior.calendar');
$pid_car = VikRequest::getInt('id_car', '', 'request');
$df = VikRentCar::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$usedf = 'd/m/Y';
} elseif ($df == "%m/%d/%Y") {
	$usedf = 'm/d/Y';
} else {
	$usedf = 'Y/m/d';
}
$currencysymb = VikRentCar::getCurrencySymb(true);
$days_diff = (int)floor(($tots - $fromts) / 86400);
?>
<form action="index.php?option=com_vikrentcar&amp;task=graphs" id="vrc-statsform" method="post" style="margin: 0;">
	<div id="filter-bar" class="btn-toolbar" style="width: 100%; display: inline-block;">
		<div class="btn-group pull-left">
			<select name="statsmode" onchange="document.getElementById('vrc-statsform').submit();">
				<option value="ts"<?php echo $pstatsmode == 'ts' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSTATSMODETS'); ?></option>
				<option value="nights"<?php echo $pstatsmode == 'nights' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSTATSMODENIGHTS'); ?></option>
			</select>
		</div>
		<div class="btn-group pull-right">
			&nbsp;<button type="submit" class="btn"><?php echo JText::_('VRCORDERSLOCFILTERBTN'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<select name="id_car">
				<option value=""><?php echo JText::_('VRCSTATSALLCARS'); ?></option>
			<?php
			foreach ($arr_cars as $car) {
				?>
				<option value="<?php echo $car['id']; ?>"<?php echo $car['id'] == $pid_car ? ' selected="selected"' : ''; ?>><?php echo $car['name']; ?></option>
				<?php
			}
			?>
			</select>
		</div>
		<div class="btn-group pull-right">
			<?php echo JHTML::_('calendar', date($usedf, $tots), 'dto', 'dto', $df, array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?>
		</div>
		<div class="btn-group pull-right">
			<?php echo JHTML::_('calendar', date($usedf, $fromts), 'dfrom', 'dfrom', $df, array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?>
		</div>
	</div>
</form>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#dfrom').val('<?php echo date($usedf, $fromts); ?>').attr('data-alt-value', '<?php echo date($usedf, $fromts); ?>');
	jQuery('#dto').val('<?php echo date($usedf, $tots); ?>').attr('data-alt-value', '<?php echo date($usedf, $tots); ?>');
});
</script>
<?php
$months_map = array(
	'1' => JText::_('VRSHORTMONTHONE'),
	'2' => JText::_('VRSHORTMONTHTWO'),
	'3' => JText::_('VRSHORTMONTHTHREE'),
	'4' => JText::_('VRSHORTMONTHFOUR'),
	'5' => JText::_('VRSHORTMONTHFIVE'),
	'6' => JText::_('VRSHORTMONTHSIX'),
	'7' => JText::_('VRSHORTMONTHSEVEN'),
	'8' => JText::_('VRSHORTMONTHEIGHT'),
	'9' => JText::_('VRSHORTMONTHNINE'),
	'10' => JText::_('VRSHORTMONTHTEN'),
	'11' => JText::_('VRSHORTMONTHELEVEN'),
	'12' => JText::_('VRSHORTMONTHTWELVE')
);
if (!(count($bookings) > 0) || !(count($arr_months) > 0)) {
	?>
<p class="warn"><?php echo JText::_('VRNOBOOKINGSTATS'); ?></p>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikrentcar" />
</form>
	<?php
} else {
	$datasets = array();
	$donut_datasets = array();
	$nights_datasets = array();
	$nights_donut_datasets = array();
	$months_labels = array_keys($arr_months);
	foreach ($months_labels as $mlbk => $mlbv) {
		$mlb_parts = explode('-', $mlbv);
		$months_labels[$mlbk] = $months_map[$mlb_parts[0]].' '.$mlb_parts[1];
	}
	$tot_months = count($months_labels);
	$tot_channels = count($arr_channels);
	$cars_pool = array();
	foreach ($bookings as $bk => $bv) {
		if (array_key_exists('car_names', $bv) && count($bv['car_names']) > 0) {
			foreach ($bv['car_names'] as $r) {
				if (!in_array($r, $cars_pool)) {
					$cars_pool[] = $r;
				}
			}
		}
	}
	$tot_cars = count($cars_pool);
	$rand_max = $tot_channels + $tot_cars;
	$rgb_rand = array();
	for ($z = 0; $z < $rand_max; $z++) { 
		$rgb_rand[$z] = mt_rand(0, 255).','.mt_rand(0, 255).','.mt_rand(0, 255);
	}
	$known_ch_rgb = array(
		JText::_('VRCWEBSITECHANNEL') => '34,72,93',
	);
	$ch_dataset = array();
	$ch_donut_dataset = array();
	$ch_map = array();
	foreach ($arr_channels as $chname) {
		$ch_color = $rgb_rand[rand(0, ($tot_channels - 1))];
		if (array_key_exists(strtolower($chname), $known_ch_rgb)) {
			$ch_color = $known_ch_rgb[strtolower($chname)];
		} else {
			foreach ($known_ch_rgb as $kch => $krgb) {
				if (stripos($chname, $kch) !== false) {
					$ch_color = $krgb;
					break;
				}
			}
		}
		$ch_dataset[$chname] = array(
			'label' => $chname,
			'fillColor' => "rgba(".$ch_color.",0.2)",
			'strokeColor' => "rgba(".$ch_color.",1)",
			'pointColor' => "rgba(".$ch_color.",1)",
			'pointStrokeColor' => "#fff",
			'pointHighlightFill' => "#fff",
			'pointHighlightStroke' => "rgba(".$ch_color.",1)",
			'tot_bookings' => 0,
			'data' => array()
		);
		$ch_donut_dataset[$chname] = array(
			'label' => $chname,
			'color' => "rgba(".$ch_color.",1)",
			'highlight' => "rgba(".$ch_color.",0.9)",
			'value' => 0
		);
		$ch_map[$chname] = $chname;
	}
	$ch_nights_dataset = array(
		'label' => JText::_('VRCGRAPHTOTNIGHTSLBL'),
		'fillColor' => "rgba(34,72,93,0.2)",
		'strokeColor' => "rgba(34,72,93,1)",
		'pointColor' => "rgba(34,72,93,1)",
		'pointStrokeColor' => "#fff",
		'pointHighlightFill' => "#fff",
		'pointHighlightStroke' => "rgba(34,72,93,1)",
		'tot_nights' => 0,
		'data' => array()
	);
	$ch_nights_donut_dataset = array();
	foreach ($cars_pool as $rpk => $r) {
		$ch_color = $rgb_rand[($tot_channels + $rpk)];
		$ch_nights_donut_dataset[$r] = array(
			'label' => $r,
			'color' => "rgba(".$ch_color.",1)",
			'highlight' => "rgba(".$ch_color.",0.9)",
			'value' => 0
		);
	}
	foreach ($arr_months as $monyear => $chbookings) {
		$tot_monchannels = count($chbookings);
		$monchannels = array();
		$totnb = 0;
		foreach ($chbookings as $chname => $ords) {
			$monchannels[] = $chname;
			$totchb = 0;
			foreach ($ords as $ord) {
				$totchb += (float)$ord['order_total'];
				$totnb += $ord['days'];
				if (array_key_exists('car_names', $ord)) {
					foreach ($ord['car_names'] as $r) {
						if (array_key_exists($r, $ch_nights_donut_dataset)) {
							$ch_nights_donut_dataset[$r]['value'] += $ord['days'];
						}
					}
				}
			}
			$ch_dataset[$chname]['tot_bookings'] += count($ords);
			$ch_dataset[$chname]['data'][] = $totchb;
			$ch_donut_dataset[$chname]['value'] += $totchb;
		}
		$ch_nights_dataset['tot_nights'] += $totnb;
		$ch_nights_dataset['data'][] = $totnb;
		if ($tot_monchannels < $tot_channels) {
			$ch_missing = array_diff($ch_map, $monchannels);
			foreach ($ch_missing as $chnk => $chnv) {
				if (array_key_exists($chnv, $ch_dataset)) {
					$ch_dataset[$chnv]['data'][] = 0;
				}
			}
		}
	}
	foreach ($ch_dataset as $chname => $chgraph) {
		$chgraph['label'] = $chgraph['label'].' ('.$chgraph['tot_bookings'].')';
		unset($chgraph['tot_bookings']);
		$datasets[] = $chgraph;
	}
	foreach ($ch_donut_dataset as $chname => $chgraph) {
		$donut_datasets[] = $chgraph;
	}
	$nights_datasets[] = $ch_nights_dataset;
	//Sort the array depending on the number of days sold per car
	$nights_donut_sortmap = array();
	foreach ($ch_nights_donut_dataset as $rname => $rgraph) {
		$nights_donut_sortmap[$rname] = $rgraph['value'];
	}
	arsort($nights_donut_sortmap);
	$copy_nights_donut = $ch_nights_donut_dataset;
	$ch_nights_donut_dataset = array();
	foreach ($nights_donut_sortmap as $rname => $soldnights) {
		$ch_nights_donut_dataset[$rname] = $copy_nights_donut[$rname];
	}
	unset($copy_nights_donut);
	//end Sort
	foreach ($ch_nights_donut_dataset as $rname => $rgraph) {
		$nights_donut_datasets[] = $rgraph;
	}
	?>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<fieldset class="adminform">
		<legend class="adminlegend"><?php echo JText::sprintf('VRCSTATSFOR', count($bookings), $days_diff); ?></legend>
		<div class="vrc-graph-introtitle"><span><?php echo JText::_('VRCGRAPHTOTSALES'); ?></span></div>
		<div class="vrc-graphstats-left">
			<canvas id="vrc-graphstats-left-canv"></canvas>
			<div id="vrc-graphstats-left-legend"></div>
		</div>
		<!--
		<div class="vrc-graphstats-right">
			<canvas id="vrc-graphstats-right-canv"></canvas>
			<div id="vrc-graphstats-right-legend"></div>
		</div>
		-->
		<div class="vrc-graphstats-secondright">
			<h4><?php echo JText::_('VRCSTATSTOPCOUNTRIES'); ?></h4>
			<div class="vrc-graphstats-countries">
			<?php
			$clisted = 0;
			foreach ($arr_countries as $ccode => $cdata) {
				if ($clisted > 4) {
					break;
				}
				?>
				<div class="vrc-graphstats-country-wrap">
					<span class="vrc-graphstats-country-img"><?php echo $cdata['img']; ?></span>
					<span class="vrc-graphstats-country-name"><?php echo $cdata['country_name']; ?></span>
					<span class="vrc-graphstats-country-totb badge"><?php echo $cdata['tot_bookings']; ?></span>
				</div>
				<?php
				$clisted++;
			}
			?>
			</div>
		</div>
		<div class="vrc-graphstats-thirdright">
			<p class="vrc-graphstats-income"><span><?php echo JText::_('VRCSTATSTOTINCOME'); ?></span> <?php echo $currencysymb.' '.VikRentCar::numberFormat($arr_totals['total_income']); ?></p>
			<?php
		if ($pstatsmode == 'nights') {
			?>
			<span style="float: right;"><i class="vrcicn-info hasTooltip" title="<?php echo addslashes(JText::_('VRCGRAPHAVGVALUES')); ?>"></i></span>
			<?php
		}
		?>
		</div>
	<?php
	if ($pstatsmode == 'nights') {
		$tot_occ_pcent = round((100 * $arr_totals['nights_sold'] / ($tot_cars_units * $days_diff)), 3);
	?>
		<br clear="all" /><br/>
		<div class="vrc-graph-introtitle"><span><?php echo JText::sprintf('VRCGRAPHTOTNIGHTS', $arr_totals['nights_sold']); ?> - <?php echo JText::sprintf('VRCGRAPHTOTOCCUPANCY', $tot_occ_pcent); ?></span></div>
		<div class="vrc-graphstats-left vrc-graphstats-left-nights">
			<canvas id="vrc-graphstats-left-canv-nights"></canvas>
			<div id="vrc-graphstats-left-legend-nights"></div>
		</div>
	<?php
		if (count($nights_donut_datasets) > 0) {
		?>
		<div class="vrc-graphstats-right vrc-graphstats-right-nights">
			<canvas id="vrc-graphstats-right-canv-nights"></canvas>
			<div id="vrc-graphstats-right-legend-nights"></div>
		</div>
		<?php
		}
		?>
		<div class="vrc-graphstats-thirdright vrc-graphstats-thirdright-nights">
			<p class="vrc-graphstats-totocc"><span><?php echo JText::_('VRCGRAPHTOTOCCUPANCYLBL'); ?></span> <?php echo $tot_occ_pcent; ?>%</p>
			<p class="vrc-graphstats-totunits"><span><?php echo JText::_('VRCGRAPHTOTUNITSLBL'); ?></span> <?php echo $tot_cars_units; ?></p>
		<?php
		if ($tot_months > 1 && count($nights_datasets[0]['data']) > 1) {
			$remonths_labels = array_keys($arr_months);
			$max_nights = max($nights_datasets[0]['data']);
			$min_nights = min($nights_datasets[0]['data']);
			$max_month_key = array_search($max_nights, $nights_datasets[0]['data']);
			$min_month_key = array_search($min_nights, $nights_datasets[0]['data']);
			$max_monyear = explode('-', $remonths_labels[$max_month_key]);
			$max_month_days = date('t', mktime(0, 0, 0, $max_monyear[0], 1, $max_monyear[1]));
			$min_monyear = explode('-', $remonths_labels[$min_month_key]);
			$min_month_days = date('t', mktime(0, 0, 0, $min_monyear[0], 1, $min_monyear[1]));
			if ($max_month_key !== false && $min_month_key !== false) {
				?>
			<div class="vrc-graphstats-thirdright-nights-bestworst">
				<span class="vrc-graphstats-nights-best"><i class="vrcicn-stats-bars2" style="color: green;"></i> <?php echo $months_labels[$max_month_key]; ?>: <?php echo $max_nights; ?> <?php echo JText::_('VRCGRAPHTOTNIGHTSLBL'); ?> (<?php echo round((100 * $max_nights / ($tot_cars_units * $max_month_days)), 3); ?>%)</span>
				<span class="vrc-graphstats-nights-worst"><?php echo $months_labels[$min_month_key]; ?>: <?php echo $min_nights; ?> <?php echo JText::_('VRCGRAPHTOTNIGHTSLBL'); ?> (<?php echo round((100 * $min_nights / ($tot_cars_units * $min_month_days)), 3); ?>%) <i class="vrcicn-stats-bars2" style="color: red; margin: 0 0 0 0.25em;"></i></span>
			</div>
				<?php
			}
		}
		?>
		</div>
		<?php
	}
	?>
	</fieldset>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikrentcar" />
</form>
<script type="text/javascript">
Chart.defaults.global.responsive = true;

var data = {
	labels: <?php echo json_encode($months_labels); ?>,
	datasets: <?php echo json_encode($datasets); ?>
};

var donut_data = <?php echo json_encode($donut_datasets); ?>;

var nights_data = {
	labels: <?php echo json_encode($months_labels); ?>,
	datasets: <?php echo json_encode($nights_datasets); ?>
};

var nights_donut_data = <?php echo json_encode($nights_donut_datasets); ?>;

var options = {
	///Boolean - Whether grid lines are shown across the chart
	scaleShowGridLines : true,
	//String - Colour of the grid lines
	scaleGridLineColor : "rgba(0,0,0,.05)",
	//Number - Width of the grid lines
	scaleGridLineWidth : 1,
	//Boolean - Whether to show horizontal lines (except X axis)
	scaleShowHorizontalLines: true,
	//Boolean - Whether to show vertical lines (except Y axis)
	scaleShowVerticalLines: true,
	//Boolean - Whether the line is curved between points
	bezierCurve : true,
	//Number - Tension of the bezier curve between points
	bezierCurveTension : 0.4,
	//Boolean - Whether to show a dot for each point
	pointDot : true,
	//Number - Radius of each point dot in pixels
	pointDotRadius : 4,
	//Number - Pixel width of point dot stroke
	pointDotStrokeWidth : 1,
	//Number - amount extra to add to the radius to cater for hit detection outside the drawn point
	pointHitDetectionRadius : 20,
	//Boolean - Whether to show a stroke for datasets
	datasetStroke : true,
	//Number - Pixel width of dataset stroke
	datasetStrokeWidth : 2,
	//Boolean - Whether to fill the dataset with a colour
	datasetFill : true,
	//String - A legend template
	legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++) {%><li><span class=\"entry\" style=\"background-color:<%=datasets[i].strokeColor%>\"></span><%if (datasets[i].label) {%><%=datasets[i].label%><%}%></li><%}%></ul>",
	tooltipTemplate: "<%if (label) {%><%=label%>: <%}%><?php echo $currencysymb; ?> <%=value%>",
	multiTooltipTemplate: "<%if (datasetLabel) {%><%=datasetLabel.substring( 0, datasetLabel.indexOf('(')-1 )%>: <%}%><?php echo $currencysymb; ?> <%=value%>",
	scaleLabel: "<?php echo $currencysymb; ?> <%=value%>"
};

var donut_options = {
	//Boolean - Whether we should show a stroke on each segment
	segmentShowStroke : true,
	//String - The colour of each segment stroke
	segmentStrokeColor : "#fff",
	//Number - The width of each segment stroke
	segmentStrokeWidth : 2,
	//Number - The percentage of the chart that we cut out of the middle
	//percentageInnerCutout : 30, // This is 0 for Pie charts, 50 for Donut charts
	//Number - Amount of animation steps
	animationSteps : 100,
	//String - Animation easing effect
	animationEasing : "easeOutQuart",
	//Boolean - Whether we animate the rotation of the Doughnut
	animateRotate : true,
	//Boolean - Whether we animate scaling the Doughnut from the centre
	animateScale : false,
	legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++) {%><li><span class=\"entry\" style=\"background-color:<%=segments[i].fillColor%>\"></span><%if (segments[i].label) {%><%=segments[i].label%><span class=\"vrc-graphstats-legend-sub\">(<?php echo $currencysymb; ?> <%=segments[i].value%>)</span><%}%></li><%}%></ul>",
	tooltipTemplate: "<%if (label) {%><%=label%>: <%}%><?php echo $currencysymb; ?> <%=value%>"
};

var ctx = document.getElementById("vrc-graphstats-left-canv").getContext("2d");
var vrcLineChart = new Chart(ctx).Line(data, options);
var legend = vrcLineChart.generateLegend();
jQuery('#vrc-graphstats-left-legend').html(legend);

/*
var donut_ctx = document.getElementById("vrc-graphstats-right-canv").getContext("2d");
var vrcDonutChart = new Chart(donut_ctx).Pie(donut_data, donut_options);
var legend = vrcDonutChart.generateLegend();
jQuery('#vrc-graphstats-right-legend').html(legend);
*/

<?php if ($pstatsmode == 'nights') { ?>
var nights_options = options;
nights_options.tooltipTemplate = "<%if (label) {%><%=label%>: <%}%><%=value%> <?php echo addslashes(JText::_('VRCGRAPHTOTNIGHTSLBL')); ?>";
nights_options.scaleLabel = "<%=value%>";
var nights_ctx = document.getElementById("vrc-graphstats-left-canv-nights").getContext("2d");
var vrcLineChart = new Chart(nights_ctx).Line(nights_data, nights_options);
var legend = vrcLineChart.generateLegend();
jQuery('#vrc-graphstats-left-legend-nights').html(legend);
<?php } ?>

<?php if (count($nights_donut_datasets) > 0) { ?>
var nights_donut_options = donut_options;
nights_donut_options.legendTemplate = "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++) {%><li><span class=\"entry\" style=\"background-color:<%=segments[i].fillColor%>\"></span><%if (segments[i].label) {%><%=segments[i].label%><span class=\"vrc-graphstats-legend-sub\">(<%=segments[i].value%>)</span><%}%></li><%}%></ul>";
nights_donut_options.tooltipTemplate = "<%if (label) {%><%=label%>: <%}%> <%=value%> <?php echo addslashes(JText::_('VRCGRAPHTOTNIGHTSLBL')); ?>";
var donut_ctx = document.getElementById("vrc-graphstats-right-canv-nights").getContext("2d");
var vrcDonutChart = new Chart(donut_ctx).Pie(nights_donut_data, nights_donut_options);
var legend = vrcDonutChart.generateLegend();
jQuery('#vrc-graphstats-right-legend-nights').html(legend);
<?php } ?>

</script>
	<?php
}
