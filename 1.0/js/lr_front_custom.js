jQuery(function() {
//----- OPEN
jQuery('[data-popup-open]').on('click', function(e)  {
var targeted_popup_class = jQuery(this).attr('data-popup-open');
jQuery('[data-popup="' + targeted_popup_class + '"]').fadeIn(350);
e.preventDefault();
});
//----- CLOSE
jQuery('[data-popup-close]').on('click', function(e)  {
var targeted_popup_class = jQuery(this).attr('data-popup-close');
jQuery('[data-popup="' + targeted_popup_class + '"]').fadeOut(350);
e.preventDefault();
});
});
jQuery(document).ready(function() {
	jQuery('#couponForm').submit(function(e){	 
		e.preventDefault();
		var couponName =  jQuery('#couponName').val();
		var couponEmail =  jQuery('#couponEmail').val();

		jQuery.ajax({
			url: count_down_mail.ajax_url,
			type: "POST",
			data:{ 
				action: 'count_down_mail', 
				couponName: couponName,
				couponEmail: couponEmail
			},
			success:function(res){
				jQuery(".emailForm").css("display", "none");				 
				jQuery(".succuess").css("display", "block");
			}
		}); 
	});
});