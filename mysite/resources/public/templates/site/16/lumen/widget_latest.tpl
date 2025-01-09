{if $latest_widget_order}
	{$latest_widget_order = $latest_widget_order + 1 scope="global"} {* use index to load correct widget var*}
{else}
	{$latest_widget_order = 1 scope="global"}
	<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function(e){
		//Multi-item carousel, same children classes as carousel but different root class
		var carouselFunction =  function(carousel) {
		    var items = carousel.querySelectorAll(".carousel-inner > .row > .col")
		    var noloop  = parseInt(carousel.dataset.noloop) > 0? 1 : 0 //use data to automaticaly convert Int or use parseInt(carousel.attr("data-noloop")
		    var dynamic = parseInt(carousel.dataset.dynamic) > 0? 1 : 0
		    var current = (carousel.dataset.slideTo && parseInt(carousel.dataset.slideTo) <= items.length-length*noloop)? parseInt(carousel.dataset.slideTo) : 0
		    var getLength = function(carousel, items){
		    	if (carousel.dataset.length && parseInt(carousel.dataset.length) <= items.length ){
		            length = parseInt(carousel.dataset.length)
		        } else if (items.length > 0){
		            length = items.length;
		        }
		        var vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0)
				if (vw < 400) {
				    (length > 1) && (length = 1)
				} else if (vw < 600) {
				    (length > 2) && (length = 2)
				} 
				return length
		    }	
		    var slide = function(el, i) {
		    	//el.style.opacity = .8
		    	el.style.transition = 'all .15s linear'
		    	el.addEventListener("mouseover", function(ev) {
		    		//this.style.opacity = 1
		    	})
		    	el.addEventListener("mouseout", function(ev) {
		    		//this.style.opacity = .8
		    	})	
		        el.classList.remove("order-last", "d-none")
		        if (current >= items.length-length && i < current+length-items.length) {
		            el.classList.add("order-last") //looping slide, re-order beginning items 
		        } else if (i < current || i >= current+length) {
		            el.classList.add("d-none")
		        }      
		    };
		    var update = function() {
		        items = carousel.querySelectorAll(".carousel-inner > .row > .col") //update items
		        //length should never be 0
		        length = getLength(carousel, items);

		        current = (items.length && current > items.length-length*noloop)? items.length-length*noloop : current; 
		        if (items.length) {
		            carousel.querySelector('[data-bs-slide="next"]').classList.remove("d-none");
		        } else {
		            carousel.querySelector('[data-bs-slide]').classList.add("d-none");
		        }
		    }
		    //carousel.attr("data-length", length); //set it for future reference  
		    var length = getLength(carousel, items);
		    items.forEach(slide);
		    if (noloop) {
		        if (current <= 0) {
		            carousel.querySelector('[data-bs-slide="prev"]').classList.add('d-none');
		        }  
		        if (current >= items.length - length) { //always have one control incase items.length == length
		            carousel.querySelector('[data-bs-slide="next"]').classList.add("d-none");
		        }        
		    }

		    //prev, next control
		    carousel.querySelector('[data-bs-slide="next"]').addEventListener("click", function(ev) {
		       ev.preventDefault();
		       dynamic && update(); 
		       this.previousElementSibling.classList.remove('d-none');
		       current++;

		       if (noloop && current >= items.length - length) {
		           this.classList.add('d-none');
		           if (current > items.length - length) current = items.length - length;
		       } 
		       if (current >= items.length) {
		           current = 0;
		       }
		       items.forEach(slide);
		    })
		    
		    carousel.querySelector('[data-bs-slide="prev"]').addEventListener("click", function(ev) {
		       ev.preventDefault();
		       dynamic && update(); 
		       this.nextElementSibling.classList.remove('d-none');
		       current--;
		       if (noloop && current <= 0) {
		           this.classList.add('d-none');
		           current = 0;
		       }         
		       if (current < 0) {
		           current = items.length - 1;
		       }
		       items.forEach(slide);
		    })
		    //listen to updated event and update items
		    carousel.addEventListener('updated', function(){ 
		       update();
		       current = (items.length >= length)? items.length - length : 0 //show the newly added image last
		       items.forEach(slide); 
		    })	    
			window.addEventListener('resize', function(){ 
		       update();
		       items.forEach(slide); 
		    })
		};
		document.querySelectorAll('.carousel-multi').forEach(el => {
	    	carouselFunction(el);
		})
	})		
	</script>
	<style type="text/css">
	  .thumbnail img {
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
	  .thumbnail:hover img:only-child {
	    opacity: 1;    
	  }	
	</style>
{/if}
{if $api["widget_latest_{$latest_widget_order}"] }
{$_wlso = "widget_latest_settings_{$latest_widget_order}" }
<div class="row my-3">
    <div class="col-12 text-center mt-3"><h5>{if $api[$_wlso]["slug"]}<a class="text-no-decoration" href='{$api[$_wlso]["slug"]}'>{$api[$_wlso]["title"]}</a>{else}{$api[$_wlso]["title"]}{/if}</h5></div>
</div>
	{if $api[$_wlso]["slide_multiple"]}
		<div id="carousel_panel_{$latest_widget_order}" class='carousel carousel-dark pb-3 {$api[$_wlso]["container_classes"]}' data-bs-ride="carousel">
		  <div class="carousel-inner">
		  	{foreach $api["widget_latest_{$latest_widget_order}"] as $row}
		  		{if $row@index % $api[$_wlso]["columns"] == 0}
		    	<div class="carousel-item {if $row@first}active{/if}">
		    		<div class="card-group js-sg-collection-item">
		    	{/if}	
					    <div class='thumbnail card {$api[$_wlso]["item_classes"]}'>
					      <a class="sg-img-container text-decoration-none mx-auto" href="{$row.slug}"><img class="img-fluid card-img-top" src='{$row.image}' alt="" /></a>
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
					            {if $row.price > 0}<span class="js-sg-currency">{$row.price}</span>
					            	{if $row.was > $row.price}(<span class="js-sg-currency text-decoration-line-through">{$row.was}</span> -{(100*(1-$row.price/$row.was))|truncate:2:''}%){/if}
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
		    	{if $row@index % $api[$_wlso]["columns"] == ($api[$_wlso]["columns"] - 1) OR $row@last}
		    		</div>
		    	</div>
		    	{/if}
		    {/foreach}
		  </div>
		  <button class="carousel-control-prev justify-content-start w-auto ms-3" type="button" data-bs-target="#carousel_panel_{$latest_widget_order}" data-bs-slide="prev">
		    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
		    <span class="visually-hidden">Previous</span>
		  </button>
		  <button class="carousel-control-next justify-content-end w-auto me-3" type="button" data-bs-target="#carousel_panel_{$latest_widget_order}" data-bs-slide="next">
		    <span class="carousel-control-next-icon" aria-hidden="true"></span>
		    <span class="visually-hidden">Next</span>
		  </button>
		</div>
	{else}
		<div class='carousel-multi carousel-dark position-relative col pb-3 {$api[$_wlso]["container_classes"]}' data-length='{$api[$_wlso]["columns"]|default:4}' data-noloop='{$api[$_wlso]["noloop"]|default:0}' data-dynamic="0">
			<div class="carousel-inner overflow-visible for-ribbon">
				<div class="row g-2 mt-1">
				{foreach $api["widget_latest_{$latest_widget_order}"] as $row}
					<div class='col js-sg-collection-item border-bottom-0 position-relative {if $row@index >= $api[$_wlso]["columns"]}d-none{/if}'>
					    <div class='thumbnail card h-100 {$api[$_wlso]["item_classes"]}'>
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
			</div>		
			<button class="carousel-control-prev justify-content-start w-auto ms-3" type="button" data-bs-slide="prev">
			  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
			  <span class="visually-hidden">{"Previous"|trans}</span>
			</button>
			<button class="carousel-control-next justify-content-end w-auto me-3" type="button" data-bs-slide="next">
			  <span class="carousel-control-next-icon" aria-hidden="true"></span>
			  <span class="visually-hidden">{"Next"|trans}</span>
			</button>
		</div>
	{/if}
{/if}