jQuery(function($) {

	// ================== new meta form creator ===================

	var meta_removed;
	
	//attaching hide and delete events for existing meta data
	$("#form-meta-setting ul li").each(function(i, item){
		$(item).find(".ui-icon-carat-2-n-s").click(function(e) {
			$(item).find("table").slideToggle(300);
		});
		// for delete box
		$(item).find(".ui-icon-trash").click(function(e) {
			$("#remove-meta-confirm").dialog("open");
			meta_removed = $(item);
		});
		// for copy box
		$(item).find(".ui-icon-copy").click(function(e) {
			$(item).clone().insertAfter(item);
		});	
	});
	
	$('.ui-icon-circle-triangle-n').click(function(e){
		$("#form-meta-setting ul li").find('table').slideUp();
	});
	$('.ui-icon-circle-triangle-s').click(function(e){
		$("#form-meta-setting ul li").find('table').slideDown();
	});
	
	
	$("#nmpersonalizedproduct-form-generator").tabs();
	$("#tab-container").tabs()

	$("#form-meta-setting ul").sortable({
		revert : true,
		stop : function(event, ui) {
			// console.log(ui);

			// only attach click event when dropped from right panel
			if (ui.originalPosition.left > 20) {
				$(ui.item).find(".ui-icon-carat-2-n-s").click(function(e) {
					$(this).parent('.postbox').find("table").slideToggle(300);
				});

				// for delete box
				$(ui.item).find(".ui-icon-trash").click(function(e) {
					$("#remove-meta-confirm").dialog("open");
					meta_removed = $(ui.item);
				});
				
				// for copy box
				$(ui.item).find(".ui-icon-copy").click(function(e) {
					$(ui.item).clone().insertAfter(ui.item);
				});
			}
		}
	});

	// =========== remove dialog ===========
	$("#remove-meta-confirm").dialog({
		resizable : false,
		height : 160,
		autoOpen : false,
		modal : true,
		buttons : {
			"Remove" : function() {
				$(this).dialog("close");
				meta_removed.remove();
			},
			Cancel : function() {
				$(this).dialog("close");
			}
		}
	});

	$("#nm-input-types li").draggable(
			{
				connectToSortable : "#form-meta-setting ul",
				helper : "clone",
				revert : "invalid",
				start: function(event, ui){
					$("#form-meta-setting ul li").find('table').slideUp();
					ui.helper.width('100%');
					ui.helper.height('auto');
				},
				stop : function(event, ui) {
					// console.log($('.ui-draggable'));

					$('.ui-sortable .ui-draggable').removeClass(
							'input-type-item').find('div').addClass('postbox');

					// now replacing the icons with arrow
					$('.postbox').find('.ui-icon-arrow-4').removeClass(
							'ui-icon-arrow-4')
							.addClass('ui-icon-carat-2-n-s')
							.attr('title', 'Slide Up/Down');
					$('.postbox').find('.ui-icon-placehorder').removeClass(
							'ui-icon-placehorder').addClass(
							'ui-icon ui-icon-trash')
							.attr('title', 'Remove');
				$('.postbox').find('.ui-icon-placehorder-copy').removeClass(
							'ui-icon-placehorder-copy').addClass(
							'ui-icon ui-icon-copy')
							.attr('title', 'Copy');

				}
			});
	//$("ul, li").disableSelection();

	// ================== new meta form creator ===================

	// add validation message if required
	$('input:checkbox[name="meta-required"]').change(function() {

		if ($(this).is(':checked')) {
			$(this).parent().find('span').show();
		} else {
			$(this).parent().find('span').hide();
		}
	});

	// increaing/saming the width of section's element
	$(".the-section").find('input, select, textarea').css({
		'width' : '35%'
	});

	$("#form-meta-setting img.add_rule").live("click", function(){
		
		var $div    = $(this).closest('div');
		var $clone = $div.clone();
		$clone.find('strong').val('Rule just added');
		
		var $td = $div.closest('td');
		$td.append($clone);
	});
	
	$("#form-meta-setting img.remove_rule").live("click", function(){
		
		var $div    = $(this).closest('div');
		var $td = $div.closest('td');
		if($td.find('div').length > 1)
			$div.remove();
		else
			alert('Not allowed');
	});
	
	/* ============= new options / remove options =============== */
	$("#form-meta-setting img.add_option").live("click", function(){
			
			var $div    = $(this).closest('div');
			var $clone = $div.clone();
			// $clone.find('strong').val('Rule just added');
			
			var $td = $div.closest('td');
			$td.append($clone);
	});
	
	$("#form-meta-setting img.remove_option").live("click", function(){
		
		var $div    = $(this).closest('div');
		var $td = $div.closest('td');
		if($td.find('div').length > 1)
			$div.remove();
		else
			alert('Not allowed');
	});
	
	// making table sortable
	// make table rows sortable
	$('#nm-file-meta-admin tbody').sortable(
			{
				start : function(event, ui) {
					// fix firefox position issue when dragging.
					if (navigator.userAgent.toLowerCase().match(/firefox/)
							&& ui.helper !== undefined) {
						ui.helper.css('position', 'absolute').css('margin-top',
								$(window).scrollTop());
						// wire up event that changes the margin whenever the
						// window scrolls.
						$(window).bind(
								'scroll.sortableplaylist',
								function() {
									ui.helper.css('position', 'absolute')
											.css('margin-top',
													$(window).scrollTop());
								});
					}
				},
				beforeStop : function(event, ui) {
					// undo the firefox fix.
					if (navigator.userAgent.toLowerCase().match(/firefox/)
							&& ui.offset !== undefined) {
						$(window).unbind('scroll.sortableplaylist');
						ui.helper.css('margin-top', 0);
					}
				},
				helper : function(e, ui) {
					ui.children().each(function() {
						$(this).width($(this).width());
					});
					return ui;
				},
				scroll : true,
				stop : function(event, ui) {
					// SAVE YOUR SORT ORDER
				}
			}).disableSelection();
	
	
	// condtions handling
	populate_conditional_elements();
	
	
	
/* ============== pre uploaded images 1- Media uploader launcher ================= */
	
	var $uploaded_image_container;
	
	$('input:button[name="pre_upload_image_button"]').live('click', function(){
		
		$uploaded_image_container = $(this).closest('div');
		
		wp.media.editor.send.attachment = function(props, attachment)
		{
			var existing_images;
			var fileurl = attachment.url;
			var fileid	= attachment.id;
			
			if(fileurl){
	        	var image_box 	 = '<table>';
	        	image_box 		+= '<tr>';
	        	image_box 		+= '<td><img width="75" src="'+fileurl+'"></td>';
	        	image_box 		+= '<input type="hidden" name="pre-upload-link" value="'+fileurl+'">';
	        	image_box 		+= '<input type="hidden" name="pre-upload-id" value="'+fileid+'">';
	        	image_box 		+= '<td><input placeholder="title" style="width:100px" type="text" name="pre-upload-title"><br>';
	        	image_box 		+= '<input placeholder="price" style="width:100px" type="text" name="pre-upload-price"><br>';
	        	image_box 		+= '<input style="width:100px; color:red" name="pre-upload-delete" type="button" class="button" value="Delete"><br>';
	        	image_box 		+= '</td></tr>';
	        	image_box 		+= '</table><br>';
	        	
	        	$uploaded_image_container.append(image_box);
	        	//console.log(image_box);
        }
		}
		
		wp.media.editor.open(this);
		
		return false;
	});
    
    $('input:button[name="pre-upload-delete"]').live('click', function(){
    	
    	$(this).closest('table').remove();
    });
    
    /* ================== auto generate data name field ============= */
    $("#form-meta-setting").find('input[name="data_name"]').live('focus', function(){
    
    	var dataname = $(this).closest('table').find('input[name="title"]').val().replace(/[^A-Z0-9]/ig, "_");
    	dataname = dataname.toLowerCase();
   		$(this).val( dataname );
    });
    
    
    
});

// saving form meta
function save_form_meta() {

	jQuery("#nm-saving-form").show();
	
	
	//usetting the photo_editing option is api key is not set
	if(jQuery('input[name="aviary_api_key"]').val() === "")
		jQuery('input[name="photo_editing"]').attr('checked',false);
	
	var product_meta_values = new Array();		//{};		//Array();
	jQuery("#meta-input-holder li").each(
			function(i, item) {

				var inner_array = {};
				inner_array['type']	= jQuery(item).attr('data-inputtype');
				
				jQuery(this).find('td.table-column-input').each(
						function(i, col) {

							var meta_input_type = jQuery(col).attr('data-type');
							var meta_input_name = jQuery(col).attr('data-name');
							var cb_value = '';

							if(meta_input_type == 'checkbox'){
								if(meta_input_name === 'editing_tools'){
									cb_value = (jQuery(this).find('input:checkbox[name="' + meta_input_name + '[]"]:checked').serialize() === undefined ? '' : jQuery(this).find('input:checkbox[name="' + meta_input_name + '[]"]:checked').serialize());
									inner_array[meta_input_name] = cb_value;
								}else{
									cb_value = (jQuery(this).find('input:checkbox[name="' + meta_input_name + '"]:checked').val() === undefined ? '' : jQuery(this).find('input:checkbox[name="' + meta_input_name + '"]:checked').val());
									inner_array[meta_input_name] = cb_value;
								}
							}else if(meta_input_type == 'textarea'){
								inner_array[meta_input_name] = jQuery(this).find('textarea[name="' + meta_input_name + '"]').val();
							}else if(meta_input_type == 'select'){
								inner_array[meta_input_name] = jQuery(this).find('select[name="' + meta_input_name + '"]').val();
							}else if (meta_input_type == 'html-conditions') {
								
								var all_conditions = {};
								var the_conditions = new Array();	//{};
								
								all_conditions['visibility'] = jQuery(
										this)
										.find(
												'select[name="condition_visibility"]')
										.val();
								all_conditions['bound'] = jQuery(
										this)
										.find(
												'select[name="condition_bound"]')
										.val();
								jQuery(this).find('div.webcontact-rules').each(function(i, div_box){
								
									var the_rule = {};
									
									the_rule['elements'] = jQuery(
											this)
											.find(
													'select[name="condition_elements"]')
											.val();
									the_rule['operators'] = jQuery(
											this)
											.find(
													'select[name="condition_operators"]')
											.val();
									the_rule['element_values'] = jQuery(
											this)
											.find(
													'select[name="condition_element_values"]')
											.val();
									
									the_conditions.push(the_rule);
								});
								
								all_conditions['rules'] = the_conditions;
								inner_array[meta_input_name] = all_conditions;
							}else if (meta_input_type == 'pre-images') {
								
								var all_preuploads = new Array();
								jQuery(this).find('div.pre-upload-box table').each(function(i, preupload_box){
									var pre_upload_obj = {	
											link: jQuery(preupload_box).find('input[name="pre-upload-link"]').val(),
											id: jQuery(preupload_box).find('input[name="pre-upload-id"]').val(),
											title: jQuery(preupload_box).find('input[name="pre-upload-title"]').val(),
											price: jQuery(preupload_box).find('input[name="pre-upload-price"]').val(),};
									
									all_preuploads.push(pre_upload_obj);
								});
								
								inner_array['images'] = all_preuploads;
							}else if (meta_input_type == 'paired') {
								
								var all_options = new Array();
								jQuery(this).find('div.data-options').each(function(i, option_box){
									var option_set = {	option: jQuery(option_box).find('input[name="options[option]"]').val(),
														price: jQuery(option_box).find('input[name="options[price]"]').val(),};
									
									all_options.push(option_set);
								});
								
								inner_array['options'] = all_options;
							} else {
								inner_array[meta_input_name] = jQuery.trim(jQuery(this).find('input[name="'+ meta_input_name+ '"]').val())
								// inner_array.push(temp);
							}
							
						});

				product_meta_values.push( inner_array );

			});
	

	//console.log(product_meta_values); return false;
	// ok data is collected, so send it to server now Huh?

	var productmeta_id = jQuery('input[name="productmeta_id"]').val();

	if (productmeta_id != 0) {
		do_action = 'nm_personalizedproduct_update_form_meta';
	} else {
		do_action = 'nm_personalizedproduct_save_form_meta';
	}
	
	var server_data = {
		action 				: do_action,
		productmeta_id 		: jQuery.trim(jQuery('input[name="productmeta_id"]').val()),
		productmeta_name 	: jQuery.trim(jQuery('input[name="productmeta_name"]').val()),
		productmeta_validation 	: jQuery.trim(jQuery('input:checkbox[name="enable_ajax_validation"]:checked').val()),
		dynamic_price_hide 	: jQuery.trim(jQuery('input:checkbox[name="dynamic_price_hide"]:checked').val()),
		show_cart_thumb 	: jQuery.trim(jQuery('input:checkbox[name="show_cart_thumb"]:checked').val()),
		aviary_api_key 		: jQuery.trim(jQuery('input[name="aviary_api_key"]').val()),
		productmeta_style	: jQuery.trim(jQuery('textarea[name="productmeta_style"]').val()),
		
		product_meta 		: product_meta_values
	};
		jQuery.post(ajaxurl, server_data, function(resp) {
			
			//console.log(resp); return false;
			jQuery("#nm-saving-form").html(resp.message);
			if(resp.status == 'success'){
				
				if(resp.productmeta_id != ''){
					window.location = nm_personalizedproduct_vars.plugin_admin_page + '&productmeta_id=' + resp.productmeta_id+'&do_meta=edit';
				}else{
					window.location.reload(true);	
				}
			}
			
		}, 'json');
}

function updateOptions(options) {

	var opt = jQuery.parseJSON(options);

	/*
	 * getting action from object
	 */

	/*
	 * extractElementData defined in nm-globals.js
	 */
	var data = extractElementData(opt);

	if (data.bug) {
		// jQuery("#reply_err").html('Red are required');
		alert('bug here');
	} else {

		/*
		 * [1]
		 */
		data.action = 'nm_personalizedproduct_save_settings';

		jQuery.post(ajaxurl, data, function(resp) {

			// jQuery("#reply_err").html(resp);
			alert(resp);
			// window.location.reload(true);

		});
	}
}

function are_sure(productmeta_id) {

	var a = confirm('Are you sure to delete this file?');
	if (a) {
		jQuery("#del-file-" + productmeta_id).attr("src", nm_personalizedproduct_vars.doing);

		jQuery.post(ajaxurl, {
			action : 'nm_personalizedproduct_delete_meta',
			productmeta_id : productmeta_id
		}, function(resp) {
			// alert(data);
			alert(resp);
			window.location.reload(true);

		});

	}
}

//conditiona logic for select, radio and checkbox
function populate_conditional_elements() {

	// resetting
	jQuery('select[name="condition_elements"]').html('');

	jQuery("ul#meta-input-holder li").each(
			function(i, item) {

				var input_type = jQuery(item).attr('data-inputtype');
				var conditional_elements = jQuery(item).find(
						'input[name="title"]').val();
				var conditional_elements_value = jQuery(item).find(
						'input[name="data_name"]').val();
				// console.log(conditional_elements);

				if (conditional_elements !== '' && (input_type === 'select' || input_type === 'radio' || input_type === 'image')){
					
					jQuery('select[name="condition_elements"]')
					.append(
							'<option value="'
									+ conditional_elements_value + '">'
									+ conditional_elements
									+ '</option>');
					
				}
					
			});
	
	// setting the existing conditional elements
	jQuery("ul#meta-input-holder li").each(
			function(i, item) {
				
				jQuery(item).find('select[name="condition_elements"]').each(function(i, condition_element){
				
					var existing_value1 = jQuery(condition_element).attr("data-existingvalue");
					jQuery(condition_element).val(existing_value1);
					
					// populating element_values, also setting existing option
					load_conditional_values(jQuery(condition_element));
				});
				
					
			});


}

// load conditional values
function load_conditional_values(element) {

	// resetting
	jQuery(element).parent().find('select[name="condition_element_values"]')
			.html('');

	jQuery("ul#meta-input-holder li").each(
			function(i, item) {

				var conditional_elements_value = jQuery(item).find(
						'input[name="data_name"]').val();
				if (conditional_elements_value === jQuery(element).val()) {

					
					var opt = '';
					jQuery(item).find('input:text[name="options[option]"], input:text[name="pre-upload-title"]').each(function(i, item){
						
						//console.log(jQuery(item).val());
						opt = jQuery(item).val();
						var existing_value2 = jQuery(element).parent().find('select[name="condition_element_values"]').attr("data-existingvalue");
						var selected = (opt === existing_value2) ? 'selected = "selected"' : '';

						//console.log(jQuery(element).val() + ' ' +existing_value2);
						jQuery(element).parent().find(
								'select[name="condition_element_values"]')
								.append(
										'<option '+selected+' value="' + opt + '">' + opt
												+ '</option>');
					});
					

				}

			});
}

function validate_api_wooproduct(form){
	
	jQuery(form).find("#nm-sending-api").html(
			'<img src="' + nm_personalizedproduct_vars.doing + '">');
	
	var data = jQuery(form).serialize();
	data = data + '&action=nm_personalizedproduct_validate_api';
	
	jQuery.post(ajaxurl, data, function(resp) {

		//console.log(resp);
		jQuery(form).find("#nm-sending-api").html(resp.message);
		if( resp.status == 'success' ){
			window.location.reload(true);			
		}
	}, 'json');
	
	
	return false;
}

