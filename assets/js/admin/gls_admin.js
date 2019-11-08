/* global woocommerce_admin */
(function (
    $,
    gls_admin
) {
    $(function () {
        if ('undefined' ===
            typeof gls_admin) {
            return;
        }

        // Toggle gateway on/off.
        $('.gls_options')
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
                    action: 'woocommerce_toggle_option_enabled',
                    security: gls_admin.nonces.option_toggle,
                    option_id: $row.data(
                        'option_id')
                };

                $toggle.addClass(
                    'woocommerce-input-toggle--loading');

                $.ajax(
                    {
                        url: gls_admin.ajax_url,
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
    gls_admin
);
