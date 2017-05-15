/*
 * NOTE: all actions are prefixed by plugin shortnam_action_name
 */
var selected_slide = 0;
var total_sections = 0;
var boxes		= new Array();	//checking bound connection

jQuery(function($){

	//tweaking file uploader button css
	$("#uploadifive-nm_contact_file").css({'margin':'#fff'});
	
	//setting all input widht to 95% within P tags
	$(".nm-productmeta-box").find('input:text, input[type="email"], textarea, select').css({'width': '100%', 'padding': 0});
	
	/*
	 * handling date input
	 */
	$("input[data-type='date']").each(function(i, item){
		
		//console.log(item);
		$(item).datepicker({ 	changeMonth: true,
			changeYear: true,
			dateFormat: $(item).attr('data-format')
			});
	});
	
	
	
	/*
	 * all about section slides
	 * pagination
	 */
	
	if(nm_personalizedproduct_vars.section_slides === 'on'){
		var section_titles_tds = '';
		$(".nm-productmeta-box section").each(function(i, section){
			
			//console.log(section);
			section_titles_tds += '<td>'+$(section).find('h2').html()+'</td>';		
			$(section).hide();
			
			total_sections += 1;
			
		});
		
		//now adding titles to bottom of slider
		$("#section_titles tr").html(section_titles_tds);
		
		//showing only first section at start
		$(".nm-productmeta-box section:first").slideDown(200);
		$("#section_titles tr td:first").css({'color':'#000', 'background-color': '#ccc'});
		set_arrows();
		
		$("#slide_next").click(function(e){
	
			slide_section('next');
			e.preventDefault();
		});
		$("#slide_back").click(function(e){
	
			slide_section('back');
			e.preventDefault();
		});
	}
	
	// pagination ends ==============
	
	//conditional elements handling
	$(".nm-productmeta-box").find('select, input[type="checkbox"], input[type="radio"]').on('change', function(){
		
		var element_name 	= $(this).attr("name");
		var element_value	= $(this).val();
		
		$(".nm-productmeta-box p, .nm-productmeta-box div.fileupload-box").each(function(i, p_box){

			var parsed_conditions 	= $.parseJSON ($(p_box).attr('data-rules'));
			var box_id				= $(p_box).attr('id');
			var element_box = new Array();
			// console.log( parsed_conditions );
			
			if(parsed_conditions !== null){
			
				
				var _visiblity		= parsed_conditions.visibility;
				var _bound			= parsed_conditions.bound;
				var _total_rules 	= Object.keys(parsed_conditions.rules).length;
				
				 var matched_rules = {};
				 var last_meched_element = '';
				$.each(parsed_conditions.rules, function(i, rule){
					
					var _element 		= rule.elements;
					var _elementvalues	= rule.element_values;
					var _operator 		= rule.operators;
					
					//console.log('_element ='+_element+' element_name ='+element_name);
					var matched_rules = {};	
					
					if(_element === element_name && last_meched_element !== _element){
						
						var temp_matched_rules = {};
						
						switch(_operator){
						
							case 'is':
								
								if(_elementvalues === element_value){
									
									last_meched_element = element_name;
									
									if(boxes[box_id]){
					                    jQuery.each(boxes[box_id], function(j, matched){
					                        if(matched !== undefined){
					                            jQuery.each(matched, function(k,v){
					                            	if(k !== _element){
					                            		temp_matched_rules[k]=v;
						                                element_box.push(temp_matched_rules);
					                            	}
					                            });
					                        }
					                    });
					                }
									
									matched_rules[_element]=element_value;
					                element_box.push(matched_rules);
					                boxes[box_id] = element_box;
								}else{
									
									remove_existing_rules(boxes[box_id], _element);
									
								}		
								break;
								
								
							case 'not':
								
								if(_elementvalues !== element_value){
									
									if(boxes[box_id]){
					                    jQuery.each(boxes[box_id], function(j, matched){
					                        if(matched !== undefined){
					                            jQuery.each(matched, function(k,v){
					                            	if(k !== _element){
					                            		temp_matched_rules[k]=v;
						                                element_box.push(temp_matched_rules);
					                            	}
					                            });
					                        }
					                    });
					                }
									
									matched_rules[_element]=element_value;
					                element_box.push(matched_rules);
					                boxes[box_id] = element_box;
								}else{
									
									remove_existing_rules(boxes[box_id], _element);
									
								}		
								break;
								
								
								case 'greater then':
									
									if(parseFloat(_elementvalues) < parseFloat(element_value) ){
										
										if(boxes[box_id]){
						                    jQuery.each(boxes[box_id], function(j, matched){
						                        if(matched !== undefined){
						                            jQuery.each(matched, function(k,v){
						                            	if(k !== _element){
						                            		temp_matched_rules[k]=v;
							                                element_box.push(temp_matched_rules);
						                            	}
						                            });
						                        }
						                    });
						                }
										
										matched_rules[_element]=element_value;
						                element_box.push(matched_rules);
						                boxes[box_id] = element_box;
									}else{
										
										remove_existing_rules(boxes[box_id], _element);
										
									}		
									break;
									
								
								case 'less then':
									
									if(parseFloat(_elementvalues) > parseFloat(element_value) ){
										
										if(boxes[box_id]){
						                    jQuery.each(boxes[box_id], function(j, matched){
						                        if(matched !== undefined){
						                            jQuery.each(matched, function(k,v){
						                            	if(k !== _element){
						                            		temp_matched_rules[k]=v;
							                                element_box.push(temp_matched_rules);
						                            	}
						                            });
						                        }
						                    });
						                }
										
										matched_rules[_element]=element_value;
						                element_box.push(matched_rules);
						                boxes[box_id] = element_box;
									}else{
										
										remove_existing_rules(boxes[box_id], _element);
										
									}		
									break;
									}
						
						set_visibility(p_box, _bound, _total_rules, _visiblity);
					}
					
				});
				
			}
			
			
		});
		
	});
	
	/* ============= setting prices dynamically on product page ============= */
	$(".nm-productmeta-box").find('select,input:checkbox,input:radio').on('change', function(){
		
		// 
		if ($(".single_variation .price .amount").length > 0){
			var base_amount = $(".single_variation .price .amount").text();
			base_amount = base_amount.replace ( /[^\d.]/g, '' );
			$price = $(".single_variation .price");
		}else{
			var base_amount = $('p[itemprop="price"] .amount').text();
			base_amount = base_amount.replace ( /[^\d.]/g, '' );
			$price = $('p[itemprop="price"]');
		}
		
		
		$(".amount-options").remove();
		
		var html = '<div class="amount-options">';
		var option_price, option_label,updated_price,total_price;
		
		updated_price = 0;
		$(".nm-productmeta-box").find('select').each(function(i, item){
			option_price = $("option:selected", this).attr('data-price');
			
			if(option_price != undefined && option_price != ''){
				// console.log(option_price);
				updated_price = parseFloat(updated_price, 2) +  parseFloat(option_price, 2);
				
			}
							
		});
		$(".nm-productmeta-box").find('input:checkbox').each(function(i, item){
			option_price = $(this).attr('data-price');
			option_label = ($(this).attr('data-title') == undefined) ? $(this).val() : $(this).attr('data-title');	// for image type
			
			if($(this).is(':checked') && option_price != undefined && option_price != ''){
				updated_price = parseFloat(updated_price, 2) +  parseFloat(option_price, 2);
				option_price = get_wc_price(updated_price);
				html += option_label + ': '+ option_price + '<br>';
			}
							
		});
		
		$(".nm-productmeta-box").find('input:radio').each(function(i, item){
			option_price = $(this).attr('data-price');
			option_label = ($(this).attr('data-title') == undefined) ? $(this).val() : $(this).attr('data-title');	// for image type
			
			if($(this).is(':checked') && option_price != undefined && option_price != ''){
				updated_price = parseFloat(updated_price, 2) +  parseFloat(option_price, 2);
				option_price = get_wc_price(updated_price);
				html += option_label + ': '+ option_price + '<br>';
			}
							
		});
		
		
		
		if(updated_price != 0){
			total_price = parseFloat(updated_price,2)+ parseFloat(base_amount,2);
			total_price = Number((total_price).toFixed(2));
			
			jQuery.post(nm_personalizedproduct_vars.ajaxurl, {action:'nm_personalizedproduct_get_option_price', price1: updated_price}, function(resp){
				
				console.log(resp);
				option_price = resp;		//nm_personalizedproduct_vars.woo_currency + option_price;
				html += $(this).val() + ': '+option_price + '<br>';
				
				html += '<strong>' + nm_personalizedproduct_vars.option_amount_text + nm_personalizedproduct_vars.woo_currency + total_price + '</strong><br>';
				html += '</div>';
				
				$('input[name="woo_option_price"]').val(updated_price);
				$price.append(html);
			});
			
				
		}
		
	});
		
});


function get_wc_price(price){
	
	jQuery.post(nm_personalizedproduct_vars.ajaxurl, {action:'nm_personalizedproduct_get_option_price', price1: price}, function(resp){
		
		//console.log(resp);
		return resp;
	});
	
	
}

function set_visibility(p_box, _bound, _total_rules, _visiblity){
	
	var box_id				= jQuery(p_box).attr('id');
	if(boxes[box_id] !== undefined){
		
		// console.log(box_id+': total rules = '+_total_rules+' rules matched = '+Object.keys(boxes[box_id]).length);
		switch(_visiblity){
		
		case 'Show':
			if((_bound === 'Any' &&  (Object.keys(boxes[box_id]).length > 0)) || _total_rules === Object.keys(boxes[box_id]).length){
				jQuery(p_box).show(200);
			}else{
				jQuery(p_box).hide(200);
          		
          		//update_rule_childs(element_name);
			}
			break;					
		
		case 'Hide':
			if((_bound === 'Any' &&  (Object.keys(boxes[box_id]).length > 0)) || _total_rules === Object.keys(boxes[box_id]).length){
				jQuery(p_box).hide(200);
				// console.log('hiddedn rule '+box_id);
				jQuery(p_box).find('select, input:radio, input:text, textarea').val('');
			}else{
				jQuery(p_box).show(200);
			}
			break;
	}
	}
}


function is_valid_email(email) {
	var pattern = new RegExp(
			/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
	return pattern.test(email);
};

function get_option(key) {

	/*
	 * TODO: change plugin shortname
	 */
	var keyprefix = 'nm_personalizedproduct';

	key = keyprefix + key;

	var req_option = '';

	jQuery.each(nm_personalizedproduct_vars.settings, function(k, option) {

		// console.log(k);

		if (k == key)
			req_option = option;
	});

	// console.log(req_option);
	return req_option;
}

function slide_section(move){
	
	//hiding all section first
	jQuery(".nm-productmeta-box section").hide(100);
	//setting td titles to grey back
	jQuery("#section_titles tr td").css({'color':'#ccc', 'background-color': ''});
	
	if(move === 'next'){
	
		selected_slide++;
	
		jQuery(".nm-productmeta-box section").each(function(index, section){
			
			if(index === selected_slide){
				jQuery(section).slideDown(300);
				jQuery("#section_titles tr td:nth-child("+(index+1)+")").css({'color':'#000', 'background-color': '#ccc'});
			}
		});
		
	}else{
		
		selected_slide--;
		
		jQuery(".nm-productmeta-box section").each(function(index, section){
			
			if(index === selected_slide){
				jQuery(section).slideDown(300);				
				jQuery("#section_titles tr td:nth-child("+(index+1)+")").css({'color':'#000', 'background-color': '#ccc'});
			}
		});
	}
	
	set_arrows();
}

function set_arrows(){
	
	jQuery(".productmeta-save-button").hide();
	
	if(selected_slide <= 0){		//just started
		
		jQuery("#slide_back").hide();
		jQuery("#slide_next").show();
		
	}else if(selected_slide > 0 && selected_slide < (total_sections-1)){		//somewhere between
		
		jQuery("#slide_back").show();
		jQuery("#slide_next").show();
	}else if(selected_slide >= (total_sections-1)){		// it is last section
		
		jQuery(".productmeta-save-button").show();
		
		jQuery("#slide_back").show();
		jQuery("#slide_next").hide();
	}
}

function update_rule_childs(element_name, element_values){
	
	jQuery(".nm-productmeta-box > p, .nm-productmeta-box div.fileupload-box").each(function(i, p_box){

		var parsed_conditions 	= jQuery.parseJSON (jQuery(p_box).attr('data-rules'));
		var box_id				= jQuery(p_box).attr('id');
		
		if(parsed_conditions !== null){
		
			var _visiblity		= parsed_conditions.visibility;
			var _bound			= parsed_conditions.bound;
			var _total_rules 	= Object.keys(parsed_conditions.rules).length;
			
			 var matched_rules = {};
			 var last_meched_element = '';
			jQuery.each(parsed_conditions.rules, function(i, rule){
				
				var _element 		= rule.elements;
				var _elementvalues	= rule.element_values;
				var _operator 		= rule.operators;
				
				//console.log('_element ='+_element+' element_name ='+element_name);
				var matched_rules = {};	
				
				if(element_values === 'child')
					_elementvalues = element_values;
				
				if(_element === element_name && _elementvalues === element_values){
					//console.log('Hiding _element ='+_element+' under box ='+jQuery(p_box).find('select').attr('name'));
					//console.log('hiddedn rule '+element_name+' value ' + element_values + 'under box = ' + jQuery(p_box).attr('id'));
					jQuery(p_box).hide(300, function(){
						update_rule_childs(jQuery(this).find('select, input:radio').attr('name'), 'child');
					});
					
				}
			});
		}
});
	
}
	
function remove_existing_rules(box_rules, element){
	
	if(box_rules){
        jQuery.each(box_rules, function(j, matched){
            if(matched !== undefined){
                jQuery.each(matched, function(k,v){
                	if(k === element){
                  		delete box_rules[j];
                  		update_rule_childs(k, v);
                	}
                });
            }
        });
    }
}

function stripslashes (str) {
	  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  // +   improved by: Ates Goral (http://magnetiq.com)
	  // +      fixed by: Mick@el
	  // +   improved by: marrtins
	  // +   bugfixed by: Onno Marsman
	  // +   improved by: rezna
	  // +   input by: Rick Waldron
	  // +   reimplemented by: Brett Zamir (http://brett-zamir.me)
	  // +   input by: Brant Messenger (http://www.brantmessenger.com/)
	  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
	  // *     example 1: stripslashes('Kevin\'s code');
	  // *     returns 1: "Kevin's code"
	  // *     example 2: stripslashes('Kevin\\\'s code');
	  // *     returns 2: "Kevin\'s code"
	  return (str + '').replace(/\\(.?)/g, function (s, n1) {
	    switch (n1) {
	    case '\\':
	      return '\\';
	    case '0':
	      return '\u0000';
	    case '':
	      return '';
	    default:
	      return n1;
	    }
	  });
	}