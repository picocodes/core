<?php

namespace MailOptin\Core\Connections;

use MailOptin\Core\EmailCampaigns\TemplateTrait;
use MailOptin\Core\Repositories\EmailCampaignRepository;

abstract class AbstractConnect
{
    use TemplateTrait;

    const EMAIL_MARKETING_TYPE = 'emailmarketing';
    const SOCIAL_TYPE = 'social';
    const CRM_TYPE = 'crm';
    const OTHER_TYPE = 'other';
    const ANALYTICS_TYPE = 'analytics';

    public function __construct()
    {
        add_action('customize_controls_print_footer_scripts', [$this, 'js_script']);
    }

    /**
     * Change the label from "Email Provider List" to "ConvertKit Form" on convertkit selected as email provider.
     */
    public function js_script()
    {
        $ck_label = __('ConvertKit Form', 'mailoptin');
        $drip_label = __('Drip Campaign', 'mailoptin');
        $gr_label = __('GetResponse Campaign', 'mailoptin');
        $default_label = __('Email Provider List', 'mailoptin');
        ?>

        <script type="text/javascript">
            (function ($) {
                function logic(connection_service) {
                    if (connection_service === undefined) {
                        connection_service = $("select[data-customize-setting-link*='connection_service']").val();
                    }

                    if (connection_service === 'GetResponseConnect') {
                        $('li[id*="connection_email_list"] .customize-control-title').text('<?php echo $gr_label; ?>');
                    }

                    if (connection_service === 'ConvertKitConnect') {
                        $('li[id*="connection_email_list"] .customize-control-title').text('<?php echo $ck_label; ?>');
                    }

                    if (connection_service === 'DripConnect') {
                        $('li[id*="connection_email_list"] .customize-control-title').text('<?php echo $drip_label; ?>');
                    }
                }

                // on ready event
                $(function () {
                    logic();
                    $(document.body).on('mo_email_list_data_found', function (e, connection_service) {
                        // restore default label before change
                        $('li[id*="connection_email_list"] .customize-control-title').text('<?php echo $default_label; ?>');
                        logic(connection_service);
                    });
                })
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Helper to return success error.
     *
     * @return array
     */
    public static function ajax_success()
    {
        return ['success' => true];
    }

    /**
     * Check if HTTP status code is not successful.
     *
     * @param int $code
     * @return bool
     */
    public static function is_http_code_not_success($code)
    {
        $code = absint($code);
        return $code < 200 || $code > 299;
    }

    /**
     * Check if HTTP status code is successful.
     *
     * @param int $code
     * @return bool
     */
    public static function is_http_code_success($code)
    {
        $code = absint($code);
        return $code >= 200 && $code <= 299;
    }

    /**
     * Helper to return failed error.
     *
     * @param string $error
     *
     * @return array
     */
    public static function ajax_failure($error)
    {
        return ['success' => false, 'message' => $error];
    }

    /**
     * Save error log.
     *
     * @param string $message
     * @param int $campaign_log_id
     * @param int $email_campaign_id
     */
    public static function save_campaign_error_log($message, $campaign_log_id, $email_campaign_id)
    {
        if (!isset($message) || !isset($campaign_log_id) || !isset($email_campaign_id)) {
            return;
        }

        $email_campaign_name = EmailCampaignRepository::get_email_campaign_name($email_campaign_id);
        $filename = md5($email_campaign_name . $campaign_log_id);


        $error_log_folder = MAILOPTIN_CAMPAIGN_ERROR_LOG;

        // does bugs folder exist? if NO, create it.
        if (!file_exists($error_log_folder)) {
            mkdir($error_log_folder, 0755);
        }

        error_log($message . "\r\n\r\n", 3, "{$error_log_folder}{$filename}.log");
    }

    /**
     * Save email service/connect specific optin errors.
     *
     * @param string $message error message
     * @param string $filename log file name.
     *
     * @return bool
     */
    public static function save_optin_error_log($message, $filename = 'error')
    {
        $error_log_folder = MAILOPTIN_OPTIN_ERROR_LOG;

        // does bugs folder exist? if NO, create it.
        if (!file_exists($error_log_folder)) {
            mkdir($error_log_folder, 0755);
        }

        return error_log($message . "\r\n", 3, "{$error_log_folder}{$filename}.log");
    }

    /**
     * Split full name into first and last names.
     *
     * @param string $name
     *
     * @return array
     */
    public static function get_first_last_names($name)
    {
        $data = [];

        $names = explode(' ', $name);

        $data[] = isset($names[0]) ? trim($names[0]) : '';
        $data[] = isset($names[1]) ? trim($names[1]) : '';

        return $data;
    }

    /**
     * Get selected list ID of an email campaign.
     *
     * @param int $email_campaign_id
     *
     * @return int|string
     */
    public function get_email_campaign_list_id($email_campaign_id)
    {
        return EmailCampaignRepository::get_customizer_value($email_campaign_id, 'connection_email_list');
    }

    /**
     * Get campaign title of an email campaign.
     *
     * @param int $email_campaign_id
     *
     * @return string
     */
    public function get_email_campaign_campaign_title($email_campaign_id)
    {
        return EmailCampaignRepository::get_email_campaign_name($email_campaign_id);
    }
}