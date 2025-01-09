<div class="col-12 col-md-10 pt-2 pb-4 mx-auto">
  <div class="row row-cols-2 row-cols-md-4 g-3 view-container">
  {foreach $html.top_menu.apps.children as $cat}
    {foreach $cat.children as $app}
    <div class="item col" data-filter="">
      <div class="thumbnail card py-3 h-100">
        <div class="card-body">
          <a href="{$app.slug}" class="text-decoration-none">
            <div class="row align-items-center">
              <div class="col-auto mx-3 rounded-circle text-nowrap fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 12px;  background-color:{$app.style.bg}; color: {$app.style.color}">{$app.style.abbr}</div>  
              <div class="col ps-1">    
                {$app.name|trans}
              </div>
            </div>
          </a>
        </div>               
      </div>
    </div>
    {/foreach}
  {/foreach}
  </div> 
</div> 