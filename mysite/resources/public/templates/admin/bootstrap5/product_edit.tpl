{$api.app.name = product}
{extends "app_edit.tpl"}
{block name="APP_tabname"}
			<li class="nav-item"><button type="button" data-bs-target="#tab-variants" aria-controls="tab-variants" class="nav-link active" role="tab" data-bs-toggle="tab">{"Product Variants"|trans}</a></li>
{/block}
		<!-- Tab panes -->
{block name="APP_fields"}
			<div id="tab-variants" class="tab-pane fade tab-content-full show active" role="tabpanel"> 			
        <div id="variant-table" class="mb-2">
          <div class="row mx-0 py-3 border-bottom align-items-center font-weight-bold sticky-top bg-white">
            <div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div>	
            <div class="col-1 ps-0" data-style="min-width: 120px;"><span class="d-none d-sm-block">{"Images"|trans}</span></div>	
            <div class="col-2 px-0">{"SKU"|trans}</div>
            <div class="col ps-0">{"Price"|trans} ({$site.currency.prefix}{$site.currency.suffix})</div>
            <div class="col ps-0">{"Was"|trans} ({$site.currency.prefix}{$site.currency.suffix})</div>
            <div class="col ps-0">{"Stock"|trans}</div>
            {foreach from=$api.variants.0.options key=name item=value}
            <div class="col ps-0 sg-movable">{$name} <i class="bi bi-three-dots-vertical sg-option-menu float-end pe-2" role="button"></i><i class="bi bi-arrow-bar-left sg-move-left float-end pe-2 d-none" role="button"></i><i class="bi bi-x-octagon sg-removable float-end pe-2 d-none" role="button"></i></div>
            {/foreach}
            {foreach from=$api.variants.0.meta key=name item=value}    {if $name eq downloads}{continue}{/if}
            <div class="col ps-0 sg-movable">@{$name} <i class="bi bi-three-dots-vertical sg-option-menu float-end pe-2" role="button"></i><i class="bi bi-arrow-bar-left sg-move-left float-end pe-2 d-none" role="button"></i><i class="bi bi-x-octagon sg-removable float-end pe-2 d-none" role="button"></i></div>
            {/foreach}
            <div class="col-auto ps-0 text-center"><button type="button" class="btn btn-sm btn-outline-light rounded-circle js-add-property"><i class="bi bi-plus-square text-info" data-bs-toggle="modal" data-bs-target="#product-modal" title="{'Add Property'|trans}"></i></button></div>
          </div>
          {foreach from=$api.variants key=index item=variant}
          <div id="variant{$index}" class="variant row mx-0 py-3 border-bottom align-items-center position-relative sg-hover">
            <div class="col-auto px-0"><i class="bi bi-grip-vertical text-black-50" aria-hidden="true"></i>
            	<input type="hidden" name="page[variants][{$index}][id]" value="{$variant.id}"></div>
            <div class="col-1 ps-0">
							<img src="{$variant.images.0|default:'https://via.placeholder.com/120x80/5a5c69/fff?text=Add%20Image'}" class="btn img-thumbnail thumbnail-small p-0" data-bs-toggle="collapse" data-bs-target="#v{$index}images" title="{'Add Images'|trans}">
            </div>
            <div class="col-2 ps-0"><input class="form-control" type="text" name="page[variants][{$index}][sku]" value="{$variant.sku}"></div>
            <div class="col ps-0"><input class="form-control" type="text" name="page[variants][{$index}][price]" value="{$variant.price}"></div>
            <div class="col ps-0"><input class="form-control" type="text" name="page[variants][{$index}][was]" value="{$variant.was}"></div>
            <div class="col ps-0"><input class="form-control" type="text" name="page[variants][{$index}][stock]" value="{$variant.stock}"></div>
            {foreach from=$variant.options key=name item=value}
            <div class="col ps-0"><input class="form-control" type="text" name="page[variants][{$index}][{$name}]" value="{$value}"></div>
            {/foreach}
            {foreach from=$variant.meta key=name item=value}     {if $name eq downloads}{continue}{/if}
            <div class="col ps-0"><input class="form-control" type="text" name="page[variants][{$index}][@{$name}]" value="{$value}"></div>
            {/foreach}
            <div class="col-auto pe-2 sg-variant-controls">
							<div class="btn-group dropstart">
							  <button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle {if $variant.shipping.weight OR $variant.shipping.length OR $variant.meta.downloads.0}btn-outline-light text-primary{/if} js-add-shipping" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" data-bs-display="static"><i class="bi bi-{if $page.subtype ne Shipping}download{else}truck{/if}"></i></button>
							  <div class="dropdown-menu px-2 bg-warning-subtle" style="min-width: 200px; top: -1em">
							  	<div class="row g-2 download-options {if $page.subtype eq Shipping}d-none{/if}" id="v{$index}downloads" style="min-width: 400px;">
								  	<div class="col-12 text-center">{"Download Sources"|trans}</div>
							    	{foreach $variant.meta.downloads AS $path} {if !$path}{continue}{/if}
							    	<div class="col-12 js-download-container variant sg-hover position-relative px-0 rounded-2">
							      	<div class="form-floating">
										  	<input type="text" class="form-control get-download-callback" name="page[variants][{$index}][@downloads][]" value="{$path}" placeholder="{'File'|trans}" data-container="#v{$index}downloads" data-folder="l2_Lw" data-bs-toggle="modal" data-bs-target="#dynamicModal" title="{'Download'|trans}">
										  	<label>{"File"|trans}</label>
											</div>
											<button class="btn border-0 position-absolute end-0 top-50 translate-middle-y pe-sm-4 sg-hover-visible" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
								  	</div>
								  	{/foreach}
								  	<div class="col-12 js-download-container variant sg-hover position-relative px-0 rounded-2">
							      	<div class="form-floating">
										  	<input type="text" class="form-control get-download-callback" name="page[variants][{$index}][@downloads][]" value="" placeholder="{'File'|trans}" data-container="#v{$index}downloads" data-folder="l2_Lw" data-bs-toggle="modal" data-bs-target="#dynamicModal" title="{'Download'|trans}">
										  	<label>{"File"|trans}</label>
											</div>
											<button class="btn border-0 position-absolute end-0 top-50 translate-middle-y pe-sm-4 sg-hover-visible" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
								  	</div>
								  </div>	
							    <div class="row g-2 shipping-options {if $page.subtype ne Shipping}d-none{/if}">
							      <div class="col-6">
							      	<div class="form-floating">
											  <input type="text" class="form-control" name="page[variants][{$index}][shipping][weight]" value="{$variant.shipping.weight}" placeholder="{'W'|trans}">
											  <label>{"Weight"|trans} ({$html.config.mass_unit})</label>
											</div>
										</div>
							      <div class="col-6">
							      	<div class="form-floating">
											  <input type="text" class="form-control" name="page[variants][{$index}][shipping][length]" value="{$variant.shipping.length}" placeholder="{'L'|trans}">
											  <label>{"L"|trans} ({$html.config.distance_unit})</label>
											</div>
										</div>            
							      <div class="col-6">
							      	<div class="form-floating">
											  <input type="text" class="form-control" name="page[variants][{$index}][shipping][width]" value="{$variant.shipping.width}" placeholder="{'W'|trans}">
											  <label>{"W"|trans} ({$html.config.distance_unit})</label>
											</div>
										</div>						
							      <div class="col-6">
							      	<div class="form-floating">
											  <input type="text" class="form-control" name="page[variants][{$index}][shipping][height]" value="{$variant.shipping.height}" placeholder="{'H'|trans}">
											  <label>{"H"|trans} ({$html.config.distance_unit})</label>
											</div>
										</div>
							      <div class="col-12">
							      	<div class="form-floating">
										  	<input type="text" class="form-control" name="page[variants][{$index}][shipping][insurance_value]" value="{$variant.shipping.insurance_value}" placeholder="{'Insurance Value'|trans}">
										  	<label>{"Insurance Value"|trans} ({$site.currency.prefix}{$site.currency.suffix})</label>
											</div>
								  	</div>
							  	</div>	
							  </div>
							</div>
							<br>
            	<button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle mt-3" data-url="{$links.manage}/{$variant.id}" data-cors="1" data-confirm="delete" data-remove="#variant{$index}" data-name="id" data-value="{$variant.id}" {if $variant@first}disabled="disabled"{/if} title="{'Delete'|trans}"><i class="bi bi-trash"></i></button>
            </div>
            <div class="col-12 pt-3 collapse" id="v{$index}images">
              <div class="row align-items-top">
	          		<div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div> 
					    	<div class="carousel-multi col multiple-values" data-length="3" data-noloop="1" data-dynamic="1">
					    		<div class="carousel-inner item-removable row gx-0">
			          	{if $variant.images}{foreach $variant.images as $image}
					    			<div class="col position-relative">
					    				<img src="{$image}" class="img-thumbnail p-0 d-block mx-auto">
					    				<input type="hidden" name="page[variants][{$index}][images][]" value="{$image}">
										</div>
			        		{/foreach}{/if}	   
									</div>		
							    <button class="carousel-control-prev justify-content-start ms-3" type="button" data-bs-slide="prev">
							      <i class="bi bi-chevron-left control-icon"></i>
							      <span class="visually-hidden">{"Previous"|trans}</span>
							    </button>
							    <button class="carousel-control-next justify-content-end me-3" type="button" data-bs-slide="next">
							      <i class="bi bi-chevron-right control-icon"></i>
							      <span class="visually-hidden">{"Next"|trans}</span>
							    </button>
								</div>
          			<div class="col-auto ps-0 pe-2">
          				<button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle js-btn-fullscreen" title="{'Full screen'|trans}"><i class="bi bi-arrows-fullscreen pe-none"></i></button><br><br>
									
									<a class="btn btn-sm btn-outline-secondary border rounded-circle" href="https://{$site.account_url}/account/cart/add?id={$variant.id}&coupon=" target="_blank"><i class="bi bi-bag-plus"></i></a><br><br>
									
									<button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle js-sg-clone"><i class="bi bi-files"></i></button><br><br>
          				
          				<button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle get-image-callback position-relative" data-name="page[variants][{$index}][images][]" data-multiple="1" data-container="#v{$index}images .carousel-inner" data-folder="{$html.upload_dir}" data-bs-toggle="modal" data-bs-target="#dynamicModal" title="{'Upload'|trans}"><i class="bi bi-images"></i>
          					<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-danger">+</span>
          				</button>
          			</div> 
              </div> 
            </div>
            <i class="bi bi-chevron-expand text-secondary position-absolute start-50 bottom-0 sg-hover-visible" data-bs-toggle="collapse" data-bs-target="#v{$index}images" role="button"></i>
          </div>
          {foreachelse}
          <div class="variant row mx-0 py-3 border-bottom align-items-center position-relative sg-hover">
            <div class="col-auto px-0"><i class="bi bi-grip-vertical text-black-50" aria-hidden="true"></i></div>
            <div class="col-1 ps-0"><img src="https://via.placeholder.com/120x80/5a5c69/fff?text=Add%20Image" class="btn img-thumbnail thumbnail-small p-0" data-bs-toggle="collapse" data-bs-target="#v0images" title="{'Add Images'|trans}"></div>
            <div class="col-2 ps-0"><input class="form-control" type="text" name="page[variants][0][sku]" value=""></div>
            <div class="col ps-0"><input class="form-control" type="text" name="page[variants][0][price]" value=""></div>
            <div class="col ps-0"><input class="form-control" type="text" name="page[variants][0][was]" value=""></div>
            <div class="col ps-0"><input class="form-control" type="text" name="page[variants][0][stock]" value=""></div>
            <div class="col-auto pe-2 sg-variant-controls">
							<div class="btn-group dropstart">
							  <button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle {if $variant.shipping.weight OR $variant.shipping.length OR $variant.meta.downloads.0}btn-outline-light text-primary{/if} js-add-shipping" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" data-bs-display="static"><i class="bi bi-truck"></i></button>
							  <div class="dropdown-menu px-2 bg-warning-subtle" style="min-width: 200px; top: -1em">
							  	<div class="row g-2 download-options d-none" id="v0downloads" style="min-width: 400px;">
								  	<div class="col-12 text-center">{"Download Sources"|trans}</div>
								  	<div class="col-12 js-download-container variant sg-hover position-relative px-0 rounded-2">
							      	<div class="form-floating">
										  	<input type="text" class="form-control get-download-callback" name="page[variants][0][@downloads][]" value="" placeholder="{'File'|trans}" data-container="#v0downloads" data-folder="l2_Lw" data-bs-toggle="modal" data-bs-target="#dynamicModal" title="{'Download'|trans}">
										  	<label>{"File"|trans}</label>
											</div>
											<button class="btn border-0 position-absolute end-0 top-50 translate-middle-y pe-sm-4 sg-hover-visible" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
								  	</div>
								  </div>	
							  	<div class="row g-2 shipping-options">
							      <div class="col-6">
							      	<div class="form-floating">
											  <input type="text" class="form-control" name="page[variants][0][shipping][weight]" placeholder="{'W'|trans}">
											  <label>{"Weight"|trans}</label>
											</div>
										</div>
							      <div class="col-6">
							      	<div class="form-floating">
											  <input type="text" class="form-control" name="page[variants][0][shipping][length]" placeholder="{'L'|trans}">
											  <label>{"L"|trans}</label>
											</div>
										</div>            
							      <div class="col-6">
							      	<div class="form-floating">
											  <input type="text" class="form-control" name="page[variants][0][shipping][width]" placeholder="{'W'|trans}">
											  <label>{"W"|trans}</label>
											</div>
										</div>						
							      <div class="col-6">
							      	<div class="form-floating">
											  <input type="text" class="form-control" name="page[variants][0][shipping][height]" placeholder="{'H'|trans}">
											  <label>{"H"|trans}</label>
											</div>
										</div>
							      <div class="col-12">
							      	<div class="form-floating">
										  	<input type="text" class="form-control" name="page[variants][0][shipping][insurance_value]" placeholder="{'Insurance Value'|trans}">
										  	<label>{"Insurance Value"|trans}</label>
											</div>
								  	</div>
							  	</div>	
							  </div>
							</div>
							<br>
            	<button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle btn-delete mt-3" data-confirm="delete" disabled="disabled" title="{'Delete'|trans}"><i class="bi bi-trash"></i></button>
            </div>
            <div class="col-12 pt-3 collapse" id="v0images">
            	<div class="row align-items-top">
      					<div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div> 
		    				<div class="carousel-multi col multiple-values" data-length="3" data-noloop="1" data-dynamic="1">
		    					<div class="carousel-inner item-removable row gx-0"></div>		
							    <button class="carousel-control-prev justify-content-start ms-3" type="button" data-bs-slide="prev">
							      <i class="bi bi-chevron-left control-icon"></i>
							      <span class="visually-hidden">{"Previous"|trans}</span>
							    </button>
							    <button class="carousel-control-next justify-content-end me-3" type="button" data-bs-slide="next">
							      <i class="bi bi-chevron-right control-icon"></i>
							      <span class="visually-hidden">{"Next"|trans}</span>
							    </button>
								</div>
      					<div class="col-auto ps-0 pe-2">
    							<button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle js-btn-fullscreen" title="{'Full screen'|trans}"><i class="bi bi-arrows-fullscreen"></i></button><br><br>
									
									<button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle js-sg-clone"><i class="bi bi-files"></i></button><br><br>
          				
       						<button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle get-image-callback position-relative" data-name="page[variants][0][images][]" data-multiple="1" data-container="#v0images .carousel-inner" data-folder="{$html.upload_dir}" data-bs-toggle="modal" data-bs-target="#dynamicModal" title="{'Upload'|trans}"><i class="bi bi-images"></i>
       							<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-danger">+</span>
       						</button>
       					</div> 
              </div> 
            </div> 
            <i class="bi bi-chevron-expand text-secondary position-absolute start-50 bottom-0 sg-hover-visible" data-bs-toggle="collapse" data-bs-target="#v0images" role="button"></i>             
          </div>
          {/foreach}
          <style type="text/css">
          	.collapse.show + .bi-chevron-expand::before {
          		content: "\f27d";
          	}
          </style>   
          <div class="text-center pt-3">
            <button type="button" class="btn border-0 new-table-row" title="{'Add Variant'|trans}"><i class="bi bi-plus-circle-fill fs-2 text-info"></i></button>
          </div>                                  
        </div>
			</div>
			<div id="tab-custom" class="tab-pane fade tab-content-full" role="tabpanel">
        <div class="p-3 border-bottom text-center">{"Create custom fields here to obtain extra information from customers during the order process"|trans}</div>
        <div id="product_fields" class="mb-2 app-only">
          <div class="row mx-0 py-3 border-bottom align-items-center font-weight-bold">
            <div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div> 
            <div class="col-2 ps-4"><b>{"Type"|trans}</b></div>  
            <div class="col ps-4"><b>{"Name"|trans}</b></div>
            <div class="col ps-4"><b>{"Label"|trans}</b></div>
            <div class="col ps-4"><b>{"Description"|trans}</b></div>
            <div class="col-auto ps-0 text-center"><button type="button" class="btn btn-sm"><svg class="bi" width="16" height="16"></svg></button></div>
          </div>
          {$supportedInputs = ['Text', 'Header', 'Lookup', 'File', 'Image', 'Checkbox', 'Select', 'Radio', 'Radio Hover', 'Rating', 'Percentage', 'Textarea', 'Password', 'URL', 'Email', 'Tel', 'Date', 'Time', 'Currency', 'Duration', 'Color', 'Country']}
          {foreach from=$page.meta.product_fields key=name item=variant}
          <div data-index="{$variant@index}" class="variant row mx-0 py-3 border-bottom align-items-center">
            <input type="hidden" name="page[product_fields][{$variant@index}][id]" value="{$variant.id}">
            <div class="col-auto px-0"><i class="bi bi-grip-vertical text-black-50" aria-hidden="true"></i></div>
            <div class="col-2">
              <select class="form-select select-handler" name="page[product_fields][{$variant@index}][type]">
                {foreach $supportedInputs as $input}
                <option value="{$input|lower}" {if $variant.type eq $input|lower}selected{/if}>{$input}</option>
                {/foreach}
              </select>
            </div>
            <div class="col"><input class="form-control" type="text" name="page[product_fields][{$variant@index}][name]" value="{$name}"></div>
            <div class="col"><input class="form-control" type="text" name="page[product_fields][{$variant@index}][label]" value="{$variant.label}"></div>
            <div class="col"><input class="form-control" type="text" name="page[product_fields][{$variant@index}][description]" value="{$variant.description}"></div>
            <div class="col-auto ps-0"><button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle my-1" data-bs-toggle="collapse" data-bs-target="#product_fields{$variant@index}"><i class="bi bi-chevron-expand"></i></button></div>
            <div class="col-12 collapse" id="product_fields{$variant@index}">
              <div class="row pt-3">
                <div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div> 
                <div class="col-2">
                  <select class="form-select" name="page[product_fields][{$variant@index}][visibility]">
                    <optgroup label="{'Visibility'|trans}">
                      <option value="client_editable" {if $variant.visibility eq client_editable}selected{/if}>{"Client Editable"|trans}</option>
                      <option value="client_readonly" {if $variant.visibility eq client_readonly}selected{/if}>{"Client ReadOnly"|trans}</option>
                      <option value="client_hidden" {if $variant.visibility eq client_hidden}selected{/if}>{"Client Hidden"|trans}</option>
                      <option value="hidden" {if $variant.visibility eq hidden}selected{/if}>{"Hidden"|trans}</option>
                      <option value="readonly" {if $variant.visibility eq readonly}selected{/if}>{"Staff ReadOnly"|trans}</option>
                      <option value="editable" {if $variant.visibility eq editable}selected{/if}>{"Staff Editable"|trans}</option>
                    </optgroup>  
                  </select>
                </div>
                <div class="col">
                  <div class="row">
                    <div class="col">
                      <select class="form-select" name="page[product_fields][{$variant@index}][is]">
                        <optgroup label="{'Input value is'|trans}">
                          <option value="optional" {if $variant.is eq optional}selected{/if}>{"Optional"|trans}</option>
                          <option value="required" {if $variant.is eq required}selected{/if}>{"Required"|trans}</option>
                          <option value="multiple" {if $variant.is eq multiple}selected{/if}>{"Multiple Values"|trans}</option>
                        </optgroup>
                      </select>
                    </div>
                    <div class="col"><input class="form-control" type="text" name="page[product_fields][{$variant@index}][value]" value="{$variant.value}" placeholder="{'Default Value'|trans}"></div>
                    <div class="col col-form-label">
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="config-fieldset{$variant@index}" name="page[product_fields][{$variant@index}][fieldset]" value="1" {if $variant.fieldset}checked{/if}>
                        <label class="form-check-label" for="config-fieldset{$variant@index}">{"Fieldset"|trans}</label>
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
                    <div class="col-4 mb-3 variant sg-hover bg-transparent position-relative">
                      <button class="btn border-0 position-absolute end-0 pe-sm-4 sg-hover-visible {if $option@index < 3}text-white" disabled="disabled{/if}" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
                      <input class="form-control" type="text" name="page[product_fields][{$variant@index}][options][]" value="{$option}">
                    </div>
                    {foreachelse}
                    <div class="col-4 mb-3 variant sg-hover bg-transparent position-relative">
                      <button class="btn border-0 position-absolute end-0 pe-sm-4 sg-hover-visible text-white" disabled="disabled" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
                      <input class="form-control" type="text" name="page[product_fields][{$variant@index}][options][]" placeholder="{'option 1'|trans}">
                    </div>                            
                    <div class="col-4 mb-3 variant sg-hover bg-transparent position-relative">
                      <button class="btn border-0 position-absolute end-0 pe-sm-4 sg-hover-visible text-white" disabled="disabled" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
                      <input class="form-control" type="text" name="page[product_fields][{$variant@index}][options][]" placeholder="{'option 2'|trans}">
                    </div>                            
                    <div class="col-4 mb-3 variant sg-hover bg-transparent position-relative">
                      <button class="btn border-0 position-absolute end-0 pe-sm-4 sg-hover-visible text-white" disabled="disabled" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
                      <input class="form-control" type="text" name="page[product_fields][{$variant@index}][options][]" placeholder="{'another option'|trans}">
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
              <select class="form-select select-handler" name="page[product_fields][0][type]">
                {foreach $supportedInputs as $input}
                <option value="{$input|lower}">{$input}</option>
                {/foreach}
              </select>
            </div>
            <div class="col"><input class="form-control" type="text" name="page[product_fields][0][name]" value=""></div>
            <div class="col"><input class="form-control" type="text" name="page[product_fields][0][label]" value=""></div>
            <div class="col"><input class="form-control" type="text" name="page[product_fields][0][description]" value=""></div>
            <div class="col-auto ps-0"><button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle my-1" data-bs-toggle="collapse" data-bs-target="#product_fields0"><i class="bi bi-chevron-expand"></i></button></div>
            <div class="col-12 collapse" id="product_fields0">
              <div class="row pt-3">
                <div class="col-auto px-0"><svg class="bi" width="16" height="16"></svg></div> 
                <div class="col-2">
                  <select class="form-select" name="page[product_fields][0][visibility]">
                    <optgroup label="{'Visibility'|trans}">
                      <option value="client_editable">{"Client Editable"|trans}</option>
                      <option value="client_readonly">{"Client ReadOnly"|trans}</option>
                      <option value="client_hidden">{"Client Hidden"|trans}</option>
                      <option value="hidden">{"Hidden"|trans}</option>
                      <option value="readonly">{"Staff ReadOnly"|trans}</option>
                      <option value="editable">{"Staff Editable"|trans}</option>
                    </optgroup>  
                  </select>
                </div>
                <div class="col">
                  <div class="row">
                    <div class="col">
                      <select class="form-select" name="page[product_fields][0][is]">
                        <optgroup label="{'Input value is'|trans}">
                          <option value="optional">{"Optional"|trans}</option>
                          <option value="required">{"Required"|trans}</option>
                          <option value="multiple">{"Multiple Values"|trans}</option>
                        </optgroup>
                      </select>
                    </div>
                    <div class="col"><input class="form-control" type="text" name="page[product_fields][0][value]" value="{$variant.value}" placeholder="{'Default Value'|trans}"></div>
                    <div class="col col-form-label">
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="config-fieldset0" name="page[product_fields][0][fieldset]" value="1">
                        <label class="form-check-label" for="config-fieldset0">{"Fieldset"|trans}</label>
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
                    <div class="col-4 mb-3 variant sg-hover bg-transparent position-relative">
                      <button class="btn border-0 position-absolute end-0 pe-sm-4 sg-hover-visible text-white" disabled="disabled" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
                      <input class="form-control" type="text" name="page[product_fields][0][options][]" placeholder="{'option 1'|trans}">
                    </div>                            
                    <div class="col-4 mb-3 variant sg-hover bg-transparent position-relative">
                      <button class="btn border-0 position-absolute end-0 pe-sm-4 sg-hover-visible text-white" disabled="disabled" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
                      <input class="form-control" type="text" name="page[product_fields][0][options][]" placeholder="{'option 2'|trans}">
                    </div>                            
                    <div class="col-4 mb-3 variant sg-hover bg-transparent position-relative">
                      <button class="btn border-0 position-absolute end-0 pe-sm-4 sg-hover-visible text-white" disabled="disabled" type="button" data-confirm="delete"><i class="bi bi-x-circle-fill"></i></button>
                      <input class="form-control" type="text" name="page[product_fields][0][options][]" placeholder="{'another option'|trans}">
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
			{$smarty.block.parent}
{/block}
{block name=APP_tabcontent}
		{if $prefix eq 'page'} {* to exclude subapp *}
			<div class="form-group row mb-3">
				<label class="col-sm-3 col-form-label text-sm-end">{'Video URL'|trans}</label>
				<div class="col-sm-9">
					<div class="input-group">
						{if $page.meta.video}<span class="input-group-text bg-transparent" role="button" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-url="{$page.meta.video}"><i class="bi bi-eye"></i></span>{/if}			
						<input class="form-control" id="featured-video-product" type="text" name="page[meta][video]" value="{$page.meta.video}">
					</div>
					<small class="form-text text-secondary">YouTube, Google Drive, Facebook, Tiktok, Vimeo, Vine, Instagram, DailyMotion, Youku, Peertube</small>  
				</div>
			</div>
			<div class="form-group row mb-3">
				<label class="col-sm-3 col-form-label text-sm-end">{"Recommended Products"|trans}</label>
				<div class="col-sm-{$colwidth}">
	        <div class="input-group dropup">
	          <button class="input-group-text bg-transparent" type="button"><i class="bi bi-search"></i></button>
	          <input class="form-control lookup-field dropdown-toggle" type='text' placeholder="{'Name'|trans}" data-lookup="product" data-index="1" data-name="page[meta][related][ ]" data-multiple="1" data-bs-auto-close="outside" data-bs-toggle="dropdown" aria-expanded="false">
	          <div class="dropdown-menu px-2" style="max-height: 50vh; overflow: scroll;">{if $page.meta.related}
	            {foreach $page.meta.related.rows as $id => $value}
	              <div class="form-check">
	                <input class="form-check-input lookup-item" id="product-lookup1-{$id}" name="page[meta][related][ ]" type="checkbox" value="{$id}" checked>
	                <label class="form-check-label" for="product-lookup1-{$id}">{$value}</label>
	              </div>                  
	            {/foreach}
	          {/if}</div>
	          {foreach $page.meta.related.rows as $id => $value}
	            <span class="input-group-text bg-transparent"><a href="#" data-url="{$links.edit}/{$id}?sgframe=1" data-title="{$value}" data-bs-toggle="modal" data-bs-target="#dynamicModal" class="text-decoration-none" target="_blank">{$value}</a></span>
	          {/foreach}
	        </div>
	        {include "lookup.tpl"} 
	      </div>
	    </div>
	  {/if}      
{/block}	
{block name=APP_tabsettings}
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-form-label text-sm-end">{"Fulfillment Processor"|trans}</label>
					<div class="col-sm-7">
						<select class="form-select" name="page[subtype]" id="js-sg-subtype">
							{foreach $api.fulfillments AS $key => $fulfillment}
								<option value="{$key}" {if $page.subtype eq $key}selected{/if}>{$fulfillment}</option>
							{/foreach}
							<option value="Shipping" {if $page.subtype eq Shipping}selected{/if}>{"Shipping"|trans}</option>
							<option value="Download" {if $page.subtype eq Download}selected{/if}>{"Download"|trans}</option>
							<option value="Listing" {if $page.subtype eq Listing}selected{/if}>{"Listing"|trans}</option>
							<option value="Deposit" {if $page.subtype eq Deposit}selected{/if}>{"Deposit"|trans}</option>
						</select>
						<small class="form-text text-secondary">{"This product will be fulfilled by the selected Processor"|trans}</small> 
					</div>
				</div>				
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-form-label text-sm-end">{"Automatic Fulfillment"|trans}</label>
					<div class="checkbox col-sm-7">
						<div class="form-check form-switch col-form-label">
							<input type="hidden" name="page[meta][auto_setup]" value="0">
							<input type="checkbox" class="form-check-input" id="setup-mode-switch" name="page[meta][auto_setup]" value="1" {if $page.meta.auto_setup == 1}checked{/if}>
							<label class="form-check-label" for="setup-mode-switch">{"Start the processor immediately when the order is paid"|trans}</label>
						</div>  
					</div>
				</div> 
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-form-label text-sm-end">{"Dynamic Pricing"|trans}</label>
					<div class="checkbox col-sm-7">
						<div class="form-check form-switch col-form-label">
							<input type="hidden" name="page[meta][dynamic_price]" value="0">
							<input type="checkbox" class="form-check-input" id="pricing-mode-switch" name="page[meta][dynamic_price]" value="1" {if $page.meta.dynamic_price == 1}checked{/if}>
							<label class="form-check-label" for="pricing-mode-switch">{"Variant price can be increased dynamically in the shopping cart"|trans}</label>
						</div>  
					</div>
				</div> 
        {include "form_field.tpl" formFields=[
            'groups' => [
              'type' => 'select', 
              'is' => 'multiple',
              'label' => {'Add Buyers to Customer Groups'|trans},
              'description' => {"Customers who purchase the product will automatically be added to the chosen group(s)"|trans}, 
              'slug' => $links.group,
              'value' => $page.meta.groups,
              'options' => $api.groups
            ]
          ] fieldPrefix="page[meta]"}  

				{if $api.taxes}
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-form-label text-sm-end">{"Tax Rate"|trans}</label>
					<div class="col-sm-7">
						<select class="form-select" name="page[meta][tax_rate]">
							<option value="">{"Default"|trans}</option>
							{foreach $api.taxes AS $tax}
								{if ! $tax.active}
									<option value="{$tax.id}" {if $tax.id eq $page.meta.tax_rate}selected{/if}>{$tax.name} {$tax.rate}%</option>
								{/if}
							{/foreach}		
						</select>
						<small class="form-text text-secondary">{"Choose another tax rate to override the default one"|trans}</small> 
					</div>
				</div>	
			 	{/if}	
				<div class="form-group row mb-3 shipping-options {if $page.subtype ne Shipping}d-none{/if}">
					<label class="col-sm-3 col-form-label text-sm-end">{"Shipping Surcharge/Discount"|trans}</label>
					<div class="col-sm-7">
						<input class="form-control" name="page[meta][shipping_fee]" value="{$page.meta.shipping_fee}">
					</div>
				</div>
{/block}
{block name="APP_footer"}
	{$smarty.block.parent}
	<div id="product-modal" class="modal fade backdrop-blur">
	  <div class="modal-dialog modal-sm">
	      <div class="modal-content">
	         <div class="modal-header">
	            <h5 class="modal-title">{"New Property"|trans}</h5>
	            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	         </div>
	         <div class="modal-body">
	         	<div class="form-floating my-3">
						  <input type="text" class="form-control" id="new-property-name"  name="" value="" placeholder="{'Property Name'|trans}">
						  <label for="floatingInput">{'Property Name'|trans}</label>
						</div>	
	         </div>
	         <div class="modal-footer">
	            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{"Cancel"|trans}</button>
	            <button class="btn btn-primary" id="product-modal-ok">{"OK"|trans}</button>
	         </div>
	      </div>
	   </div>      
	</div>
	{literal}
	<script>
  	document.addEventListener("DOMContentLoaded", function(e){
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
	        lastRow = $(target).find('> div.variant:last');
	        newIndex = 0; //$('#variant-table').find('tr').length;
	        $(target).find('> div.variant').each(function() {
	          newIndex = Math.max($(this).attr('data-index'), newIndex);
	        });
	        newIndex++;            
	        newRow = lastRow.clone();
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
	        newRow.insertAfter(lastRow);
	      });

	      $(target).sortable({
	        items: "> div.variant",
	        axis: 'y',
	        handle: ".bi-grip-vertical",
	      });
	    } 
	    initFormField('#product_fields'); 
	    $('#product_fields').on('click', '[data-confirm="delete"]', function(e) { 
	      $(this).closest('.variant').remove();
	    });
	    $('.download-options').on('click', '[data-confirm="delete"]', function(e) { 
	      $(this).closest('.variant').remove();
	    });

      $('#product-modal-ok').click(function(e) {
        if (name = $('#new-property-name').val()) {	            	
        	let element = $('<div class="col sg-movable"></div>');
        	element.text(name);//dom xss handling
        	element.append('<i class="bi bi-three-dots-vertical sg-option-menu float-end pe-2" role="button"></i><i class="bi bi-arrow-bar-left sg-move-left float-end pe-2 d-none" role="button"></i><i class="bi bi-x-octagon sg-removable float-end pe-2 d-none" role="button"></i>');
          $('#variant-table').children(":first").children(":last").before(element);
          $('#variant-table').find('div.variant').each(function(){
	          //page[variants][{$index}][sku]
	          currentVariant = $(this).find("input[name$='\[sku\]']");
	          if (currentVariant.length) {
	              rowName = currentVariant.attr('name').replace(/\[sku\]/ig, '['+name+']');
	              element = $('<input class="form-control" type="text" name="" value="">').attr('name', rowName);//xss handling
	              element = $('<div class="col"></div').append(element);
	              $(this).children(".sg-variant-controls").before(element);
	          }    
	        });
      	}    
  
      	$('#new-property-name').val('');
      	$('#product-modal').modal('hide');
      });

      $('.new-table-row').click(function(e) {
        lastRow = $('#variant-table').find('> div.variant:last'); //there are nested variant classes
        newIndex = 0;
				$('#variant-table').find('> div.variant').each(function() {
				    newIndex = Math.max(this.id.replace('variant', ''), newIndex);
				});
				newIndex++;            
	      newRow  = lastRow.clone();
	      newRow.attr("id", "variant" + newIndex)
	      	  	.find('[data-bs-toggle="collapse"]').attr("data-bs-target", "#v" + newIndex + "images")
	      	  								   .attr("src", "https://via.placeholder.com/120x80/5a5c69/fff?text=Add%20Image");
	      newRow.find('.collapse').attr("id", "v" + newIndex + "images")
	      	  	.find('.carousel-inner').children().remove(); //remove current images
	      newRow.find('.get-image-callback').attr('data-name', 'page[variants]['+newIndex+'][images][]')
	      			.attr('data-container', "#v" + newIndex + "images .carousel-inner");	
	      newRow.find('.download-options').attr('id', 'v'+newIndex+'downloads')
	      			.find('.get-download-callback')
	      			.attr('data-container', "#v" + newIndex + "downloads")
	      			.val('')					  
	      newRow.find('[data-confirm="delete"]').prop("disabled", false)
							.attr("data-value", "")
							.on('click', function(e) { 
								$(this).closest('.variant').remove();
							});
	      newRow.find("input[name^='page\[variants\]']").each(function(){
	          $(this).attr('name', $(this).attr('name').replace(/page\[variants\]\[\d+\]/ig, 'page[variants]['+newIndex+']'));
	      });
	      newRow.find("input[name='page[variants]["+newIndex+"][id]']").attr("value", "");
	      newRow.insertAfter(lastRow)
	      	  	.find('.carousel-multi').each(Sitegui.carousel);
	    });
			
			var cloneRow = function(target, row) {
	      newIndex = 0; //$('#variant-table').find('tr').length;
	      $(target).find('> div.variant').each(function() {
				  newIndex = Math.max(this.id.replace('variant', ''), newIndex);
	      });
	      newIndex++;            
	      newRow = row.clone();
	      newRow.attr("id", "variant" + newIndex)
	      	  	.find('[data-bs-toggle="collapse"]').attr("data-bs-target", "#v" + newIndex + "images")
	      	  	//temporary for HT copy .attr("src", "https://via.placeholder.com/120x80/5a5c69/fff?text=Add%20Image");
	      newRow.find('.collapse').attr("id", "v" + newIndex + "images")
	      	  	//.find('.carousel-inner').children().remove(); //remove current images
	      newRow.find('.get-image-callback').attr('data-name', 'page[variants]['+newIndex+'][images][]')
	      			.attr('data-container', "#v" + newIndex + "images .carousel-inner");		  
	      newRow.find('[data-confirm="delete"]').prop("disabled", false)
							.attr("data-value", "")
							.attr("data-remove", "#variant" + newIndex)
							.on('click', function(e) { 
								$(this).parent().parent().remove();
							});
	      newRow.find("input[name^='page\[variants\]']").each(function(){
	          $(this).attr('name', $(this).attr('name').replace(/page\[variants\]\[\d+\]/ig, 'page[variants]['+newIndex+']'));
	      });
	      newRow.find("input[name='page[variants]["+newIndex+"][id]']").attr("value", "");

	      newRow.find(".overlay").remove(); //remove images' overlay control
	      newRow.insertAfter(row)
	      	  	.find('.carousel-multi').each(Sitegui.carousel);
	      newRow.find('.carousel-inner').trigger('updated');	  	
	    }	    
	    $('#variant-table').on('click', '.js-sg-clone', function(e) {
	      cloneRow('#variant-table', $(this).closest('div.variant') )
	    })

	    $('#variant-table').on('click', '.sg-option-menu', function(e) {
	      $('.sg-removable').addClass('d-none')
	      $('.sg-move-left').addClass('d-none')
	      if ( $(this).is('.sg-open') ){
	      	$(this).removeClass('sg-open')	
	      }	else {
	      	$(this).siblings('.bi').removeClass('d-none')
	      	$('.sg-open').removeClass('sg-open') //remove open state for other buttons
	      	$(this).addClass('sg-open')
	      }
	    });

	    $('#variant-table').on('click', '.sg-removable', function(e) {
	      var ndx = $(this).parent().index() + 1;
	      // Find all TD elements with the same index
	      $("#variant-table").find('> div.variant').children().remove(":nth-child(" + ndx + ")");            
	      $(this).parent().remove();
	    });

	    //move option
	    $('#variant-table').on('click', '.sg-move-left', function(e) {
	      var ndx = $(this).parent().index() + 1;
				if ( $(this).parent().prev().is('.sg-movable') ){
	      	$(this).parent().insertBefore( $(this).parent().prev() );
		      // Find all TD elements with the same index
		      $('#variant-table > div.variant').children(":nth-child(" + ndx + ")").each(function(){
		      	$(this).insertBefore(this.previousElementSibling);
		      }) 	      		
	      }           
	    });
	        
	    $("#variant-table").sortable({
	    	items: "> div.variant",
	      helper: function(e, ui) {
	        ui.children().each(function() {
	            //$(this).width($(this).width());
	        });
	        return ui;
	      },
	      axis: 'y',
	      handle: ".bi-grip-vertical",
	    });

	    $("#variant-table").on("click", ".js-btn-fullscreen", function(ev) { 
      	$(this).closest('.collapse').toggleClass('sg-fullscreen');
      	$(this).find('.bi').toggleClass('bi-fullscreen-exit');
    	});

    	$('#featured-video-product').on('change', function(){
    		let input = $(this).val()

    		let iframe = $('.page-content:first').summernote('module', 'videoDialog').createVideoNode(input)
    		if (iframe) {
    			iframe = $(iframe).attr('src')
    			$(this).val(iframe)
    		} else {
    			const tiktokRegExp = /(?:www\.|\/\/)tiktok\.com\/.*?\/video\/(.[a-zA-Z0-9_-]*)/;
    			const tiktokMatch = input.match(tiktokRegExp);
    			const fbRegExp = /(?:www\.|\/\/)fb\.watch\/(.[a-zA-Z0-9_-]*)/;
    			const fbMatch = input.match(fbRegExp);
    			if (tiktokMatch){
    				$(this).val("https://www.tiktok.com/embed/v2/"+ tiktokMatch[1])
    			} else if (fbMatch){
    				$(this).val("https://www.facebook.com/plugins/video.php?show_text=0&width=560&href=https://fb.watch/"+ fbMatch[1])
    			}
    		}
    	})

    	$('#js-sg-subtype').on('change', function(){
    		if (this.value != 'Shipping'){
    			$('.shipping-options').addClass('d-none')
    			$('.download-options').removeClass('d-none')
    			$('.bi-truck').removeClass('bi-truck').addClass('bi-download')
    		} else {
    			$('.shipping-options').removeClass('d-none')
    			$('.download-options').addClass('d-none')
    			$('.bi-download').removeClass('bi-download').addClass('bi-truck')
    		}
    	})        
		});
	</script>
	{/literal}  
	{if $html.onboard_product}
	<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function(e){
	  const driverObj = window.driver.js.driver({
	    showProgress: true,
	    steps: [
	      { 
	        element: '.input-name', 
	        popover: { 
	          title: Sitegui.trans(':Item Quick Tour', {
	          	"Item": "Product"
	          }), 
	          description: Sitegui.trans('Enter a Label for your product here'), 
	          side: "bottom", 
	          align: 'center',
	          onNextClick: () => {
	            const selectors = [
	              "#variant-table .img-thumbnail",
	            ]
	            selectors.forEach(sel => {
	              $('body').on('click', sel, () => {
	                driverObj.moveNext()
	              })
	            })
	            driverObj.moveNext();
	          } 
	        }
	      },{ 
	        element: '#variant-table', 
	        popover: { 
	          title: Sitegui.trans('Variants'), 
	          description: Sitegui.trans('A product may have different variants of sizes and colors etc. Each variant should have its own SKU, price, stock and other properties'), 
	          side: "left", 
	          align: 'center',
	        },
	      },{ 
	        element: '.js-add-property', 
	        popover: { 
	          title: Sitegui.trans('Add Product Property'), 
	          description: Sitegui.trans('Click the button to add a new property for all variants.'), 
	          side: "bottom", 
	          align: 'center',          
	        }
	      },{ 
	        element: '.js-add-shipping', 
	        popover: { 
	          title: Sitegui.trans('Add Shipping Property'), 
	          description: Sitegui.trans('If this is a physical product that requires shipping, enter the weight and length to help calculating shipping fee easier.'), 
	          side: "bottom", 
	          align: 'center',          
	        }
	      },{ 
	        element: '#variant-table .img-thumbnail', 
	        popover: { 
	          title: Sitegui.trans('Manage Variant Images'), 
	          description: Sitegui.trans('Click on the image thumbnail to manage images for the variant.'), 
	          side: "bottom", 
	          align: 'center',
	          onNextClick: (el) => {
	          	$(el).effect("shake")
	          } 
	        }
	      },{ 
	        element: '#variant-table .get-image-callback', 
	        popover: { 
	          title: Sitegui.trans('Select Images'), 
	          description: Sitegui.trans('Click on the button to upload/select variant specific images'), 
	          side: "bottom", 
	          align: 'center' 
	        }
	      },{ 
	        element: '#variant-table .new-table-row', 
	        popover: { 
	          title: Sitegui.trans('Add Another Variant'), 
	          description: Sitegui.trans('Click on the button to add another variant by cloning the last one.'), 
	          side: "bottom", 
	          align: 'center' 
	        }
	      },{ 
	        element: '#main-tab .nav-item:nth-child(2)', 
	        popover: { 
	          title: Sitegui.trans('Add Product Content'), 
	          description: Sitegui.trans('Click on the Content tab to add content for your product, we use the same content for all the variants'), 
	          side: "bottom", 
	          align: 'center',
	        }
	      },{ 
	        element: '#sg-btn-save', 
	        popover: { 
	          title: Sitegui.trans('Save the Product'), 
	          description: Sitegui.trans('Click on the button to save your product.'), 
	          side: "top", 
	          align: 'center',
	          onNextClick: () => {
	            var nvp = 'done=product&csrf_token='+ window.csrf_token +'&format=json';
	            $.post('{$links.onboard}', nvp, function(data) { // already a json object jQuery.parseJSON(data);
	              if (data.status.result == 'success') {                
	              } else {
	              }   
	            }) 
	            driverObj.moveNext();
	          } 
	        }
	      },{ 
	        element: '#sg-btn-publish',
	        popover: { 
	          title: Sitegui.trans('Publish the Product'), 
	          description: Sitegui.trans('Click on the button to publish your product on your website. You can change the published time in the Settings tab.'), 
	          side: "top", 
	          align: 'center',
	        } 
	      }
	    ]
	  })

	  driverObj.drive()
	})  
	</script> 
	{/if}
{/block}
