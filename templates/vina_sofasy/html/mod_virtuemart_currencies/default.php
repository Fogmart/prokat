<?php // no direct access
defined('_JEXEC') or die('Restricted access');
vmJsApi::jQuery();
vmJsApi::chosenDropDowns();

$this_name_form = 'user_mode'.$module->id; 
$class = "class='inputbox selectpicker virtuemart_currency' OnChange='".$this_name_form.".submit();return false;'";	
?>

<!-- Currency Selector Module -->
<?php echo $text_before ?>

<form action="<?php echo vmURI::getCleanUrl() ?>" name="user_mode<?php echo $module->id; ?>" method="post">

	<!--<br />
    <input class="button" type="submit" name="submit" value="<?php echo vmText::_('MOD_VIRTUEMART_CURRENCIES_CHANGE_CURRENCIES') ?>" />
	<br />-->
	<?php echo JHTML::_('select.genericlist', $currencies, 'virtuemart_currency_id', $class, 'virtuemart_currency_id', 'currency_txt', $virtuemart_currency_id) ; ?>
</form>
