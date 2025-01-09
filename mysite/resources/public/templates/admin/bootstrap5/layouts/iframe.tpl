<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset={$charset|default: 'utf-8'}" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <base href="{$system.url}">
  <!-- Custom fonts for this template-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
</head>
<body style="overflow:hidden;">
<div class="row g-0">
  {if $api.sub.published}
  <div class="col text-end pe-2">
    <button type="submit" class="js-btn-publish btn btn-{if $api.sub.published eq 0}outline-{/if}info mb-3 text-nowrap" data-id="{$api.sub.id}" data-subtype="{$api.sub.subtype}" data-published="{if $api.sub.published gt 0}0{else}1{/if}">{if $api.sub.published gt 0}{'Unpublish'|trans}{else}{'Publish'|trans}{/if}</button>
  </div>
  {/if}
  <div class="col{if $api.sub.subtype}-auto{/if} text-end pe-2">
    <form method="POST" action="{$links.delete}" target="_parent">
      <input type="hidden" name="id" value="{$api.sub.id}">
      <input type="hidden" name="csrf_token" value="{$token}">
      <button type="submit" class="btn btn-outline-secondary text-nowrap">{'Delete'|trans}</button>
    </form>
  </div>
  {if $api.sub.subtype}
  <div class="col{if $api.sub.published}-auto{/if} text-end">
    <form method="POST" action="{$links.update}" target="_parent">
      <input type="hidden" name="page[page_linking_only]" value="unlink">
      <input type="hidden" name="page[id]" value="{$api.id}">
      <input type="hidden" name="page[subtype]" value="{$api.subtype}">
      <input type="hidden" name="page[sub][{$api.sub.subtype}][id]" value="{$api.sub.id}">
      <input type="hidden" name="csrf_token" value="{$token}">
      <button type="submit" class="btn btn-outline-secondary mb-3 text-nowrap">{'Unlink'|trans}</button>
    </form>
  </div>
  {/if}
</div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">
$('.js-btn-publish').on('click', function (ev) {
  let href = '{$links.update}'
  let post = {
    page: {
      id: $(this).attr('data-id'),
      subtype: $(this).attr('data-subtype'),
      published: $(this).attr('data-published')
    },
    csrf_token: '{$token}',
    format: 'json'
  }
  $(ev.currentTarget).text("{'Updating'|trans}...")
  $.post(href, post, function(response) {
    if (response.status.result == 'success'){
      if (post.page.published > 0) {
        $(ev.currentTarget).attr('data-published', 0).addClass('btn-info').removeClass('btn-outline-info').text("{'Unpublish'|trans}")
      } else {
        $(ev.currentTarget).attr('data-published', 1).addClass('btn-outline-info').removeClass('btn-info').text("{'Publish'|trans}")
      }
      parent.postMessage(JSON.stringify(post.page),"{$system.edit_url}"); 
    } 
  }).fail(function () {
    if (post.page.published > 0) {
      $(ev.currentTarget).addClass('btn-outline-info').removeClass('btn-info').text("{'Publish'|trans}")
    } else {
      $(ev.currentTarget).addClass('btn-info').removeClass('btn-outline-info').text("{'Unpublish'|trans}")
    }
  })
})
</script>
</html>        