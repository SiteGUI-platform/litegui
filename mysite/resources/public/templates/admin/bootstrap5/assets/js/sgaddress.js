//use data-step in toggle element to indicate which step it is, step 1 will have z-index = 4 -1
var Sitegui = Sitegui || {}
Sitegui.Address = new function() {
  this.completed = 0
  //send ajax request
  this.ajax = function(method, url, data, handler, handler_params = false) {
    var xhr = window.XMLHttpRequest? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
    xhr.onreadystatechange = function() {
      if (xhr.readyState > 3 ){//&& xhr.status == 200) { 
        handler(xhr.responseText, handler_params) 
      }
    }
    if (method == 'POST') {
      xhr.open('POST', url);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      var params = (typeof data == 'string')? data : Object.keys(data).map(
        function(k){ 
          return encodeURIComponent(k) +'='+ encodeURIComponent(data[k]) 
        }
      ).join('&');
      params += '&format=json&csrf_token='+ window.csrf_token
      xhr.send(params);
    } else {
      xhr.open('GET', url, true);
      xhr.send();
    }  
    return xhr;
  }
  //normalize UTF-8
  this.normalize = function(value) {
    return value
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/[đĐ]/g, 'd')
      .toLowerCase()
  }

  this.filterItemsOnInput = function(input, parent){
    input.addEventListener('keyup', function(){
      let search = Sitegui.Address.normalize(this.value)
      bootstrap.Dropdown.getOrCreateInstance(parent.querySelector('[data-bs-toggle="dropdown"]')).show()
      parent.querySelector('.sg-js-add') && (parent.querySelector('.sg-js-add').innerText = '') //clear
      parent.querySelectorAll('.dropdown-item').forEach(el => {
        if (search && Sitegui.Address.normalize(el.innerText).indexOf( search ) == -1 ){
          el.classList.add('d-none')
        } else {
          el.classList.remove('d-none')
        }
      })
      if ( parent.querySelector('.sg-js-add') ){
        if ( ! parent.querySelector('.dropdown-item:not(.d-none)') ){
          parent.querySelector('.sg-js-add').innerText = this.value
          parent.querySelector('.sg-js-add').parentNode.classList.remove('d-none')
        } else {
          parent.querySelector('.sg-js-add').parentNode.classList.add('d-none')
        }  
      } 
    })
  }
  this.createMenu = function(target, selector, params){
    let menu = params.container.querySelector(selector +'-list') 
    if ( !menu ){
      menu = document.createElement('ul')
      menu.classList.add(selector.substring(1) +'-list', 'dropdown-menu', 'dropdown-menu-scroll', 'bg-warning-subtle', 'py-0')
      if (target.dataset.step){
        menu.classList.add('z-'+ (4 - parseInt( target.dataset.step )) ) //step 1 -> z-3 (top), step 10 -> z--6
      }
      target.parentNode.classList.add('dropdown')
      target.parentNode.append(menu)
      target.setAttribute('data-bs-toggle', 'dropdown')
      target.setAttribute('data-bs-auto-close', "inside") //required to stop hiding menu immediately after we show it (due to auto-close)
      menu.addEventListener('click', function(ev){
        if ( ev.target.classList.contains('dropdown-item') ){
          target.value = ev.target.innerText
          this.childNodes.forEach(el => {
            el.classList.remove('d-none') //no more filter to show all items on the next dropdown
          })
          this.querySelector('.sg-js-add') && this.querySelector('.sg-js-add').parentNode.classList.add('d-none')
          Sitegui.Address.completed = parseInt( target.dataset.step )
        }  
      })
    }
    menu.innerText = ''
    bootstrap.Dropdown.getOrCreateInstance(target).hide() //hide first
    bootstrap.Dropdown.getOrCreateInstance(target).show() //need auto-close = inside
    return menu
  }
  this.createItem = function(content, menu){
    var li = document.createElement("li");
    li.innerText = content
    li.classList.add('dropdown-item')
    menu.appendChild(li)
    return li
  }
  this.setupFilter = function(target, menu, data, params){
    if ( menu.querySelector('.dropdown-item') ){
      target.setAttribute('readonly', true) 
      var li = document.createElement("li");
      li.classList.add('bg-warning-subtle', 'position-sticky', 'top-0', 'pt-2')
      li.innerHTML = '<span class="float-end me-2"><b class="bi bi-x-lg-none text-secondary" role="button">✕</b></span><div class="sg-dropdown-header ms-3 fw-semibold"></div><div class="input-group p-2"><input class="form-control rounded-0 shadow-none z-1">\
      <span class="input-group-text bg-transparent border-0 position-absolute end-0 pt-2 z-2">🔍</span></div>' 
      li.querySelector('.sg-dropdown-header').innerText = target.getAttribute('placeholder')
      menu.prepend(li)
      Sitegui.Address.filterItemsOnInput(li.querySelector('input'), target.parentNode)

      if (params.add > 0) {
        var li = document.createElement("li");
        li.classList.add('dropdown-item', 'd-none')
        li.innerHTML = '<b class="bi bi-plus-lg-none">+</b><span class="sg-js-add ps-3"></span>' 
        li.addEventListener('click', function(){
          target.value = this.querySelector('.sg-js-add').innerText
          this.parentNode.childNodes.forEach(el => {
            el.classList.remove('d-none') //no more filter to show all items on the next dropdown
          })
          this.classList.add('d-none')
          menu.querySelector('.input-group input').value = ''
        })
        menu.appendChild(li)
      } 
    } else {
      target.removeAttribute('readonly')
      Sitegui.Address.filterItemsOnInput(target, target.parentNode)
    }
  }
  this.showCities = function(response, params) {
    let elCity = params.container.querySelector(params.container.dataset.selectorCity)
    if ( elCity ){
      if (elCity.tagName == 'INPUT'){  
        let menu = Sitegui.Address.createMenu(elCity, params.container.dataset.selectorCity, params)
        response = JSON.parse(response)
        if (response && typeof response === 'object'){
          Object.keys(response).forEach( city => {
            let li = Sitegui.Address.createItem(city, menu)
            if ( response[ city ] ){
              li.addEventListener('click', function(){
                let elWard = params.container.querySelector(params.container.dataset.selectorStreet2)
                if (elWard){
                  let menu2 = Sitegui.Address.createMenu(elWard, params.container.dataset.selectorStreet2, params)
                  Object.keys(response[ city ]).forEach( ward => {
                    if ( response[ city ][ ward ]['ward'] ){
                      let li2 = Sitegui.Address.createItem(response[ city ][ ward ]['ward'], menu2)
                    }  
                  })
                  //Object.keys(response[ city ]).length && (elWard.value = '') //user has click city, clear ward value
                  Sitegui.Address.setupFilter(elWard, menu2, response[ city ], params)
                }  
              })
            }
          })
          //Object.keys(response).length && (elCity.value = '') //user has click state, clear city value
          Sitegui.Address.setupFilter(elCity, menu, response, params)
        }
      }  
    }  
  }  
  this.showStates = function(response, params) {
    //console.log(params, params.container, params.container.dataset)
    let elState = params.container.querySelector(params.container.dataset.selectorState)
    if ( elState ){
      if (elState.tagName == 'INPUT'){  
        try {
          response = JSON.parse(response)
          if (response && typeof response === 'object' ){
            let menu = Sitegui.Address.createMenu(elState, params.container.dataset.selectorState, params)
            !params.auto && bootstrap.Dropdown.getOrCreateInstance(elState).hide()
            menu.addEventListener('click', function(ev){
              if ( ev.target.dataset.state ){
                Sitegui.Address.ajax('GET', params.src + params.country +'/'+ ev.target.dataset.state +'.json', '', Sitegui.Address.showCities, params)  
              }  
            })
            Object.keys(response).forEach( key => {
              if ( response[key]['state'] ){
                let li = Sitegui.Address.createItem(response[key]['state'], menu);
                li.setAttribute('data-state', key)
              }
            }) 
            Sitegui.Address.setupFilter(elState, menu, response, params)
          }
        } catch(error){
          response = null
          //clear last data
          Object.keys(params.container.dataset).forEach(key => {
            if ( key.includes('selector') ){
              if (params.container.querySelector(params.container.dataset[ key ] +'-list') ){ 
                params.container.querySelector(params.container.dataset[ key ] +'-list').innerText = ''
              } 
              if (params.container.querySelector(params.container.dataset[ key ]) ){
                params.container.querySelector(params.container.dataset[ key ]).removeAttribute('readonly')
              }
            } 
          })
        }  
      }  
    }  
  }   
  this.init = function(src){
    document.querySelectorAll('.sg-js-address').forEach(el => {
      let params = {
        "container": el,
        "add": el.dataset.addrAdd??null, //add missing value
        "auto": el.dataset.addAuto??null, //display menu automatically
        "src": src.replace(/\/$/, "") +'/'
      }
      el.querySelectorAll('[data-step]').forEach(target => {
        //target.addEventListener('show.bs.dropdown', function(){
        //  console.log('show ', target)
        //})
        //target.addEventListener('hide.bs.dropdown', function(){
        //  console.log('hide ', target)
        //})
        target.addEventListener('click', function(ev){
          if (this.dataset.step > Sitegui.Address.completed + 1 && params.container.querySelector('[data-step="'+ (Sitegui.Address.completed + 1) +'"]')){
            bootstrap.Dropdown.getOrCreateInstance(params.container.
              querySelector('[data-step="'+ (Sitegui.Address.completed + 1) +'"]') ).show()
          }
        })
      })
      if (el.dataset.defaultCountry){
        params.country = el.dataset.defaultCountry.toLowerCase()
        Sitegui.Address.ajax('GET', params.src + params.country +'/index.json', '', Sitegui.Address.showStates, params)
      }
      //change country
      el.querySelector(el.dataset.selectorCountry) && el.querySelector(el.dataset.selectorCountry).addEventListener('change', function(){
        params.country = this.value.toLowerCase()
        Sitegui.Address.ajax('GET', params.src + params.country +'/index.json', '', Sitegui.Address.showStates, params)
      })  
    })
  }
}
//init Address
let jsaddr = document.querySelector('#sg-js-address');
if (jsaddr && jsaddr.dataset.source) {
  document.addEventListener("DOMContentLoaded", function(e){
    Sitegui.Address.init(jsaddr.dataset.source)
  })   
}