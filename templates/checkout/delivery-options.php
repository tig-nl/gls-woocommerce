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

$deliveryOptions = GLS()->api_delivery_options()->call();
?>
<h3 id="delivery_options_heading"><?php _e("Shipping Options", "gls-woocommerce"); ?></h3>
<div id="delivery_options" class="gls-woocommerce-checkout-delivery-options">
    <?php if (isset($deliveryOptions->deliveryOptions) && count($deliveryOptions->deliveryOptions) > 0): ?>
        <?php $options = $deliveryOptions->deliveryOptions; ?>
        <?php foreach ($options as $option): ?>
            <div class="container gls-delivery-service">
                <?php if (isset($option->subDeliveryOptions)): ?>
                    <strong class="gls-sub-delivery-options-title"><?= $option->title; ?></strong>
                <?php else: ?>
                    <input type="radio" name="gls_delivery_option" class="radio" value="<?= $option->expectedDeliveryDate; ?>" id="default"/>
                    <label for="default"><?= $option->title; ?></label>
                <?php endif; ?>
                <?php if (isset($option->subDeliveryOptions)): ?>
                    <?php foreach ($option->subDeliveryOptions as $subOption): ?>
                        <div class="gls-woocommerce-sub-delivery-options">
                            <input type="radio" name="gls_delivery_option" class="radio" value="<?= $subOption->service; ?>" id="<?= $subOption->service; ?>"/>
                            <label for=<?= $subOption->service; ?>><?= $subOption->title; ?></label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
