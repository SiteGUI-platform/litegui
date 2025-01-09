<div class="col-12 mx-auto">
	<div class="card pt-4 bg-light rounded-0">
		<div role="tabpanel">
			<!-- Nav tabs -->
			<ul id="main-tab" class="nav nav-tabs" role="tablist">
			{foreach $site.locales as $lang => $language}
				<li class="nav-item">
					<button type="button" class="nav-link {if $language@first}active{/if}" data-bs-toggle="tab" data-bs-target="#tab-{$lang}" role="tab" aria-controls="tab-{$lang}" aria-selected="true">{$language|capitalize}</button>
				<li>		    
			{/foreach}   
			</ul>

			<!-- Tab panes -->
			<div id="main-tab-content" class="sg-main tab-content bg-white px-4">
			{foreach $site.locales as $lang => $language}
				<div id="tab-{$lang}" class="tab-pane fade {if $language@first}show active{/if}" role="tabpanel"> 
					<div>
						<textarea name="widget[data][{$lang}]" id="input_content_{$lang}" class="page-content form-control" rows="10" style="width:100%" placeholder="{'Text'|trans}">{$api.widget.data[{$lang}]}</textarea><br/>
						{if $site.editor == 'wysiwygTBR' || $site.editor == 'CKEditorTBR'}
							{if $language@first}{include "summernote.tpl"}{/if}
							<script>
								document.addEventListener("DOMContentLoaded", function(e){
									NOTECONFIG.toolbar.pop(); //remove last item to add codeview
  									NOTECONFIG.toolbar.push(['view', ['fullscreen', 'codeview', 'help']]);
									$('#input_content_{$lang}').summernote(NOTECONFIG)
										.next().find('.note-toolbar').append($('.note-wysiwyg-{$lang}'));	
								});	
							</script>
						{/if}	
					</div> 
				</div>
			{/foreach}			
			</div>	
		</div>
	</div>
</div>
<script defer src="{$system.cdn}/{$template}/assets/js/postmessage.js" id="sg-post-message" data-origin="{$system.url}"></script>