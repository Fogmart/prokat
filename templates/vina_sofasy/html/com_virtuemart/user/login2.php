<?php
/**
*
* Layout for the login
*
* @package	VirtueMart
* @subpackage User
* @author Max Milbers, George Kostopoulos
*
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: cart.php 4431 2011-10-17 grtrustme $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//set variables, usually set by shopfunctionsf::getLoginForm in case this layout is differently used
if (!isset( $this->show )) $this->show = TRUE;
if (!isset( $this->from_cart )) $this->from_cart = FALSE;
if (!isset( $this->order )) $this->order = FALSE ;

if (empty($this->url)){
	$url = vmURI::getCleanUrl();
} else{
	$url = $this->url;
}

$user = JFactory::getUser();

if ($this->show and $user->id == 0  ) {
	JHtml::_('behavior.formvalidation');
	JHtml::_ ( 'behavior.modal' );
	//$uri = JFactory::getURI();
	//$url = $uri->toString(array('path', 'query', 'fragment'));

	//Extra login stuff, systems like openId and plugins HERE
    if (JPluginHelper::isEnabled('authentication', 'openid')) {
        $lang = JFactory::getLanguage();
        $lang->load('plg_authentication_openid', JPATH_ADMINISTRATOR);
        $langScript = '
		//<![CDATA[
		'.'var JLanguage = {};' .
						' JLanguage.WHAT_IS_OPENID = \'' . vmText::_('WHAT_IS_OPENID') . '\';' .
						' JLanguage.LOGIN_WITH_OPENID = \'' . vmText::_('LOGIN_WITH_OPENID') . '\';' .
						' JLanguage.NORMAL_LOGIN = \'' . vmText::_('NORMAL_LOGIN') . '\';' .
						' var comlogin = 1;
		//]]>
                ';
		vmJsApi::addJScript('login_openid',$langScript);
        JHtml::_('script', 'openid.js');
    }

    $html = '';
    JPluginHelper::importPlugin('vmpayment');
    $dispatcher = JDispatcher::getInstance();
    $returnValues = $dispatcher->trigger('plgVmDisplayLogin', array($this, &$html, $this->from_cart));

    if (is_array($html)) {
		foreach ($html as $login) {
		    echo $login.'<br />';
		}
    }
    else {
		echo $html;
    }
    //end plugins section

    //anonymous order section
    if ($this->order  ) { ?>
	    <div class="order-view">
			<h2><?php echo vmText::_('COM_VIRTUEMART_ORDER_ANONYMOUS') ?></h2>
			<form action="<?php echo JRoute::_( 'index.php', 1, $this->useSSL); ?>" method="post" name="com-login" >
				<div class="width30 floatleft" id="com-form-order-number">
					<label for="order_number"><?php echo vmText::_('COM_VIRTUEMART_ORDER_NUMBER') ?></label><br />
					<input type="text" id="order_number" name="order_number" class="inputbox" size="18" />
				</div>
				<div class="width30 floatleft" id="com-form-order-pass">
					<label for="order_pass"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PASS') ?></label><br />
					<input type="text" id="order_pass" name="order_pass" class="inputbox" size="18" value="p_"/>
				</div>
				<div class="width30 floatleft" id="com-form-order-submit">
					<input type="submit" name="Submitbuton" class="button" value="<?php echo vmText::_('COM_VIRTUEMART_ORDER_BUTTON_VIEW') ?>" />
				</div>
				<div class="clr"></div>
				<input type="hidden" name="option" value="com_virtuemart" />
				<input type="hidden" name="view" value="orders" />
				<input type="hidden" name="layout" value="details" />
				<input type="hidden" name="return" value="" />
			</form>
	    </div>
	<?php } ?>

	<?php // XXX style CSS id com-form-login ?>
	<div class = "vmshop-account account-login">		
		<h1 class="page-header header">
			<span><?php echo vmText::_('COM_VIRTUEMART_LOGIN'); ?></span>
		</h1>
		<div class="row-set row">
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 new-users">
				<div class="content">
					<h2><?php echo JText::_('VM_LANG_HAVE_NOT_ACCOUNT'); ?></h2>
					<p><?php echo JText::_('VM_LANG_REGISTER_DES'); ?></p>
				</div>				
				<div class="buttons-set">
					<button type="button" title="<?php echo JText::_('VM_LANG_REGISTRATION'); ?>" class="vina-button" onclick="window.location='<?php echo JRoute::_('index.php?option=com_virtuemart&view=user&task=edit', FALSE); ?>'"><span><span><?php echo JText::_('VM_LANG_REGISTRATION'); ?></span></span></button>
				</div>				
			</div>
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 login-users">
				<form id="com-form-login" action="<?php echo JRoute::_('index.php', $this->useXHTML, $this->useSSL); ?>" method="post" name="com-login" >
					<fieldset class="userdata content block-border">				
						<h2><?php echo JText::_('VM_LANG_HAVE_ACCOUNT'); ?></h2>
						<p><?php echo vmText::_('COM_VIRTUEMART_ORDER_CONNECT_FORM').':'; ?></p>
						<ul class="form-list">
							<li>
								<label class="required"><em>*</em><?php echo vmText::_('COM_VIRTUEMART_USERNAME'); ?>:</label>
								<div id="com-form-login-username" class="input-box">
									<input type="text" name="username" class="inputbox" size="18" value="<?php echo vmText::_('COM_VIRTUEMART_USERNAME'); ?>" onblur="if(this.value=='') this.value='<?php echo addslashes(vmText::_('COM_VIRTUEMART_USERNAME')); ?>';" onfocus="if(this.value=='<?php echo addslashes(vmText::_('COM_VIRTUEMART_USERNAME')); ?>') this.value='';" />
								</div>
							</li>
							<li>
								<label class="required"><em>*</em><?php echo vmText::_('COM_VIRTUEMART_PASSWORD'); ?>:</label>
								<div id="com-form-login-password" class="input-box">
									<input id="modlgn-passwd" type="password" name="password" class="inputbox" size="18" value="<?php echo vmText::_('COM_VIRTUEMART_PASSWORD'); ?>" onblur="if(this.value=='') this.value='<?php echo addslashes(vmText::_('COM_VIRTUEMART_PASSWORD')); ?>';" onfocus="if(this.value=='<?php echo addslashes(vmText::_('COM_VIRTUEMART_PASSWORD')); ?>') this.value='';" />
								</div>
							</li>
							<li id="com-form-login-remember">
								<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>								
									<input type="checkbox" id="remember" name="remember" class="inputbox" value="yes" />
									<label for="remember"><?php echo $remember_me = vmText::_('JGLOBAL_REMEMBER_ME') ?></label>
								<?php endif; ?>
							</li>
						</ul>					
					</fieldset>
					<div class="buttons-set">
						<input type="submit" name="Submit" class="default width30" value="<?php echo vmText::_('COM_VIRTUEMART_LOGIN'); ?>" />
						<div class="floatleft">
							<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>" rel="nofollow">
							<?php echo vmText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_USERNAME'); ?></a>
						</div>
						<div class="floatleft">
							<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>" rel="nofollow">
							<?php echo vmText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_PASSWORD'); ?></a>
						</div>
					</div>
					<div class="clr"></div>
					
					<input type="hidden" name="task" value="user.login" />
					<input type="hidden" name="option" value="com_users" />
					<input type="hidden" name="return" value="<?php echo base64_encode($url) ?>" />
					<?php echo JHtml::_('form.token'); ?>
				</form>					
			</div>
		</div>
	</div>
<!-- Logout -->
<?php } else if ( $user->id ) { ?>
	<div class="row row-set logout-users">
		<div class="col-md-12">
			<div class="content block-border">
				<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="login" id="form-login">
					<?php echo vmText::sprintf( 'COM_VIRTUEMART_HINAME', $user->name ); ?>					
					<input type="submit" name="Submit" class="btn-logout button" value="<?php echo vmText::_( 'COM_VIRTUEMART_BUTTON_LOGOUT'); ?>" />
					<input type="hidden" name="option" value="com_users" />
					<input type="hidden" name="task" value="user.logout" />
					<?php echo JHtml::_('form.token'); ?>
					<input type="hidden" name="return" value="<?php echo base64_encode($url) ?>" />
				</form>
			</div>
		</div>
	</div>
<?php } ?>	

