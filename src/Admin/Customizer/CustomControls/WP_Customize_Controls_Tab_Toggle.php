<?php

namespace MailOptin\Core\Admin\Customizer\CustomControls;

use WP_Customize_Control;

class WP_Customize_Controls_Tab_Toggle extends WP_Customize_Control
{
    public $type = 'mailoptin_tab_toggle';

    public $choices = [];

    /**
     * Enqueue scripts/styles.
     *
     * @since 3.4.0
     */
    public function enqueue()
    {
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
    }

    public function render_content()
    {
        $tabs = [
            'general' => __('General', 'mailoptin'),
            'style' => __('Style', 'mailoptin'),
            'advance' => __('Advance', 'mailoptin'),
        ];
        echo '<div class="mailoptin-toggle-control-tab">';
        foreach ($tabs as $key => $title) {
            $name = '_customize-radio-' . $this->id;
            $dashicon = '<span class="dashicons dashicons-admin-settings"></span>';
            ?>
            <input
                    id="<?= $this->type . '_' . $key; ?>"
                    type="radio"
                    name="<?= $name; ?>"
                    style="display: none"
                    value="<?php echo $key ?>"
                <?php $this->link();
                checked($this->value(), $key); ?>
            />
            <div class="mo-toggle-tab-wrapper">
                <label for="<?= $this->type . '_' . $key; ?>" class="mo-single-toggle-tab mo-general">
                    <?= $dashicon; ?>
                    <?= $title; ?>
                </label>
            </div>
            <?php
        }
        echo '</div>';
    }
}
