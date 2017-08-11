<?php

namespace MailOptin\Core\Admin\Customizer\CustomControls;

use WP_Customize_Control;

class WP_Customize_Email_Schedule_Time_Fields_Control extends WP_Customize_Control
{
    /**
     * Choices/options for the select dropdown.
     *
     * @var array
     */
    public $select_choices = array();

    /**
     * HTML Attributes to add to the <select> tag
     *
     * @var array
     */
    public $select_attrs = array();

    public $type = 'email_campaign_schedule';

    public function select_attrs()
    {
        foreach ($this->select_attrs as $attr => $value) {
            echo $attr . '="' . esc_attr($value) . '" ';
        }
    }

    public function render_content()
    {
        ?>
        <label>
            <?php if (!empty($this->label)) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <input type="<?php echo esc_attr($this->type); ?>" <?php $this->input_attrs(); ?> value="<?php echo esc_attr($this->value('schedule_digit')); ?>" <?php $this->link('schedule_digit'); ?> />
            <select <?php $this->link('schedule_type'); ?> <?php $this->select_attrs(); ?> >
                <?php
                foreach ($this->select_choices as $value => $label)
                    echo '<option value="' . esc_attr($value) . '"' . selected($this->value('schedule_type'), $value, false) . '>' . $label . '</option>';
                ?>
            </select>
        </label>

        <?php if (!empty($this->description)) : ?>
        <span class="description customize-control-description"><?php echo $this->description; ?></span>
    <?php endif;
    }
}