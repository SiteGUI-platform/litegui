<!DOCTYPE html>
<html lang="en">
<head>
{block name="block_head"}
  <meta http-equiv="content-type" content="text/html; charset={$charset|default: 'utf-8'}" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="description" content="{$api.page.description}" />
  <meta name="generator" content="SiteGUI.CMS" />
  <title>{$api.page.title|default: $api.page.name} - {$site.name} {$html.app_label_plural}</title>
	<!-- Custom styles for this template - including bootstrap-->
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.3/lumen/bootstrap.min.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!-- Custom fonts for this template-->
	<script src="{$site.cdn}/{$template}/assets/sitegui.js?v=12" id="sitegui-js" data-locale="{$site.locale|default:$user.language|default:$site.language}" data-currency="{$site.currency.code|default:USD}" data-precision="{$site.currency.precision|default:2}" data-timezone="{$user.timezone|default:$site.timezone|default:UTC}"></script>
	<link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600" rel="stylesheet" />	
 	<link href="{$site.cdn}/{$template}/assets/sitegui.css?v=33" rel="stylesheet" />
	<link rel="shortcut icon" href="{$site.cdn}/{$template}/assets/favicon.png" /> 
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
	{$block_head nofilter}
</head>
<body> 
{/block}

{block name="block_header"}
{$block_header nofilter}
<div class="row bg-light mb-2">
	<div class="col-12">
		<nav class="navbar navbar-expand-lg navbar-light border-0 bg-white py-2 z-1" data-observer-classes-off="" data-observer-margin="300px">
			<div class="container">
			  <a class="navbar-brand" href="//{$site.url}"><img class="img-responsive" src="{if $site.logo}{$site.logo}{else}{$site.cdn}/{$template}/assets/logo.png{/if}" alt="Site Logo" style="max-width: min(200px, 35vw);"></a>
				<div class="input-group d-none d-lg-flex mx-5 px-5">
					<form action="/store" method="GET" class="w-100 position-relative">
		      	<input name="searchPhrase" class="form-control shadow-none rounded-5 px-4" type="text" placeholder="{'Search'|trans}">
		      	<button type="submit" class="btn position-absolute top-50 end-0 translate-middle-y text-muted fs-base m-1 shadow-none"><i class="bi bi-search"></i></button>
		    	</form>
		    </div>	  
				<div class="navbar-toolbar d-flex flex-shrink-0 align-items-center">
				  <button class="navbar-toggler border-0 px-1" type="button" data-bs-toggle="collapse" data-bs-target="#sg-id-1646279124182" aria-controls="sg-id-1646279124182" aria-expanded="false" aria-label="Toggle navigation">
				    <span class="navbar-toggler-icon"></span>
				  </button>
		      {if $site.social.facebook}
		      <a class="btn text-dark d-lg-flex mt-1 pe-1" href="https://facebook.com/{$site.social.facebook}">
		        <i class="bi bi-facebook fs-5"></i>
		      </a>
		      {/if}
		      {if $site.social.tiktok}
		      <a class="btn text-dark d-lg-flex mt-1 pe-1" href="https://tiktok.com/@{$site.social.tiktok}">
		        <i class="bi bi-tiktok fs-5"></i>
		      </a>
		      {/if}
		      <a class="btn text-dark mt-1 pe-1" href="https://{$site.account_url}/account">
		      	<i class="bi bi-person-circle fs-5"></i>
		      </a>
		      <a class="btn text-dark mt-1 pe-1" href="https://{$site.account_url}/account/cart" data-url="https://{$site.account_url}/account/cart?sgframe=1" data-title="{'Shopping Cart'|trans}" data-bs-toggle="modalHide" data-bs-target="#dynamicModal">
		      	<i class="bi bi-cart3 fs-5 position-relative">{if $html.SGCartQty}<span class="position-absolute bg-dark rounded-circle text-white p-1 sg-cart-count">{$html.SGCartQty}</span>{/if}</i>
		      </a>
		    </div>
			</div>
		</nav>	  
		<nav class="navbar navbar-expand-lg navbar-light border-0 bg-white pb-0 pb-md-2" data-observer-classes-off="" data-observer-margin="300px">
			<div class="container px-0">
			  <div class="collapse navbar-collapse" id="sg-id-1646279124182" data-sg-id-ref="data-bs-target,aria-controls">
					<div class="input-group d-lg-none px-3 mb-3">
						<form action="/store" method="GET" class="w-100 position-relative">
			      	<input name="searchPhrase" class="form-control shadow-none rounded-5 px-4" type="text" placeholder="Tìm">
			      	<button type="submit" class="btn position-absolute top-50 end-0 translate-middle-y text-muted fs-base m-1 shadow-none"><i class="bi bi-search"></i></button>
			    	</form>
			    </div>
		      <ul class="navbar-nav mb-2 mb-lg-0 w-100 justify-content-center">
		      {foreach $html.top_menu as $level1}
		      {if $level1.children}
		        <li class="nav-item dropdown">
		          <a class="nav-link text-nowrap fw-bold px-3 text-uppercase {if $level1.id eq $api.page.id}text-danger{elseif $level1.active}text-warning{else}{/if} dropdown-toggle shadow-none" href="{$level1.slug}" id="top-menu-{$level1@index}" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
		            {$level1.name}
		          </a>
		          <ul class="dropdown-menu" aria-labelledby="top-menu-{$level1@index}">
		          {foreach $level1.children as $level2}
		          	{if $level2.children}
		          	<li class="nav-item dropend">
				          <a class="nav-link text-nowrap fw-bold px-3 text-uppercase {if $level2.id eq $api.page.id}text-danger{elseif $level2.active}text-warning{else}{/if} dropdown-toggle shadow-none" href="{$level2.slug}" id="top-menu-{$level1@index}-{$level2@index}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
				            {$level2.name}
				          </a>
		          		<ul class="submenu dropdown-menu" aria-labelledby="top-menu-{$level1@index}-{$level2@index}">
		          		{foreach $level2.children as $level3}
		          			{if $level3.children}
					          	<li class="nav-item dropend">
							          <a class="nav-link text-nowrap fw-bold px-3 text-uppercase {if $level3.id eq $api.page.id}text-danger{elseif $level3.active}text-warning{else}{/if} dropdown-toggle shadow-none" href="{$level3.slug}" id="top-menu-{$level1@index}-{$level2@index}-{$level3@index}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							            {$level3.name}
							          </a>
					          		<ul class="submenu dropdown-menu" aria-labelledby="top-menu-{$level1@index}-{$level2@index}-{$level3@index}">
					          		{foreach $level3.children as $level4}
					          			<li><a class="dropdown-item text-nowrap text-uppercase {if $level4.active}text-danger{/if}" href="{$level4.slug}">{$level4.name}</a></li>
					          		{/foreach}
					          		</ul>	
					          	</li>	
					          {else}
		          				<li><a class="dropdown-item text-nowrap text-uppercase {if $level3.active}text-danger{/if}" href="{$level3.slug}">{$level3.name}</a></li>
					          {/if}	
		          		{/foreach}
		          		</ul>	
		          	</li>	
		          	{else}
		            	<li class="nav-item"><a class="dropdown-item text-nowrap text-uppercase {if $level2.active}text-danger{/if}" href="{$level2.slug}">{$level2.name}</a></li>
		            {/if}	
		          {/foreach}
		          </ul>
		        </li>
		      {else}
		        <li class="nav-item">
		          <a class="nav-link text-nowrap fw-bold px-3 text-uppercase {if $level1.id eq $api.page.id}text-danger{elseif $level1.active}text-danger{else}{/if} {if $level1.active}active{/if}" href="{$level1.slug}">{$level1.name}</a>
		        </li>
		      {/if}
		      {foreachelse}
		        <li class="nav-item">
		          <a class="nav-link text-nowrap fw-bold px-3 text-uppercase" href="#">{'Home'|trans}</a>
		        </li>
		        <li class="nav-item">
		          <a class="nav-link text-nowrap fw-bold px-3 text-uppercase" href="#">{'About'|trans}</a>
		        </li>
		        <li class="nav-item">
		          <a class="nav-link text-nowrap fw-bold px-3 text-uppercase" href="#">{'Contact'|trans}</a>
		        </li>
		      {/foreach}
					{if $html.locale_urls}
						<li class="nav-item dropdown">
							<a class="nav-link text-nowrap fw-bold px-3 dropdown-toggle" href="#" id="navbarDropdownLang" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="bi bi-translate"></i>
							</a>
							<ul class="dropdown-menu" aria-labelledby="navbarDropdownLang">									
							{foreach from=$html.locale_urls key=lang item=url}
								<li><a class="dropdown-item" href="{$url}">{$site.locales[$lang]|capitalize|trans}</a></li>
							{/foreach}
							</ul>
						</li>	
					{/if}
		      </ul>    
			  </div>
			</div>  
		</nav>	
	</div>
</div>		
{/block}
<div class='container-fluid'>
 <div class='row'>
   <div id='block_spotlight' class='col-sm-12'>
    <div class='row'>
      <div id='spotlight_content' class='col-sm-12 whaterver'>
        {block name='block_spotlight'}{$block_spotlight nofilter}{/block}
      </div>
    </div>
   </div>
 </div>
</div>

{if $api.status.message}
	{block name='block_top'}
	<div class="sg-message col-12 col-md-10 mx-auto">
		{if $html.message_title || $api.status.result eq 'error'}
		<div class="card mb-3">
		  	<div class="card-header text-white bg-{if $html.message_type}{$html.message_type}{else if $api.status.result eq 'error'}danger{else}primary{/if}">
		    	{$html.message_title|default:'Error'}
		  	</div>
		  	<div class="card-body">
		  	{foreach from=$api.status.message item=message}
				<div class="card-text">{$message}</div>	 	 			
		  	{/foreach}
		  	</div>
		</div>
		{else}
		<div class="status-message-container alert alert-{$html.message_type|default: 'info'} alert-dismissible fade show" role="alert">
			{foreach from=$api.status.message item=message}
			<div>{$message}</div>	 	 			
			{/foreach}
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			<script type="text/javascript">
  				document.addEventListener("DOMContentLoaded", function(e){
					$(".status-message-container").fadeTo(6000, 500).slideUp(500, function(){
					    $(".status-message-container").alert('close');
					});
				});	
			</script>
		</div>
		{/if}
	</div>
	{$block_top nofilter}	
	{/block}
{/if}