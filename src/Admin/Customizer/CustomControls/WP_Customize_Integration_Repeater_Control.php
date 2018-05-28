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

    public function template()
    {
        ?>
        <div class="mo-integration-widget mo-integration-part-widget">
            <div class="mo-integration-widget-top mo-integration-part-widget-top ui-sortable-handle">
                <div class="mo-integration-part-widget-title-action">
                    <button type="button" class="mo-integration-widget-action">
                        <span class="toggle-indicator"></span>
                    </button>
                </div>
                <div class="mo-integration-widget-title">
                    <h3>Short Text<span class="in-widget-title">: <span>First name</span></span></h3>
                </div>
            </div>
            <div class="mo-integration-widget-content">
                <div class="mo-integration-widget-form">
                    <?php echo $this->connections_control; ?>
                    <p>
                        <label for="single_line_text_0_title">Title</label>
                        <input type="text" id="single_line_text_0_title" class="widefat title" value="First name">
                    </p>
                    <p>
                        <label for="single_line_text_0_label_placement">Title placement</label>
                        <select id="single_line_text_0_label_placement" data-bind="label_placement">
                            <option value="above" selected="">Above</option>
                        </select>
                    </p>
                    <p>
                        <label for="single_line_text_0_description">Description</label>
                        <textarea id="single_line_text_0_description" data-bind="description"></textarea>
                    </p>
                    <p>
                        <label for="single_line_text_0_placeholder">Placeholder</label>
                        <input type="text" id="single_line_text_0_placeholder" class="widefat title" value="" data-bind="placeholder">
                    </p>
                    <p>
                        <label>
                            <input type="checkbox" class="checkbox" value="1" checked="checked" data-bind="required"> This is a required field
                        </label>
                    </p>
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