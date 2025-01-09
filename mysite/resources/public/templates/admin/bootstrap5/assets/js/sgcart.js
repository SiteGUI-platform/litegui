//define Cart
Sitegui.Cart = new function() {
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
  //update cart UI
  this.template = null
  this.update = function(response) {
    //we want to get cart's data ASAP but sometimes it comes before page has finished rendering and fails to execute because dom not ready
    //check the last item we need to update at each Interval and update cart when it is ready
    if ( document.querySelector('.sg-total') ) { //check page document.readyState === 'complete'
      var response = JSON.parse(response);
      if (response.status.result == 'success' || response.status.result == 'warning') {
        var template = Sitegui.Cart.template || document.querySelector('.sg-item:first-child');
        var container = document.getElementById('sg-item-container')
        //show warning message  
        if (response.status.result == 'warning') {
          Sitegui.Cart.showMessage(response.status.message.join('. '))
        }
        //update cart UI
        container.innerText = ''
        if ( response.cart && response.cart.items && Object.keys(response.cart).length != 0 && Object.keys(response.cart.items).length != 0 ){
          Object.keys(response.cart.items).forEach( key => {
            var item = template.cloneNode(true)
            var value = response.cart.items[ key ]
            item.setAttribute('id', 'sg-item'+ key)
            item.classList.remove('d-none')
            //console.log(value.images);
            let thumb = item.querySelector('.sg-thumbnail')
            if (thumb){
              if (value.images){
                thumb.setAttribute('src', value.images[0])
                thumb.parentNode.classList.remove('d-none')
              } else {
                thumb.parentNode.classList.add('d-none')
              }  
            }
            if (value.product.name) {
              item.querySelector('.sg-item-name').innerText = value.product.name
            } else {
              item.querySelector('.sg-item-name').innerText = ''
            } 
            if (value.options && value.options.length) {
              item.querySelector('.sg-item-variant').innerText = Object.values(value.options)[0]
            } else {
              item.querySelector('.sg-item-variant').innerText = ''
            } 
            if (value.product_fields && value.product_fields.domain && value.product_fields.domain.value && isNaN(value.product_fields.domain.value) ){
              item.querySelector('.sg-item-variant').innerText = item.querySelector('.sg-item-variant').innerText +' '+ value.product_fields.domain.value
            }
            if (value.product_fields && value.product_fields.length){
              item.querySelector('.sg-item-variant').append('<a href="#">'+ Sitegui.trans('Edit') +'</a>')
            }
            if (item.querySelector('.sg-item-qty') ){
              item.querySelector('.sg-item-qty').innerText = (value.qty >= 0)? value.qty : ''
              item.querySelector('.sg-item-qty').setAttribute('data-id', value.id)     
            }         
            item.querySelector('.sg-item-amount').innerText = Sitegui.Currency.format(value.amount)
            
            let linkEl = item.querySelector('.sg-item-url')
            if (linkEl){
              linkEl.setAttribute('href', linkEl.dataset.url +'/'+ value.product.slug)
            }    

            let removeBtn = item.querySelector('.sg-item-remove')
            if (removeBtn){
              removeBtn.addEventListener('click', function(ev){
                ev.preventDefault()
                Sitegui.Cart.ajax('POST', removeBtn.getAttribute('data-url'), 'item='+ key, Sitegui.Cart.update)
                removeBtn.parentNode.parentNode.classList.add('d-none')
              })
            }  

            container.appendChild(item);
          })
          document.getElementById("sg-checkout-btn").removeAttribute("disabled")
        } else {
          document.getElementById('sg-checkout-btn').setAttribute('disabled', true)
          document.getElementById('sg-coupon-btn').setAttribute('disabled', true)
          document.getElementById('sg-coupon-input').setAttribute('disabled', true)
          template.classList.add('d-none')
          document.getElementById('sg-item-container').appendChild(document.createTextNode(Sitegui.trans('Cart is empty')))
        }
        document.querySelector('.sg-subtotal').innerText = Sitegui.Currency.format(response.cart.subtotal ?? 0)
        if (response.cart.coupon && response.cart.coupon.amount != null) {
          document.querySelector('.sg-coupon-code').innerText = response.cart.coupon.amount? response.cart.coupon.code : ''
          document.querySelector('.sg-coupon-amount').innerText = response.cart.coupon.amount? ' -'+ Sitegui.Currency.format(response.cart.coupon.amount) : '-'
        }
          
        if (response.cart.tax && response.cart.tax.amount != null) {
          if (response.cart.tax.name){
            document.querySelector('.sg-tax-name').innerText = '('+ response.cart.tax.name +')'
          }  
          document.querySelector('.sg-tax-amount').innerText = Sitegui.Currency.format(response.cart.tax.amount)
        }  
        document.querySelector('.sg-total').innerText = Sitegui.Currency.format(response.cart.total ?? 0)
        document.querySelector('.sg-count').innerText = response.cart.count ?? ''           
      } else { //show error message
        Sitegui.Cart.showMessage(response.status.message.join('. '))
      }  
    } else {
      var interval = setInterval(function() {
        if( document.querySelector('.sg-total') ) {
            Sitegui.Cart.update(response);
            clearInterval(interval);
        }    
      }, 2);         
    }          
  }  

  this.showMessage = function(text) {
    document.getElementById("sg-gateway-message") && (document.getElementById("sg-gateway-message").innerText = text);
  }
  // Show a spinner on payment submission
  this.changeLoadingState = function(isLoading) {
    if (isLoading) {
      this.showMessage('');
      document.getElementById("sg-checkout-btn").setAttribute('disabled', true)
      document.getElementById("sg-checkout-spinner").classList.remove("d-none")
      document.getElementById("sg-checkout-text").classList.add("d-none")
    } else {
      document.getElementById("sg-checkout-btn").removeAttribute("disabled")
      document.getElementById("sg-checkout-spinner").classList.add("d-none")
      document.getElementById("sg-checkout-text").classList.remove("d-none")
    }
  }
  //show login button
  this.showLogin = function(response) {
    var response = JSON.parse(response);
    if (response.status.result == 'success'){
      if (response.exist){
        document.getElementById('js-sg-password') && document.getElementById('js-sg-password').classList.add('show')
      } else {
        document.getElementById('js-sg-password') && document.getElementById('js-sg-password').classList.remove('show')
      }
    }  
  }
  //check login state
  this.checkLogin = function(response) {
    var response = JSON.parse(response)
    if (response.status && response.status.result && response.status.result == 'success'){
      window.location.href = window.location.href
    } else if ( checkBtn = document.querySelector('#js-sg-password button') ){
      if (checkBtn.dataset.check > 1){
        checkBtn.dataset.check = checkBtn.dataset.check - 1
      } else {
        checkBtn.innerText = Sitegui.trans('Incorrect Password')
        checkBtn.classList.add('btn-warning')
      }  
    }  
  } 
  //setup listener for form events
  this.initListener = function() {
    Sitegui.Cart.template = document.querySelector('.sg-item:first-child')
    let form = document.getElementById("sg-payment-form")
    if (form){
      form.addEventListener('change', function(ev) {
        Sitegui.Cart.showMessage('');
      })
    }
    let couponBtn = document.getElementById('sg-coupon-btn')
    if (couponBtn) {
      couponBtn.addEventListener('click', function(ev){
        ev.preventDefault()
        let couponCode = document.getElementById('sg-coupon-input').value
        Sitegui.Cart.showMessage('')
        Sitegui.Cart.ajax('POST', couponBtn.getAttribute('data-url'), 'item='+ couponCode, Sitegui.Cart.update)
      })
    }
    document.getElementById('sg-item-container') && document.getElementById('sg-item-container').addEventListener('click', function(ev){
      Sitegui.Cart.showMessage('')
      if ( ev.target.matches('.sg-increase-btn') ){
        let params = 'item[id]='+ ev.target.previousElementSibling.getAttribute('data-id') +'&item[qty]='+ (parseInt(ev.target.previousElementSibling.innerText) + 1)
        Sitegui.Cart.ajax('POST', cartjs.dataset.linkCart +'/update', params, Sitegui.Cart.update);
      } else if ( ev.target.matches('.sg-decrease-btn') ){
        let params = 'item[id]='+ ev.target.nextElementSibling.getAttribute('data-id') +'&item[qty]='+ (parseInt(ev.target.nextElementSibling.innerText) - 1)
        if (ev.target.nextElementSibling.innerText < 1) {
          ev.target.parentNode.parentNode.classList.add('d-none')
        }
        Sitegui.Cart.ajax('POST', cartjs.dataset.linkCart +'/update', params, Sitegui.Cart.update);
      } 
    })
    //check existing customer
    document.querySelectorAll('.js-sg-username').forEach( el => { 
      el.addEventListener('change', function(ev){
        this.value && Sitegui.Cart.ajax('POST', cartjs.dataset.linkCart +'/exist', 'item='+ this.value, Sitegui.Cart.showLogin)
      }) 
    })
    document.querySelector('#js-sg-password input') && 
    document.querySelector('#js-sg-password input').addEventListener('keyup', function(ev){
      document.querySelector('#js-sg-password button').innerText = Sitegui.trans('Login')
      document.querySelector('#js-sg-password button').classList.remove('btn-warning')
    })  
    //Login
    document.querySelector('#js-sg-password button') && 
    document.querySelector('#js-sg-password button').addEventListener('click', function(ev){
      ev.preventDefault()
      let query = 'user_login=1&password='+ document.querySelector('#js-sg-password input').value
      let check = 0
      if ( value = document.querySelector('.js-sg-username[name="mobile"]').value ){
        Sitegui.Cart.ajax('POST', cartjs.dataset.linkCart +'/checkout', query +'&username='+ value, Sitegui.Cart.checkLogin)
        this.dataset.check = ++check
      }  
      //check using email
      if ( value = document.querySelector('.js-sg-username[name="email"]').value ){
        Sitegui.Cart.ajax('POST', cartjs.dataset.linkCart +'/checkout', query +'&username='+ value, Sitegui.Cart.checkLogin)
        this.dataset.check = ++check
      }
      if (check){  
        this.innerText = Sitegui.trans('Loading') +'...'
      }  
    }) 

    //update using selected address
    var updateAddress = function (container, address){
      if ( address && address.country ){
        if (address.street2 && container.querySelector('.sg-address-street2') ){
          container.querySelector('.sg-address-street2').value = address.street2
        }
        container.querySelector('.sg-address-street input').value = address.street??''
        document.querySelector('input[name$="\[rstreet\]"]') && 
        !document.querySelector('input[name$="\[rstreet\]"]').value && 
        (document.querySelector('input[name$="\[rstreet\]"]').value = address.street??'')
        
        container.querySelector('.sg-address-city input').value = address.city??''
        document.querySelector('input[name$="\[rcity\]"]') && 
        !document.querySelector('input[name$="\[rcity\]"]').value && 
        (document.querySelector('input[name$="\[rcity\]"]').value = address.city??'')
        
        container.querySelector('.sg-address-state').value = address.state??''
        document.querySelector('input[name$="\[rstate\]"]') && 
        !document.querySelector('input[name$="\[rstate\]"]').value && 
        (document.querySelector('input[name$="\[rstate\]"]').value = address.state??'')
        
        container.querySelector('.sg-address-zip') && 
        (container.querySelector('.sg-address-zip').value = address.zip??'')
        document.querySelector('input[name$="\[rzip\]"]') && 
        !document.querySelector('input[name$="\[rzip\]"]').value && 
        (document.querySelector('input[name$="\[rzip\]"]').value = address.zip??'')
        
        document.querySelector('select[name$="\[rcountry\]"]') && 
        !document.querySelector('select[name$="\[rcountry\]"]').value && 
        $('select[name$="\[rcountry\]"]').selectpicker('val', address.country??'')

        container.querySelector('.sg-address-street').classList.add('d-none')
        container.querySelector('.sg-address-city').classList.add('d-none')
        container.querySelector('.sg-address-state-zip').classList.add('d-none')
        container.querySelector('.sg-address-country').classList.add('d-none')
        return true
      } else {
        container.querySelector('.sg-address-street').classList.remove('d-none')
        container.querySelector('.sg-address-city').classList.remove('d-none')
        container.querySelector('.sg-address-state-zip').classList.remove('d-none')
        container.querySelector('.sg-address-country').classList.remove('d-none')
        return false
      }  
    } 
    document.getElementById('sg-address-billing') && 
    document.getElementById('sg-address-billing').addEventListener('change', function(ev){
      console.log(ev.target.options[ev.target.selectedIndex].dataset.country)
      updateAddress(ev.target.parentNode.parentNode.parentNode, ev.target.options[ev.target.selectedIndex].dataset) && 
      $('.billing-country').selectpicker('val', ev.target.options[ev.target.selectedIndex].dataset.country)
    })
    document.getElementById('sg-address-shipping') && 
    document.getElementById('sg-address-shipping').addEventListener('change', function(ev){
      updateAddress(ev.target.parentNode.parentNode.parentNode, ev.target.options[ev.target.selectedIndex].dataset) && 
      $('.shipping-country').selectpicker('val', ev.target.options[ev.target.selectedIndex].dataset.country)
    })

    document.getElementById('sg-amount-paid') && 
    document.getElementById('sg-amount-paid').addEventListener('keyup', function(ev){
      if (this.value - this.dataset.total >=0){
        document.querySelector('#sg-amount-change').innerText = Sitegui.Currency.format(this.value - this.dataset.total)
        document.querySelector('#sg-amount-paid-label').classList.add('text-success')
      } else {
        document.querySelector('#sg-amount-change').innerText = 0
        document.querySelector('#sg-amount-paid-label').classList.remove('text-success')
      }
    })  
  }
  //update cart UI
  this.addresses = {}
  this.showAddress = function(response) {
    var response = JSON.parse(response);
    if (response.status.result == 'success' && response.rows){
      response.rows.forEach(address => {
        Sitegui.Cart.addresses[address.id] = address 
        let el = document.createElement('option')
        el.setAttribute('value', address.id)
        el.innerText = (address.name??'') +', '+ (address.street2??'') +' '+ (address.city??'') +', '+ (address.state??'')
        document.querySelector('#sg-address-billing select') && 
        document.querySelector('#sg-address-billing select').prepend(el)
        
        document.querySelector('#sg-address-shipping select') && 
        document.querySelector('#sg-address-shipping select').prepend(el.cloneNode(true))
      })
      if (document.getElementById('sg-address-billing') ){
        document.getElementById('sg-address-billing').classList.remove('d-none')
        //trigger change selected address
        document.querySelector('#sg-address-billing option').selected = 'selected' //first option
        document.querySelector('#sg-address-billing select').dispatchEvent(new Event('change', { 'bubbles': true }))
      }  
      if (document.getElementById('sg-address-shipping') ){
        document.getElementById('sg-address-shipping').classList.remove('d-none')
        document.querySelector('#sg-address-shipping option').selected = 'selected'
        document.querySelector('#sg-address-shipping select').dispatchEvent(new Event('change', { 'bubbles': true }))
      }  
    }
  }
}
//init Cart
let cartjs = document.querySelector('#sg-js-cart');
if (cartjs) {
  if (cartjs.dataset.linkCart) {
    Sitegui.Cart.ajax('GET', cartjs.dataset.linkCart +'.json', '', Sitegui.Cart.update);
  }
  document.addEventListener("DOMContentLoaded", function(e){
    Sitegui.Cart.initListener()
    if (cartjs.dataset.billingCountry) {
      //selectpicker is a jquery plugin
      $('.billing-country').selectpicker('val', cartjs.dataset.billingCountry) 
    }
    if (cartjs.dataset.shippingCountry) {
      $('.shipping-country').selectpicker('val', cartjs.dataset.shippingCountry) 
    }
    if (cartjs.dataset.linkAddress) {
      Sitegui.Cart.ajax('GET', cartjs.dataset.linkAddress, '', Sitegui.Cart.showAddress);
    }
    if ("undefined" !== typeof history.pushState) { //handle back button -> err_cache_missed
      $state = {
        "url": cartjs.dataset.linkCart + ((cartjs.dataset.step != 0)? 
          '/checkout?step='+ cartjs.dataset.step + ((cartjs.dataset.guest == 'guest')? '&guest_checkout=1' : '') : '' 
        ),
        'page': 'Checkout Step '+ cartjs.dataset.step
      }
      history.replaceState($state, $state.page, $state.url)
    } 
  })   
}