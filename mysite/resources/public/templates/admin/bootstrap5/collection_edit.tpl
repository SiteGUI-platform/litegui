{$api.app.name = collection}
{extends "page_edit.tpl"}
{block name="APP_tabname_end"}
			<li class="nav-item"><button type="button" data-bs-target="#tab-items" aria-controls="tab-items" class="nav-link" role="tab" data-bs-toggle="tab">{"Collection Items"|trans}</a></li>
{/block}

		<!-- Tab panes -->
{block name="APP_fields"}
			<div id="tab-items" class="sg-form tab-pane fade" role="tabpanel"> 
				{include "datatable.tpl" forapp="items"} 
			</div>
			{$smarty.block.parent}
{/block}