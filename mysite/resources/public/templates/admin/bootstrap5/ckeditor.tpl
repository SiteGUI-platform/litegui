<!-- We need $links.file_api, $token (csrf) defined and ckeditor src (below) already loaded-->
{if ! $ckeditor_script_loaded}{$ckeditor_script_loaded = 1 scope="global"}
<script defer src="https://cdn.ckeditor.com/ckeditor5/19.0.0/classic/ckeditor.js"></script>
<script type="text/javascript">
  document.addEventListener("DOMContentLoaded", function(e){

    // elfinder folder hash of the destination folder to be uploaded in this CKeditor 5
    const uploadTargetHash = '{$html.upload_dir|default:l1_dXBsb2Fk}';

    // elFinder connector URL - should already be defined in another instance
    const connectorUrl = '{$links.file_api}';
    window.ckeditors = {}; // You can also use new Map() if you use ES6.
    // we may use the following cmd to insert image to ckeditor from external
    // window.ckeditors.input_content_english.execute( 'imageInsert', { source: 'https://picsum.photos/400/200?sd' } );
    //global var for elfinder instance - just use one instance 
    var _fm;
    window.createEditor = function (elementId) {    
        // To create CKEditor 5 classic editor
        return ClassicEditor
            .create(document.getElementById( elementId ), {   //'imageUpload',
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'ckfinder', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo']
            } )
            .then(editor => {
                window.ckeditors[ elementId ] = editor; 
                const ckf = editor.commands.get('ckfinder'),
                fileRepo = editor.plugins.get('FileRepository'),
                ntf = editor.plugins.get('Notification'),
                i18 = editor.locale.t,
                // Insert images to editor window
                insertImages = urls => {
                    const imgCmd = editor.commands.get('imageUpload');
                    if (!imgCmd.isEnabled) { //upload button is disabled
                        ntf.showWarning(i18('Could not insert image at the current position.'), {
                            title: i18('Inserting image failed'),
                            namespace: 'ckfinder'
                        });
                        return;
                    } 
                    editor.execute('imageInsert', { source: urls });
                },
                // To get elFinder instance
                getfm = (open, targetEditor) => {
                    return new Promise((resolve, reject) => {
                        // Execute when the elFinder instance is created
                        const done = () => {
                            if (open) {
                                // request to open folder specify
                                if (!Object.keys(_fm.files()).length) {
                                    // when initial request
                                    _fm.one('open', () => {
                                        _fm.file(open)? resolve(_fm) : reject(_fm, 'errFolderNotFound');
                                    });
                                } else {
                                    // elFinder has already been initialized
                                    new Promise((res, rej) => {
                                        if (_fm.file(open)) {
                                            res();
                                        } else {
                                            // To acquire target folder information
                                            _fm.request({
                                                cmd: 'parents', 
                                                target: open
                                            }).done(e =>{
                                                _fm.file(open)? res() : rej();
                                            }).fail(() => {
                                                rej();
                                            });
                                        }
                                    }).then(() => {
                                        // Open folder after folder information is acquired
                                        _fm.exec('open', open).done(() => {
                                            resolve(_fm);
                                        }).fail(err => {
                                            reject(_fm, err? err : 'errFolderNotFound');
                                        });
                                    }).catch((err) => {
                                        reject(_fm, err? err : 'errFolderNotFound');
                                    });
                                }
                            } else {
                                // show elFinder manager only
                                resolve(_fm);
                            }
                        };

                        // Check elFinder instance
                        if (window._fm) {
                            // elFinder instance has already been created
                            _fm = window._fm;
                            _fm.options.editor = window.ckeditors[ targetEditor ];
                            //console.log(_fm.options.editor);
                            done();
                        } else {
                            // To create elFinder instance
                            //console.log("elfinder instance started for " + elementId);
                            _fm = window._fm = $('<div/>').dialogelfinder( {
                                // dialog title
                                title : 'File Manager',
                                // connector URL
                                url : connectorUrl,
                                editor : editor, //our own option to identify editor in multiple editor setup
                                customData: {
                                    csrf_token : '{$token}',     
                                }, 
                                // start folder setting
                                startPathHash : open? open : void(0),
                                // Set to do not use browser history to un-use location.hash
                                useBrowserHistory : false,
                                // Disable auto open
                                autoOpen : false,
                                // elFinder dialog width
                                width : '80%',
                                // set getfile command options
                                commandsOptions : {
                                    getfile: {
                                        oncomplete : 'close',
                                        multiple : true
                                    }
                                },
                                // Insert in CKEditor when choosing files
                                getFileCallback : (files, fm) => {
                                    editor = fm.options.editor;
                                    //console.log("inside callback: "); console.log(fm.options.editor);
                                    let imgs = [];
                                    fm.getUI('cwd').trigger('unselectall');
                                    $.each(files, function(i, f) {
                                        if (f && f.mime.match(/^image\//i)) {
                                            imgs.push(fm.convAbsUrl(f.url));
                                        } else {
                                            editor.execute('link', fm.convAbsUrl(f.url));
                                        }
                                    });
                                    if (imgs.length) {
                                        insertImages(imgs);
                                    }
                                }
                            }).elfinder('instance');
                            done();
                        }
                    });
                };

                // elFinder instance
                let _fm;

                if (ckf) {
                    // Take over ckfinder execute()
                    ckf.execute = () => {
                        {if $html.file_manager}
                            $('#dynamicModal').modal('show');
                            //This works but we use our modal to work with cross domain
                            //getfm('', elementId).then(fm => {
                            //    fm.getUI().dialogelfinder('open');
                            //});
                        {/if}
                    };
                }

                // Set up image uploader
                if (location.origin != "{$system.edit_url}") {
                    class MyUploadAdapter {
                        constructor( loader ) {
                            // The file loader instance to use during the upload.
                            this.loader = loader;
                        }

                        // Starts the upload process.
                        upload() {
                            return this.loader.file
                            .then( file => new Promise( ( resolve, reject ) => {
                                this._initRequest();
                                this._initListeners( resolve, reject, file );
                                this._sendRequest( file );
                            } ) );
                        }

                        // Aborts the upload process.
                        abort() {
                            if ( this.xhr ) {
                                this.xhr.abort();
                            }
                        }

                        // Initializes the XMLHttpRequest object using the URL passed to the constructor.
                        _initRequest() {
                            const xhr = this.xhr = new XMLHttpRequest();

                            // Note that your request may look different. It is up to you and your editor
                            // integration to choose the right communication channel. This example uses
                            // a POST request with JSON as a data structure but your configuration
                            // could be different.
                            xhr.open( 'POST', connectorUrl, true );
                            xhr.responseType = 'json';
                        }

                        // Initializes XMLHttpRequest listeners.
                        _initListeners( resolve, reject, file ) {
                            const xhr = this.xhr;
                            const loader = this.loader;
                            const genericErrorText = `Couldn't upload file: ${ file.name }.`;

                            xhr.addEventListener( 'error', () => reject( genericErrorText ) );
                            xhr.addEventListener( 'abort', () => reject() );
                            xhr.addEventListener( 'load', () => {
                                const response = xhr.response;

                                // This example assumes the XHR server's "response" object will come with
                                // an "error" which has its own "message" that can be passed to reject()
                                // in the upload promise.
                                //
                                // Your integration may handle upload errors in a different way so make sure
                                // it is done properly. The reject() function must be called when the upload fails.
                                if ( !response || response.error ) {
                                    return reject( response && response.error ? response.error.message : genericErrorText );
                                }

                                // If the upload is successful, resolve the upload promise with an object containing
                                // at least the "default" URL, pointing to the image on the server.
                                // This URL will be used to display the image in the content. Learn more in the
                                // UploadAdapter#upload documentation.
                                resolve( {
                                    default: response.added[0].url //elfinder response 
                                } );
                            } );

                            // Upload progress when it is supported. The file loader has the #uploadTotal and #uploaded
                            // properties which are used e.g. to display the upload progress bar in the editor
                            // user interface.
                            if ( xhr.upload ) {
                                xhr.upload.addEventListener( 'progress', evt => {
                                    if ( evt.lengthComputable ) {
                                        loader.uploadTotal = evt.total;
                                        loader.uploaded = evt.loaded;
                                    }
                                } );
                            }
                        }

                        // Prepares the data and sends the request.
                        _sendRequest( file ) {
                            // Prepare the form data.
                            const data = new FormData();
                            data.append('cmd', 'upload');
                            data.append('overwrite', 1);
                            data.append('target', uploadTargetHash); //php $volumeId . rtrim(strtr(base64_encode($path), '+/=', '-_.'), '.');
                            data.append('csrf_token', '{$token}');
                            data.append('upload[]', file );

                            // Important note: This is the right place to implement security mechanisms
                            // like authentication and CSRF protection. For instance, you can use
                            // XMLHttpRequest.setRequestHeader() to set the request headers containing
                            // the CSRF token generated earlier by your application.

                            // Send the request.
                            this.xhr.send( data );
                        }
                    }

                    fileRepo.createUploadAdapter = loader => {
                        //return new uploader(loader);
                        return new MyUploadAdapter( loader );
                    };
                } 
            })
            .catch(error => {
                console.error( error );
            });
    }

  });         
</script>
{/if}