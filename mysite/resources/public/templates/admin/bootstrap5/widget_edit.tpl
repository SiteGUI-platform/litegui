{if $hide_editor_toolbar}
	{$block_widget nofilter}
{else}
	{$footer_padding = 0 scope=global}
	{$widget = $api.widget}
	<link href='{$system.cdn}/{$template}/assets/css/nestable.css' rel='stylesheet' />
	<form class="form-inline px-0" id='form-widget' action="{$links.update}" method="post">
	  <div class="col-md-12 toolbar">
	    <div class="row justify-content-center align-items-center">
	      <div class="col-auto d-none d-sm-block sg-hover-back">
				{if $links.main}<a href="{$links.main}">{/if}
				<i class="bi bi-bookmark fs-5"></i> 
				{if $links.main}</a>{/if}
		  	</div>
			  <div class="col px-sm-0 ps-3">	
			 		{if $widget.id > 0}<input name='widget[id]' type='hidden' value='{$widget.id}'>{/if}
					<input class="input-name form-control-lg text-success" type="text" name="widget[name]" placeholder="{':Item Name'|trans:['item' => $api.app|default:$widget.type] }" value="{$widget.name}" required>
					<input name='widget[type]' type='hidden' value='{$widget.type}'>
	      </div>
			  <div class="col-sm col-auto order-sm-last text-end"><button id="sg-save-widget" class="btn btn-lg btn-primary" type="submit" name='save_widget'>{"Save"|trans}</button></div>
	      <div class="col-sm-auto my-2">
	      	<div class="input-group">
						<button type="button" class="input-group-text btn btn-secondary" data-bs-toggle="modal" data-bs-target="#locModal">{"Location"|trans} <i class="bi bi-arrow-up-right-square-fill text-lime"></i></button>  
						<select class="form-select" id='location' name='widget[location]'>
							<option></option>
							<option value='Site'>{"Site"|trans}</option>
							<option value='Page'>{"Page"|trans}</option>		
							<option value='Product'>{"Product"|trans}</option>
							{foreach $html.top_menu.apps.children.Apps.children AS $app}
							<option value="{$app.type}">{$app.name}</option>
							{/foreach}
							{foreach $html.top_menu.apps.children['More Apps'].children AS $app}
							<option value="{$app.type}">{$app.name}</option>
							{/foreach}
						</select>
					</div>	
	  		</div>	
		    <div class="col-sm-auto my-2">	  
		      	<div class="input-group">
					<button type="button" class="input-group-text btn btn-secondary pe-3 pe-md-2"><a class="text-decoration-none text-white" href="https://{$site.url}/?nam=sgblock" target="_blank">{"Section"|trans} <i class="bi bi-arrow-up-right-square-fill text-lime"></i></a></button>
					<select class="form-select" id='section' name='widget[section]'>
						<option></option>
						{foreach from=$html.sections item=section}
						<option value='{$section}'>{$section|replace:'_':' '|capitalize}</option>								
						{/foreach}}
					</select>
			  		<input class="form-control" type='number' placeholder="{'Placement'|trans}" name='widget[sort]' value="{$widget.sort}" maxlength="3" min="-100" max="999" style="width: 1rem;">
					<button type="button" class="input-group-text btn btn-secondary"><i class="bi bi-sort-numeric-down"></i></button>
				</div>	
			</div>		
			<div id='page-id-wrapper' class="col-sm-auto my-2 d-none">
			  	<div class="input-group dropdown">
			  		<button class="input-group-text btn btn-secondary" type="button"><i class="bi bi-search"></i></button>
			  		<input class="form-control lookup-field dropdown-toggle rounded-end" id='page-id' type='text' placeholder="{'Name'|trans}" data-url="{$links.lookup}" data-lookup="page" data-name='widget[page_id]' data-multiple=0 data-bs-auto-close="outside" data-bs-toggle="dropdown" aria-expanded="false">
						<div class="dropdown-menu px-2"></div>
				</div>	
			</div>		
	    </div>
	  </div>
	  <div id="widget-editor" class="col-md-12">
			{$block_widget nofilter}
	  </div>
	</form>
  	<div id="locModal" class="modal fade backdrop-blur">
		<div class="modal-dialog modal-sm">
		    <div class="modal-content">
		       <div class="modal-header">
		          <h5 class="modal-title">{":Item Location"|trans:['item' => $widget.type] }</h5>
		          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		       </div>
		       <div class="modal-body">
					<form class="form-inline px-0" action="{$links.update}" method="post">
					{foreach from=$widget.locations item=loc}
						<div id='loc{$loc.id}'>
							<a href="#" class="float-end" data-url='{$links.delete_location}' data-name="id" data-value="{$loc.id}" data-confirm="delete" data-remove="#loc{$loc.id}"><i class="bi bi-x-circle text-danger"></i></a>
							{$loc.location|capitalize|trans}{if $loc.name}: {$loc.name}{elseif $loc.page_id}#{$loc.page_id}{/if} @ {$loc.section|capitalize|trans} <span class="text-nowrap">(⇅ <input class="border-0" type="number" name="widget[placements][{$loc.id}]" value="{$loc.sort}" maxlength="3" min="-100" max="999">)</span>
						</div>	 
					{foreachelse}
						{"No records found"|trans}!		
					{/foreach}
					{if $widget.locations}
						<div class="d-grid mt-4">
							<input type="hidden" name="widget[id]" value="{$widget.id}">
							<button class="btn btn-primary">{'Update :item'|trans:["item" => "Placement"]}</button>
						</div>	
					{/if} 
					</form>      	
		       </div>
		    </div>
	 	</div>      
  	</div>
	<script>
	  	document.addEventListener("DOMContentLoaded", function(e){
			$('#location').change(function() {
				if ($(this).val() && $(this).val() != 'Site') {
					$('#page-id-wrapper').removeClass('d-none').find('.lookup-field').attr('data-lookup', $(this).val().toLowerCase());
					//remove previously selected value
					$('#page-id-wrapper .dropdown-menu').text('')	
					$('#page-id-wrapper span.input-group-text').remove()
				}else{
					$('#page-id-wrapper').addClass('d-none');
				} 
			});
		});
	</script>
	{include "lookup.tpl"}
{/if}