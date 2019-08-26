
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @subpackage DJ-MediaTools galleryGrid layout
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 */

this.DJOptionsSwitcher = new Class({
	
	select: null,
	optionFields: [],
	
	initialize: function(select) {
		
		this.select = select;
		var parent = this.select.getParent('li, .control-group');
		
		var name = this.select.get('name').replace(']','','g');
		var parts = name.split('[');
			
		var control = name.replace(parts.getLast(),'').replace('[','_','g');
		
		var options = this.select.getElements('option');
		
		if ((options.length == 0)) {
			return;
		}
		
		options.each(function(option, index){
			var elements = option.value.split(';');
			if (elements.length > 0) {
				var value = elements[0];
				this.optionFields[index] = [];
				this.optionFields[index][0] = value;
				for (var i = 1; i < elements.length; i++) {
					this.optionFields[index][i] = elements[i];
				}
			}
		}.bind(this));
		
		parent.addEvent('click', function(){
			
			var elements = this.select.value.split(';');
			var selected = null;
			
			//console.log(this.optionFields);
			for(var index = 0; index < this.optionFields.length; index++) {
				
				if(elements[0] == this.optionFields[index][0]) selected = index;
				
				if(this.optionFields[index].length > 1) for (var i = 1; i < this.optionFields[index].length; i++) {
					var inputId = document.id(control + this.optionFields[index][i]);
					var labelId = document.id(control + this.optionFields[index][i] + '-lbl');
					if (inputId) {
						inputId.getParent('li, .control-group').setStyle('display', 'none');
					} else if (labelId) {
						labelId.getParent('li, .control-group').setStyle('display', 'none');
					}
				}
					
			}
			
			if(elements.length > 1) for (var i = 1; i < this.optionFields[selected].length; i++) {
				var inputId = document.id(control + this.optionFields[selected][i]);
				var labelId = document.id(control + this.optionFields[selected][i] + '-lbl');
				if (inputId) {
					inputId.getParent('li, .control-group').setStyle('display', 'block');
				} else if (labelId) {
					labelId.getParent('li, .control-group').setStyle('display', 'block');
				}
			}
			
		}.bind(this, control));
		
		parent.fireEvent('click');
	}	
});

window.addEvent('domready',function(){
	
	$$('.djoptionswitcher').each(function(list){ new DJOptionsSwitcher(list); });
	
});