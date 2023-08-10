jQuery(function($) {
    $('input[name=ppo_autocreate]').on('change', function(e) {
        if ($(this).is(":checked")) {
            $('.skip-receipt-creation').show()
            $('.select-order-statuses').show()
            $('.order-statuses').show()
            $('select.chosen.order-statuses').chosen({
                width: '300px',
            })
        } else {
            $('.order-statuses').hide()
            $('.select-order-statuses').hide()
            $('select.chosen.order-statuses').chosen('destroy')
            $('select.chosen.order-statuses').val('')

            $('.skip-receipt-creation').hide()
            $('.select-order-status').hide()
            $('td.skip-receipt-creation input[type=radio]').val('')
        }
    })
    if ($('input[name=ppo_autocreate]').is(':checked')) {
        $('select.chosen.order-statuses').chosen({
            width: '300px',
        })
    }
    jQuery('#ppo_is_dev_mode').change(function(){
        if(jQuery(this).is(':checked')){
            jQuery('.test-text-checkbox').show();
        }
        else{
            jQuery('.test-text-checkbox').hide();
        }
    });
    jQuery('.code-tax-checkbox .tooltip').click(function(){
        window.open('https://my.checkbox.ua/dashboard/taxrates', '_blank');
    });
    jQuery('select.chosen').chosen();
})