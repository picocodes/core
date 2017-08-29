( function (api) {
    'use strict';

    api('mo_email_campaigns[' + mailoptin_email_campaign_id + '][connection_service]', function (setting) {
        console.log(setting);
        console.log(setting.get());
        var is_display_footer_description, linkSettingValueToControlActiveState;

        /**
         * Determine whether the hide name dependent fields should be displayed.
         *
         * @returns {boolean} Is displayed?
         */
        is_display_footer_description = function () {
            return setting.get() !== 'ActiveCampaignConnect';
        };

        console.log(is_display_footer_description());

        /**
         * Update a control's active state according to the header_textcontrol setting's value.
         *
         * @param {wp.customize.Control} control Site Title or Tagline Control.
         */
        linkSettingValueToControlActiveState = function (control) {
            var setActiveState = function () {
                control.active.set(is_display_footer_description());
            };

            // FYI: With the following we can eliminate all of our PHP active_callback code.
            control.active.validate = is_display_footer_description;

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
            console.log('event triggered');
            setting.bind(setActiveState);
        };

        // Call linkSettingValueToControlActiveState on the site title and tagline controls when they exist.
        api.control('mo_email_campaigns[' + mailoptin_email_campaign_id + '][footer_description]', linkSettingValueToControlActiveState);
    });

}(wp.customize) );