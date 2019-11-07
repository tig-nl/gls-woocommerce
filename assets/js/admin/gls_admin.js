/* global woocommerce_admin */
(function (
    $,
    woocommerce_admin
) {
    $(function () {
        if ('undefined' ===
            typeof woocommerce_admin) {
            return;
        }

        // Toggle gateway on/off.
        $('.wc_gateways')
        .on('click',
            '.gls-delivery-option-method-toggle-enabled',
            function () {
                var $link = $(
                    this),
                    $row = $link.closest(
                        'tr'),
                    $toggle = $link.find(
                        '.woocommerce-input-toggle');

                var data = {
                    action: 'gls_toggle_option_enabled',
                    security: woocommerce_admin.nonces.option_toggle,
                    gateway_id: $row.data(
                        'option_id')
                };

                $toggle.addClass(
                    'woocommerce-input-toggle--loading');

                $.ajax(
                    {
                        url: woocommerce_admin.ajax_url,
                        data: data,
                        dataType: 'json',
                        type: 'POST',
                        success: function (response) {
                            if (true ===
                                response.data) {
                                $toggle.removeClass(
                                    'woocommerce-input-toggle--enabled, woocommerce-input-toggle--disabled');
                                $toggle.addClass(
                                    'woocommerce-input-toggle--enabled');
                                $toggle.removeClass(
                                    'woocommerce-input-toggle--loading');
                            } else if (false ===
                                response.data) {
                                $toggle.removeClass(
                                    'woocommerce-input-toggle--enabled, woocommerce-input-toggle--disabled');
                                $toggle.addClass(
                                    'woocommerce-input-toggle--disabled');
                                $toggle.removeClass(
                                    'woocommerce-input-toggle--loading');
                            } else if ('needs_setup' ===
                                response.data) {
                                window.location.href = $link.attr(
                                    'href');
                            }
                        }
                    });

                return false;
            }
        );
    });
})(
    jQuery,
    woocommerce_admin
);
