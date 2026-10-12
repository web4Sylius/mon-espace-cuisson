/**
 * jQuery NiceMultiSelect is a jquery plugin in javascript to render multi select 
 * a better way and more user friendly
 * Copyright (C) 2012 Julien LA VINH
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * jQuery NiceMultiSelect version 1, Copyright (C) 2012 Julien LA VINH
 * jQuery NiceMultiSelect comes with ABSOLUTELY NO WARRANTY; for details
 * type `show w'.  This is free software, and you are welcome
 * to redistribute it under certain conditions; please read the  webpage 
 * http://www.gnu.org/licenses/gpl-2.0.html for more details.
 */
 (function($) {
	// The object
  $.niceMultiSelect = function(element, options) {
  	// The default options
    var defaults = {
      lang_choose: 'Click to add...'
    }
    
    var niceMultiSelect = this;
    niceMultiSelect.settings = {}
    
    var $element = $(element),
         element = element;
		
		// Private vars
		var $wrapper;
		var $list;
		var $input;
		var $settings;
		
		// Constructor
    niceMultiSelect.init = function() {
    	// Load settings
      $settings = $.extend({}, defaults, options);
			
      // Add wrapper
      $element.hide().wrap('<div class="multiselect-wrapper" />');
			$wrapper = $element.parent('.multiselect-wrapper');
			
			// Hide the source select
			$wrapper.hide();
			
			// Add select list
			$wrapper.append('<ul />');
			$list = $wrapper.find('ul');
			adjustListPosition();
			initListSelection();
			
			// Add message to add element
			addChooseText();
			
			$list.find('li').click(function() {
				selectElement($(this));
			});
			
			// call private method
			$wrapper.click(function() {
				onFocus();
			}).mouseleave(function() {
				onLeave();
			});
			
			// Display the new select
			$wrapper.show();
    }

		// Method when focus on the wrapper
		var onFocus = function() {
			$wrapper.find('.textadd').remove();
			adjustListPosition();
			$wrapper.find('ul').slideDown(100);
		};
		// When leaving the wrapper
		var onLeave = function () {
			// Hide the select list
			$list.slideUp(100);
			addChooseText();
		};
		
		// Action when a list element is click
		var selectElement = function($li) {
			// Mark it as selected
			var valToCheck = $li.attr('rel');
			$element.find('option').each(function() {
				if($(this).val()==valToCheck) {
					$(this).attr('selected', 'selected');
					
					// Hide it from the list
					$li.slideUp(100);
					// Add it to the displayed list
					$wrapper.append('<span class="choice new" rel="'+valToCheck+'" style="display:none">'+$li.html()+'</span>');
					$wrapper.find('span.new').fadeIn('fast', function() {
						$(this).removeClass('new');
					});
					$wrapper.find('span').click(function(e) {
						e.preventDefault();
						removeChoice($(this));
					});
					
					// Move the list to the good height if needed
					adjustListPosition();
					return;
				}
			});
		};
		
		// Method to remove an element from the list
		var removeChoice = function ($choice) {
			var valToRemove = $choice.attr('rel');
			// Remove from selected
			$element.find('option:selected').each(function() {
				if($(this).val()==valToRemove) {
					$(this).removeAttr('selected');
				}
			});
			// Display it back in the list
			$list.find('li[rel="'+valToRemove+'"]').show();
			// Remove item
			$choice.fadeOut('fast', function() {
				$(this).remove();
			});
		}
		
		// Add the "choose text" in the div
    var addChooseText = function() {
    	// Make sure the text is removed
    	$wrapper.find('.textadd').remove();
    	// Add it
      $wrapper.append('<span class="textadd">' + $settings.lang_choose + '</span>');
    };
    
    // Adjust the list position to match the bottom of the wrapper
		var adjustListPosition = function() {
			$list
				.css('top', parseInt($wrapper.height()) + parseInt($wrapper.css('paddingTop')) + parseInt($wrapper.css('paddingBottom')) )
				.width(parseInt($wrapper.width()) + parseInt($wrapper.css('paddingLeft')) + parseInt($wrapper.css('paddingRight')));
		}
		
		// Init the list selection from the select option to the select ul list
		var initListSelection = function() {
			// Fill the list
			$element.find('option').each(function() {
				var $li = $('<li />')
					.attr('rel', $(this).val())
					.html($(this).html());
				$list.append($li);
				if($(this).attr('selected')=='selected') {
					selectElement($li);
					$li.hide();
				}
			});
		}
		
		// Init the plugin
    niceMultiSelect.init();
  }

	// Make the object a callable jquery plugin
  $.fn.niceMultiSelect = function(options) {
    return this.each(function() {
      if (undefined == $(this).data('niceMultiSelect')) {
        var niceMultiSelect = new $.niceMultiSelect(this, options);
        $(this).data('niceMultiSelect', niceMultiSelect);
      }
    });
  }
})(jQuery);
