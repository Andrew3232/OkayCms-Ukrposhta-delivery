const upConfigParamsObj = {
    //placeholder: 'Выберите город...', // Place holder text to place in the select
    minimumResultsForSearch: 3, // Overrides default of 15 set above
    width: 'resolve',
    matcher: function(params, data)
    {
        if ($.trim(params.term) === '')
        {
            return data;
        }
        if (data.text.toLowerCase().startsWith(params.term.toLowerCase()))
        {
            var modifiedData = $.extend({}, data, true);
            return modifiedData;
        }
        return null;
    },
};

const officesParams = {
    matcher: function(params, data)
    {
        if ($.trim(params.term) === '')
        {
            return data;
        }
        if ($.isNumeric(params.term))
        {
            if (~data.text.indexOf('№' + params.term))
            {
                return data;
            }
        } else if (~data.text.toLowerCase().indexOf(params.term.toLowerCase()))
        {
            var modifiedData = $.extend({}, data, true);
            return modifiedData;
        }
        return null;
    },
};

var region = $(document).find('[name="ukrposhta_region"]').val();
var prev_district = $(document).find('[name="ukrposhta_delivery_district_id"]').val();
var prev_city = $(document).find('[name="ukrposhta_delivery_city_id"]').val();

$('select.region_ukrposhta').select2(upConfigParamsObj);
$('select.office_ukrposhta').select2(upConfigParamsObj);

$(document).ready(function()
{
    init();
    if($('.delivery__label.active').length)
    {
        $('.delivery__label.active').removeClass('active'); // fix blocked first payment
    }
});

$(document).on('change', 'input[name="ukrposhta_redelivery"]', function(e)
{
    update_up_payments();
    select_first_active_up_payment();
});
$(document).on('change', '.region_ukrposhta', function(e)
{
    $('[name="ukrposhta_delivery_region_id"]').val($(this).val());
    $('[name="ukrposhta_delivery_district_id"]').val('');
    $('[name="ukrposhta_delivery_city_id"]').val('');
    $('[name="ukrposhta_delivery_office_id"]').val('');
    $('[name="ukrposhta_district"]').val('');
    $('[name="ukrposhta_city"]').val('');
    $('[name="ukrposhta_office"]').val('').trigger('change');
});

$(document).on('change', 'input[name="ukrposhta_office"]', function(e)
{
    $('[name="ukrposhta_delivery_office_id"]').val($(this).val());
});

function init()
{
    $('.up_preloader').hide();
}

$('.fn_delivery_ukrposhta input.district_ukrposhta').devbridgeAutocomplete({
    serviceUrl: okay.router['Custom_UkrposhtaInfo_find_district'],
    minChars: 1,
    maxHeight: 320,
    noCache: true,
    showNoSuggestionNotice: true,
    noSuggestionNotice: '' + okay.up_ukrpostha_not_found,
    onSearchStart: function(params)
    {
        params.region_id = $(document).find('[name="ukrposhta_region"]').val();
        prev_district = $(document).find('[name="ukrposhta_delivery_district_id"]').val();

    },
    onSelect: function(suggestion)
    {
        if ($('[name="ukrposhta_region"]').val() !== suggestion.data.region_id)
        {
            $('[name="ukrposhta_region"]').val(suggestion.data.region_id).change();
            $(this).val(suggestion.data.name);
        }
        $('[name="ukrposhta_delivery_district_id"]').val(suggestion.data.district_id);

        if (prev_district && prev_district !== suggestion.data.district_id)
        {
            $('[name="ukrposhta_delivery_city_id"]').val('');
            $('[name="ukrposhta_delivery_office_id"]').val('');
            $('[name="ukrposhta_city"]').val('');
            $('[name="ukrposhta_office"]').val('').trigger('change');
        }

    },
    formatResult: function(suggestion, currentValue)
    {
        var reEscape = new RegExp('(\\' +['/','.','*','+','?','|','(',')','[',']','{','}','\\'].join('|\\') + ')', 'g');
        var pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
        return '<div style=\'text-align: left\'>' + suggestion.value.replace(new RegExp(pattern, 'gi'),'<strong>$1<\/strong>') + '<\/div>';
    },
});

$('.fn_delivery_ukrposhta input.city_ukrposhta').devbridgeAutocomplete({
    serviceUrl: okay.router['Custom_UkrposhtaInfo_find_city'],
    minChars: 1,
    maxHeight: 500,
    noCache: true,
    showNoSuggestionNotice: true,
    noSuggestionNotice: '' + okay.up_ukrpostha_not_found,
    onSearchStart: function(params)
    {
        params.region_id = $(document).find('[name="ukrposhta_delivery_region_id"]').val();
        params.district_id = $(document).find('[name="ukrposhta_delivery_district_id"]').val();
        prev_city = $(document).find('[name="ukrposhta_delivery_city_id"]').val();
    },
    onSelect: function(suggestion)
    {
        if ($('[name="ukrposhta_region"]').val() !== suggestion.data.region_id)
        {
            $('[name="ukrposhta_region"]').val(suggestion.data.region_id).change();
            $(this).val(suggestion.data.name);
        }
        $('[name="ukrposhta_delivery_district_id"]').val(suggestion.data.district_id);
        $('[name="ukrposhta_district"]').val(suggestion.data.district_name);
        $('[name="ukrposhta_delivery_city_id"]').val(suggestion.data.city_id);

        if (prev_city !== suggestion.data.city_id)
        {
            $('[name="ukrposhta_delivery_office_id"]').val('');
        }
        var office = $('[name="ukrposhta_office"]').val();

        $.ajax({
            url: okay.router['Custom_UkrposhtaInfo_get_post_offices'],
            data: {
                region_id: suggestion.data.region_id,
                district_id: suggestion.data.district_id,
                city_id: suggestion.data.city_id,
                office_id: office,
            },
            dataType: 'json',
            type: 'post',
            success: function(data)
            {
                if (data.hasOwnProperty('response') && data.response.success)
                {
                    $('[name="ukrposhta_office"]').
                        html(data.response.offices).
                        select2(officesParams);
                } else
                {
                    $('[name="ukrposhta_office"]').
                        find('option').
                        each(function(item)
                        {
                            if (!$(this).attr('disabled'))
                            {
                                $(this).remove();
                            }
                        });

                }
            },
        });
    },
    formatResult: function(suggestion, currentValue)
    {
        var reEscape = new RegExp('(\\' +
            [
                '/',
                '.',
                '*',
                '+',
                '?',
                '|',
                '(',
                ')',
                '[',
                ']',
                '{',
                '}',
                '\\'].join(
                '|\\') + ')', 'g');
        var pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
        return '<div style=\'text-align: left\'>' +
            suggestion.value.replace(new RegExp(pattern, 'gi'),
                '<strong>$1<\/strong>') + '<\/div>';
    },
});

$('[name="delivery_id"]').on('change', function()
{
    if (Number($(this).data('module_id')) !==
        Number(okay.up_delivery_module_id))
    {
        return;
    }
    update_up_payments();
    select_first_active_up_payment();
});

function update_up_payments()
{
    const payment_method_ids = get_up_payment_method_ids();
    const redelivery_enabled = $('input[name="delivery_id"]:checked').
        closest('.fn_delivery_item').
        find('[name="ukrposhta_redelivery"]').
        prop('checked');

    if (redelivery_enabled)
    {
        for (const payment_id of payment_method_ids)
        {
            if (okay.up_redelivery_payments_ids.includes(payment_id))
            {
                $(`.fn_payment_method__item_${payment_id}`).show();
            } else
            {
                $(`.fn_payment_method__item_${payment_id}`).hide();
            }
        }
    } else
    {
        for (const payment_id of payment_method_ids)
        {
            if (okay.up_redelivery_payments_ids.includes(payment_id))
            {
                $(`.fn_payment_method__item_${payment_id}`).hide();
            } else
            {
                $(`.fn_payment_method__item_${payment_id}`).show();
            }
        }
    }
}

function select_first_active_up_payment()
{
    const payment_method_elements = $('[name="payment_method_id"]');
    for (const element of payment_method_elements)
    {
        const id = element.attributes.id.nodeValue;
        if (!$(`#${id}`).closest('.fn_payment_method__item').is(':hidden'))
        {
            $(`#${id}`).click();//trigger('click');
            break;
        }
    }
}

function get_up_payment_method_ids()
{
    let deliveryInput = $('input[name="delivery_id"]:checked').
        closest('.fn_delivery_item').
        find('[name="ukrposhta_redelivery"]').
        closest('.fn_delivery_item').
        find('[name="delivery_id"]');

    if (deliveryInput.data('payment_method_ids') !== undefined)
    {
        return String(deliveryInput.data('payment_method_ids')).
            split(',').
            map(Number);
    } else
    {
        return [];
    }
}