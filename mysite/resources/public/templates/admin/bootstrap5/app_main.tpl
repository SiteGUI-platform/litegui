{$html.app_label_plural = 'Apps'}
{extends "datagrid.tpl"} 

{block name="grid_item"}
                <div class="col js-sg-collection-item" data-filter="{$row.subtype|lower}" {if $row.editable}data-owner="1"{elseif $row.account_id}data-purchased="1"{/if}>
                  <div class="thumbnail card border-0 h-100">
                    {if $row.image}
                    <img class="sg-img-container img-fluid card-img-top {if !$row.image}no-image mx-md-5 px-md-5{/if}" src='{$row.image|default:"https://ui-avatars.com/api/?size=120&rounded=1&length=4&font-size=.3&bold=1&background=random&b8ea86&color=417505&name={$row.name}"}' alt="" />
                    {else}
                    <div class="sg-img-container no-image mx-auto">
                      <span class="rounded-circle text-nowrap fw-bold d-flex align-items-center justify-content-center" style="width: 90px; height: 90px; font-size: 20px; background-color:{$row.style.bg}; color: {$row.style.color}">{$row.style.abbr}</span>
                    </div>
                    {/if}
                    <div class="card-body p-1"></div>
                    <div class="caption card-footer p-2 bg-white border-top-0">
                      <div class="row">
                        <div class="col">
                          <span class="text-decoration-none pe-2">{$row.name}</span> 
                        </div>
                        <div class="col-auto text-end">
                          <span class="card-text small">{$row.subtype|trans}{if $row.default} <i class="bi bi-star-fill text-warning"></i>{/if}
                          </span>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-auto pt-3 pe-0">
                          {if $links.register && $row.subtype == 'Core'}
                             <a class="link-secondary" data-url="{$links.register}" data-confirm="{'register :Item'|trans:['item' => $row.name] }" data-name="name" data-value="Core\{$row.slug}" role="button"><i class="bi bi-r-circle"></i></a>
                          {/if}
                          {if $links.deregister && $row.subtype == 'Core'}
                             <a class="link-danger" data-url="{$links.deregister}" data-confirm="{'deregister :Item'|trans:['item' => $row.name] }" data-name="name" data-value="Core\{$row.slug}" role="button"><i class="bi bi-box-arrow-down"></i></a>
                          {/if}     
                          {if $row.editable}
                            {if $row.subtype eq 'App' OR $row.subtype eq 'Core' OR $row.subtype eq 'Hook' OR $row.subtype eq 'Widget'}  
                            <a class="link-secondary" href="{$links.build}/{$row.id}"><i class="bi bi-pencil-square"></i></a> 
                            {/if}
                            <a class="link-warning" href="{$links.edit}/{$row.id}"><i class="bi bi-coin"></i></a> 
                            <a class="link-secondary" data-url="{$links.delete}" data-confirm="{'delete :Item'|trans:['item' => $row.name] }" data-name="id" data-value="{$row.id}" role="button"><i class="bi bi-trash"></i></a> 
                          {/if}
                          {if $links.show AND $row.activated AND ($row.subtype eq Core OR $row.subtype eq App OR $row.subtype eq Widget)}
                            <span class="text-secondary small js-sg-show" data-url="{$links.show}" data-app="{$row.subtype}\{$row.slug}" role="button" title="Show in Menu"><i class="bi bi-eye{if $row.hide}-slash{/if}"></i></button>
                          {/if}
                        </div>  
                        <div class="col pt-2 ps-0 text-end">
                          {if $links.configure && $row.activated } 
                            {if $row.subtype eq App OR $row.configurable}
                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-url="{$links.configure}?name={$row.subtype}/{$row.slug|replace:'_':' '|capitalize:true|replace:' ':'_'}&sgframe=1" data-bs-target="#dynamicModal" data-title="{'Configure :item'|trans:['item' => $row.name] }">{"Configure"|trans}</button>
                            {/if}
                            {if $row.id AND !$row.default} {* not default app *}
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-url="{$links.deactivate}" data-confirm="{'deactivate :Item'|trans:['item' => $row.name] }" data-name="name" data-value="{if $row.account_id gt 0 && $row.account_id != 'free'}{$row.account_id}{else}appstore-{$row.id}{/if}">{"Deactivate"|trans}</button>
                            {/if}
                          {elseif $links.activate && ($row.creator eq $site.owner || $row.published > 0) } {* when user == app owner == site owner *}
                            <button type="button" class="btn btn-sm btn-outline-primary" data-url="{$links.activate}" data-confirm="{'activate :Item'|trans:['item' => $row.name] }" data-name="name" data-value="{if $row.account_id}{$row.account_id}{else}appstore-{$row.id}{/if}">{"Activate"|trans}</button>
                          {/if}
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
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="pill" type="button" role="tab" aria-selected="true" data-filter="all">{"All"|trans}</button>
                        </li> 
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" type="button" role="tab" aria-selected="false" data-filter="core">{"Core"|trans}</button>
                        </li> 

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" type="button" role="tab" aria-selected="false" data-filter="app">{"App"|trans}</button>
                        </li> 
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" type="button" role="tab" aria-selected="false" data-filter="widget">{"Widget"|trans}</button>
                        </li> 
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" type="button" role="tab" aria-selected="false" data-filter="template">{"Template"|trans}</button>
                        </li> 
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" type="button" role="tab" aria-selected="false" data-filter="gateway">{"Gateway"|trans}</button>
                        </li>                     
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" type="button" role="tab" aria-selected="false" data-filter="fulfillment">{"Fulfillment"|trans}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" type="button" role="tab" aria-selected="false" data-filter="delivery">{"Delivery"|trans}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" type="button" role="tab" aria-selected="false" data-filter="notification">{"Notification"|trans}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" type="button" role="tab" aria-selected="false" data-filter="hook">{"Hook"|trans}</button>
                        </li> 
                        {foreach $html.app_menu as $level1}
                        <li class="nav-item" role="presentation">
                            <a class="text-sm-center nav-link" aria-current="page" href="{$level1.slug|default: '#'}">
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
{block name="grid_header"}
<script type="text/javascript">
  document.addEventListener("DOMContentLoaded", function(){
    //post via ajax to show/hide app
    $('.js-sg-show').on('click', function(ev) {
      ev.preventDefault();
      var el = this
      var href = $(el).attr('data-url');
      var nvp = 'show[app]='+ $(this).attr('data-app') +'&csrf_token='+ window.csrf_token +'&format=json';
      $.post(href, nvp, function(response){
        //console.log(response)
        if (response.status.result == 'success'){
          if (response.show){
            el.querySelector('i').classList.replace('bi-eye-slash', 'bi-eye')
            //el.parentNode.classList.remove('text-secondary')
          } else if (response.show == false) {
            el.querySelector('i').classList.replace('bi-eye', 'bi-eye-slash')
            //el.parentNode.classList.add('text-secondary')
          }  
        }
      }).fail( () => el.remove() )
    })   
  })
</script>
{/block}  
