var $jq=jQuery.noConflict();

function viewMode(mode) {
	jQuery('.view-mode').find('a').removeClass('btn-primary');
	if(mode == 'list') {	
		jQuery('.product.vm-col').addClass('vm-products-horizon');		
		jQuery('.view-mode').find('a.mode-list').addClass('btn-primary');
	} else {	
		jQuery('.product.vm-col').removeClass('vm-products-horizon');	
		jQuery('.view-mode').find('a.mode-grid').addClass('btn-primary');
	}
}

jQuery(function($) {
	$('.vinaTabs').vinatab({
		animation: 'fade',
		duration: 400,
		transition: 'linear',
		btnPos: 'top',
		activator: 'click'
	});
});


jQuery(document).ready(function($){
	
	if( $("#sp-left.col-md-3").length ){
		$("#sp-component.col-md-9").addClass("col-md-push-3");
		$("#sp-left.col-md-3").addClass("col-md-pull-9");
	}

	if( $(".vina_slideToggle .modtitle").length ){		
		$(".vina_slideToggle .modtitle").on('click', function() {
			$('.vina_slideToggle .sp-module-content').slideToggle("show");
			
		});		
	}
	if( $(".search-dropdown .modtitle").length ){		
		$(".search-dropdown .modtitle").on('click', function() {
			$('.search-dropdown .sp-vmsearch-content').slideToggle("show");
		});		
	}
	if( $(".search-dropdown .modtitle").length ){		
		$(".search-dropdown .modtitle").on('click', function() {
			$('.search-dropdown .sp-module-content').slideToggle("show");
			$('.search-dropdown .zmdi').toggleClass("zmdi-close");
			$('.search-dropdown .zmdi').toggleClass("zmdi-search");
		});		
	}
	if( $(".vm_category_menu2 .sp-module-title").length ){
		$(".vm_category_menu2 .sp-module-title").on('click', function() {
			$('.vm_category_menu2 .sp-module-content').slideToggle("show");
		});		
	}
	/* Fix conflict MooTools and Bootstrap */
	var bootstrapLoaded = (typeof $().carousel == 'function');
	var mootoolsLoaded = (typeof MooTools != 'undefined');
	if (bootstrapLoaded && mootoolsLoaded) {
		Element.implement({
			hide: function () {
				return this;
			},
			show: function (v) {
				return this;
			},
			slide: function (v) {
				return this;
			}
		});
	}
	
	/*Fix Carousel
	if( $(".carousel").length ){
		$(".carousel").each( function() {
			$(this).parent().addClass("wrap-carousel");
		});
	}*/

});

