<?php
/*
 * Followig class handling checkbox input control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Checkbox_wooproduct extends NM_Inputs_wooproduct{
	
	/*
	 * input control settings
	 */
	var $title, $desc, $settings;
	
	/*
	 * this var is pouplated with current plugin meta
	*/
	var $plugin_meta;
	
	function __construct(){
		
		$this -> plugin_meta = woopa_get_plugin_data();
		
		$this -> title 		= __ ( 'Checkbox Input', 'nm-personalizedproduct' );
		$this -> desc		= __ ( 'regular checkbox input', 'nm-personalizedproduct' );
		$this -> settings	= self::get_settings();
		
	}
	
	
	
	
	private function get_settings(){
		
		return array (
						'title' => array (
								'type' => 'text',
								'title' => __ ( 'Title', 'nm-personalizedproduct' ),
								'desc' => __ ( 'It will be shown as field label', 'nm-personalizedproduct' ) 
						),
						'data_name' => array (
								'type' => 'text',
								'title' => __ ( 'Data name', 'nm-personalizedproduct' ),
								'desc' => __ ( 'REQUIRED: The identification name of this field, that you can insert into body email configuration. Note:Use only lowercase characters and underscores.', 'nm-personalizedproduct' ) 
						),
						'description' => array (
								'type' => 'text',
								'title' => __ ( 'Description', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Small description, it will be diplay near name title.', 'nm-personalizedproduct' ) 
						),
						'error_message' => array (
								'type' => 'text',
								'title' => __ ( 'Error message', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Insert the error message for validation.', 'nm-personalizedproduct' ) 
						),
						
						'options' => array (
								'type' => 'paired',
								'title' => __ ( 'Add options', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Type option with price (optionally)', 'nm-personalizedproduct' )
						),
						
						'required' => array (
								'type' => 'checkbox',
								'title' => __ ( 'Required', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Select this if it must be required.', 'nm-personalizedproduct' ) 
						),
						'class' => array (
								'type' => 'text',
								'title' => __ ( 'Class', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Insert an additional class(es) (separateb by comma) for more personalization.', 'nm-personalizedproduct' ) 
						),
						'width' => array (
								'type' => 'text',
								'title' => __ ( 'Width', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Type field width in % e.g: 50%', 'nm-personalizedproduct' ) 
						),
						'checked' => array (
								'type' => 'textarea',
								'title' => __ ( 'Checked option(s)', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Type option(s) name (given above) if you want already checked.', 'nm-personalizedproduct' ) 
						),
						
						'min_checked' => array (
								'type' => 'text',
								'title' => __ ( 'Min. Checked option(s)', 'nm-personalizedproduct' ),
								'desc' => __ ( 'How many options can be checked by user e.g: 2. Leave blank for default.', 'nm-personalizedproduct' ) 
						),
						
						'max_checked' => array (
								'type' => 'text',
								'title' => __ ( 'Max. Checked option(s)', 'nm-personalizedproduct' ),
								'desc' => __ ( 'How many options can be checked by user e.g: 3. Leave blank for default.', 'nm-personalizedproduct' ) 
						),
						'logic' => array (
								'type' => 'checkbox',
								'title' => __ ( 'Enable conditional logic', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Tick it to turn conditional logic to work below', 'nm-personalizedproduct' )
						),
						'conditions' => array (
								'type' => 'html-conditions',
								'title' => __ ( 'Conditions', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Tick it to turn conditional logic to work below', 'nm-personalizedproduct' )
						),
		);
	}
	
	
	/*
	 * @params: $opt['option']ions
	*/
	function render_input($args, $options = "", $default = "") {
		
		$_html = '';
		foreach ( $options as $opt) {
			
			if ($default) {
				if (in_array ( $opt['option'], $default ))
					$checked = 'checked="checked"';
				else
					$checked = '';
			}
			
			if($opt['price']){
				$output	= stripslashes(trim($opt['option'])) .' (+ ' . wc_price($opt['price']).')';
			}else{
				$output	= stripslashes(trim($opt['option']));
			}
			
			//if in percent
			if($opt['price'] && strpos($opt['price'],'%') !== false){
				$output	= stripslashes(trim($opt['option'])) .' (+ ' . $opt['price'].')';
			}
			
			$field_id = $args['name'] . '-meta-'.strtolower ( preg_replace ( "![^a-z0-9]+!i", "_", $opt['option'] ) );
			
			$_html .= '<label for="'.$field_id.'"> <input id="'.$field_id.'" data-price="'.$opt['price'].'" type="checkbox" ';
			
			foreach ($args as $attr => $value){
					
				if ($attr == 'name') {
					$value .= '[]';
				}
				$_html .= $attr.'="'.stripslashes( $value ).'"';
			}
			
			$_html .= ' value="'.$opt['option'].'" '.$checked.'> ';
			$_html .= $output;
			
			$_html .= '</label> ';
		}
		
		echo $_html;
	}
}