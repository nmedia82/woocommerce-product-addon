<?php
/*
 * This our new world, Inshallah 29 aug, 2013
 */
//global $this;
//$this -> pa($this -> inputs);

$productmeta_name 		= '';
$enable_ajax_validation = '';
$dynamic_price_hide  	= '';
$show_cart_thumb		= '';
$aviary_api_key 		= '';
$productmeta_style 		= '';
$product_meta 			= '';

if (isset ( $_REQUEST ['productmeta_id'] ) && $_REQUEST ['do_meta'] == 'edit') {
	
	$single_productmeta = $this -> get_product_meta( intval ( $_REQUEST ['productmeta_id'] ) );
	//$this -> pa($single_productmeta);
	
	$productmeta_name 		= (isset($single_productmeta -> productmeta_name) ? $single_productmeta -> productmeta_name : '');
	$enable_ajax_validation = (isset($single_productmeta -> productmeta_validation) ? $single_productmeta -> productmeta_validation : '');
    $dynamic_price_hide  	= (isset($single_productmeta -> dynamic_price_display) ? $single_productmeta -> dynamic_price_display : '');
	$show_cart_thumb		= (isset($single_productmeta -> show_cart_thumb) ? $single_productmeta -> show_cart_thumb : '');
	$aviary_api_key 		= (isset($single_productmeta -> aviary_api_key) ? $single_productmeta -> aviary_api_key : '');
	$productmeta_style 		= (isset($single_productmeta -> productmeta_style) ? $single_productmeta -> productmeta_style : '');
	$product_meta 			= json_decode ( $single_productmeta->the_meta, true );
	
	//$this->pa ( $product_meta );
}
$url_cancel = add_query_arg(array('action'=>false,'productmeta_id'=>false, 'do_meta'=>false));
echo '<p><a class="button" href="'.$url_cancel.'">'.__('&laquo; Existing Product Meta', 'nm-personalizedproduct').'</a></p>';
?>

<input type="hidden" name="productmeta_id"
	value="<?php echo $_REQUEST['productmeta_id']?>">
<div id="nmpersonalizedproduct-form-generator">
	<ul>
		<li><a href="#formbox-1"><?php _e('Product Meta Basic Settings', 'nm-personalizedproduct')?></a></li>
		<li><a href="#formbox-2"><?php _e('Product Meta Fields', 'nm-personalizedproduct')?></a></li>
		<li><a href="#formbox-3"><?php _e('=- 25% Discount/Pro Version -=', 'nm-personalizedproduct')?></a></li>
		<li><a href="#formbox-4"><?php _e('More WooCommerce Plugins', 'nm-personalizedproduct')?></a></li>
		<li style="float: right"><button class="button-primary button"
				onclick="save_form_meta()"><?php _e('Save settings', 'nm-personalizedproduct')?></button>
			<span id="nm-saving-form" style="display:none"><img alt="saving..." src="<?php echo $this -> plugin_meta['url']?>/images/loading.gif"></span></li>
	</ul>

	<div id="formbox-1">

		<table id="form-main-settings" border="0" bordercolor=""
			style="background-color: #F8F8F8; padding: 10px" width="100%"
			cellpadding="0" cellspacing="0">
			<tr>
				<td class="headings"><?php _e('Meta group name', 'nm-personalizedproduct')?></td>
				<td><input type="text" name="productmeta_name"
					value="<?php echo $productmeta_name?>" /> <br />
					<p class="s-font"><?php _e('For your reference', 'nm-personalizedproduct')?></p></td>
			</tr>
			
			
            <tr>
				<td class="headings"><?php _e('Hide dynamic price display on product page?', 'nm-personalizedproduct')?></td>
				<td><input type="checkbox" <?php checked($dynamic_price_hide, 'yes')?> name="dynamic_price_hide" value="yes" /> <br />
					<p class="s-font"><?php _e('sometime prices are not display correctly on product page when variation changes, but these are added to cart correctly. So better to hide dynamic prices display on product page.', 'nm-personalizedproduct')?></p></td>
			</tr>
			
			 
			
			<!-- Photo editing with Aviary -->
			<tr>
				<td class="headings"><?php _e('Aviary API Key (Photo Editing)', 'nm-personalizedproduct')?>
				<a class="button" href="http://aviary.com/web" target="_blank"><?php _e('Learn about Aviary', 'nm-personalizedproduct')?></a></td>
				<td>
				
				<?php if ($this -> is_aviary_installed()) {?>
				<input type="text" name="aviary_api_key"
					value="<?php echo $aviary_api_key?>" /> <br />
					<p class="s-font"><?php _e('Enter Aviary API Key.', 'nm-personalizedproduct')?>
					<br><?php _e('You need to get your API key from Aviary to use this. It is free as long as you need paid features', 'nm-personalizedproduct')?></p>
				<?php }else{?>
					<p class="s-font">
						<a href="http://www.najeebmedia.com/photo-editing-add-on-for-n-media-website-contact-form/" class="button-primary" target="_blank"><?php _e('Buy this Add-on', 'nm-personalizedproduct')?></a>
						<a href="http://webcontact.wordpresspoets.com/demo-of-photo-editing/" class="button-primary" target="_blank"><?php _e('See Demo', 'nm-personalizedproduct')?></a>
					</p>
					
					<?php }?>
						
					</td>
			</tr>
			
			<tr>
				<td class="headings"><?php _e('Form styling/css', 'nm-personalizedproduct')?></td>
				<td><textarea rows="7" cols="25" name="productmeta_style"><?php echo stripslashes($productmeta_style)?></textarea> <br />
					<p class="s-font"><?php _e('Form styling/css.', 'nm-personalizedproduct')?></p></td>
			</tr> 
		</table>

	</div>
	
	<!--------------------- END formbox-1 ---------------------------------------->

	<div id="formbox-2">
		<div id="form-meta-bttons">
			<p>
		<?php _e('select input type below and drag it on right side. Then set more options', 'nm-personalizedproduct')?>
		</p>

		<p><span class="pro-feature">For Red Inputs</span> = <a href="http://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/">Get PRO</a>
		</p>
			<ul id="nm-input-types">
		<?php
		
		foreach ( $this -> inputs as $type => $meta ) {
			
			if( $meta != NULL ){
				
				$ispro = (isset($meta -> ispro) ? 'pro-feature' : '');
				
				echo '<li class="input-type-item" data-inputtype="' . $type . '">';
				echo '<div><h3><span class="top-heading-text '.$ispro.'">' . $meta -> title . '</span>';
				echo '<span class="top-heading-icons ui-icon ui-icon-arrow-4"></span>';
				echo '<span class="top-heading-icons ui-icon-placehorder"></span>';
				echo '<span class="top-heading-icons ui-icon-placehorder-copy"></span>';
				echo '<span style="clear:both;display:block"></span>';
				echo '</h3>';
				
				// this function Defined below
				echo render_input_settings ( $meta -> settings );
				
				echo '</div></li>';
				// echo '<div><p>'.$data['desc'].'</p></div>';
			}
			
		}
		?>
		</ul>
		</div>


		<div id="form-meta-setting" class="postbox-container">

			<div id="postcustom" class="postbox">
				<h3>
					<span style="float: left"><?php _e('Drag FORM fields here', 'nm-personalizedproduct')?></span>
					<span style="float: right"><span style="float: right"
						title="<?php _e('Collapse all', 'nm-personalizedproduct')?>"
						class="ui-icon ui-icon-circle-triangle-n"></span><span
						title="<?php _e('Expand all', 'nm-personalizedproduct')?>"
						class="ui-icon ui-icon-circle-triangle-s"></span></span> <span
						class="clearfix"></span>
				</h3>
				<div class="inside" style="background-color: #fff;">
					<ul id="meta-input-holder">
					<?php render_existing_form_meta($product_meta, $this -> inputs)?>
					</ul>
				</div>
			</div>
		</div>

		<div class="clearfix"></div>
	</div>
	
	<br /><!--------------------- END formbox-2 ---------------------------------------->

	<div id="formbox-3">
		
		Unlock the true power of this plugin and get PRO version will following options:
		<ul>
			<li>Image editing</li>
			<li>Most secure and HTML5 based upload</li>
			<li>Bulk/Discount Price Matrix</li>
			<li>More inputs e.g: Date, Mask, Number, Email, Image, Section etc </li>
			<li>Best support via Forum, Skype and Email</li>
		</ul>
		
		<a href="http://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/" class="btn btn-primary">More Detail</a>
		
		
		<h3>Surprise Discount</h3>
		Thanks for using our plugin, Apply following Coupon Code to get 25% Discount<br>
		<h4>Coupon Code: PPOM25-2017</h4>
		<a href="https://www.2checkout.com/checkout/purchase?sid=1686663&quantity=1&product_id=15" class="btn btn-primary">Apply 25% Discount Here</a>
		
	</div>
	
	<br /><!--------------------- END formbox-3 ---------------------------------------->

	<div id="formbox-4">
		
	<h2 class="post-header"><a href="http://najeebmedia.com/wordpress-plugin/woocommerce-checkout-field-editor-plugin/">WooCommerce Checkout Field Editor Plugin</a>
	- <a href="http://woo.wordpresspoets.com/product/tshirts/">Demo</a>
	</h2>
		<p>It's our best selling pluign on CodeCanyon. Allow store admin to control checkut page by adding/removing/editing core fields.</p>
		
		<h2><a href="http://najeebmedia.com/wordpress-plugin/woocommerce-2checkout-payment-gateway-inline-support/">WooCommerce 2Checkout Payment Gateway with inline support</a></h2>
		<p>2Checkout is best payment Gateway to accept payments online which allow your customers to make purchases in any of 8 payment methods, 15 languages, and 26 currencies. N-Media integrated 2Checkout Payment Gateway with WooCommerce. It is tightly integrated with WooCommerce Core API.</p>
			
		<h2><a href="http://najeebmedia.com/wordpress-plugin/woocommerce-file-upload-plugin-checkout/">WooCommerce File Upload Plugin After Checkout</a></h2>
		<p>Woocommerce file upload plugin allow clients to send files when order is completed. It renders a simple form on front end on a page using a shortcode. This plugin is a great tool  for communicate between site admin and client to share files related to order. One to one message thread between client and store admin.</p>
		
		<h2><a href="http://najeebmedia.com/wordpress-plugin/woocommerce-customizer/">WooCommerce Customizer</a>
		- <a href="https://najeebmedia.com/personalized-woostore-guide/">Guide</a>
		</h2>
		<p>WooCommerce is a best shopping cart solution in WordPress. It provides options to setup and run online store so easily. However there are some options which cannot be configured using woocommerce settings like:

    Changing Lables
        Shop, Cart, Checkout, My Account, Email Template etc
    Adding Tabs,
    Disable login form on checkout page,
    Enquiry Form for Product.
</p>

<h2><a href="http://najeebmedia.com/wordpress-plugin/woocommerce-product-designer/">WooCommerce Simple Event Booking with Tickets
</a> - <a href="http://woodemo.theproductionarea.net/">Demo</a></h2>
<p>This plugin is Simple solution to create Events and Ticket with WooCommerce Products. Unlimited Events can be attached against one Product and each Event can have unlimited numbers of Tickets. Price and Spots/Quantity of Tickets generate total price of Event(s), it’s then add to cart and all Event Meta is sent Cart, Checkout and Email. Even Map can also set using Latitude/Longitude.
</p>

<h2><a href="http://najeebmedia.com/wordpress-plugin/woocommerce-product-designer/">Boost UP your WooCommerce Store Speed Upto 70%
</a> - <a href="http://woo.wordpresspoets.com/one-page-woostore/">Demo</a></h2>
<p>
	One Page WooCommerce loads all woocommerce pages at one place with real power of AngularJS. 
	<ul>
<li>Fastest woocommerce page loading</li>
<li>No need extra theme</li>
<li>Customize Front end theme</li>
<li>Exclude/Include categories</li>
<li>Easy shortcode generator</li>
<li>No extra scripts, just woocommece core templates</li>
</ul>
</p>


<h2><a href="http://najeebmedia.com/wordpress-plugin/woocommerce-product-designer/">WooCommerce Product Designer</a> 
- <a href="http://woo.wordpresspoets.com/product/design-your-shirt/">Demo T-Shirt</a>
- <a href="http://woo.wordpresspoets.com/product/custom-mug/">Demo Mug</a>
</h2>
<p>WooCommerce Product Designer plugin now give client a new way to personalize their order. Client can:

    Choose Template (e.g: T-Shirt, Mug, iPhone case etc),
    Drag & drop clip arts (Stars, Flowers, Hearts etc),
    Resize & rotate,
    Add Text, change font style, size & color,
    Upload images,
    And much More.
</p>

<h2><a href="http://najeebmedia.com/wordpress-plugin/woocommerce-bring-shipping-api-norway/">WooCommerce Bring Shipping API for Norway</a></h2>
<p>WooCommerce Norway Post is shipping API works with WooCommerce. Shipping rates are calculated using product weight + size. When product is added to cart it displays all selected available methods to ship product in Norway.</p>





	</div>
</div>

<!-- ui dialogs -->
<div id="remove-meta-confirm"
	title="<?php _e('Are you sure?', 'nm-personalizedproduct')?>">
	<p>
		<span class="ui-icon ui-icon-alert"
			style="float: left; margin: 0 7px 20px 0;"></span>
  <?php _e('Are you sure to remove this input field?', 'nm-personalizedproduct')?></p>
</div>

<?php
function render_input_settings($settings, $values = '') {
	
	$setting_html = '<table>';
	if ($settings != '') {
		
		foreach ( $settings as $meta_type => $data ) {
			
			// nm_personalizedproduct_pa($data);
			
			
			 
			$setting_html .= '<tr>';
			$setting_html .= '<td class="table-column-title">' . $data ['title'] . '</td>';
			
			$input_data_options	 = (isset( $data ['options'] ) ? $data ['options'] : '');
			$colspan	= ($data ['type'] == 'html-conditions' ? 'colspan="2"' : '' );
			
			$data_values = NULL;
			if ($values){
				$data_values = (isset( $values [$meta_type] ) ? $values [$meta_type] : NULL );
			}
			$setting_html .= '<td '.$colspan.' class="table-column-input" data-type="' . $data ['type'] . '" data-name="' . $meta_type . '">' . render_input_types ( $data ['type'], $meta_type, $data_values, $input_data_options ) . '</td>';
				
			//removing the desc column for type: html-conditions
			if ($data ['type'] != 'html-conditions') {
				$setting_html .= '<td class="table-column-desc">' . $data ['desc'] . '</td>';;
			}
			
			$setting_html .= '</tr>';
		}
		
	}
	
	$setting_html .= '</table>';
	
	return $setting_html;
}

/*
 * this function is rendring input field for settings
 */
function render_input_types($type, $name, $value = '', $options = '') {
	
	$plugin_meta = woopa_get_plugin_data();
	$html_input = '';
	
	// var_dump($value);
	if(!is_array($value))
		$value = stripslashes($value);
	
	switch ($type) {
		
		case 'text' :
			$html_input .= '<input type="text" name="' . $name . '" value="' . esc_html( $value ). '">';
			break;
		
		case 'textarea' :
			$html_input .= '<textarea name="' . $name . '">' . esc_html( $value ) . '</textarea>';
			break;
		
		case 'select' :
			$html_input .= '<select name="' . $name . '">';
			foreach ( $options as $key => $val ) {
				$selected = ($key == $value) ? 'selected="selected"' : '';
				$html_input .= '<option value="' . $key . '" ' . $selected . '>' . esc_html( $val ) . '</option>';
			}
			$html_input .= '</select>';
			break;
		
		case 'paired' :
			
			if($value){
				foreach ($value as $option){
					$html_input .= '<div class="data-options" style="border: dashed 1px;">';
					$html_input .= '<input type="text" name="options[option]" value="'.$option['option'].'" placeholder="'.__('option','nm-personalizedproduct').'">';
					$html_input .= '<input type="text" name="options[price]" value="'.$option['price'].'" placeholder="'.__('price (if any). 3 or 3%','nm-personalizedproduct').'">';
					$html_input	.= '<img class="add_option" src="'.$plugin_meta['url'].'/images/plus.png" title="add rule" alt="add rule" style="cursor:pointer; margin:0 3px;">';
					$html_input	.= '<img class="remove_option" src="'.$plugin_meta['url'].'/images/minus.png" title="remove rule" alt="remove rule" style="cursor:pointer; margin:0 3px;">';
					$html_input .= '</div>';
				}
			}else{
				$html_input .= '<div class="data-options" style="border: dashed 1px;">';
				$html_input .= '<input type="text" name="options[option]" placeholder="'.__('option','nm-personalizedproduct').'">';
				$html_input .= '<input type="text" name="options[price]" placeholder="'.__('price (if any). 3 or 3%','nm-personalizedproduct').'">';
				$html_input	.= '<img class="add_option" src="'.$plugin_meta['url'].'/images/plus.png" title="add rule" alt="add rule" style="cursor:pointer; margin:0 3px;">';
				$html_input	.= '<img class="remove_option" src="'.$plugin_meta['url'].'/images/minus.png" title="remove rule" alt="remove rule" style="cursor:pointer; margin:0 3px;">';
				$html_input .= '</div>';
			}
			
			break;
			
		case 'checkbox' :
			
			if ($options) {
				foreach ( $options as $key => $val ) {
					
					parse_str ( $value, $saved_data );
					$checked = '';
					if ( isset( $saved_data ['editing_tools'] ) && $saved_data ['editing_tools']) {
						if (in_array($key, $saved_data['editing_tools'])) {
							$checked = 'checked="checked"';
						}else{
							$checked = '';
						}
					}
					// $html_input .= '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
					$html_input .= '<input type="checkbox" value="' . $key . '" name="' . $name . '[]" ' . $checked . '> ' . $val . '<br>';
				}
			} else {
				$checked = ( (isset($value) && $value != '' ) ? 'checked = "checked"' : '' );
					
				$html_input .= '<input type="checkbox" name="' . $name . '" ' . $checked . '>';
			}
			break;
			
		case 'html-conditions' :
			
			// nm_personalizedproduct_pa($value);
			$rule_i = 1;
			if($value){
				
	
					$visibility_show = ($value['visibility'] == 'Show') ? 'selected="selected"' : '';
					$visibility_hide = ($value['visibility'] == 'Hide') ? 'selected="selected"' : '';
					
					$html_input	 = '<select name="condition_visibility">';
					/*$html_input .= '<option '.$visibility_show.'>'.__('Show','nm-personalizedproduct').'</option>';
					$html_input .= '<option '.$visibility_hide.'>'.__('Hide', 'nm-personalizedproduct').'</option>';*/
					$html_input .= '<option '.$visibility_show.'>Show</option>';
					$html_input .= '<option '.$visibility_hide.'>Hide</option>';
					$html_input	.= '</select> ';
					
					
					$html_input .= __('only if', 'nm-personalizedproduct');
					
					$bound_all = ($value['bound'] == 'All') ? 'selected="selected"' : '';
					$bound_any = ($value['bound'] == 'Any') ? 'selected="selected"' : '';
					
					$html_input	.= '<select name="condition_bound">';
					/*$html_input 	.= '<option '.$bound_all.'>'.__('All','nm-personalizedproduct').'</option>';
					$html_input .= '<option '.$bound_any.'>'.__('Any', 'nm-personalizedproduct').'</option>';*/
					$html_input 	.= '<option '.$bound_all.'>All</option>';
					$html_input .= '<option '.$bound_any.'>Any</option>';
					$html_input	.= '</select> ';
						
					$html_input .= __(' of the following matches', 'nm-personalizedproduct');
					
					
				foreach ($value['rules'] as $condition){
					
					
					// conditional elements
					$html_input .= '<div class="webcontact-rules" id="rule-box-'.$rule_i.'">';
					$html_input .= '<br><strong>'.__('Rule # ', 'nm-personalizedproduct') . $rule_i++ .'</strong><br>';
					$html_input .= '<select name="condition_elements" data-existingvalue="'.$condition['elements'].'" onblur="load_conditional_values(this)"></select>';
					
					// is
					
					$operator_is 		= ($condition['operators'] == 'is') ? 'selected="selected"' : '';
					$operator_not 		= ($condition['operators'] == 'not') ? 'selected="selected"' : '';
					$operator_greater 	= ($condition['operators'] == 'greater than') ? 'selected="selected"' : '';
					$operator_less 		= ($condition['operators'] == 'less than') ? 'selected="selected"' : '';
					
					$html_input .= '<select name="condition_operators">';
					/*$html_input	.= '<option '.$operator_is.'>'.__('is','nm-personalizedproduct').'</option>';
					$html_input .= '<option '.$operator_not.'>'.__('not', 'nm-personalizedproduct').'</option>';
					$html_input .= '<option '.$operator_greater.'>'.__('greater then', 'nm-personalizedproduct').'</option>';
					$html_input .= '<option '.$operator_less.'>'.__('less then', 'nm-personalizedproduct').'</option>';*/
					$html_input	.= '<option '.$operator_is.'>is</option>';
					$html_input .= '<option '.$operator_not.'>not</option>';
					$html_input .= '<option '.$operator_greater.'>greater than</option>';
					$html_input .= '<option '.$operator_less.'>less than</option>';
					$html_input	.= '</select> ';
					
					// conditional elements values
					$html_input .= '<select name="condition_element_values" data-existingvalue="'.$condition['element_values'].'"></select>';
					$html_input	.= '<img class="add_rule" src="'.$plugin_meta['url'].'/images/plus.png" title="add rule" alt="add rule" style="cursor:pointer; margin:0 3px;">';
					$html_input	.= '<img class="remove_rule" src="'.$plugin_meta['url'].'/images/minus.png" title="remove rule" alt="remove rule" style="cursor:pointer; margin:0 3px;">';
					$html_input .= '</div>';
					
				}
			}else{

					
				$html_input	 = '<select name="condition_visibility">';
				$html_input .= '<option>Show</option>';
				$html_input .= '<option>Hide</option>';
				$html_input	.= '</select> ';
					
				$html_input	.= '<select name="condition_bound">';
				$html_input .= '<option>All</option>';
				$html_input .= '<option>Any</option>';
				$html_input	.= '</select> ';
					
				$html_input .= __(' of the following matches', 'nm-personalizedproduct');
				// conditional elements
				
				$html_input .= '<div class="webcontact-rules" id="rule-box-'.$rule_i.'">';
				$html_input .= '<br><strong>'.__('Rule # ', 'nm-personalizedproduct') . $rule_i++ .'</strong><br>';
				$html_input .= '<select name="condition_elements" data-existingvalue="" onblur="load_conditional_values(this)"></select>';
					
				// is
					
				$html_input .= '<select name="condition_operators">';
				$html_input	.= '<option>is</option>';
				$html_input .= '<option>not</option>';
				$html_input .= '<option>greater than</option>';
				$html_input .= '<option>less than</option>';
				$html_input	.= '</select> ';
					
				// conditional elements values
				$html_input .= '<select name="condition_element_values" data-existingvalue=""></select>';
				$html_input	.= '<img class="add_rule" src="'.$plugin_meta['url'].'/images/plus.png" title="add rule" alt="add rule" style="cursor:pointer; margin:0 3px;">';
				$html_input	.= '<img class="remove_rule" src="'.$plugin_meta['url'].'/images/minus.png" title="remove rule" alt="remove rule" style="cursor:pointer; margin:0 3px;">';
				$html_input .= '</div>';
			}

			break;
			
			case 'pre-images' :
			
				
				//$html_input	.= '<textarea name="pre_upload_images">'.$pre_uploaded_images.'</textarea>';
				$html_input	.= '<div class="pre-upload-box">';
				$html_input	.= '<input name="pre_upload_image_button" type="button" value="'.__('Select/Upload Image', 'nm-personalizedproduct').'" />';
				// nm_personalizedproduct_pa($value);
				if ($value) {
					foreach ($value as $pre_uploaded_image){
				
						$image_link = (isset($pre_uploaded_image['link']) ? $pre_uploaded_image['link'] : '');
						$image_id = (isset($pre_uploaded_image['id']) ? $pre_uploaded_image['id'] : '');
						
						$html_input .='<table>';
						$html_input .= '<tr>';
						$html_input .= '<td><img width="75" src="'.$pre_uploaded_image['link'].'"></td>';
						$html_input .= '<input type="hidden" name="pre-upload-link" value="'.$image_link.'">';
						$html_input .= '<input type="hidden" name="pre-upload-id" value="'.$image_id.'">';
						$html_input .= '<td><input style="width:100px" type="text" value="'.stripslashes($pre_uploaded_image['title']).'" name="pre-upload-title"><br>';
						$html_input .= '<input style="width:100px" type="text" value="'.stripslashes($pre_uploaded_image['price']).'" name="pre-upload-price"><br>';
						$html_input .= '<input style="width:100px; color:red" name="pre-upload-delete" type="button" class="button" value="Delete"><br>';
						$html_input .= '</td></tr>';
						$html_input .= '</table><br>';
				
					}
					//$pre_uploaded_images = $value;
				}
				
				$html_input .= '</div>';
			
			break;
	}
	
	return $html_input;
}


/*
 * this function is rendering the existing form meta
 */
function render_existing_form_meta($product_meta, $types) {
	if ($product_meta) {
		foreach ( $product_meta as $key => $meta ) {
			
			$type = $meta ['type'];
			
			// nm_personalizedproduct_pa($meta);
			
			echo '<li data-inputtype="' . $type . '"><div class="postbox">';
			echo '<h3><span class="top-heading-text">' . stripcslashes($meta ['title']) . ' (' . $type . ')</span>';
			echo '<span class="top-heading-icons ui-icon ui-icon-carat-2-n-s"></span>';
			echo '<span class="top-heading-icons ui-icon ui-icon-trash"></span>';
			echo '<span class="top-heading-icons ui-icon ui-icon-copy"></span>';
			echo '<span style="clear:both;display:block"></span></h3>';
			
			echo render_input_settings ( $types[$type] -> settings, $meta );
			
			echo '</div></li>';
		}
	}
}

?>