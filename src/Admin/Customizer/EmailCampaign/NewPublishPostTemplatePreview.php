<?php

namespace MailOptin\Core\Admin\Customizer\EmailCampaign;


use MailOptin\Core\EmailCampaigns\NewPublishPost\Templatify;
use MailOptin\Core\EmailCampaigns\TemplateTrait;

class NewPublishPostTemplatePreview extends Templatify
{
    public function __construct($post, $email_campaign_id = null, $template_class = null)
    {
        parent::__construct($post, $email_campaign_id, $template_class);
    }

    public function post_title()
    {
        return SolitaryDummyContent::title();
    }

    public function post_content()
    {
        return SolitaryDummyContent::content();
    }

    public function post_url()
    {
        return '#';
    }

    public function feature_image($post_id)
    {
        // we need the default image shown, so we are gonna supply invalid post ID
        return parent::feature_image('');
    }
}