<?php

namespace MailOptin\Core\Admin\Customizer\OptinForm;

use MailOptin\Core\Repositories\OptinCampaignsRepository;

/**
 * Main aim is to serve as a unify store for all optin form customizer default settings.
 */
class AbstractCustomizer
{
    /** @var array store arrays of optin form customizer default values. */
    public $customizer_defaults;

    /** @var int Optin campaign ID */
    protected $optin_campaign_id;

    /**
     * AbstractCustomizer constructor.
     *
     * @param null|int $optin_campaign_id
     */
    public function __construct($optin_campaign_id = null)
    {
        $this->optin_campaign_id = $optin_campaign_id;
        $this->customizer_defaults = $this->register_customizer_defaults();
    }

    /**
     * Return array of optin customizer default values.
     *
     * @return array
     */
    public function register_customizer_defaults()
    {
        $defaults = [];
        $defaults['form_background_image'] = apply_filters('mo_optin_form_background_image_default', '');
        $defaults['form_image'] = apply_filters('mo_optin_form_image_default', '');
        $defaults['form_background_color'] = apply_filters('mo_optin_form_background_color_default', '');
        $defaults['form_border_color'] = apply_filters('mo_optin_form_border_color_default', '');
        $defaults['form_custom_css'] = apply_filters('mo_optin_form_custom_css_default', '');

        $defaults['headline'] = apply_filters('mo_optin_form_headline_default', __("Don't miss our update", 'mailoptin'));
        $defaults['headline_font_color'] = apply_filters('mo_optin_form_headline_font_color_default', '');
        $defaults['headline_font'] = apply_filters('mo_optin_form_headline_font_default', 'Helvetica Neue');

        $defaults['description'] = apply_filters('mo_optin_form_description_default', __('Be the first to get latest updates and exclusive content straight to your email inbox.', 'mailoptin'));
        $defaults['description_font_color'] = apply_filters('mo_optin_form_description_font_color_default', '');
        $defaults['description_font'] = apply_filters('mo_optin_form_description_font_default', 'Tahoma');

        $defaults['note'] = apply_filters('mo_optin_form_note_default', __('We promise not to spam you. You can unsubscribe at any time.', 'mailoptin'));
        $defaults['note_font_color'] = apply_filters('mo_optin_form_note_font_color_default', '');
        $defaults['note_font'] = apply_filters('mo_optin_form_note_font_default', '');
        $defaults['note_close_optin_onclick'] = apply_filters('mo_optin_form_note_close_optin_onclick_default', false);

        $defaults['display_only_button'] = apply_filters('mo_optin_form_hide_name_field_default', false);
        $defaults['hide_name_field'] = apply_filters('mo_optin_form_hide_name_field_default', false);
        $defaults['name_field_placeholder'] = apply_filters('mo_optin_form_name_field_placeholder_default', __('Enter your name here...', 'mailoptin'));
        $defaults['name_field_color'] = apply_filters('mo_optin_form_name_field_color_default', '');
        $defaults['name_field_font'] = apply_filters('mo_optin_form_name_field_font_default', 'Consolas, Lucida Console, monospace');
        $defaults['email_field_placeholder'] = apply_filters('mo_optin_form_email_field_placeholder_default', __('Enter your email address here...', 'mailoptin'));
        $defaults['email_field_color'] = apply_filters('mo_optin_form_email_field_color_default', '');
        $defaults['email_field_font'] = apply_filters('mo_optin_form_email_field_font_default', 'Consolas, Lucida Console, monospace');
        $defaults['submit_button'] = apply_filters('mo_optin_form_submit_button_default', __('Subscribe Now', 'mailoptin'));
        $defaults['submit_button_color'] = apply_filters('mo_optin_form_submit_button_color_default', '');
        $defaults['submit_button_background'] = apply_filters('mo_optin_form_submit_button_background_default', '');
        $defaults['submit_button_font'] = apply_filters('mo_optin_form_submit_button_font_default', '');


        $defaults['campaign_title'] = apply_filters('mo_optin_form_campaign_title_default', OptinCampaignsRepository::get_optin_campaign_name($this->optin_campaign_id));

        $defaults['bar_position'] = apply_filters('mo_optin_form_bar_position_default', 'top');
        $defaults['slidein_position'] = apply_filters('mo_optin_form_slidein_position_default', 'bottom_right');
        $defaults['bar_sticky'] = apply_filters('mo_optin_form_hide_headline_default', true);
        $defaults['hide_close_button'] = apply_filters('mo_optin_form_hide_close_button_default', false);
        $defaults['hide_headline'] = apply_filters('mo_optin_form_hide_headline_default', false);
        $defaults['hide_description'] = apply_filters('mo_optin_form_hide_description_default', false);
        $defaults['hide_note'] = apply_filters('mo_optin_form_hide_note_default', false);
        $defaults['success_message'] = apply_filters('mo_optin_form_success_message_default', __('Thanks for subscribing! Please check your email for further instructions.', 'mailoptin'));
        $defaults['cookie'] = apply_filters('mo_optin_form_cookie_default', 30);

        $defaults['inpost_form_optin_position'] = apply_filters('mo_optin_form_inpost_form_optin_position_default', 'after_content');

        $defaults['schedule_status'] = apply_filters('mo_optin_form_schedule_status_default', false, $this->customizer_defaults);
        $defaults['schedule_start'] = apply_filters('mo_optin_form_schedule_start_default', '', $this->customizer_defaults);
        $defaults['schedule_end'] = apply_filters('mo_optin_form_schedule_end_default', '', $this->customizer_defaults);
        $defaults['schedule_timezone'] = apply_filters('mo_optin_form_schedule_timezone_default', 'visitors_local_time', $this->customizer_defaults);

        $defaults['modal_effects'] = apply_filters('mo_optin_form_modal_effects_default', '', $this->customizer_defaults);
        $defaults['success_action'] = apply_filters('mo_optin_form_success_action_default', 'success_message', $this->customizer_defaults);
        $defaults['pass_lead_data_redirect_url'] = apply_filters('mo_optin_form_pass_lead_data_redirect_url_default', false, $this->customizer_defaults);
        $defaults['state_after_conversion'] = apply_filters('mo_optin_form_state_after_conversion_default', 'success_message_shown', $this->customizer_defaults);

        return $defaults;
    }
}