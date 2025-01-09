document.addEventListener("DOMContentLoaded", function(e){ //functions relying on loader 
    window.csrf_token = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({
        data: { 
            csrf_token: window.csrf_token,
            format: 'json'       
        },
        dataType: "json",
        xhrFields: {
            withCredentials: true
        },
        crossDomain: true,
        //headers: { 'csrf_token' : window.csrf_token }
        statusCode: {
            401: function () {
                location.reload()
            }
        }
    });
    $('form').each(function() {
        $(this).append('<input type="hidden" name="csrf_token" value="'+ window.csrf_token +'">');
    });
    //get notifications
    Sitegui.Notification = new function() {
        let notificationApi = document.querySelector('#sitegui-js').dataset.notification
        let unseenEl = document.querySelector('.js-sg-notification-unseen')
        let unseenClass = 'fw-semibold'
        let insertBefore
        let _poller = null;
        this.poll = function(){
            if (notificationApi){
                $.get(notificationApi +'?cors=1', function(response) {
                    if ( ! insertBefore ){
                        insertBefore = document.querySelector('.dropdown-item.js-sg-notification-end') //init
                    } else {
                        insertBefore = insertBefore.parentNode.firstElementChild //after init => prepend new notification to the list
                    }
                    if (response.status.result == 'success') {
                        unseenEl.innerText = ''
                        if (response.total){
                            let unseen = 0
                            Object.values(response.rows).forEach(item => {
                                if ( !item.seen){
                                    unseen++
                                    item.style = unseenClass
                                    item.iconFill = '-fill'
                                } else {
                                    item.style = item.iconFill = ''
                                } 
                                item.el = insertBefore.parentNode.querySelector('#sg-notif-'+ item.id)
                                if ( ! item.el ){ //not here
                                    $('<a class="dropdown-item border-bottom p-2 '+ item.style +'"></a>')
                                    .attr('id', 'sg-notif-'+ item.id)
                                    .attr('href', notificationApi +'/edit/'+ item.id)
                                    .text(Sitegui.trans(item.type) +': '+ item.name)
                                    .prepend(
                                        $('<button type="button" class="btn btn-sm border-0 text-primary btn-outline-warning rounded-circle me-1"><i class="bi bi-bell'+ item.iconFill +' small"></i></button')
                                        .click(ev => {
                                            ev.preventDefault()
                                            ev.stopPropagation()
                                            if ( !item.seen ){
                                                const source = new EventSource(notificationApi +'/edit/'+ item.id +'.json?cors=1&csrf_token='+ window.csrf_token, { withCredentials: true }); 
                                                source.onmessage = function (event) {
                                                    if(event.data == "[DONE]") {
                                                      // SSE spec says the connection is restarted
                                                      // if we don't explicitly close it
                                                      source.close();
                                                      return;
                                                    }
                                                    var noti = JSON.parse(event.data)
                                                    if (noti.seen) {
                                                        Sitegui.Notification.markRead(ev.currentTarget.parentNode, unseenClass)
                                                        if ( ! isNaN(unseenEl.innerText) ){
                                                            unseenEl.innerText = unseenEl.innerText > 1? +unseenEl.innerText - 1 : ''
                                                        }    
                                                    }
                                                };
                                                // Handling errors in receiving SSE events
                                                source.onerror = function (error) {
                                                  console.error('Error:', error);
                                                  source.close(); // Close the connection
                                                };//*/
                                            }    
                                        })
                                    )
                                    .insertBefore(insertBefore)
                                } else if ( !item.seen ){ //present here but updated somewhere, add unseenClass anyway
                                    Sitegui.Notification.markUnRead(item.el, unseenClass)
                                } else if (item.el.classList.contains(unseenClass) ){
                                    //seen elsewhere but not updated here
                                    Sitegui.Notification.markRead(item.el, unseenClass)
                                }
                            })
                            if (unseen > 0){
                                if (unseen >= response.rowCount && unseen < response.total) {
                                    unseen = (unseen - 1) +'+' //better ux
                                }
                                unseenEl.innerText = unseen
                            }                  
                        }
                    } else {
                        insertBefore.parentNode.querySelectorAll("[id^='sg-notif-']").forEach( el => {
                            el.remove()
                            insertBefore = null
                        })
                    }
                });        
            }
        }
        this.markRead = function(el, style) {
            $(el)
            .insertBefore(
                insertBefore
                .parentNode
                .querySelector('.dropdown-item:not(.'+ style +')')
            )
            .removeClass(style) //after insertBefore because :not(style) 
            .find('.bi-bell-fill')
            .removeClass('bi-bell-fill')
            .addClass('bi-bell')
        }
        this.markUnRead = function(el, style) {
            $(el)
            .addClass(style)
            .find('.bi-bell')
            .addClass('bi-bell-fill')
            .removeClass('bi-bell')
        }
        this.start = function(){
            Sitegui.Notification.poll()
            _poller = setInterval(Sitegui.Notification.poll, 25000);
        }
        this.stop = function(){
            clearInterval(_poller)
        }    
    } 
    Sitegui.Notification.start();
    //run poller when tab is active only
    document.addEventListener('visibilitychange', function(){
        if (document.visibilityState === 'hidden') {
            Sitegui.Notification.stop()
        } else {
            Sitegui.Notification.start()
        }
    });
    // if action needs confirmation
    // bootgrid stops propagation for click so we use a custom click.namespace to propagate to body
    $('body').on('mouseenter', '[data-cors]', function(ev) {
        var url = $(this).attr('data-url');
        let iframe = $('#dataConfirmModal iframe').length? $('#dataConfirmModal iframe') : $('<iframe width="100%" height="40" frameborder="0"/>').appendTo( $('#dataConfirmModal .modal-footer') )
        if ( iframe.attr('src') != url ){           
            iframe.attr('src', url)
                .removeClass('d-none')
        }                               
        $('#dataConfirmModal .modal-footer button').addClass('d-none')
    })                                   
    .on('click.sg.datatable', '[data-confirm]', function(ev) {
        var url = $(this).attr('data-url')
        if ( $(this).attr('data-cors') ){
            $('#dataConfirmModal .modal-action').text('')
        } else {    
            var name = $(this).attr('data-name')
            var value = $(this).attr('data-value')
            var ajax = $(this).attr('data-remove')
            if (ajax) { //this is an ajax call
                $('#dataConfirmOK').attr('data-ajaxremove', ajax)
                $('#dataConfirmOK').attr('data-url', url)                  
                $('#dataConfirmOK').attr('data-nvp', name +'='+ value)                    
            } else {
                $('#dataConfirmForm').attr('action', url)
                $('#dataConfirmHidden').attr('name', name)
                $('#dataConfirmHidden').attr('value', value) 
            } 
            $('#dataConfirmModal iframe').addClass('d-none')
            $('#dataConfirmModal .modal-footer button').removeClass('d-none')
            $('#dataConfirmModal .modal-action').text(Sitegui.trans($(this).attr('data-confirm')));
        }    
        (new bootstrap.Modal(document.getElementById('dataConfirmModal'))).show()
        return false
    });

    //post via ajax and remove specified element
    $('body').on('click', '[data-ajaxremove]', function(ev) {
        ev.preventDefault();
        var href = $(this).attr('data-url');
        var nvp = $(this).attr('data-nvp') +'&csrf_token='+ window.csrf_token +'&format=json';
        var remove = $(this).attr('data-ajaxremove');
        $.post(href, nvp, function(data)  
        {
            var response = data; // already a json object jQuery.parseJSON(data);
            if (response.status.result == 'success')
            {
                var parent = $(remove).parent();
                $(remove).remove();
                parent.trigger('removed');
            }
        });
    });
    //modal should be hidden if we come back 
    $('body').on('click', '#dataConfirmOK', function(ev) {
        bootstrap.Modal.getInstance(document.getElementById('dataConfirmModal')).hide();
    });
        
    $('body').on('click', '.sidebar-toggler', function(ev) {
        ev.preventDefault();
        $("body").toggleClass("sidebar-small");
    });
    $('body').on('click', '.cart-toggler', function(ev) {
        ev.preventDefault();
        $("body").toggleClass("sidebar-small, sidebar-full");
    });
    //$("#block_left").height($("#block_right").height());
    //$("#block_left").affix({offset: {top: $("#block_header").outerHeight(true)} });

    $('#dynamicModal').on('show.bs.modal', function (e) {
      var iframe = $(this).find('iframe');
      var button = $(e.relatedTarget); // Button that triggered the modal
      var title = button.data('title');
      title && $('#dynamicModalName').text(title);
      var url = button.data('url')? button.data('url') : iframe.attr('data-src') +"?CKEditorFuncNum=1#"+ button.data('folder'); 
      //var $CKEditorFuncNum = 1; //ckeditor 4 - but still useful
      if (iframe.attr('src') != url) {
        iframe.contents().find("body").html(''); //clear existing content
        iframe.attr('src', url)
          .on('load', function(){ //ready fired too soon, used load instead
            $(this).siblings(".progress").addClass('d-none');
            $(this).show();
          })
        $(this).find(".progress").removeClass('d-none');
        $('#dynamicModalReload').addClass('d-none')
        $('#dynamicModalLink').attr('href', url.replace('sgframe=1', '') )
      } else {
        iframe.show();
        iframe.siblings(".progress").addClass('d-none');  
        $('#dynamicModalReload').removeClass('d-none')
      }
    }).on('hide.bs.modal', function(e) {
      $(this).find('iframe').hide();
    });

    $('#dynamicModalReload').on('click', function (e) {
        var iframe = $(this).closest('.modal-content').find('iframe');
        iframe.hide()
        iframe.attr('src', iframe.attr('src') )
          .on('load', function(){ //ready fired too soon, used load instead
            $(this).siblings(".progress").addClass('d-none');
            $(this).show();
          })
        iframe.siblings(".progress").removeClass('d-none');  
    })  
});
