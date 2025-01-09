<link href='{$system.cdn}/{$template}/assets/css/layout.editor.css?v=2' rel='stylesheet' />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<script defer type="text/javascript" src="{$system.cdn}/{$template}/assets/js/jquery.ui.touch-punch.js"></script>
<script defer src="{$system.cdn}/{$template}/assets/js/postmessage.js" id="sg-post-message" data-origin="{$system.url}"></script> 
{if !$hide_editor_toolbar}
  <!-- Toolbar here -->
  <iframe id="wysiwyg-frame" class="mx-auto d-block shadow-none" title="WYSIWYG Editor" src="{$links.editor}?frame=wysiwyg" tabindex="0" allowtransparency="true" frameborder="0"></iframe>
  <script type="text/javascript">
  document.addEventListener("DOMContentLoaded", function(e){
    //Save button on parent frame
    $('#sg-save-widget').on('click', async function (ev) {
      ev.preventDefault();
      let iframe = document.querySelector('#wysiwyg-frame').contentWindow.document
      $('#layout-editor > .tab-pane', iframe).click(); //exit editing 
      //exit editing code
      $('#layout-editor pre code .CodeMirror', iframe).each(function() {
        $(this).parent().text(this.CodeMirror.getValue());
      });

      $('#layout-editor > .tab-pane', iframe).each(function () {
        $(this, iframe).find(".sg-toolbox-overlay").remove()
        $(this, iframe).find(".sg-block-wrapper").removeClass('ui-sortable-handle ui-draggable ui-draggable-handle ui-resizable');//, 'sg-block-hover');
        $('<textarea name="widget[data]['+ $(this, iframe).attr("data-lang") +']"></textarea>').appendTo($('#form-widget')).val($(this, iframe).html());
      });
      $(this).closest('form').submit();
    }) 

    $('body').addClass('sidebar-small');  
    //enable tooltip
    new bootstrap.Tooltip(document.body, {
      selector: '[data-bs-toggle="tooltip"]'
    });  
  });
  </script> 
  <style type="text/css">
  .content-pane {
    flex-direction: row;
  }
  #widget-editor {
    display: flex;
    flex-direction: column;
  }  
  #sg-editor-save,
  #sg-editor-toolbar .sidebar-toggler,
  #sg-editor-toolbar #sg-multi-label {
    display: none;
  }
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
{/if}  
