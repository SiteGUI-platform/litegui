{extends "blog_collection.tpl"} 
{block name="grid_menu"}
<div class="col">
  <ul class="nav nav-pills" role="tablist">
      {if $html.menu_config.0 != readonly}
      <li class="nav-item" role="presentation">
        <button class="btn btn-sm btn-outline-primary text-nowrap" type="button" data-url="https://{$site.account_url}/account/app/view{$links.edit}{$links.edit2}?sgframe=1" data-title="{'New :item'|trans:['item' => 'Thread']}" data-bs-toggle="modal" data-bs-target="#dynamicModal" aria-expanded="false">{'New :item'|trans:['item' => 'Thread']}</button> 
      </li>
      {/if}                    
  </ul>
</div>  
{/block}
{block name="grid_item"}
  <div class="js-sg-collection-item col {if !$html.grid_mode}list-view{/if}" data-filter="{$row.subtype|lower}">
    <div class="thumbnail card border-0 h-100">
      <div class="card-body p-0">
        <div class="row">
          <div class="col">
            <a class="fw-bold text-decoration-none pe-2" href="{$row.slug}">{$row.title|default:$row.name}</a> 
          </div>
          <div class="col-auto text-end js-sg-date">
            {$row.updated}
          </div>
        </div> 
        {if $html.show_author}                    
        <div class="row">
          <div class="col-auto mt-2">
            {if $row.creator_avatar}<span class="only-child"><img class="rounded-circle me-2 sg-datatable-thumb" loading="lazy" src="{$row.creator_avatar}" /></span>{/if}
            <a class="text-decoration-none text-secondary" href="{$links.datatable.creator}/{$row.creator_handle}">{$row.creator_name}</a>

            {if $row.forum_category AND $api.has_collections}{'in'|trans} 
              <b>{foreach $row.forum_category AS $cid => $category}
                {if $api.has_collections.$cid.slug}
                  <a class="text-decoration-none text-secondary" href="{$api.has_collections.$cid.slug}">{$category}</a>
                {else}
                  {$category} 
                {/if}
              {/foreach}
              </b>
            {/if}
          </div>
          <div class="col text-end text-nowrap mt-2">
            {foreach $api.subapp.show AS $subapp => $show}
              {$line = "`$row.id`-`$subapp`"}
              {if $api.subapp.count[$line]}
                {$api.subapp.count[$line]} {if $api.subapp.count[$line] > 1}{$show.plural}{else}{$show.single}{/if}
              {/if}
            {/foreach}
          </div>
        </div>
        {/if}
        <div class="row sg-description pt-2">
          <div class="col">
            {$row.description}
          </div>
        </div> 
      </div>
    </div>
  </div>    
{/block} 