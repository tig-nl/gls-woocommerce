/* global gls_admin */
(function ($, gls_admin) {
    $(function () {
        if ('undefined' === typeof gls_admin) {
            return;
        }

        // Toggle free shipping fields (via ajax request)
        $(document).on("change", '#tig_glstig_gls_freeshipping_enabled', function() {
            toggleFreeShippingFields($(this).val());
        });

        $(document).on("click", '.wc-shipping-zone-method-settings', function() {
            toggleFreeShippingFields($('#tig_glstig_gls_freeshipping_enabled').val());
        });

        function toggleFreeShippingFields(value) {
            $("#tig_glstig_gls_freeshipping").parent().parent().parent().css('display','none');
            $("#tig_glstig_gls_freeshipping_extra").parent().parent().parent().parent().css('display','none');

            if (value == 1 ) {
                $("#tig_glstig_gls_freeshipping").parent().parent().parent().css('display','table-row');
                $("#tig_glstig_gls_freeshipping_extra").parent().parent().parent().parent().css('display','table-row');
            }
        }

        if ($("#tig_glstig_gls_freeshipping_enabled").length) {
            toggleFreeShippingFields($("#tig_glstig_gls_freeshipping_enabled").val());
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
        $('.create_label').on('click', function (event) {
            if (typeof this.href !== 'undefined') {
                var order_id = this.href.match(/^\d+|\d+\b|\d+(?=\w)/g)[0];
            }
            var data = {
                action: 'woocommerce_create_label',
                security: gls_admin.nonces.create_label,
                order_id: new URL(window.location.href).searchParams.get('post') ?? order_id,
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
