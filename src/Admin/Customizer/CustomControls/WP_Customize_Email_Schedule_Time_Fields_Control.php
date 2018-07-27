<?php

namespace MailOptin\Core\Admin\Customizer\CustomControls;

use MailOptin\Core\Repositories\EmailCampaignRepository;
use WP_Customize_Control;

class WP_Customize_Email_Schedule_Time_Fields_Control extends WP_Customize_Control
{
    public $type = 'mailoptin_email_campaign_schedule';

    public $format = EmailCampaignRepository::NEW_PUBLISH_POST;

    public function select_attrs($select_attrs)
    {
        foreach ($select_attrs as $attr => $value) {
            echo $attr . '="' . esc_attr($value) . '" ';
        }
    }

    public function mo_input_attrs($input_attrs)
    {
        foreach ($input_attrs as $attr => $value) {
            echo $attr . '="' . esc_attr($value) . '" ';
        }
    }

    public function new_publish_post_format()
    {
        if ($this->format !== EmailCampaignRepository::NEW_PUBLISH_POST) return;

        $settings = array_keys($this->settings);

        $select_choices = [
            'minutes' => __('Minutes', 'mailoptin'),
            'hours' => __('Hours', 'mailoptin'),
            'days' => __('Days', 'mailoptin'),
        ];

        $input_attrs = [
            'size' => 2,
            'maxlength' => 2,
            'style' => 'width:auto',
            'pattern' => '([0-9]){2}'
        ];

        $select_attrs = ['style' => 'width:auto'];
        ?>
        <div>
            <input type="text" <?php echo $this->mo_input_attrs($input_attrs); ?> value="<?php echo esc_attr($this->value($settings[0])); ?>" <?php $this->link($settings[0]); ?> />
            <select <?php $this->link($settings[1]); ?> <?php echo $this->select_attrs($select_attrs); ?> >
                <?php
                foreach ($select_choices as $value => $label)
                    echo '<option value="' . esc_attr($value) . '"' . selected($this->value($settings[1]), $value, false) . '>' . $label . '</option>';
                ?>
            </select>
            <span style="display: inline-block" class="description customize-control-description"><?php _e('after publishing', 'mailoptin'); ?></span>
        </div>
        <?php
    }

    public function email_digest_format()
    {
        if ($this->format !== EmailCampaignRepository::POSTS_EMAIL_DIGEST) return;

        $settings = array_keys($this->settings);
        $schedule_interval_choices = [
            'every_day' => __('Every Day', 'mailoptin'),
            'every_week' => __('Every Week', 'mailoptin'),
            'every_month' => __('Every Month', 'mailoptin'),
        ];
        ?>
        <select <?php $this->link($settings[0]); ?>>
            <?php
            foreach ($schedule_interval_choices as $value => $label)
                echo '<option value="' . esc_attr($value) . '"' . selected($this->value($settings[0]), $value, false) . '>' . $label . '</option>';
            ?>
        </select>
        <?php
    }

    public function render_content()
    {
        ?>
        <label>
            <?php if (!empty($this->label)) : ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <?php $this->new_publish_post_format(); ?>
            <?php $this->email_digest_format(); ?>
        </label>

        <?php if (!empty($this->description)) : ?>
        <span class="description customize-control-description"><?php echo $this->description; ?></span>
    <?php endif;
    }
}