
var oAJAXRequest = false;
var options_price_added = [];	//this contains all option which is added for price
jQuery(function($){
   
   $.blockUI.defaults.overlayCSS.cursor = "default";
   
   /* ============= setting prices dynamically on product page ============= */
	$(".nm-productmeta-box").find('select,input:checkbox,input:radio').on('change', function(){
		
		//console.log($("option:selected", this)
		var selected_option_price = $("option:selected", this).attr('data-price');
		var checked_option_price = $(this).attr('data-price');
		var does_option_has_price = true;
		
		if( $(this).prop("checked")){
			
			if(checked_option_price == undefined || checked_option_price == '' || checked_option_price == 0){
				//console.log('turn it' + checked_option_price);
				does_option_has_price = false;
				if($.inArray( $(this).attr('name'), options_price_added ) < 0 ){
					return;
				}else{
					options_price_added.splice($.inArray( $(this).attr('name')), 1);
				}
			}
		}else if( selected_option_price == undefined || selected_option_price == '' || selected_option_price == 0){
			//console.log('selected price '+selected_option_price);
			does_option_has_price = false;
			if($.inArray( $(this).attr('name'), options_price_added ) < 0 ){
				return;
			}else{
				options_price_added.splice($.inArray( $(this).attr('name')), 1);
			}
		}
		
		if( does_option_has_price )
			options_price_added.push($(this).attr('name'));
			
		var option_prices = [];
		
		setTimeout(function(){
  			
  			if( oAJAXRequest != false )
			return;

			$(".nm-productmeta-box").find('select').each(function(i, item){
				
				option_price = $("option:selected", this).attr('data-price');
				
				fixedfee = $(this).attr('data-onetime');
				fixedfee_taxable = $(this).attr('data-onetime-taxable');
				
				//console.log($(this).attr('id')+' '+$(this).closest('div').css('display'));
				if(option_price != undefined && option_price != '' && $(this).closest('div').css('display') != 'none'){
					option_prices.push({option: $(this).val(), price: option_price, isfixed: fixedfee, fixedfeetaxable:fixedfee_taxable});
				}				
				
				
			});
			
			$(".nm-productmeta-box").find('input:checkbox').each(function(i, item){
				option_price = $(this).attr('data-price');
				option_label = ($(this).attr('data-title') == undefined) ? $(this).val() : $(this).attr('data-title');	// for image type
				
				if($(this).is(':checked') && option_price != undefined && option_price != '' && $(this).closest('div').css('display') != 'none'){
					option_prices.push({option: option_label, price: option_price});
				}
								
			});
			
			$(".nm-productmeta-box").find('input:radio').each(function(i, item){
				option_price = $(this).attr('data-price');
				option_label = ($(this).attr('data-title') == undefined) ? $(this).val() : $(this).attr('data-title');	// for image type
				fixedfee = $(this).attr('data-onetime');
				fixedfee_taxable = $(this).attr('data-onetime-taxable');
				
				if($(this).is(':checked') && option_price != undefined && option_price != '' && $(this).closest('div').css('display') != 'none'){
					option_prices.push({option: option_label, price: option_price, isfixed: fixedfee, fixedfeetaxable: fixedfee_taxable});
				}
								
			});
			
			var base_amount = $('#_product_price').val();
			var price_html = '';
			//console.log(fixedfees);
			if ($('form.cart').closest('div').find('.price').length > 0){
				price_html = $('form.cart').closest('div').find('.price')[0];
			}
			
			
			
			//disabling add to cart button for a while
			$('form.cart').block({
	                    message: null,
	                    overlayCSS: {
	                    background: "#fff",
	                    opacity: .6
				                    }
		         });
		         
			//var product_base_price = $('#_product_price').val();
			var price_matrix = $("#_pricematrix").val();
			var productmeta_id = $("#_productmeta_id").val();
			var product_id = $("#_product_id").val();
			var variation_id = $('form.cart').find('input[name=variation_id]').val();
			
			
			//console.log(option_prices);
			
			var post_data = {action: 'nm_personalizedproduct_get_option_price', 
							optionprices:option_prices,
							baseprice:base_amount,
							pricematrix: price_matrix,
							productmeta_id: productmeta_id,
							variation_id: variation_id,
							product_id: product_id,
							qty: jQuery('input[name="quantity"]').val()
							};
			
			oAJAXRequest = $.post(nm_personalizedproduct_vars.ajaxurl, post_data, function(resp){
				//console.log(resp);
				
				$(".amount-options").remove();
				if(resp.option_total > 0){
					var html = '<div class="amount-options">';
					html += resp.prices_html;
					html += '</div>';
				}			
				
				$('input[name="woo_option_price"]').val(resp.option_total);
				$('input[name="woo_onetime_fee"]').val(JSON.stringify( resp.onetime_meta ));
				
				//console.log(resp.display_price_hide);
				if (resp.display_price_hide !== 'yes'){
					$(price_html).append(html);	
				}
				
				
				//enabling add to cart button
				$('form.cart').unblock();
				oAJAXRequest = false;
				
			}, 'json');
			
		}, 200);
		
		//console.log(oAJAXRequest);
		
	});
	
});