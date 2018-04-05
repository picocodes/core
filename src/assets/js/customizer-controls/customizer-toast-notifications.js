(function ($) {
    var nt = {};

    nt.isOptinActiveFlag = false;

    /**
     * Check if optin campaign has been activated.
     *
     * @returns {boolean}
     */
    nt.isOptinActive = function () {
        return document.getElementById('mo-optin-activate-switch').checked === true;
    };

    /**
     * Check if integration has been set.
     *
     * @returns {boolean}
     */
    nt.isIntegrationSet = function () {
        return document.querySelector("select[data-customize-setting-link*='connection_service']").value !== "";
    };

    nt.dismiss_ajax = function () {
        $.post(ajaxurl, {
            action: 'mailoptin_dismiss_toastr_is_optin_activated',
            optin_id: mailoptin_optin_campaign_id
        });
    };

    nt.integrationControlFocus = function () {
        parent.wp.customize.control('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][connection_service]').focus();
    };


    window.onload = function () {
        var preview_iframe_name = $('.wp-full-overlay-main').find('iframe').prop('name');
        // source https://stackoverflow.com/a/16019605/2648410
        var preview_iframe_window = document.getElementsByName(preview_iframe_name)[0].contentWindow.window;

        var counter = 0;

        // this script is loaded inside preview iframe where heartbeat isn't available hence parent.document
        $(document).on('heartbeat-send', function () {

            if (moStateRepository.data['integration_not_set'] === mailoptin_optin_campaign_id) return;

            // interval is in seconds.
            counter += wp.heartbeat.interval();

            // after 40seconds, alert users to set an integration
            if (counter > 45 && !nt.isOptinActive() && !nt.isOptinActiveFlag) {
                nt.isOptinActiveFlag = true;

                var options = {
                    onclick: function () {
                        nt.integrationControlFocus();
                        nt.dismiss_ajax();
                    },

                    onCloseClick: function () {
                        nt.dismiss_ajax();
                    }
                };
                preview_iframe_window.moToastr('warning', 'Integration not set', 'Click me to set it up', options);
            }
        });

    }

})(jQuery);