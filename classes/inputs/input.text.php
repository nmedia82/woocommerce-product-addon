<?php
/*
 * Followig class handling text input control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Text_wooproduct extends NM_Inputs_wooproduct{
	
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
		
		$this -> title 		= __ ( 'Text Input', 'nm-personalizedproduct' );
		$this -> desc		= __ ( 'regular text input', 'nm-personalizedproduct' );
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
				
						'max_length' => array (
								'type' => 'text',
								'title' => __ ( 'Max. Length', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Max. characters allowed, leave blank for default', 'nm-personalizedproduct' )
						),
						
						'default_value' => array (
								'type' => 'text',
								'title' => __ ( 'Set default value', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Pre-defined value for text input', 'nm-personalizedproduct' )
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
	 * @params: args
	*/
	function render_input($args, $content=""){
		
		$_html = '<input type="text" ';
		
		foreach ($args as $attr => $value){
			
			$_html .= $attr.'="'.stripslashes( $value ).'"';
		}
		
		if($content)
			$_html .= 'value="' . stripslashes($content	) . '"';
		
		$_html .= ' />';
		
		echo $_html;
	}
}