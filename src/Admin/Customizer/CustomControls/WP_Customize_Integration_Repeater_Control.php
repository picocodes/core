<?php

namespace MailOptin\Core\Admin\Customizer\CustomControls;

use MailOptin\Core\Repositories\ConnectionsRepository;
use MailOptin\Core\Repositories\OptinCampaignsRepository;
use WP_Customize_Control;

// WP_Customize_Control seem not to be defined during ajax call hence this.
if (!class_exists('WP_Customize_Control')) {
    require ABSPATH . WPINC . '/class-wp-customize-control.php';
}

class WP_Customize_Integration_Repeater_Control extends WP_Customize_Control
{
    public $type = 'mailoptin-integration';

    public $option_prefix;

    public $optin_campaign_id;

    public $customizerClassInstance;

    /**
     * Enqueue control related scripts/styles.
     *
     * @access public
     */
    public function enqueue()
    {
        add_action('customize_controls_print_footer_scripts', [$this, 'integration_template']);

        wp_enqueue_script('mailoptin-customizer-integrations', MAILOPTIN_ASSETS_URL . 'js/customizer-controls/integration-control/control.js', array('jquery', 'customize-base'), false, true);
        wp_enqueue_style('mailoptin-customizer-integrations', MAILOPTIN_ASSETS_URL . 'js/customizer-controls/integration-control/style.css', null);

        // toggle control assets
        wp_enqueue_script('mo-customizer-toggle-control', MAILOPTIN_ASSETS_URL . 'js/customizer-controls/toggle-control/customizer-toggle-control.js', array('jquery'), false, true);
        wp_enqueue_style('mo-pure-css-toggle-buttons', MAILOPTIN_ASSETS_URL . 'js/customizer-controls/toggle-control/pure-css-togle-buttons.css', array(), false);

        $css = '
			.disabled-control-title {
				color: #a0a5aa;
			}
			input[type=checkbox].tgl-light:checked + .tgl-btn {
				background: #0085ba;
			}
			input[type=checkbox].tgl-light + .tgl-btn {
			  background: #a0a5aa;
			}
			input[type=checkbox].tgl-light + .tgl-btn:after {
			  background: #f7f7f7;
			}

			input[type=checkbox].tgl-ios:checked + .tgl-btn {
			  background: #0085ba;
			}

			input[type=checkbox].tgl-flat:checked + .tgl-btn {
			  border: 4px solid #0085ba;
			}
			input[type=checkbox].tgl-flat:checked + .tgl-btn:after {
			  background: #0085ba;
			}

		';
        wp_add_inline_style('mo-pure-css-toggle-buttons', $css);

        // color field
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');

        do_action('mo_optin_integration_control_enqueue');
    }

    public function integration_template()
    {
        ?>
        <script type="text/html" id="tmpl-mo-integration-js-template">
            <?php $this->template(); ?>
        </script>
        <?php
    }

    public static function text_field($name, $class = '', $label = '', $description = '', $type = 'text')
    {
        $type = empty($type) ? 'text' : $type;

        $saved_value = '';
        $default = '';

        if (!empty($class)) {
            $class = " $class";
        }

        $random_id = wp_generate_password(5, false);
        echo "<div class=\"$name mo-integration-block{$class}\">";
        if (!empty($label)) : ?>
            <label for="<?php echo $random_id; ?>" class="customize-control-title"><?php echo esc_html($label); ?></label>
        <?php endif; ?>
        <?php if (!empty($description)) : ?>
        <span class="description customize-control-description"><?php echo $description; ?></span>
    <?php endif; ?>
        <input
                id="<?php echo $random_id; ?>"
                type="<?php echo esc_attr($type); ?>"
                name="<?php echo $name; ?>"
                value="<?php echo esc_attr($saved_value); ?>"
        />
        <?php
        echo '</div>';
    }

    public static function select_field($name, $choices, $class = '', $label = '', $description = '')
    {
        $saved_value = '';
        $default = '';

        if (empty($choices)) {
            return;
        }

        $random_id = wp_generate_password(5, false);

        if (!empty($class)) {
            $class = " $class";
        }

        echo "<div class=\"$name mo-integration-block{$class}\">";
        if (!empty($label)) : ?>
            <label for="<?php echo $random_id ?>" class="customize-control-title"><?php echo esc_html($label); ?></label>
        <?php endif; ?>
        <select id="<?php echo $random_id ?>" class="mo-optin-integration-field" name="<?php echo $name ?>">
            <?php
            foreach ($choices as $value => $label) {
                echo '<option value="' . esc_attr($value) . '"' . selected($saved_value, $value, false) . '>' . $label . '</option>';
            }
            ?>
        </select>
        <?php if (!empty($description)) : ?>
        <span class="description customize-control-description"><?php echo $description; ?></span>
    <?php endif; ?>
        </div>
        <?php
    }

    public static function mc_group_select($name, $choices, $class = '')
    {
        $saved_value = '';
        $default = '';

        if (!empty($class)) {
            $class = " $class";
        }

        echo "<div class=\"$name mo-integration-block{$class}\">";

        if (empty($choices)) {
            echo '<div style="background:#000000;color:#fff;padding:10px;font-size:14px;">' . __('No MailChimp group found. Try selecting another email list.', 'mailoptin') . '</div>';
            return;
        }

        foreach ($choices as $choice) : ?>
            <div>
                <span class="customize-control-title"><?= $choice['title']; ?></span>
                <?php foreach ($choice['interests'] as $interests) : ?>
                    <div>
                        <label>
                            <input type="checkbox" class="mo_mc_interest" name="<?= $name; ?>[]" value="<?= $interests['id']; ?>" <?php if (is_array($saved_value) && in_array($interests['id'], $saved_value)) {
                                echo 'checked="checked"';
                            } ?>
                            >
                            <?= $interests['name']; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach;
        echo '</div>';
    }

    public static function color_field($name, $class = '', $label = '', $description = '')
    {
        $default = '';
        $save_value = '';

        $defaultValue = '#RRGGBB';
        $defaultValueAttr = '';

        if (!empty($class)) {
            $class = " $class";
        }

        if ($default && is_string($default)) {
            if ('#' !== substr($default, 0, 1)) {
                $defaultValue = '#' . $default;
            } else {
                $defaultValue = $default;
            }
            $defaultValueAttr = " data-default-color=\"$defaultValue\""; // Quotes added automatically.
        }

        echo "<div class=\"$name mo-integration-block{$class}\">";
        if ($label) {
            echo '<span class="customize-control-title">' . $label . '</span>';
        }

        echo '<div class="customize-control-content">';
        echo '<label><span class="screen-reader-text">' . $label . '</span>';

        echo '<input name="' . $name . '" class="mo-color-picker-hex" type="text" maxlength="7" placeholder="' . $defaultValue . '"' . $defaultValueAttr . '/>';
        echo '</label>';
        echo '</div>';
        if ($description) {
            echo '<span class="description customize-control-description">' . $description . '</span>';
        }
        echo '</div>';
    }

    public static function font_fields($name, $class = '', $label = '', $description = '', $count = 40)
    {
        $count = empty($count) ? 40 : $count;

        $default = '';
        $save_value = '';

        if (!empty($class)) {
            $class = " $class";
        }

        $fonts = WP_Customize_Google_Font_Control::get_fonts($count);
        echo "<div class=\"$name mo-integration-block{$class}\">";
        if (!empty($fonts)) {
            ?>
            <label>
                <span class="customize-control-title"><?php echo esc_html($label); ?></span>
                <select name="<?= $name; ?>">
                    <?php
                    foreach ($fonts as $v) {
                        $option_value = str_replace(' ', '+', $v);
                        printf('<option value="%s" %s>%s</option>', $option_value, selected($save_value, $option_value, false), $v);
                    }
                    ?>
                </select>
                <?php if (!empty($description)) : ?>
                    <span class="description customize-control-description"><?php echo $description; ?></span>
                <?php endif; ?>
            </label>
            <?php
        }
        echo '</div>';
    }

    public static function toggle_field($name, $class = '', $label = '', $description = '')
    {
        $saved_value = '';


        if (!empty($class)) {
            $class = " $class";
        }

        $random_id = wp_generate_password(5, false);
        ?>
    <div class="<?= $name; ?> mo-integration-block<?= $class; ?>">
        <div style="display:flex;flex-direction: row;justify-content: flex-start;">
            <span class="customize-control-title" style="flex: 2 0 0; vertical-align: middle;"><?php echo $label; ?></span>
            <input name="<?= $name; ?>" id="<?php echo $random_id ?>" type="checkbox" class="tgl tgl-light" value="<?php echo esc_attr($saved_value); ?>" <?php checked($saved_value); ?> />
            <label for="<?php echo $random_id ?>" class="tgl-btn"></label>
        </div>
        <?php if (!empty($description)) : ?>
        <span class="description customize-control-description"><?php echo $description; ?></span>
        </div>
    <?php endif;
    }

    public function parse_control($control_args)
    {
        if (!is_array($control_args) || empty($control_args)) return;

        foreach ($control_args as $key => $control_arg) {
            switch ($control_arg['field']) {
                case 'text':
                    self::text_field(
                        @$control_arg['name'],
                        @$control_arg['class'],
                        @$control_arg['label'],
                        @$control_arg['description'],
                        @$control_arg['type']
                    );
                    break;
                case 'select':
                    self::select_field(
                        @$control_arg['name'],
                        @$control_arg['choices'],
                        @$control_arg['class'],
                        @$control_arg['label'],
                        @$control_arg['description']
                    );
                    break;
                case 'mc_group_select':
                    self::mc_group_select(
                        @$control_arg['name'],
                        @$control_arg['choices'],
                        @$control_arg['class']
                    );
                    break;
                case 'color':
                    self::color_field(
                        @$control_arg['name'],
                        @$control_arg['class'],
                        @$control_arg['label'],
                        @$control_arg['description']
                    );
                    break;
                case 'font':
                    self::font_fields(
                        @$control_arg['name'],
                        @$control_arg['class'],
                        @$control_arg['label'],
                        @$control_arg['description'],
                        @$control_arg['count']
                    );
                    break;
                case 'toggle':
                    self::toggle_field(
                        @$control_arg['name'],
                        @$control_arg['class'],
                        @$control_arg['label'],
                        @$control_arg['description']
                    );
                    break;
                case 'custom_content':
                    echo $control_arg['content'];
                    break;
            }
        }
    }

    public function template($index = 0)
    {
        $saved_values = $this->value();
        $default = $this->setting->default;

        $email_providers = ConnectionsRepository::get_connections();

        $saved_email_provider = OptinCampaignsRepository::get_customizer_value($this->optin_campaign_id, 'connection_service');

        // prepend 'Select...' to the array of email list.
        // because select control will be hidden if no choice is found.
        $connection_email_list = ['' => __('Select...', 'mailoptin')] + ConnectionsRepository::connection_email_list($saved_email_provider);
        ?>
        <div class="mo-integration-widget mo-integration-part-widget" data-integration-index="<?= $index; ?>">
            <div class="mo-integration-widget-top mo-integration-part-widget-top ui-sortable-handle">
                <div class="mo-integration-part-widget-title-action">
                    <button type="button" class="mo-integration-widget-action">
                        <span class="toggle-indicator"></span>
                    </button>
                </div>
                <div class="mo-integration-widget-title">
                    <h3><?php _e('New Integration', 'mailoptin') ?></h3>
                </div>
            </div>
            <div class="mo-integration-widget-content">
                <div class="mo-integration-widget-form">
                    <?php $this->parse_control(apply_filters('mo_optin_integrations_controls_before', [], $this->optin_campaign_id)); ?>
                    <?php self::select_field('connection_service', $email_providers, '', __('Email Provider', 'mailoptin')); ?>
                    <?php self::select_field('connection_email_list', $connection_email_list, '', __('Email Provider List', 'mailoptin')); ?>
                    <?php $this->parse_control(apply_filters('mo_optin_integrations_controls_after', [], $this->optin_campaign_id)); ?>
                </div>
                <div class="mo-integration-widget-actions">
                    <a href="#" class="mo-integration-delete"><?php _e('Delete', 'mailoptin'); ?></a>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_content()
    {
        $this->template();
        ?>
        <div class="mo-integration__add_new">
            <button type="button" class="button mo-add-new-integration">
                <?php _e('Add Another Integration', 'mailoptin') ?>
            </button>
        </div>
        <input class="mo-integrations-save-field" id="<?= '_customize-input-' . $this->id; ?>" type="hidden" value="<?php esc_attr_e($this->value()); ?>" <?php $this->link(); ?>/>
        <?php
    }
}