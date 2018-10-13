<?php

namespace MailOptin\Core\Admin\SettingsPage;

use MailOptin\Libsodium\EDD_SL_Plugin_Updater;
use WP_Error;

class LicenseControl
{
    /**
     * @var string license key
     */
    protected $license_key;

    /**
     * @var string plugin name
     */
    protected $item_name = null;

    /**
     * @var string plugin EDD ID
     */
    protected $item_id = null;

    /**
     * @var string store URL
     */
    protected $store_url = 'https://my.mailoptin.io';

    /**
     * @var string current plugin version number
     */
    protected $version_number;

    /**
     * @var string name of product / download author
     */
    protected $plugin_author = 'MailOptin';

    /**
     * @var string the plugin main file system file path (__FILE__).
     */
    protected $file_path;

    public function __construct($license_key, $file_path, $version_number, $item_id, $item_name = '', $plugin_author = '', $store_url = '')
    {
        $this->license_key = $license_key;
        $this->file_path   = $file_path;
        if ( ! empty($store_url)) {
            $this->store_url = $store_url;
        }
        $this->version_number = $version_number;
        $this->item_name      = $item_name;
        $this->item_id        = absint($item_id);

        if ( ! empty($plugin_author)) {
            $this->plugin_author = $plugin_author;
        }
    }

    /**
     * Activate License key
     *
     * @param string $license_key
     *
     * @return mixed
     */
    public function activate_license($license_key = '')
    {
        // data to send in our API request
        $api_params = array(
            'edd_action' => 'activate_license',
            'license'    => ! empty($license_key) ? $license_key : $this->license_key,
            'item_name'  => urlencode($this->item_name), // the name of our product in EDD
            'item_id'    => $this->item_id,
            'url'        => home_url()
        );

        if (isset($this->item_id) && ! empty($this->item_id)) {
            unset($api_params['item_name']);
        }

        // Call the custom API.
        $response = wp_remote_post($this->store_url, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        // make sure the response came back okay
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

            if (is_wp_error($response)) {
                $error = $response->get_error_message();
            } else {
                $error = __('License validation error. Please try again', 'mailoptin');
            }

        } else {

            $license_data = json_decode(wp_remote_retrieve_body($response));

            if (false === $license_data->success) {

                switch ($license_data->error) {
                    case 'expired' :
                        $error = sprintf(
                            __('Your license key expired on %s.'),
                            date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                        );
                        break;
                    case 'revoked' :
                        $error = __('Your license key has been disabled.');
                        break;
                    case 'missing' :
                        $error = __('Invalid license.');
                        break;
                    case 'invalid' :
                    case 'site_inactive' :
                        $error = __('Your license is not active for this URL.');
                        break;
                    case 'item_name_mismatch' :
                        $error = sprintf(__('This appears to be an invalid license key for %s.'), $this->item_name);
                        break;
                    case 'no_activations_left':
                        $error = __('Your license key has reached its activation limit.');
                        break;
                    default :
                        $error = __('An error occurred, please try again.');
                        break;
                }

            }

        }

        if (isset($error) && ! empty($error)) {
            return new WP_Error('license_error', $error);
        }

        return $license_data;
    }

    /**
     * Plugin update method
     */
    public function plugin_updater()
    {
        // retrieve our license key from the DB
        $license_key = trim($this->license_key);

        // setup the updater
        return new EDD_SL_Plugin_Updater(
            $this->store_url,
            $this->file_path,
            array(
                'version'   => $this->version_number,
                'license'   => $license_key,
                'item_name' => $this->item_name,
                'item_id'   => $this->item_id,
                'author'    => $this->plugin_author
            )
        );

    }


    /**
     * Deactivate license
     */
    public function deactivate_license()
    {
        $license = $this->license_key;

        // data to send in our API request
        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license'    => $license,
            'item_name'  => urlencode($this->item_name),
            'item_id'    => $this->item_id,
            'url'        => home_url()
        );

        // Call the custom API.
        $response = wp_remote_post($this->store_url, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        // make sure the response came back okay
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            if (is_wp_error($response)) {
                $error = $response->get_error_message();
            } else {
                $error = __('An error occurred, please try again.');
            }

            return new WP_Error('deactivate_error', $error);
        }


        // decode the license data
        $license_data = json_decode(wp_remote_retrieve_body($response));

        return $license_data;
    }

    public function check_license()
    {
        $api_params = array(
            'edd_action' => 'check_license',
            'license'    => $this->license_key,
            'item_name'  => urlencode($this->item_name),
            'item_id'    => $this->item_id,
            'url'        => home_url()
        );

        // Call the custom API.
        $response = wp_remote_post($this->store_url, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        // make sure the response came back okay
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            if (is_wp_error($response)) {
                $error = $response->get_error_message();
            } else {
                $error = __('An error occurred, please try again.');
            }

            return new WP_Error('license_check_error', $error);
        }

        $license_data = json_decode(wp_remote_retrieve_body($response));

        return $license_data;
    }

    /**
     * @return LicenseControl
     */
    public static function get_instance($license_key = '')
    {
        return new LicenseControl(
            $license_key,
            MAILOPTIN_SYSTEM_FILE_PATH,
            MAILOPTIN_VERSION_NUMBER,
            EDD_MO_ITEM_ID
        );
    }
}