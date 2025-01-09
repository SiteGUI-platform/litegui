{extends "page_edit.tpl"}

{block name=APP_header}
          <input class="input-name form-control my-1 px-3 font-italic" type="text" name="page[slug]" value="{$page.slug}" {if $page.slug}readonly{/if} placeholder="{'Slug'|trans}">  
{/block}
{block name="APP_tabname_end"}
      <li class="nav-item"><button type="button" data-bs-target="#tab-faq" aria-controls="tab-faq" class="nav-link" role="tab" data-bs-toggle="tab">{"FAQs"|trans}</a></li>  
{/block}
{block name=APP_fields}
        <div id="tab-faq" class="tab-pane fade tab-content-full" role="tabpanel">
          <div class="accordion accordion-flush" id="accordion-flush">
            {foreach $api.page.meta.faq AS $index => $faq}
            <div class="accordion-item variants" data-index="{$index}">
              <div class="input-group accordion-body">
                <span class="input-group-text bg-transparent text-primary"><i class="bi bi-chevron-down {if $index}collapsed{/if}" data-bs-toggle="collapse" data-bs-target="#faq-{$index}" aria-expanded="false" aria-controls="faq-{$index}" role="button"></i></span>  
                <input type="text" name="page[faq][{$index}][question]" class="form-control" placeholder="{'Question'|trans}" value="{$faq.question}">
                <button class="input-group-text bg-transparent" data-confirm="delete" {if !$index}disabled{/if}><i class="bi bi-trash"></i></button>
              </div>
              <div id="faq-{$index}" class="accordion-collapse collapse {if !$index}show{/if}" data-bs-parent="#accordion-flush">
                <div class="input-group accordion-body pt-0">
                  <span class="input-group-text invisible"><i class="bi bi-chevron-down"></i></span>
                  <textarea name="page[faq][{$index}][answer]" rows="3" class="form-control rounded" placeholder="Answer">{$faq.answer}</textarea>
                </div>
              </div>
            </div>
            {foreachelse}
            <div class="accordion-item variants" data-index="0">
              <div class="input-group accordion-body">
                <span class="input-group-text bg-transparent text-primary"><i class="bi bi-chevron-down" data-bs-toggle="collapse" data-bs-target="#faq-0" role="button"></i></span>                  
                <input type="text" name="page[faq][0][question]" class="form-control" placeholder="{'Question'|trans}" value="">
                <button class="input-group-text bg-transparent" data-confirm="delete" disabled><i class="bi bi-trash"></i></button>
              </div>
              <div id="faq-0" class="accordion-collapse collapse show" data-bs-parent="#accordion-flush">
                <div class="input-group accordion-body pt-0">
                  <span class="input-group-text invisible"><i class="bi bi-chevron-down"></i></span>
                  <textarea name="page[faq][0][answer]" rows="3" class="form-control rounded" placeholder="Answer"></textarea>
                </div>
              </div>
            </div>
            {/foreach}
            <div class="col-12 text-center my-3">
              <button type="button" class="btn border-0 px-1 js-new-row"><i class="bi bi-plus-circle-fill fs-3 text-warning"></i></button>
              <script type="text/javascript">
              document.addEventListener("DOMContentLoaded", function(e){ 
                $('.js-new-row').on('click', function(e) {
                  lastRow = $(this).parents('.tab-pane').find('div.variants:last');
                  newIndex = 0; //$('#variant-table').find('tr').length;
                  $(this).parents('.tab-pane').find('div.variants').each(function() {
                    newIndex = Math.max($(this).attr('data-index'), newIndex);
                  });
                  newIndex++;            
                  newRow = lastRow.clone();
                  newRow.attr("data-index", newIndex)
                  newRow.find('[data-confirm="delete"]').prop("disabled", false).removeClass('d-none')
                  newRow.find("[name^='page']").each(function(){
                      $(this).attr('name', $(this).attr('name').replace(/extras\]\[\d+\]/, 'extras]['+ newIndex +']').replace(/faq\]\[\d+\]/, 'faq]['+ newIndex +']') );
                  });
                  newRow.find('[data-bs-toggle="collapse"]').each(function(){
                      $(this).attr('data-bs-target', $(this).attr("data-bs-target").replace(/\d+$/, newIndex) );
                  });
                  newRow.find('.collapse').each(function(){
                      $(this).attr('id', $(this).attr('id').replace(/\d+$/, newIndex) );
                  });
 
                  newRow.insertAfter(lastRow);
                })
                $('.tab-pane').on('click', '[data-confirm="delete"]', function(e) { 
                  $(this).closest('.variants').remove();
                });
              })   
            </script>
            </div>
            <style type="text/css">
              .bi-chevron-down.collapsed::before {
                transform: rotate(-90deg);
              }
              i[data-bs-toggle="collapse"] {  
                transition: transform .2s ease-in-out;
              }
            </style>
          </div>
        </div>  
{/block}
{block name=APP_tabcontent}  
    {include "form_field.tpl" formFields=[
      'images' => [
        'type' => 'image',
        'is'   => 'multiple', 
        'label' => 'Images', 
        'value' => $api.variants.0.images
      ]
    ] fieldPrefix="page[variants][0]" c2=9}       
{/block}  
{block name=APP_tabsettings}
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Type"|trans}</label>
            <div class="col-sm-7">
              <div class="input-group">
                <select class="form-select" name="page[subtype]" {if $page.subtype}disabled{/if}>
                {foreach $html.subtype as $subtype}
                  <option value="{$subtype}" {if $subtype eq $page.subtype}selected{/if}>{$subtype|trans}</option>
                {/foreach}  
                </select>
                {if $page.id}
                <span class="input-group-text"><a href="#" data-url="{$links.build}/{$page.id}?sgframe=1" data-title="{'App Builder'|trans}" data-bs-toggle="modal" data-bs-target="#dynamicModal" class="text-decoration-none">{'App Builder'|trans}</a></span>
                {/if}
              </div>  
            </div>
          </div>
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Billing"|trans}</label>
            <div class="col-sm-7">
              <select class="form-select" name="page[billing]">
                <option value="">{"One Time"|trans}</option>
                <option value="1 Month" {if $api.variants.0.options.Billing eq "1 Month"}selected{/if}>{"Monthly"|trans}</option>
                <option value="1 Year" {if $api.variants.0.options.Billing eq "1 Year"}selected{/if}>{"Yearly"|trans}</option>
              </select>
              <small class="form-text text-secondary"></small> 
            </div>
          </div> 
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"License Fee"|trans}</label>
            <div class="col-sm-7">
              <div class="row g-2">
                <div class="col">
                  <input class="form-control" type="hidden" name="page[variants][0][id]" value="{$api.variants.0.id}">
                  <input class="form-control" type="hidden" name="page[variants][0][License]" value="{$api.variants.0.License|default:'Single'}">
                  <div class="form-floating">
                    <input id="floating-single" class="form-control" type="text" name="page[variants][0][price]" value="{$api.variants.0.price}" placeholder="{'Single Site'|trans}">
                    <label for="floating-single">{"Single Site"|trans}</label>
                  </div>  
                </div>   
                <div class="col">
                  <input class="form-control" type="hidden" name="page[variants][1][id]" value="{$api.variants.1.id}">
                  <input class="form-control" type="hidden" name="page[variants][1][License]" value="{$api.variants.1.License|default:'Multiple'}">
                  <div class="form-floating">
                    <input id="floating-multiple" class="form-control" type="text" name="page[variants][1][price]" value="{$api.variants.1.price}" placeholder="{'Multiple'|trans}">
                    <label for="floating-multiple">{"Multiple Sites"|trans}</label> 
                  </div>
                </div>   
                <div class="col">
                  <input class="form-control" type="hidden" name="page[variants][2][id]" value="{$api.variants.2.id}">
                  <input class="form-control" type="hidden" name="page[variants][2][License]" value="{$api.variants.2.License|default:'Developer'}">
                  <div class="form-floating">
                    <input id="floating-developer" class="form-control" type="text" name="page[variants][2][price]" value="{$api.variants.2.price}" placeholder="{'Developer'|trans}">
                    <label for="floating-developer">{"Developer"|trans}</label> 
                  </div>  
                </div> 
              </div>  
            </div>
          </div> 

          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Package Summary"|trans}</label>
            <div class="col-sm-7">
              <div class="row g-2">
                <div class="col">
                  <textarea class="form-control" name="page[summary][0]" rows="3">{$page.meta.summary.0}</textarea> 
                </div>   
                <div class="col">
                  <textarea class="form-control" name="page[summary][1]" rows="3">{$page.meta.summary.1}</textarea> 
                </div>   
                <div class="col">
                  <textarea class="form-control" name="page[summary][2]" rows="3">{$page.meta.summary.2}</textarea>  
                </div> 
              </div>  
            </div>
          </div> 
          <div class="form-group row mb-3 ">  
            <label class="col-sm-3 col-form-label text-sm-end">
              {"Enable Developer License"}
            </label>
            <div class="col-sm-7">
                <div class="btn-group form-switch-radio col-form-label me-2 mb-1 pt-2" role="group">          
                  <input type="radio" class="btn-check btn-off" name="page[developer_license]" id="cid3-developer-0" value="0" autocomplete="off" checked="">
                  <input type="radio" class="btn-check btn-on" name="page[developer_license]" id="cid3-developer-1" value="1" autocomplete="off" {if $page.meta.developer_license}checked{/if}>
                  <label class="btn btn-off btn-sm btn-outline-secondary border-end-0" for="cid3-developer-0"></label>
                  <label class="btn btn-on btn-sm btn-outline-primary border-start-0" for="cid3-developer-1"></label>
                </div>
                <label class="col-form-label" for="cid3-developer-1"></label>                          
            </div>   
          </div> 
          <div class="form-group row mb-3 ">  
            <label class="col-sm-3 col-form-label text-sm-end">
              Receive Payment in SGC 
            </label>
            <div class="col-sm-7">
                <div class="btn-group form-switch-radio col-form-label me-2 mb-1 pt-2" role="group">          
                  <input type="radio" class="btn-check btn-off" name="page[receive_sgc]" id="cid3-receive_sgc-0" value="0" autocomplete="off" checked="">
                  <input type="radio" class="btn-check btn-on" name="page[receive_sgc]" id="cid3-receive_sgc-1" value="1" autocomplete="off" {if $page.meta.receive_sgc}checked{/if}>
                  <label class="btn btn-off btn-sm btn-outline-secondary border-end-0" for="cid3-receive_sgc-0"></label>
                  <label class="btn btn-on btn-sm btn-outline-primary border-start-0" for="cid3-receive_sgc-1"></label>
                </div>
                <label class="col-form-label" for="cid3-receive_sgc-1">Payment will be paid in SGC (SiteGUI CryptoCurrency) instead of USD. Current SGC/USD exchange rate is 0.001 and is scheduled to appreciate to 0.016 (x16) in one year (double every 3 months) and then follows the market price.</label>                          
            </div>   
          </div> 
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"File Archive"|trans}</label>
            <div class="col-sm-7">
                <div class="input-group">
                <input id="file-archive" class="form-control" type="text" name="page[meta][file]" value="{$page.meta.file}">
                <span class="input-group-text get-file-callback" data-name="page[meta][file]" data-container="#file-archive" data-bs-toggle="modal" data-bs-target="#dynamicModal"><i class="bi bi-upload"></i></span> 
              </div>  
            </div>
          </div>

{/block}
{block name=APP_script}
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(e) { 
  {if ! $page.id}
    //auto-fill slug
    $('.input-name.form-control-lg').keyup(function(e) { 
      var txtVal = '{$user.name} ' + $(this).val(); //unlike page_edit, we use _ instead of - for app name
      $('input[name="page[slug]"]').val(txtVal.replace(/\s+/g, '_').toLowerCase());
    });
  {/if} 
});    
</script>
{/block}