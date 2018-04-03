<?php

namespace MailOptin\Core\Admin\Customizer\EmailCampaign;


use MailOptin\Core\EmailCampaigns\NewPublishPost\TemplatifyNewPostPublish;
use MailOptin\Core\EmailCampaigns\TemplateTrait;

class TemplatePreview extends TemplatifyNewPostPublish
{
    use TemplateTrait;

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

    public function feature_image()
    {
        return MAILOPTIN_ASSETS_URL . 'images/email-templates/default-feature-img.jpg';
    }
}