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

defined('ABSPATH') || exit;

class GLS_AJAX extends WC_AJAX
{
    /**
     * Hook in ajax handlers.
     */
    public static function init()
    {
        // @formatter:off
        add_action('init', array(__CLASS__, 'define_ajax'), 0);
        add_action('template_redirect', array(__CLASS__, 'do_wc_ajax'), 0);

        // @formatter:on
        self::add_ajax_events();
    }

    /**
     * Hook in methods - uses WordPress ajax handlers (admin-ajax).
     */
    public static function add_ajax_events()
    {
        $ajax_events_nopriv = array(
            'update_delivery_options',
            'update_parcel_shops',
            'delivery_option_selected'
        );

        foreach ($ajax_events_nopriv as $ajax_event) {
            // @formatter:off
            add_action('wp_ajax_woocommerce_' . $ajax_event, array(__CLASS__, $ajax_event));
            add_action('wp_ajax_nopriv_woocommerce_' . $ajax_event, array(__CLASS__, $ajax_event));
            add_action('wc_ajax_' . $ajax_event, array(__CLASS__, $ajax_event));
        }

        $ajax_events = array(
            'toggle_option_enabled',
            'create_label',
            'delete_label'
        );

        foreach ($ajax_events as $ajax_event) {
            add_action('wp_ajax_woocommerce_' . $ajax_event, array(__CLASS__, $ajax_event));
            // @formatter:on
        }
    }

    /**
     * @throws Exception
     */
    public static function update_delivery_options()
    {
        check_ajax_referer('update-delivery-options', 'security');
        /** @var StdClass $response */
        $response = GLS()->api_delivery_options()->call();

        if ($response->error || isset($response->statusCode) && $response->statusCode !== 200) {
            $code = $response->statusCode ?: 412;
            wp_send_json_error($response->message, $code);
        }

        $available_delivery_options = $response->deliveryOptions;
        $enabled_delivery_options   = GLS()->delivery_options()->enabled_delivery_options();
        $delivery_options           = GLS()->delivery_options()->delivery_options($available_delivery_options, $enabled_delivery_options);

        wp_send_json_success($delivery_options, $response->status);
    }

    /**
     * @throws Exception
     */
    public static function update_parcel_shops()
    {
        check_ajax_referer('update-parcel-shops', 'security');

        if (GLS()->post('country') !== 'NL') {
            wp_send_json_success([], 200);
        }

        /** @var StdClass $response */
        $response = GLS()->api_pickup_locations()->call();

        if ($response->error || isset($response->statusCode) && $response->statusCode !== 200) {
            $code = $response->statusCode ?: 412;
            wp_send_json_error($response->message, $code);
        }

        // These elements only exist if some required settings aren't set.
        if (isset($response->username) || isset($response->amountOfShops) || isset($response->passwordLength)) {
            foreach ($response as $item => $message) {
                wp_send_json_error(__('The GLS plugin is not configured properly' . ': ' . reset($message)), 401);
            }
        }

        $available_parcel_shops = $response->parcelShops;
        $parcel_shops           = GLS()->delivery_options()->parcel_shops($available_parcel_shops);

        wp_send_json_success($parcel_shops, $response->status);
    }

    /**
     * Adds fee of selected delivery option to order review block.
     */
    public static function delivery_option_selected()
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        $details = GLS()->post('details');
        $title   = isset($details['title']) ? strtolower($details['title']) : '';

        if (strpos($title, ' | ')) {
            $_POST['details']['title'] = explode(' | ', $title)[0];
        }

        parse_str(GLS()->post('delivery_address', false), $delivery_address);

        $type                      = isset($delivery_address['ship_to_different_address']) ? 'shipping_' : 'billing_';
        $_POST['delivery_address'] = self::map_delivery_address($delivery_address, $type);

        $session = WC()->session;
        $session->set('gls_service', $_POST);

        wp_die();
    }

    /**
     * Map delivery address in the format required by GLS, so we can always deliver it in the right format.
     *
     * @param        $delivery_address
     * @param string $type
     *
     * @return array
     */
    private static function map_delivery_address($delivery_address, $type = 'billing_')
    {
        $first_name = $type . 'first_name';
        $last_name  = $type . 'last_name';
        $street     = $type . 'address_1';
        $houseNo    = $type . 'address_2';
        $country    = $type . 'country';
        $zipcode    = $type . 'postcode';
        $city       = $type . 'city';
        $company    = $type . 'company';

        return [
            'name1'         => $delivery_address[$first_name] . ' ' . $delivery_address[$last_name],
            'street'        => $delivery_address[$street],
            'houseNo'       => substr($delivery_address[$houseNo], 0, 10),
            'name2'         => $delivery_address[$houseNo],
            'countryCode'   => $delivery_address[$country],
            'zipCode'       => $delivery_address[$zipcode],
            'city'          => $delivery_address[$city],
            // Email and Phone are always retrieved from billing, since they don't exist in shipping.
            'email'         => $delivery_address['billing_email'],
            'phone'         => $delivery_address['billing_phone'] ?: '+00000000000',
            'addresseeType' => empty($delivery_address[$company]) ? 'p' : 'b'
        ];
    }

    /**
     * Toggle delivery option on or off via AJAX.
     *
     * @since 1.0.0
     */
    public static function toggle_option_enabled()
    {
        if (current_user_can('manage_woocommerce') && check_ajax_referer('gls-toggle-delivery-option-enabled', 'security') && isset($_POST['option_id'])) {
            $delivery_options = GLS()->delivery_options->available_delivery_options();
            $option_id        = GLS()->post('option_id');

            /** @var stdClass $option */
            foreach ($delivery_options as $option) {
                if (!in_array(
                    $option_id, array(
                    $option->id,
                    sanitize_title(get_class($option))
                ), true
                )) {
                    continue;
                }

                $enabled = $option->get_option('enabled', 'no');

                if (!wc_string_to_bool($enabled)) {
                    if ($option->needs_setup()) {
                        wp_send_json_error('needs_setup');
                        wp_die();
                    } else {
                        $option->update_option('enabled', 'yes');
                    }
                } else {
                    // Disable the option.
                    $option->update_option('enabled', 'no');
                }

                wp_send_json_success(!wc_string_to_bool($enabled));
                wp_die();
            }
        }

        wp_send_json_error('invalid_option_id');
        wp_die();
    }

    /**
     * Trigger CreateLabel-call and save it as post meta data.
     */
    public static function create_label()
    {
        check_ajax_referer('create-label', 'security');

        $order = wc_get_order(GLS()->post('order_id'));
        /** @var StdClass $response */
        $response = GLS()->api_create_label($_POST['order_id'])->call();

        if ($response->error || isset($response->statusCode) && $response->statusCode !== 200) {
            GLS_Admin_Notice::admin_add_notice($response->message,'error','shop_order');
            wp_send_json_error($response->message, $response->status);
        }

        // These elements only exist if some required settings aren't set.
        if (isset($response->username) || isset($response->amountOfShops) || isset($response->passwordLength)) {
            foreach ($response as $item => $message) {
                GLS_Admin_Notice::admin_add_notice(__('The GLS plugin is not configured properly') . ': ' . reset($message),'error','shop_order');
                wp_send_json_error(__('The GLS plugin is not configured properly') . ': ' . reset($message), 401);
            }
        }

        $order->update_meta_data('_gls_label', $response);
        $order->save();

        GLS_Admin_Notice::admin_add_notice('Label created successfully','success','shop_order');
        wp_send_json_success('Label created successfully', $response->status);
    }

    /**
     * Trigger DeleteLabel-call and remove it from post meta data if successful.
     */
    public static function delete_label()
    {
        check_ajax_referer('delete-label', 'security');

        /** @var StdClass $response */
        $response = GLS()->api_delete_label()->call();

        if ($response->error || isset($response->statusCode) && $response->statusCode !== 200) {
            GLS_Admin_Notice::admin_add_notice('Label could not be deleted from the GLS API','error','shop_order');
            wp_send_json_error('Label could not be deleted from the GLS API', $response->status);
        }

        $order = wc_get_order(GLS()->post('order_id'));
        $order->delete_meta_data('_gls_label');
        $order->save();

        GLS_Admin_Notice::admin_add_notice('Label deleted successfully','success','shop_order');
        wp_send_json_success('Label deleted successfully', $response->status);
    }

}

GLS_AJAX::init();
