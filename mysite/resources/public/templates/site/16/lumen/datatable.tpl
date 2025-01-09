{*<!-- Frontend use slug instead of id for edit/view link and bg-light for card-->*}
{if ! $datatable_script_loaded}
  {$datatable_script_loaded = 1 scope="global"}
  <script defer src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-bootgrid/1.3.1/jquery.bootgrid.min.css"/>
	<script defer type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-bootgrid/1.3.1/jquery.bootgrid.min.js"></script>
	<link rel="stylesheet" type="text/css" href="https://cdn.sitegui.com/public/templates/admin/bootstrap5/assets/css/jkanban.css?v=9">	
	<script type="text/javascript" src="https://cdn.sitegui.com/public/templates/admin/bootstrap5/assets/js/jkanban.js?v=42"></script>
	<script type="text/javascript">
	  document.addEventListener("DOMContentLoaded", function(e){
			//extend bootgrid to add columns when loading via ajax, also support kanban
			(function ($, window, undefined){
				/*jshint validthis: true */
				"use strict";
				var extension = {
				  addColumn: function(column) {
				  	//let last = this.columns.pop()
				  	this.columns.push(column)//, last)
				  	return this
			    },
			    hideColumn: function(id, visible) {
			    	$.each(this.columns, function (i, column){
			    		if (id == column.id) column.visible = visible
			    	})	
			    	return this
			    }
		    }

				$.extend($.fn.bootgrid.Constructor.prototype, extension);
			})(jQuery, window);

			//sgView global object
			sgView = new function() {
				var self = this
				//var to store server response
				var ajaxResponse = {
					{if $links}
						links: {
							{foreach $links AS $action => $link} 
								{if $action == datatable}
									datatable: {
										{foreach $link AS $a => $l}
											{$a}: "{$l}", 
										{/foreach} {*/*} 
									},
								{else}
									{$action}: "{$link}", 
								{/if} {*/*}
							{/foreach} {*/*}
						}
					{/if} {*/*}
				}		

				var converters = {
					translate: {
						from: function (value) { return value },
						to: function(value) {
							return Sitegui.trans(value)
						}
					},
					currency: {
						from: function (value) { return value },
						to: function(value) {
							return value? Sitegui.Currency.format(value) : ''
						}
					},
					duration: {
						from: function (value) { return value },
						to: function(value) {
							return Sitegui.duration(value)
						}
					},
		      datetime: {
		        from: function (value) { return value },
		        to: function (value) { 
		        	if (value > 86400) {
		        		const now = new Date();
		          	const date = new Date(value*1000);
		          	const seconds = Math.round((now - date) / 1000);
		          	if (seconds < 0) {
							    return Sitegui.date(value)
							  } if (seconds < 5) {
							    return '{"now"|trans}'
							  } else if (seconds < 90) {
							    return `${ seconds }{'s ago'|trans}`
							  } else if (seconds < 5400) {
							    return Math.round(seconds / 60) +'{'m ago'|trans}' 
							  } else if (seconds < 3600*36) {
							    return Math.round(seconds / 3600) +'{'h ago'|trans}'  
							  } else if (seconds < 3600*24*10) {
							    return Math.round(seconds / 3600 / 24) +' {"days ago"|trans}'    
							  } else {
			          	return Sitegui.date(value) 
							  }  
		        	} else if (value > 0){
		        		return '✔️'
		        	}
		        }
		      },
		      cleanXSS: {
		        from: function (value) { return value },
		        to: function (value) { //value likely from .text() which may contain unescaped HTML tag
		        	if (typeof value === 'object' && value != null){
		        		value = Object.values(value).join(', ');
		        	} 
		        	return $('<p/>').text(value).html(); //return escaped HTML
		        }		        	
		      }
		    }		
				var formatters = {
					status: function(column, row){
						let cls = 'status__'+ (row.status? row.status.replaceAll(' ', '_').toLowerCase() : '')
						let rgb = {}
						if ( row.status && ! $('style[for="'+ cls +'"]').length ){
							//change constant 25 to adjust color pastel
							rgb.v = row.status.replaceAll(' ','')
							rgb.r = rgb.v.charCodeAt(0)*25 % 255
							rgb.g = rgb.v.charCodeAt(Math.floor(rgb.v.length/2))*25 % 255
							rgb.b = rgb.v.charCodeAt(rgb.v.length-3)*25 % 255
							if ( (rgb.v.startsWith('Re') || rgb.v.startsWith('Ca')) && rgb.v.endsWith('ed')
							){ //grey
								rgb.r = rgb.g = rgb.b = Math.floor((rgb.r + rgb.g + rgb.b)/3)
							}
							rgb.tc = (rgb.r*0.299 + rgb.g*0.587 + rgb.b*0.114) > 149? '#000000' : '#FFFFFF';							
							$('<style for="'+ cls +'">.'+ cls +'{ldelim}background-color: rgb('+ rgb.r +','+ rgb.g +','+ rgb.b +'); color: '+ rgb.tc +'}</style>').prependTo($('head'))
						}	
						return $('<span>')
							.addClass('status__'+ (row.status? row.status.replaceAll(' ', '_').toLowerCase() : '') )
							.text(row.status)
							.wrap('<p/>')
							.parent()
							.html();
					},
			    links: function(column, row){
			    	if ( !ajaxResponse.links ) return
			      var thisLinks = '';
						if (row.slug){
							thisLinks += $('<a class="btn-group"><button type="button" class="btn btn-sm border-0 text-primary btn-outline-light rounded-circle"><i class="bi bi-card-text"></i></button></a>')
								.attr('href', row.slug )
								.wrap('<p/>')
								.parent()
								.html()
						}
						if (ajaxResponse.links.copy){
							thisLinks += $('<a class="btn-group"><button type="button" class="btn btn-sm border-0 text-primary btn-outline-light rounded-circle"><i class="bi bi-files"></i></button></a>')
								.attr('href', ajaxResponse.links.copy +'/'+ row.id )
								.wrap('<p/>')
								.parent()
								.html()
						}
						if (ajaxResponse.links.delete){
							thisLinks += $('<button type="button" class="btn btn-sm border-0 text-primary btn-outline-light rounded-circle" data-confirm="delete" data-name="id"><i class="bi bi-trash"></i></button>')
								.attr('data-url', ajaxResponse.links.delete +'{if $system.sgframe}?sgframe=1{/if}')
								.attr('data-value', row.id)
								.attr('data-subapps', (ajaxResponse.html.subapps)? ajaxResponse.html.subapps : '')
								.wrap('<p/>')
								.parent()
								.html()
						}
						if (ajaxResponse.links.manage){
							thisLinks += $('<button type="button" class="btn btn-sm btn-outline-secondary border rounded-circle" data-confirm="delete" data-reconfirm="1"><i class="bi bi-trash"></i></button>')
								.attr('href', ajaxResponse.links.manage +'/{$page.subtype|lower}/{$page.id}/'+ ajaxResponse.html.current_app.toLowerCase() +'/'+ row.id )
								.wrap('<p/>')
								.parent()
								.html()
						}
			      return '<div class="btn-toolbar float-end">'+ thisLinks +'</div>';
			    },
					html: function(column, row){ 
						//be careful as this returns HTML, make sure to escape HTML to prevent stored XSS like <img src=x onerror=alert(1)>
					  if ( column.id == 'creator' || column.id == 'creator_name' ){ //creator name convert to link
							return $('<a class="text-decoration-none"></a>')
								.attr('href', (ajaxResponse.links.datatable.creator || links.creator_name) +'/'+ row.creator_handle )
								.text(row.creator_name)
								.prepend(row.creator_avatar? 
									$('<img class="rounded-circle me-1 sg-datatable-thumb" loading="lazy">')
									.attr('src', row.creator_avatar) : '' 
								)
								.wrap('<p/>')
								.parent()
								.html()
						} else if ( row[column.id] ){
							if (typeof row[column.id] === 'object' && row[column.id] != null) { //load via ajax
								obj = row[column.id];
								href = (ajaxResponse.links && ajaxResponse.links.datatable)? ajaxResponse.links.datatable[column.id] +'/' : '';
							} else { //manual table cell render above
								obj = JSON.parse(row[column.id]);
								href = '';
							}	
							let value = '';
							for (var item in obj) {
								if (obj.hasOwnProperty(item)){								
									let abbr = obj[item]
									if ( (column.id == 'user' || column.id == 'staff') && isNaN(abbr) ){ //grid only&& Object.keys(column).length == 3
										abbr = abbr.split(' ').map((n)=>n[0]).join('').toUpperCase();
										abbr = $('<div class="sg-datatable-thumb">').text(abbr)
									} else {
										abbr = $('<div>').text(abbr)
									}
									value += ', ' + $('<a class="text-decoration-none"></a>')
										.attr('href', href + item )
										.attr('title', obj[item])
										.attr('href', '#'+ obj[item])
										.prepend(
											ajaxResponse.image_sources && 
											ajaxResponse.image_sources[ column.id ] && 
											ajaxResponse.image_sources[ column.id ][ item ] ? 
												$('<img class="rounded-circle sg-datatable-thumb" loading="lazy">')
												.attr('src', ajaxResponse.image_sources[ column.id ][ item ]) : abbr
										)
										.wrap('<p/>')
										.parent()
										.html()
								}		
							}	
							return value.slice(2);
						}	
					}	
				}

				var bootgridOptions = {
					{if ($api.rowCount AND !$api.rows) OR $html.ajax}
						ajax: true,
						url: {if $links.custom_api}"{$links.custom_api}".replaceAll('&amp;', '&'){elseif $links.subapp}"{$links.subapp}.json?subapp={$forapp}&html=1"{else}"{$links.api}.json?html=1"{/if}{*/*},	
					{/if} {*/*}	
					ajaxSettings: {
		      	method: "GET",
		      },	
			    responseHandler: function (response) {
			    	response.rows = response.rows? Object.values(response.rows) : []; 
			    	ajaxResponse = response //kept in global var for other function to work

						let that = this
			    	let $col
		    		if ( response.rows.length ){
				    	if (response.html.display == 'flat') {	
				    		if (response.rows.length == response.total){
				    			$(that.container +'-footer').addClass('d-none')
				    		}	else {	    	
			    				$(that.container +'-footer').attr('id', that.container +'-footer-show')	//change id to prevent hiding by loaded event
			    			}	
			    			$(that.container +'-wrapper').removeClass('d-none')		
								$(that.container +'-header').addClass('d-none')	
								$(that.container +'-extra').addClass('d-none')				
				    		$(that.container +'-content-wrapper').text('')
								$(that.container).addClass('d-none')
				    		let t, template = '<div class="col-12 mb-3 mx-auto">\
				    			<div class="card">\
				    				<div class="row g-0">\
				    					<div class="col-sm-3 text-end p-3 bg-primary bg-opacity-10 sg-creator-wrapper">\
				    						<div class="row">\
				    							<div class="col-12 text-start text-sm-end"><a class="sg-creator text-decoration-none text-secondary" href="#"></a></div>\
				    							<div class="col-auto col-sm-12 text-start text-sm-end card-text pt-1 pt-sm-0"><small class="text-secondary sg-date"></small></div>\
				    						</div>\
				    					</div>\
				    					<div class="col-sm-9">\
				    						<div class="card-body h-100 d-flex flex-column">\
				    							<div class="col card-text pb-3">\
				    								<span class="sg-content">{$subpage.content|strip_tags}</span>\
					    							<p class="card-text mt-2">\
					    								<i class="sg-attachment"><a href="#" data-bs-toggle="modal" data-bs-target="#dynamicModal" data-title="{"Preview"|trans}" data-url="{$attachment}"></a></i>\
					    							</p>\
					    						</div>\
													<div class="col-auto sg-subapp-nav d-none">\
				    								<ul class="nav nav-underline ps-0"></ul>\
				    								<div class="tab-content"></div>\
				    							</div>\
				    						</div>\
				    					</div>\
				    				</div>\
				    			</div>\
				    		</div>'

								let $subapp = (response.subapp && response.subapp.show)? response.subapp : {}
				    		response.rows.forEach( $row => {
			    				t = $(template)
			    				t.addClass({if $forapp}'px-0'{else}'col-md-10'{/if}{*/*})
			    				if ($row.creator){
			    					t.find('.sg-creator').text( $row.creator_name ).attr('href', response.links.datatable.creator +'/'+ $row.creator_handle)
			    					{if $forapp}
			    						if ($row.creator == {$page.creator|default:0}){
			    							$('<div class="bi bi-person-plus"></div>').text(" {$page.creator_label}").insertAfter(t.find('.sg-creator'))
			    						}
			    					{/if}{*/*}
			    					if ($row.creator_avatar){
			    						t.find('.sg-creator').append($('<img class="rounded-circle me-1 sg-datatable-thumb" loading="lazy">').attr('src', $row.creator_avatar))
			    					}	
			    				}	
			    				//$(template).find('.sg-user').text( $row.creator_name )
			    				if ($row.updated){
			    					t.find('.sg-date').text( Sitegui.time($row.updated) )
			    				}	
			    				if ($row.content){
				    				t.find('.sg-content').html( $row.content ) //xss potential - content is purified though
			    				} else if ($row.name){
				    				$('<p class="card-title"></p>').text( $row.name )
				    				.prependTo( t.find('.sg-content') )
			    				}
			    				if ($row.status) {
			    					$('<label class="float-end"></label>').text( $row.status )
				    				.prependTo( t.find('.sg-content') )
			    				}
			    				sgView.showHeaderFields($row, ajaxResponse, t.find('.sg-content'), 1)

			    				if ($row.attachments || $row.attachment) {
			    					( $row.attachments || $row.attachment).forEach ($attachment =>{
			    						t.find('.sg-attachment')
			    						 .clone()
			    						 .insertBefore( t.find('.sg-attachment') )
			    						 .removeClass('sg-attachment')
			    						 .addClass('bi bi-paperclip mt-3 d-block')
			    						 .find('a')
			    						 .attr('data-url', $attachment.indexOf('://') >= 0? $attachment : {if $links.file_view}'{$links.file_view}/'+{/if}{*/*} $attachment )
			    						 .text( $attachment.split("/").pop() )
			    					})
			    				}
			    				if (Object.keys($subapp).length) {
			    					$subapp.rootUrl = {if $links.custom_api}"{$links.custom_api}".replaceAll('&amp;', '&'){elseif $links.subapp}"{$links.subapp}.json?html=1"{else}"{$links.api}.json?html=1"{/if}; {*/*}
			    					let c = t.find('.sg-subapp-nav').removeClass('d-none')
			    					Object.keys($subapp.show).forEach( $sub => {
			    						if ($subapp.count[$row.id +'-'+ $sub]) {
			    							let tid = that.container.replace('#', '') +"-"+ $sub +"-"+ $row.id
			    							$('<a class="nav-link py-0 sg-no-badge" href="#"></a> ')
			    								.text($subapp.count[$row.id +'-'+ $sub] +' '+ Sitegui.trans( (($subapp.count[$row.id +'-'+ $sub] > 1)? $subapp.show[$sub]['plural'] : $subapp.show[$sub]['single'] ) ) )
			    								.attr('data-api', $subapp.rootUrl + '&parent='+ $row.id +'&subapp='+ $sub)
			    								.attr('data-bs-target', "#"+ tid +"-tabpane")
			    								.attr('data-sg-target', "#"+ tid)
			    								.wrap('<li class="nav-item pe-2" />')
			    								.parent()
				    						 	.appendTo( c.find('.nav') )

				    						$('<div class="tab-pane" role="tabpanel" tabindex="0"></div>')
				    							.attr('id', tid +"-tabpane")
				    							.attr('aria-labelledby', tid +"-tab")
				    							.append(
				    								$('<div class="mt-3"></div>').attr('id', tid +'-content-wrapper')
				    							)
				    							.append(
				    								$('<div class="text-center"><div class="spinner-border text-info" role="status"><span class="visually-hidden">Loading...</span></div></div>').attr('id', tid)
				    							)
				    							.appendTo( c.find('.tab-content') )
				    						//sgView.initBootgrid(that.container +"-"+ $sub +"-"+ $row.id, $subapp.rootUrl + '&parent='+ $row.id +'&subapp='+ $sub)
			    						}
			    					})
			    					if (ajaxResponse.links && ajaxResponse.links.edit){
			    						$('<a class="py-0" href="#" data-bs-toggle="modal" data-bs-target="#dynamicModal"><i class="bi bi-patch-plus"></a>')
			    							.attr('data-url', 'https://{$site.account_url}/account/app/view'+ ajaxResponse.links.edit + '/'+ $row.id + '?sgframe=1&add=1')
			    							.attr('data-title', Sitegui.trans('Add'))
			    							.wrap('<li class="nav-item pe-2 pt-1" />')
		    								.parent()
			    						 	.appendTo( c.find('.nav') )
		    						}
		    						 	
			    					c.find('.nav-link').each(function(){
										  this.addEventListener('click', ev => {
										    ev.preventDefault()
										    if (this.classList.contains('active')){
										    	this.classList.remove('active')
										    	$(this.dataset.bsTarget).removeClass('active show')
										    } else {
										    	sgView.initBootgrid(this.dataset.sgTarget, this.dataset.api)
										    	$(this.dataset.sgTarget +'-header').addClass('d-none') //header is not needed
										    	bootstrap.Tab.getOrCreateInstance(this).show()
										    }	
										  })
										})
			    				}
			    				$(that.container +'-content-wrapper').append( t )
				    		})
				    		//response.rows = [] //stop bootgrid from rendering table	
				    	} else if (response.html.display == 'kanban' || localStorage.getItem('viewKanban-'+ response.html.current_app) ){
				    		$col = localStorage.getItem('viewKanban-'+ response.html.current_app) || response.html.kanban
				    		self.initKanban(response, this.container, $col)
				    	} else if (window.screen.availWidth < 500 || response.html.display == 'grid' || localStorage.getItem('viewGrid-'+ response.html.current_app) ){
				    		self.initGrid(response, this.container)
				    	}
			    	} else {
				    	$(that.container +'-header').addClass('d-none')	
				    	$(that.container +' .spinner-border').addClass('d-none')	
			    	}
			    	if (response.html.display != 'flat') {	
			    		$(that.container +'-content-wrapper').addClass('d-none')	
			    	}		    		
			    	//display subapp's number of records
			    	if (response.total > 0 || response.html.user_engaged) {
			    		let btn = $('[data-bs-target="'+ that.container +'-tabpane"]')
			    		if (response.html.user_engaged){
			    			btn.addClass('sg-user-engaged')
			    		}
			    		if (response.total > 0 && !btn.is('.sg-no-badge')){
			    			btn.append( $('<span class="position-absolute top-0 start-75 translate-middle badge bg-light rounded-pill"/>').text(response.total) )
			    		}	
			    	}
			    	//app's first load: add column, header and view option
			    	if ( response.html.table_header ){
			    		//add column, header and view option
			    		if ( this.app != response.html.current_app ){ 
				    		this.app = response.html.current_app
								let $columnSelection = $('<ul class="dropdown-menu dropdown-menu-start column-selection" />'),
										$view = $('<div class="dropdown btn-group btn-group-sm view-kanban" />').append('<button class="btn border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-kanban"></i></button><ul class="dropdown-menu dropdown-menu-end">')
			 				
				    		Object.keys(response.html.table_header).forEach( $id => {
				    			let $header = response.html.table_header[$id];
				    			let $type = (response.html.column_type && $id in response.html.column_type)? response.html.column_type[$id] : ''
				    			let $column = {
							  		id: isNaN($id)? $id : $header.replace(' ', '_').toLowerCase(),
				            text: $header,
							  		identifier: false,
							  		converter: {
				          		from: function (value) { return value; },
				          		to: function (value) { return value; }
				          	},	
				            align: 'left',
				            headerAlign: 'left',
				            cssClass: '',
				            headerCssClass: '',
				            formatter:  null,
				            order: null,
				            searchable: true, // default: true
				            sortable:true, // default: true
							  		visible: true,
				            visibleInSelection: true, // default: true
				            width: null
							  	}

							  	let $date_id = $id.replace(response.html.current_app.toLowerCase() +'_', '')
				    			if ($id == "id"){
				    				$column.identifier = "true" 
				    				$column.visible = false
										$column.headerCssClass += " d-none"
				    			} else if ($id == "action"){
										$column.sortable = false
										$column.headerCssClass += " text-end"
										$column.formatter = formatters.links
									} else if ($type == 'duration') {
										$column.converter =	converters.duration
									} else if ($type == 'date' || $type == 'time'){
									// || $date_id.endsWith('date') || $date_id == 'due' || $date_id == "date" || $id == "published" || $id == "expire" || $id == "updated" || $id == "created" || $id == 'registered' || $id == 'seen'){
										$column.headerCssClass += " col-date"
										$column.converter =	converters.datetime
									} else {
										if ($id == 'status') {
											$column.formatter = formatters.status
										} else if ( $type == 'currency' ){
											$column.converter = converters.currency
										} else {
											$column.converter = converters.cleanXSS
						        }
						        if ( response.links && response.links.datatable && response.links.datatable[$id] ){
						        	$column.formatter = formatters.html
						        }
						        //Add kanban view option (exclude above columns)
					    			if (response.html.kanban && $id != "id" && $id != "name" && $id != "slug") {
					    				$view.children('.dropdown-menu').append( 
					    					$('<li></li>').append(
					    						$('<a class="dropdown-item" href="#" />')
					    						.text($header)
					    						.attr('data-column', $column.id)
					    						.addClass($column.id == $col? "active" : "")
					    					) 
					    				)
					    			}
									}
									//Add table header
				    			$(that.container +' > thead > tr').append( 
				    				$('<th/>').append( 
				    					$('<a class="column-header-anchor"></a>')
				    						.addClass($column.sortable? 'sortable' : '')
					    					.append( $('<span class="text"></span>').text($header) )
					    					.append('<span class="icon bi"></span>') 
				    				).attr('data-column-id', $column.id)
				    				.addClass($column.headerCssClass) 
				    			)
					        //Add column selection
					        $columnSelection.append(
					        	$('<li></li>').append( 
			    						$('<label class="dropdown-item"/>')
			    						.text(" "+ $header)
			    						.prepend(
			    							$('<input type="checkbox" value="1" class="dropdown-item-checkbox">')
			    							.attr('data-name', $column.id)
			    							.prop('checked', $column.visible)
			    						)
			    					).on('click', function (e) { //create event listener for column selection
			    						e.stopPropagation(); //prevent dropdown from closing
			    						let checkbox = $(this).find('.dropdown-item-checkbox')
			                if ( ! checkbox.prop("disabled") ){
			                  $(that.container)
			                  	//.bootgrid("clear") //does not work with ajax
			                  	.bootgrid('hideColumn', checkbox.attr('data-name'), checkbox.prop("checked") )
			                  	.bootgrid("reload")
			                  	.on("loaded.rs.jquery.bootgrid", function (e) {
			                  		$(that.container +'-header [data-view="table"]').click()
			                  	})
			                  $(that.container +" tbody").text('') // clear	
			                  //the last checked column	should not be unchecked
			                  var enable = $(this).parents('.column-selection').find(".dropdown-item:has(.dropdown-item-checkbox:checked)")
			                  enable._bgEnableAria(enable.length > 1).find('.dropdown-item-checkbox')._bgEnableField(enable.length > 1)
			                	if (checkbox.prop("checked")) { //show hide header
			                		$(that.container +' th[data-column-id='+ checkbox.attr('data-name') +']').removeClass('d-none')
			                	} else {
			                		$(that.container +' th[data-column-id='+ checkbox.attr('data-name') +']').addClass('d-none')
			                	}
			                }
			    					}) 
					        )	
					        //add column to bootgrid internal
									$(that.container).bootgrid('addColumn', $column)
				    		})

				    		if (response.html.kanban) {
				    			$view.children('.dropdown-menu').prepend('<li><hr class="dropdown-divider"></li><li><button type="button" class="btn border-0 ps-3 disabled">Kanban</button> <button type="button" class="btn border-0 float-end pe-4 disabled"><i class="bi bi-kanban"></i></button></li><li><a class="dropdown-item" href="#" data-column="month">Month</a></li><li><a class="dropdown-item" href="#" data-column="year">Year</a></li>')
				    		}	
				    		$view.children('.dropdown-menu')
				    			.prepend('<li><hr class="dropdown-divider"></li><li><div class="dropdown ps-1" data-view="grid"><button type="button" class="btn border-0">Grid</button><button type="button" class="btn border-0 float-end pe-4"><i class="bi bi-grid"></i></button></div></li>')
				    			.prepend(
				    				$('<li />').append(
					    				$('<div class="dropdown ps-1"/>')
						    			.append('<button type="button" class="btn border-0" data-view="table">Table</button>')
						    			.append(
						    				$('<button type="button" class="btn border-0 dropdown-toggle dropdown-toggle-split float-end" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent"><i class="bi bi-card-checklist"></i> <span class="visually-hidden">Toggle Dropdown</span></button>')
						    				.on('click', e => { e.stopPropagation() })
						    			)	
						    			.append($columnSelection)	
				    				)
				    			)	
				    		//attach view button	
			    			$(this.container +'-header .actions').append($view)
			    		}
			    			
							//Listeners for view change, apply to same app when next page is ajax loaded as well
							$(this.container +'-wrapper')
								.off('click.sg.app.load') //remove previous event listener using namespace 
								.on('click.sg.app.load', '[data-column]', function (ev) {
									ev.preventDefault()
									self.initKanban(response, that.container, $(this).attr('data-column'))
									$(this).addClass('active')
										.parent().siblings().find('.active').removeClass('active')
								})
								.on('click.sg.app.load', '[data-view]', function (ev) {
									ev.preventDefault()									
									$(this).closest('.dropdown-menu').find('.active').removeClass('active')
									localStorage.removeItem('viewKanban-'+ response.html.current_app);				
									$(that.container +'-kanban-wrapper').html(''); //clear
									$(that.container +'-grid .item:not(.d-none)').remove(); //clear grid item excluding template

									if ($(this).attr('data-view') === 'table' ){
										$(that.container).removeClass('d-none');
										//$(that.container +'-footer').removeClass('d-none');
										$(that.container +'-extra').removeClass('d-none');	
										localStorage.removeItem('viewGrid-'+ response.html.current_app);				
									} else {
										sgView.initGrid(response, that.container)
									}	
								})	
			    	}
			    	return response;
			    },
					caseSensitive: false,
			    selection: false,
			    multiSelect: false,
					rowCount: {if $html.rowCount}[{$html.rowCount}, {2*$html.rowCount}, 200]{else}[12, 24, 48, 200]{/if}, {*/*}
					padding: 1,
			    converters: converters,
					formatters: formatters,
			    templates: { 
			    	header: '<div id="{literal}{{ctx.id}}" class="{{css.header}}{/literal} fade show">\
				    	<div class="row">\
				    		<div class="col-6 col-sm-4 pb-2 pb-sm-0 {if $forapp}d-none{/if}">\
				    			<h5 class="{if !$forapp}mt-sm-2{/if} text-success sg-app-header">\
				    				{if !$forapp}\
				    					{if $links.edit}\
					    					<a href="https://{$site.account_url}/account/app/view{$links.edit}{$links.edit2}" data-url="https://{$site.account_url}/account/app/view{$links.edit}{$links.edit2}?sgframe=1" data-title="{"New :item"|trans:["item" => $html.app_label]}" class="sg-app-create {if $html.app_readonly}d-none{/if}" data-bs-toggle="modal" data-bs-target="#dynamicModal">\
					    						<i class="bi bi-plus-circle-dotted fs-4 ps-sm-2 pe-3"></i></a>\
				    					{/if}\
				    					<span class="sg-app-name">{$html.app_label_plural}</span>\
				    					<small class="text-muted">\
				    						{foreach $html.app_menu as $level1}\
				    							{if $level1.slug}<a href="{$level1.slug}">{/if}{if $level1.icon}<i class="{$level1.icon}"></i>{/if}\
				    							{$level1.name}{if $level1.slug}</a>{/if}\
				    						{/foreach}\
				    						<p class="{literal}{{css.infos}}{/literal}"></p>\
				    					</small>\
				    				{/if}\
				    			</h5>\
				    		</div>\
				    		<div class="{if $forapp}col-12 col-sm-8 px-sm-5 order-sm-5{else}col-6 col-sm-4{/if}">{literal}<p class="{{css.search}}"></p></div>\
				    		<div class="col-6 col-sm-2 pt-1 order-sm-4"><p class="{{css.pagination}}"></p></div>\
				    		<div class="col-6 col-sm-2 pt-1 order-sm-last text-end"><p class="{{css.actions}}"></p></div>\
				    	</div>\
			    	</div>',

			    	search: '<div class="form-group"><div class="input-group"><span class="input-group-text bg-transparent border rounded-start {{css.icon}} {{css.iconSearch}}"></span> <input type="text" class="{{css.searchField}} border rounded-end shadow-none" placeholder="{{lbl.search}}" /></div></div>',

		        actionButton: '<button class="btn border" type="button" title="{{ctx.text}}">{{ctx.content}}</button>',
		            
		        actionDropDown: '<div class="{{css.dropDownMenu}}"><button class="btn border dropdown-toggle" type="button" data-bs-toggle="dropdown"><span class="{{css.dropDownMenuText}}">{{ctx.content}}</span> <span class="caret"></span></button><ul class="{{css.dropDownMenuItems}}" role="menu"></ul></div>',

			    	paginationItem: '<li class="paginate_button page-item {{ctx.css}}"><a data-page="{{ctx.page}}" class="{{css.paginationButton}}">{{ctx.text}}</a></li>',
			    },
			    css: {
			      icon: "icon bi",
			    	iconColumns: "bi-card-checklist",
		        iconDown: "bi-caret-down",
		        iconRefresh: "bi-arrow-repeat",
		      	iconSearch: "bi-search px-2",
		        iconUp: "bi-caret-up",
		        actions: "actions btn-group btn-group-sm",
		        dropDownMenu:	"dropdown btn-group btn-group-sm",
		        infos: "infos small ps-sm-3 d-none d-sm-inline",
		        pagination: "pagination pagination-sm", //left aligned
		      	paginationButton: "page-link border pt-0 pb-1",
		      	selectCell: "select-cell pe-1",
		      	table: "bootgrid-table table mt-2 mb-4"
			    },
			    labels: {
            infos: "{{ctx.start}}-{{ctx.end}} / {{ctx.total}}",
				    {/literal} {*/*}
            noResults: "{'No results found'|trans}",
            search: "{'Search'|trans}",
            all: "{'All'|trans}"
          }, 
				}
				
				this.initBootgrid = function (container, apiUrl) {
					bootgridOptions.container = container; //store the container selector for later use
					if (apiUrl){
						bootgridOptions.url = apiUrl
						bootgridOptions.ajax = true
					}
					$(container).bootgrid(bootgridOptions).on("loaded.rs.jquery.bootgrid", function(e){
						$(container +'-wrapper button[data-confirm]').click(function(ev) {
							$(this).trigger('click.sg.datatable') //custom namespace to bypass stopPropagation by bootgrid event handler
						})
						$(container +'-footer').addClass('d-none')
					})
				}
				
				// Setup listener to use ajax loading	
				this.initListener = function(container) {
					$('.sg-app-listing a').on('click', function (ev) {
						ev.preventDefault()
						let $state = {
							url: $(this).attr('href')
						}

					  $(container +'-kanban-wrapper').html(''); //clear Kanban
					  $(container +'-grid .item:not(.d-none)').remove(); //clear grid item excluding template
					  $(container).bootgrid('destroy') 

					  self.initBootgrid(container, $state.url + ".json?html=1") 
						$(container).on("loaded.rs.jquery.bootgrid", function (e) {
						  //update App name at several places
						  let $label = ajaxResponse.html? ajaxResponse.html.app_label_plural : ev.target.innerText
							$('.sg-app-listing').parent().find('.dropdown-item.active').removeClass('active rounded')
							$('.sg-app-listing').closest('.nav-item').find('.nav-link span').text($label)
							$('.sg-app-create')
								.attr('data-url', (ajaxResponse.html && ajaxResponse.links)? ajaxResponse.links.edit +'?sgframe=1' : '#')
								.attr('href', '#')
								.attr('data-title', Sitegui.trans("New :item", {
									"item": ajaxResponse.html.app_label
								} ) )
							$('.sg-app-name').text($label)
							$(ev.target).addClass('active rounded')
							$state.page = ajaxResponse.html.title

							if ("undefined" !== typeof history.pushState) {
						    history.pushState($state, $state.page, $state.url)
						    document.title = $state.page
						  }
						  $(container +"-header").addClass('show')
					  }) 
					  $(container +"-header").removeClass('show') //invoke bootgrid first to create this element
					})
				}
					
				//Kanban boards: should always load even if the current app does not need it, because it needs to be ready for other app
				var kanbanGroupBy = function (ajaxResponse, $col) {
					let $arr = ajaxResponse.rows
					let boards = ajaxResponse.html.boards || []
					let $validLabel = 0;
					if ( ! boards[$col] ){
						boards[$col] = ($col == 'year' || $col == 'month')?	{} : {}; //array for numeric $label for easy sorting, object otherwise because array cant have non-numeric index
						$validLabel = 1;
					}
					//setup boards for pre-defined
					Object.keys(boards[$col]).forEach( $label => {
						if ( boards[$col][$label]['id'] === undefined ){
							boards[$col][$label]['id'] = $label;
							if ($col == 'month') {
								boards[$col][$label]['name'] = $label.slice(0,4) +'.'+ $label.slice(4)	
							} else if ($col == 'status') {
								boards[$col][$label]['name'] = Sitegui.trans($label)
							}	else {
								boards[$col][$label]['name'] = $label
							}
						}
					})
					//for (boards[$col]
					for (let $i=0; $i < $arr.length; $i++){
						let $label;
						let $item = {
							id: $arr[ $i ]['id'],
							name: $arr[ $i ]['name'],
							title: $arr[ $i ]['name']? $arr[ $i ]['name'] : "{'Preview'|trans}",
							url: $arr[ $i ]['slug'] +'?sgframe=1',
							'bs-toggle': "modal",
							'bs-target': "#dynamicModal",
						}
						if ( $arr[ $i ]['creator'] ){
							$item.creator = $arr[ $i ]['creator_name']
						}
						if ( $arr[ $i ]['creator_avatar'] ){
							$item.avatar = $arr[ $i ]['creator_avatar']
						}
						if ( $arr[ $i ]['status'] && $col != 'status' ){
							$item.status = $arr[ $i ]['status']
						}

						if ($col == 'year'){
							$label = new Date($arr[ $i ]['updated'] * 1000).getFullYear(); 
						} else if ($col == 'month'){
							let date = new Date($arr[ $i ]['updated'] * 1000); 
							$label = date.getFullYear() + ('0' + (date.getMonth() + 1).toString()).slice(-2); 
						} else if ($col == 'creator'){
							$label = $arr[ $i ]['creator_name'];
						} else {
							$label = $arr[ $i ][ $col ];
						}
						if (typeof $label === 'object' && $label !== null) { //id => name
							Object.keys($label).forEach($id => { //using id as board id
								let $label2 = $label[ $id ]
								if ( ! boards[ $col ][ $label2 ] ){
									if ( !$validLabel || !$label2){
										$label2 = 'None'; //board having ordered names will put out of order items to None
										$id = 0
									}	
									if ( ! boards[ $col ][ $label2 ] ){
										boards[ $col ][ $label2 ] = {}
										boards[ $col ][ $label2 ]['id'] = $id;
										if ($col == 'month') {
											boards[ $col ][ $label2 ]['name'] = $label2.slice(0,4) +'.'+ $label2.slice(4)	
										} else {
											boards[ $col ][ $label2 ]['name'] = $label2
										}
									}	
								}
								if ( ! boards[ $col ][ $label2 ]['item'] ){
									boards[ $col ][ $label2 ]['item'] = [];
								}
								boards[ $col ][ $label2 ]['item'][ $arr[$i]['id'] ] = $item; //use id to prevent duplicate when groupBy invoked second time	for same $col
							});
						} else { //same as above for single value
							if ( ! boards[ $col ][ $label ] ){
								let $id = $label
								if ( !$validLabel || !$label ){
									$label = "{'None'|trans}"; //board having ordered names will put out of order items to None
									$id = 0
								}	
								if ( ! boards[ $col ][ $label ] ){
									boards[ $col ][ $label ] = {};
									boards[ $col ][ $label ]['id'] = $id 

									if ($col == 'month') {
										boards[ $col ][ $label ]['name'] = $label.slice(0,4) +'.'+ $label.slice(4)	
									} else {
										boards[ $col ][ $label ]['name'] = $label;
									}
								}	
							}
							if ( ! boards[ $col ][ $label ]['item'] ){
								boards[ $col ][ $label ]['item'] = [];
							}
							boards[ $col ][ $label ]['item'][ $arr[$i]['id'] ] = $item; //use id to prevent duplicate when groupBy invoked second time	for same $col				  
						}	
					}

					return boards[$col];
				}
				var kanbanOptions = {
			    element          : '#datatable-kanban-wrapper',                             // selector of the kanban container
			    gutter           : '10px',                                       // gutter of the board
			    widthBoard       : '250px',                                      // width of the board
			    responsivePercentage: false,                                    // if it is true I use percentage in the width of the boards and it is not necessary gutter and widthBoard
			    dragItems        : true,        					// if false, all items are not draggable
			    boards           : [],                                           // json of boards
			    dragBoards       : true,                                         // the boards are draggable, if false only item can be dragged
			    itemAddOptions: { //we use this for expand/collapse instead
			        enabled: true,                                              // add a button to board for easy item creation
			        content: '\u2194',                                                // text or no-html content of the board button   
			        class: 'kanban-title-button btn border-0 btn-xs p-0 text-secondary',         // default class of the button
			        footer: false                                                // position the button on footer
			    },    
			    itemHandleOptions: {
			        enabled             : false,                                 // if board item handle is enabled or not
			        handleClass         : "item_handle",                         // css class for your custom item handle
			        customCssHandler    : "drag_handler",                        // when customHandler is undefined, jKanban will use this property to set main handler class
			        customCssIconHandler: "drag_handler_icon",                   // when customHandler is undefined, jKanban will use this property to set main icon handler class. If you want, you can use font icon libraries here
			        customHandler       : "<span class='item_handle'><i class='bi bi-grip-vertical'></i></span> %name% "  // your entirely customized handler. Use %title% to position item title 
			                                                                     // any key's value included in item collection can be replaced with %key%
			    },
			    click            : function (el) {},                             // callback when any board's item are clicked
			    context          : function (el, event) {},                      // callback when any board's item are right clicked
			    dragEl           : function (el, source) {},                     // callback when any board's item are dragged
			    dragendEl        : function (el) {},                             // callback when any board's item stop drag
			    dropEl           : function (el, target, source, sibling) {
			    	if ( $(target).parent().attr('data-id') == "0" ){
			    		return false
			    	}
			    	if ( $(target).parent().attr('data-id') && $(el).attr('data-eid') > 0 ){
			        let href = this.ajaxResponse.links.update;
			        let post = {
			        	page: {
			        		id: $(el).attr('data-eid'),
			        		subtype: this.ajaxResponse.html.current_app
			        	}
			        }
			        $(el).prepend('<div class="sg-status float-end fade show" role="status"><div class="spinner-grow spinner-grow-sm text-info" role="status"><span class="visually-hidden">Saving...</span></div></div>')
				    	let $input = localStorage.getItem('viewKanban-'+ this.ajaxResponse.html.current_app)
				    	if ( $input.indexOf(this.ajaxResponse.html.current_app.toLowerCase() +'_') === 0) {
				    		$input = $input.slice(this.ajaxResponse.html.current_app.length + 1)
				    		post.page.fields = {}
				    		post.page.fields[ $input ] = $(target).parent().attr('data-id')
				    	} else {
				    		post.page[ $input ] = $(target).parent().attr('data-id')
				    	}
			        $.post(href, post, function(data) {
		            var response = data // already a json object jQuery.parseJSON(data);
		            if (response.status.result == 'success'){
		            	//console.log(response)
		            	$(el).find('.sg-status').html('<i class="bi bi-check2-circle fs-5 text-info align-top"></i>')
		            	setTimeout(function(){
		            		$(el).find('.sg-status').removeClass('show')
		            	}, 3000);
		            	setTimeout(function(){
		            		$(el).find('.sg-status').remove()
		            	}, 4000);
		            } 
			        }).fail(function () {
		            $(el).prependTo($(source))
		            $(el).find('.sg-status').html('<i class="bi bi-send-slash fs-5 text-danger align-top"></i>')
		          	setTimeout(function(){
		          		$(el).find('.sg-status').removeClass('show')
		          	}, 3000);
		            setTimeout(function(){
		          		$(el).find('.sg-status').remove()
		          	}, 4000);	
			        })
			    	}
			    },    // callback when any board's item drop in a board
			    dragBoard        : function (el, source) {},                     // callback when any board stop drag
			    dragendBoard     : function (el) {},                             // callback when any board stop drag
			    buttonClick      : function(el, boardId) {
			    	if ( $(el).closest('.kanban-board').hasClass('fold') ) {
			    		$(el)//.removeClass('float-start')
			    		.closest('.kanban-board').removeClass('fold')
			    	} else {
			    		$(el)//.addClass('float-start')
			    		.closest('.kanban-board').addClass('fold')
			    	}

			    },                     // callback when the board's button is clicked
			    propagationHandlers: [],                                         // the specified callback does not cancel the browser event. possible values: "click", "context"
				}

				this.initKanban = function(ajaxResponse, container, $col) {
			    //console.log($col, ajaxResponse)
			    kanbanOptions.element = container +'-kanban-wrapper'
			    kanbanOptions.ajaxResponse = ajaxResponse
					kanbanOptions.boards = kanbanGroupBy(ajaxResponse, $col)
					if ( $.inArray($col, ["month", "year", "creator"]) > -1){
						kanbanOptions.dragItems = false
					}	else {
						kanbanOptions.dragItems = {if $forapp}false{else}false{/if}{*/ no drag at frontend *}
					}
					$(container +'-kanban-wrapper').html('') //clear
					$(container +'-kanban-wrapper').prev().addClass('col-md-12')
					$(container +'-grid').addClass('d-none')
					$(container +'-grid .item:not(.d-none)').remove() //clear grid item excluding template
					$(container +'-footer').addClass('d-none')				
					$(container +'-extra').addClass('d-none')				
					$(container).addClass('d-none')
					//console.log(kanbanOptions)
					var boards = new jKanban(kanbanOptions);
					$(container +'-kanban-wrapper .kanban-container').addClass('mx-auto')
					$(container +'-kanban-wrapper .kanban-board').addClass('card')
					if (Object.keys(kanbanOptions.boards).length > 4){
						Object.values(kanbanOptions.boards).forEach(function(el, index){
							el.item || $(container +'-kanban-wrapper .kanban-board:nth-child('+ (index + 1) +')').addClass('fold') //fold if empty
						})
					}
					localStorage.setItem('viewKanban-'+ ajaxResponse.html.current_app, $col)
				}
				//grid view
				this.initGrid = function(ajaxResponse, container, type) {
			    let $arr = ajaxResponse.rows;
					let template = $(container +'-grid > .item.d-none:first-child')
					$(container +'-grid').html('').append(template)
					for (let $i=0; $i < $arr.length; $i++){
						if ( !type || !$arr[ $i ]['subtype'] || $arr[ $i ]['subtype'] === type){
							item = template.clone().removeClass('d-none')
							let title = $arr[ $i ]['name'] != undefined? $arr[ $i ]['name'] : Object.values($arr[ $i ])[1] //value of column after id if name is not used
							if (title){
								item.find('.sg-title').text(title)
								if (ajaxResponse.links.edit){
									item.find('.sg-title').wrap('<a class="text-decoration-none text-secondary fw-bold">')
										.parent()
										.attr('href', $arr[ $i ]['slug'] +'{if $system.sgframe}?sgframe=1{/if}')
								}
							}	else {
								item.find('.sg-title').addClass('d-none')
							}
							$arr[ $i ]['image'] && item.find('.img-container').attr('src', $arr[ $i ]['image'])

							if ( $arr[ $i ]['status'] ){
								item.find('.sg-title:not(.d-none)').after('<br>')
								item.find('.sg-status')
									.text( Sitegui.trans($arr[ $i ]['status']) )
									.addClass('status__'+ ($arr[ $i ]['status']? $arr[ $i ]['status'].replaceAll(' ', '_').toLowerCase() : '') )
									 //safe to use trans directly
							}	else if ( $arr[ $i ]['subtype'] ){
								//no need item.find('.sg-status').text($arr[ $i ]['subtype']).before('<br>')
							} 

							if ( $arr[ $i ]['creator'] ){
								let sanitize = {
									id: "creator",
									creator_handle: $arr[ $i ]['creator_handle'],
									creator_name: $arr[ $i ]['creator_name'],
									creator_avatar: $arr[ $i ]['creator_avatar'],
								}
								item.find('.sg-creator').html( formatters.html(sanitize, sanitize) ) //do go thru html sanitizer
								//item.find('.sg-creator').text( $arr[ $i ]['creator_name'] )
							}	

							//other columns
				    	sgView.showHeaderFields($arr[ $i ], ajaxResponse, item.find('.card-body'), 1)	

							let $count = ''
							if ( ajaxResponse.subapp && ajaxResponse.subapp.count && ajaxResponse.subapp.show ){
								Object.keys(ajaxResponse.subapp.show).forEach( $subapp => {
									let $key = $arr[ $i ]['id'] +'-'+ $subapp
									if (ajaxResponse.subapp.count[ $key ] ){
										$count += ajaxResponse.subapp.count[ $key ] + ' '
										$count += (ajaxResponse.subapp.count[ $key ] > 1)? ajaxResponse.subapp.show[ $subapp ]['plural'] : ajaxResponse.subapp.show[ $subapp ]['single']
										$count += ' '
									}				
								})
								if ($count) {
									item.find('.sg-links').text($count)
								}
							}
							if ( $arr[ $i ]['published'] || $arr[ $i ]['expire'] || $arr[ $i ]['registered'] || $arr[ $i ]['created'] || $arr[ $i ]['updated'] || $arr[ $i ]['due'] || $arr[ $i ]['due_date'] ){
								let item_date = $arr[ $i ]['published'] > 1000? $arr[ $i ]['published'] : ($arr[ $i ]['due']??$arr[ $i ]['due_date']??$arr[ $i ][ ajaxResponse.html.current_app.toLowerCase() +'_due']??$arr[ $i ][ ajaxResponse.html.current_app.toLowerCase() +'_due_date']??$arr[ $i ]['registered']??$arr[ $i ]['created']??$arr[ $i ]['updated']);
								item_date = converters.datetime.to(item_date)
								if ($count) {
									item.find('.card-body > .row:last-child').append( 
					    			$('<div class="col-12 pt-1" />').text( item_date ) 
					    		)
								} else {	
									item.find('.sg-links').text( item_date )
								}		
							}
							$(formatters.links(null, $arr[ $i ]))
	    					.appendTo( item.find('.sg-links-end') )

							$(container +'-grid').append(item)
						}
					}	
					$(container +'-kanban-wrapper').html('') //clear
					$(container +'-footer').addClass('d-none')				
					$(container +'-extra').addClass('d-none')
					$(container +'-grid').removeClass('d-none')
					$(container).addClass('d-none')
					localStorage.setItem('viewGrid-'+ ajaxResponse.html.current_app, 1)
				}

				//show app's header fields
				this.showHeaderFields = function(row, ajaxResponse, container, col = 1){
					if (ajaxResponse && ajaxResponse.html.table_header){
						container = $('<div class="row row-cols-'+ col +'"></div>').appendTo( container )
	  				Object.keys(ajaxResponse.html.table_header).forEach( ($id, index) => {
	  				//$(container +'-header').find('.column-selection .dropdown-item-checkbox:checked').each( (index, el) => {
	  					if ($id == 'id' || $id == 'name' || $id == 'slug' || (row['name'] == undefined && index == 1) ){ //exclude value of column after id if name is not used, id not checked
	  						return;
	  					}	
	  					//$id = $(el).attr('data-name')
	  					$date_id = $id.replace(ajaxResponse.html.current_app.toLowerCase() +'_', '')
							let $header = ajaxResponse.html.table_header[ $id ]
							let $type = (ajaxResponse.html.column_type && $id in ajaxResponse.html.column_type)? ajaxResponse.html.column_type[$id] : ''
			    		if ($id && $header && row[ $id ] && !['name', 'content', 'subtype', 'image', 'status', 'creator', 'published', 'expire', 'registered', 'updated', 'created', 'action', 'attachment', 'attachments'].includes($id) && !['due', 'due_date'].includes($date_id)
			    		){
								let $value = (typeof row[ $id ] === 'object' && row[ $id ] != null)? Object.values(row[ $id ]).join(', ') : row[ $id ]
								if ($value){
				    			container.append( 
				    				$('<div class="col pt-1" />')
				    				.text(' '+ 
				    					( ($type == 'currency')? converters.currency.to($value) : 
				    						($type == 'date' || $type == 'time')? converters.datetime.to($value) : 
				    						($type == 'duration')? converters.duration.to($value) : $value
				    					) 
				    				)
				    				.prepend( $('<b>').text( $header ) )
				    			)
				    		}	
			    		}
			    	})
			    }	
				} 	
			}	
		})	
	</script>
{/if}	

{if $api.total OR $html.ajax}
	{if $forapp}{$table = "app-$forapp"}{else}{$table = datatable}{/if}

<div class="container-md px-{if $forapp}0{else}md-3{/if} mb-4" id="{$table}-content-wrapper"></div>

<div class="container-md px-{if $forapp}0{else}md-3{/if}">
	<div class="card datatable-wrapper border-0 {if $forapp AND $app.sub.$forapp.display == flat}d-none{/if}" id="{$table}-wrapper">
		<table id="{$table}" class="table table-hover table-custom">
			<thead><tr>
			{if $api.rows}	
				{foreach $html.table_header as $col => $th}
					<th {if $col == ID} data-column-id="id" data-identifier="true" data-visible="false"
						{elseif (int)$col }data-column-id="{$th|lower|replace: ' ':'_'}" {* $col is integer *}
						{else}data-column-id="{$col}"{/if} 
						{if $col == action} data-formatter="links" data-sortable="false" data-header-css-class="text-end"
						{elseif $col == due OR $col == due_date OR $col == published OR $col == expire or $col == created or $col == updated or $col == date or $col == registered or $col == seen} data-converter="datetime" data-header-css-class="col-date"
						{else} data-converter="cleanXSS" {if $links.datatable.$col}data-formatter="html"{/if}{/if}
						>{$th}</th>
				{/foreach}
			{/if}	
			</tr></thead>
			{if $api.rows}
				<tbody>
				{foreach $api.rows as $row}
					<tr>
					{foreach $html.table_header as $col => $th}
						{if (int)$col OR $th == ID}
							{$value = $row[ $th|lower|replace: ' ':'_' ]}
						{else}
							{$value = $row.$col }
						{/if}		
						<td>{if $value eq (array)$value}
									{if $links.datatable.$col} {* prepare json string to be parsed back by formatter later, plese escape double-quote *} 
										{
											{foreach $value as $k => $v}
												"{$links.datatable.$col}/{$k}": "{$v|replace:'"':'\\"'}"{if ! $v@last},{/if}
											{/foreach}
										}										 
									{else}
										{foreach $value as $v}{$v}{if ! $v@last}, {/if}{/foreach}
									{/if}	
								{else}{$value}{/if}</td>
					{/foreach}
					</tr>
				{/foreach}
				</tbody>
			{/if}
		</table>
	</div>
	<div id="{$table}-grid" class="row row-cols-2 row-cols-sm-{($grid_column|default:$html.grid_column|default:4)/2|truncate:1:''} row-cols-md-{$grid_column|default:$html.grid_column|default:4} g-3 mt-3 mb-5 d-none">
    <div class="item col d-none" data-filter="subtype">
      <div class="thumbnail card h-100 bg-light bg-opacity-10">
        <img class="img-container img-fluid card-img-top" src='' alt="" />
        <div class="card-body pb-0 small lh-lg">
          <div class="row">
            <div class="col-12">
              <span class="sg-title pe-2 fs-6"></span>
              <span class="sg-status card-text text-success"></span> 
              <span class="sg-creator card-text ps-sm-1 float-end mt-1"></span> 
            </div>
          </div>        	
        </div>
        <div class="caption card-footer pe-2 border-top-0 bg-transparent">
          <div class="row">
            <div class="col-auto sg-links pt-1 small"></div>  
            <div class="col text-end sg-links-end small"></div>
          </div>
        </div>
      </div>
    </div> 
	</div>	
</div>

<div class="kanban-wrapper col-12 mx-auto mt-4 pb-5" id="{$table}-kanban-wrapper" style="overflow-x:auto"></div>

<script type="text/javascript">
  document.addEventListener("DOMContentLoaded", function(e){				
		//init bootgrid on first load
		let apiUrl
		{if ($api.rowCount AND !$api.rows) OR $html.ajax}
			apiUrl = {if $links.custom_api}"{$links.custom_api}".replaceAll('&amp;', '&'){elseif $links.subapp}"{$links.subapp}.json?subapp={$forapp}&html=1"{else}"{$links.api}.json?html=1"{/if}
		{/if}
		
		{if !$data_onclick}	
			sgView.initBootgrid("#{$table}", apiUrl)
		{elseif $forapp}
			$('[data-bs-target="#tab-{$forapp}"]').on('click', function(){
				sgView.initBootgrid("#{$table}", apiUrl)
			})
		{/if}	

		//force ajax loading for other apps
		{if !$forapp AND (($api.rowCount AND !$api.rows) OR $html.ajax) } //if table is not provided via ajax, column will not be removed completely when destroyed
			sgView.initListener("#{$table}")
		{/if} {*/*}			
		//subapp new button
		{if $forappTBR}
			{if $api.app.sub.$name.entry == multiple OR $api.app.sub.$name.entry == single OR 
         ($api.app.sub.$name.entry == creator_readonly AND $api.page.creator AND $api.page.creator != $user.id) OR  
         ($api.app.sub.$name.entry == other_readonly AND $api.page.creator == $user.id) 
      } {* display input fields if not readonly *}
				$('#{$table}-header .sg-app-header').append( 
					$('<button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" aria-expanded="false"></button>')
						.attr('data-bs-target', '#collapse-sub-{$forapp}') 
						.attr('aria-controls', 'collapse-sub-{$forapp}') 
						.text('{"New :item"|trans:["item" => $forapp|replace:"_":" "]}') 
				)
			{/if}		
		{/if} 
	});		
</script>	
{/if}	