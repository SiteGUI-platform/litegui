{$footer_padding = 0 scope=global}
{$layout = $api.layout}
<link href='{$system.cdn}/{$template}/assets/css/layout.editor.css' rel='stylesheet' />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<script defer src="{$system.cdn}/{$template}/assets/js/postmessage.js" id="sg-post-message" data-origin="{$system.url}"></script> 

{if $links.editor} {* Control frame content *}
  <iframe id='wysiwyg-frame' class="mx-auto d-block g-0" title="WYSIWYG Editor" src='{$links.editor}?frame=wysiwyg' tabindex="0" allowtransparency="true" frameborder="0"></iframe>   

{else} {* WYSIWYG frame content *}
  <script defer src="{$system.cdn}/{$template}/assets/js/editor.js?v=61" id="sg-editor-script" data-links-widget="{$links.widget}" data-links-snippet="{$links.snippet}"></script>
  <script defer src="{$system.cdn}/{$template}/assets/js/layout.editor.js"></script>
  <script defer type="text/javascript" src="{$system.cdn}/{$template}/assets/js/jquery.ui.touch-punch.js"></script>
  {include "codemirror.tpl"}
  
  <form id="sg-editor-toolbar" class="form-inline g-0" action="{$links.update}" method="post">
    <div class="col-md-12 toolbar">
      <div class="row justify-content-center align-items-center">
        <div class="col-auto">
          <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              {"Toggle"|trans}
            </button>
            <div class="dropdown-menu">
              <a href="#" class="dropdown-item text-secondary" id="sg-show-content"><i class="bi bi-card-heading fs-5"></i> {"Content"|trans}</a>
              <a href="#" class="dropdown-item text-secondary" id="sg-show-sample"><i class="bi bi-lightbulb fs-5"></i> {"Sample"|trans}</a>
              <a href="#" class="dropdown-item text-secondary" id="sg-show-preview"><i class="bi bi-collection fs-5"></i> {"Preview"|trans}</a>
            </div>
          </div>
          <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{"Device"|trans}</button>
            <div class="dropdown-menu">
              <div class="dropdown-item text-secondary sg-view-desktop"><i class="bi bi-display fs-5"></i> {"Desktop"|trans}</div>
              <div class="dropdown-item text-secondary sg-view-laptop"><i class="bi bi-laptop fs-5"></i> {"Laptop"|trans}</div>
              <div class="dropdown-item text-secondary sg-view-tablet"><i class="bi bi-tablet fs-5"></i> {"Tablet"|trans}</div>
              <div class="dropdown-item text-secondary sg-view-phone"><i class="bi bi-phone fs-5"></i> {"Phone"|trans}</div>
            </div>
          </div>
        </div>
        <div class="col text-end"><button id="sg-editor-save" class="btn btn-lg btn-primary" type="submit">{"Save"|trans}</button></div>
        <div class="col-sm-5 col-12 py-2 order-sm-first">
          <div class="input-group">
            <div class="input-group-text btn-secondary px-1"><span class="d-none d-sm-block px-2">{"Template"|trans}</span></div>
            {if $layout.id}
            <div class="input-group-text btn-secondary">{$layout.template|capitalize:true}</div>
            <input name='layout[id]' type='hidden' value='{$layout.id}'>
            {else}
            <select class="form-select" id="layout-template" name="layout[template]" style="max-width:35%;">
              {foreach $html.templates as $tpl}
              <option value="{$tpl}" {if $tpl eq $layout.template}selected{/if}>{$tpl}</option>
              {/foreach}
            </select>
            {/if}
            <input class="form-control text-success" type="text" name="layout[name]" placeholder="{'Layout Name'|trans}" value="{$layout.name}" required {if ! $layout.name}autofocus{/if}>
          </div>                 
          <textarea id="layout-content" name="layout[content]" class="d-none">{if $layout.content }{$layout.content}{else}<div class='container-fluid'></div>{/if}</textarea>
        </div>
      </div>
    </div>
    <input type="hidden" class="manual" name="csrf_token" value="{$token}">
  </form>

  <div id="layout-editor" class="sg-main tab-content col-md-12 px-0"></div>

  <!-- Block's options -->
  <div id="layout-modal" class="modal fade backdrop-blur">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
           <div class="modal-header">
              <h5 class="modal-title">{"Options"|trans}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body">
              <label>{"ID"|trans}</label> <input type="text" id="sg-editing--id" class="form-control" value=""><br/>
              <label>{"Wrapper Classes"|trans}</label> <div id="sg-editing--wrapper" contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>
              <label>{"Content Classes"|trans}</label> <div id="sg-editing--content" contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>
           </div>
           <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{"Cancel"|trans}</button>
              <button class="btn btn-primary" id="sg-editing--ok">{"OK"|trans}</button>
           </div>
        </div>
     </div>      
  </div>

  <!-- Control block -->
  <div class="sg-toolbox-overlay">
    <span class="badge rounded-pill bg-warning text-white">
      <span class="sg-toolbox-menu d-none">
        <button class="sg-toolbox-delete btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Delete'|trans}"><i class="bi bi-trash"></i></button>
        <button class="sg-toolbox-up btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Up'|trans}"><i class="bi bi-box-arrow-up"></i></button>
        <button class="sg-toolbox-down btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Down'|trans}"><i class="bi bi-box-arrow-down"></i></button>
        <button class="sg-toolbox-clone btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Clone'|trans}"><i class="bi bi-files"></i></button>
        <button class="sg-toolbox-edit-code btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Code'|trans}"><i class="bi bi-code-slash"></i></button>
        <button class="sg-toolbox-edit-attr btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Attributes'|trans}"><i class="bi bi-tags"></i></button>
        <button class="sg-toolbox-edit btn btn-sm border-0 p-0 mx-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Edit'|trans}"><i class="bi bi-pencil-square"></i></button>

        <button class="sg-toolbox-close btn btn-sm border-0 p-0 mx-1 d-none" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Close'|trans}" data-animation="false"><i class="bi bi-x-circle"></i></button>
      </span>
      <button class="sg-toolbox-open btn btn-sm border-0 p-0" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,20" title="{'Element'|trans}" data-animation="false"><i class="bi bi-three-dots"></i></button>
    </span>
  </div>

  <!-- Layout Sample -->
  <div class="sg-block-title">
    <textarea id="sg-layout-sample" class="d-none">
    {literal}
      <div id='content' class='container-md mycontent'>
       <div class='row'>
         <section id='custom1' class='sg-block-content'>This block uses section tag and does not reside in a column, we'll wrap it in a column</section>
         <div id='custom2' class='sg-block-content col-12'>This block is both a column and content wrapper, we'll wrap it in a column</div>
         <div class='col-12 col-sm-10 col-md-6 multiple'>
          <div class=' rowclass'>
            <nav id='block_logo' class='col-sm-4 px-0'>
              <div class='sg-block-content'>{block name='block_logo'} {$block_logo|default:$site.logo nofilter} {/block}</div>
            </nav>  
            <div id='block_header' class='col-sm-8'>
              <div class='sg-block-content'>{block name='block_header'} {$block_header nofilter} {/block}</div>
            </div>
          </div>
          <div class='row myclass2'>
            <div id='block_menu' class='col-sm-12'>
              <div class='sg-block-content'>{block name='block_menu'} {$block_menu nofilter} {/block}</div>
            </div>  
          </div>
          <div class=' rowclass'>
            <div class='container'>
              <div class='sg-block-content'>{block name='block_logo'} {$block_logo|default:$site.logo nofilter} {/block}
                  <div id='block_menu' class='col-sm-12'>
                    <div class='sg-block-content'>{block name='block_menu'} {$block_menu nofilter} {/block}</div>
                  </div>
              </div>
            </div>  
          </div>  
         </div>
         <div id='block_spotlight' class='whatever col-sm-12'>
           <div class='sg-block-content myclass'>{block name='block_spotlight'} {$block_spotlight nofilter} {/block}</div>   
         </div>
         <div class='col-sm-9'>
          <div id='block_top_wrapper' class='row wrapper'>
            <div id='block_top' class='col-sm-12'>
              <div class='sg-block-content'>{block name='block_top'} {$block_top nofilter} {/block}</div>
            </div>
            <div id='block_main' class='col-sm-12'>
              <div class='sg-block-content'>
                {block name='block_main'} {$block_main nofilter} {/block}
                {* You can place everything before this line into header.tpl and anything after this line to footer.tpl *}
              </div>  
            </div>
            <div id='block_bottom' class='col-sm-12'>
              <div class='sg-block-content'>{block name='block_bottom'} {$block_bottom nofilter} {/block}</div>
            </div>  
          </div>
         </div>
         <div class='col-sm-3'>
          <div class='row'>
            <div class='col-sm-12'>
             <div class='row'>
               <div id='block_left' class='col-sm-12'>
                  <div class='sg-block-content'>{block name='block_left'} {$block_left nofilter} {/block}</div>
               </div>
               <div id='block_1' class='col-sm-12'>
                <div class='sg-block-content'>{block name='block_1'} {$block_1 nofilter} {/block}</div>
               </div>
               <div id='block_right' class='col-sm-12'>
                <div class='sg-block-content'>{block name='block_right'} {$smarty.block.child} {$block_right nofilter} {/block}</div>
               </div>
             </div>
            </div>
          </div>
         </div>
       </div>
      </div>
      <div class='container-fluid mycontent'>
       <div class='row'>
         <div id='block_footnote' class='col-sm-12'>
           <div class='sg-block-content'>
             {block name='block_footnote'} {$block_footnote nofilter} {/block}
           </div>   
         </div>
         <div class='col-sm-12'>
          <div class='row'>
            <div id='block_footer' class='col-sm-12'>
              <div class='sg-block-content'>{block name='block_footer'} {$block_footer nofilter} {/block}</div>
            </div>
          </div>
         </div>
       </div>
      </div>
    {/literal}  
    </textarea>
  </div>

  <!-- Sidebar -->
  <!-- Main tab panes -->
  <div id="sg-sidebar" class="tab-content">
    <div class="tab-pane active" id="sg-editor__elements" role="tabpanel" aria-labelledby="tab-elements">      
      <!-- {* Tab Elements *} -->
      <div id="tab-elements">
        <div class="row">
          <div class="col px-0 position-fixed sg-sidebar-nav">
            <ul class="nav nav-pills px-0">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-label-default-elements" data-bs-toggle="tab" data-bs-target="#sg-blocks__default" type="button" role="tab" aria-controls="sg-blocks__default" aria-selected="true"><i class="bi bi-layout-wtf" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{'Elements'|trans}"></i></button>
              </li>
              {if $api.widgets}
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="label-widget-elements" data-bs-toggle="tab" data-bs-target="#sg-blocks__widgets" type="button" role="tab" aria-controls="sg-blocks__widgets" aria-selected="true"><i class="bi bi-journal-bookmark" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{'Widgets'|trans}"></i></button>
              </li>
              {/if}
              {if $api.snippets.template}
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="label-theme-elements" data-bs-toggle="tab" data-bs-target="#sg-blocks__theme" type="button" role="tab" aria-controls="sg-blocks__theme" aria-selected="true"><i class="bi bi-gift" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{'Theme Elements'|trans}"></i></button>
              </li>
              {/if}
            </ul> 
          </div>
        </div> 
        <!-- Element tab panes -->
        <div class="tab-content text-center">
          <div class="col-12 py-3">
            <div class="input-group bg-dark rounded">
              <button class="input-group-text border-dark bg-dark text-secondary"><i class="bi bi-search"></i></button> 
              <input id="sg-editor-search" class="form-control border-dark bg-dark text-lime px-1" type="text" placeholder="{'Find'|trans} ...">
              <button id="sg-editor-search-clear" class="input-group-text btn border-dark bg-dark text-secondary" type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{'Clear'|trans}"><i class="bi bi-x-lg"></i></button>
            </div>
          </div>
          <div class="tab-pane active" id="sg-blocks__default" role="tabpanel" aria-labelledby="tab-label-default-elements">  
          {foreach $api.snippets.system as $category}
            <section class="row row-cols-3">
              <div class="col-12 py-2 text-start small sg-sidebar-heading">{if $category.icon} {$category.icon nofilter} {/if} {$category.name}</div>
              {foreach $category.snippets as $snippet}
              <div class="sg-block-wrapper col">          
                <div class="sg-block-title" data-sg-sid="system__.{$system.template}.{$snippet.id}">{if $snippet.icon} {$snippet.icon nofilter} {/if} {$snippet.name}</div>
              </div>  
              {/foreach}
            </section> 
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
            <section class="row row-cols-3">
              <div class="col-12 py-2 text-start small sg-sidebar-heading">{if $category.icon} {$category.icon nofilter} {/if} {$category.name}</div>
              {foreach $category.snippets as $snippet}
              <div class="sg-block-wrapper col">          
                <div class="sg-block-title" data-sg-sid="{$site.template}.{$snippet.id}">{if $snippet.icon} {$snippet.icon nofilter} {/if} {$snippet.name}</div>
              </div>  
              {/foreach}
            </section> 
          {/foreach}
          </div>
        </div>  
      </div>  
    </div>
    <div class="tab-pane" id="sg-editor__editing" role="tabpanel" aria-labelledby="tab-editor">
      <div class="row position-fixed sg-sidebar-nav">
        <div class="col px-0">   
          <ul class="nav nav-pills px-0" role="tablist"> 
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="label-editor__text" data-bs-toggle="pill" data-bs-target="#sg-editor__text" type="button" role="tab" aria-controls="sg-editor__text" aria-selected="true"><i class="bi bi-textarea-t" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{'Content'|trans}"></i></button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="label-editor__attribute" data-bs-toggle="pill" data-bs-target="#sg-editor__attribute" type="button" role="tab" aria-controls="sg-editor__attribute" aria-selected="false"><i class="bi bi-aspect-ratio" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{'Attributes'|trans}"></i></button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="label-editor__effect" data-bs-toggle="pill" data-bs-target="#sg-editor__effect" type="button" role="tab" aria-controls="sg-editor__effect" aria-selected="false"><i class="bi bi-lightning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{'Effect'|trans}">fx</i></button>
            </li>
          </ul>
        </div>  
        <div class="col-auto px-0">
          <ul class="nav nav-pills px-0">
            <li class="nav-item" role="presentation">
              <button class="nav-link text-lime" id="sg-editor-done" data-bs-toggle="tab" data-bs-target="#sg-editor__elements" type="button" role="tab" aria-controls="sg-editor__elements" aria-selected="true"><i class="bi bi-check2-all" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{'Done'|trans}"></i></button>
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
                <button class="input-group-text border-dark bg-dark text-secondary sg-animation-repeat pe-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{'Repeat'|trans}"><i class="bi bi-arrow-repeat"></i></button> 
                <span class="input-group-text border-dark bg-dark text-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{'Current Effect'|trans}"><i class="bi bi-lightning"></i></span> 
                <input class="form-control border-dark bg-dark text-lime px-0" type="text" readonly>
                <button class="input-group-text sg-animation-none btn border-dark bg-dark text-secondary" type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{'Clear Effect'|trans}"><i class="bi bi-x-lg"></i></button>
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
  <!-- Additional style/script provided by template, sg-editor-resources used to load/unload resources-->
  <div class='sg-editor-resources'>
    {$api.snippets.resources nofilter}
  </div>

  <style type="text/css">
  .sg-block-wrapper {
    align-items: flex-start;
    background-color: #FFF;
    /*border: 1px solid transparent;*/
    padding-left: calc(var(--bs-gutter-x) * .5);
    padding-right: calc(var(--bs-gutter-x) * .5);
  }
  .sg-block-wrapper:not(.sg-show-preview.sg-show-content *) {
    border-radius: .25rem;
    box-shadow: 0 0 0 1px lightgrey;
  }  
  .sg-block-wrapper .sg-block-wrapper { 
    background-color: rgb(238,255,255);
  }
  .sg-block-wrapper .sg-block-wrapper .sg-block-wrapper { 
    background-color: rgb(238,255,215);
  }
  .sg-block-wrapper .sg-block-wrapper .sg-block-wrapper .sg-block-wrapper { 
    background-color: rgb(238,255,175);
  }
  .sg-block-wrapper .sg-block-wrapper .sg-block-wrapper .sg-block-wrapper .sg-block-wrapper { 
    background-color: #E9EFFF;
  }
  .sg-block-wrapper .sg-block-wrapper .sg-block-wrapper .sg-block-wrapper .sg-block-wrapper .sg-block-wrapper { 
    background-color: #F7FBFB;
  }
  .sg-block-wrapper:not(.card-disable)[data-type='row'],
  .sg-block-wrapper:not(.card-disable)[data-type^='container'] {
    background-color: #FFF;
  }
  .sg-block-wrapper[data-type='row'] > .sg-toolbox-overlay,
  .sg-block-wrapper[data-type^='container'] > .sg-toolbox-overlay {
    box-shadow: 0 0 0 2px violet;  
  }

  .sg-show-preview.sg-show-content .sg-block-wrapper {
    background-color: transparent !important;
  }
  #layout-editor.sg-show-preview.sg-show-content {
    background: #FFF !important;
  }
  .sg-show-preview.sg-show-content .sg-block-content[contenteditable="true"] {
    background-color: #FFF;
  } 
  
  .sg-show-preview:not(.sg-show-content) [data-type='content'] > .sg-block-title {
    display: block !important;
  }  

  #layout-editor > .sg-animation-repeat + .sg-block-wrapper > .sg-toolbox-overlay .badge,
  #layout-editor > .sg-block-wrapper:first-child > .sg-toolbox-overlay .badge {
    top: 0; 
  }  
  .row > .sg-toolbox-overlay .sg-toolbox-edit,
  .row > .sg-toolbox-overlay .sg-toolbox-edit-code,
  [data-type='row'] > .sg-toolbox-overlay .ui-resizable-e,
  [data-type^='container'] > .sg-toolbox-overlay .ui-resizable-e,
  .sg-show-preview .card-empty, 
  .sg-show-preview .layout-block-menu,
  .sg-show-preview .sg-block-title:not(.ui-draggable-dragging *) {
    display: none !important;
  }
  .sg-show-preview [data-type='container'], 
  .sg-show-preview [data-type='container-fluid'], 
  .sg-show-preview [data-type='row'],
  .sg-show-preview [data-type='block'] {
    padding-left: 0px !important;
    padding-right: 0px !important;
    margin-bottom: 0px !important;
    border: none !important;
  }

  .sg-show-preview .sg-block-wrapper {
    min-height: 60px;
    margin-bottom: 0px;
  }
  .sg-block-content {
    padding: 0;
    position: relative;
  }
  .sg-block-content[contenteditable='true']:not(.sg-edit-codeview) {
    padding: 10px;
  }  
  .sg-block-content:not(.sg-show-preview.sg-show-content *) {
    margin-bottom: 15px;
    background-color: #FFF !important; 
    cursor: default; 
  }
  /*.sg-show-preview.sg-show-content .sg-block-content[contenteditable="true"] {
    background-color: #FFF;
  }  
  .sg-edit-codeview {
    padding: 0 !important;
  }*/
  #layout-editor .row {
    margin-left: unset;
    margin-right: unset;
  }  
  .container, .container-fluid, .container-lg, .container-md, .container-sm, .container-xl, .container-xxl {
    margin-right: auto !important;
    margin-left: auto !important;
  }
  .sg-edit-buttons {
    width: auto;
    position: absolute;
    top: -15px; 
    right: -15px; 
    padding: .65em;
    z-index: 2022;
    cursor: pointer;
    color: #212529;
  }
  
  .note-editor.note-frame {
    z-index: 2000 !important;
    padding-left: 0;
    padding-right: 0;
    background-color: #FFF;
  }
  .note-fullscreen-body .sg-edit-buttons {
    position: fixed;
    top: 0;
  }
  .sg-fullscreen .sg-edit-buttons {
    top: 3px;
    right: 3px;
  }
  .content-pane {
    padding-bottom: 0;
  }
  /*.sg-toolbox-overlay .badge:not(.sg-show-preview.sg-show-content *) {
    padding: 3px 3px;
    border-bottom: 1px solid #ddd;
    border-left: 1px solid #ddd;
    border-radius: 0 5px;
  } */
  </style>
{/if}  