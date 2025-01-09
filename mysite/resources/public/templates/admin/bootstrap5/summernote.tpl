{if ! $summernote_script_loaded}
  {$summernote_script_loaded = 1 scope="global"}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" integrity="sha512-ZbehZMIlGA8CTIOtdE+M81uj3mrcgyrh6ZFeG33A4FHECakGrOsTPlPQ8ijjLkxgImrdmSVUHn1j+ApjodYZow==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js" integrity="sha512-lVkQNgKabKsM1DA/qbhJRFQU8TuwkLF2vSN3iU/c7+iayKs08Y8GXqfFxxTZr1IcpMovXnf2N/ZZoMgmZep1YQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<style>
.note-editor .dropdown-toggle::after { 
	all: unset; 
} 
.note-para .note-dropdown-menu {
    min-width: 238px !important;
}
.note-editor .note-dropdown-menu, .note-editor .note-modal-footer { 
	box-sizing: content-box; 
}
.note-style .dropdown-style {
	min-width: 180px !important; 
}
.note-editor.fullscreen {
	background-color: #fff;
}
.note-editor {
	cursor: default;
}
.note-editor.note-frame .note-editing-area .note-editable[contenteditable=false] {
    background-color: rgba(var(--bs-light-rgb), 0.3)!important;
}
</style>
<script type="text/javascript">	
// Initiate plugin
document.addEventListener("DOMContentLoaded", function(e){
  {*/*$.extend($.summernote.plugins, {
    'brenter': function (context) {
        var self = this,
            ui = $.summernote.ui,
            $note = context.layoutInfo.note,
            $editor = context.layoutInfo.editor,
            options = context.options,
            lang = options.langInfo;
        this.events = {
            // Bind on ENTER
            'summernote.enter': function (we, e) {
                //e.preventDefault(); // Prevent <div> creation
                console.log('enter')
                //$editor.find('.note-editable > div').append('<br>'); // Add a <br> at the end of your text because if you don't the first enter won't do a new line.
                context.invoke('editor.pasteHTML', '<br>&zwnj;');
                //$editor.find('.note-editable > div > br + br:last-child').remove(); // Remove extra added <br> but keep last one.
            },
            // Always activate SHIFT key on ENTER
            'insertParagraph': function (evt) {
            	                console.log('insertParagraph')

                if (evt.which === 13 || evt.keyCode === 13)
                    evt.shiftKey = true;
            },
            // Do not allow div wrapper to be removed
            'summernote.codeview.toggled': function (we, e) {
                var isCodeview = context.invoke('codeview.isActivated');
                if (!isCodeview) {
                    if (!$(this).val().startsWith('<div>')) {
                        $editor.find('.note-editable').wrapInner("<div></div>");
                    }
                }
            }
        };
    }
  });*/*}	
  NOTECONFIG = {
    fontSizes: ['12', '14', '16', '18', '20', '24', '28', '32', '36', '44', '48'],
	toolbar: [
	  ['font', ['bold', 'italic', 'clear']], //'underline',
	  ['style', ['style', 'fontsize', 'color']],//'fontname', 
	  //['color', ['color']],
	  ['para', ['ul', 'ol', 'paragraph']],
	  ['table', ['table']],
	  ['insert', ['link', '{if $html.file_manager}elfinder{else}picture{/if}', 'video']],
	  ['view', ['fullscreen', 'help']], //should be the last in order to be removed by page that enable codeview
	],
    icons: {
        bold: "<i class='bi bi-type-bold fs-6'></i>",
        italic: "<i class='bi bi-type-italic fs-6'></i>",
        eraser: "<i class='bi bi-eraser-fill fs-6'></i>",
        magic: "<i class='bi bi-type-h1 fs-6'></i>",
        font: "bi bi-fonts fs-6",
        caret: "<span class='note-icon-caret fs-6'></span>",
        unorderedlist: "<i class='bi bi-list-ul fs-6'></i>",
        orderedlist: "<i class='bi bi-list-ol fs-6'></i>",
        alignJustify: "<i class='bi bi-justify fs-6'></i>",
        alignCenter: "<i class='bi bi-text-center fs-6'></i>",
        alignRight: "<i class='bi bi-text-right fs-6'></i>",
        alignLeft: "<i class='bi bi-text-left fs-6'></i>",
        indent: "<i class='bi bi-text-indent-left fs-6'></i>",
        outdent: "<i class='bi bi-text-indent-right fs-6'></i>",
        table: "<i class='bi bi-grid-3x3 fs-6'></i>",
        picture: "<i class='bi bi-image fs-6'></i>",
        link: "<i class='bi bi-link-45deg fs-6'></i>",
        video: "<i class='bi bi-youtube fs-6'></i>",
        arrowsAlt: "<i class='bi bi-arrows-fullscreen fs-6'></i>",
        code: "<i class='bi bi-code-slash fs-6'></i>",
        question: "<i class='bi bi-question-lg fs-6'></i>",
     },     
	//focus: true, //cause problem with inserting image when there are multiple editors
	minHeight: 200,
    buttons: {
		elfinder: function (context) {
		  var ui = $.summernote.ui;
		  var button = ui.button({
		    contents: '<i class="bi bi-image fs-6"/>',
		    tooltip: 'File',
		    container: context.layoutInfo.editor, //fix for tooltip
		    click: function () {
		      $('#dynamicModal').attr('data-folder', '{$html.upload_dir}')
              $('#dynamicModal').modal('show');
		      //console.log(context);
		    }
		  });
		  return button.render();   // return button as jquery object
		}
	}, 
	callbacks: {
		onEnter: function(e) { //prevent summer from duplicating tag when hitting Enter - https://github.com/summernote/summernote/issues/546
			//$(this).summernote('pasteHTML', '<br>&zwnj;'); //zero-width non-joiner to force cursor to move down, still duplicate tag
      		//document.execCommand('insertLineBreak'); //this is better but does work with list, etc when we want to move out of current tag
      		//e.preventDefault();  
      	}	
	},
    //keyMap: {
    //    pc: {
    //      'ENTER': 'insertHorizontalRule',
    //    },
    //    mac: {
    //      'ENTER': 'insertHorizontalRule',
    //    }
    //},    
  }
  //file upload does not work with sandbox url
  if (location.origin != "{$system.edit_url}") {
	NOTECONFIG.callbacks.onImageUpload = function(files) {
        // Prepare the form data.
        let self = $(this);
        self.next()
        	.find('.note-dropzone').addClass('d-table')
    		.find('.note-dropzone-message').html('<div class="p-2"> Uploading...</div><div class="progress"><div class="progress-bar bg-info progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div></div>');

        let data = new FormData();
        data.append('cmd', 'upload');
        data.append('overwrite', 1);
        data.append('target', '{$html.upload_dir|default:l1_dXBsb2Fk}'); //php $volumeId . rtrim(strtr(base64_encode($path), '+/=', '-_.'), '.');
        data.append('csrf_token', '{$token}');
        for(let i = 0; i < files.length; i++) {
        	data.append('upload[]', files[i]);
        }	
        $.ajax({
            data: data,
            type: "POST",
            url: "{$links.file_api}",
            cache: false,
            contentType: false,
            processData: false,
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                if ( myXhr.upload ) {
			        self.summernote('focus'); //this editor needs focused otherwise image is inserted at focused editor
                    myXhr.upload.addEventListener( 'progress', evt => {
                        if ( evt.lengthComputable ) {
                            self.next().find('.note-dropzone-message .progress-bar').css('width', Math.ceil(evt.loaded*100/evt.total) +'%');
                        }
                    });
                }
                return myXhr;
            },
            success: function (response) {
            	for(let i = 0; i < response.added.length; i++) {
	                if(response.added[i].mime.match('image')){
						let theImage = $('<img src="'+ response.added[i].url +'"/>');
						theImage.on('load', function() {
				    		self.summernote('insertNode', theImage.get(0));
			        		self.next().find('.note-dropzone').removeClass('d-table'); //hide uploading message
						});
						//theImage.src = response.added[i].url;  
					} else {
	                    self.summernote('createLink', {
	                        text: response.added[i].name,
	                        url: response.added[i].url,
	                        isNewWindow: true
	                    });
		        		self.next().find('.note-dropzone').removeClass('d-table');						
					}	          		
		    	}	
            }
        });
    }
  }
});  	
</script>
{/if}