<?php

namespace MailOptin\Core\EmailCampaign;


interface CampaignLogInterface
{
    public function email_campaign_id();

    public function title();

    public function content_html();

    public function content_text();
}