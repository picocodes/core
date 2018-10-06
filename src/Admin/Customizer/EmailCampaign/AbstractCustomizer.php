<?php

namespace MailOptin\Core\Admin\Customizer\EmailCampaign;

use MailOptin\Core\Repositories\EmailCampaignRepository;

/**
 * Main aim is to serve as a unify store for all template customizer default settings.
 */
class AbstractCustomizer
{
    /** @var array store arrays of template customizer default values. */
    public $customizer_defaults;

    /**
     * AbstractCustomizer constructor.
     *
     * @param null|int $email_campaign_id
     */
    public function __construct($email_campaign_id = null)
    {
        $this->email_campaign_type  = EmailCampaignRepository::get_email_campaign_type($email_campaign_id);
        $this->email_campaign_class = EmailCampaignRepository::get_template_class($email_campaign_id);

        $this->customizer_defaults = $this->register_customizer_defaults($email_campaign_id);
    }

    public function campaign_title_default()
    {
        $val = sprintf('[New post] %s', '{{title}}');
        if ($this->email_campaign_type == EmailCampaignRepository::POSTS_EMAIL_DIGEST) {
            $val = __('Round up of Latest Posts', 'mailoptin');
        }

        return $val;
    }

    /**
     * Return array of template customizer default values.
     *
     * @return array
     */
    public function register_customizer_defaults($email_campaign_id = null)
    {
        $blog_name    = get_bloginfo('name');
        $current_year = date("Y");

        $defaults                            = [];
        $defaults['activate_email_campaign'] = apply_filters('mailoptin_customizer_email_campaign_activate', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);

        $defaults['email_campaign_title'] = apply_filters('mailoptin_email_campaign_title_default', EmailCampaignRepository::get_email_campaign_name($email_campaign_id), $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);

        $defaults['email_campaign_subject']  = apply_filters('mailoptin_email_campaign_subject_default', $this->campaign_title_default(), $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);

        $defaults['default_image_url']     = apply_filters('mailoptin_customizer_email_campaign_default_image_url', MAILOPTIN_ASSETS_URL . 'images/email-templates/default-feature-img.jpg', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['post_content_length']   = apply_filters('mailoptin_customizer_email_campaign_post_content_length', 150, $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['item_number']           = apply_filters('mailoptin_customizer_email_campaign_item_number', 5, $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['post_categories']       = apply_filters('mailoptin_customizer_email_campaign_post_categories', [], $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['post_tags']             = apply_filters('mailoptin_customizer_email_campaign_post_tags', [], $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['connection_service']    = apply_filters('mailoptin_customizer_email_campaign_connection_service', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['connection_email_list'] = apply_filters('mailoptin_customizer_email_campaign_connection_email_list', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['send_immediately']      = apply_filters('mailoptin_customizer_email_campaign_send_immediately', false, $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['schedule_type']         = apply_filters('mailoptin_customizer_email_campaign_schedule_type', 'hours', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['schedule_digit']        = apply_filters('mailoptin_customizer_email_campaign_schedule_digit', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);

        $defaults['schedule_interval']   = apply_filters('mailoptin_customizer_email_campaign_schedule_interval', 'every_day', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['schedule_time']       = apply_filters('mailoptin_customizer_email_campaign_schedule_time', '00', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['schedule_day']        = apply_filters('mailoptin_customizer_email_campaign_schedule_day', '0', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['schedule_month_date'] = apply_filters('mailoptin_customizer_email_campaign_schedule_month_date', '1', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);

        $defaults['page_background_color'] = apply_filters('mailoptin_page_background_color_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);

        $defaults['header_removal']                = apply_filters('mailoptin_header_removal_default', false, $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['header_logo']                   = apply_filters('mailoptin_header_logo_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['header_background_color']       = apply_filters('mailoptin_header_background_color_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['header_text_color']             = apply_filters('mailoptin_header_text_color_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['header_text']                   = apply_filters('mailoptin_header_text_default', $blog_name, $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['header_web_version_link_label'] = apply_filters('mailoptin_header_web_version_link_label_default', __('View this email in your browser', 'mailoptin'), $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['header_web_version_link_color'] = apply_filters('mailoptin_header_web_version_link_color_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);

        $defaults['content_before_main_content']              = apply_filters('mailoptin_content_before_main_content_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['content_after_main_content']              = apply_filters('mailoptin_content_after_main_content_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['content_remove_ellipsis_button']           = apply_filters('mailoptin_content_remove_ellipsis_button_default', false, $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['content_remove_feature_image']             = apply_filters('mailoptin_content_remove_feature_image_default', false, $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['content_background_color']                 = apply_filters('mailoptin_content_background_color_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['content_text_color']                       = apply_filters('mailoptin_content_text_color_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['content_alignment']                        = apply_filters('mailoptin_content_alignment_default', 'center', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['content_ellipsis_button_alignment']        = apply_filters('mailoptin_content_ellipsis_button_alignment_default', 'center', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['content_ellipsis_button_background_color'] = apply_filters('mailoptin_content_ellipsis_button_background_color_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['content_ellipsis_button_text_color']       = apply_filters('mailoptin_content_ellipsis_button_text_color_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['content_title_font_size']                  = apply_filters('mailoptin_content_title_font_size_default', '19', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['content_body_font_size']                   = apply_filters('mailoptin_content_body_font_size_default', '16', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['content_ellipsis_button_label']            = apply_filters('mailoptin_content_ellipsis_button_label_default', __('Read more', 'mailoptin'), $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);

        $defaults['footer_removal']                = apply_filters('mailoptin_footer_removal_default', false, $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['footer_background_color']       = apply_filters('mailoptin_footer_background_color_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['footer_text_color']             = apply_filters('mailoptin_footer_text_color_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['footer_font_size']              = apply_filters('mailoptin_footer_font_size_default', '12', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['footer_copyright_line']         = apply_filters('mailoptin_footer_copyright_line_default', "&copy; $current_year $blog_name. All rights reserved.", $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['footer_unsubscribe_line']       = apply_filters('mailoptin_footer_unsubscribe_line_default', __('If you do not want to receive emails from us, you can', 'mailoptin'), $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['footer_unsubscribe_link_label'] = apply_filters('mailoptin_footer_unsubscribe_link_label_default', __('unsubscribe', 'mailoptin'), $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['footer_unsubscribe_link_color'] = apply_filters('mailoptin_footer_unsubscribe_link_color_default', '', $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
        $defaults['footer_description']            = apply_filters('mailoptin_footer_description_default', "Our mailing address is:
{{company_name}}
{{company_address}},
{{company_address_2}}
{{company_city}}, {{company_state}} {{company_zip}}.
{{company_country}}.", $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);

        return apply_filters('mailoptin_template_customizer_defaults', $defaults, $this->customizer_defaults, $this->email_campaign_type, $this->email_campaign_class);
    }

}