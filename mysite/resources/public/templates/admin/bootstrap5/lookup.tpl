{if ! $lookup_script_loaded}
	 {$lookup_script_loaded=1 scope="global"}
{* sample here - support data-multiple, input should not have name but data-name to prevent submitting search query
  	<div class="input-group dropdown">
  		<button class="input-group-text" type="button"><i class="bi bi-search"></i></button>
  		<input class="form-control lookup-field dropdown-toggle" type='text' placeholder="{'Name'|trans}" data-lookup="page" data-name='widget[page_id][ ]' data-multiple=1 data-create=0 data-bs-auto-close="outside" data-bs-toggle="dropdown" aria-expanded="false">
		<div class="dropdown-menu px-2" style="max-height: 50vh; overflow: scroll;"></div>
	</div>		
*}
<script>
  	document.addEventListener("DOMContentLoaded", function(e){
		function debounceLookup(timeout = 300){
		  	var timer; //must be outside the checking function
		  	var searched;
		    return function() {
			    let value = $(this).val();
			    let href = '{$links.lookup}'; //$(this).attr('data-url');
			    let scope = $(this).attr('data-scope')? '&scope='+ $(this).attr('data-scope') : '';
			    let minlen = $(this).attr('data-minlen')??2; //min length 
			    let nvp = 'lookup='+ $(this).attr('data-lookup') 
			    		+ scope
			        	+'&value='+ value
			        	+'&format=json&csrf_token='+ window.csrf_token;

			    let lookup = function(href, nvp, target) {
			      	//console.log(nvp);
			      	let container = target.parent();
			      	container.find('.bi-search').addClass('spinner-grow spinner-grow-sm h-auto bg-transparent'); //show searching indicator
			      	bootstrap.Dropdown.getInstance(target[0]).hide(); //hide dropdown item
			        $.post(href, nvp, function(data) {
			            if (data.status.result == 'success'){
			                let type = target.attr('data-multiple') > 0? 'checkbox' : 'radio';
			                container.find('.dropdown-menu input:not(:checked)').parent().remove(); //remove unchecked item
			             	if (data.rows && Object.keys(data.rows).length) {
				                //console.log(data);
				                let type = target.attr('data-multiple') > 0? 'checkbox' : 'radio';
				                let index = target.attr('data-index')
				                if (type == 'radio') { //add option to select None
				                	data.rows[''] = 'None'
				                }
				                //data.rows should be an array instead of object for sorting, workaround for now
				                let sortedList = Object.entries(data.rows)
				                sortedList.sort((a, b) => {
				                	var x = (typeof a[1] == 'string')? a[1].toLowerCase() : a[1],
										y = (typeof b[1] == 'string')? b[1].toLowerCase() : b[1];
									return x<y ? -1 : x>y ? 1 : 0;
				                })
				                $.each(sortedList, function($i, $a) {
				                	let $k = $a[0]
				                	let $v = $a[1]
				                	let $id = target.attr('data-lookup') +'-lookup'+ index +'-'
				                	$id += $k? $k.replaceAll(':', '') : 'none';
				                	if ( ! container.find('#'+ $id)[0]) { //element not exist
					                	container.find('.dropdown-menu')
				                		.append(
				                			$('<div class="form-check"></div>')
				                			.append(
				                				$('<input class="form-check-input lookup-item" type="'+ type +'">')
			                					.attr('id', $id)
			                					.attr('name', target.attr('data-name'))
			                					.attr('value', $k)
				                			)	
				                			.append(
				                				$('<label class="form-check-label"></label>')
				                				.attr('for', $id)
				                				.text($v)
				                				.prepend(data.images && data.images[$k]?
			                						$('<img class="rounded-circle me-1 sg-datatable-thumb" loading="lazy">')
			                						.attr('src', data.images[$k]) : ''
			                					)
			                				)
					                	);
					                }	
				                });
				                data.slug && container.attr('data-slug', data.slug)
				                bootstrap.Dropdown.getInstance(target[0]).show()
				            } else if ( target.attr('data-create') ){
				            	if (container.find('.dropdown-menu input.sg-lookup-create:not(:checked)').length){
				            		container.find('.dropdown-menu input.sg-lookup-create:not(:checked)')
				            		.first()
				            		.attr('value', value)
				            		.siblings('label').text(Sitegui.trans('Add') +': '+ value)
				            	} else {
					           		let $id = 'lookup-create-new'+ Math.random()
					            	container.find('.dropdown-menu')
			                		.append(
			                			$('<div class="form-check"></div>')
			                			.append(
			                				$('<input class="form-check-input lookup-item sg-lookup-create" type="'+ type +'">')
		                					.attr('id', $id)
		                					.attr('name', target.attr('data-name'))
		                					.attr('value', value)
			                			)	
			                			.append(
			                				$('<label class="form-check-label"></label>')
			                				.attr('for', $id)
			                				.text(Sitegui.trans('Add') +': '+ value)
			                			)	
				                	);
				                }	
				                bootstrap.Dropdown.getInstance(target[0]).show()
				            }    
			            }
				        container.find('.bi-search').removeClass('spinner-grow spinner-grow-sm h-auto bg-transparent');
			        }).fail(function(response) {
					    container.find('.bi-search').removeClass('spinner-grow spinner-grow-sm h-auto bg-transparent');
					});
			      	searched = value;			    	
			    }

			    if (value.length >= minlen && value != searched) {
				    if (!timer) {
				    	lookup(href, nvp, $(this));
				    }
				    clearTimeout(timer);
				    timer = setTimeout(() => {
				    	if (value != searched) {
					      lookup(href, nvp, $(this)); //final  value
				    	}		
				      	timer = undefined;
				    }, timeout);
				} else if (value == searched){
					bootstrap.Dropdown.getInstance($(this)[0]).show(); //show previous results
			    } else {
			    	bootstrap.Dropdown.getInstance($(this)[0]).hide(); 
			    }		    	
		    }
		}
		//lookup input
		$('body').on('keyup', '.lookup-field', debounceLookup());	
		//update choosen value
		$('body').on('change', '.lookup-item', function(ev) {
			let container = $(ev.currentTarget).closest('.input-group');
			let truncate = parseInt(container.find('.lookup-field').attr('data-truncate'))

           	container.parent().find('.sg-lookup-result').remove(); //works for both radio and checkbox         	
			container.find("input:checked").each(function(){ //all checked input
				if ($(this).val()) {
					let $v = $(this).siblings('label').text()
					if ( $(this).hasClass('sg-lookup-create') ){
						$v = $v.replace(Sitegui.trans('Add') +': ', '')
					}

					if (truncate && $v.length > truncate + 3){
			            $v = $v.substring(0, truncate) +'...'
			        }
			        container.find('.lookup-field').val('')  
					//unselect button
					let $x = $(this).siblings('label').clone().html('<i class="text-primary bi bi-x ps-1 pt-1" role="button"></i>')
					let $add
					if ($(this).attr('type') == 'radio'){ //select none to remove
						$x.attr('for', $x.attr('for').replace($(this).val(), 'none') )
					}
					if ($(this).attr('type') == 'radio' || !container.children('.sg-lookup-result').length ){ //first result	
						$add = $('<label class="sg-lookup-result bg-body input-group-text rounded"></label>')
							.appendTo(container)
					} else {
						$add = $('<label class="sg-lookup-result bg-body border rounded me-1 mt-2 p-1 ps-3"></label>')
						$add.appendTo( container.parent() )
					}
					$add.append( container.attr('data-slug') && !$(this).hasClass('sg-lookup-create')? //link or text?
						$('<a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#dynamicModal"></a>')
						.attr('data-url', container.attr('data-slug') +'/'+ $(this).val() + '?sgframe=1')
						.attr('data-title', $v)
						.text($v) : $v 
					)
					.append( $x )	
				}	
            });
            bootstrap.Dropdown.getInstance(container.find(".lookup-field")[0]) && bootstrap.Dropdown.getInstance(container.find(".lookup-field")[0]).update(); 
		});	
		//lookup using other input value
		$('[data-lookup-input]').each(function() {
			let target = $(this);
			let trigger = $('[name*="'+ target.attr('data-lookup-input') +'"]')
			trigger.on('change', function(){
			  let href = "{$links.lookup}";
			  let scope = target.attr('data-scope')? '&scope='+ target.attr('data-scope') : '';
			  let nvp = 'lookup='+ target.attr('data-lookup')
			  		+ scope
			        +'&value='+ $(this).val()
			        +'&csrf_token={$token}&format=json';
			  $.post(href, nvp, function(data) {
			    if (data.status.result == 'success') {
			      //let target = $('[name*="{$fieldPrefix}[{$key}]"]');
			      target.html(target.attr('multiple')? '' : '<option/>'); //clear or add blank option
			      if ( data.rows && Object.keys(data.rows).length ){
				      $.each(data.rows, function($k, $v) {
				        target.append($('<option/>').attr('value', $k).text($v));
				      })
				  }
			      target.selectpicker('refresh')  
			    }
			  })        
			});
			trigger.val() && document.addEventListener('DOMContentLoaded', trigger.trigger('change')) //no cookie sent???
		})  	
	});
</script>
<style type="text/css">
.lookup-field {
	min-width: 100px !important;
}
.dropdown-menu:empty { 
	visibility: hidden;
}
</style>
{/if}