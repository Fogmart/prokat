<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2015 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

defined('_JEXEC') or die;
$doc = JFactory::getDocument();
$app = JFactory::getApplication();

require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

$twofactormethods = UsersHelper::getTwoFactorMethods();

//Load Helix
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';
if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $this->helix3 = Helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

//custom css file
$custom_css_path = JPATH_ROOT . '/templates/' . $this->template . '/css/custom.css';

//Comingsoon Logo
if ($logo_image = $this->params->get('comingsoon_logo')) {
	$logo = JURI::root() . '/' .  $logo_image;
	$path = JPATH_ROOT . '/' .  $logo_image;
} else {
    $logo 		= $this->baseurl . '/templates/' . $this->template . '/images/presets/preset1/logo.png';
    $path 		= JPATH_ROOT . '/templates/' . $this->template . '/images/presets/preset1/logo.png';
    $ratlogo 	= $this->baseurl . '/templates/' . $this->template . '/images/presets/preset1/logo@2x.png';
}

if(file_exists($path)) {
	$image 		 = new JImage( $path );
	$logo_width  = $image->getWidth();
	$logo_height = $image->getHeight();
} else {
	$logo_width 	= '';
	$logo_height 	= '';
}

$comingsoon_title = $this->params->get('comingsoon_title');
if( $comingsoon_title ) {
	$doc->setTitle( $comingsoon_title . ' | ' . $app->get('sitename') );
}

$comingsoon_date = explode('-', $this->params->get("comingsoon_date"));

//Load jQuery
JHtml::_('jquery.framework');

?>
<!DOCTYPE html>
<html class="sp-comingsoon" xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    if($favicon = $this->helix3->getParam('favicon')) {
        $doc->addFavicon( JURI::base(true) . '/' .  $favicon);
    } else {
        $doc->addFavicon( $this->helix3->getTemplateUri() . '/images/favicon.ico' );
    }
	$megabgcolor   = ($this->helix3->PresetParam('_megabg')) ? $this->helix3->PresetParam('_megabg') : '#ffffff';
	$megabgtx      = ($this->helix3->PresetParam('_megatx')) ? $this->helix3->PresetParam('_megatx') : '#333333';

	$preloader_bg  = ($this->helix3->getParam('preloader_bg')) ? $this->helix3->getParam('preloader_bg') : '#f5f5f5';
	$preloader_tx  = ($this->helix3->getParam('preloader_tx')) ? $this->helix3->getParam('preloader_tx') : '#f5f5f5';
    ?>
    <jdoc:include type="head" />
    <?php
    $this->helix3->addCSS('bootstrap.min.css, font-awesome.min.css')
        ->lessInit()->setLessVariables(array(
            'preset'=>$this->helix3->Preset(),
            'bg_color'=> $this->helix3->PresetParam('_bg'),
            'text_color'=> $this->helix3->PresetParam('_text'),
            'major_color'=> $this->helix3->PresetParam('_major'),
			'megabg_color' => $megabgcolor,
			'megatx_color' => $megabgtx,
			'preloader_bg' => $preloader_bg,
			'preloader_tx' => $preloader_tx,
            ))
        ->addLess('master', 'template')
        ->addLess('presets',  'presets/'.$this->helix3->Preset())
    	->addJS('jquery.countdown.min.js');
    	// has exist custom.css then load it
    	if (file_exists($custom_css_path)) {
			 $this->helix3->addCSS('custom.css');
		}

		//background image bg_comingsoon.jpg
		$comingsoon_bg = JURI::root() . '/templates/' . $this->template . '/images/bg_comingsoon.jpg"';
		$hascs_bg = 'has-background';
		if ($cs_bg = $this->params->get('comingsoon_bg')) {
			$comingsoon_bg 	= JURI::root() . $cs_bg;
			$hascs_bg 		= 'has-background';
		}
    ?>
</head>
<body>
	<div class="sp-comingsoon-wrap <?php echo $hascs_bg; ?>" style="background-image: url(<?php echo $comingsoon_bg; ?>);">	
		<div class="container">
			<div class="text-center">
				<div id="sp-comingsoon" class="offline-inner">
					<jdoc:include type="message" />

					<div id="frame" class="outline">
						<?php if ($app->get('offline_image') && file_exists($app->get('offline_image'))) : ?>
							<img src="<?php echo $app->get('offline_image'); ?>" alt="<?php echo htmlspecialchars($app->get('sitename')); ?>" />
						<?php endif; ?>
						<h1>
							<?php echo htmlspecialchars($app->get('sitename')); ?>
						</h1>
						<?php if ($app->get('display_offline_message', 1) == 1 && str_replace(' ', '', $app->get('offline_message')) != '') : ?>
							<p>
								<?php echo $app->get('offline_message'); ?>
							</p>
						<?php elseif ($app->get('display_offline_message', 1) == 2 && str_replace(' ', '', JText::_('JOFFLINE_MESSAGE')) != '') : ?>
							<p>
								<?php echo JText::_('JOFFLINE_MESSAGE'); ?>
							</p>
						<?php endif; ?>
						<div class="form-login-wrapper">
							<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login" class="form-inline">
								<div class="form-group" id="form-login-username">
									<input name="username" id="username" type="text" class="form-control" placeholder="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" size="18" />
								</div>
								
								<div class="form-group" id="form-login-password">
									<input type="password" name="password" class="form-control" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" id="passwd" />
								</div>
								<?php if (count($twofactormethods) > 1) : ?>
								<div class="form-group" id="form-login-secretkey">
									<input type="text" name="secretkey" class="form-control" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" id="secretkey" />
								</div>
								<?php endif; ?>
								<div class="form-group" id="submit-buton">
									<input type="submit" name="Submit" class="btn btn-success login" value="<?php echo JText::_('JLOGIN'); ?>" />
								</div>

								<input type="hidden" name="option" value="com_users" />
								<input type="hidden" name="task" value="user.login" />
								<input type="hidden" name="return" value="<?php echo base64_encode(JUri::base()); ?>" />
								<?php echo JHtml::_('form.token'); ?>
							</form>
						</div>
						<?php
						//Social Icons
						$facebook 	= $this->params->get('facebook');
						$twitter  	= $this->params->get('twitter');
						$googleplus = $this->params->get('googleplus');
						$pinterest 	= $this->params->get('pinterest');
						$youtube 	= $this->params->get('youtube');
						$linkedin 	= $this->params->get('linkedin');
						$dribbble 	= $this->params->get('dribbble');
						$behance 	= $this->params->get('behance');
						$skype 		= $this->params->get('skype');
						$flickr 	= $this->params->get('flickr');
						$vk 		= $this->params->get('vk');

						if( $this->params->get('show_social_icons') && ( $facebook || $twitter || $googleplus || $pinterest ||$youtube || $linkedin || $dribbble || $behance || $skype || $flickr || $vk ) ) {
							$html  = '<ul class="social-icons">';

							if( $facebook ) {
								$html .= '<li><a class="facebook" target="_blank" href="'. $facebook .'"><i class="fa fa-facebook"></i></a></li>';
							}
							if( $twitter ) {
								$html .= '<li><a class="twitter" target="_blank" href="'. $twitter .'"><i class="fa fa-twitter"></i></a></li>';
							}
							if( $googleplus ) {
								$html .= '<li><a class="google-plus" target="_blank" href="'. $googleplus .'"><i class="fa fa-google-plus"></i></a></li>';
							}
							if( $pinterest ) {
								$html .= '<li><a class="pinterest" target="_blank" href="'. $pinterest .'"><i class="fa fa-pinterest"></i></a></li>';
							}
							if( $youtube ) {
								$html .= '<li><a class="youtube" target="_blank" href="'. $youtube .'"><i class="fa fa-youtube"></i></a></li>';
							}
							if( $linkedin ) {
								$html .= '<li><a class="linkedin" target="_blank" href="'. $linkedin .'"><i class="fa fa-linkedin"></i></a></li>';
							}
							if( $dribbble ) {
								$html .= '<li><a class="dribbble" target="_blank" href="'. $dribbble .'"><i class="fa fa-dribbble"></i></a></li>';
							}
							if( $behance ) {
								$html .= '<li><a class="behance" target="_blank" href="'. $behance .'"><i class="fa fa-behance"></i></a></li>';
							}
							if( $flickr ) {
								$html .= '<li><a class="flickr" target="_blank" href="'. $flickr .'"><i class="fa fa-flickr"></i></a></li>';
							}
							if( $vk ) {
								$html .= '<li><a class="vk" target="_blank" href="'. $vk .'"><i class="fa fa-vk"></i></a></li>';
							}
							if( $skype ) {
								$html .= '<li><a class="skype" href="skype:'. $skype .'?chat"><i class="fa fa-skype"></i></a></li>';
							}

							$html .= '<ul>';

							echo $html;
						} ?>
					</div>

				</div>
			</div>
		</div>
	</div>
</body>
</html>
