jQuery(function($) {
    
    $(".nm-productmeta-box").closest('form').find('button').click(function(event)
    {
        event.preventDefault(); // cancel default behavior
        
        if( validate_cart_data() ){
        	$(this).closest('form').submit();
        }
    });
});

function validate_cart_data(){
	
	var form_data = jQuery.parseJSON( woopa_vars.fields_meta );
	var has_error = true;
	var error_in = '';
	
	console.log(form_data);
	
	jQuery.each( form_data, function( key, meta ) {
		
		var type = meta['type'];
		var error_message	= stripslashes( meta['error_message'] );
		//console.log('err message '+error_message+' id '+meta['data_name']);
		
		error_message = (error_message === '') ? woopa_vars.default_error_message : error_message;
		
		if(type === 'text' || type === 'textarea' || type === 'select' || type === 'email' || type === 'date'){
			
			var input_control = jQuery('#'+meta['data_name']);
			
			if(meta['required'] === "on" && jQuery(input_control).val() === '' && jQuery(input_control).closest('div').css('display') != 'none'){
				jQuery(input_control).closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
				error_in = meta['data_name']
			}else{
				jQuery(input_control).closest('div').find('span.errors').html('').css({'border' : '','padding' : '0'});
			}
		}else if(type === 'checkbox'){
			
			if(meta['required'] === "on" && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]:checked').length === 0 && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').css('display') != 'none'){
				
				jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
			}else if(meta['min_checked'] != '' && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]:checked').length < meta['min_checked']){
				jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
			}else if(meta['max_checked'] != '' && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]:checked').length > meta['max_checked']){
				jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
			}else{
				
				jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').find('span.errors').html('').css({'border' : '','padding' : '0'});
				
				}
		}else if(type === 'radio'){
				
				if(meta['required'] === "on" && jQuery('input:radio[name="'+meta['data_name']+'"]:checked').length === 0 && jQuery('input:radio[name="'+meta['data_name']+'"]').closest('div').css('display') != 'none'){
					jQuery('input:radio[name="'+meta['data_name']+'"]').closest('div').find('span.errors').html(error_message).css('color', 'red');
					has_error = false;
					error_in = meta['data_name']
				}else{
					jQuery('input:radio[name="'+meta['data_name']+'"]').closest('div').find('span.errors').html('').css({'border' : '','padding' : '0'});
				}
		}else if(type === 'file'){
			
				var $upload_box = jQuery('#nm-uploader-area-'+meta['data_name']);
				var $uploaded_files = $upload_box.find('input:checkbox:checked');
				if(meta['required'] === "on" && $uploaded_files.length === 0 && $upload_box.css('display') != 'none'){
					$upload_box.find('span.errors').html(error_message).css('color', 'red');
					has_error = false;
					error_in = meta['data_name']
				}else{
					$upload_box.find('span.errors').html('').css({'border' : '','padding' : '0'});
				}
		}else if(type === 'image'){
			
			var $image_box = jQuery('#pre-uploaded-images-'+meta['data_name']);
			if(meta['required'] === "on" && jQuery('input:radio[name="'+meta['data_name']+'"]:checked').length === 0 && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]:checked').length === 0 && $image_box.css('display') != 'none'){
				$image_box.find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
				error_in = meta['data_name']
			}else{
				$image_box.find('span.errors').html('').css({'border' : '','padding' : '0'});
			}
		}else if(type === 'masked'){
			
			var input_control = jQuery('#'+meta['data_name']);
			
			if(meta['required'] === "on" && (jQuery(input_control).val() === '' || jQuery(input_control).attr('data-ismask') === 'no') && jQuery(input_control).closest('div').css('display') != 'none'){
				jQuery(input_control).closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
				error_in = meta['data_name'];
			}else{
				jQuery(input_control).closest('div').find('span.errors').html('').css({'border' : '','padding' : '0'});
			}
		}
		
	});
	
	//console.log( error_in ); return false;
	return has_error;
}