<?php
namespace SiteGUI\Core;

class Menu {
	use Traits\Application { generateRoutes as trait_generateRoutes; }

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user); //construct Application
		$this->table = $this->site_prefix ."_menu";
		$this->requirements['CREATE'] 	= "Page::create";
		$this->requirements['PUBLISH'] 	= "Page::publish";	
	}

	/**
	* list all 
	* @return none
	*/
	public function main() {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('list');
		} else {
			if ($menus = $this->getMenus()){
				$block['api']['total'] = count($menus);					
				$block['api']['rows'] = $menus;					

				if ($this->view->html){				
					$block['html']['table_header'] = [
						'id' => $this->trans('ID'),
						'name' => $this->trans('Name'),
						'location' => $this->trans('Location'),
						'action' => $this->trans('Action'),
					];
					$links['edit'] = $this->slug('Menu::action', ["action" => "edit"] );
					$links['copy'] = $this->slug('Menu::action', ["action" => "copy"] );
					$links['delete'] = $this->slug('Menu::action', ["action" => "delete"] );
					$block['links'] = $links;	
					$block['template']['file'] = "datatable";		
				}
			} else {
				$status['result'] = "error";
				$status['message'][] = $this->trans('You have not created any :type', ['type' => 'Menu']);
				
				if ($this->view->html){				
					$status['html']['message_type'] = 'info';
					$status['html']['message_title'] = $this->trans('Information');	
					$link = $this->slug($this->class .'::action', ["action" => "edit"] );
			        $status['message'][ $link ] = $this->trans('Click here to create a new :type', ['type' => $this->class] );
				}
			}			
			!empty($status) && $this->view->setStatus($status);							
			$this->view->addBlock('main', $block, 'Menu::main');
		}						
	}

	protected function create($menu) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('create');
		} else {
			$data = $this->prepareData($menu);
			return $this->db
						->table($this->table)
						->insertGetId($data);	
		}		
	}

	protected function read($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('read');
		} else {
			$data = [];
			$data = $this->db
						 ->table($this->table)
						 ->where('id', intval($id))
						 ->first();

			return $data;
		}	
	}

	public function update($menu) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('update');
		}

		if ( ! $this->user->has($this->requirements['PUBLISH'])) {
			//$page = Page::read($menu["page-id"]);
			if ($menu["location"] == "Site" OR $page["published"] > 0){
				unset($menu["location"]); // only add location if page hasn't been published.				
			}
		}

		$assigned = !empty($menu['id'])? $this->getLocations($menu['id']) : null;
		if ( ! $this->user->has($this->requirements['PUBLISH']) AND !empty($assigned)){	// Assigned menu cannot be edited by unauthorized admin		
			$this->logActivity('Menu Update Denied. Creating a new one', $this->class .'.', $menu['id'], 'Warning');
			$menu['name'] .= " (Copied)";
			unset($menu['id']); //force creating a new menu
		}	
			
		if (empty($menu['id'])){
			if ($menu['id'] = $this->create($menu)) { // create a new menu	
				$status['message'][] = $this->trans(':item created successfully', ['item' => 'Menu']);						
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not created', ['item' => 'Menu']);						
			}		
		} else {
			$data = $this->prepareData($menu);

			if ($this->db->table($this->table)->where('id', $menu['id'])->update($data)) {
				$status['message'][] = $this->trans(':item updated successfully', ['item' => 'Menu']);		
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not updated', ['item' => 'Menu']);										
			}	
		}

		if (!empty($menu['location']) AND !empty($menu['section']) AND !empty($menu['id'])){
			$location['app_type'] = "menu";
			$location['app_id'] = $menu['id'];
			$location['location'] = $menu['location'];
			$location['section'] = $menu['section'];

			if ($menu['location'] != "Site" AND !empty($menu['page_id'])) {
				$location['page_id'] = $menu['page_id'];
			}
			$query = $this->db
				->table($this->site_prefix . '_location')
				->select('id');
			foreach ($location as $key => $value) {
				$query->where($key, $value);
			}
			$results = $query->first();			
			if (empty($results) AND $this->db->table($this->site_prefix . '_location')->insertGetId($location)) {
				$status2['message'] = $this->trans(':item added successfully', ['item' => 'Menu location']);						
				if ( !empty($status['result']) AND $status['result'] == 'error' ){
					unset($status);	//other widget fields are not changed	
				}
			} else {
				$status2['result'] = 'error';
				$status2['message'] = $this->trans(':item was not added', ['item' => 'Menu location']);										
			}	
			$this->view->setStatus($status2); 			
		}
		if ( !empty($status) ){
			$this->view->setStatus($status); //required here, after this line widget location may produce other status	
			$this->logActivity( implode('. ', $status['message']), $this->class .'.', $menu['id']);
		}

		if ($this->view->html) {				
			$this->edit($menu['id']);
		}	
	}

	public function delete($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('delete');
		} 

		$assigned = $this->db
						 ->table($this->site_prefix . '_location')
						 ->select('id')
					   	 ->where("app_id", intval($id))
						 ->where("app_type", "menu")
						 ->first();
		if ( ! $this->user->has($this->requirements['PUBLISH']) AND !empty($assigned)){	// Assigned menu cannot be deleted by unauthorized admin		
			$status['result'] = 'error';
			$status['message'][] = $this->trans('You cannot delete an in-use menu');
		} else {
			$result1 = $this->db
							->table($this->table)
							->delete(intval($id));
			$result2 = $this->db
							->table($this->site_prefix . '_location')
							->where('app_id', intval($id))
							->where('app_type', 'menu')
							->delete();
			
			if (!empty($result1) AND $result2 !== false) {
				$status['result'] = 'success';
				$status['message'][] = $this->trans(':item deleted successfully', ['item' => 'Menu']);				
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not deleted', ['item' => 'Menu']);				
			}
		}

		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);	

		$this->logActivity( implode('. ', $status['message']), $this->class .'.', $id);

		if ($this->view->html){				
			$this->main();
		}								
	}

	/**
	 * print out edit form
	 * @param  integer $id [description]
	 * @return [type]           [description]
	 */
	public function edit($id = 0){
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess($id? 'edit' : 'create');
		} else {		
			$menu_items = $this->getMenuItems();
			$menu = $this->read($id);
			if ( !empty($menu['data']) ){
				$menu['data'] = json_decode($menu['data']??'', true);
				foreach ( ($menu['data']??[]) as $level1){
					$lid1 = intval($level1['id']);
					$data[$lid1] = $menu_items[$lid1]??[];
					unset($menu_items[$lid1]);
					foreach ( ($level1["children"]??[]) as $level2){
						$lid2 = intval($level2['id']);
						$data[$lid1]["children"][$lid2] = $menu_items[$lid2]??[];
						unset($menu_items[$lid2]);
						foreach ( ($level2["children"]??[]) as $level3){
							$lid3 = intval($level3['id']);
							$data[$lid1]["children"][$lid2]["children"][$lid3] = $menu_items[$lid3]??[];
							unset($menu_items[$lid3]);
						}					
					}
			    }
			}    
		    if (!empty($data)) {
		    	$menu['data'] = $data;
		    } elseif ( count($menu_items) > 0 ){
		    	$menu['data'] = @array_slice($menu_items, 0, 1, true); // add one to start
		    } else {
		    	$menu['data'] = [];
		    }	
			$menu['locations'] = $this->getLocations($id);				

			$block['template']['file'] = "menu_edit";		
			$block['api']['menu'] = $menu;					

			if ($this->view->html){								
				$links['delete_location'] = $this->slug('Menu::deleteLocation', [] );
				$links['lookup']  = $this->slug('Lookup::now');
				$links['page_api'] = $this->slug('Page::update');
				$links['update'] = $this->slug($this->class .'::update');
				$links['main']   = $this->slug($this->class .'::main');
				if ( !empty($this->config['system']['sgframe']) ){
					$links['update'] .= '?sgframe=1';
					$links['main']   .= '?sgframe=1';
				}

				foreach ($menu_items AS $item) {
					//$item['type'] == 'Collection' AND $item['name'] = 'Collection: '. $item['name'];
					$type = ($item['subtype'] && $item['type'] != 'Product')? str_replace('App::', '', $item['subtype']) : $item['type'];
					if ($item['type'] == 'Collection') {
						$available_items[ $this->pluralize($type) ]['Collection'][ ] = $item;
					} else {
						$available_items[ $this->pluralize($type) ][ ] = $item;
					}	
				}
				$block['html']['menu_items'] = $available_items??[];
				$block['links'] = $links;
				$block['html']['sections'] = $this->core_blocks;
				foreach ($this->core_blocks AS $blk) { 
					$main['html']['sections'][ ] = 'content_'. $blk;
				}			
			}

			$this->view->addBlock('main', $block, 'Menu::edit');
		}					
	}
	public function copy($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('copy');
		} else {
			$menu_info = $this->read($id);
			if ($menu_info){
				$menu_info['name'] .= " (Copied)";
				$data = $this->prepareData($menu_info);
				$data['data'] = $menu_info['data']; // little hack to make it work

				$new_id = $this->db
							   ->table($this->table)
							   ->insertGetId($data);
				if ($new_id) {
					$status['result'] = 'success';
					$status['message'][] = $this->trans(':item copied successfully', ['item' => 'Menu']);
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not copied', ['item' => 'Menu']);					
				}
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('Original :item cannot be read', ['item' => 'Menu']);						
			}

			!empty($status) && $this->view->setStatus($status);							
			$this->logActivity( implode('. ', $status['message']), $this->class .'.', $new_id);

			if ($this->view->html AND !empty($new_id)) {				
				$this->edit($new_id);
			}				
		}		
	}

	protected function prepareData($data) {
		$result['name'] = (!empty($data['name']))? $data['name'] : "Menu ". date("H.i");	
		$result["data"] = html_entity_decode($data['data']);
		return $result;		
	}

	protected function getMenuItems($with_slug = false) {
		$results = $this->db->select('SELECT id, type, subtype, name, slug FROM '. $this->site_prefix .'_page
			WHERE published > 0 AND (menu_id > 0 OR type = "Collection")');
		$data = [];
		foreach ($results AS $item) {
			$item['name'] = $this->getRightLanguage(json_decode($item['name']??'', true));
			if (!empty($with_slug)){
				$item['slug'] = $this->generateSlug($item['slug'], $item['type'], $item['subtype']);	
			}
			$data[ $item['id'] ] = $item;
		}
		return $data;			
	}

	protected function getLocations($id) {
		$data = [];
		try {
			$results = $this->db
				->table($this->site_prefix .'_location AS l')
				->leftJoin($this->site_prefix .'_page AS p', 'p.id', '=', 'l.page_id')
				->where('app_type', 'menu')
				->where('app_id', $id)
				->select('l.id', 'app_id', 'location', 'section', 'page_id', 'name', 'published')
				->get()->all();
			foreach ($results AS $location) {
				$location['name'] = $this->getRightLanguage(json_decode($location['name']??'', true));
				$data[] = $location;
			}
		} catch (\Exception $e){

		}	
		return $data;			
	}	
	public function deleteLocation($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('delete location');
		} 
		$current = $this->db->select('SELECT l.id, app_id, location, page_id, name, title, published 
				FROM '. $this->site_prefix .'_location AS l 
				LEFT JOIN '. $this->site_prefix .'_page AS p ON p.id = l.page_id 
				WHERE l.id = '. intval($id));
		$current = $current[0];
		if ($this->user->has($this->requirements['PUBLISH']) OR ($current['location'] != "Site" AND empty($current['published']))) {	// delete by admin or on unpublished page only
			$result = $this->db
				->table($this->site_prefix . '_location')
			   	->delete(intval($id));
			
			if (!empty($result)) {
				$status['result'] = 'success';
				$status['message'][] = $this->trans(':item deleted successfully', ['item' => 'Location']);				
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not deleted', ['item' => 'Location']);				
			}
		} else {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('You cannot delete an in-use menu');
		}

		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);								
	}

	public function generateRoutes($extra = []) {
		$name  = strtolower($this->class);
		$extra['deleteLocation'] = ['POST', '/[i:site_id]/'. $name .'/delete/location.[json:format]?/[POST:id]?'];

		return $this->trait_generateRoutes($extra);
	}
	
	public function getMenus() {
		try {
			$locs = $this->db
				->table($this->site_prefix .'_location AS l')
				->leftJoin($this->site_prefix .'_page AS p', 'p.id', '=', 'l.page_id')
				->select('app_id', 'location', 'section', 'page_id', 'name')
				->where("app_type", "menu")
				->get()->all();
			$locations =[];			 
			foreach ($locs AS $loc) {
				$loc['name'] = $this->getRightLanguage(json_decode($loc['name']??'', true));
				if ($this->view->html){
					$locations[ $loc["app_id"] ] = ($locations[ $loc["app_id"] ]??''). $this->trans($loc['location']);
					if ($loc['name']) {
						$locations[ $loc["app_id"] ] .= ': '. $loc['name']; 
					} elseif ($loc['page_id']) {
						$locations[ $loc["app_id"] ] .= '#'. $loc['page_id'];  
					}
					$locations[ $loc["app_id"] ] .= ' @ '. $this->trans(ucfirst($loc['section'])) . ', ';
				} else {
					$locations[ $loc["app_id"] ][] = $loc;
				}	 		
			}

			$data = [];
			$data = $this->db
				->table($this->table)
				->select("id", "name")
				->get()->all();	
			foreach ($data AS $menu) {
				if ( isset($locations[ $menu['id'] ]) ){
					$menu['location'] = is_string($locations[ $menu['id'] ])? @trim($locations[ $menu['id'] ], ', ') : $locations[ $menu['id'] ];	//remove the last comma
				} else {
					$menu['location'] = '';
				}	
				$menus[] = $menu;
			}
		} catch (\Exception $e){
			
		}	
		
		return $menus??[];
	}

	public function addItemToMenu($id, $page_id) {
		$assigned = $this->getLocations($id);
		if ($this->user->has($this->requirements['PUBLISH']) OR empty($assigned)){	// Assigned menu cannot be edited by unauthorized admin	
			if (!empty($id) AND !empty($page_id)) {
				$menu_info = $this->read($id);
				$menu_info['data'] = json_decode($menu_info['data']??'', true);
				$menu_info['data'][] = ["id" => intval($page_id) ];
				$data['data'] = json_encode($menu_info['data']);
				return $this->db
					 		->table($this->table)
					 		->where('id', $menu_info['id'])
					 		->update($data);
			}
		}	
		return false;
	}	
	/*
	Description: this function combines multiple menus at the same section into one single menu, children items of the same level parent in different menus will be merged. Currently we support 3 level menu only
	menuitem[name] => 
        	[icon] => 
        	[slug] => #
        	[target/id]
        	[type] - optional
        	[active] => 1
        	[children] =>
	 */
	public function render($page_id, $app = 'Page') {
		$results = $this->db
			->table($this->table .' AS m')
			->join($this->site_prefix .'_location AS l', 'm.id', '=', 'l.app_id')
			->where('app_type', 'menu')
			->where(function ($query) use ($page_id, $app) {
		  		$query->where('location', 'Site')
		  			  ->orWhere('page_id', $page_id)	
		  			  ->orWhere(function ($query) use ($app) {
		  					$query->where('location', $app)
		  			  			  ->whereNull('page_id');
		  				});
		  	})
		  	->select('*')
		  	->get()->all();

		$menu_items = $this->getMenuItems("with_slug");
		foreach ($results AS $menu) {
			$menu['data'] = json_decode($menu['data']??'', true);
			$pointer = & $blocks[ $menu['section'] ]['menu']; // we use pointer/reference here
			foreach ( ($menu['data']??[]) as $level1){
				$lid1 = intval($level1['id']);
				if (!empty($menu_items[$lid1]) AND empty($pointer[$lid1])){
					$pointer[$lid1] = $menu_items[$lid1];
				}
				foreach ( ($level1["children"]??[]) as $level2){
					$lid2 = intval($level2['id']);
					if (!empty($menu_items[$lid2]) AND empty($pointer[$lid1]["children"][$lid2])){
						if ($lid2 == $page_id) {
							$pointer[$lid1]['active'] = 1;
							$menu_items[$lid2]['active'] = 1;
						}
						$pointer[$lid1]["children"][$lid2] = $menu_items[$lid2];
					}
					foreach ( ($level2["children"]??[]) as $level3){
						$lid3 = intval($level3['id']);
						if (!empty($menu_items[$lid3]) AND empty($pointer[$lid1]["children"][$lid2]["children"][$lid3])){
							if ($lid3 == $page_id) {
								$pointer[$lid1]["children"][$lid2]['active'] = 1;
								$menu_items[$lid3]['active'] = 1;
							}
							$pointer[$lid1]["children"][$lid2]["children"][$lid3] = $menu_items[$lid3];
						}
					}					
				}
		    }

			if ( ! empty($menu['template']['string']) ) {
				$blocks[ $menu['section'] ]['template']['string'] = $menu['template']['string'];
			} elseif ( ! empty($menu['template']['file']) ) {
				$blocks[ $menu['section'] ]['template']['file'] = $menu['template']['file'];
			}	
		}
		foreach ( ($blocks??[]) as $section => $block) {
			$block['order'] = 0; //set order
			$this->view->addBlock($section, $block, 'Menu::render');		
		}
	}	
}