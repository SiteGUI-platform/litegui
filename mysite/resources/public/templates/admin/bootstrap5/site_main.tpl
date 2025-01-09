{extends "datagrid.tpl"} 

{block name="grid_item"}
                {if $site.id eq 1}
                  {$url = $system.url|replace:"://":"://w{$row.owner}."}
                {else}
                  {$url = $system.url|replace:"://w{$site.owner}":"://w{$row.owner}"}
                {/if}
                <div class="col js-sg-collection-item" data-filter="{$row.subtype|lower}">
                  <div class="thumbnail card border-0 h-100">
                    <a class="sg-img-container no-image {if $row.logo}pt-md-2{else}{/if} text-decoration-none mx-auto position-relative overflow-hidden" href="{$url}{$links.api}/{$row.id}/page?oauth=google&login=step1">
                      <img class="img-fluid card-img-top" src='{$row.logo|default:"https://ui-avatars.com/api/?size=80&rounded=1&length=4&font-size=.3&bold=1&background=random&b8ea86&color=417505&name={$row.name}"}' alt="{$row.name}" />
                    </a>  
                    <div class="card-body p-1"></div>
                    <div class="caption card-footer bg-white border-top-0">
                      <div class="row align-items-end">
                        <div class="col-auto pt-2 ">
                          <a class="" href="{$url}{$links.edit}/{$row.id}?oauth=google&login=step1"><i class="bi bi-gear"></i></a> 
                        </div>  
                        <div class="col pt-2 px-0 text-center">                         
                          <a class="fw-bold text-success text-break text-decoration-none pe-2" href="{$url}{$links.api}/{$row.id}/page?oauth=google&login=step1">{$row.name}</a> 
                        </div>
                        <div class="col-auto pt-2 text-end">
                          <a class="link-secondary" data-url="{$links.delete}" data-confirm="{'delete :Item'|trans:['item' => $row.name] }" data-name="id" data-value="{$row.id}"><i class="bi bi-trash"></i></a> 
                        </div>
                      </div>
                    </div>
                  </div>
                </div>    
{/block}
{block name="grid_menu"}
 
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
