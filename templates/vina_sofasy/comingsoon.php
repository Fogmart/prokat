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
    	->addJS('jquery.countdown.min.js, owl.carousel.min.js');
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
				<div id="sp-comingsoon">
					<jdoc:include type="message" />
					<div class="comingsoon-page-logo">
						<?php if($comingsoon_logo = $this->params->get('comingsoon_logo')){ ?>
							<img class="comingsoon-logo" alt="logo" src="<?php echo $logo; ?>" />
						<?php } else { ?>
							<img class="sp-default-logo comingsoon-logo" alt="logo" src="<?php echo $logo; ?>" />
							<img class="sp-retina-logo comingsoon-logo" alt="logo" src="<?php echo $ratlogo; ?>" width="<?php echo $logo_width; ?>" height="<?php echo  $logo_height; ?>" />
						<?php }?>
					</div>

					<?php if( $comingsoon_title ) { ?>
						<h1 class="sp-comingsoon-title">
							<?php echo $comingsoon_title; ?>
						</h1>
					<?php } ?>

					<?php if( $this->params->get('comingsoon_content') ) { ?>
						<div class="sp-comingsoon-content">
							<?php echo $this->params->get('comingsoon_content'); ?>
						</div>
					<?php } ?>

					<div id="sp-comingsoon-countdown" class="sp-comingsoon-countdown"></div>
					
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
						
					<?php if($this->countModules('comingsoon')) { ?>
					<div class="sp-position-comingsoon">
						<jdoc:include type="modules" name="comingsoon" style="sp_xhtml" />
					</div>
					<?php } ?>

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

					if( $this->params->get('show_social_icons') && ( $facebook || $twitter || $googleplus || $pinterest || $youtube || $linkedin || $dribbble || $behance || $skype || $flickr || $vk ) ) {
						$html  = '<ul class="social-icons hi-icon-wrap hi-icon-effect-8">';

						if( $facebook ) {
							$html .= '<li><a target="_blank" href="'. $facebook .'"><i class="hi-icon fa fa-facebook"></i></a></li>';
						}
						if( $twitter ) {
							$html .= '<li><a target="_blank" href="'. $twitter .'"><i class="hi-icon fa fa-twitter"></i></a></li>';
						}
						if( $googleplus ) {
							$html .= '<li><a target="_blank" href="'. $googleplus .'"><i class="hi-icon fa fa-google-plus"></i></a></li>';
						}
						if( $pinterest ) {
							$html .= '<li><a target="_blank" href="'. $pinterest .'"><i class="hi-icon fa fa-pinterest"></i></a></li>';
						}
						if( $youtube ) {
							$html .= '<li><a target="_blank" href="'. $youtube .'"><i class="hi-icon fa fa-youtube"></i></a></li>';
						}
						if( $linkedin ) {
							$html .= '<li><a target="_blank" href="'. $linkedin .'"><i class="hi-icon fa fa-linkedin"></i></a></li>';
						}
						if( $dribbble ) {
							$html .= '<li><a target="_blank" href="'. $dribbble .'"><i class="hi-icon fa fa-dribbble"></i></a></li>';
						}
						if( $behance ) {
							$html .= '<li><a target="_blank" href="'. $behance .'"><i class="hi-icon fa fa-behance"></i></a></li>';
						}
						if( $flickr ) {
							$html .= '<li><a target="_blank" href="'. $flickr .'"><i class="hi-icon fa fa-flickr"></i></a></li>';
						}
						if( $vk ) {
							$html .= '<li><a target="_blank" href="'. $vk .'"><i class="hi-icon fa fa-vk"></i></a></li>';
						}
						if( $skype ) {
							$html .= '<li><a href="skype:'. $skype .'?chat"><i class="hi-icon hi-icon fa fa-skype"></i></a></li>';
						}

						$html .= '</ul>';
						

						echo $html;
					}

					?>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">

		jQuery(function($) {
			$('#sp-comingsoon-countdown').countdown('<?php echo trim($comingsoon_date[2]); ?>/<?php echo trim($comingsoon_date[1]); ?>/<?php echo trim($comingsoon_date[0]); ?>', function(event) {
			    $(this).html(event.strftime('<div class="days"><span class="number">%-D</span><span class="string">%!D:<?php echo JText::_("HELIX_DAY"); ?>,<?php echo JText::_("HELIX_DAYS"); ?>;</span></div><div class="hours"><span class="number">%H</span><span class="string">%!H:<?php echo JText::_("HELIX_HOUR"); ?>,<?php echo JText::_("HELIX_HOURS"); ?>;</span></div><div class="minutes"><span class="number">%M</span><span class="string">%!M:<?php echo JText::_("HELIX_MINUTE"); ?>,<?php echo JText::_("HELIX_MINUTES"); ?>;</span></div><div class="seconds"><span class="number">%S</span><span class="string">%!S:<?php echo JText::_("HELIX_SECOND"); ?>,<?php echo JText::_("HELIX_SECONDS"); ?>;</span></div>'));
			});
		});

	</script>

</body>
</html>