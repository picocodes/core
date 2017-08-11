<?php

namespace MailOptin\Core\Connections;

use MailOptin\Core\EmailCampaign\EmailCampaignTrait;
use MailOptin\Core\Repositories\EmailCampaignRepository;

abstract class AbstractConnect
{
    use EmailCampaignTrait;

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
     */
    public static function save_optin_error_log($message, $filename = 'error')
    {
        $error_log_folder = MAILOPTIN_OPTIN_ERROR_LOG;

        // does bugs folder exist? if NO, create it.
        if (!file_exists($error_log_folder)) {
            mkdir($error_log_folder, 0755);
        }

        error_log($message . "\r\n", 3, "{$error_log_folder}{$filename}.log");
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

        if (isset($names[0])) {
            $data[] = trim($names[0]);
        }
        if (isset($names[1])) {
            $data[] = trim($names[1]);
        }

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