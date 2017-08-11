<?php

namespace MailOptin\Core\Admin\Customizer\EmailCampaign;

/**
 * Main aim is to serve as a unify store for all template customizer default settings.
 */
class AbstractCustomizer
{
    /** @var array store arrays of template customizer default values. */
    public $customizer_defaults;

    public function __construct()
    {
        $this->customizer_defaults = $this->register_customizer_defaults();
    }

    /**
     * Return array of template customizer default values.
     *
     * @return array
     */
    public function register_customizer_defaults()
    {
        $blog_name = get_bloginfo('name');
        $current_year = date("Y");

        $defaults = [];
        $defaults['activate_email_campaign'] = apply_filters('mailoptin_customizer_email_campaign_activate', '');
        $defaults['email_campaign_subject'] = apply_filters('mailoptin_email_campaign_subject_default', sprintf('[New post] %s', '{{title}}'));
        $defaults['default_image_url'] = apply_filters('mailoptin_customizer_email_campaign_default_image_url', MAILOPTIN_ASSETS_URL . 'images/email-templates/default-feature-img.jpg');
        $defaults['post_content_length'] = apply_filters('mailoptin_customizer_email_campaign_post_content_length', 150);
        $defaults['connection_service'] = apply_filters('mailoptin_customizer_email_campaign_connection_service', '');
        $defaults['connection_email_list'] = apply_filters('mailoptin_customizer_email_campaign_connection_email_list', '');
        $defaults['send_immediately'] = apply_filters('mailoptin_customizer_email_campaign_send_immediately', '');

        $defaults['page_background_color'] = apply_filters('mailoptin_page_background_color_default', '');

        $defaults['header_removal'] = apply_filters('mailoptin_header_removal_default', false);
        $defaults['header_logo'] = apply_filters('mailoptin_header_logo_default', '');
        $defaults['header_background_color'] = apply_filters('mailoptin_header_background_color_default', '');
        $defaults['header_text_color'] = apply_filters('mailoptin_header_text_color_default', '');
        $defaults['header_text'] = apply_filters('mailoptin_header_text_default', $blog_name);
        $defaults['header_web_version_link_label'] = apply_filters('mailoptin_header_web_version_link_label_default', __('View this email in your browser', 'mailoptin'));
        $defaults['header_web_version_link_color'] = apply_filters('mailoptin_header_web_version_link_color_default', '');

        $defaults['content_remove_ellipsis_button'] = apply_filters('mailoptin_content_remove_ellipsis_button_default', false);
        $defaults['content_background_color'] = apply_filters('mailoptin_content_background_color_default', '');
        $defaults['content_text_color'] = apply_filters('mailoptin_content_text_color_default', '');
        $defaults['content_alignment'] = apply_filters('mailoptin_content_alignment_default', 'center');
        $defaults['content_ellipsis_button_alignment'] = apply_filters('mailoptin_content_ellipsis_button_alignment_default', 'center');
        $defaults['content_ellipsis_button_background_color'] = apply_filters('mailoptin_content_ellipsis_button_background_color_default', '');
        $defaults['content_ellipsis_button_text_color'] = apply_filters('mailoptin_content_ellipsis_button_text_color_default', '');
        $defaults['content_title_font_size'] = apply_filters('mailoptin_content_title_font_size_default', '19');
        $defaults['content_body_font_size'] = apply_filters('mailoptin_content_body_font_size_default', '16');
        $defaults['content_ellipsis_button_label'] = apply_filters('mailoptin_content_ellipsis_button_label_default', __('Read more', 'mailoptin'));

        $defaults['footer_removal'] = apply_filters('mailoptin_footer_removal_default', false);
        $defaults['footer_background_color'] = apply_filters('mailoptin_footer_background_color_default', '');
        $defaults['footer_text_color'] = apply_filters('mailoptin_footer_text_color_default', '');
        $defaults['footer_font_size'] = apply_filters('mailoptin_footer_font_size_default', '12');
        $defaults['footer_copyright_line'] = apply_filters('mailoptin_footer_copyright_line_default', "&copy; $current_year $blog_name. All rights reserved.");
        $defaults['footer_unsubscribe_line'] = apply_filters('mailoptin_footer_unsubscribe_line_default', __('If you do not want to receive emails from us, you can', 'mailoptin'));
        $defaults['footer_unsubscribe_link_label'] = apply_filters('mailoptin_footer_unsubscribe_link_label_default', __('unsubscribe', 'mailoptin'));
        $defaults['footer_unsubscribe_link_color'] = apply_filters('mailoptin_footer_unsubscribe_link_color_default', '');
        $defaults['footer_description'] = apply_filters('mailoptin_footer_description_default', "Our mailing address is:
{{company_name}}
{{company_address}},
{{company_address_2}}
{{company_city}}, {{company_state}} {{company_zip}}.
{{company_country}}.");

        return apply_filters('mailoptin_template_customizer_defaults', $defaults);
    }

}