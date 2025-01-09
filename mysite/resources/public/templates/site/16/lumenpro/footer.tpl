{block name="block_footer"}
<div id="footer" class="row">
	<div class="">
		<div class="container-md">
			<div class="row justify-content-md-end">
				{foreach from=$html.footer_menu item=level1}
				<div class="col-md-{math equation='x/y' x=9 y=$html.footer_menu|count format='%d'} p-3">
				 	<div class="">
				    	<a class='title' href="{$level1.slug}">{$level1.name}{if $level1.id eq $page_id}{/if}{if $level1.active}{/if}</a>
				    	<ul class="footer-list">
							{foreach from=$level1.children item=level2}
				          	<li class="footer-link"><a href="{$level2.slug}">{$level2.name}{if $level2.id eq $page_id}{/if}</a></li>
				       		{/foreach}
				    	</ul>
				 	</div>
				</div>
				{/foreach}
				<div class="col-md-3 p-3 text-end d-none d-md-block">
						<span class='title'>{$site.name}</span><br>
						<br>{$site.name} © {$smarty.now|date_format:"%Y"}. 
						<br>{'All rights reserved.'|trans}
				</div>	
				<div class="col-md-12">{$block_footer nofilter}</div>
			</div>	
		</div>	
	</div>		
</div>
{/block}
{block name='block_script'}
<!-- Dynamic Modal -->
<div class="modal fade backdrop-blur" id="dynamicModal" tabindex="-1" role="dialog" aria-labelledby="dynamicModalName" style='z-index:5000;'>
  <div class="modal-dialog modal-fullscreen modal-halfscreen-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dynamicModalName"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      <button id="dynamicModalReload" type="button" class="bg-transparent border-0 text-secondary p-0 ms-2 fs-4 d-none"><i class="bi bi-arrow-repeat"></i></button>
      </div>
      <div class="modal-body" style='height:80vh; padding: 1px;'>
        <!-- Element where elFinder will be created (REQUIRED) -->
        <div class="progress" style="height: 5px; width:100%; position: absolute;">
          <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
        </div>
        <iframe id="dynamicModalFrame" src="" data-src="{$html.file_manager}" style="zoom:1" width="100%" height="100%" frameborder="0"></iframe>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>
</div>
<style type="text/css">

</style>
<script type="text/javascript">
	//3-level dropdown navigation menu 
	document.querySelectorAll('.nav-link.dropdown-toggle').forEach(el => {
		el.addEventListener('click', function(ev) {
			if ( !el.classList.contains("clickable")){
		    //console.log('go')
		    location.href = el.getAttribute('href')
			} else {
		    //console.log('nogo')
				ev.preventDefault()
		    el.classList.add("clickable")
			}
	  })
	  el.addEventListener('shown.bs.dropdown', function() {
	  	el.classList.add("clickable")
	  })
	  el.addEventListener('hidden.bs.dropdown', function() {
	  	el.classList.remove("clickable")
	  })	
	  el.parentNode.addEventListener('touchstart', function(ev) {
	  	//console.log('touchstart')
	  	el.parentNode.removeEventListener('mouseenter', el.parentNode.mouseenter)
	  })	
	})
	document.querySelectorAll('.navbar .nav-item').forEach(el => {	  
	  el.addEventListener('mouseenter', el.mouseenter = function(ev) {
		  //console.log('touch devices trigger mouseenter when clicked, mouseout too quick when moving mouse to dropdown-menu')
		 	//console.log(ev.target.parentNode.querySelectorAll('.nav-link.dropdown-toggle.show.clickable'))
		 	ev.target.parentNode.querySelectorAll('.nav-link.dropdown-toggle.show.clickable').forEach(el2 => {
		 		bootstrap.Dropdown.getOrCreateInstance(el2).hide()
	  	})	
     	el.querySelector('.dropdown-toggle') && bootstrap.Dropdown.getOrCreateInstance(el.querySelector('.dropdown-toggle')).show()
	  })
	})
</script>
{/block}
</body>
</html>
