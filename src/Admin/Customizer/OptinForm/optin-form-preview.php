<?php

use MailOptin\Core\Admin\Customizer\OptinForm\OptinFormFactory;

$optin_campaign_id = absint($_REQUEST['mailoptin_optin_campaign_id']);

echo OptinFormFactory::make($optin_campaign_id)->get_preview_structure();

// hide any element that might have been injected to footer by any plugin.
echo '<div style="display:none">';
wp_footer();
echo '</div>';