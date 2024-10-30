<?php

add_action('admin_init', 'lr_options_init_fn' );
add_action('admin_menu', 'lr_options_add_page_fn');

// Define default option settings
function lr_add_defaults_fn() {
	$tmp = get_option('lr_count_down_options');
	if(($tmp['chkbox2']=='on')||(!is_array($tmp))) {
   
		$arr = array('chkbox2'=>'on',"textarea_one" => '<div class="mail-content" style="max-width:600px; margin:0 auto; width:100%; box-shadow:0px 1px 4px #ccc">
<div class="header">
	<h1 style="background-color: cadetblue; font-weight:normal; padding: 30px; color: white;">You have received a coupon from {site_title}</h1>
	</div>
	<div class="bodycontent" style="padding: 20px;">
	<p>Hi {couponName},
		thanks for making the first purchase on {order_date} on our shop {site_title}!
		Because of this, we would like to offer you this coupon as a little gift:
	</p>
	<h3 style="color:cadetblue;">Coupon code: <strong style="color:cadetblue;">{coupon_name}</strong></h3>
	<p>Coupon amount: <strong style="color:cadetblue;">{coupon_amount}</strong></p>	
	<p>Expiration date:<strong style="color:cadetblue;">{expiry_date}</strong></p>
	<p>See you on our shop,</p>	
	<p>{site_title}</p>
</div>
</div>', "text_string" => "You have received a coupon from {site_title}", "test_text_string"=>'Get Your Coupon','email_form_title'=>'Get Your Coupon','email_form_desc'=>'Enter your details below to get your coupon code.');
		update_option('lr_count_down_options', $arr);
	}
	
}

// Register our settings. Add the settings section, and settings fields
function lr_options_init_fn(){
	register_setting('lr_count_down_options', 'lr_count_down_options', 'lr_count_down_options_validate' );
	add_settings_section('lr_general_section', 'Main Settings', 'section_text_fn', __FILE__);
	add_settings_section('lr_advance_section', 'Test Settings', 'test_text_fn', __FILE__);
	add_settings_field('lr_plugin_chk', 'Enable coupon sending', 'lr_setting_chk_fn', __FILE__, 'lr_general_section');
	add_settings_field('lr_drop_down', 'Select Coupon', 'lr_setting_dropdown_fn', __FILE__, 'lr_general_section');
	add_settings_field('lr_text_string', 'Email subject', 'lr_setting_string_fn', __FILE__, 'lr_general_section');
	add_settings_field('lr_textarea_string', 'Large Textbox!', 'lr_setting_textarea_fn', __FILE__, 'lr_general_section');
	
	add_settings_field('lr_adv_text_string', 'Button Text', 'lr_adv_string_fn', __FILE__, 'lr_advance_section');
	add_settings_field('lr_adv_form_title', 'Email Form Title', 'lr_adv_email_form_title_fn', __FILE__, 'lr_advance_section');
	add_settings_field('lr_adv_form_desc', 'Email Form Desciption', 'lr_adv_email_form_desc_fn', __FILE__, 'lr_advance_section');
	add_settings_field("lr_adv_colo_piker", "Count Down Background", "lr_adv_colo_piker_fn", __FILE__, "lr_advance_section");
}

// Add sub page to the Settings Menu
function lr_options_add_page_fn() {
// add optiont to main settings panel
 add_menu_page('LR Count Down', 'LR Count Down Settings', 'administrator', __FILE__, 'lr_options_page_fn');

}

// ************************************************************************************************************

// Callback functions

// Init plugin options to white list our options

// Section HTML, displayed before the first option
function  section_text_fn() {
	echo '<p>Below are some examples of different option controls.</p>';
}
function  test_text_fn() {
	echo '<p>Below are some examples of different option controls.</p>';
}
// DROP-DOWN-BOX - Name: lr_count_down_options[dropdown1]
function  lr_setting_dropdown_fn() {
	$options = get_option('lr_count_down_options');
	$args = array(
		'posts_per_page'   => -1,
		'orderby'          => 'title',
		'order'            => 'asc',
		'post_type'        => 'shop_coupon',
		'post_status'      => 'publish',
	);
		
	$coupons = get_posts( $args );
	$coupon_names = array();
	echo "<select class='regular-text' id='lr_drop_down' name='lr_count_down_options[dropdown1]'>";
	foreach ( $coupons as $coupon ) {
		// Get the name for each coupon post
		$coupon_id = $coupon->ID;
		$coupon_name = $coupon->post_title;
		
		array_push( $coupon_names, $coupon_name );
		$selected = ($options['dropdown1']==$coupon_name) ? 'selected="selected"' : '';
		echo "<option value='$coupon_name' $selected>$coupon_name</option>";
	}
	echo "</select>";
}
function lr_adv_colo_piker_fn(){
	$options = get_option('lr_count_down_options');
	echo "<input id='lr_adv_text_string' class='color-field' name='lr_count_down_options[lr_adv_colo_piker]' size='40' type='text' value='{$options['lr_adv_colo_piker']}' />";
}

function lr_adv_email_form_desc_fn() {
	$options = get_option('lr_count_down_options');
	echo "<input class='regular-text' id='lr_adv_text_string' name='lr_count_down_options[email_form_desc]' size='40' type='text' value='{$options['email_form_desc']}' />";
	
}
function lr_adv_email_form_title_fn() {
	$options = get_option('lr_count_down_options');
	echo "<input class='regular-text' id='lr_adv_text_string' name='lr_count_down_options[email_form_title]' size='40' type='text' value='{$options['email_form_title']}' />";
}
// TEXTBOX - Name: lr_count_down_options[text_string]
function lr_adv_string_fn() {
	$options = get_option('lr_count_down_options');
	echo "<input class='regular-text' id='lr_adv_text_string' name='lr_count_down_options[test_text_string]' size='40' type='text' value='{$options['test_text_string']}' />";
}

// TEXTBOX - Name: lr_count_down_options[text_string]
function lr_setting_string_fn() {
	$options = get_option('lr_count_down_options');
	echo "<input class='regular-text' id='lr_text_string' name='lr_count_down_options[text_string]' size='40' type='text' value='{$options['text_string']}' />";
}

// CHECKBOX - Name: lr_count_down_options[chkbox2]
function lr_setting_chk_fn() {
	$options = get_option('lr_count_down_options');
	if($options['chkbox2']) { $checked = ' checked="checked" '; }
	echo "<input class='regular-text' ".$checked." id='lr_plugin_chk' name='lr_count_down_options[chkbox2]' type='checkbox' />";
}

// TEXTAREA - Name: lr_count_down_options[text_area]
function lr_setting_textarea_fn() {
	$options = get_option('lr_count_down_options');
	$args = array("textarea_name" => "lr_count_down_options[textarea_one]");
	wp_editor( $options['textarea_one'], "lr_count_down_options[textarea_one]", $args );
	
}
// Display the admin options page
function lr_options_page_fn() {
?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>My Example Options Page</h2>
		Some optional text here explaining the overall purpose of the options and what they relate to etc.
		<form action="options.php" method="post">
					<?php
if ( function_exists('wp_nonce_field') ) 
	wp_nonce_field('plugin-name-action_' . "yep"); 
?>
		<?php settings_fields('lr_count_down_options'); ?>
		<?php do_settings_sections(__FILE__); ?>
		
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
		</form>
	</div>
<?php
}

// Validate user data for some/all of your input fields
function lr_count_down_options_validate($input) {
	// Check our textbox option field contains no HTML tags - if so strip them out
	$input['text_string'] =  wp_filter_nohtml_kses($input['text_string']);	
	return $input; // return validated input
}