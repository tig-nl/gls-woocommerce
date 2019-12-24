/* global gls_checkout_params */
jQuery(
    function ($) {
        // gls_checkout_params is required to continue, ensure the object exists
        if (typeof gls_checkout_params === 'undefined') {
            return false;
        }

        var gls_delivery_options_form = {
            selected_delivery_option: false,
            xhr: false,
            $order_review: $('#order_review'),
            $checkout_form: $('form.checkout'),
            $delivery_options_container: $('.gls-delivery-options'),
            $delivery_option: $('.gls-delivery-option'),
            $parcel_shops_container: $('.gls-parcel-shops'),
            $parcel_shop: $('.gls-parcel-shop'),
            $error_container: $('.gls-error'),

            init: function () {
                $(document.body).bind('update_delivery_options', this.update_delivery_options);
                this.$checkout_form.on('click', 'input[name="gls_delivery_option"]', this.delivery_option_selected);

                // Manual trigger
                this.$checkout_form.on('update', this.trigger_update_delivery_options);

                // Trigger on load
                this.trigger_update_delivery_options();

                // Inputs/selects which update delivery options
                this.$checkout_form.on('change', '.address-field input.input-text, .address-field select.country_select', this.trigger_update_delivery_options);
            },

            delivery_option_selected: function (e) {
                e.stopPropagation();

                var selectedDeliveryOption = $('.woocommerce-checkout input[name="gls_delivery_option"]:checked'),
                    shippingAddress        = $('#ship-to-different-address-checkbox:checked').length > 0
                        ? $('.woocommerce-shipping-fields input, .woocommerce-shipping-fields select, #billing_phone_field input, #billing_email_field input')
                        : $('.woocommerce-billing-fields input, .woocommerce-billing-fields select');

                if (selectedDeliveryOption !==
                    gls_delivery_options_form.selected_delivery_option) {
                    $(document.body).trigger('delivery_option_selected');
                }

                gls_delivery_options_form.selected_delivery_option = selectedDeliveryOption;

                gls_delivery_options_form.xhr = $.ajax(
                    {
                        type: 'POST',
                        url: gls_checkout_params.wc_ajax_url.toString().replace(
                            '%%endpoint%%', 'delivery_option_selected'),
                        data: {
                            type: selectedDeliveryOption.data('service'),
                            details: {
                                service: selectedDeliveryOption.val(),
                                title: selectedDeliveryOption.data('title'),
                                fee: selectedDeliveryOption.data('fee')
                            },
                            delivery_address: shippingAddress.serialize()
                        },
                        beforeSend: function() {
                            $('[id*=tig_gls]').prop('checked', true);
                        },
                        success: function() {
                            gls_delivery_options_form.$error_container.hide();
                            $(document.body).trigger( 'update_checkout');
                        }
                    }
                );
            },

            trigger_update_delivery_options: function () {
                gls_delivery_options_form.reset_update_checkout_timer();
                $(document.body).trigger('update_delivery_options');
            },

            reset_update_checkout_timer: function () {
                clearTimeout(gls_delivery_options_form.updateTimer);
            },

            update_delivery_options: function (event, args) {
                // Small timeout to prevent multiple requests when several fields update at the same time
                gls_delivery_options_form.reset_update_checkout_timer();
                gls_delivery_options_form.updateTimer = setTimeout(gls_delivery_options_form.update_delivery_options_action, '5', args);
            },

            update_delivery_options_action: function () {
                if (gls_delivery_options_form.xhr) {
                    gls_delivery_options_form.xhr.abort();
                }

                if (gls_delivery_options_form.$checkout_form.length === 0) {
                    return;
                }

                var country = $('#billing_country').val(),
                    postcode = $(':input#billing_postcode').val();

                if ($('#ship-to-different-address').find('input').is(':checked')) {
                    country = $('#shipping_country').val();
                    postcode = $(':input#shipping_postcode').val();
                }

                var data = {
                    security: gls_checkout_params.update_delivery_options_nonce,
                    postcode: postcode,
                    country: country
                };

                gls_delivery_options_form.xhr = $.ajax(
                    {
                        type: 'POST',
                        url: gls_checkout_params.wc_ajax_url.toString().replace(
                            '%%endpoint%%', 'update_delivery_options'),
                        data: data,
                        beforeSend: function() {
                            // Remove any options that we're retrieved in a previous call.
                            currentOptions = gls_delivery_options_form.$delivery_options_container.children();

                            if (currentOptions.length > 1) {
                                var i;
                                for (i = 1; i < currentOptions.length; i++) {
                                    currentOptions[i].remove();
                                }
                            }
                        },
                        success: function (options) {
                            gls_delivery_options_form.$error_container.hide();
                            options.data.forEach(gls_delivery_options_form.display_delivery_option);
                        },
                        error: function (message) {
                            gls_delivery_options_form.$error_container.html(message.responseJSON.data).show();
                        }
                    }
                );
            },

            display_delivery_option: function(option) {
                gls_delivery_options_form.map_delivery_option_attributes(option);
            },

            map_delivery_option_attributes: function(option) {
                template = this.$delivery_option.clone(true);

                option_input = template.children('.gls-delivery-option > input');
                option_title = template.children('.gls-delivery-option > label');
                option_fee   = template.children('.gls-delivery-option > .delivery-fee');
                service_code = option.service !== 'undefined' ? option.service : 'default';

                option_input.val(service_code);
                option_input.attr('id', service_code);
                option_title.attr('for', service_code);
                option_title.text(option.title);
                option_input.attr('data-fee', option.fee);
                option_input.attr('data-title', option.title);
                option_input.attr('data-service', option.service !== undefined ? option.service  : 'DeliveryService');
                option_fee.html(option.formatted_fee);

                if (option.subDeliveryOptions !== undefined) {
                    sub_option_title = template.children('.gls-delivery-option > strong');
                    sub_option_title.text(option.title).show();
                    option_fee.remove();
                    option_input.remove();
                    option_title.remove();

                    var i = 0;
                    option.subDeliveryOptions.forEach(function(sub_option) {
                        gls_delivery_options_form.map_sub_delivery_attributes(sub_option, option.service, template, template.find('.gls-sub-delivery-options > .gls-sub-delivery-option')[i]);
                        i++;
                    });
                }

                template.appendTo(this.$delivery_options_container).show();
            },

            map_sub_delivery_attributes: function(option, service, parent_template, template) {
                container    = jQuery(parent_template).find('.gls-sub-delivery-options');
                sub_template = jQuery(template).clone(true);

                option_input = sub_template.children('.gls-sub-delivery-option > input');
                option_title = sub_template.children('.gls-sub-delivery-option > label');
                option_fee   = sub_template.children('.gls-sub-delivery-option .delivery-fee');

                jQuery(option_input).val(option.service);
                jQuery(option_input).attr('id', option.service);
                jQuery(option_title).attr('for', option.service);
                jQuery(option_title).text(option.title);
                jQuery(option_input).attr('data-fee', option.fee);
                jQuery(option_input).attr('data-title', option.title);
                jQuery(option_input).attr('data-service', service);
                jQuery(option_fee).html(option.formatted_fee);

                sub_template.appendTo(container).show();
            }
        };

        gls_delivery_options_form.init();
    }
);