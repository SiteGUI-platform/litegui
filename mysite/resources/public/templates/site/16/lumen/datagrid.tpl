<style type="text/css">
  .js-sg-collection-item, .js-sg-collection-item.list-view img {
      -webkit-transition: all 0.5s ease-in-out;
      -moz-transition: all 0.5s ease-in-out;
      -o-transition: all 0.5s ease-in-out;
      transition: all 0.5s ease-in-out;
  }
  .js-sg-collection-item.list-view {
      width: 100%;
      background: transparent !important;
      -ms-flex: 0 0 100%;
      flex: 0 0 100%;
      max-width: 100%;
  }

  .js-sg-collection-item.list-view .thumbnail {
      -ms-flex-direction: row;
      flex-direction: row;
  }
  .js-sg-collection-item .sg-selection:not(.list-view *) {
      display: flex;
  }
  .js-sg-collection-item .sg-img-container {
      /*min-height: 200px;
      max-height: 200px;*/
      width: auto;
  }
  .js-sg-collection-item.list-view .sg-img-container {
      min-width: 25vw;
      max-width: 25vw;
      max-height: fit-content;
      margin-right: 1em !important;
  }  

  .js-sg-collection-item img[src=""]{
    display: none;
  }
  
  .js-sg-collection-item.list-view .caption,
  .js-sg-collection-item.list-view .card-footer {
      /*float: left;*/
      width: 85%;
  }
  .js-sg-collection-item.list-view .card-img-top {
      border-radius: 0 !important;
  }
  .js-sg-collection-item.list-view .sg-description {
    display: block !important;
  }
  .js-sg-collection-item.list-view .card,
  .sg-img-container:not(.list-view *) {
    border: 0 !important;
  }   
  .thumbnail .hover-visible {
    visibility: hidden;
    position:absolute; 
    right:0; 
    bottom:0; 
    /*transform: translate(-50%, -50%);*/  
  }
  .list-view .thumbnail .hover-visible {
    right: unset;
    left: 50%;
  }  
  .list-view .card-body {
    padding-top: 0;
  }
  .thumbnail:hover .hover-visible {
    visibility: visible;
    transition-delay: .3s;
  }
  .thumbnail img {
    opacity: .95;
    -webkit-transition: all .6s,-webkit-filter .6s,-webkit-transform .6s,-webkit-box-shadow .3s;
    transition: all .6s,-webkit-filter .6s,-webkit-transform .6s,-webkit-box-shadow .3s;
    -o-transition: filter .6s,all .6s,transform .6s,box-shadow .3s;
    transition: filter .6s,all .6s,transform .6s,box-shadow .3s;
    transition: filter .6s,all .6s,transform .6s,box-shadow .3s,-webkit-filter .6s,-webkit-transform .6s,-webkit-box-shadow .3s;
  }
  .thumbnail img + img,
  .thumbnail:hover img {
    opacity: 0;
  } 
  .thumbnail:hover img + img,
  .thumbnail:hover img:only-child,
  .thumbnail .btn-secondary {
    opacity: 1;    
  }
  @media (max-width: 575px){  
    .js-sg-collection-item {
        border-bottom: solid 1px #EEE;
    }
  }    
</style>
<script type="text/javascript">
  document.addEventListener("DOMContentLoaded", function(e){
    //show app image when it near the top
    document.getElementById('list').addEventListener('click', function(event){
      event.preventDefault();
      document.querySelectorAll('.view-container .js-sg-collection-item').forEach(function(el) {
        el.classList.add('list-view');
      })  
    })
    document.getElementById('grid').addEventListener('click', function(event){
      event.preventDefault();
      document.querySelectorAll('.view-container .js-sg-collection-item').forEach(function(el) {
        el.classList.remove('list-view');
      })  
    })
    //touch devices
    if ( ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0) ){
        document.querySelectorAll('.hover-visible').forEach(function(el) {
            el.classList.remove('hover-visible');
        })
    }
  });       
</script>
{block name="content_header"}{$content_header nofilter}{/block}  
<div class="container-md">
    {block name="content_spotlight"}{$content_spotlight nofilter}{/block}
    <div class="row">
        {block name="content_left"}{$content_left nofilter}{/block}
        <div class="col-12 col-md">
            {block name="content_top"}{$content_top nofilter}{/block}
            {*<!-- grid_body -->*}
            <div class="row row-cols-1 row-cols-sm-{($grid_column|default:$html.grid_column|default:4)/2|truncate:1:''} row-cols-md-{$grid_column|default:$html.grid_column|default:4} g-4 mb-3 view-container">
            {foreach $api.rows as $row}
                {block name="grid_item"}
                <div class="col js-sg-collection-item" data-filter="{$row.subtype|lower}">
                    <div class="thumbnail card h-100">
                        <a class="sg-img-container border text-decoration-none mx-auto position-relative overflow-hidden" href="{$row.slug}">
                            <img class="img-fluid card-img-top" src='{$row.image}' alt="" />
                        </a>    
                        <div class="card-body p-0"></div>
                        <div class="caption card-footer py-3 bg-white border-top-0">
                            <h5 class="card-title">
                                {if $links.edit}<a href="{$links.edit}/{$row.id}">{$row.name}</a>
                                {else}{$row.name}{/if}
                                <small class="text-muted">{$row.subtype}</small>
                            </h5>
                            <div class="row">
                                <div class="col-12 col-md-3">
                                    <p class="lead">{$row.price}</p>
                                </div>
                                <div class="col-12 col-md-9 text-end">
                                    {foreach $links as $action => $link}
                                        {foreach $row.variants as $variant}
                                            <button type="button" class="btn btn-sm btn-success" data-url="{$link}" data-confirm="{$action}" data-name="id" data-value="{$row.id}.{$variant.id}" class="btn-group">
                                                {if $variant.price}
                                                    {$action|capitalize} | ${$variant.price}
                                                {else}
                                                    {'Free'|trans}
                                                {/if}    
                                            </button>
                                        {/foreach}
                                    {/foreach}    
                                </div>
                            </div>
                        </div>               
                    </div>
                </div>
                {/block}    
            {foreachelse}
                <div class="col text-center">{'No results found'|trans}!</div>
            {/foreach}
            </div>
            {block name="content_bottom"}{$content_bottom nofilter}{/block}
            {*<!-- grid_footer -->*}
            <div class="card mb-4 border-0">
                <div class="card-body row align-items-center">
                    <div class="col mt-1">
                    {if $api.rowCount}
                        {*<!-- Pagination -->*}
                        <nav aria-label="Pagination">
                          <ul class="pagination pagination-sm mb-0">
                            {$pagination = (int) (($api.total-1)/$api.rowCount) + 1}
                            {if $api.current > 2}
                            <li class="page-item {if $api.current == 1}disabled{/if}">
                              <a class="page-link" href="{$api.page.slug}?current=1{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}" aria-label="Previous">
                                <span aria-hidden="true">1</span>
                              </a>
                            </li>
                            {/if}
                            {if $api.current > 3}
                            <li class="page-item"><a class="page-link" href="#">...</a></li>
                            {/if}
                            {if $api.current > ($pagination - 1) AND $api.current > 3}
                                <li class="page-item"><a class="page-link" href="{$api.page.slug}?current={$api.current-2}{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}">{$api.current-2}</a></li>
                            {/if}
                            {if $api.current > 1}
                                <li class="page-item"><a class="page-link" href="{$api.page.slug}?current={$api.current-1}{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}">{$api.current-1}</a></li>
                            {/if}
                            <li class="page-item active"><a class="page-link" href="{$api.page.slug}?current={$api.current}{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}">{$api.current}</a></li>
                            {if $api.current < $pagination}
                                <li class="page-item"><a class="page-link" href="{$api.page.slug}?current={$api.current+1}{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}">{$api.current+1}</a></li>
                            {/if}
                            {if $api.current == 1 AND $api.current < ($pagination - 1)}
                                <li class="page-item"><a class="page-link" href="{$api.page.slug}?current={$api.current+2}{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}">{$api.current+2}</a></li>
                            {/if}
                            {if $api.current < ($pagination - 2)}
                                <li class="page-item"><a class="page-link" href="#">...</a></li>
                            {/if}
                            {if $api.current < ($pagination - 1) AND $pagination > 3}    
                                <li class="page-item {if $api.current == $pagination}disabled{/if}">
                                  <a class="page-link" href="{$api.page.slug}?current={$pagination}{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}" aria-label="Next">
                                    <span aria-hidden="true">{$pagination}</span>
                                  </a>
                                </li>
                            {/if}
                          </ul>
                        </nav>
                    {/if}    
                    </div>
                    {block name="grid_menu"}
                    {if $html.app_menu}
                    <div class="col">
                        <ul class="nav nav-pills" role="tablist">
                            {foreach $html.app_menu as $level1}
                                <li class="nav-item" role="presentation">
                                    <a class="text-sm-center nav-link" aria-current="page" href="{$level1.slug|default: '#'}">
                                    {if $level1.icon}
                                        <i class="{$level1.icon}"></i>
                                    {/if}
                                        {$level1.name}
                                    </a>
                                </li>    
                            {/foreach}
                        </ul>
                    </div>
                    {/if}
                    {/block}  
                    <div class="col-auto text-end">
                        <span class="d-none d-sm-inline pb-1">{'Display'|trans}</span>
                        <div class="btn-group">
                            <a href="#" id="list" class="btn btn-outline-secondary btn-sm pt-1"><span class="bi bi-list-stars">
                            </span> {'List'|trans}</a> <a href="#" id="grid" class="btn btn-outline-secondary btn-sm pt-1"><span
                                class="bi bi-grid"></span> {'Grid'|trans}</a>
                        </div>
                    </div>
                </div>        
            </div>
        </div>    
        {block name="content_right"}{$content_right nofilter}{/block}
    </div>    
    {block name="content_footnote"}{$content_footnote nofilter}{/block}
</div>
{block name="content_footer"}{$content_footer nofilter}{/block}