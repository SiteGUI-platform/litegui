{$page = $api.page}
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<script defer type="text/javascript" src="{$system.cdn}/{$template}/assets/js/jquery.ui.touch-punch.js"></script>

    <!--iframe id='layout-frame' frameborder="0" style="width: 100%; height: 100vh;" title="Layout Editor" src='javascript: "<script>window.onload = function() {
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
      
<div id='layout-editor' class="col-sm-12 py-0 bg-white tab-content" style="background: none;">
{foreach $site.locales as $lang => $language}
  <div id="tab-{$lang}" data-lang="{$lang}" class="tab-pane fade {if $language@first}show active{/if}" role="tabpanel"> 
    {$page.content[{$lang}] nofilter}   
  </div>  
{/foreach}
</div>  

<form action="{$html.links.update}" id="editor-form" class="p-0 d-none" method="post" target="_top">
  <input type="hidden" name="page[content][wysiwyg]" value="1">
  {if $page.id > 0}<input type="hidden" name="page[id]" value="{$page.id}">{/if}
  {if $page.subtype}<input type="hidden" name="page[subtype]" value="{$page.subtype}">{/if}
</form>  


<div id="tab-elements">
  <div class="row bg-dark mb-3">
    <div class="col-auto px-0">
      <ul class="nav nav-pills px-0">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tab-label-default-elements" data-bs-toggle="tab" data-bs-target="#sg-default-elements" type="button" role="tab" aria-controls="sg-default-elements" aria-selected="true"><i class="bi bi-layout-wtf" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Elements"></i></button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="label-widget-elements" data-bs-toggle="tab" data-bs-target="#sg-widget-elements" type="button" role="tab" aria-controls="sg-widget-elements" aria-selected="true"><i class="bi bi-journal-bookmark" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Widgets"></i></button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="label-theme-elements" data-bs-toggle="tab" data-bs-target="#sg-theme-elements" type="button" role="tab" aria-controls="sg-theme-elements" aria-selected="true"><i class="bi bi-gift" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Theme Elements"></i></button>
        </li>
      </ul> 
    </div>
  </div>  
 
  <!-- Element tab panes -->
  <div class="tab-content">
    <div class="tab-pane active" id="sg-default-elements" role="tabpanel" aria-labelledby="tab-label-default-elements">  
      <div class="row row-cols-3">    
        <div class="sg-element-wrapper col"><h5 class="p-2 sg-element-title">Widget X</h5>
          <div class="znpb-shape-divider-icon zb-mask zb-mask-pos--top sg-element-content" style="color: rgb(165, 63, 197);">
            <svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" viewBox="0 0 1440 180"><path d="M568.61 170c134.21-12.32 255.61-43.42 376.79-75.47-106.32 7.45-209.94 19.05-312 30.32-141.25 15.6-287.54 30.96-438.34 33.95 122.09 16.85 249.47 22.59 373.55 11.2zM945.4 94.53c65.92-4.61 132.87-7.61 201.15-7.92 101.62-.46 199.3 4.98 293.44 14.27V0c-86.23 7.01-170.88 18.66-251.88 35.06-83.2 16.85-162.83 38.37-242.71 59.47zm-750.34 64.28c-67.1-9.26-132.6-21.88-195.06-36.86v32.46c38.42 2.76 77.84 4.37 117.55 4.8 25.84.25 51.72.12 77.51-.4z" fill="currentColor" fill-opacity=".3"></path><path d="M1440 180v-79.12c-94.15-9.28-191.82-14.72-293.44-14.27-68.28.31-135.23 3.31-201.15 7.92-121.18 32.05-242.59 63.15-376.8 75.47-124.08 11.38-251.47 5.65-373.55-11.19-25.79.52-51.67.65-77.52.39-39.71-.42-79.12-2.03-117.55-4.79V180H1440z" fill="currentColor"></path></svg>
          </div>
        </div>         
        <div class="sg-element-wrapper col">
          <h5 class="p-2 sg-element-title">Block</h5>
          <div class="row sg-element-content"><div class="sg-element-wrapper card-empty card-disable"></div></div>
        </div> 
        <div class="sg-element-wrapper col">
          <h5 class="p-2 sg-element-title">Column</h5>
          <div class="row sg-element-content">
            <div class="sg-element-wrapper col-sm-4" data-w="4"><div class="row"><div class="sg-element-wrapper card-empty card-disable"></div></div></div>
            <div class="sg-element-wrapper col-sm-4" data-w="4"><div class="row"><div class="sg-element-wrapper card-empty card-disable"></div></div></div>
            <div class="sg-element-wrapper col-sm-4 sg-editor-template sg-editor-removable" data-w="4"><div class="row"><div class="sg-element-wrapper card-empty card-disable"></div></div></div>
          </div>  
        </div>  
        <div class="sg-element-wrapper col">
          <h5 class="p-2 sg-element-title">Tab</h5>
          <div role="tabpanel" class="sg-element-content">
            <div class="d-none">
              <script type="text/javascript">
                function widget_tab(el) {
                  //console.log(el);
                  el.find('imge').each(function () {
                    add = $('<img src="'+ $(this).attr('src') + '" class="img-fluid" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="File Manager" data-url="{$html.file_manager}?CKEditorFuncNum=1#elf_l1_dXBsb2Fk">');
                    add.data('target', $(this));
                    add.appendTo($("#sg-editor__editing", parent.document))
                      .on('click', function () {
                        var self = $(this);
                        //setup parent postMessage callback function
                        parent.window.getImageCallback = function(url){
                          //console.log(url);
                          self.attr('src', url);
                          self.data('target').attr('src', url);
                        }  
                      })
                      .on('mouseover', function () {
                        $(this).data('target').get(0).scrollIntoView();
                      });

                    //add.appendTo($("#sg-editor__editing", parent.document))
                  });

                }
              </script>
            </div>

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
              <li class="nav-item"><a href="#home" aria-controls="home" class="nav-link active sg-editor-removable" role="tab" data-bs-toggle="tab">Home</a></li>
              <li class="nav-item"><a href="#profile" aria-controls="profile" class="nav-link sg-editor-removable" role="tab" data-bs-toggle="tab">Profile</a></li>
              <li class="nav-item"><a href="#messages" aria-controls="messages" class="nav-link sg-editor-removable" role="tab" data-bs-toggle="tab">Messages</a></li>
              <li class="nav-item sg-editor-template" data-sg-active-selector=".nav-link" data-sg-active-class="active"><a href="#tabindex-3" aria-controls="tabindex-3" data-sg-change="href, aria-controls" class="nav-link sg-editor-removable" role="tab" data-bs-toggle="tab">Settings</a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
              <div role="tabpanel" class="tab-pane active" id="home">home</div>
              <div role="tabpanel" class="tab-pane" id="profile">profile</div>
              <div role="tabpanel" class="tab-pane" id="messages">messages</div>
              <div role="tabpanel" class="tab-pane sg-editor-template" data-sg-active-selector=".tab-pane" data-sg-active-class="active" id="tabindex-3" data-sg-change="id">settings</div>
            </div>
          </div>
        </div>
        <div class="sg-element-wrapper col">
          <h5 class="p-2 sg-element-title">Accordion</h5>
          <div class="accordion sg-element-content" id="accordionExample">
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button sg-editor-removable" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                  Accordion Item #1
                </button>
              </h2>
              <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  <strong>This is the first item's accordion body.</strong> It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed sg-editor-removable" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                  Accordion Item #2
                </button>
              </h2>
              <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  <strong>This is the second item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                </div>
              </div>
            </div>
            <div class="accordion-item sg-editor-template" data-sg-active-selector=".accordion-collapse" data-sg-active-class="show">
              <h2 class="accordion-header" id="heading3" data-sg-change="id"  data-sg-increment="17">
                <button class="accordion-button collapsed sg-editor-removable" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3" data-sg-change="data-bs-target, aria-controls" data-sg-increment="17">
                  Accordion Item #3
                </button>
              </h2>
              <div id="collapse3" data-sg-change="id, aria-labelledby" data-sg-increment="17" class="accordion-collapse collapse" aria-labelledby="heading3" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  <strong>This is the third item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                </div>
              </div>
            </div>
          </div>  
        </div>
        <div class="sg-element-wrapper col">
          <h5 class="p-2 sg-element-title">Carousel</h5>
          <div id="unique-id-234" data-sg-id-ref="data-bs-target" class="sg-element-content carousel carousel-fade slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
              <button type="button" data-bs-target="#unique-id-234" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
              <button type="button" data-bs-target="#unique-id-234" data-bs-slide-to="1" aria-label="Slide 2"></button>
              <button class="sg-editor-template" data-sg-active-selector="button" data-sg-active-class="active" type="button" data-bs-target="#unique-id-234" data-bs-slide-to="2" data-sg-change="data-bs-slide-to, aria-label" data-sg-increment="1" aria-label="Slide 3"></button>
            </div>
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="https://picsum.photos/800/200?sig=1" class="d-block w-100 sg-editor-removable" alt="...">
                <div class="carousel-caption d-none d-md-block">
                  <h5>First slide label</h5>
                  <p>Some representative placeholder content for the first slide.</p>
                </div>
              </div>
              <div class="carousel-item">
                <img src="https://picsum.photos/800/200?sig=2" class="d-block w-100 sg-editor-removable" alt="...">
                <div class="carousel-caption d-none d-md-block">
                  <h5>Second slide label</h5>
                  <p>Some representative placeholder content for the second slide.</p>
                </div>
              </div>
              <div class="carousel-item sg-editor-template" data-sg-active-selector=".carousel-item" data-sg-active-class="active">
                <img src="https://picsum.photos/800/200?sig=3" class="d-block w-100 sg-editor-removable" alt="...">
                <div class="carousel-caption d-none d-md-block">
                  <h5>Third slide label</h5>
                  <p>Some representative placeholder content for the third slide.</p>
                </div>
              </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#unique-id-234" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#unique-id-234" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
          </div>
        </div>  
      </div>  
    </div>
    <div class="tab-pane" id="sg-widget-elements" role="tabpanel" aria-labelledby="label-widget-elements">      
      <div class="row row-cols-3">    
        {foreach $api.widgets as $widget}
        <div class="sg-element-wrapper col">
          <h5 class="p-2 sg-element-title" data-sg-wid={$widget.id}>{$widget.name}</h5>
        </div> 
        {/foreach}
      </div>  
    </div>
    <div class="tab-pane" id="sg-theme-elements" role="tabpanel" aria-labelledby="label-theme-elements">      
      <div class="row row-cols-3">    
        {foreach $api.widgets as $widget}
        <div class="sg-element-wrapper col">
          <h5 class="p-2 sg-element-title" data-sg-wid={$widget.id}>{$widget.name}</h5>
        </div> 
        {/foreach}
      </div> 
      

      <div class="row">
        <div class="col-12">
          <label class="form-label">Border</label>
          <div>
            <i class="bi bi-border-outer"></i>
            <i class="bi bi-border-left"></i>
            <i class="bi bi-border-top"></i>
            <i class="bi bi-border-right"></i>
            <i class="bi bi-border-bottom"></i>
          </div>  
        </div>  
      </div>  

    </div>
  </div>    
</div>  

<div id="sg-language-switcher" class="nav nav-tabs d-none" role="tablist">
  {foreach $site.locales as $lang => $language}
    <button type="button" class="{if $language@first}active{/if}" data-bs-target="#tab-{$lang}" role="tab" aria-controls="tab-{$lang}" data-bs-toggle="tab">{$language|capitalize}</button>
    
    <script type="text/javascript">
      document.addEventListener("DOMContentLoaded", function(e){
        $('.sg-label-{$lang}', parent.document).val('{$page.name[{$lang}]}');
      });  
    </script>
  {/foreach}
</div> 



<link href='{$system.cdn}/{$template}/assets/css/layout.editor.css' rel='stylesheet' />
<!--script defer src="https://cdn.ckeditor.com/ckeditor5/30.0.0/inline/ckeditor.js"></script-->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
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
    insertCss(context, frameHead, 'https://cdn.sitegui.com/public/admin/bootstrap5/assets/css/mysite.css');
    insertCss(context, frameHead, 'https://cdn.sitegui.com/public/admin/bootstrap5/assets/css/layout.editor.css');
  });
*/
 
  var editorSelector  = "#layout-editor > .tab-pane";
  var blockSelector   = ".sg-element-wrapper";
  var contentSelector = ".sg-element-content";

  //turn existing content into editable
  $(editorSelector).children(':not(.row)').addClass('sg-element-wrapper');
  //before dropping/adding item 
  function beforeStop (event, ui) {
     // new item being added
    if (!ui.item.attr('data-w') && !ui.item.hasClass('container-fluid') && !ui.item.hasClass('container')) {
      ui.item.children(".d-none").removeClass("d-none"); 
      ui.item.removeClass('col-sm-1 col-sm-2 col-sm-3 col-sm-4 col-sm-5 col-sm-6 col-sm-7 col-sm-8 col-sm-9 col-sm-10 col-sm-11 col-sm-12').addClass('col-sm-12');
      ui.item.attr('data-w', 12);
      //ui.item.append(menuString);
      if (ui.item.children(contentSelector).length !== 0) {
        ui.item.find("a[data-edit-content]").removeClass("d-none");  
      } 
      ui.item.find('.sg-element-title').remove();
      
      if (ui.item.attr('data-type') == 'block') {
        //ui.item.append('<div class="row"><div class="sg-element-wrapper card-empty card-disable"></div></div>');
      }
      if (ui.item.attr('data-type') == 'column') {
        //ui.item.children('.row').children().append('<div class="row"><div class="sg-element-wrapper card-empty card-disable"></div></div>');
      }
      //initResizable(ui.item);

      // modifying unique id
      ui.item.find('[data-sg-id-ref]').each(function () {
        let id = 'sg-id-' + (new Date()).getTime();
        $(this).attr('id', id);
        let attributes = $(this).attr('data-sg-id-ref');
        attributes.split(',').forEach(function (attr) {
          ui.item.find('[' + attr +']').each(function () {
            $(this).attr(attr, '#'+ id);
          });
        });
      }); 
      //block = ui.item; //required for other functions
      //$('#editing-item-id').val(block.attr('data-id'));
      //$('#layout-modal').modal('show'); 
    }
    ui.item.removeAttr('style');
    // remove droppable area for previously empty placeholder
    ui.placeholder.parent().children('.card-empty').remove();
   // wrap item inside a row if it is not placed inside a row
    if (!ui.placeholder.parent().hasClass('row') ){
      if (ui.placeholder.prev().hasClass('row')) { //placeholder prev is a row
        ui.placeholder.prev().append(ui.placeholder, ui.item); //move placeholder to support click to add
      } else if (ui.placeholder.next().hasClass('row')) { //placeholder next is a row
        ui.placeholder.next().prepend(ui.placeholder, ui.item);
      } else {  
        ui.item = ui.item.wrap('<div class="row"></div>').parent(); //fix to work with both dragging and click
      } 
    }

    return ui.item;
  }
  //get Widget content when item is about to be dragged or user click item to add - execute in wysiwyg context
  function getWidget(event, ui) { //used mainly for draggable widget items
    if ( ! ui.item.children(contentSelector).length) { //widget
      let wid = ui.item.children('.sg-element-title').attr('data-sg-wid');
      if (wid) {
        var href = '{$html.links.widget}';
        var nvp = 'id='+ wid +'&csrf_token='+ window.csrf_token +'&format=json';
        ui.helper.append('<div class="row"><div class="sg-element-wrapper card-empty card-disable text-center"><p class="animate__animated animate__pulse animate__infinite pt-1">Rendering...</p></div></div>'); //loading indicator

        $.post(href, nvp, function(data) { // already a json object jQuery.parseJSON(data);
          if (data.status.result == 'success') {
              ui.helper.find('.card-empty').parent().remove(); //remove loading indicator
              ui.helper.append('<div class="sg-element-content">'+ data.widget.output +'</div>');
              ui.item.append('<div class="sg-element-content d-none">'+ data.widget.output +'</div>');
          }
        });
      }
    }
  }

  function addAttributes(container, element) {
    add = element.attr('id')? $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2">'+ element.attr('id') +'</div>') : $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2"></textarea>');
    add.on('keyup', function () {
      $(this).data('target').attr('id', $(this).text().trim());
    });
    add.data('target', element);
    container.append('<label class="form-label">ID</label>').append(add);

    //class
    let hiddenClasses = ''; 
    let classes = element.attr('class').split(" ").filter(function(c) {
      if (c.startsWith('ui-') || c.startsWith('sg-') ){
        hiddenClasses += ' '+ c;
        return false;
      } else {  
        return true;
      }
    }).join(" ");
    add = classes? $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2">'+ classes +'</div>') : $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>');
    add.on('keyup', function () {
      $(this).data('target').attr('class', $(this).text().trim() + hiddenClasses);
    });
    add.data('target', element);
    container.append('<label class="form-label">Class</label>').append(add);

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
  }
  var removableIndex;
  function addEditables(container, element, index) {
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
      if ( $(this).hasClass('d-none') && $(this).hasClass('sg-editor-template') ) return; //skip hidden template 
      let add;

      if (this.childElementCount) {
        addEditables(container, $(this), removableIndex);
      } else if (this.tagName == 'IMG') {
        add = $('<div class="thumbnail mb-2 border-1" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="File Manager" data-url="{$html.file_manager}?CKEditorFuncNum=1#elf_l1_dXBsb2Fk"><img src="'+ $(this).attr('src') + '" class="img-fluid px-5 "></div>');
        add.on('click', function () {
          var self = $(this);
          //setup parent postMessage callback function
          parent.window.getImageCallback = function(url){
            //console.log(url);
            self.children('img').attr('src', url);
            self.data('target').attr('src', url);
          }  
        });
      } else if (this.tagName != 'SCRIPT' && this.tagName != 'STYLE' && (this.nodeType == 1 || this.nodeType == 3) && this.textContent != null && this.textContent.trim() ){ 
        //only text nodes or elements containing a single text node here
        //console.log(this);
        //console.log(this.nodeType);
        add = $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2">'+ this.textContent +'</div>');
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
        add.data('target', $(this));
        add.on('mouseover', function () {
          if ($(this).data('target').get(0).nodeType === 3) {
            $(this).data('target').get(0).parentNode.scrollIntoViewIfNeeded();
          } else {  
            $(this).data('target').get(0).scrollIntoViewIfNeeded();
          }  
        });
        add.appendTo(container);

        if ($(this).hasClass('sg-editor-removable')) {
          let class2add;
          if (this.tagName == 'IMG') {
            class2add = "position-absolute";
          } else class2add = "float-end";

          add.before('<hr class="row mt-3"></hr><button class="btn text-warning '+ class2add +' sg-editor-remove" data-sg-remove="'+ removableIndex++ +'" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove Item"><i class="bi bi-x-circle"></i></button>'); 
        }
      }  
      
    })
  } 
  // Sortable
  $(editorSelector).sortable({
      items: blockSelector,
      cancel: ".card-disable",
      placeholder: "ui-state-highlight animate__animated animate__zoomIn animate__faster",
      //tolerance: "pointer",
      forceHelperSize: true,
      opacity: 0.8,
      //revert: true, //animation
      cursor: "move",  
      cursorAt: { left: 10 },
      activate: function(event, ui) { //used mainly for draggable widget items
        getWidget(event, ui);
      },
      beforeStop: function(event, ui) {  
        beforeStop(event, ui);
      },
      start: function(event, ui) {  
        $('.sg-toolbox-overlay').appendTo($('body')).addClass('d-none');       
        //ui.item.data('originalParent', ui.item.parent());
        // add droppable area to empty block
        if (ui.item.parent() && !ui.item.parent().is(editorSelector) && ui.item.parent().children(blockSelector).length < 2) {
          //console.log(ui.item.parent());
          ui.item.parent().append('<div class="sg-element-wrapper card-empty card-disable"></div>');
        }
      },      
      update: function(event, ui) {
        // add droppable area to empty block
        /*if (ui.item.data('originalParent')  && ui.item.data('originalParent').children(blockSelector).length < 1) {
          //console.log(ui.item.data('originalParent'));
          ui.item.data('originalParent').append('<div class="card-empty card-disable"></div>');
          ui.item.data('originalParent', '');           
        }*/
        //serializeLayout($(this));
        //$(frameSelector).contents().find('body').html($('#layout-editor').html());
      }
  });

  // Prepare SG elements
  var justAdded = 0; //when item is clicked, it is attached to mouse cursor, we dont need this behaviour when click to add item
  $("#tab-elements .tab-pane > .row").children(blockSelector).draggable({
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
      'placeholder': $(editorSelector +".active").find('.sg-element-placeholder'),
    }; 

    if (ui.placeholder.length) { 
      ui.item = $(this).clone();

      let widget = {
        'item': $(this),
        'helper': ui.item,  
      };
      getWidget(ev, widget);

      ev.preventDefault();
      ui.item = beforeStop(ev, ui);
      ui.placeholder.after(ui.item).remove();
      justAdded = 1;
    }  
  })
  .children(contentSelector).addClass("d-none");
  $("#tab-elements").appendTo($("#sg-editor__elements", parent.document));

  //initResizable($(".sg-element-wrapper"));
  $(editorSelector).on('click', function () { //destroy summernote instance when clicking outside
    $(this).find('.note-editor').prev().summernote('destroy');
    $(editorSelector).sortable( "enable" );
  })   

  $(editorSelector).on('dblclick', '.sg-element-wrapper', function(ev) {
    //console.log(ev);
    ev.preventDefault();
    ev.stopPropagation(); //prevent nested editor
    $(this).mouseleave();  
    $(editorSelector).sortable( "disable" );
    $('.tab-pane-n-link__elements', parent.document).click(); //close content editing
    $('body', parent.document).removeClass('sidebar-small'); //close sidebar 
    $(this).children().summernote({
      focus: true,
      callbacks: {
        onInit: function(summer) {
          //console.log(summer);
          $(summer.editor).on('click dblclick mouseenter', function(e) {
            e.stopPropagation(); //stop these events from bubling up  
          });
          //$('.note-status-output').append('This is an error using a Bootstrap alert that has been restyled to fit here.');
        }
      }
    });
  }).on({ 
    mouseenter: function () {
      if ($(editorSelector).find('.ui-sortable-placeholder').length || $(editorSelector).find('.ui-resizable-resizing').length) return; //no toolbox while drag n drop or resizing
      //var container = $(this).css({ //add to sg-element-wrapper's css
      //    position: "relative",
      //    cursor: "move",
      //});
      var control = $('.sg-toolbox-overlay'); 
      if (control.length) {
          control.removeClass('d-none');
      } else {
        control = $('<div class="sg-toolbox-overlay"><span class="badge rounded-pill bg-warning text-white"><span class="sg-toolbox-menu d-none"><button class="sg-toolbox-edit btn btn-sm p-0 mx-1" type="button"><i class="fas fa-sm fa-pencil-alt"></i></button><button class="sg-toolbox-clone btn btn-sm p-0 mx-1" type="button"><i class="far fa-sm fa-copy"></i></button><button class="sg-toolbox-delete btn btn-sm p-0 mx-1" type="button"><i class="fas fa-sm fa-trash"></i></button><button class="sg-toolbox-up btn btn-sm p-0 mx-1" type="button"><i class="fas fa-sm fa-arrow-up"></i></button><button class="sg-toolbox-down btn btn-sm p-0 mx-1" type="button"><i class="fas fa-sm fa-arrow-down"></i></button><button class="sg-toolbox-inner btn btn-sm p-0 mx-1" type="button"><i class="far fa-sm fa-object-ungroup"></i></button><button class="btn btn-sm p-0 mx-1 sg-toolbox-close" type="button"><i class="fas fa-sm fa-times-circle"></i></button></span><button class="btn btn-sm p-0 sg-toolbox-open" type="button"><i class="fas fa-sm fa-ellipsis-h"></i></button></span>                           <span class="sg-toolbox-add btn btn-sm btn-warning text-dark rounded-circle" title="Add"><i class="fas fa-sm fa-plus"></i></span></div>');

        control.find(".sg-toolbox-open").on('click', function() { 
          $(this).parent().find('.sg-toolbox-menu').removeClass('d-none');
          $(this).parent().find('.sg-toolbox-open').addClass('d-none');
        });
        control.find(".sg-toolbox-close").on('click', function(ev) { 
          ev.stopPropagation(); //dont let other handlers know 
          $(this).parent().addClass('d-none');
          $(this).parent().parent().find('.sg-toolbox-open').removeClass('d-none');
        });  
        control.find(".sg-toolbox-edit").on('click', function() { 
          targetElement = control.parent();
          addEditables($("#sg-editor__text", parent.document), targetElement, 0);
          
          $("#sg-editor__attribute", parent.document).text('');
          addAttributes($("#sg-editor__attribute", parent.document), targetElement);

          $(".tab-pane-n-link__elements", parent.document).removeClass('active');
          $(".tab-pane-n-link__editing", parent.document).addClass('active');
          $('body', parent.document).addClass('sidebar-small');  
          widget_tab(targetElement); 
        });
        control.find(".sg-toolbox-delete").on('click', function() { 
          let element = control.parent().mouseleave();
          (typeof targetElement != 'undefined') && element.get(0) == targetElement.get(0) && $('.tab-pane-n-link__elements', parent.document).click(); //close content editing
          //element.mouseleave();
          if (!element.parent().is(editorSelector) && element.parent().children(blockSelector).length < 2) {
            if (element.parent().is('.row') && element.parent().children().length < 2 && element.parent().parent().is(editorSelector) ){
              element.parent().remove(); //remove empty row at top level
              return; //stop here
            }  
            element.parent().append('<div class="sg-element-wrapper card-empty card-disable"></div>');
          } 
          element.remove(); 
        });
        control.find(".sg-toolbox-clone").on('click', function() { 
          let element = control.parent().mouseleave();
          element.after(element.clone());
        });             
        control.find(".sg-toolbox-up").on('click', function() { 
          let prev = control.parent().prev();
          if (prev.length) {
            prev.before(control.parent());
          } else {
            control.addClass('animate__animated animate__bounce animate__faster');
            setTimeout(function () { 
              control.removeClass('animate__animated animate__bounce animate__faster');
            }, 500);
          } 
        }); 
        control.find(".sg-toolbox-down").on('click', function() { 
          let next = control.parent().next();
          if (next.length) {
            next.after(control.parent());
          } else {
            control.addClass('animate__animated animate__wobble animate__faster');
            setTimeout(function () { 
              control.removeClass('animate__animated animate__wobble animate__faster');
            }, 500);
          }  
        }); 
        control.find(".sg-toolbox-inner").on('click', function() { 
          let element = control.parent();
          let target = element.find(blockSelector).first();
          if (target.length) {
            control.appendTo(target)
              .addClass('animate__animated animate__bounceIn animate__faster');
            setTimeout(function () { 
              control.removeClass('animate__animated animate__bounceIn animate__faster');
            }, 500);  
          } else {
            target = element.parent().closest(blockSelector);
            if (target.length) {
              control.appendTo(target)
                .addClass('animate__animated animate__zoomIn animate__faster');
              setTimeout(function () { 
                control.removeClass('animate__animated animate__zoomIn animate__faster');
              }, 500);                
            }          
          }
          if (target.hasClass('sg-element-wrapper')) {
            control.append('<div class="ui-resizable-handle ui-resizable-e"></div>'); //existing handle will be removed, new one is required
            if (target.resizable("instance") != undefined) {
              target.resizable('option', 'handles', { e: target.find('.ui-resizable-e').last()});
            } else {
              initResizable(target);
            }  
          } 
        }); 

        control.find(".sg-toolbox-add").on('click', function() { 
          let prev = control.prev();
          let placeholder = '<div class="sg-element-wrapper card-disable ui-sortable-helper ui-state-highlight sg-element-placeholder"></div>';
          $(editorSelector).find('.sg-element-placeholder').remove();
          if (prev.is('.row')) {
            prev.append(placeholder);
          } else {
            control.parent().after(placeholder);
          }
          control.parent().mouseleave();
        });  
      }
      $(this).append(control)
        ;//.addClass('sg-element-hover');
      if ($(this).hasClass('sg-element-wrapper')) {
        control.append('<div class="ui-resizable-handle ui-resizable-e"></div>'); //existing handle will be removed, new one is required
        if ($(this).resizable("instance") != undefined) {
          $(this).resizable('option', 'handles', { e: $(this).find('.ui-resizable-e').last()});
        } else {
          initResizable($(this));
        }  
      }  
      //always check due to dynamic item update/remove
      //if (!$(this).prev().length) { //first col
          //control.find(".star-handler").addClass('text-warning active');
      //}                   
    },
    mouseleave: function () {
      //console.log($(this));
      //$(this).removeClass('sg-element-hover');
      $(this).find(".sg-toolbox-close").click();
      $(this).find('.sg-toolbox-overlay').appendTo($('body'))
        .addClass('d-none')
        .removeClass('animated bounce');
    },
  }, '.sg-element-wrapper:not(.ui-sortable-helper)'); 

  // Resizable function
  viewport = 'md-'; //default
  function initResizable(element) {
    element.resizable({
      handles: { e: element.find('.ui-resizable-e').last()},
      cancel: ".card-disable",
      //autoHide: true,  
      grid: [40,1],
      containment: "parent",
      start: function( event, ui ) {
        $(this).mouseleave(); //hide overlay toolbox  
        $('body').css('cursor', 'ew-resize');        
        lastWidth= ui.originalSize.width;
      },
      resize: function (event, ui) {
        thisW = parseInt($(this).attr("data-w")) || 12; 
        delta = ui.size.width - lastWidth;

        nextNode = $(this).next();
        if (nextNode && nextNode.hasClass("sg-element-wrapper") && !nextNode.hasClass("card-disable")){
          nextW = parseInt(nextNode.attr("data-w")) || 12;
          if (thisW + nextW != 12) { //we don't resize nextNode if they can't form one row
            nextNode = null;
          } 
        } else {  
          nextNode = null;
        }  

        $(this).css("width", "").css("height", "");                    

        if (delta < -38) { //because one step is 40 set by grid option
            if (thisW > 1) {
                $(this).removeClass("col-"+ viewport +"1 col-"+ viewport +"2 col-"+ viewport +"3 col-"+ viewport +"4 col-"+ viewport +"5 col-"+ viewport +"6 col-"+ viewport +"7 col-"+ viewport +"8 col-"+ viewport +"9 col-"+ viewport +"10 col-"+ viewport +"11 col-"+ viewport +"12");
                thisW--;
                $(this).addClass("col-" + viewport + thisW).attr("data-w", thisW);
            } 
            if (nextNode && nextW < 11 && thisW < 11) { 
                nextNode.removeClass("col-"+ viewport +"1 col-"+ viewport +"2 col-"+ viewport +"3 col-"+ viewport +"4 col-"+ viewport +"5 col-"+ viewport +"6 col-"+ viewport +"7 col-"+ viewport +"8 col-"+ viewport +"9 col-"+ viewport +"10 col-"+ viewport +"11 col-"+ viewport +"12");
                nextW++;
                nextNode.addClass("col-" + viewport + nextW).attr("data-w", nextW);
            }   
        }
        if (delta > 38) { //expand width
            if (thisW < 12) {
                $(this).removeClass("col-"+ viewport +"1 col-"+ viewport +"2 col-"+ viewport +"3 col-"+ viewport +"4 col-"+ viewport +"5 col-"+ viewport +"6 col-"+ viewport +"7 col-"+ viewport +"8 col-"+ viewport +"9 col-"+ viewport +"10 col-"+ viewport +"11 col-"+ viewport +"12");
                thisW++;
                $(this).addClass("col-" + viewport + thisW).attr("data-w", thisW);
            } 
          
            if (nextNode && nextW > 1) {
                nextNode.removeClass("col-"+ viewport +"1 col-"+ viewport +"2 col-"+ viewport +"3 col-"+ viewport +"4 col-"+ viewport +"5 col-"+ viewport +"6 col-"+ viewport +"7 col-"+ viewport +"8 col-"+ viewport +"9 col-"+ viewport +"10 col-"+ viewport +"11 col-"+ viewport +"12");
                nextW--;
                nextNode.addClass("col-" + viewport + nextW).attr("data-w", nextW);
            } 
        }
         
        //$('#out').append(delta +"<br>");
        lastWidth = ui.size.width; // width as of last step
        //serialize Layout
        //serializeLayout($(editorSelector));
      },       
    });
  }
  
  //Viewport control 
  $('.sg-view-desktop', parent.document).on('click', function () {
    viewport = 'md-';
    $('#wysiwyg-frame', parent.document).css({ 'width': '100%', 'height': '100vh'})
      .parent().css({ 'height': 'auto' });
  })
  $('.sg-view-laptop', parent.document).on('click', function () {
    viewport = 'md-';
    $('#wysiwyg-frame', parent.document).css({ 'width': '991px', 'height': '768px'})
      .parent().css({ 'height': '864px' });
  })
  $('.sg-view-tablet', parent.document).on('click', function () {
    viewport = 'sm-';
    $('#wysiwyg-frame', parent.document).css({ 'width': '767px', 'height': '1024px'})
      .parent().css({ 'height': '1120px' });
  })
  $('.sg-view-phone', parent.document).on('click', function () {
    viewport = '';
    $('#wysiwyg-frame', parent.document).css({ 'width': '360px', 'height': '640px'})
      .parent().css({ 'height': '736px' });
  }) 
  //Save button on parent frame
  $('#sg-editor-save', parent.document).on('click', function (ev) {
    ev.preventDefault();
    $('#layout-editor > .tab-pane').each(function () {
      $(this).find(blockSelector).removeClass('ui-sortable-handle', 'ui-draggable', 'ui-draggable-handle', 'ui-resizable');//, 'sg-element-hover');
      $('<textarea name="page[content]['+ $(this).attr("data-lang") +']"></textarea>').appendTo($('#editor-form')).val($(this).html());
    });

    $('#editor-form').append($('#sg-multi-label', parent.document).clone()).submit();
  }) 
  //Language switcher on parent frame
  $('#sg-language-remoter [data-bs-toggle="tab"]', parent.document).on('click', function (e) { 
    $("#sg-language-switcher [data-bs-target='"+ e.target.dataset.bsTarget +"']").tab("show");
    $(this).siblings().removeClass('active');
    $('#sg-language-remoter', parent.document).find('.dropdown-toggle').text($(this).text()).append(' <i class="far fa-language"></i>');
    //$("#sg-language-switcher [data-bs-target='"+ e.target.dataset.bsTarget +"']", parent.document).tab("show");
  });  

  //Animation when in viewport
  let observer = new IntersectionObserver(function (entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.remove('animate__off');
        if (entry.target.className.indexOf('Out') == -1 && $('body').find('.sg-animation-repeat').length) { //no Out animation and no play once 
          setTimeout(function () { 
            entry.target.classList.add('animate__off');
          }, 1100);  
        } 
      } 
      //entry.target.classList.toggle('animate__off', !entry.isIntersecting);
    });
  }, 
  {
    root: null,
    rootMargin: '0px',
    threshold: 0
  });
  document.querySelectorAll('.animate__animated').forEach(el => {
    observer.observe(el);
  });
  //Animation selector on parent frame
  $('#sg-editor__effect .sg-element-wrapper', parent.document).on('click', function (e) { 
    let animate__classes = ' animate__animated animate__'+ $(this).attr('data-animation');
    $('.sg-animation-current input', parent.document).val($(this).attr('data-animation'));
    //play once
    if ( $('body').find('.sg-animation-repeat').length ){
      $('#sg-editor__effect .sg-animation-repeat', parent.document).addClass('text-lime');
    } else {
      $('#sg-editor__effect .sg-animation-repeat', parent.document).removeClass('text-lime');
    }

    targetElement.children('.sg-element-content').each(function () {
      let classes = $(this).get(0).className.split(" ").filter(c => !c.startsWith('animate__'));
      $(this).get(0).className = classes.join(" ").trim() + animate__classes;
      observer.observe(this);
      $('.sg-animation-current', parent.document)
        .removeClass('d-none')
        .find('input, .sg-animation-none')
          .removeClass('animate__animated animate__zoomOutLeft');
    })
  });  
  $('#sg-editor__effect .sg-animation-none', parent.document).on('click', function (e) { 
    targetElement.children('.sg-element-content').each(function () {
      let classes = $(this).get(0).className.split(" ").filter(c => !c.startsWith('animate__'));
      $(this).get(0).className = classes.join(" ").trim();
      $('.sg-animation-current', parent.document)
        .find('input, .sg-animation-none')
          .addClass('animate__animated animate__zoomOutLeft');
      observer.unobserve(this);
    })
  });
  $('#sg-editor__effect .sg-animation-repeat', parent.document).on('click', function (e) { 
    if ($(this).hasClass('text-lime')) { //turn off
      $(this).removeClass('text-lime');
      $('body').find('.sg-animation-repeat').remove();
      $('body').find('.animate__animated').removeClass('animate__off');     
    } else { //turn on repeat
      $(this).addClass('text-lime');
      $('body').find('.sg-element-wrapper').first().parent()
        .prepend('<div class="sg-animation-repeat d-none"/>')
        .find('.animate__animated').addClass('animate__off');     
    }
  });  
  //add new item
  $('#sg-editor__text', parent.document).on('click', '.sg-editor-add', function (e) { 
    targetElement.find('.sg-editor-template').each(function () {
      if ($(this).hasClass('d-none')) {
        $(this).removeClass('d-none');
      } else {
        let clone = $(this).clone();
        let activeClass = $(this).attr('data-sg-active-class');

        if (activeClass){
          let itemSelector = $(this).attr('data-sg-active-selector');
          let item = clone.find(itemSelector).addBack(itemSelector);
          if (item.hasClass(activeClass)) {
            item.removeClass(activeClass);  
          }
        }

        clone.find('[data-sg-change]').addBack('[data-sg-change]').each(function () {
          let el = $(this);
          el.attr('data-sg-change').split(',').forEach(function (attr) {
            el.attr(attr.trim(), el.attr(attr.trim()).replace(/(\d+)$/, function (match, n) {
              return el.attr('data-sg-increment')? parseInt(n) + parseInt(el.attr('data-sg-increment')) : parseInt(n) + 1; 
            })); // replace using pattern );
          });  
          //$(this).attr($(this).attr('data-sg-change'), +$(this).attr($(this).attr('data-sg-change'))+1);
        });
        $(this).parent().append(clone);
        $(this).removeClass('sg-editor-template d-none'); //use previous template
      }
    }); 

    addEditables($("#sg-editor__text", parent.document), targetElement, 0);
  }); 

  //delete item
  $('#sg-editor__text', parent.document).on('click', '.sg-editor-remove', function (e) { 
    removableIndex = $(this).attr('data-sg-remove');
    var confirmed = -1;
    targetElement.find('.sg-editor-template').each(function () {
      let remove = $(this).parent().children().eq(removableIndex-1);
      let activeClass = $(this).attr('data-sg-active-class');

      if (activeClass){
        let itemSelector = $(this).attr('data-sg-active-selector');
        let item = remove.find(itemSelector).addBack(itemSelector);
        if (item.hasClass(activeClass)) {
          if (confirmed > 0 || confirm('The element to be removed is active. Proceed (not recommended)?')) {
            confirmed = 1;
            if (remove.prev().length) {
              remove.prev().find(itemSelector).addBack(itemSelector).addClass(activeClass);
            } else if (remove.next().length) {
              remove.next().find(itemSelector).addBack(itemSelector).addClass(activeClass);
            } 
          } else {
            confirmed = 0;
            return false;   
          }
        }
      }

      if (remove.hasClass('sg-editor-template')) { //do not remove template
        remove.addClass('d-none');
      } else remove.remove();
    });

    (confirmed != 0) && addEditables($("#sg-editor__text", parent.document), targetElement, 0);
  });   
});      
</script>
<style type="text/css">
  .sg-element-wrapper {
    position: relative;
  }
  .sg-element-wrapper:hover {
    cursor: move;
  }  
  .sg-element-wrapper.ui-resizable-resizing {
    cursor: ew-resize !important;
  }
  .sg-element-wrapper .ui-resizable-e:before {
    color: cyan !important;
  }

  .ui-resizable-resizing, .sg-element-placeholder {
    box-shadow: 0 0 0 2px cyan;
  }

  .ui-resizable-e {
    pointer-events: auto;
    z-index:2030;
  }
  /*.sg-element-wrapper .ui-resizable-e {
    right: calc(var(--bs-gutter-x)/2 - 4px);
  }
  .row + .ui-resizable-e { 
    right: -7px;
  }*/
  .sg-toolbox-overlay .badge {
    position:absolute; 
    top: -15px; 
    right: -15px; 
    pointer-events: auto;
  }  
  .sg-toolbox-overlay .sg-toolbox-add {
    position:absolute; 
    bottom: -15px; 
    left: calc(50% - 15px); 
    pointer-events: auto;
  }
   
  .sg-toolbox-overlay {
    /*border: cyan solid 2px; */
    box-shadow: 0 0 0 2px cyan;
    width: 100%; 
    height: 100%; 
    position: absolute; 
    top: 0; 
    left: 0;
    pointer-events: none;
    z-index: 2020;
  } 
  .row + .sg-toolbox-overlay {
    box-shadow: 0 0 0 2px violet;  
  }
  .sg-element-content:not(.row) + .sg-toolbox-overlay {
    width: calc(100% - var(--bs-gutter-x)); 
    left: calc(var(--bs-gutter-x)/ 2);
  }
  /*.row + .sg-toolbox-overlay, .card-empty > .sg-toolbox-overlay {
    left: 0;
    width: 100%;
  } */ 
  .card-empty, .sg-element-placeholder {
    border: 1px dashed #333333;
    background: transparent !important;
    /*order: 6!important;*/
    display: none;
  }
  .card-empty:only-child, .ui-state-highlight {
    height: 40px !important;
    display: block;
  }  
  .card-empty, .row > .ui-state-highlight { 
    width: calc(100% - var(--bs-gutter-x));
    margin-left: calc(var(--bs-gutter-x)/2);
  } 

  #layout-editor {
    min-height: 100vh;
  }
  .ui-draggable-dragging .sg-element-content, .ui-draggable-dragging .card-empty {
    display: none;
  }
  #layout-editor > .tab-pane {
    height: 100%;
  }
  .animate__off {
    -webkit-animation-name: unset !important;
    animation-name: unset !important;
  }
</style>