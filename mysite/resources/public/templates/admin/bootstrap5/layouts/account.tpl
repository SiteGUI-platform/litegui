<!DOCTYPE html>
<html lang="en" style="height: 100%;">
<head>
  <meta http-equiv="content-type" content="text/html; charset={$charset|default: 'utf-8'}" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="csrf-token" content="{$token}">
  <base href="{$system.url}{$system.base_path}">
  <title>{$html.title} - {$site.name}</title>
  <!-- Custom styles for this template - including bootstrap-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="{$system.cdn}/{$template}/assets/js/sitegui.js?v=9" id="sitegui-js" data-locale="{$site.locale|default:$user.language|default:$site.language}" data-currency="{$site.currency.code|default:USD}" data-precision="{$site.currency.precision|default:2}" data-timezone="{$user.timezone|default:$site.timezone|default:UTC}"></script>
  <link href="{$system.cdn}/{$template}/assets/css/mysite.css?v=61" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css" />
  <link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css" rel="stylesheet" type="text/css" />

  <script defer src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script defer src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>    
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
  <!-- Custom scripts for all pages-->
  <script defer src="{$system.cdn}/{$template}/assets/js/mysite.js?v=7"></script>
  <link rel="shortcut icon" href="{$system.cdn}/{$template}/assets/img/favicon.png?v=1"> 
  {block 'block_head'}{$block_head nofilter}{/block}
</head>
<body {if $system.sgframe}class="bg-{if $block_main}white{else}light{/if}"{else}style="background-image: repeating-radial-gradient(rgb(0,81,120), black);"{/if}>
{if !$system.sgframe}
  {block 'block_header'}{$block_header nofilter}{/block}
  <div id="block_header" class="container-fluid g-0 mb-2 mb-sm-4">
    <!-- Fixed navbar -->
    <nav class="navbar navbar-expand navbar-light bg-white border-bottom p-0 text-end">
      <div class="align-self-start py-1">
        <div class="">
          <a href="https://{$site.url}"><img class="img-responsive" src="{if $site.logo}{$site.logo}{else}{$system.cdn}/{$template}/assets/img/logo.png{/if}" alt="Site Logo" style="max-width: 200px;max-height: 80px;"></a>
        </div>
      </div>
      <div class="navbar-brand required-for-pushing-navbar-toggler-right"></div>
      <button class="navbar-toggler m-2 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarRight" aria-controls="navbarRight" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse my-2" id="navbarRight">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item mx-1">
            <a href="{$system.url}{$system.base_path}" class="nav-link text-success">
                <i class="bi bi-house-lock text-secondary"></i>
            </a>
          </li>  
        {foreach $html.top_menu as $level1}
          {if $level1.children}
          <li class="nav-item dropdown mx-1 position-static {if $level1.id eq $page_id}{/if}">
            <a href="{$level1.slug}" class="nav-link {if $level1.name}dropdown-toggle{/if} text-success" data-bs-toggle="dropdown">
              {if $level1.icon}
                <i class="{$level1.icon} text-secondary"></i>
              {/if}
              {if $level1.name AND $api.status.result ne 'error'}<span class="d-none d-sm-inline">{$level1.name|trans}</span>{/if}
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in">
              <div class="row mx-1">
            {foreach $level1.children as $name => $level2}
              {if $level2.children}
                <div class="col-12 col-sm mt-2 pe-sm-5 ">
                  <span class="d-block pb-1">{$name|trans}</span>
                {foreach $level2.children as $level3}
                  <a class="dropdown-item d-inline-block d-sm-block w-49 {if $level3.active}active rounded{/if}" {if $level3.name == 'File' || $level3.name == 'Files'} href="#" data-bs-toggle="modal" data-bs-target="#dynamicModal"{else} href="{$level3.slug}" {/if}>
                    
                    {if $level3.icon}
                      <i class="{$level2.icon} text-danger"></i>
                    {/if}
                    {$level3.name|trans}
                  </a>
                {/foreach}
                </div>
              {else}
              <a class="dropdown-item {if $level2.active}active rounded{/if}" {if $level2.name == 'File' || $level2.name == 'Files'} href="#" data-bs-toggle="modal" data-bs-target="#dynamicModal"{else} href="{$level2.slug}" {/if}>
                {if $level2.icon}
                  <i class="{$level2.icon} text-danger"></i>
                {/if}
                {$level2.name|trans}
              </a>
              {/if}
            {/foreach}
              </div>
            </div>
          </li>
          {else}
          <li class="nav-item mx-1"><a href="{$level1.slug}" class="nav-link text-success">
            {if $level1.icon}
              <i class="{$level1.icon} text-secondary"></i>
            {/if}
            {if $level1.name AND $api.status.result ne 'error'}<span class="d-none d-sm-inline">{$level1.name|trans}</span>{/if}
          </a></li>
          {/if}
        {/foreach}
          <li class="nav-item dropdown position-static mx-1">
            <a href="#" class="nav-link text-success" id="dropdownMenu4" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bi bi-person text-secondary"></i><span class="d-none d-md-inline"> {$user.name} </span>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="dropdownMenu4">
            {if $user.id}  
              <a class="dropdown-item js-sg-notification-end" href="{$system.base_path}/view"><i class="bi bi-person-badge me-2"></i> {'Account'|trans}</a>
              <a class="dropdown-item" href="#" data-url="{$system.base_path}?logout=true" data-confirm="{'Logout'|trans}{if  $user.type && $user.type != 'System'}? {'You logged in via :Auth, have you logged out of :Auth yet'|trans:['auth' => $user.type] }{/if}" data-name="" data-value=""><i class="bi bi-box-arrow-right me-2"></i> {"Logout"|trans} </a>
            {else}
              <a class="dropdown-item" href="#" data-url="{$system.base_path}"><i class="bi bi-box-arrow-in-left me-2"></i> {"Login"|trans} </a>
            {/if}  
            </div>
          </li>
        </ul>
      </div>  
    </nav>
  </div>
{else}
  <script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function(e){  
      if ( ! $('.input-name').length || $('.input-name').val() ){  
        $('div[role="tabpanel"]').get(0) && $('div[role="tabpanel"]').get(0).scrollIntoView({
          behavior: "smooth", 
          block: "start", 
          inline: "nearest"
        })
        window.scrollBy(0, -15) //due to padding
      }  
    })  
  </script>
{/if}
  {block 'block_spotlight'}{$block_spotlight nofilter}{/block}
  {block 'block_left'}{$block_left nofilter}{/block} 
  <div class="container-fluid">
    <div class="row">
      {block 'block_top'}{$block_top nofilter}{/block} 
      <div id="block_main" class="col-md-12">
        <div class="content-pane pb-2 row" {if $html.title eq $LANG.carttitle} id="whmcsorderfrm" {/if}>
           {include file="message.tpl"}
           {block name='block_main'}{$block_main nofilter}{/block}
            {* You can place everything before this line into header.tpl and anything after this line to footer.tpl *}
        </div>
      </div>
      {block 'block_bottom'}{$block_bottom nofilter}{/block} 
    </div>
  </div> 
  {block 'block_right'}{$block_right nofilter}{/block} 
  {block 'block_footer'}{$block_footer nofilter}{/block}
  {block 'block_footnote'}{$block_footnote nofilter}{/block} 
</body>
</html>        