<!DOCTYPE html>
<html lang="en" style="height: 100%;">
<head>
  <meta http-equiv="content-type" content="text/html; charset={$charset|default: 'utf-8'}" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="csrf-token" content="{$token}">
  <base href="{$system.url}">
  <title>{$html.title}</title>
  <!-- Custom styles for this template - including bootstrap-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="{$system.cdn}/{$template}/assets/js/sitegui.js?v=9" id="sitegui-js" data-locale="{$site.locale|default:$user.language|default:$site.language}" data-currency="{$site.currency.code|default:USD}" data-precision="{$site.currency.precision|default:2}" data-timezone="{$user.timezone|default:$site.timezone|default:UTC}" data-notification="{$links.notifier}"></script>
  <link href="{$system.cdn}/{$template}/assets/css/mysite.css?v=63" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css" />
  <link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css" rel="stylesheet" type="text/css" />

  <script defer src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script defer src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>    
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
  <!-- Custom scripts for all pages-->
  <script defer src="{$system.cdn}/{$template}/assets/js/mysite.js?n=38"></script>
  <link rel="shortcut icon" href="{$system.cdn}/{$template}/assets/img/favicon.png?v=1"> 
  {block 'block_head'}{$block_head nofilter}{/block}
</head>
<body class="bg-dark" {if $api.status.result ne error}style="background-image: repeating-radial-gradient(rgb({$html.rgb}), black);"{/if}>
{block 'block_header'}{$block_header nofilter}{/block}
<div id="block_header" class="container-fluid g-0">
  <!-- Fixed navbar -->
  <nav class="navbar navbar-expand navbar-dark p-0 text-end">
    <div class="logo-fixed align-self-start py-1 text-start">
      <div id="logo">
        <a href="{$system.url}{$system.base_path}"><img src="{if $site.logo AND $site.tier > 19}{$site.logo}{else}{$system.cdn}/{$template}/assets/img/logo.png?v=4{/if}" style="max-width: 200px; max-height: 50px;"></a>
      </div>
      <button type="button" class="navbar-toggler sidebar-toggler {if $html.sidebar}opa-1{/if} {if $html.cart}cart-toggler{/if}">
          <span class="sr-only"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
      </button>
    </div>
    <div class="navbar-brand required-for-pushing-navbar-toggler-right"></div>
    <button class="navbar-toggler border-0 m-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarRight" aria-controls="navbarRight" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse my-2" id="navbarRight">
      <ul class="navbar-nav ms-auto">
      {foreach $html.top_menu as $level1}
        {if $level1.children}
        <li class="nav-item dropdown mx-1 position-static">
          <a href="{$level1.slug}" class="nav-link {if $level1.name}dropdown-toggle{/if} text-info" id="dropdownMenu{$level1@index}" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {if $level1.icon}<i class="{$level1.icon}"></i>{/if} 
            {if $level1.name AND ($api.status.result ne 'error' OR $level1@index != 1)}<span class="d-none d-sm-inline">{$level1.name|trans}</span>{/if}
          </a>
          <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="dropdownMenu{$level1@index}">
            <div class="row mx-1">
          {foreach $level1.children as $name => $level2}
            {if $level2.children}
              <div class="col-12 col-sm mt-2 pe-sm-5 {if $name eq 'Apps' AND $level1.name != ''}sg-app-listing{/if}">
                <span class="d-block pb-1">{$name|trans}</span>
              {foreach $level2.children as $level3}
                <a class="dropdown-item d-inline-block d-sm-block w-49 {if $level3.active}active rounded{/if}" {if $level3.name == 'File' || $level3.name == 'Files'} href="#" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="{'File Manager'|trans}" data-url="{$html.file_manager}?CKEditorFuncNum=1#{$html.upload_dir}"{else} href="{$level3.slug|default:'#'}" {/if}>
                  {if $level3.activeTBREMOVED}&check;{/if} 
                  {if $level3.icon}
                    <i class="{$level3.icon} text-danger"></i>
                  {/if}
                  {$level3.name|trans}
                </a>
              {/foreach}
              </div>
            {else}
              <a class="dropdown-item {if $level2.active}active rounded{/if}" {if $level2.name == 'File' || $level2.name == 'Files'} href="#" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="{'File Manager'|trans}" data-url="{$html.file_manager}?CKEditorFuncNum=1#{$html.upload_dir}"{else} href="{$level2.slug|default:'#'}" {/if}>
                {if $level2.active}&check;{/if} 
                {if $level2.icon}
                  <i class="{$level2.icon} text-danger"></i>
                {/if}
                {$level2.name|replace:'_':' '|trans}
              </a>
            {/if}
          {/foreach}
            </div>
          </div>
        </li>
        {else}
        <li class="nav-item mx-1"><a href="{$level1.slug}" class="nav-link text-info">{$level1.name|trans}</a></li>
        {/if}
      {/foreach}
        <li class="nav-item dropdown position-static mx-1">
            <a href="{$html.top_menu.sites.slug}" class="nav-link text-info" id="dropdownMenu4" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bi bi-person-fill"></i><span class="d-none d-sm-inline"> {$user.name} </span>
                <span class="js-sg-notification-unseen position-absolute top-0 end-0 badge rounded-pill bg-dark text-lime"></span>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="dropdownMenu4">
              {if $site.id AND $user.roles} 
              <a class="dropdown-item js-sg-notification-end mt-2" href="{$system.url}{$system.base_path}{if $user.id}/staff/edit/{$user.id}{/if}"><i class="bi bi-person-circle me-2"></i> 
                {foreach $user.roles AS $role}
                  {$role|trans}{if !$role@last}, {/if}
                {/foreach}
              </a>
              {/if}
              <a class="dropdown-item" href="https://my.sitegui.com/account" target="_blank"><i class="bi bi-grid-fill me-2"></i> {"Account Center"|trans}</a>
              <a class="dropdown-item" href="https://sitegui.com/docs" target="_blank"><i class="bi bi-info-circle me-2"></i> {"Documentation"|trans}</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="#" data-url="{$system.url}{$system.base_path}/page/edit?logout=true" data-confirm="{'Logout'|trans}{if $user.type && $user.type != 'System'}? {'You logged in via :Auth, have you logged out of :Auth yet'|trans:['auth' => $user.type] }{/if}" data-name="" data-value=""><i class="bi bi-box-arrow-right me-2"></i> {"Logout"|trans} </a>
            </div>
        </li>
      </ul>
    </div>  
  </nav>
</div>
{block 'block_spotlight'}{$block_spotlight nofilter}{/block} 
<div class="container-fluid">
  <div class="row">
    <div id="block_left" class="col-auto bg-dark px-0 navbar-default" role="navigation"><!-- sidebar -->
      {block 'block_left'}{$block_left nofilter}{/block}
    </div>
    <div id="block_right" class="col">
      <div class="row">
        {block 'block_top'}
          <div id="block_top" class="col-md-12 mx-auto">{$block_top nofilter}</div>
        {/block}
        <div id="block_main" class="col-md-12 mx-auto">
          {block 'content_head'}{$content_head nofilter}{/block}
          <div class="content-pane pt-2 row">
            {include file="message.tpl"}
            {block 'content_header'}
              <div id="content_header" class="col-12 mx-auto">
                <div class="row">
                  {$content_header nofilter}
                </div>  
              </div>
            {/block}
            {block name='block_main'}{$block_main nofilter}{/block}
            {* You can place everything before this line into header.tpl and anything after this line to footer.tpl *}
            {block 'content_footnote'}{$content_footnote nofilter}{/block}
          </div>
          {block 'content_footer'}{$content_footer nofilter}{/block}
        </div> 
        {block 'block_bottom'}{$block_bottom nofilter}{/block} 
      </div>      
    </div>
    {block 'block_right'}{$block_right nofilter}{/block} 
  </div>  
</div>
{block 'block_footer'}{$block_footer nofilter}{/block}
{block 'block_footnote'}{$block_footnote nofilter}{/block}
<div class="col-12 footer-padding pb-{$footer_padding|default:4}"></div>  
</body>
</html>        
