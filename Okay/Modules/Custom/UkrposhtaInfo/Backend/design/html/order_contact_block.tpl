<div class="fn_delivery_ukrposhta"{if $delivery->module_id != $urkposhta_module_id} style="display: none;"{/if}>
    <input name="ukrposhta_district_id" type="hidden" value="{$ukrposhta_delivery_data->district_id|escape}"/>
    <input name="ukrposhta_city_id" type="hidden" value="{$ukrposhta_delivery_data->city_id|escape}"/>
    <input name="ukrposhta_office_id" type="hidden" value="{$ukrposhta_delivery_data->office_id|escape}"/>

    <div class="mb-1">
        <div class="heading_label">{$btr->order_up_region}</div>
        <select class="fn_ukrpost_region form-control selectpicker" name="ukrposhta_region_id">
            <option selected value="">{$lang->up_form_enter_region|escape}</option>
            {foreach $ukrposhta_regions as $region}
                <option value="{$region->region_id}"
                        {if $region->disabled}disabled{/if}
                        {if $region->region_id == $ukrposhta_delivery_data->region_id}selected{/if}
                >{$region->name}</option>
            {/foreach}
        </select>
    </div>
    <div class="mb-1">
        <div class="heading_label">{$btr->order_up_district}</div>
        <input type="text"
               class="fn_ukrpost_district form-control"
               autocomplete="off"
               name="ukrposhta_district"
               value="{$ukrposhta_district->name}">
    </div>
    <div class="mb-1">
        <div class="heading_label">{$btr->order_up_city}</div>
        <input type="text" class="fn_ukrpost_city form-control" autocomplete="off"
               name="ukrposhta_city" value="{$ukrposhta_city->name}">
    </div>
    <div class="mb-1">
        <div class="heading_label">{$btr->order_up_office}
            <i class="fn_tooltips" title="{$btr->up_update_address_info|escape}">
                {include file='svg_icon.tpl' svgId='icon_tooltips'}
            </i>
        </div>
        <select name="ukrposhta_office"
                tabindex="1"
                class="fn_ukrpost_office form-control"
                data-live-search="true">
            {foreach $ukrposhta_offices as $office}
                <option value="{$office->office_id}"
                        {if $office->office_id == $ukrposhta_delivery_data->office_id}selected{/if}>{$office->description}</option>
            {/foreach}
        </select>
    </div>

    <div class="mb-1">
        <div class="heading_label">
            <input type="checkbox"
                   id="ukrposhta_redelivery"
                   name="ukrposhta_redelivery"
                   value="1"
                   {if $ukrposhta_delivery_data->redelivery}checked{/if}/>
            <label for="ukrposhta_redelivery">{$btr->order_up_redelivery}</label>
        </div>
    </div>
</div>

<script src="design/js/autocomplete/jquery.autocomplete-min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    var prev_district = $(document).find('[name="ukrposhta_district_id"]').val();
    var prev_city = $(document).find('[name="ukrposhta_city_id"]').val();
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

    $('select[name="delivery_id"]').on('change', function()
    {
        if ($(this).children(':selected').data('module_id') === '{$urkposhta_module_id}')
        {
            $('.fn_delivery_ukrposhta').show();
        } else
        {
            $('.fn_delivery_ukrposhta').hide();
        }
    });

    $('.fn_ukrpost_region').on('change', function()
    {
        $('.fn_ukrpost_district').val('');
        $('[name="ukrposhta_district_id"').val('');
        $('.fn_ukrpost_city').val('');
        $('[name="ukrposhta_city_id"').val('');
        $('.fn_ukrpost_office').val('');
        $('[name="ukrposhta_office_id"').val('');
    });

    $('select[name="ukrposhta_office"]').on('change', function()
    {
        $('[name="ukrposhta_office_id"').val($(this).val());
    });

    $('.fn_delivery_ukrposhta .fn_ukrpost_district').devbridgeAutocomplete({
        serviceUrl: okay.router['Custom_UkrposhtaInfo_find_district'],
        minChars: 1,
        maxHeight: 320,
        noCache: true,
        showNoSuggestionNotice: true,
        noSuggestionNotice: '{$btr->up_ukrpostha_empty_results}',
        onSearchStart: function(params)
        {
            params.region_id = $(document).find('[name="ukrposhta_region_id"]').val();
            prev_district = $(document).find('[name="ukrposhta_district_id"]').val();
        },
        onSelect: function(suggestion)
        {
            if ($('.fn_ukrpost_region').val() !== suggestion.data.region_id)
            {
                $('.fn_ukrpost_region').val(suggestion.data.region_id).change();
                $(this).val(suggestion.data.name);
            }
            $('[name="ukrposhta_district_id"]').val(suggestion.data.district_id);
            if (prev_district && prev_district !== suggestion.data.district_id)
            {
                $('[name="ukrposhta_city_id"]').val('');
                $('.fn_ukrpost_city').val('');
                $('[name="ukrposhta_office_id"]').val('');
            }
        },
        formatResult: function(suggestion, currentValue)
        {
            var reEscape = new RegExp(
                '(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{','}', '\\'].join('|\\') + ')', 'g');
            var pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
            return '<div style=\'text-align: left\'>' +
                suggestion.value.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>') + '<\/div>';
        },
    });

    $('.fn_delivery_ukrposhta .fn_ukrpost_city').devbridgeAutocomplete({
        serviceUrl: okay.router['Custom_UkrposhtaInfo_find_city'],
        minChars: 1,
        maxHeight: 500,
        noCache: true,
        showNoSuggestionNotice: true,
        noSuggestionNotice: '{$btr->up_ukrpostha_empty_results}',
        onSearchStart: function(params)
        {
            params.region_id = $(document).find('[name="ukrposhta_region_id"]').val();
            params.district_id = $(document).find('[name="ukrposhta_district_id"]').val();
            prev_city = $(document).find('[name="ukrposhta_city_id"]').val();
        },
        onSelect: function(suggestion)
        {
            if ($('.fn_ukrpost_region').val() !== suggestion.data.region_id)
            {
                $('.fn_ukrpost_region').val(suggestion.data.region_id).change();
                $(this).val(suggestion.data.name);
            }
            $('[name="ukrposhta_district_id"]').val(suggestion.data.district_id);
            $('.fn_ukrpost_district').val(suggestion.data.district_name);
            $('[name="ukrposhta_city_id"]').val(suggestion.data.city_id);
            if (prev_city !== suggestion.data.city_id)
            {
                $('[name="ukrposhta_office_id"]').val('');
            }
            var office = $('[name="ukrposhta_office_id"]').val();

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
                        $('[name="ukrposhta_office"]').html(data.response.offices).select2(officesParams);
                        if (office)
                        {
                            $('[name="ukrposhta_office"]').val(office).change();
                        }
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
            var reEscape = new RegExp(
                '(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{','}', '\\'].join('|\\') + ')', 'g');
            var pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
            return '<div style=\'text-align: left\'>' +
                suggestion.value.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>') + '<\/div>';
        },
    });
</script>