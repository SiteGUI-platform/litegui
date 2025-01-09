<div class="col-12 {if $system.sgframe}px-0{else}col-md-10 pt-sm-2{/if} mx-auto">
<form class="w-100" action="{$links.update}" method="post">
  <div class="card">
    <div class="card-body row">
      <div class="col-auto pe-0 pt-2 d-none d-sm-block sg-hover-back">
        {if $links.main}<a href="{$links.main}">{/if}
        <i class="bi bi-bookmark px-3 fs-4"></i>
        {if $links.main}</a>{/if}
      </div>
      <div class="col ps-sm-0">  
        {if $api.role.id > 0}<input name='role[id]' type='hidden' value='{$api.role.id}'>{/if} 
        <input class="input-name form-control-lg text-success" type='text' title='Enter role name here' name='role[name]' value="{$api.role.name|default:$html.request.name}" placeholder="{'Role Name'|trans}" required>
        <input class="input-name form-control my-1 px-3 font-italic" type="text" name="role[description]" value="{$api.role.description|default:$html.request.description}" placeholder="{'Description'|trans}">
      </div>
    </div>
    <div role="tabpanel">
      <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
          <a href="#tab-role" aria-controls="tab-role" class="nav-link active" role="tab" data-bs-toggle="tab">
            {"Permissions"|trans}
          </a>
        </li>
        {if $api.role.id > 0}
        <li class="nav-item">
          <a href="#tab-members" aria-controls="tab-members" class="nav-link" role="tab" data-bs-toggle="tab">
            {"Members"}
          </a>
        </li>
        {/if}
      </ul>
      <!-- Tab panes -->
      <div id="main-tab-content" class="sg-main tab-content">
        <div id="tab-role" class="tab-pane fade show active" role="tabpanel">       
          <ul class="list-group list-group-flush">
            {if $html.global_role} 
            <li class="list-group-item py-4">
              <div class="form-check form-switch float-end">
                <input type="checkbox" class="form-check-input" id="input-role" name="role[object][global]">
                <label class="form-check-label" for="input-role"></label>
              </div> 
              <b>{"System Role"|trans}</b>
            </li>
            {/if}
            {foreach from=$api.role.permissions item=permission}
            <li class="list-group-item py-4">
              <div class="form-check form-switch float-end">
                <input type="checkbox" class="form-check-input" id="input{$permission@index}" name="role[permissions][{$permission.property}]" {if $permission.enabled OR $html.request.permissions[{$permission.property}]}checked{/if}>
                <label class="form-check-label" for="input{$permission@index}"></label>
              </div> 
              {$permission.property}<br />
              <b>{$permission.name}</b>: <i>{$permission.value}</i>
            </li>
            {/foreach}
          </ul> 
        </div>
        <div id="tab-members" class="tab-pane fade" role="tabpanel">
          {if $api.total > 0}
            <div class="row">
              {$subapp = "Member"}
              {include "datatable.tpl" forapp="$subapp"}
                        
              {if $links.edit}
              <script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function(e){        
                  $("#app-{$subapp}-header .sg-app-header").append( 
                    $('<button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#dynamicModal"></button>')
                    .attr('data-url', "{$links.edit}?sgframe=1") 
                    .attr('data-title', "{'New :item'|trans:['item' => $subapp|replace: '_':' ']}")
                    .text("{'New :item'|trans:['item' => $subapp|replace: '_':' ']}") 
                  )
                })  
              </script>  
              {/if}
              </div> 
            </div>  
            {*append var='html' value=$api.role.name index='app_label_plural'*}
          {else}
            <div class="text-center"><a class="text-decoration-none" href="{$links.edit}">{'No member yet. Click here to add'|trans}</a></div>
          {/if}
        </div>         
      </div>           
    </div>        
    <div class="card-footer text-center">
      <span><button type="submit" class="btn btn-lg btn-primary my-2"><i class="bi bi-save pe-2"></i>  {"Save"|trans}</button></span>
    </div>
  </div>
</form>
</div>  
<script type="text/javascript">
  document.addEventListener("DOMContentLoaded", function(e){
    $('#tab-members > .col-md-10').removeClass('col-md-10');
  })  
</script>