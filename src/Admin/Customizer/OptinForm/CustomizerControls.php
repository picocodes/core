<?php

namespace MailOptin\Core\Admin\Customizer\OptinForm;

use MailOptin\Core\Admin\Customizer\CustomControls\ControlsHelpers;
use MailOptin\Core\Admin\Customizer\CustomControls\WP_Customize_Custom_Content;
use MailOptin\Core\Admin\Customizer\CustomControls\WP_Customize_Google_Font_Control;
use MailOptin\Core\Admin\Customizer\CustomControls\WP_Customize_Font_Stack_Control;
use MailOptin\Core\Admin\Customizer\CustomControls\WP_Customize_Chosen_Select_Control;
use MailOptin\Core\Admin\Customizer\CustomControls\WP_Customize_Tinymce_Control;
use MailOptin\Core\Admin\Customizer\CustomControls\WP_Customize_Toggle_Control;
use MailOptin\Core\OptinForms\AbstractOptinForm;
use MailOptin\Core\Repositories\ConnectionsRepository;
use MailOptin\Core\Repositories\OptinCampaignsRepository;

class CustomizerControls
{
    /** @var \WP_Customize_Manager */
    private $wp_customize;

    /** @var Customizer */
    private $customizerClassInstance;

    /** @var string DB option name prefix */
    private $option_prefix;

    /** @var string DB option name prefix */
    private $optin_class_instance;

    /** @var string default image URL for form_image partial */
    private $default_form_image;

    /** @var string default image URL for form_background_image partial */
    private $default_form_background_image;

    /**
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param Customizer $customizerClassInstance
     * @param null|AbstractOptinForm $optin_class_instance
     */
    public function __construct($wp_customize, $option_prefix, $customizerClassInstance, $optin_class_instance = null)
    {
        $this->wp_customize = $wp_customize;
        $this->customizerClassInstance = $customizerClassInstance;
        $this->option_prefix = $option_prefix;
        $this->optin_class_instance = $optin_class_instance;

        $this->optin_campaign_id = $customizerClassInstance->optin_campaign_id;
    }

    public function design_controls()
    {
        $page_control_args = apply_filters(
            "mo_optin_form_customizer_design_controls",
            [],
            $this->wp_customize,
            $this->option_prefix,
            $this->customizerClassInstance
        );

        if (apply_filters('mo_optin_form_enable_form_image', false)) {

            $this->default_form_image = apply_filters('mo_optin_form_partial_default_image', '');

            if (isset($this->wp_customize->selective_refresh)) {
                $this->wp_customize->selective_refresh->add_partial($this->option_prefix . '[form_image]', array(
                    // Whether to refresh the entire preview in case a partial cannot be refreshed.
                    // A partial render is considered a failure if the render_callback returns false.
                    'fallback_refresh' => true,
                    'selector' => apply_filters('mo_optin_form_image_partial_selector', '.mo-optin-form-image-wrapper'),
                    // determines if change will apply to container / wrapper element.
                    'container_inclusive' => apply_filters('mo_optin_form_image_partial_container_inclusive', false),
                    'render_callback' => apply_filters('mo_optin_form_image_render_callback', function () {
                        return do_shortcode("[mo-optin-form-image default='{$this->default_form_image}']");
                    })
                ));
            } else {
                // if selective refresh not supported, fallback to 'refresh' transport.
                $this->wp_customize->get_setting($this->option_prefix . '[form_image]')->transport = 'refresh';
            }

            $page_control_args['form_image'] = new \WP_Customize_Cropped_Image_Control(
                $this->wp_customize,
                $this->option_prefix . '[form_image]',
                apply_filters('mo_optin_form_customizer_form_image_args', array(
                        'width' => 220,
                        'height' => 35,
                        'flex_width' => true,
                        'flex_height' => true,
                        'label' => __('Image', 'mailoptin'),
                        'section' => $this->customizerClassInstance->design_section_id,
                        'settings' => $this->option_prefix . '[form_image]',
                        'priority' => 10,
                    )
                )
            );
        }

        if (apply_filters('mo_optin_form_enable_form_background_image', false)) {

            $this->default_form_background_image = apply_filters('mo_optin_form_partial_default_background_image', '');

            if (apply_filters('mo_optin_form_enable_selective_refresh_form_background_image', true) && isset($this->wp_customize->selective_refresh)) {
                $this->wp_customize->selective_refresh->add_partial($this->option_prefix . '[form_background_image]', array(
                    // Whether to refresh the entire preview in case a partial cannot be refreshed.
                    // A partial render is considered a failure if the render_callback returns false.
                    'fallback_refresh' => true,
                    'selector' => apply_filters('mo_optin_form_background_image_partial_selector', '.mo-optin-form-background-image-wrapper'),
                    // determines if change will apply to container / wrapper element.
                    'container_inclusive' => apply_filters('mo_optin_form_image_partial_container_inclusive', false),
                    'render_callback' => apply_filters('mo_optin_form_image_render_callback', false)
                ));
            } else {
                // if selective refresh not supported, fallback to 'refresh' transport.
                $this->wp_customize->get_setting($this->option_prefix . '[form_background_image]')->transport = 'refresh';
            }

            $page_control_args['form_background_image'] = new \WP_Customize_Image_Control(
                $this->wp_customize,
                $this->option_prefix . '[form_background_image]',
                apply_filters('mo_optin_form_customizer_form_background_image_args', array(
                        'label' => __('Background Image', 'mailoptin'),
                        'section' => $this->customizerClassInstance->design_section_id,
                        'settings' => $this->option_prefix . '[form_background_image]',
                        'priority' => 20,
                    )
                )
            );
        }

        $page_control_args ['form_background_color'] = new \WP_Customize_Color_Control(
            $this->wp_customize,
            $this->option_prefix . '[form_background_color]',
            apply_filters('mailoptin_optin_customizer_form_background_color_args', array(
                    'label' => __('Background Color', 'mailoptin'),
                    'section' => $this->customizerClassInstance->design_section_id,
                    'settings' => $this->option_prefix . '[form_background_color]',
                    'priority' => 20,
                )
            )
        );

        $page_control_args['form_border_color'] = new \WP_Customize_Color_Control(
            $this->wp_customize,
            $this->option_prefix . '[form_border_color]',
            apply_filters('mo_optin_form_customizer_form_border_color_args', array(
                    'label' => __('Border Color', 'mailoptin'),
                    'section' => $this->customizerClassInstance->design_section_id,
                    'settings' => $this->option_prefix . '[form_border_color]',
                    'priority' => 40,
                )
            )
        );

        if (!defined('MAILOPTIN_DETACH_LIBSODIUM')) {
            $content = sprintf(
                '<div class="mo-pro"><a href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=custom_css_notice" target="_blank">%s</a></div>',
                __('Premium Version Available', 'mailoptin')
            );

            $content .= sprintf(
                __('Upgrade to %sMailOptin Premium%s for more customization options including feature to add your own custom CSS.', 'mailoptin'),
                '<a target="_blank" href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=custom_css_notice">',
                '</a>'
            );

            // always prefix with the name of the connect/connection service.
            $page_control_args['custom_css_notice'] = new WP_Customize_Custom_Content(
                $this->wp_customize,
                $this->option_prefix . '[custom_css_notice]',
                apply_filters('mo_optin_form_customizer_custom_css_notice_args', array(
                        'content' => $content,
                        'section' => $this->customizerClassInstance->design_section_id,
                        'settings' => $this->option_prefix . '[custom_css_notice]',
                        'priority' => 199,
                    )
                )
            );
        }

        do_action('mailoptin_before_design_controls_addition');

        foreach ($page_control_args as $id => $args) {
            if (is_object($args)) {
                $this->wp_customize->add_control($args);
            } else {
                $this->wp_customize->add_control($this->option_prefix . '[' . $id . ']', $args);
            }
        }

        do_action('mailoptin_after_design_controls_addition');
    }

    public function headline_controls()
    {
        $headline_control_args = apply_filters(
            "mo_optin_form_customizer_headline_controls",
            array(
                'headline' => new WP_Customize_Tinymce_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[headline]',
                    apply_filters('mo_optin_form_customizer_headline_args', array(
                            'label' => __('Headline', 'mailoptin'),
                            'section' => $this->customizerClassInstance->headline_section_id,
                            'settings' => $this->option_prefix . '[headline]',
                            'editor_id' => 'headline',
                            'editor_height' => 50,
                            'priority' => 10
                        )
                    )
                ),
                'headline_font_color' => new \WP_Customize_Color_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[headline_color]',
                    apply_filters('mo_optin_form_customizer_headline_font_color_args', array(
                            'label' => __('Color', 'mailoptin'),
                            'section' => $this->customizerClassInstance->headline_section_id,
                            'settings' => $this->option_prefix . '[headline_font_color]',
                            'priority' => 30
                        )
                    )
                ),
                'headline_font' => new WP_Customize_Google_Font_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[headline_font]',
                    apply_filters('mo_optin_form_customizer_headline_font_args', array(
                            'label' => __('Font Family', 'mailoptin'),
                            'section' => $this->customizerClassInstance->headline_section_id,
                            'settings' => $this->option_prefix . '[headline_font]',
                            'count' => 200,
                            'priority' => 20
                        )
                    )
                ),
            ),
            $this->wp_customize,
            $this->option_prefix,
            $this->customizerClassInstance
        );

        do_action('mailoptin_before_headline_controls_addition');

        if (!empty($headline_control_args)) {
            foreach ($headline_control_args as $id => $args) {
                if (is_object($args)) {
                    $this->wp_customize->add_control($args);
                } else {
                    $this->wp_customize->add_control($this->option_prefix . '[' . $id . ']', $args);
                }
            }

            do_action('mailoptin_after_headline_controls_addition');
        }
    }

    public function description_controls()
    {
        $description_controls_args = apply_filters(
            "mo_optin_form_customizer_description_controls",
            array(
                'description' => new WP_Customize_Tinymce_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[description]',
                    apply_filters('mo_optin_form_customizer_description_args', array(
                            'label' => __('Description', 'mailoptin'),
                            'section' => $this->customizerClassInstance->description_section_id,
                            'settings' => $this->option_prefix . '[description]',
                            'editor_id' => 'description',
                            'priority' => 1
                        )
                    )
                ),
                'description_font_color' => new \WP_Customize_Color_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[description_font_color]',
                    apply_filters('mo_optin_form_customizer_description_font_color_args', array(
                            'label' => __('Color', 'mailoptin'),
                            'section' => $this->customizerClassInstance->description_section_id,
                            'settings' => $this->option_prefix . '[description_font_color]',
                            'priority' => 3
                        )
                    )
                ),
                'description_font' => new WP_Customize_Google_Font_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[description_font]',
                    apply_filters('mo_optin_form_customizer_description_font_args', array(
                            'label' => __('Font Family'),
                            'section' => $this->customizerClassInstance->description_section_id,
                            'settings' => $this->option_prefix . '[description_font]',
                            'count' => 200,
                            'priority' => 2
                        )
                    )
                ),
            ),
            $this->wp_customize,
            $this->option_prefix,
            $this->customizerClassInstance
        );

        do_action('mailoptin_before_description_controls_addition');

        foreach ($description_controls_args as $id => $args) {
            if (is_object($args)) {
                $this->wp_customize->add_control($args);
            } else {
                $this->wp_customize->add_control($this->option_prefix . '[' . $id . ']', $args);
            }
        }

        do_action('mailoptin_after_description_controls_addition');
    }


    public function note_controls()
    {
        $note_controls_args = apply_filters(
            "mo_optin_form_customizer_note_controls",
            array(
                'note' => new WP_Customize_Tinymce_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[note]',
                    apply_filters('mo_optin_form_customizer_note_args', array(
                            'label' => __('Note', 'mailoptin'),
                            'section' => $this->customizerClassInstance->note_section_id,
                            'settings' => $this->option_prefix . '[note]',
                            'editor_id' => 'note',
                            'editor_height' => 50,
                            'priority' => 10
                        )
                    )
                ),
                'note_font_color' => new \WP_Customize_Color_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[note_font_color]',
                    apply_filters('mo_optin_form_customizer_note_font_color_args', array(
                            'label' => __('Color', 'mailoptin'),
                            'section' => $this->customizerClassInstance->note_section_id,
                            'settings' => $this->option_prefix . '[note_font_color]',
                            'priority' => 30
                        )
                    )
                ),
                'note_font' => new WP_Customize_Google_Font_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[note_font]',
                    apply_filters('mo_optin_form_customizer_note_font_args', array(
                            'label' => __('Font Family'),
                            'section' => $this->customizerClassInstance->note_section_id,
                            'settings' => $this->option_prefix . '[note_font]',
                            'count' => 200,
                            'priority' => 20
                        )
                    )
                ),
                'note_close_optin_onclick' => new WP_Customize_Toggle_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[note_close_optin_onclick]',
                    apply_filters('mo_optin_form_customizer_note_close_optin_onclick_args', array(
                            'label' => __('Close Optin on Click', 'mailoptin'),
                            'section' => $this->customizerClassInstance->note_section_id,
                            'settings' => $this->option_prefix . '[note_close_optin_onclick]',
                            'description' => sprintf(
                                __('Activate if you want a click on "note" to close the optin form. Particularly useful if close icon is hidden. %sLearn More%s', 'mailoptin'),
                                '<a href="https://mailoptin.io/article/text-link-closes-popup-optin-form/" target="_blank">', '</a>'
                            ),
                            'priority' => 40,
                        )
                    )
                ),
            ),
            $this->wp_customize,
            $this->option_prefix,
            $this->customizerClassInstance
        );

        do_action('mailoptin_before_note_controls_addition');

        foreach ($note_controls_args as $id => $args) {
            if (is_object($args)) {
                $this->wp_customize->add_control($args);
            } else {
                $this->wp_customize->add_control($this->option_prefix . '[' . $id . ']', $args);
            }
        }

        do_action('mailoptin_after_note_controls_addition');

    }

    public function fields_controls()
    {
        $cta_button_action_description = '';
        $cta_button_action_choices = [
            'reveal_optin_form' => __('Reveal Optin Form', 'mailoptin'),
            'navigate_to_url' => __('Navigate to URL', 'mailoptin'),
        ];

        if (!in_array($this->customizerClassInstance->optin_campaign_type, ['sidebar', 'inpost'])) {
            $cta_button_action_choices['close_optin'] = __('Close optin', 'mailoptin');
            $cta_button_action_choices['close_optin_reload_page'] = __('Close optin and reload page', 'mailoptin');
        }

        if (!defined('MAILOPTIN_DETACH_LIBSODIUM')) {

            $cta_button_action_description = sprintf(
                __('Upgrade to %sMailOptin Premium%s to have the option to close, close and reload or reveal optin form when CTA button is clicked.', 'mailoptin'),
                '<a target="_blank" href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=cta_button_action">',
                '</a>'
            );

            unset($cta_button_action_choices['reveal_optin_form']);
            unset($cta_button_action_choices['close_optin']);
            unset($cta_button_action_choices['close_optin_reload_page']);
        }

        $field_controls_args = apply_filters(
            "mo_optin_form_customizer_fields_controls",
            array(
                'hide_name_field' => new WP_Customize_Toggle_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[hide_name_field]',
                    apply_filters('mo_optin_form_customizer_hide_name_field_args', array(
                            'label' => __('Hide Name Field', 'mailoptin'),
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[hide_name_field]',
                            'description' => __('Activate to hide the name field.', 'mailoptin'),
                            'type' => 'light',
                            'priority' => 10,
                        )
                    )
                ),
                'name_field_header' => new WP_Customize_Custom_Content(
                    $this->wp_customize,
                    $this->option_prefix . '[name_field_header]',
                    apply_filters('mo_optin_form_customizer_name_field_header_args', array(
                            'content' => '<div class="mo-field-header">' . __("Name Field", 'mailoptin') . '</div>',
                            'block_class' => 'mo-field-header-wrapper',
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[name_field_header]',
                            'priority' => 18,
                        )
                    )
                ),
                'name_field_placeholder' => apply_filters('mo_optin_form_customizer_name_field_placeholder_args',
                    array(
                        'type' => 'text',
                        'label' => __('Name Placeholder', 'mailoptin'),
                        'section' => $this->customizerClassInstance->fields_section_id,
                        'settings' => $this->option_prefix . '[name_field_placeholder]',
                        'description' => __('The placeholder text for the name field.', 'mailoptin'),
                        'priority' => 20
                    )
                ),
                'name_field_color' => new \WP_Customize_Color_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[name_field_color]',
                    apply_filters('mo_optin_form_customizer_name_field_color_args', array(
                            'label' => __('Name Color', 'mailoptin'),
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[name_field_color]',
                            'priority' => 30,
                            'description' => __('The text color for the name field.', 'mailoptin'),
                        )
                    )
                ),
                'name_field_background' => new \WP_Customize_Color_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[name_field_background]',
                    apply_filters('mo_optin_form_customizer_email_field_background_args', array(
                            'label' => __('Name Background', 'mailoptin'),
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[name_field_background]',
                            'priority' => 37,
                            'description' => __('The background color of the name field.', 'mailoptin'),
                        )
                    )
                ),
                'name_field_font' => new WP_Customize_Font_Stack_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[name_field_font]',
                    apply_filters('mo_optin_form_customizer_name_field_font_args', array(
                            'label' => __('Name Font'),
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[name_field_font]',
                            'description' => __('The font family for the name field.', 'mailoptin'),
                            'count' => 50,
                            'priority' => 40
                        )
                    )
                ),
                'email_field_header' => new WP_Customize_Custom_Content(
                    $this->wp_customize,
                    $this->option_prefix . '[email_field_header]',
                    apply_filters('mo_optin_form_customizer_email_field_header_args', array(
                            'content' => '<div class="mo-field-header">' . __("Email Field", 'mailoptin') . '</div>',
                            'block_class' => 'mo-field-header-wrapper',
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[email_field_header]',
                            'priority' => 48,
                        )
                    )
                ),
                'email_field_placeholder' => apply_filters('mo_optin_form_customizer_email_field_placeholder_args',
                    array(
                        'type' => 'text',
                        'label' => __('Email Placeholder', 'mailoptin'),
                        'section' => $this->customizerClassInstance->fields_section_id,
                        'settings' => $this->option_prefix . '[email_field_placeholder]',
                        'priority' => 50,
                        'description' => __('The placeholder text for the email field.', 'mailoptin'),
                    )
                ),
                'email_field_color' => new \WP_Customize_Color_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[email_field_color]',
                    apply_filters('mo_optin_form_customizer_email_field_color_args', array(
                            'label' => __('Email Color', 'mailoptin'),
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[email_field_color]',
                            'priority' => 60,
                            'description' => __('The text color for the email field.', 'mailoptin'),
                        )
                    )
                ),
                'email_field_background' => new \WP_Customize_Color_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[email_field_background]',
                    apply_filters('mo_optin_form_customizer_email_field_background_args', array(
                            'label' => __('Email Background', 'mailoptin'),
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[email_field_background]',
                            'priority' => 67,
                            'description' => __('The background color of the email field.', 'mailoptin'),
                        )
                    )
                ),
                'email_field_font' => new WP_Customize_Font_Stack_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[email_field_font]',
                    apply_filters('mo_optin_form_customizer_email_field_font_args', array(
                            'label' => __('Email Font'),
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[email_field_font]',
                            'count' => 50,
                            'priority' => 70,
                            'description' => __('The font family for the email field.', 'mailoptin'),
                        )
                    )
                ),
                'submit_button_header' => new WP_Customize_Custom_Content(
                    $this->wp_customize,
                    $this->option_prefix . '[submit_button_header]',
                    apply_filters('mo_optin_form_customizer_submit_button_header_args', array(
                            'content' => '<div class="mo-field-header">' . __("Submit Field", 'mailoptin') . '</div>',
                            'block_class' => 'mo-field-header-wrapper',
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[submit_button_header]',
                            'priority' => 78,
                        )
                    )
                ),
                'submit_button' => apply_filters('mo_optin_form_customizer_submit_button_args',
                    array(
                        'type' => 'text',
                        'label' => __('Submit Button', 'mailoptin'),
                        'section' => $this->customizerClassInstance->fields_section_id,
                        'settings' => $this->option_prefix . '[submit_button]',
                        'priority' => 80,
                        'description' => __('The value/label of the submit button.', 'mailoptin'),
                    )
                ),
                'submit_button_color' => new \WP_Customize_Color_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[submit_button_color]',
                    apply_filters('mo_optin_form_customizer_submit_button_color_args', array(
                            'label' => __('Submit Button Color', 'mailoptin'),
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[submit_button_color]',
                            'priority' => 90,
                            'description' => __('The text color for the submit button field.', 'mailoptin'),
                        )
                    )
                ),
                'submit_button_background' => new \WP_Customize_Color_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[submit_button_background]',
                    apply_filters('mo_optin_form_customizer_submit_button_background_args', array(
                            'label' => __('Submit Button Background', 'mailoptin'),
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[submit_button_background]',
                            'priority' => 100,
                            'description' => __('The background color of the submit button.', 'mailoptin'),
                        )
                    )
                ),
                'submit_button_font' => new WP_Customize_Google_Font_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[submit_button_font]',
                    apply_filters('mo_optin_form_customizer_submit_button_font_args', array(
                            'label' => __('Submit Button Font'),
                            'section' => $this->customizerClassInstance->fields_section_id,
                            'settings' => $this->option_prefix . '[submit_button_font]',
                            'description' => __('The font family for the submit button field.', 'mailoptin'),
                            'count' => 200,
                            'priority' => 110
                        )
                    )
                )
            ),
            $this->wp_customize,
            $this->option_prefix,
            $this->customizerClassInstance
        );

        if (in_array($this->optin_class_instance->cta_button, $this->optin_class_instance->features_support())) {

            $field_controls_args['display_only_button'] = new WP_Customize_Toggle_Control(
                $this->wp_customize,
                $this->option_prefix . '[display_only_button]',
                apply_filters('mo_optin_form_customizer_display_only_button_args', array(
                        'label' => __('Display Only CTA Button', 'mailoptin'),
                        'section' => $this->customizerClassInstance->fields_section_id,
                        'settings' => $this->option_prefix . '[display_only_button]',
                        'description' => __('Activate to hide opt-in form and display a call-to-action button instead.', 'mailoptin'),
                        'type' => 'light',
                        'priority' => 3,
                    )
                )
            );

            $field_controls_args['cta_button_header'] = new WP_Customize_Custom_Content(
                $this->wp_customize,
                $this->option_prefix . '[cta_button_header]',
                apply_filters('mo_optin_form_customizer_cta_button_header_args', array(
                        'content' => '<div class="mo-field-header">' . __("Call-to-action Button", 'mailoptin') . '</div>',
                        'block_class' => 'mo-field-header-wrapper',
                        'section' => $this->customizerClassInstance->fields_section_id,
                        'settings' => $this->option_prefix . '[cta_button_header]',
                        'priority' => 120,
                    )
                )
            );

            $field_controls_args['cta_button_action'] = apply_filters('mo_optin_form_customizer_cta_button_action_args', array(
                    'type' => 'select',
                    'description' => $cta_button_action_description,
                    'choices' => $cta_button_action_choices,
                    'label' => __('Action After Button Click', 'mailoptin'),
                    'section' => $this->customizerClassInstance->fields_section_id,
                    'settings' => $this->option_prefix . '[cta_button_action]',
                    'priority' => 125,
                )
            );

            $field_controls_args['cta_button_navigation_url'] = apply_filters('mo_optin_form_customizer_cta_button_navigation_url_args', array(
                    'type' => 'text',
                    'label' => __('Enter URL', 'mailoptin'),
                    'description' => __('URL should begin with http or https.', 'mailoptin'),
                    'section' => $this->customizerClassInstance->fields_section_id,
                    'settings' => $this->option_prefix . '[cta_button_navigation_url]',
                    'input_attrs' => ['placeholder' => 'https://'],
                    'priority' => 127,
                )
            );

            $field_controls_args['cta_button'] = apply_filters('mo_optin_form_customizer_cta_button_args',
                array(
                    'type' => 'text',
                    'label' => __('CTA Button', 'mailoptin'),
                    'section' => $this->customizerClassInstance->fields_section_id,
                    'settings' => $this->option_prefix . '[cta_button]',
                    'priority' => 130,
                    'description' => __('The value/label of the call-to-action button.', 'mailoptin'),
                )
            );

            $field_controls_args['cta_button_color'] = new \WP_Customize_Color_Control(
                $this->wp_customize,
                $this->option_prefix . '[cta_button_color]',
                apply_filters('mo_optin_form_customizer_cta_button_color_args', array(
                        'label' => __('CTA Button Color', 'mailoptin'),
                        'section' => $this->customizerClassInstance->fields_section_id,
                        'settings' => $this->option_prefix . '[cta_button_color]',
                        'priority' => 140,
                        'description' => __('The text color for the call-to-action button field.', 'mailoptin'),
                    )
                )
            );

            $field_controls_args['cta_button_background'] = new \WP_Customize_Color_Control(
                $this->wp_customize,
                $this->option_prefix . '[cta_button_background]',
                apply_filters('mo_optin_form_customizer_cta_button_background_args', array(
                        'label' => __('CTA Button Background', 'mailoptin'),
                        'section' => $this->customizerClassInstance->fields_section_id,
                        'settings' => $this->option_prefix . '[cta_button_background]',
                        'priority' => 150,
                        'description' => __('The background color of the call-to-action button.', 'mailoptin'),
                    )
                )
            );

            $field_controls_args['cta_button_font'] = new WP_Customize_Google_Font_Control(
                $this->wp_customize,
                $this->option_prefix . '[cta_button_font]',
                apply_filters('mo_optin_form_customizer_cta_button_font_args', array(
                        'label' => __('CTA Button Font'),
                        'section' => $this->customizerClassInstance->fields_section_id,
                        'settings' => $this->option_prefix . '[cta_button_font]',
                        'description' => __('The font family for the call-to-action button field.', 'mailoptin'),
                        'count' => 200,
                        'priority' => 160
                    )
                )
            );
        }

        do_action('mailoptin_before_fields_controls_addition');

        foreach ($field_controls_args as $id => $args) {
            if (is_object($args)) {
                $this->wp_customize->add_control($args);
            } else {
                $this->wp_customize->add_control($this->option_prefix . '[' . $id . ']', $args);
            }
        }

        do_action('mailoptin_after_fields_controls_addition');

    }


    public function configuration_controls()
    {
        $content_control_args = apply_filters(
            "mo_optin_form_customizer_configuration_controls",
            array(
                'campaign_title' => apply_filters('mo_optin_form_customizer_campaign_title_args', array(
                        'type' => 'text',
                        'label' => __('Optin Campaign Name', 'mailoptin'),
                        'section' => $this->customizerClassInstance->configuration_section_id,
                        'settings' => $this->option_prefix . '[campaign_title]',
                        'priority' => 10,
                    )
                ),
                'split_test_note' => apply_filters('mo_optin_form_customizer_split_test_note_args', array(
                        'type' => 'textarea',
                        'label' => __('Split Test Note', 'mailoptin'),
                        'section' => $this->customizerClassInstance->configuration_section_id,
                        'settings' => $this->option_prefix . '[split_test_note]',
                        'description' => __('Useful for keeping track of changes between each split test you create.', 'mailoptin'),
                        'priority' => 13,
                    )
                ),
                'inpost_form_optin_position' => apply_filters('mo_optin_form_customizer_inpost_form_optin_position_args', array(
                        'type' => 'select',
                        'label' => __('Optin Form Position', 'mailoptin'),
                        'section' => $this->customizerClassInstance->configuration_section_id,
                        'settings' => $this->option_prefix . '[inpost_form_optin_position]',
                        'description' => __('Select position within your post the optin form will be displayed.', 'mailoptin'),
                        'choices' => [
                            'before_content' => __('Before Content', 'mailoptin'),
                            'after_content' => __('After Content', 'mailoptin'),
                        ],
                        'priority' => 15,
                    )
                ),
                'slidein_position' => apply_filters('mo_optin_form_customizer_slidein_position_args', array(
                        'type' => 'select',
                        'choices' => ['bottom_right' => __('Bottom Right', 'mailoptin'), 'bottom_left' => __('Bottom Left', 'mailoptin')],
                        'label' => __('Slide-in Position', 'mailoptin'),
                        'section' => $this->customizerClassInstance->configuration_section_id,
                        'settings' => $this->option_prefix . '[slidein_position]',
                        'priority' => 20,
                    )
                ),
                'bar_position' => apply_filters('mo_optin_form_customizer_bar_position_args', array(
                        'type' => 'select',
                        'choices' => ['top' => __('Top', 'mailoptin'), 'bottom' => __('Bottom', 'mailoptin')],
                        'label' => __('Bar Position', 'mailoptin'),
                        'section' => $this->customizerClassInstance->configuration_section_id,
                        'settings' => $this->option_prefix . '[bar_position]',
                        'priority' => 30,
                    )
                ),
                'bar_sticky' => new WP_Customize_Toggle_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[bar_sticky]',
                    apply_filters('mo_optin_form_customizer_bar_sticky_args', array(
                            'label' => __('Sticky Bar?', 'mailoptin'),
                            'section' => $this->customizerClassInstance->configuration_section_id,
                            'settings' => $this->option_prefix . '[bar_sticky]',
                            'description' => __('Check to make bar sticky.', 'mailoptin'),
                            'type' => 'light',
                            'priority' => 40,
                        )
                    )
                ),
                'hide_close_button' => new WP_Customize_Toggle_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[hide_close_button]',
                    apply_filters('mo_optin_form_customizer_hide_close_button_args', array(
                            'label' => __('Hide Close Button', 'mailoptin'),
                            'section' => $this->customizerClassInstance->configuration_section_id,
                            'settings' => $this->option_prefix . '[hide_close_button]',
                            'type' => 'light',
                            'priority' => 45,
                        )
                    )
                ),
                'hide_headline' => new WP_Customize_Toggle_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[hide_headline]',
                    apply_filters('mo_optin_form_customizer_hide_headline_args', array(
                            'label' => __('Hide Headline', 'mailoptin'),
                            'section' => $this->customizerClassInstance->configuration_section_id,
                            'settings' => $this->option_prefix . '[hide_headline]',
                            'type' => 'light',
                            'priority' => 50,
                        )
                    )
                ),
                'hide_description' => new WP_Customize_Toggle_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[hide_description]',
                    apply_filters('mo_optin_form_customizer_hide_description_args', array(
                            'label' => __('Hide Description', 'mailoptin'),
                            'section' => $this->customizerClassInstance->configuration_section_id,
                            'settings' => $this->option_prefix . '[hide_description]',
                            'type' => 'light',
                            'priority' => 60,
                        )
                    )
                ),
                'hide_note' => new WP_Customize_Toggle_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[hide_note]',
                    apply_filters('mo_optin_form_customizer_hide_note_args', array(
                            'label' => __('Hide Note', 'mailoptin'),
                            'section' => $this->customizerClassInstance->configuration_section_id,
                            'settings' => $this->option_prefix . '[hide_note]',
                            'type' => 'light',
                            'priority' => 70,
                        )
                    )
                ),
                'cookie' => apply_filters('mo_optin_form_customizer_cookie_args', array(
                        'type' => 'text',
                        'label' => __('Cookie Duration', 'mailoptin'),
                        'section' => $this->customizerClassInstance->configuration_section_id,
                        'settings' => $this->option_prefix . '[cookie]',
                        'priority' => 80,
                        'description' => sprintf(
                            __('The length of time before this optin will display again to the user once they exit this campaign (defaults to 30 days). %sSet to 0 to prevent cookies from being set.%s', 'mailoptin'),
                            '<strong>', '</strong>'
                        )
                    )
                ),
                'success_cookie' => apply_filters('mo_optin_form_customizer_success_cookie_args', array(
                        'type' => 'text',
                        'label' => __('Success Cookie Duration', 'mailoptin'),
                        'section' => $this->customizerClassInstance->configuration_section_id,
                        'settings' => $this->option_prefix . '[success_cookie]',
                        'priority' => 90,
                        'description' => sprintf(
                            __('The length of time before the optin will display again to the user once they successfully opt in to this campaign (defaults to value of exit cookie above). %sSet to 0 to prevent cookies from being set.%s', 'mailoptin'),
                            '<strong>', '</strong>'
                        )
                    )
                ),
                'success_message' => apply_filters('mo_optin_form_customizer_success_message_args', array(
                        'type' => 'textarea',
                        'label' => __('Optin Success Message', 'mailoptin'),
                        'section' => $this->customizerClassInstance->configuration_section_id,
                        'settings' => $this->option_prefix . '[success_message]',
                        'priority' => 100,
                    )
                ),
                'remove_branding' => new WP_Customize_Toggle_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[remove_branding]',
                    apply_filters('mo_optin_form_customizer_remove_branding_args', array(
                            'label' => __('Remove MailOptin Branding', 'mailoptin'),
                            'description' => sprintf(
                                __('%sSet your affiliate link%s and make money with branding.', 'mailoptin'),
                                '<a href="' . MAILOPTIN_SETTINGS_SETTINGS_PAGE . '#mailoptin_affiliate_url_row" target="_blank">',
                                '</a>'
                            ),
                            'section' => $this->customizerClassInstance->configuration_section_id,
                            'settings' => $this->option_prefix . '[remove_branding]',
                            'type' => 'light',
                            'priority' => 110,
                        )
                    )
                )
            ),
            $this->wp_customize,
            $this->option_prefix,
            $this->customizerClassInstance
        );

        if (!OptinCampaignsRepository::is_split_test_variant($this->optin_campaign_id)) {
            unset($content_control_args['split_test_note']);
        }

        do_action('mailoptin_before_configuration_controls_addition');

        foreach ($content_control_args as $id => $args) {
            if (is_object($args)) {
                $this->wp_customize->add_control($args);
            } else {
                $this->wp_customize->add_control($this->option_prefix . '[' . $id . ']', $args);
            }
        }

        do_action('mailoptin_after_configuration_controls_addition');
    }

    public function integration_controls()
    {
        $email_providers = ConnectionsRepository::get_connections();

        $saved_email_provider = OptinCampaignsRepository::get_customizer_value($this->optin_campaign_id, 'connection_service');

        // prepend 'Select...' to the array of email list.
        // because select control will be hidden if no choice is found.
        $connection_email_list = ['' => __('Select...', 'mailoptin')] + ConnectionsRepository::connection_email_list($saved_email_provider);

        $integration_control_args = apply_filters(
            "mo_optin_form_customizer_integration_controls",
            array(
                'connection_service' => apply_filters('mo_optin_form_customizer_connection_service_args', array(
                        'type' => 'select',
                        'label' => __('Email Provider', 'mailoptin'),
                        'section' => $this->customizerClassInstance->integration_section_id,
                        'settings' => $this->option_prefix . '[connection_service]',
                        'choices' => $email_providers,
                        'priority' => 20,
                    )
                ),
                'connection_email_list' => apply_filters('mo_optin_form_customizer_connection_email_list_args', array(
                        'type' => 'select',
                        'label' => __('Email Provider List', 'mailoptin'),
                        'section' => $this->customizerClassInstance->integration_section_id,
                        'settings' => $this->option_prefix . '[connection_email_list]',
                        'choices' => $connection_email_list,
                        'priority' => 40,
                    )
                ),
                'ajax_nonce' => apply_filters('mo_optin_form_customizer_ajax_nonce_args', array(
                        'type' => 'hidden',
                        // simple hack because control won't render if label is empty.
                        'label' => '&nbsp;',
                        'section' => $this->customizerClassInstance->integration_section_id,
                        'settings' => $this->option_prefix . '[ajax_nonce]',
                        // 999 cos we want it to be bottom.
                        'priority' => 999,
                    )
                )
            ),
            $this->wp_customize,
            $this->option_prefix,
            $this->customizerClassInstance
        );

        do_action('mailoptin_before_integration_controls_addition');

        if (!defined('MAILOPTIN_DETACH_LIBSODIUM')) {
            $content = sprintf(
                '<div class="mo-pro"><a href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=integration_panel" target="_blank">%s</a></div>',
                __('Premium Version Available', 'mailoptin')
            );
            $content .= sprintf(
                __('Upgrade to %sMailOptin Premium%s for double-optin control, build hyper segmented list and increase automation possibilities via lead tagging (in MailChimp, via groups).', 'mailoptin'),
                '<a target="_blank" href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=integration_panel">',
                '</a>'
            );

            // always prefix with the name of the connect/connection service.
            $integration_control_args['after_integration_notice'] = new WP_Customize_Custom_Content(
                $this->wp_customize,
                $this->option_prefix . '[after_integration_notice]',
                apply_filters('mo_optin_form_customizer_after_integration_notice_args', array(
                        'content' => $content,
                        'section' => $this->customizerClassInstance->integration_section_id,
                        'settings' => $this->option_prefix . '[after_integration_notice]',
                        'priority' => 299,
                    )
                )
            );
        }

        foreach ($integration_control_args as $id => $args) {
            if (is_object($args)) {
                $this->wp_customize->add_control($args);
            } else {
                $this->wp_customize->add_control($this->option_prefix . '[' . $id . ']', $args);
            }
        }

        do_action('mailoptin_after_integration_controls_addition');
    }

    /**
     * @param CustomizerControls $instance
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param Customizer $customizerClassInstance
     */
    public function after_conversion_controls()
    {
        $success_message_config_link = sprintf(
            '<br>' . __("To customize success message, %sclick here%s.", 'mailoptin'),
            '<a onclick="wp.customize.control(\'mo_optin_campaign[' . $this->optin_campaign_id . '][success_message]\').focus()" href="#">',
            '</a>'
        );

        $success_control_choices = [
            'success_message' => __('Display success message.', 'mailoptin'),
            'close_optin' => __('Close optin', 'mailoptin'),
            'close_optin_reload_page' => __('Close optin and reload page', 'mailoptin'),
            'redirect_url' => __('Redirect to URL', 'mailoptin')
        ];

        if (in_array($this->customizerClassInstance->optin_campaign_type, ['inpost', 'sidebar'])) {
            unset($success_control_choices['close_optin']);
            unset($success_control_choices['close_optin_reload_page']);
        }

        $success_controls_args = apply_filters(
            "mo_optin_form_customizer_success_controls",
            array(
                'success_action' => apply_filters('mo_optin_form_customizer_success_action_args', array(
                        'type' => 'select',
                        'choices' => $success_control_choices,
                        'label' => __('Success Action', 'mailoptin'),
                        'section' => $this->customizerClassInstance->success_section_id,
                        'settings' => $this->option_prefix . '[success_action]',
                        'description' => __('What to do after users subscribe.', 'mailoptin') . $success_message_config_link,
                        'priority' => 10,
                    )
                ),
                'redirect_url_value' => apply_filters('mo_optin_form_customizer_redirect_url_value_args', array(
                        'type' => 'text',
                        'label' => __('Redirect URL', 'mailoptin'),
                        'section' => $this->customizerClassInstance->success_section_id,
                        'settings' => $this->option_prefix . '[redirect_url_value]',
                        'priority' => 20,
                        'description' => __('Specify a URL to redirect users to after opt-in. Must begin with http or https.', 'mailoptin')
                    )
                )
            ),
            $this->wp_customize,
            $this->option_prefix,
            $this->customizerClassInstance
        );

        if (!defined('MAILOPTIN_DETACH_LIBSODIUM')) {
            $content = sprintf(
                '<div class="mo-pro"><a href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=quick_setup_panel" target="_blank">%s</a></div>',
                __('Premium Version Available', 'mailoptin')
            );
            $content .= sprintf(
                __('Upgrade to %sMailOptin Premium%s to pass lead data to redirect URL, trigger success script after conversion and specify the state of optin form to subscribed users.', 'mailoptin'),
                '<a target="_blank" href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=quick_setup_panel">',
                '</a>'
            );

            // always prefix with the name of the connect/connection service.
            $success_controls_args['after_conversion_notice'] = new WP_Customize_Custom_Content(
                $this->wp_customize,
                $this->option_prefix . '[after_conversion_notice]',
                apply_filters('mo_optin_form_customizer_after_conversion_notice_args', array(
                        'content' => $content,
                        'section' => $this->customizerClassInstance->success_section_id,
                        'settings' => $this->option_prefix . '[after_conversion_notice]',
                        'priority' => 199,
                    )
                )
            );
        }

        do_action('mailoptin_before_success_controls_addition');

        foreach ($success_controls_args as $id => $args) {
            if (is_object($args)) {
                $this->wp_customize->add_control($args);
            } else {
                $this->wp_customize->add_control($this->option_prefix . '[' . $id . ']', $args);
            }
        }

        do_action('mailoptin_after_success_controls_addition');
    }

    /**
     * Controls for "(Quick) setup" display rules panel section.
     */
    public function setup_display_rule_controls()
    {
        $setup_display_control_args = [];

        if (defined('MAILOPTIN_DETACH_LIBSODIUM')) {

            $setup_display_control_args['load_optin_globally'] = new WP_Customize_Toggle_Control(
                $this->wp_customize,
                $this->option_prefix . '[load_optin_globally]',
                apply_filters('mo_optin_form_customizer_load_optin_globally_args', array(
                        'label' => __('Globally load optin', 'mailoptin'),
                        'section' => $this->customizerClassInstance->setup_display_rule_section_id,
                        'settings' => $this->option_prefix . '[load_optin_globally]',
                        'description' => __('The optin will be loaded on all pages of your website if checked.', 'mailoptin'),
                        'type' => 'light',
                        'priority' => 20
                    )
                )
            );

            $setup_display_control_args['who_see_optin'] = apply_filters('mo_optin_form_customizer_who_see_optin_args', array(
                    'type' => 'select',
                    'label' => __('Who should see this optin?', 'mailoptin'),
                    'section' => $this->customizerClassInstance->setup_display_rule_section_id,
                    'settings' => $this->option_prefix . '[who_see_optin]',
                    'description' => __('Decide who are able to see this optin.', 'mailoptin'),
                    'choices' => [
                        'show_all' => __('Show optin to all visitors and users', 'mailoptin'),
                        'show_logged_in' => __('Show optin to only logged-in users', 'mailoptin'),
                        'show_non_logged_in' => __('Show optin to only users not logged-in', 'mailoptin'),
                    ],
                    'priority' => 25,
                )
            );
        } else {
            $content = sprintf(
                '<div class="mo-pro"><a href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=quick_setup_panel" target="_blank">%s</a></div>',
                __('Premium Version Available', 'mailoptin')
            );

            $content .= sprintf(
                __('Upgrade to %sMailOptin Premium%s to get optin triggers such as %3$sExit Intent%4$s, %3$sPage views%4$s, %3$sTime on Site%4$s and %3$sScroll trigger%4$s as well as powerful display rules proven to boost conversions.', 'mailoptin'),
                '<a target="_blank" href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=quick_setup_panel">',
                '</a>',
                '<strong>',
                '</strong>'
            );

            // always prefix with the name of the connect/connection service.
            $setup_display_control_args['optin_trigger_notice'] = new WP_Customize_Custom_Content(
                $this->wp_customize,
                $this->option_prefix . '[optin_trigger_notice]',
                apply_filters('mo_optin_form_customizer_optin_trigger_notice_args', array(
                        'content' => $content,
                        'section' => $this->customizerClassInstance->setup_display_rule_section_id,
                        'settings' => $this->option_prefix . '[optin_trigger_notice]',
                        'priority' => 199,
                    )
                )
            );
        }

        $setup_display_control_args = apply_filters(
            "mo_optin_form_customizer_setup_display_rule_controls",
            $setup_display_control_args,
            $this->wp_customize,
            $this->option_prefix,
            $this->customizerClassInstance
        );

        do_action('mailoptin_before_setup_display_rule_controls_addition');

        foreach ($setup_display_control_args as $id => $args) {
            if (is_object($args)) {
                $this->wp_customize->add_control($args);
            } else {
                $this->wp_customize->add_control($this->option_prefix . '[' . $id . ']', $args);
            }
        }

        do_action('mailoptin_after_setup_display_rule_controls_addition');
    }

    /**
     * Page filter display rule.
     *
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param Customizer $customizerClassInstance
     */
    public function page_filter_display_rule_controls()
    {
        $page_filter_control_args = apply_filters(
            "mo_optin_form_customizer_page_filter_controls",
            array(
                'load_optin_index' => new WP_Customize_Toggle_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[load_optin_index]',
                    apply_filters('mo_optin_form_customizer_load_optin_index_args', array(
                            'label' => __('Front Page, Archive and Search Pages', 'mailoptin'),
                            'section' => $this->customizerClassInstance->page_filter_display_rule_section_id,
                            'settings' => $this->option_prefix . '[load_optin_index]',
                            'description' => __('Loads the optin on home front page, archive and search pages', 'mailoptin'),
                            'priority' => 30,
                            'type' => 'light',
                        )
                    )
                ),
                'exclusive_post_types_posts_load' => new WP_Customize_Chosen_Select_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[exclusive_post_types_posts_load]',
                    apply_filters('mo_optin_form_customizer_exclusive_post_types_posts_load_args', array(
                            'label' => __('Load optin exclusively on:'),
                            'section' => $this->customizerClassInstance->page_filter_display_rule_section_id,
                            'settings' => $this->option_prefix . '[exclusive_post_types_posts_load]',
                            'description' => __('Loads the optin only on the selected posts and pages.', 'mailoptin'),
                            'search_type' => 'exclusive_post_types_posts_load',
                            'choices' => ControlsHelpers::get_all_post_types_posts(),
                            'priority' => 35
                        )
                    )
                ),
                'post_categories_load' => new WP_Customize_Chosen_Select_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[post_categories_load]',
                    apply_filters('mo_optin_form_customizer_post_categories_load_args', array(
                            'label' => __('Load on post categories:'),
                            'section' => $this->customizerClassInstance->page_filter_display_rule_section_id,
                            'settings' => $this->option_prefix . '[post_categories_load]',
                            'description' => __('Loads the optin on posts that are in one of the selected categories.', 'mailoptin'),
                            'choices' => ControlsHelpers::get_categories(),
                            'priority' => 40
                        )
                    )
                ),
                'exclusive_post_types_load' => new WP_Customize_Chosen_Select_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[exclusive_post_types_load]',
                    apply_filters('mo_optin_form_customizer_exclusive_post_types_load_args', array(
                            'label' => __('Load optin only on "post types":'),
                            'section' => $this->customizerClassInstance->page_filter_display_rule_section_id,
                            'settings' => $this->option_prefix . '[exclusive_post_types_load]',
                            'description' => __('Loads the optin only on the selected "post types".', 'mailoptin'),
                            'choices' => ControlsHelpers::get_post_types(),
                            'priority' => 50
                        )
                    )
                ),
                'posts_never_load' => new WP_Customize_Chosen_Select_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[posts_never_load]',
                    apply_filters('mo_optin_form_customizer_posts_never_load_args', array(
                            'label' => __('Never load optin on these posts:'),
                            'section' => $this->customizerClassInstance->page_filter_display_rule_section_id,
                            'settings' => $this->option_prefix . '[posts_never_load]',
                            'description' => __('Select the posts this optin should never be loaded on.', 'mailoptin'),
                            'search_type' => 'posts_never_load',
                            'choices' => ControlsHelpers::get_post_type_posts('post'),
                            'priority' => 60
                        )
                    )
                ),
                'pages_never_load' => new WP_Customize_Chosen_Select_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[pages_never_load]',
                    apply_filters('mo_optin_form_customizer_pages_never_load_args', array(
                            'label' => __('Never load optin on these pages:'),
                            'section' => $this->customizerClassInstance->page_filter_display_rule_section_id,
                            'settings' => $this->option_prefix . '[pages_never_load]',
                            'description' => __('Select the pages this optin should never be loaded on.', 'mailoptin'),
                            'search_type' => 'pages_never_load',
                            'choices' => ControlsHelpers::get_post_type_posts('page'),
                            'priority' => 70
                        )
                    )
                ),
                'cpt_never_load' => new WP_Customize_Chosen_Select_Control(
                    $this->wp_customize,
                    $this->option_prefix . '[cpt_never_load]',
                    apply_filters('mo_optin_form_customizer_cpt_never_load_args', array(
                            'label' => __('Never load optin on these CPT posts:'),
                            'section' => $this->customizerClassInstance->page_filter_display_rule_section_id,
                            'settings' => $this->option_prefix . '[cpt_never_load]',
                            'description' => __('Select "custom post type" posts this optin should never be loaded on.', 'mailoptin'),
                            'search_type' => 'cpt_never_load',
                            'choices' => ControlsHelpers::get_all_post_types_posts(array('post', 'page')),
                            'priority' => 80
                        )
                    )
                ),
            ),
            $this->wp_customize,
            $this->option_prefix,
            $this->customizerClassInstance
        );

        do_action('mailoptin_before_page_filter_controls_addition');

        foreach ($page_filter_control_args as $id => $args) {
            if (is_object($args)) {
                $this->wp_customize->add_control($args);
            } else {
                $this->wp_customize->add_control($this->option_prefix . '[' . $id . ']', $args);
            }
        }

        do_action('mailoptin_after_page_filter_controls_addition');

    }
}