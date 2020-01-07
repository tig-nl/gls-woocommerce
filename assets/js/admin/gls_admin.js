/* global gls_admin */
(function ($, gls_admin) {
    $(function () {
        if ('undefined' === typeof gls_admin) {
            return;
        }

        var is_blocked = function ($node) {
            return $node.is('.processing') || $node.parents('.processing').length;
        };

        var block = function ($node) {
            if (!is_blocked($node)) {
                $node.addClass('processing').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
            }
        };

        // Toggle gateway on/off.
        $('.gls_options').on('click', '.gls-delivery-option-method-toggle-enabled', function () {
            var $link = $(this), $row = $link.closest('tr'), $toggle = $link.find('.woocommerce-input-toggle');

            var data = {
                action: 'woocommerce_toggle_option_enabled',
                security: gls_admin.nonces.option_toggle,
                option_id: $row.data('option_id')
            };

            $toggle.addClass('woocommerce-input-toggle--loading');

            $.ajax({
                url: gls_admin.ajax_url,
                data: data,
                dataType: 'json',
                type: 'POST',
                success: function (response) {
                    if (true === response.data) {
                        $toggle.removeClass('woocommerce-input-toggle--enabled, woocommerce-input-toggle--disabled');
                        $toggle.addClass('woocommerce-input-toggle--enabled');
                        $toggle.removeClass('woocommerce-input-toggle--loading');
                    } else if (false === response.data) {
                        $toggle.removeClass('woocommerce-input-toggle--enabled, woocommerce-input-toggle--disabled');
                        $toggle.addClass('woocommerce-input-toggle--disabled');
                        $toggle.removeClass('woocommerce-input-toggle--loading');
                    } else if ('needs_setup' === response.data) {
                        window.location.href = $link.attr('href');
                    }
                }
            });

            return false;
        });

        // Trigger create label call.
        $('.create_label').on('click', function () {
            var data = {
                action: 'woocommerce_create_label',
                security: gls_admin.nonces.create_label,
                order_id: new URL(window.location.href).searchParams.get('post'),
                label_amount: $('#gls-label-amount').val()
            };

            $.ajax({
                url: gls_admin.ajax_url,
                data: data,
                dataType: 'json',
                type: 'POST',
                beforeSend: function () {
                    block($('#gls-order-label'));
                },
                complete: function () {
                    location.reload();
                },
                success: function () {
                    window.open(gls_admin.admin_url + '&post=' + data.order_id, '_blank');
                }
            });

            return false;
        });

        // Trigger delete label call
        $('#delete-action a').on('click', function () {
            var data = {
                action: 'woocommerce_delete_label',
                security: gls_admin.nonces.delete_label,
                order_id: new URL(window.location.href).searchParams.get('post')
            };

            $.ajax({
                url: gls_admin.ajax_url,
                data: data,
                dataType: 'json',
                type: 'POST',
                beforeSend: function () {
                    block($('#gls-order-label'));
                },
                complete: function () {
                    location.reload();
                }
            });

            return false;
        })
    });
})(jQuery, gls_admin);
