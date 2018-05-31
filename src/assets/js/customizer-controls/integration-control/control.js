(function ($) {
    wp.customize.controlConstructor["mailoptin-integration"] = wp.customize.Control.extend({
        ready: function () {
            "use strict";
            $(document).on('click', '.mo-integration-widget-action', this.toggleWidget);

            $(window).on('load', this.contextual_display);

        },

        toggleWidget: function (e) {
            e.preventDefault();
            var parent = $(this).parents('.mo-integration-widget');
            $('.mo-integration-widget-content', parent).slideToggle(function () {
                parent.toggleClass('mo-integration-widget-expanded');
            });
        },

        contextual_display: function () {

            $('.mo-integration-widget').each(function () {
                toggle_connect_service_connected_fields($(this));
                fetch_email_list($(this));
            });

            toggle_connect_service_email_list_field();

            function fetch_email_list(parent) {

                function add_spinner(placement) {
                    var spinner_html = $('<img class="mo-spinner fetch-email-list" src="' + mailoptin_globals.admin_url + 'images/spinner.gif">');
                    $(placement, parent).after(spinner_html);
                }


                function remove_spinner() {
                    $('.mo-spinner.fetch-email-list', parent).remove();
                }

                $("select[name='connection_service']", parent).change(function () {

                    var connect_service = $(this).val();

                    // hide email list select dropdown field before fetching the list of the selected connect/email service.
                    $(".connection_email_list", parent).hide();

                    // hide all fields that depending on a connection service before showing that belonging to the selected one
                    $('div[class*="Connect"]', parent).hide();

                    add_spinner(this);

                    $.post(ajaxurl, {
                            action: 'mailoptin_customizer_fetch_email_list',
                            connect_service: connect_service,
                            security: $("input[data-customize-setting-link*='[ajax_nonce]']").val()
                        },
                        function (response) {

                            if (_.isObject(response) && 'success' in response && 'data' in response) {
                                var data = response.data;

                                if (_.size(data) >= 1 || $.inArray(connect_service, ['ConvertFoxConnect', 'RegisteredUsersConnect']) !== -1) {

                                    // clear out the select options before appending.
                                    $("select[name='connection_email_list'] option", parent).remove();

                                    var connection_email_list = $("select[name='connection_email_list']", parent);

                                    // append default "Select..." option to select dropdown.
                                    connection_email_list.append($('<option>', {
                                        value: '',
                                        text: 'Select...'
                                    }));

                                    $.each(data, function (key, value) {
                                        connection_email_list.append($('<option>', {
                                            value: key,
                                            text: value
                                        }));
                                    });

                                    if ($.inArray(connect_service, ['ConvertFoxConnect', 'RegisteredUsersConnect']) === -1) {
                                        // show email list field.
                                        $(".connection_email_list", parent).show();
                                    }

                                    toggle_connect_service_connected_fields();

                                    $(document.body).trigger('mo_email_list_data_found', [connect_service, parent]);
                                }
                                else {

                                    $(".connection_email_list", parent).hide();

                                    // hide all dependent connection service fields if no connection email list was returned.
                                    $('div[class*="Connect"]', parent).hide();
                                    $(document.body).trigger('mo_email_list_data_not_found', [connect_service, parent]);
                                }
                            }
                            else {
                                $(".connection_email_list", parent).hide();

                                // hide all dependent connection service fields if ajax response came badly or invalid.
                                $('div[class*="Connect"]', parent).hide();
                                $(document.body).trigger('mo_email_list_invalid_response', [connect_service, parent]);
                            }

                            remove_spinner();
                        }
                    );
                });
            }

            /**
             * contextually toggle custom fields connected to a connection service/ email provider
             */
            function toggle_connect_service_connected_fields(parent) {

                // for other selected connect dependent settings fields, hide them if their dependent connection isn't selected.
                // the code below apparently wont work for fields such as radio, checkbox
                var selected_connection_service = $("select[name='connection_service']", parent).val();

                if (selected_connection_service !== '' &&
                    selected_connection_service !== null &&
                    selected_connection_service !== '..' &&
                    selected_connection_service !== '...'
                ) {
                    // hide any shown connection service fields before showing that of selected one.
                    $('div[class*="Connect"]', parent).hide();

                    $('div[class*="' + selected_connection_service + '"]', parent).show();
                }
                else {
                    $('div[class*="Connect"]', parent).hide();
                }
            }

            /**
             * contextually toggle email list/option connected to a connection service/ email provider
             */
            function toggle_connect_service_email_list_field(parent) {
                // Hide email list row if no option is found otherwise show it on admin page load.
                // '*=' selector check if the string after = is found in the element.
                // >= 2 is used because connection email list select-dropdown always have a default "Select..." option.
                if ($("select[name='connection_email_list'] option", parent).length >= 2) {
                    $('.connection_email_list').show();
                }
                else {
                    $('.connection_email_list').hide();
                }
            }
        }
    });

})(jQuery);

