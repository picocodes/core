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

    var ajax_get_custom_fields = function() {
        $.post(ajaxurl, {
                action: 'mailoptin_customizer_fetch_fields',
                connect_service: connect_service,
                security: $("input[data-customize-setting-link*='[ajax_nonce]']").val()
            },
            function (response) {

                if (_.isObject(response) && 'success' in response && 'data' in response) {
                    var data = response.data;

                    if (_.size(data) >= 1 || $.inArray(connect_service, ['ConvertFoxConnect', 'RegisteredUsersConnect']) !== -1) {


                    }
                    else {

                    }
                }

                _this.remove_spinner(parent);
            }
        );
    };

    $(function() {
        $(document).on('click', '.mo-optin-map-custom-field .map-link', function(e) {
            e.preventDefault();
            var parent = $(this).parents('.mo-integration-widget');
            show_modal(parent);
            add_spinner(parent);
            ajax_get_custom_fields();
        })
    });

})(wp.customize, jQuery);