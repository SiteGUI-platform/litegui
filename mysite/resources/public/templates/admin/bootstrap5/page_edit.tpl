{$page = $api.page}
{$app  = $api.app}
{$prefix = 'page'}
{$suffix = $html.current_app} {*<!-- used for dom id only -->*}	

<div class="col-12 {if $system.sgframe}px-0{else}col-md-10 pt-sm-2{/if} mx-auto">
<form class="has-date" action="{$links.update}" method="post" {if !$html.file_manager}enctype="multipart/form-data"{/if}>
	<div class="card">
		<div class="card-body row g-0 align-items-center">
			<div class="col-auto pe-0 sg-hover-back sg-ob-list">
				{if $links.main}<a href="{$links.main}">{/if}
				<i class="bi bi-bookmark px-md-3 fs-4"></i>
				{if $links.main}</a>{/if}
			</div>	
			<div class="col ps-md-0 px-2">
				{if $page.id > 0}<input type="hidden" name="{$prefix}[id]" value="{$page.id}">{/if}
				{if $page.subtype}<input type="hidden" name="{$prefix}[subtype]" value="{$page.subtype}">{/if}
				{if ! $app.hide.name} {$app.hide.name = shown} {*<!-- disable for first language -->*}
					<input class="input-name form-control-lg text-success" type="text" name="{$prefix}[name][{$site.language}]" placeholder="{'Label'|trans}" value="{$page.name[$site.language]}" required {if ! $page.name[$site.language]}autofocus{/if}>	
				{else}
				  <label class="col-form-label pt-2">
						<span class="form-control-lg text-success ps-0"><span class="text-dark me-2">{$html.app_label|default: $page.type}</span> {if $page.name[$site.language]} {$page.name[$site.language]}{elseif $page.id}#{$page.id}{/if}</span> 
					</label>	
				{/if}
				{block name=APP_header}{/block}
			</div>	
			{if ! ($app.hide.tabcontent OR $app.hide.locales) }
				{if ($site.locales|count) gt 1 AND !$app.hide.locales }
					<div class="col-auto py-1 d-none d-sm-block">
						<div id="sub-tab" class="nav nav-pills float-end d-flex">
							{foreach from=$site.locales key=lang item=language}
							<button type="button" data-bs-target="#tab-{$lang}" class="nav-link tab-languages" role="tab" aria-controls="tab-{$lang}" data-bs-toggle="tab">{$lang|capitalize}</button>
							{/foreach}
						</div>
					</div>	
				{/if}	
			{/if}
			{if ! $app.hide.save}	
			<div class="col-auto ps-2 d-none d-sm-block">
				<button type="submit" name="save-btn" class="btn btn-outline-secondary border rounded-circle sg-ob-save" title="{'Save'|trans}" style="height: 40px"><i class="bi bi-save"></i></button>
			</div>
			{/if}
		</div>
		<div role="tabpanel">
			<!-- Nav tabs -->
			<ul id="main-tab" class="nav nav-tabs" role="tablist">
			{if $api.app AND ! $app.hide.tabapp}
				{block name=APP_tabname hide}{$deactiveTabContent = 1}{$smarty.block.child}{/block}
			{/if}
	        
	        {if ! $app.hide.tabcontent}
	          {if ($site.locales|count) gt 1 AND !$app.hide.locales }
				<li class="nav-item dropdown"><button type="button" id="tab-languages" class="nav-link dropdown-toggle {if ! $deactiveTabContent}active{/if}" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="true" aria-haspopup="true" aria-expanded="false">{if $page.subtype AND $app.hide.tabapp}{$html.app_label}{else}{"Content"|trans}{/if}</button>
					<div class="dropdown-menu border-top-0"><!--not use ul/li to make use of javascript siblings-->
						{foreach $site.locales as $lang => $language}
						<button type="button" class="dropdown-item tab-languages {if $language@first && ! $deactiveTabContent}active{/if}" data-bs-target="#tab-{$lang}" role="tab" aria-controls="tab-{$lang}" data-bs-toggle="tab">{$language|capitalize}</button>
						{/foreach}
					</div>
				</li>
			  {else}
				<li class="nav-item">
					<button type="button" class="nav-link {if ! $deactiveTabContent}active{/if}" data-bs-toggle="tab" data-bs-target="#tab-{$site.language}" role="tab" aria-controls="tab-{$site.language}" aria-selected="true">{if $page.subtype AND $app.hide.tabapp}{$html.app_label}{else}{"Content"|trans}{/if}</button>
				</li>		    
			  {/if}
			{/if}   

			{foreach $api.subapp AS $name => $subapp}
				{if ! $subapp.hide.tabapp}
					{block name=SUBAPP_tabname hide}{$smarty.block.child}{/block}
				{/if}
			{/foreach}

        	{if ! $app.hide.tabsettings}
				<li class="nav-item">
					<button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-settings" role="tab" aria-controls="tab-settings" aria-selected="false">{"Settings"|trans}</button>
				</li>
			{/if}	
			{if $api.versioning}
				<li class="nav-item">
					<button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-versions" role="tab" aria-controls="tab-versions" aria-selected="false">{"Versions"|trans}</button>
				</li>					
			{/if}
				{block name=APP_tabname_end hide}{$smarty.block.child}{/block}
			</ul>

			<!-- Tab panes -->
			<div id="main-tab-content" class="sg-main tab-content">
			{if $api.app AND ! $app.hide.tabapp }
        		{block name=APP_fields hide}{/block}
	        {/if}	

        	{if ! $app.hide.tabcontent OR $app.sub}
        		{if $app.hide.tabapp OR $app.hide.content}
        			{$colwidth = 7}
        		{else}
        			{$colwidth = 9} {*<!-- subapp will change this in app_edit-->*}       		
        		{/if}	
				{foreach $site.locales as $lang => $language}
					{if $language@first OR !$app.hide.locales }
					<div id="tab-{$lang}" class="sg-form tab-pane fade {if $language@first && ! $deactiveTabContent}show active{/if}" role="tabpanel">
					{block name=APP_content}
						{if ! $app.hide.name}
							<div class="form-group row mb-3">
								<label class="col-sm-3 col-form-label text-sm-end">{'Label'|trans}</label>
								<div class="col-sm-{$colwidth}">
									<input class="form-control" type="text" name="{$prefix}[name][{$lang}]" value="{$page.name[$lang]}">		
								</div>
							</div>
						{elseif $app.hide.name == shown}
							{$app.hide.name = 0} {*<!-- enable for other languages, only reset if WAS SET previously-->*}	
						{/if}
						{if ! $app.hide.slug AND $language@first}
							<div class="form-group row mb-3">
								<label class="col-sm-3 col-form-label text-sm-end">{'Slug'|trans}</label>
								<div class="col-sm-{$colwidth}">
									<div class="input-group">
										<input class="form-control" type="text" name="{$prefix}[slug]" value="{$page.slug}" {if $page.slug ==404}readonly{/if}>
										{if $page.id}<a class="btn text-primary input-group-text" target="_blank" href="{$links.uri}"><i class="bi bi-box-arrow-up-right"></i></a>{/if}
									</div>
									{if $page.type eq Link}
									<small class="form-text text-secondary">{"This is a link, no content will be served"|trans}</small> 
									{/if} 		
								</div>
							</div>
						{/if}
						{if $page.type ne Link}
							{if ! $app.hide.title}
								<div class="form-group row mb-3">
									<label class="col-sm-3 col-form-label text-sm-end">{"Title"|trans}</label>
									<div class="col-sm-{$colwidth}">
										<input class="form-control" type="text" name="{$prefix}[title][{$lang}]" value="{$page.title[{$lang}]}">
									</div>
								</div>
							{/if}
							{if ! $app.hide.description}	
								<div class="form-group row mb-3">
									<label class="col-sm-3 col-form-label text-sm-end">{"Description"|trans}</label>
									<div class="col-sm-{$colwidth}">
										<div class="form-control js-description" contenteditable="true">{$page.description[{$lang}]}</div>
										<input class="js-description-input" type="hidden" name="{$prefix}[description][{$lang}]" value="{$page.description[{$lang}]}">
									</div>
								</div>
							{/if}
							{if ! $app.hide.image AND $language@first}
								<div class="form-group row mb-3">
									<label class="col-sm-3 col-form-label text-sm-end">{"Featured Image"|trans}</label>
									<div class="col-sm-{$colwidth}">
										<div class="carousel-multi pb-1 w-100" data-length="3" data-slide-to="0" data-noloop="1" data-dynamic="1">
								          	<div id="featured-img-{$suffix}" class="carousel-inner item-removable row gx-0">
								            {if $page.image}<div class="col position-relative">
								              	<img src="{if $page.image|truncate:4:'' ne 'http'}{$links.file_view}/{/if}{$page.image}" class="img-thumbnail p-0 d-block mx-auto"><input type="hidden" name="{$prefix}[image]" value="{$page.image}">
								            </div>{/if}  
								          </div>    
								          <button class="carousel-control-prev justify-content-start ms-3 d-none" type="button" data-bs-slide="prev">
								            <i class="bi bi-chevron-left control-icon"></i>
								            <span class="visually-hidden">{"Previous"|trans}</span>
								          </button>
								          <button class="carousel-control-next justify-content-end me-3 {if !$page.image}d-none{/if}" type="button" data-bs-slide="next">
								            <i class="bi bi-chevron-right control-icon"></i>
								            <span class="visually-hidden">{"Next"|trans}</span>
								          </button>
								        </div>

								        <div class="input-group pb-2 {if $page.image}d-none{/if}">
								          <input id="fid-featured-img" class="form-control get-image-callback" data-container="#featured-img-{$suffix}" data-name="{$prefix}[image]" 
								          {if $html.file_manager}
								            type="text" name2="{$prefix}[image]" data-folder="{$html.upload_dir}" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="{'File Manager'|trans}"
								          {else}
								            type="file" name2="{$prefix}[image]" accept="image/*"
								          {/if}>
								          {if $html.file_manager}
								            <label class="input-group-text bg-body" for="fid-featured-img" role="button"><i class="bi bi-upload"></i></label>
								          {/if}
								        </div>   
									</div>									
								</div>
							{/if}
		
							{*<!-- extra content: use $language@first in child blocks to show one or multiple languages -->*}
							{block name=APP_tabcontent}{/block}	
							{block name=SUBAPP_tabcontent}{/block}	

							{*<!-- Show app fields in content tab if tabapp disabled - 1st language only. 
							   Use $app.sub as only main app has this var, also take care when app has no subapp -->*}
							{if $language@first AND $app.hide.tabapp AND (!$api.subapp OR $app.sub) }
					        	{block name=APP_fields hide}{/block}
					        {/if}
					        {*<!-- Subapp fields: must alway present here for staying (tabapp disabled) or capturing (tabapp enabled)-->*}	
					        {if $api.subapp AND !$app.sub }	
					        	{block name=SUBAPP_fields hide}{/block}
					        {/if}

					        {*<!-- App content is readonly when updating with subapp and subapp shows content 
					           Hide subapp content whenever app content is editable -->*}
							{*<!-- if ! $app.hide.content && ( (!$page.id && !$hide_subapp_content) || !$api.subapp || $api.subapp.hide.content) }
								{$hide_subapp_content = 1-->*}		
							{if ! $app.hide.content AND $page.type ne Link}
								{if $language@first}{include "summernote.tpl"}{/if}
								
								<div class="sg-text-editor position-relative">
									{if $links.editor && ! $app.hide.wysiwyg}
									<div id="{$suffix}_wysiwyg_{$lang}" class="text-center {if !$page.content.wysiwyg}position-absolute" style="top: 4px; right: 4px; z-index: 1;{/if}">
										<button type="button" class="btn {if $page.content.wysiwyg}{/if} btn-secondary" data-bs-toggle="modal" data-bs-target="#fullscreen-modal"><i class="bi bi-layout-wtf"></i> <span class="{if !$page.content.wysiwyg}d-none d-md-inline{/if}">{"Visual Mode"|trans}</span></button>
									</div>	
									{/if}
									{if ! $page.content.wysiwyg}
										{$prefix_id = $prefix|replace:['[',']']:['_','']}
										<textarea {if $page.id AND $app.sub AND !$api.subapp.hide.content}readonly{/if} name="{$prefix}[content][{$lang}]" id="{$prefix_id}_content_{$lang}" class="page-content form-control" rows="10" style="width:100%" placeholder="{'Content'|trans}">{$page.content[{$lang}]}</textarea><br/>
										{if $site.editor == 'wysiwyg' || $site.editor == 'CKEditor'}
											<script>
												document.addEventListener("DOMContentLoaded", function(e){
													//createEditor("{$suffix}_content_{$lang}");
													$('#{$prefix_id}_content_{$lang}').summernote(NOTECONFIG)
														.next().find('.note-toolbar').append($('#{$prefix_id}_wysiwyg_{$lang}'));	

													{if $page.id AND $app.sub AND !$api.subapp.hide.content}
														$('#{$prefix_id}_content_{$lang}').summernote('disable')
														$('#{$prefix_id}_content_{$lang}').next()
															.on('click', function() {
																$(this).prev().summernote('enable')
																$(this).find('.note-toolbar').removeClass('d-none')
																$(this).off('click').removeClass('pt-4 bg-light')
															})
															{if !$app.hide.wysiwyg}
																.addClass('pt-4 bg-light')
															{/if} {*when visual mode shown, remove classes when dblclick any cases/*}
															.mousedown(function(ev){ //prevent text selection
																if (ev.detail == 2){ //dblclick
																	ev.preventDefault()
																	$(this).off('mousedown')
																}	
															})
															.find('.note-toolbar').addClass('d-none')
													{/if}
												});	
											</script>
										{/if}
									{/if}	
								</div> 
							{/if}
						{/if}	 
					  {/block}
					</div>
					{/if}
				{/foreach}
			{/if}	
        	{if ! $app.hide.tabsettings}
				<div id="tab-settings" class="tab-pane fade" role="tabpanel">
					{block name=APP_tabsettings}{/block}	
					{block name=SUBAPP_tabsettings}{/block}	
					{block name=APP_settings}
						{if ! $app.hide.publishing}	{*<!-- hide publishing tool -->*}
							{if $page.type ne Link}
								{if ! $app.hide.layout}
									<div class="form-group row mb-3">
										<label class="col-sm-3 col-form-label text-sm-end">{"Layout"|trans}</label>
										<div class="col-sm-7">
											<select class="form-select" name="{$prefix}[layout]">
												<option value=""></option>
												{foreach $html.layouts.names as $layout}
												<option value="{$layout}" {if $layout eq $page.layout}selected{/if}>{$layout|replace: '-':' '|capitalize:true}</option>
												{/foreach}  
											</select> 
											<small class="form-text text-secondary">{'Leave Blank for default Page layout'|trans}</small>
										</div>
									</div>
								{/if}
								{if ! $app.hide.menu_id}
									<div class="form-group row mb-3">
										<label class="col-sm-3 col-form-label text-sm-end">{"Add to Menu"|trans}</label>
										<div class="col-sm-7">
											<select class="form-select" name="{$prefix}[menu_id]">
												<option value=""></option>
												{foreach $html.menus as $m}
												<option value="{$m.id}" {if $m.id eq $page.menu_id}selected{/if}>{$m.name|capitalize:true}{if $m.location} ({$m.location}){/if}</option>
												{/foreach}  
											</select>
										</div>
									</div>
								{/if}
								{if ! $app.hide.collection}
									<div class="form-group row mb-3">
										<label class="col-sm-3 col-form-label text-sm-end">{"Collection/Category"|trans}</label>
										<div class="col-sm-7">
											<div class="input-group dropup">
												<div class="input-group-text bg-transparent"><i class="bi bi-search"></i></div>	
												<input class="form-control lookup-field dropdown-toggle rounded-end" type='text' placeholder="{'Name'|trans}" data-lookup="collection" data-scope="{if $page.type eq App OR (!$page.type AND $page.subtype)}App::{/if}{if $html.current_app eq Collection}{$page.subtype|default:Page}{elseif $html.current_app eq Appstore}Product{else}{$html.current_app}{/if}" data-index="0" data-name="{$prefix}[collection][ ]" data-create="1" data-multiple="1" data-bs-auto-close="outside" data-bs-toggle="dropdown" aria-expanded="false">
          										<div class="dropdown-menu dropdown-menu-scroll px-2"></div>
											{if $api.collections AND $api.collections|count > 1}
											</div>  
											{/if} 	
											{foreach from=$api.collections item=collection}
												<label id="collection{$collection.id}" class="
												{if $api.collections AND $api.collections|count > 1}border me-1 mt-2 p-1 ps-3
												{else}
													input-group-text
												{/if}
												{if $collection.published}text-success{/if} bg-transparent rounded">{if $collection.parent}{$collection.parent} ⇢ {/if}{$collection.name}<a href="#" data-confirm="delete" data-remove="#collection{$collection.id}" data-url="{$links.leave_collection}" data-name="id" data-value="{$collection.id}"><i class="bi bi-x ps-1 pt-1"></i></a></label>
											{/foreach}
											{if !$api.collections OR $api.collections|count == 1}
											</div>
											{/if}
											{include "lookup.tpl"}
										</div>
									</div>
								{/if}
								{if ! $app.hide.breadcrumb}
									<div class="form-group row mb-3">
										<label class="col-sm-3 col-form-label text-sm-end">{"Breadcrumbs"|trans}</label>
										<div class="checkbox col-sm-7">
											<div class="form-check form-switch col-form-label">
												<input type="hidden" name="{$prefix}[breadcrumb]" value="0">
												<input type="checkbox" class="form-check-input" id="bc-mode-{$suffix}" name="{$prefix}[breadcrumb]" value="1" {if $page.breadcrumb == 1}checked{/if}>
												<label class="form-check-label" for="bc-mode-{$suffix}">{'Show navigation path on this page'|trans}</label>
											</div>  
										</div>         
									</div>    
								{/if}
							{/if}	
							{if ! $app.hide.private}
								<div class="form-group row mb-3">
									<label class="col-sm-3 col-form-label text-sm-end">{"Private"|trans}</label>
									<div class="checkbox col-sm-7">
										<div class="form-check form-switch col-form-label">
											<input type="hidden" name="{$prefix}[private]" value="0">
											<input type="checkbox" class="form-check-input" id="private-mode-{$suffix}" name="{$prefix}[private]" value="1" {if $page.private == 1}checked{/if}>
											<label class="form-check-label" for="private-mode-{$suffix}">{'Make page private, only authenticated users can view'|trans}</label>
										</div>  
									</div>
								</div>      
							{/if}
							{if ! $app.hide.published}
								<div class="form-group row mb-3">
									<label class="col-sm-3 col-form-label text-sm-end">{"Published"|trans}</label>
									<div class="checkbox col-sm-7">
										<div class="form-check form-switch col-form-label">
											<input type="hidden" name="{$prefix}[published]" value="0">
											<input type="checkbox" class="form-check-input" id="publish-mode-{$suffix}" name="{$prefix}[published]" value="1" {if $page.published > 0}checked{/if}>
											{if ! $app.hide.published_at}
											<label class="form-check-label" for="publish-mode-{$suffix}">{'Publish page now, optionally set published time below'|trans}</label>
											{/if}
										</div>  
										<div class="row">
										{if ! $app.hide.published_at}
											<div class="col-sm pt-2">					    	
												<div class="input-group">
													<span class="input-group-text bg-transparent">{"From"|trans}</span>
													<input name="{$prefix}[published_at]" id="startdate-{$suffix}" type="text" class="form-control datetimepicker-input" data-target="#startdate-{$suffix}" data-toggle="datetimepicker" value="" placeholder="{'Start Date'|trans}"/> 
													<span class="bi bi-calendar2-event input-group-text bg-transparent" data-target="#startdate-{$suffix}" data-toggle="datetimepicker"></span>
												</div>  
											</div>    
										{/if}
										{if ! $app.hide.expire}
											<div class="col-sm pt-2">					    	
												<div class="input-group">
													<span class="input-group-text bg-transparent">{"To"|trans}</span>
													<input name="{$prefix}[expire]" id="enddate-{$suffix}" type="text" class="form-control datetimepicker-input" data-target="#enddate-{$suffix}" data-toggle="datetimepicker" value="" placeholder="{'End Date'|trans}"/> 
													<span class="bi bi-calendar2-week input-group-text bg-transparent" data-target="#enddate-{$suffix}" data-toggle="datetimepicker"></span>
												</div>  
											</div> 
										{/if}
										</div> 
										{if !$app.hide.published_at || !$app.hide.expire }
											{if ! $datetime_script_loaded}
          										{$datetime_script_loaded = 1 scope="global"}
												<script defer type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment-with-locales.min.js"></script>
												<script defer type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.31/moment-timezone-with-data.js"></script>
												<script defer type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/js/tempusdominus-bootstrap-4.min.js"></script>
												<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/css/tempusdominus-bootstrap-4.min.css" />    
											{/if}	
											<script type="text/javascript">
												document.addEventListener("DOMContentLoaded", function(e){
													$('#startdate-{$suffix}').datetimepicker({
												        //format: 'L',
												        locale: "{$site.locale|default:$user.language|default:$site.language}",
												        timeZone: '{$user.timezone|default:$site.timezone}',    
														icons: {
															time: 'bi bi-clock',
															date: 'bi bi-calendar-day',
															previous: 'bi bi-chevron-left',
															next: 'bi bi-chevron-right',
															up:   'bi bi-chevron-up',
                											down: 'bi bi-chevron-down',
														},    
														{if $page.published > 86400}
															defaultDate: new Date({$page.published} * 1000).toLocaleString('en-CA', {
																timeZone: '{$user.timezone|default:$site.timezone}', hour12: false,
															}).replace(', ', 'T'),
															//use en-CA to format YYYY-MM-DDThh:mm with site's timezone
															//to keep any chosen date as intended date for site
		    					 								//year: 'numeric', 
		    					 								//month: '2-digit', 
		    					 								//day: '2-digit', 
		    					 								//hour: '2-digit', 
		    					 								//minute: '2-digit', 
		    					 								//hour12: false
		    												//}).replace(' ', 'T'),
															{if $page.expire > $page.published}
															maxDate: new Date({$page.expire} * 1000).toLocaleString('en-CA', {
																timeZone: '{$user.timezone|default:$site.timezone}', hour12: false,
															}).replace(', ', 'T'),
															{/if}
														{/if}
													});
												{if ! $app.hide.expire} 
													$('#enddate-{$suffix}').datetimepicker({
												        //format: 'L',
												        locale: "{$site.locale|default:$user.language|default:$site.language}",
												        timeZone: '{$user.timezone|default:$site.timezone}',
														icons: {
															time: 'bi bi-clock',
															date: 'bi bi-calendar-day',
															previous: 'bi bi-chevron-left',
															next: 'bi bi-chevron-right',
															up:   'bi bi-chevron-up',
                											down: 'bi bi-chevron-down',
														},    
														useCurrent: false,
														{if $page.expire > 0}
															defaultDate: new Date({$page.expire} * 1000).toLocaleString('en-CA', {
																timeZone: '{$user.timezone|default:$site.timezone}', hour12: false,
															}).replace(', ', 'T'),
															{if $page.expire > $page.published}
																minDate: new Date({$page.published} * 1000).toLocaleString('en-CA', {
																timeZone: '{$user.timezone|default:$site.timezone}', hour12: false,
															}).replace(', ', 'T'),
															{/if}
														{/if}
													});
													$('#enddate-{$suffix}').on("change.datetimepicker", function (e) {
														$('#startdate-{$suffix}').datetimepicker('maxDate', e.date);
													})
												{/if}	
													$('#startdate-{$suffix}').on("change.datetimepicker", function (e) {
														$('#enddate-{$suffix}').datetimepicker('minDate', e.date);
														document.getElementById("publish-mode-{$suffix}").checked = true;
													});
												});    
											</script>	
										{/if}				    	
									</div>
								</div>    
							{/if}
						{/if}
					{/block}	
				</div>			
			{/if}	

			{foreach $api.subapp AS $name => $subapp}
				{if $subapp@first}{$first_subapp = $name}{/if}
			    {if ! $subapp.hide.tabapp }
			        <div id="app-{$name}-tabpane" class="sg-form sg-sub tab-pane fade" role="tabpanel"> 
		        		{block name=SUBAPP_tabbody hide}{/block}
		        	</div>	
	        	{/if}
	        {/foreach}	
		        	
			{if $api.versioning}
				<div id="tab-versions" class="tab-pane fade" role="tabpanel">
					<div class="row">						
						{if $api.versioning.master}
							<div class="col-12 {if $api.versioning.master.updated <= $api.page.updated}order-first{else}order-last{/if}">
								<div class="form-group row mb-3">
									<label class="col-3"><strong>{"Original Version"|trans}</strong> {if $api.versioning.master.updated > $api.page.updated}(<span class="text-danger">{"Newer"|trans}!</span>){/if}</label>
									<div class="col">
										<a href="{$links.edit}/{if $api.versioning.master.type eq App}{$api.versioning.master.subtype|lower}/{/if}{$api.versioning.master.id}" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-url="{$links.edit}/{if $api.versioning.master.type eq App}{$api.versioning.master.subtype|lower}/{/if}{$api.versioning.master.id}?sgframe=1" data-title="{$api.versioning.master.name}">{$api.versioning.master.name}</a>
										<span class="float-end">	
										 {$api.versioning.master.creator_name|default:$api.versioning.master.creator} - {"updated "} @ <span class="js-sg-time">{$api.versioning.master.updated}</span>
										</span> 
									</div>
								</div>
							</div>	
							{if $html.publisher}
							<div class="col-12 {if $api.versioning.master.updated <= $api.page.updated}order-first{else}order-last{/if}">
								<div class="form-group row mb-3">
									<label class="col-3 col-form-label"><i class="bi bi-arrow-return-right"></i> {"Action"|trans}</label>
									<div class="col">
										<select class="form-select" name="{$prefix}[updated]">
											<option value="">{"None"|trans}</option>
											<option value="replace_original_version_with_this">{"Replace The Original Version With This Version"|trans}</option>
										</select>
									</div>
								</div>
							</div>	
							{/if}
						{/if} 
						<div class="col-12 order-2">
							<div class="form-group row mb-3">
								<label class="col-3 {if $api.versioning.master}"><i class="bi {if $api.versioning.master.updated <= $api.page.updated}bi-arrow-return-right{else}bi-arrow-90deg-right{/if}"></i> {else}ps-4">{/if}<strong>{"This Version"|trans}</strong></label>
								<div class="col">
									{$api.page.name[$site.language]}
									<span class="float-end">@ <span class="js-sg-time">{$api.page.updated}</span></span>
								</div>
							</div>
						</div>	
						{foreach $api.versioning.versions AS $version}
						<div class="col-12 {if $version.updated < $api.page.updated}order-1{else}order-2{/if}">
							<div class="form-group row mb-3 ms-0 {if $api.versioning.master AND ($version.updated < $api.page.updated OR $api.versioning.master.updated > $api.page.updated)}border-start border-secondary{/if}">
								<label class="col-3"><i class="bi {if $version.updated < $api.page.updated}bi-arrow-90deg-right{else}bi-arrow-return-right{/if} ps-3"></i> {"Version"|trans} #{$api.page.id}-{$version@index + 1}</label>
								<div class="col ps-0">
									<a href="{$links.edit}/{if $version.type eq App}{$version.subtype|lower}/{/if}{$version.id}" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-url="{$links.edit}/{if $version.type eq App}{$version.subtype|lower}/{/if}{$version.id}?sgframe=1" data-title="{$version.name}">{$version.name}</a>
									<span class="float-end">{$version.creator_name|default:$version.creator} -  {"updated "} @ <span class="js-sg-time">{$version.updated}</span></span>
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
			{if ! $app.hide.save AND $html.challenge_captcha}
				<div class="position-absolute mt-1">
		        	<script async defer src="https://cdn.jsdelivr.net/npm/altcha/dist/altcha.min.js" type="module"></script>
					<altcha-widget challengejson="{$html.challenge_captcha}" NOfloating hidelogo hidefooter></altcha-widget>
				</div>
	        {/if}
			{if ! $app.hide.save}<span><button type="submit" name="save-btn" id="sg-btn-save" class="btn btn-lg btn-primary m-1"><span class="sg-btn-text"><i class="bi bi-save pe-2"></i>  {"Save"|trans}</span><div class="spinner-border spinner-border mx-4 d-none" role="status"><span class="visually-hidden">{"Saving"|trans}...</span></div></button></span>{/if}
			{if ! $app.hide.published}
				{if $app.hide.tabsettings OR $app.hide.publishing}
				<input type="checkbox" class="form-check-input d-none" id="publish-mode-{$suffix}" name="{$prefix}[published]" value="1" {if $page.published > 0}checked{/if}>
				{/if}
				<button type="submit" name="publish-btn" id="sg-btn-publish" class="btn btn-lg {if $html.publisher}btn-outline-success{else}btn-outline-secondary{/if} m-1">
				{if $html.publisher OR !$api.versioning.master}
					{"Publish"|trans}  <i class="bi bi-globe2 ps-2"></i>
				{else}
					{"Request Merge"|trans}  <i class="bi bi-sign-merge-left ps-2"></i>
				{/if}	
				</button>
			{/if}
			{if $page.id}
				{if $app.tick.params}
					<input type="checkbox" class="btn-check" id="app-btn-tick" name="{$prefix}[tick]" value="{$app.tick.params}" autocomplete="off" onclick="$('#sg-btn-save').click()">
					<label class="btn btn-lg btn-warning m-1" for="app-btn-tick">✔️ {$app.tick.label}</label>
				{/if} 
				{foreach $app.buttons AS $button}
					<input type="checkbox" class="btn-check" id="app-btn-{$button@index + 1}" name="{$prefix}[fields][{$button.name}]" value="{$button.value}" autocomplete="off" onclick="$('#sg-btn-save').click()">
					<label class="btn btn-lg btn-{$button.style|default:secondary} m-1" for="app-btn-{$button@index + 1}">{$button.label}</label>
				{/foreach}
			{/if}	
		</div>
	</div>
</form>
</div>
{block name=APP_footer hide}{/block}

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(e){
	$('[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) { 
		//current tab is not a .tab-languages -> remove active class from all .tab-languages items
		if ( !e.target.classList.contains('tab-languages') ){
			$(".tab-languages").removeClass('active'); 
		}	
	  	// add active class to all switchers that has the target's name - siblings require no li between tab
		$("[data-bs-target='"+ e.target.dataset.bsTarget +"']").siblings().removeClass('active'); 
		$("[data-bs-target='"+ e.target.dataset.bsTarget +"']").addClass('active');
	  	e.target.dataset.bsTarget && window.localStorage.setItem('resumeTab', e.target.dataset.bsTarget);
	});
	$('#sub-tab [data-bs-toggle="tab"]').on('click', function (e) { //always show content tab when a remote switcher is clicked
		bootstrap.Tab.getOrCreateInstance(document.querySelector("#main-tab #tab-languages")).show();
		bootstrap.Tab.getOrCreateInstance(document.querySelector("#main-tab [data-bs-target='"+ e.target.dataset.bsTarget +"']")).show();
	});
	//always show first tab when inactive dropdown is clicked
	document.querySelector("#tab-languages") && document.querySelector("#tab-languages").addEventListener('shown.bs.dropdown', function (e) {
		e.target.classList.contains('active') || bootstrap.Tab.getOrCreateInstance(document.querySelector('#main-tab .tab-languages[data-bs-toggle="tab"]:first-child')).show()
	});
	//resume previously selected tab
	{if !$app}
	var resumeTab = window.localStorage.getItem('resumeTab');
	resumeTab = document.querySelector('#main-tab [data-bs-target="' + resumeTab + '"]')
	if (resumeTab) { 
		resumeTab = bootstrap.Tab.getOrCreateInstance(resumeTab);
		if (resumeTab._element != null) { //check valid BS element
		  resumeTab.show();
		}
	}
	{/if} {*/*}

	$('.carousel-multi').each(Sitegui.carousel);

	$('#fullscreen-modal').on('show.bs.modal', function (e) {
		var iframe = $(this).find('iframe');
		if ( ! iframe.attr('src') ) {
	    	var button = $(e.relatedTarget); // Button that triggered the modal
	    	var url = button.data('url')? button.data('url') : iframe.attr('data-src'); 
	    	iframe.attr('src', url)
	    		.on('load', function(){ //ready fired too soon, used load instead
			    	$(this).parent().find(".progress").addClass('d-none');
			  	});
	  	}  
	});
	$('#sg-btn-publish').on('click', function (ev) {
		ev.preventDefault();
		$('#publish-mode-{$suffix}').prop('checked', true);
		$('#sg-btn-save').click();
	})
	$('#sg-btn-save').on('click', function(ev){
		var empty = false;
        $('input[required]').each(function(){
            var val = $(this).val().trim();
            if(val.length == 0 || typeof val == 'undefined'){
                empty = true;
            }           
        })
        if (!empty){
			$('#sg-btn-save .sg-btn-text').addClass('d-none')
			$('#sg-btn-save .spinner-border').removeClass('d-none')	
			parent && parent.postMessage(JSON.stringify({
				iframeUnload: true
			}), "{$system.url}") 
		}	
	}).on('blur', function(){ //in case form validation failed
		$('#sg-btn-save .sg-btn-text').removeClass('d-none')
		$('#sg-btn-save .spinner-border').addClass('d-none')	
	})
	$('.js-description').on('keyup', function (ev) {
		$(this).siblings('.js-description-input').val(this.textContent);
	})   

	$('form.has-date').on('submit', function(ev) {
      ev.preventDefault()
      let that = $(this)
      $('.datetimepicker-input').each(function() {
      	if ( ! $(this).val() || $(this).is('.datetimepicker-timestamp') ){
      		return true;
      	} else {
      		$(this).addClass('datetimepicker-timestamp')
      	}
        $(this).clone().addClass('d-none').val(
          $(this).datetimepicker("viewDate").unix()
        ).appendTo(that)
      })
      this.submit()
    })

	{if ! $page.id}
		//auto-fill slug when create a new item
		$('.input-name.form-control-lg').keyup(function(e) { 
		    var txtVal = $(this).val()
		    	.normalize("NFD")
		    	.replace(/[\u0300-\u036f]/g, "")
		    	.replace(/đ/g, 'd')
		    	.replace(/\s+/g, '-')
		    	.toLowerCase();
		    $('input[name="{$prefix}[slug]"]').val(txtVal);
		    $('input[name^="{$prefix}[title]"]').val($(this).val());
		});
	{/if}	
});		 
</script>
<script defer src="{$system.cdn}/{$template}/assets/js/postmessage.js?v=29" id="sg-post-message" data-origin="{$system.url}"></script>
{if $html.onboard_page}
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(e){
  const driverObj = window.driver.js.driver({
    showProgress: true,
    //allowClose: false,
    steps: [
      { 
        popover: { 
          title: Sitegui.trans(':Item Quick Tour', {
          	"Item": "Page"
          }), 
          description: Sitegui.trans("Let's take a quick tour on how to create a new page for your site."), 
          side: "bottom", 
          align: 'center',
          onNextClick: () => {
            const selectors = [
              "[data-bs-target='#tab-settings']",
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
        element: '.input-name', 
        popover: { 
          title: Sitegui.trans('Page Label'), 
          description: Sitegui.trans('Enter a Label for your page here. The label will be used in menus and collections.'), 
          side: "bottom", 
          align: 'center',
        },
      },{ 
        element: '.tab-pane.active .form-group:first-child', 
        popover: { 
          title: Sitegui.trans('Page Slug'), 
          description: Sitegui.trans('Slug is a part of the page address to distinguish between pages. Slug can be generated automatically based on page label.'), 
          side: "bottom", 
          align: 'center',          
        }
      },{ 
        element: '.tab-pane.active .form-group:nth-child(2)', 
        popover: { 
          title: Sitegui.trans('Page Title'), 
          description: Sitegui.trans("Title is displayed in browser's tab to show what your page is about. It also helps search engines index your page better."), 
          side: "bottom", 
          align: 'center',          
        }
      },{ 
        element: '.tab-pane.active .form-group:nth-child(3)', 
        popover: { 
          title: Sitegui.trans('Page Description'), 
          description: Sitegui.trans("Description should be used to summarize your page's content in 2 or 3 sentences. It also helps search engines index your page better."), 
          side: "bottom", 
          align: 'center',          
        }
      },{ 
        element: '.tab-pane.active .form-group:nth-child(4)', 
        popover: { 
          title: Sitegui.trans('Featured Image'), 
          description: Sitegui.trans('This image alone can tell your audiences what your page is about.'), 
          side: "bottom", 
          align: 'center',          
        }
      },{ 
        element: '.sg-text-editor', 
        popover: { 
          title: Sitegui.trans('Page Content'), 
          description: Sitegui.trans('Your page content can be edited using the text editor.'), 
          side: "top", 
          align: 'center',          
        }
      },{ 
        element: '.sg-text-editor [data-bs-toggle=modal]', 
        popover: { 
          title: Sitegui.trans('Visual Editor'), 
          description: Sitegui.trans('The Visual Editor supports many pre-made HTML blocks which you can drag and drop to build your page.'),
          side: "left", 
          align: 'center',          
        }
      },{ 
        element: '[data-bs-target="#tab-settings"]', 
        popover: { 
          title: Sitegui.trans('Page Settings'), 
          description: Sitegui.trans('Click here to configure this page.'), 
          side: "left", 
          align: 'center',
          onNextClick: (el) => {
            $(el).effect("shake")
          }
        }
      },{ 
        element: '.tab-pane.active .form-group:first-child', 
        popover: { 
          title: Sitegui.trans('Page Layout'), 
          description: Sitegui.trans('Instead of the default layout, you can use a different layout for your page. You can also create your own layout through Layout app.'), 
          side: "bottom", 
          align: 'center',          
        }
      },{ 
        element: '.tab-pane.active .form-group:nth-child(2)', 
        popover: { 
          title: Sitegui.trans('Add Page To A Menu'), 
          description: Sitegui.trans('You can add your page to an existing menu as a menu item here.'), 
          side: "bottom", 
          align: 'center',          
        }
      },{ 
        element: '.tab-pane.active .form-group:nth-child(3)', 
        popover: { 
          title: Sitegui.trans('Add Page To Collections'), 
          description: Sitegui.trans('You can add your page to existing collection(s) or a new one here.'), 
          side: "bottom", 
          align: 'center',          
        }
      },{ 
        element: '.tab-pane.active .form-group:nth-child(6)', 
        popover: { 
          title: Sitegui.trans('Publish Page'), 
          description: Sitegui.trans('Publish page to make it accessible on your site. Optionally, a start date and an end date can be specified to make page available during that time only.'), 
          side: "bottom", 
          align: 'center',          
        }
      },{ 
        element: '#sg-btn-save', 
        popover: { 
          title: Sitegui.trans('Save the Page'), 
          description: Sitegui.trans('Click here to save your page.'), 
          side: "top", 
          align: 'center',
        }
      },{ 
        element: '#sg-btn-publish',
        popover: { 
          title: Sitegui.trans('Quick Publish Button'), 
          description: Sitegui.trans('Click here to save and publish your page right away.'), 
          side: "top", 
          align: 'center',
          onNextClick: () => {
            var nvp = 'done=page&csrf_token='+ window.csrf_token +'&format=json';
            $.post('{$links.onboard}', nvp, function(data) { // already a json object jQuery.parseJSON(data);
              if (data.status.result == 'success') {                
              } else {
              }   
            }) 
            driverObj.moveNext();
          }
        } 
      },{ 
        element: '.sg-ob-save', 
        popover: { 
          title: Sitegui.trans('Save the Page'), 
          description: Sitegui.trans('You can also click here to save your page.'), 
          side: "top", 
          align: 'center',
        }
      },{ 
        element: '.sg-ob-list', 
        popover: { 
          title: Sitegui.trans('Return to Page Listing'), 
          description: Sitegui.trans('You can click here to see all the pages.'), 
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
{block name=APP_script hide}{/block}
