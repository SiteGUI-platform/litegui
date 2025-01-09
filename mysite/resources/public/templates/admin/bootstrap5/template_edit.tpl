<form class="w-100" action="{$links.update}" method="post">
<div class="col-12 {if $system.sgframe}px-0{else}col-md-10 pt-sm-2{/if} pb-40 mx-auto">
  <div class="card">   
    <div class="card-body">
      <label class="col-form-label sg-hover-back px-3">
        {if $links.main}<a href="{$links.main}">{/if}<i class="bi bi-journal-code fs-3"></i>{if $links.main}</a>{/if}
        <span class="form-control-lg text-success">{"Template"|trans}{if $api.template.id}: {$api.template.name} <input type="hidden" name="template[id]" value="{$api.template.id}">{/if}</span>      
      </label>  
      {if $api.template.id}
      <div class="form-check form-switch pt-2 float-end">
        <input type="checkbox" class="form-check-input" id="syntax-mode">
        <label class="form-check-label" for="syntax-mode">Smarty</label>
      </div>        
      {/if}      
    </div>
    <div role="tabpanel">
      <!-- Nav tabs -->
      <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a href="#tab-main" aria-controls="tab-main" class="nav-link active" role="tab" data-bs-toggle="tab">{if $api.template.id}Edit {$api.template.file_name}{else}{'New'|trans}{/if}</a></li>
      </ul>
      <!-- Tab panes -->
      <div class="tab-content px-3 py-4">
        <div id="tab-main" class="tab-pane active" role="tabpanel">
          {if $api.template.id} 
            <textarea class="form-control" style="width: 100%;" id="templateEditor" name="template[content]" rows="20">{$api.template.content}</textarea>  
            {include "codemirror.tpl" target_editor="#templateEditor"} 
          {else}
            <div class="row">
              {if $html.templates OR $api.template.name}
              <div class="col-md mt-2">
                <div class="card text-center">
                  <div class="card-header">{"New :item"|trans:['item' => 'Template File']}</div>
                  <div class="card-body py-sm-5">
                    <div class="row gx-1">
                      <div class="col-12 mt-2">
                        <div class="input-group">
                          <label class="input-group-text" for="inputGroup">{"Template"|trans}</label>
                          <select class="form-select" id="inputGroup" name="template[name]">
                          {foreach $html.templates as $tpl}
                            <option value="{$tpl}" {if $tpl eq $site.template}selected{/if}>{$tpl}</option>
                          {foreachelse}
                            <option value="{$api.template.name}">{$api.template.name}</option>
                          {/foreach}
                          </select>  
                        </div>
                      </div>
                      <div class="col-12 mt-2">
                        <input class="form-control" type="text" name="template[file_name]" size="35" placeholder="{'File name without extension'|trans}">
                      </div>
                    </div> 
                    <button type="submit" class="btn btn-primary mt-4">{"Create"|trans}</button>
                  </div>
                </div>
              </div>  
              {/if}
              {if !$api.template.name}
              <div class="col-md mt-2">
                <div class="card text-center">
                  <div class="card-header">{"New :item"|trans:['item' => 'Template']}</div>
                  <div class="card-body p-sm-5">
                    <div class="row">
                      <div class="col-12 mt-2">
                        <input class="form-control" type="text" name="template[new_name]" size="35" placeholder="{'Template name'|trans}">
                      </div>
                      <div class="col-12 mt-2 text-start">
                        <div class="form-check form-switch col-form-label">
                          <input type="checkbox" class="form-check-input" id="clone-switch" name="template[clone]" value="1" >
                          <label class="form-check-label" for="clone-switch">{"Clone the default template's files"|trans}</label>
                        </div> 
                      </div>
                    </div> 
                    <button type="submit" class="btn btn-success mt-4">{"Create"|trans}</button>
                    {if $links.copy}<a href="{$links.copy}" class="btn btn-outline-primary mt-4">{"Create from existing Website"|trans}</a>{/if}
                  </div>
                </div>
              </div>
              {/if}
            </div>  
          {/if}
        </div>      
      </div>  
    </div>
    {if $api.template.id}
    <div class="card-footer text-center">
      <span><button type="submit" class="btn btn-lg btn-primary my-2"><i class="bi bi-save pe-2"></i>  {"Save"|trans}</button></span>
    </div> 
    {/if}   
  </div>
</div>  
</form>