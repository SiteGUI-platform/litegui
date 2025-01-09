{$page = $api.page}
{$app  = $api.app}
<div id="content" class="container-md">
    <div class="col-12 px-0">
        {if $page.breadcrumb}
        <nav class="text-left d-none d-sm-block" aria-label="breadcrumb">  <ol class="breadcrumb bg-transparent border-0 ps-0">
            <li class="breadcrumb-item"><a href="/"><i class="bi bi-house"></i></a></li>
            {foreach $html.breadcrumbs AS $breadcrumb}
        	<li class="breadcrumb-item"><a href="{$breadcrumb.slug}">{$breadcrumb.name}</a></li>
        	{/foreach}
            <li class="breadcrumb-item active" aria-current="page">{$page.name}</li>
          </ol>
        </nav>	
        {/if}
        <div class="row">
            {block name="content_head"}{$content_head nofilter}{/block}
            {if $api.variants}
            <div class="col-sm px-0 ps-sm-3 pe-md-4 py-2">
                <div id="product-main-carousel" class="carousel slide carousel-dark carousel-fade js-sg-zoom" {if !$api.page.meta.video}data-bs-ride="carousel"{/if}>
                    <button type="button" class="js-sg-zoom-close-btn d-none position-fixed top-0 end-0 btn text-secondary fs-5">✕</button>
                    <div class='carousel-outer'>  
                        <div class="carousel-inner rounded-2">
                        {$i = 0}    
                        {if $api.page.meta.video}
                            <div class="carousel-item {if !$indicators}active{/if}">
                                <div class="ratio mx-auto" style="--bs-aspect-ratio: calc(4/3* 100%); max-width: calc(3/4*100vh)">
                                    <iframe src="{$api.page.meta.video}" class="w-100" style="min-width: auto; max-width:100%; max-height: 100vh;" allowfullscreen></iframe>
                                </div>
                            </div>
                            {$indicators = $indicators|cat:"<button type='button' data-bs-target='#product-main-carousel' data-bs-slide-to={$i++} class='{if !$indicators}active{/if}' aria-label='Slide {$i}'></button>"}
                        {/if}
                        {if $api.page.image}
                            <div class="carousel-item {if !$indicators}active{/if}">
                              <img src="{$api.page.image}" class="d-block position-relative mx-auto" style="min-width: auto; max-width:100%; height:auto; max-height: 100vh;">
                            </div>
                            {$indicators = $indicators|cat:"<button type='button' data-bs-target='#product-main-carousel' data-bs-slide-to={$i++} class='{if !$indicators}active{/if}' aria-label='Slide {$i}'></button>"}
                        {/if}
                        {foreach $api.variants AS $index => $variant}
                            {if $variant.price < 0}{continue}{/if}
                            {$prices = $prices|cat: "<h5 class='js-sg-price {$variant.options_hex} {if $variant.stock gt 0}stocked{/if} {if $variant@index > 0}d-none{/if}' data-varid='{$variant.id}'><span class='js-sg-currency'>{$variant.price}</span> {if $variant.was > 0 AND $variant.was > $variant.price}(<span class='js-sg-currency text-decoration-line-through text-secondary'>{$variant.was}</span> -{(100*(1-$variant.price/$variant.was))|truncate:2:''}%){/if}</h5>"}
                            {foreach $variant.options AS $key => $opt}
                                {*if $opt@index > 2}{break}{/if*}
                                {if $opt@first AND $opt != (int) $opt}
                                    {if $variant.images.0}
                                        {$thumbs.$opt = $variant.images.0}
                                    {/if}    
                                    {$first_opts = $first_opts|cat: "<span class='d-none js-first-opt {$variant.options_hex}'>{$opt}</span>"}
                                {/if}

                                {$filters.$key.$opt = "opt-{$opt@iteration}-{$opt|escape:'hex'|replace:'%':''}-x"}
                            {/foreach}

                            {foreach $variant.images AS $img}
                                <div class="carousel-item {$variant.options_hex} {if !$indicators}active{/if}">
                                  <img src="{$img}" class="d-block position-relative mx-auto" style="min-width: auto; max-width:100%; height:auto; max-height: 100vh;">
                                </div>
                                {$indicators = $indicators|cat:"<button type='button' data-bs-target='#product-main-carousel' data-bs-slide-to={$i++} class='{$variant.options_hex} {if !$indicators}active{/if}' aria-label='Slide {$i}'></button>"}
                            {/foreach}
                        {/foreach}
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#product-main-carousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#product-main-carousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                    <div class="carousel-indicators mb-0">
                        {$indicators nofilter}    
                    </div>
                </div>
                {block name="content_left"}{$content_left nofilter}{/block}  
            </div>    
            {/if}
            <div class="col-sm pt-md-4">
                {block name="content_header"}{$content_header nofilter}{/block}
                <h2>{$page.name}</h2>
                {if $page.public.like_count}
                    {$stars = ($page.public.like_sum/$page.public.like_count)|truncate:3:''}
                    <div class="my-2 position-relative">
                      <input class="sg-rating-display" type="range" max="5" step="0.5" value="{$stars}" style="--value:{$stars}"> 
                      <label class="position-absolute top-0 m-1 "><a href="#main-tab" class="text-decoration-none">{$stars} ({$page.public.like_count})</a></label>
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

                {block name="content_spotlight"}{$content_spotlight nofilter}{/block}
                {$prices nofilter}
                {foreach $filters AS $key => $filter}
                    <h6>
                    {foreach $filter AS $opt => $select}
                        {*$select = "opt-{$filter@iteration}-{$opt|escape:'hex'|replace:'%':''}-x"*}
                        {if $select@first}
                            <span class="me-3">{$key|trans}: </span>
                            {if $filter@first}
                                {$first_opts nofilter}
                            {/if}
                            <br>
                        {/if}
                        <button 
                            data-row="opt-{$filter@iteration}" 
                            data-select="{$select}" 
                            class="btn btn-sm border-1 border-dark my-2 position-relative" 
                            {if $filter@first AND !$html.text_selection}
                                style="padding: 2px;">
                                {if $thumbs.$opt}
                                    <img src="{$thumbs.$opt}" style="height: 42px;">
                                {else}
                                    <span class="align-items-center d-flex px-2" style="height: 42px;">{$opt}</span>
                                {/if}    
                                <svg class="opacity-0 position-absolute top-0 start-0 w-100 h-100" viewBox="0 0 10 10" preserveAspectRatio="none">
                                    <line x1="10" y1="0" x2="0" y2="10" stroke="black" stroke-width=".3" stroke-opacity="0.5"></line>
                                </svg>
                            {else}
                                >{$opt}
                            {/if}                            
                        </button>                    
                    {/foreach}
                    </h6>
                {/foreach}
                {block name="content_right"}{$content_right nofilter}{/block}
                {if $api.variants}
                <form action="{$links.cart_add}" method="POST">
                    <input type="hidden" name="item[id]" value="{$api.variants.0.id}" id="js-varid-input" class="{if $html.stock_checking}js-require-stock{/if}">
                    <input type="hidden" name="csrf_token" value="{$token}">
                    <button type="submit" class="btn btn-sm btn-warning my-3 me-3" id="js-cart-add" {if $html.stock_checking && $api.variants.0.stock lt 1}disabled{/if}>{'Add To Cart'|trans}</button>
                    {if $html.check_availability}
                    <button type="button" class="btn btn-sm btn-secondary my-3" id="js-check-availability">{'Check Stock Availability'|trans}</button>
                    <div class="collapse" id="collapse-availability">
                      <div class="card card-body px-0 mb-3" style="max-height:50vh; overflow: scroll;">
                        <button type="button" class="btn text-secondary fs-6 text-end position-absolute top-0 end-0" data-bs-toggle="collapse" data-bs-target="#collapse-availability">✕</button>
                        <ul class="list-group list-group-flush border-0">
                        </ul>  
                        <li class="sg-template list-group-item d-flex d-none justify-content-between align-items-start py-3">
                            <div class="ms-2 me-auto">
                              <div class="fw-bold sg-store-name"></div>
                              <div class="sg-store-address"></div>
                            </div>
                            <span class="badge text-bg-success rounded-pill sg-stock"></span>
                        </li>
                      </div>
                    </div>
                    {/if}
                </form>
                {/if} 
                {block name="content_top"}{$content_top nofilter}{/block}   
                {$page.content nofilter}
                <p class="mt-3">
                {foreach $api.collections AS $collection}
                    <a href="{$collection.slug}">{$collection.name}</a>{if !$collection@last}, {/if}
                {/foreach}
                </p>
                {block name="content_bottom"}{$content_bottom nofilter}{/block}
            </div>
        </div>  
        {if $page.meta.faq}
        <div class="row pb-4">
            <div class="col-12 pt-3 pb-2"><h6>{'FAQ'|trans}</h6></div>
            <div class="col-12">
                <div class="accordion" id="sg-faq" style="--bs-accordion-active-bg: rgb(98,87,255); --bs-accordion-active-color: white;">
                    {foreach $page.meta.faq AS $index => $faq}    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                          <button class="accordion-button fw-bolder shadow-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sg-faq-{$index}" aria-expanded="false" aria-controls="collapse1">
                            <i class="bi bi-bookmark me-2"></i>{$faq.question}</button>
                        </h2>
                        <div id="sg-faq-{$index}" class="accordion-collapse collapse" data-bs-parent="#sg-faq" style="">
                          <div class="accordion-body py-5">{$faq.answer}</div>
                        </div>
                    </div>
                    {/foreach}    
                </div>
            </div>
        </div>        
        {/if}    
        {block name="content_footnote"}{$content_footnote nofilter}{/block}
        {*<!-- related items -->*}
        {if $api.collections_items OR $page.meta.related}
        <div class="row py-4">
            <div class="col-12 text-center"><h5>{'You May Also Like'|trans}</h5></div>
        </div>    
        <div class="row row-cols-1 row-cols-md-4 g-3 mb-4 view-container">
        {function relatedProduct}    
            <div class="col js-sg-collection-item" data-filter="{$row.subtype|lower}">
            <div class="thumbnail card rounded-bottom-0 h-100">
              {if $row.was > 0 AND $row.was > $row.price}
                <span class="sg-discount position-absolute top-0 my-2 z-3 badge rounded-0 rounded-end bg-danger">-{(100*(1-$row.price/$row.was))|truncate:2:''}%</span>
              {/if} 
              <a class="sg-img-container text-decoration-none mx-auto position-relative overflow-hidden" href="{$row.slug}">
                <img class="img-fluid card-img-top {if !$row.image AND !$row.variants.0.images.0}no-image{/if}" src='{$row.image|default:"{$row.variants.0.images.0}"|default:"https://ui-avatars.com/api/?size=80&rounded=1&length=4&font-size=.3&bold=1&background=random&b8ea86&color=417505&name={$row.name}"}' alt="" />{*hide on hover*}
                {if $row.variants.0.images.0}
                <img class="img-fluid card-img-top position-absolute top-0 start-0 z-1" src="{$row.variants.0.images.0}" alt="" />
                {/if}
              </a>
              <div class="card-body align-items-end d-flex p-2 mx-auto">
              {if $row.variants AND $row.variants|count > 1}  
                {$seen = []}
                {foreach $row.variants AS $variant}
                  {foreach $variant.options AS $option}
                    {$option2 = $option|replace:' ':'-'|lower}
                    {if $option@index eq 0 AND !$seen[ $option2 ] AND $variant.images.0} {$seen[ $option2 ] = 1}
                      <span class="js-sg-variant-selection variant-{$option2}" title="{$option}" {if $variant.images.0}data-src="{$variant.images.0}"{/if}></span>
                    {/if}
                  {/foreach}
                {/foreach} 
              {/if}                                       
              </div>
              <div class="card-footer px-3 bg-white border-top-0">
                <div class="row">
                  <div class="col">
                    <a class="fw-bold text-decoration-none pe-2" href="{$row.slug}">{$row.name}</a> 
                    {if $row.price > 0}<span class="js-sg-currency fw-bold">{$row.price}</span>
                      {if $row.was > $row.price}(<span class="js-sg-currency text-decoration-line-through">{$row.was}</span>){/if}
                    {else} {'Free'|trans}{/if}
                  </div>
                </div> 
                <div class="row d-none sg-description">
                  <div class="col">
                    {$row.description}
                  </div>
                </div> 
                <div class="row">
                {if $row.price} 
                  <div class="col pt-2 text-center">
                    <div class="btn-group hover-visible">
                    {if $row.variants|count > 1}
                      <button type="button" class="btn btn-warning text-white dropdown-toggle m-2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{'Order'|trans} <span class="caret"></span>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end float-end">
                        <div class="row mx-1 flex-sm-nowrap">
                          {foreach $row.variants as $variant}
                            {if $variant@index % 6 == 0}<div class="col">{/if}
                                <form action="{$links.cart_add}" method="POST" class="text-end">
                                  <input type="hidden" name="item[id]" value="{$variant.id}">
                                  <input type="hidden" name="csrf_token" value="{$token}">
                                  <button type="submit" class="btn btn-sm text-nowrap border-0 m-2" {if $html.stock_checking && $variant.stock lt 1}disabled{/if}>
                                    {if $variant.options}
                                      {foreach $variant.options AS $key => $option}
                                        {$option|trans} - 
                                      {/foreach}
                                    {/if}
                                    {if $variant.price > 0} <span class="js-sg-currency">{$variant.price}</span> {else} {'Free'|trans} {/if}
                                  </button>
                                </form> 
                            {if $variant@index % 6 == 5 OR $variant@last}</div>{/if}
                          {/foreach}
                        </div>  
                      </div>
                    {else}
                      <form action="{$links.cart_add}" method="POST">
                        <input type="hidden" name="item[id]" value="{$row.variants.0.id}">
                        <input type="hidden" name="csrf_token" value="{$token}">
                        <button type="submit" class="btn btn-warning text-white m-2" {if $html.stock_checking && $row.variants.0.stock lt 1}disabled{/if}>{'Order'|trans}</button>
                      </form>                             
                    {/if}  
                    </div>
                  </div>
                {/if}
                </div>
              </div>
            </div>
            </div> 
        {/function}    
        {foreach $page.meta.related as $row}
            {relatedProduct row=$row}
        {/foreach}
        {foreach $api.collections_items as $row}
            {relatedProduct row=$row}
        {/foreach}
        </div>
        {/if}
    </div>

    {if $api.subapp}
    <div class='row mx-md-0 mt-4' role="tabpanel">
        <!-- Nav tabs -->
        <ul id="main-tab" class="nav nav-tabs px-3" role="tablist">
            {foreach $api.subapp AS $name => $subapp}
                {if ! $subapp.hide.tabapp }
                  <li class="nav-item {if $api.app.sub.$name.entry eq quick AND $user.id}ms-auto order-last{/if}">
                    <button type="button" class="nav-link position-relative mt-0 {if $api.app.sub.$name.entry eq quick AND $user.id}border-0 js-sg-quick-engagement{/if} {if $subapp@first}active{/if}" data-bs-toggle="tab" data-bs-target="#app-{$name}-tabpane" role="tab" aria-controls="app-{$name}-tabpane" aria-selected="false">{$api.app.sub.$name.alias|default:($name|replace:'_':' ')|trans}</button>   
                  </li> 
                {/if}
            {/foreach}
        </ul>
        <style type="text/css">
            #main-tab .nav-link {
                height: 2.7rem;
                margin-top: 0.3rem !important;
                transition: all .2s ease-in-out;
            }
            #main-tab .nav-link.active,
            #main-tab .nav-link:hover {
                height: 3rem;
                margin-top: 0 !important;
                padding-bottom: .5rem !important;
            }
        </style>
        <!-- Tab panes -->
        <div id="main-tab-content" class="sg-main tab-content pt-3 px-md-0">
            {foreach $api.subapp AS $name => $subapp}
                {if ! $subapp.hide.tabapp }
                    <div id="app-{$name}-tabpane" class="sg-form sg-sub tab-pane fade {if $subapp@first}show active{/if}" role="tabpanel"> 
                    {if $api.page.id}   
                        {include "datatable.tpl" forapp=$name}
                        {if  $api.app.sub.$name.entry == multiple OR $api.app.sub.$name.entry == single OR 
                             $api.app.sub.$name.entry == quick OR 
                            ($api.app.sub.$name.entry == creator_readonly AND $api.page.creator AND $api.page.creator != $user.id) OR  
                            ($api.app.sub.$name.entry == other_readonly AND $api.page.creator == $user.id) 
                        } {* display input fields if not readonly *}
                            <form action="{$links.update}" method="post" enctype="multipart/form-data">
                                <div class="text-center pb-5">
                                  <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-sub-{$name}" aria-expanded="false" aria-controls="collapse-sub-{$name}">{"New :item"|trans:['item' => $api.app.sub.$name.alias|default:($name|replace:'_':' ')]}</button>
                                </div>  
                                <div class="collapse pb-5" id="collapse-sub-{$name}">
                                {if $user.id}    
                                    {include "form_field.tpl" formFields=$subapp.fields fieldPrefix="page[sub][$name][fields]"}
                                    {include "form_field.tpl" formFields=$subapp.show   fieldPrefix="page[sub][$name]"}
                                    <div class="text-center">
                                        <input type="hidden" name="page[id]" value="{$api.page.id}">    
                                        <input type="hidden" name="page[subtype]" value="{$api.page.subtype}">
                                        <input type="hidden" name="csrf_token" value="{$api.page.id}sg{$token}">
                                        <button type="submit" name="save-btn" id="sg-btn-save" class="btn btn-outline-primary my-2" {$onclick}><i class="bi bi-save pe-2"></i>  {'Submit'|trans}</button>
                                    </div>
                                {else}
                                    <div class="text-center">
                                        <a href="https://{$site.account_url}/account/?oauth=sso&login=step2&token={$token}{$site.id}&requester={$html.sso_requester}" class="btn btn-outline-primary my-2">{'Sign In'|trans}</a>
                                    </div>                                    
                                {/if}    
                                </div>
                            </form>    
                        {/if}
                    {/if}   
                    </div>  
                {/if}
            {/foreach}
        </div>    
    </div>            
    {/if}
    {block name="content_footer"}{$content_footer nofilter}{/block}
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(e){  
    $_ = document.querySelector.bind(document)
    $$ = document.querySelectorAll.bind(document)  
    var updateButtons = function(){
        //resolve selected options
        var selected = {}
        var selector = ''
        $$('[data-row][data-select].active').forEach(el => {
            selected[ el.dataset.row ] = el.dataset.select
            selector += '.'+ el.dataset.select
        })     
        //check each button
        var stocked = $_('#js-varid-input.js-require-stock')? '.stocked' : '' //stock checking option
        var buttons = $$('[data-row][data-select]')
        if (buttons.length > 1){ //multiple choice - disable buttons until selection is reduced to 1
            $_('#js-varid-input').setAttribute('value', '')
            $_('#js-cart-add').disabled = true
            $_('#js-check-availability') && ($_('#js-check-availability').disabled = true) 
        }
        buttons.forEach(el => {
            var test = stocked +'.'+ el.dataset.select
            Object.keys(selected).forEach(row => {
                if (row != el.dataset.row){ //exclude selected value of current row
                    test += '.'+ selected[ row ]
                }    
            })
            //console.log(test)
            if ( ! $_('[data-varid]'+ test) ){
                el.disabled = true
                el.classList.add('text-decoration-line-through')
            }
        })
        if (selector) {
            //set selector item active        
            if ($_('#product-main-carousel .carousel-item'+ selector) ){
                //hide carousel items
                $$('#product-main-carousel .carousel-item:not('+ selector +')').forEach(hideEl => {
                    hideEl.classList.replace('carousel-item', 'carousel-item-hide')
                    hideEl.classList.add('d-none')
                })    
                $$('#product-main-carousel [data-bs-slide-to]:not('+ selector +')').forEach(hideEl => {
                    hideEl.classList.add('d-none')
                }) 
                $_('#product-main-carousel .carousel-item.active') && $_('#product-main-carousel .carousel-item.active').classList.remove('active')
                $_('#product-main-carousel .carousel-item'+ selector).classList.add('active')
                $_('#product-main-carousel [data-bs-slide-to].active') && $_('#product-main-carousel [data-bs-slide-to].active').classList.remove('active')
                $_('#product-main-carousel [data-bs-slide-to]'+ selector) && $_('#product-main-carousel [data-bs-slide-to]'+ selector).classList.add('active')            
            } 
            //display first option's text when any option is chosen (not just the 1st opt)
            $$('.js-first-opt:not(.d-none)').forEach(el => el.classList.add('d-none') )
            $_('.js-first-opt' + selector) && $_('.js-first-opt' + selector).classList.remove('d-none') //first match only   
            //display price
            $_('[data-varid].js-sg-price:not(.d-none)') && $_('[data-varid].js-sg-price:not(.d-none)').classList.add('d-none')
            selector = $$('[data-varid].js-sg-price' + selector + stocked )
            if (selector) {
                //just show the first matched (stocked) varid's price but not select unless it is the single choice
                selector[0].classList.remove('d-none')
                $_('#js-cart-add').disabled = (selector.length != 1)
                $_('#js-check-availability') && ($_('#js-check-availability').disabled = (selector.length != 1))  
                $_('#js-varid-input').setAttribute('value', (selector.length == 1)? selector[0].getAttribute('data-varid') : '')
            }
        }
    }     
    //set click handler
    $$('[data-row][data-select]').forEach(el => el.addEventListener('click', function(ev){
        //console.log(ev, ev.target.dataset.select)
        //set choosen btn active
        $$('[data-row='+ el.dataset.row +']').forEach(key => {
            key != el && key.classList.remove('active', 'btn-outline-warning')
        })    
        el.classList.toggle('active')
        el.classList.toggle('btn-outline-warning')

        //clear previous hidden carousel items
        $$('#product-main-carousel .carousel-item-hide').forEach(hideEl => {
            hideEl.classList.replace('carousel-item-hide', 'carousel-item')
            hideEl.classList.remove('d-none', 'active')
        })
        $$('#product-main-carousel [data-bs-slide-to].d-none').forEach(hideEl => {
            hideEl.classList.remove('d-none', 'active')
        })
        //clear disabled btns 
        $$('[data-row].text-decoration-line-through').forEach(hideEl => {
            hideEl.disabled = false
            hideEl.classList.remove('text-decoration-line-through')
        }) 

        updateButtons()
    }))        
    updateButtons() //initialize (disable out of stock button for example)

    // check valid varid before add to cart
    $_('#js-cart-add').addEventListener('click', ev => {
        if ( ! $_('#js-varid-input').getAttribute('value') ){
            alert(Sitegui.trans('Please select an option'))
            ev.preventDefault()
        }
    })

    $_('#js-check-availability') && $_('#js-check-availability').addEventListener('click', ev => {
        let vid = $_('#js-varid-input').getAttribute('value')
        $_('#collapse-availability ul').innerHTML = '<div class="text-center text-danger fw-bold"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>'

        if ( ! vid ){
            alert(Sitegui.trans('Please select an option'))
            ev.preventDefault()
        } else {
            var http = new XMLHttpRequest();
            var url = '{$links.inventory}.json'
            http.open('POST', url, true);

            http.onreadystatechange = function() {
                if (http.readyState == 4 && http.status == 200) {
                    response = JSON.parse(http.responseText);
                    let choosen = ''
                    $_('[data-varid="'+ vid +'"]').className.split(' ').forEach(cl => {
                        if (cl.includes('opt-')){
                            choosen += ($_('[data-select="'+ cl.trim() +'"]').innerText || $_('.js-first-opt.'+ cl.trim()).innerText) + ' - '
                        }
                    })
                    $_('#collapse-availability ul .text-center').innerText = choosen.slice(0, -3) 
                    if (response.status && response.status.result == 'success' && Object.keys(response.stocks).length ){
                        Object.keys(response.stocks).forEach(st => {
                            let template = $_('#collapse-availability .sg-template').cloneNode(true)
                            template.querySelector('.sg-store-name').innerText = response.warehouse[st].name +' - Tel: '+ response.warehouse[st].phone
                            template.querySelector('.sg-store-address').innerText = response.warehouse[st].street +', '+ response.warehouse[st].street2  +' '+ response.warehouse[st].city  +', '+ response.warehouse[st].state  +' '+ response.warehouse[st].zip  
                            template.querySelector('.sg-stock').innerText = response.stocks[st]
                            template.classList.remove('d-none')
                            $_('#collapse-availability ul').append(template)
                        })
                    } else {
                        let template = $_('#collapse-availability .sg-template').cloneNode(true)
                        template.querySelector('.sg-store-name').innerText = Sitegui.trans('Out of Stock')
                        template.classList.remove('d-none')
                        $_('#collapse-availability ul').append(template)
                    }  
                    $_('#collapse-availability').classList.add('show')
                }
            }    
            http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            http.send("item[id]="+ vid +"&csrf_token={$token}")            
        }
    })
    
    //touch devices
    if ( ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0) ){
        document.querySelectorAll('.hover-visible').forEach(function(el) {
            el.classList.remove('hover-visible');
        })
    }
})
</script> 
<style type="text/css">
{if $api.page.meta.video}
  .js-sg-zoom .carousel-indicators {
    top: 100%;
  }
  #product-main-carousel:not(.sg-fullscreen) {
    margin-bottom: 1rem;
  }
{/if}
   .js-sg-collection-item .img-container {
      /*min-height: 200px;
      max-height: 200px;*/
      width: auto;
  }
  #product-main-carousel button[data-bs-slide] {
    visibility: hidden;
  }
  #product-main-carousel:hover button[data-bs-slide] {
    visibility: visible;
  } 
  .thumbnail .hover-visible {
    visibility: hidden;
    position:absolute; 
    right:0; 
    bottom:0; 
    /*transform: translate(-50%, -50%);*/  
  } 
  .thumbnail:hover .hover-visible {
    visibility: visible;
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
  button:disabled .opacity-0 {
    opacity: 1 !important;
  }   
</style> 