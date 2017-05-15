<?php
/*
 * Followig class handling date input control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Color_wooproduct extends NM_Inputs_wooproduct{
	
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
		
		$this -> title 		= __ ( 'Color picker', 'nm-personalizedproduct' );
		$this -> desc		= __ ( 'Color pallete input', 'nm-personalizedproduct' );
		$this -> settings	= self::get_settings();
		$this -> ispro		= true;
		
		$this -> input_scripts = array(	'shipped'		=> array(''),
		
										'custom'		=> array(
												array (
														'script_name' => 'wp_iris_script',
														'script_source' => '/js/color/Iris/dist/iris.min.js',
														'localized' => false,
														'type' => 'js',
														'depends'	=> array('jquery','jquery-ui-core','jquery-ui-draggable', 'jquery-ui-slider'),
														'in_footer'	=> '',
												),
										)
		);
		
		
		add_action ( 'wp_enqueue_scripts', array ($this, 'load_input_scripts'));
		
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
		
		'required' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Required', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Select this if it must be required.', 'nm-personalizedproduct' ) 
		),
				
		'default_color' => array (
				'type' => 'text',
				'title' => __ ( 'Dedfault color', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Define default color e.g: #effeff', 'nm-personalizedproduct' ) 
		),
		
		'show_palletes' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Show palletes', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Tick if need to show a group of common colors beneath the square', 'nm-personalizedproduct' )
		),
				
		'show_onload' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Show color picker', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Display color picker by default, otherwise will show when field selected', 'nm-personalizedproduct' )
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
		
	
	}
}