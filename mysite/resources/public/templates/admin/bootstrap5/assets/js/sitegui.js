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
    if ($str && $str.length && Sitegui.lang.hasOwnProperty($str) ){
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
Sitegui.duration = function (t){ 
  if (t < 60) {
    return t +'s'
  } else if (t < 3600) {
    return Math.round(t / 60) +'m' 
  } else if ( !isNaN(t) ) {
    let h = Math.floor(t / 3600)
    return h +'h '+ Math.round( (t - 3600*h) / 60) +'m'
  } else {
    return t
  }
}
//Multi-item carousel, same children classes as carousel but different root class
Sitegui.carousel = function() {
    var carousel = $(this);
    var items = carousel.find(".carousel-inner").children().css('opacity', .8);
    var noloop  = carousel.data("noloop") > 0? 1 : 0; //use data to automaticaly convert Int or use parseInt(carousel.attr("data-noloop")
    var dynamic = carousel.data("dynamic") > 0? 1 : 0;
    var length  = (carousel.data("length") && carousel.data("length") <= items.length)? carousel.data("length") : (items.length??1);
    var current = (carousel.data("slide-to") && carousel.data("slide-to") <= items.length-length*noloop)? carousel.data("slide-to") : 0;
    var slide = function(i, v) {
        $(this).removeClass("order-last d-none");
        if (current >= items.length-length && i < current+length-items.length) {
            $(this).addClass("order-last"); //looping slide, re-order beginning items 
        } else if (i < current || i >= current+length) {
            $(this).addClass("d-none");
        }      
    };
    var update = function(el) {
        items = carousel.find(".carousel-inner").children(); //update items
        //length should never be 0
        if (items.length > carousel.data("length")) {
            length = carousel.data("length");
        } else if (items.length > 0){
            length = items.length;
        } 

        current = (items.length && current > items.length-length*noloop)? items.length-length*noloop : current; 
        if (current < items.length - length) {
            carousel.find('[data-bs-slide="prev"]').removeClass("d-none");
        } else {
            carousel.find('[data-bs-slide]').addClass("d-none");
        }
    }
    //carousel.attr("data-length", length); //set it for future reference   
    items.each(slide);
    if (noloop) {
        if (current <= 0) {
            carousel.find('[data-bs-slide="prev"]').addClass('d-none');
        }  
        if (current >= items.length - length) { //always have one control incase items.length == length
            carousel.find('[data-bs-slide="next"]').addClass("d-none");
        }        
    }

    //prev, next control
    carousel.on("click", '[data-bs-slide="next"]', function(ev) {
       ev.preventDefault();
       dynamic && update(); 
       $(this).siblings().removeClass('d-none');
       current++;

       if (noloop && current >= items.length - length) {
           $(this).addClass('d-none');
           if (current > items.length - length) current = items.length - length;
       } 
       if (current >= items.length) {
           current = 0;
       }
       items.each(slide);
    })
    .on("click", '[data-bs-slide="prev"]', function(ev) {
       ev.preventDefault();
       dynamic && update(); 
       $(this).siblings().removeClass('d-none');
       current--;
       if (noloop && current <= 0) {
           $(this).addClass('d-none');
           current = 0;
       }         
       if (current < 0) {
           current = items.length - 1;
       }
       items.each(slide);
    })
    //listen to updated event and update items
    .on('updated', function(){ 
        update();
        current = (items.length >= length)? items.length - length : 0 //show the newly added image last
        items.each(slide);
        input = $(this).parent().find('input.get-image-callback');
        //last image removed, show input or hide if not support multiple values
        if ($(this).find('.carousel-inner').children().length > 0) { //has image item
            if ( ! input.is('.multiple-values') ){
              input.parent().addClass('d-none');
            }
        } else {  
            input.parent().removeClass('d-none');
        } 
    })
    //Showing handlers for removable carousel items when hovering
    .on({ 
        mouseover: function () {
            var container = $(this).css({
                opacity: 1, 
                transition: "opacity .15s linear" 
            });
            var control = container//.find('img').addClass('')
                .find('.overlay');
            var parent  = container.parent();
            if (control.length ){
                control.removeClass('d-none');
            } else {
                control = $('<div class="overlay btn-group" style="position:absolute; top:50%; left:50%; transform: translate(-50%, -50%);"><span class="removable-handler btn btn-dark" title="Remove"><i class="bi bi-trash"></i></span></div>');
                if (parent.closest('.carousel-multi') && parent.closest('.carousel-multi').is(".multiple-values") ){
                    control.prepend('<i class="star-handler btn btn-dark bi bi-star" title="Mark Default"></i> ')
                }
                control.find(".star-handler").on('click', function() { 
                    container.prependTo(parent)
                           .siblings().find('.star-handler').removeClass('text-warning active');
                    parent.closest('.variant')
                           .find('[data-bs-toggle="collapse"]').attr('src', container.find('img').attr('src'));
                    $(this).addClass('text-warning active');       
                    parent.trigger('updated'); //let carousel know to update its items
                });
                control.find(".removable-handler").on('click', function() { 
                    //var parent = $(this).parent().parent().parent();
                    if ($(this).siblings('.star-handler').hasClass('active')) { //default image
                        var img = (container.next().find('img').attr('src'))? container.next().find('img').attr('src') : 'https://via.placeholder.com/120x80/5a5c69/fff?text=Add%20Image';
                        parent.closest('.variant')
                           .find('[data-bs-toggle="collapse"]').attr('src', img);
                    }
                    container.remove();
                    parent.trigger('updated'); //let carousel know to update its items
                });             
                control.prependTo(container);
            }
            //always check due to dynamic item update/remove
            if (!container.prev().length) { //first col
                control.find(".star-handler").addClass('text-warning active');
            }                   
        },
        mouseout: function () {
            $(this).css('opacity', .8)
                   //.find('img').removeClass('rounded-pill')
                   .find('.overlay').addClass('d-none');
        },
    }, '.carousel-inner.item-removable .col'); //carousel item container      
};

document.addEventListener("DOMContentLoaded", function(e){ //functions relying on loader 
  //Animation when in viewport
  Sitegui.Observer = new IntersectionObserver(function (entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.remove('animate__off');
        if (entry.target.className.indexOf('Out') == -1 && document.querySelector('.sg-animation-repeat')) { //no Out animation and no play once 
          setTimeout(function () { 
            entry.target.classList.add('animate__off');
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

  document.querySelectorAll(".js-sg-duration").forEach(el => {
    if (+el.textContent > 0) {
      el.textContent = Sitegui.duration(el.textContent)
      el.style.opacity = "1"
    }
  })
})      