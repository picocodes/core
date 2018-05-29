<?php
/**
 * Input fields with bottom sub description below field.
 */

namespace MailOptin\Core\Admin\Customizer\CustomControls;

use MailOptin\Core\Admin\Customizer\OptinForm\Customizer;
use MailOptin\Core\Repositories\ConnectionsRepository;
use WP_Customize_Control;

class WP_Customize_Integration_Repeater_Control extends WP_Customize_Control
{
    public $type = 'mailoptin-integration';

    public $connections_control;

    public $connection_email_list;

    public $option_prefix;

    public $customizerClassInstance;

    /**
     * Enqueue control related scripts/styles.
     *
     * @access public
     */
    public function enqueue()
    {
        wp_enqueue_script('mailoptin-customizer-integrations', MAILOPTIN_ASSETS_URL . 'js/customizer-controls/integration-control/control.js', array('jquery', 'customize-base'), false, true);
        wp_enqueue_style('mailoptin-customizer-integrations', MAILOPTIN_ASSETS_URL . 'js/customizer-controls/integration-control/style.css', null);
    }

    public function sanitize_control($control)
    {
        return preg_replace(
            ['/<li\s([^<>]+)?>([\S\s]+(?:data-customize-setting-link="(.+)")?[\S\s]+)<\/li>/'],
            ['<p>$2</p>'],
            $control
        );
    }

    /**
     * @param array $control
     */
    public function parse_control_object($controls = [])
    {
        if (!is_array($controls)) return;

        /** @var WP_Customize_Control $control */
        foreach ($controls as $control) {
            echo $this->sanitize_control($control->get_content());
        }
    }

    public function template()
    {
        $before_core_controls = apply_filters(
            'mo_optin_integrations_controls_before',
            [],
            $this->manager,
            $this->option_prefix,
            $this->customizerClassInstance
        );

        $after_core_controls = apply_filters(
            'mo_optin_integrations_controls_after',
            [],
            $this->manager,
            $this->option_prefix,
            $this->customizerClassInstance
        );
        ?>
        <div class="mo-integration-widget mo-integration-part-widget">
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
                    <?php $this->parse_control_object($before_core_controls); ?>
                    <?php echo $this->sanitize_control($this->connections_control); ?>
                    <?php echo $this->sanitize_control($this->connection_email_list); ?>
                    <?php $this->parse_control_object($after_core_controls); ?>
                </div>
                <div class="mo-integration-widget-actions">
                    <a href="#" class="mo-integration-form-part-remove">Delete</a>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_content()
    {
        $this->template();
        ?>
        <!--        <label>-->
        <!--            --><?php //if (!empty($this->label)) :
        ?>
        <!--                <span class="customize-control-title">--><?php //echo esc_html($this->label);
        ?><!--</span>-->
        <!--            --><?php //endif;
//            if (!empty($this->description)) :
        ?>
        <!--                <span class="description customize-control-description">--><?php //echo $this->description;
        ?><!--</span>-->
        <!--            --><?php //endif;
        ?>
        <!--            <input type="--><?php //echo esc_attr($this->input_type);
        ?><!--" --><?php //$this->input_attrs();
        ?><!-- value="--><?php //echo esc_attr($this->value());
        ?><!--" --><?php //$this->link();
        ?><!-- />-->
        <!--            --><?php //if (!empty($this->sub_description)) :
        ?>
        <!--                <span class="description customize-control-description">--><?php //echo $this->sub_description;
        ?><!--</span>-->
        <!--            --><?php //endif;
        ?>
        <!--        </label>-->
        <div class="mo-integration__add_new">
            <button type="button" class="button">
                <?php _e('Add Another Integration', 'mailoptin') ?>
            </button>
        </div>
        <?php
    }
}