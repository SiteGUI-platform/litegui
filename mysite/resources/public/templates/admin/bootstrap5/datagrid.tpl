{if $api.rows}
<style type="text/css">
  .js-sg-collection-item, .js-sg-collection-item.list-view img {
      -webkit-transition: all 0.5s ease-in-out;
      -moz-transition: all 0.5s ease-in-out;
      -o-transition: all 0.5s ease-in-out;
      transition: all 0.5s ease-in-out;
  }
  .js-sg-collection-item.list-view img {
    border-top-right-radius: 0;
    border-bottom-left-radius: var(--bs-card-inner-border-radius);
  }  
  .js-sg-collection-item.list-view {
      width: 100%;
      background: transparent !important;
      -ms-flex: 0 0 100%;
      flex: 0 0 100%;
      max-width: 100%;
  }
  
  .js-sg-collection-item .sg-selection:not(.list-view *) {
      display: flex;
  }

  .js-sg-collection-item.list-view .thumbnail {
      -ms-flex-direction: row;
      flex-direction: row;
  }

  .js-sg-collection-item .sg-img-container {
      /*min-height: 200px;
      max-height: 200px;*/
    width: auto;
    overflow-y: hidden;
    max-height: 0; 
    transition-property: all;
    transition-duration: 2s;
    transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
  }
  .js-sg-collection-item.list-view .sg-img-container {
      min-width: 25vw;
      max-width: 25vw;
      max-height: fit-content;
  }  
  .sg-img-container.intersected {
    max-height: 200px; /* approximate max height */
  }

  .sg-img-container.intersected.no-image {
    margin-top: 30px;
  }
  .list-view .sg-img-container.intersected.no-image {
    margin-top: 0px;
  }  
  .js-sg-collection-item.list-view .sg-img-container {
      /*float: left;*/
      width: 15%;
  }
  
  .js-sg-collection-item img[src=""]{
    display: none;
  }

  .js-sg-collection-item.list-view .caption,
  .js-sg-collection-item.list-view .card-footer {
      /*float: left;*/
      width: 85%;
      margin: 0 15px;
  }
  .js-sg-collection-item.list-view:nth-of-type(even) .caption {
      /*order: -1;*/
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
  .thumbnail:hover .hover-visible {
    visibility: visible;
  }
  .thumbnail img {
    opacity: 0.8;
    transition: all 0.15s linear;
  }
  .thumbnail:hover img {
    opacity: 1;
  }  
  .thumbnail .btn-secondary {
    opacity: 1;    
  } 
</style>
<script type="text/javascript">
  document.addEventListener("DOMContentLoaded", function(e){
    //show app image when it near the top
    /*function debounce(func, timeout = 150){
      let timer;
      return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
      };
    }
    let baseline = 0;
    let distance;

    $('.sg-img-container').each(function() { //hide images first
      let position = $(this).parent().offset().top;
      if (baseline < 1) {
        baseline = position - 20;
        distance = $(this).parent().height();
      }
      if (position > baseline+distance){ 
        $(this).hide();
      } 
    });
    
    if ($(document).height() == $(window).height()) { //workaround for chrome which doesnt fire scroll event when content fits in window
      $('body').height($(window).height()+1);
    }  
    //$('body').append($('<div class="bg-red" style="opacity:.5; position:absolute; z-index:1000;width:100%; border: solid 1px;"></div>'));
    let upper = baseline;
    let last = 0;
    function showImages() { //show image 
      if ($('.view-container .js-sg-collection-item').hasClass('list-view')) return; //not for list view
      let direction = $(window).scrollTop();
      //console.log(direction);
      if (direction >= last && (direction > 0 || $(document).height() == $(window).height()) ){ //document is within window, it's hard to determine up or down direction when it is 0, but it doesnt matter too much
        //console.log('down');
        upper = Math.max(upper, direction+baseline) + distance;
        if (upper+distance > $(document).height()) {
          upper = $(document).height() - distance;
        }  
      } else {
        //console.log('up');
        upper = Math.min(upper, direction) + baseline; 
      }      
      last = direction;
      //console.log('upper: '+ upper);
      let lower = upper + distance;

      /*$('.bg-red').css({
        top: upper,
        height: distance
      });*/
      /*  
      images.each(function() {
        let position = $(this).parent().offset().top;
        if ((position > upper && position < lower)){
          $(this).slideDown(1000);
        } else if (position > lower) {
          $(this).slideUp(1000);
        } 
      });
    }
    $(window).scroll(debounce(() => showImages())); */
    //Animation when in viewport
    let observer = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            //if (entry.target.className.indexOf('observer-delay') == -1) { //delay next event 1.1s 
                //entry.target.classList.add('observer-delay')
                //setTimeout(function () { 
                //  entry.target.classList.remove('observer-delay')
                //}, 1100) 

                if (entry.isIntersecting) {
                    $(entry.target).find('.sg-img-container').addClass('intersected')
                } else {
                    $(entry.target).find('.sg-img-container').removeClass('intersected')
                }
            //} 
            //entry.target.classList.toggle('animate__off', !entry.isIntersecting);
        });
    }, {
      rootMargin: '50000px 0px -55% 0px', //wont change upper items
      threshold: 0
    });
    document.querySelectorAll('.card.thumbnail').forEach(el => {
      observer.observe(el);
    })

    //bottom of page observer, open when scroll down, close when scroll up
    let previousY = 0
    let footerObserver = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting && window.scrollY >= previousY) {
                let top = 0;
                $('.card.thumbnail .sg-img-container:not(.intersected)').each(function(index, el){
                    !top && (top = el.getBoundingClientRect().top) //first el's top
                    if (el.getBoundingClientRect().top == top) {
                        el.classList.add('intersected')
                    }   
                })
            } else if (window.scrollY < previousY) {
                let bottom = 0;
                $($('.card.thumbnail .sg-img-container').get().reverse()).each(function(index, el){
                    !bottom && (bottom = el.getBoundingClientRect().top) //last el's top
                    if (el.getBoundingClientRect().top == bottom) {
                        el.classList.remove('intersected')
                    }   
                })
            }
            previousY = window.scrollY
        });
    });
    footerObserver.observe(document.querySelector('.sg-grid-footer'))

    $('#list').click(function(event){
      event.preventDefault();
      $('.view-container .js-sg-collection-item').addClass('list-view');
      //images.show();
    });
    $('#grid').click(function(event){
      event.preventDefault();
      $('.view-container .js-sg-collection-item').removeClass('list-view');
      //setTimeout(() => { showImages(); }, 600);
    });
    $('.nav-link[data-bs-toggle="pill"]').click(function(ev) {  
        var filter = $(this).attr('data-filter');
        $(".js-sg-collection-item[data-filter]").filter(function() {
            if (filter == 'all') {
                $(this).toggle(true);
            } else if (filter == 'mine') {
                $(this).toggle($(this).attr('data-owner') == 1);
            } else if (filter == 'purchased') {
                $(this).toggle($(this).attr('data-purchased') == 1);
            } else $(this).toggle($(this).attr('data-filter') == filter);
        });
        //showImages();
        //$(this).parent().parent().find('a.active').removeClass('active');           
        //$(this).addClass('active');
    });
  });       
</script>
{block name="grid_header"}{/block}  
<div class="col-12 {if $system.sgframe}px-0{else}col-md-10 pt-sm-2{/if} pb-40 mx-auto">
    <div class="card mb-4">
        <div class="card-body align-items-center">
            <nav class="navbar navbar-expand-lg py-0">
              <div class="container-fluid px-0">
                <div class="col-auto pe-0 sg-hover-back sg-ob-list mb-1">
         	      <a href="{$links.pagination}"><i class="bi bi-bookmark fs-4"></i></a>
                  <a class="navbar-brand link-success mx-2 py-0" href="{$links.pagination}">{$html.app_label_plural}</a>
      			 </div>
                {block name="grid_menu"}
                    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-app" aria-controls="navbar-app" aria-expanded="false" aria-label="Toggle navigation">
                      <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbar-app">
                      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                          <a class="nav-link active" aria-current="page" href="#">{'Home'|trans}</a>
                        </li>
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
                {/block}  
              </div>
            </nav> 
        </div>       
    </div>

    <div id="carouselAppstore" class="carousel slide">
      <div class="carousel-inner">
        <div class="carousel-item active">
            <div class="row row-cols-1 row-cols-md-4 g-3 view-container">
                {foreach $api.rows as $row}
                {block name="grid_item"}
                <div class="col js-sg-collection-item" data-filter="{$row.subtype|lower}">
                    <div class="thumbnail card border-0 h-100">
                        <img class="sg-img-container img-fluid card-img-top" src='{$row.image|default:"https://picsum.photos/400/200?sig={$row.id}"}' alt="" />
                        <div class="card-body p-1"></div>
                        <div class="caption card-footer py-3 bg-white border-top-0">
                            <h5 class="card-title">
                                {if $links.edit}<a href="{$links.edit}/{$row.id}">{$row.name}</a>
                                {else}{$row.name}{/if}
                                <small class="text-secondary">{$row.subtype}</small>
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
                                                    Free
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
                {/foreach}
            </div>
        </div>
        <div class="carousel-item">Store
        </div>
      </div>
    </div>

    {*<!-- grid_footer -->*}
    <div class="card bg-transparent border-0 sg-grid-footer">
        <div class="card-body row align-items-center">
            <div class="col mt-1">
            {if $api.rowCount}
                {*<!-- Pagination -->*}
                <nav aria-label="Pagination">
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {if $api.current == 1}disabled{/if}">
                      <a class="page-link" href="{$api.page.slug}?current=1{$links.query}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                      </a>
                    </li>
                    {if $api.rowCount}
                        {$pagination = (int) (($api.total-1)/$api.rowCount) + 1}
                    {/if}    
                    {if $api.current > ($pagination - 1) AND $api.current > 2}
                        <li class="page-item"><a class="page-link" href="{$links.pagination}?current={$api.current-2}{$links.query}">{$api.current-2}</a></li>
                    {/if}
                    {if $api.current > 1}
                        <li class="page-item"><a class="page-link" href="{$links.pagination}?current={$api.current-1}{$links.query}">{$api.current-1}</a></li>
                    {/if}
                    <li class="page-item active"><a class="page-link" href="{$links.pagination}?current={$api.current}{$links.query}">{$api.current}</a></li>
                    {if $api.current < $pagination}
                        <li class="page-item"><a class="page-link" href="{$links.pagination}?current={$api.current+1}{$links.query}">{$api.current+1}</a></li>
                    {/if}
                    {if $api.current == 1 AND $api.current < ($pagination - 1)}
                        <li class="page-item"><a class="page-link" href="{$links.pagination}?current={$api.current+2}{$links.query}">{$api.current+2}</a></li>
                    {/if}
                    <li class="page-item {if $api.current == $pagination}disabled{/if}">
                      <a class="page-link" href="{$links.pagination}?current={$pagination}{$links.query}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                      </a>
                    </li>
                  </ul>
                </nav>
            {/if}    
            </div>
      
            <div class="col-auto text-end">
                <span class="d-none d-sm-inline">{'Display'|trans}</span>
                <div class="btn-group bg-white">
                    <a href="#" id="list" class="btn btn-outline-secondary btn-sm"><span class="bi bi-list-stars">
                    </span> {'List'|trans}</a> <a href="#" id="grid" class="btn btn-outline-secondary btn-sm"><span
                        class="bi bi-grid"></span> {'Grid'|trans}</a>
                </div>
            </div>
        </div>        
    </div>
</div> 
<script defer src="{$system.cdn}/{$template}/assets/js/postmessage.js?v=5" id="sg-post-message" data-origin="{$system.edit_url}"></script>     
{/if}    
