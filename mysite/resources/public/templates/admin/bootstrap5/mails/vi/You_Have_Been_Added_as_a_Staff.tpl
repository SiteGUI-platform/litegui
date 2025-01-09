{if $data.existing_staff}
	{$data.content = "{$data.inviter} đã mời bạn làm {$data.roles} cho trang web {$site.name} trên nền tảng SiteGUI. Bạn có thể nhấn vào nút bên dưới để bắt đầu quản lý tài khoản."}
	{$data.cta_text = "Quản Lý"}
{else}
	{$data.content = "{$data.inviter} đã mời bạn làm {$data.roles} cho trang web {$site.name} trên nền tảng SiteGUI. Vui lòng nhấn vào nút bên dưới để chấp nhận lời mời này, bạn có thể quản lý bằng tài khoản hiện tại (nếu có) hoặc tạo một tài khoản mới."}
	{$data.cta_text = "Chấp Nhận"}
{/if}	

{include "base.tpl"}