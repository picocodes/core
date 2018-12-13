(function (api, $) {

    var show_modal = function (parent) {
        $('.mo-optin-map-custom-field-settings', parent).show();
    };

    var add_spinner = function (parent) {
        $('.mo-optin-map-custom-field-settings', parent).prepend('<div class="mo-optin-map-custom-field-spinner"></div>');
    };

    var remove_spinner = function (parent) {
        $('.mo-optin-map-custom-field-spinner', parent).remove();
    };

    var hide_modal = function (parent) {
        $('.mo-optin-map-custom-field-settings', parent).hide();
        $('.mo-optin-map-custom-field-settings-content', parent).hide();
    };

    var ajax_get_custom_fields = function (parent) {
        $.post(ajaxurl, {
                action: 'mailoptin_customizer_optin_map_custom_field',
                connect_service: $("select[name='connection_service']", parent).val(),
                list_id: $("select[name='connection_email_list']", parent).val(),
                custom_fields: $('.mo-fields-save-field').val(),
                security: $("input[data-customize-setting-link*='[ajax_nonce]']").val()
            },
            function (response) {

                if (_.isObject(response) && 'data' in response) {
                    $('.mo-optin-map-custom-field-settings', parent).show();
                    $('.mo-optin-map-custom-field-settings-content', parent).html(response.data).show();
                }

                remove_spinner(parent);
            }
        );
    };

    $(function () {
        $(document).on('click', '.mo-optin-map-custom-field .map-link', function (e) {
            e.preventDefault();
            var parent = $(this).parents('.mo-integration-widget');
            show_modal(parent);
            add_spinner(parent);
            ajax_get_custom_fields(parent);
        });

        $(document).on('click', '.mo-optin-map-custom-field-close', function (e) {
            e.preventDefault();
            var parent = $(this).parents('.mo-integration-widget');
            hide_modal(parent);
        });

        $(document).on('click', '.mo-optin-field-map-save', function (e) {
            e.preventDefault();
            var parent = $(this).parents('.mo-integration-widget');
            hide_modal(parent);
        });
    });

})(wp.customize, jQuery);