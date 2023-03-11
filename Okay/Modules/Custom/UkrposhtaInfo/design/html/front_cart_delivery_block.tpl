{if $delivery->module_id == $up_delivery_module_id}
    <div class="ukrposhta_div fn_delivery_ukrposhta" style="margin-top: 15px;">
        <div class="up_preloader"></div>

            <div class="form__group">
                <select class="region_ukrposhta form__input form__placeholder--focus" name="ukrposhta_region" style="width: 100%">
                    <option selected value="">{$lang->up_form_enter_region|escape}*</option>
                    {foreach $up_regions as  $region}
                        <option value="{$region->region_id}" {if $region->region_id == $request_data.ukrposhta_delivery_region_id}selected{/if}>{$region->name} </option>
                    {/foreach}
                </select>
            </div>

            <div class="form__group">
                <input class="district_ukrposhta form__input form__placeholder--focus" name="ukrposhta_district" autocomplete="on" type="text" value="{$request_data.ukrposhta_district|escape}" >
                <span class="form__placeholder">{$lang->up_cart_district}*</span>
            </div>

            <div class="form__group">
                <input class="city_ukrposhta form__input form__placeholder--focus" name="ukrposhta_city" autocomplete="on" type="text" value="{$request_data.ukrposhta_city|escape}" >
                <span class="form__placeholder">{$lang->up_cart_city}*</span>
            </div>

            <div class="form__group">
                <select class="fn_select_office_ukrposhta office_ukrposhta" tabindex="1" name="ukrposhta_office" style="width: 100%;">
                    <option value="" {if empty($request_data.ukrposhta_delivery_office_id)}selected{/if}>{$lang->up_form_enter_office|escape}</option>
                </select>
            </div>

        {if $up_redelivery_payments_ids}    
            <div class="form__group">
                <label for="redelivery_{$delivery->id}">
                <input name="ukrposhta_redelivery" id="redelivery_{$delivery->id}" value="1" type="checkbox" {if $request_data.ukrposhta_redelivery == true}checked{/if} />
                {$lang->up_cart_cod} 
            </label>
            </div>
        {/if}
    
        <input name="is_ukrposhta_delivery" type="hidden" value="1"/>
        <input name="ukrposhta_delivery_region_id" type="hidden" value="{$request_data.ukrposhta_delivery_region_id}"/>
        <input name="ukrposhta_delivery_district_id" type="hidden" value="{$request_data.ukrposhta_delivery_district_id}"/>
        <input name="ukrposhta_delivery_city_id" type="hidden" value="{$request_data.ukrposhta_delivery_city_id}"/>
        <input name="ukrposhta_delivery_office_id" type="hidden" value="{$request_data.ukrposhta_delivery_office_id}"/>
    </div>

    <script>
        var form_enter_ukrposhta_region = "{$lang->up_form_enter_region|escape}";
        var form_enter_ukrposhta_district = "{$lang->up_form_enter_district|escape}";
        var form_enter_ukrposhta_city = "{$lang->up_form_enter_city|escape}";
        var form_enter_ukrposhta_office = "{$lang->up_form_enter_office|escape}";
    </script>
{/if}
