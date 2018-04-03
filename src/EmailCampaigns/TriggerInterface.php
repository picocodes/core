<?php

namespace MailOptin\Core\EmailCampaigns;


interface TriggerInterface
{
    /**
     * Replaces placeholders in email campaign subject with their actual values.
     *
     * @param string $email_subject
     * @param string $data_source
     *
     * @return string
     */
    public static function format_campaign_subject($email_subject, $data_source);

    /**
     * Does the actual campaign sending.
     *
     * @param int $email_campaign_id
     * @param int $campaign_id
     */
    public function send_campaign($email_campaign_id, $campaign_id);
}