<!-- elFinder CSS (REQUIRED) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/elfinder/2.1.65/css/elfinder.min.css" integrity="sha512-WOBDaA8BfIhFqCQs9OaIt2oYGlJYNwjkLJ+GU0+xn3e/LCWmC2dO4AFj/c6BD9uqBONhPH2KrxrE+Dg3CMah+w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/elfinder/2.1.65/css/theme.min.css" integrity="sha512-rYFN4lXm7EUZ/dXgE/6vB8fw1OpXiumtQOzbTUciq9Uai8KIW3do+VGe80F1BldC7bi887j6AkQigc+Ihnq4/A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" type="text/css" href="{$system.cdn}/{$template}/assets/elfinder/themes/moono/css/theme.css" media="screen">
<!-- elFinder JS (REQUIRED) -->
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/elfinder/2.1.65/js/elfinder.min.js" integrity="sha512-WJC7xcYftu+jl2taNq8hF9tdT8BuizNJSazzKJSVGaHselut9uQhe0ag0R3Hj6j2Cg7TGN9D0s+iH05XtcR/MA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- elFinder initialization (REQUIRED) -->
<script type="text/javascript" charset="utf-8">
  document.addEventListener("DOMContentLoaded", function(e){
    // Helper function to get parameters from the query string.
    function getUrlParam(paramName) {
        var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i') ;
        var match = window.location.search.match(reParam) ;

        return (match && match.length > 1) ? match[1] : '' ;
    }
    function copyStringToClipboard (str) {
       var el = document.createElement('textarea');
       el.textContent = str;
       // Set non-editable to avoid focus and move outside of view
       el.setAttribute('readonly', '');
       el.style = {
            position: 'absolute', left: '-9999px'
       };
       document.body.appendChild(el);
       // Select text inside element
       el.select();
       // Copy text to clipboard
       document.execCommand('copy');
       document.body.removeChild(el);
    }

    $().ready(function() {
        var funcNum = getUrlParam('CKEditorFuncNum');
        var opts = { 
        	url: '{$links.file_api}',
        	height: (funcNum)? '100%' : '80%', //iframe or not
            // Set to do not use browser history to un-use location.hash
            useBrowserHistory : false,
            customData: {
                csrf_token : '{$token}'
            },
            baseUrl: '{$system.cdn}/{$template}/assets/elfinder/',
            soundPath: '{$system.cdn}/{$template}/assets/elfinder/sounds/',
            cssAutoLoad: false,

            commandsOptions: {
                getfile: { 
                    multiple: true 
                }
            }
        };
        
        if (funcNum) {
        	opts.getFileCallback = function(files, elfinder) {
                if (window.opener != null) { //new window
                    target = window.opener;
                } else if (parent != null) { //iframe
                    target = parent;  
                }
                files.forEach( (file, index) =>{
                    if (file.url && file.url.includes('csrf_token=')) { //remove csrf token hash
                        file.url = file.url.replace(/csrf_token=[0-9a-zA-Z]+&/g, '');
                        if (!file.url.includes('://')) { //relative path
                            file.url = window.location.origin + file.url;
                        }
                        files[index].url = file.url                   
                    }
                })

                //console.log(document.referrer);
                try { //browsers block access to any property of different origin
                    done = false;
                    if (typeof target.getImageCallback === "function") { // openned by a button
                        target.getImageCallback(files);
                        target.getImageCallback = undefined;
                        done = true;
                    } else if (target.$(".sg-main.tab-content .tab-pane.active .note-editor")) { //summernote
                        let activeEditor = target.$(".sg-main.tab-content .tab-pane.active .note-editor").prev();
                        if (activeEditor.length){
                            activeEditor.summernote('focus'); //this editor needs focused otherwise image is inserted at focused editor
                            files.forEach(file => {
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
                    } else if (target.ckeditors != null) { //same origin
                        //target.CKEDITOR.tools.callFunction(funcNum, file.url);
                        activeEditorId = target.$(".sg-main.tab-content .tab-pane.active textarea.page-content").attr('id');
                        if (typeof activeEditorId !== "undefined") {
                            editor = target.ckeditors[ activeEditorId ];
                            ntf = editor.plugins.get('Notification'),
                            i18 = editor.locale.t,
                            imgCmd = editor.commands.get('imageUpload');
                            if (!imgCmd.isEnabled) { //upload button is disabled
                                ntf.showWarning(i18('Could not insert image at the current position.'), {
                                    title: i18('Inserting image failed'),
                                    namespace: 'ckfinder'
                                });
                                return;
                            } 
                            files.forEach(file => {
                                editor.execute( 'imageInsert', { source: file.url } )
                            })    
                            done = true;
                        }    
                    }
                    if (done) {
                        target.$("#dynamicModal").modal('hide'); 
                    } else {
                        //console.log(window.location);
                        //elfinder.exec('quicklook');
                        copyStringToClipboard(files[0].url);
                        elfinder.toast({
                          msg: 'File address copied.',
                          hideDuration: 500,
                          showDuration: 300,
                          timeOut: 3000,
                          mode: 'success'
                        });
                    }
                } catch(e) {
                    //console.log('different origin');
                    //console.log(e);
                    var message = {
                        "funcNum": funcNum,
                        "files": files
                    }
                    target.postMessage(JSON.stringify(message),"{$system.edit_url}"); 
                    //console.log(JSON.stringify(message));
                }

                if (window.opener != null) { //new window
                    window.close();
                }
            }
        }
        var elfinder = $('.elfinder-placeholder').elfinder(opts);
        elfinder.elfinder('instance');

        if (funcNum) { //when loaded directly
            $(window).resize(function(){
                var h = $(window).height();
                if( elfinder.height() != h ){
                    //console.log('Resize called. Window height: '+ h +', elfinder: '+ elfinder.height());
                    //elfinder.height(h-66).width($(window).width()-30).resize();
                    elfinder.height(h).width($(window).width()).resize();
                }
            })
        }    
    });
  });  
</script>
<!-- Element where elFinder will be created (REQUIRED) -->
<div class="col-sm-12 p-0">
    <div class="elfinder-placeholder"></div>
</div>    
<style type="text/css">
.elfinder-cwd-view-icons .elfinder-cwd-file {
    width: 150px;
    height: 130px;
}
.elfinder-cwd-view-icons .elfinder-cwd-file-wrapper {
    width: 96px;
    height: 96px;
    border-radius: 10px;
}
.elfinder-cwd-icon {
    -moz-transform-origin: top center;
    -moz-transform: scale(2);
    zoom: 2;
}
.elfinder-cwd-icon:not(.elfinder-cwd-icon-directory):before {
   zoom: .5;
}
</style>