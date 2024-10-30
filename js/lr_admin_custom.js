jQuery(document).ready(function () {
	jQuery('#_lr_sale_price_from,#_lr_sale_price_to').datetimepicker({
	format:'m/d/Y H:i:s'
	});
	jQuery( "#_lr_sale_price_from,#_lr_sale_price_to" ).datetimepicker({ minDate: 0});
});