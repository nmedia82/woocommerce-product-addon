<?php
/*
 * Followig class handling hidden input control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Hidden_wooproduct extends NM_Inputs_wooproduct{
	
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
		
		$this -> title 		= __ ( 'Hidden Input', 'nm-personalizedproduct' );
		$this -> desc		= __ ( 'regular hidden input', 'nm-personalizedproduct' );
		$this -> settings	= self::get_settings();
		$this -> ispro		= true;
		
	}
	
	
	
	
	private function get_settings(){
		
		return array (

					'data_name' => array (
							'type' => 'text',
							'title' => __ ( 'Data name', 'nm-personalizedproduct' ),
							'desc' => __ ( 'REQUIRED: The identification name of this field, that you can insert into body email configuration. Note:Use only lowercase characters and underscores.', 'nm-personalizedproduct' )
					),
					'field_value' => array (
							'type' => 'text',
							'title' => __ ( 'Field value', 'nm-personalizedproduct' ),
							'desc' => __ ( 'you can pre-set the value of this hidden input.', 'nm-personalizedproduct' )
					),
				);
	}
	
	
	/*
	 * @params: args
	*/
	function render_input($args, $content=""){
		
		$_html = '<input type="hidden" ';
		
		foreach ($args as $attr => $value){
			
			$_html .= $attr.'="'.stripslashes( $value ).'"';
		}
		
		if($content)
			$_html .= 'value="' . stripslashes($content) . '"';
		
		$_html .= ' />';
		
		echo $_html;
	}
}