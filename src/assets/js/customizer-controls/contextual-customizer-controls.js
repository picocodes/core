(function (api, $) {
    'use strict';

    $(window).on('load', function () {
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

        api('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][click_launch_status]', function (setting) {
            var is_displayed, linkSettingValueToControlActiveState;

            is_displayed = function () {
                return setting.get();
            };

            linkSettingValueToControlActiveState = function (control) {
                var setActiveState = function () {
                    control.active.set(is_displayed());
                    // hide all display rules sections save for click launch when click launch is activated.
                    api.control('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][load_optin_globally]').active(!is_displayed());
                    api.section('mo_wp_exit_intent_display_rule_section').active(!is_displayed());
                    api.section('mo_wp_x_seconds_display_rule_section').active(!is_displayed());
                    api.section('mo_wp_x_scroll_display_rule_section').active(!is_displayed());
                    api.section('mo_wp_x_page_views_display_rule_section').active(!is_displayed());
                    api.section('mo_wp_page_filter_display_rule_section').active(!is_displayed());
                    api.section('mo_wp_shortcode_template_tag_display_rule_section').active(!is_displayed());
                };

                control.active.validate = is_displayed;

                // Set initial active state.
                setActiveState();

                setting.bind(setActiveState);
            };

            api.control('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][click_launch_basic_shortcode]', linkSettingValueToControlActiveState);
            api.control('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][click_launch_advance_shortcode]', linkSettingValueToControlActiveState);
            api.control('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][click_launch_html_code]', linkSettingValueToControlActiveState);
        });

        // contextual display of redirect_url and download_url fields in success panel/section.
        api('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][success_action]', function (setting) {
                var is_redirect_url_value_displayed, is_download_file_value_displayed,
                    linkSettingValueToControlActiveState1, linkSettingValueToControlActiveState2;

                is_redirect_url_value_displayed = function () {
                    return setting.get() === 'redirect_url';
                };

                is_download_file_value_displayed = function () {
                    return setting.get() === 'download_file';
                };

                linkSettingValueToControlActiveState1 = function (control) {
                    var setActiveState = function () {
                        control.active.set(is_redirect_url_value_displayed());
                    };

                    control.active.validate = is_redirect_url_value_displayed;

                    // Set initial active state.
                    setActiveState();

                    setting.bind(setActiveState);
                };

                linkSettingValueToControlActiveState2 = function (control) {
                    var setActiveState = function () {
                        control.active.set(is_download_file_value_displayed());
                    };

                    control.active.validate = is_download_file_value_displayed;

                    // Set initial active state.
                    setActiveState();

                    setting.bind(setActiveState);
                };

                api.control('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][redirect_url_value]', linkSettingValueToControlActiveState1);
                api.control('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][download_file_value]', linkSettingValueToControlActiveState2);
            }
        );

        // handles click to select on input readonly fields
        $('.mo-click-select').click(function () {
            this.select();
        });

        // handles activation and deactivation of optin
        $('#mo-optin-activate-switch').on('change', function () {
            $.post(ajaxurl, {
                action: 'mailoptin_optin_toggle_active',
                id: mailoptin_optin_campaign_id,
                status: this.checked,
                security: $("input[data-customize-setting-link*='[ajax_nonce]']").val()
            }, function ($response) {
                console.log($response);
            });
        });


    });

})(wp.customize, jQuery);