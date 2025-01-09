<style>
.content-pane {
	padding-top: 0;
}
.login-container {
  width: 100vw;		
  height: 100vh;
  display: grid;
}

#particles {
	width: 100vw;
	height: 100vh;
	position: absolute;
}
</style>
<div class="login-container">
	<div class="col-sm-8 m-auto modal d-block">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
			  <div class="modal-header" draggable="true" ondragstart="this.parentNode.parentNode.classList.toggle('ms-5')" ondblclick="this.parentNode.parentNode.classList.toggle('me-5')">
			    	<h5 class="modal-title"><a href="https://{$site.url}"><img class="img-responsive" src="{if $site.logo}{$site.logo}{else}{$system.cdn}/{$template}/assets/img/logo.png{/if}" alt="Site Logo" style="max-width: 200px;max-height: 80px;"></a></h5>
			  </div>
        {if $html.guest_checkout}
        <form action="{$links.cart_checkout}" method="post">
          <div class="modal-body mx-auto px-5">
            <div class="row g-0 pb-3">
              <div class="col-12 d-grid mt-3">
                <button class="btn btn-block btn-outline-danger" name="guest_checkout" value="1">{'Guest Checkout'|trans}</button>
              </div>
            </div> 
          </div>   
        </form>
        {/if}        
        <form method="post" action="{$system.base_path}" class="w-100">
			  <div class="modal-body mx-auto px-5" style="min-height: 280px;">
          <div class="row pb-3">
            <div class="col">
              <h6 class="card-title">
                {"Please enter your"|trans} 
                {if $html.do_recover}
                  {"new password"|trans}
                {else}
                  {"mobile or email address to"|trans}
                  <span class="collapse-login collapse collapse-horizontal show">{"login"|trans} {if $html.request.user_register}{"or create an account"|trans}{/if}</span>
                  <span class="collapse-login collapse collapse-horizontal">{"recover password"|trans}</span>
                {/if}
              </h6>  
            </div>                     
          </div>
          <div class="row pb-3 {if !$html.request.user_invite}d-none{/if}">
            <div class="col input-group">
              <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
              <input type="text" class="form-control" name="name" id="js-sg-name" value="{$html.request.name}" placeholder="{'Your Name'|trans}">
            </div>
          </div> 
          <div class="row pb-3">
            <div class="col input-group">
              <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
              <input type="text" class="form-control" name="username" value="{$html.request.username}" placeholder="{'Mobile or Email'|trans}" required>
              {if $html.user_otp}
              <input type="hidden" name="user_otp" value="{$html.user_otp}">
              {/if}
            </div>
          </div>  
          <div class="row collapse-login collapse show">            
            <div class="col input-group pb-3">
              <span class="input-group-text"><i class="bi bi-key"></i></span>
              <input type="password" class="form-control" name="password" placeholder="{if $html.user_otp}OTP{else}{'Your Password'|trans}{/if}" autocomplete="current-password">
            </div>
          </div>
          {if $html.request.user_invite || $html.do_recover}
          <div class="row collapse-login collapse show">            
            <div class="col input-group pb-3">
              <span class="input-group-text"><i class="bi bi-key"></i></span>
              <input type="password" class="form-control" name="password2" placeholder="{'Confirm Password'|trans}" autocomplete="current-password">
              {if $html.do_recover}
              <input type="hidden" class="form-control" name="user_recover" value="{$html.request.user_recover}">
              {/if}
            </div>
          </div>
          {/if}    
          <div class="row collapse-login collapse show">
            <div class="col d-none">
              <input type="checkbox" value="remember-me" style="margin: 0 11px;">  {"Remember Me"|trans} 
            </div>
            <div class="col">
              <a href="#" class="float-start" data-bs-toggle="collapse" data-bs-target=".collapse-login" aria-expanded="true" aria-controls="collapse-login">{"Forgot password"|trans}?</a>
            </div>
          </div> 
          <div class="row collapse-login collapse show">
            <div class="col d-grid py-3">
              <button class="btn btn-primary" name="user_login" value="1">
                {if $html.do_recover}
                  {"Change Password"|trans}
                {else}
                  {"Login"|trans}
                {/if}  
              </button>
            </div>
            {if $html.request.user_register}
            <div class="col d-grid py-3">
              <button class="btn btn-outline-secondary" name="user_register" id="js-sg-create" value="1">{"Create Account"|trans}</button>
            </div>
            {/if}
          </div> 
          <div class="row collapse-login collapse">
            <div class="col d-grid py-3">
              <a href="#" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target=".collapse-login" aria-expanded="true" aria-controls="collapse-login"><i class="bi bi-box-arrow-left me-2"></i> {"Back to Login"|trans} </a>
            </div>
            <div class="col d-grid py-3">
              <button class="btn btn-warning text-white" name="user_recover" value="1">{"Recover Password"|trans}</button>
            </div>
          </div> 
        </div>        
        </form>
        {if $api.oauth} 
        <div class="modal-footer row gx-0 pb-4">
          <div class="col-12 text-center pb-2">                
            <i class="card-text">{"or connect using social account"|trans}...</i>
          </div>
          {foreach $api.oauth AS $oid => $label}
            {if $label@index < 2 OR $api.oauth|count eq 3}  
            <div class="col text-center">
              <a class="btn-group text-decoration-none" href="{$system.url}{$system.base_path}?oauth={$oid}&login=step1" target="_top">
                <button type="button" class="btn btn-{if $oid eq google}danger{else}primary{/if}"><i class="bi bi-{$oid} bi-twitter-{$oid}"></i></button>
                <button type="button" class="btn btn-{if $oid eq google}danger{else}primary{/if}">{$label}</button>
              </a>
            </div> 
            {else}
              {$extra_oauth = $extra_oauth|cat:"<li><a class='dropdown-item text-decoration-none' href='{$system.url}{$system.base_path}?oauth={$oid}&login=step1' target='_top'><i class='bi bi-{$oid} bi-twitter-{$oid}'></i> {$label}</a></li>"}
            {/if}
          {/foreach}
          {if $extra_oauth}          
          <div class="col text-center">
            <div class="dropdown">
              <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                {"Login via"|trans}
              </button>
              <ul class="dropdown-menu">
                {$extra_oauth nofilter}  
              </ul>
            </div>
          </div>
          {/if}
        </div>     
        {/if}  
			</div>
      <br><br>               	
		</div>
	</div>
</div>
<script type="text/javascript">
  document.querySelector('#js-sg-create') && 
  document.querySelector('#js-sg-create').addEventListener('click', function(ev){
    if ( !document.querySelector('#js-sg-name').value ){
      ev.preventDefault()
      document.querySelector('#js-sg-name').parentNode.parentNode.classList.remove('d-none')
      document.querySelector('#js-sg-name').setAttribute('placeholder', Sitegui.trans('Please enter your name'))
      document.querySelector('#js-sg-name').focus()
    }
  })
</script>