{if $data.existing_staff}
	{$data.content = "{$data.inviter} has added you as {$data.roles} for site {$site.name} on SiteGUI platform. Please click the button below to start managing that site."}
	{$data.cta_text = "Manage"}
{else}
	{$data.content = "{$data.inviter} has added you as {$data.roles} for site {$site.name} on SiteGUI platform. To accept the addition and complete the setup process, please click the button below. You may use existing login or create a new one."}
	{$data.cta_text = "Confirm"}
{/if}	

{include "base.tpl"}