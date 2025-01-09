{extends "page.tpl"}

{block name="content_header"}
    {if $api.config.name}
      <div class="row justify-content-center position-absolute top-0 start-0 me-0" style="z-index: -1;">
        <img class="img-fluid px-0" src="{$api.config.banner.0}" style="min-height: 100px; width:auto;" />
      </div>
      <div class="col pt-3">
        <a href="/blog" class="text-decoration-none"><h5 class="">{$api.config.name}</h5></a>
      </div>
      <style type="text/css">
        #block_header .navbar {
          background-color: transparent !important;
        }
      </style>   
    {/if}
    {$content_header nofilter}  
{/block}

{block name="content_top"}
    {if $html.show_author}                      
    <div class="row pt-0 pb-3">
      <div class="col-auto">
        {if $page.creator_avatar}<img class="rounded-circle me-2 sg-datatable-thumb" loading="lazy" src="{$page.creator_avatar}" />{/if}
        {if $api.profile.slug}<a class="text-decoration-none text-secondary" href="{$api.profile.slug}">{/if}
          {$page.creator_name}
        {if $api.profile.slug}</a>{/if}
      </div>  
      <div class="col text-end js-sg-date">{$page.created}</div>
    </div>
    {/if}
    {if $api.page.image}
    <div class="row" style="z-index: -1;">
      <img class="img-fluid mb-4" src="{$api.page.image}" style="width:auto;" />
    </div>
    {/if}
    {$content_top nofilter} 
{/block}

{block name="content_right"}
  <div class="col-12 col-md-3 mb-3 pt-3">
  {if $api.collections_items}
    <h6 class="ps-3 pb-2">{'Related Topics'|trans}</h6>
    <ul class="list-group">
    {foreach $api.collections_items AS $item}
      <a href="{$collection.slug}" class="list-group-item d-flex justify-content-between align-items-center">
        {$item.name}
        {if $item.quantity}
          <span class="badge bg-primary rounded-pill">{$item.quantity}</span>
        {/if}  
      </a>
    {/foreach}  
    </ul>
  {/if} 
  </div>
  {$content_right nofilter}  
{/block} 