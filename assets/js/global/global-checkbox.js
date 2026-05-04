jQuery(window).on('load', function() 
{
	jQuery('select[name="mrkv_checkbox_cashiers"]').select2();

	jQuery('.mrkv_checkbox_remove_receipt').click(function(){
		let $btn = jQuery(this);
        let order_id = $btn.attr('data-order');
        let $loader = $btn.find('.mrkv_ua_ship_create_receipt__loader');

        if(order_id)
        {
        	$loader.show();
        	jQuery.ajax({
                type: 'POST',
                url: mrkv_checkbox_helper.ajax_url,
                data: {
                    action: 'mrkv_checkbox_remove_receipt',
                    order_id: order_id,
                    nonce: mrkv_checkbox_helper.nonce,
                },
                success: function(response) {
                    $loader.hide();
                    
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    $loader.hide();
                    alert('Server error occurred.');
                    location.reload();
                }
            });
        }
	});

	jQuery('.mrkv_ua_ship_custom_receipt').click(function(){
		let $btn = jQuery(this);
        let order_id = $btn.attr('data-order');
        let custom_receipt = jQuery('.mrkv_checkbox_custom_receipt').val();
        let $loader = $btn.find('.mrkv_ua_ship_create_receipt__loader');

        if(order_id && custom_receipt)
        {
        	$loader.show();
        	jQuery.ajax({
                type: 'POST',
                url: mrkv_checkbox_helper.ajax_url,
                data: {
                    action: 'mrkv_checkbox_custom_receipt',
                    order_id: order_id,
                    custom_receipt: custom_receipt,
                    nonce: mrkv_checkbox_helper.nonce,
                },
                success: function(response) {
                    $loader.hide();
                    
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    $loader.hide();
                    alert('Server error occurred.');
                    location.reload();
                }
            });
        }
	});

	jQuery('.mrkv_ua_ship_global_create__receipt').click(function(){
		let $btn = jQuery(this);
        let order_id = $btn.attr('data-order');
        let cashbox_id = $btn.closest('.mrkv_checkbox_create_receipt_form').find('select[name="mrkv_checkbox_cashiers"]').val();
        let $loader = $btn.find('.mrkv_ua_ship_create_receipt__loader');

        if(order_id && cashbox_id)
        {
        	$loader.show();
        	jQuery.ajax({
                type: 'POST',
                url: mrkv_checkbox_helper.ajax_url,
                data: {
                    action: 'mrkv_checkbox_create_receipt',
                    order_id: order_id,
                    cashbox_id: cashbox_id,
                    nonce: mrkv_checkbox_helper.nonce,
                },
                success: function(response) {
                    $loader.hide();
                    
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    $loader.hide();
                    alert('Server error occurred.');
                    location.reload();
                }
            });
        }
	});
});