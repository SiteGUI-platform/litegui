{$site = $api.site}
<form action="{$links.update}" method="post" class="form-horizontal w-100">
<div class="col-12 {if $system.sgframe}px-0{else}col-md-10 pt-sm-2{/if} pb-40 mx-auto">
  <div class="card">
    <div class="card-body row">
      <div class="col-auto col-form-label sg-hover-back px-3 py-1 d-none d-sm-block">
        {if $links.main}<a href="{$links.main}">{/if}
        <i class="bi bi-globe fs-3"></i>
        {if $links.main}</a>{/if}
      </div>
      <div class="col ps-sm-0 px-2">
        <input class="input-name form-control-lg text-success" type="text" name="site[name]" value="{$site.name}" placeholder="{'Website Name'|trans}" required {if ! $site.name}autofocus{/if}>
        {if $site.id > 0}<input type="hidden" name="site[id]" value="{$site.id}">{/if}
      </div>   
    </div> 
    <div role="tabpanel">
      <!-- Nav tabs -->
      <ul id="main-tab" class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a href="#tab-settings" aria-controls="tab-settings" class="nav-link active" role="tab" data-bs-toggle="tab">{"Settings"|trans}</a></li>        
        <li class="nav-item"><a href="#tab-social" aria-controls="tab-social" class="nav-link" role="tab" data-bs-toggle="tab">{"Social Media Handles"|trans}</a></li>
        <li class="nav-item"><a href="#tab-apps" aria-controls="tab-apps" class="nav-link" role="tab" data-bs-toggle="tab">{"Default Apps"|trans}</a></li>        
      </ul>
      <!-- Tab panes -->
      <div class="tab-content px-3 py-4">
        <div id="tab-settings" class="tab-pane active" role="tabpanel">
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Domain"|trans}</label>
            <div class="col-sm-7">
              <div class="input-group">
                <input class="form-control" type="text" name="site[url]" value="{$site.url}" placeholder="{'Domain Name'|trans}" required>
                {if $site.id}
                  {if $site.status eq 'Active'}
                    <span class="input-group-text btn btn-success" {if $site.tier > 1} data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="{'DNS Check'|trans}" data-url="{$links.verify}/{$site.id}?sgframe=1"{/if}>&check;</span>
                  {elseif $site.status eq 'Suspended'}
                    <button class="input-group-text btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="{'Reinstate'|trans}" data-url="{$links.unsuspend}">{"Unsuspend"|trans}</button>
                  {else}
                    <button class="input-group-text btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="{'Domain Verification'|trans}" data-url="{$links.verify}/{$site.id}?sgframe=1">{"Verify"|trans}</button>
                  {/if}
                {else}
                <select class="form-select" name="site[tld]">
                  <option value="{$html.subdomain}">{$html.subdomain}</option>
                  <option value="custom">{"My Domain"|trans}</option>
                </select>
                {/if}
              </div>  
            </div>
          </div>                      
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Site Logo"|trans}</label>
            <div class="col-sm-7">
              <div class="input-group">
                {if $site.logo}<span class="input-group-text bg-transparent" role="button" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-url="{$site.logo}"><i class="bi bi-eye"></i></span>{/if}
                <input class="form-control get-file-callback" id="js-site-logo" type="text" name="site[logo]" value="{$site.logo}" data-container="#js-site-logo" data-bs-toggle="modal" data-bs-target="#dynamicModal" placeholder="~ 200 x 60px">
                <label class="input-group-text bg-transparent" for="js-site-logo" role="button"><i class="bi bi-upload"></i></label>  
              </div>  
            </div>
          </div>
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Template"|trans}</label>
            <div class="col-sm-7">
              <select class="form-control selectpicker show-tick" data-style="border border-1" name="site[template]">
                <option value="" {if ! $site.template}selected{/if}>{"Default"|trans}</option>
                {foreach $html.templates as $tpl}
                <option value="{$tpl}" {if $tpl == $site.template}selected{/if}>
                  {$tpl|capitalize:true}
                </option>
                {/foreach}  
              </select>
            </div>
          </div>
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"WYSIWYG Editor"|trans}</label>
            <div class="col-sm-7">
              <select class="form-control selectpicker show-tick" data-style="border border-1" name="site[editor]">
                <option value="wysiwyg" {if $site.editor == wysiwyg || $site.editor == CKEditor}selected='selected'{/if}>{"Yes"|trans}</option>
                <option value="" {if ! $site.editor}selected='selected'{/if}>{"No"|trans}</option>
              </select>
            </div>
          </div> 
          {if $html.role_sites} 
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Use Staff Role From"|trans}</label>
            <div class="col-sm-3">
              <select class="form-control selectpicker show-tick" data-style="border border-1" name="site[role_site]">
                <option value="" {if ! $site.role_site}selected{/if}>{"Site"|trans} - {$site.name}</option>
                {foreach $html.role_sites as $s}
                  {if $s.id != $site.id}
                  <option value="{$s.id}" {if $s.id == $site.role_site}selected{/if}>
                    {"Other"|trans} - {$s.name}
                  </option>
                  {/if}
                {/foreach}  
              </select>
            </div>
            <div class="col-sm-4">
              <div class="form-check form-switch pt-2">
                <input type="checkbox" class="form-check-input" id="user-mode" name="site[user_site]" value="1" {if $site.user_site}checked{/if}>
                <label class="form-check-label" for="user-mode">{"Also use User DB"|trans}</label>
              </div>
            </div>  
          </div>
          {/if} 
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Language"|trans}</label>
            <div class="col-sm-7">
              {if $site.language eq ''}{$site.language = 'en'}{/if}
              <select class="form-control selectpicker show-tick" data-live-search="true" data-style="border border-1" name="site[language]">
                {foreach $html.languages as $key => $lang}
                <option value="{$key}" {if $key == $site.language}selected{/if}>
                  {$lang|capitalize:true}
                </option>
                {/foreach}  
              </select>            
            </div>
          </div> 
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Other Languages"|trans}</label>
            <div class="col-sm-7">
              <select class="form-control selectpicker" name="site[locales][]" data-live-search="true" data-max-options="3" data-style="border border-1" data-actions-box="true" multiple>
                <!--option value=''></option--> 
                {foreach $html.languages as $key => $lang}
                <option value="{$key}" {foreach $site.locales as $locale}{if $locale eq $key}selected{/if}{/foreach}>{$lang|capitalize:true}</option>
                {/foreach}  
              </select> 
            </div>
          </div> 
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Timezone"|trans}</label>
            <div class="col-sm-7">
              <select class="form-control selectpicker show-tick" data-live-search="true" data-style="border border-1" name="site[timezone]">
                {foreach $html.timezones as $tz}
                <option value="{$tz.identifier}" {if $tz.identifier == $site.timezone}selected{/if}>
                  (GMT {$tz.offset}) {$tz.identifier|replace: '_':' '}
                </option>
                {/foreach}  
              </select>     
            </div>
          </div> 
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Currency"|trans}</label>
            <div class="col-sm-7">
              <div class="row g-2">
                <div class="col">
                  <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="currency-code" name="site[currency][code]" value="{$site.currency.code|default:'USD'}">
                    <label for="currency-code">{"Code"|trans}</label>
                  </div>
                </div>
                <div class="col">
                  <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="currency-prefix" name="site[currency][prefix]" value="{$site.currency.prefix}">
                    <label for="currency-prefix">{"Prefix"|trans}</label>
                  </div>
                </div>
                <div class="col">
                  <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="currency-suffix" name="site[currency][suffix]" value="{$site.currency.suffix}">
                    <label for="currency-suffix">{"Suffix"|trans}</label>
                  </div>
                </div>
                <div class="col">
                  <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="currency-precision" name="site[currency][precision]" value="{$site.currency.precision|default:2}">
                    <label for="currency-precision">{"Precision"|trans}</label>
                  </div>
                </div>
                <div class="col-12 form-text text-secondary mt-2">{"Specify the currency for the product prices. Use the ISO 4217 currency code"|trans}</div>
              </div>    
            </div>
          </div>
        </div>      
        <div id="tab-social" class="tab-pane" role="tabpanel">
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Contact"|trans}</label>
            <div class="col-sm-7">
              <div class="row">
                <div class="col-6 mb-3">
                  <div class="input-group">
                    <span class="input-group-text btn btn-light border"><i class="bi bi-telephone"></i></span>
                    <input class="form-control" type="text" name="site[social][phone]" value="{$site.social.phone}" placeholder="{'Phone'|trans}">
                  </div>  
                </div>
                <div class="col-6 mb-3">
                  <div class="input-group">
                    <span class="input-group-text btn btn-light border"><i class="bi bi-envelope-at"></i></span>
                    <input class="form-control" type="text" name="site[social][email]" value="{$site.social.email}" placeholder="{'Email'|trans}">
                  </div>  
                </div>
              </div>
            </div>
          </div>      
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Social Media Handles"|trans}</label>
            <div class="col-sm-7">
              <div class="row">
                <div class="col-6 mb-3">
                  <div class="input-group">
                    <span class="input-group-text btn btn-light border"><i class="bi bi-facebook"></i></span>
                    <input class="form-control" type="text" name="site[social][facebook]" value="{$site.social.facebook}" placeholder="Facebook">
                  </div>  
                </div>
                <div class="col-6 mb-3">
                  <div class="input-group">
                    <span class="input-group-text btn btn-light border"><i class="bi bi-twitter-x"></i></span>
                    <input class="form-control" type="text" name="site[social][twitter]" value="{$site.social.twitter}" placeholder="X/Twitter">
                  </div>  
                </div>
                <div class="col-6 mb-3">
                  <div class="input-group">
                    <span class="input-group-text btn btn-light border"><i class="bi bi-youtube"></i></span>
                    <input class="form-control" type="text" name="site[social][youtube]" value="{$site.social.youtube}" placeholder="Youtube">
                  </div>  
                </div>
                <div class="col-6 mb-3">
                  <div class="input-group">
                    <span class="input-group-text btn btn-light border"><i class="bi bi-instagram"></i></span>
                    <input class="form-control" type="text" name="site[social][instagram]" value="{$site.social.instagram}" placeholder="Instagram">
                  </div>  
                </div>
                <div class="col-6 mb-3">
                  <div class="input-group">
                    <span class="input-group-text btn btn-light border"><i class="bi bi-tiktok"></i></span>
                    <input class="form-control" type="text" name="site[social][tiktok]" value="{$site.social.tiktok}" placeholder="Tiktok">
                  </div>  
                </div>
                <div class="col-6 mb-3">
                  <div class="input-group">
                    <span class="input-group-text btn btn-light border"><i class="bi bi-linkedin"></i></span>
                    <input class="form-control" type="text" name="site[social][linkedin]" value="{$site.social.linkedin}" placeholder="Linkedin">
                  </div>  
                </div>
              </div>  
            </div>
          </div>
        </div>      
        {if $site.id > 0}
        <div id="tab-apps" class="tab-pane" role="tabpanel">
          <div class="row row-cols-1 row-cols-md-4 g-3 view-container">
            {foreach $site.apps as $app_id => $app}
            <div class="item col" data-filter="">
              <div class="thumbnail card h-100">
                <div class="card-body">
                  <div class="row">
                    <div class="col-12 {if $app.hide}text-secondary{/if}">{$app.type} ➝ {$app.name}
                      {if $app.label}
                        / {$app.label}
                      {/if}
                      {if $links.show AND ($app.type eq Core OR $app.type eq App OR $app.type eq Widget)}
                        <button type="button" class="btn btn-sm border-0 py-0 btn-outline-secondary float-end js-sg-show" data-url="{$links.show}" data-app="{$app_id}" role="button" title="Show in Menu"><i class="bi bi-eye{if $app.hide}-slash{/if}"></i></button>
                      {/if}
                    </div>
                    <div class="col-12 pt-3">
                    {if $links.register}
                      <button type="button" class="btn btn-sm btn-outline-primary" data-url="{$links.register}" data-confirm="register {$app.name}" data-name="name" data-value="{$app_id}">{"Register"|trans}</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary" data-url="{$links.deregister}" data-confirm="deregister {$app.name}" data-name="name" data-value="{$app_id}">{"Deregister"|trans}</button>
                    {/if}
                    {if $app.configurable == 'configurable' OR $app.configurable == 'developer'} 
                      <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-url="{$links.configure}?name={$app.type}/{$app.name}&sgframe=1" data-bs-target="#dynamicModal" data-title="{'Configure :item'|trans: ['item' => $app.name]}">{'Configure'|trans}</button>
                    {/if}   
                    </div>
                  </div>
                </div>               
              </div>
            </div>
            {/foreach}
          </div>  
        </div>
        {/if}        
      </div>  
    </div>
    <div class="card-footer text-center">
      <input type="submit" name="submit" class="btn btn-lg btn-primary my-2" value="Save">
    </div>
  </div>
</div>  
</form>
<!--  Bootstrap select -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<script defer src="{$system.cdn}/{$template}/assets/js/postmessage.js" id="sg-post-message" data-origin="{$system.url}"></script>
<script type="text/javascript">
  document.addEventListener("DOMContentLoaded", function(){
    //post via ajax to show/hide app
    $('.js-sg-show').on('click', function(ev) {
      ev.preventDefault();
      var el = this
      var href = $(el).attr('data-url');
      var nvp = 'show[app]='+ $(this).attr('data-app') +'&csrf_token='+ window.csrf_token +'&format=json';
      $.post(href, nvp, function(response){
        if (response.status.result == 'success'){
          if (response.show){
            el.querySelector('i').classList.replace('bi-eye-slash', 'bi-eye')
            el.parentNode.classList.remove('text-secondary')
          } else if (response.show == false) {
            el.querySelector('i').classList.replace('bi-eye', 'bi-eye-slash')
            el.parentNode.classList.add('text-secondary')
          }  
        }
      }).fail( () => el.remove() )
    }) 
  })
</script>