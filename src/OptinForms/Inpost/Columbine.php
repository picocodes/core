<?php

namespace MailOptin\Core\OptinForms\Inpost;

use MailOptin\Core\Admin\Customizer\CustomControls\WP_Customize_Tinymce_Control;
use MailOptin\Core\Admin\Customizer\OptinForm\CustomizerSettings;
use MailOptin\Core\OptinForms\AbstractOptinTheme;

class Columbine extends AbstractOptinTheme
{
    public $optin_form_name = 'Columbine';

    public function __construct($optin_campaign_id, $wp_customize = '')
    {
        // -- default for design sections -- //
        add_filter('mo_optin_form_background_color_default', function () {
            return '#ffffff';
        });
        add_filter('mo_optin_form_border_color_default', function () {
            return '#91a6bf';
        });

        add_filter('mo_optin_form_name_field_placeholder_default', function () {
            return __("Enter your name...", 'mailoptin');
        });

        add_filter('mo_optin_form_email_field_placeholder_default', function () {
            return __("Enter your email...", 'mailoptin');
        });

        // -- default for headline sections -- //
        add_filter('mo_optin_form_headline_default', function () {
            return __("Subscribe To Newsletter", 'mailoptin');
        });

        add_filter('mo_optin_form_headline_font_color_default', function () {
            return '#555555';
        });

        add_filter('mo_optin_form_headline_font_default', function () {
            return 'Lora';
        });

        // -- default for description sections -- //
        add_filter('mo_optin_form_description_font_default', function () {
            return 'Lora';
        });

        add_filter('mo_optin_form_description_default', function () {
            return $this->_description_content();
        });

        add_filter('mo_optin_form_description_font_color_default', function () {
            return '#555555';
        });

        // -- default for fields sections -- //
        add_filter('mo_optin_form_name_field_color_default', function () {
            return '#555555';
        });

        add_filter('mo_optin_form_email_field_color_default', function () {
            return '#555555';
        });

        add_filter('mo_optin_form_submit_button_color_default', function () {
            return '#ffffff';
        });

        add_filter('mo_optin_form_submit_button_background_default', function () {
            return '#54C3A5';
        });

        add_filter('mo_optin_form_submit_button_font_default', function () {
            return 'Lora';
        });

        add_filter('mo_optin_form_name_field_font_default', function () {
            return 'Palatino Linotype, Book Antiqua, serif';
        });

        add_filter('mo_optin_form_email_field_font_default', function () {
            return 'Palatino Linotype, Book Antiqua, serif';
        });

        // -- default for note sections -- //
        add_filter('mo_optin_form_note_font_color_default', function () {
            return '#555555';
        });

        add_filter('mo_optin_form_note_default', function () {
            return '<em>' . __('Give it a try. You can unsubscribe at any time.', 'mailoptin') . '</em>';
        });

        add_filter('mo_optin_form_note_font_default', function () {
            return 'Lora';
        });

        add_filter('mo_get_optin_form_headline_font', function ($font, $font_type) {
            if ($font_type == 'headline_font' && $font == 'Lora') {
                $font .= ':400,700,400italic';
            }

            return $font;
        }, 10, 2);

        add_filter('mailoptin_customizer_optin_campaign_MailChimpConnect_user_input_field_color', function () {
            return '#555555';
        });

        parent::__construct($optin_campaign_id);
    }

    public function features_support()
    {
        return [$this->cta_button];
    }

    /**
     * @param mixed $settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_design_settings($settings, $CustomizerSettingsInstance)
    {
        return $settings;
    }

    /**
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_design_controls($controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $controls;
    }

    /**
     * @param mixed $settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_headline_settings($settings, $CustomizerSettingsInstance)
    {
        $settings['mini_headline'] = array(
            'default' => "DON'T MISS OUT!",
            'type' => 'option',
            'transport' => 'refresh',
            'sanitize_callback' => array($CustomizerSettingsInstance, '_remove_paragraph_from_headline'),
        );

        return $settings;
    }

    /**
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_headline_controls($controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        add_filter('mailoptin_tinymce_customizer_control_count', function ($count) {
            return ++$count;
        });

        $controls['mini_headline'] = new WP_Customize_Tinymce_Control(
            $wp_customize,
            $option_prefix . '[mini_headline]',
            apply_filters('mo_optin_form_customizer_mini_headline_args', array(
                    'label' => __('Mini Headline', 'mailoptin'),
                    'section' => $customizerClassInstance->headline_section_id,
                    'settings' => $option_prefix . '[mini_headline]',
                    'editor_id' => 'mini_headline',
                    'editor_height' => 50,
                    'priority' => 5
                )
            )
        );


        return $controls;
    }

    /**
     * @param mixed $settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_description_settings($settings, $CustomizerSettingsInstance)
    {
        return $settings;
    }

    /**
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_description_controls($controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $controls;
    }

    /**
     * @param mixed $settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_note_settings($settings, $CustomizerSettingsInstance)
    {
        return $settings;
    }

    /**
     * @param array $controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_note_controls($controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $controls;
    }

    /**
     * @param mixed $fields_settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_fields_settings($fields_settings, $CustomizerSettingsInstance)
    {
        // change toggling of nae field to 'refresh' transport to handle switching different styling
        // for when name field is available or not.
        $fields_settings['hide_name_field']['transport'] = 'refresh';
        $fields_settings['display_only_button']['transport'] = 'refresh';

        return $fields_settings;
    }

    /**
     * @param array $fields_controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_fields_controls($fields_controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $fields_controls;
    }

    /**
     * @param mixed $configuration_settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_configuration_settings($configuration_settings, $CustomizerSettingsInstance)
    {
        return $configuration_settings;
    }


    /**
     * @param array $configuration_controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_configuration_controls($configuration_controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $configuration_controls;
    }

    /**
     * @param mixed $output_settings
     * @param CustomizerSettings $CustomizerSettingsInstance
     *
     * @return mixed
     */
    public function customizer_output_settings($output_settings, $CustomizerSettingsInstance)
    {
        return $output_settings;
    }


    /**
     * @param array $output_controls
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param \MailOptin\Core\Admin\Customizer\OptinForm\Customizer $customizerClassInstance
     *
     * @return array
     */
    public function customizer_output_controls($output_controls, $wp_customize, $option_prefix, $customizerClassInstance)
    {
        return $output_controls;
    }

    /**
     * Default description content.
     *
     * @return string
     */
    private function _description_content()
    {
        return __('Receive top education news, lesson ideas, teaching tips and more!', 'mailoptin');
    }

    /**
     * Fulfil interface contract.
     */
    public function optin_script()
    {
        ob_start();
        ?>
        <script type="text/javascript">
            // ensure doAction() is called/loaded after page is ready.
            (function () {
                if (document.readyState === "complete" ||
                    (document.readyState !== "loading" && !document.documentElement.doScroll)) {
                    doAction();
                } else {
                    document.addEventListener("DOMContentLoaded", doAction);
                }

                function doAction() {
                    jQuery(function ($) {
                        if ($('input#<?php echo $this->optin_campaign_uuid; ?>_inpost_name_field:visible').length == 0) {
                            $('div#<?php echo $this->optin_campaign_uuid; ?>_inpost #columbine-email-field').removeClass().addClass('columbine-two-col1');
                            $('div#<?php echo $this->optin_campaign_uuid; ?>_inpost #columbine-submit-button').removeClass().addClass('columbine-two-col2');
                        }
                    });
                }
            })();
        </script>
        <?php

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Template body.
     *
     * @return string
     */
    public function optin_form()
    {
        $mini_header = $this->get_customizer_value('mini_headline');
        $mini_header = empty($mini_header) ? __("Don't miss out!", 'mailoptin') : $mini_header;

        return <<<HTML
[mo-optin-form-wrapper class="columbine-container"]
     <div class="columbine-miniText">$mini_header</div>
    [mo-optin-form-headline tag="div" class="columbine-heading"]
    [mo-optin-form-description class="columbine-caption"]
    <div class="columbine-form">
    [mo-optin-form-error]
    [mo-optin-form-fields-wrapper]
    <div id="columbine-name-field" class="columbine-three-col1">[mo-optin-form-name-field class="columbine-input"]</div>
    <div id="columbine-email-field" class="columbine-three-col2">[mo-optin-form-email-field class="columbine-input"]</div>
    <div id="columbine-submit-button" class="columbine-three-col3">[mo-optin-form-submit-button class="columbine-submit"]</div>
    [/mo-optin-form-fields-wrapper]
    [mo-optin-form-cta-button]
    </div>
    [mo-mailchimp-interests]
    [mo-optin-form-note class="columbine-note"]
[/mo-optin-form-wrapper]
HTML;
    }


    /**
     * Template CSS styling.
     *
     * @return string
     */
    public function optin_form_css()
    {
        $optin_css_id = $this->optin_css_id;
        return <<<CSS
div#$optin_css_id.columbine-container {
         background: #fff;
         border: 3px solid #91a6bf;
         -webkit-border-radius: 5px;
         -moz-border-radius: 5px;
         border-radius: 5px;
         max-width: 100%;
         margin: 10px auto;
         text-align: center;
         width: 100%;
         padding: 20px 30px;
         font-family: 'Lora', times, serif;
         color: #555;
         -webkit-box-sizing: border-box;
         -moz-box-sizing: border-box;
         box-sizing: border-box;
     }

div#$optin_css_id.columbine-container div.columbine-miniText {
         font-size: 1em;
         line-height: 28px;
         text-transform: uppercase;
         color: #54C3A5;
         font-weight: bold;
     }

div#$optin_css_id.columbine-container div.columbine-heading {
         font-size: 40px;
         font-weight: bold;
         line-height: 50px;
     }

div#$optin_css_id.columbine-container div.columbine-caption {
         margin-top: 12px;
         font-style: italic;
         font-size: 18px;
         line-height: 28px;
     }

div#$optin_css_id.columbine-container .columbine-form {
         overflow: hidden;
         margin-top: 30px;
     }
div#$optin_css_id.columbine-container div.columbine-three-col1 {
         float: left;
         width: 33.333%;
     }

div#$optin_css_id.columbine-container div.columbine-three-col2 {
         float: left;
         width: 33.333%;
     }

div#$optin_css_id.columbine-container div.columbine-three-col3 {
         float: left;
         width: 33.333%;
     }
div#$optin_css_id.columbine-container div.columbine-two-col1 {
         float: left;
         width: 66.333%;
     }

div#$optin_css_id.columbine-container div.columbine-two-col2 {
         float: right;
         width: 33.333%;
     }

div#$optin_css_id.columbine-container input.columbine-input {
         background-color: #ffffff;
         width: 100%;
         display: block;
         -webkit-box-sizing: border-box;
         -moz-box-sizing: border-box;
         box-sizing: border-box;
         -webkit-appearance: none;
         -webkit-border-radius: 0;
         -moz-border-radius: 0;
         border-radius: 0;
         font-family: 'Lora', times, serif;
         padding: 11px 17px;
         font-size: 16px;
         line-height: 16px;
         text-align: left;
         border: 1px solid #ccc;
         color: #555;
         outline: none;
         margin: 0;
     }

div#$optin_css_id.columbine-container input.columbine-submit, div#$optin_css_id.columbine-container input[type="submit"].mo-optin-form-cta-button {
         display: block;
         -webkit-box-sizing: border-box;
         -moz-box-sizing: border-box;
         box-sizing: border-box;
         -webkit-appearance: none;
         border: 0;
         background: #54C3A5;
         font-family: 'Lora', times, serif;
         padding: 13px 18px;
         font-size: 16px;
         line-height: 16px;
         text-align: center;
         color: #fff;
         outline: none;
         cursor: pointer;
         font-weight: 600;
         width: 100%;
         margin: 0;
         border-radius: 0;
     }

div#$optin_css_id.columbine-container div.columbine-note {
         margin-top: 10px;
         font-size: 12px;
         line-height: normal;
     }

div#$optin_css_id.columbine-container div.mo-optin-error {
         display: none;
         background: #FF0000;
         color: white;
         text-align: center;
         padding: .2em;
         margin: 0;
         width: 100%;
         font-size: 16px;
         -webkit-box-sizing: border-box;
         -moz-box-sizing: border-box;
         box-sizing: border-box;
         border: 1px solid #FF0000;
     }

@media only screen and (max-width: 650px) {

    div#$optin_css_id.columbine-container div.mo-optin-error {
             margin-bottom: -10px;
     }
    div#$optin_css_id.columbine-container div.columbine-two-col1,
         div#$optin_css_id.columbine-container div.columbine-two-col2,
              div#$optin_css_id.columbine-container div.columbine-three-col1,
                   div#$optin_css_id.columbine-container div.columbine-three-col2,
                        div#$optin_css_id.columbine-container div.columbine-three-col3 {
                                 float: none;
                                 width: 100%;
                                 margin-right: 0;
                                 margin-top: 10px;
                             }

}
CSS;

    }
}