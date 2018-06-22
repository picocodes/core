<?php

namespace MailOptin\Core\Admin\SettingsPage\LiteToPremiumUpgrade;


class UpgraderSkin extends \Plugin_Upgrader_Skin
{
    public function after()
    {
        $this->plugin = $this->upgrader->plugin_info();
        if (!empty($this->plugin) && !is_wp_error($this->result) && $this->plugin_active) {
            // Currently used only when JS is off for a single plugin update?
            echo '<iframe title="' . esc_attr__('Update progress') . '" style="border:0;overflow:hidden" width="100%" height="170" src="' . wp_nonce_url('update.php?action=activate-plugin&networkwide=' . $this->plugin_network_active . '&plugin=' . urlencode($this->plugin), 'activate-plugin_' . $this->plugin) . '"></iframe>';
        }

        $update_actions = array(
            'activate_plugin' => '<a href="' . wp_nonce_url('plugins.php?action=activate&amp;plugin=' . urlencode($this->plugin), 'activate-plugin_' . $this->plugin) . '" target="_parent">' . __('Activate Plugin') . '</a>',
            'plugins_page' => '<a href="' . MAILOPTIN_SETTINGS_SETTINGS_PAGE . '" target="_parent">' . __('Return to MailOptin Settings', 'mailoptin') . '</a>'
        );

        if ($this->plugin_active || !$this->result || is_wp_error($this->result) || !current_user_can('activate_plugin', $this->plugin))
            unset($update_actions['activate_plugin']);

        if (!empty($update_actions))
            $this->feedback(implode(' | ', (array)$update_actions));
    }
}