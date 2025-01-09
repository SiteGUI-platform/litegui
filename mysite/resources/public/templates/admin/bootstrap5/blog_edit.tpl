{extends "app_edit.tpl"}

{block name="APP_header"}{/block}

{block name="APP_tabname"}
        <li class="nav-item">
          <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-app" role="tab" aria-controls="tab-app" aria-selected="false">{$page.subtype}</button>
        </li> 
{/block}
    <!-- Tab panes -->
{block name="APP_tabcontent"}
    {if ! $app.hide.tabapp}
      <div id="tab-app" class="tab-pane fade show active" role="tabpanel"> 
    {/if} 
    <div class='text-center'>Yeah we can add any content through this template</div>
    {if $app.fields}
        {include "form_field.tpl" formFields=$app.fields fieldPrefix='page[fields]'}
    {/if}
    
    {if ! $app.hide.tabapp}
      </div>  
    {/if}  
{/block}

{block name="APP_tabcontent"}{/block}
{block name="APP_tabsettings"}{/block}

{block name="APP_footer"}{/block}