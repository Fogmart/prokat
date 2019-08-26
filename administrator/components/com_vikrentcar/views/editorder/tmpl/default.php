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

$row = $this->row;
$customer = $this->customer;
$payments = $this->payments;

$vrc_app = new VrcApplication();
$currencyname = VikRentCar::getCurrencyName();
$car = VikRentCar::getCarInfo($row['idcar']);
$dbo = JFactory::getDBO();
$nowdf = VikRentCar::getDateFormat(true);
$nowtf = VikRentCar::getTimeFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$payment = VikRentCar::getPayment($row['idpayment']);
$is_cust_cost = (!empty($row['cust_cost']) && $row['cust_cost'] > 0);

$pickup_place = '';
$dropoff_place = '';

$tar = array(array());
if (!empty($row['idtar']) || $is_cust_cost) {
	if (!empty($row['idtar'])) {
		if ($row['hourly'] == 1) {
			$q = "SELECT * FROM `#__vikrentcar_dispcosthours` WHERE `id`='".$row['idtar']."';";
		} else {
			$q = "SELECT * FROM `#__vikrentcar_dispcost` WHERE `id`='".$row['idtar']."';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$tar = $dbo->loadAssocList();
		if ($row['hourly'] == 1) {
			foreach ($tar as $kt => $vt) {
				$tar[$kt]['days'] = 1;
			}
		}
	} else {
		//Custom Rate
		$tar = array(0 => array(
			'id' => -1,
			'idcar' => $row['idcar'],
			'days' => $row['days'],
			'idprice' => -1,
			'cost' => $row['cust_cost'],
			'attrdata' => '',
		));
	}
	//vikrentcar 1.6
	$checkhourscharges = 0;
	$hoursdiff = 0;
	$ppickup = $row['ritiro'];
	$prelease = $row['consegna'];
	$secdiff = $prelease - $ppickup;
	$daysdiff = $secdiff / 86400;
	if (is_int($daysdiff)) {
		if ($daysdiff < 1) {
			$daysdiff = 1;
		}
	} else {
		if ($daysdiff < 1) {
			$daysdiff = 1;
			$checkhourly = true;
			$ophours = $secdiff / 3600;
			$hoursdiff = intval(round($ophours));
			if ($hoursdiff < 1) {
				$hoursdiff = 1;
			}
		} else {
			$sum = floor($daysdiff) * 86400;
			$newdiff = $secdiff - $sum;
			$maxhmore = VikRentCar::getHoursMoreRb() * 3600;
			if ($maxhmore >= $newdiff) {
				$daysdiff = floor($daysdiff);
			} else {
				$daysdiff = ceil($daysdiff);
				//vikrentcar 1.6
				$ehours = intval(round(($newdiff - $maxhmore) / 3600));
				$checkhourscharges = $ehours;
				if ($checkhourscharges > 0) {
					$aehourschbasp = VikRentCar::applyExtraHoursChargesBasp();
				}
				//
			}
		}
	}
	//
}
$pactive_tab = VikRequest::getString('vrc_active_tab', 'vrc-tab-details', 'request');

if ($row['status'] == "confirmed") {
	$saystaus = '<span class="label label-success">'.JText::_('VRCONFIRMED').'</span>';
} elseif ($row['status']=="standby") {
	$saystaus = '<span class="label label-warning">'.JText::_('VRSTANDBY').'</span>';
} else {
	$saystaus = '<span class="label label-error" style="background-color: #d9534f;">'.JText::_('VRCANCELLED').'</span>';
}
?>
<script type="text/javascript">
function vrToggleLog(elem) {
	var logdiv = document.getElementById('vrpaymentlogdiv').style.display;
	if (logdiv == 'block') {
		document.getElementById('vrpaymentlogdiv').style.display = 'none';
		jQuery(elem).parent(".vrc-bookingdet-noteslogs-btn").removeClass("vrc-bookingdet-noteslogs-btn-active");
	} else {
		jQuery(".vrc-bookingdet-noteslogs-btn-active").removeClass("vrc-bookingdet-noteslogs-btn-active");
		document.getElementById('vradminnotesdiv').style.display = 'none';
		document.getElementById('vrpaymentlogdiv').style.display = 'block';
		jQuery(elem).parent(".vrc-bookingdet-noteslogs-btn").addClass("vrc-bookingdet-noteslogs-btn-active");
	}
}
function changePayment() {
	var newpayment = document.getElementById('newpayment').value;
	if (newpayment != '') {
		var paymentname = document.getElementById('newpayment').options[document.getElementById('newpayment').selectedIndex].text;
		if (confirm('<?php echo addslashes(JText::_('VRCCHANGEPAYCONFIRM')); ?>' + paymentname + '?')) {
			document.adminForm.submit();
		} else {
			document.getElementById('newpayment').selectedIndex = 0;
		}
	}
}
function vrToggleNotes(elem) {
	var notesdiv = document.getElementById('vradminnotesdiv').style.display;
	if (notesdiv == 'block') {
		document.getElementById('vradminnotesdiv').style.display = 'none';
		jQuery(elem).parent(".vrc-bookingdet-noteslogs-btn").removeClass("vrc-bookingdet-noteslogs-btn-active");
	} else {
		jQuery(".vrc-bookingdet-noteslogs-btn-active").removeClass("vrc-bookingdet-noteslogs-btn-active");
		if (document.getElementById('vrpaymentlogdiv')) {
			document.getElementById('vrpaymentlogdiv').style.display = 'none';
		}
		document.getElementById('vradminnotesdiv').style.display = 'block';
		jQuery(elem).parent(".vrc-bookingdet-noteslogs-btn").addClass("vrc-bookingdet-noteslogs-btn-active");
	}
}
function toggleDiscount(elem) {
	var discsp = document.getElementById('vrdiscenter').style.display;
	if (discsp == 'block') {
		document.getElementById('vrdiscenter').style.display = 'none';
		jQuery(elem).find('i').removeClass("fa-chevron-up").addClass("fa-chevron-down");
	} else {
		document.getElementById('vrdiscenter').style.display = 'block';
		jQuery(elem).find('i').removeClass("fa-chevron-down").addClass("fa-chevron-up");
	}
}
</script>

<div class="vrc-bookingdet-topcontainer">
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		
		<div class="vrc-bookdet-container">
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span>ID</span>
				</div>
				<div class="vrc-bookdet-foot">
					<span><?php echo $row['id']; ?></span>
				</div>
			</div>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VREDITORDERONE'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
					<span><?php echo date($df.' '.$nowtf, $row['ts']); ?></span>
				</div>
			</div>
		<?php
		if (count($customer)) {
		?>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VRCDRIVERNOMINATIVE'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
					<?php echo (isset($customer['country_img']) ? $customer['country_img'].' ' : '').'<a href="index.php?option=com_vikrentcar&task=editcustomer&cid[]='.$customer['id'].'" target="_blank">'.ltrim($customer['first_name'].' '.$customer['last_name']).'</a>'; ?>
				</div>
			</div>
		<?php
		} elseif (!empty($row['nominative'])) {
		?>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VRCDRIVERNOMINATIVE'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
					<?php echo $row['nominative']; ?>
				</div>
			</div>
		<?php
		}
		?>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VREDITORDERFOUR'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
					<?php echo ($row['hourly'] == 1 && count($tar[0]) ? $tar[0]['hours'].' '.JText::_('VRCHOURS') : $row['days']); ?>
				</div>
			</div>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VREDITORDERFIVE'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
				<?php
				$ritiro_info = getdate($row['ritiro']);
				$short_wday = JText::_('VR'.strtoupper(substr($ritiro_info['weekday'], 0, 3)));
				?>
					<?php echo $short_wday.', '.date($df.' '.$nowtf, $row['ritiro']); ?>
				</div>
			</div>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VREDITORDERSIX'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
				<?php
				$consegna_info = getdate($row['consegna']);
				$short_wday = JText::_('VR'.strtoupper(substr($consegna_info['weekday'], 0, 3)));
				?>
					<?php echo $short_wday.', '.date($df.' '.$nowtf, $row['consegna']); ?>
				</div>
			</div>
		<?php
		if (!empty($row['idplace'])) {
			$pickup_place = VikRentCar::getPlaceName($row['idplace']);
			?>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VRRITIROCAR'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
					<?php echo $pickup_place; ?>
				</div>
			</div>
			<?php
		}
		if (!empty($row['idreturnplace'])) {
			$dropoff_place = VikRentCar::getPlaceName($row['idreturnplace']);
			?>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VRRETURNCARORD'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
					<?php echo $dropoff_place; ?>
				</div>
			</div>
			<?php
		}
		?>
			<div class="vrc-bookdet-wrap">
				<div class="vrc-bookdet-head">
					<span><?php echo JText::_('VRSTATUS'); ?></span>
				</div>
				<div class="vrc-bookdet-foot">
					<span><?php echo $saystaus; ?></span>
				</div>
			</div>
		</div>

		<div class="vrc-bookingdet-innertop">
			<div class="vrc-bookingdet-commands">
			<?php
			if (!empty($row['idbusy']) || $row['status'] == "standby") {
				?>
				<div class="vrc-bookingdet-command">
					<button onclick="document.location.href='index.php?option=com_vikrentcar&task=editbusy&return=order&cid[]=<?php echo $row['id']; ?>';" class="btn btn-secondary" type="button"><i class="icon-pencil"></i> <?php echo JText::_('VRMODRES'); ?></button>
				</div>
				<?php
			}
			if (($row['status'] == 'standby' || count($tar[0])) && time() < $row['ritiro']) {
				?>
				<div class="vrc-bookingdet-command">
					<button onclick="window.open('<?php echo JURI::root(); ?>index.php?option=com_vikrentcar&task=vieworder&sid=<?php echo $row['sid']; ?>&ts=<?php echo $row['ts']; ?>', '_blank');" type="button" class="btn btn-secondary"><i class="icon-eye"></i> <?php echo JText::_('VRCVIEWORDFRONT'); ?></button>
				</div>
				<?php
			}
			if ($row['status'] == "confirmed" && count($tar[0])) {
				?>
				<div class="vrc-bookingdet-command">
					<button class="btn btn-success" type="button" onclick="document.location.href='index.php?option=com_vikrentcar&task=resendordemail&cid[]=<?php echo $row['id']; ?>';"><i class="icon-mail"></i> <?php echo JText::_('VRCRESENDORDEMAIL'); ?></button>
				</div>
				<div class="vrc-bookingdet-command">
					<button class="btn btn-success" type="button" onclick="document.location.href='index.php?option=com_vikrentcar&task=resendordemail&sendpdf=1&cid[]=<?php echo $row['id']; ?>';"><i class="icon-mail"></i> <?php echo JText::_('VRCRESENDORDEMAILANDPDF'); ?></button>
				</div>
				<?php
			}
			if ($row['status'] == "standby") {
				?>
				<div class="vrc-bookingdet-command">
					<button class="btn btn-success" type="button" onclick="if (confirm('<?php echo addslashes(JText::_('VRSETORDCONFIRMED')); ?> ?')) {document.location.href='index.php?option=com_vikrentcar&task=setordconfirmed&cid[]=<?php echo $row['id']; ?>';}"><i class="vrcicn-checkmark"></i> <?php echo JText::_('VRSETORDCONFIRMED'); ?></button>
				</div>
				<?php
			}
			?>
			</div>

			<div class="vrc-bookingdet-tabs">
				<div class="vrc-bookingdet-tab vrc-bookingdet-tab-active" data-vrctab="vrc-tab-details"><?php echo JText::_('VRCBOOKDETTABDETAILS'); ?></div>
				<div class="vrc-bookingdet-tab" data-vrctab="vrc-tab-admin"><?php echo JText::_('VRCBOOKDETTABADMIN'); ?></div>
			</div>
		</div>

		<div class="vrc-bookingdet-tab-cont" id="vrc-tab-details" style="display: block;">
			<div class="vrc-bookingdet-innercontainer">
				<div class="vrc-bookingdet-customer">
					<div class="vrc-bookingdet-detcont<?php echo $row['closure'] > 0 ? ' vrc-bookingdet-closure' : ''; ?>">
					<?php
					$custdata_parts = explode("\n", $row['custdata']);
					if (count($custdata_parts) > 2 && strpos($custdata_parts[0], ':') !== false && strpos($custdata_parts[1], ':') !== false) {
						//attempt to format labels and values
						foreach ($custdata_parts as $custdet) {
							if (strlen($custdet) < 1) {
								continue;
							}
							$custdet_parts = explode(':', $custdet);
							$custd_lbl = '';
							$custd_val = '';
							if (count($custdet_parts) < 2) {
								$custd_val = $custdet;
							} else {
								$custd_lbl = $custdet_parts[0];
								unset($custdet_parts[0]);
								$custd_val = trim(implode(':', $custdet_parts));
							}
							?>
						<div class="vrc-bookingdet-userdetail">
							<?php
							if (strlen($custd_lbl)) {
								?>
							<span class="vrc-bookingdet-userdetail-lbl"><?php echo $custd_lbl; ?></span>
								<?php
							}
							if (strlen($custd_val)) {
								?>
							<span class="vrc-bookingdet-userdetail-val"><?php echo $custd_val; ?></span>
								<?php
							}
							?>
						</div>
							<?php
						}
					} else {
						if ($row['closure'] > 0) {
							?>
						<div class="vrc-bookingdet-userdetail">
							<span class="vrc-bookingdet-userdetail-val"><?php echo nl2br($row['custdata']); ?></span>
						</div>
							<?php
						} else {
							echo nl2br($row['custdata']);
							?>
						<div class="vrc-bookingdet-userdetail">
							<span class="vrc-bookingdet-userdetail-val">&nbsp;</span>
						</div>
							<?php
						}
					}
					if (!empty($row['ujid'])) {
						$orig_user = JFactory::getUser($row['ujid']);
						$author_name = is_object($orig_user) && property_exists($orig_user, 'name') && !empty($orig_user->name) ? $orig_user->name : '';
						?>
						<div class="vrc-bookingdet-userdetail">
							<span class="vrc-bookingdet-userdetail-val"><?php echo JText::sprintf('VRCBOOKINGCREATEDBY', $row['ujid'].(!empty($author_name) ? ' ('.$author_name.')' : '')); ?></span>
						</div>
						<?php
					}
					?>
					</div>
				<?php
				$contracted = file_exists(VRC_SITE_PATH.DS.'resources'.DS.'pdfs'.DS.$row['id'].'_'.$row['ts'].'.pdf');
				$invoiced = file_exists(VRC_SITE_PATH.DS.'helpers'.DS.'invoices'.DS.'generated'.DS.$row['id'].'_'.$row['sid'].'.pdf');
				$cancheckin = $row['status'] == "confirmed" && !empty($row['carindex']);
				if ($contracted || $invoiced || $cancheckin) {
					?>
					<div class="vrc-bookingdet-detcont vrc-hidein-print">
					<?php
					if ($row['status'] == "confirmed") {
						?>
						<div>
							<span class="label label-success"><?php echo JText::_('VRCCONFIRMATIONNUMBER'); ?> <span class="badge"><?php echo $row['sid'].'_'.$row['ts']; ?></span></span>
						</div>
						<?php
					}
					if ($cancheckin) {
						?>
						<div>
							<a href="index.php?option=com_vikrentcar&amp;task=customercheckin&amp;cid[]=<?php echo $row['id']; ?>"><i class="fas fa-sign-in-alt"></i> <?php echo JText::_('VRCCUSTOMERCHECKIN'); ?></a>
						</div>
						<?php
						if (file_exists(VRC_SITE_PATH . DS . "resources" . DS . "pdfs" . DS . $row['id'].'_'.$row['ts'].'_checkin.pdf')) {
						?>
						<div>
							<a href="<?php echo VRC_SITE_URI; ?>resources/pdfs/<?php echo $row['id'].'_'.$row['ts']; ?>_checkin.pdf" target="_blank" class="vrcpdfcheckin"><i class="fas fa-download"></i> <?php echo JText::_('VRCPDFCHECKIN'); ?></a>
						</div>
						<?php
						}
					}
					if ($contracted) {
						?>
						<div>
							<span class="label label-success"><span class="badge"><a href="<?php echo VRC_SITE_URI; ?>resources/pdfs/<?php echo $row['id'].'_'.$row['ts']; ?>.pdf" target="_blank"><i class="vrcicn-file-text2"></i><?php echo JText::_('VRCDOWNLOADPDF'); ?></a></span></span>
						</div>
						<?php
					}
					if ($invoiced) {
						?>
						<div>
							<span class="label label-success"><span class="badge"><a href="<?php echo VRC_SITE_URI; ?>helpers/invoices/generated/<?php echo $row['id'].'_'.$row['sid']; ?>.pdf" target="_blank"><i class="vrcicn-file-text2"></i><?php echo JText::_('VRCDOWNLOADPDFINVOICE'); ?></a></span></span>
						</div>
						<?php
					}
					?>
					</div>
					<?php
				}
				if ($row['closure'] < 1) {
				?>
					<div class="vrc-bookingdet-detcont vrc-hidein-print">
						<label for="custmail"><?php echo JText::_('VRCCUSTEMAILADDR'); ?></label>
						<input type="text" name="custmail" id="custmail" value="<?php echo $row['custmail']; ?>" size="25"/>
						<?php if (!empty($row['custmail'])) : ?> <button type="button" class="btn btn-secondary" onclick="vrcToggleSendEmail();" style="vertical-align: top;"><i class="vrcicn-envelop"></i><?php echo JText::_('VRSENDEMAILACTION'); ?></button><?php endif; ?>
					</div>
					<div class="vrc-bookingdet-detcont vrc-hidein-print">
						<label for="custphone"><?php echo JText::_('VRCUSTOMERPHONE'); ?></label>
						<input type="text" name="custphone" id="custphone" value="<?php echo $row['phone']; ?>" size="25"/>
					</div>
				<?php
				}
				?>
				</div>

				<?php
				$isdue = 0;
				?>

				<div class="vrc-bookingdet-summary">
					<div class="table-responsive">
						<table class="table">
							<tr class="vrc-bookingdet-summary-car">
								<td class="vrc-bookingdet-summary-car-firstcell">
									<div class="vrc-bookingdet-summary-carnum"><i class="fa fa-car"></i></div>
								<?php
								//Car Specific Unit
								if ($row['status'] == "confirmed" && !empty($car['params'])) {
									$car_params = json_decode($car['params'], true);
									$arr_features = array();
									$unavailable_indexes = VikRentCar::getCarUnitNumsUnavailable($row);
									if (is_array($car_params) && @count($car_params['features']) > 0) {
										foreach ($car_params['features'] as $cind => $cfeatures) {
											if (in_array($cind, $unavailable_indexes)) {
												continue;
											}
											foreach ($cfeatures as $fname => $fval) {
												if (strlen($fval)) {
													$arr_features[$cind] = '#'.$cind.' - '.JText::_($fname).': '.$fval;
													break;
												}
											}
										}
									}
									if (count($arr_features) > 0) {
										?>
									<div class="vrc-bookingdet-summary-carnum-chunit">
										<?php echo $vrc_app->getNiceSelect($arr_features, $row['carindex'], 'carindex', JText::_('VRCFEATASSIGNUNIT'), JText::_('VRCFEATASSIGNUNITEMPTY'), '', 'document.adminForm.submit();', 'carindex'); ?>
									</div>
										<?php
									}
								}
								if (!empty($pickup_place) && !empty($dropoff_place)) {
									?>
									<div class="vrc-bookingdet-summary-locations">
										<i class="fa fa-location-arrow"></i>
										<span><?php echo $pickup_place; ?></span>
									<?php
									if ($dropoff_place != $pickup_place) {
										?>
										<span class="vrc-bookingdet-location-divider">-&gt;</span>
										<span><?php echo $dropoff_place; ?></span>
										<?php
									}
									?>
									</div>
									<?php
								}
								if (!empty($row['nominative'])) {
									?>
									<div class="vrc-bookingdet-summary-guestname">
										<span><?php echo $row['nominative']; ?></span>
									</div>
								<?php
								}
								?>
								</td>
								<td>
									<div class="vrc-bookingdet-summary-carname"><?php echo $car['name']; ?></div>
									<div class="vrc-bookingdet-summary-carrate">
									<?php
									if (!empty($row['idtar']) || $is_cust_cost) {
										if ($checkhourscharges > 0 && $aehourschbasp == true && !$is_cust_cost) {
											$ret = VikRentCar::applyExtraHoursChargesCar($tar, $row['idcar'], $checkhourscharges, $daysdiff, false, true, true);
											$tar = $ret['return'];
											$calcdays = $ret['days'];
										}
										if ($checkhourscharges > 0 && $aehourschbasp == false && !$is_cust_cost) {
											$tar = VikRentCar::extraHoursSetPreviousFareCar($tar, $row['idcar'], $checkhourscharges, $daysdiff, true);
											$tar = VikRentCar::applySeasonsCar($tar, $row['ritiro'], $row['consegna'], $row['idplace']);
											$ret = VikRentCar::applyExtraHoursChargesCar($tar, $row['idcar'], $checkhourscharges, $daysdiff, true, true, true);
											$tar = $ret['return'];
											$calcdays = $ret['days'];
										} else {
											if (!$is_cust_cost) {
												//Seasonal prices only if not a custom rate
												$tar = VikRentCar::applySeasonsCar($tar, $row['ritiro'], $row['consegna'], $row['idplace']);
											}
										}
										$car_base_cost = $is_cust_cost ? $tar[0]['cost'] : VikRentCar::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'], $row);
										$isdue = $car_base_cost;

										echo $is_cust_cost ? JText::_('VRCRENTCUSTRATEPLAN') : VikRentCar::getPriceName($tar[0]['idprice']);
										if (isset($tar[0]['attrdata']) && !empty($tar[0]['attrdata'])) {
											echo '<br/>' . VikRentCar::getPriceAttr($tar[0]['idprice']) . ': ' . $tar[0]['attrdata'];
										}
									}
									?>
									</div>
								</td>
								<td>
									<div class="vrc-bookingdet-summary-price">
									<?php
									if (!empty($row['idtar']) || $is_cust_cost) {
										echo $currencyname . ' ' . VikRentCar::numberFormat($car_base_cost);
									} else {
										echo $currencyname . ' -----';
									}
									?>
									</div>
								</td>
							</tr>
							<?php
							//Options
							if (!empty($row['optionals'])) {
								$stepo = explode(";", $row['optionals']);
								$counter = 0;
								foreach ($stepo as $oo) {
									if (empty($oo)) {
										continue;
									}
									$stept = explode(":", $oo);
									$q = "SELECT * FROM `#__vikrentcar_optionals` WHERE `id`=".(int)$stept[0].";";
									$dbo->setQuery($q);
									$dbo->execute();
									if ($dbo->getNumRows() != 1) {
										continue;
									}
									$counter++;
									$actopt = $dbo->loadAssocList();
									$realcost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $row['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]);
									$basequancost = intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $row['days']) : $actopt[0]['cost'];
									if ($actopt[0]['maxprice'] > 0 && $basequancost > $actopt[0]['maxprice']) {
										$realcost = $actopt[0]['maxprice'];
										if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
											$realcost = $actopt[0]['maxprice'] * $stept[1];
										}
									}
									$tmpopr = VikRentCar::sayOptionalsPlusIva($realcost, $actopt[0]['idiva'], $row);
									$isdue += $tmpopr;
									?>
							<tr class="vrc-bookingdet-summary-options">
								<td class="vrc-bookingdet-summary-options-title"><?php echo $counter == 1 ? JText::_('VREDITORDEREIGHT') : '&nbsp;'; ?></td>
								<td>
									<span class="vrc-bookingdet-summary-lbl"><?php echo ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name']; ?></span>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-cost"><?php echo $currencyname." ".VikRentCar::numberFormat($tmpopr); ?></span>
								</td>
							</tr>
								<?php
								}
							}
							//VRC 1.7 - Location fees
							if (!empty($row['idplace']) && !empty($row['idreturnplace'])) {
								$locfee = VikRentCar::getLocFee($row['idplace'], $row['idreturnplace']);
								if ($locfee) {
									//Location fees overrides
									if (strlen($locfee['losoverride']) > 0) {
										$arrvaloverrides = array();
										$valovrparts = explode('_', $locfee['losoverride']);
										foreach ($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($row['days'], $arrvaloverrides)) {
											$locfee['cost'] = $arrvaloverrides[$row['days']];
										}
									}
									//
									$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $row['days']) : $locfee['cost'];
									$locfeewith = VikRentCar::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $row);
									$isdue += $locfeewith;
									?>
							<tr class="vrc-bookingdet-summary-custcosts">
								<td class="vrc-bookingdet-summary-custcosts-title">&nbsp;</td>
								<td>
									<span class="vrc-bookingdet-summary-lbl"><?php echo JText::_('VREDITORDERTEN'); ?></span>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-cost"><?php echo $currencyname." ".VikRentCar::numberFormat($locfeewith); ?></span>
								</td>
							</tr>
									<?php
								}
							}
							//VRC 1.9 - Out of Hours Fees
							$oohfee = VikRentCar::getOutOfHoursFees($row['idplace'], $row['idreturnplace'], $row['ritiro'], $row['consegna'], array('id' => $row['idcar']));
							if (count($oohfee) > 0) {
								$oohfeewith = VikRentCar::sayOohFeePlusIva($oohfee['cost'], $oohfee['idiva']);
								$isdue += $oohfeewith;
								?>
							<tr class="vrc-bookingdet-summary-custcosts">
								<td class="vrc-bookingdet-summary-custcosts-title">&nbsp;</td>
								<td>
									<span class="vrc-bookingdet-summary-lbl"><?php echo JText::_('VRCOOHFEEAMOUNT'); ?></span>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-cost"><?php echo $currencyname." ".VikRentCar::numberFormat($oohfeewith); ?></span>
								</td>
							</tr>
								<?php
							}
							//Custom extra costs
							if (!empty($row['extracosts'])) {
								$counter = 0;
								$cur_extra_costs = json_decode($row['extracosts'], true);
								foreach ($cur_extra_costs as $eck => $ecv) {
									$counter++;
									$efee_cost = VikRentCar::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $row);
									$isdue += $efee_cost;
									?>
							<tr class="vrc-bookingdet-summary-custcosts">
								<td class="vrc-bookingdet-summary-custcosts-title"><?php echo $counter == 1 ? JText::_('VRPEDITBUSYEXTRACOSTS') : '&nbsp;'; ?></td>
								<td>
									<span class="vrc-bookingdet-summary-lbl"><?php echo $ecv['name']; ?></span>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-cost"><?php echo $currencyname." ".VikRentCar::numberFormat($efee_cost); ?></span>
								</td>
							</tr>
									<?php
								}
							}
							//VRC 1.6 - Coupon
							$usedcoupon = false;
							$origisdue = $isdue;
							if (strlen($row['coupon']) > 0) {
								$usedcoupon = true;
								$expcoupon = explode(";", $row['coupon']);
								$isdue = $isdue - $expcoupon[1];
								?>
							<tr class="vrc-bookingdet-summary-coupon">
								<td><?php echo JText::_('VRCCOUPON'); ?></td>
								<td>
									<span class="vrc-bookingdet-summary-lbl"><?php echo $expcoupon[2]; ?></span>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-cost">- <?php echo $currencyname; ?> <?php echo VikRentCar::numberFormat($expcoupon[1]); ?></span>
								</td>
							</tr>
								<?php
							}
							//Order Total
							?>
							<tr class="vrc-bookingdet-summary-total">
								<td>
									<span class="vrapplydiscsp" onclick="toggleDiscount(this);">
										<i class="fa fa-chevron-down" title="<?php echo JText::_('VRCAPPLYDISCOUNT'); ?>"></i>
									</span>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-lbl"><?php echo JText::_('VREDITORDERNINE'); ?></span>

									<div class="vrdiscenter" id="vrdiscenter" style="display: none;">
										<div class="vrdiscenter-entry">
											<span class="vrdiscenter-label"><?php echo JText::_('VRCAPPLYDISCOUNT'); ?>:</span><span class="vrdiscenter-value"><?php echo $currencyname; ?> <input type="number" step="any" name="admindisc" value="" size="4"/></span>
										</div>
										<div class="vrdiscenter-entrycentered">
											<button type="submit" class="btn btn-success"><?php echo JText::_('VRCAPPLYDISCOUNTSAVE'); ?></button>
										</div>
									</div>
								</td>
								<td>
									<span class="vrc-bookingdet-summary-cost"><?php echo $currencyname; ?> <?php echo VikRentCar::numberFormat($row['order_total']); ?></span>
								</td>
							</tr>
						<?php
						if (!empty($row['totpaid']) && $row['totpaid'] > 0) {
							$diff_to_pay = $row['order_total'] - $row['totpaid'];
							?>
							<tr class="vrc-bookingdet-summary-totpaid">
								<td>&nbsp;</td>
								<td><?php echo JText::_('VRCAMOUNTPAID'); ?></td>
								<td><?php echo $currencyname.' '.VikRentCar::numberFormat($row['totpaid']); ?></td>
							</tr>
							<?php
							if ($diff_to_pay > 1) {
							?>
							<tr class="vrc-bookingdet-summary-totpaid vrc-bookingdet-summary-totremaining">
								<td>&nbsp;</td>
								<td>
									<div><?php echo JText::_('VRCTOTALREMAINING'); ?></div>
								</td>
								<td><?php echo $currencyname.' '.VikRentCar::numberFormat($diff_to_pay); ?></td>
							</tr>
							<?php
							}
						}
						?>
						</table>
					</div>
				</div>
			</div>
		</div>

		<div class="vrc-bookingdet-tab-cont" id="vrc-tab-admin" style="display: none;">
			<div class="vrc-bookingdet-innercontainer">
				<div class="vrc-bookingdet-admindata">
					<div class="vrc-bookingdet-admin-entry">
						<label for="newpayment"><?php echo JText::_('VRPAYMENTMETHOD'); ?></label>
					<?php
					if (is_array($payment)) {
						?>
						<span><?php echo $payment['name']; ?></span>
						<?php
					}
					$chpayment = '';
					if (is_array($payments)) {
						$chpayment = '<div><select name="newpayment" id="newpayment" onchange="changePayment();"><option value="">'.JText::_('VRCCHANGEPAYLABEL').'</option>';
						foreach($payments as $pay) {
							$chpayment .= '<option value="'.$pay['id'].'">'.(is_array($payment) && $payment['id'] == $pay['id'] ? ' ::' : '').$pay['name'].'</option>';
						}
						$chpayment .= '</select></div>';
					}
					echo $chpayment;
					?>
					</div>
				<?php
				$tn = VikRentCar::getTranslator();
				$all_langs = $tn->getLanguagesList();
				if (count($all_langs) > 1) {
				?>
					<div class="vrc-bookingdet-admin-entry">
						<label for="newlang"><?php echo JText::_('VRCBOOKINGLANG'); ?></label>
						<select name="newlang" id="newlang" onchange="document.adminForm.submit();">
						<?php
						foreach ($all_langs as $lk => $lv) {
							?>
							<option value="<?php echo $lk; ?>"<?php echo $row['lang'] == $lk ? ' selected="selected"' : ''; ?>><?php echo isset($lv['nativeName']) ? $lv['nativeName'] : $lv['name']; ?></option>
							<?php
						}
						?>
						</select>
					</div>
				<?php
				}
				?>
				</div>
				<div class="vrc-bookingdet-noteslogs">
					<div class="vrc-bookingdet-noteslogs-btns">
						<div class="vrc-bookingdet-noteslogs-btn vrc-bookingdet-noteslogs-btn-active">
							<a href="javascript: void(0);" onclick="javascript: vrToggleNotes(this);"><?php echo JText::_('VRCTOGGLEORDNOTES'); ?></a>
						</div>
					<?php
					if (!empty($row['paymentlog'])) {
						?>
						<div class="vrc-bookingdet-noteslogs-btn">
							<a href="javascript: void(0);" id="vrc-trig-paylogs" onclick="javascript: vrToggleLog(this);"><?php echo JText::_('VRCPAYMENTLOGTOGGLE'); ?></a>
							<a name="paymentlog" href="javascript: void(0);"></a>
						</div>
						<?php
					}
					?>
					</div>
					<div class="vrc-bookingdet-noteslogs-cont">
						<div id="vradminnotesdiv" style="display: block;">
							<textarea name="adminnotes" class="vradminnotestarea"><?php echo strip_tags($row['adminnotes']); ?></textarea>
							<br clear="all"/>
							<input type="submit" name="updadmnotes" value="<?php echo JText::_('VRCUPDATEBTN'); ?>" class="btn btn-secondary" />
						</div>
					<?php
					if (!empty($row['paymentlog'])) {
						?>
						<div id="vrpaymentlogdiv" style="display: none;">
							<pre style="min-height: 100%;"><?php echo htmlspecialchars($row['paymentlog']); ?></pre>
						</div>
						<script type="text/javascript">
						if (window.location.hash == '#paymentlog') {
							vrToggleLog(document.getElementById('vrc-trig-paylogs'));
							jQuery(".vrc-bookingdet-tab[data-vrctab='vrc-tab-admin']").trigger('click');
						}
						</script>
						<?php
					}
					?>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="task" value="editorder">
		<input type="hidden" name="vrc_active_tab" id="vrc_active_tab" value="">
		<input type="hidden" name="whereup" value="<?php echo $row['id']; ?>">
		<input type="hidden" name="cid[]" value="<?php echo $row['id']; ?>">
		<input type="hidden" name="option" value="com_vikrentcar">
		<?php
		$tmpl = VikRequest::getVar('tmpl');
		if ($tmpl == 'component') {
			echo '<input type="hidden" name="tmpl" value="component">';
		}
		$pgoto = VikRequest::getString('goto', '', 'request');
		if ($pgoto == 'overv') {
			echo '<input type="hidden" name="goto" value="overv">';
		}
		?>
	</form>
</div>

<div class="vrc-info-overlay-block">
	<a class="vrc-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vrc-info-overlay-content vrc-info-overlay-content-sendemail">
		<div id="vrc-overlay-email-cont" style="display: none;">
			<h4><?php echo JText::_('VRSENDEMAILACTION'); ?>: <span id="emailto-lbl"><?php echo $row['custmail']; ?></span></h4>
			<form action="index.php?option=com_vikrentcar" method="post" enctype="multipart/form-data">
				<input type="hidden" name="bid" value="<?php echo $row['id']; ?>" />
			<?php
			$cur_emtpl = array();
			$q = "SELECT `setting` FROM `#__vikrentcar_config` WHERE `param`='customemailtpls';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$cur_emtpl = $dbo->loadResult();
				$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
				$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
			}
			if (count($cur_emtpl) > 0) {
				?>
				<div style="float: right;">
					<select id="emtpl-customemail" onchange="vrcLoadEmailTpl(this.value);">
						<option value=""><?php echo JText::_('VREMAILCUSTFROMTPL'); ?></option>
					<?php
					foreach ($cur_emtpl as $emk => $emv) {
						?>
						<optgroup label="<?php echo $emv['emailsubj']; ?>">
							<option value="<?php echo $emk; ?>"><?php echo JText::_('VREMAILCUSTFROMTPLUSE'); ?></option>
							<option value="rm<?php echo $emk; ?>"><?php echo JText::_('VREMAILCUSTFROMTPLRM'); ?></option>
						</optgroup>
						<?php
					}
					?>
					</select>
				</div>
				<?php
			}
			?>
				<div class="vrc-calendar-cfield-entry">
					<label for="emailsubj"><?php echo JText::_('VRSENDEMAILCUSTSUBJ'); ?></label>
					<span><input type="text" name="emailsubj" id="emailsubj" value="" size="30" /></span>
				</div>
				<div class="vrc-calendar-cfield-entry">
					<label for="emailcont"><?php echo JText::_('VRSENDEMAILCUSTCONT'); ?></label>
					<textarea name="emailcont" id="emailcont" style="width: 99%; min-width: 99%; max-width: 99%; height: 120px; margin-bottom: 1px;"></textarea>
					<div class="btn-group pull-left vrc-smstpl-bgroup vrc-custmail-bgroup">
						<button onclick="setSpecialTplTag('emailcont', '{customer_name}');" class="btn btn-secondary btn-small" type="button">{customer_name}</button>
						<button onclick="setSpecialTplTag('emailcont', '{pickup_date}');" class="btn btn-secondary btn-small" type="button">{pickup_date}</button>
						<button onclick="setSpecialTplTag('emailcont', '{dropoff_date}');" class="btn btn-secondary btn-small" type="button">{dropoff_date}</button>
						<button onclick="setSpecialTplTag('emailcont', '{pickup_place}');" class="btn btn-secondary btn-small" type="button">{pickup_place}</button>
						<button onclick="setSpecialTplTag('emailcont', '{dropoff_place}');" class="btn btn-secondary btn-small" type="button">{dropoff_place}</button>
						<button onclick="setSpecialTplTag('emailcont', '{num_days}');" class="btn btn-secondary btn-small" type="button">{num_days}</button>
						<button onclick="setSpecialTplTag('emailcont', '{car_name}');" class="btn btn-secondary btn-small" type="button">{car_name}</button>
						<button onclick="setSpecialTplTag('emailcont', '{total}');" class="btn btn-secondary btn-small" type="button">{total}</button>
						<button onclick="setSpecialTplTag('emailcont', '{total_paid}');" class="btn btn-secondary btn-small" type="button">{total_paid}</button>
						<button onclick="setSpecialTplTag('emailcont', '{remaining_balance}');" class="btn btn-secondary btn-small" type="button">{remaining_balance}</button>
					</div>
				</div>
				<div class="vrc-calendar-cfield-entry">
					<label for="emailattch"><?php echo JText::_('VRSENDEMAILCUSTATTCH'); ?></label>
					<span><input type="file" name="emailattch" id="emailattch" /></span>
				</div>
				<div class="vrc-calendar-cfield-entry">
					<label for="emailfrom"><?php echo JText::_('VRSENDEMAILCUSTFROM'); ?></label>
					<span><input type="text" name="emailfrom" id="emailfrom" value="<?php echo VikRentCar::getSenderMail(); ?>" size="30" /></span>
				</div>
				<br clear="all" />
				<div class="vrc-calendar-cfields-bottom">
					<button type="submit" class="btn"><i class="vrcicn-envelop"></i><?php echo JText::_('VRSENDEMAILACTION'); ?></button>
				</div>
				<input type="hidden" name="email" id="emailto" value="<?php echo $row['custmail']; ?>" />
				<input type="hidden" name="goto" value="<?php echo urlencode('index.php?option=com_vikrentcar&task=editorder&cid[]='.$row['id']); ?>" />
				<input type="hidden" name="task" value="sendcustomemail" />
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
var vrc_overlay_on = false;
var vrc_print_only = false;
if (jQuery.isFunction(jQuery.fn.tooltip)) {
	jQuery(".hasTooltip").tooltip();
}
function vrcToggleSendEmail() {
	var cur_email = jQuery("#emailto").val();
	var email_set = jQuery("#custmail").val();
	if (email_set.length && email_set != cur_email) {
		jQuery("#emailto").val(email_set);
		jQuery("#emailto-lbl").text(email_set);
	}
	jQuery("#vrc-overlay-email-cont").show();
	jQuery(".vrc-info-overlay-block").fadeToggle(400, function() {
		if (jQuery(".vrc-info-overlay-block").is(":visible")) {
			vrc_overlay_on = true;
		} else {
			vrc_overlay_on = false;
		}
	});
}
function setSpecialTplTag(taid, tpltag) {
	var tplobj = document.getElementById(taid);
	if (tplobj != null) {
		var start = tplobj.selectionStart;
		var end = tplobj.selectionEnd;
		tplobj.value = tplobj.value.substring(0, start) + tpltag + tplobj.value.substring(end);
		tplobj.selectionStart = tplobj.selectionEnd = start + tpltag.length;
		tplobj.focus();
	}
}
jQuery(document).ready(function(){
	jQuery(document).mouseup(function(e) {
		if (!vrc_overlay_on) {
			return false;
		}
		var vrc_overlay_cont = jQuery(".vrc-info-overlay-content");
		if (!vrc_overlay_cont.is(e.target) && vrc_overlay_cont.has(e.target).length === 0) {
			jQuery(".vrc-info-overlay-block").fadeOut();
			vrc_overlay_on = false;
		}
	});
	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27 && vrc_overlay_on) {
			jQuery(".vrc-info-overlay-block").fadeOut();
			vrc_overlay_on = false;
		}
	});
	jQuery(".vrc-bookingdet-tab").click(function() {
		var newtabrel = jQuery(this).attr('data-vrctab');
		var oldtabrel = jQuery(".vrc-bookingdet-tab-active").attr('data-vrctab');
		if (newtabrel == oldtabrel) {
			return;
		}
		jQuery(".vrc-bookingdet-tab").removeClass("vrc-bookingdet-tab-active");
		jQuery(this).addClass("vrc-bookingdet-tab-active");
		jQuery("#"+oldtabrel).hide();
		jQuery("#"+newtabrel).fadeIn();
		jQuery("#vrc_active_tab").val(newtabrel);
	});
	jQuery(".vrc-bookingdet-tab[data-vrctab='<?php echo $pactive_tab; ?>']").trigger('click');
});
var cur_emtpl = <?php echo json_encode($cur_emtpl); ?>;
function vrcLoadEmailTpl(tplind) {
	if (!(tplind.length > 0)) {
		jQuery('#emailsubj').val('');
		jQuery('#emailcont').val('');
		return true;
	}
	if (tplind.substr(0, 2) == 'rm') {
		if (confirm('<?php echo addslashes(JText::_('VRCDELCONFIRM')); ?>')) {
			document.location.href = 'index.php?option=com_vikrentcar&task=rmcustomemailtpl&cid[]=<?php echo $row['id']; ?>&tplind='+tplind.substr(2);
		}
		return false;
	}
	if (!cur_emtpl.hasOwnProperty(tplind)) {
		jQuery('#emailsubj').val('');
		jQuery('#emailcont').val('');
		return true;
	}
	jQuery('#emailsubj').val(cur_emtpl[tplind]['emailsubj']);
	jQuery('#emailcont').val(cur_emtpl[tplind]['emailcont']);
	jQuery('#emailfrom').val(cur_emtpl[tplind]['emailfrom']);
	return true;
}
<?php
$pcustomemail = VikRequest::getInt('customemail', '', 'request');
if ($pcustomemail > 0) {
	?>
	vrcToggleSendEmail();
	<?php
}
?>
</script>
