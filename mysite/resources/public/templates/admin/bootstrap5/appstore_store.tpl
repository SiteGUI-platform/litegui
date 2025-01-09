{$table_data=$api.appstore}
{extends "datagrid.tpl"} 

{block name="grid_item"}
                <img class="img-container img-fluid card-img-top" src='{$row.image|default:"https://source.unsplash.com/random/400x200?sig={$row.id}"}' alt="" />
                <div class="card-body p-0"></div>
                <div class="caption card-footer p-2 bg-white border-top-0">
                  <div class="row">
                    <div class="col">
                      <a class="fw-bold text-decoration-none pe-2" href="/product/{$row.slug}">{$row.name}</a> 
                      <span class="">{if $row.price} ${$row.price} {else} Free {/if}</span>
                    </div>
                    <div class="col-auto text-end">
                      <span class="card-text small">{$row.subtype}</span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-sm pt-2 text-center">
                      <div class="btn-group hover-visible">
                        <button type="button" class="btn btn-success text-white dropdown-toggle m-2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Order <span class="caret"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end float-end">
                        {foreach $row.variants as $variant}
                          <a href="#" class="dropdown-item" data-url="{$html.links.cart_add}" data-confirm="order" data-name="item[id]" data-value="{$variant.id}">
                              {$variant.options.name} - {if $variant.price} ${$variant.price} {else} Free {/if}    
                          </a>
                        {/foreach}
                        </div>
                      {if 0}
                        <button type="button" class="btn btn-primary" data-url="{$html.links.activate}" data-confirm="activate {$row.name}" data-name="name" data-value="{$row.id}">Activate</button>
                      {/if}
                      </div>
                    </div>
                  </div>
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
