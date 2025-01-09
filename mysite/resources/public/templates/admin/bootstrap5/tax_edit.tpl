{$tax = $api.tax}
<div class="col-12 {if $system.sgframe}px-0{else}col-md-10 pt-sm-2{/if} pb-40 mx-auto">
<form action='{$links.update}' method='post'>
	{if $tax.id > 0}
	<input name='tax[id]' type='hidden' value='{$tax.id}'>
	{/if}
 	<div class="card w-100">
	  <div class="card-body row g-0 align-items-center">
			<div class="col-auto pe-0 d-none d-sm-block sg-hover-back">
				{if $links.main}<a href="{$links.main}">{/if}
				<i class="bi bi-bookmark px-3 fs-4"></i>
				{if $links.main}</a>{/if}
			</div>
			<div class="col ps-sm-0 px-2">	
				<input class="input-name form-control-lg text-success" type='text' value='{$tax.name}' title='Enter tax name here' name='tax[name]' placeholder="{'Tax Name'|trans}" required {if ! $tax.name}autofocus{/if}>
			</div>
			<div class="col-auto ps-2 d-none d-sm-block">
				<button type="submit" name="save-btn" class="btn btn-outline-secondary border rounded-circle" title="{'Save'|trans}" style="height: 40px"><i class="bi bi-save"></i></button>
			</div>				
	  </div>
		<div role="tabpanel">
			<!-- Nav tabs -->
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item"><a href="#tab-tax" aria-controls="tab-tax" class="nav-link active" role="tab" data-bs-toggle="tab">{"Rate"|trans}</a></li>
			</ul>

			<!-- Tab panes -->
			<div class="tab-content px-3 py-4">
				<div id="tab-tax" class="tab-pane active" role="tabpanel"> 
					<div class="form-group row mb-3">
				    <label class="col-sm-3 col-form-label text-sm-end">{"Tax Rate %"|trans}</label>
				    <div class="col-sm-7">
				        <input class="form-control" type="text" name="tax[value][rate]" value="{$tax.value.rate}">
				        <small class="form-text text-secondary">{"Enter the rate without %, e.g: 12 for 12%"|trans}</small>      
				    </div>
					</div>
					<div class="form-group row mb-3">
						<label class="col-sm-3 col-form-label text-sm-end">{"Country"|trans}</label>
				    <div class="col-sm-7">
              <!--  Bootstrap select -->
              <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
              <script defer src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>

              <select name="tax[country]" autocomplete="billing country" aria-label="Country or region" class="form-control selectpicker show-tick" data-live-search="true" data-style="border border-1"><option value="">All Countries</option><option value="AF">Afghanistan</option><option value="AX">Åland Islands</option><option value="AL">Albania</option><option value="DZ">Algeria</option><option value="AD">Andorra</option><option value="AO">Angola</option><option value="AI">Anguilla</option><option value="AQ">Antarctica</option><option value="AG">Antigua &amp; Barbuda</option><option value="AR">Argentina</option><option value="AM">Armenia</option><option value="AW">Aruba</option><option value="AC">Ascension Island</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="AZ">Azerbaijan</option><option value="BS">Bahamas</option><option value="BH">Bahrain</option><option value="BD">Bangladesh</option><option value="BB">Barbados</option><option value="BY">Belarus</option><option value="BE">Belgium</option><option value="BZ">Belize</option><option value="BJ">Benin</option><option value="BM">Bermuda</option><option value="BT">Bhutan</option><option value="BO">Bolivia</option><option value="BA">Bosnia &amp; Herzegovina</option><option value="BW">Botswana</option><option value="BV">Bouvet Island</option><option value="BR">Brazil</option><option value="IO">British Indian Ocean Territory</option><option value="VG">British Virgin Islands</option><option value="BN">Brunei</option><option value="BG">Bulgaria</option><option value="BF">Burkina Faso</option><option value="BI">Burundi</option><option value="KH">Cambodia</option><option value="CM">Cameroon</option><option value="CA">Canada</option><option value="CV">Cape Verde</option><option value="BQ">Caribbean Netherlands</option><option value="KY">Cayman Islands</option><option value="CF">Central African Republic</option><option value="TD">Chad</option><option value="CL">Chile</option><option value="CN">China</option><option value="CO">Colombia</option><option value="KM">Comoros</option><option value="CG">Congo - Brazzaville</option><option value="CD">Congo - Kinshasa</option><option value="CK">Cook Islands</option><option value="CR">Costa Rica</option><option value="CI">Côte d’Ivoire</option><option value="HR">Croatia</option><option value="CW">Curaçao</option><option value="CY">Cyprus</option><option value="CZ">Czechia</option><option value="DK">Denmark</option><option value="DJ">Djibouti</option><option value="DM">Dominica</option><option value="DO">Dominican Republic</option><option value="EC">Ecuador</option><option value="EG">Egypt</option><option value="SV">El Salvador</option><option value="GQ">Equatorial Guinea</option><option value="ER">Eritrea</option><option value="EE">Estonia</option><option value="SZ">Eswatini</option><option value="ET">Ethiopia</option><option value="FK">Falkland Islands</option><option value="FO">Faroe Islands</option><option value="FJ">Fiji</option><option value="FI">Finland</option><option value="FR">France</option><option value="GF">French Guiana</option><option value="PF">French Polynesia</option><option value="TF">French Southern Territories</option><option value="GA">Gabon</option><option value="GM">Gambia</option><option value="GE">Georgia</option><option value="DE">Germany</option><option value="GH">Ghana</option><option value="GI">Gibraltar</option><option value="GR">Greece</option><option value="GL">Greenland</option><option value="GD">Grenada</option><option value="GP">Guadeloupe</option><option value="GU">Guam</option><option value="GT">Guatemala</option><option value="GG">Guernsey</option><option value="GN">Guinea</option><option value="GW">Guinea-Bissau</option><option value="GY">Guyana</option><option value="HT">Haiti</option><option value="HN">Honduras</option><option value="HK">Hong Kong SAR China</option><option value="HU">Hungary</option><option value="IS">Iceland</option><option value="IN">India</option><option value="ID">Indonesia</option><option value="IQ">Iraq</option><option value="IE">Ireland</option><option value="IM">Isle of Man</option><option value="IL">Israel</option><option value="IT">Italy</option><option value="JM">Jamaica</option><option value="JP">Japan</option><option value="JE">Jersey</option><option value="JO">Jordan</option><option value="KZ">Kazakhstan</option><option value="KE">Kenya</option><option value="KI">Kiribati</option><option value="XK">Kosovo</option><option value="KW">Kuwait</option><option value="KG">Kyrgyzstan</option><option value="LA">Laos</option><option value="LV">Latvia</option><option value="LB">Lebanon</option><option value="LS">Lesotho</option><option value="LR">Liberia</option><option value="LY">Libya</option><option value="LI">Liechtenstein</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="MO">Macao SAR China</option><option value="MG">Madagascar</option><option value="MW">Malawi</option><option value="MY">Malaysia</option><option value="MV">Maldives</option><option value="ML">Mali</option><option value="MT">Malta</option><option value="MQ">Martinique</option><option value="MR">Mauritania</option><option value="MU">Mauritius</option><option value="YT">Mayotte</option><option value="MX">Mexico</option><option value="MD">Moldova</option><option value="MC">Monaco</option><option value="MN">Mongolia</option><option value="ME">Montenegro</option><option value="MS">Montserrat</option><option value="MA">Morocco</option><option value="MZ">Mozambique</option><option value="MM">Myanmar (Burma)</option><option value="NA">Namibia</option><option value="NR">Nauru</option><option value="NP">Nepal</option><option value="NL">Netherlands</option><option value="NC">New Caledonia</option><option value="NZ">New Zealand</option><option value="NI">Nicaragua</option><option value="NE">Niger</option><option value="NG">Nigeria</option><option value="NU">Niue</option><option value="MK">North Macedonia</option><option value="NO">Norway</option><option value="OM">Oman</option><option value="PK">Pakistan</option><option value="PS">Palestinian Territories</option><option value="PA">Panama</option><option value="PG">Papua New Guinea</option><option value="PY">Paraguay</option><option value="PE">Peru</option><option value="PH">Philippines</option><option value="PN">Pitcairn Islands</option><option value="PL">Poland</option><option value="PT">Portugal</option><option value="PR">Puerto Rico</option><option value="QA">Qatar</option><option value="KR">Republic of Korea</option><option value="RE">Réunion</option><option value="RO">Romania</option><option value="RU">Russia</option><option value="RW">Rwanda</option><option value="WS">Samoa</option><option value="SM">San Marino</option><option value="ST">São Tomé &amp; Príncipe</option><option value="SA">Saudi Arabia</option><option value="SN">Senegal</option><option value="RS">Serbia</option><option value="SC">Seychelles</option><option value="SL">Sierra Leone</option><option value="SG">Singapore</option><option value="SX">Sint Maarten</option><option value="SK">Slovakia</option><option value="SI">Slovenia</option><option value="SB">Solomon Islands</option><option value="SO">Somalia</option><option value="ZA">South Africa</option><option value="GS">South Georgia &amp; South Sandwich Islands</option><option value="SS">South Sudan</option><option value="ES">Spain</option><option value="LK">Sri Lanka</option><option value="BL">St. Barthélemy</option><option value="SH">St. Helena</option><option value="KN">St. Kitts &amp; Nevis</option><option value="LC">St. Lucia</option><option value="MF">St. Martin</option><option value="PM">St. Pierre &amp; Miquelon</option><option value="VC">St. Vincent &amp; Grenadines</option><option value="SR">Suriname</option><option value="SJ">Svalbard &amp; Jan Mayen</option><option value="SE">Sweden</option><option value="CH">Switzerland</option><option value="TW">Taiwan</option><option value="TJ">Tajikistan</option><option value="TZ">Tanzania</option><option value="TH">Thailand</option><option value="TL">Timor-Leste</option><option value="TG">Togo</option><option value="TK">Tokelau</option><option value="TO">Tonga</option><option value="TT">Trinidad &amp; Tobago</option><option value="TA">Tristan da Cunha</option><option value="TN">Tunisia</option><option value="TR">Turkey</option><option value="TM">Turkmenistan</option><option value="TC">Turks &amp; Caicos Islands</option><option value="TV">Tuvalu</option><option value="UG">Uganda</option><option value="UA">Ukraine</option><option value="AE">United Arab Emirates</option><option value="GB">United Kingdom</option><option value="US">United States</option><option value="UY">Uruguay</option><option value="UZ">Uzbekistan</option><option value="VU">Vanuatu</option><option value="VA">Vatican City</option><option value="VE">Venezuela</option><option value="VN">Vietnam</option><option value="WF">Wallis &amp; Futuna</option><option value="EH">Western Sahara</option><option value="YE">Yemen</option><option value="ZM">Zambia</option><option value="ZW">Zimbabwe</option></select>
              {if $tax.country OR $api.location}
              <script type="text/javascript">
              	document.addEventListener("DOMContentLoaded", function(e){
					      	$('.selectpicker').selectpicker('val', '{$tax.country|default:$api.location}') 
					      })	
              </script>	
					    {/if}
			        <small class="form-text text-secondary">{"Country to apply tax"|trans}</small>
			      </div>   
					</div>						
					<div class="form-group row mb-3">
						<label class="col-sm-3 col-form-label text-sm-end">{"State"|trans}</label>
				    <div class="col-sm-7">
				        <input class="form-control" type="text" name="tax[state]" value="{$tax.state}">
				        <small class="form-text text-secondary">{"State to apply tax, leave blank for all states"|trans}</small> 
				    </div>
					</div>			
					<div class="form-group row mb-3">
						<label class="col-sm-3 col-form-label text-sm-end">{"Level"|trans}</label>
				    <div class="col-sm-7">
				    	<select class="form-control" name="tax[value][level]">
				    		<option value="1" {if $tax.value.level eq 1}selected{/if}>{"Level 1 - Apply first"|trans}</option> 
				    		<option value="2" {if $tax.value.level eq 2}selected{/if}>{"Level 2 - Apply after Level 1"|trans}</option> 
				    		<option value="3" {if $tax.value.level eq 3}selected{/if}>{"Level 3 - Apply after Level 2"|trans}</option> 
				    	</select>
				    </div>
					</div>		
					<div class="form-group row mb-3">
						<label class="col-sm-3 col-form-label text-sm-end">{"Compound"|trans}</label>
				    <div class="col-sm-7">
							<div class="form-check form-switch col-form-label">
			          <input type="hidden" name="tax[value][compound]" value="0">
			          <input type="checkbox" name="tax[value][compound]" value="1" id="switch-compound" class="form-check-input" {if $tax.value.compound}checked{/if}>
			          <label class="form-check-label" for="switch-compound">{"Apply this tax on top of prior level taxes (tax over prior taxes)"|trans}</label>
			        </div> 	
			      </div>
			    </div>
					<div class="form-group row mb-3">
						<label class="col-sm-3 col-form-label text-sm-end">{"Shipping"|trans}</label>
				    <div class="col-sm-7">			        
							<div class="form-check form-switch col-form-label">
			          <input type="hidden" name="tax[value][shipping]" value="0">
			          <input type="checkbox" name="tax[value][shipping]" value="1" id="switch-shipping" class="form-check-input" {if $tax.value.shipping}checked{/if}>
			          <label class="form-check-label" for="switch-shipping">{"Apply this tax on shipping fee"|trans}</label>
			        </div> 			
			      </div>
			    </div>
					<div class="form-group row mb-3">
						<label class="col-sm-3 col-form-label text-sm-end">{"Active"|trans}</label>
				    <div class="col-sm-7">
							<div class="form-check form-switch col-form-label">
			          <input type="hidden" name="tax[value][active]" value="0">
			          <input type="checkbox" name="tax[value][active]" value="1" id="switch-active" class="form-check-input" {if $tax.value.active}checked{/if}>
			          <label class="form-check-label" for="switch-active">{"Apply this tax to all products having no tax rate set"|trans}</label>
			        </div> 	
			      </div>
			    </div>
				</div>		
			</div>	
		</div>
		<div class="card-footer">
		  <div class="row">				
				<div class="col text-center">
					<button id="submit-button" class="btn btn-lg btn-primary my-1" type="submit" name='save_tax'>{"Save"|trans}</button>
				</div>
		  </div>		
		</div> 		
	</div>	 
</form>
</div>