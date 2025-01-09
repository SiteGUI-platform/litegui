{extends "page_edit.tpl"}

{*<!--provide extra content for APP if necessary -->*}
{block name=APP_header}{/block}
{block name=APP_tabcontent}{/block}
{block name=APP_tabsettings}{/block}

{if ! $app.hide.tabapp}
  {block name=APP_tabname}
        <li class="nav-item">
          <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-app" role="tab" aria-controls="tab-app" aria-selected="false">{$html.app_label}</button>
        </li> 
  {/block}
{/if}  
{*<!-- Somehow putting if outside does not work -->*}
{block name=APP_fields}
  {if $api.parent}
    <div class="form-group row mb-3">
      <label class="col-sm-3 col-form-label text-sm-end">{'For'|trans} <b>{$api.parent.app_label}</b></label>
      <div class="col-sm-7">
        <span class="input-group-text bg-transparent"><a href="#" data-url="{if $api.parent.type eq App}{$links.edit}/{$api.parent.subtype|lower}{else}{$links.edit|replace:'app':$api.parent.type|lower}{/if}/{$api.parent.id}?sgframe=1" data-title="{$api.parent.app_label}: {$api.parent.name}" data-bs-toggle="modal" data-bs-target="#dynamicModal" class="text-decoration-none">{$api.parent.name}</a></span>
      </div>  
    </div> 
    {if $api.parent.id ne $api.parent.root_id}
    <div class="form-group row mb-3">
      <label class="col-sm-3 col-form-label text-sm-end">{'In'|trans} <b>{$api.parent.root_app_label}</b></label>
      <div class="col-sm-7">
        <span class="input-group-text bg-transparent"><a href="#" data-url="{if $api.parent.root_type eq App}{$links.edit}/{$api.parent.root_subtype|lower}{else}{$links.edit|replace:'app':$api.parent.root_type|lower}{/if}/{$api.parent.root_id}?sgframe=1" data-title="{$api.parent.root_app_label}: {$api.parent.root_name}" data-bs-toggle="modal" data-bs-target="#dynamicModal" class="text-decoration-none">{$api.parent.root_name}</a></span>
      </div>  
    </div> 
    {/if}
  {/if}
  {if $app.fields}
    <div id="tab-app" class="sg-form tab-pane fade show active" role="tabpanel">  
      {include "form_field.tpl" formFields=$app.fields fieldPrefix="{$prefix}[fields]"}
    </div>  
  {/if}   
{/block}


{*<!-- Subapp Area -->*}
{if $api.subapp}  
  {*<!-- use APP block for extra header/footer, SUBAPP blocks are necessary if inside APP_content -->*}
  {block name=SUBAPP_tabcontent}{/block}
  {block name=SUBAPP_tabsettings}{/block}
  
  {block name=APP_content}
    {$smarty.block.parent} {*<!-- insert parent fields and then set var to sub page -->*}

    {foreach $api.subapp AS $name2 => $subapp2}
      {$app = $subapp2}
      {$page = []}
      {$prefix = "page[sub][$name2]"}

      {*<!-- capture subapp's fields and move them outside form to  -->*}
      {if $language@first OR !$app.hide.locales}
        {if $language@first} 
          {*$subapp2.fields = []*} {*<!--Dont display subapp's custom fields for other languages as they work with 1 lang atm-->*}
          {capture "{$name2}_{$first_lang}"}
          {$colwidth = 7}
          {$smarty.block.parent} {*<!-- insert sub fields and then set var back to parent page -->*}    
          {/capture}
         {/if}   
      {/if}
    {/foreach}    
    
    {$app = $api.app}
    {$page = $api.page}
    {$prefix = 'page'}
  {/block}  

  {block name=APP_settings}
    {$smarty.block.parent}

    {foreach $api.subapp AS $name3 => $subapp3}
      {$app = $subapp3}
      {$page = []}
      {$prefix = "page[sub][$name3]"}
      {if ! $subapp3.hide.tabsettings}
        {capture "settings_$name3"}
          <!-hr class="or w-100 my-5" name="{$name3|replace: '_':' '}"-->
          {$smarty.block.parent}
        {/capture}
      {/if}       
    {/foreach}    
    
    {$app = $api.app}
    {$page = $api.page}
    {$prefix = 'page'}
  {/block}  

  {*<!-- Subapp is always displayed in its own tab, condition is optional but recommended: Subapp tab name-->*}
  {block name=SUBAPP_tabname}
      <li class="nav-item">
        <button type="button" class="nav-link position-relative" data-bs-toggle="tab" data-bs-target="#app-{$name}-tabpane" role="tab" aria-controls="app-{$name}-tabpane" aria-selected="false">{$api.app.sub.$name.alias|default:($name|replace: '_':' ')|trans}</button>
      </li> 
  {/block}

  {function renderDiscussion}
    {foreach $subpages as $subpage}
      <div class="col-12 {if $system.sgframe}px-0{else}col-md-10{/if} mt-3 mx-auto">
        <div class="card">
          <div class="row g-0">
            <div class="col-sm-3 text-end p-3 bg-primary bg-opacity-10">
              <div class="row">
                {foreach $subpage.creator AS $i => $n}
                <div class="col col-sm-12 text-start text-sm-end">  
                  {$n}
                </div> 
                {if $i == $user.id}
                <div class="col col-sm-12 text-end card-text">{"Edit Now"|trans}</div>
                {/if} 
                {/foreach}
                {if $subpage.updated}               
                <div class="col col-sm-12 text-end card-text">   
                  <small class="text-secondary js-sg-time">{$subpage.updated}</small>
                </div>
                {/if}  
              </div>  
            </div>
            <div class="col-sm-9">
              <div class="card-body">
                <span class="card-text">{$subpage.content|strip_tags}</span>
                <p class="card-text">
                {foreach $subpage.meta.attachment AS $attachment}
                  <a class="" href="#" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="{'Preview'|trans}" data-url="{$attachment}">{"Attachment"|trans}</a> 
                {/foreach}
                </p>
              </div>
            </div>
          </div>
        </div>        
      </div>        
    {/foreach}     
  {/function}

  {*<!--SUBAPP_fields (below), label, content will be captured to $smarty.capture.subapp, 
  and then placed in SUBAPP_tabbody and inserted to tab-subapp --> *}
  {block name=SUBAPP_tabbody}
    {if $name != $first_subapp OR $api.app.sub.$name.display != flat} {* first discussion subapp goes to footer *}
      {if $api.page.id}   
        {include "datatable.tpl" forapp=$name}
      {/if} 
    {/if}    
    {if $api.app.sub.$name.entry == multiple OR $api.app.sub.$name.entry == single OR $api.app.sub.$name.entry == quick OR 
       ($api.app.sub.$name.entry == client_readonly AND $html.file_manager) OR
       ($api.app.sub.$name.entry == creator_readonly AND $api.page.creator AND $api.page.creator != $user.id) OR  
       ($api.app.sub.$name.entry == other_readonly AND $api.page.creator == $user.id) 
    } {* display input fields if not readonly *}
      {if $smarty.capture["{$name}_{$first_lang}"]}
        <!-- if $api.page.id use datatable.tpl directly -->
        <div class="ps-2 mb-4 {if $api.page.id AND $api.app.sub.$name.display != flat}d-none{/if}">
          <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-sub-{$name}" aria-expanded="false" aria-controls="collapse-sub-{$name}">{"New :item"|trans:['item' => $api.app.sub.$name.alias|default:($name|replace:'_':' ')]}</button>
        </div> 
        
        <div class="collapse collapse-placeholder pt-1" id="collapse-sub-{$name}">
          {*$smarty.capture.$name nofilter*}
        </div>  
      {/if}
    {/if}  
  {/block}

  {block name=SUBAPP_fields}
    {include "form_field.tpl" formFields=$subapp2.fields fieldPrefix="page[sub][$name2][fields]"}
  {/block}

  {block name=APP_footer}
    {foreach $api.subapp AS $name => $subapp}
      <div class="d-none" id="collapse-sub-{$name}-wrapper">
        {foreach $site.locales as $lang => $language}
          {if $language@first}
            {$smarty.capture["{$name}_{$first_lang}"] nofilter}
          {*elseif !$subapp.hide.locales}
            <button class="btn btn-outline-primary m-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-subapp-{$lang}" aria-expanded="false" aria-controls="collapse-subapp-{$lang}">{$language}</button>
            <div class="collapse collapse-placeholder" id="collapse-subapp-{$lang}">
              {$smarty.capture["{$name}_{$lang}"] nofilter}
            </div> *} 
          {/if}
        {/foreach}  
        {$smarty.capture["settings_$name"] nofilter}
      </div> 
    {/foreach}
    {if $api.page.id AND $first_subapp AND $api.app.sub.$first_subapp.display == flat} {* first discusstion subapp goes to footer *}
      <div class="col-12 {if $system.sgframe}px-000{else}col-md-10 mb-4{/if} mx-auto">
        <div class="row">
        {*renderDiscussion subpages=$api.subpages.$first_subapp.api.rows*}
        {include "datatable.tpl" forapp=$first_subapp}
        </div>    
      </div>  
    {/if}      
  {/block}
{/if}