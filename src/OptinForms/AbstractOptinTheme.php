<?php

namespace MailOptin\Core\OptinForms;


abstract class AbstractOptinTheme extends AbstractOptinForm
{
    public function __construct($optin_campaign_id, $wp_customize = null)
    {
        add_shortcode('mo-optin-form-wrapper', [$this, 'shortcode_optin_form_wrapper']);
        add_shortcode('mo-optin-form-headline', [$this, 'shortcode_optin_form_headline']);
        add_shortcode('mo-optin-form-description', [$this, 'shortcode_optin_form_description']);
        add_shortcode('mo-optin-form-error', [$this, 'shortcode_optin_form_error']);
        add_shortcode('mo-optin-form-name-field', [$this, 'shortcode_optin_form_name_field']);
        add_shortcode('mo-optin-form-email-field', [$this, 'shortcode_optin_form_email_field']);
        add_shortcode('mo-optin-form-submit-button', [$this, 'shortcode_optin_form_submit_button']);
        add_shortcode('mo-optin-form-note', [$this, 'shortcode_optin_form_note']);

        parent::__construct($optin_campaign_id, $wp_customize);
    }

    /**
     * Generate honeypot field for optin form.
     *
     * @return string
     */
    public function honeypot()
    {
        // Give ability to turn off honeypot.
        if (apply_filters('mailoptin_disable_optin_honeypot', false)) {
            return '';
        }

        $optin_css_id = $this->optin_css_id;

        // Create honeypot fields and allow them to be filtered.
        // Don't hange type from text to email to prevent "An invalid form control with name='text' is not focusable." error
        $fields = "<input id='{$optin_css_id}_honeypot_email_field' type='text' name='email' value='' style='display:none'/>";
        $fields .= '<input id="' . $optin_css_id . '_honeypot_website_field" type="text" name="website" value="" style="display:none" />';
        $fields .= '<input id="' . $optin_css_id . '_honeypot_timestamp" type="hidden" name="mo-timestamp" value="' . time() . '" style="display:none" />';
        $fields = apply_filters('mailoptin_optin_honeypot_fields', $fields, $this->optin_campaign_id);

        return $fields;
    }

    /**
     * Headline styles.
     *
     * @return string
     */
    public function headline_styles()
    {
        $style_arg = apply_filters('mo_optin_form_headline_styles',
            [
                'color:' . $this->get_customizer_value('headline_font_color'),
                'font-family:' . $this->_construct_font_family($this->get_customizer_value('headline_font')),
            ],
            $this->optin_campaign_id,
            $this->optin_campaign_type,
            $this->optin_campaign_uuid
        );

        if ($this->get_customizer_value('hide_headline')) {
            $style_arg[] = 'display: none';
        }

        return implode(';', $style_arg);
    }

    /**
     * Description styles.
     *
     * @return string
     */
    public function description_styles()
    {
        $style_arg = apply_filters('mo_optin_form_description_styles',
            [
                'color:' . $this->get_customizer_value('description_font_color'),
                'font-family:' . $this->_construct_font_family($this->get_customizer_value('description_font'))
            ],
            $this->optin_campaign_id,
            $this->optin_campaign_type,
            $this->optin_campaign_uuid
        );

        if ($this->get_customizer_value('hide_description')) {
            $style_arg[] = 'display: none';
        }

        return implode(';', $style_arg);
    }

    /**
     * Note styles.
     *
     * @return string
     */
    public function note_styles()
    {
        $style_arg = apply_filters('mo_optin_form_note_styles',
            [
                'color:' . $this->get_customizer_value('note_font_color'),
                'font-family:' . $this->_construct_font_family($this->get_customizer_value('note_font'))
            ],
            $this->optin_campaign_id,
            $this->optin_campaign_type,
            $this->optin_campaign_uuid
        );

        if ($this->get_customizer_value('hide_note')) {
            $style_arg[] = 'display: none';
        }

        return implode(';', $style_arg);
    }

    /**
     * Name field styles.
     *
     * @return string
     */
    public function name_field_styles()
    {
        $style_arg = apply_filters('mo_optin_form_name_field_styles',
            [
                'color:' . $this->get_customizer_value('name_field_color'),
                'font-family:' . $this->get_customizer_value('name_field_font'),
                'height: auto'
            ],
            $this->optin_campaign_id,
            $this->optin_campaign_type,
            $this->optin_campaign_uuid
        );

        if ($this->get_customizer_value('hide_name_field')) {
            $style_arg[] = 'display: none';
        }

        return implode(';', $style_arg);
    }

    /**
     * Email field styles.
     *
     * @return string
     */
    public function email_field_styles()
    {
        $style_arg = apply_filters('mo_optin_form_email_field_styles',
            [
                'color:' . $this->get_customizer_value('email_field_color'),
                'font-family:' . $this->get_customizer_value('email_field_font'),
                'height: auto'
            ],
            $this->optin_campaign_id,
            $this->optin_campaign_type,
            $this->optin_campaign_uuid
        );

        return implode(';', $style_arg);
    }

    /**
     * Submit button styles.
     *
     * @return string
     */
    public function submit_button_styles()
    {
        $style_arg = apply_filters('mo_optin_form_submit_button_styles',
            [
                'background:' . $this->get_customizer_value('submit_button_background'),
                'color:' . $this->get_customizer_value('submit_button_color'),
                'font-family:' . $this->_construct_font_family($this->get_customizer_value('submit_button_font')),
                'height: auto'
            ],
            $this->optin_campaign_id,
            $this->optin_campaign_type,
            $this->optin_campaign_uuid
        );

        return implode(';', $style_arg);
    }

    /**
     * Form container styles.
     *
     * @return string
     */
    public function form_container_styles()
    {
        $style_arg = apply_filters('mo_optin_form_container_styles',
            [
                'position: relative',
                'margin-right: auto',
                'margin-left: auto',
                'background:' . $this->get_customizer_value('form_background_color'),
                'border-color:' . $this->get_customizer_value('form_border_color'),
                'line-height:normal'
            ],
            $this->optin_campaign_id,
            $this->optin_campaign_type,
            $this->optin_campaign_uuid
        );

        return implode(';', $style_arg);
    }

    /**
     * Optin form wrapper shortcode.
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public function shortcode_optin_form_wrapper($atts, $content)
    {
        $optin_campaign_uuid = $this->optin_campaign_uuid;
        $optin_css_id = $this->optin_css_id;
        $form_container_styles = $this->form_container_styles();
        $name_email_class_indicator = $this->get_customizer_value('hide_name_field') === false ? 'mo-has-name-email' : 'mo-has-email';


        $atts = shortcode_atts(
            array(
                'class' => '',
                'style' => '',
            ),
            $atts
        );

        $class = sanitize_html_class($atts['class']);
        $class = "mo-optin-form-wrapper $name_email_class_indicator $class";

        $style = esc_html($atts['style']);
        $style = "$form_container_styles $style";

        $html = "<div id=\"$optin_css_id\" class=\"$class\" style=\"$style\">";
        $html .= apply_filters('mo_optin_form_before_form_tag', '', $this->optin_campaign_id, $this->optin_campaign_type, $optin_campaign_uuid, $optin_css_id);
        $html .= "<form method=\"post\" class='mo-optin-form' id='{$optin_css_id}_form'>";
        $html .= do_shortcode($content);
        $html .= $this->honeypot();
        $html .= '</form>';
        $html .= apply_filters('mo_optin_form_after_form_tag', '', $this->optin_campaign_id, $this->optin_campaign_type, $optin_campaign_uuid, $optin_css_id);

        // processing / spinner div
        $html .= apply_filters('mo_optin_processing_structure', '<div class="mo-optin-spinner" style="display: none"></div>', $optin_campaign_uuid, $optin_css_id);
        // success div
        $html .= apply_filters('mo_optin_success_structure', '<div class="mo-optin-success-msg" style="display: none">' . $this->get_customizer_value('success_message') . '</div>', $optin_campaign_uuid, $optin_css_id);

        if ($this->optin_campaign_type != 'lightbox') {
            $html .= $this->branding_attribute();
        }

        $html .= "</div>";

        if ($this->optin_campaign_type == 'lightbox') {
            $html .= $this->branding_attribute();
        }

        return $html;
    }

    /**
     * Plugin attribution link.
     *
     * @return mixed
     */
    public function branding_attribute()
    {
        // disable branding for lite users
        if (!defined('MAILOPTIN_DETACH_LIBSODIUM')) {
            return;
        }

        if ($this->get_customizer_value('remove_branding') === true) {
            return;
        }

        $mailoptin_url = 'https://mailoptin.io/?ref=optin-branding';

        return apply_filters(
            'mailoptin_branding_attribute',
            "<div class=\"mo-optin-powered-by\" style='display:block !important;visibility:visible !important;position:static !important;top: 0 !important;left: 0 !important;text-align: center !important;height: 100% !important;width: 100% !important;overflow: visible !important;opacity:1 !important;text-indent: 0 !important;clip: auto !important;clip-path: none !important;'>" .
            "<a style='display:block !important;visibility:visible !important;position:static !important;top: 0 !important;left: 0 !important;text-align: center !important;height: 100% !important;width: 100% !important;overflow: visible !important;opacity:1 !important;text-indent: 0 !important;clip: auto !important;clip-path: none !important;' target=\"_blank\" href=\"$mailoptin_url\">" .
            '<img src="' . MAILOPTIN_ASSETS_URL . 'images/mo-optin-brand.png" style="height:auto !important;width:60px !important;display:inline !important;visibility:visible !important;position:static !important;top: 0 !important;left: 0 !important;text-align: center !important;overflow: visible !important;opacity:1 !important;text-indent: 0 !important;clip: auto !important;clip-path: none !important;"/>' .
            '</a></div>'
        );
    }

    /**
     * Optin form headline shortcode.
     *
     * @param array $atts
     *
     * @return string
     */
    public function shortcode_optin_form_headline($atts)
    {
        $headline = apply_filters('mo_optin_form_before_headline', '', $this->optin_campaign_id, $this->optin_campaign_type, $this->optin_campaign_uuid, $atts);
        $headline .= $this->get_customizer_value('headline');
        $headline .= apply_filters('mo_optin_form_after_headline', '', $this->optin_campaign_id, $this->optin_campaign_type, $this->optin_campaign_uuid, $atts);
        $headline_styles = $this->headline_styles();

        $atts = shortcode_atts(
            array(
                'class' => '',
                'style' => '',
                'tag' => 'h2',
            ),
            $atts
        );

        $tag = sanitize_text_field($atts['tag']);
        $class = sanitize_html_class($atts['class']);
        $class = "mo-optin-form-headline $class";

        $style = esc_attr($atts['style']);
        $style = "$headline_styles $style";

        if ($atts['tag'] == 'h2') {
            $style .= "padding: 0;";
        }

        $html = "<$tag class=\"mo-optin-form-headline $class\" style=\"$style\">$headline</$tag>";

        return $html;
    }

    /**
     * Optin form description shortcode.
     *
     * @param array $atts
     *
     * @return string
     */
    public function shortcode_optin_form_description($atts)
    {
        $description = apply_filters('mo_optin_form_before_description', '', $this->optin_campaign_id, $this->optin_campaign_type, $this->optin_campaign_uuid, $atts);
        $description .= $this->get_customizer_value('description');
        $description .= apply_filters('mo_optin_form_after_description', '', $this->optin_campaign_id, $this->optin_campaign_type, $this->optin_campaign_uuid, $atts);

        $description_styles = $this->description_styles();

        $atts = shortcode_atts(
            array(
                'class' => '',
                'style' => '',
            ),
            $atts
        );

        $class = sanitize_html_class($atts['class']);
        $class = "mo-optin-form-description $class";

        $style = esc_attr($atts['style']);
        $style = "$description_styles $style";

        $html = "<div class=\"$class\" style=\"$style\">$description</div>";

        return $html;
    }

    /**
     * Optin form note shortcode.
     *
     * @param array $atts
     *
     * @return string
     */
    public function shortcode_optin_form_note($atts)
    {
        $note = apply_filters('mo_optin_form_before_note', '', $this->optin_campaign_id, $this->optin_campaign_type, $this->optin_campaign_uuid, $atts);
        $note .= $this->get_customizer_value('note');
        $note .= apply_filters('mo_optin_form_after_note', '', $this->optin_campaign_id, $this->optin_campaign_type, $this->optin_campaign_uuid, $atts);

        $note_styles = $this->note_styles();

        $atts = shortcode_atts(
            array(
                'class' => '',
                'style' => '',
            ),
            $atts
        );

        $class = sanitize_html_class($atts['class']);
        $class = "mo-optin-form-note $class";

        $style = esc_attr($atts['style']);
        $style = "$note_styles $style";

        $html = "<div class=\"$class\" style=\"$style\">$note</div>";

        return $html;
    }

    /**
     * Optin form name field shortcode.
     *
     * @param array $atts
     *
     * @return string
     */
    public function shortcode_optin_form_name_field($atts)
    {
        $optin_css_id = $this->optin_css_id;
        $name_field_styles = $this->name_field_styles();
        $name_field_placeholder = $this->get_customizer_value('name_field_placeholder');

        $atts = shortcode_atts(
            array(
                'class' => '',
                'style' => '',
            ),
            $atts
        );

        $class = sanitize_html_class($atts['class']);
        $class = "mo-optin-field mo-optin-form-name-field $class";

        $style = esc_attr($atts['style']);
        $style = "$name_field_styles $style";

        $html = apply_filters('mo_optin_form_before_form_name_field', '', $this->optin_campaign_id, $this->optin_campaign_type, $this->optin_campaign_uuid, $atts);
        $html .= "<input id=\"{$optin_css_id}_name_field\" class=\"$class\" style='$style' type=\"text\" placeholder=\"$name_field_placeholder\" name=\"mo-name\" autocomplete=\"on\">";
        $html .= apply_filters('mo_optin_form_after_form_name_field', '', $this->optin_campaign_id, $this->optin_campaign_type, $this->optin_campaign_uuid, $atts);

        return $html;
    }

    /**
     * Optin form email field shortcode.
     *
     * @param array $atts
     *
     * @return string
     */
    public function shortcode_optin_form_email_field($atts)
    {
        $optin_css_id = $this->optin_css_id;
        $email_field_styles = $this->email_field_styles();
        $email_field_placeholder = $this->get_customizer_value('email_field_placeholder');

        $atts = shortcode_atts(
            array(
                'class' => '',
                'style' => '',
            ),
            $atts
        );

        $class = sanitize_html_class($atts['class']);
        $class = "mo-optin-field mo-optin-form-email-field $class";

        $style = esc_attr($atts['style']);
        $style = "$email_field_styles $style";

        $html = apply_filters('mo_optin_form_before_form_name_field', '', $this->optin_campaign_id, $this->optin_campaign_type, $this->optin_campaign_uuid, $atts);
        $html .= "<input id=\"{$optin_css_id}_email_field\" class=\"$class\" style=\"$style\" type=\"email\" placeholder=\"$email_field_placeholder\" name=\"mo-email\" autocomplete=\"on\">";
        $html .= apply_filters('mo_optin_form_after_form_email_field', '', $this->optin_campaign_id, $this->optin_campaign_type, $this->optin_campaign_uuid, $atts);

        return $html;
    }

    /**
     * Optin form submit button shortcode.
     *
     * @param array $atts
     *
     * @return string
     */
    public function shortcode_optin_form_submit_button($atts)
    {
        $optin_css_id = $this->optin_css_id;
        $submit_button_styles = $this->submit_button_styles();
        $submit_button = $this->get_customizer_value('submit_button');

        $atts = shortcode_atts(
            array(
                'class' => '',
                'style' => '',
            ),
            $atts
        );

        $class = sanitize_html_class($atts['class']);
        $class = "mo-optin-form-submit-button $class";

        $style = esc_attr($atts['style']);
        $style = "$submit_button_styles $style";

        $html = apply_filters('mo_optin_form_before_form_submit_button', '', $this->optin_campaign_id, $this->optin_campaign_type, $this->optin_campaign_uuid, $atts);
        $html .= "<input id=\"{$optin_css_id}_submit_button\" class=\"$class\" style=\"$style\" type=\"submit\" value=\"$submit_button\">";
        $html .= apply_filters('mo_optin_form_after_form_submit_button', '', $this->optin_campaign_id, $this->optin_campaign_type, $this->optin_campaign_uuid, $atts);

        return $html;
    }

    /**
     * Optin form error shortcode.
     *
     * @param array $atts
     *
     * @return string
     */
    public function shortcode_optin_form_error($atts)
    {
        $atts = shortcode_atts(
            array(
                'class' => '',
                'style' => '',
                'label' => __('Invalid email address', 'mailoptin'),
            ),
            $atts
        );

        $class = sanitize_html_class($atts['class']);
        $class = "mo-optin-error $class";

        $style = esc_attr($atts['style']);

        $label = $atts['label'];

        $html = "<div class=\"$class\" style='$style'>$label</div>";

        return $html;
    }

    /**
     * Filter form field attributes for unofficial attributes.
     *
     * @param array $atts supplied shortcode attributes
     *
     * @return string
     */
    function mo_optin_form_other_field_atts($atts)
    {
        if (!is_array($atts)) return $atts;

        $official_atts = array('name', 'class', 'id', 'value', 'title', 'required', 'placeholder', 'key');

        $other_atts = array();

        foreach ($atts as $key => $value) {
            if (!in_array($key, $official_atts)) {
                $other_atts[$key] = $value;
            }
        }

        $other_atts_html = '';
        foreach ($other_atts as $key => $value) {
            $other_atts_html .= "$key=\"$value\" ";
        }

        return $other_atts_html;
    }
}