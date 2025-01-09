{extends "page.tpl"}

{block name="content_top"}
    <style type="text/css">
      .position-responsive {
        width: 100px;
        min-height: 50px;
        height: auto;
      }  
      @media (max-width: 767px){
        .position-responsive {
          position: absolute;
          top: 0;
          left: .5em;
          transform: translateY(-50%) !important;
        }
      }  
    </style>
    <div class="row mb-4">
      <div class="col-12 col-md px-0 px-md-3 order-md-last text-md-end" style="z-index: -1;">
        <img class="img-fluid w-100 rounded-2" src="{$page.image}" style="min-height: 100px; max-height: 250px; width:auto;" />
      </div>
      <div class="col-12 col-md position-relative ms-1 ps-2">
        <div class="row">
          {if $api.page.avatar}
          <div class="col-auto">
            <button class="btn position-responsive border-1 border-light rounded-3 bg-white p-1">
              <img class="img-fluid rounded-2" src="{$api.page.avatar}"/>
            </button> 
          </div>   
          {/if}
          <div class="col ms-5 ms-md-0 ps-5 ps-md-3 pt-3">
            <span class="fw-bold">{$page.name}</span>
            <div class="">{$page.meta.tagline}</div>
            <span class="">{'Location'|trans}: {$page.meta.country}. </span>
            {if $page.meta.website|truncate:4:'' eq http}
            <span class="">{'Website'|trans}: <a href="{$page.meta.website}">{$page.meta.website}</a></span>
            {/if}
            {if $page.public.like_count}
            {$stars = ($page.public.like_sum/$page.public.like_count)|truncate:3:''}
            <div class="mt-2 position-relative">
              <input class="sg-rating-display" type="range" max="5" step="0.5" value="{$stars}" style="--value:{$stars}"> 
              <label class="position-absolute top-0 m-1 "><a href="#main-tab" role="button" class="text-decoration-none">{$stars} ({$page.public.like_count})</a></label>
              <style type="text/css">
                /* Created by Mads Stoumann https://dev.to/madsstoumann/star-rating-using-a-single-input-i0l */
                .sg-rating-display {
                  --dir: right;
                  --fill: gold;
                  --fillbg: rgba(100, 100, 100, 0.15);
                  --star: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 17.25l-6.188 3.75 1.641-7.031-5.438-4.734 7.172-0.609 2.813-6.609 2.813 6.609 7.172 0.609-5.438 4.734 1.641 7.031z"/></svg>');
                  --stars: 5;
                  --starsize: 1.5rem;
                  --symbol: var(--star);
                  --value: 1;
                  --w: calc(var(--stars) * var(--starsize));
                  --x: calc(100% * (var(--value) / var(--stars)));
                  block-size: var(--starsize);
                  inline-size: var(--w);
                  position: relative;
                  touch-action: manipulation;
                  -webkit-appearance: none;
                }
                .sg-rating-display::-moz-range-track {
                  background: linear-gradient(to var(--dir), var(--fill) 0 var(--x), var(--fillbg) 0 var(--x));
                  block-size: 100%;
                  mask: repeat left center/var(--starsize) var(--symbol);
                }
                .sg-rating-display::-webkit-slider-runnable-track {
                  background: linear-gradient(to var(--dir), var(--fill) 0 var(--x), var(--fillbg) 0 var(--x));
                  block-size: 100%;
                  mask: repeat left center/var(--starsize) var(--symbol);
                  -webkit-mask: repeat left center/var(--starsize) var(--symbol);
                }
                .sg-rating-display::-moz-range-thumb {
                  height: var(--starsize);
                  opacity: 0;
                  width: var(--starsize);
                }
                .sg-rating-display::-webkit-slider-thumb {
                  height: var(--starsize);
                  opacity: 0;
                  width: var(--starsize);
                  -webkit-appearance: none;
                }        
              </style>                    
            </div>
            {/if}
            <a href="#main-tab" class="btn btn-sm btn-outline-secondary position-relative" data-bs-target="#app-Follow-tabpane" role="tab" aria-controls="app-Follow-tabpane" aria-selected="true" onclick=bootstrap.Tab.getOrCreateInstance(document.querySelector('.nav-link[data-bs-target="#app-Follow-tabpane"]')).show()>Follower</a>
            </div>  
          </div>  
        </div>  
      </div>  
    </div>    
    {$content_top nofilter} 
{/block}

{block name="content_bottom"}
  {function pagination}
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
            <li class="page-item"><a class="page-link" href="{$page.slug}?current={$api.current-2}{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}">{$api.current-2}</a></li>
        {/if}
        {if $api.current > 1}
            <li class="page-item"><a class="page-link" href="{$page.slug}?current={$api.current-1}{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}">{$api.current-1}</a></li>
        {/if}
        <li class="page-item active"><a class="page-link" href="{$page.slug}?current={$api.current}{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}">{$api.current}</a></li>
        {if $api.current < $pagination}
            <li class="page-item"><a class="page-link" href="{$page.slug}?current={$api.current+1}{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}">{$api.current+1}</a></li>
        {/if}
        {if $api.current == 1 AND $api.current < ($pagination - 1)}
            <li class="page-item"><a class="page-link" href="{$page.slug}?current={$api.current+2}{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}">{$api.current+2}</a></li>
        {/if}
        {if $api.current < ($pagination - 2)}
            <li class="page-item"><a class="page-link" href="#">...</a></li>
        {/if}
        {if $api.current < ($pagination - 1) AND $pagination > 3}    
            <li class="page-item {if $api.current == $pagination}disabled{/if}">
              <a class="page-link" href="{$page.slug}?current={$pagination}{if $html.searchPhrase}&searchPhrase={$html.searchPhrase|escape:'url'}{/if}" aria-label="Next">
                <span aria-hidden="true">{$pagination}</span>
              </a>
            </li>
        {/if}
      </ul>
    </nav>
  {/function}  
  {foreach $api.portfolio AS $app_name => $portfolio}
    {if $portfolio.rows}
    <div class="row mt-4 mb-3">
        <div class="col"><h5>{$app_name|trans}</h5></div>
        <div class="col-auto text-end">
        {if $portfolio.total > $portfolio.rowCount}
          {pagination api=$portfolio}
        {/if}    
        </div>
    </div>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mb-5">
    {foreach $portfolio.rows as $row}
      <div class='col js-sg-collection-item position-relative'>
          <div class='thumbnail card h-100'>
            {if $row.was > 0 AND $row.was > $row.price}
              <span class="sg-discount position-absolute top-0 my-2 z-3 badge rounded-0 rounded-end bg-danger">-{(100*(1-$row.price/$row.was))|truncate:2:''}%</span>
          {/if}   
            <a class="sg-img-container text-decoration-none mx-auto position-relative overflow-hidden" href="{$row.slug}">
              <img class="img-fluid card-img-top" src='{$row.image}' alt="" />
              {if $row.variants.0.images.0}
              <img class="img-fluid card-img-top position-absolute top-0 start-0 z-1" src="{$row.variants.0.images.0}" alt="" />
              {/if}
            </a>
            <div class="card-body align-items-end d-flex p-2 mx-auto">                                
            </div>
            <div class="card-footer px-3 bg-white border-top-0">
              <div class="row">
                <div class="col">
                  <a class="fw-bold text-decoration-none pe-2" href="{$row.slug}">{$row.name}</a> 
                  {if $row.price > 0}<span class="js-sg-currency fw-bold">{$row.price}</span>
                    {if $row.was > $row.price}(<span class="js-sg-currency text-decoration-line-through">{$row.was}</span>){/if}
                  {/if}
                </div>
              </div>
              {if $row.description} 
              <div class="row sg-description">
                <div class="col">
                  {$row.description}
                </div>
              </div>
              {/if}
            </div>
          </div>
      </div>
    {/foreach}
    </div>
    {/if}
  {/foreach}
{/block}

{block name="block_bottom"}
  {if $api.collections_items}
  <div class="col-12 col-md-3 mb-3 pt-3">
    <h6 class="ps-3 pb-2">{'Related Topics'|trans}</h6>
    <ul class="list-group">
    {foreach $api.collections_items AS $item}
      <a href="{$collection.slug}" class="list-group-item d-flex justify-content-between align-items-center">
        {$item.name}
        {if $item.quantity}
          <span class="badge bg-primary rounded-pill">{$item.quantity}</span>
        {/if}  
      </a>
    {/foreach}  
    </ul>
  </div>
  {/if} 
  {$block_bottom nofilter}  
{/block} 