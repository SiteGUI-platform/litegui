{include "datatable.tpl"}

{if $html.permissions}
<div class="col-12 col-md-10 mx-auto pb-4" id="datatable-extra">
  <div class="p-3 pt-0">
    <span class="card-title h5">{"Permission Table"|trans}</span>
  </div>
  <div class="card" style="overflow-x:auto">
    <table class="table table-hover table-custom">
      <thead><tr>
      {foreach $html.permissions_header as $th}
        <th {if $th == ID} data-column-id="id" data-identifier="true" data-order="desc" data-visible="false"
          {else}data-column-id="col{$th@index}"{/if} 
          {if $th == Action} data-formatter="links" data-sortable="false" data-align="right" data-header-align="right" data-header-css-class=""
          {elseif $th == Published or $th == Date or $th == Registered} data-converter="datetime"
          {else} data-converter="cleanXSS"{/if}
          >{$th}
        </th>
      {/foreach}
      </tr></thead>
      <tbody>
      {foreach $html.permissions as $row}
        <tr>
        {foreach $row as $col}
          <td>{$col}</td>
        {/foreach}
        </tr>
      {/foreach}
      </tbody>
    </table>
  </div>
</div>
{/if}