{$menu = $api.menu}
<link href='{$system.cdn}/{$template}/assets/css/nestable.css' rel='stylesheet' />
<script defer type="text/javascript" src="{$system.cdn}/{$template}/assets/js/jquery.nestable.js"></script>
<div class="col-12 {if $system.sgframe}px-0{else}col-md-10 pt-sm-2{/if} pb-40 mx-auto">
<form class="form-inline w-100" action='{$links.update}' method='post'>
{if $menu.id > 0}
	<input name='menu[id]' type='hidden' value='{$menu.id}'>
{/if}
	<input name='menu[data]' type='hidden' id='nestable-output'>
 	<div class="card w-100">
	  <div class="card-body row g-0 align-items-center">
			<div class="col-auto pe-0 d-none d-sm-block sg-hover-back">
				{if $links.main}<a href="{$links.main}">{/if}
				<i class="bi bi-bookmark px-3 fs-4"></i>
				{if $links.main}</a>{/if}
			</div>
			<div class="col ps-sm-0 px-2">	
				<input class="input-name form-control-lg text-success" type='text' value='{$menu.name}' title="{'Enter menu name here'|trans}" name='menu[name]' placeholder="{':Item Name'|trans:['item' => 'Menu'] }" required {if ! $menu.name}autofocus{/if}>
			</div>	
	  	<div class="col-auto ps-2 d-none d-sm-block">
				<button type="submit" name="save-btn" class="btn btn-outline-secondary border rounded-circle" title="{'Save'|trans}" style="height: 40px"><i class="bi bi-save"></i></button>
			</div>
	  </div>
		<div role="tabpanel">
			<!-- Nav tabs -->
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item"><a href="#tab-menu" aria-controls="tab-menu" class="nav-link active" role="tab" data-bs-toggle="tab">{"Builder"|trans}</a></li>
			</ul>

			<!-- Tab panes -->
			<div class="tab-content px-3 py-4">
				<div id="tab-menu" class="tab-pane active" role="tabpanel"> 
					<div class="row">
						<div class="col-md-8">
							<div id='nestable' class='dd position-sticky top-0 py-1'>
								<ol class='dd-list'>
								{foreach from=$menu.data item=level1}
									<li class='dd-item' data-id={$level1.id}>
										<div class='dd-handle'>{$level1.name}</div>
										{if $level1.children}
										<ol class='dd-list'>
										{foreach from=$level1.children item=level2}
											<li class='dd-item' data-id={$level2.id}>
												<div class='dd-handle'>{$level2.name}</div>
												{if $level2.children}
												<ol class='dd-list'>
												{foreach from=$level2.children item=level3}
													<li class='dd-item' data-id={$level3.id}><div class='dd-handle'>{$level3.name}</li>
												{/foreach}
												</ol>
												{/if}
											</li>
										{/foreach}
										</ol>
										{/if}
									</li>
								{foreachelse}
									<li class='dd-empty'></li>	
								{/foreach}
								</ol>
							</div>
						</div>
						<div class="col-md-4 pt-sm-2"> 
							<div class="card">
								<div class="card-body">
									<div class="row mb-3 position-sticky top-0 bg-white py-1">
										<div class="col col-form-label"><b>{"Menu Items"|trans}</b></div>
										<div class="col">
						          <div class="input-group rounded">
						            <input id="sg-menu-search" class="form-control border-end-0" type="text" placeholder="{'Lookup'|trans}...">
						            <button id="sg-menu-search-clear" class="input-group-text bg-transparent" type="button"><i class="bi bi-x-lg"></i></button>
						          </div>
										</div>	
									</div>	
									<div id='nestable2' class='dd'>
										<ol class='dd-list'>
											{foreach $html.menu_items AS $name => $items}				
											<li class='dd-item'>
												<div class='dd-handle'><div class='dd-nodrag'>{$name}</div></div>
												<ol class='dd-list'>
													{if $items.Collection}
													<li class='dd-item'>
														<div class='dd-handle'><div class='dd-nodrag'>{"Collection"|trans}</div></div>
														<ol class='dd-list'>
															{foreach $items.Collection AS $item}			
															<li class='dd-item' data-id={$item.id}><div class='dd-handle'>{$item.name}</div></li>
															{/foreach}
														</ol>
													</li>	
													{/if}
													{foreach $items AS $k => $item}	
														{if 'Collection' !== $k}		
														<li class='dd-item' data-id={$item.id}><div class='dd-handle'>{$item.name}</div></li>
														{/if}
													{/foreach}
												</ol>
											</li>
											{foreachelse}
											<li class='dd-empty'></li>	
											{/foreach}
										</ol>
										<div class="col-12 mt-3 text-center" id="sg-link-message"></div>	
									</div>									
								</div>
							</div>
							<div class="row mt-3">
								<div class="col">
				          <input id="sg-link-name" class="form-control" type="text" placeholder="{'Name'|trans}" data-bs-toggle="collapse" data-bs-target="#sg-link-collapse">
								</div>	
								<div class="col-auto"><button id="sg-link-create" class="btn btn-success text-white" type="button">{"Create Link"|trans}</button></div>
								<div class="col-12 mt-3 collapse" id="sg-link-collapse">
				          <input id="sg-link-slug" class="form-control" type="text" placeholder="{'Address'|trans}">
								</div>
							</div>								
						</div>	
					</div>
				</div>		
			</div>	
		</div>
		<div class="card-footer">
		  <div class="row">
			<div class="col-sm-auto my-2">
				<div class="input-group">
					<button type="button" class="input-group-text btn btn-secondary" data-bs-toggle="modal" data-bs-target="#locModal">{"Location"|trans} <i class="bi bi-arrow-up-right-square-fill text-lime"></i></button>  
					<select class="form-select" id='location' name='menu[location]'>
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
					<button type="button" class="input-group-text btn btn-secondary"><a class="text-decoration-none text-white" href="https://{$site.url}/?nam=sgblock" target="_blank">{"Section"|trans} <i class="bi bi-arrow-up-right-square-fill text-lime"></i></a></button>
					<select class="form-select" id='section' name='menu[section]'>
						<option></option>
						{foreach from=$html.sections item=section}
						<option value='{$section}'>{$section|replace:'_':' '|capitalize}</option>								
						{/foreach}}
					</select>
				</div>	
			</div>		
			<div id='page-id-wrapper' class="col-sm-auto my-2 d-none">
				<div class="input-group dropup">
					<button class="input-group-text btn btn-secondary" type="button"><i class="bi bi-search"></i></button>
					<input class="form-control lookup-field dropdown-toggle rounded-end" id='page-id' type='text' placeholder="{'Name'|trans}" data-url="{$links.lookup}" data-lookup="page" data-name='menu[page_id]' data-multiple=0 data-bs-auto-close="outside" data-bs-toggle="dropdown" aria-expanded="false">
					<div class="dropdown-menu px-2"></div>
				</div>	
			</div>		
			<div class="col text-center text-sm-end">
				<button id="submit-button" class="btn btn-lg btn-primary my-1" type="submit" name='save_menu'><i class="bi bi-save pe-2"></i> {"Save"|trans}</button>
			</div>
		  </div>		
		</div> 		
	</div>	 
</form>
</div>

<div id="locModal" class="modal fade backdrop-blur">
	<div class="modal-dialog modal-sm">
	    <div class="modal-content">
	       <div class="modal-header">
	          <h5 class="modal-title">{":Item Location"|trans:['item' => 'Menu'] }</h5>
	          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	       </div>
	       <div class="modal-body">
				{foreach from=$menu.locations item=loc}
					<div id='loc{$loc.id}'>
						{$loc.location|capitalize|trans}{if $loc.name}: {$loc.name}{elseif $loc.page_id}#{$loc.page_id}{/if} @ {$loc.section|capitalize|trans}
						<a href="#" class="float-end" data-url='{$links.delete_location}' data-name="id" data-value="{$loc.id}" data-confirm="delete" data-remove="#loc{$loc.id}"><i class="bi bi-x-circle text-danger"></i></a><br />
					</div>	 
				{foreachelse}
					{"No records found"|trans}!		
				{/foreach}       	
	       </div>
	    </div>
	 </div>      
</div>

<script>
  document.addEventListener("DOMContentLoaded", function(e){
		var updateOutput = function(e) {
			var list = e.length ? e : $(e.target),
			output = list.data('output');
			if (window.JSON) 
			{
				output.val(window.JSON.stringify(list.nestable('serialize')));//, null, 2));
			} else {
				output.val('JSON browser support required for this demo.');
			}
		};
		// activate Nestable for list 1
		$('#nestable').nestable({
			maxDepth: 3,
		}).on('change', updateOutput);
		$('#nestable2').nestable({
			maxDepth: 3,
		});
		$('#nestable2.dd').nestable('collapseAll');
		// output initial serialised data, after nestable is activated.
		updateOutput($('#nestable').data('output', $('#nestable-output'))); 		
		
		$('#location').change(function() {
			if ($(this).val() && $(this).val() != 'Site') {
				$('#page-id-wrapper').removeClass('d-none')
					.find('.lookup-field')
						.attr('data-lookup', $(this).val().toLowerCase())
						.attr('placeholder', $(this).val() + ' Name');
				//remove previously selected value
				$('#page-id-wrapper .dropdown-menu').text('')	
				$('#page-id-wrapper span.input-group-text').remove()		
			} else {
				$('#page-id-wrapper').addClass('d-none');
			} 
		});

    $("#sg-menu-search").on("keyup", function() {
      var value = $(this).val().toLowerCase();
			if (value.length) {
				$('#nestable2.dd').nestable('expandAll');
			} else {
				$('#nestable2.dd').nestable('collapseAll');
			}	
      $("#nestable2 [data-id]").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
      });
    });  
    $("#sg-menu-search-clear").on("click", function() {
      $("#sg-menu-search").val('');
      $("#sg-menu-search").keyup();
    });

    //create link
    $("#sg-link-create").on("click", function() {
    	var request = { page : {} };
      request.page.name = $("#sg-link-name").val();
      if (request.page.name.length) {
      	$("#sg-link-message").html('<div class="spinner-grow spinner-grow-sm text-primary" role="status"><span class="visually-hidden">{"Loading"|trans}...</span></div>');
	      request.page.type = 'Link' 
	      request.page.slug = $("#sg-link-slug").val() || '#';
	      request.page.published = 1;
	      {if $menu.id}request.page.menu_id = {$menu.id};{/if} {*/*}
      	request.csrf_token = window.csrf_token;
      	request.format = 'json';
        $.post("{$links.page_api}", request, function(data) { // already a json object jQuery.parseJSON(data);
        	if (data.status.result == 'success') {
        		if (data.page.published > 0) {
        			$("#nestable2 > .dd-list").append("<li class='dd-item' data-id="+ data.page.id +"><div class='dd-handle'>"+ request.page.name +"</div></li>");
        			$("#sg-link-message").text('');
        		} else {
        			$("#sg-link-message").text('{"Link created but is not published"|trans}');
        		}
        	} else {
        		$("#sg-link-message").text('{":item was not created"|trans:["item" => "Link"]}');
        	}	
        });	
      } else {
      	$("#sg-link-name").focus();
      }      
    });    		
	});
</script>
{include "lookup.tpl"}