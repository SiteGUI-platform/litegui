{if $api.config.app_configs OR $html.subapp_support}
<div class="col-12 {if $system.sgframe}px-0{else}col-md-10 pt-sm-2{/if} mx-auto">
  <form action="{$links.configure}" method="post" class="form-horizontal w-100">
	<div class="card">
	    <div class="card-body row g-0">
			<div class="col-auto pe-0 d-none d-sm-block sg-hover-back pt-1">
				{if $links.main}<a href="{$links.main}">{/if}
				<i class="bi bi-gear ps-3 fs-4"></i>
				{if $links.main}</a>{/if}
			</div>
			<div class="col p-2">
				<span class="form-control-lg text-success input-name border-0">{"Configure :item"|trans:['item' => $api.config.name|replace: '\\' : ' ➝ '|replace: '_' : ' '] }</span>
				<input class="form-control" type="hidden" name="name" value="{$api.config.name}">
			</div>
			<div class="col-auto ps-2 d-none d-sm-block">
				<button type="submit" name="save-btn" class="btn btn-outline-secondary border rounded-circle" title="{'Save'|trans}" style="height: 40px"><i class="bi bi-save"></i></button>
			</div>	  
	    </div> 
	    <div role="tabpanel">
		    <!-- Nav tabs -->
		    <ul id="main-tab" class="nav nav-tabs" role="tablist">
		    {if $api.config.app_configs}
		        <li class="nav-item"><a class="nav-link active" href="#tab-settings" aria-controls="tab-settings" class="nav-link" role="tab" data-bs-toggle="tab">{"Settings"|trans}</a></li>
	        {/if}
	        {if $html.subapp_support}
	        	{if $api.config.name != 'Core\Page' AND $api.config.name != 'Core\Product' AND $api.config.name != 'Core\Order' }
          	<li class="nav-item">
            	<button type="button" class="nav-link {if !$api.config.app_configs}active{/if}" data-bs-toggle="tab" data-bs-target="#tab-inputs" role="tab" aria-controls="tab-inputs" aria-selected="false">{"Custom Fields"|trans}</button>
          	</li>
          	{/if}
          	{if $api.config.name != 'Core\Order'}
		        <li class="nav-item"><a class="nav-link" href="#tab-subapp" aria-controls="tab-subapp" class="nav-link" role="tab" data-bs-toggle="tab">{"SubApps"|trans}</a></li>
		        {/if}
		    {/if} 
		    </ul>
		    <!-- Tab panes -->
		    <div id="main-tab-content" class="sg-main tab-content">
		    {if $api.config.app_configs}
		        <div id="tab-settings" class="tab-pane active px-4" role="tabpanel">
				  	{if $api.config.app_configs}
					  	<div class="form-text text-center mb-4 px-4 text-warning {if $html.hide_warning}d-none{/if}">{"Warning: values entered here get sent to the remote app for every app request. Do not enter any sensitive information such as password, token secret unless they are provided by the remote app for the authentication purpose"|trans}</div>
					    {include "form_field.tpl" formFields=$api.config.app_configs fieldPrefix='page'}
				    {/if}
				</div>
			{/if}
			{if $html.subapp_support}
				{if $api.config.name != 'Core\Page' AND $api.config.name != 'Core\Product' }
					{$supportedInputs = ['Text', 'Header', 'Lookup', 'File', 'Image', 'Checkbox', 'Select', 'Radio', 'Radio Hover', 'Rating', 'Percentage', 'Textarea', 'Password', 'URL', 'Email', 'Tel', 'Date', 'Time', 'Currency', 'Duration', 'Color', 'Country']} 
	        <div id="tab-inputs" class="tab-pane tab-content-full {if !$api.config.app_configs}active{/if}" role="tabpanel"> 
              <div class="card m-3">
                <div class="accordion accordion-flush">
                  <div class="accordion-item">
                    <label class="accordion-header w-100" for="tabbuttons-switch">
                      <div class="accordion-button" data-bs-toggle="collapse" data-bs-target="#collapse-buttons" aria-expanded="false" aria-controls="tab-buttons">
                        <div class="form-check form-switch">
                          <input type="checkbox" class="form-check-input" id="tabbuttons-switch" {if $api.config.app_buttons}checked{/if}>
                          <label class="form-check-label" for="tabbuttons-switch">{"Action Buttons"|trans}</label>
                        </div> 
                      </div>  
                    </label>
                    <div id="collapse-buttons" class="accordion-collapse collapse {if $api.config.app_buttons}show{/if}" aria-expanded="false">
                      <div id="config_app_buttons" class="accordion-body pt-0 px-0"> 
                        <div data-index="0" class="variant row mx-0 py-3 border-bottom align-items-center">
                          <div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div>
                          <div class="col-2">
                            <b>{"Visibility"|trans}</b>
                          </div>
                          <div class="col"><b>{"Name"|trans}</b></div>
                          <div class="col"><b>{"Label"|trans}</b></div>
                          <div class="col"><b>{"Value"|trans}</b></div>
                          <div class="col-auto ps-0">
                          	<button type="button" class="btn btn-sm"><svg class="bi" width="16" height="16"></svg></button>
                          </div> 
                        </div>                             
                        {foreach $api.config.app_buttons AS $variant}
                        <div data-index="{$variant@index}" class="variant row mx-0 py-3 border-bottom align-items-center">
                          <div class="col-auto px-0"><i class="bi bi-grip-vertical text-black-50" aria-hidden="true"></i></div>
                          <div class="col-2">
                            <select class="form-select" name="page[config_app_buttons][{$variant@index}][visibility]"> 
                              <option value="staff" {if $variant.visibility eq staff}selected{/if}>{"Staff"|trans}</option>
                              <option value="client" {if $variant.visibility eq client}selected{/if}>{"Client"|trans}</option>
                              <option value="staff_client" {if $variant.visibility eq staff_client}selected{/if}>{"Staff"|trans} & {"Client"|trans}</option>
                              <option value="creator" {if $variant.visibility eq creator}selected{/if}>{"Creator Only"|trans}</option>
                            </select>
                          </div>
                          <div class="col"><input class="form-control field-name" type="text" name="page[config_app_buttons][{$variant@index}][name]" value="{$variant.name}"></div>
                          <div class="col">
                          	<div class="input-group">
                              <select class="form-select bg-{$variant.style}" name="page[config_app_buttons][{$variant@index}][style]" style="flex: none;" onchange="this.className='form-select bg-'+ this.value">
                                <option value="primary">Primary</option>
                                <option value="secondary">Secondary</option>
                                <option value="success">Success</option>
                                <option value="info">Info</option>
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                                <option value="warning">Warning</option>
                                <option value="danger">Danger</option>
                              </select>
                          		<input class="form-control" type="text" name="page[config_app_buttons][{$variant@index}][label]" value="{$variant.label}">
                          	</div>
                          </div>	
                          <div class="col"><input class="form-control" type="text" name="page[config_app_buttons][{$variant@index}][value]" value="{$variant.value}"></div>
                          <div class="col-auto ps-0">
                          	<button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle btn-delete my-1" data-confirm="delete" {if $variant@first}disabled="disabled"{/if}><i class="bi bi-trash"></i></button>
                          </div> 
                        </div>
                        {foreachelse}
                        <div data-index="0" class="variant row mx-0 py-3 border-bottom align-items-center">
                          <div class="col-auto px-0"><i class="bi bi-grip-vertical text-black-50" aria-hidden="true"></i></div>
                          <div class="col-2">
                            <select class="form-select" name="page[config_app_buttons][0][visibility]"> 
                              <option value="staff" {if $variant.visibility eq staff}selected{/if}>{"Staff"|trans}</option>
                              <option value="client" {if $variant.visibility eq client}selected{/if}>{"Client"|trans}</option>
                              <option value="staff_client" {if $variant.visibility eq staff_client}selected{/if}>{"Staff"|trans} & {"Client"|trans}</option>
                              <option value="creator" {if $variant.visibility eq creator}selected{/if}>{"Creator Only"|trans}</option>
                            </select>
                          </div>
                          <div class="col"><input class="form-control field-name" type="text" name="page[config_app_buttons][0][name]" value=""></div>
                          <div class="col">
                          	<div class="input-group">
                              <select class="form-select bg-primary" name="page[config_app_buttons][0][style]" style="flex: none;" onchange="this.className='form-select bg-'+ this.value">
                                <option value="primary">Primary</option>
                                <option value="secondary">Secondary</option>
                                <option value="success">Success</option>
                                <option value="info">Info</option>
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                                <option value="warning">Warning</option>
                                <option value="danger">Danger</option>
                              </select>
                          		<input class="form-control" type="text" name="page[config_app_buttons][0][label]" value="">
                          	</div>
                          </div>	
                          <div class="col"><input class="form-control" type="text" name="page[config_app_buttons][0][value]" value=""></div>
                          <div class="col-auto ps-0">
                          	<button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle btn-delete my-1" data-confirm="delete" disabled="disabled"><i class="bi bi-trash"></i></button>
                          </div> 
                        </div>
                        {/foreach}
                        <div class="text-center pt-3">
                          <button type="button" class="btn border-0 new-row"><i class="bi bi-plus-circle-fill fs-3 text-warning"></i></button>
                        </div>
                      </div>
                    </div>
                  </div>        
                </div>
              </div>

	            <div class="px-3"></div>
	            <div id="config_app_fields" class="mt-4 border-top">
	              <div class="row mx-0 py-3 border-bottom align-items-center font-weight-bold">
	                <div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div> 
	                <div class="col-2 ps-4"><b>{"Type"|trans}</b></div>  
	                <div class="col ps-4"><b>{"Name"|trans}</b></div>
	                <div class="col ps-4"><b>{"Label"|trans}</b></div>
	                <div class="col ps-4"><b>{"Description"|trans}</b></div>
	                <div class="col-auto ps-0 text-center"><button type="button" class="btn btn-sm"><svg class="bi" width="16" height="16"></svg></button></div>
	              </div>
	              {foreach from=$api.config.app_fields key=name item=variant}
	              <div data-index="{$variant@index}" class="variant row mx-0 py-3 border-bottom align-items-center">
	                <input type="hidden" name="page[config_app_fields][{$variant@index}][id]" value="{$variant.id}">
	                <div class="col-auto px-0"><i class="bi bi-grip-vertical text-black-50" aria-hidden="true"></i></div>
	                <div class="col-2">
	                  <select class="form-select select-handler" name="page[config_app_fields][{$variant@index}][type]">
	                    {foreach $supportedInputs as $input}
	                    <option value="{$input|lower}" {if $variant.type eq $input|lower}selected{/if}>{$input}</option>
	                    {/foreach}
	                  </select>
	                </div>
	                <div class="col"><input class="form-control field-name" type="text" name="page[config_app_fields][{$variant@index}][name]" value="{$name}"></div>
	                <div class="col"><input class="form-control" type="text" name="page[config_app_fields][{$variant@index}][label]" value="{$variant.label}"></div>
	                <div class="col"><input class="form-control" type="text" name="page[config_app_fields][{$variant@index}][description]" value="{$variant.description}"></div>
	                <div class="col-auto ps-0"><button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle my-1" data-bs-toggle="collapse" data-bs-target="#config_app_fields{$variant@index}"><i class="bi bi-chevron-expand"></i></button></div>
	                <div class="col-12 collapse" id="config_app_fields{$variant@index}">
	                  <div class="row pt-3">
	                    <div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div> 
	                    <div class="col-2">
	                      <select class="form-select" name="page[config_app_fields][{$variant@index}][visibility]"> 
	                        <optgroup label="{'Visibility'|trans}">
	                          <option value="client_editable" {if $variant.visibility eq client_editable}selected{/if}>{"Client Editable"|trans}</option>
	                          <option value="client_hidden" {if $variant.visibility eq client_hidden}selected{/if}>{"Client Hidden"|trans}</option>
	                          <option value="client_readonly" {if $variant.visibility eq client_readonly}selected{/if}>{"Client ReadOnly"|trans}</option>
	                          <option value="staff_client_readonly" {if $variant.visibility eq staff_client_readonly}selected{/if}>{"ReadOnly"|trans}</option>
	                          <option value="hidden" {if $variant.visibility eq hidden}selected{/if}>{"Hidden"|trans}</option>
	                          <option value="readonly" {if $variant.visibility eq readonly}selected{/if}>{"Staff ReadOnly"|trans}</option>
	                          <option value="editable" {if $variant.visibility eq editable}selected{/if}>{"Staff Editable"|trans}</option>
	                        </optgroup>  
	                      </select>
	                    </div>
	                    <div class="col">
	                      <div class="row">
	                        <div class="col">
	                          <select class="form-select" name="page[config_app_fields][{$variant@index}][is]">
	                            <optgroup label="{'Input value is'|trans}">
	                              <option value="optional" {if $variant.is eq optional}selected{/if}>{"Optional"|trans}</option>
	                              <option value="required" {if $variant.is eq required}selected{/if}>{"Required"|trans}</option>
	                              <option value="multiple" {if $variant.is eq multiple}selected{/if}>{"Multiple Values"|trans}</option>
	                            </optgroup>
	                          </select>
	                        </div>
	                        <div class="col"><input class="form-control" type="text" name="page[config_app_fields][{$variant@index}][value]" value="{$variant.value}" placeholder="{'Default Value'|trans}"></div>
	                        <div class="col col-form-label">
	                          <div class="form-check form-switch">
	                            <input type="checkbox" class="form-check-input" id="field-is-column{$variant@index}" name="page[config_app_fields][{$variant@index}][column]" value="1" {if $variant.column}checked{/if}>
	                            <label class="form-check-label" for="field-is-column{$variant@index}">{"Listing Column"|trans}</label>
	                          </div>                      
	                        </div>
	                      </div>  
	                    </div>  
	                    <div class="col-auto ps-0"><button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle btn-delete" data-confirm="delete" {if $variant@first}disabled="disabled"{/if}><i class="bi bi-trash"></i></button></div> 
	                  </div> 
	                  <div class="row showhide-option pt-3 {if $variant.type != 'radio' && $variant.type != 'radio hover' && $variant.type != 'select'}d-none{/if}">
	                    <div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div> 
	                    <div class="col-2 col-form-label text-end">{"Options"|trans}</div>
	                    <div class="col">
	                      <div class="row option-container">
	                        {foreach $variant.options as $option}
	                        <div class="col-4 mb-3 variant bg-transparent position-relative">
	                          <button class="btn border-0 position-absolute end-0 pe-sm-4 js-show-on-hover {if $option@index < 3}text-white" disabled="disabled{/if}" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
	                          <input class="form-control" type="text" name="page[config_app_fields][{$variant@index}][options][]" value="{$option}">
	                        </div>
	                        {foreachelse}
	                        <div class="col-4 mb-3 variant bg-transparent position-relative">
	                          <button class="btn border-0 position-absolute end-0 pe-sm-4 js-show-on-hover text-white" disabled="disabled" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
	                          <input class="form-control" type="text" name="page[config_app_fields][{$variant@index}][options][]" placeholder="{'option 1'|trans}">
	                        </div>                            
	                        <div class="col-4 mb-3 variant bg-transparent position-relative">
	                          <button class="btn border-0 position-absolute end-0 pe-sm-4 js-show-on-hover text-white" disabled="disabled" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
	                          <input class="form-control" type="text" name="page[config_app_fields][{$variant@index}][options][]" placeholder="{'option 2'|trans}">
	                        </div>                            
	                        <div class="col-4 mb-3 variant bg-transparent position-relative">
	                          <button class="btn border-0 position-absolute end-0 pe-sm-4 js-show-on-hover text-white" disabled="disabled" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
	                          <input class="form-control" type="text" name="page[config_app_fields][{$variant@index}][options][]" placeholder="{'another option'|trans}">
	                        </div>                                                        
	                        {/foreach}
	                      </div>  
	                    </div>
	                    <div class="col-auto ps-0 add-option"><button type="button" class="btn btn-sm border-0 mt-1"><i class="bi bi-plus-square text-info"></i></button></div> 
	                  </div> 
	                </div>  
	              </div>
	              {foreachelse}
	              <div data-index=0 class="variant row mx-0 py-3 border-bottom align-items-center">
	                <div class="col-auto px-0"><i class="bi bi-grip-vertical text-black-50" aria-hidden="true"></i></div>
	                <div class="col-2">
	                  <select class="form-select select-handler" name="page[config_app_fields][0][type]">
	                    {foreach $supportedInputs as $input}
	                    <option value="{$input|lower}">{$input}</option>
	                    {/foreach}
	                  </select>
	                </div>
	                <div class="col"><input class="form-control field-name" type="text" name="page[config_app_fields][0][name]" value=""></div>
	                <div class="col"><input class="form-control" type="text" name="page[config_app_fields][0][label]" value=""></div>
	                <div class="col"><input class="form-control" type="text" name="page[config_app_fields][0][description]" value=""></div>
	                <div class="col-auto ps-0"><button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle my-1" data-bs-toggle="collapse" data-bs-target="#config_app_fields0"><i class="bi bi-chevron-expand"></i></button></div>
	                <div class="col-12 collapse" id="config_app_fields0">
	                  <div class="row pt-3">
	                    <div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div> 
	                    <div class="col-2">
	                      <select class="form-select" name="page[config_app_fields][0][visibility]">
	                        <optgroup label="{'Visibility'|trans}">
	                          <option value="client_editable">{"Client Editable"|trans}</option>
	                          <option value="client_hidden">{"Client Hidden"|trans}</option>
	                          <option value="client_readonly">{"Client ReadOnly"|trans}</option>
	                          <option value="staff_client_readonly">{"ReadOnly"|trans}</option>
	                          <option value="hidden">{"Hidden"|trans}</option>
	                          <option value="readonly">{"Staff ReadOnly"|trans}</option>
	                          <option value="editable">{"Staff Editable"|trans}</option>
	                        </optgroup> 
	                      </select>
	                    </div>
	                    <div class="col">  
	                      <div class="row">
	                        <div class="col">
	                          <select class="form-select" name="page[config_app_fields][0][is]">
	                            <optgroup label="{'Input value is'|trans}">
	                              <option value="optional">{"Optional"|trans}</option>
	                              <option value="required">{"Required"|trans}</option>
	                              <option value="multiple">{"Multiple Values"|trans}</option>
	                            </optgroup>
	                          </select>
	                        </div>
	                        <div class="col"><input class="form-control" type="text" name="page[config_app_fields][0][value]" value="" placeholder="{'Default Value'|trans}"></div>
	                        <div class="col col-form-label">
	                          <div class="form-check form-switch">
	                            <input type="checkbox" class="form-check-input" id="field-is-column0" name="page[config_app_fields][0][column]" value="1">
	                            <label class="form-check-label" for="field-is-column0">{"Listing Column"|trans}</label>
	                          </div>                      
	                        </div>
	                      </div>
	                    </div>    
	                    <div class="col-auto ps-0"><button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle btn-delete" data-confirm="delete" disabled="disabled"><i class="bi bi-trash"></i></button></div> 
	                  </div> 
	                  <div class="row showhide-option pt-3 d-none">
	                    <div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div> 
	                    <div class="col-2 col-form-label text-end">{"Options"|trans}</div>
	                    <div class="col">
	                      <div class="row option-container">
	                        <div class="col-4 mb-3 variant bg-transparent position-relative">
	                          <button class="btn border-0 position-absolute end-0 pe-sm-4 js-show-on-hover text-white" disabled="disabled" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
	                          <input class="form-control" type="text" name="page[config_app_fields][0][options][]" placeholder="{'option 1'|trans}">
	                        </div>                            
	                        <div class="col-4 mb-3 variant bg-transparent position-relative">
	                          <button class="btn border-0 position-absolute end-0 pe-sm-4 js-show-on-hover text-white" disabled="disabled" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
	                          <input class="form-control" type="text" name="page[config_app_fields][0][options][]" placeholder="{'option 2'|trans}">
	                        </div>                            
	                        <div class="col-4 mb-3 variant bg-transparent position-relative">
	                          <button class="btn border-0 position-absolute end-0 pe-sm-4 js-show-on-hover text-white" disabled="disabled" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
	                          <input class="form-control" type="text" name="page[config_app_fields][0][options][]" placeholder="{'another option'|trans}">
	                        </div>                                                        
	                      </div>  
	                    </div>
	                    <div class="col-auto ps-0 add-option"><button type="button" class="btn btn-sm border-0 mt-1"><i class="bi bi-plus-square text-info"></i></button></div> 
	                  </div> 
	                </div>  
	              </div>
	              {/foreach}                 
	              <div class="text-center pt-3">
	                <button type="button" class="btn border-0 new-row"><i class="bi bi-plus-circle-fill fs-2 text-info"></i></button>
	              </div>
	            </div>
	        </div>
	      {/if}    
				<div id="tab-subapp" class="tab-pane tab-content-full" role="tabpanel">
					<div id="config_app_sub">
					  <div class="row mx-0 mb-3">
					    <small class="form-text text-secondary col-12 px-4 pb-3">{"Extend this App by adding others as its supplementary apps"|trans}</small>    
					  </div>
					  <div class="row mx-0 pb-2 border-bottom align-items-center font-weight-bold">
					    <div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div> 
					    <div class="col ps-4"><b>{"App"|trans}</b></div>
              <div class="col ps-4"><b>{"Alias"|trans}</b></div>
					    <div class="col ps-4"><b>{"Entry/User"|trans}</b></div>
					    <div class="col ps-4"><b>{"Listing Style"|trans}</b></div>
					    <div class="col-auto ps-0 text-center">
					      <button type="button" class="btn btn-sm"><svg class="bi" width="16" height="16"></svg></button>
					    </div>
					  </div>
					  {foreach $api.config.app_sub AS $app_sub => $variant}
					  <div data-index={$variant@index} class="variant row mx-0 py-3 border-bottom align-items-center">
					    <div class="col-auto px-0"><i class="bi bi-grip-vertical text-black-50" aria-hidden="true"></i></div>
					    <div class="col">
					      <input class="form-control js-subapp-name" type="text" name="page[config_app_sub][{$variant@index}][name]" value="{$app_sub}">
					    </div>
              <div class="col">
                <input class="form-control" type="text" name="page[config_app_sub][{$variant@index}][alias]" value="{$variant.alias}">
              </div>
					    <div class="col">
					      <select class="form-select" name="page[config_app_sub][{$variant@index}][entry]">
					        <option value="multiple" {if $variant.entry == multiple}selected{/if}>{"Multiple Entries"|trans}</option>
					        <option value="single" {if $variant.entry == single}selected{/if}>{"Single Entry"|trans}</option>
                  <option value="quick" {if $variant.entry == quick}selected{/if}>{"Quick Single Entry"|trans}</option>
                  <option value="other_readonly" {if $variant.entry == other_readonly}selected{/if}>{"Creator Only"|trans}</option>
                  <option value="creator_readonly" {if $variant.entry == creator_readonly}selected{/if}>{"Creator ReadOnly"|trans}</option>
                  <option value="client_readonly" {if $variant.entry == client_readonly}selected{/if}>{"Client ReadOnly"|trans}</option>
                  <option value="readonly" {if $variant.entry == readonly}selected{/if}>{"Staff ReadOnly"|trans}</option>
                  <option value="staff_client_readonly" {if $variant.entry == staff_client_readonly}selected{/if}>{"ReadOnly"|trans}</option>
					      </select>
					    </div>
					    <div class="col">
					      <select class="form-select" name="page[config_app_sub][{$variant@index}][display]">
					        <option value="table" {if $variant.display == table}selected{/if}>{"Table"|trans}</option>
					        <option value="grid" {if $variant.display == grid}selected{/if}>{"Grid"|trans}</option>
					        <option value="kanban" {if $variant.display == kanban}selected{/if}>{"Kanban"|trans}</option>
					        <option value="flat" {if $variant.display == flat}selected{/if}>{"Flat Discussion"|trans}</option>
					        <!--option value="threaded" {if $variant.display == threaded}selected{/if}>{"Threaded Discussion"|trans}</option>
					        <option value="single" {if $variant.display == single}selected{/if}>{"Single Entry"|trans}</option-->
					        <option value="client_hidden" {if $variant.display == client_hidden}selected{/if}>{"Client Hidden"|trans}</option>
					      </select>
					    </div>   
					    <div class="col-auto ps-0">
					      <button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle btn-delete my-1" data-confirm="delete" {if $variant@first}disabled="disabled"{/if}><i class="bi bi-trash"></i></button>
					    </div>
					  </div>              
					  {foreachelse}
					  <div data-index=0 class="variant row mx-0 py-3 border-bottom align-items-center">
					    <div class="col-auto px-0"><i class="bi bi-grip-vertical text-black-50" aria-hidden="true"></i></div>
					    <div class="col">
					      <input class="form-control js-subapp-name" type="text" name="page[config_app_sub][0][name]" value="">
					    </div>
              <div class="col">
                <input class="form-control" type="text" name="page[config_app_sub][0][alias]" value="">
              </div>
					    <div class="col">
					      <select class="form-select" name="page[config_app_sub][0][entry]">
					        <option value="multiple">{"Multiple Entries"|trans}</option>
					        <option value="single">{"Single Entry"|trans}</option>
                  <option value="quick">{"Quick Single Entry"|trans}</option>
                  <option value="other_readonly">{"Creator Only"|trans}</option>
                  <option value="creator_readonly">{"Creator ReadOnly"|trans}</option>
                  <option value="client_readonly">{"Client ReadOnly"|trans}</option>
                  <option value="readonly">{"Staff ReadOnly"|trans}</option>
                  <option value="staff_client_readonly">{"ReadOnly"|trans}</option>
					      </select>
					    </div>
					    <div class="col">
					      <select class="form-select" name="page[config_app_sub][0][display]">
					        <option value="table">{"Table"|trans}</option>
					        <option value="grid">{"Grid"|trans}</option>
					        <option value="kanban">{"Kanban"|trans}</option>
					        <option value="flat">{"Flat Discussion"|trans}</option>
					        <!--option value="threaded">{"Threaded Discussion"|trans}</option>
					        <option value="single">{"Single Entry"|trans}</option-->
					        <option value="client_hidden">{"Client Hidden"|trans}</option>
					      </select>
					    </div>   
					    <div class="col-auto ps-0">
					      <button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle btn-delete my-1" data-confirm="delete"disabled="disabled"><i class="bi bi-trash"></i></button>
					    </div>
					  </div> 
					  {/foreach}
					  <div class="text-center py-4">
					    <button type="button" class="btn border-0 new-row"><i class="bi bi-plus-circle-fill fs-2 text-info new-fieldset"></i></button>
					  </div>
					</div>  
		        </div>
	    {/if}
	    </div>
    </div>    	
  	<div class="card-footer text-center">
      <button type="submit" name="save-btn" class="btn btn-lg btn-primary my-2" {$onclick}><i class="bi bi-save pe-2"></i>  {"Save"|trans}</button>
    </div>
	</div>  
  </form>
</div>
{if $api.config.app_show}
<!-- here for js to add to automation--> 
<div id="default-inputs" class="d-none">
	{foreach $api.config.app_show AS $key => $value}
		<input type="checkbox" class="form-check-input field-name" id="{$key}" checked>
	{/foreach}
</div>
{/if}
{* copy tab app_sub/automation and js code to here and replace #app_sub/[app_sub]/#automation/[automation] with #config_app_sub/[config_app_sub]/#config_app_automation/[config_app_automation] *}
<script>
document.addEventListener("DOMContentLoaded", function(e){
  var cloneRow = function(target, row) {
    newIndex = 0; //$('#variant-table').find('tr').length;
    $(target).find('> div.variant').each(function() {
      newIndex = Math.max($(this).attr('data-index'), newIndex);
    });
    newIndex++;            
    newRow = row.clone();
    newRow.attr("data-index", newIndex)
    newRow.find('[data-confirm="delete"]').prop("disabled", false);
    newRow.find('[data-bs-toggle="collapse"]').attr("data-bs-target", "#" + target.replace('#', '') + newIndex);
    newRow.find('.collapse').attr("id", target.replace('#', '') + newIndex);
              
    newRow.find("[name^='page["+ target.replace('#', '') +"]']").each(function(){
        $(this).attr('name', $(this).attr('name').replace(/\[\d+\]/, '['+ newIndex +']') );
    });
    newRow.find("input.form-check-input").each(function(){
        $(this).attr('id', $(this).attr('id').replace(/\d+$/, newIndex) );
    }); 
    newRow.find("label.form-check-label").each(function(){
        $(this).attr('for', $(this).attr('for').replace(/\d+$/, newIndex) );
    });                
    newRow.find("input[name='page["+ target.replace('#', '') +"]["+newIndex+"][id]']").attr("value", "");
    newRow.insertAfter(row);
  }  
  var initFormField = function(target) {
    $(target).on('click', '.add-option', function(e) {
      var lastCol = $(this).parent().find('.option-container').children().last();
      lastCol.clone()
        .insertAfter(lastCol)
        .find('[data-confirm="delete"]').removeClass("text-white").prop('disabled', false)
    })

    $(target).on('change', '.select-handler', function(e) {
      if (this.value == 'select' || this.value == 'radio' || this.value == 'radio hover' ){
        $(this).parent().parent().find('.showhide-option').removeClass('d-none');
      } else {
        $(this).parent().parent().find('.showhide-option').addClass('d-none');
      }

      if(this.value == 'lookup') {
        $(this).parent().parent().find('.is-multiple').removeClass('d-none');
        $(this).parent().parent().find('.is-required').addClass('d-none');
      } else {
        $(this).parent().parent().find('.is-multiple').addClass('d-none');
        $(this).parent().parent().find('.is-required').removeClass('d-none');
      }
    });
    $(target).find('.new-row').on('click', function(e) {
      cloneRow(target, $(target).find('> div.variant:last') )
    })

    $(target).sortable({
      items: "> div.variant",
      axis: 'y',
      handle: ".bi-grip-vertical",
    });
  }  
  initFormField('#config_app_fields');
  initFormField('#config_app_buttons');
  initFormField('#config_app_sub');  
  initFormField('#config_app_automation'); 
  
  $('#main-tab-content').on('click', '[data-confirm="delete"]', function(e) { 
    $(this).closest('.variant').remove();
  });
  //action tab
  $('#config_app_automation').on('show.bs.dropdown', '.trigger', function (e) { 
    if ( $(e.currentTarget).is('.trigger-target') || $(e.currentTarget).is('.trigger-param:nth-child(odd)') ){
      let $action = $(e.currentTarget).closest('.variant').find('.trigger-action').val();
      if ( $action == 'email' || $action == 'notify' ){
        $(e.currentTarget).find('.dropdown-menu').html('<li class="dropdown-header">{"Email address or Permission/Lookup input"|trans}</li>\
          <li class="dropdown-item" data-value="{ldelim}{ldelim}creator{rdelim}{rdelim}">{ldelim}{ldelim}creator{rdelim}{rdelim}: {"user first created the record"|trans}</li>\
          <li class="dropdown-item" data-value="{ldelim}{ldelim}supervisor{rdelim}{rdelim}">{ldelim}{ldelim}supervisor{rdelim}{rdelim}: {"staff who can manage records (can be multiple)"|trans}</li>');
        return;  
      } else if ( $action == 'http' ){
        $(e.currentTarget).find('.dropdown-menu').html('<li class="dropdown-header">{"Target is an HTTPS URL, and 1st param is POST or GET"|trans}</li>');
        return;  
      } else if ( $action == 'update' ){
        $(e.currentTarget).find('.dropdown-menu').html('<li class="dropdown-item" data-value="App">{"Target is App, and 1st param is the app name"|trans}</li>');//clear
        return;
      } else {
        $(e.currentTarget).find('.dropdown-menu').html('<li class="dropdown-item" data-value="$">{"Temporary variable $"|trans}</li>'); //clear
      } 
    } else if ( $(e.currentTarget).is('.trigger-param') ){
      $(e.currentTarget).find('.dropdown-menu').html('<li class="dropdown-item" data-value="{ldelim}{ldelim}ip{rdelim}{rdelim}">{ldelim}{ldelim}ip{rdelim}{rdelim}: {"IP address of the poster"|trans}</li>\
      	<li class="dropdown-item" data-value="{ldelim}{ldelim}site.url{rdelim}{rdelim}">{ldelim}{ldelim}site.url{rdelim}{rdelim}: {"Site URL"|trans}</li>\
          <li class="dropdown-item" data-value="{ldelim}{ldelim}system.url{rdelim}{rdelim}">{ldelim}{ldelim}system.url{rdelim}{rdelim}: {"System URL"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}timestamp{rdelim}{rdelim}">{ldelim}{ldelim}timestamp{rdelim}{rdelim}: {"timestamp when this is triggered"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}poster{rdelim}{rdelim}">{"Poster"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}$${rdelim}{rdelim}">{ldelim}{ldelim}$${rdelim}{rdelim}: {"current temporary variable $$ value"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}$subapp.input_name{rdelim}{rdelim}">{"Subapp Input"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}$_previous.input_name{rdelim}{rdelim}">{"Previous Value"|trans} ($_previous.input_name, $_public.property)</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}$var1 + $var2{rdelim}{rdelim}">{ldelim}{ldelim}$var1 + $var2{rdelim}{rdelim}: {"equal"|trans} $var1 + $var2 \
          ({"or a constant i.e"|trans}: {ldelim}{ldelim}$view + 1{rdelim}{rdelim})</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}$var1 - $var2{rdelim}{rdelim}">{ldelim}{ldelim}$var1 - $var2{rdelim}{rdelim}: {"equal"|trans} $var1 - $var2 ($var {"can be"|trans} $subapp.input)</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}$var1 * $var2{rdelim}{rdelim}">{ldelim}{ldelim}$var1 * $ var2{rdelim}{rdelim}: {"equal"|trans} $var1 * $var2 ($var {"can be"|trans} $_previous.input)</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}$var1 / $var2{rdelim}{rdelim}">{ldelim}{ldelim}$var1 / $var2{rdelim}{rdelim}: {"equal"|trans} $var1 / $var2 ($var {"can be"|trans} $_public.property)</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}$var1 & $var2{rdelim}{rdelim}">{ldelim}{ldelim}$var1 & $var2{rdelim}{rdelim}: {"concatenate"|trans} $var1 {"and"|trans} $var2</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}$id{rdelim}{rdelim}">{ldelim}{ldelim}$id{rdelim}{rdelim}</li>');
    } else if ( $(e.currentTarget).is('.trigger-value') ){ 
      $(e.currentTarget).find('.dropdown-menu').html('<li class="dropdown-header">{"Dynamic Value"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}created{rdelim}{rdelim}">{ldelim}{ldelim}created{rdelim}{rdelim}: {"when {ldelim}{ldelim}record{rdelim}{rdelim} is created only"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}updated{rdelim}{rdelim}">{ldelim}{ldelim}updated{rdelim}{rdelim}: {"when {ldelim}{ldelim}record{rdelim}{rdelim} is updated only"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}posted{rdelim}{rdelim}">{ldelim}{ldelim}posted{rdelim}{rdelim}: {"when {ldelim}{ldelim}record{rdelim}{rdelim} is either created/updated"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}empty{rdelim}{rdelim}">{ldelim}{ldelim}empty{rdelim}{rdelim}: {"when input is null/empty"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}creator{rdelim}{rdelim}">{ldelim}{ldelim}creator{rdelim}{rdelim}: {"user first created the record, use with {ldelim}{ldelim}poster{rdelim}{rdelim}"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}supervisor{rdelim}{rdelim}">{ldelim}{ldelim}supervisor{rdelim}{rdelim}: {"staff who can manage records, use with {ldelim}{ldelim}poster{rdelim}{rdelim}"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}$${rdelim}{rdelim}">{ldelim}{ldelim}$${rdelim}{rdelim}: {"current temporary variable $$ value"|trans}</li>\
        <li class="dropdown-item" data-value="{ldelim}{ldelim}$id{rdelim}{rdelim}">{ldelim}{ldelim}$id{rdelim}{rdelim}</li>');
    } else {  
      $(e.currentTarget).find('.dropdown-menu').html('<li class="dropdown-item" data-value="{ldelim}{ldelim}record{rdelim}{rdelim}">{"Record"|trans}</li>\
      <li class="dropdown-item" data-value="{ldelim}{ldelim}poster{rdelim}{rdelim}">{"Poster"|trans}</li>\
      <li class="dropdown-item" data-value="$">{"Temporary variable $"|trans}</li>');//clear
    }
    
    $('#config_app_fields').find('.field-name').each(function() {
      if (value = $(this).val()){
        if ( $(e.currentTarget).is('.trigger-param:nth-child(even)') || $(e.currentTarget).is('.trigger-value') ){
          value = '{ldelim}{ldelim}$'+ value +'{rdelim}{rdelim}';
        }
        $(e.currentTarget).find('.dropdown-menu').append($('<li class="dropdown-item"></li>').attr('data-value', value).text(value));
      }
    });
    $('#default-inputs').find('.field-name:checked').each(function() {
      if (value = $(this).attr('id').replace('-switch', '') ){
        if ( $(e.currentTarget).is('.trigger-param:nth-child(even)') || $(e.currentTarget).is('.trigger-value') ){
          value = '{ldelim}{ldelim}$'+ value +'{rdelim}{rdelim}';
        }
        $(e.currentTarget).find('.dropdown-menu').append($('<li class="dropdown-item"></li>').attr('data-value', value).text(value));
      }
    });
    subapp = $('.js-subapp-name').first().val() || 'subapp'
    if ( !$(e.currentTarget).is('.trigger-param:nth-child(even)') && !$(e.currentTarget).is('.trigger-value') ){
      $(e.currentTarget).find('.dropdown-menu')
        .append($('<li class="dropdown-item"></li>').attr('data-value', '_public.property').text('_public.property'))
        .append($('<li class="dropdown-item"></li>').attr('data-value', subapp.toLowerCase() +'.input_name').text('{"Subapp Input"|trans}'))
      if ( $(e.currentTarget).is('.trigger-when') ){
        $(e.currentTarget).find('.dropdown-menu').append($('<li class="dropdown-item"></li>').attr('data-value', '_previous.input_name').text('{"Previous Value"|trans}'))
      } else {
        $(e.currentTarget).find('.dropdown-menu').append($('<li class="dropdown-item"></li>').attr('data-value', subapp ).text( subapp ) )
      } 
    }
  });
 
  $('#config_app_automation').on('click', '.dropdown-item', function(e) {
    $(this).parents('.js-selectable').find('input').val($(e.target).attr('data-value'));
  }); 

  $('#config_app_automation').on('click', '.add-param', function(e) {
    var lastCol = $(this).closest('.variant').find('.param-container').children().last();
    lastCol.clone().insertAfter(lastCol)
      .find('[data-confirm="delete"]').removeClass("text-white").prop('disabled', false);
  }); 
  $('#config_app_automation').on('click', '.sg-andor-input', function(e) {
    if (this.value == 'or'){
      $(this).parents('.variant').find('.sg-andor:not(:first)').html('<i class="bi bi-pause"></i>')
    } else {
      $(this).parents('.variant').find('.sg-andor:not(:first)').html('<i class="small px-1">&</i>')
    }
  })
  $('#config_app_automation').on('click', '.add-condition', function(e) {
    lastRow = $(this).parents('.variant').find('div.condition:last');
    newIndex = 0; //$('#variant-table').find('tr').length;
    $(this).parents('.variant').find('div.condition:last').each(function() {
      newIndex = Math.max($(this).attr('data-index'), newIndex);
    });
    newIndex++;            
    newRow = lastRow.clone();
    newRow.attr("data-index", newIndex)
    if ($(this).parents('.variant').find('.sg-andor-input:checked').val() == 'or'){
      newRow.find('.sg-andor').html('<i class="bi bi-pause"></i>') 
    } else {
      newRow.find('.sg-andor').html('<i class="small px-1">&</i>')
    }
    newRow.find('[data-bs-toggle="collapse"]').replaceWith(
      $(this).parents('.collapse')
      .find('.btn-delete-main')
      .clone()
      .addClass('border-light')
      .prop("disabled", false)
      .html('<i class="bi bi-x"></i>')
    )
    newRow.find("[name^='page[config_app_automation]']").each(function(){
        $(this).attr('name', $(this).attr('name').replace(/conditions\]\[\d+\]/, 'conditions]['+ newIndex +']') );
    });
    newRow.insertAfter(lastRow);
  })  
  $('#config_app_automation').on('click', '.add-action', function(e) {
    lastRow = $(this).parents('.collapse').find('div.action:last');
    newIndex = 0; //$('#variant-table').find('tr').length;
    $(this).parents('.collapse').find('div.action').each(function() {
      newIndex = Math.max($(this).attr('data-index'), newIndex);
    });
    newIndex++;            
    newRow = lastRow.clone();
    newRow.attr("data-index", newIndex)
    newRow.find('[data-confirm="delete"]:not(.param-container *)').prop("disabled", false).addClass('text-danger');
    newRow.find("[name^='page[config_app_automation]']").each(function(){
        $(this).attr('name', $(this).attr('name').replace(/actions\]\[\d+\]/, 'actions]['+ newIndex +']') );
    });
    newRow.insertAfter(lastRow);
  }); 
  $('#config_app_automation').on('click', '.js-sg-clone', function(e) {
    cloneRow('#config_app_automation', $(this).closest('div.variant') )
  })  

  $('.action-container').sortable({
    items: "div.action",
    axis: 'y',
    handle: ".bi-grip-vertical",
  });    
  $('#config_app_automation').find('.new-row').on('click', function(e) { //initiate sortable after adding new row
    $('.action-container').sortable({
      items: "div.action",
      axis: 'y',
      handle: ".bi-grip-vertical",
    });
  }); 

  $('.sg-message a').attr('target', '_blank') //force message link (dependent apps) to open in a new tab   
})
</script>
<script defer src="{$system.cdn}/{$template}/assets/js/postmessage.js" id="sg-post-message" data-origin="{$system.url}"></script>
{/if}
