<?php
/*
Plugin Name: WooCommerce Product Add-on
Plugin URI: http://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/
Description: This plugin allow WooCommerce store admin to add four types of input so user can add data before checkout to personalize the order.
Version: 3.0
Author: nmedia
Text Domain: nm-personalizedproduct
Author URI: http://www.najeebmedia.com/
*/


function woopa_addon_settings( $links ) {
    $settings_link = '<a href="'.admin_url( 'options-general.php?page=nm-personalizedproduct').'">Settings</a>';
  	array_push( $links, $settings_link );
  	return $links;
}


$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'woopa_addon_settings' );

/*
 * loading plugin config file
 */
$_config = dirname(__FILE__).'/config.php';
if( file_exists($_config))
	include_once($_config);
else
	die('Reen, Reen, BUMP! not found '.$_config);


/* ======= the plugin main class =========== */
$_plugin = dirname(__FILE__).'/classes/plugin.class.php';
if( file_exists($_plugin))
	include_once($_plugin);
else
	die('Reen, Reen, BUMP! not found '.$_plugin);

/*
 * [1]
 */

$nmpersonalizedproduct = NM_PersonalizedProduct::get_instance();
NM_PersonalizedProduct::init();
//nm_personalizedproduct_pa($nmpersonalizedproduct);

if( is_admin() ){

	$_admin = dirname(__FILE__).'/classes/admin.class.php';
	if( file_exists($_admin))
		include_once($_admin );
	else
		die('file not found! '.$_admin);

	$nmpersonalizedproduct_admin = new NM_PersonalizedProduct_Admin();
}


/*
 * activation/install the plugin data
*/
register_activation_hook( __FILE__, array('NM_PersonalizedProduct', 'activate_plugin'));
register_deactivation_hook( __FILE__, array('NM_PersonalizedProduct', 'deactivate_plugin'));