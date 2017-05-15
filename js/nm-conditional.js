var boxes		= new Array();	//checking bound connection


jQuery(function($){
   
   //conditional elements handling
	$(".nm-productmeta-box").find('select, input[type="checkbox"], input[type="radio"]').on('change', function(e){
		
		var element_name 	= $(this).attr("name");
		var element_value	= '';
		//console.log(element_name);
		
		if($(this).attr('data-type') === 'radio'){
			element_value	= $(this).filter(':checked').val();
		}else if($(this).attr('data-type') === 'image'){
			element_value	= $.parseJSON($(this).val());
			element_value 	= element_value.title;
		}else{
			element_value	= $(this).val();
		}
		
		//console.log( 'changed_element_val '+element_value );
		
		$(".nm-productmeta-box div, .nm-productmeta-box div.fileupload-box").each(function(i, p_box){

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
									//reset value if set before
									jQuery('#'+box_id).find(':input').not(':checkbox, :radio').val('');
									jQuery('#'+box_id).find(':input','select').removeAttr('checked').removeAttr('selected');
									jQuery('#'+box_id).find('select, input[type="checkbox"], input[type="radio"]').change();
									
									
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
									jQuery('#'+box_id).find(':input').not(':checkbox, :radio').val('');
									 jQuery('#'+box_id).find(':input','select').removeAttr('checked').removeAttr('selected');
									 jQuery('#'+box_id).find('select, input[type="checkbox"], input[type="radio"]').change();
									
								}		
								break;
								
								
								case 'greater than':
									
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
										jQuery('#'+box_id).find(':input').not(':checkbox, :radio').val('');
									 	jQuery('#'+box_id).find(':input','select').removeAttr('checked').removeAttr('selected');
									 	jQuery('#'+box_id).find('select, input[type="checkbox"], input[type="radio"]').change();
										
									}		
									break;
									
								
								case 'less than':
									
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
										jQuery('#'+box_id).find(':input').not(':checkbox, :radio').val('');
										jQuery('#'+box_id).find(':input','select').removeAttr('checked').removeAttr('selected');
									 	jQuery('#'+box_id).find('select, input[type="checkbox"], input[type="radio"]').change();
										
									}		
									break;
									}
						
						set_visibility(p_box, _bound, _total_rules, _visiblity);
					}
					
				});
				
			}
			
			
		});
		
	}); 
});