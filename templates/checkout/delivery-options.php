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
 * This source file is subject to the GPLv3 or later.
 * It is available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
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
 * @license     http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<div class="gls-wrapper">
    <h3 class="gls-block-title" id="delivery_options_heading"><?php _e("Shipping Options", "gls-woocommerce"); ?></h3>
    <div id="delivery_options" class="gls-woocommerce-checkout-delivery-options">
        <div class="gls-tabs">
            <div class="gls-tab gls-tab-delivery active">
                <span class="gls-tab-title"><?php _e('Home delivery', 'gls-woocommerce'); ?></span>
            </div>
            <div class="gls-tab gls-tab-pickup">
                <span class="gls-tab-title"><?php _e('ParcelShop', 'gls-woocommerce'); ?></span> <span class="gls-tab-price"><?= GLS()->delivery_options()->format_shop_delivery_fee(); ?></span>
            </div>
        </div>
        <div class="gls-error error" style="display: none;"></div>
        <div class="gls-services">
            <div class="gls-delivery-options">
                <div class="gls-container gls-delivery-option" style="display: none;">
                    <strong class="gls-sub-delivery-options-title" style="display: none;">%%title%%</strong>

                    <input type="radio" name="gls_delivery_option" class="radio" id="default"/>
                    <label class="gls-label" for="default">%%title%%</label>
                    <span class="delivery-fee">%%fee%%</span>

                    <div class="gls-sub-delivery-options">
                        <div class="gls-sub-delivery-option" style="display: none;">
                            <input type="radio" name="gls_delivery_option" class="radio" />
                            <label class="gls-sub-label" for="%%service%%">%%title%%</label>
                            <span class="delivery-fee">%%fee%%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="gls-parcel-shops" style="display: none;">
                <div class="gls-container gls-parcel-shop" style="display: none;">
                    <input type="radio" name="gls_delivery_option" class="radio" value="%%parcelShopId%%" id="shop_%%parcelShopId%%" />
                    <label for="shop_%%parcelShopId%%"></label><br/>

                    <div class="address-information">
                        <span class="street">%%street%% %%houseNo%%</span>
                        <span class="city">%%zipcode %%city%%</span>
                        <span class="distance-meters">%%distanceMeters%% m</span>
                    </div>

                    <a class="open-business-hours-link">
                        <?php _e('Show business hours', 'gls-woocommerce'); ?>
                    </a>

                    <div class="table gls-container">
                        <a class="gls-close">
                            <span><?php _e('Close', 'gls-woocommerce'); ?></span>
                            <svg width="13px" height="13px" viewBox="0 0 13 13" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="business_hours" transform="translate(-444.000000, -13.000000)" fill="#003BFB">
                                        <g id="Group-4">
                                            <path d="M451.863961,19.5 L456.863961,24.5 L455.449747,25.9142136 L450.5,20.9644661 L445.550253,25.9142136 L444.136039,24.5 L449.136039,19.5 L444.136039,14.5 L445.550253,13.0857864 L450.5,18.0355339 L455.449747,13.0857864 L456.863961,14.5 L451.863961,19.5 Z" id="Combined-Shape"></path>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </a>
                        <div class="table parcel-business-hours">
                            <div class="gls-row">
                                <div class="gls-cell day-of-the-week">%%dayOfWeek%%</div>
                                <div class="gls-cell opening-hours">%%openTime%% - %%closedTime%%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>