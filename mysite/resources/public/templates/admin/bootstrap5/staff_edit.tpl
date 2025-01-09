{extends "user_edit.tpl"}
{block name="tab-edit"}
        <div id="tab-edit" class="tab-pane active" role="tabpanel"> 
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Staff Name"|trans}</label>
            <div class="col-sm-7">
              {if $api.staff.user_id}
              <input type="hidden" name="staff[user_id]" value="{$api.staff.user_id}">
              {/if}
              <input class="form-control" type="text" name="staff[name]" size="35" value="{$api.staff.name}" placeholder="{'Name'|trans}" required {if $api.staff.admin_id}readonly{/if}>
            </div>
          </div> 
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Staff Email"|trans}</label>
            <div class="col-sm-7">
              <input class="form-control" type="text" name="staff[email]" size="35" value="{$api.staff.email}" placeholder="{'Email'|trans}" required {if $api.staff.admin_id}readonly{/if}>
            </div>
          </div>    
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Staff Roles"|trans}</label>
            <div class="col-sm-7">
              <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
              <script defer src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
              <select class="form-control selectpicker show-tick" name="staff[role_ids][]" data-style="form-control" data-none-selected-text="&nbsp;" multiple required>
              {foreach $html.roles as $role}
                <option value="{$role.id}" {if $role.selected}selected{/if}>{$role.name|trans}</option>
              {/foreach}
              </select>  
            </div>
          </div>  
          {if $api.staff.admin_id}
          <div class="form-group row mb-3">
            <label class="col-sm-3 col-form-label text-sm-end">{"Staff Status"|trans}</label>
            <div class="col-sm-7">
              <select class="form-select" name="staff[status]">
                <option value="Inactive">{"Inactive"|trans}</option>
                <option value="Active" {if $api.staff.status eq 'Active'}selected{/if}>{"Active"|trans}</option>
              </select>  
            </div>
          </div>
          {/if}
        </div>
{/block}