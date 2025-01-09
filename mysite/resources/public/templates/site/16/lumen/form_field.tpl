{function buildForm}
  {$form_script_loaded = $form_script_loaded + 1 scope="global"}
  {$keyid = $key|replace:'[':'--'|replace:']':'--'}
  <div class="form-group row mb-3 {if $field.visibility == hidden}d-none{/if}">
  {if $field.type == 'header'}
    <label class="col-12 col-form-label fs-5">
      {$field.label|trans}
    </label>    
    {if $field.description}<small class="form-text text-muted col-12 pb-3">{$field.description|trans}</small>{/if}    
  {else} 
    <label class="col-sm-{$c1|default:3} col-form-label text-sm-end">
      {$field.label|trans} {if $field.is == required}*{/if}
    </label>
    <div class="col-sm-{$c2|default:7}">
      {if $field.type == 'lookup'}
        <div {if $field.visibility != readonly || !$field.value}class="input-group dropup"{/if}>
        {if $field.visibility != readonly || !$field.value}
          <button class="input-group-text bg-transparent" type="button"><i class="bi bi-search"></i></button>
          <input class="form-control lookup-field dropdown-toggle rounded-end" type='text' placeholder="{'Name'|trans}" data-lookup="{$field.lookup_key|default:$key}" data-index="{$form_script_loaded}" {if $field.options.scope}data-scope="{$field.scope}"{/if} data-name="{$fieldPrefix}[{$key}]{if $field.is == multiple}[ ]" data-multiple="1{/if}" data-bs-auto-close="outside" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="dropdown-menu px-2" style="max-height:50vh; overflow: scroll;">{if $field.value}
            {foreach $field.value as $id => $value}
              <div class="form-check">
                <input class="form-check-input lookup-item" id="{$keyid}-lookup{$form_script_loaded}-{$id}" name="{$fieldPrefix}[{$key}]{if $field.is == multiple}[]" type="checkbox{else}" type="radio{/if}" value="{$id}" checked>
                <label class="form-check-label" for="{$keyid}-lookup{$form_script_loaded}-{$id}">
                  {if $field.images.$id}<img class="rounded-circle me-1 sg-datatable-thumb" loading="lazy" src="{$field.images.$id}">{/if}
                  {$value}
                </label>
              </div>                  
            {/foreach}
            {if $field.is != multiple}
              <div class="form-check"><input class="form-check-input lookup-item" type="radio" id="lookup-item{$form_script_loaded}-none" name="{$fieldPrefix}[{$key}]" value=""><label class="form-check-label" for="lookup-item{$form_script_loaded}-none">{'None'|trans}</label></div> 
            {/if}
          {/if}</div>
        {/if}  
        {foreach $field.value as $id => $value}
          {if $field.is == multiple || $value@last}
            <span class="input-group-text bg-transparent rounded">
              {if $field.images.$id}
                <img class="rounded-circle me-1 sg-datatable-thumb" loading="lazy" src="{$field.images.$id}">
              {/if}
              {if $field.slug}<a href="#" data-url="{$field.slug}/{$id}?sgframe=1" data-title="{$value}" data-bs-toggle="modal" data-bs-target="#dynamicModal" class="text-decoration-none" target="_blank">{$value}</a>{else}{$value}{/if}
            </span>
          {/if}
        {/foreach}
        </div> 
      {elseif $field.type == 'file'} 
        {foreach $field.value AS $file}
          <div class="input-group pb-2">
            <span class="input-group-text bg-transparent" role="button" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-url="{if $file|truncate:4:'' ne 'http'}{$links.file_view}/{/if}{$file}" data-title="Preview"><i class="bi bi-eye"></i></span>
            <input class="form-control file-upload-indicator border-end-0" type="text" id="fid{$form_script_loaded}-{$keyid}-{$file@index}"  data-upload-name="{$fieldPrefix}[{$key}][ ]" name="{$fieldPrefix}[{$key}][ ]" value="{$file}" readonly>
            {if $html.file_manager AND ($field.visibility != readonly OR !$field.value)} 
              <span class="input-group-text bg-transparent get-file-callback pe-auto" role="button" data-container="#fid{$form_script_loaded}-{$keyid}-{$file@index}" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="File Manager"><i class="bi bi-upload"></i></span> 
            {/if}  
            <button class="input-group-text bg-transparent self-remove" type="button" ><i class="bi bi-x-lg"></i></button>
          </div>    
        {/foreach} 
        {if $field.is == multiple || !$field.value}  
        <div class="input-group pb-2"> {* PHP's assign file array unintuitive, do not use prefix *}
          <input id="fid{$form_script_loaded}-{$keyid}" class="form-control get-file-callback" 
          {if $html.file_manager}
            type="text" name="{$fieldPrefix}[{$key}][ ]" data-container="#fid{$form_script_loaded}-{$keyid}" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="{'File Manager'|trans}"
          {else}
            type="file" name="{$fieldPrefix}[{$key}][ ]" accept="image/*"
          {/if} {if $field.is == required && $field.visibility != hidden}required{/if}  {if $field.visibility == readonly AND $field.value}disabled{/if}>
          <label class="input-group-text bg-transparent" for="fid{$form_script_loaded}-{$keyid}" role="button"><i class="bi bi-upload"></i></label>
          {if $field.is == multiple}<span class="input-group-text bg-transparent add-another-input" role="button" ><i class="bi bi-plus-lg"></i></span>{/if}
        </div>
        {/if}
      {elseif $field.type == 'image'}
        <div class="carousel-multi pb-1 w-100 {if $field.is == multiple}multiple-values{/if}" data-length="3" data-slide-to="0" data-noloop="1" data-dynamic="1">
          <div id="target{$form_script_loaded}-{$keyid}" class="carousel-inner item-removable row gx-0">
            {foreach $field.value as $image}
            <div class="col position-relative">
              <img src="{if $image|truncate:4:'' ne 'http'}{$links.file_view}/{/if}{$image}" class="img-thumbnail p-0 d-block mx-auto"><input type="hidden" name="{$fieldPrefix}[{$key}][ ]" value="{$image}">
            </div>
            {/foreach}                
          </div>    
          <button class="carousel-control-prev justify-content-start ms-3 d-none" type="button" data-bs-slide="prev">
            <i class="bi bi-chevron-left control-icon"></i>
            <span class="visually-hidden">{'Previous'|trans}</span>
          </button>
          <button class="carousel-control-next justify-content-end me-3 {if !$field.value}d-none{/if}" type="button" data-bs-slide="next">
            <i class="bi bi-chevron-right control-icon"></i>
            <span class="visually-hidden">{'Next'|trans}</span>
          </button>
        </div>

        <div class="input-group pb-2 {if $field.is != multiple && $field.value}d-none{/if}">
          <input id="fid{$form_script_loaded}-{$keyid}" class="form-control get-image-callback {if $field.is == multiple}multiple-values{/if}" data-container="#target{$form_script_loaded}-{$keyid}" data-name="{$fieldPrefix}[{$key}][ ]" 
          {if $html.file_manager}
            type="text" name="{$fieldPrefix}[{$key}][ ]" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="{'File Manager'|trans}"
          {else}
            type="file" name="{$fieldPrefix}[{$key}][ ]" accept="image/*"
          {/if} {if $field.is == required && !$field.value && $field.visibility != hidden}required{/if} {if $field.visibility == readonly AND $field.value}disabled{/if}>
          {if $html.file_manager AND ($field.visibility != readonly OR !$field.value)}
            <label class="input-group-text bg-transparent" for="fid{$form_script_loaded}-{$keyid}" role="button"><i class="bi bi-upload"></i></label>
          {/if}
        </div>  
      {elseif $field.type == 'checkbox'}
        <!--select class="form-select" name="{$fieldPrefix}[{$key}]" {if $field.is == required && $field.visibility != hidden}required{/if}>
          <option value="1" {if $field.value == 1}selected{/if}>Yes</option>
          <option value="0" {if $field.value == 0}selected{/if}>No</option>
        </select-->
        <div class="btn-group form-switch-radio col-form-label me-2 mb-1 pt-2" role="group">{* inputs first, order is important for css selector *}
          <input type="radio" class="btn-check btn-off" name="{$fieldPrefix}[{$key}]" id="cid{$form_script_loaded}-{$keyid}-0" value="0" autocomplete="off" {if !$field.value}checked{/if} {if $field.is == required && $field.visibility != hidden}required{/if} {if $field.visibility == readonly AND $field.value}disabled{/if}>
          <input type="radio" class="btn-check btn-on" name="{$fieldPrefix}[{$key}]" id="cid{$form_script_loaded}-{$keyid}-1" value="1" autocomplete="off" {if $field.value}checked{/if} {if $field.is == required && $field.visibility != hidden}required{/if} {if $field.visibility == readonly AND $field.value}disabled{/if}>
          <label class="btn btn-off btn-sm btn-outline-secondary border-end-0" for="cid{$form_script_loaded}-{$keyid}-0"></label>
          <label class="btn btn-on btn-sm btn-outline-primary border-start-0" for="cid{$form_script_loaded}-{$keyid}-1"></label>
        </div>
        <label class="col-form-label" for="cid{$form_script_loaded}-{$keyid}-1">{$field.description|trans}</label>
        <!--div class="form-check col-form-label">
          <input type="hidden" name="{$fieldPrefix}[{$key}]" value="0" />
          <input type="checkbox" name="{$fieldPrefix}[{$key}]" value="1" {if $field.value}checked{/if} class="form-check-input" />
        </div-->  
      {elseif $field.type == 'select'}
        {if $field.visibility != readonly OR !$field.value}
          <input type="hidden" name="{$fieldPrefix}[{$key}]" value="" data-default="no-multiple" class="hide when select is disabled"/>
        {/if} 
        <select class="form-control {if $field.visibility != readonly OR !$field.value}selectpicker show-tick{/if}" data-style="form-control border" data-none-selected-text="&nbsp;" name="{$fieldPrefix}[{$key}]{if $field.is == multiple}[ ]" multiple{else}"{/if} {if $field.is == required && $field.visibility != hidden}required{/if} {if $field.visibility == readonly && $field.value}disabled{/if} {if $field.listen}data-lookup="{$field.lookup}" data-lookup-input="\[{$field.listen}\]"  {if $field.scope}data-scope="{$field.scope}"{/if}{/if}>
          {if !$field.is OR $field.is eq optional}<option/>{/if}
          {if $field.value == (array) $field.value}
            {foreach $field.value AS $option => $label}
              <option value="{$option}" selected>{($field.options.$option|default:$label)|trans}</option>
            {/foreach}
            {foreach $field.options AS $option => $label}
              {if ! $field.value.$option}
              <option value="{$option}">{$label|trans}</option>
              {/if}
            {/foreach}
          {else}
            {foreach $field.options AS $option => $label}
              <option value="{$option}" {if $option == $field.value}selected{/if}>{$label|trans}</option>
            {/foreach}
          {/if}    
        </select>
        <!--  Bootstrap select -->
        {if ! $bs_select_script_loaded}
          {$bs_select_script_loaded = 1 scope="global"}
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
          <script defer src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
        {/if} 
      {elseif $field.type == 'radio'}
        {foreach $field.options AS $option => $label}
          {if $option AND $label}
          <div class="form-check col-form-label">
            <input class="form-check-input" type="radio" name="{$fieldPrefix}[{$key}]" id="field{$form_script_loaded}-{$option}" value="{$option}" {if $option == $field.value}checked{/if} {if $field.is == required && $field.visibility != hidden}required{/if} {if $field.visibility == readonly AND $field.value}disabled{/if}>
            <label class="form-check-label" for="field{$form_script_loaded}-{$option}">{$label|trans}</label>
          </div>
          {/if}
        {/foreach}
      {elseif $field.type == 'radio hover'}
        <div class="sg-radio-hover" role="button" {if $field.visibility == readonly AND $field.value}disabled{/if}>
          {if $field.value == (array) $field.value}
            {foreach $field.value AS $label}
              {$field.value = $label}
            {/foreach}
          {/if}  
          {foreach $field.options AS $label}
            <input type="radio" name="{$fieldPrefix}[{$key}]" id="rhover{$form_script_loaded}-{$keyid}{$label@index}" value="{$label}" {if $label eq $field.value OR $label@first}checked{/if} {if $field.visibility == readonly AND $field.value}disabled{/if}><label for="rhover{$form_script_loaded}-{$keyid}{$label@index}">{$label|trans}</label>
          {/foreach}
        </div>
        <style type="text/css">
          .sg-radio-hover input, 
          .sg-radio-hover label {
            display:none;
          }
          .sg-radio-hover input:checked + label,
          .sg-radio-hover:not([disabled]):hover label,
          .sg-radio-hover:active .label {
            display: inline;
          }
          .sg-radio-hover label {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 1.5em;
          }
          .sg-radio-hover label:hover {
            background-color: gold;
          }          
        </style>   
      {elseif $field.type == 'rating' OR $field.type == 'percentage'}
        <input class="sg-rating mt-1" type="range" 
          {if $field.type == percentage}
            max="100" step="1" style="--value:{($field.value|default:0|replace:'%':'')}; --star: none; --stars:100; --fill:limegreen; width:100%; margin-top: .6rem !important;"
            oninput="this.style.setProperty('--value', {literal}`${this.valueAsNumber}`);{/literal}{*/*} this.parentNode.querySelector('.sg-percentage-text.percentage{$keyid}').innerText = {literal}`${this.valueAsNumber+'%'}`{/literal}"
          {else}
            max="5" step="0.5" style="--value:{$field.value|default:0}; --starsize: 1.8rem;"
            oninput="this.style.setProperty('--value', {literal}`${this.valueAsNumber}`);{/literal}"            
          {/if}
          name="{$fieldPrefix}[{$key}]" value="{$field.value|default:0}" 
          {if $field.is == required && $field.visibility != hidden}required{/if} 
          {if $field.visibility == readonly && $field.value}disabled{/if}
        >
        {if $field.type == percentage}<div class="position-relative pe-none"><span class="sg-percentage-text percentage{$keyid}">{$field.value}</span></div>{/if}
        <style type="text/css">
          /* Created by Mads Stoumann https://dev.to/madsstoumann/star-rating-using-a-single-input-i0l */
          .sg-rating {
            --dir: right;
            --fill: gold;
            --fillbg: rgba(100, 100, 100, 0.15);
            --star: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 17.25l-6.188 3.75 1.641-7.031-5.438-4.734 7.172-0.609 2.813-6.609 2.813 6.609 7.172 0.609-5.438 4.734 1.641 7.031z"/></svg>');
            --stars: 5;
            --starsize: 1rem;
            --symbol: var(--star);
            --value: 1;
            --w: calc(var(--stars) * var(--starsize));
            --x: calc(100% * (var(--value) / var(--stars)));
            block-size: var(--starsize);
            inline-size: var(--w);
            position: relative;
            touch-action: manipulation;
            -webkit-appearance: none;
          }
          .sg-rating::-moz-range-track {
            background: linear-gradient(to var(--dir), var(--fill) 0 var(--x), var(--fillbg) 0 var(--x));
            block-size: 100%;
            mask: repeat left center/var(--starsize) var(--symbol);
          }
          .sg-rating::-webkit-slider-runnable-track {
            background: linear-gradient(to var(--dir), var(--fill) 0 var(--x), var(--fillbg) 0 var(--x));
            block-size: 100%;
            mask: repeat left center/var(--starsize) var(--symbol);
            -webkit-mask: repeat left center/var(--starsize) var(--symbol);
          }
          .sg-rating::-moz-range-thumb {
            height: var(--starsize);
            opacity: 0;
            width: var(--starsize);
          }
          .sg-rating::-webkit-slider-thumb {
            height: var(--starsize);
            opacity: 0;
            width: var(--starsize);
            -webkit-appearance: none;
          }
          .sg-percentage-text {
            position: absolute;
            top: -1.6rem;
            margin-left: .75rem;
            color: white;
          }      
        </style>   
      {elseif $field.type == 'duration'}
        <div class="input-group">
          <div class="form-control js-sg-duration">{$field.value}</div>
          <div class="input-group-text"><i class="bi bi-hourglass{if $field.value}-bottom{/if}"></i></div>
        </div>
      {elseif $field.type == 'date' OR $field.type == 'time'}
        {if $field.visibility == readonly AND $field.value}
          <div class="col-form-label js-sg-{$field.type}">{$field.value}</div>
        {else}
          <div class="input-group">
            <input name="{$fieldPrefix}[{$key}]" type="text" id="{$field.type}{$form_script_loaded}-{$keyid}" class="form-control {$field.type}picker-input datetimepicker-input" data-target="#{$field.type}{$form_script_loaded}-{$keyid}" data-toggle="datetimepicker" value="{$field.value}" placeholder="{$field.type|capitalize|trans}"/> 
            <span class="bi bi-calendar2-event input-group-text bg-transparent" data-target="#{$field.type}{$form_script_loaded}-{$keyid}" data-toggle="datetimepicker"></span>
          </div>
          {if ! $datetime_script_loaded}
            {$datetime_script_loaded = 1 scope="global"}
            <script defer type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment-with-locales.min.js"></script>
            <script defer type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.31/moment-timezone-with-data.js"></script>
            <script defer type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/js/tempusdominus-bootstrap-4.min.js"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/css/tempusdominus-bootstrap-4.min.css" />
            <script type="text/javascript" >
              document.addEventListener("DOMContentLoaded", function(e){
                $('.datetimepicker-input').closest('form').submit(function(ev) {
                  ev.preventDefault()
                  let that = $(this)
                  $('.datetimepicker-input').each(function() {
                    if ( ! $(this).val() || $(this).is('.datetimepicker-timestamp') ){
                      return true;
                    } else {
                      $(this).addClass('datetimepicker-timestamp')
                    }
                    $(this).clone().addClass('d-none').val(
                      $(this).datetimepicker("viewDate").unix()
                    ).appendTo(that)
                  })
                  this.submit()
                }) 
                Sitegui.initDatePicker = function (parent = 'body'){
                  $(parent).find('.datepicker-input').each(function() {
                    let option = {
                      format: 'L',
                      locale: "{$site.locale|default:$user.language|default:$site.language}", 
                      timeZone: '{$user.timezone|default:$site.timezone}',   
                      icons: {
                        previous: 'bi bi-chevron-left',
                        next: 'bi bi-chevron-right',
                      },
                    }
                    if ($(this).attr('value')){
                      option.defaultDate = new Date($(this).attr('value') * 1000).toLocaleDateString("en-CA", {
                        timeZone: '{$user.timezone|default:$site.timezone}', hour12: false,
                      })
                      $(this).removeAttr('value') //required for picker to work
                    }
                    $(this).datetimepicker(option)
                    this.style.opacity = 1  
                  }) 
                } 
                Sitegui.initTimePicker = function (parent = 'body'){
                  $(parent).find('.timepicker-input').each(function() {
                    let option = {
                      locale: "{$site.locale|default:$user.language|default:$site.language}", 
                      timeZone: '{$user.timezone|default:$site.timezone}',   
                      icons: {
                        time: 'bi bi-clock',
                        date: 'bi bi-calendar-day',
                        previous: 'bi bi-chevron-left',
                        next: 'bi bi-chevron-right',
                        up:   'bi bi-chevron-up',
                        down: 'bi bi-chevron-down',
                      },
                    }
                    if ($(this).attr('value')){
                      option.defaultDate = new Date($(this).attr('value') * 1000).toLocaleString("en-CA", {
                        timeZone: '{$user.timezone|default:$site.timezone}', hour12: false,
                      }).replace(', ', 'T')
                      $(this).removeAttr('value') //required for picker to work
                    }
                    $(this).datetimepicker(option)
                    this.style.opacity = 1  
                  }) 
                } 
              });    
            </script> 
          {/if} 
          {if ! $date_script_loaded AND $field.type == 'date'}
            {$date_script_loaded = 1 scope="global"}
            <script type="text/javascript">
              document.addEventListener("DOMContentLoaded", function(e){
                Sitegui.initDatePicker() 
              })
            </script>
          {/if}
          {if ! $time_script_loaded AND $field.type == 'time'}
            {$time_script_loaded = 1 scope="global"}
            <script type="text/javascript">
              document.addEventListener("DOMContentLoaded", function(e){
                Sitegui.initTimePicker() 
              })
            </script>
          {/if}
        {/if}  
      {elseif $field.type == 'textarea'}
        <textarea class="form-control" name="{$fieldPrefix}[{$key}]" rows="{$field.rows|default: 4}" cols="{$field.cols}" {if $field.is == required && $field.visibility != hidden}required{/if} {if $field.visibility == readonly && $field.value}readonly{/if}>{if $field.value}{$field.value}{/if}</textarea> 
      {elseif $field.type == 'country'}
        {if ! $bs_select_script_loaded}
          {$bs_select_script_loaded = 1 scope="global"}
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
          <script defer src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
        {/if} 
        {if $field.value}
        <script type="text/javascript">
          document.addEventListener("DOMContentLoaded", function(e){
            //selectpicker is a jquery plugin
            $('#picker{$form_script_loaded}-{$keyid}').selectpicker('val', '{$field.value}') 
          });  
        </script>
        {/if}
        <select name="{$fieldPrefix}[{$key}]" id="picker{$form_script_loaded}-{$keyid}" autocomplete="country" aria-label="Country" class="form-control selectpicker show-tick" data-live-search="true" data-style="form-control border border-1" {if $field.is == required && $field.visibility != hidden}required{/if} {if $field.visibility == readonly && $field.value}disabled{/if}><option value="" disabled="" hidden=""></option>{if $field.is != required}<option value=""></option>{/if}<option value="AF">Afghanistan</option><option value="AX">Aland Islands</option><option value="AL">Albania</option><option value="DZ">Algeria</option><option value="AD">Andorra</option><option value="AO">Angola</option><option value="AI">Anguilla</option><option value="AQ">Antarctica</option><option value="AG">Antigua &amp; Barbuda</option><option value="AR">Argentina</option><option value="AM">Armenia</option><option value="AW">Aruba</option><option value="AC">Ascension Island</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="AZ">Azerbaijan</option><option value="BS">Bahamas</option><option value="BH">Bahrain</option><option value="BD">Bangladesh</option><option value="BB">Barbados</option><option value="BY">Belarus</option><option value="BE">Belgium</option><option value="BZ">Belize</option><option value="BJ">Benin</option><option value="BM">Bermuda</option><option value="BT">Bhutan</option><option value="BO">Bolivia</option><option value="BA">Bosnia &amp; Herzegovina</option><option value="BW">Botswana</option><option value="BV">Bouvet Island</option><option value="BR">Brazil</option><option value="IO">British Indian Ocean Territory</option><option value="VG">British Virgin Islands</option><option value="BN">Brunei</option><option value="BG">Bulgaria</option><option value="BF">Burkina Faso</option><option value="BI">Burundi</option><option value="KH">Cambodia</option><option value="CM">Cameroon</option><option value="CA">Canada</option><option value="CV">Cape Verde</option><option value="BQ">Caribbean Netherlands</option><option value="KY">Cayman Islands</option><option value="CF">Central African Republic</option><option value="TD">Chad</option><option value="CL">Chile</option><option value="CN">China</option><option value="CO">Colombia</option><option value="KM">Comoros</option><option value="CG">Congo - Brazzaville</option><option value="CD">Congo - Kinshasa</option><option value="CK">Cook Islands</option><option value="CR">Costa Rica</option><option value="CI">Côte d’Ivoire</option><option value="HR">Croatia</option><option value="CW">Curaçao</option><option value="CY">Cyprus</option><option value="CZ">Czechia</option><option value="DK">Denmark</option><option value="DJ">Djibouti</option><option value="DM">Dominica</option><option value="DO">Dominican Republic</option><option value="EC">Ecuador</option><option value="EG">Egypt</option><option value="SV">El Salvador</option><option value="GQ">Equatorial Guinea</option><option value="ER">Eritrea</option><option value="EE">Estonia</option><option value="SZ">Eswatini</option><option value="ET">Ethiopia</option><option value="FK">Falkland Islands</option><option value="FO">Faroe Islands</option><option value="FJ">Fiji</option><option value="FI">Finland</option><option value="FR">France</option><option value="GF">French Guiana</option><option value="PF">French Polynesia</option><option value="TF">French Southern Territories</option><option value="GA">Gabon</option><option value="GM">Gambia</option><option value="GE">Georgia</option><option value="DE">Germany</option><option value="GH">Ghana</option><option value="GI">Gibraltar</option><option value="GR">Greece</option><option value="GL">Greenland</option><option value="GD">Grenada</option><option value="GP">Guadeloupe</option><option value="GU">Guam</option><option value="GT">Guatemala</option><option value="GG">Guernsey</option><option value="GN">Guinea</option><option value="GW">Guinea-Bissau</option><option value="GY">Guyana</option><option value="HT">Haiti</option><option value="HN">Honduras</option><option value="HK">Hong Kong SAR China</option><option value="HU">Hungary</option><option value="IS">Iceland</option><option value="IN">India</option><option value="ID">Indonesia</option><option value="IQ">Iraq</option><option value="IE">Ireland</option><option value="IM">Isle of Man</option><option value="IL">Israel</option><option value="IT">Italy</option><option value="JM">Jamaica</option><option value="JP">Japan</option><option value="JE">Jersey</option><option value="JO">Jordan</option><option value="KZ">Kazakhstan</option><option value="KE">Kenya</option><option value="KI">Kiribati</option><option value="XK">Kosovo</option><option value="KW">Kuwait</option><option value="KG">Kyrgyzstan</option><option value="LA">Laos</option><option value="LV">Latvia</option><option value="LB">Lebanon</option><option value="LS">Lesotho</option><option value="LR">Liberia</option><option value="LY">Libya</option><option value="LI">Liechtenstein</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="MO">Macao SAR China</option><option value="MG">Madagascar</option><option value="MW">Malawi</option><option value="MY">Malaysia</option><option value="MV">Maldives</option><option value="ML">Mali</option><option value="MT">Malta</option><option value="MQ">Martinique</option><option value="MR">Mauritania</option><option value="MU">Mauritius</option><option value="YT">Mayotte</option><option value="MX">Mexico</option><option value="MD">Moldova</option><option value="MC">Monaco</option><option value="MN">Mongolia</option><option value="ME">Montenegro</option><option value="MS">Montserrat</option><option value="MA">Morocco</option><option value="MZ">Mozambique</option><option value="MM">Myanmar (Burma)</option><option value="NA">Namibia</option><option value="NR">Nauru</option><option value="NP">Nepal</option><option value="NL">Netherlands</option><option value="NC">New Caledonia</option><option value="NZ">New Zealand</option><option value="NI">Nicaragua</option><option value="NE">Niger</option><option value="NG">Nigeria</option><option value="NU">Niue</option><option value="MK">North Macedonia</option><option value="NO">Norway</option><option value="OM">Oman</option><option value="PK">Pakistan</option><option value="PS">Palestinian Territories</option><option value="PA">Panama</option><option value="PG">Papua New Guinea</option><option value="PY">Paraguay</option><option value="PE">Peru</option><option value="PH">Philippines</option><option value="PN">Pitcairn Islands</option><option value="PL">Poland</option><option value="PT">Portugal</option><option value="PR">Puerto Rico</option><option value="QA">Qatar</option><option value="KR">Republic of Korea</option><option value="RE">Réunion</option><option value="RO">Romania</option><option value="RU">Russia</option><option value="RW">Rwanda</option><option value="WS">Samoa</option><option value="SM">San Marino</option><option value="ST">São Tomé &amp; Príncipe</option><option value="SA">Saudi Arabia</option><option value="SN">Senegal</option><option value="RS">Serbia</option><option value="SC">Seychelles</option><option value="SL">Sierra Leone</option><option value="SG">Singapore</option><option value="SX">Sint Maarten</option><option value="SK">Slovakia</option><option value="SI">Slovenia</option><option value="SB">Solomon Islands</option><option value="SO">Somalia</option><option value="ZA">South Africa</option><option value="GS">South Georgia &amp; South Sandwich Islands</option><option value="SS">South Sudan</option><option value="ES">Spain</option><option value="LK">Sri Lanka</option><option value="BL">St. Barthélemy</option><option value="SH">St. Helena</option><option value="KN">St. Kitts &amp; Nevis</option><option value="LC">St. Lucia</option><option value="MF">St. Martin</option><option value="PM">St. Pierre &amp; Miquelon</option><option value="VC">St. Vincent &amp; Grenadines</option><option value="SR">Suriname</option><option value="SJ">Svalbard &amp; Jan Mayen</option><option value="SE">Sweden</option><option value="CH">Switzerland</option><option value="TW">Taiwan</option><option value="TJ">Tajikistan</option><option value="TZ">Tanzania</option><option value="TH">Thailand</option><option value="TL">Timor-Leste</option><option value="TG">Togo</option><option value="TK">Tokelau</option><option value="TO">Tonga</option><option value="TT">Trinidad &amp; Tobago</option><option value="TA">Tristan da Cunha</option><option value="TN">Tunisia</option><option value="TR">Turkey</option><option value="TM">Turkmenistan</option><option value="TC">Turks &amp; Caicos Islands</option><option value="TV">Tuvalu</option><option value="UG">Uganda</option><option value="UA">Ukraine</option><option value="AE">United Arab Emirates</option><option value="GB">United Kingdom</option><option value="US">United States</option><option value="UY">Uruguay</option><option value="UZ">Uzbekistan</option><option value="VU">Vanuatu</option><option value="VA">Vatican City</option><option value="VE">Venezuela</option><option value="VN">Vietnam</option><option value="WF">Wallis &amp; Futuna</option><option value="EH">Western Sahara</option><option value="YE">Yemen</option><option value="ZM">Zambia</option><option value="ZW">Zimbabwe</option></select>        
      {else} {*if $field.type == 'text' || $field.type == 'password' || $field.type == 'url' || $field.type == 'email' || $field.type == 'tel' || $field.type == 'color' || $field.type == 'currency' *}
        <input class="form-control {if $field.type == 'color'}form-control-color{/if}" type="{$field.type}" name="{$fieldPrefix}[{$key}]" value="{if $field.value == (array) $field.value}{$field.value[$site.language]}{else}{$field.value}{/if}" {if $field.is == required && $field.visibility != hidden}required{/if} {if $field.visibility == readonly && $field.value}readonly{/if}>
      {/if}
      {if $field.description AND $field.type != 'checkbox'}<div class="form-text text-muted mt-2">{$field.description|trans}</div>{/if}      
    </div>
  {/if}  
  </div> 
{/function}

{if ! $form_script_loaded}
  {$form_script_loaded = 1 scope="global"}
  <script>
  document.addEventListener("DOMContentLoaded", function(e){    
    $('.fieldset-container').on('click', '.new-fieldset', function(e) {
      let container = $(e.delegateTarget);
      lastRow = container.find('div.variant:last');
      newIndex = 0; //$('#variant-table').find('tr').length;
      container.find('div.variant').each(function() {
        newIndex = Math.max($(this).attr('data-index'), newIndex);
      });
      newIndex++;            
      newRow = lastRow.clone();
      newRow.attr("data-index", newIndex)
      newRow.find('.btn-close').prop("disabled", false);
      newRow.find("[name*='fieldset']").each(function(){
          $(this).attr('name', $(this).attr('name').replace(/\[\d+\]/, '['+ newIndex +']') );
          $(this).attr('id') && $(this).attr('id', $(this).attr('id').replace(/\-\-\d+\-\-/, '--'+ newIndex +'--') );
          //$(this).val('');
      });
      newRow.find("[data-target*='fieldset']").each(function(){
        $(this).attr('data-target') && $(this).attr('data-target', $(this).attr('data-target').replace(/\-\-\d+\-\-/, '--'+ newIndex +'--') );
      }); 
      newRow.find("[data-name*='fieldset']").each(function(){
          $(this).attr('data-name', $(this).attr('data-name').replace(/\[\d+\]/, '['+ newIndex +']') );
      });
      newRow.find("label").each(function(){
        $(this).attr('for') && $(this).attr('for', $(this).attr('for').replace(/\-\-\d+\-\-/, '--'+ newIndex +'--') );
      });                

      if ( newRow.find(".selectpicker").length ){
        newRow.find(".selectpicker").siblings().remove()
        newRow.find(".selectpicker").unwrap().selectpicker('render')
      } 
      newRow.find(".datetimepicker-input").val('') //required to work
      Sitegui.initTimePicker(newRow) 
      Sitegui.initDatePicker(newRow) 

      newRow.insertAfter(lastRow);
    });
    //input-group self remove
    $('.input-group').on('click', '.self-remove', function (e) {
      if ($(e.delegateTarget).is(":first-child")) {
        input = $(e.delegateTarget).find('input');
        input.removeAttr('readonly').val('');
        input.prev('.input-group-text').remove(); //remove quicklook
        if (input.hasClass('file-upload-indicator') && input.next().is('.self-remove')){ //file upload
          input.attr('type', 'file').attr('name', input.attr('data-upload-name'));//change to file upload
        }
      } else {
        $(e.delegateTarget).remove();
      }
    });
    //input-group clone/adding
    $('body').on('click', '.add-another-input', function (e) {
      lastRow = $(e.currentTarget).parent();
      newRow = lastRow.clone();
      newId = 'id' + new Date().valueOf();
      newRow.find('.get-file-callback').attr('id', newId).attr('data-container', '#'+ newId).removeAttr('required').val('')
      newRow.find('.input-group-text').attr('for', newId);
      newRow.insertAfter(lastRow);
      lastRow.find('.bi-plus-lg').removeClass('bi-plus-lg').addClass('bi-x-lg');
      lastRow.find('.add-another-input').removeClass('add-another-input')
           .on('click', function(e) { 
                  $(this).parent().remove();
          });
    }); 

    //Listener for collapse button to add custom fields to form
    $('.sg-main').on('show.bs.collapse', function(ev) {
      if ( $(ev.target).hasClass('collapse-placeholder') ){
        $(ev.target).append( $('#'+ $(ev.target).attr('id') +'-wrapper' ).children() )
      }
    })
    $('.sg-main').on('hidden.bs.collapse', function(ev) {
      if ( $(ev.target).hasClass('collapse-placeholder') ){
        $('#'+ $(ev.target).attr('id') +'-wrapper' ).append( $(ev.target).children() )
      }
    })

    $('.sg-main').on('shown.bs.collapse', function(ev) {
      ev.target.scrollIntoView({
        behavior: "smooth",
        block: 'center'
      });
    })

    //select/upload image
    $('body').on('change', '.get-image-callback', function (e) { 
      if (e.currentTarget.files[0]) { //client upload only
        let name = $(e.currentTarget).attr('data-name');
        let container = $(e.currentTarget).attr('data-container');
        let src = URL.createObjectURL(e.currentTarget.files[0]); //local file
        //order is important as e.currentTarget will be moved
        $(e.currentTarget)
          .parent()
            .clone()
            .insertAfter(
              $(e.currentTarget).parent()
            )
            .find('input')
            .val('')
            .removeAttr('required');

        $(container).append(
          $('<div class="col"></div>')
          .append( 
            $('<img class="img-thumbnail p-0 d-block mx-auto">')
            .attr('src', src)
            .on('load', function() {
              URL.revokeObjectURL(src) // free memory
            })
          )
          .append( $(e.currentTarget).attr('name', name).parent().hide() )   
        )
        .trigger('updated');      
      }  
    });
    //last image removed, show input or hide if not support multiple values
    $('.carousel-multi').parent().on('updated', function (e) {
      input = $(this).find('.get-image-callback');
      if ($(this).find('.carousel-inner').children().length > 0) { //has image item
        if ( ! input.is('.multiple-values') ){
          input.parent().addClass('d-none');
        }
      } else {  
        input.parent().removeClass('d-none');
      }
    });
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
    $('.carousel-multi').each(Sitegui.carousel);        
  }); 
  </script>
  <style type="text/css">
    .form-switch-radio .btn {
      width: 16px;
      height: 16px;
      padding: 0;
      border: 1px solid rgba(0,0,0,.25);
      border-radius: 50%;
      background-repeat: no-repeat;
    }
    .form-switch-radio .btn-off {
      border-top-left-radius: 50% !important;
      border-bottom-left-radius: 50% !important;
    }
    .form-switch-radio .btn-on:checked ~ .btn-on {
      border-color: #0d6efd;
      background-color: #0d6efd;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
    } 
    .form-switch-radio .btn-on:checked ~ .btn-off {
      border-color: #0d6efd;
      background-color: #0d6efd;
    } 
    .form-switch-radio .btn-off:checked ~ .btn-off {
      border-color: rgba(0,0,0,.25);
      background-color: transparent;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%280, 0, 0, 0.25%29'/%3e%3c/svg%3e");
    }     
  </style>
{/if} 

{foreach $formFields as $name1 => $field1}
  {if $field1.type == 'fieldset' && $field1.fields}
    <div class="fieldset-container">
    {if $field1.label OR $field1.description}
      <div class="py-3">
        <h5>{$field1.label}</h5> <small>{$field1.description}</small>
      </div>
    {/if}  
    {if ! $field1.value}{$field1.value = ''}{/if}  
    {foreach $field1.value as $index => $values}
      <div class="variant px-0 alert alert-light alert-dismissible fade show" role="alert" data-index={$values@index}>
        {foreach $field1.fields as $name2 => $field2}
          {if $values && $values.$name2}
            {$field2.value = $values.$name2}
          {/if}
          {if $field1.visibility == readonly}
            {$field2.visibility = 'readonly'}
          {/if}
          {if $field2.type == 'lookup' || $field2.listen}
            {$field2.lookup_key = $name2} {* before changing $name2 *}
            {include "lookup.tpl"}
          {/if}  
          {$name2 = "{$name1}{']['}{$index}{']['}{$name2}"}
          {buildForm key=$name2 field=$field2}
        {/foreach}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" {if $values@first}disabled{/if}></button>
      </div>   
    {/foreach}
    {if $field1.visibility != readonly}
      <div class="text-center mb-3">
        <button type="button" class="btn border-0"><i class="bi bi-plus-circle-fill text-info fs-3 new-fieldset {$name1}"></i></button>
      </div>
    {/if}  
    </div>     
  {else}
    {buildForm key=$name1 field=$field1}
    {if ($field1.type == 'lookup' || $field1.listen) AND $field1.visibility != 'readonly'}
      {* wont lookup at public include "lookup.tpl"*}
    {/if}  
  {/if}  
{/foreach}