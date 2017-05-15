<?php
/*
 * this file contains pluing meta information and then shared
 * between pluging and admin classes
 * * [1]
 */



if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	define('NM_DIR_SEPERATOR', '\\');
} else {
	define('NM_DIR_SEPERATOR', '/');
}

function woopa_get_plugin_data(){
	
	
	return array('name'			=> 'Personalized Product',
							'dir_name'		=> '',
							'shortname'		=> 'nm_personalizedproduct',
							'path'			=> untrailingslashit(plugin_dir_path( __FILE__ )),
							'url'			=> untrailingslashit(plugin_dir_url( __FILE__ )),
							'db_version'	=> 3.12,
							'logo'			=> plugin_dir_url( __FILE__ ) . 'images/logo.png',
							'menu_position'	=> 90
	);
}


function woopa_printA($arr){
	
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}

/**
 * some WC functions wrapper
 * */
 
/**
 * some WC functions wrapper
 * */
 

if( !function_exists('woopa_wc_add_notice')){
function woopa_wc_add_notice($string, $type="error"){
 	
 	global $woocommerce;
 	if( version_compare( $woocommerce->version, 2.1, ">=" ) ) {
 		wc_add_notice( $string, $type );
	    // Use new, updated functions
	} else {
	   $woocommerce->add_error ( $string );
	}
 }
}

if( !function_exists('woopa_wc_add_order_item_meta') ){
	
	function woopa_wc_add_order_item_meta($item_id, $key, $val){
		global $woocommerce;
		if( version_compare( $woocommerce->version, 2.1, ">=" ) ) {
			wc_add_order_item_meta( $item_id, $key, $val );
			// Use new, updated functions
		} else {
		   woocommerce_add_order_item_meta($item_id, $key, $val);
		}
	}
}
 
 
 function woopa_is_ppom_active($plugin_path) {
    $return_var = in_array( $plugin_path, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
    return $return_var;
  }

