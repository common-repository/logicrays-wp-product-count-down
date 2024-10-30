<?php 
/*
Plugin Name: WooCommerce Product Count Down
Plugin URI: http://www.logicrays.com/
Description: A full-featured WordPress count down plugin .
Author: LogicRays WordPress Team
Version: 1.0
*/

define('lr_count_down_product_path', plugins_url('', __FILE__));
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// check woocommerce install or not
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) 
{
	register_activation_hook(__FILE__, 'lr_add_defaults_fn');
	require_once plugin_dir_path( __FILE__ ) . '/admin/lr-wp-product-count-down.php';
	
	// First Register the Tab by hooking into the 'woocommerce_product_data_tabs' filter
	add_filter( 'woocommerce_product_data_tabs', 'lr_count_down_product_tab' );
	function lr_count_down_product_tab( $product_data_tabs ) {
		$product_data_tabs['lr-count-down-tab'] = array(
			'label' => __( 'Product CountDown', 'woocommerce' ),
			'target' => 'lr_count_down_product_data',
			'class'     => array( 'show_if_simple' ),
		);
		return $product_data_tabs;
	}

	/** CSS To Add Custom tab Icon */
	function lr_admin_count_down_product_style() {
		
		wp_enqueue_style('lr_jquery_datetimepicker_css', lr_count_down_product_path.'/css/jquery.datetimepicker.css');
		
		wp_enqueue_script('lr_jquery_datetimepicker_js',lr_count_down_product_path.'/js/jquery.datetimepicker.full.js');	
		
		wp_enqueue_script('lr_lr_admin_custom_js',lr_count_down_product_path.'/js/lr_admin_custom.js');
		
		wp_enqueue_style( 'wp-color-picker' ); 
		wp_enqueue_media();
         
        // Include our custom jQuery file with WordPress Color Picker dependency
        wp_enqueue_script( 'custom-script-handle', lr_count_down_product_path. '/js/custom-script.js', array( 'wp-color-picker' ), false, true ); 
		
	?>
	<style>
	#woocommerce-product-data ul.wc-tabs li.lr-count-down-tab_options a:before { font-family: WooCommerce; content: "\e012"; }
	</style>
	<?php 
	}
	add_action( 'admin_enqueue_scripts', 'lr_admin_count_down_product_style' ); // admin js and css file

	add_action('get_footer', 'lr_front_count_down_product_style'); // front-end js and css file
	function lr_front_count_down_product_style() {
		 
		wp_enqueue_script('lr_lr_front_custom_js',lr_count_down_product_path.'/js/lr_front_custom.js');
		wp_enqueue_style('lr_front_counter_css',lr_count_down_product_path.'/css/lr_front_counter.css');
		
		wp_localize_script( 'lr_lr_front_custom_js', 'count_down_mail', array(
		'ajax_url' => admin_url( 'admin-ajax.php' )
		));
		
	}
	add_action( 'wp_ajax_count_down_mail', 'count_down_mail_fn');
	add_action( 'wp_ajax_nopriv_count_down_mail', 'count_down_mail_fn' );
	// functions you can call to output text boxes, select boxes, etc.
	add_action('woocommerce_product_data_panels', 'lr_count_down_product_data_fields');
	
	function count_down_mail_fn() {
		$options = get_option('lr_count_down_options');	
		$coupon_name = $options['dropdown1'];
		$coupon_id	 =  wc_get_coupon_id_by_code($coupon_name);
		$couponEmail = sanitize_text_field($_POST['couponEmail']);
		$couponName = sanitize_text_field($_POST['couponName']);
		$message = get_option('lr_count_down_options');					
		$message = $message['textarea_one'];
		$coupon_amount = get_post_meta($coupon_id,'coupon_amount',true);
		$expiry_date = get_post_meta($coupon_id,'expiry_date',true);
		$free_shipping = get_post_meta($coupon_id,'free_shipping',true);
		
		if($free_shipping == "yes"){
			$coupon_amt = $coupon_amount.'% off + Free Shipping' ;
		}else{
			$coupon_amt = $coupon_amount.'% off';
		}
		$body_tags = array(
			'{couponEmail}'			=>	sanitize_text_field($_POST['couponEmail']),
			'{couponName}'  		=>	sanitize_text_field($_POST['couponName']),
			'{order_date}' 			=> 	date("F d,Y"),
			'{site_title}'  		=> 	get_bloginfo( 'name' ),
			'{coupon_name}'			=>  $coupon_name,
			'{coupon_amount}'		=>  $coupon_amt,
			'{expiry_date}'			=>  $expiry_date
		);
		$subject_tags = array(
			'{site_title}'			=>	get_bloginfo( 'name' )
		);
		
		
		$result =str_replace( array_keys( $body_tags ), array_values( $body_tags ), $message );
		$body = $result;
		$to = sanitize_text_field($_POST['couponEmail']);
		$subject = 'You have received a coupon from '.get_bloginfo( 'name' );
		$headers = array('Content-Type: text/html; charset=UTF-8');			
		wp_mail( $to, $subject, $body, $headers );
		wp_die(); 
	}

	function lr_count_down_product_data_fields() {
		global $post;

		// Note the 'id' attribute needs to match the 'target' parameter set above
		?> 	<div id = 'lr_count_down_product_data'
		class = 'panel woocommerce_options_panel' > <?php
			?> 	<div class = 'options_group' > <?php
				  // Checkbox
				  woocommerce_wp_checkbox(
					array(
					  'id' => '_lr_checkbox',
					  'label' => __('Enable', 'woocommerce' ),
					  'description' => __( 'Enable LR WooCommerce Product Countdown for this product!', 'woocommerce' )
					)
				  );
							  // Text Field
				  woocommerce_wp_text_input(
					array(
					  'id' => '_lr_sale_price_from',
					  'label' => __( 'Count Down Data From', 'woocommerce' ),
					  'wrapper_class' => 'show_if_simple', //show_if_simple or show_if_variable
					  'placeholder' => 'Enter Count Down From Date',
					  'desc_tip' => 'true',
					  'description' => __( 'Sell Start Date.', 'woocommerce' )
					)
				  );
					
				  woocommerce_wp_text_input(
					array(
					  'id' => '_lr_sale_price_to',
					  'label' => __( 'Count Down Data To', 'woocommerce' ),
					  'wrapper_class' => 'show_if_simple', //show_if_simple or show_if_variable
					  'placeholder' => 'Enter Count Down To Date',
					  'desc_tip' => 'true',
					  'description' => __( 'Sell End Date.', 'woocommerce' )
					)
				  );
				?> 
				</div>

			</div>
		<?php
	}

	/** Hook callback function to save custom fields information */
	function lr_count_down_product_custom_fields($post_id) {
		
		// Save Checkbox
		//$enable = sanitize_text_field($_POST['_lr_checkbox']);
		$_lr_checkbox = sanitize_text_field($_POST['_lr_checkbox']) ? 'yes' : 'no';
		update_post_meta($post_id, '_lr_checkbox', $_lr_checkbox);
		
		
		$_lr_sale_price_from = sanitize_text_field($_POST['_lr_sale_price_from']);
		if (!empty($_lr_sale_price_from)) {
			update_post_meta($post_id, '_lr_sale_price_from', esc_attr($_lr_sale_price_from));
		}

		$_lr_sale_price_to = sanitize_text_field($_POST['_lr_sale_price_to']);
		if (!empty($_lr_sale_price_to)) {
			update_post_meta($post_id, '_lr_sale_price_to', esc_attr($_lr_sale_price_to));
		}   

	}

	add_action( 'woocommerce_process_product_meta_simple', 'lr_count_down_product_custom_fields'  );

	add_action( 'woocommerce_process_product_meta_variable', 'lr_count_down_product_custom_fields'  );

	function lr_custom_post_product($atts) {	
		
		$args = array(
			'post_type' => 'product',	
			'post_status' => 'publish',
			'meta_key' => '_lr_checkbox',
			'posts_per_page' => -1,
			'meta_query' => array(			
				array(
					'meta_key' => '_lr_checkbox',
					'meta_value' => 'yes'
				)
			)
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) 
		{
			
			while ( $the_query->have_posts() ) 
			{
				$the_query->the_post();			
				$post_id = get_the_ID();
				$to_date = get_post_meta($post_id,"_lr_sale_price_to",true);
				
				$from_date = get_post_meta($post_id,"_lr_sale_price_from",true);
				
				extract(
					shortcode_atts(
						array(
							'userID'	 => 	$post_id,
							'to_date'	 => 	$to_date,
							'form_date'  => 	$form_date
						), 
						$atts)
				);		
			?>
				
			<?php				
			wp_reset_postdata();			
			}
			$options = get_option('lr_count_down_options');					
			$buttonName = $options['test_text_string'];
			$backroundColor = $options['lr_adv_colo_piker'];
			$email_form_title = $options['email_form_title'];
			$email_form_desc = $options['email_form_desc'];
			$coupon_selected = $options['dropdown1'];
			
			
			?>
			<script>
				var timer;

				var compareDate = new Date('<?php echo $from_date; ?>')				
				var to_date = new Date('<?php echo $to_date; ?>')			
				var get_to_date = to_date.getDate();
				var get_compareDate = compareDate.getDate();			
				var get_to_date = get_to_date - get_compareDate			
				compareDate.setDate(compareDate.getDate() + get_to_date); 		
				timer = setInterval(function() {
				  timeBetweenDates(compareDate);
				}, 1000);

				function timeBetweenDates(toDate) {
				  var dateEntered = toDate;
				  var now = new Date();
				  //var difference = dateEntered - now;
				  var difference = dateEntered.getTime() - now.getTime();			
				  if (difference <= 0) {				// Timer done
					clearInterval(timer);			  
				  } else {				
					var seconds = Math.floor(difference / 1000);
					var minutes = Math.floor(seconds / 60);
					var hours = Math.floor(minutes / 60);
					var days = Math.floor(hours / 24);				
					
					hours %= 24;
					minutes %= 60;
					seconds %= 60;

					jQuery("#countdown-days").text(days);
					jQuery("#countdown-hours").text(hours);
					jQuery("#countdown-minutes").text(minutes);
					jQuery("#countdown-seconds").text(seconds);
				  }
				}
				</script>	
				<!-- count down header desing -->
				<header id="sticky-nav-header" style = "background-color:<?php if(!empty($backroundColor)){ echo $backroundColor; }else{ echo '#eaeaea';}?>">
					<div id="scarcity" class="header-section">
					<div id="sale-ends">
						<div id="sale-ends-text">Sale Ends In</div>
						<div id="sale-ends-countdown">
							<span class="countdown-item" id="countdown-days" data-label="DAYS"></span>
							<span class="countdown-item" id="countdown-hours" data-label="HOURS"></span>
							<span class="countdown-item" id="countdown-minutes" data-label="MINS"></span>
							<span class="countdown-item" id="countdown-seconds" data-label="SECS"></span>
						</div>
					</div>
				</div>
					<div id="header-cta" class="header-section">					
					<a type="button" class="button btn cta-button" data-popup-open="popup-1" href="#"><?php if(!empty($buttonName)){echo $buttonName;}else{ echo 'Get Your Coupon';}?></a>
					
				</div>				
				</header>
				<!-- count down header desing end -->
				<!-- form popup -->
				<form id="couponForm" name="couponForm" method = "post" action = "" enctype="multipart/form-data">
					<div id="optin-overlay" class="overlay popup" data-popup="popup-1" style="display: none;">
						<div class="optin popup-inner" style="">
							<a class="popup-close" data-popup-close="popup-1" href="#">x</a>
							<div class="emailForm">
								<div class="headline"><?php if(!empty($email_form_title)){ echo $email_form_title; } else {echo 'Claim Your Coupon';} ?></div>
								<div class="content"><?php if(!empty($email_form_desc)){echo $email_form_desc; } else { echo 'Enter your details below to claim your coupon.';}?></div>
								<div class="error" style="display: none;" id="fname-error">Please enter your first name</div>
								
								<input id="couponName" type="text" name="couponName" placeholder="First name" required>
								<div class="optin-icon"></div>
								<div class="error" style="display: none;" id="email-error">Please enter a valid email address</div>
								
								<input id="couponEmail" type="email" name="couponEmail" placeholder="Email address" required>
								<div class="optin-icon"></div>							
								
								<input id="optin-submit" type="submit" class="button" name="Claim My Coupon" value="<?php if(!empty($buttonName)){echo $buttonName;}else{ echo'Get Your Coupon';}?>">
								<img style="margin: 0 auto;" width="160" height="77" src="<?php echo plugin_dir_url(__FILE__) ?>/image/amazon-logo.png" ><p id="privacy" class="content-editable content-editable-html">
								</p>
								
							</div>
							<div class="overlay-inner succuess  style="display:none;">
								<div class="headline">Thank You!</div>
								
								<div class="content">Please check your email inbox to access your coupon code. (Note: It may take a few minutes for your email to arrive. If it doesn't, please check your spam folder.)</div>
							</div>
						</div>						
					</div>
				</form>				
			<?php			
		}		
	}

	add_shortcode( 'LR_Count_Down_Product', 'lr_custom_post_product' );

	// here print this shortcode on single product page
	add_action( 'woocommerce_before_single_product','lr_check_single_product_page' );
	function lr_check_single_product_page() {
		global $post;
		$product_id = $post->ID;
		$to_date = get_post_meta($product_id,"_lr_sale_price_to",true);
		$from_date = get_post_meta($product_id,"_lr_sale_price_from",true);	
		$count_down_enable = get_post_meta($product_id,"_lr_checkbox",true);
		
		if($count_down_enable == 'yes'){
			echo do_shortcode( '[LR_Count_Down_Product to_date="'.$to_date.'" from_date="'.$from_date.'" userID="'.$post_id.'"]' );
		}	
	}
} else {
?>
<div class="error">
        <p><?php _e( 'Logicrays WP Product Count Down is enabled but not effective. It requires WooCommerce in order to work.' ); ?></p>
    </div>
<?php
}