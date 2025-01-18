<div class="col-12 {if $system.sgframe}px-0{else}col-md-10 pt-sm-2{/if} pb-40 mx-auto">
  <div class="card">   
    <div class="card-body">
      <label class="col-form-label sg-hover-back px-3">
        {if $links.main}<a href="{$links.main}">{/if}<i class="bi bi-bookmark fs-4"></i>{if $links.main}</a>{/if}
        <span class="form-control-lg text-success">{"Activity"|trans}</span>      
      </label>     
    </div>
    <div role="tabpanel">
      <!-- Nav tabs -->
      <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a href="#tab-main" aria-controls="tab-main" class="nav-link active" role="tab" data-bs-toggle="tab">{'Details'|trans}</a></li>
      </ul>
      <!-- Tab panes -->
      <div class="tab-content px-3 py-4">
        <div id="tab-main" class="tab-pane active" role="tabpanel">
          {if $links.ref}
          	<a class="text-decoration-none d-block mb-2" href="#" data-url="{$links.ref}?sgframe=1" data-title="{$html.ref}" data-bs-toggle="modal" data-bs-target="#dynamicModal">{$html.ref}</a>
          {/if}
          <textarea class="form-control" style="width: 100%;" id="templateEditor" rows="20">{$api.activity}</textarea>  
            {include "codemirror.tpl" target_editor="#templateEditor"} 
        </div>      
      </div>  
    </div>
    <div class="card-footer text-center">
      <span id="syntax-mode"></span>
    </div> 
  </div>
</div> 