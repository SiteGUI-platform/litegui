//var frameSelector   = "#layout-frame";
var editorSelector  = "#layout-editor";
//var blockSelector   = ".sg-block-wrapper";
//var contentSelector = ".sg-block-content";
//var coreBlocks = ['block_toolbar', 'block_header', 'block_spotlight', 'block_left', 'block_right', 'block_top', 'block_bottom', 'block_main', 'block_footnote', 'block_footer'];
//var menuString      = $('.sg-toolbox-overlay').eq(0).prop('outerHTML');
// Note: jquery doesn't process head, body tag, everything should go to body
// we use parseHtml to create a dom node instead of using iframe 
//    $(frameSelector).contents().find('body').html($('#layout-content').text());
//    $(frameSelector).ready(function() {
sgEditor.initToolbar();
// Move sidebar to parent frame  
sgEditor.initSidebar(editorSelector);
 
// Initialize layout function
function initLayout(htmlString) {
  var dom = $('<div>').append($.parseHTML(htmlString, null, 1)); //keepScript is enabled to keep script source, xss may occur in sandbox
  var data = visualizeLayout(dom, 0);
  //$(frameSelector).contents().find('body').html($('#layout-content').text());
  //console.log($(frameSelector).contents().find('body'));
  //var data = visualizeLayout($(frameSelector).contents().find('body'), 0);
  $(editorSelector).html(data);
  //initSortable($(editorSelector));
  //initResizable($(editorSelector).find(blockSelector).filter('[data-type="block"], [data-type="content"]'), 'custom');
  //serializeLayout($(editorSelector));
}

// Initialize layout
initLayout($('#layout-content', parent.document).text());  
sgEditor.initOverlay(editorSelector);

//Block control buttons
// Lock block
$(editorSelector).on('click', '.sg-block-title .bi', function(ev) {
  targetBlock = $(this).parent().parent();
  if (targetBlock.hasClass("card-disable")){
    $(this).removeClass("bi-lock-fill").addClass("bi-unlock");
    targetBlock.removeClass("card-disable");                  
  } else {
    $(this).removeClass("bi-unlock").addClass("bi-lock-fill");
    targetBlock.addClass("card-disable");  
  }        
});

//Doubleclick edit
$(editorSelector).on('dblclick', blockSelector, function(ev) {
  ev.preventDefault();
  ev.stopPropagation();
  $(this).find(".sg-toolbox-edit-code").click();
}); 

// Edit code
$(editorSelector).on('click', ".sg-toolbox-edit-code", function(ev) {
  ev.preventDefault();
  ev.stopPropagation();

  block = $(this).closest(blockSelector);
  if (block.children(contentSelector).length !== 0) {
    $('body', parent.document).removeClass('sidebar-small'); //close sidebar 
    typeof targetElement != 'undefined' && $('#sg-editor__elements', parent.document).addClass('active').siblings().removeClass('active'); 
    block.find(".sg-toolbox-close").click();
    $('<div class="modal-backdrop fade show"></div>').appendTo(document.body);
    $('#wysiwyg-frame', parent.document).css('zIndex', 1060);
    $('<div class="modal-backdrop fade show temp-backdrop"></div>').appendTo(parent.document.body);

    $(editorSelector).sortable("disable");
    block.addClass('sg-show-content tab-pane active')
         .data("content-zindex", block.children(contentSelector).css('zIndex'))
         .append('<span class="sg-edit-buttons badge rounded-pill bg-warning"><i class="sg-edit-code bi bi-code-slash fs-6 p-0 mx-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Code"></i> <i class="sg-edit-wide bi bi-arrows-fullscreen fs-6 p-0 mx-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Fullscreen"></i> <i class="sg-edit-done bi bi-check2-circle fs-6 p-0 mx-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Done"></i></span>');
    block.children(contentSelector)
         //.wrap('<div class="sm-wrapper"/>').parent()
         //.summernote(NOTECONFIG);
         .prop('contenteditable', 'true').css('zIndex', 2000)
         .focus()
    //enable tooltip
    new bootstrap.Tooltip(block.get(0), {
      selector: '[data-bs-toggle="tooltip"]'
    });         

    $(".sg-edit-done").click(function(ev) { 
      ev.preventDefault();
      //block.modal('hide');
      //$('body').removeClass('modal-open');
      $('.modal-backdrop').remove();
      $('.temp-backdrop', parent.document).remove();
      $('#wysiwyg-frame', parent.document).css('zIndex', 'auto');
      bootstrap.Tooltip.getOrCreateInstance(this).dispose();
      $(this).closest('.sg-edit-buttons').remove();              
      $(editorSelector).sortable("enable");
      var content = block.children(contentSelector).first();
      if (content.hasClass('sg-edit-codeview')) {
        content.removeClass('sg-edit-codeview');
        content.html(content.data('codemirror').getValue());
      }
      block.removeClass('sg-show-content tab-pane active sg-fullscreen')
           //.find(contentSelector).parent()
           //.summernote('destroy');
      //block.find(contentSelector).unwrap('.sm-wrapper');
      content.removeAttr('contenteditable')
             .css('zIndex', block.data("content-zindex"));
      //serializeLayout($(editorSelector));
    });
    $(".sg-edit-code").click(function(ev) { 
      ev.preventDefault();
      var content = block.children(contentSelector).first();
      if (content.hasClass('sg-edit-codeview')) {
        $(this).attr('data-bs-original-title', 'Code')
          .removeClass('bi-file-earmark-richtext');
        content.removeClass('sg-edit-codeview');
        content.html(content.data('codemirror').getValue());
      } else {
        content.addClass('sg-edit-codeview');
        content.data('html', content.html());
        content.text(''); //clear content
        content.data('codemirror', CodeMirror(content.get(0), {
          value: content.data('html'),
          lineNumbers: true,
          lineWrapping: true,
          mode: {
            name: "smarty", 
            version: 3, 
            baseMode: "text/html"
          },
          matchBrackets: true,
          showCursorWhenSelecting: true
        }));
        $(this).addClass('bi-file-earmark-richtext');
        $(this).attr('data-bs-original-title', 'Text');
        //content.text(content.html());
      }  
    }).click();
    $(".sg-edit-wide").click(function(ev) { 
      block.toggleClass('sg-fullscreen').mouseleave();
      $(this).toggleClass('bi-fullscreen-exit');
    });  
  } 
});  
// Edit block
$(editorSelector).on('click', ".sg-toolbox-edit-attr", function(ev) {
  ev.preventDefault();
  block = $(this).closest(blockSelector);
  block.mouseleave()
  $('#sg-editing--id').val(block.attr('data-id') || '');
  $('#sg-editing--wrapper').text(block.attr('data-class') || '');
  $('#sg-editing--content').text(block.children(contentSelector).attr('class') || ''); //may not just content but block
  $('#layout-modal').modal('show');
}); 

//Cancel
$('#layout-modal [data-bs-dismiss]').on('click', function(ev) {
  $('#layout-modal').modal('hide');
});  
// Done editing
$('#sg-editing--ok').on('click', function(ev) {
  ev.preventDefault();
  let id = $('#sg-editing--id').val(); 
  if (id) {
    block.attr('data-id', id);
  } else block.removeAttr('data-id');  
  //block.removeClass(block.attr('data-class')).addClass($('#sg-editing--wrapper').val()); //clear old classes and add new ones
  let classes = $('#sg-editing--wrapper').text().trim();
  if (classes) {
    block.attr('data-class', classes);
  } else block.removeAttr('data-class');    
  block.children(contentSelector)
      .attr('class', $('#sg-editing--content').text().trim())
      .addClass(contentSelector.slice(1).split(', .').join(' ')); //can't be removed - multiple classes

  label = (id)? id : 
            (classes)? "<i>"+ classes.split(' ')[0] +"</i>" :
              block.attr('data-type') == 'content'? "<i>content block</i>" : "<i>block</i>";
  block.children('.sg-block-title').replaceWith('<div class="sg-block-title"><i class="bi bi-unlock"></i> '+ label.replace('_', ' ') +'</div>');
  $('#layout-modal').modal('hide');
});

//Parent frame control
// Show sample layout
$('#sg-show-sample', parent.document).on('click', function(ev) {
  ev.preventDefault();
  initLayout($('#sg-layout-sample').text());  
});    
// Toggle content
$('#sg-show-content', parent.document).on('click', function(ev) {
  ev.preventDefault();
  $(editorSelector).toggleClass("sg-show-content");             
});

// Preview layout
$('#sg-show-preview', parent.document).on('click', function(ev) {
  ev.preventDefault();
  $(editorSelector).toggleClass("sg-show-preview");    
});

//Retrieve template resources/snippet from server
$('#layout-template', parent.document).on('change', function(ev) { 
  href = $('#sg-editor-script').attr('data-links-snippet');
  id = $(this).val().trim() + '.index';
  nvp = 'id='+ id +'&csrf_token='+ window.csrf_token +'&format=json';

  $.post(href, nvp, function(data) { // already a json object jQuery.parseJSON(data);
    $('.sg-editor-resources').html( (data.status.result == 'success')? data.snippet.output : '' ); //replace current style
  });
});
//Save button on parent frame
$('#sg-editor-save', parent.document).on('click', async function (ev) {
  ev.preventDefault();
  await serializeLayout($(editorSelector));
  $(this).closest('form').submit();
}) 

sgEditor.initEditor(editorSelector);
sgEditor.initEditables();
sgEditor.initSearch();


//before dropping/adding item 
beforeStop = function(event, ui) {                   
  // prevent container from being placed inside container
  if (!ui.placeholder.parent().is(editorSelector) && ui.item.hasClass("container")) {
    //ui.item.removeClass('container container-fluid').addClass('col-sm-12').attr("data-type", "column");
    //item.attr('data-w', 12);
  } 
  
  // remove droppable area for previously empty placeholder 
  //if (ui.item.siblings('.card-empty').length) {
  //  ui.item.siblings('.card-empty').remove();
  //}
  // new item being added and not lazy loaded snippet which is processed by getSnippet that adds loading indicator card-empty to ui.item
  if (!ui.item.hasClass('ui-resizable') && !ui.item.hasClass('container-fluid') && 
      !ui.item.hasClass('container') && ui.item.attr('data-type') != 'row' &&
      !ui.item.children('.card-empty').length) {
    //ui.item.removeClass('col col-1 col-2 col-3 col-4 col-5 col-6 col-7 col-8 col-9 col-10 col-11').addClass('col-12');
    //ui.item.attr('data-w', 12);
    //ui.item.append(menuString);
    //basically replace dropped item by its visualizeLayout, however the item may not have any content while getSnippet's still working
    let newEl = ui.item.clone()
                       .removeClass('col sg-block-wrapper ui-draggable ui-draggable-handle ui-draggable-dragging') //some classes added by drag'n'drop
                       .addClass('col-12');
    newEl.children('.sg-block-title').remove(); //remove before visualizeLayout
    // modifying unique id
    sgEditor.uniqueIds(newEl); 
    newEl = visualizeLayout(newEl.wrap('<div/>').parent(), 0); //visualize from the root sg-block-wrapper
    if (newEl.length) { 
      ui.item.replaceWith( newEl );
      ui.item = newEl;
    }  
    sgScript.execute(ui.item.attr('data-sg-func'), ui.item, 'init');

    //if (ui.item.children(contentSelector).length !== 0) {
    //  ui.item.attr('data-type', 'content');
    //} else {
    //  ui.item.attr('data-type', 'block');
    //}
    
    //if (ui.item.attr('data-type') == 'block') {
    //  ui.item.append('<div class="sg-block-wrapper card-empty card-disable"></div>');
    //}
    //initResizable(ui.item);

    // asking for id 
    block = ui.item; //required for other functions
    //ui.item.find('.sg-block-title > .bi').remove();
    //ui.item.children('.sg-block-title').prepend('<i class="bi bi-unlock"></i> ');
    
    //Mimic click to edit option
    $('.sg-toolbox-overlay').appendTo(ui.item);
    ui.item.find('.sg-toolbox-edit-attr').click();
    ui.item.mouseleave(); //leave to prevent getSnippet from removing .sg-toolbox-overlay
    //$('#sg-editing--id', parent.document).val(block.attr('data-id') || '');
    //$('#sg-editing--wrapper', parent.document).text(block.attr('data-class') || '');
    //$('#sg-editing--content', parent.document).text(block.children(contentSelector).attr('class') || '');
    //$('#layout-modal', parent.document).modal('show'); 
  }
  // wrap item inside a container if it is placed at top level
  if (ui.placeholder.parent().is(editorSelector) && !ui.item.hasClass("container") && !ui.item.hasClass("container-fluid")) {
    if (ui.item.is('[data-type="container"]')) {
      ui.item.removeClass('col-sm-12').addClass(ui.item.attr('data-type'));
    } else if (ui.item.prev().hasClass('container-fluid')) { //placeholder prev is the item itself
      ui.item.appendTo(ui.item.prev());
    } else if (ui.placeholder.next().hasClass('container-fluid')) { //placeholder prev is the item itself
      ui.item.insertAfter(ui.placeholder.next().children('.sg-block-title'));
    } else {  
      ui.item.wrap('<div class="sg-block-wrapper row container-fluid ui-sortable-handle" data-type="container-fluid"></div>');
      ui.item.parent().prepend('<div class="sg-block-title"><i class="bi bi-unlock"></i> <i>Fullwidth container</i></div>');// + menuString);
    } 
  }
  //ui.item.removeAttr('style');
  //console.log(ui.item.parent().attr('data-id'));
  return ui.item;
}

function visualizeLayout(element, depth) {
  var itemContainer = $('<div/>');
  var selector = ""; 
  //valid layout div must have the following classes: container, container-fluid, row with id or another classes, col- and contentSelector. Others will be ignored   
  element.children(selector).each(function(){
    var current = $(this),
        item,
        id,
        label,
        colClasses,
        otherClasses = '',
        container;
    id = current.attr("id");
    label = (id)? id : '';
    if (current.attr("class")) {
      colClasses = [];
      otherClasses = current.attr('class').replace(/\bcol\b(-\w+\b)?(-[\d|\w]+)?\b/g, function (match){ 
        colClasses.push(match); //should match "col", "col-auto", "col-12", "col-sm-auto", "col-md-6"
        return '';
      });
      otherClasses = $.trim(otherClasses.replace('row', '').replace(/\s+/g, ' '));
      //w = current.attr("class").match(regex);
      //classes = $.trim(current.attr("class").replace(regex, '').replace('row', ''));        
    }
    //w = (w)? w[1] : 12; //we have to change row to col-sm-12 to keep col-row order consistent, though it will create more col-row wrapper
    // container and container-fluid and row won't have width, others will have them
    if (current.is(contentSelector)){ // content that does not stay within a col block
      item = $('<div class="sg-block-wrapper col-12"></div>').append(current);
      item.attr('data-type', 'content'); 
      label = (label)? label : "<i>content block</i>"; 
      id = otherClasses = ''; //these classes are contentSelector classes which are already kept with contentSelector div           
    } else if (current.hasClass('container-fluid')) {
      item = $('<div class="sg-block-wrapper row container-fluid"></div>');
      label = (label)? label : "<i>fullwidth container</i>";
      item.attr('data-type', 'container-fluid');
    } else if (container = otherClasses.match(/\bcontainer\b(-\w{2,3}\b)?/g) ){//.container, .container-sm, ... .container-xl, .container-xxl
      item = $('<div class="sg-block-wrapper row"></div>').addClass( container.join(' ') );
      label = (label)? label : "<i>fixed container</i>";
      item.attr('data-type', 'container');
    } else if (current.hasClass('row') && (id || otherClasses) ){
      item = $('<div class="sg-block-wrapper row"></div>');
      //label = (label)? label : "<i>row</i>";
      item.attr('data-type', 'row');
    } else if (colClasses && colClasses.length){ //current.is('[class*="col-"]') ){ 
      //all divs that have col- class or row class with id or other classes, others except contentSelector will be ignored
      item = $('<div class="sg-block-wrapper row"></div>'); //block containing other block-wrappers so it should have row
      item.attr('data-type', 'block'); //required for preview styling
      item.addClass(colClasses.join(' '));
      // column has content, add content block to item and stop processing
      if (current.children().length == 1 && current.children().first().is(contentSelector)){ 
        item.attr('data-type', 'content')
            .removeClass('row'); //content block does not need row
        child = current.children().first();
        if (!label && child.attr('id')) {
          label = child.attr('id');
        }
        //child.text(child.html());
        //child.removeClass("row col-sm-1 col-sm-2 col-sm-3 col-sm-4 col-sm-5 col-sm-6 col-sm-7 col-sm-8 col-sm-9 col-sm-10 col-sm-11 col-sm-12")
        //     .addClass('card-disable col-sm-12');
        item.append(child);
      } 
      //if ($.inArray(id, coreBlocks) !== -1) {
        //item.addClass('card-disable');
      //}
    } 

    // Items have been identified
    if (item) {
      id && item.attr('data-id', id);
      otherClasses && item.attr('data-class', otherClasses); 

      if (current.prop("tagName") != 'DIV' && ! current.is(contentSelector) ){ //contentSelector is already wrapped with div
        item.attr('data-tag', current.prop("tagName").toLowerCase());
      }
      if (current.attr('data-sg-func')) {
        item.attr('data-sg-func', current.attr('data-sg-func'));
      }
      //if (w && item.attr('data-type') != 'row' && item.attr('data-type') != 'container' && item.attr('data-type') != 'container-fluid') {
      //  item.attr('data-w', w);
      //  item.addClass(col +"-"+ w);
      //}

      label = (label)? label.replaceAll('_', ' ') : 
                (otherClasses)? "<i>"+ otherClasses.split(' ')[0] +"</i>" : 
                  item.attr('data-type') == 'content'? "<i>content block</i>" : "<i>block</i>";
      item.prepend('<div class="sg-block-title"><i class="bi bi-unlock"></i> '+ label +'</div>');
      //item.append(menuString);
      //if (item.attr('data-type') == 'content'){ // || item.attr('data-type') == 'content-wrapper') {
      //  item.find("a[data-edit-content]").removeClass("d-none");
      //}
    }

    //$('#out').append("Level "+depth+": "+ id +"<br>");
    if (current.children(selector).length) {
      if(!item) {
        item = visualizeLayout(current, depth + 1);
      } else if (item.attr('data-type') != 'content' && !item.is(contentSelector) ){ //only continue processing for non-content item
        item.append(visualizeLayout(current, depth + 1));
      }
    }
    //add placeholder for empty block
    if (item && !item.children(contentSelector +','+ blockSelector).length){
      item.append('<div class="sg-block-wrapper card-empty card-disable"></div>');
    }
    itemContainer.append(item);
  });
  return itemContainer.children();
};

function serializeLayout(element) {
  $(".sg-edit-done").click();
  var data,
      depth = 0,
      step  = function(level, depth)
      {
          var array = [];
          level.children(blockSelector).each(function()
          {
              var current   = $(this),
                  children  = current.children(blockSelector),
                  item = {}; //object
              if (current.hasClass('card-empty')) return;  //skip this  
              var colClasses = $(this).attr('class').match(/\bcol\b(-\w+\b)?(-[\d|\w]+)?\b/g);
              if (colClasses && colClasses.length) item.class = colClasses.join(' ');
              if (current.attr('data-class')) item.class = item.class? item.class +' '+ current.attr('data-class') : current.attr('data-class');
              if (current.attr('data-id'))    item.id    = current.attr('data-id');
              if (current.attr('data-tag'))   item.tag   = current.attr('data-tag');
              if (current.attr('data-sg-func'))  item.func = current.attr('data-sg-func');
              item.type = current.attr('data-type')? current.attr('data-type') : "content";
              if (item.type == "content") {
                var content  = current.children(contentSelector).first();
                item.content = content.clone()
                                      .wrap('<div>')
                                      .parent()
                                      .html(); //html() returns HTML of child, we want HTML of content                     
                //console.log("Level "+depth+": "+item.content);
              } else if (children.length) { //do not care children of content-wrapper
                item.children = step(current, depth + 1);
              }
              array.push(item);
              //$('#out').append("Level "+depth+": "+ item.id +", width: "+ item.w +", type: "+ item.type +"<br>");
          });
          return array;
      };
  data = step(element, depth);
  $('#layout-content', parent.document).text(window.JSON.stringify(data));
  //console.log(data);
  //console.log($('#layout-content', parent.document));          
}