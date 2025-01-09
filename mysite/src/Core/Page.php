<?php
namespace SiteGUI\Core;

class Page {
	use Traits\Application { generateRoutes as trait_generateRoutes; }
	use Traits\SubApp;
	use Traits\Automation; //requires $path, $changeble

	protected $changeable = ['name', 'slug', 'title', 'description', 'content', 'image', 'creator', 'created', 'updated', 'published', 'published_at', 'expire', 'private', 'status', 'menu_id', 'breadcrumb', 'layout', 'collection', 'collection_replace', '$', '@user', '@user_write', '@staff', '@staff_write', 'page_linking_only']; //default page property, no id here, $ is used to store temporary value and be dismissed when updating, @ for limit record to User/Staff ID, changing creator only takes effect when creating a new entry. Type/subtype should not be changed as permissions rely on them, created/updated wont be saved 

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user); //construct Application
		$this->table = $this->site_prefix ."_page";
		//Inherited Class will get permissions from its config first
		$this->requirements['READ']    = $this->config('app_permissions')['staff_read_permission']??
										  self::config('app_permissions')['staff_read_permission']??'-'; //prevent empty value
		$this->requirements['CREATE']  = $this->config('app_permissions')['staff_write_permission']??
										  self::config('app_permissions')['staff_write_permission']??'-';
		$this->requirements['PUBLISH'] = $this->config('app_permissions')['staff_manage_permission']??
										  self::config('app_permissions')['staff_manage_permission']??'-';		
		if (!empty($this->config['system']['base_dir'])) {
			//use public/templates/app for faster rendering, Appstore writes to symlinked protected/app for central storage
			$this->path = $this->config['system']['base_dir'] .'/resources/public/templates/app/';
		} else {
			echo "Template directory is not defined!";
		}
	}
	
	public static function config($property = '') {
		$config['app_visibility'] = 'staff'; //direct config instead of app_menu (like config.php)
		$config['subapp_support'] = 1; //allow adding subapp via Appstore configure
	    $config['app_configs'] = [];
	    $config['app_permissions'] = [
	    	'staff_read'   => 1,
	    	'staff_write'  => 1,
	    	'staff_manage' => 1,
	    	'staff_read_permission'   => "Page::create",
	    	'staff_write_permission'  => "Page::create",
	    	'staff_manage_permission' => "Page::publish",
	    ];	
    	return ($property)? ($config[ $property ]??null) : $config;		
    }

	public function generateRoutes($extra = []) {
		$name  = strtolower($this->class);
		//route update can be used by all inherited classes that capture POST page
		$extra['update'] = ['POST', '/[i:site_id]/'. $name .'/update.[json:format]?/[POST:page]?'];
		$extra['action'] = ['GET|POST', '/[i:site_id]/'. $name .'/[edit|copy:action]/[*:id]?.[json:format]?']; //accept hashed id
		$routes = $this->trait_generateRoutes($extra);
        //$routes['user']['Page::render']           = ['GET', '/[*:slug]?.[html|json:format]?'];
        //$routes['user']['Page::renderCollection'] = ['GET', '/category/[*:slug]?.[html|json:format]?'];
		
		return $routes;
	}

	/**
	* list all pages 
	* @return none
	*/
	public function main($type = '') {
		if ( !$this->user->has($this->requirements['READ']) ){
			$this->denyAccess('list');
		} else {
			if (empty($type)) {
				$type = $this->class;		
			}
			$query = $this->db->table($this->table)
				->select('id', 'name', 'slug', 'image', 'published', 'creator', 'status')
				->where(function ($query) use ($type) {
					$query->where('type', $type);
					if ($type == 'Page') {
						$query->orWhere('type', 'Link'); 
					}
				});
			$block = $this->pagination($query);
			if ( $block['api']['total']  < 1 ) {
				$status['result'] = "error";
				$status['message'][] = !empty($_REQUEST['searchPhrase'])? $this->trans('Your search returns no matches') : $this->trans('You have not created any :type', ['type' => $type]);
				
				if ($this->view->html){				
					$status['html']['message_type'] = 'info';
					$status['html']['message_title'] = $this->trans('Information');	
					$link = $this->slug($this->class .'::action', ["action" => "edit"] );
			        $status['message'][ $link ] = $this->trans('Click here to create a new :type', ['type' => $this->class] );
				}
			} else {	
				$creators = $this->lookupById('staff', array_unique(array_column($block['api']['rows'], 'creator') ) );
				if ( !$creators ){
					$creators = $this->lookupById('user', array_unique(array_column($block['api']['rows'], 'creator') ) );
					$links['datatable']['creator'] = $this->slug('User::action', ["action" => "edit"] );
				} else {
					$links['datatable']['creator'] = $this->slug('Staff::action', ["action" => "edit"] );
				}
				foreach ($block['api']['rows'] AS $key => $data){
					$block['api']['rows'][ $key ] = $this->preparePage($data, 0);
					$block['api']['rows'][ $key ]['creator_name'] = $creators['rows'][ $data['creator'] ]??$data['creator'];
					$block['api']['rows'][ $key ]['creator_avatar'] = $creators['images'][ $data['creator'] ]??null;
				}
				if ($this->view->html){				
					$block['html']['table_header'] = [
						'id' => $this->trans('ID'),
						'name' => $this->trans('Name'),
						'slug' => $this->trans('Slug'), 
						'published' => $this->trans('Published'),
						'action' => $this->trans('Action'),
					];
					$block['html']['column_type'] = ['published' => 'date'];

					$links['api']  =   $this->slug($type .'::main');
					$links['edit'] =   $this->slug($type .'::action', ["action" => "edit"] );
					$links['copy'] =   $this->slug($type .'::action', ["action" => "copy"] );
					$links['delete'] = $this->slug($type .'::action', ["action" => "delete"] );
					$block['links'] = $links;					
					$block['template']['file'] = "datatable";		
				}
			} 

			!empty($status) && $this->view->setStatus($status);							
			$this->view->addBlock('main', $block, $type .'::main'); 
		}						
	}

	public function create($page) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('create');
		} else {
			$data = $this->prepareData($page);

			$data['views'] = 0;
			$data['created'] = time();

			if ( !empty($page['creator']) AND is_numeric($page['creator']) ){
				$data['creator'] = $page['creator']; //can be customer id or staff id depending on app
			} else {
				$data['creator'] = $this->user->getId(); 
			}	

			$data['id'] = $this->db
				->table($this->table)
				->insertGetId($data);	
			return $data;	
		}		
	}

	protected function read($id, $type = '', $subtype = '', $creator = NULL, $for_update = NULL) {
		// Authentication must be implemented by calling method
		if (empty($type)) {
			$type = $this->class;		
		}
		try {
			$result = $this->db
				->table($this->table)
				->where('id', intval($id) )
				->where(function ($query) use ($type) {
					$query->where('type', $type);
					if ($type == 'Page') {
						$query->orWhere('type', 'Link');
					}
				})
				->when($subtype, function($query, $subtype) {
					$query->where('subtype', $subtype);
				})
				->when($creator, function($query, $creator) {
					$query->where('creator', $creator);
				})
				->when($for_update, function($query) {
					$query->lockForUpdate();
				})
				->first();
		} catch (\Exception $e){
			
		}		
		if(!empty($result)){
			//read always returns data for all languages
			$result['title']     = json_decode($result['title']??'', true);
			$result['name']		 = json_decode($result['name']??'', true);
			$result['description'] = json_decode($result['description']??'', true);
			$result['content']   = json_decode($result['content']??'', true);
			if (!empty($result['public'])){
				$result['public'] = json_decode($result['public']??'', true);
			}
		}		
		return ($result)? $result : [];
	}

	protected function readMeta($id, $property = null) {
		$meta = $this->db
			->table($this->table .'meta')
			->where('page_id', $id)
			->when($property, function ($query) use ($property) {
				return $query->where('property', $property);
			})
			->pluck('value', 'property')
			->all();

		foreach( ($meta??[]) AS $key => $value) {
			$meta[$key] = json_decode($value??'', true)?? $value; //both json and string are ok, except null
		}
		return $property? ($meta[ $property ]??null) : ($meta??[]);	
	}

	protected function readSlug($slug, $type = '', $subtype = '') { //mostly used by frontend
		// Authentication must be implemented by calling method
		if (empty($type)) {
			$type = $this->class;		
		}

		$query = $this->db
			->table($this->table)
			->where('slug', strtolower($slug))
			->where('type', $type);
		if ($subtype) {
			$query = $query->where('subtype', $subtype);
		}			 
		$result = $query->orderBy('id')->first(); //always select the oldest 
			
		if ( ! empty($result['id'])	) {
			//readSlug always returns data for 1 language and correct slug
			$result = $this->preparePage($result);
			$result['meta'] = $this->readMeta($result['id']);
		}
		return $result??[];	
	}
	//add a version of published page
	protected function addVersion($master_id, $version_id) {
		if (is_int($master_id) AND is_int($version_id) AND $master_id > 0 AND $version_id > 0) {
			$insert_data[] = 'versioning';
			$insert_data[] = $master_id;
			$insert_data[] = $version_id; 

			if ( ! $this->upsert($this->site_prefix .'_location', ['app_type', 'app_id', 'page_id'], $insert_data, ['id' => 'id'])) {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('Version was created but not linked to the original page');		
			}
		}			
	}
	//replace the original version with the current one's content/meta/variants, links/menu won't be replaced
	//authorization must be done by calling method
	//replace $page
	protected function commitVersion(&$page, $method){
		if ( !empty($page['id']) ){
			$original = $this->db
				->table($this->site_prefix .'_location')
				->join($this->table, 'app_id', '=', $this->table .'.id')
				->where('app_type', 'versioning')
				->where('page_id', $page['id'])
				->first();
			if ( !empty($original['id']) ){
				unset($page['updated']); //replace_original_version_with_this
				$page['hide_success_message'] = true;
				$this->{$method}($page); //update this version first
				if ( $backup_page = $this->copy($original['id'], 'backup') ){ //backup the original version
					$this->addVersion($original['id'], $backup_page['id']);
				}	
				//load data for this version (as $page may contain partial data)
				if ( $page_info = $this->read($page['id']) ){//only replace the original if current version exist
					$page = $page_info;
					$page['fields'] = $this->readMeta($page_info['id']);
					if (method_exists($this, 'getVariants')) { 
						$page['variants'] = $this->getVariants($page_info['id'], 'with_meta');
						$original_variants = $this->getVariants($original['id'], 'with_meta');
						foreach( $page['variants']??[] AS $i => $variant ){ //replace variant ID with original's variant ID
							unset($page['variants'][ $i ]['meta'], $page['variants'][ $i ]['options']); //remove before assign options key again
							if ( !empty($original_variants[ $i ]['id']) ){
								$page['variants'][ $i ]['id'] = $original_variants[ $i ]['id'];
								unset($original_variants[ $i ]);
							} else {
								unset($page['variants'][ $i ]['id']); //create new variant
							}
							foreach($variant['meta']??[] AS $k => $v){ //prepare meta for saving
								$page['variants'][ $i ][ '@'. $k ] = $v;
							}
							foreach($variant['options']??[] AS $k => $v){ //prepare options for saving
								$page['variants'][ $i ][ $k ] = $v;
							}
						}
						//left-over $original_variants 
						foreach( $original_variants??[] AS $variant ){
							$variant['stock'] = 0; //hide on frontend
							$page['variants'][ ++$i ] = $variant; //use next $i from above
							unset($page['variants'][ $i ]['meta'], $page['variants'][ $i ]['options']); //remove before assign options key again
							foreach($variant['meta']??[] AS $k => $v){ //prepare meta for saving
								$page['variants'][ $i ][ '@'. $k ] = $v;
							}
							foreach($variant['options']??[] AS $k => $v){ //prepare options for saving
								$page['variants'][ $i ][ $k ] = $v;
							}
						}
					}

					$page['id'] = $original['id']; //change id to update original version
					$page['published'] = $original['published'];
					$page['slug'] = $original['slug'];
					unset($page['view']); //keep original view 
					if (str_ends_with($page['name'][ $this->config['site']['language'] ]??'', ' (Cloned)') OR 
						str_ends_with($page['name'][ $this->config['site']['language'] ]??'', ' (Backup)')
					){
						$page['name'][ $this->config['site']['language'] ] = substr($page['name'][ $this->config['site']['language'] ], 0, -9);
					}
				}
			} else {
				$page['published'] = 1; //no original, publish this instead
			}	
		}
	}
	//remove page from versioning 
	protected function deleteVersions($pid) {
		return $this->db
			->table($this->site_prefix .'_location')
			->where('app_type', 'versioning')
			->where(function ($query) use ($pid) {
				$query->where('page_id', $pid)
					->orWhere('app_id', $pid);
			})
			->delete();		
	}

	//get page versions
	protected function getVersions($master) {
		$root = $this->db
			->table($this->site_prefix .'_location')
			->join($this->table, 'app_id', '=', $this->table .'.id')
			->where('app_type', 'versioning')
			->where('page_id', $master)
			->get([$this->table .'.id', 'type', 'subtype', 'slug', 'name', 'creator', 'updated'])
			->first();
		$versions = $this->db
			->table($this->site_prefix .'_location')
			->join($this->table, 'page_id', '=', $this->table .'.id')
			->where('app_type', 'versioning')
			->where('app_id', $master)
			->orderBy('updated')
			->get([$this->table .'.id', 'type', 'subtype', 'slug', 'name', 'creator', 'updated'])
			->all();
		if ($versions){
			$staff = array_column($versions, 'creator');
		}	
		if ($root){
			$staff[] = $root['creator'];
		}
		if ( !empty($staff) ){
			$staff = $this->lookupById('staff', array_unique($staff) );
		}	

		if ($root){
			$root['creator_name'] = $staff['rows'][ $root['creator'] ]??'';
			$root['creator_avatar'] = $staff['images'][ $root['creator'] ]??'';
			$results['master'] = $this->preparePage($root);
		}
		foreach ($versions AS $item) {
			$item['creator_name'] = $staff['rows'][ $item['creator'] ]??''; 
			$item['creator_avatar'] = $staff['images'][ $item['creator'] ]??''; 
			$results['versions'][] = $this->preparePage($item);
		}
		return $results??[];
	}
	//update page public data
	protected function updatePublic($pid, $public) {
		return $this->db->table($this->table)
			->where('id', $pid)
			->update([
				'public' => json_encode($public),
			]);
	}	
	//update page meta
	protected function updateMeta($pid, $meta) {
		if (!empty($meta)) {
			$sort = 0;
			foreach($meta AS $key => $value) {	
				$upsert_data[] = intval($pid);
				$upsert_data[] = $this->sanitizeFileName($key);	
				if (is_array($value)) {
					$upsert_data[] = json_encode($this->array_remove_by_values($value), JSON_FORCE_OBJECT); // remove null item except 0, final empty [] is acceptable;
				} else {
					$upsert_data[] = $value; //allow update empty value
				}					
				$upsert_data[] = $sort++; //order	
			}
			// both existing and new meta submitted in a single post, run both update and insert 
			if (!empty($upsert_data)) {
				if ( ! $this->upsert($this->table .'meta', ['page_id', 'property', 'value', '`order`'], $upsert_data, ['value']) ) {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not added', ['item' => 'Metadata']);	
					$this->view->setStatus($status);
					return false;		
				} 
				return true;
			}
		}	
		return false;
	}
	//delete page meta
	protected function deleteMeta($pid) {
		return $this->db
			->table($this->table .'meta')
			->where('page_id', $pid)
			->delete();
	}

	protected function getUploadFolder($page){
		$upload_base = '/public/uploads/site/'. $this->config['site']['id'];
		if ((!empty($page['image']) AND str_contains($page['image'], $upload_base )) OR 
			(!empty($page['variants'][0]['images'][0]) AND str_contains($page['variants'][0]['images'][0], $upload_base ))
		){
			$upload_dir = dirname(parse_url(!empty($page['image'])? $page['image'] : $page['variants'][0]['images'][0]??'', PHP_URL_PATH));
			return substr($upload_dir, strlen($upload_base) + 1 );
		}
		return false;
	}

	public function update($page) {
		if ($this->loop > 100) $this->denyAccess('proceed. Loop detected at '. __FUNCTION__ .' ('. $this->loop .')');
		$this->loop++;

		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('update');
			return false;
		} 
		//update comes from sandbox page, make sure the id isnt arbitrary
		if ( isset($_GET['for']) ){
			if ( $this->config['system']['csrf'] AND 
				$_POST['csrf_token'] != $_SESSION['token'] AND 
				$_GET['for'] != trim($page['id']??0) //for new entry without id => use 0
			){
				$this->denyAccess('update this page');
			}
			unset($_GET['for']); //run once only 
		}
		//update version
		if ( !empty($page['id']) AND !empty($page['updated']) AND $page['updated'] == 'replace_original_version_with_this'){
		 	if( $this->user->has($this->requirements['PUBLISH']) ){
				$this->commitVersion($page, __FUNCTION__);
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans("You don't have permissions to :action", ['action' => 'merge' ]);
			}	
		}	

		$this->db->beginTransaction(); //start the db transaction
		$page_info = $this->read($page['id']??NULL, NULL, NULL, NULL, 'for_update');
		//Published page can't be updated by unauthorized admin
		if ( ! $this->user->has($this->requirements['PUBLISH']) AND $page_info["published"] > 0) { 
			$this->logActivity('Update Request Denied. Creating a new one', $this->class .'.', $page_info['id'], 'Warning');
			//$page['slug'] .= "-copy";
			if ( !empty($page['name'][ $this->config['site']['language'] ]) AND !str_ends_with($page['name'][ $this->config['site']['language'] ], ' (Cloned)') ){
				$page['name'][ $this->config['site']['language'] ] .= " (Cloned)";
			}
			$_master = $page_info['id']; //revision
			unset($page['id'], $page['published'], $page_info['id'], $page_info['published']); //force creating a new page
		}

		//let's process app's PRE actions here to work for both normal and page_linking_only, may modify $page value
		//print_r($this->config('app_configs'));
		if ( !empty($page_info['id']) ){
			$page_info['meta'] = $this->readMeta($page_info['id']); //for automation to get stored input
		}
		$app = $this->getAppInfo($this->class, 'Core');
		//prevent data tamper
		$page['_public'] = $page_info['public']??[];
		unset($page['$']);
		//$page['meta'] = []; //allow any meta data set by frontend

		//button keys which  are not included in app_fields, should be processed after app_fields as it filters out hidden/readonly $key, eligible keys in app_fields already added
		foreach ( ($app['app_buttons']??[]) AS $button ){
			if ( !empty($button['name']) AND !empty($page['fields'][ $button['name'] ]) AND str_contains($button['visibility'], 'staff') ){
				if ( in_array($button['name'], $this->changeable) ){
					$page[ $button['name'] ] = $page['fields'][ $button['name'] ]; //set this for automation
				} else {
					$page['meta'][ $button['name'] ] = $page['fields'][ $button['name'] ]; //set this for automation
				}	
			}
		}

		if ( !empty($app['app_automation']['pre']) ){
			$this->runAutomation($app['app_automation']['pre'], $app, $page, $page_info, __FUNCTION__);
		}	
		$this->runHook($this->class .'::'. __FUNCTION__, [ &$page, $page_info??[] ]);
		//page linking only often indicated by runAutomation's update to bypass heavy workload
		if ( empty($page['page_linking_only']) ){
			//we should trust $page_info['id'] as it is returned from our db
			if (empty($page_info['id'])) {
				if ($data = $this->create($page)) { // create a new page	
					$page_info['id'] = $data['id']; //we set value for the referenced id set in runAutomation first 
					$page_info = $data; //now $page_info can be overridden without affecting the reference 
					$status['message'][] = $this->trans(':item created successfully', ['item' => $this->class ]);
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not created', ['item' => $this->class]);
				}		
			} else {
				$data = $this->prepareData($page);				
				if ( $page_info['type'] == 'Link' ){
					$data['type'] = 'Link';
				}
				if ($this->db
					->table($this->table)
					->where('id', $page_info['id'])
					->where('type', $data['type']) //trust the type returned by prepareData, also make sure types match
					->update($data) 
				) {
					$status['message'][] = $this->trans(':item updated successfully', ['item' => $this->class ]);	
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not updated', ['item' => $this->class ]);
				}	
			}
			if ( empty($status['result']) AND !empty($page["published"]) AND empty($data['published']) ){
				$status['message'][] = $this->trans("You don't have permissions to :action", ['action' => 'publish']);
			}
		}	
		//store meta data for both new and updated page
		if (!empty($page_info['id'])) {
			//do page linking (and create subpage if needed) - unlink is not needed for clientView			
			if ( !empty($app['app_sub']) AND !empty($page['sub'] = $this->array_remove_by_values($page['sub']??[])) ){
				empty($status) && $status = [];
				$this->linkSubApps(__FUNCTION__, $page, $page_info, $status, $app, $this->user->has($this->requirements['PUBLISH']));	
			}

			//set by automation only, public should be set to page_info[public]??[] before running automation 
			if ( !empty($page['_public']) AND $page['_public'] != ($page_info['public']??null) AND (empty($status['result']) OR $status['result'] != 'error') ){
				$this->updatePublic($page_info['id'], $page['_public']);
			}

			if ( empty($page['page_linking_only']) ){
				//versioning
				if (!empty($_master)) {
					$this->addVersion($_master, $page_info['id']);
					$status['result'] = 'warning';
					$status['message'][0] = $this->trans('Direct update not allowed, a new version has been created for you. Please work on this version and request a merge when it is completed');
				}

				if ($upload_dir = $this->getUploadFolder($page)){
					$page['meta']['upload_dir'] = $upload_dir;
				}

				if (!empty($page['meta'])) { 
					$this->updateMeta($page_info['id'], $page['meta']);
				}
				//only for inherited classes using ProductVariant trait
				if (!empty($page['variants']) AND method_exists($this, 'updateVariants')) { 
					$this->updateVariants($page_info['id'], $page['variants']);
				}

				//update page collection
				$subtype = ($this->class === 'Collection' AND !empty($page_info['subtype']))? $page_info['subtype'] : $this->class;
				if ( array_key_exists('collection_replace', $page)) {
					//remove existing collection(s) and join specified collection(s)
					$next_actions['Collection::removeCollectionsByPageId'] = [
						"pid" => $page_info['id'],
					];
					$next_actions['Collection::joinExisting'] = [
						"pid" => $page_info['id'], 
						"collection_ids" => $page['collection_replace'], 
						"subtype" => $subtype,
						"sub" => ($this->class === 'Collection'),					
					];
				}
				//mix-up possible when $somevar = collection-replace but collection = collection_replace + collection but let app decide
				if (!empty($page['collection'])) {
					// Join page to collections of subtype = page's type (Page)
					$next_actions['Collection::join'] = [
						"pid" => $page_info['id'], 
						"collections" => $page['collection'], 
						"subtype" => $subtype,
						"sub" => ($this->class === 'Collection'),
					];
				}

				if (!empty($page['menu_id']) AND (empty($page['id']) OR $page['menu_id'] != ($page_info['menu_id']??null)) ){
					$next_actions['Menu::addItemToMenu'] = [
						"id" => $page['menu_id'], 
						"page_id" => $page_info['id'],
					];
				} 
			}
			
		}	
		$this->db->commit(); //can commit the db transaction here

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

		$this->logActivity( implode('. ', $status['message']), $this->class .'.', $page_info['id']??null );

		if ( empty($page['hide_success_message']) ){
			if ( $this->view->html ){	
				//$next_actions[ $this->class .'::edit'] = [$page_info['id']];					
				//$this->view->setStatus($status); //set normal status first
				$links['edit']    = $this->slug($this->class .'::action', ['action' => 'edit' ]);
				$links['update']  = $this->slug($this->class .'::update') .'?for='. $page_info['id'];
				$links['update'] .= !empty($this->config['system']['sgframe'])? '&sgframe=1' : '';
				$links['update'] = $this->getSandboxUrl($links['edit'], $page_info['id'], $links['update']);

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

	//client can only do page_linking_only otherwise
	public function clientUpdate($page) {
		if ($this->loop > 100) $this->denyAccess('proceed. Loop detected at '. __FUNCTION__ .' ('. $this->loop .')');
		$this->loop++;

		$app = $this->getAppInfo($this->class, 'Core');
		if ( ! $app ) {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('No such :item', ['item' => 'App']);
		} elseif ( empty($app['subapp_support']) ){
			$this->denyAccess('update');
		} elseif ( !empty($page['id']) AND !empty($page['sub']) ){ //page linking only, page id and sub required
			//update comes from sandbox page, make sure the id isnt arbitrary
			//update from public using cors generated by App:render, page_id is prepend to $_POST['csrf_token']
			if ( isset($_GET['for']) OR isset($_GET['cors']) ){
				if ( $this->config['system']['csrf'] AND 
					$_POST['csrf_token'] != $_SESSION['token'] AND 
					(
						( isset($_GET['for']) AND $_GET['for'] != trim(strtolower($this->class) .'/'. ($page['id']??''), '/') ) OR 
						( isset($_GET['cors']) AND !str_starts_with($_POST['csrf_token'], $page['id'] .'sg') )
					)	
				){
					$this->denyAccess('update this record');
				}
				unset($_GET['for'], $_GET['cors']); //run once only 
			}	

			$this->db->beginTransaction(); //start the db transaction
			$page_info = $this->read($page['id'], NULL, NULL, NULL, 'for_update');
			//Client can do page linking for published pages only
			if ( empty($page_info["published"]) ){
				$this->denyAccess('update');
			} 
			$page_info['meta'] = $this->readMeta($page['id']); //for automation to get stored input
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
			$page['_public'] = $page_info['public']??[];
			unset($page['$']);
			//$page['meta'] = []; //allow any meta data set by frontend/inherited classes
			//button keys which  are not included in app_fields, should be processed after app_fields as it filters out hidden/readonly $key, eligible keys in app_fields already added
			foreach ( ($app['app_buttons']??[]) AS $button ){
				if ( !empty($button['name']) AND !empty($page['fields'][ $button['name'] ]) AND str_contains($button['visibility'], 'client') ){
					if ( in_array($button['name'], $this->changeable) ){
						$page[ $button['name'] ] = $page['fields'][ $button['name'] ]; //set this for automation
					} else {
						$page['meta'][ $button['name'] ] = $page['fields'][ $button['name'] ]; //set this for automation
					}	
				}
			}

			if ( !empty($app['app_automation']['pre']) ){
				$this->runAutomation($app['app_automation']['pre'], $app, $page, $page_info, __FUNCTION__);
			}	
			$this->runHook($this->class .'::'. __FUNCTION__, [ &$page, $page_info??[] ]);

			//do page linking (and create subpage if needed) - unlink is not needed for clientView			
			if ( !empty($app['app_sub']) AND !empty($page['sub'] = $this->array_remove_by_values($page['sub']??[])) ){
				empty($status) && $status = [];
				$this->linkSubApps(__FUNCTION__, $page, $page_info, $status, $app, $this->user->has($this->requirements['PUBLISH']));	
			} 

			//set by automation only, public should be set to page_info[public]??[] before running automation 
			if ( !empty($page['_public']) AND $page['_public'] != ($page_info['public']??null) AND (empty($status['result']) OR $status['result'] != 'error') ){
				$this->updatePublic($page_info['id'], $page['_public']);
			}
			$this->db->commit();

			if ( empty($status['result']) OR $status['result'] != 'error' ){
				//run post-update automation rules
				if ( !empty($app['app_automation']['post']) ){
					$this->runAutomation($app['app_automation']['post'], $app, $page, $page_info, __FUNCTION__);
				}
			}
		} else { //page_linking but no data
			$status['result'] = 'error';
			$status['message'][] = $this->trans("Nothing to update for SubApp while App does not accept your update");
		}

		$this->logActivity( implode('. ', $status['message']), $this->class .'.', $page_info['id']??null );	
		if ( empty($page['hide_success_message']) ){
			if ( $this->view->html ){	
				$url = $this->url($page_info['slug'], $page_info['type'], $page_info['subtype']);
				$status['message'][ $url ] = $this->trans('Click here to keep editing');	

				if ( empty($status['result']) OR $status['result'] != 'error' ){
					$status['html']['message_title'] = $this->trans('Information');	
				}
			}
			!empty($status) && $this->view->setStatus($status);
		} 
	}	
	/**
	 * print out edit form
	 * @param  integer $id [description]
	 * @return [type]           [description]
	 */
	public function edit($id = 0, $menus = 'Menu::getMenus') {
		if ( $this->user->has($this->requirements['CREATE']) OR ($this->user->has($this->requirements['READ']) AND $id) ){
			/* This works too
			if ($menus === 'Menu::getMenus'){
				$next_actions['Page::edit'] = [$id,
		  		   "Menu::getMenus" => [],
		  		]; 
				$this->router->registerQueue($next_actions);
				return false;				

				$next_actions['anon::mous'] = ['target' => function ($vars) use ($id) {
					    echo "<pre>Closure defined via next_actions";
					    print_r($vars); 
					    echo "</pre>";
								},
					'params' => ["Site::edit" => [25]],
					'name'   => 'anonymous',
				];	
			}*/
			// we hash the target URL and use it as the onetime CSRF token, $for will limit update to the current id or new entry only
			$links['update'] = $this->slug($this->class .'::update') .'?for='. $id;
			$links['update'] .= !empty($this->config['system']['sgframe'])? '&sgframe=1' : '';
			$links['edit']   = $this->slug($this->class .'::action', ['action' => 'edit' ]);
			$this->loadSandboxPage($links['edit'], $id, $links['update']);
			//should be in sandbox url now
			//reuse one-time edit url to keep the crsf token intact
			$links['editor'] = $this->config['system']['edit_url'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			//return early for editor frame
			if ( isset($_REQUEST['frame']) AND $_REQUEST['frame'] == 'editor' ){
				$block['template']['file'] = "page_editor";
				//check user's onboarding
				$block['html']['onboard_wysiwyg'] = $this->user->meta('onboard_wysiwyg');
				$links['onboard'] = $this->slug('Staff::onboard');
				$links['onboard'] .= '?cors='. $this->hashify($links['onboard'] .'::'. $_SESSION['token']);
				$block['links'] = $links;	

				$this->view->addBlock('main', $block, $this->class .'::edit');
				$this->view->setLayout('blank');
				return;
			} 

			if($id > 0){
				$page_info = $this->read($id);
			}
			//wysiwyg frame needs page content only	
			if ( isset($_REQUEST['frame']) AND $_REQUEST['frame'] == 'wysiwyg' ){
				$links['widget'] = $this->slug('Widget::preview');
				$links['widget'] .= '?cors='. @$this->hashify($links['widget'] .'::'. $_SESSION['token']);

				$links['snippet'] = $this->slug('Template::snippet');
				$links['snippet'] .= '?cors='. @$this->hashify($links['snippet'] .'::'. $_SESSION['token']);

				//$links['genai'] = $this->slug('Assistant::action', ['action' => 'generate']);
				//$links['genai'] .= '?cors='. @$this->hashify($links['genai'] .'::'. $_SESSION['token']);

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
				$next_actions['Template::getSnippets'] = [];

				$block['template']['file'] = "page_wysiwyg";
				$this->view->setLayout('blank');
			} elseif ( empty($_REQUEST['subapp']) ){ //normal page edit
				if ($id AND empty($page_info) ){
					$status['result'] = 'error';
					$status['message'][] = $this->trans('No such :item', ['item' => $this->class ]);
					$this->view->setStatus($status);
				} else { 
					if( !empty($page_info) AND $page_info['type'] != 'Link'){ //existing page (not link)
						$block['api']['versioning'] = $this->getVersions($page_info['id']);
						$page_info['meta'] = $this->readMeta($id);
						$next_actions['Collection::getCollectionsByPageId'] = [ $id ];
					}
					//for both new and existing page
					if ( empty($page_info['type']) OR $page_info['type'] != 'Link'){
						$next_actions['Layout::getLayouts'] = [ $this->config['site']['template'] ];
					}	

					if ($this->view->html){	
						$links['main'] = $this->slug($this->class .'::main');
						$links['main'] .= !empty($this->config['system']['sgframe'])? '?sgframe=1' : '';
						if ( !empty($page_info) ){
							$links['uri']  = $this->url($page_info['slug'], $page_info['type'], $page_info['subtype']);
			         		if (empty($page_info['published'])){
				         		$links['uri'] .= '?oauth=sso';
				         		$links['uri'] .= '&login=step1';
				         		$links['uri'] .= '&preview=1';
				         		$links['uri'] .= '&initiator='. $this->encode($this->config['system']['url'] . $this->config['system']['base_path']);		         			
			         		}
		         		}
						//$links['edit']   = $this->slug($this->class .'::action', ['action' => 'edit' ]);
						//$links['update'] = $this->slug($this->class .'::update');
						//$links['file_manager'] = $this->slug('File::main');
						$links['activities'] = $this->slug('Activity::main') .'?cors=1&html=1&for='. $this->class .'.&fid='. ($page_info['id']??'');
						$links['file_api'] = $this->slug('File::action', ["action" => "manage"] );
						$links['leave_collection'] = $this->slug('Collection::leave');
						$links['leave_collection'] = $links['leave_collection'] .'?cors='. @$this->hashify($links['leave_collection'] .'::'. $_SESSION['token']);
						//check user's onboarding
						if ('Page' == $this->class){
							$block['html']['onboard_page'] = $this->user->meta('onboard_page');
							$links['onboard'] = $this->slug('Staff::onboard');
							$links['onboard'] .= '?cors='. $this->hashify($links['onboard'] .'::'. $_SESSION['token']);
						}

						$block['template']['file'] = "app_edit"; //change to app_edit to support subapps
						if (is_array($menus)) {
							$block['html']['menus'] = $menus; 
						}				
						if ( empty($page_info['meta']['upload_dir']) ){
							$upload_dir = date("Y") .'Q'. ceil(date("n") / 3);
						} else {
							$upload_dir = $page_info['meta']['upload_dir'];
						}	
						$block['html']['upload_dir'] = 'elf_l1_'. rtrim(strtr(base64_encode($upload_dir), '+/=', '-_.'), '.');
						$block['html']['ajax'] = 1;
						//$sidebar['output'] = 'This is sidebar content manually added in Page::edit';
						//$sidebar['html']['sidebar'] = 1;
						//$this->view->addBlock('left', $sidebar, 'Page::edit');
					}
				}		
				//hook point
				$this->runHook('Page::edit', [ &$page_info ]);
				if ($this->class != 'Page') {
					$this->runHook($this->class .'::edit', [ &$page_info ]);
				}
			}	

			/*Enable subapp
			$block['template']['file'] = "app_edit";
			$block['api']['app']['hide'] = ['locales' => 1];
			$block['api']['app']['sub']['👍'] = [
				'entry' => 'multiple',
				'display' => 'grid',
			];			
			$block['api']['subapp']['👍'] = [
				'hide' => [
					'wysiwyg' => 1,
				],
				'fields' => null,
			];*/
			//subapp pages
			$app = $this->getAppInfo($this->class, 'Core'); 
			if ( !empty($app['app_sub']) ){
				$this->editSubApps($page_info??null, $app, $this->user->has($this->requirements['PUBLISH']), $links['update']);

				$block['api']['app']['sub'] = $app['app_sub']??null;
				$links['subapp'] = $links['editor'];
			}
			$block['api']['app']['hide']['tabapp'] = 1;
			$block['html']['publisher'] = (!empty($app['app_permissions']['staff_manage']) AND (empty($app['app_permissions']['staff_manage_permission']) || $this->user->has($app['app_permissions']['staff_manage_permission'])) );

			$links['lookup'] = $this->slug('Lookup::now');
			$links['lookup'] = $links['lookup'] .'?cors='. @$this->hashify($links['lookup'] .'::'. $_SESSION['token']);
			$block['links'] = $links;
			if ( empty($_REQUEST['subapp']) ){ //also for ['frame'] == 'wysiwyg' 	
				$block['api']['page'] = $page_info??[];
			}	
			
			$this->view->addBlock('main', $block, $this->class .'::edit');
			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}
 		} else {
 			$this->denyAccess($id? 'edit' : 'create');
 		}					
	}
	//clone page or backup raw data
	public function copy($id, $backup = false) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('copy');
		} else {
			if ($backup){
				$type = ($this->class == 'Appstore')? 'Product' : $this->class;
				//read raw data to insert it back
				$page = $this->db
					->table($this->table)
					->where('id', intval($id) )
					->where(function ($query) use ($type) {
						$query->where('type', $type);
						if ($type == 'Page') {
							$query->orWhere('type', 'Link');
						}
					})
					->first();
			} else {
				$page = $this->read($id); //copy will run through prepareData
			}
			if ($page){
				unset($page['published']); //remove existing data for the new copy/backup
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
					$page['slug'] .= "-copy";
					if ( !str_ends_with($page['name'][ $this->config['site']['language'] ]??'', ' (Copied)') ){
						$page['name'][ $this->config['site']['language'] ] .= " (Copied)";
					}
					$page_info = $this->create($page);
				}

				if ( !empty($page_info['id']) ){
					$status['message'][] = $backup? $this->trans(':item backed up successfully', ['item' => $this->class]) : $this->trans(':item copied successfully', ['item' => $this->class]);
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
					$status['message'][] = $this->trans(':item was not copied', ['item' => $this->class]);					
				}
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('Original :item cannot be read', ['item' => $this->class]);
			}

			!empty($status) && $this->view->setStatus($status);							
			$this->logActivity( implode('. ', $status['message']), $this->class .'.', $page['id'] );

			if ($this->view->html AND !empty($page_info['id']) AND !$backup ){				
				$this->edit($page_info['id']);
			}				
		}
		return empty($page_info['id'])? null : $page_info; //return whole $page only if $page['id'] exist 	
	}	

	function delete($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('delete');
		} 

		$page_info = $this->read($id);
		if ( !$this->user->has($this->requirements['PUBLISH']) AND $page_info["published"] > 0){	//Published page cannot be deleted by unauthorized admin		
			$status['result'] = 'error';
			$status['message'][] = $this->trans("You don't have permissions to :action", ['action' => 'delete a published item']);
		} elseif ( ! $page_info ){
			$status['result'] = 'error';
			$status['message'][] = $this->trans('No such :item', ['item' => $this->class ]);
		} else {			
			if ($page_info['type'] === 'Link' AND $this->class === 'Page') {
				$type = 'Link';
			} elseif ($page_info['type'] === 'Product' AND $this->class === 'Appstore') {
				$type = 'Product';
			} else {
				$type = $this->class;
			}	
			if ($this->db->table($this->table)->where('type', $type)->delete(intval($id))) {
				$status['message'][] = $this->trans(':item deleted successfully', ['item' => $this->class ]);			
				if (method_exists($this, 'deleteVariants')) { //only for inherited classes using ProductVariant trait
					$this->deleteVariants($page_info['id']);
				}
				$this->deleteVersions($page_info['id']);
				$this->deleteMeta($page_info['id']);
				$this->runHook($this->class .'::deleted', [ $page_info ]);

				$next_actions['Collection::removeCollectionsByPageId'] = [ $page_info['id'] ];
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not deleted', ['item' => $this->class]);				
			}
			$this->logActivity( implode('. ', $status['message']), $this->class .'.', $page_info['id']);
		}

		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);	

		if ($this->view->html){				
			$this->main();
		}									
	}

	protected function prepareData($page) {
		$result['updated'] = time();
		foreach (['name', 'title', 'description', 'content'] AS $key) {
			if (isset($page[ $key ])) {//no need for array_key_exists here
				//language not set, set it - it's used to determine slug
				if ( !is_array($page[ $key ]) ) {
					$page[ $key ] = [ 
						$this->config['site']['language'] => $page[ $key ]
					];		
				}
				//HTML purifier content field if wysiwyg is not enabled
				if ( $key == 'content' && empty($page['content']['wysiwyg']) ){
					require_once $this->config['system']['base_dir'] .'/src/vendor/HTMLPurifier/HTMLPurifier.auto.php';
					$config = \HTMLPurifier_Config::createDefault();
					$config->set('Cache.SerializerPath', $this->config['system']['base_dir'] .'/resources/templates_c');
					//this config accept base64 but not normal images
					//$config->set('URI.AllowedSchemes', ['data' => true]);
					//$config->set('CSS.AllowedProperties', 'width'); //allow image css width
					//$config->set('CSS.MaxImgLength', null);
					$purifier = new \HTMLPurifier($config); 
					$folder = date("y") .'Q'. ceil(date("n") / 3); //21Q2 
					$base_dir = $this->config['system']['base_dir'] .'/resources/protected/client_uploads/'. ($site_id??$this->config['site']['id']) .'/clients/'. $folder; 

					foreach ($page['content'] AS $lang => $content ){
						if (str_contains($content, 'src="data:image/') AND preg_match_all('/src="data:image\/([a-zA-Z\+]*);base64,([a-zA-Z0-9\+\/=]*)"/', $content, $matches) ){ //1st svg+xml, 2nd base64 chars
						    foreach ($matches[2]??[] AS $i => $match){
						    	$type = strtolower($matches[1][ $i ]); // jpg, png, gif
								if (in_array($type, [ 'jpg', 'jpeg', 'gif', 'png', 'webp', 'svg+xml' ]) && $match = base64_decode(trim($match)) 
								){
									//save image and replace content
									$file = $folder . uniqid() .'.'. $type;
									if ($type == 'svg+xml'){
										$file = substr($file, 0, strlen($file) - 4);
									}
									if (@file_put_contents($base_dir .'/'. $file, $match)){
										$content = str_replace('data:image/'. $type .';base64,'. $matches[2][ $i ], $this->config['system']['cdn'] .'/public/uploads/site/'. ($site_id??$this->config['site']['id']) .'/clients/'. $folder .'/'. $file, $content);
									}
							    }						    
						    }
						    //echo $content;print_r($matches);
						}

						$page['content'][ $lang ] = $purifier->purify($content);
					}   
				}

				$result[ $key ] = json_encode($page[ $key ]);
			}
		}
		//this should be first as type can be changed to Link when evaluate slug
		$result['type'] = (($page['type']??null) == 'Link')? 'Link' : $this->class;

		if (!empty($page['subtype'])) { //only add/update it if not empty
			$result['subtype'] = $this->sanitizeFileName($page['subtype']);
		} 

		if ( !empty($page['slug']) ){
			if ( strpos($page['slug'], 'http://')  === 0 OR 
				 strpos($page['slug'], 'https://') === 0 OR 
				 strpos($page['slug'], '/')        === 0 OR 
				 strpos($page['slug'], '?')        === 0 OR 
				 strpos($page['slug'], '#')        === 0
			){ //link
				$result['type'] = "Link";
				$result['slug'] = $page['slug'];
			} else {	
				$result['slug'] = $this->sanitizeFileName($page['slug']);
			}	
		} elseif (empty($page['id']) ){ //for new entry only
			if ( !empty($page['name'][ $this->config['site']['language'] ]) ){
				$result['slug'] = $this->sanitizeFileName($page['name'][ $this->config['site']['language'] ]);
			} elseif ( !empty($page['title'][ $this->config['site']['language'] ]) ){
				$result['slug'] = $this->sanitizeFileName($page['title'][ $this->config['site']['language'] ]);
			}
			if ( !empty($result['slug']) ){
				$result['slug'] .= '-'. rand(10,99) . time(); //new post
			}	
		}	
		//check if the slug can be used (exclude versioning)
		if ( !empty($result['slug']) AND $result['type'] != 'Link' ){
			$result['slug'] = trim(preg_replace("/[^a-z0-9_.@\-]/i", '', $result['slug']), '-');
			$taken = $this->db
				->table($this->table)
				->where('slug', $result['slug'])
				->where('type', $result['type'])
				->when( !empty($result['subtype']), function ($query) use ($result) {
					return $query->where('subtype', $result['subtype']);
				})
				->when( !empty($page['id']), function ($query) use ($page) { //update record, slug not changed
					return $query->where('id', '<>', $page['id']);
				})
				->first();
			if ($taken){
				$result['slug'] = preg_replace('/(-\d+)?$/', '-'. rand(10,99) . time(), $result['slug'], 1); //replace or append timestamp
			}	
		}


		if (isset($page['menu_id'])) {//no need for array_key_exists here
			$result['menu_id'] = intval($page['menu_id']);
		}

		if (isset($page['breadcrumb'])) {//no need for array_key_exists here
			$result['breadcrumb'] = intval($page['breadcrumb']);
		}

		if (isset($page['private']) ) {//no need for array_key_exists here
			$result['private'] = $page['private'];//may contain page level permission
		}
		if ( isset($page['@user_write']) ) {//limit updating record to specified User IDs
			if ( !isset($result['private']) ){
				$result['private'] = '';
			}
			if ( is_array($page['@user_write']) ){
				foreach( $page['@user_write'] AS $userid ){
					$result['private'] .= 'U'. $userid .'::w'; //U8855::w
				}
			} elseif ( is_numeric($page['@user_write']) ){
				$result['private'] .= 'U'. $page['@user_write'] .'::w';
			}
		}
		if ( isset($page['@user']) ) {//limit reading record to specified User IDs
			if ( !isset($result['private']) ){
				$result['private'] = '';
			}
			if ( is_array($page['@user']) ){
				foreach( $page['@user'] AS $userid ){
					$result['private'] .= 'U'. $userid .'::'; //U8855::, //read
				}
			} elseif ( is_numeric($page['@user']) ){
				$result['private'] .= 'U'. $page['@user'] .'::';
			}
		}
		if ( isset($page['@staff_write']) ) {//limit updating record to specified Staff IDs
			if ( !isset($result['private']) ){
				$result['private'] = '';
			}
			if ( is_array($page['@staff_write']) ){
				foreach( $page['@staff_write'] AS $userid ){
					$result['private'] .= 'S'. $userid .'::w'; //S8855::w
				}
			} elseif ( is_numeric($page['@staff_write']) ) {
				$result['private'] .= 'S'. $page['@staff_write'] .'::w';
			}
		}
		if ( isset($page['@staff']) ) {//limit reading record to specified Staff IDs
			if ( !isset($result['private']) ){
				$result['private'] = '';
			}
			if ( is_array($page['@staff']) ){
				foreach( $page['@staff'] AS $userid ){
					$result['private'] .= 'S'. $userid .'::'; //S8855:: //read
				}
			} elseif ( is_numeric($page['@staff']) ) {
				$result['private'] .= 'S'. $page['@staff'] .'::';
			}
		}		

		if (isset($page['status'])) {//no need for array_key_exists here
			$result['status'] = $page['status'];
		}

		if (isset($page['image'])) {//no need for array_key_exists here
			$result['image'] = $page['image'];
		}	
		if (isset($page['layout'])) {//no need for array_key_exists here
			$result['layout'] = $this->sanitizeFileName($page['layout']);
		}			

		if ($this->user->has($this->requirements['PUBLISH']) ){
			if ( array_key_exists('published', $page) ){
				try {
					if ( !empty($page['published']) ){
						if ( !empty($page['published_at']) ){
							if ( is_numeric($page['published_at']) ){ //already timestamp
								$result['published'] = $page['published_at'];
							} else {
								$date = new \DateTime( $page['published_at'] ); 
								$result['published'] = $date->format('U');
							}	
						} elseif ( is_numeric($page['published']) ) { //already timestamp
							$result['published'] = $page['published'];
						} else {
							$result['published'] = time();
						}
					} else {
						$result['published'] = 0;
					}	
				} catch (\Exception $e) {
					echo $e->getMessage();
					$result['published'] = time();
				}
			}	
			//expiry date must be 0 or > published date when page is published
			//date will be epoch timestamp and displayed by localeString in user's timezone
			if ( array_key_exists('expire', $page) ){
				$result['expire'] = 0;
				if ( !empty($page['expire']) ){
					try {
						if ( ! is_numeric($page['expire']) ){ //already timestamp
							$date = new \DateTime($page['expire']);
							$page['expire'] = $date->format('U');
						}	
					} catch (\Exception $e) { 
						$page['expire'] = 0;
					}
					if ( empty($result['published']) || ($page['expire'] > $result['published']) ){
						$result['expire'] = $page['expire'];
					}
				} 
			}	
		} else {
			//$result['published'] = 0;
		}

		return $result;		
	}

	//choose correct language for display
	protected function preparePage($page, $slugify = 1) {
		if(!empty($page['slug']) AND !empty($page['type']) AND !empty($slugify)){
			$page['slug'] = $this->generateSlug($page['slug'], $page['type'], $page['subtype']??null);	
		}
		if(!empty($page['title'])){
			//if $page['title'] is string then json_decode will return null
			$page['title'] = $this->getRightLanguage(json_decode($page['title']??'', true)??$page['title']);
		}
		if(!empty($page['name'])){
			$page['name'] = $this->getRightLanguage(json_decode($page['name']??'', true)??$page['name']);
		}
		if(!empty($page['parent'])){ //for collection or page has parent
			$page['parent'] = $this->getRightLanguage(json_decode($page['parent']??'', true)??$page['parent']);
		}
		if(!empty($page['description'])){
			$page['description'] = $this->getRightLanguage(json_decode($page['description']??'', true)??$page['description']);
		}
		if(!empty($page['content'])){
			$page['content'] = $this->getRightLanguage(json_decode($page['content']??'', true)??$page['content']);
		}
		
		if (!empty($page['public'])){
			$page['public'] = json_decode($page['public']??'', true);
		}		
		return $page;
	}	
	//get public profile
	protected function getProfile($user, $basic = false){
		try {
		    $profile = $this->db
				->table($this->table .' AS page')
				->join($this->table_user .' AS user', 'page.creator', '=', 'user.id')
				->where('page.type', 'Profile')
				->where('page.published', '>', 0)
				->where('page.creator', $user)
				->select('page.id', 'user.handle AS slug', 'user.name', 'user.image', 'page.type', 'page.subtype', 'page.content', 'page.public')
				->first();	
			
			if (!empty($profile['id'])){
				$profile['slug'] = $this->url($profile['slug'], $profile['type'], $profile['subtype']);
				$profile['content'] = $this->getRightLanguage(json_decode($profile['content']??'', true));
	    		$profile['public'] = json_decode($profile['public']??'', true);
				if ($basic){
					return $profile;
				}
				
				$profile['meta'] = $this->readMeta($profile['id']);
				if (!empty($profile['meta']['languages']) AND is_array($profile['meta']['languages'])){
					foreach ($profile['meta']['languages']??[] AS $l ){
						$languages[] = ucwords($this->getLanguages($l));
					}
					if ( !empty($languages) ){
						$profile['meta']['languages'] = implode(', ', $languages);
					}
				}	
				if ( !empty($profile['meta']['country']) ){
					$profile['meta']['country'] = $this->getCountry($profile['meta']['country']);
				}
				if ( !empty($profile['meta']['groups']) ){ //general groups, can use in most cases
					$profile['meta']['groups'] =  $this->lookupById('collection', $profile['meta']['groups'])['rows']??$profile['meta']['groups'];
				}
			}
			return $profile;	
		} catch (\Exception $e){

		}	
	}

	//check if page can be viewed by the current user, can add a banner for showing unpublished page to owner
	protected function canView(&$page) {
		if ($page['published'] < 1 OR $page['published'] > time() OR ($page['expire'] > 0 AND $page['expire'] < time() ) ){ //unpublished slug			
			if ($page['creator'] == $this->user->getId() OR $this->user->has($this->requirements['PUBLISH'])) { // admin/creator override
     			$page['published'] = 1;
     			$page['content']   = '<div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>'. $this->trans('Unpublished Page - Creator Preview Only') .'</strong><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'. $page['content'];
     			return true;
			} else { // normal user will see 404 error
				return false;
			}
		} elseif ($page['private']) {
			if ( is_numeric($page['private']) ){ //private page - site level
				if ( ! $this->user->getId() ){
					$_SESSION[ $this->config['system']['passport'] ]['auth_request_from'] = $page['slug'];
					header('Location: https://'. $this->config['site']['account_url'] .'/account?oauth=sso&login=step2&token='. $_SESSION['token'] . $this->config['site']['id'] .'&requester='. $this->encode('https://'. $this->config['site']['url'] . $page['slug'] ));
					exit;
				}
				return true;	
			} elseif ( ! $this->user->has($page['private'])) {//page level permission
				$this->denyAccess('view');
			}
			return true;
	  	} else { //published slug
	    	//update_views($page['id']);
	    	if ( !empty($this->redis) ){
	    		$hourly = 'hits:'. $this->config['site']['id'] .':hourly:'. gmdate('H'); //use GMT hour as each site has different tz
	    		$page['views'] = ($page['views']??0) + $this->redis->hIncrBy($hourly, $page['id'], 1); //hIncrBy returns updated value
	    	}
	    	return true;
		}
	}

	public function render($full_path_without_query = '') { //This method used for unauthenticated users, be careful
		//echo $full_path_without_query;
		$slug = trim($full_path_without_query, '/');
		if (empty($slug) OR $slug == trim(parse_url($this->config['site']['url'], PHP_URL_PATH), '/')) {
			$slug = "index.html";
		}
		$page = $this->readSlug($slug);	
		$app = $this->getAppInfo($this->class, 'Core'); 

		if (empty($page)) {
			$final_path = basename($slug); //get the last part
			if ($final_path != $slug) {
				$page = $this->readSlug($final_path);	
			}			
		}

		if (empty($page)) { //no such slug; return false and let the main controller handle 404 
			if ( $slug == 'index.html' ){ //no custom index.html defined, check if index.html is a link or list all records
				if ($this->class === 'Page'){
					$redirection = $this->db->table($this->table)
						->where('type', 'Link')
						->whereRaw('JSON_CONTAINS(name, \'"index.html"\', ?)', [ '$.'. $this->config['site']['language'] ])
						->value('slug');
					if ($redirection){
						if ( !str_starts_with($redirection, 'http')){
							$redirection = 'https://'. $this->config['site']['url'] .'/'. ltrim($redirection, '/');
						}
						if (filter_var($redirection, FILTER_VALIDATE_URL)) {
							header('Location: '. $redirection);
							exit;
						} else {
							return false;
						}	
					} 
				}
				return $this->renderIndex();
			} else {
				return false; 
			} 
		} elseif ( ! $this->canView($page) ){	
			return false;
		}
		//normal app render
		if ( empty($_REQUEST['subapp']) ){
			$page['content'] = html_entity_decode($page['content']);
			$block['template']['file'] = strtolower($this->class);		
			$block['api']['page'] = $page;			
			if ($this->view->html){				
				//$block['html']['title'] = $page['title'];
				if ( !empty($app['app_sub']) AND !$this->user->getId() AND !empty($page['id']) ){
					$block['html']['sso_requester'] = $this->encode('https://'. $this->config['site']['url'] . $page['slug']);
				}
				$this->view->setLayout( ($page['layout'])? $page['layout'] : 'default');
			}
			//subapp
			if ( !empty($app['app_sub']) ){
				$block['api']['app']['sub'] = $app['app_sub'];
			}
			$this->runHook($this->class .'::'. __FUNCTION__, [ &$block ], 'silent', 'add_blocks');
			$this->view->addBlock('main', $block, $this->class .'::render');
			$next_actions['Collection::getCollectionsByPageId'] = [ $page['id'], "slug" => 1 ];
			//Page may not need it $next_actions['Collection::getRelatedItems']        = [ $page['id'], "slug" => 1 ];
			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}
		}
		//subapp pages
		if ( !empty($app['app_sub']) ){
			$this->renderSubApps($page??null, $app);
		}	

		!empty($status) && $this->view->setStatus($status);
		return $page;
	}

	public function renderSiteMap() {
		$query = $this->db
			->table($this->table .' AS page')
			->whereNotIn('page.id', function($query){ //not sub-pages
				$query->select('page_id')
					->from($this->site_prefix .'_location')
					->where('app_type', 'subapp'); 
			})
			->where('type', '<>', 'Link')
			->when( !empty($_REQUEST['rss']), function($query){
				return $query->where('type', '<>', 'Collection')
					->take(48);
			}, function($query){	
				return $query->take(50000);
			})
			->selectRaw("page.id, page.type, page.subtype, page.slug,
				JSON_UNQUOTE( JSON_EXTRACT(page.name, '$.". $this->sanitizeFileName($this->config['site']['language']) ."')) AS name,
				JSON_UNQUOTE( JSON_EXTRACT(page.title, '$.". $this->sanitizeFileName($this->config['site']['language']) ."')) AS title,
				JSON_UNQUOTE( JSON_EXTRACT(page.description, '$.". $this->sanitizeFileName($this->config['site']['language']) ."')) AS description,
				page.updated, page.image")
			->orderBy('updated', 'desc');
		$query = $this->getPublished($query);
		$block['api']['pages'] = $query->get()->all();
		foreach ($block['api']['pages']??[] AS $i => $p){
			$p['slug'] = $this->url($p['slug'], $p['type'], $p['subtype']);
			$p['updated'] = date(DATE_RSS, $p['updated']);
			$block['api']['pages'][ $i ] = $p;
		}

		//print_r($block['api']['pages']);
		$block['html']['type'] = !empty($_REQUEST['rss'])? 'RSS' : 'Sitemap';
		$this->view->setLayout("sitemap");
		$this->view->addBlock('main', $block, $this->class .'::renderSiteMap');
	}
			
	protected function renderIndex($app = '', $template = null) {
		$query = $this->db
			->table($this->table .' AS page')
			->where('type', $this->class)
			->select('page.id', 'page.type', 'page.subtype', 'page.slug', 'page.name', 'page.title', 'page.description', 'page.updated', 'page.image', 'page.public')
			->orderBy('updated', 'desc');
		//check if table user has been initialized
		if ($this->config['site']['user_site'] ?? $this->db->table('information_schema.tables')
			->where('table_schema', $this->db->raw('DATABASE()') )
			->where('table_name', $this->site_prefix .'_user')
			->get('table_schema')
			->all()
		){
			//leftJoin to get staff created pages
			$query = $query->leftJoin($this->table_user .' AS user', 'page.creator', '=', 'user.id') 
				->addSelect('user.handle AS creator_handle', 'user.name AS creator_name', 'user.image AS creator_avatar');
		}	
		$query = $this->getPublished($query);
			
		if ( empty($_REQUEST['current']) ){
			$_REQUEST['current'] = 1; //force non-ajax mode 
		}	
		$block = $this->pagination($query);

		$type = ($this->class === 'Product')? 'Store': $this->class; //product use store	
		if ($page = $this->readSlug('/'. $type, 'Link') ){//if Link to this page exists
			if ( ! $this->canView($page) ){	
				return false;
			}
			$block['api']['page'] = $page; 
		}

		if ( !empty($block['api']['rows']) ){
			foreach ($block['api']['rows'] AS $index => $item) {
				$item = $this->preparePage($item);
				if ( method_exists($this, 'getProductsMinPrice') ){
					//$pids_array[ $item['id'] ] = $item['id'];
					if ( !in_array($item['subtype'], $this->subtype) ){
						unset($item['subtype']); //for Appstore
					}
					$item['price']    = & $pids_array[ $item['id'] ]['price']; //reference to a future value
					$item['was'] 	  = & $pids_array[ $item['id'] ]['was']; 
					$item['variants'] = & $pids_array[ $item['id'] ]['variants']; 
				}	
				$block['api']['rows'][ $index ] = $item;				
			}
			if ( method_exists($this, 'getProductsMinPrice') ){
				$pids_array_keys = array_keys($pids_array);			
				$variants = $this->getProductsVariants($pids_array_keys);
				$prices   = $this->getProductsMinPrice($pids_array_keys);
				foreach ($prices as $index => $value) {
					$pids_array[ $index ]['price'] = $value['price'];
					$pids_array[ $index ]['was'] = $value['was'];
					$pids_array[ $index ]['variants'] = $variants[ $index ]??null;
				}
				if ($this->view->html){
					$block['html']['stock_checking'] = $this->getAppConfigValues('Core\\Cart', [], 'stock_checking');
					$link = $this->slug('Cart::add');				
					$block['links']['cart_add'] = 'https://'. $this->config['site']['account_url'] . $link .'?cors='. $this->hashify($link .'::'. $_SESSION['token']);
				}
			}	

			$block['template']['file'] = $template? $template : strtolower($this->class) ."_collection";
			if ($this->view->html){	
				if (empty($block['api']['page']) ){
					$block['api']['page']['name'] = $block['api']['page']['title'] = $this->pluralize($this->class);
				}			
				$block['links']['datatable']['creator'] = $this->slug('Profile::render'); 
			}
			
			$next_actions['Collection::getCollectionItems'] = [ $this->class, NULL, "slug" => 1 ];
			$this->router->registerQueue($next_actions);

			!empty($status) && $this->view->setStatus($status);		
			$this->runHook($this->class .'::'. __FUNCTION__, [ &$block ], 'silent', 'add_blocks');
			$this->view->addBlock('main', $block, $this->class .'::renderIndex');

			return $block['api']['page']??[
					'id' => $this->class, //should return an id
				];	
		} else {
			return false;
		}			 
	}

	public function renderCollection($full_path_without_query) {
		$page = $this->readSlug(trim($full_path_without_query, '/'), 'Collection', $this->class);
	
		if ( !empty($page['id']) ){
			if ( ! $this->canView($page) ){	
				return false;
			}

			$query = $this->db
				->table($this->table .' AS page')
				->join($this->site_prefix .'_location', 'page_id', '=', 'page.id')
				->where('app_type', 'collection')
				->where('app_id', $page['id'])
				->where('type', $this->class)
				->select('page.id', 'page.type', 'page.subtype', 'page.slug', 'page.name', 'page.title', 'page.description', 'page.updated', 'page.image', 'page.public')
				->orderBy('updated', 'desc');
			$query = $this->getPublished($query);

			if ( empty($_REQUEST['current']) ){
				$_REQUEST['current'] = 1; //force non-ajax mode 
			}	
			$block = $this->pagination($query);

			if ( !empty($block['api']['rows']) ){
				foreach ($block['api']['rows'] AS $index => $item) {
					$block['api']['rows'][ $index ] = $this->preparePage($item);		
				}
			}	
			//print_r($page);
			!empty($status) && $this->view->setStatus($status);

			$block['template']['file'] = "page_collection";
			$block['api']['page'] = $page;			
			//if ($this->view->html){				
			//	$block['html']['title'] = $page['name'];
			//}
			$this->runHook($this->class .'::'. __FUNCTION__, [ &$block ], 'silent', 'add_blocks');
			$this->view->addBlock('main', $block, $this->class .'::renderCollection');
			//$this->view->addBlock('right', ['output' => '<h1>This content is produced by module itself</h1>'], $this->class .'::renderCollection');
			$next_actions['Collection::getCollectionItems'] = [ $this->class, $page['id'], "slug" => 1 ];
			$this->router->registerQueue($next_actions);
			
			return $page;				 
		} else {
			return false;
		}
	}

	public function renderBreadcrumb($page_id, $app = 'Page', $app_render = false) {
		$breadcrumbs = [];
		$i = 0;
		do {
			$collection = $this->db
				->table($this->site_prefix .'_location AS relation')
				->join($this->table, 'app_id', '=', $this->table .'.id')
				->where('app_type', 'collection')
				->where('page_id', $page_id)
				->orderBy('relation.id','asc')						 
				->first(['app_id', 'type', 'subtype', 'name', 'slug']);
			if ($collection) {
				$collection = $this->preparePage($collection);
				$page_id = $collection['app_id'];
				unset($collection['app_id']);
				unset($collection['type']);
				unset($collection['subtype']);
				$breadcrumbs[ ] = $collection; 
			}
			$i++;
			if ($i > 10) break;	// 10 level is more than enough
		} while ($collection);	

		if ($app != 'Page') {
			if ($app_render){
				$collection['slug'] = $this->slug('App::render', ['app' => strtolower($app), 'slug' => ''] );
				$class = str_replace('Core', 'App', __NAMESPACE__) .'\\'. $app;
			} else {
				$collection['slug'] = $this->slug($app .'::render', ['slug' => ''] );
				$class = __NAMESPACE__ .'\\'. $app;
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
			$collection['name'] = $this->trans(($app == 'Product')? 'Store' : $label[4]??$this->formatAppLabel($app));
		} 	
		if (!empty($collection)) {
			$breadcrumbs[ ] = $collection; 	
		} 
		$breadcrumbs = array_reverse($breadcrumbs);
		if ($this->view->html){				
			$block['html']['breadcrumbs'] = $breadcrumbs;
			$this->view->addBlock('main', $block, 'Page::renderBreadcrumb');
		}

		return $breadcrumbs;
	}	

	public function renderLocales($page_id) {
		$c = $this->config['site'];
		if (count($c['locales']) > 1) {//only need when site has multiple locales
			$data = $this->db
				->table($this->table)
				->where('id', $page_id)
				->select('slug', 'type', 'subtype')
				->first();

			if ($data) {
				//$c = $this->config['site'];
				$base_path = rtrim($this->config['system']['base_path'], "/"); //if base_path is /, the leading / is also removed		
				$remove  = ($base_path)? $base_path .'/' : '/';
				//$remove .= ($c['locale'])? $c['locale'] .'/' : '';
				$slug = substr($this->generateSlug($data['slug'], $data['type'], $data['subtype']), strlen($remove));

				foreach ($c['locales'] as $short => $long) {
					if ($short == $c['language']) { //default language 
						$locales[ $short ] = '/'. $slug;
					} else {
						$locales[ $short ] = '/'. $short .'/'. $slug;
					}
				}
				if ($this->view->html){				
					$block['html']['locale_urls'] = $locales;
					$this->view->addBlock('main', $block, 'Page::renderLocales');
				}

				return $locales;
			}
		}	
		return false;
	}

	//cron to update page views
	public function cron(){
		if ( empty($this->redis) ){
			return false;
		}
    	$site_prefix = $this->site_prefix;
    	$db = $this->db;
    
        try {
        	$hourly = 'hits:'. $this->config['site']['id'] .':hourly:'. (((int) gmdate('H')?: 24) - 1); //last hour
        	if ($views = $this->redis->hGetAll($hourly) ){
	        	$query  = 'INSERT INTO '. $this->table .' (id, views) VALUES ';
	        	foreach ($views AS $pid => $view){
	        		if (is_numeric($pid)){
		        		$query .= '(?, ?),';
		        		$data[] = $pid;
		        		$data[] = $view;
	        		}
	        	}
	        	if (!empty($data)){
	        		$query = rtrim($query, ','); //remove the last ,
					$query .= ' ON DUPLICATE KEY UPDATE views = VALUE(views) + views'; 
					//echo $hourly . (count($data) < 200? $query : ' count: '. count($data)/2);
					$db->insert($query, $data);	        		
	        	}
        	}
        	$this->redis->expire($hourly, 4000);
	    } catch (\Exception $e){
	    	echo $e->getMessage();
	    }    
    }
}