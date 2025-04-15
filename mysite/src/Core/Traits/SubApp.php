<?php
namespace SiteGUI\Core\Traits;

trait SubApp {
	protected function linkSubApps($func, &$page, $page_info, &$status, $app, $is_supervisor = false){
		$_parent = $this->hashify(getmypid());
		foreach ($page['sub'] AS $name => $value) {
			$name = $this->formatAppName($name);
			if ( !empty($app['app_sub'][ $name ]) ){ //valid subapp	
				$subapp = $this->getAppInfo($name);
				//create subpage only when sub data (exclude id) is present. If only id is present, it is intended for page linking
				if ( !empty( array_diff_key( $page['sub'][ $name ], ['id' => 0] )) ){
					//check if subapp accept entry or limit to single entry
					if ( $app['app_sub'][ $name ]['entry'] == 'readonly' OR $app['app_sub'][ $name ]['entry'] == 'staff_client_readonly' OR ($app['app_sub'][ $name ]['entry'] == 'client_readonly' AND !$is_supervisor) ){
						$status['result'] = 'error'; //if error, replace main message
						$status['message'][0] = $this->trans("You don't have permissions to :action", [
							'action' => $this->trans('create') .' '. $this->trans($app['label']) .' ➝ '. $this->trans($subapp['label']) 
						]); 
						continue;	
					} elseif ( isset($app['app_sub'][ $name ]['entry']) AND ($app['app_sub'][ $name ]['entry'] == 'single' OR $app['app_sub'][ $name ]['entry'] == 'quick') ){
						$timestamp = $this->db
							->table($this->table .' AS page')
							->leftJoin($this->site_prefix .'_location', 'page_id', '=', 'page.id')
							->leftJoin($this->table_prefix .'1_user AS user', 'creator', '=', 'user.id')
							->where('app_type', 'subapp')
							->where('app_id', $page_info['id'])
							->where('section', $name)
							->where('creator', $this->user->getId())
							->value('updated');
						if ($timestamp) {
							$status['result'] = 'error'; //if error, replace main message
							$status['message'][0] = $this->trans('Another :type was already recorded at :date. Please update it instead as :Type just accepts one single entry', [
								'type' => $subapp['label'],
								'date' => date("M d, Y H:i", $timestamp),
							]);
							continue;										
						}	
					}	
					$page['sub'][ $name ]['subtype'] = $name;
					unset($page['sub'][ $name ]['sub']); //prevent loop here
					$page['sub'][ $name ]['hide_success_message'] = true; //hide success message for subapp entry
					//set parent so subapp may refer back to parent
					if ( !empty($page_info['_parent']['root_id']) ){
						$page['sub'][ $name ][ $_parent ] = $page_info['_parent']; //get root, override parent below
					}
					$page['sub'][ $name ][ $_parent ]['id'] = $page_info['id'];
					$page['sub'][ $name ][ $_parent ]['name'] = $page['name']??$page_info['name']??''; //this is an array
					$page['sub'][ $name ][ $_parent ]['slug'] = $page['slug']??$page_info['slug']??'';
					$page['sub'][ $name ][ $_parent ]['type'] = $page_info['type']??$page['slug']??'';
					$page['sub'][ $name ][ $_parent ]['subtype'] = $page_info['subtype']??$page['slug']??'';
					$page['sub'][ $name ][ $_parent ]['creator'] = $page_info['creator']??null;
					$page['sub'][ $name ][ $_parent ]['status'] = $page['status']??$page_info['status']??null;

					$class_app = str_replace('Core\Traits', 'Core\App', __NAMESPACE__); //SubApps cannot be Core app
					if ($this instanceof $class_app ){
						$that = $this;
					} else {
						$that = new $class_app($this->config, $this->dbm, $this->router, $this->view, $this->user);
					}
					$page['sub'][ $name ]['id'] = $that->{$func}($page['sub'][ $name ]);
				} else { //user may provide abitrary sub id, check access permission

				}	

				//create relationship with the main page $page_info['id']
				if ( !empty($page['sub'][ $name ]['id']) ){
					//get root id of current parent
					$root_id = $this->db->table($this->site_prefix .'_location')
						->where('app_type', 'subapp')
						->where('page_id', $page_info['id'])
						->value('location');

					$linking   = ['subapp']; //reset
					$linking[] = $page_info['id']; //app_id
					$linking[] = $root_id?: $page_info['id']; //location is root_id
					$linking[] = $name; //section 
					$linking[] = $page['sub'][ $name ]['id']; //page_id

					if ( $this->upsert($this->site_prefix .'_location', ['app_type', 'app_id', 'location', 'section', 'page_id'], $linking) ){
						$status['message'][] = $this->trans(':item added successfully', ['item' => $subapp['label'] ]);
						//show approval info when the main entry is published, clientpublish publish automatically anyway
						if ( !$is_supervisor AND !empty($page_info["published"]) AND empty($app['app_enable']['clientpublish']) ){
							$status['message'][] = $this->trans('Pending approval'); 
						}
					} else {
						$status['result'] = 'error';
						$status['message'][0] = $this->trans(':item was not added', ['item' => $subapp['label'] ]);
					}
				}	
			}	
		}		
	}
	protected function editSubApps ($page, $app, $is_supervisor = false, $link_publish = null){
		if ( !empty($app['app_sub']) ){
			$block = [];
			//$show_creator = $this->showCreator($app);
			//single subapp pages request
			if ( !empty($_REQUEST['subapp']) ){
				$subapp = $this->formatAppName($_REQUEST['subapp']);
				if ( !empty($_REQUEST['parent']) ){ //subapp of subapp
					//make sure this parent id is indeed a sub-record of this page
					$parent_app = $this->db->table($this->site_prefix .'_location')
						->where('app_type', 'subapp')
						->where('page_id', $_REQUEST['parent'])
						->where('location', $page['id']??null) //root_id is this page
						->value('section');
					//valid $parent_app => get app info and check display	
					if ( $parent_app AND $parent_app = $this->getAppInfo($parent_app) AND $parent_app['app_sub'][ $subapp ]['display'] != 'client_hidden' ){
						$subapp = $this->readSubPages($_REQUEST['parent'], $parent_app, $subapp); 
						if ($subapp) {
							$block = $subapp['app_pages'];
							$block['html']['display'] = 'flat'; //force flat $parent_app['app_sub'][ $subapp['name'] ]['display'];//display
							//subapp has subapp
							$block['links']['edit'] = rtrim($this->slug('App::action', ['action' => 'edit', 'id' => $subapp['slug']] ), '/');
						}	
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans('Disconnected SubApp');							
					}	
				} else {
					if ( !empty($app['app_sub'][ $subapp ]) AND ($is_supervisor OR $app['app_sub'][ $subapp ]['display'] != 'client_hidden' ) ){
						$subapp = $this->readSubPages($page['id']??null, $app, $subapp); 
						if ($subapp) {
							$block = $subapp['app_pages'];
							$block['html']['display'] = $app['app_sub'][ $subapp['name'] ]['display'];
						}	
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans('Disconnected App');							
					}
				}
				if ( !empty($subapp['slug']) ){
					$block['links']['edit'] = rtrim($this->slug('App::action', ['action' => 'edit', 'id' => $subapp['slug']] ), '/');
					if ( !empty($page['published']) AND $is_supervisor ){
						$block['links']['publish'] = $link_publish; //$links['update']
					}
				}	
			} else {
				$block['html']['ajax'] = 1; //enable ajax loading for subapp
				foreach( ($app['app_sub']??[]) AS $name => $options ){
					if ( $is_supervisor OR $options['display'] != 'client_hidden' ){
						$subapp = $this->readSubPages(null, $app, $name); //null page id to avoid loading subpages
						if ($subapp) {
							if ( empty($subapp['app_permissions']['staff_read']) OR 
								(
									!empty($subapp['app_permissions']['staff_read_permission']) AND 
									!$this->user->has($subapp['app_permissions']['staff_read_permission']) 
								) OR (
									!empty($options['entry']) AND 
									(
										(
											empty($page['id']) AND 
											(
												$options['entry'] == 'readonly' OR 
												$options['entry'] == 'staff_client_readonly'	
											)
										) OR (
											($options['entry'] == 'creator_only') AND 
											!empty($page['creator']) AND 
											$page['creator'] != $this->user->getId()
										) OR (
											($options['entry'] == 'creator_readonly') AND 
											empty($page['creator'])
										)
									)		
								)
							){
								unset($block['api']['app']['sub'][ $name ]); //unset to not display tab for this subapp
								continue;
							}
							//get App to process fields for new record
							$response = $this->appProcess($subapp, 'edit', 'new');	

							if ( empty($response['result']) || $response['result'] == 'error'){
								$status['result'] = 'error';
								$status['message'][] = $response['message']??$this->trans('Invalid response from app');
							} else {
								foreach ( ($response['blocks']??[]) as $section => $sub_block) {
									if ($section == 'main'){
									  	//combine stored app config	with config from app at run time only - keep $app intact 
									  	//combine stored app_hide with response app.hide, builder config is preferred 
										$block['api']['subapp'][ $name ]['hide'] = ($subapp['app_hide']??[]) + ($sub_block['api']['app']['hide']??[]); 
										$block['api']['subapp'][ $name ]['hide']['wysiwyg'] = 1; //disable wysiwyg
										//force displaying subapp in separated tab 
										unset($block['api']['subapp'][ $name ]['hide']['tabapp']);

										//Hide published button if does not have permission
										if ( !$this->user->has($this->requirements['PUBLISH']) OR !$is_supervisor ){
											$block['api']['subapp'][ $name ]['hide']['published'] = 1;
										} 
									  	//combine stored app_fields with response app.fields, before adding meta value	
									  	$fields = ($subapp['app_fields']??[]) + ($sub_block['api']['app']['fields']??[]);
										foreach ( $fields AS $key => $field) {
											//When SUBAPP publish permission is set, and employee doesnt have it, 
											//allow client_editable/readonly fields like clientView
											if (empty($field)){
												continue;
											} elseif ( !empty($field['visibility']) AND $field['visibility'] == 'staff_client_readonly'){
												if ( empty($field['value']) ){
													unset($fields[ $key ]);
													continue;
												} else {
													$field['visibility'] = 'readonly';
												}
											} elseif ( !$is_supervisor ){
												if ( empty($field['visibility']) ){
													//treat it as client_editable
												} elseif ( str_contains($field['visibility'], 'client_readonly') AND (str_contains($field['is']??null, 'required') OR !empty($field['value']) ) ){
													//adjust (staff_)client_readonly visibility for displaying purpose
													$field['visibility'] = 'readonly';
												} elseif ( $field['visibility'] != 'client_editable' ){
													unset($fields[ $key ]);
													continue;
												}
											} 
											//FIELDS are for NEW EDIT, no stored value so just process field['value']
											//special case: lookup using other input, should NOT move inside formatFieldValue
											if ( !empty($field['type']) AND $field['type'] == 'select' AND (!empty($field['options']['From::input']) OR !empty($field['options']['From::lookup'])) ){
												$lookup = array_values($field['options']);
												$field['lookup'] = $lookup[1]??null;
												if ( !empty($lookup[3]) ){
													$field['listen'] = $lookup[3];
													$lookup = $field['listen']; //shorten
										    		//NEW EDIT so only default field value may be available  
			    									$field['lookup-value'] = $subapp['app_fields'][ $key ][ $lookup ]['value']??null;
													if ( !empty($field['options']['Scope::SubRecords']) ){
														$field['scope'] = 'SubRecords';
													}
									    		}	
											}
							    			if (!empty($field['value']) OR $field['type'] == 'fieldset') {
							    				$this->formatFieldValue(null, $key, $field, $subapp);
								    		}						
											//process options lookup, configs even if no value is set, $field passed by ref
											$this->formatFieldOptions($key, $field, $subapp);
					
											$fields[ $key ] = $field; 	
										}
										$block['api']['subapp'][ $name ]['fields'] = $fields??null;	
									} else {
										//no need as we prepare this for new record only
										//$block['api']['subapp'][ $name ][ $section ] = $sub_block;
									}
								}								
							}
						}
					}	
				}
			}	
			if ($block) {
				$this->view->addBlock('top', $block, $app['name'] .'::'. __FUNCTION__ );//add to top to override current_app
			}
			!empty($status) && $this->view->setStatus($status);
		}
	}

	protected function clientViewSubApps($page, $app, $is_supervisor = false, $link_publish = null){
		if ( !empty($app['app_sub']) ){
			$block = [];
			//$show_creator = $this->showCreator($app);
			//single subapp pages request
			if ( !empty($_REQUEST['subapp']) ){
				$subapp = $this->formatAppName($_REQUEST['subapp']);
				if ( !empty($_REQUEST['parent']) ){ //subapp of subapp
					//make sure this parent id is indeed a sub-record of this page
					$parent_app = $this->db->table($this->site_prefix .'_location')
						->where('app_type', 'subapp')
						->where('page_id', $_REQUEST['parent'])
						->where('location', $page['id']??null) //root_id is this page
						->value('section');
					//valid $parent_app => get app info and check display	
					if ( $parent_app AND $parent_app = $this->getAppInfo($parent_app) AND $parent_app['app_sub'][ $subapp ]['display'] != 'client_hidden' ){
						$subapp = $this->readSubPages($_REQUEST['parent'], $parent_app, $subapp, 'client'); 
						if ($subapp) {
							$block = $subapp['app_pages'];
							$block['html']['display'] = 'flat'; //force flat $parent_app['app_sub'][ $subapp['name'] ]['display'];//display
							$block['links']['edit'] = rtrim($this->slug('App::clientView', ['id' => $subapp['slug'], 'slug' => ''] ), '/');
						}	
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans('Disconnected SubApp');							
					}	
				} else {
					if ( !empty($app['app_sub'][ $subapp ]) AND $app['app_sub'][ $subapp ]['display'] != 'client_hidden' ){
						$subapp = $this->readSubPages($page['id']??null, $app, $subapp, 'client'); 
						if ($subapp) {
							$block = $subapp['app_pages'];
							$block['html']['display'] = $app['app_sub'][ $subapp['name'] ]['display'];
							//$block['html']['current_app_alias'] = $app['app_sub'][ $subapp['name'] ]['alias']??'';	
							$block['links']['edit'] = rtrim($this->slug('App::clientView', ['id' => $subapp['slug'], 'slug' => ''] ), '/');
						}	
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans('Disconnected App');							
					}
				}	
			} else {
				$block['html']['ajax'] = 1; //enable ajax loading for subapp
				foreach( ($app['app_sub']??[]) AS $name => $options ){
					if ($options['display'] != 'client_hidden') {
						$subapp = $this->readSubPages(null, $app, $name, 'client'); //null page id to avoid loading subpages
						if ($subapp) {
							if (empty($subapp['app_permissions']['client_read']) OR 
								(!empty($options['entry']) AND 
									(
										(
											!str_starts_with($options['entry'], 'creator') AND 
											str_contains($options['entry'], 'readonly') AND 
											empty($page['id'])
										) OR (
											($options['entry'] == 'creator_only') AND 
											!empty($page['creator']) AND 
											$page['creator'] != $this->user->getId()
										) OR (
											($options['entry'] == 'creator_readonly') AND 
											empty($page['creator'])
										)
									)		
								)
							){
								unset($block['api']['app']['sub'][ $name ]); //unset to not display tab for this subapp
								continue;
							}
							//get App to process fields for new record
							$response = $this->appProcess($subapp, 'edit', 'new');	//should be clientView

							if ( empty($response['result']) || $response['result'] == 'error' ){
								$status['result'] = 'error';
								$status['message'][] = $response['message']??$this->trans('Invalid response from app');
							} else {
								foreach ( ($response['blocks']??[]) as $section => $sub_block) {
									if ($section == 'main'){
									  	//combine stored app config	with config from app at run time only - keep $app intact 
									  	//combine stored app_hide with response app.hide, builder config is preferred 
										$block['api']['subapp'][ $name ]['hide'] = ($subapp['app_hide']??[]) + ($sub_block['api']['app']['hide']??[]); 
										$block['api']['subapp'][ $name ]['hide']['wysiwyg'] = 1; //disable wysiwyg
										//Hide published button if does not have permission
										$block['api']['subapp'][ $name ]['hide']['published'] = 1;
										//force displaying subapp in separated tab 
										unset($block['api']['subapp'][ $name ]['hide']['tabapp']);

									  	//combine stored app_fields with response app.fields, before adding meta value	
									  	$fields = ($subapp['app_fields']??[]) + ($sub_block['api']['app']['fields']??[]);
										foreach ( $fields AS $key => $field) {
											if (empty($field)){
												continue;
											} elseif ( empty($field['visibility']) ){
												//treat it as client_editable
											} elseif ( str_contains($field['visibility'], 'client_readonly') AND (str_contains($field['is']??null, 'required') OR !empty($field['value']) ) ){ //show if (staff_)client_readonly  has value or is required/multiple-required, otherwise hide, should not change form_field.tpl as this applies to client only
												$field['visibility'] = 'readonly';
											} elseif ( $field['visibility'] != 'client_editable' ){
												unset($fields[ $key ]);
												continue;
											}								
											//FIELDS are for NEW EDIT, no stored value so just process field['value']
											//special case: lookup using other input, should NOT move inside formatFieldValue
											if ( !empty($field['type']) AND $field['type'] == 'select' AND (!empty($field['options']['From::input']) OR !empty($field['options']['From::lookup'])) ){
												$lookup = array_values($field['options']);
												$field['lookup'] = $lookup[1]??null;
												if ( !empty($lookup[3]) ){
													$field['listen'] = $lookup[3];
													$lookup = $field['listen']; //shorten
										    		//NEW EDIT so only default field value may be available  
			    									$field['lookup-value'] = $subapp['app_fields'][ $key ][ $lookup ]['value']??null;
									    		}	
											}
							    			if (!empty($field['value']) OR $field['type'] == 'fieldset') {
							    				$this->formatFieldValue(null, $key, $field, $subapp);
								    		}							
											//process options lookup, configs even if no value is set, $field passed by ref
											$this->formatFieldOptions($key, $field, $subapp);
					
											$fields[ $key ] = $field; 
										}	
										$block['api']['subapp'][ $name ]['fields'] = $fields??null;	
									} 
								}								
								//$block['api']['subapp'][ $name ]['hide'] = $subapp['app_hide']; 
								//$block['api']['subapp'][ $name ]['hide']['wysiwyg'] = 1; //disable wysiwyg
								//$block['api']['subapp'][ $name ]['fields'] = $subapp['app_fields']; 
								//force displaying subapp in separated tab -NOT ONLY when creating new entry if empty($page_id) 
								//unset($block['api']['subapp'][ $name ]['hide']['tabapp']);
							}	
						}
					}	
				}
			}	
			if ($block){
				$this->view->addBlock('top', $block, 'App::'. $app['name'] .'::clientView::'. ($subapp['name']??'') );//add to top to override current_app
			}
		}			
	}
	protected function renderSubApps($page, $app) {
		$block = [];
		//$show_creator = $this->showCreator($app);
		//single subapp pages request
		if ( !empty($_REQUEST['subapp']) ){
			$subapp = $this->formatAppName($_REQUEST['subapp']);
			if ( !empty($_REQUEST['parent']) ){ //subapp of subapp
				//make sure this parent id is indeed a sub-record of this page
				$parent_app = $this->db->table($this->site_prefix .'_location')
					->where('app_type', 'subapp')
					->where('page_id', $_REQUEST['parent'])
					->where('location', $page['id']??null) //root_id is this page
					->value('section');
				//valid $parent_app => get app info and check display	
				if ( $parent_app AND $parent_app = $this->getAppInfo($parent_app) AND $parent_app['app_sub'][ $subapp ]['display'] != 'client_hidden' ){
					$subapp = $this->readSubPages($_REQUEST['parent'], $parent_app, $subapp, null, 'guest'); 
					if ($subapp) {
						$block = $subapp['app_pages'];
						$block['html']['display'] = 'flat'; //force flat $parent_app['app_sub'][ $subapp['name'] ]['display'];//display
						$block['links']['edit'] = rtrim($this->slug('App::render', ['app' => $subapp['slug'], 'slug' => ''] ), '/');
					}	
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans('Disconnected SubApp');							
				}	
			} else {	
				if ( !empty($app['app_sub'][ $subapp ]) AND $app['app_sub'][ $subapp ]['display'] != 'client_hidden' ){
					$subapp = $this->readSubPages($page['id']??null, $app, $subapp, null, 'guest', $app['app_sub'][ $subapp ]['entry']); 
					if ($subapp) {
						$block = $subapp['app_pages'];
						$block['html']['display'] = $app['app_sub'][ $subapp['name'] ]['display'];
						$block['links']['edit'] = rtrim($this->slug('App::render', ['app' => $subapp['slug'], 'slug' => ''] ), '/');
					}	
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans('Disconnected App');							
				}
			}	
		} else {
			$block['html']['ajax'] = 1; //enable ajax loading for subapp
			$block['links']['subapp'] = $page['slug']??null; 
			$block['links']['file_view'] = $this->slug('File::clientView');
			//we add $page_id+separator to csrf token i.e: $pageid.sg.$csrf
			$uri = str_contains($app['class'], '\\Core\\')? '/account/'. $app['slug'] .'/update' : '/account/app/update';
			$block['links']['update'] = 'https://'. $this->config['site']['account_url'] . $uri .'?cors=';
			$block['links']['update'] .= @$this->hashify($uri .'::'. ($page['id']??null) .'sg'. $_SESSION['token']); 
			$block['links']['update'] .= !empty($this->config['system']['sgframe'])? '&sgframe=1' : '';

			foreach( ($app['app_sub']??[]) AS $name => $options ){
				if ($options['display'] != 'client_hidden' ){
					$subapp = $this->readSubPages(null, $app, $name, null, 'guest'); //null page id to avoid loading subpages
					if ($subapp){
						//get App to process fields for new record
						$response = $this->appProcess($subapp, 'edit', 'new');	//should be clientView

						if ( empty($response['result']) || $response['result'] == 'error' ){
							$status['result'] = 'error';
							$status['message'][] = $response['message']??$this->trans('Invalid response from app');
						} else {
							foreach ( ($response['blocks']??[]) as $section => $sub_block) {
								if ($section == 'main'){
								  	//combine stored app config	with config from app at run time only - keep $app intact 
								  	//combine stored app_hide with response app.hide, builder config is preferred 
									$subapp['app_hide'] += ($sub_block['api']['app']['hide']??[]);
									foreach (['name', 'slug', 'title', 'description', 'collection', 'image', 'content'] AS $i => $key) {
										if ( empty($subapp['app_hide'][ $key ]) ){
											$which = $i < 4? 'fields' : 'show'; //name -> description displayed first, then custom input, then everything else
											$block['api']['subapp'][ $name ][ $which ][ $key ] = [
												'type' => 'text',
			             						'label' => ucwords($key),
			             						'visibility' => 'client_editable',
											];
										}
									}

								  	//combine stored app_fields with response app.fields, before adding meta value	
								  	$fields = ($subapp['app_fields']??[]) + ($sub_block['api']['app']['fields']??[]);
									foreach ( $fields AS $key => $field) {
										if ( empty($field['visibility']) ){
											//treat it as client_editable
										} elseif ( str_contains($field['visibility'], 'client_readonly') AND (str_contains($field['is']??null, 'required') OR !empty($field['value']) ) ){ //show if (staff_)client_readonly  has value or is required/multiple-required, otherwise hide, should not change form_field.tpl as this applies to client only
											$field['visibility'] = 'readonly';
										} elseif ( $field['visibility'] != 'client_editable' ){
											unset($fields[ $key ]);
											continue;
										}

										//FIELDS are for NEW EDIT, no stored value so just process field['value']
										//special case: lookup using other input, should NOT move inside formatFieldValue
										if ( !empty($field['type']) AND $field['type'] == 'select' AND (!empty($field['options']['From::input']) OR !empty($field['options']['From::lookup'])) ){
											$lookup = array_values($field['options']);
											$field['lookup'] = $lookup[1]??null;
											if ( !empty($lookup[3]) ){
												$field['listen'] = $lookup[3];
												$lookup = $field['listen']; //shorten
									    		//NEW EDIT so only default field value may be available  
		    									$field['lookup-value'] = $subapp['app_fields'][ $key ][ $lookup ]['value']??null;
								    		}	
										}
						    			if (!empty($field['value']) ) {
						    				$this->formatFieldValue(null, $key, $field, $subapp);
							    		}							

										$this->formatFieldOptions($key, $field, $subapp);
										$fields[ $key ] = $field; 
									}	
									$block['api']['subapp'][ $name ]['fields'] = $fields;	
								} 
							}								

							if ( empty($subapp['app_hide']['image']) ){
								$block['api']['subapp'][ $name ]['show']['image']['type'] = 'file';
								$block['api']['subapp'][ $name ]['show']['image']['label'] = $this->trans('Featured Image');
							}	
							if ( empty($subapp['app_hide']['content']) ){
								$block['api']['subapp'][ $name ]['show']['content']['type'] = 'textarea';
							}
							//$block['api']['subapp'][ $name ]['hide'] = $subapp['app_hide']; 
							//$block['api']['subapp'][ $name ]['hide']['wysiwyg'] = 1; //disable wysiwyg
							//force displaying subapp in separated tab ---NOT ONLY when creating new entry if ( empty($page_id) ) 
							//unset($block['api']['subapp'][ $name ]['hide']['tabapp']);
							//$block['api']['subpages'][ $name ]['html']['ajax'] = 1;
						}	
					}
				}	
			}
		}	
		if ($block){
			$this->view->addBlock('top', $block, 'App::'. $app['name'] .'::render::'. ($subapp['name']??'') );//add to top to override current_app
		}
	}
	protected function getActivities($app, $page_id){
		if (!empty($app['name']) && $page_id){
			$query = $this->db
				->table($this->site_prefix .'_activity AS page')
				->where('app_type', $app['name'] . ($app['subtype'] == 'Core'? '.' : '') )
				->where('app_id', $page_id)
				->select('page.id', 'page.level', 'page.message', 'page.created', 'page.processed', 'page.retry', 'page.meta');
			$block = $this->prepareMain($query, ['app_columns' => ['creator' => 1]], true);//show HTML tag, 'strip_tags');
			if ($this->view->html){
				$block['html']['table_header'] = [
					'id' => $this->trans('ID'),
					'message' => $this->trans('Message'),
					//'level' => $this->trans('Level'),
					'creator' => $this->trans('User'), 
					'created' => $this->trans('Created'), 
					'processed' => $this->trans('Processed'), 
					//'retry' => $this->trans('Retry'),
					'meta' => $this->trans('Details')
				];
				$block['html']['column_type']['created'] = 'time';
				$block['html']['column_type']['processed'] = 'time';
			}	
		}
		return $block??null;
	}

	// $for_client: prepare for clientarea, $for_guest: prepare for public
	// client, non-supervisor can see a list of Unprotected subpages created by others i.e: replies but may not directly view/edit them
	protected function readSubPages($page_id, $app, $subapp, $for_client = false, $for_guest = false) {
		$subapp = $this->getAppInfo($subapp);
		if (empty($subapp)) {
			return;
		} 
		$this->checkActivation($subapp['class']);
		$type = $app['slug'];

		if ($for_client OR $for_guest) { 
			//when preparing for clientarea, make sure clientarea is supported and do include app_visibility = hidden 
		  	if ( ($for_client AND !str_contains($subapp['app_users']??'', '_client') ) OR 
		  		 ($for_guest  AND !str_contains($subapp['app_users']??'', '_guest') ) OR 
		  		 empty($subapp['app_permissions']['client_read'])
		  	){
				return false;
			}
			//when $for_guest but guest has logged in, turn to client to include private subpages for published main page
			//after permission check
			if ( $for_guest AND $this->user->getId() ){
				//$for_guest = null;
				//$for_client = true;
			}	
			//keep only client_editable/readonly fields for processing
			foreach ( ($subapp['app_fields']??[]) AS $key => $field) {
				if ( empty($field['visibility']) OR $key == 'creator'){ //allow creator in index
					//treat it as client_editable
				} elseif ( !str_contains($field['visibility'], 'client_') OR $field['visibility'] == 'client_hidden'){
					unset($subapp['app_columns'][ $subapp['slug'] .'_'. $key ], $subapp['app_columns'][ $key ]);
				}
			}				
		} elseif ( empty($subapp['app_permissions']['staff_read']) OR (
			!empty($subapp['app_permissions']['staff_read_permission']) AND !$this->user->has($subapp['app_permissions']['staff_read_permission'])
		)){//staff
			return false;
		}

		//Remove lookup/select_lookup field and listing column for the current main app
		if ( !empty($subapp['app_fields'][ $type ]['type']) AND ($subapp['app_fields'][ $type ]['type'] == 'lookup' OR 
			($subapp['app_fields'][ $type ]['type'] == 'select' AND !empty($subapp['app_fields'][ $type ]['options']['From::lookup']) AND !empty($subapp['app_fields'][ $type ]['options'][ $type ]) ) )
		){
			unset( $subapp['app_fields'][ $type ], 
			 	$subapp['app_columns'][ $type ], 
			 	$subapp['app_columns'][ $subapp['slug'] .'_'. $type ] 
			);
		}

		//Get subpages when page_id present
		if ($page_id){
			$query = $this->db
				->table($this->table .' AS page')
				->leftJoin($this->site_prefix .'_location', 'page_id', '=', 'page.id')
				->where('app_type', 'subapp')
				->where('app_id', $page_id)
				->where('section', $subapp['name'])
				->select('page.id', 'page.name', 'page.created', 'page.updated', 'page.public')
				->when( empty($subapp['app_hide']['slug']) OR $for_guest, function($query) {
					return $query->addSelect('page.slug');
				})
				->addSelect('page.content', 'page.type', 'page.subtype', 'page.published'); //published needed if main page is published
			//before changing $query to get published records only
			if (!empty($app['app_sub'][ $subapp['name'] ]['entry']) AND 
				$app['app_sub'][ $subapp['name'] ]['entry'] == 'quick' AND 
				$this->user->getId() 
			){
				$user_engaged = (clone $query)->where('creator', $this->user->getId())
					->value('creator'); 
			}

			$query = $for_guest? $this->getPublished($query) : $this->getUnprotected($query);
			//include creators in order to display in grid/discussion
			empty($subapp['app_columns']['creator']) && $subapp['app_columns']['creator'] = $this->trans('User');
			
			//$block = $this->pagination($query);
			//provide default sorting since automatic sorting has problem with unionAll
			if ( empty($_REQUEST['sort']) ){
				$_REQUEST['sort']['id'] = 'asc'; //mainly for discussion
			}
			$show_links = ($for_client OR $for_guest)? 0 : $this->view->html;
			$block = $this->prepareMain($query, $subapp, $show_links);//show HTML tag, 'strip_tags');
			if ( $this->view->html ){
				if ($block['api']['total']){											
					if ( !empty($subapp['app_sub']) ) {
						$block['html']['subapp'] = $subapp['app_sub'];
						//count subapp's subentries, not sum for root
						$block['api']['subapp'] = $this->countSubPages($subapp, array_column($block['api']['rows'], 'id'), false, false);	
					}
					//client or non-supervisor
					if ($for_client AND !empty($subapp['app_permissions']['client_read']) ){
						$block['links']['edit'] = $this->slug('App::clientView', ["id" => strtolower($subapp['name']) ] );
					} elseif ( !$for_client AND !$for_guest ) { //staff
						//$block['links']['api'] = $this->slug('App::main', ["app" => strtolower($subapp['name']) ] );
						$block['links']['edit'] = $this->slug('App::action', ["action" => "edit", "id" => strtolower($subapp['name']) ] );
						//$block['links']['copy'] = $this->slug('App::action', ["action" => "copy", "id" => strtolower($subapp['name']) ] );
						if ( !empty($subapp['app_permissions']['staff_manage']) AND (empty($subapp['app_permissions']['staff_manage_permission']) OR $this->user->has($subapp['app_permissions']['staff_manage_permission']))
						){
							$block['links']['manage'] = $this->slug('App::action', ["action" => "manage"] );
						}
					}

					//kanban display, subapp_display is overridden by main app
					foreach ( ($subapp['app_fields']??[]) AS $key => $field) {
						if ( $field['type'] == 'select' OR $field['type'] == 'lookup' ){
							$this->formatFieldOptions($key, $field, $subapp);
							if ( !empty($field['options']) AND count($field['options']) > 1){ //at least 2 options
								$block['html']['boards'][ $key ] = array_fill_keys($field['options'], new \StdClass()); //create empty object instead of array
							}	
						}	
					}	
					$block['html']['kanban'] = !empty($block['html']['boards'])? key($block['html']['boards']) : 'month'; //indicate kanban support
				}
				if (!empty($user_engaged)){
					$block['html']['user_engaged'] = 1; //total may be 0 for non-published records
				}
				$block['html']['current_app'] = $subapp['name'];
				$block['html']['app_label'] = $block['html']['app_label_plural'] = '';
				if ($for_client){
					$block['links']['datatable']['creator'] = $this->slug('Profile::clientView', ['id' => 'to']);
				} elseif ($for_guest){
					$block['links']['datatable']['creator'] = $this->slug('Profile::render');
				} else {
					$block['links']['datatable']['creator'] = $this->slug('User::action', ['action' => 'edit']);
				}
			}
			$subapp['app_pages'] = $block;
		}
		return $subapp;	
	}
	//count subapps' number of entries for an array of id of an App, group by root_id when root_id in a collection, app_id when rendering the root_id
	protected function countSubPages($app, $ids, $is_supervisor = false, $sum_for_root = true) {
		//count subapp's subentries, column section is used to link to the root record
		foreach($app['app_sub'] AS $name => $options ){
			if ( !$is_supervisor AND $options['display'] == 'client_hidden') {
				continue;
			}
			$subapps[ $name ]['single'] = empty($options['alias'])? $name : $options['alias'];
			$subapps[ $name ]['plural'] = $this->trans($this->formatAppLabel($this->pluralize($subapps[ $name ]['single'])));
			$subapps[ $name ]['single'] = $this->trans($this->formatAppLabel($subapps[ $name ]['single']));
		}

		if (!empty($subapps) ){
			$query = $this->db->table($this->site_prefix .'_location')
				->join($this->table .' AS page', 'page.id', '=', 'page_id')
				->where('app_type', 'subapp')
				//->where('location', $page_id)
				->whereIn('section', array_keys($subapps) )
				->where(function($query) use($ids) {
					$query->whereIn('app_id', $ids)
						->orWhereIn('location', $ids); //root_id
				});
			if ($sum_for_root) { //can always do this as we use 'location in()' in select which covers 'else statement' below 
				$query->groupBy('cid')
					->selectRaw('CONCAT(IF(location in ('. implode(',', $ids) .'), location, app_id), "-", section) AS cid, COUNT(*) AS quantity'); //root_id must be within $ids or counts are for external root_id (from parent app)
			} else {
				$query->groupBy('cid')
					->selectRaw('CONCAT(app_id, "-", section) AS cid, COUNT(*) AS quantity'); 
			}	
			$query = $this->user->getId()? $this->getUnprotected($query) : $this->getPublished($query);	
			
			$block['api']['subapp']['count'] = $query->pluck('quantity', 'cid')->all();
			$block['api']['subapp']['show'] = $subapps;
		}
		return $block['api']['subapp']??null;	
	}
	//pass by ref, can change both $app and $query
	//return $show_creator and optionally change $query to unionAll
	protected function showCreator(&$app, &$query = false, $add_select = null) {
		//if ( !empty($app['app_columns']['creator']) ){ //employee app - override creator
			//check if table user has been initialized
			if ($this->config['site']['user_site'] ?? $this->db->table('information_schema.tables')
				->where('table_schema', $this->db->raw('DATABASE()') )
				->where('table_name', $this->site_prefix .'_user')
				->get('table_schema')
				->all()
			){
				$show_creator = $app['app_columns']['creator'];					
				unset($app['app_columns']['creator']);
			}		
		//}

		if ( !empty($show_creator) AND !empty($query) ){ //union query for site other than site1
			if ( !empty($add_select) AND is_array($add_select) ){
				$query->addSelect(...$add_select);
			}	
			if ($this->config['site']['id'] != 1 AND ( empty($this->config['site']['user_site']) OR $this->config['site']['user_site'] != 1) ){ 
				//clone the above query and inner join with table user, original query join with table staff below
				$query->unionAll(
					(clone $query)->join($this->table_user .' AS user', 'creator', '=', 'user.id') //valid user
				);
			}	
			$query->join($this->table_prefix .'1_user AS user', 'creator', '=', 'user.id'); //inner join to select records with 	valid staff 
		}
		return $show_creator??null;
	}

	//process field value, field passed by reference
	protected function formatFieldValue($value, $key, &$field, $app, $for_guest = false) {
		//default value is set by app config, get the actual value first, later it may be used for lookup below
		if ( !empty($field['value']) ){
			if ( $field['value'] == 'From::configs' ){
	    		$field['value'] = $this->getAppConfigValues($app['subtype'] .'\\'. $app['name'], $this->getAppConfigFields($app), $key);
			} elseif ( $field['type'] == 'lookup' AND is_string($field['value']) AND strpos($field['value'], 'Scope::') !== false){ //lookup status must be here, cant be in formatFieldOptions as the value can be overridden if stored in db
				$field['scope'] = substr($field['value'], 7); //for form_field
				$field['value'] = null;
			} 
		} //standalone if, do not connect with if below
			
		if ($field['type'] == 'lookup' OR !empty($field['lookup']) ){ //process value: lookup field and lookup using other input
			if ( !empty($field['lookup']) ){
				$key = $field['lookup']; //lookup using other input
			}
			if ( !empty($value) ) {
				if ( $for_guest ) { //for public page => format to work with form_field
					$field['_value'] = $this->lookupRelatedPages($value, $this->formatAppName($key)); //for caller 
					foreach ($field['_value']??[] AS $r) {
						if ( is_array($r) ){
							$result['rows'][ $r['id'] ] = $r['name'];
						} else {
							$result['rows'] = $field['_value'];
						}	
					}
					if ( !empty($r['slug']) ){
						$result['slug'] = $r['slug'] .'?';
					}
					if ( !empty($r['image']) ){
						$result['images'][ $r['id'] ] = $r['image'];
					}
				} else { 
					$result = $this->lookupById($key, $value);
					if ($key == 'creator' AND empty($result['rows']) ){ //not a user, lookup staff instead
						$result = $this->lookupById('staff', $value);
					}
				}	
			} elseif ( !empty($field['value']) ){
				$result = $this->lookupByValue($key, $field['value']); //lookup default value
			}

			if ( !empty($result['slug']) ){
				$field['slug'] = $result['slug'];						
			}
			if ( !empty($result['images']) ){
				$field['images'] = $result['images'];						
			} 
			$field['value'] = $result['rows']??null;	
					
		//	return (!empty($value))? $this->lookupById($key, $value) : $this->lookupByValue($key, $field['value']);
		// no need because we dont encrypt it when saving} elseif ($field['type'] == 'password') {
		//	return $this->decode($value, 'static');
		//} elseif ($field['type'] == 'fieldset') {
		//	return json_decode($value, true);
		} elseif ($field['type'] == 'select' AND is_array($value) ){ //multiple options
			$field['value'] = array_key_exists(0, $value)? array_combine($value, $value) : $value; //sequential array only: values also keys for smarty to check using key
		} elseif ($field['type'] == 'fieldset' AND !empty($field['fields']) ){
			foreach ($value??[] AS $index => $fieldset) {
				foreach ($fieldset AS $key2 => $value2) {
					if ( empty($field['fields'][ $key2 ]['type']) OR empty($value2) ) continue;

					if ( $field['fields'][ $key2 ]['type'] == 'lookup' ){
						$value[ $index ][ $key2 ] = $this->lookupById($key2, $value2)['rows']??
							[ $value2 => $value2 ];
					}
				}	
			}
			foreach ($field['fields'] as $key2 => $field2) {
				$this->formatFieldValue(null, $key2, $field['fields'][ $key2 ], $app, $for_guest);
			}
			if ( !is_null($value) ){
				$field['value'] = $value;
			}		
		} elseif ( !is_null($value) ){
			$field['value'] = $value;
		} // else use default value set in $field['value']
	}

	//process field options after value as $field['value'] required, field passed by reference
	protected function formatFieldOptions($key, &$field, $app) {
		//process options lookup, configs even if no value is set
		if ( is_numeric($key) OR empty($field['type']) ){
			return;
		}		
		if ( ($field['type'] == 'select' OR $field['type'] == 'radio' OR $field['type'] == 'radio hover') ){
			$this->formatOptions($key, $field, $app);
		} elseif ($field['type'] == 'fieldset' AND !empty($field['fields'])) {
			foreach ($field['fields'] as $key2 => $field2) {
				$this->formatOptions($key2, $field['fields'][ $key2 ], $app);
			}	
		}	
	}
	
	protected function formatOptions($key, &$field, $app) {
		if ( !empty($field['options']['From::lookup']) ){ //options provided by lookup
			$lookup = array_values($field['options']); 
			if ( !empty($lookup[1]) ){//lookup key
				$this->resolveOptions($field, $lookup);	
				if ( empty($field['value']) AND ($lookup[1] == 'staff' OR $lookup[1] == 'creator' OR $lookup[1] == 'user') ){
					if (($field['visibility']??'') == 'readonly') {
						$field['value'] = $this->user->getId();
						$field['visibility'] = 'editable'; //otherwise field is disabled due to value is set
					}
				}
			}
		} elseif ( !empty($field['options']['From::configs']) ){ //options provided by configs
			$stored = $this->getAppConfigValues($app['subtype'] .'\\'. $app['name'], $this->getAppConfigFields($app));

			$field['options'] = []; //empty it first
			if ( !empty($stored[ $key ]) ){
				if ( is_array($stored[ $key ]) ){
					$field['options'] = $stored[ $key ];
				} else {
					$field['options'][ $stored[$key] ] = $stored[$key];
				}
			}	
			foreach ($stored['fieldset1']??[] as $option) {
				if ( !empty($option[$key]) ){
					$field['options'][ $option[$key] ] = $option[$key];
				}	
			}
			foreach ($stored['fieldset2']??[] as $option) {
				if ( !empty($option[$key]) ){
					$field['options'][ $option[$key] ] = $option[$key];
				}	
			}
			foreach ($stored['fieldset3']??[] as $option) {
				if ( !empty($option[$key]) ){
					$field['options'][ $option[$key] ] = $option[$key];
				}	
			}
		}	
	}
	//resolve field's options = From::lookup
	protected function resolveOptions(&$field, $lookup){
		if ( !empty($lookup[2]) AND trim($lookup[2]) == '{{poster}}') { //lookup current poster, if value is set lookup using value instead
			$result = $this->lookupById($lookup[1], !empty($field['value'])? $field['value']: $this->user->getId() );
		} elseif ( !empty($field['lookup-value']) ){//options looked up using other's value instead of config value
			$result = $this->lookupByValue($lookup[1], $field['lookup-value'], $field['scope']??null);
			unset($field['lookup-value']);
		} elseif ( !empty($lookup[2]) AND strpos($lookup[2], 'Scope::') !== false ) {	//lookup using status
			$result = $this->lookupByValue($lookup[1], '', substr(trim($lookup[2]), 7) );
		} else { //lookup using option provided value	
			$result = $this->lookupByValue($lookup[1], $lookup[2]??null);
		}
		if ( !empty($result['slug']) ){
			$field['slug'] = $result['slug'];						
		} 
		if ( !empty($result['images']) ){
			$field['images'] = $result['images'];						
		} 
		$field['options'] = $result['rows']??[];
	}
}