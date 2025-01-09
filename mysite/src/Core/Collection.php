<?php
namespace SiteGUI\Core;

class Collection extends Page {
	use Traits\ProductVariant;

	public function __construct($config, $dbm, $router, $view, $user){
		parent::__construct($config, $dbm, $router, $view, $user);
		$this->table_product = $this->site_prefix ."_product";
	}	
	/**
	* list all collection 
	* @return none
	*/
	public function main($type = '') {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('list');
		} else {
			if (empty($type)) {
				$type = $this->class;		
			}
			$query = $this->db->table($this->table .' AS page')
				->leftJoin($this->site_prefix .'_location AS l1', 'page.id', '=', 'page_id')
				->leftJoin($this->table .' AS parent', 'parent.id', '=', 'l1.app_id')
				->select('page.id', 'page.name', 'page.subtype AS type', 'page.slug', 'parent.name AS description', 'page.published')
				->where('page.type', $type)
				->where(function($query){
					$query->where('l1.app_type', 'collection')
						->orWhereNull('l1.app_type');
				});
			$block = $this->pagination($query);
			if ( $block['api']['total'] < 1 ) {
				$status['result'] = "error";
				$status['message'][] = !empty($_REQUEST['searchPhrase'])? $this->trans('Your search returns no matches') : $this->trans('You have not created any :type', ['type' => $type]);
				
				if ($this->view->html){				
					$status['html']['message_type'] = 'info';
					$status['html']['message_title'] = $this->trans('Information');	
					$link = $this->slug($this->class .'::action', ["action" => "edit"] );
			    $status['message'][ $link ] = $this->trans('Click here to create a new :type', ['type' => $this->class] );
				}
			} else {	
				foreach ($block['api']['rows'] AS $key => $data){
					if ( strpos($data['type'], 'App::') === 0 ){
						$data['type'] = str_replace('_', ' ', substr($data['type'], 5));
					}
					$block['api']['rows'][ $key ] = $this->preparePage($data, 0);
				}
				//$block['api'][ strtolower($this->pluralize($type)) ] = $pages;					
				if ($this->view->html){				
					$block['html']['table_header'] = [
						'id' => $this->trans('ID'),
						'name' => $this->trans('Name'),
						'type' => $this->trans('Type'),
						'slug' => $this->trans('Slug'), 
						'description' => $this->trans('Parent Collection'),
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

	public function edit($id = 0, $menus = 'Menu::getMenus'){
		if ( $this->user->has($this->requirements['CREATE']) OR ($this->user->has($this->requirements['READ']) AND $id) ){
			if ( empty($_REQUEST['subapp']) ){
				parent::edit($id, $menus); //Page->edit
				if ( empty($_REQUEST['frame']) ){
					$block = $this->view->getBlock('main', 'Collection::edit');

					$page_info = $block['api']['page']??null;
					if( !empty($page_info['id']) AND $page_info['type'] == 'Collection' ){
						//$block['api']['collection'] = 
					}
					unset($block['api']['app']['hide']['tabapp']);

					if ($this->view->html){				
						$block['template']['file'] = "collection_edit";		
						$block['links']['subapp'] = $block['links']['editor'];
						$block['html']['ajax'] = 1;
					}
					$this->view->addBlock('main', $block, 'Collection::edit');
				}	
			} elseif ($_REQUEST['subapp'] == 'items') {
				$query = $this->db
					->table($this->table .' AS page')
					->join($this->site_prefix .'_location AS l', 'l.page_id', '=', 'page.id')
					->where('app_id', $id)					
					->where('app_type', 'collection')
					->select('page.id', 'page.type', 'page.subtype', 'page.slug', 'page.name');
				$block = $this->pagination($query);
				if ( $block['api']['total'] ) {
					foreach ($block['api']['rows'] AS $key => $data){
						if ( $data['type'] == 'App' ){
							$app_type = $data['type'] = $data['subtype'];
						}
						$block['api']['rows'][ $key ] = $this->preparePage($data, 0);
					}
					//$block['api'][ strtolower($this->pluralize($type)) ] = $pages;					
					if ($this->view->html){				
						$block['html']['table_header'] = [
							'id' => $this->trans('ID'),
							'name' => $this->trans('Name'),
							'type' => $this->trans('Type'),
							'slug' => $this->trans('Slug'), 
							'published' => $this->trans('Published'),
							'action' => $this->trans('Action'),
						];
						if ( !empty($app_type) ){
							$links['edit'] = $this->slug('App::action', ["action" => "edit", "id" => strtolower($app_type) ] );
						} else {	
							$links['edit'] =   $this->slug($data['type'] .'::action', ["action" => "edit"] );
						}	
						$links['collection'] =   $this->slug('Collection::action', ["action" => "edit"] );
						$block['links'] = $links;					
					}
				} 

				!empty($status) && $this->view->setStatus($status);							
				$this->view->addBlock('main', $block, 'Collection::Items'); 
			} elseif ($_REQUEST['subapp'] == 'Activities') {
				$app = $this->getAppInfo($this->class, 'Core'); 
				$this->editSubApps(['id' => $id], $app, $this->user->has($this->requirements['PUBLISH']));
			}	
		} else {
			$this->denyAccess($id? 'edit' : 'create');			
		}					
	}

	public function getCollectionItems($app, $cid = 0, $slugify = 0) {
		$query = $this->db
			->table($this->table .' AS page')
			->where('main.app_type', 'collection')
			->where('type', 'Collection')
			->where('subtype', $app)
			->select('page.id', 'page.type', 'page.subtype', 'page.slug', 'page.name', 'page.public')//, 'sub.location')
			->selectRaw('COUNT(*) AS quantity'); 
		if (!empty($cid)) { //get specific collection, main is use for collection name, sub is page in the collection
			$query->join($this->site_prefix .'_location AS main', 'main.page_id', '=', 'page.id')
				->join($this->site_prefix .'_location AS sub',  'main.page_id', '=', 'sub.app_id')
				->where('main.app_id', intval($cid))
				->where('sub.app_type', 'collection')
				->where('sub.location', '') //dont count entry that is also a collection
				->groupBy('sub.app_id');
		}	else { //get all top level collections
			$query->join($this->site_prefix .'_location AS main', 'main.app_id', '=', 'page.id')
				->whereNotIn('page.id', function($query){ //join is slower in this case
					$query->select('page_id')
						->from($this->site_prefix .'_location')
						->where('app_type', 'collection'); //only sub-collections have their ids stored as page_id in the parent collection row
				})
				->where('main.location', '') //dont count entry that is also a collection (has root id or its id here)
				->groupBy('main.app_id');
		}	
		$query = $this->getPublished($query);

		$results = $query->get()->all();
		$collections = [];
		foreach ($results AS $item) {
			$collections[] = $this->preparePage($item, $slugify);
		}
		$block['api']['has_collections'] = array_column($collections, NULL, 'id'); //index category for easier lookup at frontend 
		$this->view->addBlock('main', $block, 'Collection::getCollectionItems::'. $app);		
	}	

	public function getRelatedItems($pid = 0, $slugify = 0, $pricing = 0) {
		$query = $this->db
			->table($this->table .' AS page')
			->join($this->site_prefix .'_location', 'page_id', '=', 'page.id')
			->where('app_type', 'collection')
			->where('page.type', '<>', 'Collection') //exclude sub-collection
			->where('page.id', '<>', intval($pid)) //exclude this page
			->orderBy('updated', 'desc');
		$query = $this->getPublished($query);		
		
		if (!empty($pid)) { //get all collections
			$query = $query->whereIn('app_id', function ($query) use ($pid) {
        $query->select('app_id')
       	  ->from($this->site_prefix . '_location')
       	  ->where('app_type', 'collection')	
          ->where('page_id', intval($pid));
      });
		}

		$results = $query->distinct()
			->take(8)
			->get(['page.id', 'type', 'subtype', 'slug', 'name', 'description', 'image', 'page.public'])->all();
		$related = [];
		foreach ($results AS $index => $row) {
			$row = $this->preparePage($row, $slugify);
			if ( !empty($pricing) ){
				$row['price']    = & $pids_array[ $row['id'] ]['price']; //reference to a future value
				$row['was'] = & $pids_array[ $row['id'] ]['was'];
				$row['variants'] = & $pids_array[ $row['id'] ]['variants'];
			}
			$related[ $index ] = $row;
		}
		if (!empty($pids_array) ){
			$pids_array_keys = array_keys($pids_array);
			$this->table_product = $this->site_prefix ."_product"; //set for getProductsVariants
			$variants = $this->getProductsVariants($pids_array_keys);
			$prices   = $this->getProductsMinPrice($pids_array_keys);

			foreach ($prices??[] as $index => $value) {
				$pids_array[ $index ]['price'] = $value['price'];
				$pids_array[ $index ]['was'] = $value['was'];
				$pids_array[ $index ]['variants'] = $variants[ $index ]??null;
			}
		}

		$block['api']['collections_items'] = $related;
		$this->view->addBlock('main', $block, 'Collection::getRelatedItems');		
	}	

	public function getCollectionsByPageId($pid = 0, $slugify = 0) {
		$query = $this->db
			->table($this->table .' AS page')
			->join($this->site_prefix .'_location AS l2', 'l2.app_id', '=', 'page.id')
			->leftJoin($this->site_prefix .'_location AS l1', 'l1.page_id', '=', 'l2.app_id')
			->leftJoin($this->table .' AS parent', 'l1.app_id', '=', 'parent.id')
			->leftJoin($this->table .' AS root', function($join){
				$join->on('l1.location', '=', 'root.id')
					->whereColumn('l1.location', '<>', 'l1.app_id');
			})
			->where('l2.app_type', 'collection')
			->where('l2.page_id', $pid)
			->where(function($query){
				$query->where('l1.app_type', 'collection')
					->orWhereNull('l1.app_type');
			})
			//->selectRaw("l2.id, page.type, page.subtype, page.slug, CONCAT_WS(' > ', JSON_UNQUOTE( JSON_EXTRACT(grand.name, '$.". $this->sanitizeFileName($this->config['site']['language']) ."')), JSON_UNQUOTE( JSON_EXTRACT(page.name, '$.". $this->sanitizeFileName($this->config['site']['language']) ."')) ) AS name")
			->selectRaw("l2.id, page.type, page.subtype, page.slug, page.name, 'page.public', CONCAT_WS(' ⇢ ', JSON_UNQUOTE( JSON_EXTRACT(root.name, '$.". $this->sanitizeFileName($this->config['site']['language']) ."')), JSON_UNQUOTE( JSON_EXTRACT(parent.name, '$.". $this->sanitizeFileName($this->config['site']['language']) ."'))) AS parent")
			->orderBy('root.id');
		if ($slugify){ //
			$query = $this->getPublished($query);			
		} else {
			$query->addSelect('page.published'); //for backend
		}
		
		$collections = [];	
		foreach ($query->get()->all() AS $item) {
			$collections[] = $this->preparePage($item, $slugify);
		}
		$block['api']['collections'] = $collections;
		$this->view->addBlock('main', $block, 'Collection::getCollectionsByPageId');
	}

	public function removeCollectionsByPageId($pid) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('remove from collection');
		} 
		// remove reference to this page from all collections
		return $this->db
			 		->table($this->site_prefix .'_location')
					->where('app_type', 'collection')
			 		->where('page_id', $pid)
			 		->delete();
	}
	/*
	 * Add a page to collection(s)
	 */
	public function join($pid, $collections, $subtype = 'Page', $sub = false) {
		// User can create collection, add item to published collection if they can create page
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('update');
			return false;
		} 

		if ($pid) {
			if ( is_array($collections) ){
				$use_id = 1; //search existing collection when array (may contain both IDs and string) is provided
			} else {
				$collections = explode(",", $collections);
			}
			foreach($collections AS $name) {
				$name = trim($name);
				if (!empty($name)) {
					$collection['name'][ $this->config['site']['language'] ] = $name; //name is multilingual
					$collection['slug'] = $this->sanitizeFileName($name);

					$current = $this->db
						->table($this->table)
						->where('type', 'Collection')
						->where('subtype', $subtype)
						->where('slug', $collection['slug'])
						->when( !empty($use_id), function($query) use ($collection) {
							return $query->orWhere('id', $collection['slug']);
						})
						->select('id AS app_id', 'published')
						->first();

					if (empty($current)) {
						$collection['type'] = 'Collection';
						$collection['subtype'] = $subtype;
						$collection['title'] = $collection['name'];
						$collection['breadcrumb'] = 1; 
						$collection['published'] = 1;

						if ( !($current['app_id'] = $this->create($collection)['id']) ){ //create a page of type Page::Collection
							continue; 
						}	
					} // else $current['app_id'] is retrieved from database query above

					//if ($this->user->has($this->requirements['PUBLISH']) OR empty($current["published"] ) ) { 
					//add to published collection is ok as it just loads published records only
					$columns = ['app_type', 'app_id', 'page_id'];
					$insert_data[] = 'collection';
					$insert_data[] = $current['app_id'];
					$insert_data[] = intval($pid);
					if ($sub) {
						//get root id of current parent
						$root_id = $this->db->table($this->site_prefix .'_location')
							->where('app_type', 'collection')
							->where('page_id', $current['app_id'])
							->value('location');
						$insert_data[] = $root_id??$current['app_id']; //root collection
						$columns[] = 'location';
					} 
					//}	
				}
			}
			if (!empty($insert_data)) {
				if ($this->upsert($this->site_prefix .'_location', $columns, $insert_data, ['id' => 'id'])) {
					//$status['message'][] = $this->trans('Collection added successfully');						
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not added', ['item' => 'Collection']);										
				}		
			}
		}	
		!empty($status) && $this->view->setStatus($status);
	}
	/*
	 * Add a page to exiting collection(s) by client: user collection_ids instead
	 */
	public function joinExisting($pid, $collection_ids, $subtype = 'Page', $sub = false) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('update existing collection');
		} 

		if ($pid) {
			if ( !is_array($collection_ids) ){
				$collection_ids = explode(",", $collection_ids);
			}
			foreach($collection_ids AS $cid) {
				if (!empty($cid)) {
					$existing = $this->db
						->table($this->table)
						->where('type', 'Collection')
						->where('subtype', $subtype)
						->where('id', $cid)
						->value('id');

					if ( $existing ) {
						$columns = ['app_type', 'app_id', 'page_id'];
						$insert_data[] = 'collection';
						$insert_data[] = $cid;
						$insert_data[] = $pid;
						if ($sub) {
							//get root id of current parent
							$root_id = $this->db->table($this->site_prefix .'_location')
								->where('app_type', 'collection')
								->where('page_id', $cid)
								->value('location');
							$insert_data[] = $root_id??$cid; //root collection
							$columns[] = 'location';
						}  
					}	
				}
			}
			if (!empty($insert_data)) {
				if ( $this->upsert($this->site_prefix .'_location', $columns, $insert_data, ['id' => 'id']) ){
					//$status['message'][] = $this->trans('Collection added successfully');						
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not added', ['item' => 'Collection']);										
				}		
			}
		}	
		!empty($status) && $this->view->setStatus($status);
	}
	/*
	/*
	 * Remove item from a collection
	 */
	public function leave($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('leave collection');
		} 
		$published = $this->db->table($this->site_prefix .'_location AS relation')
			->join($this->table .' AS page', 'page_id', '=', 'page.id')
			->where('relation.id', $id)
			->value('published');

		if ($this->user->has($this->requirements['PUBLISH']) OR empty($published)) {	// delete by admin or on unpublished page only
			$result = $this->db
				->table($this->site_prefix . '_location')
				->where('id', intval($id))
				->delete();
		   
			if (!empty($result)) {
				$status['result'] = 'success';
				$status['message'][] = $this->trans('Page is removed from collection successfully');				
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('Page was not removed from collection');				
			}
		} else {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('You cannot remove a published page from collection');
		}

		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);								
	}
	public function delete($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('delete');
		} 

		$page_info = $this->read($id, 'Collection');
		if ( !$this->user->has($this->requirements['PUBLISH']) AND $page_info["published"] > 0){	//Published page cannot be deleted by unauthorized admin		
			$status['result'] = 'error';
			$status['message'][] = $this->trans("You don't have permissions to :action", ['action' => 'delete a published item']);
		} else {

			if ($this->db->table($this->table)->where('type', 'Collection')->delete(intval($id))) {
				$status['message'][] = $this->trans(':item deleted successfully', ['item' => $this->class ]);	
				if (method_exists($this, 'deleteVariants')) { //only for inherited classes using ProductVariant trait
					$this->deleteVariants($page_info['id']);
				}
				$this->deleteVersions($page_info['id']);
				$this->deleteMeta($page_info['id']);
				// remove reference to this page from all collections
				$this->db
			 		->table($this->site_prefix .'_location')
					->where('app_type', 'collection')
			 		->where('app_id', $id)
			 		->delete();

			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not deleted', ['item' => 'Collection']);				
			}
		}

		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);	
		$this->logActivity( implode('. ', $status['message']), $this->class .'.', $page_info['id']??null );

		if ($this->view->html){				
			$this->main();
		}									
	}

	public function generateRoutes($extra = []) {
		$name  = strtolower($this->class);
		// Add route to remove collectionId from a page
		$extra['leave'] = ['POST', '/[i:site_id]/'. $name .'/leave.[json:format]?/[POST:id]?'];

		return parent::generateRoutes($extra);
	}	

	protected function prepareData($page) {
		$page = parent::prepareData($page);
		if (empty($page['subtype'])) {
			$page['subtype'] = 'Page';
		}
		return $page;
	}	
}