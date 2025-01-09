{if ! $codemirror_script_loaded}{$codemirror_script_loaded = 1 scope="global"}
{* load once only, myCodeMirror works for multiple instances _magically_ not yet digged into it *}
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.60.0/codemirror.min.css" 
            integrity="sha512-xIf9AdJauwKIVtrVRZ0i4nHP61Ogx9fSRAkCLecmE2dL/U8ioWpDvFCAy4dcfecN72HHB9+7FfQj3aiO68aaaw==" crossorigin="anonymous" />
            <script defer src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.60.0/codemirror.min.js" 
            integrity="sha512-hc0zo04EIwTzKLvp2eycDTeIUuvoGYYmFIjYx7DmfgQeZPC5N27sPG2wEQPq8d8fCTwuguLrI1ffatqxyTbHJw==" crossorigin="anonymous"></script>  
            
            <script defer src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.60.0/mode/xml/xml.min.js" 
            integrity="sha512-XPih7uxiYsO+igRn/NA2A56REKF3igCp5t0W1yYhddwHsk70rN1bbbMzYkxrvjQ6uk+W3m+qExHIJlFzE6m5eg==" crossorigin="anonymous"></script>
            <script defer src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.60.0/mode/javascript/javascript.min.js" 
            integrity="sha512-isTDQTpVc8nKKMQmhm0b6fccRPjzo2g0q6l2VLy9/jDn5s8bEUu9WuSGkkAfN/NCwI3+Qi5wEVCaPRoWvT6YPw==" crossorigin="anonymous"></script>
            <script defer src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.60.0/mode/css/css.min.js" 
            integrity="sha512-rsFXL+3jYau54xgkx2FtVPo+yRM4vLMEU9VBWl8hZX+t8MEYzGBHeHh5ELv04uthHqix7Dhw2o4KgQ1c+s6dEQ==" crossorigin="anonymous"></script>
            <script defer src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.60.0/mode/htmlmixed/htmlmixed.min.js" 
            integrity="sha512-IC+qg9ITjo2CLFOTQcO6fBbvisTeJmiT5D5FnXsCptqY8t7/UxWhOorn2X+GHkoD1FNkyfnMJujt5PcB7qutyA==" crossorigin="anonymous"></script>
            <script defer src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.60.0/mode/smarty/smarty.min.js" 
            integrity="sha512-SXPt8TTniOqIwDjWmTqJ0DiGoEVs9pR0DvIC5gNd1eOFGF7rYRvcRUnU9wwpEarfkxPRQLO8a5yaoTE68jiE4g==" crossorigin="anonymous"></script>
<!--script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.60.0/mode/clike/clike.min.js" integrity="sha512-PeD3V/6m5bFv3qyIVKgDh+huybMHjvsLWuW7ZH5WZsS+hY0pZNU24si/Yja/2D4c/ff++c6k1S240dKwhJEJzw==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.60.0/mode/php/php.min.js" integrity="sha512-i+JuurEwS1TBFIkaoI0KNhkdiR2yu5nAVdFJ/3Sm3BVbMIkq/1Nv/JsFGUZsqB4VKV6vj1wP5yi1aqyxenx2kw==" crossorigin="anonymous"></script>
mode: application/x-httpd-php-->
            <style type="text/css">
              .CodeMirror {
                border: 1px solid #eee;
                height: auto;
                min-height: 80px;
                background-color: #f8f9fa;
              }              
            </style>
            <script type="text/javascript">
              document.addEventListener("DOMContentLoaded", function(e){
                initCodeMirror = function(selector, config) {
                  if ( typeof selector === 'string') {
                    selector = $(selector).get(0);
                  }  
                  if (typeof config === undefined) {
                    config = {
                      lineNumbers: true,
                      lineWrapping: true,
                      mode: {
                        name: "smarty", 
                        version: 3, 
                        baseMode: "text/html"
                      },
                      matchBrackets: true,
                      showCursorWhenSelecting: true,
                    };
                  }
                  return CodeMirror.fromTextArea(selector, config);
                }
              });    
            </script>    
{/if}
            <script type="text/javascript">
              document.addEventListener("DOMContentLoaded", function(e){
                var myCodeMirror;
                {if $target_editor}
                  myCodeMirror = initCodeMirror("{$target_editor}");

                  document.querySelector("#syntax-mode").addEventListener("click", function(event) {
                    if (this.checked == true) {
                      myCodeMirror.setOption("mode", {
                        name: 'smarty', 
                        version: 3
                      });        
                    } else {
                      myCodeMirror.setOption("mode", {
                        name: 'smarty', 
                        version: 3, 
                        baseMode: 'text/html'
                      });                  
                    }
                  });
                  document.addEventListener("shown.bs.tab", function(event) { //fix content not fully loaded 
                    myCodeMirror.refresh();
                  });
                  document.addEventListener("shown.bs.collapse", function(event) { //fix content not fully loaded 
                    myCodeMirror.refresh();
                  });
                {/if}
              });  
            </script>