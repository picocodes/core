<?php

namespace MailOptin\Core\Admin\Customizer\EmailCampaign;

use MailOptin\Core\EmailCampaigns\NewPublishPost\Templatify;
use MailOptin\Core\Repositories\EmailCampaignRepository;

$email_campaign_id = absint($_REQUEST['mailoptin_email_campaign_id']);

$email_campaign_type = EmailCampaignFactory::get_campaign_type_namespace(
    EmailCampaignRepository::get_email_campaign_type($email_campaign_id)
);

$db_template_class = EmailCampaignRepository::get_template_class($email_campaign_id);

$template_preview_class = "MailOptin\\Core\\Admin\\Customizer\\EmailCampaign\\{$email_campaign_type}TemplatePreview";

/** @var Templatify $template_preview_instance */
$template_preview_instance = new $template_preview_class('', $email_campaign_id, $db_template_class);

echo $template_preview_instance->replace_footer_placeholder_tags($template_preview_instance->forge());

// this is not in AbstractTemplate as in AbstractOptinForm so it doesn't get templatified/emogrified along with the email template
// on customizer preview.
// hide any element that might have been injected to footer by any plugin.
echo '<div style="display:none">';
wp_footer();
echo '</div>';