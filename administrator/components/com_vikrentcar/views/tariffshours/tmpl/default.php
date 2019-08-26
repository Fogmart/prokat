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

$carrows = $this->carrows;
$rows = $this->rows;
$prices = $this->prices;
$allc = $this->allc;

$vrc_app = new VrcApplication();
$vrc_app->loadSelect2();

//header
$idcar = $carrows['id'];
$name = $carrows['name'];
if (file_exists(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$carrows['img']) && getimagesize(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$carrows['img'])) {
	$img = '<img align="middle" class="maxninety" alt="Car Image" src="' . VRC_ADMIN_URI . 'resources/'.$carrows['img'].'" />';
} else {
	$img = '<img align="middle" alt="Vik Rent Car Logo" src="' . VRC_ADMIN_URI . 'vikrentcar.png' . '" />';
}
$fprice = "<div class=\"vrc-fares-tabs\"><div class=\"dailyprices\"><a href=\"index.php?option=com_vikrentcar&task=tariffs&cid[]=".$idcar."\">".JText::_('VRCDAILYFARES')."</a></div><div class=\"hourscharges\"><a href=\"index.php?option=com_vikrentcar&task=hourscharges&cid[]=".$idcar."\">".JText::_('VRCHOURSCHARGES')."</a></div><div class=\"hourlypricesactive\">".JText::_('VRCHOURLYFARES')."</div></div>\n";
if (empty($prices)) {
	$fprice .= "<br/><span class=\"err\"><b>".JText::_('VRMSGONE')." <a href=\"index.php?option=com_vikrentcar&task=newprice\">".JText::_('VRHERE')."</a></b></span>";
} else {
	$colsp = "2";
	$fprice .= "<form name=\"newd\" class=\"vrc-fares-frm\" method=\"post\" action=\"index.php?option=com_vikrentcar\" onsubmit=\"javascript: if (!document.newd.hhoursfrom.value.match(/\S/)){alert('".JText::_('VRMSGTWO')."'); return false;} else {return true;}\">\n<div class=\"vrc-insertrates-cont\"><span class=\"vrc-ratestable-lbl\">".JText::_('VRCHOURS').": </span><table><tr><td><span class=\"vrc-fares-from-lbl\">".JText::_('VRDAYSFROM')."</span> <input type=\"number\" name=\"hhoursfrom\" id=\"hhoursfrom\" value=\"".(!is_array($prices) ? '1' : '')."\" min=\"1\" /></td><td>&nbsp;&nbsp;&nbsp; <span class=\"vrc-fares-to-lbl\">".JText::_('VRDAYSTO')."</span> <input type=\"number\" name=\"hhoursto\" id=\"hhoursto\" value=\"".(!is_array($prices) ? '30' : '')."\" min=\"1\" max=\"999\" /></td></tr></table>\n";
	$fprice .= "<span class=\"vrc-ratestable-lbl\">".JText::_('VRCHOURLYPRICES').": </span><table>\n";
	$currencysymb = VikRentCar::getCurrencySymb(true);
	foreach ($prices as $pr) {
		$fprice .= "<tr><td>".$pr['name'].": </td><td>".$currencysymb." <input type=\"number\" min=\"0\" step=\"any\" name=\"hprice".$pr['id']."\" value=\"\" style=\"width: 70px !important;\"/></td>";
		if (!empty($pr['attr'])) {
			$colsp = "4";
			$fprice .= "<td>".$pr['attr']."</td><td><input type=\"text\" name=\"hattr".$pr['id']."\" value=\"\" size=\"10\"/></td>";
		}
		$fprice .= "</tr>\n";
	}
	$fprice .= "<tr><td colspan=\"".$colsp."\" align=\"right\"><input type=\"submit\" class=\"vrsubmitfares btn btn-large btn-success\" name=\"newdispcost\" value=\"".JText::_('VRINSERT')."\"/></td></tr></table></div><input type=\"hidden\" name=\"cid[]\" value=\"".$idcar."\"/><input type=\"hidden\" name=\"task\" value=\"tariffshours\"/></form>";
}
$chcarsel = "<select id=\"vrc-car-selection\" name=\"cid[]\" onchange=\"javascript: document.vrchcar.submit();\">\n";
foreach ($allc as $cc) {
	$chcarsel .= "<option value=\"".$cc['id']."\"".($cc['id'] == $idcar ? " selected=\"selected\"" : "").">".$cc['name']."</option>\n";
}
$chcarsel .= "</select>\n";
$chcarf = "<form name=\"vrchcar\" method=\"post\" action=\"index.php?option=com_vikrentcar\"><input type=\"hidden\" name=\"task\" value=\"tariffshours\"/>".$chcarsel."</form>";
echo "<table><tr><td colspan=\"2\" valign=\"top\" align=\"left\"><div class=\"vradminfaresctitle\"><span class=\"vrc-uppbold\">".$name." - ".JText::_('VRINSERTFEE')."</span> <span style=\"float: right; text-transform: none;\">".$chcarf."</span></div></td></tr><tr><td valign=\"top\" align=\"left\">".$img."</td><td valign=\"top\" align=\"left\">".$fprice."</td></tr></table><br/>\n";
?>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('#hhoursfrom').change(function() {
		var fnights = parseInt(jQuery(this).val());
		if (!isNaN(fnights)) {
			jQuery('#hhoursto').attr('min', fnights);
			var tnights = jQuery('#hhoursto').val();
			if (!(tnights.length > 0)) {
				jQuery('#hhoursto').val(fnights);
			} else {
				if (parseInt(tnights) < fnights) {
					jQuery('#hhoursto').val(fnights);
				}
			}
		}
	});
	jQuery("#vrc-car-selection").select2();
});
</script>

<?php
//page content

if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VRNOTARFOUND'); ?></p>
	<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikrentcar">
	</form>
	<?php
} else {
	$mainframe = JFactory::getApplication();
	$lim = $mainframe->getUserStateFromRequest("com_vikrentcar.limit", 'limit', 15, 'int');
	$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
	$allpr = array();
	$tottar = array();
	foreach ($rows as $r) {
		if (!array_key_exists($r['idprice'], $allpr)) {
			$allpr[$r['idprice']] = VikRentCar::getPriceAttr($r['idprice']);
		}
		$tottar[$r['hours']][] = $r;
	}
	$prord = array();
	$prvar = '';
	foreach ($allpr as $kap => $ap) {
		$prord[] = $kap;
		$prvar .= "<th class=\"title center\" width=\"150\">".VikRentCar::getPriceName($kap).(!empty($ap) ? " - ".$ap : "")."</th>\n";
	}
	$totrows = count($tottar);
	$tottar = array_slice($tottar, $lim0, $lim, true);
	?>
<script type="text/javascript">
function vrRateSetTask(event) {
	event.preventDefault();
	document.getElementById('vrtarmod').value = '1';
	document.getElementById('vrtask').value = 'cars';
	document.adminForm.submit();
}
</script>
<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vrc-list-table">
		<thead>
		<tr>
			<th class="title left" width="100" style="text-align: left;"><?php echo JText::_( 'VRCPVIEWTARHOURS' ); ?></th>
			<?php echo $prvar; ?>
			<th width="20" class="title right" style="text-align: right;">
				<input type="submit" name="modtarhours" value="<?php echo JText::_( 'VRPVIEWTARTWO' ); ?>" onclick="vrRateSetTask(event);" class="btn" /> &nbsp; <input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
			</th>
		</tr>
		</thead>
	<?php
	$k = 0;
	$i = 0;
	foreach ($tottar as $kt => $vt) {
		?>
		<tr class="row<?php echo $k; ?>">
			<td class="left"><?php echo $kt; ?></td>
		<?php
		$multiid = "";
		foreach ($prord as $ord) {
			$thereis = false;
			foreach ($vt as $kkkt => $vvv) {
				if ($vvv['idprice'] == $ord) {
					$multiid .= $vvv['id'].";";
					echo "<td class=\"center\"><input type=\"number\" min=\"0\" step=\"any\" name=\"cost".$vvv['id']."\" value=\"".$vvv['cost']."\" style=\"width: 70px !important;\"/>".(!empty($vvv['attrdata'])? " - <input type=\"text\" name=\"attr".$vvv['id']."\" value=\"".$vvv['attrdata']."\" size=\"10\"/>" : "")."</td>\n";
					$thereis = true;
					break;
				}
			}
			
			if (!$thereis) {
				echo "<td></td>\n";
			}
			unset($thereis);
			
		}
		?>
		<td class="right" style="text-align: right;"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $multiid; ?>" onclick="Joomla.isChecked(this.checked);"></td>
		</tr>
		<?php
		unset($multiid);
		$k = 1 - $k;
		$i++;
	}
	?>
	</table>
</div>
	<input type="hidden" name="carid" value="<?php echo $carrows['id']; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo $carrows['id']; ?>" />
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" id="vrtask" value="tariffshours" />
	<input type="hidden" name="tarmod" id="vrtarmod" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php
	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $totrows, $lim0, $lim );
	$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
	echo $navbut;
	?>
</form>
<?php
}
