/* global gls_checkout_params */
jQuery(
    function ($) {
        // gls_checkout_params is required to continue, ensure the object exists
        if (typeof gls_checkout_params === 'undefined') {
            return false;
        }

        var gls_delivery_options_form = {
            selected_delivery_option: false,
            delivery_options_xhr: false,
            parcel_shops_xhr: false,
            $order_review: $('#order_review'),
            $checkout_form: $('form.checkout'),
            $delivery_options_container: $('.gls-delivery-options'),
            $delivery_option: $('.gls-delivery-option'),
            $parcel_shops_container: $('.gls-parcel-shops'),
            $parcel_shop: $('.gls-parcel-shop'),
            $error_container: $('.gls-error'),

            /**
             * Initializes all events where methods should be triggered.
             */
            init: function () {
                $(document.body).bind('update_delivery_options', this.update_delivery_options);

                // Selected delivery option is saved to the session immediately.
                this.$checkout_form.on('click', 'input[name="gls_delivery_option"]', this.delivery_option_selected);

                // Manual trigger
                this.$checkout_form.on('update', this.trigger_update_delivery_options);

                // Trigger on load
                this.trigger_update_delivery_options();

                // Inputs/selects which update delivery options
                this.$checkout_form.on('change', '.address-field input.input-text, .address-field select.country_select', this.trigger_update_delivery_options);

                // Selected delivery option is saved to the session immediately.
                this.$checkout_form.on('click', '.gls-tab-delivery', this.show_delivery);
                this.$checkout_form.on('click', '.gls-tab-pickup', this.show_pickup);

            },

            /**
             * The main event.
             *
             * Triggers both calls to update delivery options and parcel shops.
             *
             * @param event
             * @param args
             */
            update_delivery_options: function (event, args) {
                gls_delivery_options_form.reset_update_checkout_timer();
                gls_delivery_options_form.updateTimer = setTimeout(gls_delivery_options_form.update_delivery_options_action, '5', args);
                gls_delivery_options_form.updateTimer = setTimeout(gls_delivery_options_form.update_parcel_shops_action, '5', args);
            },

            /**
             * Small timeout to prevent multiple requests when several fields update at the same time
             */
            reset_update_checkout_timer: function () {
                clearTimeout(gls_delivery_options_form.updateTimer);
            },

            /**
             * @param e
             */
            delivery_option_selected: function (e) {
                e.stopPropagation();

                var selectedDeliveryOption = $('.woocommerce-checkout input[name="gls_delivery_option"]:checked'),
                    shippingAddress        = $('#ship-to-different-address-checkbox:checked').length > 0
                        ? $('.woocommerce-shipping-fields input, .woocommerce-shipping-fields select, #billing_phone_field input, #billing_email_field input')
                        : $('.woocommerce-billing-fields input, .woocommerce-billing-fields select');

                if (selectedDeliveryOption !== gls_delivery_options_form.selected_delivery_option) {
                    $(document.body).trigger('delivery_option_selected');
                }

                gls_delivery_options_form.selected_delivery_option = selectedDeliveryOption;

                gls_delivery_options_form.delivery_options_xhr = $.ajax(
                    {
                        type: 'POST',
                        url: gls_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'delivery_option_selected'),
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
                            $(document.body).trigger('update_checkout');
                        }
                    }
                );

                gls_delivery_options_form.set_background_color(selectedDeliveryOption);
            },

            /**
             * Set background color on the current active radio button
             * TODO: make this work for sub-delivert-option items.
             */

            set_background_color: function(selectedDeliveryOption) {

                console.log(selectedDeliveryOption);
                var notSelectedDeliveryOption = $('.woocommerce-checkout input[name="gls_delivery_option"]:not(:checked)');

                $(selectedDeliveryOption).parent('.container').addClass('gls-highlight');
                $(notSelectedDeliveryOption).parent('.container').removeClass('gls-highlight');
            },

            /**
             * Show delivery options and hide pickup locations by default - Switch
             */

            show_delivery: function(){
                $('.gls-tab-pickup').removeClass('active');
                $('.gls-tab-delivery').addClass('active');

                $('.gls-parcel-shops').fadeOut('fast');
                $('.gls-delivery-options').fadeIn('slow');
            },

            show_pickup: function () {
                $('.gls-tab-delivery').removeClass('active');
                $('.gls-tab-pickup').addClass('active');

                $('.gls-delivery-options').fadeOut('fast');
                $('.gls-parcel-shops').fadeIn('slow');
            },

            /**
             *
             */
            trigger_update_delivery_options: function () {
                gls_delivery_options_form.reset_update_checkout_timer();
                $(document.body).trigger('update_delivery_options');
            },

            /**
             *
             */
            update_delivery_options_action: function () {
                if (gls_delivery_options_form.delivery_options_xhr) {
                    gls_delivery_options_form.delivery_options_xhr.abort();
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

                gls_delivery_options_form.delivery_options_xhr = $.ajax({
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

                        if (options.data.length > 0) {
                            options.data.forEach(gls_delivery_options_form.display_delivery_option);
                        }
                    },
                    error: function (message) {
                        gls_delivery_options_form.$error_container.html(message.responseJSON.data).show();
                    }
                });
            },

            /**
             *
             */
            update_parcel_shops_action: function () {
                if (gls_delivery_options_form.parcel_shops_xhr) {
                    gls_delivery_options_form.parcel_shops_xhr.abort();
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
                    security: gls_checkout_params.update_parcel_shops_nonce,
                    postcode: postcode,
                    country: country
                };

                gls_delivery_options_form.parcel_shops_xhr = $.ajax({
                    type: 'POST',
                    url: gls_checkout_params.wc_ajax_url.toString().replace(
                        '%%endpoint%%', 'update_parcel_shops'),
                    data: data,
                    beforeSend: function() {
                        // Remove any options that we're retrieved in a previous call.
                        currentOptions = gls_delivery_options_form.$parcel_shops_container.children();

                        if (currentOptions.length > 1) {
                            var i;
                            for (i = 1; i < currentOptions.length; i++) {
                                currentOptions[i].remove();
                            }
                        }
                    },
                    success: function (options) {
                        gls_delivery_options_form.$error_container.hide();

                        if (options.data.length > 0) {
                            options.data.forEach(gls_delivery_options_form.display_parcel_shop);
                        }
                    },
                    error: function (message) {
                        gls_delivery_options_form.$error_container.html(message.responseJSON.data).show();
                    }
                });
            },

            /**
             * @param option
             */
            display_delivery_option: function(option) {
                gls_delivery_options_form.map_delivery_option_attributes(option);
            },

            /**
             * @param option
             */
            map_delivery_option_attributes: function(option) {
                template = this.$delivery_option.clone(true);

                option_input = template.children('.gls-delivery-option > input');
                option_title = template.children('.gls-delivery-option > label');
                option_fee   = template.children('.gls-delivery-option > .delivery-fee');
                service_code = typeof option.service !== 'undefined' ? option.service : 'default_delivery_option';

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

            /**
             * @param option
             * @param service
             * @param parent_template
             * @param template
             */
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
            },

            /**
             * @param option
             */
            display_parcel_shop: function(option) {
                gls_delivery_options_form.map_parcel_shop_attributes(option);
            },

            /**
             * @param option
             */
            map_parcel_shop_attributes: function (option) {
                template = this.$parcel_shop.clone(true);

                option_input = template.children('.gls-parcel-shop > input');
                option_title = template.children('.gls-parcel-shop > label');
                option_fee   = template.children('.gls-parcel-shop > .delivery-fee');

                parcel_address = template.children('.gls-parcel-shop .address-information');
                parcel_address_street = parcel_address.children('span.street');
                parcel_address_city = parcel_address.children('span.city');
                parcel_address_distance = parcel_address.children('span.distance-meters');
                parcel_shop_id = option.parcelShopId !== 'undefined' ? option.parcelShopId : 'default';

                option_input.val(parcel_shop_id);
                option_input.attr('id', parcel_shop_id);
                option_title.attr('for', parcel_shop_id);
                option_title.text(option.name);
                option_input.attr('data-fee', option.fee);
                option_input.attr('data-title', option.name);
                option_input.attr('data-service', 'ParcelShop');
                option_fee.html(option.formatted_fee);

                parcel_address_street.html(option.street + ' ' + option.houseNo );
                parcel_address_city.html(option.zipcode + ' ' + option.city);
                parcel_address_distance.html(option.distanceMeters + 'm');

                var i = 0;
                option.businessHours.forEach(function(business_hours) {
                    gls_delivery_options_form.map_parcel_shop_business_hours(business_hours, template, template.find('.parcel-business-hours > .row')[i]);
                    i++;
                });

                template.appendTo(this.$parcel_shops_container).show();
            },

            /**
             *
             * @param business_hours
             * @param parent_template
             * @param template
             */
            map_parcel_shop_business_hours: function(business_hours, parent_template, template) {
                container    = jQuery(parent_template).find('.parcel-business-hours');
                sub_template = jQuery(template).clone(true);

                option_day = sub_template.children('.day-of-the-week');
                option_hours = sub_template.children('.opening-hours');

                jQuery(option_day).text(business_hours.dayOfWeek);
                jQuery(option_hours).text(business_hours.openTime + ' - ' + business_hours.closedTime);

                sub_template.appendTo(container).show();
            }
        };

        gls_delivery_options_form.init();
    }
);