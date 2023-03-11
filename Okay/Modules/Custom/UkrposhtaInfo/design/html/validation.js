$(function(){
    if ($('.fn_validate_cart').length > 0) {
        $('.fn_validate_cart select[name="ukrposhta_region"]').rules('add', {
            required: true,
            messages: {
                required: form_enter_ukrposhta_region,
            }
        });
        $('.fn_validate_cart input[name="ukrposhta_district"]').rules('add', {
            required: true,
            messages: {
                required: form_enter_ukrposhta_district,
            }
        });
        $('.fn_validate_cart input[name="ukrposhta_city"]').rules('add', {
            required: true,
            messages: {
                required: form_enter_ukrposhta_city,
            }
        });
        $('.fn_validate_cart input[name="ukrposhta_office"]').rules('add', {
            required: true,
            messages: {
                required: form_enter_ukrposhta_office,
            }
        });
    }
});