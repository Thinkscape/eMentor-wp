jQuery(function(){

	/**
	 * Build shortcode for a given set of params
	 *
	 * @param params
	 */
	var buildShortcode = function(params){
		var result = '[ementor_player';

		jQuery.each(params,function(name,val){
			result += ' '+name+'="'+val+'"';
		});

		result += ']';

		return result;
	}

	/**
	 * Build shortcode for a mediaItem (jQuery object)
	 *
	 * @param mediaItem
	 */
	var buildShortcodeForMediaItem = function(mediaItem){
		return buildShortcode({
			'mediaId' : mediaItem.attr('data-id'),
			'mediaName' : mediaItem.attr('data-name')
//			'preset' : 'basic'
		});
	}


	/**
	 * Show details button
	 */
	jQuery('.describe-toggle-on').click(function(){
		jQuery(this)
			.hide()
			.siblings('table.describe').show();
		jQuery(this).siblings('.describe-toggle-off').show();
		return false;
	});
	jQuery('.emt-media-items .media-item .filename').click(function(e){
		var table = jQuery(this).siblings('table.describe');
		if(table.is(':visible')){
			table.hide();
			jQuery(this).siblings('.describe-toggle-on').show();
			jQuery(this).siblings('.describe-toggle-off').hide();
		}else{
			table.show();
			jQuery(this).siblings('.describe-toggle-on').hide();
			jQuery(this).siblings('.describe-toggle-off').show();
		}
	});

	/**
	 * Hide details button
	 */
	jQuery('.describe-toggle-off').click(function(){
		jQuery(this)
			.hide()
			.siblings('table.describe').hide();
		jQuery(this).siblings('.describe-toggle-on').show();
		return false;
	});

	/**
	 * Insert media button
	 */
	jQuery('input[name="insert-media"]').click(function(){
		var mediaItem = jQuery(this).parents('.media-item');
		var code = buildShortcodeForMediaItem(mediaItem);
		var win = window.dialogArguments || opener || parent || top;
		win.send_to_editor(code);
	});

	/**
	 * Submit form on filter change
	 */
	jQuery('#wp-ementor-mediaTab-productId').change(function(){
		window.location.href = jQuery(this).find('option:selected').attr('ref');
	});

	/**
	 * Handle checkboxes
	 */
	jQuery('.emt-media-items .media-item .insert-media-checkbox').click(function(e){
		if(e.target !== this) return;

		var checkbox = jQuery(this).children('input');
		var mediaItem = jQuery(this).parents('.media-item');

		if(checkbox.is(':checked')){
			checkbox.removeAttr('checked');
			mediaItem.removeClass('selected');
		}else{
			checkbox.attr('checked',true);
			mediaItem.addClass('selected');
		}
	});

	jQuery('.emt-media-items .media-item .insert-media-checkbox input').change(function(e){
		var checkbox = jQuery(this);
		var mediaItem = jQuery(this).parents('.media-item');

		if(checkbox.is(':checked')){
			mediaItem.addClass('selected');
		}else{
			mediaItem.removeClass('selected');
		}
	});


	/**
	 * Handle insert all media button
	 */
	jQuery('#insert-media-all').click(function(){
		var selected = jQuery('.emt-media-items .media-item .insert-media-checkbox input:checked');
		if(selected.length < 1){
			alert('Nic nie wybrałeś. Zaznacz kilka pozycji z powyższej listy i kliknij ten przycisk ponownie...');
			return;
		}

		var result = [];
		jQuery.each(selected,function(x,check){
			var mediaItem = jQuery(check).parents('.media-item');
			var code = buildShortcodeForMediaItem(mediaItem);
			result.push(code);
		});

		result = result.join("<br/>");

		var win = window.dialogArguments || opener || parent || top;
		win.send_to_editor(result);

	});

});