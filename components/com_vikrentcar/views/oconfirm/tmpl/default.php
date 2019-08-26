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

$car=$this->car;
$price=$this->price;
$selopt=$this->selopt;
$days=$this->days;
//vikrentcar 1.6
$calcdays=$this->calcdays;
if ((int)$days != (int)$calcdays) {
	$origdays = $days;
	$days=$calcdays;
}
$coupon=$this->coupon;
//
$first=$this->first;
$second=$this->second;
$ftitle=$this->ftitle;
$place=$this->place;
$returnplace=$this->returnplace;
$payments=$this->payments;
$cfields=$this->cfields;
$customer_details=$this->customer_details;
$countries=$this->countries;
$vrc_tn=$this->vrc_tn;

$vrc_app = VikRentCar::getVrcApplication();

$document = JFactory::getDocument();
if (VikRentCar::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', VRC_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
}
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery-ui.min.css');
JHtml::_('script', VRC_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);

if (@is_array($cfields)) {
	foreach ($cfields as $cf) {
		if (!empty($cf['poplink'])) {
			$mbox_opts = '{
				"helpers": {
					"overlay": {
						"locked": false
					}
				},
				"width": "70%",
				"height": "75%",
				"autoScale": true,
				"transitionIn": "none",
				"transitionOut": "none",
				"padding": 0,
				"type": "iframe"
			}';
			$vrc_app->prepareModalBox('.vrcmodal', $mbox_opts);
			break;
		}
	}
	foreach ($cfields as $cf) {
		if ($cf['type'] == 'date') {
			JHTML::_('behavior.calendar');
			break;
		}
	}
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
if (VikRentCar::tokenForm()) {
	session_start();
	$vikt = uniqid(rand(17, 1717), true);
	$_SESSION['vikrtoken'] = $vikt;
	$tok = "<input type=\"hidden\" name=\"viktoken\" value=\"" . $vikt . "\"/>\n";
} else {
	$tok = "";
}

$pitemid = VikRequest::getInt('Itemid', '', 'request');
$pcategories = VikRequest::getString('categories', '', 'request');

$carats = VikRentCar::getCarCaratOriz($car['idcarat'], array(), $vrc_tn);
$imp = VikRentCar::sayCostMinusIva($price['cost'], $price['idprice']);
$totdue = VikRentCar::sayCostPlusIva($price['cost'], $price['idprice']);
$saywithout = $imp;
$saywith = $totdue;
if (is_array($selopt)) {
	foreach ($selopt as $selo) {
		$wop .= $selo['id'] . ":" . $selo['quan'] . ";";
		$realcost = intval($selo['perday']) == 1 ? ($selo['cost'] * $days * $selo['quan']) : ($selo['cost'] * $selo['quan']);
		$basequancost = intval($selo['perday']) == 1 ? ($selo['cost'] * $days) : $selo['cost'];
		if (!empty($selo['maxprice']) && $selo['maxprice'] > 0 && $basequancost > $selo['maxprice']) {
			$realcost = $selo['maxprice'];
			if (intval($selo['hmany']) == 1 && intval($selo['quan']) > 1) {
				$realcost = $selo['maxprice'] * $selo['quan'];
			}
		}
		$imp += VikRentCar::sayOptionalsMinusIva($realcost, $selo['idiva']);
		$totdue += VikRentCar::sayOptionalsPlusIva($realcost, $selo['idiva']);
	}
} else {
	$wop = "";
}
?>

<div class="vrcstepsbarcont">
	<ol class="vrc-stepbar" data-vrcsteps="4">
		<li class="vrc-step vrc-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=vikrentcar&pickup='.$first.'&return='.$second.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo JText::_('VRSTEPDATES'); ?></a></li>
		<li class="vrc-step vrc-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&task=search&place='.$place.'&pickupdate='.urlencode(date($df, $first)).'&pickuph='.date('H', $first).'&pickupm='.date('i', $first).'&releasedate='.urlencode(date($df, $second)).'&releaseh='.date('H', $second).'&releasem='.date('i', $second).'&returnplace='.$returnplace.(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>"><?php echo JText::_('VRSTEPCARSELECTION'); ?></a></li>
		<li class="vbp-step vrc-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&task=showprc&caropt='.$car['id'].'&pickup='.$first.'&release='.$second.'&place='.$place.'&returnplace='.$returnplace.'&days='.$days.(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>"><?php echo JText::_('VRSTEPOPTIONS2'); ?></a></li>
		<li class="vbp-step vrc-step-current"><span><?php echo JText::_('VRSTEPCONFIRM'); ?></span></li>
	</ol>
</div>

<h2 class="vrc-rental-summary-title"><?php echo JText::_('VRRIEPILOGOORD'); ?></h2>

<?php
// itinerary
$pickloc = VikRentCar::getPlaceInfo($place, $vrc_tn);
$droploc = VikRentCar::getPlaceInfo($returnplace, $vrc_tn);
?>

<div class="vrcinfocarcontainer">
	<div class="vrcrentforlocs">
		<div class="vrcrentalfor">
		<?php
		if (array_key_exists('hours', $price)) {
			?>
			<h3 class="vrcrentalforone"><?php echo JText::_('VRRENTAL'); ?> <?php echo $car['name']; ?> </h3>
			<p><?php echo JText::_('VRFOR'); ?> <?php echo (intval($price['hours']) == 1 ? "1 ".JText::_('VRCHOUR') : $price['hours']." ".JText::_('VRCHOURS')); ?></p>
			<?php
		} else {
			?>
			<h3 class="vrcrentalforone"><?php echo JText::_('VRRENTAL'); ?> <?php echo $car['name']; ?> </h3>
			<p><?php echo JText::_('VRFOR'); ?> <?php echo (intval($days)==1 ? "1 ".JText::_('VRDAY') : $days." ".JText::_('VRDAYS')); ?></p>
			<?php
		}
		?>
		</div>

		<div class="vrc-itinerary-confirmation">
			<div class="vrc-itinerary-pickup">
				<h4><?php echo JText::_('VRPICKUP'); ?></h4>
			<?php
			if (count($pickloc)) {
				?>
				<div class="vrc-itinerary-pickup-location">
					<i class="fa fa-location-arrow"></i>
					<div class="vrc-itinerary-pickup-locdet">
						<span class="vrc-itinerary-pickup-locname"><?php echo $pickloc['name']; ?></span>
						<span class="vrc-itinerary-pickup-locaddr"><?php echo $pickloc['address']; ?></span>
					</div>
				</div>
				<?php
			}
			?>
				<div class="vrc-itinerary-pickup-date">
					<i class="fa fa-calendar"></i>
					<span class="vrc-itinerary-pickup-date-day"><?php echo date($df, $first); ?></span>
					<span class="vrc-itinerary-pickup-date-time"><?php echo date($nowtf, $first); ?></span>
				</div>
			</div>
			<div class="vrc-itinerary-dropoff">
				<h4><?php echo JText::_('VRRETURN'); ?></h4>
			<?php
			if (count($droploc)) {
				?>
				<div class="vrc-itinerary-dropoff-location">
					<i class="fa fa-location-arrow"></i>
					<div class="vrc-itinerary-dropfff-locdet">
						<span class="vrc-itinerary-dropoff-locname"><?php echo $droploc['name']; ?></span>
						<span class="vrc-itinerary-dropoff-locaddr"><?php echo $droploc['address']; ?></span>
					</div>
				</div>
				<?php
			}
			?>
				<div class="vrc-itinerary-dropoff-date">
					<i class="fa fa-calendar"></i>
					<span class="vrc-itinerary-dropoff-date-day"><?php echo !array_key_exists('hours', $price) ? date($df, $second) : ''; ?></span>
					<span class="vrc-itinerary-dropoff-date-time"><?php echo date($nowtf, $second); ?></span>
				</div>
			</div>
		</div>
		
	</div>

	<?php
	if (!empty($car['img'])) {
	?>
	<div class="vrc-summary-car-img">
		<img src="<?php echo VRC_ADMIN_URI; ?>resources/<?php echo $car['img']; ?>"/>
	</div>
	<?php
	}
	?>
</div>

<table class="vrctableorder">
	<tr class="vrctableorderfrow"><td>&nbsp;</td>
	<td align="center"><?php echo (array_key_exists('hours', $price) ? JText::_('VRCHOURS') : JText::_('ORDDD')); ?></td>
	
	
	<td align="center"><?php echo JText::_('ORDWITHTAX'); ?></td></tr>
	<tr class="vrctableorder-car-row">
	<td align="left"><?php echo $car['name']; ?><br/><?php echo VikRentCar::getPriceName($price['idprice'], $vrc_tn).(!empty($price['attrdata']) ? "<br/>".VikRentCar::getPriceAttr($price['idprice'], $vrc_tn).": ".$price['attrdata'] : ""); ?></td>
	<td align="center"><?php echo (array_key_exists('hours', $price) ? $price['hours'] : $days); ?></td>
	
	
	<td align="center"><span class="vrc_price"><?php echo VikRentCar::numberFormat($saywith); ?></span> <span class="vrc_currency"><?php echo $currencysymb; ?></span></td></tr>
		<?php

$sf = 2;
if (is_array($selopt)) {
	foreach ($selopt as $aop) {
		if (intval($aop['perday']) == 1) {
			$thisoptcost = ($aop['cost'] * $aop['quan']) * $days;
		} else {
			$thisoptcost = $aop['cost'] * $aop['quan'];
		}
		$basequancost = intval($aop['perday']) == 1 ? ($aop['cost'] * $days) : $aop['cost'];
		if (!empty($aop['maxprice']) && $aop['maxprice'] > 0 && $basequancost > $aop['maxprice']) {
			$thisoptcost = $aop['maxprice'];
			if (intval($aop['hmany']) == 1 && intval($aop['quan']) > 1) {
				$thisoptcost = $aop['maxprice'] * $aop['quan'];
			}
		}
		$optwithout = (intval($aop['perday']) == 1 ? VikRentCar::sayOptionalsMinusIva($thisoptcost, $aop['idiva']) : VikRentCar::sayOptionalsMinusIva($thisoptcost, $aop['idiva']));
		$optwith = (intval($aop['perday']) == 1 ? VikRentCar::sayOptionalsPlusIva($thisoptcost, $aop['idiva']) : VikRentCar::sayOptionalsPlusIva($thisoptcost, $aop['idiva']));
		$opttax = VikRentCar::numberFormat($optwith - $optwithout);
		?>
			<tr<?php echo (($sf % 2) == 0 ? " class=\"vrcordrowtwo\"" : " class=\"vrcordrowone\""); ?>><td><?php echo $aop['name'].($aop['quan'] > 1 ? " <small>(x ".$aop['quan'].")</small>" : ""); ?></td>
			<td align="center"><?php echo (array_key_exists('hours', $price) ? $price['hours'] : $days); ?></td>
			
			
			<td align="center"><span class="vrc_price"><?php echo VikRentCar::numberFormat($optwith); ?></span> <span class="vrc_currency"><?php echo $currencysymb; ?></span></td></tr>
		<?php

		$sf++;
	}
}
$days = intval($days);
if (!empty($place) && !empty($returnplace)) {
	$locfee = VikRentCar::getLocFee($place, $returnplace);
	if ($locfee) {
		//VikRentCar 1.7 - Location fees overrides
		if (strlen($locfee['losoverride']) > 0) {
			$arrvaloverrides = array();
			$valovrparts = explode('_', $locfee['losoverride']);
			foreach ($valovrparts as $valovr) {
				if (!empty($valovr)) {
					$ovrinfo = explode(':', $valovr);
					$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
				}
			}
			if (array_key_exists($days, $arrvaloverrides)) {
				$locfee['cost'] = $arrvaloverrides[$days];
			}
		}
		//end VikRentCar 1.7 - Location fees overrides
		$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $days) : $locfee['cost'];
		$locfeewithout = VikRentCar::sayLocFeeMinusIva($locfeecost, $locfee['idiva']);
		$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva']);
		$locfeetax = VikRentCar::numberFormat($locfeewith - $locfeewithout);
		$imp += $locfeewithout;
		$totdue += $locfeewith;
		?>
		
	<tr<?php echo (($sf % 2) == 0 ? " class=\"vrcordrowtwo\"" : " class=\"vrcordrowone\"");?>>
		<td><?php echo JText::_('VRLOCFEETOPAY'); ?></td>
		<td align="center"><?php echo (array_key_exists('hours', $price) ? $price['hours'] : $days); ?></td>
		<td align="center"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo VikRentCar::numberFormat($locfeewithout); ?></span></td>
		
		<td align="center"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo VikRentCar::numberFormat($locfeewith); ?></span></td>
	</tr>
		
		<?php
		$sf++;
	}
}
//VRC 1.9 - Out of Hours Fees
$oohfee = VikRentCar::getOutOfHoursFees($place, $returnplace, $first, $second, $car);
if (count($oohfee) > 0) {
	$oohfeecost = $oohfee['cost'];
	$oohfeewithout = VikRentCar::sayOohFeeMinusIva($oohfeecost, $oohfee['idiva']);
	$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfeecost, $oohfee['idiva']);
	$oohfeetax = VikRentCar::numberFormat($oohfeewith - $oohfeewithout);
	$imp += $oohfeewithout;
	$totdue += $oohfeewith;
	$ooh_time = $oohfee['pickup'] == 1 ? $oohfee['pickup_ooh'] : '';
	$ooh_time .= $oohfee['dropoff'] == 1 && $oohfee['dropoff_ooh'] != $oohfee['pickup_ooh'] ? (!empty($ooh_time) ? ', ' : '').$oohfee['dropoff_ooh'] : '';
	?>
	<tr<?php echo (($sf % 2) == 0 ? " class=\"vrcordrowtwo\"" : " class=\"vrcordrowone\"");?>>
		<td><?php echo JText::sprintf('VRCOOHFEETOPAY', $ooh_time); ?></td>
		<td align="center"><?php echo (array_key_exists('hours', $price) ? $price['hours'] : $days); ?></td>
		<td align="center"><span class="vrc_price"><?php echo VikRentCar::numberFormat($oohfeewithout); ?></span> <span class="vrc_currency"><?php echo $currencysymb; ?></span></td>
		<td align="center"><span class="vrc_price"><?php echo $oohfeetax; ?></span> <span class="vrc_currency"><?php echo $currencysymb; ?></span></td>
		<td align="center"><span class="vrc_price"><?php echo VikRentCar::numberFormat($oohfeewith); ?></span> <span class="vrc_currency"><?php echo $currencysymb; ?></span></td>
	</tr>
	<?php
	$sf++;
}
//

//store Order Total in session for modules
$session = JFactory::getSession();
$session->set('vikrentcar_ordertotal', $totdue);
//

//vikrentcar 1.6
$origtotdue = $totdue;
$usedcoupon = false;
if (is_array($coupon)) {
	//check min tot ord
	$coupontotok = true;
	if (strlen($coupon['mintotord']) > 0) {
		if ($totdue < $coupon['mintotord']) {
			$coupontotok = false;
		}
	}
	if ($coupontotok == true) {
		$usedcoupon = true;
		if ($coupon['percentot'] == 1) {
			//percent value
			$minuscoupon = 100 - $coupon['value'];
			$couponsave = $totdue * $coupon['value'] / 100;
			$totdue = $totdue * $minuscoupon / 100;
		} else {
			//total value
			$couponsave = $coupon['value'];
			$totdue = $totdue - $coupon['value'];
		}
	} else {
		VikError::raiseWarning('', JText::_('VRCCOUPONINVMINTOTORD'));
	}
}
//

?>
	<tr height="20px"><td colspan="5" height="20px">&nbsp;</td></tr>
	<tr class="vrcordrowtotal"><td><?php echo JText::_('VRTOTAL'); ?></td>
	<td align="center">&nbsp;</td>
	
	
	<td align="center" class="vrctotalord"><span class="vrc_price"><?php echo VikRentCar::numberFormat($origtotdue); ?></span> <span class="vrc_currency"><?php echo $currencysymb; ?></span></td></tr>
	<?php
	if ($usedcoupon == true) {
		?>
		<tr class="vrcordrowtotal"><td><?php echo JText::_('VRCCOUPON'); ?> <?php echo $coupon['code']; ?></td><td align="center">&nbsp;</td><td align="center">&nbsp;</td><td align="center">&nbsp;</td><td align="center" class="vrctotalord"><span class="vrc_currency">- <?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo VikRentCar::numberFormat($couponsave); ?></span></td></tr>
		<tr class="vrcordrowtotal"><td><?php echo JText::_('VRCNEWTOTAL'); ?></td><td align="center">&nbsp;</td><td align="center">&nbsp;</td><td align="center">&nbsp;</td><td align="center" class="vrctotalord"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo VikRentCar::numberFormat($totdue); ?></span></td></tr>
		<?php
	}
	?>
</table>


	<div class="vrc-oconfirm-middlep">

	<?php
	//vikrentcar 1.6
	if (VikRentCar::couponsEnabled() && !is_array($coupon)) {
		?>
		<div class="vrc-coupon-outer">
			<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" method="post">
				<div class="vrcentercoupon">
					<span class="vrchaveacoupon"><?php echo JText::_('VRCHAVEACOUPON'); ?></span>
					<input type="text" name="couponcode" value="" size="20" class="vrcinputcoupon"/>
					<input type="submit" class="vrcsubmitcoupon" name="applyacoupon" value="<?php echo JText::_('VRCSUBMITCOUPON'); ?>"/>
				</div>
				<input type="hidden" name="priceid" value="<?php echo $price['idprice']; ?>"/>
				<input type="hidden" name="place" value="<?php echo $place; ?>"/>
				<input type="hidden" name="returnplace" value="<?php echo $returnplace; ?>"/>
				<input type="hidden" name="carid" value="<?php echo $car['id']; ?>"/>
		  		<input type="hidden" name="days" value="<?php echo $days; ?>"/>
		  		<input type="hidden" name="pickup" value="<?php echo $first; ?>"/>
		  		<input type="hidden" name="release" value="<?php echo $second; ?>"/>
		  		<?php
		  		if (is_array($selopt)) {
					foreach ($selopt as $aop) {
						echo '<input type="hidden" name="optid'.$aop['id'].'" value="'.$aop['quan'].'"/>'."\n";
					}
		  		}
		  		if (!empty($pitemid)) {
				?>
				<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
				<?php
				}
		  		?>
		  		<input type="hidden" name="task" value="oconfirm"/>
			</form>
		</div>
		<?php
	}
	//Customers PIN
	if (VikRentCar::customersPinEnabled() && !VikRentCar::userIsLogged() && !(count($customer_details) > 0)) {
		?>
		<div class="vrc-enterpin-block">
			<div class="vrc-enterpin-top">
				<span><span><?php echo JText::_('VRRETURNINGCUSTOMER'); ?></span><?php echo JText::_('VRENTERPINCODE'); ?></span>
				<input type="text" id="vrc-pincode-inp" value="" size="6"/>
				<button type="button" class="btn vrc-pincode-sbmt"><?php echo JText::_('VRAPPLYPINCODE'); ?></button>
			</div>
			<div class="vrc-enterpin-response"></div>
		</div>
		<script>
		jQuery(document).ready(function() {
			jQuery(".vrc-pincode-sbmt").click(function() {
				var pin_code = jQuery("#vrc-pincode-inp").val();
				jQuery(this).prop('disabled', true);
				jQuery(".vrc-enterpin-response").hide();
				jQuery.ajax({
					type: "POST",
					url: "<?php echo JRoute::_('index.php?option=com_vikrentcar&task=validatepin&tmpl=component'.(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>",
					data: { pin: pin_code }
				}).done(function(res) {
					var pinobj = jQuery.parseJSON(res);
					if (pinobj.hasOwnProperty('success')) {
						jQuery(".vrc-enterpin-top").hide();
						jQuery(".vrc-enterpin-response").removeClass("vrc-enterpin-error").addClass("vrc-enterpin-success").html("<span class=\"vrc-enterpin-welcome\"><?php echo addslashes(JText::_('VRWELCOMEBACK')); ?></span><span class=\"vrc-enterpin-customer\">"+pinobj.first_name+" "+pinobj.last_name+"</span>").fadeIn();
						jQuery.each(pinobj.cfields, function(k, v) {
							if (jQuery("#vrcf-inp"+k).length) {
								jQuery("#vrcf-inp"+k).val(v);
							}						
						});
						var user_country = pinobj.country;
						if (jQuery(".vrcf-countryinp").length && user_country.length) {
							jQuery(".vrcf-countryinp option").each(function(i){
								var opt_country = jQuery(this).val();
								if (opt_country.substring(0, 3) == user_country) {
									jQuery(this).prop("selected", true);
									return false;
								}
							});
						}
					} else {
						jQuery(".vrc-enterpin-response").addClass("vrc-enterpin-error").html("<p><?php echo addslashes(JText::_('VRINVALIDPINCODE')); ?></p>").fadeIn();
						jQuery(".vrc-pincode-sbmt").prop('disabled', false);
					}
				}).fail(function(){
					alert('Error validating the PIN. Request failed.');
					jQuery(".vrc-pincode-sbmt").prop('disabled', false);
				});
			});
		});
		</script>
		<?php
	}
	?>

	</div>
		
		<script type="text/javascript">
		function vrcValidateEmail(email) { 
		    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		    return re.test(email);
		}
  		function checkvrcFields(){
  			var vrvar = document.vrc;
			<?php

if (@is_array($cfields)) {
	foreach ($cfields as $cf) {
		if (intval($cf['required']) == 1) {
			if ($cf['type'] == "text" || $cf['type'] == "textarea" || $cf['type'] == "date" || $cf['type'] == "country") {
			?>
			if (!vrvar.vrcf<?php echo $cf['id']; ?>.value.match(/\S/)) {
				document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='#ff0000';
				return false;
			} else {
				document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='';
			}
			<?php
				if ($cf['isemail'] == 1) {
				?>
			if (!vrcValidateEmail(vrvar.vrcf<?php echo $cf['id']; ?>.value)) {
				document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='#ff0000';
				return false;
			} else {
				document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='';
			}
				<?php
				}
			} elseif ($cf['type'] == "select") {
			?>
			if (!vrvar.vrcf<?php echo $cf['id']; ?>.value.match(/\S/)) {
				document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='#ff0000';
				return false;
			} else {
				document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='';
			}
			<?php

			} elseif ($cf['type'] == "checkbox") {
				//checkbox
			?>
			if (vrvar.vrcf<?php echo $cf['id']; ?>.checked) {
				document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='';
			} else {
				document.getElementById('vrcf<?php echo $cf['id']; ?>').style.color='#ff0000';
				return false;
			}
			<?php

			}
		}
	}
}
?>
  			return true;
  		}
		</script>
		
		<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" name="vrc" method="post" onsubmit="javascript: return checkvrcFields();">
		<?php

if (@is_array($cfields)) {
	?>
		<div class="vrccustomfields">
	<?php
	$currentUser = JFactory::getUser();
	$useremail = !empty($currentUser->email) ? $currentUser->email : "";
	$useremail = array_key_exists('email', $customer_details) ? $customer_details['email'] : $useremail;
	$previousdata = VikRentCar::loadPreviousUserData($currentUser->id);
	$nominatives = array();
	if (count($customer_details) > 0) {
		$nominatives[] = $customer_details['first_name'];
		$nominatives[] = $customer_details['last_name'];
	}
	foreach ($cfields as $cf) {
		if (intval($cf['required']) == 1) {
			$isreq = "<span class=\"vrcrequired\"><sup>*</sup></span> ";
		} else {
			$isreq = "";
		}
		if (!empty($cf['poplink'])) {
			$fname = "<a href=\"" . $cf['poplink'] . "\" id=\"vrcf" . $cf['id'] . "\" rel=\"{handler: 'iframe', size: {x: 750, y: 600}}\" target=\"_blank\" class=\"vrcmodal\">" . JText::_($cf['name']) . "</a>";
		} else {
			$fname = "<label id=\"vrcf" . $cf['id'] . "\" for=\"vrcf-inp" . $cf['id'] . "\">" . JText::_($cf['name']) . "</label>";
		}
		if ($cf['type'] == "text") {
			$textmailval = intval($cf['isemail']) == 1 ? $useremail : "";
			$textmailval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : $textmailval;
			if ($cf['isphone'] == 1) {
				if (array_key_exists('phone', $customer_details)) {
					$textmailval = $customer_details['phone'];
				}
			} elseif ($cf['isnominative'] == 1) {
				if (count($nominatives) > 0) {
					$textmailval = array_shift($nominatives);
				}
			} elseif (array_key_exists('cfields', $customer_details)) {
				if (array_key_exists($cf['id'], $customer_details['cfields'])) {
					$textmailval = $customer_details['cfields'][$cf['id']];
				}
			}
			?>
			<div class="vrcdivcustomfield">
				<div class="vrc-customfield-label">
					<?php echo $isreq; ?><?php echo $fname; ?>
				</div>
				<div class="vrc-customfield-input">
					<input type="text" name="vrcf<?php echo $cf['id']; ?>" id="vrcf-inp<?php echo $cf['id']; ?>" value="<?php echo $textmailval; ?>" size="40" class="vrcinput"/>
				</div>
			</div>
		<?php
		} elseif ($cf['type'] == "textarea") {
			$defaultval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : '';
			if (array_key_exists($cf['id'], $customer_details['cfields'])) {
				$defaultval = $customer_details['cfields'][$cf['id']];
			}
		?>
			<div class="vrcdivcustomfield">
				<div class="vrc-customfield-label">
					<?php echo $isreq; ?><?php echo $fname; ?>
				</div>
				<div class="vrc-customfield-input">
					<textarea name="vrcf<?php echo $cf['id']; ?>" id="vrcf-inp<?php echo $cf['id']; ?>" rows="5" cols="30" class="vrctextarea"><?php echo $defaultval; ?></textarea>
				</div>
			</div>
		<?php
		} elseif ($cf['type'] == "date") {
			$defaultval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : '';
		?>
			<div class="vrcdivcustomfield">
				<div class="vrc-customfield-label">
					<?php echo $isreq; ?><?php echo $fname; ?>
				</div>
				<div class="vrc-customfield-input">
					<?php echo $vrc_app->getCalendar('', 'vrcf'.$cf['id'], 'vrcf-inp'.$cf['id'], VikRentCar::getDateFormat(), array('class'=>'vrcinput', 'size'=>'10', 'value'=>$defaultval, 'maxlength'=>'19')); ?>
				</div>
			</div>
			<?php
			if (!empty($defaultval)) {
			?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#vrcf<?php echo $cf['id']; ?>date').val('<?php echo addslashes($defaultval); ?>');
			});
			</script>
			<?php
			}
			?>
		<?php
		} elseif ($cf['type'] == "country" && is_array($countries)) {
			$defaultval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : '';
			if (array_key_exists('country', $customer_details)) {
				$defaultval = !empty($customer_details['country']) ? substr($customer_details['country'], 0, 3) : '';
			}
			$countries_sel = '<select name="vrcf'.$cf['id'].'" class="vrcf-countryinp"><option value=""></option>'."\n";
			foreach ($countries as $country) {
				$countries_sel .= '<option value="'.$country['country_3_code'].'::'.$country['country_name'].'"'.($defaultval == $country['country_3_code'] ? ' selected="selected"' : '').'>'.$country['country_name'].'</option>'."\n";
			}
			$countries_sel .= '</select>';
		?>
			<div class="vrcdivcustomfield">
				<div class="vrc-customfield-label">
					<?php echo $isreq; ?><?php echo $fname; ?>
				</div>
				<div class="vrc-customfield-input">
					<?php echo $countries_sel; ?>
				</div>
			</div>
		<?php
		} elseif ($cf['type'] == "select") {
			$defaultval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : '';
			$answ = explode(";;__;;", $cf['choose']);
			$wcfsel = "<select name=\"vrcf" . $cf['id'] . "\">\n";
			foreach ($answ as $aw) {
				if (!empty($aw)) {
					$wcfsel .= "<option value=\"" . JText::_($aw) . "\"".($defaultval == JText::_($aw) ? ' selected="selected"' : '').">" . JText::_($aw) . "</option>\n";
				}
			}
			$wcfsel .= "</select>\n";
		?>
			<div class="vrcdivcustomfield">
				<div class="vrc-customfield-label">
					<?php echo $isreq; ?><?php echo $fname; ?>
				</div>
				<div class="vrc-customfield-input">
					<?php echo $wcfsel; ?>
				</div>
			</div>
		<?php

		} elseif ($cf['type'] == "separator") {
			$cfsepclass = strlen(JText::_($cf['name'])) > 30 ? "vrcseparatorcflong" : "vrcseparatorcf";
		?>
			<div class="vrcdivcustomfield vrccustomfldinfo">
				<div class="<?php echo $cfsepclass; ?>"><?php echo JText::_($cf['name']); ?></div>
			</div>
		<?php
		} else {
		?>
			<div class="vrcdivcustomfield vrc-oconfirm-cfield-entry-checkbox">
				<div class="vrc-customfield-label">
					<?php echo $isreq; ?><?php echo $fname; ?>
				</div>
				<div class="vrc-customfield-input">
					<input type="checkbox" name="vrcf<?php echo $cf['id']; ?>" id="vrcf-inp<?php echo $cf['id']; ?>" value="<?php echo JText::_('VRYES'); ?>"/>
				</div>
			</div>
		<?php

		}
	}
	?>
		</div>
		<?php

}
?>
		<input type="hidden" name="days" value="<?php echo $days; ?>"/>
		<?php
		//vikrentcar 1.6
		if ($origdays) {
			?>
			<input type="hidden" name="origdays" value="<?php echo $origdays; ?>"/>
			<?php
		}
		//
		?>
		<input type="hidden" name="pickup" value="<?php echo $first; ?>"/>
		<input type="hidden" name="release" value="<?php echo $second; ?>"/>
		<input type="hidden" name="car" value="<?php echo $car['id']; ?>"/>
		<input type="hidden" name="prtar" value="<?php echo $price['id']; ?>"/>
		<input type="hidden" name="priceid" value="<?php echo $price['idprice']; ?>"/>
		<input type="hidden" name="optionals" value="<?php echo $wop; ?>"/>
		<input type="hidden" name="totdue" value="<?php echo $totdue; ?>"/>
		<input type="hidden" name="place" value="<?php echo $place; ?>"/>
		<input type="hidden" name="returnplace" value="<?php echo $returnplace; ?>"/>
		<?php
		if (array_key_exists('hours', $price)) {
			?>
		<input type="hidden" name="hourly" value="<?php echo $price['hours']; ?>"/>	
			<?php
		}
		if ($usedcoupon == true && is_array($coupon)) {
			?>
		<input type="hidden" name="couponcode" value="<?php echo $coupon['code']; ?>"/>
			<?php
		}
		?>
		<?php echo $tok; ?>
		<input type="hidden" name="task" value="saveorder"/>
		<?php

if (@is_array($payments)) {
	?>
	<div class="vrc-oconfirm-paym-block">
		<h4 class="vrc-medium-header"><?php echo JText::_('VRCHOOSEPAYMENT'); ?></h4>
		<ul class="vrc-noliststyletype">
	<?php
	foreach ($payments as $pk => $pay) {
		$rcheck = $pk == 0 ? " checked=\"checked\"" : "";
		$saypcharge = "";
		if ($pay['charge'] > 0.00) {
			$decimals = $pay['charge'] - (int)$pay['charge'];
			if ($decimals > 0.00) {
				$okchargedisc = VikRentCar::numberFormat($pay['charge']);
			} else {
				$okchargedisc = number_format($pay['charge'], 0);
			}
			$saypcharge .= " (".($pay['ch_disc'] == 1 ? "+" : "-");
			$saypcharge .= "<span class=\"".($pay['val_pcent'] == 1 ? 'vrc_price' : '')."\">" . $okchargedisc . "</span> <span class=\"".($pay['val_pcent'] == 1 ? 'vrc_currency' : '')."\">" . ($pay['val_pcent'] == 1 ? $currencysymb : "%") . "</span>";
			$saypcharge .= ")";
		}
		?>
			<li class="vrc-gpay-licont<?php echo $pk == 0 ? ' vrc-gpay-licont-active' : ''; ?>">
				<input type="radio" name="gpayid" value="<?php echo $pay['id']; ?>" id="gpay<?php echo $pay['id']; ?>"<?php echo $rcheck; ?> onclick="vrcToggleActiveGpay(this);"/>
				<label for="gpay<?php echo $pay['id']; ?>"><?php echo $pay['name'].$saypcharge; ?></label>
			</li>
		<?php
	}
	?>
		</ul>
	</div>
	<script type="text/javascript">
	function vrcToggleActiveGpay(elem) {
		jQuery('.vrc-gpay-licont').removeClass('vrc-gpay-licont-active');
		jQuery(elem).parent('li').addClass('vrc-gpay-licont-active');
	}
	</script>
	<?php
}
?>
		<div class="vrc-save-order-block">
			<input type="submit" name="saveorder" value="<?php echo JText::_('VRORDCONFIRM'); ?>" class="booknow"/>
		</div>
		<?php
	$ptmpl = VikRequest::getString('tmpl', '', 'request');
	if (!empty($pitemid)) {
		?>
		<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
		<?php
	}
	if ($ptmpl == 'component') {
		?>
		<input type="hidden" name="tmpl" value="component"/>
		<?php
	}
	?>
		</form>
		<?php
		//Build back link without using the JavaScript history
		$backto = 'index.php?option=com_vikrentcar&task=showprc&caropt='.$car['id'].'&days='.$days.'&pickup='.$first.'&release='.$second.'&place='.$place.'&returnplace='.$returnplace.(!empty($pitemid) ? '&Itemid='.$pitemid : '');
		?>
		<div class="goback">
			<a href="<?php echo JRoute::_($backto); ?>"><?php echo JText::_('VRBACK'); ?></a>
		</div>
<?php
VikRentCar::printTrackingCode();
