{$page = $api.page}
{$app  = $api.app}
<div id="content" class="container-md">
    {block name="content_header"}{$content_header nofilter}{/block}
    <div class="row">
        {block name="content_left"}{$content_left nofilter}{/block}
        <div class="col-12 col-md">
            <div class="row">
                {block name="content_spotlight"}{$content_spotlight nofilter}{/block}
                <div class="col-12">
                    {if $page.breadcrumb}
                    <nav class="text-left" aria-label="breadcrumb">  
                      <ol class="breadcrumb bg-transparent border-0 ps-0">
                        <li class="breadcrumb-item"><a href="/{if $system.sgframe}?sgframe=1{/if}"><i class="bi bi-house"></i></a></li>
                        {foreach $html.breadcrumbs AS $breadcrumb}
                    	<li class="breadcrumb-item"><a href="{$breadcrumb.slug}{if $system.sgframe}?sgframe=1{/if}">{$breadcrumb.name}</a></li>
                    	{/foreach}
                        <li class="breadcrumb-item active" aria-current="page">{$page.name}</li>
                      </ol>
                    </nav>
                    {elseif $page.type eq App}
                        <h6 class="mt-3 fs-5"><b>{$page.name}</b></h6>	
                    {/if}
                    {block name="content_top"}{$content_top nofilter}{/block}
                    {if $api.parent}
                        <div class="row">
                          <div class="col-12">
                            <span class="me-2">{'For'|trans} <b>{$api.parent.app_label}</b></span>
                            <a href="#" data-url="{$api.parent.slug}?sgframe=1" data-title="{$api.parent.app_label}: {$api.parent.name}" data-bs-toggle="modal" data-bs-target="#dynamicModal" class="text-decoration-none">{$api.parent.name}</a>
                          </div>  
                        </div> 
                        {if $api.parent.id ne $api.parent.root_id}
                        <div class="row">
                          <div class="col-12">
                            <span class="me-2">{'In'|trans} <b>{$api.parent.root_app_label}</b></span>
                            <a href="#" data-url="{$api.parent.root_slug}?sgframe=1" data-title="{$api.parent.root_app_label}: {$api.parent.root_name}" data-bs-toggle="modal" data-bs-target="#dynamicModal" class="text-decoration-none">{$api.parent.root_name}</a>
                          </div>  
                        </div> 
                        {/if}
                    {/if}
                    {if $app.fields}
                        <div class="row mb-3">    
                        {foreach $app.fields AS $col => $field}
                            {if $col != 'name' AND $field.value}
                            <div class="col-md-4">
                                <b class="me-2">{$field.label|trans}</b>                                        
                                {if $field.slug}<a href="{$field.slug}">{/if}
                                {if $field.value == (array) $field.value}
                                    {foreach $field.value AS $value}
                                      {$value}{if !$value@last}, {/if}
                                    {/foreach}
                                {else}
                                    <span class="{if $field.type eq date}js-sg-date{elseif $field.type eq time}js-sg-time{/if}">{$field.value}</span>
                                {/if}    
                                {if $field.slug}</a>{/if}         
                            </div>
                            {/if}    
                        {/foreach}
                        </div>
                    {/if}    
                    {$page.content nofilter}
                    <p class="mt-3">
                    {foreach $api.collections AS $collection}
                        <a href="{$collection.slug}">#{$collection.name}</a>{if !$collection@last}, {/if}
                    {/foreach}
                    </p>
                    {block name="content_bottom"}{$content_bottom nofilter}{/block}
                </div>
            </div>
            {if $api.subapp}
            <div class='row mx-md-0 mt-4' role="tabpanel">
                <!-- Nav tabs -->
                <ul id="main-tab" class="nav nav-tabs px-3" role="tablist">
                    {foreach $api.subapp AS $name => $subapp}
                        {if ! $subapp.hide.tabapp }
                          <li class="nav-item {if $api.app.sub.$name.entry eq quick AND $user.id}ms-auto order-last{/if}">
                            <button type="button" class="nav-link position-relative mt-0 {if $api.app.sub.$name.entry eq quick AND $user.id}border-0 js-sg-quick-engagement js-app-{$name}-captcha{/if} {if $subapp@first}active{/if}" data-bs-toggle="tab" data-bs-target="#app-{$name}-tabpane" role="tab" aria-controls="app-{$name}-tabpane" aria-selected="false">{$api.app.sub.$name.alias|default:($name|replace:'_':' ')|trans}</button>   
                          </li> 
                        {/if}
                    {/foreach}
                </ul>
                <style type="text/css">
                    #main-tab .nav-link {
                        height: 2.7rem;
                        margin-top: 0.3rem !important;
                        transition: all .2s ease-in-out;
                    }
                    #main-tab .nav-link.active,
                    #main-tab .nav-link:hover {
                        height: 3rem;
                        margin-top: 0 !important;
                        padding-bottom: .5rem !important;
                    }
                </style>
                <!-- Tab panes -->
                <div id="main-tab-content" class="sg-main tab-content pt-3 px-md-0">
                    {foreach $api.subapp AS $name => $subapp}
                        {if ! $subapp.hide.tabapp }
                            <div id="app-{$name}-tabpane" class="sg-form sg-sub tab-pane fade {if $subapp@first}show active{/if}" role="tabpanel"> 
                            {if $api.page.id}   
                                {include "datatable.tpl" forapp=$name}
                                {if  $api.app.sub.$name.entry == multiple OR $api.app.sub.$name.entry == single OR 
                                     $api.app.sub.$name.entry == quick OR 
                                    ($api.app.sub.$name.entry == creator_readonly AND $api.page.creator AND $api.page.creator != $user.id) OR  
                                    ($api.app.sub.$name.entry == other_readonly AND $api.page.creator == $user.id) 
                                } {* display input fields if not readonly *}
                                    <form action="{$links.update}" method="post" enctype="multipart/form-data">
                                        <div class="text-center pb-5">
                                          <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-sub-{$name}" aria-expanded="false" aria-controls="collapse-sub-{$name}">{"New :item"|trans:['item' => $api.app.sub.$name.alias|default:($name|replace:'_':' ')]}</button>
                                        </div>  
                                        <div class="collapse pb-5" id="collapse-sub-{$name}">
                                        {if $user.id}    
                                            {include "form_field.tpl" formFields=$subapp.fields fieldPrefix="page[sub][$name][fields]"}
                                            {include "form_field.tpl" formFields=$subapp.show   fieldPrefix="page[sub][$name]"}
                                            {if $html.challenge_captcha}
                                                <div class="position-absolute mt-1">
                                                    <script async defer src="https://cdn.jsdelivr.net/npm/altcha/dist/altcha.min.js" type="module"></script>
                                                    <altcha-widget challengejson="{$html.challenge_captcha}" floating hidelogo hidefooter></altcha-widget>
                                                </div>
                                            {/if}
                                            <div class="text-center">
                                                <input type="hidden" name="page[id]" value="{$api.page.id}">    
                                                <input type="hidden" name="page[subtype]" value="{$api.page.subtype}">
                                                <input type="hidden" name="csrf_token" value="{$api.page.id}sg{$token}">
                                                <button type="submit" name="save-btn" class="js-submit-btn btn btn-outline-primary my-2" {$onclick}><i class="bi bi-save pe-2"></i>  {'Submit'|trans}</button>
                                            </div>
                                        {else}
                                            <div class="text-center">
                                                <a href="https://{$site.account_url}/account/?oauth=sso&login=step2&token={$token}{$site.id}&requester={$html.sso_requester}" class="btn btn-outline-primary my-2">{'Sign In'|trans}</a>
                                            </div>                                    
                                        {/if}    
                                        </div>
                                    </form>    
                                {/if}
                            {/if}   
                            </div>  
                        {/if}
                    {/foreach}
                </div>    
            </div>            
            {/if}
            {block name="content_footnote"}{$content_footnote nofilter}{/block}
        </div>         
        {block name="content_right"}{$content_right nofilter}{/block}
    </div>    
</div>
{block name="content_footer"}{$content_footer nofilter}{/block}