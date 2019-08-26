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
$imp = $is_cust_cost ? VikRentCar::sayCustCostMinusIva($tar['cost'], $ord['cust_idiva']) : VikRentCar::sayCostMinusIva($tar['cost'], $tar['idprice'], $ord);
$isdue = $is_cust_cost ? $tar['cost'] : VikRentCar::sayCostPlusIva($tar['cost'], $tar['idprice'], $ord);
if (!empty ($ord['optionals'])) {
	$stepo = explode(";", $ord['optionals']);
	foreach ($stepo as $one) {
		if (!empty ($one)) {
			$stept = explode(":", $one);
			$q = "SELECT * FROM `#__vikrentcar_optionals` WHERE `id`=" . $dbo->quote($stept[0]) . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$actopt = $dbo->loadAssocList();
				$vrc_tn->translateContents($actopt, '#__vikrentcar_optionals');
				$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $ord['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
				$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $ord['days']) : $actopt[0]['cost'];
				if (!empty ($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
					$realcost = $actopt[0]['maxprice'];
					if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
						$realcost = $actopt[0]['maxprice'] * $stept[1];
					}
				}
				$imp += VikRentCar::sayOptionalsMinusIva($realcost, $actopt[0]['idiva'], $ord);
				$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $ord);
				$isdue += $tmpopr;
				$optbought .= ($stept[1] > 1 ? $stept[1] . " " : "") . $actopt[0]['name'] . ": <span class=\"vrc_currency\">" . $currencysymb . "</span> <span class=\"vrc_price\">" . VikRentCar::numberFormat($tmpopr) . "</span><br/>";
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
		$efee_cost_without = VikRentCar::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $ord);
		$imp += $efee_cost_without;
		$optbought .= $ecv['name'] . ": <span class=\"vrc_currency\">" . $currencysymb . "</span> <span class=\"vrc_price\">" . VikRentCar::numberFormat($efee_cost) . "</span><br/>";
	}
}
//
if (!empty ($ord['idplace']) && !empty ($ord['idreturnplace'])) {
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
		$locfeewithout = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $ord);
		$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $ord);
		$imp += $locfeewithout;
		$isdue += $locfeewith;
	}
}
//VRC 1.9 - Out of Hours Fees
$oohfee = VikRentCar::getOutOfHoursFees($ord['idplace'], $ord['idreturnplace'], $ord['ritiro'], $ord['consegna'], array('id' => (int)$ord['idcar']));
$ooh_time = '';
if (count($oohfee) > 0) {
	$oohfeewithout = VikRentCar::sayOohFeeMinusIva($oohfee['cost'], $oohfee['idiva']);
	$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
	$ooh_time = $oohfee['pickup'] == 1 ? $oohfee['pickup_ooh'] : '';
	$ooh_time .= $oohfee['dropoff'] == 1 && $oohfee['dropoff_ooh'] != $oohfee['pickup_ooh'] ? (!empty($ooh_time) ? ', ' : '').$oohfee['dropoff_ooh'] : '';
	$imp += $oohfeewithout;
	$isdue += $oohfeewith;
}
//

$tax = $isdue - $imp;

//vikrentcar 1.6 coupon
$usedcoupon = false;
$origisdue = $isdue;
if (strlen($ord['coupon']) > 0) {
	$usedcoupon = true;
	$expcoupon = explode(";", $ord['coupon']);
	$isdue = $isdue - $expcoupon[1];
}
//

//echo VikRentCar::getFullFrontTitle();

?>
		<?php
		if ($ord['status'] == 'standby') {
			?>
		<div class="warn">
			<i class="fas fa-exclamation-triangle"></i>
			<span><?php echo JText::_('VRORDEREDON'); ?> <?php echo date($df.' '.$nowtf, $ord['ts']); ?> - <?php echo JText::_('VRWAITINGPAYM'); ?></span>
		</div>
			<?php
		} else {
			//Cancelled
			?>
		<div class="err">
			<i class="fas fa-times-circle"></i>
			<span><?php echo JText::_('VRORDEREDON'); ?> <?php echo date($df.' '.$nowtf, $ord['ts']); ?> - <?php echo JText::_('VRCANCELLED'); ?></span>
		</div>
			<?php
		}
		?>
		
		<div class="vrcvordudata-cnt">
			<div class="vrcvordudata">
				<p><span class="vrcvordudatatitle"><?php echo JText::_('VRPERSDETS'); ?>:</span> <?php echo nl2br($ord['custdata']); ?></p>		
			</div>
		
			<div class="vrcvordcarinfo">
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
				<p><span class="vrcvordcarinfotitle"><?php echo JText::_('VRCARRENTED'); ?>:</span> <?php echo $carinfo['name']; ?></p>
				<p><div><span class="vrcvordcarinfotitle"><?php echo JText::_('VRDAL'); ?></span> <?php echo date($df.' '.$nowtf, $ord['ritiro']); ?></div> <div><span class="vrcvordcarinfotitle"><?php echo JText::_('VRAL'); ?></span> <?php echo date($df.' '.$nowtf, $ord['consegna']); ?></div></p>
				<?php if (!empty($ord['idplace'])) { ?>
				<p><span class="vrcvordcarinfotitle"><?php echo JText::_('VRRITIROCAR'); ?>:</span> <?php echo VikRentCar::getPlaceName($ord['idplace'], $vrc_tn); ?></p>
				<?php } ?>
				<?php if (!empty($ord['idreturnplace'])) { ?>
				<p><span class="vrcvordcarinfotitle"><?php echo JText::_('VRRETURNCARORD'); ?>:</span> <?php echo VikRentCar::getPlaceName($ord['idreturnplace'], $vrc_tn); ?></p>
				<?php } ?>
			</div>
		</div>
		
		<div class="vrcvordcosts">
			<div class="vrcord_typecost"><span class="vrcvordcoststitle"><?php echo $is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN') : VikRentCar::getPriceName($tar['idprice'], $vrc_tn); ?>:</span> <span class="vrc_priceorder"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo VikRentCar::numberFormat(($is_cust_cost ? $tar['cost'] : VikRentCar::sayCostPlusIva($tar['cost'], $tar['idprice'], $ord))); ?></span></span></div>
			<?php if (strlen($optbought)){ ?>
			<div class="vrcord_typecost"><span class="vrcvordcoststitle"><?php echo JText::_('VROPTS'); ?>:</span><div class="vrcvordcostsoptionals"><?php echo $optbought; ?></div></div>
			<?php } ?>
			<?php if ($locfeewith) { ?>
			<div class="vrcord_typecost"><span class="vrcvordcoststitle"><?php echo JText::_('VRLOCFEETOPAY'); ?>:</span> <span class="vrc_priceorder"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo VikRentCar::numberFormat($locfeewith); ?></span></span></div>
			<?php } ?>
			<?php if ($oohfeewith) { ?>
			<div class="vrcord_typecost"><span class="vrcvordcoststitle"><?php echo JText::sprintf('VRCOOHFEETOPAY', $ooh_time); ?></span> <span class="vrc_priceorder"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo VikRentCar::numberFormat($oohfeewith); ?></span></span></div>
			<?php } ?>
			<?php if ($usedcoupon == true) { ?>
			<div class="vrcord_typecost"><span class="vrcvordcoststitle"><?php echo JText::_('VRCCOUPON').' '.$expcoupon[2]; ?>:</span> - <span class="vrc_priceorder"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo VikRentCar::numberFormat($expcoupon[1]); ?></span></span></div>
			<?php } ?>
			<div class="vrcvordcoststot"><span class="vrcvordcoststitle"><?php echo JText::_('VRTOTAL'); ?>:</span> <span class="vrc_priceorder"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo VikRentCar::numberFormat($isdue); ?></span></span></div>
		</div>
		
		<?php

if (is_array($payment) && $ord['status'] == 'standby') {
	require_once(VRC_ADMIN_PATH . DS . "payments" . DS . $payment['file']);
	$return_url = JURI::root() . "index.php?option=com_vikrentcar&task=vieworder&sid=" . $ord['sid'] . "&ts=" . $ord['ts'];
	$error_url = JURI::root() . "index.php?option=com_vikrentcar&task=vieworder&sid=" . $ord['sid'] . "&ts=" . $ord['ts'];
	$notify_url = JURI::root() . "index.php?option=com_vikrentcar&task=notifypayment&sid=" . $ord['sid'] . "&ts=" . $ord['ts']."&tmpl=component";
	$transaction_name = VikRentCar::getPaymentName();
	$leave_deposit = 0;
	$percentdeposit = "";
	$array_order = array ();
	$array_order['order'] = $ord;
	$array_order['account_name'] = VikRentCar::getPaypalAcc();
	$array_order['transaction_currency'] = VikRentCar::getCurrencyCodePp();
	$array_order['vehicle_name'] = $carinfo['name'];
	$array_order['transaction_name'] = !empty ($transaction_name) ? $transaction_name : $carinfo['name'];
	$array_order['order_total'] = $isdue;
	$array_order['currency_symb'] = $currencysymb;
	$array_order['net_price'] = $imp;
	$array_order['tax'] = $tax;
	$array_order['return_url'] = $return_url;
	$array_order['error_url'] = $error_url;
	$array_order['notify_url'] = $notify_url;
	$array_order['total_to_pay'] = $isdue;
	$array_order['total_net_price'] = $imp;
	$array_order['total_tax'] = $tax;
	$totalchanged = false;
	if ($payment['charge'] > 0.00) {
		$totalchanged = true;
		if ($payment['ch_disc'] == 1) {
			//charge
			if ($payment['val_pcent'] == 1) {
				//fixed value
				$array_order['total_net_price'] += $payment['charge'];
				$array_order['total_tax'] += $payment['charge'];
				$array_order['total_to_pay'] += $payment['charge'];
				$newtotaltopay = $array_order['total_to_pay'];
			} else {
				//percent value
				$percent_net = $array_order['total_net_price'] * $payment['charge'] / 100;
				$percent_tax = $array_order['total_tax'] * $payment['charge'] / 100;
				$percent_to_pay = $array_order['total_to_pay'] * $payment['charge'] / 100;
				$array_order['total_net_price'] += $percent_net;
				$array_order['total_tax'] += $percent_tax;
				$array_order['total_to_pay'] += $percent_to_pay;
				$newtotaltopay = $array_order['total_to_pay'];
			}
		} else {
			//discount
			if ($payment['val_pcent'] == 1) {
				//fixed value
				$array_order['total_net_price'] -= $payment['charge'];
				$array_order['total_tax'] -= $payment['charge'];
				$array_order['total_to_pay'] -= $payment['charge'];
				$newtotaltopay = $array_order['total_to_pay'];
			} else {
				//percent value
				$percent_net = $array_order['total_net_price'] * $payment['charge'] / 100;
				$percent_tax = $array_order['total_tax'] * $payment['charge'] / 100;
				$percent_to_pay = $array_order['total_to_pay'] * $payment['charge'] / 100;
				$array_order['total_net_price'] -= $percent_net;
				$array_order['total_tax'] -= $percent_tax;
				$array_order['total_to_pay'] -= $percent_to_pay;
				$newtotaltopay = $array_order['total_to_pay'];
			}
		}
	}
	if (!VikRentCar::payTotal()) {
		$percentdeposit = (float)VikRentCar::getAccPerCent();
		if ($percentdeposit > 0) {
			$leave_deposit = 1;
			if (VikRentCar::getTypeDeposit() == "fixed") {
				$array_order['total_to_pay'] = $percentdeposit;
				$array_order['total_net_price'] = $percentdeposit;
				$array_order['total_tax'] = ($array_order['total_to_pay'] - $array_order['total_net_price']);
			} else {
				$array_order['total_to_pay'] = $array_order['total_to_pay'] * $percentdeposit / 100;
				$array_order['total_net_price'] = $array_order['total_net_price'] * $percentdeposit / 100;
				$array_order['total_tax'] = ($array_order['total_to_pay'] - $array_order['total_net_price']);
			}
		}
	}
	$array_order['leave_deposit'] = $leave_deposit;
	$array_order['percentdeposit'] = $percentdeposit;
	$array_order['payment_info'] = $payment;
	
	?>
	<div class="vrcvordpaybutton">
	<?php	
	if ($totalchanged) {
		$chdecimals = $payment['charge'] - (int)$payment['charge'];
		?>
		<p class="vrcpaymentchangetot">
		<?php echo $payment['name']; ?> 
		(<?php echo ($payment['ch_disc'] == 1 ? "+" : "-").($chdecimals > 0.00 ? VikRentCar::numberFormat($payment['charge']) : number_format($payment['charge'], 0))." ".($payment['val_pcent'] == 1 ? $currencysymb : "%"); ?>) 
		<span class="vrcorddiffpayment"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo VikRentCar::numberFormat($newtotaltopay); ?></span></span>
		</p>
		<?php
	}
	$obj = new vikRentCarPayment($array_order, json_decode($payment['params'], true));
	$obj->showPayment();
	?>
	</div>
	<?php
}
VikRentCar::printTrackingCode();
