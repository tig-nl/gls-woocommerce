<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
?>
<h3 id="delivery_options_heading"><?php _e("Shipping Options", "gls-woocommerce"); ?></h3>
<div id="delivery_options" class="gls-woocommerce-checkout-delivery-options">
    <div class="gls-error error">
    </div>
    <div class="gls-tabs">
        <div class="gls-tab gls-tab-delivery active">
            <span><?php _e('Delivery', 'gls-woocommerce'); ?></span>
        </div>
        <div class="gls-tab gls-tab-pickup">
            <span><?php _e('Pick up', 'gls-woocommerce'); ?></span>
        </div>
    </div>
    <div class="gls-services">
        <div class="gls-delivery-options">
            <div class="container gls-delivery-option" style="display: none;">
                <strong class="gls-sub-delivery-options-title" style="display: none;">%%title%%</strong>

                <input type="radio" name="gls_delivery_option" class="radio" id="default"/>
                <label class="label" for="default">%%title%%</label>
                <span class="delivery-fee">%%fee%%</span>

                <div class="gls-sub-delivery-options">
                    <div class="gls-sub-delivery-option" style="display: none;">
                        <input type="radio" name="gls_delivery_option" class="radio" />
                        <label class="sub-label" for="%%service%%">%%title%%</label>
                        <span class="delivery-fee">%%fee%%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="gls-parcel-shops">
            <div class="container gls-parcel-shop" style="display: none;">
                <input type="radio" name="gls_delivery_option" class="radio" value="%%parcelShopId" id="shop_%%parcelShopId%%" />
                <label for="shop_%%parcelShopId%%"></label><br/>

                <div class="address-information">
                    <span class="street">%%street%% %%houseNo%%</span>
                    <span class="city">%%zipcode %%city%%</span>
                    <span class="distance-meters">%%distanceMeters%% m</span>
                </div>

                <a class="open-business-hours"><?php _e('Show business hours', 'gls-woocommerce'); ?></a>
                <div class="table container">
                    <a class="close" onclick="closeBusinessHours(this)"><?php _e('Close', 'gls-woocommerce'); ?></a>
                    <div class="table" style="display: none;">
                        <div class="row">
                            <div class="cell">%%dayOfWeek%%</div>
                            <div class="cell">%%openTime%% - %%closedTime%%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
