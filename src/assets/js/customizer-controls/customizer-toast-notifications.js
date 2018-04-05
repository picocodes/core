(function ($) {
    var nt = {};

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


    window.onload = function () {
        var preview_iframe_name = $('.wp-full-overlay-main').find('iframe').prop('name');
        // source https://stackoverflow.com/a/16019605/2648410
        var preview_iframe_window = document.getElementsByName(preview_iframe_name)[0].contentWindow.window;

        var counter = 0;

        // this script is loaded inside preview iframe where heartbeat isn't available hence parent.document
        $(document).on('heartbeat-send', function () {
            // it's in seconds.
            counter += wp.heartbeat.interval();

            // after 40seconds, alert users to set an integration
            if (counter > 45 && nt.isOptinActive() === false) {
                preview_iframe_window.moToastr('warning', 'Integration not set', 'Click me to set it up');
            }
        });

    }

})(jQuery);