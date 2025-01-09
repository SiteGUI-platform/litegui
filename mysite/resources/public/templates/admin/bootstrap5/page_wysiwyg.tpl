{include "summernote.tpl"}
{include "codemirror.tpl"}
{$page = $api.page}
<script defer src="{$system.cdn}/{$template}/assets/js/editor.js?v=62" id="sg-editor-script" data-links-widget="{$links.widget}" data-links-snippet="{$links.snippet}" data-links-genai="{$links.genai}"></script>
<script defer src="{$system.cdn}/{$template}/assets/js/postmessage.js" id="sg-post-message" data-origin="{$system.url}"></script>
<link href='{$system.cdn}/{$template}/assets/css/layout.editor.css?v=4' rel='stylesheet' />
<script defer type="text/javascript" src="{$system.cdn}/{$template}/assets/js/jquery.ui.touch-punch.js"></script>
<script type="text/javascript">

</script>
    <!--iframe id='layout-frame' frameborder="0" style="width: 100%; height: 100vh;" title="{'Layout Editor'|trans}" src='javascript: "<script>window.onload = function() {
      console.info(/frame window.onload triggered./);  
      var insertjs = document.createElement(\"script\");
      insertjs.type = \"text/javascript\";
      insertjs.src = \"https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js\";
      var insertcss = document.createElement(\"link\");
      insertcss.type = \"text/css\";
      insertcss.rel = \"stylesheet\";
      insertcss.href = \"https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css\";      
      document.getElementsByTagName(\"head\").item(0).appendChild(insertcss);
      document.getElementsByTagName(\"head\").item(0).appendChild(insertjs);
}</script>";' tabindex="0" allowtransparency="true">
    </iframe-->    
<div id='layout-editor' class="sg-main tab-content sg-show-content sg-show-preview col-sm-12 py-0 bg-white" style="background: none; z-index-must-be-above-link-modal: 0;">
{foreach $site.locales as $lang => $language}
  <div id="tab-{$lang}" data-lang="{$lang}" class="tab-pane fade {if $language@first}show active{/if}" role="tabpanel"> 
    {if $api.widget.type AND $api.widget.data[{$lang}]}
      {$api.widget.data[{$lang}] nofilter}
    {else}
      {$page.content[{$lang}] nofilter}
    {/if}     
  </div>  
{/foreach}
</div>

<form action="{$links.update}" id="editor-form" class="p-0 d-none" method="post" target="_top">
  <input type="hidden" name="page[content][wysiwyg]" value="1">
  {if $page.id > 0}<input type="hidden" name="page[id]" value="{$page.id}">{/if}
  {if $page.subtype}<input type="hidden" name="page[subtype]" value="{$page.subtype}">{/if}
  {if $page.slug}<input type="hidden" name="page[slug]" value="{$page.slug}">{/if}
  {if $page.published}<input type="hidden" name="page[published]" value="{$page.published}">{/if}
</form>  

<!-- Control block -->
<div class="sg-toolbox-overlay">
  <span class="badge rounded-pill bg-warning text-white">
    <span class="sg-toolbox-menu mx-3 d-none">
      <button class="sg-toolbox-close d-none btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Close'|trans}" data-animation="false" data-bs-offset="-25,0"><i class="bi bi-x-circle"></i></button>
      <button class="sg-toolbox-delete btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Delete'|trans}"><i class="bi bi-trash"></i></button>
      <button class="sg-toolbox-up btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Up'|trans}"><i class="bi bi-box-arrow-up"></i></button>
      <button class="sg-toolbox-down btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Down'|trans}"><i class="bi bi-box-arrow-down"></i></button>
      <button class="sg-toolbox-clone btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Clone'|trans}" data-animation="false"><i class="bi bi-files"></i></button>
      <button class="sg-toolbox-inner sg-toolbox-outer btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Outer'|trans}"><i class="bi bi-back"></i></button>
      <button class="sg-toolbox-inner btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Inner'|trans}" data-animation="false"><i class="bi bi-front"></i></button>
      <button class="sg-toolbox-edit btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Edit'|trans}" data-animation="false"><i class="bi bi-pencil-square"></i></button>
    </span>
    <button class="sg-toolbox-open btn btn-sm border-0 p-0" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Element'|trans}" data-animation="false"><i class="bi bi-three-dots"></i></button>
  </span>
  <span class="sg-toolbox-add btn btn-sm border-0 btn-warning text-dark rounded-circle" data-bs-toggle="tooltip" data-bs-placement="right" title="{'Add'|trans}"><i class="bi bi-plus-lg"></i></span>
</div>

<!-- Below are blocks to be moved to parent frame -->
<div class="d-none">
  <!-- {* Toolbar *} -->
  <div id="sg-editor-toolbar" class="col-12 pb-1 px-3">
    <div class="row">
      <div class="col mx-4 mx-sm-0 pt-1 ps-0">
        <button type="button" class="navbar-toggler sidebar-toggler opa-1">
          <span class="sr-only"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        {if ! $app.hide.name}
          <div id="sg-language-switcher" class="nav nav-tabs d-none" role="tablist">
            {foreach $site.locales as $lang => $language}
              <button type="button" class="{if $language@first}active{/if}" data-bs-target="#tab-{$lang}" role="tab" aria-controls="tab-{$lang}" data-bs-toggle="tab">{$language|capitalize}</button>
            {/foreach}
          </div>
          {if !$api.widget.type} {* widget visual editor *}
          <div id="sg-multi-label" class="tab-content ps-5">      
            {foreach $site.locales as $lang => $language}
              <div id="tab-{$lang}" class="tab-pane fade {if $language@first}show active{/if}" role="tabpanel"> 
                <input class="sg-label-{$lang} input-name form-control text-success bg-transparent" type="text" name="page[name][{$lang}]" placeholder="{'Label'|trans}" value="{$page.name[$lang]}" required>  
              </div>  
            {/foreach}
          </div>
          {/if}  
        {/if}
      </div>

      <div class="col-sm d-none d-sm-block px-0 text-center">  
        <button class="btn border-0 mx-lg-1 text-secondary sg-view-desktop" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Desktop'|trans}"><i class="bi bi-display fs-5"></i></button>
        <button class="btn border-0 mx-lg-1 text-secondary sg-view-laptop" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Laptop'|trans}"><i class="bi bi-laptop fs-5"></i></button>
        <button class="btn border-0 mx-lg-1 text-secondary sg-view-tablet" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Tablet'|trans}"><i class="bi bi-tablet fs-5"></i></button>
        <button class="btn border-0 mx-lg-1 text-secondary sg-view-phone" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Phone'|trans}"><i class="bi bi-phone fs-5"></i></button>    
      </div>
      <div class="col-sm col-auto pt-1 me-5 me-sm-000 pe-0">  
        {if $site.locales|count > 1}
        <div id="sg-language-remoter" class="nav nav-pills float-end d-flex dropdown">
          <button type="button" class="btn btn-sm btn-light border-0 text-secondary mt-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{$site.locales[$site.language]|capitalize} <i class="bi bi-translate"></i></button>
          <div class="dropdown-menu"><!--not use ul/li to make use of javascript siblings-->
            {foreach $site.locales as $lang => $language}
            <button type="button" class="dropdown-item btn-sm {if $language@first}active{/if}" data-bs-target="#tab-{$lang}" role="tab" aria-controls="tab-{$lang}" data-bs-toggle="tab">{$language|capitalize}</button>
            {/foreach}
          </div>   
        </div> 
        {/if} 
        <button id="sg-editor-save" type="button" class="btn btn-sm btn-outline-success rounded-circle" style="position: fixed; top:8px; right:8px;" data-bs-toggle="tooltip" data-bs-placement="left" title="{'Save'|trans}"><i class="bi bi-save2"></i></button>  
      </div> 
    </div>    
  </div> 
   
  <!-- Sidebar -->
  <div id="sg-sidebar" class="tab-content">
    <div class="tab-pane active" id="sg-editor__elements" role="tabpanel" aria-labelledby="tab-elements">      
      <!-- {* Tab Elements *} -->
      <div id="tab-elements">
        <div class="row">
          <div class="col px-0 position-fixed sg-sidebar-nav">
            <ul class="nav nav-pills px-0">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-label-default-elements" data-bs-toggle="tab" data-bs-target="#sg-blocks__default" type="button" role="tab" aria-controls="sg-blocks__default" aria-selected="true"><i class="bi bi-layout-wtf" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Blocks'|trans}"></i></button>
              </li>
              {if $api.widgets}
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="label-widget-elements" data-bs-toggle="tab" data-bs-target="#sg-blocks__widgets" type="button" role="tab" aria-controls="sg-blocks__widgets" aria-selected="true"><i class="bi bi-journal-bookmark" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Widgets'|trans}"></i></button>
              </li>
              {/if}
              {if $api.snippets.template}
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="label-theme-elements" data-bs-toggle="tab" data-bs-target="#sg-blocks__theme" type="button" role="tab" aria-controls="sg-blocks__theme" aria-selected="true"><i class="bi bi-gift" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Theme Blocks'|trans}"></i></button>
              </li>
              {/if}
              {if $links.genai}
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="label-ai-elements" data-bs-toggle="tab" data-bs-target="#sg-blocks__ai" type="button" role="tab" aria-controls="sg-blocks__ai" aria-selected="true"><i class="bi bi-stars" data-bs-toggle="tooltip" data-bs-placement="top" title="{'AI Blocks'|trans} - Beta"></i></button>
              </li>
              {/if}
            </ul> 
          </div>
        </div>  

        <!-- Element tab panes -->
        <div class="tab-content text-center">
          <div class="col-12 py-3">
            <div class="input-group bg-dark rounded">
              <button class="input-group-text border-dark bg-dark text-secondary my-auto pe-1"><i class="bi bi-search"></i></button> 
              <div id="sg-editor-search" class="form-control border-dark bg-dark text-lime text-start px-1" contenteditable="true" type="text" placeholder="{'Find'|trans} ..."></div>
              <button id="sg-editor-search-clear" class="input-group-text btn border-dark bg-dark text-secondary" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Clear'|trans}"><i class="bi bi-x-lg"></i></button>
            </div>
          </div>
          <div class="tab-pane active" id="sg-blocks__default" role="tabpanel" aria-labelledby="tab-label-default-elements">  
          {foreach $api.snippets.system as $category}
            {if !$category.smarty}
            <section class="row row-cols-3">
              <div class="col-12 py-2 text-start small sg-sidebar-heading">{if $category.icon} {$category.icon nofilter} {/if} {$category.name}</div>
              {foreach $category.snippets as $snippet}
              {if !$snippet.smarty}
              <div class="sg-block-wrapper col">          
                <div class="sg-block-title" data-sg-sid="system__.{$system.template}.{$snippet.id}">{if $snippet.icon} {$snippet.icon nofilter} {/if} {$snippet.name}</div>
              </div>  
              {/if}      
              {/foreach}
            </section> 
            {/if}   
          {/foreach}
          </div>
          <div class="tab-pane" id="sg-blocks__widgets" role="tabpanel" aria-labelledby="label-widget-elements">      
          {foreach $api.widgets as $category}
            <section class="row row-cols-3">
              <div class="col-12 py-2 text-start small sg-sidebar-heading">{if $category.icon} {$category.icon nofilter} {/if} {$category.name}</div>
              {foreach $category.snippets as $widget}
              <div class="sg-block-wrapper col">
                <div class="sg-block-title" data-sg-sid="widget__.{$widget.type|lower}.{$widget.id}">{$widget.name}</div>
              </div> 
              {/foreach}
            </section>  
          {/foreach}
          </div>
          <div class="tab-pane" id="sg-blocks__theme" role="tabpanel" aria-labelledby="label-theme-elements">      
          {foreach $api.snippets.template as $category}
            {if !$category.smarty}
            <section class="row row-cols-3">
              <div class="col-12 py-2 text-start small sg-sidebar-heading">{if $category.icon} {$category.icon nofilter} {/if} {$category.name}</div>
              {foreach $category.snippets as $snippet}
              {if !$snippet.smarty}
              <div class="sg-block-wrapper col">          
                <div class="sg-block-title" data-sg-sid="{$site.template}.{$snippet.id}">{if $snippet.icon} {$snippet.icon nofilter} {/if} {$snippet.name}</div>
              </div>  
              {/if}      
              {/foreach}
            </section> 
            {/if}   
          {/foreach}
          </div>
          <div class="tab-pane" id="sg-blocks__ai" role="tabpanel" aria-labelledby="label-theme-ai">      
            <section class="row sg-ai-ouput">
              <div class="col d-grid pe-0">
                <button class="btn btn-dark sg-ai-image" type="button" data-type="image"><i class="bi bi-stars"></i> Image</button>
              </div>
              <div class="col d-grid">
                <button class="btn btn-dark sg-ai-text" type="button" data-type="text"><i class="bi bi-stars"></i> Content</button>
              </div>
              <div class="col-12 mt-3 border-dark border-top">
              </div> 
              <div class="sg-block-wrapper col d-none"></div> 
            </section>  
          </div>  
        </div>    
      </div> 
    </div>
    <div class="tab-pane" id="sg-editor__editing" role="tabpanel" aria-labelledby="tab-editor">
      <div class="row position-fixed sg-sidebar-nav">
        <div class="col px-0">   
          <ul class="nav nav-pills px-0" role="tablist"> 
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="label-editor__text" data-bs-toggle="pill" data-bs-target="#sg-editor__text" type="button" role="tab" aria-controls="sg-editor__text" aria-selected="true"><i class="bi bi-textarea-t" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Content'|trans}"></i></button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="label-editor__attribute" data-bs-toggle="pill" data-bs-target="#sg-editor__attribute" type="button" role="tab" aria-controls="sg-editor__attribute" aria-selected="false"><i class="bi bi-aspect-ratio" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Attributes'|trans}"></i></button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="label-editor__effect" data-bs-toggle="pill" data-bs-target="#sg-editor__effect" type="button" role="tab" aria-controls="sg-editor__effect" aria-selected="false"><i class="bi bi-lightning" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Effect'|trans}">fx</i></button>
            </li>
          </ul>
        </div>  
        <div class="col-auto px-0">
          <ul class="nav nav-pills px-0">
            <li class="nav-item" role="presentation">
              <button class="nav-link text-lime" id="sg-editor-done" data-bs-toggle="tab" data-bs-target="#sg-editor__elements" type="button" role="tab" aria-controls="sg-editor__elements" aria-selected="true"><i class="bi bi-check2-all" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Done'|trans}"></i></button>
            </li>
          </ul> 
        </div>
      </div>  
      <!--Editor tab pane-->
      <div class="tab-content">
        <div class="tab-pane fade show active" id="sg-editor__text" role="tabpanel" aria-labelledby="label-editor__text"></div>
        <div class="tab-pane fade" id="sg-editor__attribute" role="tabpanel" aria-labelledby="label-editor__attribute"></div>
        <div class="tab-pane fade" id="sg-editor__effect" role="tabpanel" aria-labelledby="label-editor__effect">
          <section class="animation-list animate__animated animate__slideInLeft">
            <div class="col-12">
              <div class="input-group sg-animation-current bg-dark mb-3 rounded d-none">
                <button class="input-group-text border-dark bg-dark text-secondary sg-animation-repeat pe-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Repeat'|trans}"><i class="bi bi-arrow-repeat"></i></button> 
                <span class="input-group-text border-dark bg-dark text-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Current Effect'|trans}"><i class="bi bi-lightning"></i></span> 
                <input class="form-control border-dark bg-dark text-lime px-0" type="text" readonly>
                <button class="input-group-text sg-animation-none btn border-dark bg-dark text-secondary" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Clear Effect'|trans}"><i class="bi bi-x-lg"></i></button>
                </span> 
              </div>
            </div> 
            <section class="attention_seekers row row-cols-2" id="attention_seekers">
              <div class="col-12 py-2 sg-sidebar-heading">{"Attention seekers"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="bounce"><div class="sg-block-title">bounce</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="flash"><div class="sg-block-title">flash</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="pulse"><div class="sg-block-title">pulse</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="rubberBand"><div class="sg-block-title">rubberBand</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="shakeX"><div class="sg-block-title">shakeX</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="shakeY"><div class="sg-block-title">shakeY</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="headShake"><div class="sg-block-title">headShake</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="swing"><div class="sg-block-title">swing</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="tada"><div class="sg-block-title">tada</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="wobble"><div class="sg-block-title">wobble</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="jello"><div class="sg-block-title">jello</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="heartBeat"><div class="sg-block-title">heartBeat</div></div>
            </section>
            <section class="back_entrances row row-cols-2" id="back_entrances">
              <div class="col-12 py-2 sg-sidebar-heading">{"Back entrances"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="backInDown"><div class="sg-block-title">backInDown</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="backInLeft"><div class="sg-block-title">backInLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="backInRight"><div class="sg-block-title">backInRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="backInUp"><div class="sg-block-title">backInUp</div></div>
            </section>
            <section class="back_exits row row-cols-2" id="back_exits">
              <div class="col-12 py-2 sg-sidebar-heading">{"Back exits"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="backOutDown"><div class="sg-block-title">backOutDown</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="backOutLeft"><div class="sg-block-title">backOutLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="backOutRight"><div class="sg-block-title">backOutRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="backOutUp"><div class="sg-block-title">backOutUp</div></div>
            </section>
            <section class="bouncing_entrances row row-cols-2" id="bouncing_entrances">
              <div class="col-12 py-2 sg-sidebar-heading">{"Bouncing entrances"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="bounceIn"><div class="sg-block-title">bounceIn</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="bounceInDown"><div class="sg-block-title">bounceInDown</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="bounceInLeft"><div class="sg-block-title">bounceInLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="bounceInRight"><div class="sg-block-title">bounceInRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="bounceInUp"><div class="sg-block-title">bounceInUp</div></div>
            </section>

            <section class="bouncing_exits row row-cols-2" id="bouncing_exits">
              <div class="col-12 py-2 sg-sidebar-heading">{"Bouncing exits"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="bounceOut"><div class="sg-block-title">bounceOut</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="bounceOutDown"><div class="sg-block-title">bounceOutDown</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="bounceOutLeft"><div class="sg-block-title">bounceOutLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="bounceOutRight"><div class="sg-block-title">bounceOutRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="bounceOutUp"><div class="sg-block-title">bounceOutUp</div></div>
            </section>

            <section class="fading_entrances row row-cols-2" id="fading_entrances">
              <div class="col-12 py-2 sg-sidebar-heading">{"Fading entrances"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeIn"><div class="sg-block-title">fadeIn</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeInDown"><div class="sg-block-title">fadeInDown</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeInDownBig"><div class="sg-block-title">fadeInDownBig</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeInLeft"><div class="sg-block-title">fadeInLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeInLeftBig"><div class="sg-block-title">fadeInLeftBig</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeInRight"><div class="sg-block-title">fadeInRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeInRightBig"><div class="sg-block-title">fadeInRightBig</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeInUp"><div class="sg-block-title">fadeInUp</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeInUpBig"><div class="sg-block-title">fadeInUpBig</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeInTopLeft"><div class="sg-block-title">fadeInTopLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeInTopRight"><div class="sg-block-title">fadeInTopRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeInBottomLeft"><div class="sg-block-title">fadeInBottomLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeInBottomRight"><div class="sg-block-title">fadeInBottomRight</div></div>
            </section>

            <section class="fading_exits row row-cols-2" id="fading_exits">
              <div class="col-12 py-2 sg-sidebar-heading">{"Fading exits"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOut"><div class="sg-block-title">fadeOut</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOutDown"><div class="sg-block-title">fadeOutDown</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOutDownBig"><div class="sg-block-title">fadeOutDownBig</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOutLeft"><div class="sg-block-title">fadeOutLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOutLeftBig"><div class="sg-block-title">fadeOutLeftBig</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOutRight"><div class="sg-block-title">fadeOutRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOutRightBig"><div class="sg-block-title">fadeOutRightBig</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOutUp"><div class="sg-block-title">fadeOutUp</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOutUpBig"><div class="sg-block-title">fadeOutUpBig</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOutTopLeft"><div class="sg-block-title">fadeOutTopLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOutTopRight"><div class="sg-block-title">fadeOutTopRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOutBottomRight"><div class="sg-block-title">fadeOutBottomRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="fadeOutBottomLeft"><div class="sg-block-title">fadeOutBottomLeft</div></div>
            </section>

            <section class="flippers row row-cols-2" id="flippers">
              <div class="col-12 py-2 sg-sidebar-heading">{"Flippers"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="flip"><div class="sg-block-title">flip</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="flipInX"><div class="sg-block-title">flipInX</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="flipInY"><div class="sg-block-title">flipInY</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="flipOutX"><div class="sg-block-title">flipOutX</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="flipOutY"><div class="sg-block-title">flipOutY</div></div>
            </section>

            <section class="lightspeed row row-cols-2" id="lightspeed">
              <div class="col-12 py-2 sg-sidebar-heading">{"Lightspeed"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="lightSpeedInRight"><div class="sg-block-title">lightSpeedInRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="lightSpeedInLeft"><div class="sg-block-title">lightSpeedInLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="lightSpeedOutRight"><div class="sg-block-title">lightSpeedOutRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="lightSpeedOutLeft"><div class="sg-block-title">lightSpeedOutLeft</div></div>
            </section>

            <section class="rotating_entrances row row-cols-2" id="rotating_entrances">
              <div class="col-12 py-2 sg-sidebar-heading">{"Rotating entrances"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="rotateIn"><div class="sg-block-title">rotateIn</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="rotateInDownLeft"><div class="sg-block-title">rotateInDownLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="rotateInDownRight"><div class="sg-block-title">rotateInDownRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="rotateInUpLeft"><div class="sg-block-title">rotateInUpLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="rotateInUpRight"><div class="sg-block-title">rotateInUpRight</div></div>
            </section>

            <section class="rotating_exits row row-cols-2" id="rotating_exits">
              <div class="col-12 py-2 sg-sidebar-heading">{"Rotating exits"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="rotateOut"><div class="sg-block-title">rotateOut</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="rotateOutDownLeft"><div class="sg-block-title">rotateOutDownLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="rotateOutDownRight"><div class="sg-block-title">rotateOutDownRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="rotateOutUpLeft"><div class="sg-block-title">rotateOutUpLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="rotateOutUpRight"><div class="sg-block-title">rotateOutUpRight</div></div>
            </section>

            <section class="specials row row-cols-2" id="specials">
              <div class="col-12 py-2 sg-sidebar-heading">{"Specials"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="hinge"><div class="sg-block-title">hinge</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="jackInTheBox"><div class="sg-block-title">jackInTheBox</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="rollIn"><div class="sg-block-title">rollIn</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="rollOut"><div class="sg-block-title">rollOut</div></div>
            </section>

            <section class="zooming_entrances row row-cols-2" id="zooming_entrances">
              <div class="col-12 py-2 sg-sidebar-heading">{"Zooming entrances"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="zoomIn"><div class="sg-block-title">zoomIn</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="zoomInDown"><div class="sg-block-title">zoomInDown</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="zoomInLeft"><div class="sg-block-title">zoomInLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="zoomInRight"><div class="sg-block-title">zoomInRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="zoomInUp"><div class="sg-block-title">zoomInUp</div></div>
            </section>

            <section class="zooming_exits row row-cols-2" id="zooming_exits">
              <div class="col-12 py-2 sg-sidebar-heading">{"Zooming exits"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="zoomOut"><div class="sg-block-title">zoomOut</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="zoomOutDown"><div class="sg-block-title">zoomOutDown</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="zoomOutLeft"><div class="sg-block-title">zoomOutLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="zoomOutRight"><div class="sg-block-title">zoomOutRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="zoomOutUp"><div class="sg-block-title">zoomOutUp</div></div>
            </section>


            <section class="sliding_entrances row row-cols-2" id="sliding_entrances">
              <div class="col-12 py-2 sg-sidebar-heading">{"Sliding entrances"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="slideInDown"><div class="sg-block-title">slideInDown</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="slideInLeft"><div class="sg-block-title">slideInLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="slideInRight"><div class="sg-block-title">slideInRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="slideInUp"><div class="sg-block-title">slideInUp</div></div>
            </section>


            <section class="sliding_exits row row-cols-2" id="sliding_exits">
              <div class="col-12 py-2 sg-sidebar-heading">{"Sliding exits"|trans}</div>
              <div class="col py-2 sg-block-wrapper" data-animation="slideOutDown"><div class="sg-block-title">slideOutDown</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="slideOutLeft"><div class="sg-block-title">slideOutLeft</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="slideOutRight"><div class="sg-block-title">slideOutRight</div></div>
              <div class="col py-2 sg-block-wrapper" data-animation="slideOutUp"><div class="sg-block-title">slideOutUp</div></div>
            </section>

          </section>
        </div>
      </div>

    </div>
  </div>
  <!-- Additional style/script provided by template-->
  {$api.snippets.resources nofilter}
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(e){
  /*var frameSelector = "#layout-frame";
  var inFrame = document.getElementById('layout-frame').contentWindow;

  //ADDS ABOVE TO IFRAME
  $(frameSelector).ready(function() {
    console.log('inject scripts to frame');
    //return;   
    //CREATES NEW JS & CSS TAGS RESPECTIVELY
    function insertScript(doc, target, src, callback) {
      var insertjs = doc.createElement("script");
      insertjs.type = "text/javascript";
      insertjs.src = src;
      target.appendChild(insertjs);
    }
    function insertCss(doc, target, href, callback) {
      var insertcss = doc.createElement("link");
      insertcss.type = "text/css";
      insertcss.rel = "stylesheet";
      insertcss.href = href;
      target.appendChild(insertcss);    
    }
    var context = window.frames['layout-frame'].contentDocument;
    var frameHead = context.getElementsByTagName('head').item(0);
    insertScript(context, frameHead, 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js');
    insertScript(context, frameHead, 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js');
    insertScript(context, frameHead, 'https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js');
    insertCss(context, frameHead, 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css');
    insertCss(context, frameHead, 'https://cdn.sitegui.com/public/templates/admin/bootstrap5/assets/css/mysite.css');
    insertCss(context, frameHead, 'https://cdn.sitegui.com/public/templates/admin/bootstrap5/assets/css/layout.editor.css');
  });
*/
  var editorSelector  = "#layout-editor > .tab-pane";
  //var blockSelector   = ".sg-block-wrapper";
  //var contentSelector = ".sg-block-content";
  sgEditor.initToolbar();
  // Move sidebar to parent frame  
  sgEditor.initSidebar(editorSelector +".active");

  //before dropping/adding item 
  beforeStop = function(event, ui) {
    // new item being added
    if (!ui.item.hasClass('ui-resizable') && !ui.item.hasClass('container-fluid') && !ui.item.hasClass('container')) {
      ui.item.removeClass('col col-1 col-2 col-3 col-4 col-5 col-6 col-7 col-8 col-9 col-10 col-11').addClass('col-12');
      ui.item.find('.sg-block-title').remove();
      // modifying unique id
      sgEditor.uniqueIds(ui.item);
      sgScript.execute(ui.item.attr('data-sg-func'), ui.item, 'init');
    }
    // wrap item inside a row if it is not placed inside a row
    if (!ui.placeholder.parent().hasClass('row') ){
      if (ui.placeholder.prev().hasClass('row')) { //placeholder prev is a row
        ui.placeholder.prev().append(ui.placeholder, ui.item); //move placeholder to support click to add
      } else if (ui.placeholder.next().hasClass('row')) { //placeholder next is a row
        ui.placeholder.next().prepend(ui.placeholder, ui.item);
      } else {  
        ui.item = ui.item.removeAttr('style').wrap('<div class="row"></div>').parent(); //fix to work with both dragging and click
      } 
    }
    return ui.item;
  }


  // Add editable content to left sidebar editor
  /*var removableIndex;
  addEditables = function(container, element, index) {
    if (index == 0) { //run only once
      removableIndex = 1; //start from 1
      if (element.find('.sg-editor-template').length) {
        container.html('<div class="d-grid"><button class="btn btn-dark sg-editor-add" type="button">Add New Item</button></div>');
      } else {
        container.html('');      
      } 
    } else {
      removableIndex = index;
    }
    element.contents().each(function (){
      if ( ($(this).hasClass('d-none') && $(this).hasClass('sg-editor-template')) || $(this).hasClass('sg-editor-hidden') ) return; //skip hidden template or content
      let add;

      if (this.childElementCount) {
        addEditables(container, $(this), removableIndex);
      } else if (this.tagName == 'IMG') {
        add = $('<div class="thumbnail mb-2 border-1" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="{'File Manager'|trans}" data-url="{$html.file_manager}?CKEditorFuncNum=1#elf_l1_dXBsb2Fk"></div>').append( $('<img class="img-fluid px-5">').attr('src', $(this).attr('src')) );
        add.on('click', function () {
          var self = $(this);
          //setup parent postMessage callback function
          parent.window.getImageCallback = function(files){
            //console.log(url);
            self.children('img').attr('src', files[0].url);
            self.data('target').attr('src', files[0].url);
          }  
        });
      } else if (this.tagName != 'SCRIPT' && this.tagName != 'STYLE' && (this.nodeType == 1 || this.nodeType == 3) && this.textContent != null && this.textContent.trim() ){ 
        //only text nodes or elements containing a single text node here
        //console.log(this);
        //console.log(this.nodeType);
        add = $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>').text(this.textContent);
        //if (this.nodeType === 3) { //text node's parent has other elements
          //$(this).wrap('<span></span>')
          //add.data('target', $(this).parent());
        //} 
        add.on('keyup', function () {
          if ($(this).data('target').get(0).nodeType === 3) { //text node
            $(this).data('target').get(0).nodeValue = $(this).text();
          } else {
            $(this).data('target').text($(this).text());
          }  
        });
      }
      if (add) {
        add.data('target', $(this)); //required for scroll and modifying target
        /*add.on('mouseover', function () {
          if ($(this).data('target').get(0).nodeType === 3) {
            $(this).data('target').get(0).parentNode.scrollIntoViewIfNeeded();
          } else {  
            $(this).data('target').get(0).scrollIntoViewIfNeeded();
          }  
        });
        add.appendTo(container);*//*
        sgEditor.addSidebarElement(add, false, container);

        if ($(this).hasClass('sg-editor-removable')) {
          let class2add;
          if (this.tagName == 'IMG') {
            class2add = "position-absolute";
          } else class2add = "float-end";

          add.before('<hr class="row mt-3"></hr><button class="btn text-warning '+ class2add +' sg-editor-remove" data-sg-remove="'+ removableIndex++ +'" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="{'Remove Item'|trans}"><i class="bi bi-x-circle"></i></button>'); 
        }
      }  
      
    })
  } 
  //Edit element attributes
  addAttributes = async function(container, element) {
    //element has js function but source has not been loaded
    if (element.attr('data-sg-func') && !sgScript.registered(element.attr('data-sg-func'))) {
      let ui = {
        //find sg-block on sidebar that wid start with function name e.g: wid=widget.text.123
        item: $('[data-sg-sid^="'+ element.attr('data-sg-func') +'"]', parent.document).eq(0).parent(), 
        helper: $('<div class="d-none"/>').appendTo($('body')), //helper is just used to attach js to context
      }  
      if (!ui.item.length) {
        //fake item for getSnippet
        ui.item = $('<div class="d-none"><div class="sg-block-title" data-sg-sid="'+ element.attr('data-sg-func') +'"></div></div>');
      }  
      //console.log(ui)
      await getSnippet(ui);        
      //setTimeout(function(){ 
      ui.helper.remove(); //somehow remove immediately will not attach js to context
      //}, 5000);
    }

    add = element.attr('id')? $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>').text(element.attr('id')) : $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>');
    add.on('keyup', function () {
      element.attr('id', $(this).text().trim());
    });
    //add.data('target', element);
    container.append('<label class="form-label">ID</label>').append(add);

    //wrapper class
    let hiddenClasses = ''; 
    let classes = element.attr('class').split(" ").filter(function(c) {
      if (c.startsWith('ui-') || c.startsWith('sg-') ){
        hiddenClasses += ' '+ c;
        return false;
      } else {  
        return true;
      }
    }).join(" ");
    add = classes? $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>').text(classes) : $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>');
    add.on('keyup', function () {
      element.attr('class', $(this).text().trim() + hiddenClasses);
    });
    //add.data('target', element);
    container.append('<label class="form-label">Wrapper Classes</label>').append(add);

    //content class
    classes = element.children(contentSelector).attr('class');
    add = $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>').text(classes);
    add.on('keyup', function () {
      element.children(contentSelector)
        .attr('class', $(this).text().trim())
        .addClass(contentSelector.slice(1)); //can't be removed
    });
    //add.data('target', element);
    container.append('<label class="form-label">Content Classes</label>').append(add);

    //current animation
    if (element.children(contentSelector).hasClass('animate__animated')) {
      let animation = element.children(contentSelector).attr('class').match(/(?!animate__(?:animated|off))animate__\w+/); //match animate__ except animate__animated and animate__off
      $('.sg-animation-current input', parent.document).val(animation[0].replace('animate__', ''));
      $('.sg-animation-current', parent.document).removeClass('d-none');
      //play once
      if ( $('body').find('.sg-animation-repeat').length ){
        $('#sg-editor__effect .sg-animation-repeat', parent.document).addClass('text-lime');
      } else {
        $('#sg-editor__effect .sg-animation-repeat', parent.document).removeClass('text-lime');
      }
    }
  }*/

  //turn non-editable content into editable
  $(editorSelector).children(':not(.row)').addClass('sg-block-content')
    .wrap('<div class="row"><div class="sg-block-wrapper"></div></div>');  
  // Sortable init
  //initSortable($(editorSelector));

/*  $(editorSelector).sortable({
    items: blockSelector,
    cancel: ".card-disable, .sg-editing",
    placeholder: "ui-state-highlight animate__animated animate__zoomIn animate__faster",
    //tolerance: "pointer",
    forceHelperSize: true,
    opacity: 0.8,
    //revert: true, //animation
    cursor: "move",  
    cursorAt: { left: 10 },
    activate: function(event, ui) { //used mainly for draggable snippet items
      getSnippet(ui);
    },
    beforeStop: function(event, ui) {
      beforeStop(event, ui);
    },
    start: function(event, ui) {  
      $(this).mouseleave(); //hide overlay toolbox  
      //$('.sg-toolbox-overlay').appendTo($('body'));    
      // add droppable area to empty block
      if (ui.item.parent() && !ui.item.parent().is(editorSelector) && ui.item.parent().children(blockSelector).length < 2) {
        //console.log(ui.item.parent());
        ui.item.parent().append('<div class="sg-block-wrapper card-empty card-disable"></div>');
      }
    }
  });*/

  //SG drag and drop elements at left sidebar
  /*var justAdded = 0; //when item is clicked, it is attached to mouse cursor, we dont need this behaviour when click to add item
  $("#tab-elements .tab-pane > .row").children(blockSelector)
    .draggable({
      helper: "clone",
      revert: "invalid",
      containment: "window",
      //iframeFix: true,
      appendTo: editorSelector +".active",
      connectToSortable: editorSelector +".active",
      start: function( event, ui ) {
        if (justAdded) {
          justAdded = 0;
          return false; //stop dragging
        }
      },
    })
    .on('click', function (ev) {
      let ui = {
        'placeholder': $(editorSelector +".active").find('.sg-block-placeholder'),
      }; 

      if (ui.placeholder.length) { 
        ui.item = $(this).clone();

        let snippet = {
          'item': $(this),
          'helper': ui.item,  
        };
        getSnippet(snippet);

        ev.preventDefault();
        ui.item = beforeStop(ev, ui);
        ui.placeholder.after(ui.item).remove();
        justAdded = 1;
      }  
    });
    //.children(contentSelector).addClass("d-none");*/
  //initDraggable(editorSelector +".active");  
  // Move sidebar to parent frame  
  //$("#sg-sidebar").appendTo($("#block_left", parent.document));
  //$("#tab-elements").appendTo($("#sg-editor__elements", parent.document));
  sgEditor.initEditor(editorSelector);
  //Summernote related function
  $(editorSelector).on('click', function () { //destroy summernote instance when clicking outside
    $(this).find('.note-editor')
      .parent().removeClass('sg-editing')
      .children(contentSelector).summernote('destroy');
    //$(editorSelector).sortable( "enable" );
  })   

  //modify Summernote config for this page
  NOTECONFIG.toolbar.pop(); //remove last item to add codeview
  NOTECONFIG.toolbar.push(['view', ['fullscreen', 'codeview', 'help']]);
  NOTECONFIG.callbacks.onInit = function(summer) {
    //console.log(summer);
    $(summer.editor).on('click dblclick mouseenter', function(e) {
      e.stopPropagation(); //stop these events from bubling up  
    });
  }
  $(editorSelector).on('dblclick', '.sg-block-wrapper', function(ev) {
    //console.log(ev);
    ev.preventDefault();
    ev.stopPropagation(); //prevent nested editor
    //we need sm-wrapper so codeview can include contentSelector
    if ($(this).children(contentSelector).summernote(NOTECONFIG).length) {
      $(this).addClass('sg-editing').mouseleave(); //disable sortable to edit content
      $('body', parent.document).removeClass('sidebar-small'); //close sidebar 
      $('html').animate({
        scrollTop: $(this).offset().top
      });
      typeof targetElement != 'undefined' && $('#sg-editor__elements', parent.document).addClass('active').siblings().removeClass('active'); 
    }
  });/*.on({ 
    mouseenter: function () {
      if ($(editorSelector).find('.ui-sortable-placeholder').length || $(editorSelector).find('.ui-resizable-resizing').length) return; //no toolbox while drag n drop or resizing
      //var container = $(this).css({ //add to sg-block-wrapper's css
      //    position: "relative",
      //    cursor: "move",
      //});
      $('.sg-toolbox-overlay')//.removeClass('d-none')
        .append('<div class="ui-resizable-handle ui-resizable-e"></div>') //existing handle will be removed, new one is required
        .appendTo( $(this) );

      if ($(this).children().is('.row')) {
        $(this).find('.sg-toolbox-open').attr('data-bs-original-title', 'Container');
      } else {
        $(this).find('.sg-toolbox-open').attr('data-bs-original-title', 'Element');
      }

      if ($(this).resizable("instance") != undefined) {
        $(this).resizable('option', 'handles', { e: $(this).find('.ui-resizable-e:last-child')});
      } else {
        initResizable($(this), 'custom');
      }  
    },
    mouseleave: function () {
      //console.log($(this));
      $(this).find(".sg-toolbox-close").click();
      $(this).find('.sg-toolbox-overlay').appendTo($('body'))
        //.addClass('d-none')
        .removeClass('animated bounce')
        .find('.ui-resizable-e:not(:last-child)').remove();
    },
  }, '.sg-block-wrapper:not(.ui-sortable-helper)'); */
 
  //Toolbox control button & action
  sgEditor.initOverlay(editorSelector);
 
  //////// Parent/Editor control
  //Viewport control 
  /*$('.sg-view-desktop', parent.document).on('click', function () {
    col = 'col-md';
    $('#wysiwyg-frame', parent.document).css({ 'width': '100%', 'height': '100vh'})
      .parent().css({ 'height': 'auto' });
  })
  $('.sg-view-laptop', parent.document).on('click', function () {
    col = 'col-md';
    $('#wysiwyg-frame', parent.document).css({ 'width': '991px', 'height': '768px'})
      .parent().css({ 'height': '864px' });
  })
  $('.sg-view-tablet', parent.document).on('click', function () {
    col = 'col-sm';
    $('#wysiwyg-frame', parent.document).css({ 'width': '767px', 'height': '1024px'})
      .parent().css({ 'height': '1120px' });
  })
  $('.sg-view-phone', parent.document).on('click', function () {
    col = '';
    $('#wysiwyg-frame', parent.document).css({ 'width': '360px', 'height': '640px'})
      .parent().css({ 'height': '736px' });
  }) */
  //Save button on parent frame
  $('#sg-editor-save', parent.document).on('click', function (ev) {
    ev.preventDefault();
    $(editorSelector).click(); //exit editing 
    //exit editing code
    $('#layout-editor pre code').children('.CodeMirror').each(function() {
      $(this).parent().text(this.CodeMirror.getValue());
    });

    $('#layout-editor > .tab-pane').each(function () {
      $(this).find(".sg-toolbox-overlay").remove()
      $(this).find(blockSelector).removeClass('ui-sortable-handle ui-draggable ui-draggable-handle ui-resizable');//, 'sg-block-hover');
      $('<textarea name="page[content]['+ $(this).attr("data-lang") +']"></textarea>').appendTo($('#editor-form')).val($(this).html());
    });
    if ( $('.input-name', parent.document).val().length ) {
      $('#editor-form').append($('#sg-multi-label', parent.document).clone()).submit();
    } else {
      $('.input-name', parent.document).focus();
    }
  }) 
  //Language switcher on parent frame
  $('#sg-language-remoter [data-bs-toggle="tab"]', parent.document).on('click', function (e) { 
    //$("#sg-language-switcher [data-bs-target='"+ e.target.dataset.bsTarget +"']").tab("show");
    $(this).siblings().removeClass('active');
    $('#sg-language-remoter', parent.document).find('.dropdown-toggle').text($(this).text()).append(' <i class="bi bi-translate"></i>');
    $("#sg-language-switcher [data-bs-target='"+ e.target.dataset.bsTarget +"']", parent.document).tab("show");
  });  


  sgEditor.initEditables();
  sgEditor.initSearch();    
  $('body').addClass('mx-3 mx-sm-5 bg-transparent').removeClass('bg-white')
});      
</script>
<style type="text/css">
  /*.ui-resizable-resizing, .sg-block-placeholder {
    box-shadow: 0 0 0 2px cyan;
  }

  .ui-resizable-e {
    pointer-events: auto;
    z-index:2030;
  }
  /*.sg-block-wrapper .ui-resizable-e {
    right: calc(var(--bs-gutter-x)/2 - 4px);
  }
  .row + .ui-resizable-e { 
    right: -7px;
  }*/
  /* the very first sg-block-wrapper block usually overflow - sg-animation-repeat is the first */
  .tab-pane.active > .row:first-child > .sg-animation-repeat + .sg-block-wrapper .sg-toolbox-overlay .badge,
  .tab-pane.active > .row:first-child > .sg-block-wrapper:first-child .sg-toolbox-overlay .badge {
    top: 0; 
  }
    
  .row + .sg-toolbox-overlay {
    box-shadow: 0 0 0 2px violet;  
  }
  .row + .sg-toolbox-overlay .sg-toolbox-edit,
  .row + .sg-toolbox-overlay .sg-toolbox-outer,
  .sg-block-content:not(.row) + .sg-toolbox-overlay .sg-toolbox-outer + .sg-toolbox-inner,
  .sg-toolbox-overlay:only-child .sg-toolbox-outer + .sg-toolbox-inner,
  .tab-pane.active > .row > .sg-block-wrapper > .sg-toolbox-overlay .sg-toolbox-outer,
  .card-empty,
  .ui-draggable-dragging .sg-block-content {
    display: none;
  }
  /*.row + .sg-toolbox-overlay, .card-empty > .sg-toolbox-overlay {
    left: 0;
    width: 100%;
  } 
  .card-empty, .sg-block-placeholder {
    border: 1px dashed #333333 !important;
    background: transparent !important;
    display: none;
  }
  .card-empty:only-child, .sg-block-placeholder {
    height: 40px !important;
    display: block;
  } */  
  .card-empty,
  .sg-block-content:not(.row) + .sg-toolbox-overlay,
  .row > .sg-block-placeholder { 
    width: calc(100% - var(--bs-gutter-x));
    margin-left: calc(var(--bs-gutter-x)/2);
  } 
  .row > .ui-state-highlight {
    /*border: 0px solid #FFF;
    border-left-width: calc(var(--bs-gutter-x));*/
    width: calc(100% - 2*var(--bs-gutter-x));
    margin: 0 calc(var(--bs-gutter-x)/2);
  }

  #layout-editor {
    min-height: 100vh;
  }
  #layout-editor > .tab-pane {
    height: 100%;
  }
</style>
