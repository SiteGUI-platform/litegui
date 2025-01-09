//this js used by sandbox editor for getting file via postMessage from file manager
document.addEventListener("DOMContentLoaded", function(e){
	//define callback for file selection using a button.get-image-callback
	//getImageCallback is called by file manager (file_main.tpl) if same origin, or called below when different
	$('body').on('click', '.get-image-callback', function () { 
    var name = $(this).attr('data-name');
    var container = $(this).attr('data-container');
    var multiple  = $(this).attr('data-multiple');

    window.getImageCallback = function(files){
      files.forEach( file => {
        $(container).append(
          $('<div class="col position-relative"></div>')
          .append( $('<img class="img-thumbnail p-0 d-block mx-auto">').attr('src', file.url) )
          .append( $('<input type="hidden">').attr('name', name).val(file.url) )
        ) 
        if ( !multiple ){
          return //break out forEach after the first entry
        }       
      })
      $(container).trigger('updated')
    }  
	});
  //select/upload image
  $('body').on('change', '.get-image-callback', function (e) { 
    if (e.currentTarget.files[0]) { //client upload only, other processed by window.getImageCallback
      let name = $(e.currentTarget).attr('data-name');
      let container = $(e.currentTarget).attr('data-container');
      let src = URL.createObjectURL(e.currentTarget.files[0]); //local file
      //order is important as e.currentTarget will be moved
      $(e.currentTarget)
        .parent()
          .clone()
          .insertAfter(
            $(e.currentTarget).parent()
          )
          .find('input')
          .val('')
          .removeAttr('required');

      $(container).append(
        $('<div class="col position-relative"></div>')
        .append( 
          $('<img class="img-thumbnail p-0 d-block mx-auto">')
          .attr('src', src)
          .on('load', function() {
            URL.revokeObjectURL(src) // free memory
          })
        )
        .append( $(e.currentTarget).attr('name', name).parent().hide() )   
      )
      .trigger('updated');      
    } 
  });    
	$('body').on('click', '.get-file-callback', function () { 
    var container = $(this).attr('data-container');
    //only need file path
    window.getImageCallback = function(files){
      $(container).val(files[0].url); //one path
    }  
	});
  $('body').on('click', '.get-download-callback', function () { 
    var container = $(this).attr('data-container');
    var that = this
    window.getImageCallback = function(files){
      files.forEach( file => {
        if ( ! file.path.startsWith('Protected Folder/') ){
          alert('Download file should be stored in the protected folder')
        } else {
          $(that).val(file.path.substring(17))
          $(container +' .js-download-container')
            .first()
            .clone()
            .appendTo($(container))
            .find('.get-download-callback')
            .val('')
        }       
      })
    }  
  });
	// Receiving file url via postMessage
	// Create IE + others compatible event handler
	var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
	var eventer = window[eventMethod];
	var messageEvent = (eventMethod == "attachEvent")? "onmessage" : "message";

	// Listen to message from child window
	eventer(messageEvent,function(e) {
		let origin = $('#sg-post-message').attr('data-origin');
    //console.log('parent received message!:  ',e.data, e.origin , origin);
    if ( e.origin == origin ) {
      var message = JSON.parse(e.data);
      done = false;
      if (message.funcNum != null) { //from file_main
      	let files = message.files;
        if (typeof getImageCallback === "function") { // openned by a button.get-image-callback
        	getImageCallback(files);
        	getImageCallback = undefined;
        	done = true;
        } else if ($(".sg-main.tab-content .tab-pane.active .note-editor")) { //summernote
          let activeEditor = $(".sg-main.tab-content .tab-pane.active .note-editor").prev();
          if (activeEditor.length){
            activeEditor.summernote('focus');
            files.forEach( file => {
              if(file.mime.match('image')){
                  activeEditor.summernote('insertImage', file.url);
              } else {
                activeEditor.summernote('createLink', {
                    text: file.name,
                    url: file.url,
                    isNewWindow: true
                });
              }               
            }) 
                  
            done = true;
          }    
        } else {
          //CKEDITOR.tools.callFunction(file.funcNum, file.url);
          activeEditorId = $(".sg-main.tab-content .tab-pane.active textarea.page-content").attr('id');
          if (typeof activeEditorId !== "undefined") {
            editor = ckeditors[ activeEditorId ];
            ntf = editor.plugins.get('Notification');
            i18 = editor.locale.t;
            imgCmd = editor.commands.get('imageUpload');
            if (!imgCmd.isEnabled) { //upload button is disabled
              ntf.showWarning(i18('Could not insert image at the current position.'), {
                  title: i18('Inserting image failed'),
                  namespace: 'ckfinder'
              });
              return;
            }
            files.forEach( file => { 
              editor.execute( 'imageInsert', { source: file.url } );
            })  
            done = true;
          }    
        }    
        //we don't implement link copy here 
      } else if (message) { //from iframe layout: update published status
        if (message.iframeUnload){
          $("#dynamicModal .progress").removeClass('d-none')
        } else if (message.id){
          let el = $('[data-publish="'+ message.id +'"');
          if (message.published > 0){
            el.addClass('text-info')
          } else {
            el.removeClass('text-info').addClass('text-dark')
          }
          el.parent().css('z-index', 9999)
          setTimeout(function(){
            el.removeClass('text-dark')
            el.parent().css('z-index','initial')
          }, 3000)
        }
      }      
      done && $('#dynamicModal').modal('hide');
    }   
	},false); 
});  