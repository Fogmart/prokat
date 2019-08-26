<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

$ord = $this->ord;
$tar = $this->tar;
$payment = $this->payment;
//vikrentcar 1.6
$calcdays = $this->calcdays;
if (strlen($calcdays) > 0) {
	$origdays = $ord['days'];
	$ord['days'] = $calcdays;
}
//
$vrc_tn = $this->vrc_tn;

$is_cust_cost = (!empty($ord['cust_cost']) && $ord['cust_cost'] > 0);

if (VikRentCar::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', VRC_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
}

$currencysymb = VikRentCar::getCurrencySymb();
$nowdf = VikRentCar::getDateFormat();
$nowtf = VikRentCar::getTimeFormat();
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$dbo = JFactory::getDBO();
$carinfo = VikRentCar::getCarInfo($ord['idcar'], $vrc_tn);
if (is_array($tar)) {
	$prname = $is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN') : VikRentCar::getPriceName($tar['idprice'], $vrc_tn);
	$isdue = $is_cust_cost ? $tar['cost'] : VikRentCar::sayCostPlusIva($tar['cost'], $tar['idprice'], $ord);
} else {
	$prname = "";
	$isdue = 0;
}
if (!empty($ord['optionals'])) {
	$stepo = explode(";", $ord['optionals']);
	foreach ($stepo as $one) {
		if (!empty($one)) {
			$stept = explode(":", $one);
			$q = "SELECT * FROM `#__vikrentcar_optionals` WHERE `id`=" . $dbo->quote($stept[0]) . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$actopt = $dbo->loadAssocList();
				$vrc_tn->translateContents($actopt, '#__vikrentcar_optionals');
				$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $ord['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
				$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $ord['days']) : $actopt[0]['cost'];
				if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
					$realcost = $actopt[0]['maxprice'];
					if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
						$realcost = $actopt[0]['maxprice'] * $stept[1];
					}
				}
				$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $ord);
				$isdue += $tmpopr;
				$optbought .= ($stept[1] > 1 ? $stept[1] . " " : "") . "<span>" . $actopt[0]['name'] . ":</span>  <div class=\"optionprice2\"><span class=\"vrc_price\">" . VikRentCar::numberFormat($tmpopr) . "</span> <span class=\"vrc_currency\">" . $currencysymb . "</span></div><br/>";
			}
		}
	}
}
//custom extra costs
if (!empty($ord['extracosts'])) {
	$cur_extra_costs = json_decode($ord['extracosts'], true);
	foreach ($cur_extra_costs as $eck => $ecv) {
		$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $ord);
		$isdue += $efee_cost;
		$optbought .= $ecv['name'] . ": <div class=\"optionprice2\"><span class=\"vrc_currency\">" . $currencysymb . "</span> <span class=\"vrc_price\">" . VikRentCar::numberFormat($efee_cost) . "</span></div><br/>";
	}
}
//
if (!empty($ord['idplace']) && !empty($ord['idreturnplace'])) {
	$locfee = VikRentCar::getLocFee($ord['idplace'], $ord['idreturnplace']);
	if ($locfee) {
		//VikRentCar 1.7 - Location fees overrides
		if (strlen($locfee['losoverride']) > 0) {
			$arrvaloverrides = array();
			$valovrparts = explode('_', $locfee['losoverride']);
			foreach($valovrparts as $valovr) {
				if (!empty($valovr)) {
					$ovrinfo = explode(':', $valovr);
					$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
				}
			}
			if (array_key_exists($ord['days'], $arrvaloverrides)) {
				$locfee['cost'] = $arrvaloverrides[$ord['days']];
			}
		}
		//end VikRentCar 1.7 - Location fees overrides
		$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $ord['days']) : $locfee['cost'];
		$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $ord);
		$isdue += $locfeewith;
	}
}

//VRC 1.9 - Out of Hours Fees
$oohfee = VikRentCar::getOutOfHoursFees($ord['idplace'], $ord['idreturnplace'], $ord['ritiro'], $ord['consegna'], array('id' => (int)$ord['idcar']));
$ooh_time = '';
if (count($oohfee) > 0) {
	$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
	$ooh_time = $oohfee['pickup'] == 1 ? $oohfee['pickup_ooh'] : '';
	$ooh_time .= $oohfee['dropoff'] == 1 && $oohfee['dropoff_ooh'] != $oohfee['pickup_ooh'] ? (!empty($ooh_time) ? ', ' : '').$oohfee['dropoff_ooh'] : '';
	$isdue += $oohfeewith;
}
//

//vikrentcar 1.6 coupon
$usedcoupon = false;
$origisdue = $isdue;
if (strlen($ord['coupon']) > 0) {
	$usedcoupon = true;
	$expcoupon = explode(";", $ord['coupon']);
	$isdue = $isdue - $expcoupon[1];
}
//

$pitemid = VikRequest::getInt('Itemid', '', 'request');
$printer = VikRequest::getInt('printer', '', 'request');
if ($printer != 1) {
?>
<div class="vrcprintdiv"><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&task=vieworder&sid='.$ord['sid'].'&ts='.$ord['ts'].'&printer=1&tmpl=component'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" target="_blank"><i class="fas fa-print"></i></a></div>
<?php
}

echo VikRentCar::getFullFrontTitle($vrc_tn);
?>
		<div class="successmade">
			<i class="fas fa-check-circle"></i>
			<span><?php echo JText::_('VRCORDERNUMBER'); ?> <?php echo $ord['id'];?> - <?php echo JText::_('VRCCONFIRMATIONNUMBER'); ?> <?php echo $ord['sid'].'_'.$ord['ts'];?> - <?php echo JText::_('VRORDEREDON'); ?> <?php echo date($df.' '.$nowtf, $ord['ts']); ?></span>
		</div>
				
				<p><span class="vrcvordcarinfotitle"><?php echo JText::_('VRCARRENTED'); ?>:</span> <?php echo $carinfo['name']; ?></p>
				
		<div class="vrcvordudata-cnt">
		
		<?php
			if (!empty($carinfo['img']) && $printer != 1) {
				$imgpath = file_exists(VRC_ADMIN_PATH.DS.'resources'.DS.'vthumb_'.$carinfo['img']) ? VRC_ADMIN_URI.'resources/vthumb_'.$carinfo['img'] : VRC_ADMIN_URI.'resources/'.$carinfo['img'];
				?>
				<div class="vrc-imgorder-block">
					<img class="imgresult" alt="<?php echo $carinfo['name']; ?>" src="<?php echo $imgpath; ?>"/>
				</div>
				<?php
			}
			?>
			
			<div class="vrcvordcarinfo">
			
				
				<p><span class="vrcvordcarinfotitle"><?php echo JText::_('VRDAL'); ?></span> <?php echo date($df.' '.$nowtf, $ord['ritiro']); ?></p>
				
				<?php if (!empty($ord['idplace'])) { ?>
				<p><?php echo VikRentCar::getPlaceName($ord['idplace'], $vrc_tn); ?></p>
				<?php } ?>
				<p><span class="vrcvordcarinfotitle"><?php echo JText::_('VRAL'); ?></span> <?php echo date($df.' '.$nowtf, $ord['consegna']); ?></p>
				
				<?php if (!empty($ord['idreturnplace'])) { ?>
				<p><?php echo VikRentCar::getPlaceName($ord['idreturnplace'], $vrc_tn); ?></p>
				<?php } ?>
			</div>
			
			<div class="vrcvordudata">
				<p><span class="vrcvordudatatitle"><?php echo JText::_('VRPERSDETS'); ?>:</span> <?php echo nl2br($ord['custdata']); ?></p>		
			</div>
		
			
		</div>
		<div class="vrcvordcosts">
			<?php 
			if (is_array($tar)){
			?>
			<div class="vrcord_typecost"><span class="vrcvordcoststitle"><?php echo $prname; ?>:</span> <span class="vrc_priceorder"> <span class="vrc_price"><?php echo VikRentCar::numberFormat(($is_cust_cost ? $tar['cost'] : VikRentCar::sayCostPlusIva($tar['cost'], $tar['idprice'], $ord))); ?></span> <span class="vrc_currency"><?php echo $currencysymb; ?></span></span></div>
			<?php } ?>
			<?php if (strlen($optbought)){ ?>
			<div class="vrcord_typecost"><span class="vrcvordcoststitle"><?php echo JText::_('VROPTS'); ?>:</span><div class="optionprice"><?php echo $optbought; ?></div></div>
			<?php } ?>
			<?php if ($locfeewith) { ?>
			<div class="vrcord_typecost"><span class="vrcvordcoststitle"><?php echo JText::_('VRLOCFEETOPAY'); ?>:</span> <span class="vrc_priceorder"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo VikRentCar::numberFormat($locfeewith); ?></span></span></div>
			<?php } ?>
			<?php if ($oohfeewith) { ?>
			<div class="vrcord_typecost"><span class="vrcvordcoststitle"><?php echo JText::sprintf('VRCOOHFEETOPAY', $ooh_time); ?></span> <span class="vrc_priceorder"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo VikRentCar::numberFormat($oohfeewith); ?></span></span></div>
			<?php } ?>
			<?php if ($usedcoupon == true) { ?>
			<div class="vrcord_typecost"><span class="vrcvordcoststitle"><?php echo JText::_('VRCCOUPON').' '.$expcoupon[2]; ?>:</span> - <span class="vrc_priceorder"> <span class="vrc_price vrc_keepcost"><?php echo VikRentCar::numberFormat($expcoupon[1]); ?></span> <span class="vrc_currency vrc_keepcost"><?php echo $currencysymb; ?></span></span></div>
			<?php } ?>
			<!--<div class="vrcvordcoststot"><span class="vrcvordcoststitle"><?php echo JText::_('VRTOTAL'); ?>:</span> <span class="vrc_priceorder"> <span class="vrc_price vrc_keepcost"><?php echo VikRentCar::numberFormat($ord['order_total']); ?></span> <span class="vrc_currency vrc_keepcost"><?php echo $currencysymb; ?></span></span></div> -->
			<?php
			if ($ord['totpaid'] > 0.00 && !($ord['totpaid'] > $ord['order_total'])) {
				?>
				<div class="vrcvordcoststot"><span class="vrcvordcoststitle"><?php echo JText::_('VRCAMOUNTPAID'); ?>:</span> <span class="vrc_priceorder"> <span class="vrc_price vrc_keepcost"><?php echo VikRentCar::numberFormat($ord['totpaid']); ?></span> <span class="vrc_currency vrc_keepcost"><?php echo $currencysymb; ?></span></span></div>
				<?php
			}
			?>
		</div>
		<?php
//hide prices in case the tariffs have changed for these dates
if (number_format($isdue, 2) != number_format($ord['order_total'], 2)) {
	?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery(".vrc_currency, .vrc_price").not(".vrc_keepcost").text("");
		//jQuery(".vrc_currency, .vrc_price").not(".vrc_keepcost").prev("span").html().replace(":", "");
		jQuery(".vrc_currency, .vrc_price").not(".vrc_keepcost").each(function(){
			var cur_txt = jQuery(this).prev("span").html();
			if (cur_txt) {
				jQuery(this).prev("span").html(cur_txt.replace(":", ""));
			}
		});
	});
	</script>
	<?php
}
//

if (@ is_array($payment) && intval($payment['shownotealw']) == 1) {
	if (strlen($payment['note']) > 0) {
		?>
		<div class="vrcvordpaynote">
		<?php
	}
	echo $payment['note'];
	if (strlen($payment['note']) > 0) {
		?>
		</div>
		<?php
	}
}

if ($printer == 1) {
	?>
	<script language="JavaScript" type="text/javascript">
	window.print();
	</script>
	<?php
}else {
	//VikRentCar 1.7 Download PDF
	if (file_exists(VRC_SITE_PATH . DS . "resources" . DS . "pdfs" . DS . $ord['id'].'_'.$ord['ts'].'.pdf')) {
		?>
		<p class="vrcdownloadpdf"><a href="<?php echo VRC_SITE_URI; ?>resources/pdfs/<?php echo $ord['id'].'_'.$ord['ts']; ?>.pdf" target="_blank"><i class="fas fa-file-pdf"></i> <?php echo JText::_('VRCDOWNLOADPDF'); ?></a></p>
		<?php
	}
	//VikRentCar 1.7 Cancellation Request
	?>
	<script type="text/javascript">
	function vrcOpenCancOrdForm() {
		document.getElementById('vrcopencancform').style.display = 'none';
		document.getElementById('vrcordcancformbox').style.display = 'block';
	}
	function vrcValidateCancForm() {
		var vrvar = document.vrccanc;
		if (!document.getElementById('vrccancemail').value.match(/\S/)) {
			document.getElementById('vrcformcancemail').style.color='#ff0000';
			return false;
		}else {
			document.getElementById('vrcformcancemail').style.color='';
		}
		if (!document.getElementById('vrccancreason').value.match(/\S/)) {
			document.getElementById('vrcformcancreason').style.color='#ff0000';
			return false;
		}else {
			document.getElementById('vrcformcancreason').style.color='';
		}
		return true;
	}
	</script>
	<div class="vrcordcancbox">
		<h3><?php echo JText::_('VRCREQUESTCANCMOD'); ?></h3>
		<a href="javascript: void(0);" id="vrcopencancform" onclick="javascript: vrcOpenCancOrdForm();"><?php echo JText::_('VRCREQUESTCANCMODOPENTEXT'); ?></a>
		<div class="vrcordcancformbox" id="vrcordcancformbox">
			<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" name="vrccanc" method="post" onsubmit="javascript: return vrcValidateCancForm();">
				<table>
					<tr><td id="vrcformcancemail"><?php echo JText::_('VRCREQUESTCANCMODEMAIL'); ?>: </td><td><input type="text" class="vrcinput" name="email" id="vrccancemail" value="<?php echo $ord['custmail']; ?>"/></td></tr>
					<tr><td style="vertical-align: top;" id="vrcformcancreason"><?php echo JText::_('VRCREQUESTCANCMODREASON'); ?>: </td><td><textarea name="reason" id="vrccancreason" rows="7" cols="30" class="vrctextarea"></textarea></td></tr>
					<tr><td colspan="2" style="text-align: right;"><input type="submit" name="sendrequest" value="<?php echo JText::_('VRCREQUESTCANCMODSUBMIT'); ?>" class="button"/></td></tr>
				</table>
				<input type="hidden" name="sid" value="<?php echo $ord['sid']; ?>"/>
				<input type="hidden" name="idorder" value="<?php echo $ord['id']; ?>"/>
				<input type="hidden" name="option" value="com_vikrentcar"/>
				<input type="hidden" name="task" value="cancelrequest"/>
			<?php
			if (!empty($pitemid)) {
				?>
				<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
				<?php
			}
			?>
			</form>
		</div>
	</div>
	<?php
}
if ($ord['seen'] < 1) {
	VikRentCar::printConversionCode($ord['id']);
}
