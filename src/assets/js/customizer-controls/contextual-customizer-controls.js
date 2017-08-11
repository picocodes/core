( function (api) {
    'use strict';

    // Add callback for when the header_textcolor setting exists.
    api('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][hide_name_field]', function (setting) {
        var is_hide_name_field, linkSettingValueToControlActiveState;

        /**
         * Determine whether the hide name dependent fields should be displayed.
         *
         * @returns {boolean} Is displayed?
         */
        is_hide_name_field = function () {
            return !setting.get();
        };

        /**
         * Update a control's active state according to the header_textcontrol setting's value.
         *
         * @param {wp.customize.Control} control Site Title or Tagline Control.
         */
        linkSettingValueToControlActiveState = function (control) {
            var setActiveState = function () {
                control.active.set(is_hide_name_field());
            };

            // FYI: With the following we can eliminate all of our PHP active_callback code.
            control.active.validate = is_hide_name_field;

            // Set initial active state.
            setActiveState();

            /*
             * Update activate state whenever the setting is changed.
             * Even when the setting does have a refresh transport where the
             * server-side active callback will manage the active state upon
             * refresh, having this JS management of the active state will
             * ensure that controls will have their visibility toggled
             * immediately instead of waiting for the preview to load.
             * This is especially important if the setting has a postMessage
             * transport where changing the setting wouldn't normally cause
             * the preview to refresh and thus the server-side active_callbacks
             * would not get invoked.
             */
            setting.bind(setActiveState);
        };

        // Call linkSettingValueToControlActiveState on the site title and tagline controls when they exist.
        api.control('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][name_field_placeholder]', linkSettingValueToControlActiveState);
        api.control('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][name_field_color]', linkSettingValueToControlActiveState);
        api.control('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][name_field_font]', linkSettingValueToControlActiveState);
    });

    // Add callback for when the header_textcolor setting exists.
    api('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][bar_position]', function (setting) {
        var is_displayed, linkSettingValueToControlActiveState;

        is_displayed = function () {
            return setting.get() == 'top';
        };

        linkSettingValueToControlActiveState = function (control) {
            var setActiveState = function () {
                control.active.set(is_displayed());
            };

            control.active.validate = is_displayed;

            // Set initial active state.
            setActiveState();

            setting.bind(setActiveState);
        };

        api.control('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][bar_sticky]', linkSettingValueToControlActiveState);
    });

}(wp.customize) );