<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  {block name='block_head'}{$block_head nofilter}{/block}
</head>
{block name='block_body'}
<body class="sg-{if $api.page.subtype}{$api.page.subtype|replace:'App::':''|lower}__{/if}{$api.page.type|lower}-dark">
 <div class='container-fluid'>
     <div class='col-12' id="block_spotlight">
        {block name='block_spotlight'}{$block_spotlight nofilter}{/block}
     </div>
     <div class='col-12'>
       <div id="block_main" class="sg-block-content">
        {block name='block_main'}{$block_main nofilter}{/block}
        {* You can place everything before this line into header.tpl and anything after this line to footer.tpl *}
       </div>
     </div>
 </div>
</body>
{/block}
{block name='block_script'} {$block_script nofilter} {/block}
</html>