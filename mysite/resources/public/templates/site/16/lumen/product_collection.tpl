{extends "datagrid.tpl"} 

{block name="content_top"}
<div class="container-md px-0 mb-4">
  {if $api.page.breadcrumb}
  <nav class="text-left" aria-label="breadcrumb">  
    <ol class="breadcrumb bg-transparent border-0 ps-0">
      <li class="breadcrumb-item"><a href="/"><i class="bi bi-house"></i></a></li>
      {foreach $html.breadcrumbs AS $breadcrumb}
      <li class="breadcrumb-item"><a href="{$breadcrumb.slug}">{$breadcrumb.name}</a></li>
      {/foreach}
      <li class="breadcrumb-item active" aria-current="page">{$api.page.name}</li>
    </ol>
  </nav>  
  {else}
    <h4 class="mt-1">{$api.page.name|trans}</h5>
  {/if}
  {if $html.searchPhrase}
    <div class="py-3">
    {if $api.total eq 1}
      {'Found 1 result for'|trans} <b>{$html.searchPhrase}</b>
    {elseif $api.total}
      {'Found :count results for'|trans:['count' => $api.total]} <b>{$html.searchPhrase}</b>
    {else}
      {'No records found'|trans}!
    {/if}
    </div>
  {/if}
	{if $api.page.content}<p>{$api.page.content nofilter}</p>{/if}

  {if $api.has_collections AND $api.has_collections|count < 20}
  <div class="col-12 mb-3">
    <ul class="nav">
    {foreach $api.has_collections AS $collection}
      <li class="nav-item">
        <a href="{$collection.slug}" class="nav-link text-success-emphasis">
          {$collection.name} 
        </a>
      </li>  
    {/foreach}
    </ul>  
  </div>
  {/if}  
</div>
{$content_top nofilter} 	
{/block}

{block name="grid_item"}
  <div class="col js-sg-collection-item border-bottom-0" data-filter="{$row.subtype|lower}">
    <div class="thumbnail card rounded-bottom-0 h-100">
      {if $row.was > 0 AND $row.was > $row.price}
        <span class="sg-discount position-absolute top-0 my-2 z-3 badge rounded-0 rounded-end bg-danger">-{(100*(1-$row.price/$row.was))|truncate:2:''}%</span>
      {/if} 
      <a class="sg-img-container border text-decoration-none mx-auto position-relative overflow-hidden" href="{$row.slug}">
        <img class="img-fluid card-img-top {if !$row.image AND !$row.variants.0.images.0}no-image{/if}" src='{$row.image|default:{$row.variants.0.images.0}|default:"https://ui-avatars.com/api/?size=80&rounded=1&length=4&font-size=.3&bold=1&background=random&b8ea86&color=417505&name={$row.name}"}' alt="" />{*hide on hover*}
        {if $row.variants.0.images.0}
        <img class="img-fluid card-img-top position-absolute top-0 start-0 z-1" src="{$row.variants.0.images.0}" alt="" />
        {/if}
      </a>
      <div class="card-body align-items-end sg-selection p-2 mx-auto">
      {if $row.variants AND $row.variants|count > 1}  
        {$seen = []}
        {foreach $row.variants AS $variant}
          {foreach $variant.options AS $option}
            {$option2 = $option|replace:' ':'-'|lower}
            {if $option@index eq 0 AND !$seen[ $option2 ] AND $variant.images.0} {$seen[ $option2 ] = 1}
              <span class="js-sg-variant-selection variant-{$option2}" title="{$option}" {if $variant.images.0}data-src="{$variant.images.0}"{/if}></span>
            {/if}
          {/foreach}
        {/foreach} 
      {/if}              	
      </div>
      <div class="card-footer px-2 bg-white border-top-0">
        <div class="row">
          <div class="col">
            <a class="fw-bold text-decoration-none pe-2" href="{$row.slug}">{$row.name}</a> 
            {if $row.price > 0}<span class="js-sg-currency fw-bold">{$row.price}</span>
              {if $row.was > $row.price}(<span class="js-sg-currency text-decoration-line-through">{$row.was}</span>){/if}
            {else} {'Free'|trans}{/if}
          </div>
          <div class="col-auto text-end">
            <span class="card-text small">{$row.subtype|trans}</span>
          </div>
        </div> 
        <div class="row d-none sg-description">
          <div class="col">
            {$row.description}
          </div>
        </div> 
        <div class="row">
        {if $row.price >= 0} 
          <div class="col pt-2 text-center">
            <div class="btn-group hover-visible">
            {if $row.variants|count > 1}
              <button type="button" class="btn btn-warning text-white dropdown-toggle m-2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{'Order'|trans} <span class="caret"></span>
              </button>
              <div class="dropdown-menu dropdown-menu-end float-end">
                <div class="row mx-1 flex-sm-nowrap">
                {foreach $row.variants as $variant}
                  {if $variant@index % 6 == 0}<div class="col">{/if}
                    <form action="{$links.cart_add}" method="POST" class="text-end">
                      <input type="hidden" name="item[id]" value="{$variant.id}">
                      <input type="hidden" name="csrf_token" value="{$token}">
                      <button type="submit" class="btn btn-sm text-nowrap border-0 m-2" {if $html.stock_checking && $variant.stock lt 1}disabled{/if}>
                        {if $variant.options}
                          {foreach $variant.options AS $key => $option}
                            {$option|trans} - 
                          {/foreach}
                        {/if}
                        {if $variant.price > 0} <span class="js-sg-currency">{$variant.price}</span> {else} {'Free'|trans} {/if}
                      </button>
                    </form> 
                  {if $variant@index % 6 == 5 OR $variant@last}</div>{/if}
                {/foreach}
                </div>
              </div>
            {else}
              <form action="{$links.cart_add}" method="POST">
                <input type="hidden" name="item[id]" value="{$row.variants.0.id}">
                <input type="hidden" name="csrf_token" value="{$token}">
                <button type="submit" class="btn btn-warning text-white m-2" {if $html.stock_checking && $row.variants.0.stock lt 1}disabled{/if}>{'Order'|trans}</button>
              </form>                             
            {/if}  
            </div>
          </div>
        {/if}
        </div>
      </div>
    </div>
  </div>    
{/block}