<div class="col-12 {if $system.sgframe}px-0{else}col-md-10 pt-sm-2{/if} pb-40 mx-auto">
<form class="w-100" action="{$links.update}" method="post" class="has-date" {if !$html.file_manager}enctype="multipart/form-data"{/if}>
  <div class="card">
    <div class="card-body row g-0 align-items-center">
      <div class="col-auto sg-hover-back pe-0">
        {if $links.main}<a href="{$links.main}">{/if}
        <i class="bi bi-bookmark ps-3 fs-4"></i>
        {if $links.main}</a>{/if}
      </div>
      <div class="col p-2">
        <span class="form-control-lg text-success">{$api.staff.name|default:$api.user.name|default:$html.title}</span>  
      </div>
      <div class="col-auto pe-2"></div>        
    </div>
    <div class="sg-main" role="tabpanel">
      <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
          <a href="#tab-edit" aria-controls="tab-edit" class="nav-link active" role="tab" data-bs-toggle="tab">
            {if $api.user.id || $api.staff.admin_id} {"Edit"|trans} {else} {"New"|trans} {/if}
          </a>
        </li>
        {foreach $api.app.sub AS $subapp => $option}
        <li class="nav-item">
          <a href="#tab-{$subapp}" data-bs-target="#tab-{$subapp}" aria-controls="tab-{$subapp}" class="nav-link" role="tab" data-bs-toggle="tab">
            {$subapp|trans}
          </a>
        </li>
        {/foreach}
        {if $html.tab_api}
        <li class="nav-item">
          <a href="#tab-api" aria-controls="tab-api" class="nav-link" role="tab" data-bs-toggle="tab">
            {"API Token"|trans}
          </a>
        </li>
        {/if}
      </ul>
      <div class="tab-content px-3 py-4 sg-form">
        {block name="tab-edit"}
        <div id="tab-edit" class="tab-pane active" role="tabpanel"> 
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Name"|trans}</label>
            <div class="col-sm-7">
              {if $api.user.id}
              <input type="hidden" name="user[id]" value="{$api.user.id}">
              {/if}
              <input class="form-control" type="text" name="user[name]" size="35" value="{$api.user.name}" placeholder="{'Name'|trans}" required>
            </div>
          </div> 
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Email"|trans}</label>
            <div class="col-sm-7">
              <input class="form-control" type="text" name="user[email]" size="35" value="{$api.user.email}" placeholder="{'Email'|trans}" autocomplete="email" {if !$html.email_optional}required{/if}>
            </div>
          </div>    

          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end"></label>
            <div class="col-sm-7">
              <a data-bs-toggle="collapse" href="#collapsePassword" role="button" aria-expanded="false" aria-controls="collapsePassword">{"Change Password"|trans}</a>
            </div>  
          </div> 
          <div class="collapse" id="collapsePassword"> 
            <div class="form-group row mb-3">
              <label class="col-sm-3 col-form-label text-sm-end">{"Password"|trans}</label>
              <div class="col-sm-7">
                <input class="form-control" type="password" name="user[password]" size="35" value="" placeholder="{'Password'|trans}" autocomplete="new-password">
              </div>
            </div>  
            <div class="form-group row mb-3">
              <label class="col-sm-3 col-form-label text-sm-end">{"Confirm Password"|trans}</label>
              <div class="col-sm-7">
                <input class="form-control" type="password" name="user[password2]" size="35" value="" placeholder="{'Confirm Password'|trans}" autocomplete="new-password">
              </div>
            </div> 
          </div>
            
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Mobile"|trans}</label>
            <div class="col-sm-7">
              <input class="form-control" type="text" name="user[mobile]" size="35" value="{$api.user.mobile}" placeholder="{'Mobile'|trans}" {if $html.email_optional}required{/if}>
            </div>
          </div>  
          {include "form_field.tpl" formFields=[
            'image' => [
              'type' => 'image', 
              'label' => 'Avatar', 
              'value' => "{$api.user.image}"
            ]
          ] fieldPrefix="user"}   
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Language"|trans}</label>
            <div class="col-sm-7">
              {if $site.language eq ''}{$site.language = 'en'}{/if}
              <select class="form-control selectpicker show-tick" data-live-search="true" data-style="form-control" name="user[language]">
                <option value="">{"System"|trans}</option>
                {foreach $html.languages as $key => $lang}
                <option value="{$key}" {if $key == $api.user.language}selected{/if}>
                  {$lang|capitalize:true}
                </option>
                {/foreach}  
              </select>                          
            </div>
          </div> 
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Timezone"|trans}</label>
            <div class="col-sm-7">
              <select class="form-control selectpicker show-tick" data-live-search="true" data-style="form-control" name="user[timezone]">
                <option value="">{"System"|trans}</option>
                {foreach $html.timezones as $tz}
                <option value="{$tz.identifier}" {if $tz.identifier == $api.user.timezone}selected{/if}>
                  (GMT {$tz.offset}) {$tz.identifier|replace: '_':' '}
                </option>
                {/foreach}  
              </select>     
            </div>
          </div>
          {if $html.file_manager} 
            {include "form_field.tpl" formFields=[
              'groups' => [
                'type' => 'select', 
                'is' => 'multiple',
                'label' => {'Groups'|trans},
                'slug' => $links.group,
                'value' => $api.user.groups,
                'options' => $api.groups
              ]
            ] fieldPrefix="user"}  
          {/if}  
 
          {if $api.user.id ne $user.id}
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Status"|trans}</label>
            <div class="col-sm-7">
              <select class="form-select" name="user[status]">
                <option value="Inactive">{"Inactive"|trans}</option>
                <option value="Active" {if $api.user.status eq Active}selected{/if}>{"Active"|trans}</option>
                <option value="Unverified" {if $api.user.status eq Unverified}selected{/if}>{"Unverified"|trans}</option>
              </select>  
            </div>
          </div>
          {/if}
        </div>
        {/block}
        {foreach $api.app.sub AS $subapp => $option}
        <div id="tab-{$subapp}" class="tab-pane" role="tabpanel">
          <div class="row">
          {$links.custom_api=$option.link_api}
          {include "datatable.tpl" forapp="$subapp"}
          
          {if $option.link_edit}
          <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function(e){        
              $("#app-{$subapp}-header .sg-app-header").append( 
                $('<button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#dynamicModal"></button>')
                .attr('data-url', "{$option.link_edit}".replaceAll('&amp;', '&')) 
                .attr('data-title', "{'New :item'|trans:['item' => $subapp|replace: '_':' ']}")
                .text("{'New :item'|trans:['item' => $subapp|replace: '_':' ']}") 
              )
            })  
          </script>  
          {/if}
          </div> 
        </div>  
        {/foreach}
        {if $html.tab_api}
        <div id="tab-api" class="tab-pane" role="tabpanel">
          <div id="js-token-message" class="text-center my-2 text-success"></div>
          <div id="js-token-display" class="col-md-8 mx-auto">
          {foreach $html.api_tokens AS $token}
            <div class="form-group row mb-3">
            {if $token@first}
              <div class="col-12 my-3">{"Token secret is not stored. Please delete and reissue a new token if needed"|trans}</div>  
            {/if}
              <label class="col-auto col-form-label text-sm-end fw-bold">{"Token Name"|trans}</label>
              <div class="col col-form-label">
                 {$token.name} 
              </div>
              <label class="col-auto col-form-label text-sm-end fw-bold">{"Expiry Date"|trans}</label>
              <div class="col col-form-label">
                 {$token.description|date_format|default: '∞'} 
              </div>
              <div class="col mb-3 text-start">
                <button type="button" class="btn btn-danger js-token-delete" data-token-id={$token.id}>{"Delete"|trans}</button>
              </div> 
            </div>     
          {/foreach}
          </div>
          <div id="js-create-form" class="{if $html.api_tokens}d-none{/if}">
            <div class="form-group row mb-3">
              <label class="col-sm-3 col-form-label text-sm-end">{"Token Name"|trans}</label>
              <div class="col-sm-7">
                <input id="js-token-name" class="form-control" type="text">
              </div>
            </div> 
            <div class="form-group row mb-3">
              <label class="col-sm-3 col-form-label text-sm-end">{"Expiry"|trans}</label>
              <div class="col-sm-7">
                <div class="input-group">
                  <input id="js-token-expiry" type="text" class="form-control datetimepicker-input" data-target="#js-token-expiry" data-toggle="datetimepicker"/> 
                  <span class="bi bi-calendar3 input-group-text" data-target="#js-token-expiry" data-toggle="datetimepicker"></span>
                </div> 
              </div>
              <script defer type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment-with-locales.min.js"></script>
              <script defer type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.31/moment-timezone-with-data.js"></script>
              <script defer type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/js/tempusdominus-bootstrap-4.min.js"></script>
              <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/css/tempusdominus-bootstrap-4.min.css" />
              <script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function(e){
                  $('#js-token-expiry').datetimepicker({
                    format: 'L',    
                    locale: "{$site.locale|default:$user.language|default:$site.language}",
                    timeZone: '{$user.timezone|default:$site.timezone}',    
                    icons: {
                      previous: 'bi bi-chevron-left',
                      next: 'bi bi-chevron-right',
                    },    
                  });
                })
              </script>    
            </div>
            <div class="col-12 mb-3 text-center">
              <button type="button" id="js-create-token" class="btn btn-outline-secondary">{"Create"|trans}</button>
            </div>
          </div>  
        </div> 
        {/if} 
      </div>
    </div>        
    <div class="card-footer text-center">
      <span><button type="submit" class="btn btn-lg btn-primary my-2"><i class="bi bi-save pe-2"></i>  {"Save"|trans}</button></span>
    </div>
  </div>
</form>
</div>  
<!--  Bootstrap select -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- hide select all button -->
<style type="text/css">
.bs-actionsbox .btn-group button {
  width: 100%;
}
.bs-select-all {
  display: none;
}
</style>
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<script defer src="{$system.cdn}/{$template}/assets/js/postmessage.js?v=13" id="sg-post-message" data-origin="{$system.url}"></script>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(e){
  $('.carousel-multi').each(Sitegui.carousel);
{if $api.staff.user_id} //token management
  $('#js-create-token').on('click', function (){
    if ($('#js-token-name').val().length) {
      var nvp = 'staff[user_id]='+ {$api.staff.user_id}
      nvp += '&staff[token][name]='+ $('#js-token-name').val();
      if ( $('#js-token-expiry').val() ){
        nvp += '&staff[token][expiry]='+ $('#js-token-expiry').datetimepicker("viewDate").unix();
      }
      nvp += '&csrf_token='+ window.csrf_token +'&format=json';

      $.post('{$links.update}', nvp, function(data) { // already a json object jQuery.parseJSON(data);
        if (data.status.result == 'success') {
          $('#js-token-message').html('{"Token secret created"|trans}: <span id="js-token-secret" class="text-primary">'+ data.token.secret +'</span');
          $('#js-token-message').append('<br><button type="button" class="btn btn-danger m-2 js-token-delete" data-token-id='+ data.token.id +'>{"Delete"|trans}</button> <button type="button" class="btn btn-outline-primary m-2 js-token-copy">{"Copy Secret"|trans}</button>');
          $('#js-create-form').addClass('d-none');
        } else {
          $('#js-token-message').text(data.status.message[0]);
        } 
      })  
    } else {
      $('#js-token-name').focus();
    }     
  })
  $('#tab-api').on('click', '.js-token-copy', function (){
    /* Get the text field */
    var token = document.getElementById("js-token-secret");
    var button = this;
    /* Select the text field */
    //copyText.select(); 
    //copyText.setSelectionRange(0, 99999); /* For mobile devices */

    navigator.clipboard.writeText(token.innerText).then(function() {
      button.innerText = 'Secret Copied';
    }, function() {
      button.innerText = 'Failed';
    });
    
  })
  $('#tab-api').on('click', '.js-token-delete', function (){
    var nvp = 'staff[user_id]='+ {$api.staff.user_id}
    nvp += '&staff[token][id]='+ $(this).attr('data-token-id');
    nvp += '&token_delete=1&csrf_token='+ window.csrf_token +'&format=json';

    $.post('{$links.update}', nvp, function(data) { // already a json object jQuery.parseJSON(data);
      if (data.status.result == 'success') {
        $('#js-token-message').text('Token deleted');
        $('#js-token-display').addClass('d-none');
        $('#js-create-form').removeClass('d-none');
      } else {
        $('#js-token-message').text(data.status.message[0]);
      }   
    }) 
  }) 
{/if}       
});  
</script>