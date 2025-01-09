{extends "datagrid.tpl"} 

{block name="content_header"}
  {if $api.config.name}
  <div class="row justify-content-center position-absolute top-0 me-0" style="z-index: -1;">
    <img class="img-fluid px-0" src="{$api.config.banner.0}" style="min-height: 100px; width:auto;" />
  </div>
  <div class="container-md pt-3">
        <a href="/{$html.current_app|lower}" class="text-decoration-none"><h5 class="">{$api.config.name}</h5></a>
  </div>  
  {/if} 
  <div class="container-md mb-4">
    {if $api.page.breadcrumb}
    <nav class="text-left" aria-label="breadcrumb">  <ol class="breadcrumb bg-transparent border-0 ps-0">
        <li class="breadcrumb-item"><a href="/"><i class="bi bi-house"></i></a></li>
        {foreach $html.breadcrumbs AS $breadcrumb}
      <li class="breadcrumb-item"><a href="{$breadcrumb.slug}">{$breadcrumb.name}</a></li>
      {/foreach}
        <li class="breadcrumb-item active" aria-current="page">{$api.page.name}</li>
      </ol>
    </nav>  
    {/if}
  	<p>{if $api.page.content}{$api.page.content nofilter}{/if}</p>
  </div>
  {$content_header nofilter}   
{/block} 
{block name="content_right"}
  {if $api.has_collections}
  <div class="col-12 col-md-3 mb-3 pt-3">
    <h6 class="ps-3 pb-2">{'Categories'|trans}</h6>
    <ul class="list-group">
    {foreach $api.has_collections AS $collection}
      {*if $collection.location}{continue}{/if*}
      <a href="{$collection.slug}" class="list-group-item d-flex justify-content-between align-items-center">
        {$collection.name}
        {if $collection.quantity}
          <span class="badge bg-primary rounded-pill">{$collection.quantity}</span>
        {/if}  
      </a>
    {/foreach}  
    </ul>
    {$content_right nofilter} 
  </div>
  {/if}
{/block}

{block name="grid_item"}
  <div class="js-sg-collection-item col {if !$html.grid_mode}list-view{/if}" data-filter="{$row.subtype|lower}">
    <div class="thumbnail card border-0 h-100">
      <a class="sg-img-container text-decoration-none mx-auto" href="{$row.slug}">
        <img class="img-fluid card-img-top" src='{$row.image}' alt="" />
      </a>
      <div class="card-body px-2 caption">
        <div class="row">
          <div class="col">
            <a class="fw-bold text-decoration-none pe-2" href="{$row.slug}">{$row.title|default:$row.name}</a> 
          </div>
        </div> 
        {if $html.show_author}                   	
        <div class="row">
          <div class="col-auto mt-2">
            {if $row.creator_avatar}<span class="only-child"><img class="rounded-circle me-2 sg-datatable-thumb" loading="lazy" src="{$row.creator_avatar}" /></span>{/if}
            <a class="text-decoration-none text-secondary" href="{$links.datatable.creator}/{$row.creator_handle}">{$row.creator_name}</a>
          </div>  
          <div class="col text-end js-sg-date mt-2">
            {$row.updated}
          </div>
        </div>
        {/if}
        <div class="row sg-description">
          <div class="col py-2">
            {$row.description}
          </div>
          <div class="col-12 text-nowrap">
            {foreach $api.subapp.show AS $subapp => $show}
              {$line = "`$row.id`-`$subapp`"}
              {if $api.subapp.count[$line]}
                {$api.subapp.count[$line]} {if $api.subapp.count[$line] > 1}{$show.plural}{else}{$show.single}{/if}
              {/if}
            {/foreach}
          </div>
        </div> 
      </div>
    </div>
  </div>    
{/block} 