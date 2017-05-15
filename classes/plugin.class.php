<?php
/*
 * The base plugin class.
 */


/* ======= the model main class =========== */
if (! class_exists ( 'NM_Framwork_V1' )) {
	$_framework = dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . 'nm-framework.php';
	if (file_exists ( $_framework ))
		include_once ($_framework);
	else
		die ( 'Reen, Reen, BUMP! not found ' . $_framework );
}

/*
 * [1]
 */
class NM_PersonalizedProduct extends NM_Framwork_V1 {
	
	static $tbl_productmeta = 'nm_personalized';
	
	
	/**
	 * this holds all input objects
	 */
	var $inputs;
	
	/**
	 * the static object instace
	 */
	private static $ins = null;
	
	
	public static function init()
	{
		add_action('plugins_loaded', array(self::get_instance(), '_setup'));
	}
	
	public static function get_instance()
	{
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}
	/*
	 * plugin constructur
	 */
	function _setup() {
		
		// setting plugin meta saved in config.php
		
		add_action( 'woocommerce_init', array( $this, 'setup_personalized_plugin' ) );
	}
	
	function setup_personalized_plugin(){
		
		
		$this -> plugin_meta = woopa_get_plugin_data ();
		
		// getting saved settings
		$this -> plugin_settings = get_option ( $this -> plugin_meta['shortname'] . '_settings' );
		
		// file upload dir name
		$this -> product_files = 'product_files';
		
		// this will hold form productmeta_id
		$this -> productmeta_id = '';
		
		// populating $inputs with NM_Inputs object
		$this -> inputs = self::get_all_inputs ();
		//woopa_printA($this->inputs);
		
		/*
		 * [2] TODO: update scripts array for SHIPPED scripts only use handlers
		 */
		// setting shipped scripts
		$this -> wp_shipped_scripts = array (
				'jquery',
				'jquery-ui-datepicker' 
		);


		/*
		 * [3] TODO: update scripts array for custom scripts/styles
		 */
		// setting plugin settings
		$this -> plugin_scripts = array (
				
				array (
						'script_name' => 'ppom-scripts',
						'script_source' => '/js/script.js',
						'localized' => true,
						'type' => 'js',
						'depends'		=> array('jquery', 'thickbox', 'jcrop'),
				),
				
				array (
						'script_name' => 'ppom-conditional',
						'script_source' => '/js/nm-conditional.js',
						'localized' => false,
						'type' => 'js',
						'depends'		=> array('jquery'),
				),
				
				array (
						'script_name' => 'ppom-dynamicprices',
						'script_source' => '/js/nm-dynamicprices.js',
						'localized' => false,
						'type' => 'js',
						'depends'		=> array('jquery'),
				),
				
				array (
						'script_name' => 'styles',
						'script_source' => '/plugin.styles.css',
						'localized' => false,
						'type' => 'style' 
				),
				
				array (
						'script_name' => 'nm-ui-style',
						'script_source' => '/js/ui/css/smoothness/jquery-ui-1.10.3.custom.min.css',
						'localized' => false,
						'type' => 'style',
						'page_slug' => array (
								'nm-new-form' 
						) 
				),

				
		);
		
		/*
		 * [4] Localized object will always be your pluginshortname_vars e.g: pluginshortname_vars.ajaxurl
		 */
		$this -> localized_vars = array (
				'ajaxurl' => admin_url( 'admin-ajax.php', (is_ssl() ? 'https' : 'http') ),
				'plugin_url' => $this -> plugin_meta ['url'],
				'doing' => $this -> plugin_meta ['url'] . '/images/loading.gif',
				'settings' => $this -> plugin_settings,
				'file_upload_path_thumb' => $this -> get_file_dir_url ( true ),
				'file_upload_path' => $this -> get_file_dir_url (),
				'file_meta' => '',
				'section_slides' => '',
				'woo_currency'	=> get_woocommerce_currency_symbol(),
				'mesage_max_files_limit'	=> __(' files allowed only', 'nm-personalizedproduct'),
				'default_error_message'	=> __('it\'s a required field.', 'nm-personalizedproduct'),
		);
		
		/*
		 * [5] TODO: this array will grow as plugin grow all functions which need to be called back MUST be in this array setting callbacks
		 */
		// following array are functions name and ajax callback handlers
		$this -> ajax_callbacks = array (
				'save_settings', // do not change this action, is for admin
				'save_form_meta',
				'update_form_meta',
				'get_option_price',
				'set_matrix_price',
				'validate_api',
				'crop_image_editor',	//loading cropping editor
				'crop_image',			//doing cropping,
				'move_images_admin',	//if images not moved to confirmed dir then admin can do it manually
		);
		
		/*
		 * plugin localization being initiated here
		 */
		add_action ( 'init', array (
				$this,
				'wpp_textdomain' 
		) );
		
		/*
		 * hooking up scripts for front-end
		 */
		add_action ( 'wp_enqueue_scripts', array (
				$this,
				'load_scripts' 
		) );
		
		add_action ( 'wp_enqueue_scripts', array (
		$this,
		'load_scripts_extra'
		) );
		
		
		/*
		 * registering callbacks
		 */
		$this -> do_callbacks ();
		
		/**
		 * change add to cart text on shop page
		 */
		 add_filter('woocommerce_loop_add_to_cart_link', array($this, 'change_add_to_cart_text'), 10, 2);
		 
		 /**
		  * changing price displa on loop for price matrix
		  * */
		  add_filter('woocommerce_get_price_html', array($this, 'change_price_html'), 1, 2);
		
		/*
		 * adding a panel on product single page in admin
		 */
		add_action ( 'add_meta_boxes', array (
				$this,
				'add_productmeta_meta_box' 
		) );
		
		/*
		 * saving product meta in admin/product signel page
		 */
		add_action ( 'woocommerce_process_product_meta', array (
				$this,
				'process_product_meta' 
		), 1, 2 );
		
		/*
		 * 1- redering all product meta front-end
		 */
		add_action ( 'woocommerce_before_add_to_cart_button', array (
				$this,
				'render_product_meta' 
		), 15 );
		
		/*
		 * 2- validating the meta before adding to cart
		 */
		add_filter ( 'woocommerce_add_to_cart_validation', array (
				$this,
				'validate_data_before_cart' 
		), 10, 3 );
		
		/*
		 * 3- adding product meta to cart
		 */
		add_filter ( 'woocommerce_add_cart_item_data', array (
				$this,
				'add_product_meta_to_cart' 
		), 10, 2 );
		
		/*
		 * 4- now loading all meta on cart/checkout page from session confirmed that it is loading for cart and checkout
		 */
		add_filter ( 'woocommerce_get_cart_item_from_session', array (
				&$this,
				'get_cart_session_data' 
		), 10, 2 );
		
		/*
		 * 5- this is showing meta on cart/checkout page confirmed that it is loading for cart and checkout
		 */
		add_filter ( 'woocommerce_get_item_data', array (
				$this,
				'add_item_meta' 
		), 10, 2 );
		
		/*
		 * 6- Adding item_meta to orders 2.0 it is in classes/class-wc-checkout function: create_order() do_action( 'woocommerce_add_order_item_meta', $item_id, $values );
		 */
		add_action ( 'woocommerce_add_order_item_meta', array (
				$this,
				'order_item_meta' 
		), 10, 2 );
		
		/*
		 * 7- Another panel in orders to display files uploaded against each product
		 */
		add_action ( 'admin_init', array (
				$this,
				'render_product_files_in_orders' 
		) );
		
		/*
		 * 7- movnig confirmed/paid orders into another directory
		 * dir_name: confirmed
		*/
		add_action ( 'woocommerce_checkout_order_processed', array (
		$this,
		'move_files_when_paid'
		) );
		
		
		/*
		 * 8- cron job (shedualed hourly)
		 * to remove un-paid images
		 */
		add_action('do_action_remove_images', array($this, 'remove_unpaid_orders_images'));
		
		
		//add_action('setup_styles_and_scripts_wooproduct', array($this, 'get_connected_to_load_it'));
		
		/*
		 * 9- adding file download link into order email
		 */
		add_action('woocommerce_email_after_order_table', array($this, 'add_files_link_in_email'), 10, 2);
		
		/*
		 * 10- adding meta list in product page
		*/
		//add_action( 'restrict_manage_posts', array( $this, 'nm_meta_dropdown' ) );
		
		add_action('admin_footer-edit.php', array($this, 'nm_add_bulk_meta'));
		
		add_action('load-edit.php', array(&$this, 'nm_meta_bulk_action'));
		
		add_action('admin_notices', array(&$this, 'nm_add_meta_notices'));
		
		
		// Add extra fee in cart
		add_action( 'woocommerce_cart_calculate_fees', array($this, 'add_fixed_fee') );
		
		//form post action for importing files in existing-meta.php
		add_action( 'admin_post_nm_importing_file_ppom', array($this, 'process_nm_importing_file_ppom') );
		
	}
	
	/*
	 * ============================================================== All about Admin -> Single Product page ==============================================================
	 */
	 
	 /**
	  * add to cart button text change
	  */
	  function change_add_to_cart_text($button, $product){
	  	
		$selected_meta_id = get_post_meta ( $product->get_id(), '_product_meta_id', true );
		if ($selected_meta_id == 0 || $selected_meta_id == 'None')
			return $button;
			
		
			if (!in_array($product->get_type(), array('variable', 'grouped', 'external'))) {
		        // only if can be purchased
		        if ($selected_meta_id) {
		            // show qty +/- with button
		            $button = sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button %s product_type_%s">%s</a>',
						esc_url( get_permalink($product->get_id()) ),
						esc_attr( $product->get_id() ),
						esc_attr( $product->get_sku() ),
						$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
						esc_attr( 'variable' ),
						esc_html( __('Select options', 'woocommerce') )
					);
		 
		        }
		    }
 
	 		return $button;
	  }
	  
	  function change_price_html($price, $product){
	  	
	  	$selected_meta_id = get_post_meta ( $product->get_id(), '_product_meta_id', true );
		if ($selected_meta_id == 0 || $selected_meta_id == 'None')
			return $price;
			
		
			if (!in_array($product->get_type(), array('variable', 'grouped', 'external'))) {
				if( $selected_meta_id ){
					
					$price_range = array();
					$single_meta = $this -> get_product_meta ( $selected_meta_id );
					$existing_meta = json_decode ( $single_meta->the_meta );
					if($existing_meta){
						foreach($existing_meta as $meta){
							
							if($meta -> type == 'pricematrix'){
								foreach($meta -> options as $matrix_option){
									$price_range[] = $matrix_option->price;
								}
							}
						}
					}
					if($price_range){
						sort($price_range);
						//woopa_printA($price_range);
						$price = wc_price($price_range[0]).'-'.wc_price($price_range[count($price_range)-1]);
					}
				}
				//$price = 'to-from';
			}
		return $price;
	  }
	
	// i18n and l10n support here
	// plugin localization
	function wpp_textdomain() {
		$locale_dir = dirname( plugin_basename( dirname(__FILE__ ) ) ) . '/locale/';
		load_plugin_textdomain('nm-personalizedproduct', false, $locale_dir);
		
		$this -> nm_export_ppom();
	}
	
	/**
	 * Adds meta groups in admin dropdown to apply on products.
	 *
	 */
	function nm_add_bulk_meta() {
		global $post_type;
			
		if($post_type == 'product' and $all_meta = $this -> get_product_meta_all ()) {
			foreach ( $all_meta as $meta ) {
				?>
<script type="text/javascript">
						jQuery(document).ready(function() {
							jQuery('<option>').val('<?php printf(__('nm_action_%d', 'nm-personalizedproduct'), $meta->productmeta_id)?>', 'nm-personalizedproduct').text('<?php _e($meta->productmeta_name)?>').appendTo("select[name='action']");
							jQuery('<option>').val('<?php printf(__('nm_action_%d', 'nm-personalizedproduct'), $meta->productmeta_id)?>').text('<?php _e($meta->productmeta_name)?>').appendTo("select[name='action2']");
						});
					</script>
<?php
			}
			?>
<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery('<option>').val('nm_delete_meta').text('<?php _e('Remove Meta', 'nm-personalizedproduct')?>').appendTo("select[name='action']");
						jQuery('<option>').val('nm_delete_meta').text('<?php _e('Remove Meta', 'nm-personalizedproduct')?>').appendTo("select[name='action2']");
					});
				</script>
<?php
	    }
	}

	function nm_meta_bulk_action() {
		global $typenow;
		$post_type = $typenow;
			
		if($post_type == 'product') {
				
			// get the action
			$wp_list_table = _get_list_table('WP_Posts_List_Table');  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
			$action = $wp_list_table->current_action();
			
			// make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
			if(isset($_REQUEST['post']) && is_array($_REQUEST['post'])){
				$post_ids = array_map('intval', $_REQUEST['post']);
			}
			
			if(empty($post_ids)) return;
			
			// this is based on wp-admin/edit.php
			$sendback = remove_query_arg( array('nm_updated', 'nm_removed', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
			if ( ! $sendback )
				$sendback = admin_url( "edit.php?post_type=$post_type" );
				
			$pagenum = $wp_list_table->get_pagenum();
			$sendback = add_query_arg( 'paged', $pagenum, $sendback );
			
			
			$nm_do_action = ($action == 'nm_delete_meta') ? $action : substr($action, 0, 10);
				
			switch($nm_do_action) {
				case 'nm_action_':
				$nm_updated = 0;
				foreach( $post_ids as $post_id ) {
							
					update_post_meta ( $post_id, '_product_meta_id', substr($action, 10) );
			
					$nm_updated++;
				}
				$sendback = add_query_arg( array('nm_updated' => $nm_updated, 'ids' => join(',', $post_ids)), $sendback );
				break;
				
				case 'nm_delete_meta':
				$nm_removed = 0;
				foreach( $post_ids as $post_id ) {
							
					delete_post_meta ( $post_id, '_product_meta_id' );
			
					$nm_removed++;
				}
				$sendback = add_query_arg( array('nm_removed' => $nm_removed, 'ids' => join(',', $post_ids)), $sendback );
				break;
				
				default: return;
			}
			
			wp_redirect($sendback);
			
			exit();
		}
	}
	/**
	 * display an admin notice on the Products page after updating meta
	 */
	function nm_add_meta_notices() {
		global $post_type, $pagenow;
			
		if($pagenow == 'edit.php' && $post_type == 'product' && isset($_REQUEST['nm_updated']) && (int) $_REQUEST['nm_updated']) {
			$message = sprintf( _n( 'Product meta updated.', '%s Products meta updated.', sanitize_text_field($_REQUEST['nm_updated']) ), number_format_i18n( sanitize_text_field($_REQUEST['nm_updated']) ) );
			echo "<div class=\"updated\"><p>{$message}</p></div>";
		}
		elseif($pagenow == 'edit.php' && $post_type == 'product' && isset($_REQUEST['nm_removed']) && (int) $_REQUEST['nm_removed']){
			$message = sprintf( _n( 'Product meta removed.', '%s Products meta removed.', $_REQUEST['nm_removed'] ), number_format_i18n( sanitize_text_field($_REQUEST['nm_removed']) ) );
			echo "<div class=\"updated\"><p>{$message}</p></div>";	
		}
	}
	 	
	function add_productmeta_meta_box() {
		add_meta_box ( 'woocommerce-image-upload', __ ( 'Select Personalized Meta', 'nm-personalizedproduct' ), array (
				$this,
				'product_meta_box' 
		), 'product', 'side', 'default' );
	}
	function product_meta_box($post) {
		$existing_meta_id = get_post_meta ( $post->ID, '_product_meta_id', true );
		$all_meta = $this -> get_product_meta_all ();
		
		echo '<p>';
		
		
		echo '<select name="nm_product_meta" id="nm_product_meta" class="select">';
		echo '<option selected="selected"> ' . __('None', 'nm-personalizedproduct'). '</option>';
		
		foreach ( $all_meta as $meta ) {
			
			if ($meta->productmeta_id == $existing_meta_id)
				$selected = 'selected="selected"';
			else
				$selected = '';
			
		
			echo '<option value="' . esc_attr($meta->productmeta_id) . '" ' . esc_attr($selected) . ' id="select_meta_group-' . esc_attr($meta->productmeta_id) . '">';
			echo $meta->productmeta_name;
			echo '</option>';
		}
		echo '</select>';
		
		echo '</p>';
	}
	
	
	function get_product_meta_all() {
		
		global $wpdb;
		
		$qry = "SELECT * FROM " . $wpdb->prefix . self::$tbl_productmeta;
		$res = $wpdb->get_results ( $qry );
		
		return $res;
	}
	
	/*
	 * saving meta data against product
	 */
	function process_product_meta($post_id, $post) {
		
		
		/* woopa_printA($_POST); exit; */

		if($_POST ['nm_product_meta'] != '')
			update_post_meta ( $post_id, '_product_meta_id', $_POST ['nm_product_meta'] );
	}
	
	/*
	 * rendering shortcode meat
	 */
	function render_product_meta() {
		
		global $post;
		
		$this -> productmeta_id = get_post_meta ( $post->ID, '_product_meta_id', true );
		
		if ($this -> productmeta_id == 0 || $this -> productmeta_id == 'None')
			return;
		
		// ajax validation script
		$single_form = $this -> get_product_meta ( $this -> productmeta_id );
		wp_enqueue_script( 'woopa-ajax-validation', $this->plugin_meta['url'].'/js/woopa-ajaxvalidation.js', array('jquery'));
			
		$woopa_vars = array('fields_meta' => stripslashes($single_form -> the_meta),
							'default_error_message'	=> __('it\'s a required field.', 'nm-personalizedproduct'));
		wp_localize_script( 'woopa-ajax-validation', 'woopa_vars', $woopa_vars);
		
		
			
		$this -> load_template ( 'render.input.php' );
		
		return false;
	}
	
	/*
	 * validating before adding to cart
	 */
	function validate_data_before_cart($passed, $product_id, $qty) {
		global $woocommerce;
		
		$selected_meta_id = get_post_meta ( $product_id, '_product_meta_id', true );
		if($selected_meta_id == 0 || $selected_meta_id == '')
			return $passed;
		
		$single_meta = $this -> get_product_meta ( $selected_meta_id );
		$existing_meta = json_decode ( $single_meta->the_meta );
		
	
		if( $single_meta -> productmeta_validation == 'yes'){
			
			return $passed;
		}
		
		//woopa_printA($_POST);
		
		if ($existing_meta) {
			foreach ( $existing_meta as $meta ) {
				
				if( isset($meta -> data_name) == NULL )
					continue;
					
				$element_name = strtolower ( preg_replace ( "![^a-z0-9]+!i", "_", $meta->data_name ) );
				$validate_name = '_'.$element_name.'_';
				
				if( !isset($_POST [$element_name]) && !isset($_POST[$validate_name]))
					continue;
					
				if ($meta->type == 'checkbox') {
					
					$element_value = sanitize_text_field($_POST [$element_name]);
					$validate_value = (isset($_POST[$validate_name]) ? sanitize_text_field($_POST[$validate_name]) : '');
					if ($meta->required == 'on' && (count ( $element_value ) == 0 && $validate_value == 'showing')) {
						$passed = false;
						$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
						woopa_wc_add_notice( $error_message );
					} elseif ($meta->min_checked != '' && (count ( $element_value ) < $meta->min_checked)) {
						$passed = false;
						$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
						woopa_wc_add_notice( $error_message );
					} elseif ($meta->max_checked != '' && (count ( $element_value ) > $meta->max_checked)) {
						$passed = false;
						$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
						woopa_wc_add_notice( $error_message );
					}
				} elseif ($meta->type == 'file') {
				
					$element_value = (isset($_POST ['thefile_' . $element_name]) ? sanitize_text_field($_POST['thefile_' . $element_name]) : '');
					$validate_value = (isset($_POST[$validate_name]) ? sanitize_text_field($_POST[$validate_name]) : '');
					if ($meta->required == 'on' && $element_value == '' && $validate_value == 'showing') {
						$passed = false;
						$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
						woopa_wc_add_notice( $error_message );
					}
				} elseif ($meta->type == 'image') {
					$element_value = (isset($_POST [$element_name]) ? sanitize_text_field($_POST [$element_name]) : '');
					$validate_value = (isset($_POST[$validate_name]) ? sanitize_text_field($_POST[$validate_name]) : '');
					
					if ($meta->required == 'on') {
						if (is_array ( $element_value )) {
							
							if (count ( $element_value ) == 0 && $validate_value == 'showing') {
								$passed = false;
								$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
								woopa_wc_add_notice( $error_message );
							}
						} elseif ($element_value == '' && $validate_value == 'showing') {
							$passed = false;
							$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
							woopa_wc_add_notice( $error_message );
						}
					}
					
				} else {
					$element_value = sanitize_text_field ( $_POST [$element_name] );
					$validate_value = (isset($_POST[$validate_name]) ? sanitize_text_field($_POST[$validate_name]) : '');
					if ($meta->required == 'on' && $element_value == '' && $validate_value == 'showing') {
						$passed = false;
						$error_message = ($meta->error_message != '' ? $meta->error_message : sprintf ( __ ( '"%s" is a required field.', 'woocommerce' ), $meta->title ));
						woopa_wc_add_notice( $error_message );
					}
				}
				
			}
		}
		
		return $passed;
	}
	
	
	function get_product_meta($meta_id) {
		
		if( !$meta_id )
			return ;
			
		global $wpdb;
		
		$qry = "SELECT * FROM " . $wpdb->prefix . self::$tbl_productmeta . " WHERE productmeta_id = $meta_id";
		$res = $wpdb->get_row ( $qry );
		
		return $res;
	}
	
	/*
	 * Adding product meta to cart A very important function
	 */
	function add_product_meta_to_cart($the_cart_data, $product_id) {
		global $woocommerce;
		
		// woopa_printA($_POST); exit;
		
		$selected_meta_id = get_post_meta ( $product_id, '_product_meta_id', true );
		
		if($selected_meta_id == 0 || $selected_meta_id == '')
			return $the_cart_data;
		
		/*
		 * now extracting product meta values
		 */
		
		$single_meta = $this -> get_product_meta ( $selected_meta_id );
		
		if( !is_object( $single_meta) )
			return $the_cart_data;
			
			
		$product_meta = json_decode ( $single_meta->the_meta );
		
		$product_meta_data = array (); // this array is giong to be pushed into with data
		
		$all_files = '';
		$price_matrix = '';
		$var_price = 0;
		$fixed_price = 0;
		$file_cost = 0;
		
		if ($product_meta) {
			
			//woopa_printA($product_meta);
			
			$var_price = 0;
			foreach ( $product_meta as $meta ) {
				
				if( !isset($meta->data_name) )
					continue;
					
				$element_name = strtolower ( preg_replace ( "![^a-z0-9]+!i", "_", $meta->data_name ) );
				$element_value = '';
				
				$thefiles_key = 'thefile_'.$element_name;
				if( !isset($_POST [$element_name]) && !isset($_POST [$thefiles_key]) )
					continue;
					
				
				if ($meta->type == 'checkbox') {					
					
					if ($_POST [$element_name])
					
						$element_value = implode ( ",", array_map( 'sanitize_text_field', wp_unslash( $_POST [$element_name] ) ) );
				} else if ($meta->type == 'select' || $meta->type == 'radio') {
					
					$element_value = sanitize_text_field ( $_POST [$element_name] );
				
				} elseif ($meta->type == 'file') {
					
					$element_value = (isset($_POST ['thefile_' . $element_name]) ? sanitize_text_field($_POST['thefile_' . $element_name]) : '');
					
					if($element_value){
						$all_files[$meta -> title] = $element_value;	
						$file_key = __ ( '_File(s) attached', 'nm-personalizedproduct' );
					}
					
				}elseif ($meta->type == 'facebook') {
					
					$element_value = stripslashes( sanitize_text_field($_POST [$element_name]) );
					$element_value = json_decode( $element_value, true);
					

					if($element_value){
						$all_files[$meta -> title] = $this -> save_imported_files( $element_value );	
						$file_key = __ ( '_File(s) attached', 'nm-personalizedproduct' );
					}
					
				}elseif ($meta->type == 'image') {
					
					$element_value = (isset($_POST [$element_name]) ? sanitize_text_field($_POST [$element_name]) : '');

					if($element_value){
						$selected_images = array('type'		=> 'image',
							'selected'	=> $element_value);
												
						//$selected_image_key = __ ( 'Image(s) selected', 'nm-personalizedproduct' );
						$product_meta_data [$meta->title] = $selected_images;
					}
					
				} else {
					//$element_value = sanitize_text_field ( $_POST [$element_name] );
					
				}
				
	
				
				$cart_meta_key = stripslashes( $meta->title );
				// finally saving values into meta array
				if ($meta->type == 'facebook'){
					
					$product_meta_data [$cart_meta_key] = $all_files[$meta -> title];
				}elseif ($meta->type != 'section' && $meta->type != 'image'){
					
					if (is_array($element_value)){
						$product_meta_data [$cart_meta_key] = $element_value;
					}else{
						//$product_meta_data [$cart_meta_key] = stripslashes( nl2br($_POST [$element_name]) );
						$post_element_name = '';

						if (isset($_POST [$element_name]) && is_array($_POST [$element_name])) {
							$post_element_name = array_map( 'sanitize_text_field', wp_unslash( $_POST [$element_name] ) );
							$nele=array();
							foreach($post_element_name as $ele) {
								$ele=stripslashes(nl2br($ele));
								$nele[]=$ele;
							}
							$post_element_name = $nele;
						}else{
							$post_element_name = sanitize_text_field($_POST [$element_name]);
						}
						
						if($post_element_name != ''){
							$product_meta_data [$cart_meta_key] = $post_element_name;
						}
					}	
				}
				
				// calculating price
				/* $var_price += $the_price;
				$the_price = 0; */
				
				
			}
		}
		
		//woopa_printA($product_meta_data); exit;
		//adding attachments
		if($all_files){
			//$product_meta_data [$file_key] = $this -> make_filename_link ( $all_files );
			$product_ref_data ['_product_attached_files'] = $all_files;
		}
			
		
		// options price
		if(isset($_POST['woo_option_price']) && $_POST['woo_option_price'] != 0){
			$var_price = sanitize_text_field($_POST['woo_option_price']);
		}
		
		//fixed_fee
		if(isset($_POST['woo_onetime_fee'])){
			$fixed_price = sanitize_text_field($_POST['woo_onetime_fee']);
		}
		
		//file_fee
		if(isset($_POST['woo_file_cost'])){
			$file_cost = sanitize_text_field($_POST['woo_file_cost']);
		}
		
		
		//price_matrix
		if(isset($_POST['_pricematrix'])){
			$price_matrix = sanitize_text_field($_POST['_pricematrix']);
		}
		
		//woopa_printA($product_meta_data); exit;
		
		$the_cart_data ['product_meta'] = array (
				'meta_data' => $product_meta_data,
				'var_price' => $var_price,
				'fixed_price'	=> stripslashes($fixed_price),
				'file_cost'     => stripslashes($file_cost),
				'price_matrix'	=> stripslashes($price_matrix),
				'_product_attached_files'	=> $all_files
		);
		
		
		// woopa_printA($the_cart_data); exit;
		
		return $the_cart_data;
	}
	
	/*
	 * cart session data Ok, this value is being pulled on Cart/Checkout page
	 */
	function get_cart_session_data($cart_items, $values) {
		                          
		//woopa_printA($cart_items);
		if($cart_items == '')
			return;
			
		if( ! isset($values['product_meta']) )
			return $cart_items;
		
		
		if (isset ( $values ['product_meta'] )) :
			$cart_items ['product_meta'] = $values ['product_meta'];	
		endif;
		
		$var_price = $values['product_meta']['var_price'];
		$cart_price = 0;
		
		
		if( isset($values ['product_meta']['price_matrix']) && $values ['product_meta']['price_matrix'] != NULL ){	
			$cart_price = $this->get_matrix_price($cart_items['quantity'], $values ['product_meta']['price_matrix']);			
		}else{			
			$cart_price = ($cart_items ['data'] -> get_price());
		}
		
		
		if($var_price){
			$cart_price = $cart_price + $var_price;
		}	
		
		
		$cart_items['data'] -> set_price($cart_price);
		
		//woopa_printA($cart_items); exit;
		
		return $cart_items;
	}
	
	
	//Add custom fee to cart automatically
	
	function add_fixed_fee($cart_object) {
	
		$custom_price = 0; // This will be your custome price
		foreach ( $cart_object->cart_contents as $key => $value ) {
	
			if( !isset( $value['product_meta']) )
				return;	    
		    //woopa_printA($value['product_meta']);
		    
			$fixed_price = json_decode($value['product_meta']['fixed_price'], true);
			$file_cost   = json_decode($value['product_meta']['file_cost'], true);
			
			if ($fixed_price){
				
				foreach ($fixed_price as $title => $fixed){
					
					$taxable = ($fixed['taxable'] == 'on' ? true : false);
					if(isset($fixed['fee']) && $fixed['fee'] != '')
						$cart_object -> add_fee( __( esc_html($title), 'woocommerce'), intval($fixed['fee']), $taxable );
				}
			}
			
			$custom_price = '';
			$custom_title = '';
			if ($file_cost){
				
				foreach ($file_cost as $option => $fixed){
					
					$fixed_fee 	 = (isset($fixed['fee']) ? $fixed['fee'] : 0);
					$fee_taxable = (isset($fixed['taxable']) ? true : true);
					$custom_price += $fixed_fee;
					$custom_title .= $option;
					$taxable = $fee_taxable;
					
				
					if($custom_price)
						$cart_object -> add_fee( __( esc_html($custom_title), 'woocommerce'), $custom_price, $taxable );
				}
			}
		}
	
	}
	
	/*
	 * this function is showing item meta on cart/checkout page
	 */
	function add_item_meta($item_meta, $existing_item_meta) {
		
		//woopa_printA($existing_item_meta ['product_meta']['meta_data']);
		
		if( ! isset($existing_item_meta['product_meta']) )
			return $item_meta;
			
		
		if ($existing_item_meta ['product_meta']['meta_data']) {
			foreach ( $existing_item_meta ['product_meta'] ['meta_data'] as $key => $val ) {
				
				if(isset($val)){
					if (is_array($val)) {
						
						$data_type = (isset($val['type']) ? $val['type'] : '');
						
						if($data_type == 'image'){
							
							// if selected designs are more then one
							if(is_array($val['selected'])){
								
								$_v = '';
								foreach ($val['selected'] as $selected){
									$selecte_image_meta = json_decode(stripslashes( $selected ));
									$_v .= $selecte_image_meta -> title.',';
								}
								$item_meta [] = array (
										'name' => $key,
										'value' => __('Photos imported - ', 'nm-personalizedproduct') . count($val['selected']),
								);
							}else{
								$selecte_image_meta = json_decode(stripslashes( $val['selected'] ));
								$item_meta [] = array (
										'name' => $key,
										'value' => $selecte_image_meta -> title
								);
							}
						}else{
							//woopa_printA($val);
							list($filekey, $filename) = each($val);
							if( $this->is_image( $filename )){
								$item_meta [] = array (
										'name' => $key,
										'value' => $this -> make_filename_link ( $val ),
								);
							}else{
								$item_meta [] = array (
										'name' => $key,
										'value' => implode(',', $val),
								);
							}
						}
						
					}else{
						$item_meta [] = array (
								'name' => $key,
								'value' => stripslashes( $val ),
						);
					}
				}
					
			}
		}
		
		//woopa_printA($item_meta); exit;
		return $item_meta;
	}
	
	/*
	 * Adding item meta to order from $cart_item On checkout page, saving meta from CART to ITEM__ORDER
	 */
	function order_item_meta($item_id, $cart_item) {

		 // removing the _File(s) attached key
		 if (isset( $cart_item ['product_meta'] ['meta_data']['_File(s) attached'] )) {
		 	unset( $cart_item ['product_meta'] ['meta_data']['_File(s) attached']);
		 }
		 
		//woopa_printA($cart_item); exit;
		
		if (isset ( $cart_item ['product_meta'] )) {
			
			foreach ( $cart_item ['product_meta'] ['meta_data'] as $key => $val ) {
				// $item_meta->add( $key, $val );
				
				if (is_array($val)) {
					if($val['type'] == 'image'){
							
						// if selected designs are more then one
						
						$order_val = '';
						
						if(is_array($val['selected'])){
				
							$_v = '';
							foreach ($val['selected'] as $selected){
								$selecte_image_meta = json_decode(stripslashes( $selected ));
								$_v .= $selecte_image_meta -> title.',';
							}
							
							$order_val = $_v;
						}else{
							$selecte_image_meta = json_decode(stripslashes( $val['selected'] ));
							$order_val = $selecte_image_meta -> title;
						}
						
						
					}else{
						
						$order_val = implode(',', $val);
					}
				
				}else{
				
					$order_val = stripslashes( $val );
				}
				
				if($val){
					woopa_wc_add_order_item_meta ( $item_id, $key, $order_val );
				}
			}
			
			// adding _product_attached_files
			woopa_wc_add_order_item_meta ( $item_id, '_product_attached_files', $cart_item ['product_meta']['_product_attached_files'] );
			
		}
	}
	
	
	/*
	 * make filename linkable used in cart data
	 */
	function make_filename_link($filenames) {

		$linkable = '';
		
		if ($filenames) {
				
				foreach ( $filenames as $key => $filename ) {
					
					$ext = strtolower ( substr ( strrchr ( $filename, '.' ), 1 ) );
					
					if ($ext == 'png' || $ext == 'jpg' || $ext == 'gif' || $ext == 'jpeg'){
						$src_thumb = $this->get_file_dir_url ( true ) . $filename;
						
					}else{
						$src_thumb = $this->plugin_meta ['url'] . '/images/file.png';
					}
					
					
					$edited_file = $this->get_file_dir_path () . 'edits/' . $filename;
					
					if (file_exists ( $edited_file )) {
						$file_link = $this->get_file_dir_url () . 'edits/' . $filename;
						
						$edited_thumb_path = $this->get_file_dir_path () . 'edits/thumbs/' . $filename;
						if (file_exists ( $edited_thumb_path ))
							$src_thumb = $this->get_file_dir_url () . 'edits/thumbs/' . $filename;
							
					} else {
						$file_link = $this->get_file_dir_url () . $filename;
					}
					
					$img = '<img src="' . esc_url($src_thumb) . '" alt="uploaded file">';
					
					$linkable .= '<a href=' . esc_url($file_link) . ' class="lightbox" itemprop="image" title="' . esc_attr($filename) . '">' . $img . '</a>';
					/* Trimming Filename $filename */
					$textLength = strlen($filename);
					$maxChars = 16;

					$trimmed_filename = substr_replace($filename, '...', $maxChars/2, $textLength-$maxChars);					
					$linkable .= ' ' . $trimmed_filename . '<br>';
				}
			
			return $linkable;
		}
		
	}

	/**
	 * saving fb imported files locally
	 */
	 function save_imported_files($imported_files){
		
		$saved_files = array();
		foreach( $imported_files as $key => $src){
			
			$image_url = preg_replace('/\?.*/', '', $src);
			$file_name = basename($image_url);
			
			$destination = $this -> setup_file_directory(). $file_name;
			//
			if( copy($src, $destination) ){
				$this->create_thumb($this->get_file_dir_path (), $file_name, 175);
				$saved_files[$key] = $file_name;
			}else{
				file_put_contents($destination, file_get_contents($src));
			}
		}
		
		return $saved_files;
		
	}
	
	/*
	 * rendering meta box in orders
	 */
	function render_product_files_in_orders() {
		add_meta_box ( 'orders_product_file_uploaded', __('Files attached/uploaded against Products','nm-personalizedproduct'),
					array ($this, 'display_uploaded_files'), 
					'shop_order', 'normal', 'default' );
		
		
		// adding meta box for pre-defined images selection
		add_meta_box ( 'selected_images_in_orders', __('Selected images/designs', 'nm-personalizedproduct'), 
						array ( $this, 'display_selected_files'),
						'shop_order', 'normal', 'default' );
	}
	
	
	function display_uploaded_files($order) {
		
		global $wpdb;
		$files_found = 0;
		$order_items = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d", $order->ID ) );
		
		$order = new WC_Order ( $order->ID );
		//woopa_printA($order);
		if (sizeof ( $order->get_items () ) > 0) {
			foreach ( $order->get_items () as $item ) {
				
				/* get_metadata( 'order_item', $item_id, $key, $single );
				$all_files = wc_get_order_item_meta($item ['product_id'], 'Your title', true);
				woopa_printA($item); */
				
				$selected_meta_id = get_post_meta ( $item ['product_id'], '_product_meta_id', true );
				
				$single_meta = $this -> get_product_meta ( $selected_meta_id);
				$product_meta = json_decode ( $single_meta->the_meta );

				//woopa_printA($item);
				if($product_meta){
					
					foreach ( $product_meta as $meta => $data ) {
					
						if ($data -> type == 'file' || $data -> type == 'facebook') {
							
							$product_files = unserialize( $item['product_attached_files'] );	//explode(',', $item[$data -> title]);
							$product_files = $product_files[$data -> title];
							$product_id = $item ['product_id'];
							
							//woopa_printA($product_files);
					
							if ($product_files) {
								
								
								echo '<strong>';
								printf(__('File attached %s', 'nm-personalizedproduct'), $data -> title);
								echo '</strong>';
									
								
								foreach ( $product_files as $file ) {
					
									$files_found++;
									$ext = strtolower ( substr ( strrchr ( $file, '.' ), 1 ) );
					
									if ($ext == 'png' || $ext == 'jpg' || $ext == 'gif' || $ext == 'jpeg')
										$src_thumb = $this -> get_file_dir_url ( true ) . $file;
									else
										$src_thumb = $this -> plugin_meta ['url'] . '/images/file.png';
					
									
									$src_file = '';
									$org_path = $this -> get_file_dir_path () . $file;
									$file_name = $order -> id . '-' . $product_id . '-' . $file;		// from version 3.4
									$confirmed_path = $this -> get_file_dir_path () . 'confirmed/' . $file_name;
									if(file_exists($org_path)){
										if(rename ( $org_path, $confirmed_path ))
											$src_file = $this -> get_file_dir_url () . 'confirmed/' . $file_name;										
									}elseif(file_exists($confirmed_path)){
										$src_file = $this -> get_file_dir_url () . 'confirmed/' . $file_name;	
									}else{
										$src_file = $this -> get_file_dir_url () . $file;
									}
									
					
									echo '<table>';
									echo '<tr><td width="100"><img src="' . esc_url($src_thumb) . '"><td><td><a href="' . esc_url($src_file) . '">' . __ ( 'Download ' ) . $file_name . '</a> ' . $this -> size_in_kb ( $file_name ) . '</td>';
									
									$edited_path = $this->get_file_dir_path() . 'edits/' . $file_name;
									if (file_exists($edited_path)) {
										$file_url_edit = $this->get_file_dir_url () .  'edits/' . $file_name;
										echo '<td><a href="' . esc_url($file_url_edit) . '" target="_blank">' . __ ( 'Download edited image', $this->plugin_meta ['shortname'] ) . '</a></td>';
									}
									
									$cropped_path = $this -> setup_file_directory('cropped') . $file;
									if (file_exists($cropped_path)) {
										$file_url_cropped = $this->get_file_dir_url () .  'cropped/' . $file;
										echo '<td><a href="' . esc_url($file_url_cropped) . '" target="_blank">' . __ ( 'Download cropped image', $this->plugin_meta ['shortname'] ) . '</a></td>';
									}
									
									echo '</tr>';
									echo '</table>';
								}


							}

							 if ($files_found == 0){
									
								echo __ ( 'No file attached/uploaded', 'nm-personalizedproduct' );
							}
						}
					}
				}
				
			}
		}
	}
	
	
	function display_selected_files($order) {
		// woo_pa($order);
		global $wpdb;
		$order_items = $wpdb->get_results ( $wpdb->prepare ( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d", $order->ID ) );
	
		$order = new WC_Order ( $order->ID );
	
		if (sizeof ( $order->get_items () ) > 0) {
			foreach ( $order->get_items () as $item ) {
	
				//woopa_printA($item);
	
				$selected_meta_id = get_post_meta ( $item ['product_id'], '_product_meta_id', true );
	
				$single_meta = $this -> get_product_meta ( $selected_meta_id);
				$product_meta = json_decode ( $single_meta->the_meta );
	
				echo '<h2>' . __ ( 'Selected pre defined image: ' . $item ['name'], 'nm-personalizedproduct' ) . '</h2>';
				echo '<p>';
				// woopa_printA($product_meta);
				if($product_meta){
						
					foreach ( $product_meta as $meta => $data ) {
							
						if ($data -> type == 'image') {
							
							$product_files = $item[$data -> title];
							
							$product_files = explode( ',', $product_files  );
							if ($product_files) {
									
								echo '<h3>' . $data -> title . '</h3>';

								//woopa_printA($data ->images);
								
								foreach ( $data ->images as $all_images ) {
									
									$selected_file = '';
									
									if ( in_array($all_images -> title, $product_files)) {
										$selected_file = $all_images -> link;
									}
									
									
									if ( $selected_file ) {
										
										$ext = strtolower ( substr ( strrchr ( $selected_file, '.' ), 1 ) );
										
										if ($ext == 'png' || $ext == 'jpg' || $ext == 'gif')
											$src_thumb = $this -> get_file_dir_url ( true ) . $selected_file;
										else
											$src_thumb = $this -> plugin_meta ['url'] . '/images/file.png';
										
										$src = $selected_file;
										
										echo '<table>';
										echo '<tr><td width="100"><img width="250" src="' . esc_url($src) . '"><td><td><a href="' . esc_url($src) . '">' . __ ( 'Download ' ) . $file . '</a></td>';
										
										echo '</tr>';
										echo '</table>';
									}
									
								}
								
							} else {
									
								echo __ ( 'No file selected', 'nm-personalizedproduct' );
							}
						}
					}
				}
	
				echo '</p>';
			}
		}
	}
	
	
	function size_in_kb($file_name) {
		
		$base_dir = $this -> get_file_dir_path ();
		$file_path = $base_dir . 'confirmed/' . $file_name;
		
		if (file_exists($file_path)) {
			$size = filesize ( $file_path );
			return round ( $size / 1024, 2 ) . ' KB';
		}elseif(file_exists( $base_dir . '/' . $file_name ) ){
			$size = filesize ( $base_dir . '/' . $file_name );
			return round ( $size / 1024, 2 ) . ' KB';
		}
		
	}
	
	/*
	 * saving form meta in admin call
	 */
	function save_form_meta() {
		
		// print_r($_REQUEST); exit;
		//woopa_printA($product_meta);
		global $wpdb;
		
		extract ( $_REQUEST );
		
		$dt = array (
				'productmeta_name'          => sanitize_text_field($_REQUEST['productmeta_name']),
				'productmeta_validation'	=> sanitize_text_field($_REQUEST['productmeta_validation']),
                'dynamic_price_display'     => sanitize_text_field($_REQUEST['dynamic_price_hide']),
                'show_cart_thumb'			=> sanitize_text_field($_REQUEST['show_cart_thumb']),
				'aviary_api_key'            => '',
				'productmeta_style'         => sanitize_text_field($_REQUEST['productmeta_style']),
				'the_meta'                  => json_encode ( $_REQUEST['product_meta'] ),
				'productmeta_created'       => current_time ( 'mysql' )
		);
		
		$format = array (
				'%s',
				'%s',
				'%s',
                '%s',
				'%s',
				'%s',
				'%s' 
		);
		
		$res_id = $this -> insert_table ( self::$tbl_productmeta, $dt, $format );
		
		/* $wpdb->show_errors(); $wpdb->print_error(); */
		
		$resp = array ();
		if ($res_id) {
			
			$resp = array (
					'message' => __ ( 'Form added successfully', 'nm-personalizedproduct' ),
					'status' => 'success',
					'productmeta_id' => $res_id 
			);
		} else {
			
			$resp = array (
					'message' => __ ( 'Error while savign form, please try again', 'nm-personalizedproduct' ),
					'status' => 'failed',
					'productmeta_id' => '' 
			);
		}
		
		echo json_encode ( $resp );
		
		die ( 0 );
	}
	
	/*
	 * updating form meta in admin call
	 */
	function update_form_meta() {
		
		// print_r($_REQUEST); exit;
		global $wpdb;
		
		extract ( $_REQUEST );
		
		//woopa_printA($product_meta); exit;
		
		$dt = array (
				'productmeta_name'          => sanitize_text_field($_REQUEST['productmeta_name']),
				'productmeta_validation'	=> sanitize_text_field($_REQUEST['productmeta_validation']),
                'dynamic_price_display'     => sanitize_text_field($_REQUEST['dynamic_price_hide']),
                'show_cart_thumb'			=> sanitize_text_field($_REQUEST['show_cart_thumb']),
				'aviary_api_key'            => '',
				'productmeta_style'         => sanitize_text_field($_REQUEST['productmeta_style']),
				'the_meta'                  => json_encode ( $_REQUEST['product_meta'] ),
		);
		
		$where = array (
				'productmeta_id' => $productmeta_id 
		);
		
		$format = array (
				'%s',
				'%s',
                '%s',
                '%s',
				'%s',
				'%s' 
		);
		$where_format = array (
				'%d' 
		);
		
		$res_id = $this -> update_table ( self::$tbl_productmeta, $dt, $where, $format, $where_format );
		
		// $wpdb->show_errors(); $wpdb->print_error();
		
		$resp = array ();
		if ($res_id) {
			
			$resp = array (
					'message' => __ ( 'Form updated successfully', 'nm-personalizedproduct' ),
					'status' => 'success',
					'productmeta_id' => $productmeta_id 
			);
		} else {
			
			$resp = array (
					'message' => __ ( 'Error while updating form, please try again', 'nm-personalizedproduct' ),
					'status' => 'failed',
					'productmeta_id' => $productmeta_id 
			);
		}
		
		echo json_encode ( $resp );
		
		die ( 0 );
	}
	
	
	
	
	/*
	 * rendering template against shortcode
	 */
	function render_shortcode_template($atts) {
		extract ( shortcode_atts ( array (
				'productmeta_id' => '' 
		), $atts ) );
		
		$this -> productmeta_id = $productmeta_id;
		
		ob_start ();
		
		$this -> load_template ( 'render.input.php' );
		
		$output_string = ob_get_contents ();
		ob_end_clean ();
		
		return $output_string;
	}
	
	
	/*
	 * returning price for option in wc price format
	 */
	function get_option_price(){

		// woopa_printA($_REQUEST); exit;
		
		extract($_REQUEST);
		
		$selected_meta_id = get_post_meta ( intval($_REQUEST['product_id']), '_product_meta_id', true );
		$single_meta = $this -> get_product_meta ( $selected_meta_id );
	
		$html = '';
		$option_total_price = 0;
		$fixed_fee = 0;
		$fixed_fee_meta = array();
		if(isset($_REQUEST['optionprices'])){
			foreach ($_REQUEST['optionprices'] as $pair){
	
				$option 		= (isset($pair['option']) ? sanitize_text_field($pair['option']) : '');
				$price 			= (isset($pair['price']) ? sanitize_text_field($pair['price']) : '');
				$onetime 		= (isset($pair['isfixed']) ? sanitize_text_field($pair['isfixed']) : '');
				$onetime_taxable= (isset($pair['fixedfeetaxable']) ? sanitize_text_field($pair['fixedfeetaxable']) : '');
				
				//if price is %
				if(strpos($price,'%') !== false){
					
					$percent_price = $price;
					$price = (intval($price) / 100) * $baseprice;
					$price = number_format( $price, 2, '.', '' );
					$html .= $option . ' ('.$percent_price.') ' . wc_price($price) . '<br>';
				}else{
					$html .= $option . ' ' . wc_price($price) . '<br>';
				}
		
				
				if($onetime){
					$fixed_fee += $price;
					$fixed_fee_meta[$option] = array('fee' => $price, 'taxable' => $onetime_taxable);
				}else{
					
					$option_total_price += $price;
				}
			}
		}
		
		$pricematrix = (isset($_REQUEST['pricematrix']) ? sanitize_text_field($_REQUEST['pricematrix']) : '');
		if($pricematrix){
			$baseprice = $this -> get_matrix_price($qty, stripslashes($pricematrix));
		}
		if($pricematrix){
			$baseprice = $this -> get_matrix_price($qty, stripslashes($pricematrix));
		}
		
		//checking if it's a variation
		//getting options
		$variation_price = '';
		if(isset($variation_id) && $variation_id != ''){
			$product_variation = new WC_Product_Variation( $variation_id );
			$variation_price = $product_variation -> get_price();
			$baseprice = $variation_price;
		}
		
		
		$total_price = $option_total_price + $baseprice;
		
		$html .= '<strong>' . __('Total: ', 'nm-personalizedproduct') . wc_price($total_price) . '</strong>';
		
		
		
		$option_prices = array(	'prices_html' 	=> $html, 
								'option_total'	=> $option_total_price,
								'total_price' 	=> $total_price,
								'onetime_fee'	=> $fixed_fee,
								'onetime_meta'	=> $fixed_fee_meta,
								'variation_price' => $variation_price,
								'display_price_hide' => $single_meta -> dynamic_price_display);
		
		
		echo json_encode($option_prices);
		
		die(0);
	}
	
	/*
	 * setting price based on matrix
	 */
	function get_matrix_price($qty, $pricematrix){
		
		
		$pricematrix = json_decode( $pricematrix, true);
		$last_range2 = 0;
		$price		= 0;
		foreach ($pricematrix as $mx){
			
			$mtx = explode('-', $mx['option']);
			$price = $mx['price'];
			
			
			$range1 = $mtx[0];	
			
			if(!isset($mtx[1]))
				$range2 = $mtx[0];
			else
				$range2 = $mtx[1];
			
			//echo 'r1 '.$range1. ' $r2 '.$range2.' qty '.$qty;
			if($qty >= $range1 && $qty <= $range2){
				
				$price_set = $price;
				break;
			}
			
			$last_range2 = $range2;
		}
		
		if( $qty > $last_range2){
			$price_set = $price;
		}
		
		return $price_set;
		
	}
	
	
	/*
	 * this function is setting up product price is matrix is found
	 */
	function set_matrix_price(){

		$price_matrix = json_decode( stripslashes( sanitize_text_field($_REQUEST['matrix']) ));
		//print_r($price_matrix);
		$last_index = count($price_matrix) - 1;
		
		$html = wc_price($price_matrix[0]->price).' - '.wc_price($price_matrix[$last_index]->price);
		
		echo $html;
		
		die(0);
	}
	
	
	
	/*
	 * deleting uploaded file from directory
	 */
	function delete_file() {
		$dir_path = $this -> setup_file_directory ();
		$file_name = sanitize_text_field($_REQUEST ['file_name']);
		$file_path = $dir_path . $file_name;
		
		if (unlink ( $file_path )) {
			
			if ($this -> is_image($file_name)){
				$thumb_path = $dir_path . 'thumbs/' . $file_name;
				if(file_exists($thumb_path))
					unlink ( $thumb_path );
				
				$cropped_image_path = $dir_path . 'cropped/' . $file_name;
				if(file_exists($cropped_image_path))
					unlink ( $cropped_image_path );
			}
			
			_e( 'File removed', 'nm-personalizedproduct' );
			
				
		} else {
			printf(__('Error while deleting file %s', 'nm-personalizedproduct'), $file_path );
		}
		
		die ( 0 );
	}
	
	/**
	 * it will return html template of uploaded file
	 * to preview
	 */
	function uploaded_html($file_dir_path, $file_name, $is_image, $settings){
		
		$thumb_url = $file_meta = $file_tools = $_html = '';
		
		$settings = json_decode(stripslashes($settings), true);
		//$this -> pa($settings);
		$file_id = 'thumb_'.time();

		if($is_image){
			
			list($fw, $fh) 	= getimagesize( $file_dir_path . $file_name );
			$file_meta		= $fw . '(w) x '.$fh.'(h)';
			$file_meta		.= ' - '.__('Size: ', 'nmpersonalizedproduct') . $this->size_in_kb($file_name);
			
			$thumb_url = $this -> get_file_dir_url ( true ) . $file_name . '?nocache='.time();
			
			//large view
			$image_url = $this -> get_file_dir_url() . $file_name . '?nocache='.time();
			$_html .= '<div style="display:none" id="u_i_c_big_'.$file_id.'"><p id="thumb-thickbox"><img src="'.$image_url.'" /></p></div>';
			
			$tb_height 	= (isset($settings['popup-height']) && $settings['popup-height'] != '' ? $settings['popup-height'] : 400);
			$tb_width	= (isset($settings['popup-width']) && $settings['popup-width'] != '' ? $settings['popup-width'] : 600);
			$file_tools .= '<a href="#" class="nm-file-tools u_i_c_tools_del" title="'.__('Remove', 'nm-personalizedproduct').'"><span class="fa fa-times"></span></a>';	//delete icon
			$file_tools .= '<a href="#TB_inline?width='.esc_attr($tb_width).'&height='.esc_attr($tb_height).'&inlineId=u_i_c_big_'.esc_attr($file_id).'" class="nm-file-tools u_i_c_tools_zoom thickbox" title="'.sprintf(__('%s', 'nm-personalizedproduct'), esc_attr($file_name)).'"><span class="fa fa-expand"></span></a>';	//big view icon
			
				
			
		}else{
			
			$file_meta		.= __('Size: ', 'nm-personalizedproduct') . $this->size_in_kb($file_name);
			$thumb_url = $this -> plugin_meta['url'] . '/images/file.png';
			
			$file_tools .= '<a class="nm-file-tools u_i_c_tools_del" href="" title="'.__('Remove', 'nm-personalizedproduct').'"><span class="fa fa-times"></span></a>';	//delete icon
		}
		
				
		$_html .= '<table class="uploaded-files-box"><tr>';
		$_html .= '<td style="vertical-align:middle"><img data-filename="'.esc_attr($file_name).'" id="'.esc_attr($file_id).'" src="'.esc_url($thumb_url).'" />';
		
		$textLength = strlen($file_name);
		$maxChars = 16;
		$trimmed_filename = substr_replace($file_name, '...', $maxChars/2, $textLength-$maxChars);
		$_html .= '<td class="nm-imagetools" style="padding-left: 5px; vertical-align:top"><h4>'.$trimmed_filename.'</h4><br>';
		$_html .= '<span class="file-meta">'.$file_meta.'</span><br>';
		$_html .= $file_tools;
		$_html .= '</td>';
		
		$_html .= '</tr></table>';
		
		return $_html;
	}
	
	
	
	/*
	 * 9- adding files link in order email
	 */
	function add_files_link_in_email($order, $is_admin){
		
		if (sizeof ( $order->get_items () ) > 0) {
			foreach ( $order->get_items () as $item ) {
		
				// woopa_printA($item);
		
				$selected_meta_id = get_post_meta ( $item ['product_id'], '_product_meta_id', true );
				if ($selected_meta_id == 0 || $selected_meta_id == 'None')
					continue;
					
		
				$single_meta = $this -> get_product_meta ( $selected_meta_id);
				$product_meta = json_decode ( $single_meta->the_meta );
		
				// woopa_printA($product_meta);
				if($product_meta){
						
					foreach ( $product_meta as $meta => $data ) {
							
						if ($data -> type == 'file' || $data -> type == 'facebook') {
							
							$product_files = unserialize( $item['product_attached_files'] );	//explode(',', $item[$data -> title]);
							$product_files = $product_files[$data -> title];
							$product_id = $item ['product_id'];
								
							if ($product_files) {
								
								echo '<strong>';
								printf(__('File attached %s', 'nm-personalizedproduct'), $data->title);
								echo '</strong>';
									
									
								foreach ( $product_files as $file ) {
										
									$files_found++;
									$ext = strtolower ( substr ( strrchr ( $file, '.' ), 1 ) );
										
									if ($ext == 'png' || $ext == 'jpg' || $ext == 'gif' || $ext == 'jpeg')
										$src_thumb = $this -> get_file_dir_url ( true ) . $file;
									else
										$src_thumb = $this -> plugin_meta ['url'] . '/images/file.png';
										
									$src_file = $this -> get_file_dir_url () . $file;
										
									if(!file_exists($src_file)){
										$file_name = $order -> id . '-' . $product_id . '-' . $file;		// from version 3.4
										$src_file = $this -> get_file_dir_url () . 'confirmed/' . $file_name;
									}else{
										$file_name = $file;
										$src_file = $this -> get_file_dir_url () . '/' . $file_name;
									}
										
										
									echo '<table>';
									echo '<tr><td width="100"><img src="' . esc_url($src_thumb) . '"><td><td><a href="' . esc_url($src_file) . '">' . __ ( 'Download ' ) . $file_name . '</a> ' . $this -> size_in_kb ( $file_name ) . '</td>';
										
									$edited_path = $this->get_file_dir_path() . 'edits/' . $file;
									if (file_exists($edited_path)) {
										$file_url_edit = $this->get_file_dir_url () .  'edits/' . $file;
										echo '<td><a href="' . esc_url($file_url_edit) . '" target="_blank">' . __ ( 'Download edited image', $this->plugin_meta ['shortname'] ) . '</a></td>';
									}
										
									echo '</tr>';
									echo '</table>';
								}
							}
		
							
						}
					}
				}
		
			}
		}
	}

	function crop_image_editor(){

		/*
		 * loading uploader template
		 */

		$ratio = json_decode( stripslashes( sanitize_text_field($_REQUEST['ratios']) ) );
		//var_dump($ratio);
		$vars = array('image_name' => sanitize_file_name($_REQUEST['image_name']), 
					'image_url' => esc_url($_REQUEST['image_url']), 
					'ratio' => $ratio, 
					'fileid' => intval($_REQUEST['file_id'])
					);
		$this -> load_template( 'crop_image.php', $vars);
		
		die(0);
	}
	
	
	function crop_image(){

		//print_r($_REQUEST); exit;
		
		$cropped_name = sanitize_file_name($_REQUEST['image_name']);
		$image_path = $this -> get_file_dir_path() . $cropped_name;
		$cropped_dest = $this -> setup_file_directory('cropped') . $cropped_name;
		
		
		
		$image = wp_get_image_editor ( $image_path );
		//$crop_coords = array($_REQUEST['coords']['x'])
		if (! is_wp_error ( $image )) {
							
			$real_size = $image->get_size();	
			$factor_x = $real_size['width']/intval($_REQUEST['img_w']);
			$factor_y = $real_size['height']/intval($_REQUEST['img_h']);
			
			$real_x = intval($_REQUEST['coords']['x']) * $factor_x;
			$real_y = intval($_REQUEST['coords']['y'])* $factor_y;
			$real_w = ( intval($_REQUEST['coords']['x2']) * $factor_x) - $real_x;
			$real_h = ( intval($_REQUEST['coords']['y2']) * $factor_y) - $real_y;
			
			/*echo 'factorx '.$factor_x.' factorY: '.$factor_y;
			echo '<br>';
			echo 'realX: '.$real_x.' realY: '.$real_y;
			echo '<br>';
			echo 'realW: '.$real_w.' realH: '.$real_h;
			exit;*/
			
			
			$image->crop ( $real_x, $real_y, $real_w, $real_h);
			
			//$image->crop ( 130, 110, 107, 145, NULL, NULL, false );
			$cropped_image = $image->save ( $cropped_dest );
			
			//also saving thumb
			$new_thumb = wp_get_image_editor ( $cropped_dest );
			$cropped_thumb_name = sanitize_text_field($_REQUEST['image_name']);
			$cropped_thumb_dest = $this -> get_file_dir_path() . 'thumbs/' . $cropped_thumb_name;
			if (! is_wp_error ( $new_thumb )) {
				$new_thumb->resize ( 75, 75 );
				$new_thumb->save ( $cropped_thumb_dest );
			}else{
				die('error while loading image '.$image_path);
			}
		}else{
			die('error while loading image '.$image_path);
		}
		
		//$the_cropped  = wp_crop_image($image_path, $_REQUEST['coords']['x'], $_REQUEST['coords']['y'], $_REQUEST['coords']['w'], $_REQUEST['coords']['h'], NULL, NULL, false);
		$thumb_url = $this -> get_file_dir_url(true) . $cropped_thumb_name . '?nocache='.time();
		echo json_encode(array('fileid' => (int) $_REQUEST['fileid'], 'cropped_image' => $thumb_url));
		die(0);
	}
	
	// ================================ SOME HELPER FUNCTIONS =========================================
	
	/*
	 * simplifying meta for admin view in existing-meta.php
	 */
	function simplify_meta($meta) {
		//echo $meta;
		$metas = json_decode ( $meta );
		
		if ($metas) {
			echo '<ul>';
			foreach ( $metas as $meta => $data ) {
				
				//woopa_printA($data);
				$req = (isset( $data -> required ) && $data -> required == 'on') ? 'yes' : 'no';
				$title = (isset( $data -> title )  ? $data -> title : '');
				$type = (isset( $data -> type )  ? $data -> type : '');
				$options = (isset( $data -> options )  ? $data -> options : '');
				
				echo '<li>';
				echo '<strong>label:</strong> ' . $title;
				echo ' | <strong>type:</strong> ' . $type;
				
				if (! is_object ( $options) && is_array ( $options )){
					echo ' | <strong>options:</strong> ';
					foreach($options as $option){
						echo $option -> option . ' (' .$option -> price .'), ';
					}
				}
				
					
				echo ' | <strong>required:</strong> ' . $req;
				echo '</li>';
			}
			
			echo '</ul>';
		}
	}
	
	/*
	 * delete meta
	 */
	function delete_meta() {
		global $wpdb;
		
		// extract ( $_REQUEST );
		$productmeta_id = intval($_REQUEST['productmeta_id']);
		$res = $wpdb->query ( "DELETE FROM `" . $wpdb->prefix . self::$tbl_productmeta . "` WHERE productmeta_id = " . $productmeta_id );
		
		if ($res) {
			
			_e ( 'Meta deleted successfully', 'nm-personalizedproduct' );
		} else {
			$wpdb->show_errors ();
			$wpdb->print_error ();
		}
		
		die ( 0 );
	}
	
	/*
	 * setting up user directory
	 */
	function setup_file_directory( $sub_dir_name = null) {
		$upload_dir = wp_upload_dir ();
		
		$parent_dir = $upload_dir ['basedir'] . '/' . $this -> product_files . '/';
		$thumb_dir  = $parent_dir . 'thumbs/';
		
		if($sub_dir_name){
			$sub_dir = $parent_dir . $sub_dir_name . '/';
			if(wp_mkdir_p($sub_dir)){
				return $sub_dir;
			}else{
				die('Error while creating parent dirctory '.$sub_dir);
			}
		}elseif(wp_mkdir_p($parent_dir)){
			if(wp_mkdir_p($thumb_dir)){
				return $parent_dir;
			}else{
				die('Error while creating parent dirctory '.$thumb_dir);
			}
		}else{
			die('Error while creating parent dirctory '.$parent_dir);
		}
	
	}
	
	/*
	 * getting file URL
	 */
	function get_file_dir_url($thumbs = false) {

		$upload_dir = wp_upload_dir ();		
		
		if ($thumbs)
			return $upload_dir ['baseurl'] . '/' . $this -> product_files . '/thumbs/';
		else
			return $upload_dir ['baseurl'] . '/' . $this -> product_files . '/';
	}
	function get_file_dir_path() {
		$upload_dir = wp_upload_dir ();
		return $upload_dir ['basedir'] . '/' . $this -> product_files . '/';
	}
	
	/*
	 * creating thumb using WideImage Library Since 21 April, 2013
	 */
	function create_thumb($dest, $image_name, $thumb_size) {

	// using wp core image processing editor, 6 May, 2014
		$image = wp_get_image_editor ( $dest . $image_name );
		$dest = $dest . 'thumbs/' . $image_name;
		if (! is_wp_error ( $image )) {
			$image->resize ( 75, 75, true );
			$image->save ( $dest );
		}
		
		return $dest;
	}
	
	
	function activate_plugin() {
		global $wpdb;
		$plugin_db_version = '3.9.12';
		/*
		 * meta_for: this is to make this table to contact more then one metas for NM plugins in future in this plugin it will be populated with: forms
		 */
		$forms_table_name = $wpdb->prefix . self::$tbl_productmeta;
		
		$sql = "CREATE TABLE $forms_table_name (
		productmeta_id INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		productmeta_name VARCHAR(50) NOT NULL,
		productmeta_validation VARCHAR(3),
        dynamic_price_display VARCHAR(3),
        show_cart_thumb VARCHAR(3),
		aviary_api_key VARCHAR(40),
		productmeta_style MEDIUMTEXT,
		the_meta MEDIUMTEXT NOT NULL,
		productmeta_created DATETIME NOT NULL
		);";
		
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta ( $sql );
		
		update_option ( "personalizedproduct_db_version", $plugin_db_version );
		
		// this is to remove un-confirmed files daily
		if ( ! wp_next_scheduled( 'do_action_remove_images' ) ) {
			wp_schedule_event( time(), 'daily', 'do_action_remove_images');
		}
		
		if ( ! wp_next_scheduled( 'setup_styles_and_scripts_wooproduct' ) ) {
			wp_schedule_event( time(), 'daily', 'setup_styles_and_scripts_wooproduct');
		}
		
	}
	
	/*
	 * removing ununsed order files
	*/
	
	function remove_unpaid_orders_images(){
		
		$dir = $this -> setup_file_directory();
		
		if(is_dir($dir)){

		$dir_handle = opendir($dir);
		while ($file = readdir($dir_handle)){
				
			if(!is_dir($file)){
				@unlink($dir . $file);
			}
		}
				
		}
		
		
		closedir($dir_handle);
	}
	
	
	
	function deactivate_plugin() {
		
		// do nothing so far.
		wp_clear_scheduled_hook( 'do_action_remove_images' );
		
		wp_clear_scheduled_hook( 'setup_styles_and_scripts_wooproduct' );
		
	}
	
	
	/*
	 * cloning product meta for admin
	 * being called from: templates/admin/create-form.php
	 */
	function clone_product_meta($meta_id){
		
		global $wpdb;
		
		$forms_table_name = $wpdb->prefix . self::$tbl_productmeta;
		
		$sql = "INSERT INTO $forms_table_name
		(productmeta_name, aviary_api_key, productmeta_style, the_meta, productmeta_created) 
		SELECT productmeta_name, aviary_api_key, productmeta_style, the_meta, productmeta_created 
		FROM $forms_table_name 
		WHERE productmeta_id = %d;";
		
		$result = $wpdb -> query($wpdb -> prepare($sql, array($meta_id)));
		
		/* var_dump($result);
		
		$wpdb->show_errors();
		$wpdb->print_error(); */
		
	}
	
	
	/*
	 * checking if aviary addon is installed or not
	 */
	function is_aviary_installed() {
		
		if( is_plugin_active('nm-aviary-photo-editing-addon/index.php') ){
			return true;
		}else{
			return false;
		}
		
	}
	
	/*
	 * returning NM_Inputs object
	*/
	function get_all_inputs() {
	
		if (! class_exists ( 'NM_Inputs_wooproduct' )) {
			$_inputs = $this -> plugin_meta ['path'] . '/classes/input.class.php';
			
			if (file_exists ( $_inputs ))
				include_once ($_inputs);
			else
				die ( 'Reen, Reen, BUMP! not found ' . $_inputs );
		}
	
		$nm_inputs = new NM_Inputs_wooproduct ();
		// webcontact_pa($this->plugin_meta);
	
		// registering all inputs here
	
		return array (
				
				'text' 		=> $nm_inputs->get_input ( 'text' ),
				'textarea' 	=> $nm_inputs->get_input ( 'textarea' ),
				'select' 	=> $nm_inputs->get_input ( 'select' ),
				'radio' 	=> $nm_inputs->get_input ( 'radio' ),
				'checkbox' 	=> $nm_inputs->get_input ( 'checkbox' ),
				'number' 	=> $nm_inputs->get_input ( 'number' ),
				'email' 	=> $nm_inputs->get_input ( 'email' ),
				'date' 		=> $nm_inputs->get_input ( 'date' ),
				'masked' 	=> $nm_inputs->get_input ( 'masked' ),
				'hidden' 	=> $nm_inputs->get_input ( 'hidden' ),				
				'color'		=> $nm_inputs->get_input ( 'color' ),				
				'file' 		=> $nm_inputs->get_input ( 'file' ),
				'image' 	=> $nm_inputs->get_input ( 'image' ),
				'pricematrix' => $nm_inputs->get_input ( 'pricematrix' ),
				'section' 	=> $nm_inputs->get_input ( 'section' ),
				'palettes' 	=> $nm_inputs->get_input ( 'palettes' ),
		);
	
		// return new NM_Inputs($this->plugin_meta);
	}
	
	
	/**
	 * adding font awesome support
	 */
	
	function load_scripts_extra(){

		wp_enqueue_style( 'prefix-font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css', array(), '4.0.3' );
	}
	
	/*
	 * check if file is image and return true
	*/
	function is_image($file){
	
		$type = strtolower ( substr ( strrchr ( $file, '.' ), 1 ) );
	
		if (($type == "gif") || ($type == "jpeg") || ($type == "png") || ($type == "pjpeg") || ($type == "jpg"))
			return true;
		else
			return false;
	}
	
	function move_images_admin(){
		
		//print_r($_REQUEST);
		$this -> move_files_when_paid(intval($_REQUEST['orderid']));
		die(0);
	}
	
	function move_files_when_paid($order_id){
	
	
		global $woocommerce;
	
		// getting product id in cart
		$cart = $woocommerce->cart->get_cart();
	
		$base_path 	= $this -> setup_file_directory();
		$confirmed_dir = $this -> setup_file_directory() . 'confirmed/';
		$edits_dir = $this -> setup_file_directory() . 'edits/';
		
		if (! is_dir ( $confirmed_dir )) {
			if (!mkdir ( $confirmed_dir, 0775, true ))
				die('Error while created directory '.$confirmed_dir);
		}	
	
		
		//woopa_printA($cart); exit;
		foreach ($cart as $item){
			
			$product_id = $item['product_id'];
			$attached_files = $item['product_meta']['_product_attached_files'];
			
			if($attached_files){
				foreach ( $attached_files as $title => $item_files ) {
					
					foreach ( $item_files as $key => $file ) {
						
						$new_filename = $order_id . '-' . $product_id . '-' . $file;
						$source_file = $base_path . $file;
						$destination = $confirmed_dir . $new_filename;
						
						if (file_exists ( $destination ))
							break;
						
						if (file_exists ( $source_file )) {
							
							if (! rename ( $source_file, $destination ))
								die ( 'Error while re-naming order image ' . $source_file );
						}
						
						//renaming edited files
						$source_file_edit = $edits_dir . $file;
						$destination_edit = $edits_dir . $new_filename;
						if (file_exists ( $source_file_edit )) {
							
							if (! rename ( $source_file_edit, $destination_edit )){
								die ( 'Error while re-naming order image ' . $source_file_edit );
							}else{
								//removing file with org name
								unlink( $source_file_edit );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * is it real plugin
	 */
	function get_real_plugin_first(){
		
		$hashcode = get_option ( $this->plugin_meta ['shortname'] . '_hashcode' );
		$hash_file = $this -> plugin_meta['path'] . '/assets/_hashfile.txt';
		if ( file_exists( $hash_file )) {
			return $hashcode;
		}else{			
			return $hashcode;
		}
	}
	
	function get_plugin_hashcode(){
		
		$key = $_SERVER['HTTP_HOST'];
		return hash( 'md5', $key );
	}
	

	
	function nm_export_ppom(){
		
		if(isset($_REQUEST['nm_export']) && sanitize_text_field($_REQUEST['nm_export']) == 'ppom'){
			
			global $wpdb;
		
			$qry = "SELECT * FROM " . $wpdb->prefix . self::$tbl_productmeta;
			$all_meta = $wpdb->get_results ( $qry, ARRAY_A );
			
			if($all_meta){
				$all_meta = $this -> add_slashes_array($all_meta);
			}
			
			//woopa_printA($all_meta); exit;
			$filename = 'ppom-export.csv';
			$delimiter = '|';
			
			 // tell the browser it's going to be a csv file
		    header('Content-Type: application/csv');
		    // tell the browser we want to save it instead of displaying it
		    header('Content-Disposition: attachement; filename="'.$filename.'";');
		    
			// open raw memory as file so no temp files needed, you might run out of memory though
		    $f = fopen('php://output', 'w'); 
		    // loop over the input array
		    foreach ($all_meta as $line) { 
		        // generate csv lines from the inner arrays
		        fputcsv($f, $line, $delimiter); 
		    }
		    // rewrind the "file" with the csv lines
		    fseek($f, 0);
		   
		    // make php send the generated csv lines to the browser
		    fpassthru($f);
		    
			die(0);
		}
	}
	
	function add_slashes_array($arr){
		foreach ($arr as $k => $v)
	        $ReturnArray[$k] = (is_array($v)) ? $this->add_slashes_array($v) : addslashes($v);
	    return $ReturnArray;
	}
	
	function process_nm_importing_file_ppom(){
		
		global $wpdb;
		//get the csv file
		//woopa_printA($_FILES);
	    $file = $_FILES[ppom_csv][tmp_name];
	    $handle = fopen($file,"r");
	    
	    $qry = "INSERT INTO ".$wpdb->prefix . self::$tbl_productmeta;
	    $qry .= " (productmeta_name,
	    			productmeta_validation,
	    			dynamic_price_display,
	    			show_cart_thumb,
	    			aviary_api_key, 
	    			productmeta_style,
	    			the_meta, 
	    			productmeta_created) VALUES";
	    	
	    	
	    //loop through the csv file and insert into database
	    do {
	        				
            //woopa_printA($data);
            if($cols){
	            foreach( $cols as $key => $val ) {
		            $cols[$key] = trim( $cols[$key] );
		            //$cols[$key] = iconv('UCS-2', 'UTF-8', $cols[$key]."\0") ;
		            $cols[$key] = str_replace('""', '"', $cols[$key]);
		            $cols[$key] = preg_replace("/^\"(.*)\"$/sim", "$1", $cols[$key]);
	        	}
            }
        	
        	 if ($cols[0]) {
	        	$qry .= "(	
	        				'".$cols[1]."',
	        				'".$cols[2]."',
	        				'".$cols[3]."',
	        				'".$cols[4]."',
	        				'".$cols[5]."',
	        				'".$cols[6]."',
	        				'".$cols[7]."',
	        				'".$cols[8]."'
	        				),";
	        				
        	//woopa_printA($cols); 
	        }
	    } while ($cols = fgetcsv($handle,2000,"|"));
	    
	    $qry = substr($qry, 0, -1);
	    
	    //print $qry; exit;
	    $res = $wpdb->query( $qry );
	    
	    /*$wpdb->show_errors();
	    $wpdb->print_error();
	    exit;*/
	    
	    wp_redirect(  admin_url( 'options-general.php?page=nm-personalizedproduct' ) );
   		exit;

	    
	   
	}
}