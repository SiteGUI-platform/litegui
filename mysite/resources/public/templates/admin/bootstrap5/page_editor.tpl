<link href='{$system.cdn}/{$template}/assets/css/layout.editor.css?v=2' rel='stylesheet' />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<script defer type="text/javascript" src="{$system.cdn}/{$template}/assets/js/jquery.ui.touch-punch.js"></script>
<script defer src="{$system.cdn}/{$template}/assets/js/postmessage.js" id="sg-post-message" data-origin="{$system.url}"></script>

<div class="col-12"></div>
<div id="block_left" class="col-auto p-0 navbar-default" role="navigation">
  <div class="logo-fixed py-1">
    <a href="{$system.url}{$system.base_path}"><img src="{$system.cdn}/{$template}/assets/img/logo.png?v=3"></a>
  </div> 
  <!-- Sidebar here -->
</div>

<div id="block_right" class="content-pane col sg-bg-light px-0"> 
  <!-- Toolbar here -->
  <iframe id='wysiwyg-frame' class="mx-auto d-block shadow-none" title="WYSIWYG Editor" src='{$links.editor}?frame=wysiwyg' tabindex="0" allowtransparency="true" frameborder="0"></iframe>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(e){
  $('body').addClass('sidebar-small bg-light').removeClass('bg-white');  
  //enable tooltip
  new bootstrap.Tooltip(document.body, {
    selector: '[data-bs-toggle="tooltip"]'
  });  
});
</script> 
<style type="text/css">
.sidebar-toggler {
  position: fixed;
  top: 10px;
  left: 10px;
  z-index: 1500;
  -webkit-transition: all 0.5s ease;
  -moz-transition: all 0.5s ease;
  -o-transition: all 0.5s ease;
  transition: all 0.5s ease;
}
.sidebar-small .sidebar-toggler {
  left: 200px;
}
@media (min-width: 768px) {
  .sidebar-small .sidebar-toggler {
    left: 260px;
  }
}  
</style>  
{if $html.onboard_wysiwyg}
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(e){
  const driverObj = window.driver.js.driver({
    showProgress: true,
    steps: [
      { 
        element: '#tab-label-default-elements', 
        popover: { 
          title: Sitegui.trans('Visual Editor Quick Tour'), 
          description: Sitegui.trans("Let's walk you through some core features of the Visual Editor to get yourself familiar with it."), 
          side: "left", 
          align: 'start',
        }
      },{ 
        element: '#sg-blocks__default', 
        popover: { 
          title: Sitegui.trans('Blocks Panel'), 
          description: Sitegui.trans('This panel contains premade and theme specific blocks to help you build a complete web page. You may drag and drop a block to the right editor panel or click to select a block and click anywhere on the editor side to drop it at that position'), 
          side: "left", 
          align: 'center',
          onNextClick: () => {
            const selectors = [
              "#sg-sidebar .sg-block-wrapper", 
              ".sg-toolbox-edit", //iframe
              ".sg-toolbox-add", //iframe
              ".sg-editor-add",
              "#sg-editor-done",
            ]
            selectors.forEach(sel => {
              $('body').on('click', sel, () => {
                driverObj.moveNext()
              })
              $('body', $('#wysiwyg-frame').contents()).on('click', sel, () => {
                driverObj.moveNext()
              })
            })
            $('#layout-editor > .tab-pane', $('#wysiwyg-frame').contents()).on('click', (ev) => {
              //work-around for click to drop first item, move to next step
              if (ev.target.matches('.ui-sortable') && ev.target.children && ev.target.children.length < 2){
                driverObj.moveNext()
              }
            })

            driverObj.moveNext();
          } 
        }
      },{ 
        element: '.sg-block-wrapper:nth-child(6)', 
        popover: { 
          title: Sitegui.trans('Select Block'), 
          description: Sitegui.trans('Please click on the block'), 
          side: "left", 
          align: 'center',
          onNextClick: (el) => {
            $(el).effect("highlight")
          } 
        },
      },{ 
        element: '#wysiwyg-frame', 
        popover: { 
          title: Sitegui.trans('Drop the block'), 
          description: Sitegui.trans('Move mouse over the editor area and click/release the mouse to drop the block.'), 
          side: "bottom", 
          align: 'center',          
        }
      },{ 
        element: '#wysiwyg-frame', 
        popover: { 
          title: Sitegui.trans('Add more blocks'), 
          description: Sitegui.trans('You should see the Addition button <span class="btn btn-sm border-0 btn-warning text-dark rounded-circle"><i class="bi bi-plus-lg"></i></span> when you move mouse over the block. Click the button to add another block, a placeholder should appear after the current block.'), 
          side: "bottom", 
          align: 'center',
          onNextClick: (el) => {
            $('.ui-sortable .row:first-child', $(el).contents()).effect("highlight")
          }  
        }
      },{ 
        element: '#sg-blocks__default', 
        popover: { 
          title: Sitegui.trans('Select another blocks'), 
          description: Sitegui.trans('Click on any block in the panel to add it to the placeholder on the editor area'), 
          side: "bottom", 
          align: 'center',
          onNextClick: (el) => {
            $(el).find('.sg-block-wrapper').effect("highlight")
          } 
        }
      },{ 
        element: '#wysiwyg-frame', 
        popover: { 
          title: Sitegui.trans('Edit the block'), 
          description: Sitegui.trans('Now move the mouse over one of the newly added blocks to see the context menu <span class="btn btn-sm border-0 btn-warning text-dark rounded-circle"><i class="bi bi-three-dots"></i></span> and click on the edit button <span class="btn btn-sm border-0 btn-warning text-dark rounded-circle"><i class="bi bi-pencil-square"></i></span> to edit the content.'), 
          side: "bottom", 
          align: 'center',
          onNextClick: (el) => {
            $('.ui-sortable .row:first-child', $(el).contents()).effect("highlight")
          } 
        }
      },{ 
        element: '#sg-editor__text', 
        popover: { 
          title: Sitegui.trans('Editing'), 
          description: Sitegui.trans('Here you can change the text, replace the images, add or remove block\'s elements.'), 
          side: "left", 
          align: 'center',
        }
      },{ 
        element: '.sg-editor-add', 
        popover: { 
          title: Sitegui.trans('Add element'), 
          description: Sitegui.trans('Click here to add a child element of the current block.'), 
          side: "left", 
          align: 'bottom',
        }
      },{ 
        element: '#sg-editor-done',
        popover: { 
          title: Sitegui.trans('Finish Editing'), 
          description: Sitegui.trans('Click here to finish editing the block and return to the Blocks Panel'), 
          side: "left", 
          align: 'bottom',
          onNextClick: (el) => {
            $(el).effect("shake")
          }
        }
      },{ 
        element: '.input-name', 
        popover: { 
          title: Sitegui.trans('Page Label'), 
          description: Sitegui.trans('Remember to enter a Label for your page here'), 
          side: "bottom", 
          align: 'center',
        }
      },{ 
        element: '#sg-editor-save', 
        popover: { 
          title: Sitegui.trans('Save Your Page'), 
          description: Sitegui.trans('Click here to save your page'), 
          side: "bottom", 
          align: 'center',
          onNextClick: () => {
            var nvp = 'done=wysiwyg&csrf_token='+ window.csrf_token +'&format=json';
            $.post('{$links.onboard}', nvp, function(data) { // already a json object jQuery.parseJSON(data);
              if (data.status.result == 'success') {                
              } else {
              }   
            }) 
            driverObj.moveNext();
          } 
        }
      },{ 
        popover: { 
          title: Sitegui.trans('Happy Building'), 
          description: Sitegui.trans('Thanks for taking the tour, go ahead and add more blocks to your page. You may click on <i class="bi bi-reply"></i> at the bottom right corner to exit the Visual Editor.'),
        } 
      }
    ]
  })

  driverObj.drive()
})  
</script> 
{/if}