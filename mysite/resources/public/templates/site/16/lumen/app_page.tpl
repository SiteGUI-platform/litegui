{extends "page.tpl"}

{block name='content_bottom'}
    <div class="row">
      <div class="col-auto">
        {if $page.creator_avatar}<img class="rounded-circle me-2 sg-datatable-thumb" loading="lazy" src="{$page.creator_avatar}" />{/if}
        {if $api.profile.slug}<a class="text-decoration-none text-secondary" href="{$api.profile.slug}">{/if}
          {$page.creator_name}
        {if $api.profile.slug}</a>{/if}
      </div>  
      <div class="col text-end js-sg-date">{$page.created}</div>
    </div>
    {$content_bottom nofilter}
{/block}