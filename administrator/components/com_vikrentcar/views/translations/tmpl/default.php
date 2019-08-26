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

$vrc_tn = $this->vrc_tn;

$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$langs = $vrc_tn->getLanguagesList();
$xml_tables = $vrc_tn->getTranslationTables();
$active_table = '';
$active_table_key = '';
if (!(count($langs) > 1)) {
	//Error: only one language is published. Translations are useless
	?>
	<p class="err"><?php echo JText::_('VRCTRANSLATIONERRONELANG'); ?></p>
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_vikrentcar" />
	</form>
	<?php
} elseif (!(count($xml_tables) > 0) || strlen($vrc_tn->getError())) {
	//Error: XML file not readable or errors occurred
	?>
	<p class="err"><?php echo $vrc_tn->getError(); ?></p>
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_vikrentcar" />
	</form>
	<?php
} else {
	$cur_langtab = VikRequest::getString('vrc_lang', '', 'request');
	$table = VikRequest::getString('vrc_table', '', 'request');
	if (!empty($table)) {
		$table = $vrc_tn->replacePrefix($table);
	}
?>
<script type="text/Javascript">
var vrc_tn_changes = false;
jQuery(document).ready(function() {
	jQuery('#adminForm input[type=text], #adminForm textarea').change(function() {
		vrc_tn_changes = true;
	});
});
function vrcCheckChanges() {
	if (!vrc_tn_changes) {
		return true;
	}
	return confirm("<?php echo addslashes(JText::_('VRCTANSLATIONSCHANGESCONF')); ?>");
}
</script>
<form action="index.php?option=com_vikrentcar&amp;task=translations" method="post" onsubmit="return vrcCheckChanges();">
	<div style="width: 100%; display: inline-block;" class="btn-toolbar" id="filter-bar">
		<div class="btn-group pull-right">
			<button class="btn" type="submit"><?php echo JText::_('VRCGETTRANSLATIONS'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<select name="vrc_table">
				<option value="">-----------</option>
			<?php
			foreach ($xml_tables as $key => $value) {
				$active_table = $vrc_tn->replacePrefix($key) == $table ? $value : $active_table;
				$active_table_key = $vrc_tn->replacePrefix($key) == $table ? $key : $active_table_key;
				?>
				<option value="<?php echo $key; ?>"<?php echo $vrc_tn->replacePrefix($key) == $table ? ' selected="selected"' : ''; ?>><?php echo $value; ?></option>
				<?php
			}
			?>
			</select>
		</div>
	</div>
	<input type="hidden" name="vrc_lang" class="vrc_lang" value="<?php echo $vrc_tn->default_lang; ?>">
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="translations" />
</form>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vrc-translation-langtabs">
<?php
foreach ($langs as $ltag => $lang) {
	$is_def = ($ltag == $vrc_tn->default_lang);
	$lcountry = substr($ltag, 0, 2);
	$flag = file_exists(JPATH_SITE.DS.'media'.DS.'mod_languages'.DS.'images'.DS.$lcountry.'.gif') ? '<img src="'.JURI::root().'media/mod_languages/images/'.$lcountry.'.gif"/>' : '';
		?>
		<div class="vrc-translation-tab<?php echo $is_def ? ' vrc-translation-tab-default' : ''; ?>" data-vrclang="<?php echo $ltag; ?>">
		<?php
		if (!empty($flag)) {
			?>
			<span class="vrc-translation-flag"><?php echo $flag; ?></span>
			<?php
		}
		?>
			<span class="vrc-translation-langname"><?php echo $lang['name']; ?></span>
		</div>
	<?php
}
?>
		<div class="vrc-translation-tab vrc-translation-tab-ini" data-vrclang="">
			<span class="vrc-translation-iniflag">.INI</span>
			<span class="vrc-translation-langname"><?php echo JText::_('VRCTRANSLATIONINISTATUS'); ?></span>
		</div>
	</div>
	<div class="vrc-translation-tabscontents">
<?php
$table_cols = !empty($active_table_key) ? $vrc_tn->getTableColumns($active_table_key) : array();
$table_def_dbvals = !empty($active_table_key) ? $vrc_tn->getTableDefaultDbValues($active_table_key, array_keys($table_cols)) : array();
if (!empty($active_table_key)) {
	echo '<input type="hidden" name="vrc_table" value="'.$active_table_key.'"/>'."\n";
}
foreach ($langs as $ltag => $lang) {
	$is_def = ($ltag == $vrc_tn->default_lang);
	?>
		<div class="vrc-translation-langcontent" style="display: <?php echo $is_def ? 'block' : 'none'; ?>;" id="vrc_langcontent_<?php echo $ltag; ?>">
	<?php
	if (empty($active_table_key)) {
		?>
			<p class="warn"><?php echo JText::_('VRCTRANSLATIONSELTABLEMESS'); ?></p>
		<?php
	} elseif (strlen($vrc_tn->getError()) > 0) {
		?>
			<p class="err"><?php echo $vrc_tn->getError(); ?></p>
		<?php
	} else {
		?>
			<fieldset class="adminform">
				<legend class="adminlegend"><?php echo $active_table; ?> - <?php echo $lang['name'].($is_def ? ' - '.JText::_('VRCTRANSLATIONDEFLANG') : ''); ?></legend>
				<table cellspacing="1" class="admintable table">
					<tbody>
		<?php
		if ($is_def) {
			//Values of Default Language to be translated
			foreach ($table_def_dbvals as $reference_id => $values) {
				?>
						<tr data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<td class="vrc-translate-reference-cell" colspan="2"><?php echo $vrc_tn->getRecordReferenceName($table_cols, $values); ?></td>
						</tr>
				<?php
				foreach ($values as $field => $def_value) {
					$title = $table_cols[$field]['jlang'];
					$type = $table_cols[$field]['type'];
					if ($type == 'html') {
						$def_value = strip_tags($def_value);
					}
					?>
						<tr data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<td width="200" class="vrc-translate-column-cell"> <b><?php echo $title; ?></b> </td>
							<td><?php echo $type != 'json' ? $def_value : ''; ?></td>
						</tr>
					<?php
					if ($type == 'json') {
						$tn_keys = $table_cols[$field]['keys'];
						$keys = !empty($tn_keys) ? explode(',', $tn_keys) : array();
						$json_def_values = json_decode($def_value, true);
						if (count($json_def_values) > 0) {
							foreach ($json_def_values as $jkey => $jval) {
								if ((!in_array($jkey, $keys) && count($keys) > 0) || empty($jval)) {
									continue;
								}
								?>
						<tr data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<td width="200" class="vrc-translate-column-cell"><?php echo !is_numeric($jkey) ? ucwords($jkey) : '&nbsp;'; ?></td>
							<td><?php echo $jval; ?></td>
						</tr>
								<?php
							}
						}
					}
					?>
					<?php
				}
			}
		} else {
			//Translation Fields for this language
			$lang_record_tn = $vrc_tn->getTranslatedTable($active_table_key, $ltag);
			foreach ($table_def_dbvals as $reference_id => $values) {
				?>
						<tr data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<td class="vrc-translate-reference-cell" colspan="2"><?php echo $vrc_tn->getRecordReferenceName($table_cols, $values); ?></td>
						</tr>
				<?php
				foreach ($values as $field => $def_value) {
					$title = $table_cols[$field]['jlang'];
					$type = $table_cols[$field]['type'];
					if ($type == 'skip') {
						continue;
					}
					$tn_value = '';
					$tn_class = ' vrc-missing-translation';
					if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content']) && strlen($lang_record_tn[$reference_id]['content'][$field])) {
						if (in_array($type, array('text', 'textarea', 'html'))) {
							$tn_class = ' vrc-field-translated';
						} else {
							$tn_class = '';
						}
					}
					?>
						<tr data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<td width="200" class="vrc-translate-column-cell<?php echo $tn_class; ?>"<?php echo in_array($type, array('textarea', 'html')) ? ' style="vertical-align: top !important;"' : ''; ?>> <b><?php echo $title; ?></b> </td>
							<td>
					<?php
					if ($type == 'text') {
						if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content'])) {
							$tn_value = $lang_record_tn[$reference_id]['content'][$field];
						}
						?>
								<input type="text" name="tn[<?php echo $ltag; ?>][<?php echo $reference_id; ?>][<?php echo $field; ?>]" value="<?php echo $tn_value; ?>" size="40" placeholder="<?php echo $def_value; ?>"/>
						<?php
					} elseif ($type == 'textarea') {
						if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content'])) {
							$tn_value = $lang_record_tn[$reference_id]['content'][$field];
						}
						?>
								<textarea name="tn[<?php echo $ltag; ?>][<?php echo $reference_id; ?>][<?php echo $field; ?>]" rows="5" cols="40"><?php echo $tn_value; ?></textarea>
						<?php
					} elseif ($type == 'html') {
						if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content'])) {
							$tn_value = $lang_record_tn[$reference_id]['content'][$field];
						}
						echo $editor->display( "tn[".$ltag."][".$reference_id."][".$field."]", $tn_value, '100%', 350, 70, 20, true, "tn_".$ltag."_".$reference_id."_".$field );
					}
					?>
							</td>
						</tr>
					<?php
					if ($type == 'json') {
						$tn_keys = $table_cols[$field]['keys'];
						$keys = !empty($tn_keys) ? explode(',', $tn_keys) : array();
						$json_def_values = json_decode($def_value, true);
						if (count($json_def_values) > 0) {
							$tn_json_value = array();
							if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content'])) {
								$tn_json_value = json_decode($lang_record_tn[$reference_id]['content'][$field], true);
							}
							foreach ($json_def_values as $jkey => $jval) {
								if ((!in_array($jkey, $keys) && count($keys) > 0) || empty($jval)) {
									continue;
								}
								?>
						<tr data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<td width="200" class="vrc-translate-column-cell"><?php echo !is_numeric($jkey) ? ucwords($jkey) : '&nbsp;'; ?></td>
							<td>
							<?php
							if (strlen($jval) > 40) {
							?>
								<textarea rows="5" cols="170" style="min-width: 60%;" name="tn[<?php echo $ltag; ?>][<?php echo $reference_id; ?>][<?php echo $field; ?>][<?php echo $jkey; ?>]"><?php echo $tn_json_value[$jkey]; ?></textarea>
							<?php
							} else {
							?>
								<input type="text" name="tn[<?php echo $ltag; ?>][<?php echo $reference_id; ?>][<?php echo $field; ?>][<?php echo $jkey; ?>]" value="<?php echo $tn_json_value[$jkey]; ?>" size="40"/>
							<?php
							}
							?>
							</td>
						</tr>
								<?php
							}
						}
					}
				}
			}
		}
		?>
					</tbody>
				</table>
			</fieldset>
		<?php
		//echo '<pre>'.print_r($table_def_dbvals, true).'</pre><br/>';
		//echo '<pre>'.print_r($table_cols, true).'</pre><br/>';
	}
	?>
		</div>
	<?php
}
//ini files status
$all_inis = $vrc_tn->getIniFiles();
?>
		<div class="vrc-translation-langcontent" style="display: none;" id="vrc_langcontent_ini">
			<fieldset class="adminform">
				<legend class="adminlegend">.INI <?php echo JText::_('VRCTRANSLATIONINISTATUS'); ?></legend>
				<table cellspacing="1" class="admintable table">
					<tbody>
					<?php
					foreach ($all_inis as $initype => $inidet) {
						$inipath = $inidet['path'];
						?>
						<tr>
							<td class="vrc-translate-reference-cell" colspan="2"><?php echo JText::_('VRCINIEXPL'.strtoupper($initype)); ?></td>
						</tr>
						<?php
						foreach ($langs as $ltag => $lang) {
							$t_file_exists = file_exists(str_replace('en-GB', $ltag, $inipath));
							$t_parsed_ini = $t_file_exists ? parse_ini_file(str_replace('en-GB', $ltag, $inipath)) : false;
							?>
						<tr>
							<td width="200" class="vrc-translate-column-cell <?php echo $t_file_exists ? 'vrc-field-translated' : 'vrc-missing-translation'; ?>"> <b><?php echo ($ltag == 'en-GB' ? 'Native ' : '').$lang['name']; ?></b> </td>
							<td>
								<span class="vrc-inifile-totrows <?php echo $t_file_exists ? 'vrc-inifile-exists' : 'vrc-inifile-notfound'; ?>"><?php echo $t_file_exists && $t_parsed_ini !== false ? JText::_('VrcINIDEFINITIONS').': '.count($t_parsed_ini) : JText::_('VrcINIMISSINGFILE'); ?></span>
								<span class="vrc-inifile-path <?php echo $t_file_exists ? 'vrc-inifile-exists' : 'vrc-inifile-notfound'; ?>"><?php echo JText::_('VrcINIPATH').': '.str_replace('en-GB', $ltag, $inipath); ?></span>
							</td>
						</tr>
							<?php
						}
					}
					?>
					</tbody>
				</table>
			</fieldset>
		</div>
	<?php
	//end ini files status
	?>
	</div>
	<input type="hidden" name="vrc_lang" class="vrc_lang" value="<?php echo $vrc_tn->default_lang; ?>">
	<input type="hidden" name="task" value="translations">
	<input type="hidden" name="option" value="com_vikrentcar" />
	<br/>
	<table align="center">
		<tr>
			<td align="center"><?php echo $vrc_tn->getPagination(); ?></td>
		</tr>
		<tr>
			<td align="center">
				<select name="limit" onchange="document.adminForm.limitstart.value = '0'; document.adminForm.submit();">
					<option value="2"<?php echo $vrc_tn->lim == 2 ? ' selected="selected"' : ''; ?>>2</option>
					<option value="5"<?php echo $vrc_tn->lim == 5 ? ' selected="selected"' : ''; ?>>5</option>
					<option value="10"<?php echo $vrc_tn->lim == 10 ? ' selected="selected"' : ''; ?>>10</option>
					<option value="20"<?php echo $vrc_tn->lim == 20 ? ' selected="selected"' : ''; ?>>20</option>
				</select>
			</td>
		</tr>
	</table>
</form>
<script type="text/Javascript">
jQuery(document).ready(function() {
	jQuery('.vrc-translation-tab').click(function() {
		var langtag = jQuery(this).attr('data-vrclang');
		if (jQuery('#vrc_langcontent_'+langtag).length) {
			jQuery('.vrc_lang').val(langtag);
			jQuery('.vrc-translation-tab').removeClass('vrc-translation-tab-default');
			jQuery(this).addClass('vrc-translation-tab-default');
			jQuery('.vrc-translation-langcontent').hide();
			jQuery('#vrc_langcontent_'+langtag).fadeIn();
		} else {
			jQuery('.vrc-translation-tab').removeClass('vrc-translation-tab-default');
			jQuery(this).addClass('vrc-translation-tab-default');
			jQuery('.vrc-translation-langcontent').hide();
			jQuery('#vrc_langcontent_ini').fadeIn();
		}
	});
<?php
if (!empty($cur_langtab)) {
	?>
	jQuery('.vrc-translation-tab').each(function() {
		var langtag = jQuery(this).attr('data-vrclang');
		if (langtag != '<?php echo $cur_langtab; ?>') {
			return true;
		}
		if (jQuery('#vrc_langcontent_'+langtag).length) {
			jQuery('.vrc_lang').val(langtag);
			jQuery('.vrc-translation-tab').removeClass('vrc-translation-tab-default');
			jQuery(this).addClass('vrc-translation-tab-default');
			jQuery('.vrc-translation-langcontent').hide();
			jQuery('#vrc_langcontent_'+langtag).fadeIn();
		}
	});
	<?php
}
?>
});
</script>
<?php
}
