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

if (class_exists('GLS_Settings_Delivery_Options', false)) {
    return new GLS_Settings_Delivery_Options();
}

/**
 * Class GLS_Settings_Delivery_Options
 */
class GLS_Settings_Delivery_Options extends WC_Settings_Page
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id    = 'tig_gls';
        $this->label = _x('GLS', 'Settings tab label', 'gls-woocommerce');

        add_action(
            'woocommerce_admin_field_delivery_options', array(
                $this,
                'delivery_options_settings'
            )
        );

        parent::__construct();
    }

    /**
     * Get sections.
     *
     * @return array
     */
    public function get_sections()
    {
        $sections = array(
            '' => __('GLS Configuration', 'gls-woocommerce'),
        );

        return apply_filters('tig_gls_get_sections_' . $this->id, $sections);
    }

    /**
     * Get settings array.
     *
     * @param string $current_section Section being shown.
     *
     * @return array
     */
    public function get_settings($current_section = '')
    {
        $settings = array();

        if ('' === $current_section) {
            $settings = apply_filters(
                'tig_gls_delivery_options_settings',
                array(
                    array(
                        'title' => __('GLS Configuration', 'gls-woocommerce'),
                        'desc'  => __('Add your API credentials to connect WooCommerce to the GLS API. Available delivery options are listed below and can be enabled/disabled to control their visibility on the frontend.', 'gls-woocommerce'),
                        'type'  => 'title',
                        'id'    => 'delivery_options_options',
                    ),
                    array(
                        'type' => 'delivery_options',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id'   => 'delivery_options_options',
                    ),
                )
            );
        }

        return apply_filters('tig_gls_get_settings_' . $this->id, $settings, $current_section);
    }

    /**
     * Output the settings.
     */
    public function output()
    {
        global $current_section;

        // Load gateways so we can show any global options they may have.
        $delivery_options = GLS()->delivery_options->delivery_options();

        if ($current_section) {
            foreach ($delivery_options as $option) {
                if (in_array(
                    $current_section, array(
                    $option->id,
                    sanitize_title(get_class($option))
                ), true
                )) {
                    if (isset($_GET['toggle_enabled'])) { // WPCS: input var ok, CSRF ok.
                        $enabled = $option->get_option('enabled');

                        if ($enabled) {
                            $option->settings['enabled'] = wc_string_to_bool($enabled) ? 'no' : 'yes';
                        }
                    }
                    $option->admin_options();
                    break;
                }
            }
        }
        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::output_fields($settings);
    }

    /**
     * Output delivery options settings.
     */
    public function delivery_options_settings()
    {
        ?>
        <tr valign="top">
            <td class="gls_delivery_options_wrapper" colspan="2">
                <table class="gls_options widefat" cellspacing="0" aria-describedby="delivery_options_options-description">
                    <thead>
                    <tr>
                        <?php
                        $default_columns = array(
                            'name'           => __('Delivery Option', 'gls-woocommerce'),
                            'description'    => __('Description', 'gls-woocommerce'),
                            'additional_fee' => __('Additional Fee', 'gls-woocommerce'),
                            'status'         => __('Enabled', 'gls-woocommerce')
                        );

                        $columns = apply_filters('tig_gls_delivery_options_setting_columns', $default_columns);

                        foreach ($columns as $key => $column) {
                            echo '<th class="' . esc_attr($key) . '">' . esc_html($column) . '</th>';
                        }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach (GLS()->delivery_options->delivery_options() as $option) {

                        echo '<tr data-option_id="' . esc_attr($option->id) . '">';

                        foreach ($columns as $key => $column) {
                            if (!array_key_exists($key, $default_columns)) {
                                do_action('tig_gls_delivery_options_setting_column_' . $key, $option);
                                continue;
                            }

                            $width = '';

                            if (in_array($key, array('status'), true)) {
                                $width = '1%';
                            }

                            $method_title   = $option->get_method_title() ?: $option->get_title();
                            $additional_fee = $option->get_additional_fee();

                            echo '<td class="' . esc_attr($key) . '" width="' . esc_attr($width) . '">';

                            switch ($key) {
                                case 'name':
                                    echo wp_kses_post($method_title);
                                    break;
                                case 'description':
                                    echo wp_kses_post($option->get_method_description());
                                    break;
                                case 'additional_fee':
                                    echo "<input type='number' min='0' value='$additional_fee' name='additional_fee[$option->id]' />";
                                    break;
                                case 'status':
                                    echo '<a class="gls-delivery-option-method-toggle-enabled" href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=tig_gls&section=' . strtolower($option->id))) . '">';
                                    if (wc_string_to_bool($option->enabled)) {
                                        /* Translators: %s Payment gateway name. */
                                        echo '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled" aria-label="' . esc_attr(sprintf(__('The "%s" delivery option is currently enabled', 'gls-woocommerce'), $method_title)) . '">' . esc_attr__('Yes', 'woocommerce') . '</span>';
                                    } else {
                                        /* Translators: %s Payment gateway name. */
                                        echo '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled" aria-label="' . esc_attr(sprintf(__('The "%s" delivery option is currently disabled', 'gls-woocommerce'), $method_title)) . '">' . esc_attr__('No', 'woocommerce') . '</span>';
                                    }
                                    echo '</a>';
                                    break;
                            }
                            echo '</td>';
                        }
                        echo '</tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php
    }

    /**
     * Save settings.
     */
    public function save()
    {
        // TODO: Fix bug where settings aren't updated after saving changes. They are updated in the database, however...
        if (current_user_can('manage_woocommerce') && isset($_POST['additional_fee'])) {
            $delivery_options = GLS()->delivery_options->delivery_options();
            $additional_fee = wc_clean(wp_unslash($_POST['additional_fee']));

            foreach ($delivery_options as $option) {
                if (!array_search($additional_fee[$option->id], $additional_fee, true)) {
                    continue;
                }

                $option->update_option('additional_fee', $additional_fee[$option->id]);
            }
        }

        parent::save();
    }
}

return new GLS_Settings_Delivery_Options();
