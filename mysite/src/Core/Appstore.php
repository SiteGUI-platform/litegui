<?php
namespace SiteGUI\Core;

class Appstore extends Product { //use Product so we can share subtype constant
	protected $system_prefix;
	protected $table_config;
	protected $show_hide;

	public function __construct($config, $dbm, $router, $view, $user){
		parent::__construct($config, $dbm, $router, $view, $user);
		$this->table_config  = $this->site_prefix ."_config"; //use siteid_config table      
		$this->table         = $this->table_prefix ."1_page"; //it should resolve to mysite1_page (system site)
		$this->table_product = $this->table_prefix ."1_product";    
		$this->requirements['CREATE'] 	= "Appstore::create";
		$this->requirements['INSTALL'] 	= "Site::update";  //Site Admin
		//this PUBLISH permission is for site 1 only, it must not be enabled for site manager role 
		//otherwise any site manager can publish appstore item to site 1
		//Site 1 admins actually just need Product:publish permission to update thru Product app
		$this->requirements['PUBLISH'] 	= "Appstore::publish";//Reserved for admins who approve developers' submission	
		
		if (!empty($this->config['system']['base_dir'])) {
			//write to protected/app for central storage, this is symlinked to public/templates/app for faster rendering
			$this->path = $this->config['system']['base_dir'] .'/resources/protected/app/';
		} else {
			echo "Template directory is not defined!";
		}
		$this->show_hide = ['locales', 'tabapp', 'tabcontent', 'tabsettings', 'name', 'slug', 'title', 'description', 'content', 'wysiwyg', 'image', 'layout', 'menu_id', 'collection', 'breadcrumb', 'private', 'published', 'published_at', 'expire'];  
	}

	public function main($type = 'Product') { //public
		$this->db = $this->dbm->getConnection('central'); //required for ProductVariant to use central db
      $url = 'https://app.sitegui.com/';
      if ( !empty($_REQUEST['type']) ){
         $subtype = $_REQUEST['type'];
         $url .= 'collection/'. $subtype;
      } else {
         $subtype = '';  
         $url .= 'store';
      }   
      $url .= '.json';
      $response = json_decode($this->httpGet($url, $_REQUEST)??'', true);

      if ( !empty($response['status']['result']) AND $response['status']['result'] == 'success'){
         $block['api'] = $response;
			if ($this->view->html){					
           		if ($this->user->has($this->requirements['PUBLISH']) ){
           			$links['edit'] = $this->slug('Appstore::action', ['action' => 'edit']);
           		}		
				$links['pagination'] = $this->slug('Appstore::main');
				$links['query'] = '';
				in_array(ucfirst($subtype), $this->subtype) && $links['query'] .= '&type='. $subtype;
				!empty($_REQUEST['searchPhrase']) && $links['query'] .= '&searchPhrase='. $_REQUEST['searchPhrase'];

            $links['cart_add'] = 'https://my.app.sitegui.com/account/cart/add';

				$block['links'] = $links;
				$block['html']['app_menu'][ ] = [
					'icon' => "fas fa-shopping-cart sidebar-toggler badge-temp badge-danger cart-count",
            ];		
				$block['template']['file'] = "appstore_main";		
			}
			$this->view->addBlock('main', $block, 'Appstore::main');	
		} else {
			$status['result'] = "error";
			$status['message'][] = !empty($_REQUEST['searchPhrase'])? $this->trans('No result found') :
				$this->trans('This store does not have any :Type at this time!', ['type' => in_array(ucfirst($subtype), $this->subtype) ? $subtype : 'apps' ]);
		}			

		!empty($status) && $this->view->setStatus($status);							
	
		if (!empty($next_actions)){
			$this->router->registerQueue($next_actions);	
		}						
	}	
	/**
	* list all apps available for site when using App::main
	* @return none
	*/
	public function site($type = 'Product') {
		if ( $this->user->has($this->requirements['CREATE']) ) {
				//list default, configurable apps only, hidden app (!empty($cfg[1]) must be configured in Site's default app
				foreach ($this->config['activation'] as $app => $cfg) {
					if ( !empty($cfg[2]) OR strpos($app, '\\App\\') ){ //All App are configurable
						strtok($app, '\\');
						$a = ['subtype' => strtok('\\') ]; //convert string to array
						$a['name'] = strtok('\\');
						$a['slug'] = strtolower($a['name']);
						$a['name'] = $this->formatAppLabel($a['name']);
						$a['default'] = 1;
						$a['activated'] = 1;
						$a['cfg'] = $cfg;
						$a['configurable'] = $cfg[2]??'';
						if ( !empty($apps[ $a['subtype'] .'::'. $a['slug'] ]) ){
							$apps[ $a['subtype'] .'::'. $a['slug'] ] += $a; //will keep activated's cfg if exists
						} else {
							$apps[ $a['subtype'] .'::'. $a['slug'] ]  = $a;
						}	
					}
				}
				
			if ($apps){
				$status['result'] = "success";
				$block['api']['total'] = $block['api']['rowCount'] = count($apps);
				$block['api']['current'] = 1;
				foreach($apps AS $a){
					if ( !empty($a['activated']) ){
						if ( (!empty($a['cfg'][1]) AND str_contains($a['cfg'][1], 'staff')) ){
							$a['hide'] = false;
						} else {
							$a['hide'] = true;
						}
					}	
					unset($a['cfg'], $a['class']);

					if (empty($a['image'])){
						$item['abbr'] = $a['name']; 
						if ( str_contains($item['abbr'], ' ') ){
							$item['abbr'] = implode('', array_map(function($v) { 
								return mb_substr($v, 0, 1, 'utf-8'); 
							}, explode(' ', $item['abbr'])));
						}
						$a['style']['abbr'] = strtoupper(substr($item['abbr'], 0, 4));
						$item['v'] = str_replace(' ', '', $a['name']);
						$item['r'] = mb_ord($item['v'][0])*30 % 255; //*30 to expand the color gap between letter
						$item['g'] = mb_ord($item['v'][ (int) floor(strlen($item['v'])/2) ])*30 % 255;
						$item['b'] = mb_ord($item['v'][ strlen($item['v']) - 3 ])*30 % 255;
						$a['style']['bg'] = "rgb(". $item['r'] .", ". $item['g'] .", ". $item['b'] .")";
						$a['style']['color'] = ($item['r']*0.299 + $item['g']*0.587 + $item['b']*0.114) > 149? '#000000' : '#FFFFFF';
					}
					$block['api']['rows'][] = $a;					
				}

				if ($this->view->html){				
					if ($this->user->has($this->requirements['CREATE']) ){
						$links['pagination'] = $this->slug('App::main');
						if ($this->user->has($this->requirements['INSTALL'] ) ){
							$links['configure']  = $this->slug('Appstore::configure');
                     $links['register'] = $this->slug('Appstore::register');                     
						}	
						if ($this->user->has($this->requirements['PUBLISH'])){ 
                     $links['deregister'] = $this->slug('Appstore::deregister');
						}

						$block['links'] = $links;	
					}	

					$block['template']['file'] = "app_main";	
				}
			} else {
				$status['result'] = "error";
				$status['message'][] = $this->trans('You have not bought or created any apps!');
				
				if ($this->view->html){				
					$status['html']['message_type'] = 'info';
					$status['html']['message_title'] = $this->trans('Information');	
	                $status['message'][ $this->slug('Appstore::main') ] = $this->trans('Click here to go to Appstore');
				}
			}		
		} else {
			$this->denyAccess('list');
		}	
			
		!empty($status) && $this->view->setStatus($status);							
		$this->view->addBlock('main', $block, 'Appstore::main');					
	}

	protected function read($id, $type = 'Product', $subtype = '', $creator = NULL, $for_update = NULL) {
		return parent::read($id, $type, $subtype, $creator, $for_update);
	}
	public function update($page) {
		return $status??null;	
	}

	protected function readAppMeta($id, $property = null) {
		$meta = $this->db
			->table($this->table_prefix .'_system')
			->where('type', 'app_meta')
			->where('object', $id)
			->when($property, function ($query) use ($property) {
				return $query->where('property', $property);
			})
			->pluck('value', 'property')
			->all();

		foreach( ($meta??[]) AS $key => $value) {
			$meta[$key] = json_decode($value??'', true)?? $value; //both json and string are ok, except null -- must use null coalescing operator (??)
			//if ($meta[$key] === null) {
			//	$meta[$key] = $value;
			//}	
		}
		return $property? ($meta[ $property ]??null) : ($meta??[]);	
	}
	//delete App Meta
	protected function deleteAppMeta($id) {
		return $this->db
			->table($this->table_prefix .'_system')
			->where('type', 'app_meta')
			->where('object', $id)
			->delete();
	}

	protected function readAppTemplates($app_info){
		if ( empty($app_info['slug']) OR empty($app_info['subtype']) ){
			$app_info = $this->db->table($this->table)
				->where('id', $app_info['id'])
				->where('type', 'Product')
				->first();
		}
		//get templates
		if ( !empty($app_info['slug']) AND !empty($app_info['subtype']) ){
			if ($app_info['subtype'] == 'App' OR $app_info['subtype'] == 'Core' OR $app_info['subtype'] == 'Widget') {
				$app_slug = strtolower( $this->formatAppName($app_info['slug']) ); 
				$path = $this->path . $app_slug . $app_info['id'];

				if ($app_slug AND is_dir($path)) {
					//app PHP file
					$_file = $path .'/'. $app_slug .'.php'; 
					if ( is_readable($_file) ) {
						$app_meta['app_file'] = @file_get_contents($_file);
					}
					//templates
					foreach (['page', 'collection', 'edit', 'widget'] AS $_template) {
						if ($app_info['subtype'] == 'Widget') {
							$_filename = 'widget_'. $app_slug; //widget_text
							if ($_template == 'edit') {
								$_filename .= '_edit'; //widget_text_edit
							} elseif ( $_template != 'widget' ){
								continue;
							}
						} elseif ( $_template == 'widget' ){
							continue;
						} else {	
							$_filename = $app_slug .'_'. $_template; //app_edit
						}
						$_file = $path .'/'. $_filename .'.tpl';
						if ( is_readable($_file) ) {
							$app_meta['app_templates'][ $_template ] = @file_get_contents($_file);
						}	
					}
					//mails template
					if ( is_dir($path .'/mails') ) {
						foreach (@glob($path .'/mails/*.tpl') as $i => $f) {
							$app_meta['app_templates']['mails'][ $i ]['name'] = substr(basename($f), 0, -4);
							$app_meta['app_templates']['mails'][ $i ]['content'] = @file_get_contents($f); 		
						}
					}							
				}	
			}
		}

		return $app_meta??[];
	}

	public function edit($id = 0, $menus = 'Menu::getMenus', Tax $taxObj = null){
	}

	//register routes, permission, also use to install app db for site
	public function register($app) {
		//$this->db->getDatabaseManager()->setDefaultConnection('central');
		$app = filter_var($app, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$type = $this->formatAppName(strtok($app, '\\'));
		$name = $this->formatAppName(strtok('\\'));

		//dont require AppstoreAdmin permission to allow the Core app to be registered by siteadmin during activation
		//make sure the code below is unharmful to the system
		if ($name AND $type == 'Core' AND $this->user->has($this->requirements['INSTALL']) ) {
			$Class = __NAMESPACE__ .'\\'. $name;
			if ( class_exists($Class) ) {
				$instance = new $Class($this->config, $this->dbm, $this->router, $this->view, $this->user);
				$routes = $instance->generateRoutes();
				//print_r($routes);
				$activated_routes = $this->router->getRoutes();
				if (is_array($routes[ $this->config['system']['passport'] ]) AND array_diff_key($routes[ $this->config['system']['passport'] ], $activated_routes) ){//routes not matches
					// register permissions
					$requirements = $instance->getRequirements();
					foreach($requirements AS $property) {
						if ( is_array($property) ){ //requirement can be array of permissions
							foreach($property AS $p) {
								$label = ucfirst(substr($p, strpos($p, '::') + 2));
								//$insert_args[] = '(?, ?, ?, ?)';
								$insert_data[] = "permission";
								$insert_data[] = $p;
								$insert_data[] = ','. $name .','; //make searching/comparing easier
								$insert_data[] = $label;
							}
						} else {
							$label = ucfirst(substr($property, strpos($property, '::') + 2));
							//$insert_args[] = '(?, ?, ?, ?)';
							$insert_data[] = "permission";
							$insert_data[] = $property;
							$insert_data[] = ','. $name .','; //make searching/comparing easier
							$insert_data[] = $label;
						}	
					}
					if (!empty($insert_data)) {
						$update['value'] = 'IF(value LIKE CONCAT("%", VALUES(value), "%"), value, REPLACE( CONCAT(value, VALUES(value)), ",,", ","))';
						$success = $this->upsert($this->table_prefix .'_system', ['type', 'property', 'value', 'name'], $insert_data, $update, 'central');
					}
					//register routes
					foreach ($routes AS $passport => $passport_routes) {
						foreach ($passport_routes AS $property => $value) {
							//$route_args[] = '(?, ?, ?)';
							$route_data[] = $passport ."_route";
							$route_data[] = $property;
							$route_data[] = str_replace('\\/', '/', json_encode($value));
						}
					}	
					if (!empty($route_data)) {
						$success = $this->upsert($this->table_prefix .'_system', ['type', 'property', 'value'], $route_data, ['value'], 'central');
					}				

					if ( isset($success) AND empty($success) ) {
						$status['result'] = 'error';
						$status['message'][] = $this->trans('Failed to register');
					} else {					
						$status['message'][] = $this->trans('Successfully registered');
					}	
				} else {
					$status['message'][] = $this->trans('Routes are already registered');
				}
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('No such :item', ['item' => 'App']);
			}
		}
		!empty($status) && $this->view->setStatus($status);	
		if ($this->view->html){		
			//method being called by activate $next_actions['App::main'] = [];
			//force rebuilding menu
			$next_actions['Site::render'] = ['App'];
			$this->router->registerQueue($next_actions);	
		}
		return ( empty($status['result']) OR $status['result'] != 'error' )? true : false;				
	}
	
	//deregister App in the system => remove routes (only Core App at this time)
	public function deregister($app) {
		$app = filter_var($app, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$type = $this->formatAppName(strtok($app, '\\'));
		$name = $this->formatAppName(strtok('\\'));

		if ($name AND $type == 'Core' AND $this->user->has($this->requirements['PUBLISH']) ) {
			$Class = __NAMESPACE__ .'\\'. $name;

			if ( class_exists($Class) ) {
				$instance = new $Class($this->config, $this->dbm, $this->router, $this->view, $this->user);
				// register permissions
				$requirements = $instance->getRequirements();
				if ($requirements) {
					foreach ($requirements AS $index => $property) { //requirement can be array of permissions
						if ( is_array($property) ){
							$requirements += $property;
							unset($requirements[ $index ]);
						}
					}	
					$results = $this->dbm->getConnection('central')
						->table($this->table_prefix .'_system')
						->whereIn('property', array_values($requirements) )
					    ->where('type', 'permission')	
					    ->select('id', 'value')
					    ->get()->all();
									    		 
					foreach($results AS $result) {
						if (trim($result['value'], ',') == $name ) { //perm only used by this class
							$delete_ids[ ] = $result['id'];
						} elseif (strpos($result['value'], ',') !== false ) {
							$update_ids[ ] = $result['id'];
						}	
					}
					if (!empty($delete_ids)) {
						$this->dbm->getConnection('central')
							->table($this->table_prefix .'_system')
							->whereIn('id', $delete_ids)
							->delete();
					}
					if (!empty($update_ids)) {
						//$query  = 'UPDATE '. $this->table_prefix .'_system SET value = REPLACE(value, ",'. $this->class .',", ",")';
						//$query .= ' WHERE id IN '. $result['id'];
						//echo $query; print_r($insert_data);
						$update['value'] = $this->db->raw('REPLACE(value, ",'. $name .',", ",")');
						$this->dbm->getConnection('central')
							->table($this->table_prefix .'_system')
							->whereIn('id', $update_ids)
							->update($update);		
					}
				}
					
				$routes = $instance->generateRoutes();
				foreach ($routes AS $passport => $passport_routes) {
					if (!empty($passport_routes)) {
						$this->dbm->getConnection('central')
							->table($this->table_prefix .'_system')
							->where('type', $passport .'_route')
							->whereIn('property', array_keys($passport_routes) )
							->delete();
					}		 
				}
				$status['message'][] = $this->trans('Deregister successfully');
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('No such :item', ['item' => 'App']);
			}

			!empty($status) && $this->view->setStatus($status);	
			if ($this->view->html){		
				$next_actions['App::main'] = [];
				//force rebuilding menu
				$next_actions['Site::render'] = ['App'];
				$this->router->registerQueue($next_actions);	
			}			
		} else {
			$this->denyAccess('deregister');
		}	
	}

	//configure specified app, both default core app and activated appstore app are accepted
	public function configure($app, $fields = null) {
		if ($this->user->has($this->requirements['INSTALL']) ){
			//little hack for get request
			if (empty($app) AND !empty($_GET['name'])) {
				$app = str_replace('/', '\\', $_GET['name']); //GET URI may not support \ and we have to use / instead
			}
			$app = filter_var($app, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$type = $this->formatAppName(strtok($app, '\\'));
			$app = $this->getAppInfo( strtok('\\'), $type, 'load_remote_info'); 
			if ($app) {				
				$activation = $this->checkActivation($app['class']);
				if ( is_string($activation) ){
					$activation = json_decode($activation??'', true);
				}	
				//get App's input fields to show as selectable item for automation
				if ($type == 'App') {
					//Captcha settings, add this before save config to save captcha
					$app['app_configs']['_captcha'] = [
						'label' => $this->trans('Enable Captcha'),
			            'type' => 'checkbox',
			            'size' => '8',
			            'value' => '',
			            'description' => $this->trans('Enable Captcha to prevent bots from spamming your app'),
					];
					foreach ( array_slice($this->show_hide, 4) as $key ) { //from 'name'
						if ( empty($app['app_hide'][ $key ]) ) {
							$config['app_show'][$key] = 1;
						}
					}
					foreach ( ($app['app_fields']??[]) as $key => $value ) {	
						$config['app_show'][$key] = 1;
					}
				}

				$config['name'] = $this->trimRootNs($app['class']); //str_replace('_', ' ', $name);
				//get default config first so we'll know if there is password type which should be encrypted
				$config['app_configs'] = $app['app_configs']??[];
				foreach ($config['app_configs'] AS $key => $f) {
					//format field without value as value retrieved by getAppConfigValues later are already formatted
					//required for lookup scope
					$this->formatFieldValue(null, $key, $config['app_configs'][ $key ], $app); 
				}
				if ( !empty($config['app_configs']['oauth']['type']) AND $config['app_configs']['oauth']['type'] == 'oauth' ){
					$app_secret = $this->getSystemConfig($config['name'] .'::'. ($app['id']??$app['slug']??''), 'app_secret'); //append $app.id to avoid loading unintended secret
					if ($app_secret){
						$app_secret = $this->decode($app_secret, 'static');
					} elseif ( !empty($config['app_configs']['oauth']['value']) ){ //save app_secret
						$this->setSystemConfig([
							'type' => 'app_secret',
							'property' => $config['name'] .'::'. ($app['id']??$app['slug']??''),
							'value' => $this->encode($config['app_configs']['oauth']['value'], 'static'),
						]);
						$app_secret = $config['app_configs']['oauth']['value'];
					}					
				}

    			$replaces = [
    				'{site_id}' => $this->config['site']['id'],
    				'{redirect_uri}' => urlencode($this->config['system']['url'] . $this->slug('Appstore::oauth') .'?name='. str_replace('\\', '/', $config['name']) ),
    				'{state}' => urlencode($_SESSION['token'] . $this->config['site']['id']),//insert site_id to use fixed redirect_uri
    				'{code_challenge}' => urlencode(substr($_SESSION['token'], 0, 43)),
    			];	
				//Oauth return auth code, we get access_token and other info here so Instant App can also utilize this
				if (!empty($_REQUEST['code']) AND 
					!empty($config['app_configs']['oauth']['options'][1]) AND 
					!empty($_REQUEST['state']) AND 
					@hash_equals($_REQUEST['state'], $replaces['{state}'] )//csrf
				){
					//enable for published app only to make sure we review the oauth field to prevent abuse/exploits
					if ( 0 AND empty($app['published']) ){
						$status['message'][] = $this->trans('This app must be published in order to use this feature');
					} else {
		    			$replaces['{client_secret}'] = urlencode($app_secret??'');
		    			$replaces['{code}'] = urlencode($_REQUEST['code']);

						$token = $this->httpOauth(
							$config['app_configs']['oauth']['options'][1], 
							$config['app_configs']['oauth']['options'][2]??'', 
							$replaces,
							$config['app_configs']['oauth']['options'][3]??null,
						);
						$token = json_decode($token??'', true);
						//print_r($response);
						if ( !empty($token['access_token']) ){
							//get other info
							if ( !empty($config['app_configs']['oauth']['options'][4]) ){
								$replaces['{access_token}'] = urlencode($token['access_token']);
								$auth_info = $this->httpOauth(
									$config['app_configs']['oauth']['options'][4], 
									$config['app_configs']['oauth']['options'][5]??'', 
									$replaces,
									$config['app_configs']['oauth']['options'][6]??null,
									$options??null
								);
								$auth_info = json_decode($auth_info??'', true);
								if ( is_array($auth_info) ){
									$this->flatten($auth_info, $auth_info);
								}	
								//print_r($auth_info);
							}
							foreach ($config['app_configs'] AS $key => $f) {
								if ( !empty($f['value']) AND str_starts_with($f['value'], '{oauth::') ){
									$p = trim(str_replace('}', '', substr($f['value'], 8) ));
									$fields[ $key ] = $auth_info[ $p ]??$token[ $p ]??$_GET[ $p ]??$f['value']; //also save selected returned key
								} 
							}
							$save_hidden = 1;
							$fields['access_token'] = $token['access_token']; //here so it cant be set by $_GET
						}
						if ( !empty($token['refresh_token']) ){
							$fields['refresh_token'] = $token['refresh_token'];
							unset($app['app_configs']['refresh_token']['visibility']); //to save this hidden field
						}
					}	
				}				
				//save config if posted				
		    	$subapp_support = ($type == 'App' || !empty($app['subapp_support']) ) && ( ($app['creator']??null) == $this->user->getId() OR (!empty($activation[2]) && $activation[2] == 'developer') );
		    	if ( !empty($fields) ){
		    		$status = $this->saveAppConfig($app, $fields, $save_hidden??null, $subapp_support);	
		    	}

				if ( !empty($config['app_configs']) OR $subapp_support ){	
				   	//get stored config value
					$stored = $this->getAppConfigValues($config['name'], $config['app_configs']);
					if ( !empty($stored) ){
						if ( array_key_exists('config_app_fields', $stored) ){
							$config['app_fields'] = $stored['config_app_fields'];
							unset($stored['config_app_fields'], $stored['config_app_columns']);
						}
						if ( array_key_exists('config_app_buttons', $stored) ){
							$config['app_buttons'] = $stored['config_app_buttons'];
							unset($stored['config_app_buttons']);
						}
						if ( array_key_exists('config_app_sub', $stored) ){
							$config['app_sub'] = $stored['config_app_sub'];
							unset($stored['config_app_sub']);
						}
						if ( array_key_exists('config_app_automation', $stored) ){
							$config['app_automation'] = array_merge( $stored['config_app_automation']['pre']??[], $stored['config_app_automation']['post']??[] );
							unset($stored['config_app_automation']);
						}	
					}

					foreach ( ($config['app_configs']??[]) AS $key => $field ){
						if ($field['type'] == 'oauth'){
							$config['app_configs'][ $key ]['value'] = ''; //remove default value as well
							//mark oauth as already authorized
							if ( !empty($stored['access_token']) ){
								$config['app_configs'][ $key ]['authorized'] = true; //set for formfield
							}
						} elseif ( !empty($field['visibility']) AND $field['visibility'] == 'hidden' ){
							$config['app_configs'][ $key ]['value'] = ''; //remove default value as well
						} elseif ( isset($stored[ $key ]) ){ //accept value 0
							$config['app_configs'][ $key ]['value'] = $stored[ $key ];//value already formatted by getAppConfigValues
						}
					}

					if ( !empty($config['app_configs']['callback']['value']) ){
						$config['app_configs']['callback']['description'] = 'Webhook URL: https://'. $this->config['site']['account_url'] .'/account/cart/callback.json?app='. $type .'/'. $app['name'] .'&verifier='. $config['app_configs']['callback']['value'];
					}	
				} else {
					$status['html']['message_title'] = $this->trans('Configuration');					
					$status['message'][] = $this->trans('No configuration needed');					
				}

				//format config fields' options only (no From::configs like App's field), value does not need format at this time 
				foreach ($config['app_configs'] as $key => $field) {
					//process options lookup, configs even if no value is set
					if ( ($field['type'] == 'select' OR $field['type'] == 'radio' OR $field['type'] == 'radio hover') ){
						if ( !empty($field['options']['From::lookup']) ){ //options provided by lookup
	    					$lookup = array_values($field['options']);
	    					if ($lookup[1]) {//lookup key *** lookupByValue return rows and slug, need rows only
		    					$this->resolveOptions($config['app_configs'][ $key ], $lookup);	
			    			}
			    		} 
		    		} elseif ($field['type'] == 'fieldset' AND !empty($field['fields'])) {
		    			foreach ($field['fields'] as $key2 => $field2) {
							if ( !empty($field2['options']['From::lookup']) ){ //options provided by lookup
		    					$lookup = array_values($field2['options']);
		    					if ($lookup[1]) {//lookup key
			    					$this->resolveOptions($config['app_configs'][ $key ]['fields'][ $key2 ], $lookup);	
				    			}
				    		} 
		    			}	
		    		} elseif ($field['type'] == 'oauth' AND !empty($config['app_configs'][ $key ]['options'][0]) ){
		    			$config['app_configs'][ $key ]['options'][0] = str_replace(array_keys($replaces), $replaces, $config['app_configs'][ $key ]['options'][0]);
		    		}		
				}

				$block['api']['config'] = $config;
				if ($this->view->html){
					$links['main']   = $this->slug('App::main');
					$links['lookup'] = $this->slug('Lookup::now');
					$links['configure'] = $this->slug('Appstore::configure');
					if ( !empty($this->config['system']['sgframe']) ){
						$links['configure'] .= '?sgframe=1';
						$this->view->setLayout('blank'); //via iframe 
					}
		
					$block['links'] = $links;		
					$block['html']['title'] = $this->trans('Configure :item', ['item' => str_replace('_', ' ', $app['name']) ]);
					$block['html']['subapp_support'] = $subapp_support;
					if ($type == 'Core'){
						$block['html']['hide_warning'] = 1;
					}
					$block['template']['file'] = "appstore_configure";		
				}
				$this->view->addBlock('main', $block, 'Appstore::configure'); //substr(__METHOD__, strlen(__NAMESPACE__) + 1);
			} else {
				$status['html']['message_title'] = $this->trans('Configuration');					
				$status['message'][] = $this->trans('No such :item', ['item' => 'App']);					
			}	
			!empty($status) && $this->view->setStatus($status);
		} else {
			$this->denyAccess('configure');
		}		
	}

	//this is also used by Notification app to save App's refresh/access_token
	public function saveAppConfig($app, $fields, $save_hidden = false, $subapp_support = false) {
		foreach ($fields as $property => $value) {
			$property = trim(filter_var($property, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
			if ($property == 'config_app_fields' AND $subapp_support ){//subapp entries
				$value = $this->prepareCustomFields($value??[], $app['slug']);
				//Always update config_app_columns: type, object, property, value
				$insert_data[] = "config";
				$insert_data[] = $this->trimRootNs($app['class']); //short class name Core\Order
				$insert_data[] = 'config_app_columns';
				$insert_data[] = !empty($value['app_columns'])? json_encode($value['app_columns'], JSON_FORCE_OBJECT) : NULL;
				unset($value['app_columns']);

				if ( !empty($value) ){
					$value = json_encode($value, JSON_FORCE_OBJECT);
				} else {
					$value = null;
				}
			} elseif ($property == 'config_app_buttons' AND $subapp_support ){
				$value = $this->prepareButtons($value??[]);
				if ( !empty($value) ){
					$value = json_encode($value, JSON_FORCE_OBJECT);
				} else {
					$value = null;
				}
			} elseif ($property == 'config_app_sub' AND $subapp_support ){
				$value = $this->prepareSubApps($value??[]);
				if ( !empty($value) ){
					$value = json_encode($value, JSON_FORCE_OBJECT);
				} else {
					$value = null;
				}	
			} elseif ($property == 'config_app_automation' AND $subapp_support ){
				$value = $this->prepareAutomation($value??[]);
				if ( !empty($value) ){
					$value = json_encode($value, JSON_FORCE_OBJECT);
				} else {
					$value = null;
				}	
			} elseif ( empty($app['app_configs'][ $property ]) OR 
				( !$save_hidden AND !empty($app['app_configs'][ $property ]['visibility']) AND 
					( $app['app_configs'][ $property ]['visibility'] == 'hidden' OR $app['app_configs'][ $property ]['visibility'] == 'readonly' )
				)	 
			){ 
				continue; //dont save non-present, hidden, readonly fields in all cases, reserved for system only
			}
			
			//app's config fields at this point only
    		if ( !empty($app['app_configs'][ $property ]['type']) ){
    			if ( $app['app_configs'][ $property ]['type'] == 'password') {
    				$value = $this->encode($value, 'static');
    			} elseif ( $app['app_configs'][ $property ]['type'] == 'image' OR $app['app_configs'][ $property ]['type'] == 'file' ){
					$value = json_encode($value, JSON_FORCE_OBJECT);
    			} elseif ( $app['app_configs'][ $property ]['type'] == 'fieldset') {
    				foreach ( $value AS $index => $fieldset ){
    					foreach ($fieldset as $property2 => $value2) {
    						if (isset($app['app_configs'][ $property ]['fields'][ $property2 ]) AND $app['app_configs'][ $property ]['fields'][ $property2 ]['type'] == 'password') {
		    					$value[ $index ][ $property2 ] = $this->encode($value2, 'static');
		    				}
		    			}
    				}
    				$value = json_encode($value, JSON_FORCE_OBJECT);
    			} 
    		}	
			//$property should be filtered but $value should take raw input and apply filter on output
			//$value    = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			//type, object, property, value
			$insert_data[] = "config";
			$insert_data[] = $this->trimRootNs($app['class']); //short class name Core\Order
			$insert_data[] = $property;
			$insert_data[] = $value;
			//save Site 1's Oauth to system for easy select at Sateline servers
			if ($this->site_id === 1 AND $property === 'oauth' AND $this->trimRootNs($app['class']) === 'Core\User'){
				$this->setSystemConfig([
					'type' => 'config',
					'object' => $this->trimRootNs($app['class']),
					'property' => 'oauth',
					'value' => $value,
				]);
			}
		}
		if (!empty($insert_data) ){			
			try {
				if ( $this->upsert($this->table_config, ['type', 'object', 'property', 'value'], $insert_data, ['value']) ){
					$status['result'] = 'success';
					$status['message'][] = $this->trans(':item updated successfully', ['item' => 'Configs']);	
				}											
			} catch (\Exception $e) {
				//echo $e->getMessage();
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not updated', ['item' => 'Configs']);	
			}		
		}
		return $status??null;
	}
	protected function preparePage($page, $slugify = 0) { // used by activate for reading app info by slug - thus keep slug intact
		return parent::preparePage($page, $slugify);
	}	
	protected function prepareCustomFields($page_fields, $app) {
		foreach ( $page_fields AS $f) {
			$field = [];
			$f['name'] = $this->sanitizeFileName($f['name']);
			if ($f['name'] AND $f['type']) {
				$field['type'] = $f['type'];
				$field['label'] = $f['label'];
				$field['description'] = $f['description']??'';
				$field['value'] = $f['value']??'';

				if (in_array($f['visibility']??'', [
					'client_editable', 
					'client_readonly', 
					'staff_client_readonly', 
					'client_hidden', 
					'editable', 
					'readonly', 
					'hidden',
				]) ){
					$field['visibility'] = $f['visibility'];						
				}
				if (!empty($f['column'])) {
					$field['column'] = $f['column'];
					//$column = in_array($f['name'], $this->changeable)? $f['name'] : $app .'_'. $f['name'];
					$column = $f['name'];
					$meta['app_fields']['app_columns'][ $column ] = $f['label'];						
				}
				if (!empty($f['fieldset'])) {
					$field['fieldset'] = $f['fieldset'];						
				}															
				if ($f['type'] == 'lookup' AND !empty($f['multiple'])) {
					//$field['multiple'] = $f['multiple'];						
				}										
				if (in_array($f['is']??'', ['optional', 'required', 'multiple'])) {
					$field['is'] = $f['is'];						
				}
				if (in_array($f['type'], ['select', 'radio', 'radio hover']) AND !empty($f['options']) ){ //options value
					foreach ( ($f['options']??[]) AS $o) {
						$field['options'][ $o ] = $o;
					}
				} 
				if (!empty($f['fieldset'])) {
					$meta['app_fields']['fieldset1']['type'] = 'fieldset';
					$meta['app_fields']['fieldset1']['fields'][ $f['name'] ] = $field;
				} else {	
					$meta['app_fields'][ $f['name'] ] = $field;
				}									
			}
		}
		return $meta['app_fields']??null;	
	}

	protected function prepareButtons($app_buttons){
		foreach ( $app_buttons AS $f) {
			$f['name'] = $this->sanitizeFileName($f['name']);
			if ( $f['name'] AND !empty($f['label']) AND !empty($f['value']) ){
				$meta['app_buttons'][] = [
					'visibility' => $f['visibility']??'staff',
					'name' => $f['name'],
					'label' => $f['label'],
					'style' => $f['style']??'secondary',
					'value' => $f['value'],
				];
			}	
		}
		return $meta['app_buttons']??null;
	}

	protected function prepareSubApps($subapps) {
		foreach ( $subapps AS $sub) {
			if ( !empty($sub['name']) ){
				$key = $this->formatAppName(str_replace(' ', '_', $sub['name']) );
				$meta['app_sub'][ $key ]['alias'] = $this->formatAppLabel($sub['alias']??'');
				if (in_array($sub['entry'], ['single', 'multiple', 'quick', 'creator_readonly', 'other_readonly', 'client_readonly', 'staff_client_readonly', 'readonly']) ){
					$meta['app_sub'][ $key ]['entry'] = $sub['entry'];
				}
				if (in_array($sub['display'], ['single', 'table', 'grid', 'kanban', 'flat', 'threaded', 'client_hidden']) ){
					$meta['app_sub'][ $key ]['display'] = $sub['display'];
				}
			}
		}
		return $meta['app_sub']??null;	
   }

   protected function prepareAutomation($automation) {
      return;
   }

	public function generateRoutes($extra = []) {
		// Add route to install an appstore
		//$extra['install'] = ['POST', '/[i:site_id]/appstore/install.[json:format]?/[POST:id]?'];

		return parent::generateRoutes($extra);
	}
}
