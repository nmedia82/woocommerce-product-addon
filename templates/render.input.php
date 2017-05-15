<?php
/*
 * rendering product meta on product page
 */
global $nmpersonalizedproduct, $product;

$single_form = $nmpersonalizedproduct -> get_product_meta ( $nmpersonalizedproduct -> productmeta_id );
//nm_personalizedproduct_pa( $single_form );

$existing_meta = json_decode ( $single_form -> the_meta, true );

if ($existing_meta) {
//pasting the custom css if used in form settings	
if ( $single_form -> productmeta_style != '') {
	echo '<style>';
		echo '.related.products .amount-options { display:none; }';

        //added on September 2, 2014
        echo '.upsells .amount-options { display:none; }';
		echo stripslashes(strip_tags( $single_form -> productmeta_style ));
	echo '</style>';
}


	echo '<div id="nm-productmeta-box-' . $nmpersonalizedproduct -> productmeta_id . '" class="nm-productmeta-box">';
	echo '<input type="hidden" name="woo_option_price">';	// it will be populated while dynamic prices set in script.js
	echo '<input type="hidden" id="_product_price" value="'.$product->get_price().'">';	// it is setting price to be used for dymanic prices in script.js
	echo '<input type="hidden" id="_productmeta_id" value="'.$nmpersonalizedproduct -> productmeta_id.'">';
	echo '<input type="hidden" id="_product_id" value="'.$product->get_id().'">';
	
	echo '<input type="hidden" name="add-to-cart" value="'.$product->get_id().'">';
	
	echo '<input type="hidden" name="woo_onetime_fee">';	// it will be populated while dynamic prices set in script.js
	echo '<input type="hidden" name="woo_file_cost">';	// to hold the file cost
	
	$row_size = 0;
	
	$started_section = '';
	
	foreach ( $existing_meta as $key => $meta ) {
		
		$type 			= ( isset($meta['type']) ? $meta ['type'] : '');
		$data_name		= ( isset($meta['data_name']) ? $meta ['data_name'] : '');
		$title			= ( isset($meta['title']) ? $meta ['title'] : '');
		$width			= ( isset($meta['width']) ? $meta ['width'] : '');
		$required		= ( isset($meta['required'] ) ? $meta['required'] : '' );
		$error_message 	= ( isset($meta['error_message'] ) ? $meta['error_message'] : '' );
		$description	= ( isset($meta['description'] ) ? $meta['description'] : '' );
		$condition		= ( isset($meta['conditions'] ) ? $meta['conditions'] : '' );
		$options		= ( isset($meta['options'] ) ? $meta['options'] : '' );
		
		$name = strtolower ( preg_replace ( "![^a-z0-9]+!i", "_", $data_name ) );
		
		// conditioned elements
		$visibility = '';
		$conditions_data = '';
		if (isset( $meta['logic'] ) && $meta['logic'] == 'on') {
		
			if($meta['conditions']['visibility'] == 'Show')
				$visibility = 'display: none';
		
			$conditions_data	= 'data-rules="'.esc_attr( json_encode( $condition )).'"';
		}
		
		if (($row_size + intval ( $width)) > 100 || $type == 'section') {
			
			echo '<div style="clear:both; margin: 0;"></div>';
			
			if ($type == 'section') {
				$row_size = 100;
			} else {
				
				$row_size = intval ( $width );
			}
		} else {
			
			$row_size += intval ( $width );
		}
		
		$show_asterisk = (isset( $meta ['required'] ) && $meta ['required']) ? '<span class="show_required"> *</span>' : '';
		$show_description = ($description) ? '<span class="show_description"> ' . stripslashes ( $description ) . '</span>' : '';
		
		$the_width = intval ( $width );
		$the_width = ($the_width > 0 ? $the_width - 1 . '%' : '99%');
		
		$the_margin = '1%';
		
		$field_label = stripslashes( $title ) . $show_asterisk . $show_description;
		
		
		$args = '';
		
		$validate_name = '_'.$name.'_';
		if($conditions_data == '')
			echo '<input type="hidden" name="'.$validate_name.'" value="showing">';
			
		switch ($type) {

			case 'text':
					
					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $required,
									'data-message'	=> $error_message,
									'maxlength'	=> $meta['max_length'],
									);
					echo '<div data-dataname="'.$name.'" id="box-'.$name.'" style="width: '. $the_width.'; margin-right: '. $the_margin.';'.$visibility.'" '.$conditions_data.'>';
					printf( __('<label for="%1$s">%2$s</label><br />', 'nm-personalizedproduct'), $name, $field_label );
					
					$data_default = ( isset( $meta['default_value'] ) ? $meta['default_value'] : '');
					$nmpersonalizedproduct -> inputs[$type]	-> render_input($args, $data_default);					
					
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					break;
					
				case 'textarea':
				
					$args = array(	'name'			=> $name,
							'id'			=> $name,
							'data-type'		=> $type,
							'data-req'		=> $required,
							'data-message'	=> $error_message,
							'maxlength'	=> $meta['max_length'],
							'minlength'	=> $meta['min_length']);
					
					echo '<div data-dataname="'.$name.'" id="box-'.$name.'" style="width: '. $the_width.'; margin-right: '. $the_margin.';'.$visibility.'" '.$conditions_data.'>';
					printf( __('<label for="%1$s">%2$s</label><br />', 'nm-personalizedproduct'), $name, $field_label );
					
					$data_default = ( isset( $meta['default_value'] ) ? $meta['default_value'] : '');
					$nmpersonalizedproduct -> inputs[$type]	-> render_input($args, $data_default);				
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					break;
					
					
				case 'select':
				
					$default_selected = (isset( $meta['selected'] ) ? $meta['selected'] : '' );
					$data_onetime = (isset( $meta['onetime'] ) ? $meta['onetime'] : '' );
					$data_onetime_taxable = (isset( $meta['onetime_taxable'] ) ? $meta['onetime_taxable'] : '' );
					
					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $required,
									'data-message'		=> $error_message,
									'data-onetime'		=> $data_onetime,
									'data-onetime-taxable'	=> $data_onetime_taxable);
				
					echo '<div data-dataname="'.$name.'" id="box-'.$name.'" style="width: '. $the_width.'; margin-right: '. $the_margin.';'.$visibility.'" '.$conditions_data.'>';
					printf( __('<label for="%1$s">%2$s</label><br />', 'nm-personalizedproduct'), $name, $field_label );
					
					$nmpersonalizedproduct -> inputs[$type]	-> render_input($args, $options, $default_selected);
				
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					break;
						
				case 'radio':
					$default_selected = $meta['selected'];
					$data_onetime = (isset( $meta['onetime'] ) ? $meta['onetime'] : '' );
					$data_onetime_taxable = (isset( $meta['onetime_taxable'] ) ? $meta['onetime_taxable'] : '' );
					
					$args = array(	'name'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $required,
									'data-message'		=> $error_message,
									'data-onetime'		=> $data_onetime,
									'data-onetime-taxable'	=> $data_onetime_taxable);
				
					echo '<div data-dataname="'.$name.'" id="box-'.$name.'" style="width: '. $the_width.'; margin-right: '. $the_margin.';'.$visibility.'" '.$conditions_data.'>';
					printf( __('<label for="%1$s">%2$s</label><br />', 'nm-personalizedproduct'), $name, $field_label );
					
					$nmpersonalizedproduct -> inputs[$type]	-> render_input($args, $options, $default_selected);
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					break;
						
				
				case 'checkbox':
			
					$defaul_checked = explode("\n", $meta['checked']);
		
					$args = array(	'name'			=> $name,
							'data-type'		=> $type,
							'data-req'		=> $required,
							'data-message'	=> $error_message);
					
					echo '<div data-dataname="'.$name.'" id="box-'.$name.'" style="width: '. $the_width.'; margin-right: '. $the_margin.';'.$visibility.'" '.$conditions_data.'>';
					printf( __('<label for="%1$s">%2$s</label><br />', 'nm-personalizedproduct'), $name, $field_label );
					
					$nmpersonalizedproduct -> inputs[$type]	-> render_input($args, $options, $defaul_checked);
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					
					break;
					
		}
	}
	
	echo '<div style="clear: both"></div>';
	
	echo '</div>'; // ends nm-productmeta-box
}
