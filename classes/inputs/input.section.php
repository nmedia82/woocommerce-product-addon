<?php
/*
 * Followig class handling text input control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Section_wooproduct extends NM_Inputs_wooproduct{
	
	/*
	 * input control settings
	 */
	var $title, $desc, $settings;
	
	
	/*
	 * check if section is started
	 */
	var $is_section_stared;
	/*
	 * this var is pouplated with current plugin meta
	*/
	var $plugin_meta;
	
	function __construct(){
		
		$this -> plugin_meta = woopa_get_plugin_data();
		
		$this -> title 		= __ ( 'Section', 'nm-personalizedproduct' );
		$this -> desc		= __ ( 'Form section', 'nm-personalizedproduct' );
		$this -> settings	= self::get_settings();
		$this -> ispro		= true;
		
	}
	
	
	
	
	private function get_settings(){
		
		return array (
						'title' => array (
								'type' => 'text',
								'title' => __ ( 'Title', 'nm-personalizedproduct' ),
								'desc' => __ ( 'It will as section heading wrapped in h2', 'nm-personalizedproduct' ) 
						),
						'description' => array (
								'type' => 'textarea',
								'title' => __ ( 'Description', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Type description, it will be diplay under section heading.', 'nm-personalizedproduct' ) 
						)
				);
	
	}
	
	
	/*
	 * @params: args
	*/
	function render_input($args, $content=""){
		
		
		$_html =  '<section id="'.$args['id'].'">';
		$_html .= '<div style="clear: both"></div>';
		
		$_html .= '<header class="webcontact-section-header">';
		$_html .= '<h2>'. stripslashes( $args['title'] ).'</h2>';
		$_html .= '<p id="box-'.$args['id'].'">'. stripslashes( $args['description']).'</p>';
		$_html .= '</header>';
		
		$_html .= '<div style="clear: both"></div>';
		
		echo $_html;
	}
}