function setCookie(key, value, expiry) {
    var expires = new Date();
    expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
    document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
}

function getCookie(key) {
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? keyValue[2] : null;
}

function eraseCookie(key) {
    var keyValue = getCookie(key);
    setCookie(key, keyValue, '-1');
}

var hash = window.location.hash;

if(!hash)
{
        var hash = getCookie('current_page_hash');

        if(hash)
        {
                window.location.hash = hash;
        }
        eraseCookie('current_page_hash');
}

if(hash)
{
        var tab_main = hash.replace('-mrkv','');
        tab_main = tab_main.replace('#','');
        jQuery('.admin_mrkv_ua_shipping__tabs_main__inner .active').removeClass('active');
        jQuery('.mrkv_up_ship_tab_btn[data-tab="' + tab_main + '"]').addClass('active');

        jQuery('.mrkv_up_ship_shipping_tab_block').removeClass('active');
        jQuery('#' + tab_main).addClass('active');
}

jQuery(window).on('load', function() 
{
        jQuery('.mrkv_ua_shipping_method_form').on('submit', function(e) {
                var hash = window.location.hash;

                if(hash)
                {
                        setCookie('current_page_hash',hash,'1');
                }
        });
        if(jQuery('.mrkv_up_ship_tab_btn').length != 0)
        {
                jQuery('.mrkv_up_ship_tab_btn').click(function()
                {
                        jQuery('.admin_mrkv_ua_shipping__tabs_main__inner .active').removeClass('active');
                        jQuery(this).addClass('active');

                        const shipping_tab = jQuery(this).attr('data-tab');

                        jQuery('.mrkv_up_ship_shipping_tab_block').removeClass('active');
                        jQuery('#' + shipping_tab).addClass('active');
                });
        }
        jQuery('.admin_mrkv_ua_shipping__settings select').select2({
                width: '100%',
        });

    jQuery(document).on('input keydown change', '.mrkv_checkbox__cashier__block input', function() {
        var $parentBlock = jQuery(this).closest('.mrkv_checkbox__cashier__block');
        $parentBlock.find('input[type="hidden"][name*="[signin]"]').val('');
    });

    jQuery(document).on('input', '.mrkv-fake-password', function(e) {
        var $fakeInput = jQuery(this);
        var $wrapper = $fakeInput.closest('.mrkv-password-mask-wrapper');
        var $realInput = $wrapper.find('.mrkv-real-password');
        
        var currentFake = $fakeInput.val();
        var realVal = $realInput.val();

        if (currentFake.length > realVal.length) {
            var addedStr = currentFake.substr(realVal.length);
            realVal += addedStr;
        } 
        else if (currentFake.length < realVal.length) {
            realVal = realVal.substring(0, currentFake.length);
        }
        
        $realInput.val(realVal);
        if (realVal.length > 4) {
            var dots = "•".repeat(realVal.length - 4);
            var lastFour = realVal.substring(realVal.length - 4);
            $fakeInput.val(dots + lastFour);
        } else {
            $fakeInput.val(realVal);
        }
    });

    jQuery('select[name^="mrkv_checkbox[automation][payments]"][name$="[label]"]').on('change', function() {
        var $select = jQuery(this);
        var selectedValue = $select.val();
        var nameAttr = $select.attr('name');
        var methodSlug = nameAttr.match(/\[payments\]\[(.*?)\]/)[1];
        
        var $customLabel = jQuery('input[name="mrkv_checkbox[automation][payments][' + methodSlug + '][custom_label]"]');
        
        if (methodSlug.includes('monopay') && selectedValue.includes('plata')) {
            $customLabel.val('Платіж через інтегратора plata by mono');
        } else if (methodSlug.includes('liqpay') && selectedValue.includes('LiqPay')) {
            $customLabel.val('Платіж через інтегратора Лікпей');
        }
    });

    jQuery('.mrkv_checkbox__change_shift_status').click(function() {
        let $btn = jQuery(this);
        let new_status = $btn.attr('data-status');
        let cashbox_id = $btn.attr('data-cashbox');
        
        let $loader = $btn.find('.mrkv_ua_ship_create_invoice__loader');
        let $parentRow = $btn.closest('.mrkv_checkbox__shift__line');
        let $statusLabel = $parentRow.find('.mrkv_checkbox__shift__status');

        if (new_status && cashbox_id) {
            $loader.show();

            jQuery.ajax({
                type: 'POST',
                url: mrkv_checkbox_helper.ajax_url,
                data: {
                    action: 'mrkv_checkbox_change_shift_status',
                    new_status: new_status,
                    cashbox_id: cashbox_id,
                    nonce: mrkv_checkbox_helper.nonce,
                },
                success: function(response) {
                    $loader.hide();
                    
                    if (response.success) {
                        if (new_status === 'open') {
                            let openedText = $statusLabel.attr('data-contraryopen');
                            $statusLabel.text(openedText).removeClass('closed').addClass('opened');
                            
                            let closeBtnText = $btn.attr('data-contraryclose');
                            $btn.text(closeBtnText).attr('data-status', 'close');
                        } else {
                            let closedText = $statusLabel.attr('data-contraryclose');
                            $statusLabel.text(closedText).removeClass('opened').addClass('closed');
                            
                            let openBtnText = $btn.attr('data-contraryopen');
                            $btn.text(openBtnText).attr('data-status', 'open');
                        }
                        
                        $btn.append('<div class="mrkv_ua_ship_create_invoice__loader"></div>');
                    } else {
                        alert('Shift action had a problem: ' + (response.data || 'Unknown error'));
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