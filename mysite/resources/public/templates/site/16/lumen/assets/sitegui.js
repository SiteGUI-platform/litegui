Sitegui = new function() {
  this.loader = document.querySelector('#sitegui-js'); //id of this script so it should be present when this is executed
  this.lang = {} //must provided by implementing script
  //translate
  this.trans = function($str, $vars = [], $plural = 0) {
    if (Array.isArray($str)) {
      let $choosen;
      $str.forEach( ($value, $key) => {
        if ($plural > $key) $choosen = $value; //use > as we dont want to use sparse array in JS 
      })
      $str = ($choosen.length)? $choosen : $str.shift();
    }
    if ($str.length && Sitegui.lang.hasOwnProperty($str) ){
      $str = Sitegui.lang[$str];
    }

    if (typeof $vars === 'object' && $vars !== null) {
      Object.keys($vars).forEach( $key => {
        $value = Sitegui.trans($vars[$key]); //also translate vars
        $search = ':'+ $key;
        $replace = $value;
        $str = $str.replaceAll($search, $replace); 
        if (typeof $value === 'string'){
          $search = ':'+ $key.toUpperCase();
          $replace = $value.toUpperCase();
          $str = $str.replaceAll($search, $replace); 

          $search = ':'+ $key[0].toUpperCase() + $value.slice(1);
          $replace = $value.length? $value[0].toUpperCase() + $value.slice(1) : '';
          $str = $str.replaceAll($search, $replace); 
        }  
      })           
    }
    return $str;
  }
}  

//load language.json
if (Sitegui.loader.dataset.locale != 'en'){
  fetch(Sitegui.loader.getAttribute('src').split('/assets/')[0] + '/lang/'+ Sitegui.loader.dataset.locale +'.json')
  .catch(err => {
    console.log(err)
  }) 
  .then(response => {
    if (response && response.ok) {
      return response.json()
    } 
    return {}
  })
  .then(json => { 
    Sitegui.lang = json
  }) 
}

Sitegui.Currency = new Intl.NumberFormat(Sitegui.loader.dataset.locale, {
  style: 'currency',
  currency: Sitegui.loader.dataset.currency,
  minimumFractionDigits: 0,
  maximumFractionDigits: Sitegui.loader.dataset.precision,
})
Sitegui.date = function(t){
  return new Date(t * 1000).toLocaleDateString(Sitegui.loader.dataset.locale, {
    timeZone: Sitegui.loader.dataset.timezone, hour12: true, day: "2-digit", month: "2-digit", year: "numeric"
  })
}
Sitegui.time = function(t){
  return new Date(t * 1000).toLocaleString(Sitegui.loader.dataset.locale, {
    timeZone: Sitegui.loader.dataset.timezone, hour12: true, hour: "2-digit", minute: "2-digit", day: "2-digit", month: "2-digit", year: "numeric"
  })
}

//Zoom Image
Sitegui.zoom = function (el){
  el.classList.contains("js-sg-zoom") || el.classList.add("js-sg-zoom")
  el.querySelectorAll("img").forEach(img => {
    img.addEventListener("click", function(ev) {
      ev.stopPropagation() 
      ev.preventDefault()
      let ow = ev.target.clientWidth
      ev.target.closest('.js-sg-zoom') && ev.target.closest('.js-sg-zoom').classList.add('sg-fullscreen')
      ev.target.closest('.sg-fullscreen').classList.toggle('js-sg-zoom')
      document.querySelector('html').classList.add('overflow-hidden')
      let nw = ev.target.clientWidth
      if (nw > ow) {
        ev.target.closest('.sg-fullscreen').scrollTo(ev.clientX*nw/ow - window.outerWidth/2, ev.clientY*nw/ow - window.outerHeight/2) 
      }
    })
  })

  if ( !el.querySelector(".js-sg-zoom-close-btn") ){
    el.insertAdjacentHTML('afterbegin', '<button type="button" class="js-sg-zoom-close-btn d-none position-fixed top-0 end-0 btn text-secondary fs-5">✕</button>')
  }
  el.addEventListener("click", function(ev) {
    if (ev.target && ev.target.matches(".js-sg-zoom-close-btn") ){ 
      document.querySelector('html').classList.remove('overflow-hidden')
      el.classList.add('js-sg-zoom')
      el.classList.remove('sg-fullscreen')
    }  
  })
}
document.addEventListener("DOMContentLoaded", function(e){ //functions relying on loader 
  //Animation when in viewport
  Sitegui.Observer = new IntersectionObserver(function (entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.remove('animate__wait');
        if (entry.target.className.indexOf('Out') == -1 && document.querySelector('.sg-animation-repeat')) { //no Out animation and no play once 
          setTimeout(function () {
            entry.target.classList.add('animate__wait');
          }, 1100);  
        } 
      } 
      //entry.target.classList.toggle('animate__off', !entry.isIntersecting);
    });
  }, {
    root: null,
    rootMargin: '0px',
    threshold: 0
  });

  document.querySelectorAll('.animate__animated').forEach(el => {
    Sitegui.Observer.observe(el);
  })

  document.querySelectorAll('.js-sg-currency').forEach(el => {
    if ( !isNaN(+el.textContent) ){
      el.textContent = Sitegui.Currency.format(el.textContent) //textContent due to visibility
    }  
    el.style.opacity = "1"
  })

  document.querySelectorAll(".js-sg-date").forEach(el => {
    if (+el.textContent > 10) {
      el.textContent = Sitegui.date(el.textContent)
      el.style.opacity = "1"
    }
  })
  document.querySelectorAll(".js-sg-time").forEach(el => {
    if (+el.textContent > 10) {
      el.textContent = Sitegui.time(el.textContent)
      el.style.opacity = "1"
    }
  })
  //Zoom Image
  document.querySelectorAll(".js-sg-zoom").forEach( Sitegui.zoom ) 
  //Quick Engagement button
  document.querySelectorAll('.js-sg-quick-engagement').forEach( el => {
    el.addEventListener('click', function(){
      if ( document.querySelector(el.dataset.bsTarget +' .js-submit-btn') && !this.classList.contains('sg-user-engaged') ){
        document.querySelector(el.dataset.bsTarget +' .js-submit-btn').click()
      } 
    })    
  })
  //dynamic modal
  document.querySelector('#dynamicModal').addEventListener('show.bs.modal', function (e) {
    var iframe = this.querySelector('iframe');
    var button = e.relatedTarget // Button that triggered the modal
    var title = button.dataset.title
    title && (document.querySelector('#dynamicModalName').innerText = title)
    var url = button.dataset.url? button.dataset.url : iframe.dataset.src +"?CKEditorFuncNum=1#elf_l1_dXBsb2Fk"; 
    //var $CKEditorFuncNum = 1; //ckeditor 4 - but still useful
    if (iframe.getAttribute('src') != url) {
      //iframe.contentDocument.body.innerHtml = ''; //clear existing content
      iframe.setAttribute('src', url)
      iframe.addEventListener('load', function(){ //ready fired too soon, used load instead
          this.parentNode.querySelector(".progress").classList.add('d-none');
          this.classList.remove('d-none');
        })
      this.querySelector(".progress").classList.remove('d-none');
      document.querySelector('#dynamicModalReload').classList.add('d-none')
      document.querySelector('#dynamicModalLink').setAttribute('href', url.replace('sgframe=1', '') )
    } else {
      iframe.classList.remove('d-none');
      iframe.parentNode.querySelector(".progress").classList.add('d-none');  
      document.querySelector('#dynamicModalReload').classList.remove('d-none')
    }
  })
  document.querySelector('#dynamicModal').addEventListener('hide.bs.modal', function(e) {
    this.querySelector('iframe').classList.add('d-none');
  });

  document.querySelector('#dynamicModalReload').addEventListener('click', function (e) {
      var iframe = this.closest('.modal-content').querySelector('iframe');
      iframe.classList.add('d-none')
      iframe.setAttribute('src', iframe.getAttribute('src') )
      iframe.addEventListener('load', function(){ //ready fired too soon, used load instead
          this.parentNode.querySelector(".progress").classList.add('d-none');
          this.classList.remove('d-none');
        })
      iframe.parentNode.querySelector(".progress").classList.remove('d-none');  
  })

  //Product Variant Hover Selection
  document.querySelectorAll('.js-sg-collection-item .card.thumbnail').forEach(el => {
    el.addEventListener('mouseenter', function(ev){
      el.querySelectorAll('.js-sg-variant-selection[data-src]:not(.sg-loaded)').forEach(img => {
        img.classList.add('sg-loaded')
        let newEl = document.createElement('IMG')
        newEl.classList.add('img-fluid', 'card-img-top', 'position-absolute', 'top-0', 'start-0', 'opacity-0')
        newEl.setAttribute('src', img.getAttribute('data-src'))
        el.querySelector('.sg-img-container').append(newEl)
        el.querySelector('.card-img-top:nth-child(2)') && el.querySelector('.card-img-top:nth-child(2)').classList.remove('opacity-0')
        
        img.addEventListener('mouseenter', async function(){
          await el.querySelector('.card-img-top:nth-child(2)') && await el.querySelector('.card-img-top:nth-child(2)').classList.add('opacity-0') 
          newEl.classList.remove('opacity-0')
        })
        img.addEventListener('mouseleave', async function(){
          await newEl.classList.add('opacity-0') 
          el.querySelector('.card-img-top:nth-child(2)') && el.querySelector('.card-img-top:nth-child(2)').classList.remove('opacity-0')
        })
      })
    })
  })
})     