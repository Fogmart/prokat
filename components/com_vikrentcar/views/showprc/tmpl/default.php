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

$tars=$this->tars;
$car=$this->car;
$pickup=$this->pickup;
$release=$this->release;
$place=$this->place;
$vrc_tn=$this->vrc_tn;

$vat_included = VikRentCar::ivaInclusa();
$tax_summary = !$vat_included && VikRentCar::showTaxOnSummaryOnly() ? true : false;

$nowdf = VikRentCar::getDateFormat();
$nowtf = VikRentCar::getTimeFormat();
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}

$pitemid = VikRequest::getInt('Itemid', '', 'request');
$ptmpl = VikRequest::getString('tmpl', '', 'request');

//load jQuery lib and navigation
$document = JFactory::getDocument();
if (VikRentCar::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', VRC_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
}
$document->addStyleSheet(VRC_SITE_URI.'resources/jquery.fancybox.css');
JHtml::_('script', VRC_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
$navdecl = '
jQuery.noConflict();
jQuery(document).ready(function() {
	jQuery(".vrcmodal").fancybox({
		"helpers": {
			"overlay": {
				"locked": false
			}
		},"padding": 0
	});
});';
$document->addScriptDeclaration($navdecl);
//

$preturnplace = VikRequest::getString('returnplace', '', 'request');
$pcategories = VikRequest::getString('categories', '', 'request');
$carats = VikRentCar::getCarCaratOriz($car['idcarat'], array(), $vrc_tn);
$currencysymb = VikRentCar::getCurrencySymb();
if (!empty($car['idopt'])) {
	$optionals = VikRentCar::getCarOptionals($car['idopt'], $vrc_tn);
}
$discl = VikRentCar::getDisclaimer($vrc_tn);

/**
 * VRC 1.12 - The first key of the tariffs could be unset for the rate plan closing dates.
 * Store what's the first index of the array.
 */
reset($tars);
$tindex = key($tars);
?>

<div class="vrcstepsbarcont">
	<ol class="vrc-stepbar" data-vrcsteps="4">
		<li class="vrc-step vrc-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&view=vikrentcar&pickup='.$pickup.'&return='.$release.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo JText::_('VRSTEPDATES'); ?></a></li>
		<li class="vrc-step vrc-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikrentcar&task=search&place='.$place.'&pickupdate='.urlencode(date($df, $pickup)).'&pickuph='.date('H', $pickup).'&pickupm='.date('i', $pickup).'&releasedate='.urlencode(date($df, $release)).'&releaseh='.date('H', $release).'&releasem='.date('i', $release).'&returnplace='.$preturnplace.(!empty($pcategories) && $pcategories != 'all' ? '&categories='.$pcategories : '').(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>"><?php echo JText::_('VRSTEPCARSELECTION'); ?></a></li>
		<li class="vrc-step vrc-step-current"><span><?php echo JText::_('VRSTEPOPTIONS2'); ?></span></li>
		<li class="vrc-step vrc-step-next"><span><?php echo JText::_('VRSTEPCONFIRM'); ?></span></li>
	</ol>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_vikrentcar'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" method="post">
	<div class="vrc-showprc-container">
		<div class="vrc-showprc-left">
		<?php
		if (array_key_exists('hours', $tars[$tindex])) {
			?>
			<h3 class="car_title"><span class="vrhword"><?php echo JText::_('VRRENTAL'); ?> <?php echo $car['name']; ?> <?php echo JText::_('VRFOR'); ?> <?php echo (intval($tars[$tindex]['hours']) == 1 ? "1 ".JText::_('VRCHOUR') : $tars[$tindex]['hours']." ".JText::_('VRCHOURS')); ?></span></h3>
			<?php
		} else {
			?>
			<h3 class="car_title"><span class="vrhword"><?php echo JText::_('VRRENTAL'); ?> <?php echo $car['name']; ?> <?php echo JText::_('VRFOR'); ?> <?php echo (intval($tars[$tindex]['days']) == 1 ? "1 ".JText::_('VRDAY') : $tars[$tindex]['days']." ".JText::_('VRDAYS')); ?></span></h3>
			<?php
		}
		?>
			<div class="vrc-cdetails-infocar">
				<div class="car_description_box">
					<?php echo $car['info']; ?>
				</div>
			</div>
			<?php if (!empty($carats)) { ?>
			<div class="vrc-showprc-car-carats">
				<?php echo $carats; ?>
			</div>
			<?php } ?>
		</div>
		<div class="vrc-showprc-right car_img_box">
			<img alt="<?php echo $car['name']; ?>" src="<?php echo VRC_ADMIN_URI; ?>resources/<?php echo $car['img']; ?>"/>
			<?php
			if (strlen($car['moreimgs']) > 0) {
				$moreimages = explode(';;', $car['moreimgs']);
				?>
				<div class="car_moreimages">
					<?php
					foreach ($moreimages as $mimg) {
						if (!empty($mimg)) {
							?>
							<a href="<?php echo VRC_ADMIN_URI; ?>resources/big_<?php echo $mimg; ?>" rel="vrcgroup<?php echo $car['id']; ?>" target="_blank" class="vrcmodal"><img src="<?php echo VRC_ADMIN_URI; ?>resources/thumb_<?php echo $mimg; ?>"/></a>
							<?php
						}
					}
					?>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	
	<div class="car_prices table-responsive">
		<span class="vrhword"><?php echo JText::_('VRPRICE'); ?>:</span>
		<table class="table">
		<?php
		$loopnum = 0;
		foreach ($tars as $k => $t) {
			$has_promotion = array_key_exists('promotion', $t) ? true : false;
			?>
			<tr><td><label for="pid<?php echo $t['idprice']; ?>"<?php echo $has_promotion === true ? ' class="vrc-label-promo-price"' : ''; ?>><?php echo VikRentCar::getPriceName($t['idprice'], $vrc_tn).":  <span class=\"vrc_price\">".($tax_summary ? VikRentCar::numberFormat($t['cost']) : VikRentCar::numberFormat(VikRentCar::sayCostPlusIva($t['cost'], $t['idprice'])))."</span> <span class=\"vrc_currency\">".$currencysymb."</span>".(strlen($t['attrdata']) ? "<br/>".VikRentCar::getPriceAttr($t['idprice'], $vrc_tn).": ".$t['attrdata'] : ""); ?></label></td><td><input type="radio" name="priceid" id="pid<?php echo $t['idprice']; ?>" value="<?php echo $t['idprice']; ?>"<?php echo ($loopnum == 0 ? " checked=\"checked\"" : ""); ?>/></td></tr>
			<?php
			$loopnum++;
		}
		?>
		</table>
	</div>
		
<?php
if (!empty($car['idopt']) && is_array($optionals)) {
?>
	<div class="car_options table-responsive">
		<span class="vrhword"><?php echo JText::_('VRACCOPZ'); ?>:</span>
		<table class="table">
		<?php
	foreach ($optionals as $k => $o) {
		$optcost = intval($o['perday']) == 1 ? ($o['cost'] * $tars[$tindex]['days']) : $o['cost'];
		if (!empty($o['maxprice']) && $o['maxprice'] > 0 && $optcost > $o['maxprice']) {
			$optcost = $o['maxprice'];
		}
		$optcost = $optcost * 1;
		//VRC 1.7 Rev.2
		if (!((int)$tars[$tindex]['days'] > (int)$o['forceifdays'])) {
			continue;
		}
		//
		//vikrentcar 1.6
		if (intval($o['forcesel']) == 1) {
			//VRC 1.7 Rev.2
			if ((int)$tars[$tindex]['days'] > (int)$o['forceifdays']) {
				$forcedquan = 1;
				$forceperday = false;
				if (strlen($o['forceval']) > 0) {
					$forceparts = explode("-", $o['forceval']);
					$forcedquan = intval($forceparts[0]);
					$forceperday = intval($forceparts[1]) == 1 ? true : false;
				}
				$setoptquan = $forceperday == true ? $forcedquan * $tars[$tindex]['days'] : $forcedquan;
				if (intval($o['hmany']) == 1) {
					$optquaninp = "<input type=\"hidden\" name=\"optid".$o['id']."\" value=\"".$setoptquan."\"/><span class=\"vrcoptionforcequant\"><small>x</small> ".$setoptquan."</span>";
				} else {
					$optquaninp = "<input type=\"hidden\" name=\"optid".$o['id']."\" value=\"".$setoptquan."\"/><span class=\"vrcoptionforcequant\"><small>x</small> ".$setoptquan."</span>";
				}
			} else {
				continue;
			}
			//
		} else {
			if (intval($o['hmany']) == 1) {
				$optquaninp = "<input type=\"number\" min=\"0\" step=\"any\" name=\"optid".$o['id']."\" value=\"0\" size=\"5\"/>";
			} else {
				$optquaninp = "<input type=\"checkbox\" name=\"optid".$o['id']."\" value=\"1\"/>";
			}
		}
		//
		?>
		<tr>
            <td class="vrc-tableopt-td-ckbx"><?php echo $optquaninp; ?></td>
            <td class="vrc-tableopt-td-img">
                <?php echo (!empty($o['img']) ? "<img src=\"".VRC_ADMIN_URI."resources/".$o['img']."\" align=\"middle\" />" : "") ?></td>
            <td class="vrc-tableopt-td-name"><?php echo $o['name']; ?></td>
            <td class="vrc-tableopt-td-price1 cbshow" style="visibility: hidden" >
                <span class="vrc_price2">
                    <?php echo ($tax_summary ? $optcost : VikRentCar::numberFormat(VikRentCar::sayOptionalsPlusIva($optcost, $o['idiva']))); ?>
                </span> <span class="vrc_currency"><?php echo $currencysymb; ?></span></td>

            <td class="vrc-tableopt-td-qty cbshow" style="visibility: hidden" >
                <span style="display: inline-flex;">
                    <span style="display: grid;padding-right: 5px;">
                        <i class="fa fa-angle-up" aria-hidden="true"></i>
                        <i class="fa fa-angle-down" aria-hidden="true"></i>
                    </span>
                    <input type="text" readonly style="width: 33px;text-align: center;padding: 0px;"
                           class="qty" value="1" name="optqty<?=$o['id']?>">
                    <input type="hidden" class="basecost" value="<?=($tax_summary ? $optcost : VikRentCar::sayOptionalsPlusIva($optcost, $o['idiva']))?>">
                </span>
            </td>
            <td class="vrc-tableopt-td-price">
                <span class="vrc_price2 totcost">
                    <?php echo ($tax_summary ? $optcost : VikRentCar::numberFormat(VikRentCar::sayOptionalsPlusIva($optcost, $o['idiva']))); ?>
                </span> <span class="vrc_currency"><?php echo $currencysymb; ?></span></td>

        </tr>
		<?php
		if (strlen(strip_tags(trim($o['descr'])))) {
		?>
		<tr class="vrc-tableopt-tr-descr"><td colspan="4"><div class="vrcoptionaldescr"><?php echo $o['descr']; ?></div></td></tr>
		<?php
		}
	}
?>
		</table>
	</div>
		<?php
}
?>
	<input type="hidden" name="place" value="<?php echo $place; ?>"/>
	<input type="hidden" name="returnplace" value="<?php echo $preturnplace; ?>"/>
	<input type="hidden" name="carid" value="<?php echo $car['id']; ?>"/>
	<input type="hidden" name="days" value="<?php echo $tars[$tindex]['days']; ?>"/>
	<input type="hidden" name="pickup" value="<?php echo $pickup; ?>"/>
	<input type="hidden" name="release" value="<?php echo $release; ?>"/>
	<input type="hidden" name="task" value="oconfirm"/>
  	<?php
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

	if (strlen($discl)) {
	?>
	<div class="car_disclaimer"><?php echo $discl; ?></div>
	<?php
	}
	
	//Build back link without using the JavaScript history
	$pfid = VikRequest::getInt('fid', '', 'request');
	if (!empty($pfid)) {
		$backto = 'index.php?option=com_vikrentcar&view=cardetails&carid='.$pfid.'&day='.$pickup.(!empty($pitemid) ? '&Itemid='.$pitemid : '');
	} else {
		$backto = 'index.php?option=com_vikrentcar&task=search&place='.$place.'&pickupdate='.urlencode(date($df, $pickup)).'&pickuph='.date('H', $pickup).'&pickupm='.date('i', $pickup).'&releasedate='.urlencode(date($df, $release)).'&releaseh='.date('H', $release).'&releasem='.date('i', $release).'&returnplace='.$preturnplace.(!empty($pcategories) && $pcategories != 'all' ? '&categories='.$pcategories : '').(!empty($pitemid) ? '&Itemid='.$pitemid : '');
	}
	//
	?>
		
	<div class="car_buttons_box">
		<input type="submit" name="goon" value="<?php echo JText::_('VRBOOKNOW'); ?>" class="btn booknow"/>
		<div class="goback">
			<a href="<?php echo JRoute::_($backto); ?>"><?php echo JText::_('VRBACK'); ?></a>
		</div>
	</div>
		
</form>

<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery(".fa-angle-up").click(addqty)
        jQuery(".fa-angle-down").click(remqty)
        jQuery("[type='checkbox']", ".vrc-tableopt-td-ckbx").click(updQtyVis)

    })
    function addqty() {
        var cnt = jQuery(".qty", jQuery(this).closest("td")).val()
        if (cnt == "") cnt = 0
        jQuery(".qty", jQuery(this).closest("td")).val(parseInt(cnt)+1)
        recalcSum( this )
    }
    function remqty() {
        var cnt = jQuery(".qty", jQuery(this).closest("td")).val()
        if (cnt == "") cnt = 0
        cnt = parseInt(cnt)-1
        if (cnt < 1) cnt = 1
        jQuery(".qty", jQuery(this).closest("td")).val(cnt)
        recalcSum( this )
    }
    function recalcSum( el ) {
        var basecost =  jQuery(".basecost", jQuery(el).closest("td")).val()
        var qty = jQuery(".qty", jQuery(el).closest("td")).val()
        var cost = parseFloat(basecost) * parseInt(qty)
        jQuery(".totcost", jQuery(el).closest("tr")).html(cost)
        jQuery("[type='checkbox']", jQuery(el).closest("tr")).val(qty)
    }
    function updQtyVis() {
        if (jQuery(this).prop("checked")){
            jQuery(".cbshow", jQuery(this).closest("tr")).css("visibility", "visible")
        } else {
            jQuery(".cbshow", jQuery(this).closest("tr")).css("visibility", "hidden")
        }
    }

</script>
<?php
VikRentCar::printTrackingCode();
