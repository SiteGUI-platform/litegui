{$html.app_label_plural = 'Appstore'}
{extends "datagrid.tpl"} 

{block name="grid_item"}
                <div class="col js-sg-collection-item" data-filter="{$row.subtype|lower}">
                  <div class="thumbnail card border-0 h-100">
                    {if $row.image}
                    <img class="sg-img-container img-fluid card-img-top {if !$row.image}no-image mx-md-5 px-md-5{/if}" src='{$row.image|default:"https://ui-avatars.com/api/?size=120&rounded=1&length=4&font-size=.3&bold=1&background=random&b8ea86&color=417505&name={$row.name}"}' alt="" data-url="https://app.sitegui.com{$row.slug}" data-title="{$row.name}" data-bs-toggle="modal" data-bs-target="#dynamicModal" role="button" />
                    {else}
                    <div class="sg-img-container no-image mx-auto">
                      <span class="rounded-circle text-nowrap fw-bold d-flex align-items-center justify-content-center" style="width: 90px; height: 90px; font-size: 20px; background-color:{$row.style.bg}; color: {$row.style.color}">{$row.style.abbr}</span>
                    </div>
                    {/if}
                    {if $row.sold_counter}<span class="position-absolute top-0 end-0 z-3 mx-2 badge rounded-0 rounded-bottom bg-warning">{$row.sold_counter} <i class="bi bi-clouds-fill"></i></span>{/if}
                    <div class="card-body p-1"></div>
                    <div class="caption card-footer p-2 bg-white border-top-0">
                      <div class="row">
                        <div class="col">
                          {if $links.edit}
                            <a class="link-warning position-relative" href="{$links.edit}/{$row.id}"><i class="bi bi-coin"></i></a> 
                          {/if}                           
                          <a class="fw-bold text-success text-decoration-none pe-2" href="https://app.sitegui.com{$row.slug}" target="_blank">{$row.name}</a> 
                          <span class="">{if $row.price != 0.00} ${$row.price} {else} {"Free"|trans} {/if}</span> 
                        </div>
                        <div class="col-auto text-end">
                          <span class="card-text small">{$row.subtype|trans}</span>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm pt-2 text-center">
                          <div class="btn-group hover-visible">
                            <button type="button" class="btn btn-primary text-white dropdown-toggle m-2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{"Buy"|trans} <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end float-end">
                            {foreach $row.variants as $variant}
                             <a class="dropdown-item {if $variant.options.License eq Developer}" href="{$links.cart_add}?id={$variant.id}" target="_blank{else}text-decoration-line-through{/if}">
                                <i class="bi bi-{if $variant.options.License eq Developer}cloud-download{else}cloud-check{/if}"></i> 
                                {if $variant.options}{foreach $variant.options AS $option}{$option} - {/foreach}{/if}
                                {if $variant.price != 0.00} ${$variant.price} {else} {"Free"|trans} {/if}    
                              </a>
                            {/foreach}
                            </div>
                          {if 0}
                            <button type="button" class="btn btn-primary" data-url="{$links.activate}" data-confirm="{'activate :Item'|trans:['item' => $row.name] }" data-name="name" data-value="{$row.id}">{"Activate"|trans}</button>
                          {/if}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>    
{/block}
{block name="grid_menu"}
                    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-app" aria-controls="navbar-app" aria-expanded="false" aria-label="Toggle navigation">
                      <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbar-app">
                      <ul class="nav nav-pills me-auto ps-0" role="tablist">
                      {foreach $api.has_collections AS $collection}
                        {if $collection@index < 5}
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" href="{$links.pagination}?type={$collection.slug|replace:'/collection/':''}">{$collection.name}</a>
                        </li> 
                        {elseif $collection@index eq 5}
                        <li class="nav-item" role="presentation">
                          <div class="dropdown">
                            <button class="btn border-0 nav-link" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="{$links.pagination}?type={$collection.slug|replace:'/collection/':''}">{$collection.name}</a></li>
                        {else}
                              <li><a class="dropdown-item" href="{$links.pagination}?type={$collection.slug|replace:'/collection/':''}">{$collection.name}</a></li>
                        {/if}
                        {if $collection@last}
                            </ul>
                          </div>
                        </li>  
                        {/if}
                      {foreachelse}
                           <li class="nav-item" role="presentation">
                              <a class="nav-link" href="{$links.pagination}?type={$api.page.slug|replace:'/collection/':''}">{$api.page.name}</a>
                           </li>
                      {/foreach}
                      </ul>
                      <form class="d-flex" role="search" method="" action="{$links.pagination}">
                        <input class="form-control me-2" type="search" name="searchPhrase" placeholder="{'Name'|trans}" aria-label="Search">
                        <button class="btn btn-outline-success" type="submit"><i class="bi bi-search"></i></button>
                      </form>                      
                    </div>  
{/block}
{block name="grid_header"}
  <script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function(e){
      $('#dataConfirmForm').each(function() {
          $(this).append('<input type="hidden" name="item[domain]" value="{$site.id}">');
      });
    });
  </script>
{/block}  
