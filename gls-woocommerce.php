<?php

/*
Plugin Name: TIG GLS for Woocommerce
Plugin URI:http://woocommerce/
Description: Plugin from to TIG to add GLS shipping to Woocommerce
Version: 1.0
Author: TIG
Author URI:https://tig.nl/
 */


add_action('woocommerce_checkout_before_order_review_heading', 'tig_gls_delivery_options', 10, 1);

function tig_gls_delivery_options ()
{
        _e( "Shipping options ", "");
    ?>
        <br>
        <input type="text" name="add_delivery_date" class="add_delivery_date" placeholder="Delivery Date">
    <?php
}
