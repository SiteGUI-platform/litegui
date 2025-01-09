<?php
namespace SiteGUI\Core\Traits;
use SiteGUI\Core\Lookup;
use \LiteGUI\User;
use \LiteGUI\Router;
use \LiteGUI\View;
use \Illuminate\Database\Capsule\Manager as DBM;

trait Application {
	use \LiteGUI\Traits\Helper;

	protected $user;
	protected $router;
	protected $db;
	protected $dbm;
	protected $redis;
	protected $lookup;
	protected $view;
	protected $site_id;
	protected $config;
	protected $table_prefix;
	protected $site_prefix;
	protected $table_user;
	protected $core_blocks;
	protected $requirements = [];
	protected $class;
	protected $handlers;
	protected $table;
	protected $path;

	public function app_construct($config, DBM $dbm, Router $router, View $view, User $user) {
		$this->redis   = $config['redis']??null;		
		$this->config  = $config;
		$this->user    = $user;
		$this->router  = $router;
		$this->view    = $view;
		$this->dbm     = $dbm;
		$this->db      = $dbm->getConnection();
		$this->site_id = $config['site']['id']??null;
		$this->table_prefix = $config['system']['table_prefix']; 
		$this->site_prefix  = $config['system']['table_prefix'] . $this->site_id;
		$this->table_user = !empty($config['site']['user_site'])? $this->table_prefix . intval($config['site']['user_site']) ."_user" : $this->site_prefix ."_user"; 
		$this->core_blocks = [
			'head',
			//'title',
			//'logo',
			//'menu',
			//'body', 
			'header',
			//'toolbar', 
			'spotlight', 
			'left', 
			'right', 
			'top', 
			'bottom', 
			'main', 
			'footnote', 
			'footer',
			//'script',
		 ];

		$this->class = (new \ReflectionClass($this))->getShortName(); //get the short name of the running class
	}

	//This is the final method, it can't be overridden in child classes. 
	//Classes use this trait can override it if they don't need to check for activation, bypass by owner is allowed
	//But classes extending another class that uses this trait can't override it to bypass checking  
	final public function checkActivation($app = '', $check_only = false) { //use $check_only to just check, no denial
		if ($this->site_id) {
			if (empty($app)) {
				$app = $this;
			}

			if (is_object($app)) {
				//get all parent classes;
				$checks = class_parents($app);
				//add this class to checks
				$app = get_class($app);			
			}
			$checks[ ] = $app; //string

			foreach ($checks as $key => $check) {
				if (isset($this->config['activation'][ $check ])) {
					unset($checks[ $key ]);
				}
			}
			//$checks = array_diff($checks, $this->config['activation']); //check against default apps
			if (!empty($checks)) {
				try {
					$enables = $this->db
						->table($this->site_prefix .'_config') //table_prefix must be overrided in child class
						->where('type', 'activation')
						->whereIn('name', $checks) 
						->pluck('description', 'name')
						->all();
					if (count($enables) != count($checks)) {
						if ( !$check_only ){
							$this->denyAccess($this->trans('use. Activation required: '). str_replace('\\', ' ➝ ', implode(', ', array_diff($checks, ($enables??[]) ))));
						}		
						return false;	
					}
				} catch (\Exception $e){
					return false;
				}				 	
			}
			return $enables[ $app ]??$this->config['activation'][ $app ]??true;	//return the activation settings	
		}
		return false; //true used to be here - but false is preferred (not checked)
	}	

	public function getRequirements() {
		return $this->requirements;
	}

	//Insert + Update multiple rows of multiple columns in any table
	protected function upsert($table, $columns, $data, $update = ['id' => 'id'], $central = false) {
		if ($table AND is_array($columns) AND is_array($data) ) {
			$count = count($columns); //number of columns
			$value = '('. rtrim(str_repeat('?,', $count), ',') .')';
			$values = rtrim(str_repeat($value .',', count($data)/$count), ','); //repeat x = no of values / no of columns
			
			$query  = 'INSERT INTO '. $table .'('. implode(', ', $columns) .') VALUES '. $values;
			$query .= ' ON DUPLICATE KEY UPDATE ';
			
			if (is_string($update)) {
				$query .= $update .' = VALUES('. $update .')'; //MySQL 8 VALUES(value) -> new.value
			} else {
				foreach ($update as $key => $col) {
					$update[ $key ] = is_numeric($key)? $col .' = VALUES('. $col .')' : $key .' = '. $col; //support [col => another col]
				}
				$query .= empty($update)? $columns[0] .' = '. $columns[0] : implode(',', $update); //empty $update? set 1st col = 1st col
			}	
			//echo $query; return true;			
			return $central? $this->dbm->getConnection('central')->insert($query, $data) : $this->db->insert($query, $data);
		}
		return false;
	}
	//Clone table rows and change value of an INT column - others are not supported to reduce SQL INjection risk
	//columns are all but id and $where
	protected function cloneRows($table, $type, $columns, $where, $old_id, $new_id){
		$query  = 'INSERT INTO '. $table .' ('. $where .', '. $columns .')';
		$query .= ' SELECT '. intval($new_id) .', '. $columns .' FROM '. $table .' WHERE '. $where .'='. intval($old_id);
		if ( !empty($type) ){
			//$type can be one or multiple app_type (array)
			$type = is_array($type)? 'app_type IN ("'. implode('", "', $type) .'")' : 'app_type = "'. $type .'"';

			$query .= ' AND '. $type;
		}
		return $this->db->statement( $this->db->raw($query) );
	}
	//Retrieve specified system config
	protected function getSystemConfig($property, $type = '', $object = '') {
		if (!empty($property)) {
			$query = $this->db->table($this->table_prefix .'_system')
							  ->where('property', $property);
			if (!empty($type)) {
				$query = $query->where('type', $type);
			}				
			if (!empty($object)) {
				$query = $query->where('object', $object);
			}	

			return $query->value('value');			    		
		}
		return NULL;
	}

	//Add specified system config
	protected function setSystemConfig($input) {
		if (!empty($input['property']) AND isset($input['value'])) { //empty value is ok
			foreach($input as $key => $value) {
				if (in_array($key, ['type', 'object', 'property', 'value', 'name', 'description'])) {
					$columns[ ] = $key;
					$data[ ]	= $value;
				}
			}	
			//$query = 'INSERT INTO '. $this->table_prefix .'_system('. $keys .') VALUES ('. $values .') ON DUPLICATE KEY UPDATE value = VALUES(value)'; //table_prefix may be overridden in child class
			return $this->upsert($this->table_prefix .'_system', $columns, $data, ['value', 'name', 'description'], 'central');
		}  
		return false;		
	}

	//Lookup by value
	protected function lookupByValue($lookup, $value = '', $scope = null) {
      if ($lookup == 'activation') {//lookup key
         if ( $scope){
            $scope = str_replace('Scope::', '', $scope);
         }
         foreach ($this->config['activation']??[] AS $class => $v){
            if ($scope AND !str_contains($class, '\\'. $scope .'\\')){
               continue;
            }
            if ($value AND !str_contains(end(explode('\\', $class)), $value)){
               continue;
            }   
            $rows[] = [end(explode('\\', $class)), end(explode('\\', $class)) ];
         }    
      } else {
         $rows = $this->db->table($this->site_prefix .'_user')
            ->where('name', 'LIKE', '%'. $value .'%')
            ->take(500)
            ->get()
            ->all();
      }   
      return ['rows' => $rows];
	}
	//Lookup by id
	protected function lookupById($lookup, $value) {
		if ($lookup) {//lookup key
         $rows = $this->db->table($this->site_prefix .'_user')
                          ->where('id', $value)
                          ->select('id', 'name', 'image') 
                          ->first(); 
         return ['rows' => $rows];
		}
	}

	//Lookup name and email of user by the Id
	protected function lookupUsers($ids) {
		if ($ids) {
			$users = $this->db->table($this->table_user .' AS user')
				->when(is_array($ids), function ($query) use ($ids) {
					return $query->whereIn('id', $ids);
				}, function ($query) use ($ids) {
					return $query->where('id', $ids);
				})	
				->whereIn('status', ['Active', 'Unverified'])
				->select('user.id', 'user.name', 'user.email', 'user.mobile', 'user.image', 'user.language')
				->get()
				->all();
			if ($users) {
				return is_array($ids)? array_column($users, NULL, 'id') : $users; //re-index id=>record
			}
		}
	}
	//Lookup name and email of staff specified by email
	protected function lookupAdminByEmail($email) {
		if ($email) {
			return $this->dbm->getConnection('central')
			->table($this->table_prefix .'_admin AS staff')
			->join($this->table_prefix .'1_user AS user', 'user_id', '=', 'user.id')
			->where('site_id', !empty($this->config['site']['role_site'])? intval($this->config['site']['role_site']) : $this->site_id)
			->where('email', $email)
			->where('staff.status', 'Active')
			->select('user.id', 'user.name', 'user.email', 'user.mobile', 'user.image', 'user.language')
			->first();
		}
	}
	//Lookup name and email of staff by the Id
	protected function lookupAdmins($ids) {
		if ($ids) {
			$users = $this->db//m->getConnection('central')
				->table($this->table_prefix .'_admin AS staff')
				->join($this->table_prefix .'1_user AS user', 'user_id', '=', 'user.id')
				->where('site_id', !empty($this->config['site']['role_site'])? intval($this->config['site']['role_site']) : $this->site_id)
				->when(is_array($ids), function ($query) use ($ids) {
					return $query->whereIn('user.id', $ids);
				}, function ($query) use ($ids) {
					return $query->where('user.id', $ids);
				})	
				->where('staff.status', 'Active')
				->select('user.id', 'user.name', 'user.email', 'user.mobile', 'user.image', 'user.language')
				->distinct() //due to multiple roles
				->get()
				->all();
			if ($users) {
				return is_array($ids)? array_column($users, NULL, 'id') : $users; //re-index id=>record
			}
		}
	}
	//Lookup name and email of staff/user by the Id
	protected function lookupCreators($ids) {
		if ($ids) {
			$users = $this->db
				->table($this->table_prefix .'_admin AS staff')
				->join($this->table_prefix .'1_user AS user', 'user_id', '=', 'user.id')
				->where('site_id', !empty($this->config['site']['role_site'])? intval($this->config['site']['role_site']) : $this->site_id)
				->when(is_array($ids), function ($query) use ($ids) {
					return $query->whereIn('user.id', $ids);
				}, function ($query) use ($ids) {
					return $query->where('user.id', $ids);
				})	
				->where('staff.status', 'Active')
				->select('user.id', 'user.name', 'user.email', 'user.mobile', 'user.image', 'user.language')
				->distinct() //due to multiple roles
				->get()
				->all();
			if ($users) {
				return is_array($ids)? array_column($users, NULL, 'id') : $users; //re-index id=>record
			}
		}
	}	
	//Lookup name and email of staff that has the specified permission, this excludes SystemAdmin role
	protected function lookupAdminsByPermission($permission) {
		if ($permission) {
			$users = $this->dbm->getConnection('central')
				->table($this->table_prefix .'_admin AS staff')
				->join($this->table_prefix .'1_user AS user', 'user_id', '=', 'user.id')
				->join($this->table_prefix .'_system AS config', 'role_id', '=', 'config.id')
				->where('site_id', !empty($this->config['site']['role_site'])? intval($this->config['site']['role_site']) : $this->site_id)
				->where(function($query) use ($permission) {
					$query->where('config.value', 'LIKE', '%,'. $permission .',%')
						->orWhere('config.value', 'LIKE', '%,Role::SystemAdmin,%'); //hardcode to avoid filling up apps' perm
				})
				->where('config.type', 'role')
				->where('staff.status', 'Active')
				->select('user.id', 'user.name', 'user.email', 'user.mobile', 'user.image', 'user.language')
				->groupBy('user_id')
				->get()
				->all();
			if ($users) {
				return array_column($users, NULL, 'id'); //re-index id=>record
			}
		}
	}	
	//Lookup related, published (app) pages  with correct slug for frontend 
	protected function lookupRelatedPages($ids, $type) {
		if ($ids) {
			if ($type == 'Creator') { //should use lookupById instead, not yet return image for creator and staff (tbd)
				return $this->db->table($this->table_user)
					->when(is_array($ids), function ($query) use ($ids) {
						return $query->whereIn('id', $ids);
					}, function ($query) use ($ids) {
						return $query->where('id', $ids);
					})
					->pluck('name', 'id')
					->all();				
			} elseif ($type == 'Staff') {
				return $this->db->table($this->table_prefix .'1_user')
					->when(is_array($ids), function ($query) use ($ids) {
						return $query->whereIn('id', $ids);
					}, function ($query) use ($ids) {
						return $query->where('id', $ids);
					})
					->pluck('name', 'id')
					->all();				
			} elseif ($type == 'Variant') {
				$type = 'Product';
				$subtype = null;
				$query = $this->db->table($this->site_prefix .'_product AS variant')
					->join($this->site_prefix .'_page AS page', 'variant.pid', '=', 'page.id' )
					->when(is_array($ids), function ($query) use ($ids) {
						return $query->whereIn('variant.id', $ids);
					}, function ($query) use ($ids) {
						return $query->where('variant.id', $ids);
					})
					->select('variant.id');				
			} else {
				if ( in_array($type, ['Page', 'Product', 'Collection', 'Profile', 'Freelance']) ){
					$subtype = null;
				} else {
					$subtype = $type;
					$type = 'App';
				}
				$query = $this->db->table($this->site_prefix .'_page AS page')
					->when(is_array($ids), function ($query) use ($ids) {
						return $query->whereIn('id', $ids);
					}, function ($query) use ($ids) {
						return $query->where('id', $ids);
					})
					->select('id');
			}
			$query = $query
				->when($type, function ($query, $type) {
					return $query->where('type', $type);
				})
				->when($subtype, function ($query, $subtype) {
					return $query->where('subtype', $subtype);
				})	
				->addSelect('name', 'slug', 'description', 'image', 'type', 'subtype');
			$query = $this->getPublished($query);
			
			$pages = $query->get()->all();		
			foreach ($pages AS $key => $page){
				$pages[ $key ]['name'] = $this->getRightLanguage(json_decode($page['name']??'', true));
				$pages[ $key ]['slug'] = $this->url($page['slug'], $page['type'], $page['subtype']);
				$pages[ $key ]['description'] = $this->getRightLanguage(json_decode($page['description']??'', true));
				//$pages[ $key ]['image'] = $page['image'];
			}	
			return $pages??null;
		}
	}
	//Retrieve self config of parent class (such as User), inherited class (Staff) should use getAppConfigValues
	protected function getMyConfig($property = '', $type = 'config') {
		$config = self::config();
		$app = $this->trimRootNs(__CLASS__); //remove root namespace
		return $this->getAppConfigValues($app, $config['app_configs'], $property, $type);
	}

	//get the values stored for each site (no store no value, wont return default values), 
	//$fields required for format the value (password, lookup value), lookup value will change to array[value] = name
	protected function getAppConfigValues($app_name, $fields, $property = '', $type = 'config', $internal_config = true) {
		try {
			$query = $this->db->table($this->site_prefix .'_config')
				->where('type', $type)
			  	->where('object', $app_name);
			if ($property){
				$query = $query->where('property', $property);
			}

			$stored = $query->pluck('value', 'property')->all(); // [ property => value, ...]	
			foreach ($stored AS $key => $value) { //config_app_sub/automation are special property	
				if ( in_array($key, ['config_app_fields', 'config_app_columns', 'config_app_buttons', 'config_app_sub', 'config_app_automation']) ){
					if ( $internal_config ){ //only load when requested only
						$stored[ $key ] = json_decode($value??'', true);
					} else {
						unset($stored[ $key ]);
					}	
					continue;
				} elseif ( empty($fields[ $key ]['type']) OR empty($value) ){
					continue; 
				} 
				
				if ( $fields[ $key ]['type'] == 'password' AND !empty($value) ){
					$stored[ $key ] = $this->decode($value, 'static');
				} elseif ( $fields[ $key ]['type'] == 'lookup' ){
					$stored[ $key ] = $this->lookupById($key, $value)['rows']??[ $value => $value ];
				} elseif ( $fields[ $key ]['type'] == 'image' OR $fields[ $key ]['type'] == 'file' ){
					$stored[ $key ] = json_decode($value??'', true);
				} elseif ( $fields[ $key ]['type'] == 'fieldset' ){
					$stored[ $key ] = json_decode($value??'', true);
					//decode password in fieldset[fields]
					foreach ($stored[ $key ] AS $index => $fieldset) {
						foreach ($fieldset AS $key2 => $value2) {
							if ( empty($fields[ $key ]['fields'][ $key2 ]['type']) OR empty($value2) ) continue;

							if ( $fields[ $key ]['fields'][ $key2 ]['type'] == 'password' AND !empty($value2) ) {
								$stored[ $key ][ $index ][ $key2 ] = $this->decode($value2, 'static');
							} elseif ( $fields[ $key ]['fields'][ $key2 ]['type'] == 'lookup' ){
								$stored[ $key ][ $index ][ $key2 ] = $this->lookupById($key2, $value2)['rows']??
									[ $value2 => $value2 ];
							}
						}	
					}	
				}
			}
			if ( $property ){
				return $stored[ $property ]??null;
			} else {
				return $stored;
			}	
		} catch (\Exception $e){
			return null;
		}	
	}

	protected function getAppConfigFields($app) {
		$config['app_configs'] = $app['app_configs']??[]; //just need unioned app_configs only
		$this->getRemoteAppInfo($app, $config);
		return $config['app_configs'];
	}	

	//load (but not override) remote app info into targeted $config (referenced), avoid loading into $app
	protected function getRemoteAppInfo($app, &$config) {
		if ( !empty($app['remote_info']) ) return; //already loaded

		if ( isset($app['app_handler']) AND substr($app['app_handler'], 0, 4) === 'http') {
			$site_config['site'] = $this->config['site'];
		   	//get stored config value i.e: token to access remote app
		   	$site_config['app'] = $this->getAppConfigValues($app['subtype'] .'\\'. $app['name'], $app['app_configs']??[] );
			foreach ( ($site_config['app']??[]) AS $target => $value) {
				if ( str_starts_with($target, 'Header__') ){ //header field
					$headers[] = substr($target, 8) .': '. $value;
					unset($site_config['app'][ $target ]);
				} 
			}			

			$remote = $this->httpPost($app['app_handler'] . 'config', [
				"json"=> json_encode(['site_config' => $site_config])
			], $headers??null);
			$remote = json_decode($remote??'', true);
			if ( isset($remote['result']) AND $remote['result'] === 'success' AND !empty($remote['config']) ) {
				foreach( ($remote['config']??[]) AS $key => $value) {
					//Appstore config overrides remote config
					if (is_array($value)){
						$config[ $key ] = ($config[ $key ]??[]) + $value; //union, not override Appstore config
					} elseif ( empty($app[ $key ]) ){
						$config[ $key ] = $value; //add remote config if Appstore config does not have it
					} 
				}
			}
		}
	}

	//Return App info for Appstore and default app - does not contain config value stored for each site
	protected function getAppInfo($slug, $subtype = 'App', $load_remote_info = false) {
		$slug = strtolower($slug); //$slug is always lowercase to make it unique to prevent loading unintended class file
		if (empty($this->handlers[ $subtype ][ $slug ])) {
			$class = str_replace('Core\Traits', $subtype, __NAMESPACE__) .'\\'. $this->formatAppName($slug);
			$app = [];
			if ( !$app AND array_key_exists($class, $this->config['activation']) ){//default app (not registered on Appstore)
				$app['slug'] = $slug;
				$app['subtype'] = $subtype;
				$app['label'] = $this->config['activation'][ $class ][4]??$this->formatAppLabel($app['slug']);
			}

			if ($app) {
				$app['name'] = $this->formatAppName($app['slug']); //must be set as many operations depend on it
				//previous $class may be sitegui/id/138
				$app['class'] = $class = str_replace('Core\Traits', $app['subtype'], __NAMESPACE__) .'\\'. $app['name'];
				//merge hardcode/remote/appstore configs in that order into one config => then union with $app to keep app's id/subtype/class intact
				if (class_exists($class) AND method_exists($class, 'config')) {
					$config = $class::config();
					if ( isset($config['result']) AND $config['result'] === 'success' AND !empty($config['config']) ) {
						$config = $config['config'];
					}	
				}

				//remote app can use getAppConfigFields to merge config fields as we dont want to slow down getAppInfo => remote app can store config fields on Appstore
				if ($load_remote_info){
					$this->getRemoteAppInfo($app, $config);	
					$app['remote_info'] = true;		
				}

				if ( !empty($app['id']) ){ //Appstore App, load additional app configs - no config value				
					$meta = $this->db
						->table($this->table_prefix .'_system')
						->where('type', 'app_meta')
						->where('object', $app['id'])
						->pluck('value', 'property')
						->all();
					foreach( ($meta??[]) AS $key => $value) {
						if ($value === NULL){
							continue; //if not set, keep current $app config	
						} 
						$meta[$key] = json_decode($value??'', true); //both json and string are ok, except null
						//Appstore config overrides hardcode/remote config
						if (is_array($meta[$key]) ){
							$config[ $key ] = $meta[$key] + ($config[ $key ]??[]); //merge, when Appstore config is null, keep hardcode/remote  
						} elseif ( $meta[$key] === NULL ) {
							$config[ $key ] = $value; //override with original value
						} else {	
							$config[ $key ] = $meta[$key]; //override with decoded value
						}
					}		
				} 
				//get per-site app_sub and merge with config's app_sub
				if ( $app['subtype'] == 'App' OR !empty($config['subapp_support']) ){
					$persite = $this->db
						->table($this->site_prefix .'_config')
						->where('type', 'config')
						->where('object', $app['subtype'] .'\\'. $app['name'])
						->whereIn('property', ['config_app_fields', 'config_app_columns', 'config_app_buttons', 'config_app_sub', 'config_app_automation'])
						->pluck('value', 'property');
					if ( !empty($persite['config_app_fields']) ){
						$persite['config_app_fields'] = json_decode($persite['config_app_fields']??'', true);
						$config['app_fields'] = array_merge($config['app_fields']??[], $persite['config_app_fields']);
					}
					if ( !empty($persite['config_app_columns']) ){
						$persite['config_app_columns'] = json_decode($persite['config_app_columns']??'', true);
						$config['app_columns'] = array_merge($config['app_columns']??[], $persite['config_app_columns']);
					}
					if ( !empty($persite['config_app_buttons']) ){
						$persite['config_app_buttons'] = json_decode($persite['config_app_buttons']??'', true);
						$config['app_buttons'] = array_merge($config['app_buttons']??[], $persite['config_app_buttons']);
					}
					if ( !empty($persite['config_app_sub']) ){
						$persite['config_app_sub'] = json_decode($persite['config_app_sub']??'', true);
						$config['app_sub'] = array_merge($config['app_sub']??[], $persite['config_app_sub']);
					}
					if ( !empty($persite['config_app_automation']) ){
						$persite['config_app_automation'] = json_decode($persite['config_app_automation']??'', true);
						$config['app_automation'] = array_merge_recursive($config['app_automation']??[], $persite['config_app_automation']);
					}
				}

				//IMPORTANT: $app['class'] should not be tampered by user $config
				$this->handlers[ $app['subtype'] ][ $app['slug'] ] = $app + ($config??[]); //union to keep id/slug/subtype/class intact	
				if ($subtype == 'id'){ //also remember by id
					$this->handlers[ $subtype ][ $slug ] = $this->handlers[ $app['subtype'] ][ $app['slug'] ];
				}	
			}				
		}

		if (!empty($slug) AND !empty($this->handlers[ $subtype ][ $slug ])) {
			return $this->handlers[ $subtype ][ $slug ];
		} else {
			return false;
		}
	}

	protected function appProcess($app, $action, $page){
	   	//get app config value
		$site_config['site'] = $this->config['site'];
	   	$site_config['app'] = $this->getAppConfigValues($app['subtype'] .'\\'. $app['name'], $app['app_configs']??[], null, 'config', false); //dont load internal app_config_automation
	   	unset($app['app_automation']); //$app['app_automation'] may contain api key information

		if (substr($app['app_handler'], 0, 4) === 'http') {//remote app
			$this->checkActivation($app['class']);	
			foreach ( ($site_config['app']??[]) AS $target => $value) {
				if ( str_starts_with($target, 'Header__') ){ //header field
					$headers[] = substr($target, 8) .': '. $value;
					unset($site_config['app'][ $target ]);
				} 
			}			
			$response = $this->httpPost($app['app_handler'] . $action, [
				"json"=> json_encode(['site_config' => $site_config] + $page)
			], $headers??null);
			//print_r($response);
			$response = json_decode($response??'', true);
		} elseif ($app['app_handler'] === 'Builder') {//app builder's app
			$this->checkActivation($app['class']);	
			$Class = str_replace('Core\Traits', 'App', __NAMESPACE__) .'\\Builder';
			$site_config['lang'] = $this->config['lang']??[];		
			$instance = new $Class($site_config, $this->view);
			$response = call_user_func([$instance, $action], $page);
		} else {
			$Class = $app['class']; 
			if (class_exists($Class)) {
				$site_config['lang'] = $this->config['lang']??[];		
				$instance = new $Class($site_config, $this->view);
				$this->checkActivation($instance);
				$response = call_user_func([$instance, $action], $page);
			} 
		}

		if (isset($response['result']) AND $response['result'] === 'success') {
			if ( ($action == 'update' OR $action == 'clientUpdate') AND !empty($app['app_permissions']['page_read']) AND !empty($app['app_permissions']['page_read_permission']) ){ //page level permission
				$key = $app['app_permissions']['page_read_permission'];
				if ($app['app_permissions']['page_read'] == 'static') {//map to permission directly
					$response['page']['private'] = $key;
				} elseif ($app['app_permissions']['page_read'] == 'dynamic' AND !empty($site_config['app']['fieldset1'])) {
					//based on input
					foreach ($site_config['app']['fieldset1'] as $value) {
						$mapping[ $value[ $key ] ] = $value['permission']??''; //Tech => Role::Engineer
					}
					//get the value of meta_key e.g: Tech and find the permission from the mapping e.g: Role::Engineer
					if (!empty($response['page']['meta'][ $key ]) OR !empty($response['page']['meta'][ strtolower($app['name']) .'_'. $key ]) ){
						$response['page']['private'] = $mapping[ $response['page']['meta'][ $key ]??$response['page']['meta'][ strtolower($app['name']) .'_'. $key ] ]??'';
					}	
				}
			}
			unset($response['result']);
			return $response;
		} 
		return [];
	}

	protected function registerHook($hook_name, $params) {
		if ( !empty($this->config['hooks'][ $hook_name ]) AND is_array($this->config['hooks'][ $hook_name ]) ){
			$params[] = $this->config['site']; //add site info for global hook 
			foreach ($this->config['hooks'][ $hook_name ] as $index => $hook) {
				//call_user_func_array($hook, [$id]);
				$next_actions[ $hook_name .'_hook'. $index ] = is_array($hook)? $hook : ["target" => $hook, "params" => $params];
			}
			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions, 'prepend');	
			}				
		}
	}

	//run hook for each site, system hook should use global hook
	//$params may contains references and hook definition also use reference to change injected params
	//Hook may add blocks by changing injected $blocks or return $blocks if the hook point allows hook to add_blocks
	//public method so we can use worker to run
	public function runHook($hook_name, $params, $silent = false, $add_blocks = false, $object = null, $run = false) {
		try {
			if ( $silent AND !$add_blocks AND !empty($this->redis) ){ 
				//asynchronous, add to (staff or client) queue: user_id, hook and data
				$this->enqueue($this->class .'::'. __FUNCTION__, $hook_name, $params, false, false, $object); 
			} else {//synchronous: run immediately
				//register global hooks, site info will be added to $params
				$this->registerHook($hook_name, $params);
				//site hook
				if ($object){ //just run this Hook object
					$hooks[] = $object;
				} else {
					$hooks = $this->db->table($this->site_prefix .'_config')
						->where('type', 'config')
						->where('object', 'LIKE', 'Hook\\\%')
						->where('property', 'hook_'. $hook_name) //this is a config field in Hook apps
						->where('value', 1)
						->orderBy('order')
						->pluck('object')
						->all();
				}		
				//print_r($hooks);			
				foreach ($hooks??[] AS $object){
					$app = $this->getAppInfo(substr($object, 5), 'Hook');
					$Class = str_replace('Core\Traits', '', __NAMESPACE__) . $object;
		   			if ( !empty($app['class']) AND !$this->checkActivation($app['class'], true) ){

		   			} elseif (!empty($app['app_handler']) AND str_starts_with($app['app_handler'], 'http') ){//remote app
						//get app config value
						$site_config['site'] = $this->config['site'];
			   			$site_config['app'] = $this->getAppConfigValues($app['subtype'] .'\\'. $app['name'], $app['app_configs']??[], null, 'config', false); //dont load internal app_config_automation
			   			unset($app['app_automation']); //$app['app_automation'] may contain api key information

						foreach ( ($site_config['app']??[]) AS $target => $value) {
							if ( str_starts_with($target, 'Header__') ){ //header field
								$headers[] = substr($target, 8) .': '. $value;
								unset($site_config['app'][ $target ]);
							} 
						}			
						$response = $this->httpPost($app['app_handler'] . (str_contains($app['app_handler'], '?')? '&' : '?') . 'hook='. $hook_name, [
							"json"=> json_encode(['site_config' => $site_config] + $params)
						], $headers??null);
						//print_r($response);
						$response = json_decode($response??'', true);	
					} elseif (class_exists($Class) AND method_exists($Class, 'dispatch') ){ //no need to getAppInfo()
						$app_configs = $Class::config('app_configs');
						$site_config['app'] = $this->getAppConfigValues($object, $app_configs);

						if ( !empty($app_configs['oauth']['type']) AND $app_configs['oauth']['type'] == 'oauth' ){
							$site_config['app']['app_secret'] = $this->db->table($this->table_prefix .'_system')
								->where('type', 'app_secret')
								->where('property', 'LIKE', $object .'::%') //as we dont have $app['id'] here
								->value('value'); 
							if ($site_config['app']['app_secret']){
								$site_config['app']['app_secret'] = $this->decode($site_config['app']['app_secret'], 'static');
							}
						}	
						$site_config['site'] = $this->config['site'];

						$instance = new $Class($site_config);
						$response = $instance->dispatch($hook_name, $params);
						if ( $add_blocks AND !empty($response['blocks']) ){ //dont add blocks with remote hooks
							foreach ( ($response['blocks']??[]) AS $section => $block) {
								$this->view->addBlock($section, $block, $object .'@'. $hook_name);
							}	
						}
					}

					if ( !empty($response) AND !$silent ){ 
						$this->view->setStatus([
							'result'  => $response['result']??null,
							'message' => $response['message'],
						]);
					}

		            if ( !empty($response['result']) || !empty($response['message'][0]) ){
			            //As runHook process custom inputs, we can only catch the app and id sometimes
			            //$job['params'][0] is hook_name, $job['params'][1] is the hook params, $job['params'][1][0] is the 1st hook param
			            if ( !empty($params[0]['id']) AND str_contains($hook_name, '::') ){
				            if ( str_starts_with($hook_name, "Product::") ){ //Product	
				            	$type = 'Product.';
				            } elseif ( str_starts_with($hook_name, "App::") ){
				            	$type = strtok(substr($hook_name, 5), '::');
				            } else {
				            	$type = strtok($hook_name, '::') .'.';
				            }
			            } else {
			            	$type = 'Hook.';
			            }

			            $app_id = $params[0]['id']??null;
				        if (empty($response['result']) || $response['result'] != 'error'){
				        	$this->logActivity($response['message'][0]??'', $type, $app_id, 'Info');
			    			if (is_numeric($run)){ //retry with log_id
								$this->markPendingJobDone($run);
							}
						} else {				        	
							$callable['log_id'] = $run; //$run = $log_id when retrying, false as 1st run
					        $callable['target'] = $this->class .'::'. __FUNCTION__;
					        $callable['params'] = [ $hook_name, $params, false, false, $object ];
				            $this->logPendingJob($callable, $type, $app_id); //for retrying later
						}
				    }        
				}
			}
		} catch(\Exception $e){

		}
	}

	//send to queue to process asynchronously, this can accept any params
    protected function enqueue($target, ...$params){
    	if ( empty($this->redis) ){
    		return false;
    	}
        $data['site']['id'] = $this->config['site']['id'];
        $data['user']['id'] = $this->user->getId();
        $data['triggered'] = time();
        $data['target'] = $target; //run synchronously
        $data['params'] = $params;
        $queue_type = $this->user->isStaff()? 'staff' : 'client';

        return $this->redis->rPush("queue_". $queue_type, json_encode($data));
    }
    	
    protected function logPendingJob($callable, $app_type = null, $app_id = null){
        if (empty($callable['log_id']) OR !is_numeric($callable['log_id']) ){ //when running immediately $run/log_id is 'run'
	        $log = [
	            'app_type' => $app_type,
	            'app_id' => $app_id,
	            'level' => $this->user->isStaff()? 'Staff' : 'Client',
	            'creator' => $this->user->getId(),
	            'created' => time(),
	            'retry' => 0,
	            'meta' => json_encode($callable)
	        ];  

	        $log['id'] = $this->db
	            ->table($this->site_prefix .'_activity')
	            ->insertGetId($log);
	    } else {
	    	$log = $this->db
	            ->table($this->site_prefix .'_activity')
	            ->where('id', $callable['log_id'])
	            ->select('id', 'retry', 'created')
	            ->first();
	        if ( $log && $log['retry'] < 5){ //no retry after 10.4h 
	   	        $this->db
		            ->table($this->site_prefix .'_activity')
		            ->where('id', $callable['log_id'])
		            ->update([
		            	'retry' => intval($log['retry']) + 1
		            ]); 
		    }           
	    } 
	    if (!empty($log['id']) AND $log['retry'] < 5 AND !empty($this->redis) ){
	    	//add to queue to track, 1st retry 30 seconds later, use site_id.log_id as key/member
	    	$interval = 300 * pow(5, ($log['retry'] - 1)); //1m, 5m, 25m, 2.1h, 10.4h, 2.2 days
        	$this->redis->zAdd('retry_jobs_'. ($this->user->isStaff()? 'staff' : 'client'), $log['created'] + $interval, $this->config['site']['id'] .'.'. $log['id']);
	    }      
      	return $log['id']??false;
    }
    protected function markPendingJobDone($log_id){
    	return $this->db
            ->table($this->site_prefix .'_activity')
            ->where('id', $log_id)
            ->update([
            	'processed' => time(),
            	'level' => 'Info'
            ]); 
    }

    public function logActivity($message = '', $app_type = null, $app_id = null, $level = 'Info', $changes = null, $created = null, $run = false) {
        if (empty($message)) return;
        if ( !$run AND !empty($this->redis)){
            return $this->enqueue($this->class .'::'. __FUNCTION__, $message, $app_type, $app_id, $level, $changes, time(), 'run');
        } else {
	        $log = [
	            'app_type' => $app_type,
	            'app_id' => $app_id,
	            'message' => $message,
	            'level' => $level,
	            'creator' => $this->user->getId(),
	            'created' => $created??time(),
	            'processed' => $created??time(),
	        ];
	        if (!empty($changes) ){ 
	            $log['meta'] = json_encode($changes);  
	        }     
	        //print_r($log);
            return $this->db->table($this->site_prefix .'_activity')
                ->insert($log);
        }
    }

	//Pagination: less than 30 records -> return all with no rowCount. 
	//More than that or 'current' present, return pagination with rowCount, rows is empty with web access as they're loaded via ajax 
	protected function pagination($query, $row_count = 12) {
		//current=1&rowCount=10&sort[sender]=asc&searchPhrase=
		if ( !empty($_REQUEST['searchPhrase']) ){
			//$search = '%'. filter_var($_REQUEST['searchPhrase'], FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_HIGH) .'%';
			$search = '%'. trim(str_replace('\\', '\\\\', json_encode($_REQUEST['searchPhrase'])), '"') .'%'; //utf-8 search
			$query = $query->when( $search, function($query) use ($search) {
				//print_r($query->columns);
				foreach( $query->getColumns()??[] AS $column){
					if (substr($column, -4) == 'name') { //column ends with 'name'
						return $query->where(explode(' AS ', $column)[0], 'LIKE', $search);
					}
				}
				//if no column 'name', use the 2nd column for search, 1st is usually id
				return $query->where(explode(' AS ', $query->columns[1])[0], 'LIKE', $search)->where('a', 1);
			});
			$block['html']['searchPhrase'] = $_REQUEST['searchPhrase'];
		}
		//count total records, count has issue with groupBy 	
		$block['api']['total'] = ($query->groups)? $query->get()->count() : $query->count();
		/*provide some agregate if ($query->groups) {
			$query = $query->get();
			$block['api']['total'] = $query->count();
			$block['api']['avg_status'] = $query->avg('status');
			$block['api']['sum_status'] = $query->sum('status');			
		} else {
			$block['api'] = $query->selectRaw('COUNT(*) AS total, AVG(page.status) AS avg_status, SUM(page.status) AS sum_status')->first();
		}*/
		if ( !empty($_REQUEST['sort']) AND 
			str_contains( implode(',', $query->getColumns())??'', key($_REQUEST['sort']) ) AND //contains instead of = due to column alias 
			in_array( current($_REQUEST['sort']), ['asc', 'desc']) ){
				$sort['column'] = key($_REQUEST['sort']); //use key/current to get key/value (pointer at first element)
				$sort['order'] = current($_REQUEST['sort']);
		} else {
			$col = $query->getColumns()[0]??'';
			$sort['column'] = str_contains($col, ',')? strtok($col, ',') : $col;
			$sort['column'] = strtok($sort['column'], ' '); //"this_column AS smth" => this_column
			if ( !empty($query->unions) AND str_contains($sort['column'], '.') ){
				$sort['column'] = substr($sort['column'], strpos($sort['column'], ".") + 1); // site.id => id due to union not using alias
			}
			$sort['order'] = 'desc';
		}
			
		$block['api']['current'] = empty($_REQUEST['current'])? 1 : intval($_REQUEST['current']);
		if ( !empty($_REQUEST['rowCount']) ){
			$block['api']['rowCount'] = ($_REQUEST['rowCount'] < 0 OR $_REQUEST['rowCount'] > 500)? 500 : intval($_REQUEST['rowCount']); //hard limit 500
		} else {
			$block['api']['rowCount'] = $row_count;
		}

		if ( $block['api']['total'] == 0 OR (empty($_REQUEST['current']) AND $this->view->html) ){ 
			//web access - rows (if any) will be provided via api
			$block['api']['rows'] = []; 
		} else { //api or specified current 
			$block['api']['rows'] = $query->skip( ($block['api']['current'] - 1) * $block['api']['rowCount'] )
				->take( abs($block['api']['rowCount']) ) //rowCount may be set to -1
				->when( $sort, function($query) use ($sort) {
					//column needs sanitized to avoid SQL injection, order is already filtered to asc/desc
					return $query->orderBy($this->sanitizeFileName($sort['column']), $sort['order']);
				})
				->get()->all();	
		}

		return $block; 		
	}
	// common method for App Main, get meta fields and lookup, sorting etc 
	// $show_links: for staff only 
	// $strip_tags: for subpages only 
	protected function prepareMain($query, $app, $show_links = null, $strip_tags = false) {
		if ( !empty($app['app_columns']) ){ //add default column to query first
			if ( !empty($app['app_columns']['creator']) ){ 
				//check if table user has been initialized
				if ($this->config['site']['user_site'] ?? $this->db->table('information_schema.tables')
					->where('table_schema', $this->db->raw('DATABASE()') )
					->where('table_name', $this->site_prefix .'_user')
					->get('table_schema')
					->all()
				){
					$show_creator = $app['app_columns']['creator'];					
					unset($app['app_columns']['creator']); //handle differently
				}		
			}

			foreach ($app['app_columns'] AS $property => $name)	{
				//we no longer need to remove app slug $field = str_replace($app['slug'] .'_', '', $property); 
				$field = $property;
				//if there are lookup values, note it for processing
				if ( !empty($app['app_fields'][ $field ]) AND ($app['app_fields'][ $field ]['type'] == 'lookup' OR !empty($app['app_fields'][ $field ]['options']['From::lookup']) ) ){
					if ($app['app_fields'][ $field ]['type'] == 'lookup') {
						$column[ $field ] = $field; // ticket_staff => staff
					} else {
						$column[ $field ] = array_values($app['app_fields'][ $field ]['options'])[1]; //ticket_sth => creator
					}
				}
				if ( in_array($property, $this->changeable) ){ //default columns
					if ( !in_array($property, ['id', 'name', 'created', 'updated', 'collection', 'collection_replace', 'published_at', 'page_linking_only']) ){ //already selected - issue with union, or not db column
						$query = $query->addSelect('page.'. $property);
					}	
				} else { //meta columns
					$properties[] = $field;
					//prepare for orderBy to avoid SQL injection
					$col_order[] = $this->sanitizeFileName($field);
				}
			}
			if ( !empty($show_creator) ){ //union query for site other than site1
				//$table_page = $this->site_prefix ."_page";
				//$lang = $this->sanitizeFileName($this->config['site']['language']);
				$query = $query->addSelect('page.creator', 'user.handle AS creator_handle', 'user.name AS creator_name', 'user.image AS creator_avatar');
					/*->when(!$show_links, function($query) use($table_page, $lang) { //client/guest will use profile if exist
						return $query->selectRaw("COALESCE( JSON_UNQUOTE( JSON_EXTRACT(profile.name, '$.". $lang ."')), user.name) AS creator_name")
							->leftJoin($table_page .' AS profile', function($join){
								$join->on('page.creator', '=', 'profile.creator')
									->where('profile.type', 'Profile');
							});
					});*/
				if ($this->config['site']['id'] != 1 AND ( empty($this->config['site']['user_site']) OR $this->config['site']['user_site'] != 1) ){ 
					//clone the above query and inner join with table user, original query join with table staff below
					$query = $query->unionAll(
						(clone $query)->join($this->table_user .' AS user', 'page.creator', '=', 'user.id') //valid user
					);
				}	
				$query = $query->join($this->table_prefix .'1_user AS user', 'page.creator', '=', 'user.id'); //must use join, leftJoin will produce duplicate
			}
		}	

		$block = $this->pagination($query);
		if ( $block['api']['total'] ){
			if ( !empty($properties) ){						
				$fields = $this->db
					->table($this->table .'meta')
					->when($block['api']['rows'], function ($query) use ($block) { //when pagination does return rows
						return $query->whereIn('page_id', array_column($block['api']['rows'], 'id'));
					})
					->whereIn('property', $properties)
					->select('page_id', 'property', 'value')
					->orderBy('page_id', 'asc') //order by page id and according to column order
					->orderBy( $this->db->raw('FIELD(property, "'. implode('", "', $col_order) .'")') )
					->get()->all();
				if ( empty($fields) AND !empty($column) ){ //create dummy $lookup to finding lookup slug
					foreach ($column AS $table) {
						$lookup[ $table ][] = 1;
					}
				}	
				foreach ( ($fields??[]) AS $data ){
					//many records has the same page_id, cant use array_column
					$value = json_decode($data['value']??'', true)?? $data['value']; //both json and string are ok, except null
					//collect lookup ids
					if ( !empty($value) AND !empty($column) AND in_array($data['property'], array_keys($column) ) ){
						if ( is_array($value) ){ //input accepts multiple values
							foreach ($value AS $index => $id) {
								//use id as key to remove duplicate id 
								$lookup[ ($column[$data['property']]) ][ $id ] = $id;
								//$meta[pid][property][key] will refer to the same value of $lookup id, so it can be updated using references after lookup is done
								$value[ $id ] =& $lookup[ ($column[$data['property']]) ][ $id ]; //$meta[pid]['ticket_assignee'][1023] =& $lookup['staff'][1023]
								unset($value[ $index ]); //remove raw data
							}
						} else {
							$id = $value;
							$value = []; //turn to array
							$lookup[ ($column[$data['property']]) ][ $id ] = $id;
							$value[ $id ] =& $lookup[ ($column[$data['property']]) ][ $id ]; //$value[1023] =& $lookup['staff'][1023]
						}	  
					}
					$meta[ $data['page_id']][ $data['property']] = $value;
				}
				foreach ( ($lookup??[]) as $table => $ids ){ //do lookup for actual values
					$values = $this->lookupById($table, array_keys($ids) );
					foreach ($values['rows']??[] as $id => $name) {
						$lookup[ $table ][ $id ] = $name; //update value for (ref to $meta id) hence update $meta
					}
					if ( !empty($values['slug']) AND $show_links){
						foreach (array_keys($column, $table) AS $p) {//can be multiple 
							$block['links']['datatable'][ $p ] = $values['slug'];
						}
					}
					if ( !empty($values['images'])){
						foreach (array_keys($column, $table) AS $p) {//can be multiple 
							$block['api']['image_sources'][ $p ] = $values['images'];
						}
					}
				}
			}

			foreach ( ($block['api']['rows']??[]) AS $key => $data ){
				/*if( !empty($data['creator']) ){
					$data['creator'] = [ $data['creator'] => $data['creator_name'] ];
					unset($data['creator_name']);
				}*/

				$block['api']['rows'][ $key ] = $this->preparePage($data, !$show_links);
				//remove tags for subapp's content
				if ( $strip_tags AND !empty($block['api']['rows'][ $key ]['content']) ){
					//$block['api']['rows'][ $key ]['content'] = strip_tags($block['api']['rows'][ $key ]['content']);
				}

				if ( !empty($meta[ $data['id'] ]) ){
					$block['api']['rows'][ $key ] = array_merge($block['api']['rows'][ $key ], $meta[ $data['id'] ] );
				}
			}
			//sorting using app columns (table column sorted using SQL already)
			if ( !empty($_REQUEST['sort']) AND !empty($app['app_columns'][ key($_REQUEST['sort']) ]) AND count($block['api']['rows']) ){
				$property = key($_REQUEST['sort']);
				usort($block['api']['rows'], function($a, $b) use ($property) {
					if ( !array_key_exists($property, $a) OR !array_key_exists($property, $b) ){
						return 0;
					}
					if ($_REQUEST['sort'][ $property ] == 'desc'){
						if ( is_array($a[ $property ]) ){
							rsort($a[ $property ]);
							$a[ $property ] = implode(', ', $a[ $property ]);
						}
						if ( is_array($b[ $property ]) ){
							rsort($b[ $property ]);
							$b[ $property ] = implode(', ', $b[ $property ]);
						}
						return strcmp($b[ $property ], $a[ $property ]);
					} else {
						if ( is_array($a[ $property ]) ){
							sort($a[ $property ]);
							$a[ $property ] = implode(', ', $a[ $property ]);
						}
						if ( is_array($b[ $property ]) ){
							sort($b[ $property ]);
							$b[ $property ] = implode(', ', $b[ $property ]);
						}
						return strcmp($a[ $property ], $b[ $property ]);
					}
				});
			}

			if ($this->view->html){				
				$block['html']['table_header'] = (!empty($app['app_hide']['slug']))? [
					'id' => 'ID', 
					'name' => $this->trans('Name'), 
					//'updated' => $this->trans('Updated')
				] : [
					'id' => 'ID', 
					'name' => $this->trans('Name'), 
					'slug' => $this->trans('Slug'), 
					//'updated' => $this->trans('Updated')
				];
				if ( !empty($show_creator) ){
					$block['html']['table_header']['creator'] = $this->trans(is_string($show_creator??null)? $show_creator : 'Creator');
					if ( $show_links ){
						$block['links']['datatable']['creator'] = (!empty($app['app_users']) AND $app['app_users'] == 'staff')? $this->slug('Staff::action', ["action" => "edit"]) : $this->slug('User::action', ["action" => "edit"]);
					} else { //client/guest
						$slug = $this->slug('Profile::clientView', ["id" => "to"]);
						if ( ! str_contains($slug, '->exception')){
							$block['links']['datatable']['creator'] = $slug;
						}
						unset($block['html']['table_header']['slug']); //wont show slug when showing creator (mostly frontend)
					}
				}	

				foreach ( ($app['app_columns']??[]) as $key => $value) {
					$block['html']['table_header'][ $key ] = $this->trans($value);
					$block['html']['column_type'][ $key ] = $app['app_fields'][ $key ]['type']??'text'; 
				}	
				$block['html']['table_header']['action'] = $this->trans('Action'); 
				$block['html']['display'] = $app['app_display']??'table';
				
				if (($app['app_display']??null) == 'kanban'){
					foreach ( ($app['app_fields']??[]) AS $key => $field) {
						if ( ($field['type'] == 'select' OR $field['type'] == 'lookup' ) ){
							$this->formatFieldOptions($key, $field, $app);
							if ( !empty($field['options']) AND count($field['options']) > 1){ //at least 2 options
								$block['html']['boards'][ $key ] = array_fill_keys($field['options'], new \StdClass()); //create empty object instead of array
							}	
						}	
					}	
					$block['html']['kanban'] = !empty($block['html']['boards'])? key($block['html']['boards']) : 'month'; //indicate kanban support and default board
				}		
			}
		}	
		return $block;
	}	
	//set query to unprotected records, if logged in, do include protected records with no custom permission or with authorized permissions)
	protected function getUnprotected ($query){
		if ( $this->user->has("Role::SystemAdmin") ){ //better hardcoded here to not fill up all apps with this role
			return $query;
		}
		$myself = $this->user->getId();
		$for_me = $this->user->isStaff()? 'S' : 'U';
		$for_me .= $myself .'::';
		$permissions = array_keys($this->user->getPermissions());
		$query->where(function ($query) use ($permissions, $myself, $for_me) {
			$query->when($myself, 
				function ($query) use ($permissions, $myself, $for_me) { //logged in
					$query->where('page.creator', $myself) //can always view/edit self created records
					->orWhereNull('page.private')
					->orWhere('page.private', 'NOT LIKE', '%::%') //private but no permission required, just requires logged in 
					->orWhere('page.private', 'LIKE', '%'. $for_me .'%') //U8855::w, should wrap ID with ::
					->orWhereIn('page.private', $permissions);
				}, 
				function ($query){ //not logged in
					$query->whereNull('page.private')
					->orWhere('page.private', 0);
				}
			);
		});
		return $query;	
	}
	//set query to published, not expired, unprotected records (if logged in, do include private records with no custom permission or with authorized permissions)
	protected function getPublished ($query){
		$query->where('page.published', '>', 0)								 
			->where('page.published', '<=', time())
			->where(function ($query) {
	          	$query->whereNull('page.expire')
	              ->orWhere('page.expire', 0)
	              ->orWhere('page.expire', '>', time());
	        });
		return $this->getUnprotected($query);	
	}

	public function generateRoutes($extra = []) {	
    	$name = strtolower($this->class);

		$r[ $this->class .'::delete'] = ['POST',	 '/'. $name .'/delete.[json:format]?[POST:id]?'];

		$r[ $this->class .'::update'] = ['POST', 	 '/'. $name .'/update.[json:format]?[POST:'. $name .']?'];
		$r[ $this->class .'::action'] = ['GET|POST', '/'. $name .'/[edit|copy:action]/[i:id]?.[json:format]?'];

		foreach ($extra as $key => $value) {
			$r[ $this->class .'::'. $key ] = str_replace('/[i:site_id]', '', $value);
		}		
		//main should come last in the route table
		if (empty($r[ $this->class .'::main'])) {
			$r[ $this->class .'::main']   = ['GET', 	 '/'. $name .'.[json:format]?'];
		}	
		$routes[ $this->config['system']['passport'] ] = $r;

		return $routes;
	}
	//Generate slug
	protected function slug($name, $params = []) {
		try {
			return $this->router->generate($name, $params + ["site_id" => $this->site_id]); //site_id in $params won't be overridden
		} catch (\Exception $e) {
			return "/slug-exception";
		}	
	}
	//Generate site slug generateSlug($slug, $type, $subtype = '') {
	protected function url($slug, $type, $subtype = '') {
		if ($type == 'Link') {
			if (strpos($slug, '://') ){
				return $slug;
			} else {
				return 'https://'. $this->config['site']['url'] .'/'. ltrim($slug, '/'); 
			}
		} else {
			return 'https://'. $this->config['site']['url'] . $this->generateSlug($slug, $type, $subtype);
		}
	}
	// Generate slug for frontend page
	protected function generateSlug($slug, $type, $subtype = '') {
		try {
			if ($type == "Link"){
				return $slug;
			} elseif ($type == "App" AND $subtype) { //App - Blog
				return $this->router->generate('App::render', ['app' => strtolower($subtype), 'slug' => $slug]);	
			} elseif ($type == "Collection"){ //Collection
				if (strpos($subtype, "App::") !== false) { //subtype App::Blog
					return $this->router->generate('App::renderCollection', ['app' => strtolower(substr($subtype, 5)), 'slug' => $slug]);
				} elseif ($subtype) { //subtype Product uses Product::renderCollection			
					return $this->router->generate($subtype .'::renderCollection', ['slug' => $slug]);
				}		
			} else { //type Product uses route Product::render
				return $this->router->generate($type .'::render', ["slug" => $slug]);		
			}
		} catch (\Exception $e) {
			//hardcoded
			if ($type == "App" AND $subtype) { //App - Blog
				return strtolower('/'. $subtype .'/'. $slug);	
			} elseif ($type == "Collection"){ //Collection
				if (strpos($subtype, "App::") !== false) { //subtype App::Blog
					return strtolower('/'. substr($subtype, 5) .'/collection/'. $slug);
				} elseif ($subtype == 'Page') { 		
					return strtolower('/category/'. $slug);
				} elseif ($subtype == 'Product') { 		
					return strtolower('/collection/'. $slug);
				} else { //subtype Product uses Product::renderCollection			
					return strtolower('/'. $subtype .'/collection/'. $slug);
				}		
			} elseif ($type == "Product") { //type Product uses store
				return strtolower('/store/'. $slug);		
			} elseif ($type != "Page") { 
				return strtolower('/'. $type .'/'. $slug);		
			} else {
				return strtolower('/'. $slug);	
			}
		}	
	}
	//Generate slug using stand-alone route
	protected function urlFromRoute($route, $params = []) {
		//code taken from AltoRouter's generate
		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
			foreach($matches as $match) {
				list($block, $pre, $type, $param, $optional) = $match;

				if ($pre) {
					$block = substr($block, 1);
				}

				if(isset($params[$param])) {
					$route = str_replace($block, $params[$param], $route);
				} elseif ($optional) {
					$route = str_replace($pre . $block, '', $route);
				}
			}
		}
		return $route;
	}

	protected function getRightLanguage($data) {
		if ( is_array($data) ){
			$c = $this->config['site'];
			if ( !empty($c['locale']) ){
				$language = $c['locale'];
			} elseif ( $this->user->getLanguage() AND array_key_exists($this->user->getLanguage(), $c['locales']) ){
				$language = $this->user->getLanguage();
			} else {
				$language = $c['language'];
			}
			$language = strtolower($language); 
			
			if (isset($data[$language])) { //use isset just in case the var is empty
				return $data[$language];
			} elseif (isset($data['en'])) {
				return $data['en'];
			} elseif (isset($data[0])) {
				return $data[0];
			} else {
				return 'Error: Cannot retrieve translated string';
			}
		} elseif ( is_string($data) ){
			return $data;
		}	
		return '';		
	}

	protected function loadSandboxPage($edit_uri, $edit_id, $update_url) {
		// Disable for API access
		if ($this->view->html AND $this->config['system']['edit_url'] AND strpos($_SERVER['SERVER_NAME'], parse_url($this->config['system']['edit_url'], PHP_URL_HOST)) === false) {
			header('Location:' . $this->getSandboxUrl($edit_uri, $edit_id, $update_url));
			exit;	
		}		
	}

	protected function getSandboxUrl($edit_uri, $edit_id, $update_url) {
		$expire = time() + 9800;				
		$string = $expire .'::'. $this->user->getId() .'::'. $edit_id; 
		$url = $this->config['system']['edit_url'] . $edit_uri;
		// We should hash the hostname + URI (which may/maynot contain site_id) so the generated hash cannot be used with other URIs/sites.
		$id = @$this->hashify($string .'::'. $url);
		// hash::expiredTime::userId::widgetIdOrType::crsfToken
		$id .= '::'. $string .'::'. @$this->hashify($update_url .'::'. $_SESSION['token']); 
		$preview = !empty($_GET['user'])? '?user='. $_GET['user'] : '';
		if ( !empty($this->config['system']['sgframe']) ){
			$preview .= $preview? '&sgframe=1' : '?sgframe=1';
		} 
		return $url .'/'. @$this->encode($id) . $preview;
	}

	protected function denyAccess($action, $require = null) {
		if (empty($action)) {
			$action = $this->trans("access this area");
		}
		$this->logActivity("Access denied! ". $this->user->getName() ." (#". $this->user->getId() .") not allowed to ". $action, 'Core.', null, 'Warning', null, null, 'run'); //run immediately otherwise enqueue will invoke this unactivated class again and denied again and again
		$status['result'] = "error";
		$status['code'] = 403;
		$status['message'][] = $this->trans("You don't have permissions to :action", ['action' => $action]);	
		if ( !empty($require) ){
			$status['message'][] = $this->trans("Permission(s) required: :req", ['req' => $require]);	
		}		
		if ($this->view->html) {
			//$status['template']['file'] = "message";
			$status['html']['message_type'] = 'danger';
			$status['html']['message_title'] = $status['html']['title'] = $this->trans('Restricted Area');
		}	
		$this->view->setStatus($status);
		//render and exit
		$this->view->render();

        if (strpos($_SERVER['SERVER_NAME'], parse_url($this->config['system']['edit_url'], PHP_URL_HOST)) !== false) { 
        	//onetime token based access, remove onetime session due to early exit
           unset($_SESSION);
           @session_destroy();
        }        

		exit;
	}	

    static function copyFolder($source, $target) {
        if (!is_dir($source)) {//it is a file, do a normal copy
            $result = @copy($source, $target);
            if ($result) {
            	@chmod($target, 0775);
            	return $result;
            } else {
            	return FALSE;
            }
        }
 
        //it is a folder, copy its files & sub-folders       
        @mkdir($target, 0775, true);
		//@chmod($target, 0775);
        $d = @dir($source);	
        $navFolders = ['.', '..'];
        $result = [];
        while (false !== ($fileEntry=$d->read() )) {//copy one by one
            //skip if it is navigation folder . or ..
            if (in_array($fileEntry, $navFolders) ) {
                continue;
            }

            //do copy
            $s = "$source/$fileEntry";
            $t = "$target/$fileEntry";
            $result[] = self::copyFolder($s, $t);
        }
        $d->close();
        return array_product($result);
    }

    static function deleteFolder($target) {
        if (!is_dir($target)) {//it is a file, do a normal copy
            return @unlink($target);
        }

        //it is a folder, copy its files & sub-folders
        $d = @dir($target);
        $navFolders = ['.', '..'];
        while (false !== ($fileEntry=$d->read() )) {//delete one by one
            //skip if it is navigation folder . or ..
            if (in_array($fileEntry, $navFolders) ) {
                continue;
            }

            //do delete
            $t = "$target/$fileEntry";
            self::deleteFolder($t);
        }
        $d->close();
        return @rmdir($target);
    }

    protected function upload(&$path, $upload, $base_dir, $folder, $public = null, $site_id = null ) {
		if ($upload['error'] != 4) { //no file was uploaded
			try {
				$save = new \Bulletproof\Image($upload);
				$save->setStorage($base_dir, 0755);
				$save->setMime(['jpeg', 'png', 'gif', 'jpg', 'bmp', 'tiff']);//, 'webp', 'pdf', 'txt']);
				$save->setSize(100, 51200000); //50Mb

				$file = $folder . uniqid() .'_'. substr(strtok($this->sanitizeFileName($upload['name']), '.'), 0, 30);//shorten 
				$save->setName($file); //$file contain no extension 
				if ( $save->upload() ){
					//allow adding more attachments to current set
					$path = str_replace($base_dir .'/', '', $save->getPath());
					if ($public){
						$path = $this->config['system']['cdn'] .'/public/uploads/site/'. ($site_id??$this->config['site']['id']) .'/clients/'. $folder .'/'. $path;
					} 
			  	} else {
			    	echo str_replace($base_dir, '', $save->getError() ); 
			  	}
			} catch (\Exception $e) {
				echo str_replace($base_dir, '', $e->getMessage() );
			}	  	
		}
	}
	//use site_id to allow users from dependent sites to upload avatar to the main site 
	protected function prepareUploads( $files, &$path = [], $base = [], $public = null, $site_id = null ){
		//set warning handler to hide base_dir
		set_error_handler(function ($errno, $errstr) {
			throw new \Exception($errstr, $errno);	
		}, E_WARNING);
		if ($public){ //public is not mounted remotely, need to use symlink through protected
			$folder = date("y") .'Q'. ceil(date("n") / 3); //21Q2 
			$base_dir = $this->config['system']['base_dir'] .'/resources/protected/client_uploads/'. ($site_id??$this->config['site']['id']) .'/clients/'. $folder; 
		} else {
			$folder = date("y") . ceil(date("n") / 3); //21Q2 -> 212 
			$base_dir = $this->config['system']['base_dir'] .'/resources/protected/site/'. ($site_id??$this->config['site']['id']) .'/attachment/'. $folder; 
		}	

	   foreach ($files AS $key => $value) {
	      if ( isset($value['error']) ){
	        if ( is_numeric($value['error']) ){
	        	while ( !empty($path[$key]) ){
	        		$key++; //there can be existing attachment in the index
	        	}
	          $this->upload($path[$key], $value, $base_dir, $folder, $public, $site_id);
	        } else {
	          $this->prepareUploads($value['error'], $path, $value, $public, $site_id); //ignore $key when it is 'page' 
	        }    
	      } else {
	        $forward = [
	            'name' => $base['name'][$key], 
	            'type' => $base['type'][$key],
	            'tmp_name' => $base['tmp_name'][$key], 
	            'error' => $base['error'][$key], 
	            'size' => $base['size'][$key],
	        ];

	        if ( is_numeric($value) ){
	        	while ( !empty($path[$key]) ){
	        		$key++;
	        	}
	         	$this->upload($path[$key], $forward, $base_dir, $folder, $public, $site_id);
	        } else {
	          $this->prepareUploads($value, $path[$key], $forward, $public, $site_id);
	        }    
	      }
	   }
	   restore_error_handler();
	}		
}	