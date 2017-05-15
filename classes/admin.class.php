<?php
/*
 * working behind the seen
 */
class NM_PersonalizedProduct_Admin extends NM_PersonalizedProduct {
	var $menu_pages, $plugin_scripts_admin, $plugin_settings;
	function __construct() {
		
		// setting plugin meta saved in config.php
		$this->plugin_meta = woopa_get_plugin_data();
		
		// getting saved settings
		$this->plugin_settings = get_option ( $this -> plugin_meta['shortname'] . '_settings' );
		
			
		// populating $inputs with NM_Inputs object
		$this -> inputs = self::get_all_inputs ();
		
		/*
		 * [1] TODO: change this for plugin admin pages
		 */
		 if(1){
			
			$this->menu_pages = array (
				array (
						'page_title' => __('PersonalizedWOO', 'nm-personalizedproduct'),
						'menu_title' => __('PersonalizedWOO', 'nm-personalizedproduct'),
						'cap' => 'manage_options',
						'slug' => 'nm-personalizedproduct',
						'callback' => 'product_meta',
						'parent_slug' => 'options-general.php' 
				),
				array (
						'page_title' => __('PersonalizedWOO', 'nm-personalizedproduct'),
						'menu_title' => __('PersonalizedWOO', 'nm-personalizedproduct'),
						'cap' => 'manage_options',
						'slug' => 'nm-personalizedproduct',
						'callback' => 'product_meta',
						'parent_slug' => 'woocommerce' 
				),
			);
		}else{
			
			$this->menu_pages = array (
					array (
							'page_title' => __('PersonalizedWOO', 'nm-personalizedproduct'),
							'menu_title' => __('PersonalizedWOO', 'nm-personalizedproduct'),
							'cap' => 'manage_options',
							'slug' => 'nm-personalizedproduct',
							'callback' => 'activate_plugin',
							'parent_slug' => 'options-general.php'
					),
					);
			
		}


		 
		
		
		/*
		 * [2] TODO: Change this for admin related scripts JS scripts and styles to loaded ADMIN
		 */
		$this->plugin_scripts_admin = array (
				array (
						'script_name' => 'scripts-global',
						'script_source' => '/js/nm-global.js',
						'localized' => false,
						'type' => 'js',
						'page_slug' => 'nm-personalizedproduct' 
				),
				array (
						'script_name' => 'scripts-admin',
						'script_source' => '/js/admin.js',
						'localized' => true,
						'type' => 'js',
						'page_slug' => array (
								'nm-personalizedproduct',
						),
						'depends' => array (
								'jquery',
								'jquery-ui-accordion',
								'jquery-ui-draggable',
								'jquery-ui-droppable',
								'jquery-ui-sortable',
								'jquery-ui-slider',
								'jquery-ui-dialog',
								'jquery-ui-tabs',
						) 
				),
				array (
						'script_name' => 'ui-style',
						'script_source' => '/js/ui/css/smoothness/jquery-ui-1.10.3.custom.min.css',
						'localized' => false,
						'type' => 'style',
						'page_slug' => array (
								'nm-personalizedproduct',
								'nm-new-form' 
						) 
				),
				array (
						'script_name' => 'thickbox',
						'script_source' => 'shipped',
						'localized' => false,
						'type' => 'style',
						'page_slug' => array (
								'nm-new-form'
						)
				),				
				array (
						'script_name' => 'plugin-css',
						'script_source' => '/templates/admin/style.css',
						'localized' => false,
						'type' => 'style',
						'page_slug' => array (
								'nm-personalizedproduct',
								'nm-new-form' 
						) 
				) 
		);
		
		add_action ( 'admin_menu', array (
				$this,
				'add_menu_pages' 
		) );
		
		
		/**
		 * laoding admin scripts only for plugin pages
		 * since 27 september, 2014
		 * Najeeb's 
		 */
		add_action( 'admin_enqueue_scripts', array (
						$this,
						'load_scripts_admin'
						));
		
	}
	
	function load_scripts_admin($hook) {
		
		// loading script for only plugin optios pages
		// page_slug is key in $plugin_scripts_admin which determine the page
		foreach ( $this->plugin_scripts_admin as $script ) {
		
			$attach_script = false;
			if (is_array ( $script ['page_slug'] )) {
					
				foreach( $script ['page_slug'] as $page){
					$script_pages = 'settings_page_'.$page;
					
					if ( $hook == $script_pages){
						$attach_script = true;
					}
				}	
			} else {
				$script_pages = 'settings_page_'.$script ['page_slug'];
				
				if ($hook == $script_pages) {
					$attach_script = true;
				}
			}
				
			//echo 'script page '.$script_pages;
			if( $attach_script ){
				// adding media upload scripts (WP 3.5+)
				wp_enqueue_media();
				
				// localized vars in js
				$arrLocalizedVars = array (
						'plugin_url' => $this->plugin_meta ['url'],
						'doing' => $this->plugin_meta ['url'] . '/images/loading.gif',
						'plugin_admin_page' => admin_url ( 'options-general.php?page=nm-personalizedproduct' )
				);
				
				// checking if it is style
				if ($script ['type'] == 'js') {
					
					$depends = (isset($script['depends']) ? $script['depends'] : NULL);
					wp_enqueue_script ( 'nm-personalizedproduct' . '-' . $script ['script_name'], $this->plugin_meta ['url'] . $script ['script_source'], $depends );
						
					// if localized
					if ($script ['localized'])
						wp_localize_script ( 'nm-personalizedproduct' . '-' . $script ['script_name'], $this -> plugin_meta['shortname'] . '_vars', $arrLocalizedVars );
				} else {
						
					if ($script ['script_source'] == 'shipped')
						wp_enqueue_style ( $script ['script_name'] );
					else
						wp_enqueue_style ( 'nm-personalizedproduct' . '-' . $script ['script_name'], $this->plugin_meta ['url'] . $script ['script_source'] );
				}
			}
		}
		
	}
	
	/*
	 * creating menu page for this plugin
	 */
	function add_menu_pages() {
		foreach ( $this->menu_pages as $page ) {
			
			if ($page ['parent_slug'] == '') {
				
				$menu = add_options_page ( __ ( $page ['page_title'] . ' Settings', 'nm-personalizedproduct' ), __ ( $page ['menu_title'] . ' Settings', 'nm-personalizedproduct' ), $page ['cap'], $page ['slug'], array (
						$this,
						$page ['callback'] 
				), $this->plugin_meta ['logo'], $this->plugin_meta ['menu_position'] );
			} else {
				
				$menu = add_submenu_page ( $page ['parent_slug'], __ ( $page ['page_title'], 'nm-personalizedproduct' ), __ ( $page ['menu_title'] . ' Settings', 'nm-personalizedproduct' ), $page ['cap'], $page ['slug'], array (
						$this,
						$page ['callback'] 
				) );
			}
			
			
		}
	}
	
	
	// ====================== CALLBACKS =================================
	
	
	function product_meta() {
		echo '<div class="wrap">';
		
		if ((!isset ( $_REQUEST ['productmeta_id']))){
			echo '<h2>' . __ ( 'N-Media WooCommerce Personalized Product Option Manager', 'nm-personalizedproduct' ) . '</h2>';
			echo '<p>' . __ ( 'Create different meta groups for different products', 'nm-personalizedproduct' ) . '</p>';
			
			echo '<h2>' . __ ( 'How it works?', 'nm-personalizedproduct' ) . '</h2>';
			echo '<p>' . __ ( 'Once you create meta groups it will be displayed on product edit page on right side panel.', 'nm-personalizedproduct' ) . '</p>';
		}
		
		$action = (isset($_REQUEST ['action']) ? $_REQUEST ['action'] : '');
		if ((isset ( $_REQUEST ['productmeta_id'] ) && $_REQUEST ['do_meta'] == 'edit') || $action == 'new') {
			$this->load_template ( 'admin/create-form.php' );
		} elseif ( isset($_REQUEST ['do_meta']) && $_REQUEST ['do_meta'] == 'clone') {
			$this -> clone_product_meta($_REQUEST ['productmeta_id']);
		}else{
			$url_add = $this->nm_plugin_fix_request_uri ( array (
					'action' => 'new'
			) );
			
			echo '<a class="button button-primary" href="' . esc_url($url_add) . '">' . __ ( 'Add Product Meta Group', 'nm-personalizedproduct' ) . '</a>';
			
			
		}
		
		$this->load_template ( 'admin/existing-meta.php' );
		
		echo '</div>';
	}

	function activate_plugin(){
		
		echo '<div class="wrap">';
		echo '<h2>' . __ ( 'Provide API key below:', 'nm-personalizedproduct' ) . '</h2>';
		echo '<p>' . __ ( 'If you don\'t know your API key, please login into your: <a target="_blank" href="http://wordpresspoets.com/member-area">Member area</a>', 'nm-personalizedproduct' ) . '</p>';
		
		echo '<form onsubmit="return validate_api_wooproduct(this)">';
			echo '<p><label id="plugin_api_key">'.__('Entery API key', 'nm-personalizedproduct').':</label><br /><input type="text" name="plugin_api_key" id="plugin_api_key" /></p>';
			wp_nonce_field();
			echo '<p><input type="submit" class="button-primary button" name="plugin_api_key" /></p>';
			echo '<p id="nm-sending-api"></p>';
		echo '</form>';
		
		echo '</div>';
		
	}

}