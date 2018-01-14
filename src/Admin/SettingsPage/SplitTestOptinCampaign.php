<?php

namespace MailOptin\Core\Admin\SettingsPage;

use MailOptin\Core\AjaxHandler;
use MailOptin\Core\Repositories\OptinCampaignsRepository;

class SplitTestOptinCampaign
{
    protected $optin_campaign_id;

    /**
     * @param int $optin_campaign_id
     */
    public function __construct($parent_optin_campaign_id, $name, $note)
    {
        $this->optin_campaign_id = $parent_optin_campaign_id;
        $this->name = $name;
        $this->note = $note;
    }

    /**
     * Do the variant creation.
     *
     * @return bool
     */
    public function forge()
    {
        $clonee = OptinCampaignsRepository::get_optin_campaign_by_id($this->optin_campaign_id);

        $optin_campaign_id = OptinCampaignsRepository::add_optin_campaign(
            AjaxHandler::generateUniqueId(),
            $this->name,
            $clonee['optin_class'],
            $clonee['optin_type']
        );

        if ($optin_campaign_id === false) return false;

        $all_optin_campaign_settings = OptinCampaignsRepository::get_settings();
        // append new template settings to existing settings.
        $all_optin_campaign_settings[$optin_campaign_id] = OptinCampaignsRepository::get_settings_by_id($this->optin_campaign_id);
        $all_optin_campaign_settings[$optin_campaign_id]['split_test_note'] = $this->note;
        // save to DB
        OptinCampaignsRepository::updateSettings($all_optin_campaign_settings);

        return $optin_campaign_id;
    }
}