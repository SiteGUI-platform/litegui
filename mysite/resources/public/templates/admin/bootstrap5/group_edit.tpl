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
        {if $api.group.id > 0}<input name='group[id]' type='hidden' value='{$api.group.id}'>{/if} 
        <input class="input-name form-control-lg text-success" type='text' title='Enter group name here' name='group[name]' value="{$api.group.name|default:$html.request.name}" placeholder="{'Group Name'|trans}" required>
      </div>
    </div>
    <div group="tabpanel">
      <ul class="nav nav-tabs" group="tablist">
        <li class="nav-item">
          <a href="#tab-group" aria-controls="tab-group" class="nav-link active" group="tab" data-bs-toggle="tab">
            {"Group"|trans}
          </a>
        </li>
        {if $api.group.id > 0}
        <li class="nav-item">
          <a href="#tab-members" aria-controls="tab-members" class="nav-link" group="tab" data-bs-toggle="tab">
            {"Users"|trans}
          </a>
        </li>
        {/if}
      </ul>
      <!-- Tab panes -->
      <div id="main-tab-content" class="sg-main tab-content">
        <div id="tab-group" class="tab-pane sg-form fade show active" group="tabpanel"> 
          <div class="form-group row my-3">      
            <label class="col-sm-3 col-form-label text-sm-end">{"Description"|trans}</label>
            <div class="col-sm-7">
              <input class="form-control" type="text" name="group[description]" value="{$api.group.description|default:$html.request.description}">
            </div>
          </div>
          {if $api.group.key}
          <div class="form-group row mb-3">      
            <label class="col-sm-3 col-form-label text-sm-end">{"Permissions"|trans}</label>
            <div class="col-sm-7">
               <span class="form-control">
              {"This client group is assigned the following permission"|trans}: Group::{$api.group.key}
               </span>
            </div>
          </div> 
          {/if} 
        </div>      
        <div id="tab-members" class="tab-pane fade" group="tabpanel">
          <div class="row">
          {if $api.total > 0}
            {$subapp = "User"}
            {include "datatable.tpl" forapp="$subapp"}
          {else}
            <div class="text-center"><a class="text-decoration-none" href="{$links.edit}">{'No member yet. Click here to add'|trans}</a></div>
          {/if}  
          </div>  
        </div>         
      </div>           
    </div>        
    <div class="card-footer text-center">
      <span><button type="submit" class="btn btn-lg btn-primary my-2"><i class="bi bi-save pe-2"></i>  {"Save"|trans}</button></span>
    </div>
  </div>
</form>
</div>
