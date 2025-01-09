<!DOCTYPE html>
<html lang="en">
<head>
{block name='block_head'}{$block_head nofilter}{/block}
</head>
{block name='block_body'}
<body class="sg-{if $api.page.subtype}{$api.page.subtype|replace:'App::':''|lower}__{/if}{$api.page.type|lower}">
 <div class='container-fluid'>
   <div class='row'>
     <div class='col-sm-12'>
       <div class='row'>
         <div id='block_logo' class='col-sm-6'>
           <div class="sg-block-content">{block name='block_logo'}{$block_logo nofilter}{/block}</div>
         </div>
         <div class='col-12'>
           <div id="block_header" class="sg-block-content">
          {block name='block_header'}{$block_header nofilter}{/block}
        </div>
         </div>
       </div>
     </div>
   </div>
 </div>
 <div class='container-fluid'>
   <div class='row'>
     <div class='col-12'>
       <div id="block_spotlight" class="sg-block-content">
          {block name='block_spotlight'}{$block_spotlight nofilter}{/block}
        </div>
     </div>
     <div id='block_left' class='col-auto px-0'>
       <div class="sg-block-content">{block name='block_left'}{$block_left nofilter}{/block}</div>
     </div>
     <div class='col top-main-bottom'>
       <div class='row'>
         <div class='col-12'>
          <div id="block_top" class="sg-block-content">
            {block name='block_top'}{$block_top nofilter}{/block}
          </div>
         </div>
         <div class='col-12 px-0'>
           <div id="block_main" class="sg-block-content" {if !$block_main}style="min-height: calc(100vh - 404px);"{/if}>
            {block name='block_main'}{$block_main nofilter}{/block}
            {* You can place everything before this line into header.tpl and anything after this line to footer.tpl *}
           </div>
         </div>
         <div class='col-12'>
           <div id="block_bottom" class="sg-block-content">
            {block name='block_bottom'}{$block_bottom nofilter}{/block}
           </div>
         </div>
       </div>
     </div>
     <div id="block_right" class='col-auto px-0'>
       <div class="sg-block-content">
          {block name='block_right'}{$block_right nofilter}{/block}
        </div>
     </div>
   </div>
 </div>
 <div class='container-fluid'>
   <div class='row'>
     <div class='col-12'>
       <div id="block_footer" class="sg-block-content">
          {block name='block_footnote'}{$block_footnote nofilter}{/block}
          {block name='block_footer'}{$block_footer nofilter}{/block}
        </div>
     </div>
   </div>
 </div>
</body>
{/block}
{block name='block_script'}{$block_script nofilter}{/block}
</html>