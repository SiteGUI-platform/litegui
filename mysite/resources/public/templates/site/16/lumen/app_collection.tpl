{extends "datagrid.tpl"} 

{block name="content_top"}
  <div class="container-md px-0 mb-4">
    {if $api.page.breadcrumb}
    <nav class="text-left" aria-label="breadcrumb">  
      <ol class="breadcrumb bg-transparent border-0 ps-0">
        <li class="breadcrumb-item"><a href="/"><i class="bi bi-house"></i></a></li>
        {foreach $html.breadcrumbs AS $breadcrumb}
        <li class="breadcrumb-item"><a href="{$breadcrumb.slug}{if $system.sgframe}?sgframe=1{/if}">{$breadcrumb.name}</a></li>
        {/foreach}
        <li class="breadcrumb-item active" aria-current="page">{$api.page.name}</li>
      </ol>
    </nav>  
    {else}
      <h4 class="">{$api.page.name}</h4>
    {/if}
  	<p>{$api.page.content nofilter}</p>
  </div>
  {$content_top nofilter}	
{/block} 

{block name="grid_menu"}
<div class="col">
  <ul class="nav nav-pills" role="tablist">
      {if !$html.app_readonly}
      <li class="nav-item" role="presentation">
        <button class="btn btn-sm btn-outline-primary" type="button" data-url="https://{$site.account_url}/account/app/view{$links.edit}{$links.edit2}?sgframe=1" data-title="{'New :item'|trans:['item' => $html.current_app|replace: '_':' ']}" data-bs-toggle="modal" data-bs-target="#dynamicModal" aria-expanded="false">{'New :item'|trans:['item' => $html.current_app|replace: '_':' ']}</button> 
      </li>
      {/if}
      {foreach $html.app_menu as $level1}
      <li class="nav-item" role="presentation">
          <a class="nav-link" aria-current="page" href="{$level1.slug|default: '#'}{if $system.sgframe}?sgframe=1{/if}">
          {if $level1.icon}
              <i class="{$level1.icon}"></i>
          {/if}
              {$level1.name}
          </a>
      </li>    
      {/foreach}                    
  </ul>
</div>  
{/block}

{block name="grid_item"}
  <div class="js-sg-collection-item col" data-filter="subtype">
    <div class="thumbnail card h-100 bg-light bg-opacity-10">
      <a class="sg-img-container border text-decoration-none mx-auto position-relative overflow-hidden" href="{$row.slug}">
        <img class="img-fluid" src="{$row.image}" alt="" />
      </a>  
      <div class="card-body px-3 pb-0">          
        <div class="row small lh-lg">
          <div class="col-12">
            <span class="sg-title text-decoration-none pe-2 fs-6">
              <a class="fw-bold text-secondary text-decoration-none pe-2" href="{$row.slug}{if $system.sgframe}?sgframe=1{/if}">{$row.name}</a> 
            </span>
            <br>
            <span class="sg-subtitle card-text text-success">{$row.status}</span>
            {if $row.creator}
            <span class="sg-subtitle card-text float-end">
              <a class="text-decoration-none text-secondary" href="{$links.datatable.creator}/{$row.creator}">{$row.creator_name}</a>
            </span> 
            {/if}
          </div>
          <div class="row d-none sg-description">
            <div class="col">
              {$row.description}
            </div>
          </div>                      
        </div>
      </div>
      <div class="card-footer px-3 border-top-0 bg-light bg-opacity-10">
        <div class="row small">                     
          <div class="col-auto sg-links text-nowrap">
            {foreach $api.subapp.show AS $subapp => $show}
              {$line = "`$row.id`-`$subapp`"}
              {if $api.subapp.count[$line]}
                {$api.subapp.count[$line]} {if $api.subapp.count[$line] > 1}{$show.plural}{else}{$show.single}{/if}
              {/if}
            {/foreach}
          </div>  
          <div class="col text-end sg-links-end js-sg-date">
            {$row.published|default: $row.registered|default: $row.updated}  
          </div>
        </div>
      </div>
    </div>
  </div>   
{/block} 