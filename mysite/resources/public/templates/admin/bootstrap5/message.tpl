<div class="sg-message col-12" {if !$block_main}style="padding-top:25vh;"{/if}>
  <div class="row">
    <div class="col-12 col-md-10 mx-auto">
    {if $api.status.message}
    	{if $html.message_title || $api.status.result eq 'error'}
    		<div class="card mb-3 mt-sm-2 border-0">
    		  	<div class="card-header text-white bg-{if $html.message_type}{$html.message_type}{else if $api.status.result eq 'error'}danger{else}primary{/if}">
    		    	{$html.message_title|default:{'Error'|trans} }
    		  	</div>
    		  	<div class="card-body pt-2">
      		  	{foreach $api.status.message AS $link => $message}
                {if $link != (int) $link}<a href="{$link}">{/if}
        				<div class="card-text pt-2">{$message}</div>	{* message can be added by client, must be filtered*} 	 			
                {if $link != (int) $link}</a>{/if}
      		  	{/foreach}
              {if $html.extra_info}
                <div class="py-3">
                  {if $user.staff}
                    {$html.extra_info}
                  {else}  
                    {$html.extra_info nofilter}  
                  {/if}
                </div>  
              {/if} 
    		  	</div>
    		</div>
    	{else}
    		<div class="status-message-container alert alert-{$html.message_type|default: 'info'} {if $system.sgframe}mt-3{/if} mt-sm-2 alert-dismissible fade show" role="alert">
    			{foreach $api.status.message AS $link => $message}
            {if $link != (int) $link}<a href="{$link}" class="">{/if}
            <div class="card-text pt-2">{$message}</div>         
            {if $link != (int) $link}</a>{/if}
          {/foreach}
    			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    			<script type="text/javascript">
      				document.addEventListener("DOMContentLoaded", function(e){
    					$(".status-message-container").fadeTo(6000, 500).slideUp(500, function(){
    					    $(".status-message-container").alert('close');
    					});
    				});	
    			</script>
    		</div>
    	{/if}
    {/if}

      <form id="dataConfirmForm" action="" method="POST">
        <div id="dataConfirmModal" class="modal fade backdrop-blur" style='z-index:5010;'>
          <div class="modal-dialog modal-sm">
              <div class="modal-content">
                 <div class="modal-header">
                    <h5 class="modal-title">{"Please Confirm"|trans}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                 </div>
                 <div class="modal-body">
                    {"Are you sure you want to"|trans} <span class="modal-action"></span>?
                    <input type="hidden" id="dataConfirmHidden" name="" value="">
                    <div class="form-check d-none" id="dataConfirmSubapp">
                      <br>
                      <input class="form-check-input" type="checkbox" name="subapps" value="1" id="delete-subapps">
                      <label class="form-check-label" for="delete-subapps">
                        {"Delete linked <span class='modal-subapp'></span> records"|trans}?
                      </label>
                    </div>
                 </div>
                 <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{"Cancel"|trans}</button>
                    <button class="btn btn-primary" id="dataConfirmOK">{"OK"|trans}</button>
                 </div>
              </div>
           </div>      
        </div>
      </form>
      <!-- Dynamic Modal -->
      <div class="modal fade backdrop-blur" id="dynamicModal" tabindex="-1" role="dialog" aria-labelledby="dynamicModalName" style='z-index:5000;'>
        <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content rounded-1">
            <div class="modal-header">
              <a href="#" id="dynamicModalLink" target="_top" class="btn btn-sm btn-outline-secondary border-0 rounded-circle me-2"><i class="bi bi-arrows-angle-expand"></i></a>
              <h5 id="dynamicModalName" class="modal-title">{"File Manager"|trans}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <button id="dynamicModalReload" type="button" class="btn border-0 text-secondary p-0 ms-2 fs-4 d-none"><i class="bi bi-arrow-repeat"></i></button>
            </div>
            <div class="modal-body" style='height:86vh; padding: 1px;'>
              <!-- Element where elFinder will be created (REQUIRED) -->
              <div class="progress" style="height: 5px; width:100%; position: absolute;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
              </div>
              <iframe id="dynamicModalFrame" src="" data-src="{$html.file_manager}" style="zoom:0.9" width="100%" height="100%" frameborder="0"></iframe>
              <div class="clearfix"></div>
            </div>
          </div>
        </div>
      </div>
      {if $links.editor}
      <!-- Full screen modal -->
      <div id="fullscreen-modal" class="modal fade px-0">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
              <div class="modal-body p-0">
                <div class="progress" style="height: 5px; width:100%; position: absolute;">
                  <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                </div>
                <iframe src="" data-src='{$links.editor}?frame=editor' id='editor-frame' class="mx-auto d-block" frameborder="0" style="width: 100%; height: 100vh;" title="WYSIWYG Editor" tabindex="0" allowtransparency="true" loading="lazy"></iframe>
              </div>
              <button type="button" class="btn border-0 p-0 ps-sm-2 text-secondary hover-lime position-fixed bottom-0" data-bs-dismiss="modal" aria-label="Back">
                <i class="bi bi-reply fs-4"></i>
              </button>
            </div>  
        </div>  
      </div>  
      {/if}
    </div> 
  </div>   
</div>

{if $links.onboard}
<script defer src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
{/if}
{if $html.onboard_site}
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(e){
  const driverObj = window.driver.js.driver({
    showProgress: true,
    allowClose: false,
    steps: [
      { 
        popover: { 
          title: Sitegui.trans(':Item Quick Tour', {
            "Item": "Site"
          }), 
          description: Sitegui.trans("Welcome! Let's check out the menu to help you navigate around easier."), 
          side: "bottom", 
          align: 'center',
          onNextClick: () => {
            const selectors = [
              ".navbar .nav-item:first-child",
              ".navbar .nav-item:nth-child(2)",
              ".navbar .nav-item:nth-child(3)",
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
        element: '.navbar .nav-item:first-child', 
        popover: { 
          title: Sitegui.trans('Manage/Switch Site'), 
          description: Sitegui.trans('Click on this menu to show all the sites under your account.'), 
          side: "left", 
          align: 'center',
          onNextClick: (el) => {
            $(el).effect("highlight")
          } 
        },
      },{ 
        element: '.navbar .nav-item:first-child .dropdown-menu .row', 
        popover: { 
          title: Sitegui.trans('Manage/Switch Site'), 
          description: Sitegui.trans('To switch site, click on the chosen site. To edit a site, click on Manage Site and then choose Edit.'), 
          side: "left", 
          align: 'center',          
        }
      },{ 
        element: '.navbar .nav-item:nth-child(2)', 
        popover: { 
          title: Sitegui.trans('Current App'), 
          description: Sitegui.trans('The current app is shown here. Click on it to see other apps available for this site.'),
          side: "bottom", 
          align: 'center',
          onNextClick: (el) => {
            $(el).effect("highlight")
          }           
        }
      },{ 
        element: '.navbar .nav-item:nth-child(2) .dropdown-menu .row', 
        popover: { 
          title: Sitegui.trans('Common Apps'), 
          description: Sitegui.trans('Here are all common apps for this site. There are CMS apps for managing content, Commerce apps for ecommerce and Management apps for configuring your Site.'), 
          side: "left", 
          align: 'center',
        }
      },{ 
        element: '.navbar .nav-item:nth-child(2) .dropdown-menu .row .col-sm:last-child .dropdown-item:nth-child(4)', 
        popover: { 
          title: Sitegui.trans('Appstore'), 
          description: Sitegui.trans('Appstore contains many free and paid apps that can extend your site.'), 
          side: "left", 
          align: 'center' 
        }
      },{ 
        element: '.navbar .nav-item:nth-child(2) .dropdown-menu .row .col-sm:last-child .dropdown-item:nth-child(3)', 
        popover: { 
          title: Sitegui.trans('Manage Apps'), 
          description: Sitegui.trans('Default and bought apps are listed here. This is where you can activate, deactivate and configure apps.'), 
          side: "left", 
          align: 'center', 
          onNextClick: () => {
            var nvp = 'done=site&csrf_token='+ window.csrf_token +'&format=json';
            $.post('{$links.onboard}', nvp, function(data) { // already a json object jQuery.parseJSON(data);
              if (data.status.result == 'success') {                
              } else {
              }   
            }) 
            driverObj.moveNext();
          }
        }
      },{ 
        element: '.navbar .nav-item:nth-child(3)', 
        popover: { 
          title: Sitegui.trans('New App Entry'), 
          description: Sitegui.trans('This menu contains links for creating a new entry for your Apps. You can create a new Page, Product, User or even a brand new App here.'), 
          side: "bottom", 
          align: 'center',
          onNextClick: (el) => {
            $(el).effect("highlight")
          }           
        }
      },{ 
        element: '.navbar .nav-item:nth-child(3) .dropdown-menu .row .col-sm:nth-child(1) .dropdown-item:nth-child(2)', 
        popover: { 
          title: Sitegui.trans('Create A New Page'), 
          description: Sitegui.trans('Click here to create a new Page.'), 
          side: "bottom", 
          align: 'center',
          onNextClick: (el) => {
            $(el).effect("highlight")
          }
        }
      }
    ]
  })

  driverObj.drive()
})  
</script> 
{/if}