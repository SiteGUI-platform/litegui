//var editorSelector  = "#layout-editor";
var blockSelector   = ".sg-block-wrapper";
var contentSelector = ".sg-block-content";
// Note: jquery doesn't process head, body tag, everything should go to body
// we use parseHtml to create a dom node instead of using iframe 
//    $(frameSelector).contents().find('body').html($('#layout-content').text());
//    $(frameSelector).ready(function() {
//global Script container: register and execute third-party provided functions - should be here so elements can register functions
sgScript = new function() {
  var container = {};
  this.register = function(name, func) {
    if (func) {
      container[name] = func;
      return name;
    }  
  }
  this.registered = function(name) {
    return container[name]? true : false;
  }
  this.execute = function(name, element, stage) { //stage = pre or post
    if (container[name]) return container[name](element, stage);
  }
  this.info = function() {
    console.log(container);
  }
} 

sgEditor = new function() {
  //////// Parent/Editor control
  //Add editable element to sidebar
  this.addSidebarElement = function(element, prepend, container = "#sg-editor__text") {
    if (element instanceof jQuery && element.data('target')) {
      element.on('mouseover', function () {
        if ($(this).data('target').get(0).nodeType === 3) {
          $(this).data('target').get(0).parentNode.scrollIntoViewIfNeeded();
        } else {  
          $(this).data('target').get(0).scrollIntoViewIfNeeded();
        }  
      });      
    }
    if (prepend) {
      $(container, parent.document).prepend(element);
    } else {
      $(container, parent.document).append(element);
    }
  }
  // Move control to parent frame first so we can setup events on them
  this.initToolbar = function() {
    $('#sg-editor-toolbar').insertBefore($('#wysiwyg-frame', parent.document));
    //Viewport control 
    $('.sg-view-desktop', parent.document).on('click', function () {
      col = 'col-md';
      $('#wysiwyg-frame', parent.document).css({ 'width': '100%', 'height': '100vh'})
        .parent().css({ 'height': 'auto' });
      parent.bootstrap.Tooltip.getOrCreateInstance(this).dispose();
    })
    $('.sg-view-laptop', parent.document).on('click', function () {
      col = 'col-md';
      $('#wysiwyg-frame', parent.document).css({ 'width': '1087px', 'height': '768px'}) //+96px margin
        .parent().css({ 'height': '864px' });
      parent.bootstrap.Tooltip.getOrCreateInstance(this).dispose();
    })
    $('.sg-view-tablet', parent.document).on('click', function () {
      col = 'col-sm';
      $('#wysiwyg-frame', parent.document).css({ 'width': '863px', 'height': '1024px'})
        .parent().css({ 'height': '1120px' });
      parent.bootstrap.Tooltip.getOrCreateInstance(this).dispose();
    })
    $('.sg-view-phone', parent.document).on('click', function () {
      col = '';
      $('#wysiwyg-frame', parent.document).css({ 'width': '400px', 'height': '640px'}) 
        .parent().css({ 'height': '736px' });
      parent.bootstrap.Tooltip.getOrCreateInstance(this).dispose();
    }) 
  }

  //Sidebar setup
  this.initSidebar = function (editorWrapper) {
    sgEditor.initDraggable(editorWrapper); //no jquery object 
    // Move sidebar to parent frame  
    $("#sg-sidebar").appendTo($("#block_left", parent.document));
    // Element effect
    $('#sg-editor__effect .sg-block-wrapper', parent.document).on({
      mouseover: function () {
        $(this).children().addClass('animate__animated animate__'+ $(this).attr('data-animation'));
      },
      mouseout: function () {
        $(this).children().removeClass('animate__animated animate__'+ $(this).attr('data-animation'));
      }, 
    });

    //add space before capital letter -temporary
    $('.animation-list .sg-block-wrapper > div', parent.document).each(function () {
      $(this).text($(this).text().replace(/([A-Z])/g, ' $1').trim());
    })
    //jquery.on does not seem to catch the event
    $('#label-ai-elements', parent.document).get(0) &&
    $('#label-ai-elements', parent.document).get(0).addEventListener('hide.bs.tab', function(e) {
      $('#sg-editor-search-clear', parent.document).trigger('click') //clear prompt
    })
  }

  //Quick search for element
  this.initSearch = function() {
    $("#sg-editor-search", parent.document).on("keyup", function() {
      var value = $(this).text().toLowerCase();
      $("#tab-elements .sg-block-wrapper", parent.document).filter(function() {
        $(this).toggle($(this).find('.sg-block-title').text().toLowerCase().indexOf(value) > -1);
      });
      $("#tab-elements .sg-sidebar-heading", parent.document).filter(function() {
        //show heading if there is at least one visible element (dont use :hidden which element may inherite from parents )
        $(this).toggle($(this).siblings('.sg-block-wrapper:not([style*="display: none"])').length > 0);
      });    
    });
    $("#sg-editor-search-clear", parent.document).on("click", function() {
      $("#sg-editor-search", parent.document).text('')
        .prev().removeClass('spinner-grow');
      $("#sg-editor-search", parent.document).keyup();
    });

    $("#sg-blocks__ai button", parent.document).on("click", function() {
      if ( $("#sg-editor-search", parent.document).text() ){
        $("#sg-editor-search", parent.document).prev().addClass('spinner-grow')
        sgEditor.genAi(this.dataset.type)
      } else {
        $("#sg-editor-search", parent.document).focus()
      }
    })  
  }
  //Editor setup
  this.initEditor = function(editorWrapper) {
    // Sortable init
    sgEditor.initSortable($(editorWrapper));
    //Make link non-clickable
    $(editorWrapper).on({  
      mouseenter: function (ev) {
        if ( $(this).data('stopBubble') ){ //trigger by mouseleave, stop propagate up
          ev.stopPropagation(); //if bubble up, the overlay is attached to the most outer block, otherwise the most inner (fit layout editor)
          $(this).removeData('stopBubble');
          //console.log('mouseenter by leave', $(this));
        } //else console.log('mouseenter ', $(this));

        if ($(editorWrapper).find('.ui-sortable-placeholder').length || 
            $(editorWrapper).find('.ui-resizable-resizing').length ||
            $(this).is('.sg-fullscreen') || $(this).find('.sg-block-content[contenteditable="true"]').length) return; //no toolbox while drag n drop or resizing/fullscreen
        //var container = $(this).css({ //add to sg-block-wrapper's css
        //    position: "relative",
        //    cursor: "move",
        //});
        if ($('.sg-toolbox-overlay').length) {
          $('.sg-toolbox-overlay')//.removeClass('d-none')
            .append('<div class="ui-resizable-handle ui-resizable-e"></div>') //existing handle will be removed, new one is required
            .appendTo( $(this) );
        } else {
          console.log('Overlay element not found');
        }    
        if ($(this).children().is('.row')) {
          $(this).find('.sg-toolbox-open').attr('data-bs-original-title', 'Container');
        } else {
          $(this).find('.sg-toolbox-open').attr('data-bs-original-title', 'Element');
        }

        if ($(this).resizable("instance") != undefined) {
          $(this).resizable('option', 'handles', { e: $(this).find('.ui-resizable-e:last-child')});
        } else {
          sgEditor.initResizable($(this), 'custom');
        } 
      },
      mouseleave: function (ev) {
        //console.log('leave ', $(this));
        ev.stopPropagation(); //mouseleave does bubble up, stop it       
        if ($(this).find('.sg-toolbox-overlay').length) { //mouseleave may be trigger from a child of row, run code only when it has overlay
          $(this).find(".sg-toolbox-close").click();
          $(this).find('.sg-toolbox-overlay').appendTo($('body'))
            .removeClass('animated bounce')
            .find('.ui-resizable-e:not(:last-child)').remove();
          //show parent block, mouseleave/mouseenter can be fired many times and unexpectedly, handle with care
          if ($(this).parent().is('.sg-block-wrapper:not(.ui-sortable-helper)')) { //layout editor
            $(this).parent().data('stopBubble', 1).mouseenter(); //mouseenter does bubble up, tell mouseenter to stop it
          } else if ($(this).parent().parent().is('.sg-block-wrapper:not(.ui-sortable-helper)')) { //page editor as immediate parent is .row
            $(this).parent().parent().data('stopBubble', 1).mouseenter(); 
          }           
        }
      },
    }, '.sg-block-wrapper:not(.ui-sortable-helper)'); 

    //prevent HTML5 editor from copying current tag and make it a new one when pressing Enter  
    document.addEventListener('keydown', event => {
      if (event.key === 'Enter') {
        document.execCommand('insertLineBreak')
        event.preventDefault()
      }
    })        
  }

  //Toolbox control button & action
  this.initOverlay = function(editorWrapper) {
    //enable tooltip
    new bootstrap.Tooltip(document.body, {
      selector: '[data-bs-toggle="tooltip"]',
      fallbackPlacements: ['bottom']
    });
    let control = $('.sg-toolbox-overlay');
    //Open button
    control.find(".sg-toolbox-open").on('mouseenter', function() { 
      $(this).parent().find('.sg-toolbox-menu').removeClass('d-none');
      $(this).parent().find('.sg-toolbox-open').addClass('d-none');
    });
    //Close button
    control.find(".sg-toolbox-close").on('click', function(ev) { 
      ev.stopPropagation(); //dont let other handlers know 
      $(this).parent().addClass('d-none');
      $(this).parent().parent().find('.sg-toolbox-open').removeClass('d-none');
    });

    //delete button
    control.find(".sg-toolbox-delete").on('click', function() { 
      let element = control.parent().mouseleave();
      (typeof targetElement != 'undefined') && element.get(0) == targetElement.get(0) && $('#sg-editor__elements', parent.document).addClass('active').siblings().removeClass('active'); //close content editing
      //element.mouseleave();
      if (!element.parent().is(editorWrapper) && element.parent().children(blockSelector).length < 2) {
        if (element.parent().is('.row') && element.parent().children().length < 2 && element.parent().parent().is(editorWrapper) ){
          element.parent().remove(); //remove empty row at top level for page editor
          return; //stop here
        }  
        element.parent().append('<div class="sg-block-wrapper card-empty card-disable"></div>');
      } 
      element.remove(); 
    });
    //clone button
    control.find(".sg-toolbox-clone").on('click', function() { 
      let element = control.parent().mouseleave();
      if (element.hasClass('card-empty')) return;
      sgEditor.uniqueIds(element.clone().insertAfter(element));
    });   
    //move up          
    control.find(".sg-toolbox-up").on('click', function() { 
      let prev = control.parent().prev(blockSelector);
      if (prev.length) {
        prev.before(control.parent());
      } else {
        control.addClass('animate__animated animate__bounce animate__faster');
        setTimeout(function () { 
          control.removeClass('animate__animated animate__bounce animate__faster');
        }, 500);
      } 
    }); 
    //move down
    control.find(".sg-toolbox-down").on('click', function() { 
      let next = control.parent().next(blockSelector);
      if (next.length) {
        next.after(control.parent());
      } else {
        control.addClass('animate__animated animate__wobble animate__faster');
        setTimeout(function () { 
          control.removeClass('animate__animated animate__wobble animate__faster');
        }, 500);
      }  
    }); 
    //select inner element
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
      //console.log(element, target)
      if (target.is(blockSelector)) {
        control.append('<div class="ui-resizable-handle ui-resizable-e"></div>'); //existing handle will be removed, new one is required
        if (target.resizable("instance") != undefined) {
          target.resizable('option', 'handles', { e: target.find('.ui-resizable-e').last()});
        } else {
          sgEditor.initResizable(target, 'custom');
        }  
      } 
    }); 
    //edit button
    control.find(".sg-toolbox-edit").on('click', function() { 
      targetElement = control.parent(); //global var as other functions rely on it
      $("#sg-editor__attribute", parent.document).text('');
      sgEditor.addEditableContent($("#sg-editor__text", parent.document), targetElement, 0)
        .then(res => sgScript.execute(targetElement.attr('data-sg-func'), targetElement, 'pre') )
        .catch(err => console.log(err));
      sgEditor.addAttributes($("#sg-editor__attribute", parent.document), targetElement);    

      $("#sg-editor-done", parent.document).removeClass('active');
      $("#sg-editor__editing", parent.document).addClass('active')
        .siblings().removeClass('active');
      $('body', parent.document).addClass('sidebar-small');  
    });
    //click to add button
    control.find(".sg-toolbox-add").on('click', function() { 
      let prev = control.prev();
      let placeholder = $('<div class="sg-block-wrapper card-disable ui-sortable-helper sg-block-placeholder"></div>');
      $(editorWrapper).find('.sg-block-placeholder').remove();
      if (prev.is('.row')) {
        prev.append(placeholder);
      } else {
        control.parent().after(placeholder);
      }
      control.parent().mouseleave();
      placeholder.prev().is('.card-empty') && placeholder.prev().remove(); //remove empty placeholder
    });          
  }

  //Add editable content to sidebar
  var removableIndex;
  this.addEditableContent = async function(container, element, index) {
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
      await sgEditor.getSnippet(ui);        
      //setTimeout(function(){ 
      ui.helper.remove(); //somehow remove immediately will not attach js to context
      //}, 5000);
    }

    if (index == 0) { //run only once
      removableIndex = 1; //start from 1
      if (element.find('.sg-editor-template').length) {
        container.html('<div class="d-grid"><button class="btn btn-dark sg-editor-add" type="button"></button></div><hr class="row mt-3"></hr>');
        container.find('.sg-editor-add').text(Sitegui.trans('Add New Item'))
      } else {
        container.html('');      
      } 
    } else {
      removableIndex = index;
    }
    element.contents().each(function (){
      if ( ($(this).hasClass('d-none') && $(this).hasClass('sg-editor-template')) || 
        $(this).hasClass('sg-editor-hidden') ||
        $(this).hasClass('sg-toolbox-overlay') || 
        $(this).is(blockSelector +'> .sg-block-title')
      ) return; //skip hidden template or content
      let add;

      if (this.childElementCount) {
        sgEditor.addEditableContent(container, $(this), removableIndex);
      } else if (this.tagName == 'IMG') {
        add = $('<div class="thumbnail mb-2 border-1" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="File Manager" data-url-use-default="file_manager?CKEditorFuncNum=1#elf_l1_dXBsb2Fk"></div>').append( $('<img class="img-fluid px-5">').attr('src', $(this).attr('src')) );
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
      } else if (this.tagName == 'I' && this.classList.contains('bi')) { 
        add = $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>').text(this.getAttribute('class'));
        add.on('keyup', function () {
          $(this).data('target').attr('class', $(this).text());
          $(this).find('i').attr('class', $(this).text() + ' ps-0 pe-2');  
        });

        $('<i/>').addClass(this.getAttribute('class') + ' ps-0 pe-2')
          .prependTo(add)
          //.wrap('<button class="btn float-start p-1" type="button"></button');          
      } else if (this.tagName == 'A') {//link
        add = $('<span>')
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
        add.appendTo(container);*/
        sgEditor.addSidebarElement(add, false, container);

        if ($(this).hasClass('sg-editor-removable')) {
          let class2add;
          if (this.tagName == 'IMG') {
            class2add = "position-absolute";
          } else class2add = "float-end";

          add.before('<hr class="row mt-3"></hr><button class="btn border-0 text-warning '+ class2add +' sg-editor-remove" data-sg-remove="'+ removableIndex++ +'" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove Item"><i class="bi bi-x-circle"></i></button>'); 
        }
        //link
        if (this.tagName == 'A') {
          var add2 = $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>').text(this.getAttribute("href"));
          add2.data('target', $(this)); 
          add2.on('keyup', function () {
            let val = $(this).text() || '#';
            $(this).data('target').attr('href', val);  
          });
          add.after(add2).after('<button class="btn text-primary float-start p-1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Link"><i class="bi bi-link-45deg fs-5"></i></button>');
        }     
      }  
      
    })

  } 

  //Edit element attributes
  this.addAttributes = function(container, element) {
    add = element.attr('id')? $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>').text(element.attr('id')) : $('<div contenteditable="true" aria-multiline="true" class="form-control mb-2"></div>');
    add.on('keyup', function () {
      element.attr('id', $(this).text().trim());
    });
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
    container.append($('<label class="form-label"></label>').text(Sitegui.trans('Wrapper Classes'))).append(add);

    //content class
    classes = element.children(contentSelector).attr('class');
    add = $('<div contenteditable="true" aria-multiline="true" class="js-sg-content-classes form-control mb-2"></div>').text(classes);
    add.on('keyup', function () {
      element.children(contentSelector)
        .attr('class', $(this).text().trim())
        .addClass(contentSelector.slice(1)); //can't be removed
    });
    container.append($('<label class="form-label"></label>').text(Sitegui.trans('Content Classes'))).append(add);

    //current animation
    if (element.children(contentSelector).hasClass('animate__animated')) {
      let animation = element.children(contentSelector).attr('class').match(/(?!animate__(?:animated|off|wait))animate__\w+/); //match animate__ except animate__animated and animate__off/wait
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
  //Setup listeners for various editable operations
  this.initEditables = function() {
    //add new item
    $('#sg-editor__text', parent.document).on('click', '.sg-editor-add', function (e) { 
      targetElement.find('.sg-editor-template').each(function () {
        if ($(this).hasClass('d-none')) {
          $(this).attr('class', $(this).attr('class').replace(/\:+/g, ' ') )
            .removeClass('d-none')
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

      sgEditor.addEditableContent($("#sg-editor__text", parent.document), targetElement, 0);
      sgScript.execute(targetElement.attr('data-sg-func'), targetElement, 'pre');
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
          remove.attr(
              'class', 
              remove.attr('class')
              .replaceAll('d-none', '')
              .replaceAll('sg-editor-template', '')
              .replace(/\:+/g, ' ')
              .replace(/\s+/g, '::') 
            )//slider may show hidden item - one way to stop it
            .addClass('d-none sg-editor-template');
        } else remove.remove();
      });
      if (confirmed != 0) {
        parent.bootstrap.Tooltip.getOrCreateInstance(this).dispose();
        sgEditor.addEditableContent($("#sg-editor__text", parent.document), targetElement, 0)
        sgScript.execute(targetElement.attr('data-sg-func'), targetElement, 'pre');
      } 
    });   

    //execute third-party function when done editing
    $('#sg-editor-done', parent.document).on('click', function (e) {
      $("#sg-editor__editing", parent.document).removeClass('active')
      sgScript.execute(targetElement.attr('data-sg-func'), targetElement, 'post');
    });

    //Animation when in viewport
    let observer = new IntersectionObserver(function (entries) {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.remove('animate__off');
          if (entry.target.className.indexOf('Out') == -1 && document.querySelector('.sg-animation-repeat')) { //no Out animation and no play once 
            setTimeout(function () { 
              entry.target.classList.add('animate__off');
            }, 1100);  
          } 
        } 
        //entry.target.classList.toggle('animate__off', !entry.isIntersecting);
      });
    }, {
      root: null,
      rootMargin: '0px',
      threshold: 0
    });
    document.querySelectorAll('.animate__animated').forEach(el => {
      observer.observe(el);
    });
    //Animation selector on parent frame
    $('#sg-editor__effect .sg-block-wrapper', parent.document).on('click', function (e) { 
      let animate__classes = ' animate__animated animate__wait animate__'+ $(this).attr('data-animation');
      $('.sg-animation-current input', parent.document).val($(this).attr('data-animation'));
      //play once
      if ( $('body').find('.sg-animation-repeat').length ){
        $('#sg-editor__effect .sg-animation-repeat', parent.document).addClass('text-lime');
      } else {
        $('#sg-editor__effect .sg-animation-repeat', parent.document).removeClass('text-lime');
      }

      targetElement.children('.sg-block-content').each(function () {
        let classes = $(this).get(0).className.split(" ").filter(c => !c.startsWith('animate__'));
        $(this).get(0).className = classes.join(" ").trim() + animate__classes;
        observer.observe(this);
        $('.sg-animation-current', parent.document)
          .removeClass('d-none')
          .find('input, .sg-animation-none')
          .removeClass('animate__animated animate__zoomOutLeft');
      })
      $("#sg-editor__attribute", parent.document).html('') //clear first
      sgEditor.addAttributes($("#sg-editor__attribute", parent.document), targetElement); //reload classes
    });  
    $('#sg-editor__effect .sg-animation-none', parent.document).on('click', function (e) { 
      targetElement.children('.sg-block-content').each(function () {
        let classes = $(this).get(0).className.split(" ").filter(c => !c.startsWith('animate__'));
        $(this).get(0).className = classes.join(" ").trim();
        $('.sg-animation-current', parent.document)
          .find('input, .sg-animation-none')
          .addClass('animate__animated animate__zoomOutLeft');
        observer.unobserve(this);
      })
      $("#sg-editor__attribute", parent.document).html('') //clear first      
      sgEditor.addAttributes($("#sg-editor__attribute", parent.document), targetElement); //reload classes
    });
    $('#sg-editor__effect .sg-animation-repeat', parent.document).on('click', function (e) { 
      if ($(this).hasClass('text-lime')) { //turn off
        $(this).removeClass('text-lime');
        $('body').find('.sg-animation-repeat').remove();
        $('body').find('.animate__animated').removeClass('animate__off');     
      } else { //turn on repeat
        $(this).addClass('text-lime');
        $('body').find('.sg-block-wrapper').first().parent()
          .prepend('<div class="sg-animation-repeat d-none"/>')
          .find('.animate__animated').addClass('animate__off');     
      }
    });  

  }

  //Retrieve snippet from server
  this.getSnippet = async function(ui) { //used mainly for draggable snippet items
    if ( ! ui.item.children(contentSelector).length && ! ui.item.data('retrieving') ){ 
      ui.item.data('retrieving', 1); //prevent duplicate load caused by sortable's activate after click to add element
      var fid = ui.item.children('.sg-block-title').attr('data-sg-sid');
      if (fid) {
        var id, href;
        if ( fid.startsWith('widget__.') ) { //widget__.text.123
          id = fid.split('.').pop();
          fid = fid.replace('.'+ id, ''); //widget__.text
          href = $('#sg-editor-script').attr('data-links-widget');
        } else { //system & template snippet
          id = fid;
          href = $('#sg-editor-script').attr('data-links-snippet');
        }
        var nvp = 'id='+ id +'&csrf_token='+ window.csrf_token +'&format=json';
        ui.helper.append('<div class="sg-block-wrapper card-empty card-disable text-center g-0"><p class="animate__animated animate__pulse animate__infinite pt-1">Rendering...</p></div>'); //loading indicator

        await $.post(href, nvp, function(data) { // already a json object jQuery.parseJSON(data);
          ui.helper.find('.card-empty').remove(); //remove loading indicator
          ui.helper.children('.sg-block-title').removeAttr('data-sg-sid');
          if (data.status.result == 'success') {
            //we have to wrap snippet in a div so we can find and remove script.register from it, without div snippet still contain it
            let snippet = $('<div class="col-12"/>').append( data.snippet.output.replace('sgScript.register(', 'sgScript.register("'+ fid +'", ') );
            if ( fid.startsWith('widget__.') ) {
              if ( snippet.children().length > 1 ) { //if more than 1 child, wrap them in a div
                snippet.addClass('sg-block-content').removeClass('col-12');
                snippet = snippet.wrap('<div class="col-12"/>').parent();
              } else {
                snippet.children().addClass('sg-block-content');
              }
            }  
            //update form
            //$('#sg-editing--content', parent.document).text(snippet.children(contentSelector).attr('class'));
            
            let script = snippet.find('script.register');
            if (script.length) {
              ui.helper.append(script); //add js to ui.helper context
              script.remove(); //remove the source, after appendding it otherwise it does not work,
              snippet.attr('data-sg-func', fid);
              ui.helper.attr('data-sg-func', fid);
              ui.item.attr('data-sg-func', fid);
            }

            // modifying unique id
            sgEditor.uniqueIds(snippet);  
                       
            if (typeof visualizeLayout === "function" && !ui.helper.is('.ui-draggable-dragging, .d-none') ) { //use for layout editor but not while dragging or fake helper to load script
              let newEl = visualizeLayout(snippet.clone().wrap('<div/>').parent(), 0);
              if (newEl.length) { 
                ui.helper.replaceWith( newEl );
                ui.helper = newEl;
                block = ui.helper; //for editing attr
                //Mimic click to edit option
                $('.sg-toolbox-overlay').appendTo(ui.helper);
                ui.helper.find('.sg-toolbox-edit-attr').click();
              }
            } else {
              ui.helper.append(snippet.clone().children()); //clone first because append will take children away from snippet
            }
            sgScript.execute(ui.helper.attr('data-sg-func'), ui.helper, 'init');

            ui.item.append( snippet.children() );
            ui.item.children('.sg-block-title').removeAttr('data-sg-sid');                  
          }
        });
      }
      ui.item.removeData('retrieving'); //remove it
    }
  }

  //Retrieve AI content
  this.genAi = function(type) { 
    let prompt
    if ( prompt = $('#sg-editor-search', parent.document).text() ){ 
      var href = $('#sg-editor-script').attr('data-links-genai');
      var nvp = 'prompt='+ encodeURIComponent(prompt) +'&csrf_token='+ window.csrf_token +'&format=json&type='+ type;
      $('.sg-ai-ouput .sg-block-wrapper', parent.document).addClass('d-none')

      $.post(href, nvp, function(data) { // already a json object jQuery.parseJSON(data);
        //ui.helper.find('.card-empty').remove(); //remove loading indicator
        if (data.status.result == 'success') {
          //we have to wrap snippet in a div so we can find and remove script.register from it, without div snippet still contain it
          $('.sg-ai-ouput .sg-block-wrapper', parent.document).html('<div class="sg-block-content">'+ data.output +'</div>'); 
          $('.sg-ai-ouput .sg-block-wrapper', parent.document).prepend('<div class="sg-block-title" style="max-width:500px;">'+ data.output +'</div>')
          $('.sg-ai-ouput .sg-block-wrapper', parent.document).removeClass('d-none').attr('style', '')
          //update form
          //$('#sg-editing--content', parent.document).text(snippet.children(contentSelector).attr('class'));
        }
      }).fail(function() {
        console.log( "failed" );
      }).always(function() {
        $('#sg-editor-search', parent.document).prev().removeClass('spinner-grow')
      })  
    }
  }
  //Setup resizable
  this.initResizable = function(element, handles) {
    element.resizable({
      handles: (handles == 'custom')? { e: element.find('.ui-resizable-e').last()} : 'e',
      autoHide: (handles == 'custom')? false : true,  
      cancel: ".card-disable", //not just item but also its descendants
      grid: [20,1],
      containment: "parent",
      start: function(event, ui) {
        $(this).mouseleave(); //hide overlay toolbox  
        $('body').css('cursor', 'ew-resize');        
        lastWidth= ui.originalSize.width;
      },
      resize: function (event, ui) {
        var step = 12*(ui.size.width - lastWidth)/vw; //mouse move distance vs viewWidth/12
        var thisW = Math.ceil( 12*lastWidth/$(this).parent().width() ); //col size just before resize 
        //console.log('orig step ', step, ' thisW ', thisW);
        if (ui.size.width < 100 || ($(this).parent().width() - ui.size.width) < 100) {
          step = step >= 0? Math.ceil(step) : Math.floor(step); //when thisW is 2 or 11, step is so small because of the edges
        } else {
          step = Math.round(step); //could be 0 to avoid flickering when resizing
        } 
        //console.log(' new step ', step); 
        thisW = thisW + step;
        //screen < 392px unable to keep col-1 and col-11 aligned, and our code doesnt work smoothly as expected [wont fix]
        var nextNode = ($(this).position().left < vw/12)? $(this).next() : null; //resize next node only when this is the first in row
        if (nextNode && nextNode.hasClass("sg-block-wrapper") && !nextNode.hasClass("card-disable") ){
          var nextW = Math.ceil( 12*nextNode.outerWidth()/nextNode.parent().width() ) - step;
          if (thisW + nextW != 12) { //we don't resize nextNode if they can't form one row
            nextNode = null;
          } 
        } else {  
          nextNode = null;
        }  
        $(this).css("width", "").css("height", "");                    
        if (thisW > 0 && thisW < 13) {
          $(this).removeClass(col +"-1 "+ col +"-2 "+ col +"-3 "+ col +"-4 "+ col +"-5 "+ col +"-6 "+ col +"-7 "+ col +"-8 "+ col +"-9 "+ col +"-10 "+ col +"-11 "+ col +"-12 "+ col +"-auto "+ col);
          $(this).addClass(col +"-"+ thisW);//.attr("data-w", thisW);
          
          if (nextNode && nextW > 0 && nextW < 13) { 
              nextNode.removeClass(col +"-1 "+ col +"-2 "+ col +"-3 "+ col +"-4 "+ col +"-5 "+ col +"-6 "+ col +"-7 "+ col +"-8 "+ col +"-9 "+ col +"-10 "+ col +"-11 "+ col +"-12 "+ col +"-auto "+ col);
              nextNode.addClass(col +"-"+ nextW);//.attr("data-w", nextW);
          } 
        } 
        lastWidth = $(this).outerWidth(); //ui.size.width; // width as of last step        
      },       
    });
  }

  //Modify block's unique ids
  this.uniqueIds = function(block) {
    block.find('[data-sg-id-ref]').each(function (index) {
      let id = 'sg-id-'+ index + (new Date()).getTime();
      let oldId = $(this).attr('id');
      $(this).attr('id', id);
      let attributes = $(this).attr('data-sg-id-ref');
      attributes.split(',').forEach(function (attr) {
        block.find('[' + attr +']').each(function () {
          if (attr.startsWith('aria-') && $(this).attr(attr) == oldId ){
            $(this).attr(attr, id);
          } else if ( $(this).attr(attr) == '#'+ oldId ) {
            $(this).attr(attr, '#'+ id);
          } 
        });
      });
    }); 
  }
    
  // Sortable init
  this.initSortable = function(editorWrapper) {
    //touch devices
    if ( ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0) ){
      return;
    }  
    editorWrapper.sortable({
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
        sgEditor.getSnippet(ui);
      },
      beforeStop: function(event, ui) {
        ui.item = beforeStop(event, ui);
        // remove droppable area for previously empty placeholder 
        if (ui.item.siblings('.card-empty').length) {
          ui.item.siblings('.card-empty').remove();
        }
        //remove style
        ui.item.removeAttr("style");
      },
      start: function(event, ui) {  
        $(this).mouseleave(); //hide overlay toolbox  
        //$('.sg-toolbox-overlay').appendTo($('body'));
        //apply ui.item col class to placeholder
        ui.placeholder.addClass( ui.item.attr('class').match(/\bcol\b(-\w+\b)?(-[\d|\w]+)?\b/g) );  
        //console.log(ui.placeholder)  
        // add droppable area to empty block
        if (ui.item.parent() && !ui.item.parent().is(editorWrapper) && ui.item.parent().children(blockSelector).length < 2) {
          //console.log(ui.item.parent());
          ui.item.parent().append('<div class="sg-block-wrapper card-empty card-disable"></div>');
        }
      }
    });
  }

  this.initDraggable = function(editorWrapper) {
    //SG drag and drop elements at left sidebar
    var justAdded = 0; //when item is clicked, it is attached to mouse cursor, we dont need this behaviour when click to add item
    $("#tab-elements .tab-pane > .row").children(blockSelector)
    .on('click', function (ev) {
      let ui = {
        'placeholder': $(editorWrapper).find('.sg-block-placeholder'),
      }; 

      if (ui.placeholder.length) { 
        ui.item = $(this).clone();

        let snippet = {
          'item': $(this), //at sidebar
          'helper': ui.item, //at editor 
        };
        sgEditor.getSnippet(snippet); //this gets called again by sortable's activate when we mouse move to editor after click to add, but we handle it inside the function

        ev.preventDefault();
        ui.item = beforeStop(ev, ui);
        ui.placeholder.after(ui.item).remove();
        justAdded = 1;
      } 
    }); 

    //touch devices -> no drag
    if ( ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0) ){
      return false;
    } 
    $("#tab-elements .tab-pane > .row").children(blockSelector)
    .draggable({
      helper: "clone",
      revert: "invalid",
      containment: "window",
      //iframeFix: true,
      appendTo: "body",//editorWrapper -> mouseenter before dropping draggable causes some trouble
      connectToSortable: editorWrapper,
      start: function( event, ui ) {
        if (justAdded) {
          justAdded = 0;
          return false; //stop dragging
        } 
      },
    })
  }

}  

//private function to detect current media query 
var checkMQ = function() {
  vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0)
  if (vw >= 768) {
    col = 'col-md'; //default
  } else if (vw >= 576) {
    col = 'col-sm'; 
  } else {
    col = 'col';
  } 
};
$(window).on('resize', checkMQ);
checkMQ(); //do it once when page loads
