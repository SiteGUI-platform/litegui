<?php
namespace SiteGUI\Core;

class App extends Page {
	use Traits\SubApp;
	use Traits\Automation; //requires $path, $changeble
	protected $lookup;

	public function __construct($config, $dbm, $router, $view, $user){
		parent::__construct($config, $dbm, $router, $view, $user);
		$this->requirements['CUSTOMER'] = 'Role::Customer';
		$this->requirements['SystemAdmin'] = "Role::SystemAdmin"; 	//Do anything
	}   
	public function generateRoutes($extra = []) {
		$extra['action'] = ['GET|POST', '/[i:site_id]/app/[edit|copy|unlink:action]/[*:id]?.[json:format]?'];
		$routes = $this->trait_generateRoutes($extra);
    	return $routes;
	}
	
	/**
	* list all pages 
	* @return none
	*/
	public function main($subtype = '') {
		if (empty($subtype)) {//no app given, load apps created by this user
			$next_actions['Appstore::site'] = [];
			$this->router->registerQueue($next_actions);	
		} else { //display records for given app
			$app = $this->getAppInfo($subtype); 
			//staff_read must be enabled
			//supervisor can list all records, normal employee can just list records created by themselves
			if ( ! $app ) {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('No such :item', ['item' => 'App']);
			} elseif ( !$this->user->has($this->requirements['CREATE']) OR 
				empty($app['app_permissions']['staff_read']) OR ( 
					!empty($app['app_permissions']['staff_read_permission']) AND 
					!$this->user->has($app['app_permissions']['staff_read_permission']) 
				) 
			){
				$this->denyAccess('list', empty($app['app_permissions']['staff_read'])? '' : $app['app_permissions']['staff_read_permission']??$this->requirements['CREATE']);
			} else {
				$this->checkActivation($app['class']);
				//Employee is a supervisor when manage permission isnt set or employee has it (both employee and non-employee app)
				if ( !empty($app['app_permissions']['staff_manage']) AND (
						empty($app['app_permissions']['staff_manage_permission']) OR 
						$this->user->has($app['app_permissions']['staff_manage_permission'])
					)
				){
					$is_supervisor = 1;
				} else {
					$is_supervisor = 0;
				}

				if ( !empty($_REQUEST['rowCount']) OR ! $this->view->html ){ //load via ajax		
					$query = $this->db->table($this->table .' AS page')
					->where('type', 'App')
					->when( !empty($_REQUEST['user']), function($query) {
						return $query->where('page.creator', $_REQUEST['user']);
					})
					->when( !empty($_REQUEST['status']), function($query) {
						return $query->where('page.status', $_REQUEST['status']);
					})
					->select('page.id', 'page.name', 'page.created', 'page.updated')
					->when( empty($app['app_hide']['slug']), function($query) {
						return $query->addSelect('page.slug');
					});
					
					if (!empty($app['name'])) { 
						$query->where('page.subtype', $app['name']);
						if ( $is_supervisor ){
							$query = $this->getUnprotected($query);							
						} else { //limit to self created records or specified records for user
							$myself = $this->user->getId();
							$for_me = 'S'. $myself .'::';
							$query->where(function ($query) use ($myself, $for_me) {
								$query->where('page.creator', $myself)
									->orWhere('page.private', 'LIKE', '%'. $for_me .'%');
							});		
						}	
					} 
					//this also change $app and $query if $show_creator
					//$show_creator = $this->showCreator($app, $query, ['creator', 'user.name AS creator_name']);
					//provide default sorting since automatic sorting has problem with unionAll
					if ( empty($_REQUEST['sort']) ){
						$_REQUEST['sort']['id'] = 'desc'; 
					}
					if ( !empty($_REQUEST['rowCount']) AND $_REQUEST['rowCount'] == 12){
						$_REQUEST['rowCount'] = 48; 
					}	
					$block = $this->prepareMain($query, $app, $this->view->html);
				}
	
				//Normal web request: no $block - Load datatable script and let it load data via ajax			
				if ( empty($block) OR $block['api']['total'] ){
					//count subapp's entries
					if ( !empty($block['api']['rows']) AND !empty($app['app_sub']) ){
						$block['api']['subapp'] = $this->countSubPages($app, array_column($block['api']['rows'], 'id'), $is_supervisor );
					}
					if ($this->view->html){				
						if ( empty($block) ){
							$block['html']['ajax'] = 1;
						}							
						if ( !empty($app['app_sub']) ) {
							$block['html']['subapps'] = implode(', ', array_keys($app['app_sub']));
						}
						if ( !empty($app['app_tick']['params']) ) {
							$block['html']['app_tick'] = $app['app_tick'];
						}
						if ( ! $is_supervisor ){
							//force display using table
							$block['html']['display'] = 'table';
						}
						$block['html']['title'] = $this->trans("Manage :item", ['item' => $this->pluralize($app['label'])]);
						$block['html']['rowCount'] = 48;
						$block['template']['file'] = "datatable";		
						$block['links']['api'] = $this->slug('App::main', ["app" => $app['slug'] ] );
						$block['links']['edit'] = $this->slug('App::action', ["action" => "edit", "id" => $app['slug'] ] );
						if ( !empty($app['app_permissions']['staff_write']) AND
			   				( empty($app['app_permissions']['staff_write_permission']) OR $this->user->has($app['app_permissions']['staff_write_permission']) )
			   			){
							$block['links']['copy'] = $this->slug('App::action', ["action" => "copy", "id" => $app['slug'] ] );
							$block['links']['update'] = $this->slug('App::update');
						}	
						if ( $is_supervisor ){
							$block['links']['delete'] = $this->slug('App::delete', ["app" => $app['slug'] ] );
			   			} 
					}
				} else {
					$status['result'] = "error";
					$status['message'][] = $this->trans('You have not created any :type!', ['type' => $app['label'] ] );
					
					if ($this->view->html){				
						$status['html']['message_type'] = 'info';
						$status['html']['message_title'] = $this->trans('Information');	

						$link = $this->slug('App::action', ["action" => "edit"] );
						if ($subtype) {
							$link .= '/'. strtolower($subtype);
						} 
						$block['links']['edit'] = $link; //for dynamic UI
						$status['message'][ $link ] = $this->trans('Click here to create a new :type', ['type' => $app['label'] ]);
					}
				}			

				$this->view->addBlock('main', $block, 'App::main');
			}		
			!empty($status) && $this->view->setStatus($status);
		}						
	}

	/**
	* client list only items they own unless they has read permission (list all)
	* @return none
	*/
	public function clientMain($subtype = '') {
		if (!empty($subtype)) {//display records for given app
			$app = $this->getAppInfo($subtype); 
			if ( !empty($app['app_permissions']['client_read_permission']) ){
				if ( $this->user->has($app['app_permissions']['client_read_permission']) ){
					$user_has_permission = 1;
				} else {	
					$app['app_permissions']['client_read'] = null;
				}	
			}
			if ( ! $app ) {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('No such :item', ['item' => 'App']);
			} elseif ( !str_contains($app['app_users']??'', '_client') OR 
				!$this->user->has($this->requirements['CUSTOMER']) OR 
				empty($app['app_permissions']['client_read'])
			){
				$this->denyAccess('list');
			} else {		
				$query = $this->db
					->table($this->table .' AS page')
					->where('page.type', 'App')
					->select('page.id', 'page.name', 'page.created', 'page.updated')
					->when( empty($app['app_hide']['slug']), function($query) {
						return $query->addSelect('page.slug', 'page.type', 'page.subtype'); //to generate url
					});
				if (!empty($app['name'])) { 
					$this->checkActivation($app['class']);
					$query = $query->where('page.subtype', $app['name']);
				} 
				if ( !empty($user_has_permission) ){ //client can read all unprotected records like staff
					$query = $this->getUnprotected($query);
				} else {
					$myself = $this->user->getId();
					$for_me = 'U'. $myself .'::';
					$query->where(function ($query) use ($myself, $for_me) {
						$query->where('page.creator', $myself)
							->orWhere('page.private', 'LIKE', '%'. $for_me .'%');
					});
				}

				//remove non client_ fields before processing
				foreach ( ($app['app_fields']??[]) AS $key => $field) {
					if ( empty($field['visibility']) ){
						//treat it as client_editable
					} elseif ( !str_contains($field['visibility'], 'client_') OR $field['visibility'] == 'client_hidden' ){
						unset($app['app_columns'][ $app['slug'] .'_'. $key ], $app['app_columns'][ $key ]);
					}
				}

				if ( !empty($_REQUEST['rowCount']) AND $_REQUEST['rowCount'] == 12){
					$_REQUEST['rowCount'] = 48; 
				}	
				$block = $this->prepareMain($query, $app, null, null);
				if ( $block['api']['total'] ){	
					//count subapp's entries
					if ( !empty($block['api']['rows']) AND !empty($app['app_sub']) ){
						$block['api']['subapp'] = $this->countSubPages($app, array_column($block['api']['rows'], 'id') );	
					}
					if ($this->view->html){				
						//force display using table
						$block['template']['file'] = "datatable";		
						$block['html']['title'] = $this->trans($this->pluralize($app['label']));
						$block['html']['display'] = 'table';
						$block['html']['rowCount'] = 48;
						$block['links']['api']  = $this->slug('App::clientMain', ["app" => $app['slug'] ] );
						$block['links']['edit'] = $this->slug('App::clientView', ["id" => $app['slug'] ] );
						if ( !empty($app['app_permissions']['client_delete']) ){
							$block['links']['delete'] = $this->slug('App::clientDelete', ["app" => $app['slug'] ] );
			   			} 
					}
				} else {
					$status['result'] = "error";
					$status['message'][] = $this->trans('You have yet to have any :item', ['item' => $app['label'] ] );
					
					if ($this->view->html){				
						$status['html']['message_type'] = 'info';
						$status['html']['message_title'] = $this->trans('Information');	

						if ( !empty($app['app_menu']['client']) AND $app['app_menu']['client'] != 'readonly' ){
							$link = $this->slug('App::clientView');
							if ($subtype) {
								$link .= '/'. strtolower($subtype);
							} 
							$status['message'][ $link ] = $this->trans('Click here to create a new :type', ['type' => $app['label']??'App'] );
						}	
					}
				}			

				$this->view->addBlock('main', $block, 'App::clientMain');
			}	
			!empty($status) && $this->view->setStatus($status);
		}						
	}

	//Catch-all for Core-app like editing URL ../task/edit/1 => ../app/edit/task/1
	public function edit2($app, $id = 0, $menus = 'Menu::getMenus'){
		$this->edit($app .'/'. $id, $menus);
	}	
	/**
	 * print out edit form
	 * @param  integer $id [description]
	 * @return [type]           [description]
	 */
	public function edit($id = 0, $menus = 'Menu::getMenus'){
		if (!$id or is_numeric($id)) {//no app given, redirect to app builder 
		} else { //either app or app/id	
			// we hash the target URL and use it as the onetime CSRF token, $for will limit update to the current id or new entry only
			$links['edit']    = $this->slug('App::action', ['action' => 'edit' ]);
			$links['update']  = $this->slug('App::update') .'?for='. urldecode($id); //may contain emoji
			$links['update'] .= !empty($this->config['system']['sgframe'])? '&sgframe=1' : '';
			$this->loadSandboxPage($links['edit'], $id, $links['update']);
			//should be in sandbox url now
			//set policy to stop sending ajax request to any site other than system url and cdn for fetching language.json
			header("Content-Security-Policy: connect-src 'self' ". $this->config['system']['url'] ." ". $this->config['system']['cdn'] .";");

			//reuse one-time edit url to keep the crsf token intact
			$links['editor'] = $this->config['system']['edit_url'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			//return early for editor frame
			if ( !empty($_REQUEST['frame']) AND $_REQUEST['frame'] == 'editor' ){
				$block['template']['file'] = "page_editor";
				$block['links'] = $links;	
				$this->view->addBlock('main', $block, $this->class .'::editor');
				$this->view->setLayout('blank');
				return;
			} 
			//we need to load appinfo to check permission
			$page = [];
			if (strpos($id, '/')) { //app/111
				$subtype = strtok($id, '/'); //urldecode to support 💬
				$id = strtok('/');
				if (is_numeric($id)) {
					$page = $this->read($id, 'App', $this->formatAppName($subtype) );

					if($page AND $page['type'] == 'App' AND !isset($_REQUEST['frame']) AND !isset($_REQUEST['subapp']) ){
						$page['meta'] = $this->readMeta($id);
						$next_actions['Collection::getCollectionsByPageId'] = [ $id ];
					} 					
				}
			} elseif ( !empty($id) ){ //new app record
				$page["subtype"] = $this->formatAppName($id);
			}	

			$app = $this->getAppInfo($page["subtype"]??''); 
			if ( !empty($app['app_permissions']['staff_manage']) AND (
					empty($app['app_permissions']['staff_manage_permission']) OR 
					$this->user->has($app['app_permissions']['staff_manage_permission'])
				)
			){
				$is_supervisor = 1;
			} else {
				$is_supervisor = 0;
			}
			$for_me = 'S'. $this->user->getId() .'::'; 
			if ( (empty($page['id']) AND 
					( //no write permission
						!$this->user->has($this->requirements['CREATE']) OR 
						empty($app['app_permissions']['staff_write']) OR ( 
							!empty($app['app_permissions']['staff_write_permission']) AND 
							!$this->user->has($app['app_permissions']['staff_write_permission']) 
						)
					)
				) OR ( 
					!empty($page['id']) AND ( //no read permission
						(
				    		empty($app['app_permissions']['staff_read']) OR (
				    			!empty($app['app_permissions']['staff_read_permission']) AND 
				    			!$this->user->has($app['app_permissions']['staff_read_permission']) 
				    		)
				    	) OR ( 
							$page['creator'] != $this->user->getId() AND 
							!str_contains($page['private'], $for_me) AND ( 
								!$is_supervisor OR ( 
									strpos($page['private'], '::') AND 
									!$this->user->has($page['private']) 
								) 
							)
			    		)
			    	)
			    )		
			){//Site + Page level permission (entry creator is granted view/edit access)
				$this->denyAccess(is_numeric($id)? 'edit' : 'create', (empty($app['app_permissions']['staff_read']) || empty($app['app_permissions']['staff_write']))? '' : 
					$app['app_permissions']['staff_manage_permission']??
					$app['app_permissions']['staff_write_permission']??
					$app['app_permissions']['staff_read_permission']??
					$this->requirements['CREATE']
				);
			} elseif ( ! $app ) {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('No such :item', ['item' => $app['label'] ]);
			} else { 
				$response = $this->appProcess($app, 'edit', $page);	

				if ( empty($response['blocks']) ){
					$status['result'] = 'error';
					$status['message'][] = $this->trans('Invalid response from app');
				}
				//wysiwyg frame needs page content only	//App does not enable Wysiwyg
				if ( !empty($_REQUEST['frame']) AND $_REQUEST['frame'] == 'wysiwyg' ){
					$this->view->setLayout('blank');
					if ( !empty($app['app_hide']['wysiwyg']) ){
						$status['result'] = 'error';
						$status['message'][] = $this->trans('Visual mode is not enabled');
					} else {						
						$block['template']['file'] = "page_wysiwyg";
						$block['api']['page'] = $response['blocks']['main']['api']['page']?? '';

						$links['widget'] = $this->slug('Widget::preview');
						$links['widget'] .= '?cors='. @$this->hashify($links['widget'] .'::'. $_SESSION['token']);

						$links['snippet'] = $this->slug('Template::snippet');
						$links['snippet'] .= '?cors='. @$this->hashify($links['snippet'] .'::'. $_SESSION['token']);
						
						$links['genai'] = $this->slug('Assistant::action', ['action' => 'generate']);
						$links['genai'] .= '?cors='. @$this->hashify($links['genai'] .'::'. $_SESSION['token']);
						$block['links'] = $links;						

						//get available widgets
						$widgets = $this->db
							->table($this->site_prefix ."_widget")
							->where('type', '<>', 'Latest')
							->where('type', '<>', 'Site::Report')
							->select('id', 'name', 'type')
							->get()->all();	
						foreach ($widgets as $widget) {
							$group[ $widget['type'] ]['name'] = $widget['type'];
							$group[ $widget['type'] ]['snippets'][] = $widget;
						}
						$block['api']['widgets'] = !empty($group)? $group : [];
						$this->view->addBlock('main', $block, 'App::'. $app['name'] .'::wysiwyg');		
							
						$next_actions['Template::getSnippets'] = [];
					}	
				} elseif ( empty($_REQUEST['subapp']) ) { //normal app edit - wont need if subapp is specified
					//let hooks change $response['blocks'], hook definition must also use references. Hook may change blocks directly or return to be added by runHook
					$this->runHook('App::'. $app['name'] .'::'. __FUNCTION__, [ &$response['blocks'] ], false, 'add_blocks');
					foreach ( ($response['blocks']??[]) as $section => $block) {
						if ($section == 'main'){
						  	//combine stored app config	with config from app at run time only - keep $app intact 
						  	//combine stored app_hide with response app.hide, builder config is preferred 
							$block['api']['app']['hide'] = ($app['app_hide']??[]) + ($block['api']['app']['hide']??[]); 
							//Hide published button if does not have permission
							if ( (!$this->user->has($this->requirements['PUBLISH']) OR !$is_supervisor) AND empty($app['app_enable']['versioning']) ){
								$block['api']['app']['hide']['published'] = 1;
							} 
						  	//combine stored app_fields with response app.fields, before adding meta value	
						  	$fields = ($app['app_fields']??[]) + ($block['api']['app']['fields']??[]);
						  	unset($block['api']['app']['fields']); //block fields should be appended

							//find parent source after $fields to remove related fields
							if ( !empty($page['id']) ){
								$block['api']['parent'] = $this->getParentPage($page, $fields);
							}
							foreach ( $fields AS $key => $field) {
								if ( is_numeric($key) OR empty($field['type']) ){
									continue;
								}

								$meta_key = $app['slug'] .'_'. $key;
								//when manage permission is set, and employee doesnt have it, 
								//allow (staff_)client_readonly/editable fields only like clientView (client_editable?)
								if ( !empty($field['visibility']) AND ($field['visibility'] == 'staff_client_readonly' OR $field['visibility'] == 'readonly') ){
									if ( (str_contains($field['is']??null, 'required')) OR 
										!empty($field['value']) OR 
										!empty($block['api']['page'][ $key ]) OR 
										!empty($block['api']['page']['meta'][ $key ]) OR 
										!empty($block['api']['page']['meta'][ $meta_key ]) 
									){
										$field['visibility'] = 'readonly';
									} else {
										//unset($block['api']['app']['fields'][ $key ]);
										unset($block['api']['page']['meta'][ $key ], $block['api']['app']['fields'][ $key ], $block['api']['page']['meta'][ $meta_key ]);
										continue;
									}
								} elseif ( !$is_supervisor ){
									if ( empty($field['visibility']) ){ //treat it as client_editable
										$field['visibility'] = 'client_editable';
									}
									//remove fields other than (staff_)client_editable/readonly
									if ( str_contains($field['visibility'], 'client_readonly') AND (str_contains($field['is']??null, 'required') OR !empty($field['value']) ) ){ //show if readonly has value or is required/multiple-required, otherwise hide, should not change form_field.tpl as this applies to client only
										$field['visibility'] = 'readonly';
									} elseif ( $field['visibility'] != 'client_editable' ){
										//unset($block['api']['app']['fields'][ $key ]);
										unset($block['api']['page']['meta'][ $key ], $block['api']['app']['fields'][ $key ], $block['api']['page']['meta'][ $meta_key ]);
										continue;
									} elseif ( !empty($page['id']) AND $page['creator'] != $this->user->getId() ){ 
										//not owner - adjust visibility for displaying purpose
										//non-supervisor like client also like staff, maybe just let them edit client_editable
										//$field['visibility'] = 'readonly';
									}
								}
								//special case: lookup using other input, should NOT move inside formatFieldValue
								if ( $field['type'] == 'select' AND (!empty($field['options']['From::input']) OR !empty($field['options']['From::lookup'])) ){
									$lookup = array_values($field['options']);
									$field['lookup'] = $lookup[1]??null;
									if ( !empty($lookup[3]) ){
										$field['listen'] = $lookup[3];
										$lookup = $field['listen']; //shorten
							    		//this value from another input should already gone thru formatFieldValue
						    			$field['lookup-value'] = in_array($lookup, $this->changeable)? 
    										($block['api']['page'][ $lookup ]??null) : 
    										($fields[ $lookup ]['value']??null); 
    									if (is_array($field['lookup-value'])){
											$field['lookup-value'] = array_keys($field['lookup-value'])[0];
										}	
										if ( !empty($field['options']['Scope::SubRecords']) ){
											$field['scope'] = 'SubRecords';
										}
						    		}	
								}
								//process value before option so option can see field['value']
				    			if ( $key == 'creator' AND empty($page['id']) AND !empty($_GET['user']) ){ 
				    				//create for user, editable so form will output a hidden field for user_id
				    				($field['visibility']??null) == 'readonly' && $field['visibility'] = 'editable';
				    				$this->formatFieldValue($_GET['user'], $key, $field, $app);
				    			} elseif ( in_array($key, $this->changeable) OR array_key_exists($key, $block['api']['page']??[]) ){ //changeable plus read-only columns
									if ( in_array($key, ['name', 'title', 'description', 'content']) ){
					    				$this->formatFieldValue($block['api']['page'][ $key ][ $this->config['site']['language'] ]??$block['api']['page'][ $key ]??null, $key, $field, $app);
				    				} else {
					    				$this->formatFieldValue($block['api']['page'][ $key ]??null, $key, $field, $app);
				    				}
					    		} elseif (array_key_exists($key, $block['api']['page']['meta']??[]) OR array_key_exists($meta_key, $block['api']['page']['meta']??[]) OR !empty($field['value']) ) {
				    				$this->formatFieldValue($block['api']['page']['meta'][ $key ]??$block['api']['page']['meta'][ $meta_key ]??null, $key, $field, $app);
					    		} elseif ($field['type'] == 'fieldset') {//format scope in value
					    			$this->formatFieldValue(null, $key, $field, $app);
					   			} 						
								//process options lookup, configs even if no value is set, $field passed by ref
								$this->formatFieldOptions($key, $field, $app);
		
								$block['api']['app']['fields'][ $key ] = $fields[ $key ] = $field; //also set value to $fields
								//unset($block['api']['page']['meta'][ $meta_key ]); //maybe leave it there for api
							}
							foreach ( $app['app_buttons']??[] AS $btn ){
								if ( str_contains($btn['visibility'], 'staff') OR (
										$btn['visibility'] == 'creator' AND (
											empty($page['creator']) OR $page['creator'] == $this->user->getId()
										)
									)
								){
									$block['api']['app']['buttons'][] = $btn;
								}
							}
							if ( !empty($app['app_tick']['params']) ){
								$block['api']['app']['tick'] = $app['app_tick'];
							}

							if ( !empty($app['app_sub']) ){
								$block['api']['app']['sub'] = $app['app_sub'];
							}
							if ( !empty($page['id']) AND !empty($app['app_enable']['versioning']) ){
								$block['api']['versioning'] = $this->getVersions($page['id']);
							}	

						 	if ($this->view->html){				
								//$links['update'] = $this->slug('App::update', []);
								$links['main'] = $this->slug('App::main', ['app' => $app['slug'] ]);
								$links['main'] .= !empty($this->config['system']['sgframe'])? '?sgframe=1' : '';
								$links['subapp'] = $links['editor'];
								$links['activities'] = $this->slug('Activity::main') .'?cors=1&html=1&for='. $app['name'] .'&fid='. ($page['id']??'');
								$links['file_view'] = $this->slug('File::action', ['action' => 'view']);
								//$links['file_api'] = $this->slug('File::action', ["action" => "manage"] );
								$links['leave_collection'] = $this->slug('Collection::leave');
								$links['leave_collection'] = $links['leave_collection'] .'?cors='. @$this->hashify($links['leave_collection'] .'::'. $_SESSION['token']);
								$links['lookup'] = $this->slug('Lookup::now');
								$links['lookup'] = $links['lookup'] .'?cors='. @$this->hashify($links['lookup'] .'::'. $_SESSION['token']);
								if (!empty($block['api']['page']['slug']) AND str_contains($app['app_users'], 'guest') ){
									$links['uri'] = $this->url($block['api']['page']['slug'], $block['api']['page']['type'], $block['api']['page']['subtype']);
									if ( empty($page_info['published'])){
						         		$links['uri'] .= '?oauth=sso';
						         		$links['uri'] .= '&login=step1';
						         		$links['uri'] .= '&preview=1';
						         		$links['uri'] .= '&initiator='. $this->encode($this->config['system']['url'] . $this->config['system']['base_path']);		         			
					         		}
					         	}
					         		
								$block['links'] = $links;						
								if (is_array($menus)) {
									$block['html']['menus'] = $menus; 
								}
								if (empty($block['html']['title'])) {
									$block['html']['title'] = $this->trans((is_numeric($id)? "Edit" : "New") ." :item", ['item' => $app['label'] ]);
								}
								if ( empty($page['meta']['upload_dir']) ){
									$upload_dir = date("Y") .'Q'. ceil(date("n") / 3);
								} else {
									$upload_dir = $page['meta']['upload_dir'];
								}
								$block['html']['ajax'] = 1; //for loading Activities
								$block['html']['publisher'] = ($this->user->has($this->requirements['PUBLISH']) OR $is_supervisor);	
								$block['html']['upload_dir'] = 'elf_l1_'. rtrim(strtr(base64_encode($upload_dir), '+/=', '-_.'), '.');

								if ( !empty($app['app_templates']['edit'])) {//override 
									$block['template']['file'] = $app['app_templates']['edit'];
								}
								if ( !empty($block['template']['file']) ){ //can be in app folder or system template folder 
									$block['template']['directory'] = 'resources/public/templates/app/'. $app['slug'] . ($app['id']??''); 
									//App may have a custom layout for editing, layout set in Builder is preferred
									$layout = $app['app_layouts']['edit']??$block['template']['layout']??null; 
									if ($layout AND is_file($this->path .'/../admin/'. $this->config['system']['template'] .'/layouts/'. $layout .'.tpl') 
									){
										$this->view->setLayout($layout); //only set if app uses custom template otherwise default layout
									}
								} else {
									$block['template']['file'] = "app_edit";
								}
							}	
						}
						$this->view->addBlock($section, $block, 'App::'. $app['name'] .'::edit');		
					}

					$next_actions['Layout::getLayouts'] = [ $this->config['site']['template'] ];
				}

				//subapp pages
				if ( !empty($app['app_sub']) ){
					$this->editSubApps($page??null, $app, $is_supervisor, $links['update']);
				}

				if (!empty($next_actions)){
					$this->router->registerQueue($next_actions);	
				}
			}		
			!empty($status) && $this->view->setStatus($status);
		}	
	}

	/**
	 * print out edit form for client, client can only view records created by them unless they has read permission or directed to them or published records 
	 * @param  integer $id [description]
	 * @return [type]           [description]
	 */
	public function clientView($id = 0, $menus = 'Menu::getMenus'){
		// we hash the target URL and use it as the onetime CSRF token
		$links['edit']    = $this->slug('App::clientView');
		$links['update']  = $this->slug('App::clientUpdate') .'?for='. urldecode($id); //may contain emoji
		$links['update'] .= !empty($this->config['system']['sgframe'])? '&sgframe=1' : '';
		$this->loadSandboxPage($this->slug('App::clientView'), $id, $links['update']);

		//we need to load appinfo to check permission
		$page = [];
		if (strpos($id, '/')) { //app/111
			$page["subtype"] = strtok($id, '/'); 
			$id = strtok('/');
		} elseif ( !empty($id) ){ //new app
			$page["subtype"] = $this->formatAppName($id); 
		}	
		$app = $this->getAppInfo($page["subtype"]??''); 
		//adjust app permissions depending on user
		if ( !empty($app['app_permissions']['client_read_permission']) ){
			if ( $this->user->has($app['app_permissions']['client_read_permission']) ){
				$user_has_read_permission = 1;
			} else {	
				$app['app_permissions']['client_read'] = null;
			}	
		}
		if ( !empty($app['app_permissions']['client_write_permission']) ){
			if ( $this->user->has($app['app_permissions']['client_write_permission']) ){
				$user_has_write_permission = 1;
			} else {	
				$app['app_permissions']['client_write'] = null;
			}	
		}

		if (is_numeric($id)) {
			$page = $this->read($id, 'App', $this->formatAppName($page["subtype"]) );
    		//unpublished page created by others  
			$for_me = 'U'. $this->user->getId() .'::'; //edit permission needed
			if (!empty($page['creator']) AND 
				$page['creator'] != $this->user->getId() AND 
				!str_contains($page['private'], $for_me) AND 
				empty($user_has_read_permission) AND ( 
					$page['published'] < 1 OR 
					$page['published'] > time() OR 
					($page['expire'] > 0 AND $page['expire'] < time() ) 
				) 
			){
				$this->denyAccess('edit');
			}
			if ( !empty($page['id']) AND $page['type'] == 'App' ){
				$page['meta'] = $this->readMeta($id);
				//$next_actions['Collection::getCollectionsByPageId'] = [ $id ];
			} 
		}
		//deny access if clientarea is support but app_visibility doesnt support client_ and hidden
		if (!str_contains($app['app_users']??'', '_client') OR 
			!$this->user->has($this->requirements['CUSTOMER']) OR
			( empty($page['id']) AND empty($app['app_permissions']['client_write']) ) OR
			(!empty($page['id']) AND empty($app['app_permissions']['client_read']) ) 
		){
			$this->denyAccess(is_numeric($id)? 'edit' : 'create');
		} elseif ( ! $app ) {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('No such :item', ['item' => $app['label'] ]);
		} elseif ( empty($_REQUEST['subapp']) ) { 
			$response = $this->appProcess($app, 'clientView', $page);	

			if ( empty($response['blocks']) ){
				$status['result'] = 'error';
				$status['message'][] = $this->trans('Invalid response from app');
			}	
			//let hooks change $response['blocks'], hook definition must also use references. Hook may change blocks directly or return to be added by runHook
			$this->runHook('App::'. $app['name'] .'::'. __FUNCTION__, [ &$response['blocks'] ], false, 'add_blocks');
			foreach ( ($response['blocks']??[]) AS $section => $block) {
				if ($section == 'main'){
				  //combine stored app config	with config from app at run time only - keep $app intact 
				  //combine stored app_hide with response app.hide, builder config is preferred 
					$block['api']['app']['hide'] = ($app['app_hide']??[]) + ($block['api']['app']['hide']??[]); 
					//Hide published button if does not have permission
					//if ( !$this->user->has($this->requirements['PUBLISH']) OR 
					//	 ( !empty($app['app_permissions']['manage']) AND !$this->user->has($app['app_permissions']['manage']) ) ){
					if ( empty($app['app_permissions']['client_write']) ){
						$block['api']['app']['hide']['save'] = 1;
						$block['api']['app']['hide']['published'] = 1;
					}
					if ( empty($app['app_enable']['versioning']) ){
						$block['api']['app']['hide']['published'] = 1;
					}	

					$block['api']['app']['hide']['tabsettings'] = 1; //client should not be able to change settings 
					//} 
					if ( !empty($page['id']) AND $page['creator'] != $this->user->getId() ){//hide tab setting if not the creator
						$block['api']['app']['hide']['tabsettings'] = 1;
					}
				  	//combine stored app_fields with response app.fields, before adding meta value	
				  	$fields = ($app['app_fields']??[]) + ($block['api']['app']['fields']??[]);
				  	unset($block['api']['app']['fields']); //block fields should be appended
					//find parent source after $fields to remove related fields
					if ( !empty($page['id']) ){
						$block['api']['parent'] = $this->getParentPage($page, $fields);
					}
					foreach ( $fields AS $key => $field) {
						$meta_key = $app['slug'] .'_'. $key;
						if ( empty($field['visibility']) ){ //treat it as client_editable
							$field['visibility'] = 'client_editable';
						}
						//remove fields other than (staff_)client_readonly/editable
						if ( str_contains($field['visibility'], 'client_readonly') AND (str_contains($field['is']??null, 'required') OR !empty($field['value']) OR !empty($block['api']['page'][ $key ]) OR !empty($block['api']['page']['meta'][ $key ]) OR !empty($block['api']['page']['meta'][ $meta_key ]) ) ){ //show if readonly has value or is required/multiple-required, otherwise hide, should not change form_field.tpl as this applies to client only
							$field['visibility'] = 'readonly';
						} elseif ( $field['visibility'] != 'client_editable' ){
							//unset($block['api']['app']['fields'][ $key ]);
							unset($block['api']['page']['meta'][ $key ], $block['api']['app']['fields'][ $key ], $block['api']['page']['meta'][ $meta_key ] );
							continue;
						} elseif ( !empty($page['id']) AND $page['creator'] != $this->user->getId() AND !str_contains($page['private'], $for_me .'w') ){ 
							//not owner - adjust visibility for displaying purpose
							$field['visibility'] = 'readonly';
						}

						//special case: lookup using other input, should NOT move inside formatFieldValue
						if ( !empty($field['type']) AND $field['type'] == 'select' AND (!empty($field['options']['From::input']) OR !empty($field['options']['From::lookup'])) ){
							$lookup = array_values($field['options']);
							$field['lookup'] = $lookup[1]??null;
							if ( !empty($lookup[3]) ){
								$field['listen'] = $lookup[3];
								$lookup = $field['listen']; //shorten
					    		//this value from another input should already gone thru formatFieldValue
				    			$field['lookup-value'] = in_array($lookup, $this->changeable)? 
									($block['api']['page'][ $lookup ]??null) : 
									($fields[ $lookup ]['value']??null); 
								if (is_array($field['lookup-value'])){
									$field['lookup-value'] = array_keys($field['lookup-value'])[0];
								}
								if (empty($field['lookup-value'])){ //client cannot lookup from frontend, unset to list all
									unset($field['options']['From::input'], $field['options'][ $field['listen'] ]);
								}
								if ( !empty($field['options']['Scope::SubRecords']) ){
									$field['scope'] = 'SubRecords';
								}	
				    		}	
						}
						//process value before option so option can see field['value']
		    			if ( in_array($key, $this->changeable) OR array_key_exists($key, $block['api']['page']??[]) ){ //changeable plus read-only columns
							if ( in_array($key, ['name', 'title', 'description', 'content']) ){
					    		$this->formatFieldValue($block['api']['page'][ $key ][ $this->config['site']['language'] ]??$block['api']['page'][ $key ]??null, $key, $field, $app);
					    	} else {
					    		$this->formatFieldValue($block['api']['page'][ $key ]??null, $key, $field, $app);
					    	}			    				
			    		} elseif ( array_key_exists($key, $block['api']['page']['meta']??[]) OR array_key_exists($meta_key, $block['api']['page']['meta']??[]) OR !empty($field['value']) ){
		    				$this->formatFieldValue($block['api']['page']['meta'][ $key ]??$block['api']['page']['meta'][ $meta_key ]??null, $key, $field, $app);
			    		} elseif ( !empty($field['type']) AND $field['type'] == 'fieldset') { //format scope in value
					    	$this->formatFieldValue(null, $key, $field, $app);
					   	}			
						//process options lookup, configs even if no value is set, $field passed by ref
						$this->formatFieldOptions($key, $field, $app);
	
						$block['api']['app']['fields'][ $key ] = $fields[ $key ] = $field; //also set value to $fields
						unset($block['api']['page']['meta'][ $key ], $block['api']['page']['meta'][ $meta_key ]);
					}
					foreach ( $app['app_buttons']??[] AS $btn ){
						if ( str_contains($btn['visibility'], 'client') OR (
								$btn['visibility'] == 'creator' AND (
									empty($page['creator']) OR $page['creator'] == $this->user->getId()
								)
							) 
						){
							$block['api']['app']['buttons'][] = $btn;
						}
					}	
					//subapp
					if ( !empty($app['app_sub']) ){
						$block['api']['app']['sub'] = $app['app_sub'];
					}
					if ( !empty($page['id']) AND !empty($app['app_enable']['versioning']) ){
						$block['api']['versioning'] = $this->getVersions($page['id']);
					}

				 	if ($this->view->html){				
						//$links['update'] = $this->slug('App::update', []);
						$links['main'] = $this->slug('App::clientMain', ['app' => $app['slug'] ]);
						$links['main'] .= !empty($this->config['system']['sgframe'])? '?sgframe=1' : '';
						$links['file_view'] = $this->slug('File::clientView');
						$links['subapp'] = $this->config['system']['edit_url'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
						if (!empty($block['api']['page']['slug']) AND str_contains($app['app_users'], 'guest') ){
							$links['uri'] = $this->url($block['api']['page']['slug'], $block['api']['page']['type'], $block['api']['page']['subtype']);
							if ( empty($page_info['published'])){
				         		$links['uri'] .= '?oauth=sso';
				         		$links['uri'] .= '&login=step1';
				         		$links['uri'] .= '&preview=1';
				         		$links['uri'] .= '&initiator='. $this->encode($this->config['system']['url'] . $this->config['system']['base_path']);		         			
			         		}
			         	}

						$block['links'] = $links;						
						if ( !empty($_COOKIE) AND $this->getAppConfigValues('App\\'. $app['name'], null, '_captcha') ) {
							$block['html']['challenge_captcha'] = $this->createCaptcha(substr($this->config['salt'], 5, 15)); 
						}
						if (is_array($menus)) {
							$block['html']['menus'] = $menus; 
						}
						if (empty($block['html']['title'])) {
							$block['html']['title'] = $this->trans((is_numeric($id)? "View" : "New") ." :item", ['item' => $app['label'] ]);
						}

						if ( !empty($app['app_templates']['edit'])) {//override 
							$block['template']['file'] = $app['app_templates']['edit'];
						}
						if ( !empty($block['template']['file']) ){ //can be in app folder or system template folder 
							$block['template']['directory'] = 'resources/public/templates/app/'. $app['slug'] . ($app['id']??''); 
							//App may have a custom layout for editing, layout set in Builder is preferred
							$layout = $app['app_layouts']['edit']??$block['template']['layout']??null; 
							if ($layout AND is_file($this->path .'/../admin/'. $this->config['system']['template'] .'/layouts/'. $layout .'.tpl') 
							){
								$this->view->setLayout($layout); //only set if app uses custom template otherwise default layout
							}
						} else {
							$block['template']['file'] = "app_edit";
						}
					}	
				}
				$this->view->addBlock($section, $block, 'App::'. $app['name'] .'::clientView');		
			}
		}		

		//subapp pages
		if ( !empty($app['app_sub']) ){
			$this->clientViewSubApps($page??null, $app);
		}

		if (!empty($next_actions)){
			$this->router->registerQueue($next_actions);	
		}
		!empty($status) && $this->view->setStatus($status);
	}


	public function update($page) {
		if ($this->loop > 100) $this->denyAccess('proceed. Loop detected at '. __FUNCTION__ .' ('. $this->loop .')');
		$this->loop++;

		$app = $this->getAppInfo($page["subtype"]??''); 
		if ( ! $app ) {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('No such :item', ['item' => 'App']);
			$this->view->setStatus($status);
		} elseif ( ! $this->user->has($this->requirements['CREATE']) OR 
			empty($app['app_permissions']['staff_write']) OR 
		   	( !empty($app['app_permissions']['staff_write_permission']) AND !$this->user->has($app['app_permissions']['staff_write_permission']) ) 
		){
			$this->denyAccess('update', empty($app['app_permissions']['staff_write'])? '' : 
				$app['app_permissions']['staff_write_permission']??
				$this->requirements['CREATE'] 
			);
		} else {
			if ( !empty($page['tick']) ){ //pre-process just like they come from frontend
				parse_str(urldecode($page['tick']), $page['tick']);
				if ( !empty($page['tick']['page']) AND is_array($page['tick']['page']) ){
					foreach ($page['tick']['page'] AS $k => $v){
						if ( in_array($k, $this->changeable) AND !array_key_exists($k, $page['fields']??[]) ){ 
							$page[ $k ] = $v; //could be override by $page['fields'][ $k ] if exists
						} else { 
							$page['fields'][ $k ] = $v; //processed later
						}
					}
				}
			}
			//update comes from sandbox page, make sure the id isnt arbitrary, 'for' is part of onetime token, cant be removed by user
 			if ( isset($_GET['for']) ){
				if ( $this->config['system']['csrf'] AND 
					$_POST['csrf_token'] != $_SESSION['token'] AND 
					$_GET['for'] != trim(strtolower($page['subtype']) .'/'. ($page['id']??''), '/') //for=app_name when creating a new entry - remove / 
				){
					$this->denyAccess('update this record');
				}
				unset($_GET['for']); //run once only 
			}	

			if ( !empty($app['app_permissions']['staff_manage']) AND (
					empty($app['app_permissions']['staff_manage_permission']) OR 
					$this->user->has($app['app_permissions']['staff_manage_permission'])
				)
			){
				$is_supervisor = 1;
			} else {
				$is_supervisor = 0;
			}			
			//update version
			if ( !empty($page['id']) AND !empty($page['updated']) AND $page['updated'] == 'replace_original_version_with_this' ){
				if ($is_supervisor ){
					$this->commitVersion($page, __FUNCTION__);
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans("You don't have permissions to :action", ['action' => 'merge' ]);
				}	
			} 

			$this->db->beginTransaction(); //start the db transaction
			if ( !empty($page['id']) ){
				$page_info = $this->read($page['id'], 'App', $app['name'], NULL, 'for_update');
				$page_info['meta'] = $this->readMeta($page['id']); //for automation to get stored input
			}	
			//prevent unauthorized update to others' records
			$for_me = 'S'. $this->user->getId() .'::w'; //write permission needed
			if ( !empty($page_info) AND 
				$page_info['creator'] != $this->user->getId() AND 
				!str_contains($page_info['private'], $for_me) AND ( 
					!$is_supervisor OR ( 
						strpos($page_info['private'], '::') AND 
						!$this->user->has($page_info['private']) 
					) 
				) 
			){
				$page['page_linking_only'] = 1; 
				//$this->denyAccess('update. Permission required: '. ($page_info['private']?: $app['app_permissions']['manage']) );
			}

			//Published page can't be updated (unless clientpublish is enable) but page_linking is allowed 
			if ( !empty($page_info) AND 
				$page_info["published"] > 0 AND 
				empty($app['app_enable']['clientpublish']) AND 
				empty($page['page_linking_only']) AND
				( !$is_supervisor OR !$this->user->has($this->requirements['PUBLISH']) ) 
			){ 
				if ( !empty($page['sub']) ){ //allow page_linking_only
					$page['page_linking_only'] = 1;
				} elseif ( !empty($app['app_enable']['versioning']) ){
					$this->logActivity('Update Request Denied. Creating a new one', $app['name'], $page_info['id'], 'Warning');
					//$page['slug'] .= "-copy";
					if ( !empty($page['name'][ $this->config['site']['language'] ]) AND !str_ends_with($page['name'][ $this->config['site']['language'] ], ' (Cloned)') ){
						$page['name'][ $this->config['site']['language'] ] .= " (Cloned)";
					}
					$_master = $page_info['id']; //revision
					unset($page['id'], $page['published'], $page_info['id'], $page_info['published']); //force creating a new page
				} else {	
					//deny instead of creating new entry for app
					$this->denyAccess('update a published entry', $app['app_permissions']['staff_manage_permission']??$this->requirements['PUBLISH'] );
				}	
			}

			//prevent data tamper
			$page['_public'] = $page_info['public']??[]; //can be written by anyone for public use purpose (count, average)
			unset($page['$']);
			$page['meta'] = []; //meta can be set by input fields

			//change only app's fields to meta and prefix app_ to key, other page fields are left untouched
			foreach ( ($app['app_fields']??[]) AS $key => $field) {
				if ( array_key_exists($key, $page['fields']??[]) ){
					//All: hidden field cant be set, reserved for automation
					//Employee app: non-supervisor - only (staff_)client_readonly/editable field when creating
					//Supervisor: all client_, and staff readonly when creating
					if ( empty($field['visibility']) OR $field['visibility'] == 'client_editable' OR 
					   ( str_contains($field['visibility'], 'client_readonly') AND ( empty($page_info['id']) OR $is_supervisor ) ) OR 
					   ( $field['visibility'] == 'readonly' AND empty($page_info['id']) AND $is_supervisor ) OR
					   ( $is_supervisor AND ($field['visibility'] == 'editable' OR $field['visibility'] == 'client_hidden')) 
					){
						if ( $key == 'image' AND !empty($page['fields']['image'][0]) ){
							$page['image'] = $page['fields']['image'][0];
						} elseif (in_array($key, $this->changeable) ){ //override value for default columns, the value wont be stored in meta
							$page[ $key ] = $page['fields'][ $key ];
						} else { 
							//$page['meta'][$app['slug'] .'_'. $key] = $page['fields'][ $key ];
							$page['meta'][ $key ] = $page['fields'][ $key ];
						}
						$changes[ $key ] = [];
					}	
					unset($page['fields'][ $key ]);
				} elseif ( empty($page_info['id']) AND 
					!empty($page['parent__id']) AND 
					!empty($page['parent__type']) AND 
					!empty($page['parent__subtype']) AND 
					$key == strtolower($page['parent__type'] == 'App'? $page['parent__subtype'] : $page['parent__type']) 
				){ //meta field for linking back to parent app, set parent id when creating only
					//$page['meta'][$app['slug'] .'_'. $key] = $page['parent__id'];
					$page['meta'][ $key ] = $page['parent__id'];
				}
			}
			//button keys which  are not included in app_fields, should be processed after app_fields as it filters out hidden/readonly $key, eligible keys in app_fields already added
			foreach ( ($app['app_buttons']??[]) AS $button ){
				if ( !empty($button['name']) AND !empty($page['fields'][ $button['name'] ]) AND (
						str_contains($button['visibility'], 'staff') OR (
							$button['visibility'] == 'creator' AND (
								empty($page_info['creator']) OR //new record
								$page_info['creator'] == $this->user->getId() 
							) 
						)
					)
				){
					if ( in_array($button['name'], $this->changeable) ){
						$page[ $button['name'] ] = $page['fields'][ $button['name'] ]; //set this for automation
					} else {
						$page['meta'][ $button['name'] ] = $page['fields'][ $button['name'] ]; //set this for automation
					}	
				}
			}	
			//unset($page['fields']);
			//let's process app's PRE actions here to work for both normal and page_linking_only, may modify $page value
			if ( !empty($app['app_automation']['pre']) ){
				$this->runAutomation($app['app_automation']['pre'], $app, $page, $page_info, __FUNCTION__);
			}	
			//print_r($page); exit;

			//page linking only often indicated by runAutomation's update to bypass heavy workload
			if ( empty($page['page_linking_only']) ){
				//replace $page by whatever app returns to us
				$response = $this->appProcess($app, 'update', $page); 
				$page = $response['page'];
				//enforce data type/subtype
				$page['type'] = 'App';
				$page['subtype'] = $app['name'];
				//let hooks change $page, hook definition must also use references. 
				$this->runHook('App::'. $app['name'] .'::'. __FUNCTION__, [ &$page, $page_info??[] ]);

				//as app may return different page id, we can only rely on page_info[id] for any operation
				if (empty($page_info['id'])) {
					if ($data = $this->create($page)) { // create a new page	
						$page_info['id'] = $data['id']; //we set value for the referenced id set in runAutomation first 
						$page_info = $data; //now $page_info can be overridden without affecting the reference 
						$status['message'][] = $this->trans(':item added successfully', ['item' => $app['label'] ]);
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans(':item was not added', ['item' => $app['label'] ]);
					}		
				} else {
					$data = $this->prepareData($page);
					//if ( !empty($page['quick']) ){
					//	unset($data['published']); //quick update wont change published status (support for kanban dragging )
					//}
					if ($this->db
						->table($this->table)
						->where('id', $page_info['id'])
						->where('subtype', $page['subtype'])//trust the subtype returned by our appProcess logic
						->update( $data )
					){
						$status['message'][] = $this->trans(':item updated successfully', ['item' => $app['label'] ]);	
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans(':item was not updated', ['item' => $app['label'] ]);
					}	
				}
				//debug loop? echo $this->loop .': '. $app['name']; print_r($page);
				if ( empty($status['result']) AND !empty($page["published"]) AND empty($data['published']) ){
					$status['message'][] = $this->trans("You don't have permissions to :action", ['action' => 'publish']);
				}
			}

			if ( !empty($page_info['id']) ){
				//Page linking
				if ( !empty($app['app_sub']) ){
					if ( !empty($page['sub']) ){
						$page['sub'] = $this->array_remove_by_values($page['sub']);
					}
					//do page unlinking if specified, for supervisor only
					if ( !empty($page['page_linking_only']) AND $page['page_linking_only'] === 'unlink' ){	
						if ( $this->formatAppName($page['subtype']) === $page_info['subtype'] AND !empty($page['sub'])
						){
							foreach ($page['sub'] AS $sub => $subid){
								$sub = $this->formatAppName($sub);
							}
							if (
								$is_supervisor AND 
								$this->db->table($this->site_prefix .'_location')
									->where('app_type', 'subapp')
									->where('app_id', $page_info['id'])
									->where('section', $sub)
									->where('page_id', $subid)
									->delete()
							){
								$status['message'][] = $this->trans(':item unlinked successfully', ['item' => $sub ]);											
							} else {
								$status['result'] = 'error';
								$status['message'][] = $this->trans(':item was not unlinked', ['item' => $sub ]);										
							}	
						} else {
							$status['result'] = 'error';
							$status['message'][] = $this->trans('Invalid inputs for unlinking pages');										
						}	
					} else if ( !empty($page['sub']) ){
						//do page linking (and create subpage if needed), $page, $status passed by ref to get sub_id
						empty($status) && $status = [];
						$this->linkSubApps(__FUNCTION__, $page, $page_info, $status, $app, $is_supervisor);	
					}
				}	
				//set by automation only, public should be set to page_info[public]??[] before running automation 
				if ( !empty($page['_public']) AND $page['_public'] != ($page_info['public']??null) AND (empty($status['result']) OR $status['result'] != 'error') ){
					$this->updatePublic($page_info['id'], $page['_public']);
				}
				//page linking only indicated by runAutomation's update
				if ( empty($page['page_linking_only']) ){
					//versioning
					if (!empty($_master)) {
						$this->addVersion($_master, $page_info['id']);
						$status['result'] = 'warning';
						$status['message'][0] = $this->trans('Direct update not allowed, a new version has been created for you. Please work on this version and request a merge when it is completed');
					} elseif ( !empty($app['app_enable']['clientpublish']) AND (!$is_supervisor OR !$this->user->has($this->requirements['PUBLISH']) OR !array_key_exists('published', $page) ) 
					){
						//publish entry if clientpublish is enabled (not for version), let supervisor unpublish if needed (by specifying published = 0)
						$data['published'] = $this->db
							->table($this->table)
							->where('id', $page_info['id'])
							->where('subtype', $page['subtype'])//trust the subtype returned by our appProcess logic
							->update(['published' => 1]);
					}

					if ($upload_dir = $this->getUploadFolder($page)){
						$page['meta']['upload_dir'] = $upload_dir;
					}
					//update page meta
					if (!empty($page['meta'])) { 
						$this->updateMeta($page_info['id'], $page['meta']);
					}
					//update variants - only for inherited classes using ProductVariant trait
					if (!empty($page['variants']) AND method_exists($this, 'updateVariants')) { 
						$this->updateVariants($page_info['id'], $page['variants']);
					}
					//update page collection
					if (!empty($page['collection_replace'])) {
						//remove existing collection(s) and join specified collection(s)
						$next_actions['Collection::removeCollectionsByPageId'] = [
							"pid" => $page_info['id'],
						];
						$next_actions['Collection::joinExisting'] = [
							"pid" => $page_info['id'], 
							"collection_ids" => $page['collection_replace'], 
							"subtype" => "App::". $page['subtype'],
						];
					}
					//mix-up possible when $somevar = collection-replace but collection = collection_replace + collection but let app decide
					if (!empty($page['collection'])) {
						// Join page to collections of subtype App::Blog
						$next_actions['Collection::join'] = [
							"pid" => $page_info['id'], 
							"collections" => $page['collection'], 
							"subtype" => "App::". $page['subtype'],
						];
					}
					//update page menu
					if (!empty($page['menu_id']) AND $page['menu_id'] != ($page_info['menu_id']??null) ){
						$next_actions['Menu::addItemToMenu'] = [
							"menu_id" => $page['menu_id'], 
							"page_id" => $page_info['id'],
						];
					} 				
					//let's process app's POST actions
					//$page['id'] = $page_info['id']; //set page id first - use page_info instead so we'll know whether page's created or updated						
				} elseif ( empty($page['sub']) ){ //page_linking but no data
					$status['result'] = 'error';
					$status['message'][] = $this->trans("Nothing to update for SubApp while App does not accept your update");
				}					
			}
			$this->db->commit(); //start the db transaction

			if ( empty($status['result']) OR $status['result'] != 'error' ){
				//run post-update automation rules
				if ( !empty($app['app_automation']['post']) ){
					$this->runAutomation($app['app_automation']['post'], $app, $page, $page_info, __FUNCTION__);
				}

				$block['api']['page']['id'] = $page_info['id'];					
				if ( !empty($data['name']) ){
					$block['api']['page']['name'] = json_decode($data['name']??'', true);	
				}	
				if ( !empty($data['published']) ){	
					$block['api']['page']['published'] = $data['published'];
				}						
				$this->view->addBlock('main', $block, $this->class .'::update');
			}

			foreach ( ($changes??[]) AS $key => $v) {
				$from = $page_info[ $key ]??$page_info['meta'][ $key ]??null;
				$to = $data[ $key ]??$page['meta'][ $key ]??null;
				if ( $from != $to ){
					$changes[ $key ] = [
						'from' => $from,
						'to' => $to,
					];
				} else {
					unset($changes[ $key ]);
				}
			}	
			$this->logActivity( implode('. ', $status['message']), $app['name'], $page_info['id']??null, 'Info', $changes??null );
			if ( empty($page['hide_success_message']) ){
				if ( $this->view->html ){				
					$pid = $app['slug'] .'/'. $page_info['id'];
					$links['edit']    = $this->slug('App::action', ['action' => 'edit' ]);
					$links['update']  = $this->slug('App::update') .'?for='. $pid;
					$links['update'] .= !empty($this->config['system']['sgframe'])? '&sgframe=1' : '';
					$links['update'] = $this->getSandboxUrl($links['edit'], $pid, $links['update']);

					$status['message'][ $links['update'] ] = $this->trans('Click here to keep editing');	
					if ( empty($status['result']) OR $status['result'] != 'error' ){
						$status['html']['message_title'] = $this->trans('Information');	
					}
				}	
				!empty($status) && $this->view->setStatus($status);
			}

			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}

			return $page_info['id'];
		}	
	}

	//client without write permission can only update page owned by themselves and do page_linking_only otherwise
	public function clientUpdate($page) {
		if ($this->loop > 100) $this->denyAccess('proceed. Loop detected at '. __FUNCTION__ .' ('. $this->loop .')');
		$this->loop++;

		$app = $this->getAppInfo($page["subtype"]??'');
		if ( !empty($app['app_permissions']['client_write_permission']) ){
			if ( $this->user->has($app['app_permissions']['client_write_permission']) ){
				$user_has_permission = 1;
			} else {	
				$app['app_permissions']['client_write'] = null;
			}	
		}

		//if app is staff_guest, allow page_linking_only to let client link subpage
		if ( 'staff_guest' == ($app['app_users']??null) ){
			$app['app_users'] = 'staff_client_guest';
			$page['page_linking_only'] = 1;
		}

		//we do allow client to create (sub)page if clientarea is enabled and app_visibility = (hidden || client_hidden || client_editable)
		if (!str_contains($app['app_users']??'', '_client') OR 
			!$this->user->has($this->requirements['CUSTOMER']) OR 
			empty($app['app_permissions']['client_write'])
		){
			$this->denyAccess('update');
		} elseif ( ! $app ) {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('No such :item', ['item' => 'App']);
			$this->view->setStatus($status);
		} elseif ( !empty($_COOKIE) AND 
			$this->getAppConfigValues('App\\'. $app['name'], null, '_captcha') AND 
			!$this->verifyCaptcha(substr($this->config['salt'], 5, 15), $_POST['altcha']??'') 
		){
			$status['result'] = 'error';
			$status['message'][] = $this->trans('Invalid or expired captcha');
			$this->view->setStatus($status);
		} else {
			if ( !empty($page['tick']) ){ //pre-process just like they come from frontend
				parse_str(urldecode($page['tick']), $page['tick']);
				if ( !empty($page['tick']['page']) AND is_array($page['tick']['page']) ){
					foreach ($page['tick']['page'] AS $k => $v){
						if ( in_array($k, $this->changeable) AND !array_key_exists($k, $page['fields']??[]) ){ 
							$page[ $k ] = $v; //could be override by $page['fields'][ $k ] if exists
						} else { 
							$page['fields'][ $k ] = $v; //processed later
						}
					}
				}
			}	
			//update comes from sandbox page, make sure the id isnt arbitrary
			//update from public using cors generated by App:render, page_id is prepend to $_POST['csrf_token']
			if ( isset($_GET['for']) OR isset($_GET['cors']) ){
				if ( $this->config['system']['csrf'] AND 
					$_POST['csrf_token'] != $_SESSION['token'] AND (
						( isset($_GET['for']) AND $_GET['for'] != trim(strtolower($page['subtype']) .'/'. ($page['id']??''), '/') ) OR 
						( isset($_GET['cors']) AND !str_starts_with($_POST['csrf_token'], $page['id'] .'sg') )
					)	
				){
					$this->denyAccess('update this record');
				}
				unset($_GET['for'], $_GET['cors']); //run once only 
			}	

			$this->db->beginTransaction(); //start the db transaction
			if ( !empty($page['id']) ){ 
				$page_info = $this->read($page['id'], 'App', $app['name'], NULL, 'for_update' );
				$page_info['meta'] = $this->readMeta($page['id']); //for automation to get stored input
				$for_me = 'U'. $this->user->getId() .'::w'; //write permission needed
				if ($page_info AND 
					$page_info['creator'] != $this->user->getId() AND 
					!str_contains($page_info['private'], $for_me) AND 
					empty($user_has_permission)
				){
					$page['page_linking_only'] = 1; 
				}
			}	

			//Published page can't be updated (unless clientpublish is enable) but page_linking is allowed 
			if ( !empty($page_info["published"]) AND 
				empty($app['app_enable']['clientpublish']) AND 
				empty($page['page_linking_only'])
			){ 
				if ( !empty($page['sub']) ){ //allow page_linking_only
					$page['page_linking_only'] = 1;
				} elseif ( !empty($app['app_enable']['versioning']) ){
					$this->logActivity('Update Request Denied. Creating a new one', $this->class, $page_info['id'], 'Warning' );
					//$page['slug'] .= "-copy";
					if ( !empty($page['name'][ $this->config['site']['language'] ]) AND !str_ends_with($page['name'][ $this->config['site']['language'] ], ' (Cloned)') ){
						$page['name'][ $this->config['site']['language'] ] .= " (Cloned)";
					}
					$_master = $page_info['id']; //revision
					unset($page['id'], $page['published'], $page_info['id'], $page_info['published']); //force creating a new page
				} else {
					$this->denyAccess('update a published entry');
				}	
			}
			//handle once at the first run to correctly map attachments to fields 
			//handle files upload - file will be prefixed with folder name e.g: 213 = 21Q3 so we can store the file name only
			if ( !empty($_FILES) ){
				//set warning handler to hide base_dir
				set_error_handler(function ($errno, $errstr) {
					throw new \Exception($errstr, $errno);	
				}, E_WARNING);
				if ( str_contains($app['app_users'], 'guest') ){
					$this->prepareUploads($_FILES, $page, null, 'public'); //store in public folder
				} else {
					$this->prepareUploads($_FILES, $page);
				}
				unset($_FILES); //prevent sub process to run this again
				restore_error_handler();
			}
			//prevent data tamper
			$page['_public'] = $page_info['public']??[]; //can be written by anyone for public use purpose (count, average)
			unset($page['$']);
			$page['meta'] = [];
			//change only app's fields to meta and prefix app_ to key, other page fields are left untouched
			//accept client_editable (and (staff_)client_readonly field when creating)
			foreach ( ($app['app_fields']??[]) AS $key => $field) {
				if ( array_key_exists($key, $page['fields']??[]) ) {
					if ( empty($field['visibility']) OR $field['visibility'] == 'client_editable' OR (str_contains($field['visibility'], 'client_readonly') AND empty($page_info['id'])) ){
						if ( $key == 'image' AND !empty($page['fields']['image'][0]) ){
							$page['image'] = $page['fields']['image'][0];
						} elseif ( in_array($key, $this->changeable) ){ //override value for default columns
							$page[ $key ] = $page['fields'][ $key ];
						} else { 
							//$page['meta'][$app['slug'] .'_'. $key] = $page['fields'][ $key ];
							$page['meta'][ $key ] = $page['fields'][ $key ];
						}
						$changes[ $key ] = [];
					}	
					unset($page['fields'][ $key ]);
				} elseif ( empty($page_info['id']) AND !empty($page['parent__id']) AND !empty($page['parent__type']) AND !empty($page['parent__subtype']) AND $key == strtolower($page['parent__type'] == 'App'? $page['parent__subtype'] : $page['parent__type']) ){ //meta field for linking back to parent app, set parent id when creating
					//$page['meta'][$app['slug'] .'_'. $key] = $page['parent__id'];
					$page['meta'][ $key ] = $page['parent__id'];
				}
			}
			//button keys which  are not included in app_fields, should be processed after app_fields as it filters out hidden/readonly $key, eligible keys in app_fields already added
			foreach ( ($app['app_buttons']??[]) AS $button ){
				if ( !empty($button['name']) AND !empty($page['fields'][ $button['name'] ]) AND (
						str_contains($button['visibility'], 'client') OR (
							$button['visibility'] == 'creator' AND (
								empty($page_info['creator']) OR //new record
								$page_info['creator'] == $this->user->getId() 
							) 
						)
					)	
				){
					if ( in_array($button['name'], $this->changeable) ){
						$page[ $button['name'] ] = $page['fields'][ $button['name'] ]; //set this for automation
					} else {
						$page['meta'][ $button['name'] ] = $page['fields'][ $button['name'] ]; //set this for automation
					}	
				}
			}
			//unset($page['fields']);
			//let's process app's PRE actions, may modify $page value
			if ( !empty($app['app_automation']['pre']) ){
				$this->runAutomation($app['app_automation']['pre'], $app, $page, $page_info, __FUNCTION__);
			}

			//print_r($page); //exit;
			//page linking only often indicated by runAutomation's update to bypass heavy workload
			if ( empty($page['page_linking_only']) ){
				//replace $page by whatever app returns to us
				$response = $this->appProcess($app, 'update', $page); //should be clientUpdate
				$page = $response['page'];
				//enforce data type/subtype
				$page['type'] = 'App';
				$page['subtype'] = $app['name'];

				//let hooks change $page, hook definition must also use references i.e &$page
				$this->runHook('App::'. $app['name'] .'::'. __FUNCTION__, [ &$page, $page_info??[] ]);

				//as app may return different page id, we can only rely on page_info[id] for any operation
				if (empty($page_info['id'])) {
					if ($data = $this->create($page)) { // create a new page	
						$page_info['id'] = $data['id']; //we set value for the referenced id set in runAutomation first 
						$page_info = $data;
						$status['message'][] = $this->trans(':item added successfully', ['item' => $app['label'] ]);						
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans(':item was not added', ['item' => $app['label'] ]);					
					}		
				} else {
					if ($this->db
						->table($this->table)
						->where('id', $page_info['id'])
						->where('subtype', $page['subtype'])//trust the subtype returned by our appProcess logic
						->update( $this->prepareData($page) )
					){
						$status['message'][] = $this->trans(':item updated successfully', ['item' => $app['label'] ]);		
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans(':item was not updated', ['item' => $app['label'] ]);										
					}	
				}
			}

			if (!empty($page_info['id'])) {
				//do page linking (and create subpage if needed) - unlink is not needed for clientView			
				if ( !empty($app['app_sub']) AND !empty($page['sub'] = $this->array_remove_by_values($page['sub']??[])) ){
					empty($status) && $status = []; 
					$this->linkSubApps(__FUNCTION__, $page, $page_info, $status, $app);	
				}

				//set by automation only, _public should be set to page_info[public]??[] before running automation 
				if ( !empty($page['_public']) AND $page['_public'] != ($page_info['public']??null) AND (empty($status['result']) OR $status['result'] != 'error') ){
					$this->updatePublic($page_info['id'], $page['_public']);
				}

				if ( empty($page['page_linking_only']) ){
					//publish entry if clientpublish is enabled and published is not disable by Automated Action
					if (!empty($_master)) {
						$this->addVersion($_master, $page_info['id']);
						$status['result'] = 'warning';
						$status['message'][0] = $this->trans('Direct update not allowed, a new version has been created for you. Please work on this version and request a merge when it is completed');
					} elseif ( !empty($app['app_enable']['clientpublish']) AND (empty($page['updated']) OR $page['updated'] != 'unpublish') ){
						$data['published'] = $this->db
							->table($this->table)
							->where('id', $page_info['id'])
							->where('subtype', $page['subtype'])//trust the subtype returned by our appProcess logic
							->update(['published' => 1]);
						//update page collection when clientpublish only
						if (!empty($page['collection_replace'])) {
							//remove existing collection(s) and join specified collection(s)
							$next_actions['Collection::removeCollectionsByPageId'] = [
								"pid" => $page_info['id'],
							];
							$next_actions['Collection::joinExisting'] = [
								"pid" => $page_info['id'], 
								"collection_ids" => $page['collection_replace'], 
								"subtype" => "App::". $page['subtype'],
							];
						} 
						if (!empty($page['collection'])) {
							//join existing collections only
							$next_actions['Collection::joinExisting'] = [
								"pid" => $page_info['id'], 
								"collection_ids" => $page['collection'], 
								"subtype" => "App::". $page['subtype'],
							];
						}
					}
					//update page meta
					if ( !empty($page['meta']) ){ 
						$this->updateMeta($page_info['id'], $page['meta']);
					}
					//update variants - only for inherited classes using ProductVariant trait
					if ( !empty($page['variants']) AND method_exists($this, 'updateVariants') ){ 
						$this->updateVariants($page_info['id'], $page['variants']);
					}	
				} elseif ( empty($page['sub']) ){ //page_linking but no data
					$status['result'] = 'error';
					$status['message'][] = $this->trans('Nothing to update for SubApp while App does not accept your update');
				}
			}
			$this->db->commit();

			if ( empty($status['result']) OR $status['result'] != 'error' ){
				//run post-update automation rules
				if ( !empty($app['app_automation']['post']) ){
					$this->runAutomation($app['app_automation']['post'], $app, $page, $page_info, __FUNCTION__);
				}
			}	
			
			foreach ( ($changes??[]) AS $key => $c) {
				$from = $page_info[ $key ]??$page_info['meta'][ $key ]??null;
				$to = $data[ $key ]??$page['meta'][ $key ]??null;
				if ( $from != $to ){
					$changes[ $key ] = [
						'from' => $from,
						'to' => $to,
					];
				} else {
					unset($changes[ $key ]);
				}
			}	
			$this->logActivity( implode('. ', $status['message']), $app['name'], $page_info['id']??null, 'Info', $changes??null );
			if ( empty($page['hide_success_message']) ){
				if ( $this->view->html ){							
					$pid = $app['slug'] .'/'. $page_info['id'];
					$links['edit']    = $this->slug('App::clientView');
					$links['update']  = $this->slug('App::clientUpdate') .'?for='. $pid;;
					$links['update'] .= !empty($this->config['system']['sgframe'])? '&sgframe=1' : '';
					$links['update'] = $this->getSandboxUrl($links['edit'], $pid, $links['update']);
					$status['message'][ $links['update'] ] = $this->trans('Click here to keep editing');	

					if ( empty($status['result']) OR $status['result'] != 'error' ){
						$status['html']['message_title'] = $this->trans('Information');	
					}
				}
				!empty($status) && $this->view->setStatus($status);				
			}
			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}
		}
		return $page_info['id']??false;	
	}
	//id can be numeric or docs/1234
	public function copy($id, $backup = false) {
		if ($backup){
			//read raw data to insert it back
			$page = $this->db
				->table($this->table)
				->where('id', intval($id) )
				->where('type', 'App')
				->first();
			$subtype = $page['subtype']??'';	
		} elseif (strpos($id, '/')) { //app/111
			$subtype = strtok($id, '/');
			$id = strtok('/');
			if (is_numeric($id)) {
				$page = $this->read($id, 'App', !empty($subtype)? $this->formatAppName($subtype) : null );					
			}
		}		

		$app = $this->getAppInfo($subtype); 
		if ( !empty($app['app_permissions']['staff_manage']) AND (
				empty($app['app_permissions']['staff_manage_permission']) OR 
				$this->user->has($app['app_permissions']['staff_manage_permission'])
			)
		){
			$is_supervisor = 1;
		} else {
			$is_supervisor = 0;
		}
		$for_me = 'S'. $this->user->getId() .'::'; 

		if ((   !$this->user->has($this->requirements['CREATE']) OR //no write permission
				empty($app['app_permissions']['staff_write']) OR ( 
					!empty($app['app_permissions']['staff_write_permission']) AND 
					!$this->user->has($app['app_permissions']['staff_write_permission']) 
				)
			) OR ( 
				!empty($page['id']) AND ( //no read permission
					(
			    		empty($app['app_permissions']['staff_read']) OR (
			    			!empty($app['app_permissions']['staff_read_permission']) AND 
			    			!$this->user->has($app['app_permissions']['staff_read_permission']) 
			    		)
			    	) OR ( 
						$page['creator'] != $this->user->getId() AND 
						!str_contains($page['private'], $for_me) AND ( 
							!$is_supervisor OR ( 
								strpos($page['private'], '::') AND 
								!$this->user->has($page['private']) 
							) 
						)
		    		)
		    	)
		    )		
		){//Site + Page level permission (entry creator is granted view/edit access)
			$this->denyAccess('copy', (empty($app['app_permissions']['staff_read']) OR empty($app['app_permissions']['staff_write']))? '' :
				$app['app_permissions']['staff_write_permission']??
				$app['app_permissions']['staff_read_permission']??
				$this->requirements['CREATE']
			);
		} elseif ($page){
			if ($backup){
				$page_info = $page;
				$page_info['slug'] .= "-backup-". time();
				if ( $page_info['name'] = json_decode($page_info['name']??'', true) ){
					if ( !str_ends_with($page['name'][ $this->config['site']['language'] ]??'', ' (Backup)') ){
						$page_info['name'][ $this->config['site']['language'] ] .= " (Backup)";
					}
				}	
				$page_info['name'] = json_encode($page_info['name']);

				unset($page_info['id'], $page_info['published']); //for insertGetId
				$page_info['id'] = $this->db
					->table($this->table)
					->insertGetId($page_info);
			} else {
				$page['slug'] && $page['slug'] .= "-copy";
				if ( !str_ends_with($page['name'][ $this->config['site']['language'] ]??'', ' (Copied)') ){
					$page['name'][ $this->config['site']['language'] ] .= " (Copied)";
				}
				$page_info = $this->create($page);
			}	
			if ( !empty($page_info['id']) ) {
				$status['message'][] = $backup? $this->trans(':item backed up successfully', ['item' => 'Record']) : $this->trans(':item copied successfully', ['item' => 'Record']);
				//clone page's collection, widget, menu items
				$this->cloneRows( $this->site_prefix .'_location', ['collection', 'widget', 'menu'], 'app_type, app_id, location, section, sort', 'page_id', $page['id'], $page_info['id'] );
				//clone page meta
				$this->cloneRows( $this->site_prefix .'_pagemeta', null, 'property, value, name, description, `order`', 'page_id', $page['id'], $page_info['id'] );
				//clone page's subpage items - app_id here
				//$this->cloneRows( $this->site_prefix .'_location', 'subapp', 'app_type, page_id, location, section, sort', 'app_id', $page['id'], $page_info['id'] );
				//only for inherited classes using ProductVariant trait
				if ( method_exists($this, 'copyVariants') ){
					$this->copyVariants($page['id'], $page_info['id']);
				}
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not copied', ['item' => 'Page']);					
			}
		} else {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('Original :item cannot be read', ['item' => 'Page']);						
		}

		!empty($status) && $this->view->setStatus($status);							
		$this->logActivity( implode('. ', $status['message']), $app['name'], $page_info['id']??null);
		if ($this->view->html AND !empty($page_info['id']) AND !$backup ) {				
			$this->edit($app['slug'] .'/'. $page_info['id']);
		}

		return empty($page_info['id'])? null : $page_info; //return whole $page only if $page['id'] exist 					
	}	

	//From sandbox page: GET loads this action in the main page for deleting/unlinking through POST request (required valid CSRF token)
	public function manage($id) {
		if($this->view->html){				
			$params = explode('/', trim($id, '/') );

			if ( !empty($params[2]) AND !empty($params[3]) AND is_numeric($params[3]) ) {
				$params[0] = urldecode($params[0]); //may contain emoji
				$params[2] = urldecode($params[2]);
				$app = $this->getAppInfo( $params[2] );
				if ( !empty($app['app_permissions']['staff_manage']) AND (
						empty($app['app_permissions']['staff_manage_permission']) OR 
						$this->user->has($app['app_permissions']['staff_manage_permission'])
					)
				){
					$links['delete'] = $this->slug('App::delete', ["app" => strtolower($params[2]) ] );
					$links['delete'] .= !empty($this->config['system']['sgframe'])? '?sgframe=1' : '';	
					$links['update'] = $this->slug('App::update');				
					$links['update'] .= !empty($this->config['system']['sgframe'])? '?sgframe=1' : '';
					$block['links'] = $links;	
				}	
				$block['api']['subtype'] = $params[0];	
				$block['api']['id'] = $params[1];	
				$block['api']['sub']['subtype'] = $params[2];	
				$block['api']['sub']['id'] = $params[3];	
				if ( isset($_GET['published']) ){
					$block['api']['sub']['published'] = intval($_GET['published'])? : -1;
				}				
				$this->view->setLayout('iframe'); 
				$this->view->addBlock('main', $block, $this->class .'::'. $block['api']['subtype'] .'::manage');	
			}
		}		
	}
		
	public function delete($id) {
		$page = $this->read($id, 'App');
		if ($page) {
			$app = $this->getAppInfo($page["subtype"]); 	
			if ( ! $this->user->has($this->requirements['CREATE']) OR 
			   empty($app['app_permissions']['staff_manage']) OR
			   ( ! empty($app['app_permissions']['staff_manage_permission']) AND !$this->user->has($app['app_permissions']['staff_manage_permission']) ) 
			   //OR( ! empty($app['app_permissions']['page_read_permission']) AND strpos($page['private'], '::') AND !$this->user->has($page['private'])) //employee app - creator may not have delete access
			){
				$this->denyAccess('delete', empty($app['app_permissions']['staff_manage'])? '' :
				$app['app_permissions']['staff_manage_permission']??
				$this->requirements['CREATE']);
			} 
	
			//Published page cannot be deleted by unauthorized staff		
			if ( $page["published"] > 0 AND ! $this->user->has($this->requirements['PUBLISH']) ){
				$status['result'] = 'error';
				$status['message'][] = $this->trans("You don't have permissions to :action", ['action' => 'delete a published item']);
			} elseif ($app) {
				if ( 
					$this->db->table($this->table)
						->where('subtype', $page["subtype"])
						->delete($page['id']) 
				){
					$this->deleteMeta($page['id']);//delete meta
					$this->deleteVersions($page['id']);
					if ( !empty($app['app_sub']) AND !empty($_POST['subapps']) ) { //only delete subpages when requested
						foreach ( $app['app_sub'] AS $subapp => $value ){
							$subapp = $this->getAppInfo($subapp); 
							if ( empty($subapp['app_permissions']['staff_manage']) OR (
									!empty($subapp['app_permissions']['staff_manage_permission']) AND 
									!$this->user->has($subapp['app_permissions']['staff_manage_permission']) 
								)
							){
								$this->denyAccess('delete subpages', empty($subapp['app_permissions']['staff_manage'])? '' : $subapp['app_permissions']['staff_manage_permission']??null);
							} 
							//get sub ids
							$subids = $this->db->table($this->site_prefix .'_location')
								->where('app_type', 'subapp')
								->where('app_id', $id)
								->where('section', $subapp['name'])
								->pluck('page_id')
								->all();

							//delete subpages	
							$query = $this->db->table($this->table)
								->where('subtype', $subapp['name'] )
								->whereIn('id', $subids);

							//page level permission
							if (!empty($subapp['app_permissions']['page_read']) AND 
								!empty($subapp['app_permissions']['page_read_permission']) AND !$this->user->has($this->requirements['SystemAdmin']) 
							){
								$permissions = array_keys($this->user->getPermissions());
								$myself = $this->user->getId();
								$query->where(function ($query) use ($permissions, $myself) {
									$query->whereIn('private', $permissions)
									->orWhere('private', 0)
									->orWhereNull('private')
									->orWhere('creator', $myself); 
								});
							}
							$query->delete();
						}	
					}
					//Finally, delete relation whether this page is the main page or a subpage of other
					$this->db->table($this->site_prefix .'_location')
						->where('app_type', 'subapp')
						->where(function ($query) use ($id) {
							$query->where('app_id', $id)
						  	->orWhere('page_id', $id);
						})
						->delete();
					$this->runHook('App::'. $app['name'] .'::deleted', [ $page ]);
	
					$status['message'][] = $this->trans(':item deleted successfully', ['item' => 'Record']);				
					$next_actions['Collection::removeCollectionsByPageId'] = ["pid" => $page['id']];
					$this->logActivity( implode('. ', $status['message']), $app['name'], $page['id']??null);
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not deleted', ['item' => 'Record']);				
				}

				if ($this->view->html){	
					$next_actions['App::main'] = [ $app['name'] ];
					$status['html']['title'] = $this->trans('Delete :item', ['item' => $app['label'] ]);
				}
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('No such :item', ['item' => 'Record']);
			}
		} else {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('No such :item', ['item' => 'Record']);
		}	
		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);	

		if (!empty($next_actions)){
			$this->router->registerQueue($next_actions);	
		}									
	}
	//client can delete their own records only, even with delete permission
	public function clientDelete($id) {
		$page = $this->read($id, 'App');
		if ($page) {
			$app = $this->getAppInfo($page["subtype"]); 
			if (!empty($app['app_permissions']['client_delete_permission']) ){
				if ( $this->user->has($app['app_permissions']['client_delete_permission']) ){
					$user_has_permission = 1;
				} else {	
					$app['app_permissions']['client_delete'] = 0;
				}	
			}
			if ( empty($app['app_permissions']['client_delete']) OR
				( $page['creator'] != $this->user->getId() AND empty($user_has_permission) )
			){
				$this->denyAccess('delete');
			} 
	
			if ( !empty($page['published']) ){ //client may not delete published item
				$this->denyAccess('delete a published item');
			} elseif ($app) {
				if ( 
					$this->db->table($this->table)
						->where('subtype', $page["subtype"])
						->when(empty($user_has_permission), function($query){
							return $query->where('creator', $this->user->getId() );
						})
						->delete($page['id']) 
				){
					$this->deleteMeta($page['id']);//delete meta
					$this->deleteVersions($page['id']);
					if ( !empty($app['app_sub']) AND !empty($_POST['subapps']) ) { //only delete subpages when requested
						foreach ( $app['app_sub'] AS $subapp => $value ){
							$subapp = $this->getAppInfo($subapp); 
							if ( empty($subapp['app_permissions']['staff_manage']) OR (
									!empty($subapp['app_permissions']['staff_manage_permission']) AND 
									!$this->user->has($subapp['app_permissions']['staff_manage_permission']) 
								)
							){
								$this->denyAccess('delete subpages', empty($subapp['app_permissions']['staff_manage'])? '' : $subapp['app_permissions']['staff_manage_permission']??null);
							} 
							//get sub ids
							$subids = $this->db->table($this->site_prefix .'_location')
								->where('app_type', 'subapp')
								->where('app_id', $id)
								->where('section', $subapp['name'])
								->pluck('page_id')
								->all();

							//delete subpages	
							$query = $this->db->table($this->table)
								->where('subtype', $subapp['name'] )
								->whereIn('id', $subids);

							$query->delete();
						}	
					}
					//Finally, delete relation whether this page is the main page or a subpage of other
					$this->db->table($this->site_prefix .'_location')
						->where('app_type', 'subapp')
						->where(function ($query) use ($id) {
							$query->where('app_id', $id)
						  	->orWhere('page_id', $id);
						})
						->delete();
					$this->runHook('App::'. $app['name'] .'::clientDeleted', [ $page ]);
	
					$status['message'][] = $this->trans(':item deleted successfully', ['item' => 'Record']);				
					$next_actions['Collection::removeCollectionsByPageId'] = ["pid" => $page['id']];
					$this->logActivity( implode('. ', $status['message']), $app['name'], $page['id']??null);
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not deleted', ['item' => 'Record']);				
				}

				if ($this->view->html){	
					$next_actions['App::clientMain'] = [ $app['name'] ];
					$status['html']['title'] = 'Delete '. $app['label'];
				}
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('No such :item', ['item' => 'Record']);
			}
		} else {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('No such :item', ['item' => 'Record']);
		}	
		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);	

		if (!empty($next_actions)){
			$this->router->registerQueue($next_actions);	
		}									
	}

	public function render($full_path_without_query = '') { //This method used for unauthenticated users, be careful
		//$full_path_without_query = app_name::slug (modified by router to include app name) or app_name (without slug) 
		//when it is the index page which is handled by Page::render first and usually not found and passed to App::render
		//echo $full_path_without_query;
		$full_path_without_query = explode('::', trim($full_path_without_query, '/')); //modified by router - blog::slug
		$app = $this->getAppInfo($full_path_without_query[0]); 
		if (!empty($app['app_permissions']['client_read_permission']) AND 
			!$this->user->has($app['app_permissions']['client_read_permission'])
		){
			$app['app_permissions']['client_read'] = null;
		}

		if ( ! $app ) {
			return false;
		} elseif ( !str_contains($app['app_users']??'', '_guest') OR empty($app['app_permissions']['client_read']) ){ //client_read must be enabled for guest read
			$this->denyAccess('view');
		} else {
			$slug = $full_path_without_query[1]??null;
			if (empty($slug) OR $slug == trim(parse_url($this->config['site']['url'], PHP_URL_PATH), '/')) {
				$slug = "index.html";
			}

			$page = $this->readSlug($slug, 'App', $app['name']);	
			if (empty($page)) { //no such slug; return false and let the main controller handle 404 
				if ( $slug == 'index.html' ){ //no index.html defined, list all records
					return $this->renderIndex($app);
				} else {
					return false; 
				}
			} elseif ( ! $this->canView($page) ){	
				return false;
			}
			//normal app render
			if ( empty($_REQUEST['subapp']) ){
				$response = $this->appProcess($app, 'render', $page);
				$this->runHook('App::'. $app['name'] .'::'. __FUNCTION__, [ &$response['blocks'] ]);
				foreach ( ($response['blocks']??[]) AS $section => $block) {
					if ($section == 'main'){
						//combine stored app_fields with response app.fields, before adding meta value	
						$fields = ($app['app_fields']??[]) + ($block['api']['app']['fields']??[]);
					  	unset($block['api']['app']['fields']); //block fields should be appended
						//get public profile
						if ( !empty($block['api']['page']['creator']) ){
				    		$block['api']['profile'] = $this->getProfile($block['api']['page']['creator']);
							$block['api']['parent'] = $this->getParentPage($page, $fields);
				    	}	
						//remove non client_ fields
						foreach ( $fields AS $key => $field) {
							if ( empty($field['visibility']) ){
								//treat it as client_editable
							} elseif ( !str_contains($field['visibility'], 'client_') OR $field['visibility'] == 'client_hidden' ){
								unset($block['api']['page']['meta'][ $key ], $block['api']['app']['fields'][ $key ], $block['api']['page']['meta'][ $app['slug'] .'_'. $key]);
								continue;
							} 
							$meta_key = $app['slug'] .'_'. $key;
							//special case: lookup using other input, should NOT move inside formatFieldValue
							if ( $field['type'] == 'select' AND (!empty($field['options']['From::input']) OR !empty($field['options']['From::lookup'])) ){
								$lookup = array_values($field['options']);
								$field['lookup'] = $lookup[1]??null;
								if ( !empty($lookup[3]) ){
									$field['listen'] = $lookup[3];
									$lookup = $field['listen']; //shorten
						    		//this value from another input should already gone thru formatFieldValue
					    			$field['lookup-value'] = in_array($lookup, $this->changeable)? 
										($block['api']['page'][ $lookup ]??null) : 
										($block['api']['app']['fields'][ $lookup ]['value']??null); 
									if (is_array($field['lookup-value'])){
										$field['lookup-value'] = array_keys($field['lookup-value'])[0];
									}
									if ( !empty($field['options']['Scope::SubRecords']) ){
										$field['scope'] = 'SubRecords';
									}	
					    		}	
							}
							//process value before option so option can see field['value']
			    			if ( in_array($key, $this->changeable) OR 
			    				array_key_exists($key, $block['api']['page']??[]) 
			    			){ //changeable plus read-only columns
								if ( in_array($key, ['name', 'title', 'description', 'content']) ){
						    		$this->formatFieldValue($block['api']['page'][ $key ][ $this->config['site']['language'] ]??$block['api']['page'][ $key ]??null, $key, $field, $app, 'for_guest');
						    	} else {
						    		$this->formatFieldValue($block['api']['page'][ $key ]??null, $key, $field, $app, 'for_guest');
						    	}
						    	if ( !empty($field['_value']) ){ //returned info
			    					$block['api']['page'][ $key ] = $field['_value'];
			    				}			    				
				    		} elseif ( array_key_exists($key, $block['api']['page']['meta']??[]) OR 
				    			array_key_exists($meta_key, $block['api']['page']['meta']??[]) OR 
				    			!empty($field['value']) 
				    		){
			    				$this->formatFieldValue($block['api']['page']['meta'][ $key ]??$block['api']['page']['meta'][ $meta_key ]??null, $key, $field, $app, 'for_guest');
			    				if ( !empty($field['_value']) ){
			    					$block['api']['page']['meta'][ $key ] = $block['api']['page']['meta'][ $meta_key ] = $field['_value'];
			    				} 
				    		} elseif ( !empty($field['type']) AND $field['type'] == 'fieldset') { //format scope in value
						    	$this->formatFieldValue(null, $key, $field, $app, 'for_guest');
						   	} else {
				    			continue;
				    		}
				    		//guest do not have edit mode so no need to format options
							//$this->formatFieldOptions($key, $field, $app);
							unset($field['options'], $field['_value']);
							$block['api']['app']['fields'][ $key ] = $field;		
						}
						//get user name if not yet processed
						if ( !is_array($block['api']['page']['creator']) ){
							$creator = $this->lookupById('user', $block['api']['page']['creator'])??$this->lookupById('staff', $block['api']['page']['creator']); 
							$block['api']['page']['creator_name'] = $creator['rows'][ $block['api']['page']['creator'] ]??'';
							$block['api']['page']['creator_avatar'] = $creator['images'][ $block['api']['page']['creator'] ]??null;
							$block['api']['page']['creator_label'] = $this->trans($app['app_columns']['creator']??'Author');
						}
						foreach ( $app['app_buttons']??[] AS $btn ){
							if ( str_contains($btn['visibility'], 'client') OR (
									$btn['visibility'] == 'creator' AND (
										empty($page['creator']) OR $page['creator'] == $this->user->getId()
									)
								)
							){
								$block['api']['app']['buttons'][] = $btn;
							}
						}
						//subapp
						if ( !empty($app['app_sub']) ){
							$block['api']['app']['sub'] = $app['app_sub'];
						}

						if($this->view->html){				
							$block['html']['current_app'] = $app['name'];
							$block['html']['app_label'] = $this->trans($app['label']);
							$block['html']['app_label_plural'] = $this->trans($this->pluralize($app['label']));
							if ( !empty($app['app_sub']) AND !$this->user->getId() AND !empty($page['id']) ){
								$block['html']['sso_requester'] = $this->encode('https://'. $this->config['site']['url'] . $page['slug']);
							}
							if ( !empty($app['app_templates']['page']) ) {
								$block['template']['file'] = $app['app_templates']['page'];//"page";
							} elseif ( !empty($app['app_templates']['page_string']) ) { //not really neccessary
								$block['template']['string'] = base64_encode($app['app_templates']['page_string']);
							} 
							if ( !empty($block['template']['file']) AND is_file($this->path . $app['slug'] . ($app['id']??'') .'/'. $block['template']['file'] .'.tpl') 
							){
								$block['template']['directory'] = 'resources/public/templates/app/'. $app['slug'] . ($app['id']??''); 
							} elseif ( empty($block['template']['file']) ){
								$block['template']['file'] = "app_page";
							}
							if ( !empty($_COOKIE) AND $this->getAppConfigValues('App\\'. $app['name'], null, '_captcha') ) {
								$block['html']['challenge_captcha'] = $this->createCaptcha(substr($this->config['salt'], 5, 15)); 
							}
							//App may have a default layout, then each page may have its own layout
							if ($layout = $page['layout']??$app['app_layouts']['page']??$block['template']['layout']??null) {//=1 but set $layout
								$this->view->setLayout($layout); //unlike edit, layout could be stored in app folder
							}							
						}
					}

					$this->view->addBlock($section, $block, $page['type'] .'::'. $app['name'] .'::render');		
				}
				$next_actions['Collection::getCollectionsByPageId'] = [ $page['id'], "slug" => 1 ];
				$next_actions['Collection::getRelatedItems'] 		= [ $page['id'], "slug" => 1 ];
			}
			//subapp pages
			if ( !empty($app['app_sub']) ){
				$this->renderSubApps($page??null, $app);
			}
	
			!empty($status) && $this->view->setStatus($status);
			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}
				
			return $page;
		}
	}

	protected function renderIndex($app = '', $template = null) {
		if (!empty($app)) {//display records for given app
			$this->checkActivation($app['class']);
			if (!empty($app['app_permissions']['client_read_permission']) AND 
				!$this->user->has($app['app_permissions']['client_read_permission'])
			){
				$app['app_permissions']['client_read'] = null;
			}

			if ( !str_contains($app['app_users']??'', '_guest') OR empty($app['app_permissions']['client_read']) ){
				$this->denyAccess('list');
			} else {		
				//remove non client_ fields before processing
				foreach ( ($app['app_fields']??[]) AS $key => $field) {
					if ( empty($field['visibility']) OR $key == 'creator'){ //allow creator in index
						//treat it as client_editable
					} elseif ( !str_contains($field['visibility'], 'client_') OR $field['visibility'] == 'client_hidden' ){
						unset($app['app_columns'][ $app['slug'] .'_'. $key ], $app['app_columns'][ $key ]); 
					}
				}

				$query = $this->db
					->table($this->table .' AS page')
					->where('page.type', 'App')
					->where('page.subtype', $app['name'])
					->select('page.id', 'page.type', 'page.subtype', 'page.slug', 'page.name', 'page.title', 'page.description', 'page.updated', 'page.image', 'page.public')
					->orderBy('updated', 'desc');	
				$query = $this->getPublished($query);
				//include creators anyway
				empty($app['app_columns']['creator']) && ($app['app_columns']['creator'] = $this->trans('User'));
				//$show_creator = $this->showCreator($app, $query, ['creator', 'user.name AS creator_name']); //this may change $app and $query if $show_creator 

				//provide default sorting since automatic sorting has problem with unionAll
				if ( empty($_REQUEST['sort']) ){
					$_REQUEST['sort']['id'] = 'desc'; 
				}
				//if template specified, load records
				if ( empty($_REQUEST['current']) AND !empty($app['app_templates']['collection']) ){
				 	$_REQUEST['current'] = 1; //force non-ajax mode 
				}
				if ( !empty($_REQUEST['rowCount']) AND $_REQUEST['rowCount'] == 12){
					$_REQUEST['rowCount'] = 48; 
				}	
				$block = $this->prepareMain($query, $app, null);

				$response = $this->appProcess($app, 'renderIndex', $block);
				$this->runHook('App::'. $app['name'] .'::'. __FUNCTION__, [ &$response['blocks'] ]);
				foreach ( ($response['blocks']??[]) AS $section => $block) {
					if ($section == 'main'){					
						if ($page = $this->readSlug('/'. $app['name'], 'Link') ){//if Link to this page exists
							if ( ! $this->canView($page) ){	
								return false;
							}
							$block['api']['page'] = $page; 
						}						
						if ( $block['api']['total'] ){	
							//count subapp's entries
							if ( !empty($block['api']['rows']) AND !empty($app['app_sub']) ){
								$block['api']['subapp'] = $this->countSubPages($app, array_column($block['api']['rows'], 'id') );	
							}

							if ($this->view->html){				
								$block['links']['api'] = rtrim($this->slug('App::render', ['app' => $app['slug'], 'slug' => ''] ), '/');
								$block['links']['edit'] = $block['links']['api'];
								$block['links']['datatable']['creator'] = $this->slug('Profile::render');
								$block['html']['rowCount'] = 48;
								$block['html']['current_app'] = $app['name'];
								$block['html']['app_label'] = $this->trans($app['label']);
								$block['html']['app_label_plural'] = $this->trans($this->pluralize($app['label']));
	
								if ( empty($block['api']['page']['title']) ){
									$block['api']['page']['title'] = $block['html']['app_label_plural'];
								}
								if ( !str_contains($app['app_users']??'', '_client') OR empty($app['app_permissions']['client_write']) ){ //app menu_config to hide create new button
									$block['html']['app_readonly'] = 1;
								}	

								if ( !empty($app['app_templates']['collection']) ) {
									$block['template']['file'] = $app['app_templates']['collection'];//"page";
								} elseif ( !empty($app['app_templates']['collection_string']) ) { //not really neccessary
									$block['template']['string'] = base64_encode($app['app_templates']['collection_string']);
								} 
								if ( !empty($block['template']['file']) AND is_file($this->path . $app['slug'] . ($app['id']??'') .'/'. $block['template']['file'] .'.tpl') 
								){
									$block['template']['directory'] = 'resources/public/templates/app/'. $app['slug'] . ($app['id']??''); 
								} elseif ( empty($block['template']['file']) ){
									$block['template']['file'] = "datatable";
								}
								//App may have a default layout, then each page may have its own layout
								if ($layout = $page['layout']??$app['app_layouts']['collection']??$block['template']['layout']??null ){//=1 but set $layout
									$this->view->setLayout($layout); //unlike edit, layout could be stored in app folder
								}
							}
						} else {
							return false;
						}
					}	
					$this->view->addBlock($section, $block, 'App::'. $app['name'] .'::renderIndex');		
				}				
				$next_actions['Collection::getCollectionItems'] = [ 'App::'. $app['name'], NULL, "slug" => 1 ];
				$this->router->registerQueue($next_actions);	

				!empty($status) && $this->view->setStatus($status);
				return $block['api']['page']??[
					'id' => $app['slug'], //should return an id
				];							
			}	
		}
		return false;
	}

	public function renderCollection($full_path_without_query) {
		$full_path_without_query = explode('::', trim($full_path_without_query, '/'));
		$app = $this->getAppInfo($full_path_without_query[0]); 
		if (!empty($app['app_permissions']['client_read_permission']) AND 
			!$this->user->has($app['app_permissions']['client_read_permission'])
		){
			$app['app_permissions']['client_read'] = null;
		}	

		if ( ! $app ) {
			return false;
		} elseif ( !str_contains($app['app_users']??'', '_guest') OR empty($app['app_permissions']['client_read']) ){
			$this->denyAccess('view');
		} else {
			$slug = $full_path_without_query[1];
			$page = $this->readSlug($slug, 'Collection', 'App::'. $app['name']);	
		
			if (empty($page) OR !$this->canView($page) ){ //no such slug; return false and let the main controller handle 404 
				return false; 
			} 

			//remove fields other than client_editable/readonly before processing
			foreach ( ($app['app_fields']??[]) AS $key => $field) {
				if ( empty($field['visibility']) OR $key == 'creator' ){ //allow creator in collection
					//treat it as client_editable
				} elseif ( !str_contains($field['visibility'], 'client_') OR $field['visibility'] == 'client_hidden' ){
					unset($app['app_columns'][ $app['slug'] .'_'. $key ], $app['app_columns'][ $key ]); 
				}
			}

			$query = $this->db
				->table($this->table .' AS page')
				->leftJoin($this->site_prefix .'_location', 'page_id', '=', 'page.id')
				->where('app_type', 'collection')
				->where('page.type', 'App')
				->where('page.subtype', $app['name'])
				->where('app_id', $page['id'])
				->select('page.id', 'page.type', 'page.subtype', 'page.slug', 'page.name', 'page.title', 'page.description', 'page.updated', 'page.image', 'page.public')
				->orderBy('updated', 'desc');	
			$query = $this->getPublished($query);
			empty($app['app_columns']['creator']) && $app['app_columns']['creator'] = $this->trans('User');
			//$show_creator = $this->showCreator($app, $query, ['creator', 'user.name AS creator_name']); //this may change $app and $query if $show_creator 

			//provide default sorting since automatic sorting has problem with unionAll
			if ( empty($_REQUEST['sort']) ){
				$_REQUEST['sort']['id'] = 'desc'; 
			}
			if ( empty($_REQUEST['current']) ){
				$_REQUEST['current'] = 1; //force non-ajax mode 
			}
			if ( !empty($_REQUEST['rowCount']) AND $_REQUEST['rowCount'] == 12){
				$_REQUEST['rowCount'] = 48; 
			}	

			$block = $this->prepareMain($query, $app, $show_links??null, $strip_tags??null);
			$block['api']['page'] = $page;

			$response = $this->appProcess($app, 'renderCollection', $block);
			$this->runHook('App::'. $app['name'] .'::'. __FUNCTION__, [ &$response['blocks'] ]);
			foreach ( ($response['blocks']??[]) AS $section => $block) {
				if ($section == 'main'){
					//remove fields other than client_editable/readonly
					foreach ( ($app['app_fields']??[]) AS $key => $field) {
						if ( empty($field['visibility']) ){
							//treat it as client_editable
						} elseif ( !str_contains($field['visibility'], 'client_') OR $field['visibility'] == 'client_hidden' ){
							unset($block['api']['page']['meta'][ $key ], $block['api']['page']['meta'][ $app['slug'] .'_'. $key]);
						}
					}
					//count subapp's entries
					if ( !empty($block['api']['rows']) AND !empty($app['app_sub']) ){
						$block['api']['subapp'] = $this->countSubPages($app, array_column($block['api']['rows'], 'id') );	
					}

					if($this->view->html){				
						$block['links']['edit'] = rtrim($this->slug('App::render', ['app' => $app['slug'], 'slug' => ''] ), '/');
						$block['links']['datatable']['creator'] = $this->slug('Profile::render');
						$block['html']['rowCount'] = 48;
						$block['html']['current_app'] = $app['name'];
						$block['html']['app_label'] = $this->trans($app['label']);
						$block['html']['app_label_plural'] = $this->trans($this->pluralize($app['label']));
				
						if ( !empty($app['app_templates']['collection']) ) {
							$block['template']['file'] = $app['app_templates']['collection'];//"page";
						} elseif ( !empty($app['app_templates']['collection_string']) ) { //not really neccessary
							$block['template']['string'] = base64_encode($app['app_templates']['collection_string']);
						} 
						if ( !empty($block['template']['file']) AND is_file($this->path . $app['slug'] . ($app['id']??'') .'/'. $block['template']['file'] .'.tpl') 
						){
							$block['template']['directory'] = 'resources/public/templates/app/'. $app['slug'] . ($app['id']??''); 
						} elseif ( empty($block['template']['file']) ){
							$block['template']['file'] = "app_collection";//page_collection
						}
						//App may have a default layout, then each page may have its own layout
						if ($layout = $page['layout']??$app['app_layouts']['collection']??$block['template']['layout']??null ){//=1 but set $layout
							$this->view->setLayout($layout); //unlike edit, layout could be stored in app folder
						}
					}
				}
				$this->view->addBlock($section, $block, 'App::'. $app['name'] .'::renderCollection');		
			}
			$next_actions['Collection::getCollectionItems'] = [ 'App::'. $app['name'], $page['id'], "slug" => 1 ];
			$this->router->registerQueue($next_actions);	

			$status['result'] = 'success';
			!empty($status) && $this->view->setStatus($status);
			
			return $page;
		}					 
	}

	protected function getParentPage($page, &$fields){
		$parent = $this->db->table($this->site_prefix .'_location')
			->join($this->table .' AS parent', 'parent.id', '=', 'app_id')
			->join($this->table .' AS root', 'root.id', '=', 'location')
			->where('app_type', 'subapp')
			->where('page_id', $page['id'])
			->where('section', $page["subtype"])
			->first(['parent.id', 'parent.type', 'parent.subtype', 'parent.slug', 'parent.name', 'root.id AS root_id', 'root.type AS root_type', 'root.subtype AS root_subtype', 'root.slug AS root_slug', 'root.name AS root_name']);
		if ($parent) {
			$parent['root_name'] = $this->getRightLanguage(json_decode($parent['root_name']??'', true))?: $parent['root_subtype'] .' #'. $parent['root_id'];
			$parent['root_slug'] = $this->generateSlug($parent['root_slug'], $parent['root_type'], $parent['root_subtype']??null);	
			$block['api']['parent'] = $this->preparePage($parent);
			if ($parent['type'] == 'App'){
				$class = str_replace('Core', 'App', __NAMESPACE__) .'\\'. $this->formatAppName($parent['subtype']);
			} else {
				$class = __NAMESPACE__ .'\\'. $this->formatAppName($parent['type']);
			}
			if ( empty($this->config['activation'][ $class ]) ){
				$label = $this->db
					->table($this->site_prefix .'_config')
					->where('type', 'activation')
					->where('name', $class) 
					->value('description');
				$label = json_decode($label??'', true);	
			} else {
				$label = $this->config['activation'][ $class ];
			}
			$block['api']['parent']['app_label'] = $this->trans($label[4]??$this->formatAppLabel($parent['type'] == 'App'? $parent['subtype'] : $parent['type']));
			if ($parent['root_type'] == 'App'){
				$class = str_replace('Core', 'App', __NAMESPACE__) .'\\'. $this->formatAppName($parent['root_subtype']);
			} else {
				$class = __NAMESPACE__ .'\\'. $this->formatAppName($parent['root_type']);
			}
			if ( empty($this->config['activation'][ $class ]) ){
				$label = $this->db
					->table($this->site_prefix .'_config')
					->where('type', 'activation')
					->where('name', $class) 
					->value('description');
				$label = json_decode($label??'', true);	
			} else {
				$label = $this->config['activation'][ $class ];
			}
			$block['api']['parent']['root_app_label'] = $this->trans($label[4]??$this->formatAppLabel($parent['root_type'] == 'App'? $parent['root_subtype'] : $parent['root_type']));

			$_key = strtolower($parent['subtype']); 
			if ( empty($block['api']['parent']['name']) ){
				$block['api']['parent']['name'] = $parent['subtype'] .' #'. $parent['id'];
			} elseif ( !empty($fields[ $_key ]['type']) AND 
				(	$fields[ $_key ]['type'] == 'lookup' OR 
					(	$fields[ $_key ]['type'] == 'select' AND 
						!empty($fields[ $_key ]['options']['From::lookup']) AND
						!empty($fields[ $_key ]['options'][ $_key ])
					) 
				) 
			){
				$fields[ $_key ]['visibility'] = 'hidden'; //dont unset, just hide it to keep its value 
			}
			//root
			if ( !empty($parent['root_subtype']) ){
				$_key = strtolower($parent['root_subtype']); 
				if ( !empty($fields[ $_key ]['type']) AND 
					(	$fields[ $_key ]['type'] == 'lookup' OR 
						(	$fields[ $_key ]['type'] == 'select' AND 
							!empty($fields[ $_key ]['options']['From::lookup']) AND
							!empty($fields[ $_key ]['options'][ $_key ])
						) 
					) 
				){
					$fields[ $_key ]['visibility'] = 'hidden';
				}
			}
		}

		return $block['api']['parent']??null;
	}
}