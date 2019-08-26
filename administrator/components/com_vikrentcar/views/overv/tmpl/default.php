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

$rows = $this->rows;
$lim0 = $this->lim0;
$navbut = $this->navbut;
$arrbusy = $this->arrbusy;
$wmonthsel = $this->wmonthsel;
$tsstart = $this->tsstart;
$all_locations = $this->all_locations;
$plocation = $this->plocation;
$plocationw = $this->plocationw;

$nowtf = VikRentCar::getTimeFormat(true);
$wdays_map = array(
	JText::_('VRSUN'),
	JText::_('VRMON'),
	JText::_('VRTUE'),
	JText::_('VRWED'),
	JText::_('VRTHU'),
	JText::_('VRFRI'),
	JText::_('VRSAT')
);

$session = JFactory::getSession();
$mnum = $session->get('vrcOvwMnum', '1');
$mnum = intval($mnum);
?>
<form class="vrc-avov-form" action="index.php?option=com_vikrentcar&amp;task=overv" method="post" name="vroverview">
	<div class="btn-toolbar vrc-avov-toolbar" id="filter-bar" style="width: 100%; display: inline-block;">
		<div class="btn-group pull-left">
			<?php echo $wmonthsel; ?>
		</div>
		<div class="btn-group pull-left">
			<select name="mnum" onchange="document.vroverview.submit();">
			<?php
			for ($i = 1; $i <= 12; $i++) { 
				?>
				<option value="<?php echo $i; ?>"<?php echo $i == $mnum ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEMONTHS').': '.$i; ?></option>
				<?php
			}
			?>
			</select>
		</div>
	<?php
	if (is_array($all_locations)) {
		$loc_options = '<option value="">'.JText::_('VRCORDERSLOCFILTERANY').'</option>'."\n";
		foreach ($all_locations as $location) {
			$loc_options .= '<option value="'.$location['id'].'"'.($location['id'] == $plocation ? ' selected="selected"' : '').'>'.$location['name'].'</option>'."\n";
		}
		?>
		<div class="btn-group pull-right">
			<button type="submit" class="btn btn-secondary"><?php echo JText::_('VRCORDERSLOCFILTERBTN'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<select name="locationw" id="locwfilter">
				<option value="pickup"><?php echo JText::_('VRCORDERSLOCFILTERPICK'); ?></option>
				<option value="dropoff"<?php echo $plocationw == 'dropoff' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCORDERSLOCFILTERDROP'); ?></option>
				<option value="both"<?php echo $plocationw == 'both' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCORDERSLOCFILTERPICKDROP'); ?></option>
			</select>
		</div>
		<div class="btn-group pull-right">
			<label for="locfilter" style="display: inline-block; margin-right: 5px;"><?php echo JText::_('VRCORDERSLOCFILTER'); ?></label>
			<select name="location" id="locfilter"><?php echo $loc_options; ?></select>
		</div>
		<?php
	}
	?>
	</div>
</form>

<?php
$nowts = getdate($tsstart);
$curts = $nowts;
for ($mind = 1; $mind <= $mnum; $mind++) {
?>
<div class="table-responsive">
	<table class="table vrcoverviewtable">
		<tr class="vrcoverviewtablerow">
			<td class="bluedays vrcoverviewtdone"><strong><?php echo VikRentCar::sayMonth($curts['mon'])." ".$curts['year']; ?></strong></td>
		<?php
		$moncurts = $curts;
		$mon = $moncurts['mon'];
		while ($moncurts['mon'] == $mon) {
			echo '<td align="center" class="bluedays"><span class="vrc-overv-mday">'.$moncurts['mday'].'</span><span class="vrc-overv-wday">'.$wdays_map[$moncurts['wday']].'</td>';
			$moncurts = getdate(mktime(0, 0, 0, $moncurts['mon'], ($moncurts['mday'] + 1), $moncurts['year']));
		}
		?>
		</tr>
		<?php
		foreach ($rows as $car) {
			$moncurts = $curts;
			$mon = $moncurts['mon'];
			echo '<tr class="vrcoverviewtablerow">';
			echo '<td class="carname"><span class="vrc-overview-carname">'.$car['name'].'</span> <span class="vrc-overview-carunits">'.$car['units'].'</span></td>';
			while ($moncurts['mon'] == $mon) {
				$dclass = "notbusy";
				$dalt = "";
				$bid = "";
				$totfound = 0;
				if (@is_array($arrbusy[$car['id']])) {
					foreach ($arrbusy[$car['id']] as $b) {
						$tmpone = getdate($b['ritiro']);
						$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
						$ritts = strtotime($rit);
						$tmptwo = getdate($b['consegna']);
						$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
						$conts = strtotime($con);
						if ($moncurts[0] >= $ritts && $moncurts[0] <= $conts) {
							$dclass = "busy";
							$bid = $b['idorder'];
							if ($moncurts[0] == $ritts) {
								$dalt = JText::_('VRPICKUPAT')." ".date($nowtf, $b['ritiro']);
							} elseif ($moncurts[0] == $conts) {
								$dalt = JText::_('VRRELEASEAT')." ".date($nowtf, $b['consegna']);
							}
							$totfound++;
						}
					}
				}
				$useday = ($moncurts['mday'] < 10 ? "0".$moncurts['mday'] : $moncurts['mday']);
				if ($totfound == 1) {
					$dlnk = "<a href=\"index.php?option=com_vikrentcar&task=editbusy&goto=overv&cid[]=".$bid."\" style=\"color: #ffffff;\">".$totfound."</a>";
					$cal = "<td align=\"center\" class=\"".$dclass."\"".(!empty($dalt) ? " title=\"".$dalt."\"" : "").">".$dlnk."</td>\n";
				} elseif ($totfound > 1) {
					$dlnk = "<a href=\"index.php?option=com_vikrentcar&task=choosebusy&goto=overv&idcar=".$car['id']."&ts=".$moncurts[0]."\" style=\"color: #ffffff;\">".$totfound."</a>";
					$cal = "<td align=\"center\" class=\"".$dclass."\">".$dlnk."</td>\n";
				} else {
					$dlnk = $useday;
					$cal = "<td align=\"center\" class=\"".$dclass."\">&nbsp;</td>\n";
				}
				echo $cal;
				$moncurts = getdate(mktime(0, 0, 0, $moncurts['mon'], ($moncurts['mday'] + 1), $moncurts['year']));
			}
			echo '</tr>';
		}
		?>
	</table>
</div>
<?php echo ($mind + 1) <= $mnum ? '<br/>' : ''; ?>
<?php
	$curts = getdate(mktime(0, 0, 0, ($nowts['mon'] + $mind), $nowts['mday'], $nowts['year']));
}
?>

<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="overv" />
	<input type="hidden" name="month" value="<?php echo $tsstart; ?>" />
	<input type="hidden" name="mnum" value="<?php echo $mnum; ?>" />
	<?php echo '<br/>'.$navbut; ?>
</form>
