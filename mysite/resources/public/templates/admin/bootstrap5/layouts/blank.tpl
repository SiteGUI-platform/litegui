<!DOCTYPE html>
<html lang="en">
<head>
  {if $html.background}
  <style>
    body { 
      background: url('{$html.background nofilter}') no-repeat center center fixed; 
      -webkit-background-size: cover;
      -moz-background-size: cover;
      -o-background-size: cover;
      background-size: cover;
      background-color: darkslateblue;
    }
  </style> 
  {/if} 
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
  <script src="{$system.cdn}/{$template}/assets/js/sitegui.js?v=11" id="sitegui-js" data-locale="{$site.locale|default:$user.language|default:$site.language}" data-currency="{$site.currency.code|default:USD}" data-precision="{$site.currency.precision|default:2}" data-timezone="{$user.timezone|default:$site.timezone}"></script>
  <link href="{$system.cdn}/{$template}/assets/css/mysite.css?v=62" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css" />
  <link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css" rel="stylesheet" type="text/css" />

  <script defer src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script defer src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>    
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
  <!-- Custom scripts for all pages-->
  <script defer src="{$system.cdn}/{$template}/assets/js/mysite.js?v=4"></script>
  <link rel="shortcut icon" href="{$system.cdn}/{$template}/assets/img/favicon.png?v=1"> 
  {block 'block_head'}{$block_head nofilter}{/block}
</head>
<body class="bg-{if $block_main}white{else}light{/if}">
  {block 'block_header'}{$block_header nofilter}{/block}
  {block 'block_spotlight'}{$block_spotlight nofilter}{/block}
  {block 'block_left'}{$block_left nofilter}{/block}
  <div id="block_main" class="container-fluid mx-auto">
    <div class="row" {if $html.title eq $LANG.carttitle} id="whmcsorderfrm" {/if}>
      {include file="message.tpl"}
      {block 'block_top'}{$block_top nofilter}{/block}  
      {block name='block_main'}{$block_main nofilter}{/block}
      {block 'block_bottom'}{$block_bottom nofilter}{/block}
      {* You can place everything before this line into header.tpl and anything after this line to footer.tpl *}
    </div>
  </div>
  {block 'block_right'}{$block_right nofilter}{/block}
  {block 'block_footer'}{$block_footer nofilter}{/block}
  {block 'block_footnote'}{$block_footnote nofilter}{/block}
</body>
{if $system.sgframe}
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
  <style type="text/css">
    form > .card,
    .card.datatable-wrapper {
      border: 0;
      border-radius: 0;
    }

  </style>
{/if}
</html>        