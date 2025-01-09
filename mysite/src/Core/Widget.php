<?php
namespace SiteGUI\Core;

class Widget {
	use Traits\Application { generateRoutes as trait_generateRoutes; }

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user); //construct Application
		$this->table = $this->site_prefix ."_widget";
		$this->requirements['CREATE'] 	= "Page::create";
		$this->requirements['PUBLISH'] 	= "Page::publish";	
		if (!empty($this->config['system']['base_dir'])) {
			$this->path = $this->config['system']['base_dir'] .'/resources/public/templates/app/';
		} else {
			echo "Template directory is not defined!";
		}
	}

	/**
	* list all 
	* @return none
	*/
	public function main() {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('list');
		} else {
			$locs = $this->db
				->table($this->site_prefix .'_location AS l')
				->leftJoin($this->site_prefix .'_page AS p', 'p.id', '=', 'l.page_id')
				->select('app_id', 'location', 'section', 'page_id', 'name', 'sort')
				->where("app_type", "widget")
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
					$locations[ $loc["app_id"] ] .= ' @ '. $this->trans(ucwords($loc['section'], '_'));
					if ($loc['sort']){
						$locations[ $loc["app_id"] ] .= ' ⇅'. $loc['sort'];
					}
					$locations[ $loc["app_id"] ] .= ', ';
				} else {
					$locations[ $loc["app_id"] ][] = $loc;
				}	 		
			}

			$query = $this->db
				->table($this->table .' AS w')
				->leftJoin($this->site_prefix .'_location AS l', function($join){
					$join->on('w.id' ,'=', 'l.app_id')
					->where('app_type', 'widget');
				})
				//->where('app_type', 'widget')
				//->orWhereNull('app_type')
				->where('type', '<>', 'Site::Report')
				->selectRaw('w.id AS id, name, type')
				->groupBy('id')
				->orderByRaw('ISNULL(sort), sort, location DESC, section DESC'); //null last

			$block = $this->pagination($query);			 
			foreach ( ($block['api']['rows']??[]) AS $key => $w) {
				$block['api']['rows'][ $key ]['location'] = is_array($locations[ $w['id'] ]??null)? $locations[ $w['id'] ] : @trim($locations[ $w['id'] ], ', ');//remove the last comma for string
			}
			if ( $block['api']['total'] ){
				if ($this->view->html){				
					$block['template']['file'] = "datatable";		
					$block['html']['table_header'] = [
						'id' => $this->trans('ID'),
						'name' => $this->trans('Name'),
						'type' => $this->trans('Type'),
						'location' => $this->trans('Location'), 
						'action' => $this->trans('Action'),
					];

					$links['api'] = $this->slug('Widget::main');
					$links['edit'] = $this->slug('Widget::action', ["action" => "edit"] );
					$links['copy'] = $this->slug('Widget::action', ["action" => "copy"] );
					$links['delete'] = $this->slug('Widget::action', ["action" => "delete"] );
					$block['links'] = $links;					
				}
			} else {
				$status['result'] = "error";
				$status['message'][] = $this->trans('You have not created any :type', ['type' => 'Widget']);
				
				if ($this->view->html){				
					$status['html']['message_type'] = 'info';
					$status['html']['message_title'] = $this->trans('Information');	
					$link = $this->slug($this->class .'::action', ["action" => "edit"] );
			        $status['message'][ $link ] = $this->trans('Click here to create a new :type', ['type' => $this->class] );
				}
			}			
			!empty($status) && $this->view->setStatus($status);							
			$this->view->addBlock('main', $block, 'Widget::main');
		}						
	}

	public function create($widget) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('create');
		} else {
			$data = $this->prepareData($widget);
			return $this->db
						->table($this->table)
						->insertGetId($data);	
		}		
	}

	public function read($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('read');
		} else {
			$widget = $this->db
				 ->table($this->table)
				 ->where('id', $id)
				 ->where('type', '<>', 'Site::Report')				 
				 ->first();
	
			if(!empty($widget['data'])){
				$widget['data'] = json_decode($widget['data']??'', true);
			}
			if(!empty($widget['cache'])){
				$widget['cache'] = json_decode($widget['cache']??'', true);
			}		
			return $widget;
		}	
	}

	public function update($widget) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('update');
		}

		$assigned = !empty($widget['id'])? $this->getLocations($widget['id']) : null;
		if ( ! $this->user->has($this->requirements['PUBLISH']) AND !empty($assigned)){	// Assigned widget cannot be edited by unauthorized admin		
			$this->logActivity('Widget Update Denied. Creating a new one', $this->class .'.', $widget['id'], 'Warning');
			$widget['name'] .= " (Copied)";
			unset($widget['id']); //force creating a new widget
		}	
			
		if (empty($widget['id'])){
			if ($widget['id'] = $this->create($widget)) { // create a new widget	
				$status['message'][] = $this->trans(':item created successfully', ['item' => 'Widget']);						
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not created', ['item' => 'Widget']);						
			}		
		} elseif ( !empty($widget['placements']) ){
			foreach ($widget['placements'] AS $lid => $placement){
				$upsert[] = intval($lid); //id
				$upsert[] = 'widget'; //app_type				
				$upsert[] = intval($widget['id']); //app_id
				$upsert[] = intval($placement);	//order
			}
			if ( !empty($upsert) ){
				if ( $this->upsert($this->site_prefix .'_location', ['id', 'app_type', 'app_id', 'sort'], $upsert, 'sort') ){
					$status['message'][] = $this->trans(':item updated successfully', ['item' => 'Widget']);		
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not updated', ['item' => 'Widget']);
				}
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('Nothing to update');				
			}
		} else {	
			$data = $this->prepareData($widget);

			if ($this->db
					 ->table($this->table)
					 ->where('id', $widget['id'])
					 ->update($data)) {
				$status['message'][] = $this->trans(':item updated successfully', ['item' => 'Widget']);		
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not updated', ['item' => 'Widget']);		
			}	
		}

		if (!empty($widget['location']) AND !empty($widget['section']) AND !empty($widget['id'])){
			$location['app_type'] = "widget";
			$location['app_id'] = $widget['id'];
			$location['location'] = $widget['location'];
			$location['section'] = $widget['section'];

			if ($widget['location'] != "Site" AND !empty($widget['page_id'])) {
				$location['page_id'] = $widget['page_id'];
			}
			$query = $this->db
				->table($this->site_prefix . '_location')
				->select('id');
			foreach ($location as $key => $value) {
				$query->where($key, $value);
			}				
			$results = $query->first();			

			if ( !empty($widget['sort']) AND is_numeric($widget['sort']) ){
				$location['sort'] = intval($widget['sort']);
			}

			if (empty($results) AND $this->db->table($this->site_prefix . '_location')->insertGetId($location)) {
				if ( !empty($status['result']) AND $status['result'] == 'error' ){
					unset($status);	//other widget fields are not changed	
				} 			
				$status['message'][] = $this->trans(':item added successfully', ['item' => 'Widget location']);
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not added', ['item' => 'Widget location']);										
			}	
		}

		if ($this->view->html) {
			$status['html']['message_title'] = $this->trans('Information');
			$link = $this->getSandboxUrl($this->slug('Widget::action', ["action" => "edit"] ), $widget['id'], $this->slug('Widget::update'));
			$status['message'][ $link ] = $this->trans('Click here to keep editing');
		}	
		if ( !empty($status) ){
			$this->view->setStatus($status); //required here, after this line widget location may produce other status	
			$this->logActivity( implode('. ', $status['message']), $this->class .'.', $widget['id'] );
		}	
	}

	function delete($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('delete');
		} 
		$assigned = $this->db
						 ->table($this->site_prefix . '_location')
						 ->select('id')
					   	 ->where("app_id", intval($id))
						 ->where("app_type", "widget")
						 ->first();

		if ( ! $this->user->has($this->requirements['PUBLISH']) AND !empty($assigned)){	// Assigned widget cannot be deleted by unauthorized admin		
			$status['result'] = 'error';
			$status['message'][] = $this->trans('You cannot delete an in-use widget');
		} else {
			$result1 = $this->db
							->table($this->table)
							->delete(intval($id));
			$result2 = $this->db
							->table($this->site_prefix . '_location')
							->where('app_id', intval($id))
							->where('app_type', 'widget')
							->delete();
			
			if (!empty($result1) AND $result2 !== false) {
				$status['result'] = 'success';
				$status['message'][] = $this->trans(':item deleted successfully', ['item' => 'Widget']);				
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not deleted', ['item' => 'Widget']);				
			}
		}

		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);	

		$this->logActivity( implode('. ', $status['message']), $this->class .'.', $id );

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
		// we hash the target URL and use it as the onetime CSRF token
		$links['update'] = $this->slug('Widget::update');
		$links['update'] .= !empty($this->config['system']['sgframe'])? '?sgframe=1' : '';
		$this->loadSandboxPage($this->slug('Widget::action', ["action" => "edit"] ), $id, $links['update']);

		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess($id? 'edit' : 'create');
		} else {
			if (is_numeric($id)) {
				$widget = $this->read($id);
				$widget['locations'] = $this->getLocations($id);				
			} elseif ($id != '') { // new widget
				$widget["type"] = $this->formatAppName($id); 
			} else {
				$widget = [];
			}			

			if (empty($widget["type"])) {
				$widget["type"] = "Text";
			}

			$app = $this->getAppInfo($widget['type'], 'Widget');
			$Class = $app['class']??null;
         if ( !empty($app['app_handler']) && substr($app['app_handler'], 0, 4) == 'http') {
				$this->checkActivation($Class);			
				$response = $this->httpPost($app['app_handler'] . 'edit', [
					"json" => json_encode(['site_config' => $this->config['site'] ] + $widget)
				]);
				$response = json_decode($response??'', true);
			} elseif (class_exists($Class)) {
				$instance = new $Class($this->config, $this->view, $this->dbm, $this->router, $this->user);		
				$this->checkActivation($instance);			
				$response = call_user_func([$instance, 'edit'], $widget);
			}		
			
			if (isset($response['result']) && $response['result'] === 'success') {
				if ( !empty($response['output']) ){
					$block['output'] = $response['output'];
				} elseif ( !empty($response['block']) ) {
					$block = $response['block'];
					if ( !empty($app['app_templates']['edit'])) {//override 
						$block['template']['file'] = $app['app_templates']['edit'];
					}
					if ( !empty($block['template']['file']) ){ //can be in app folder or system template folder 
						$block['template']['directory'] = 'resources/public/templates/app/'. strtolower($app['name']) . ($app['id']??''); 
						//Widget may have a custom layout for editing, layout set in Builder is preferred
						$layout = $app['app_layouts']['edit']??$block['template']['layout']??null; 
						if ($layout AND is_file($this->path .'/../admin/'. $this->config['system']['template'] .'/layouts/'. $layout .'.tpl') 
						){
							$this->view->setLayout($layout); //only set if app uses custom template otherwise default layout
						}
					}	
				}
			} elseif (!$app){
            $block['output'] = $this->trans('Invalid app name');
         } else {
				$block['output'] = 'Error: cannot process widget data';
				//print_r($response, true);
			}
			//raw data -> widget provider -> processed widget block stored in $block
			$this->view->addBlock('block_widget', $block, 'Widget::'. ($app['name']??'') .'::edit'); 
			$main['api']['app'] = $app['label']??$widget["type"];
			$main['api']['widget'] = $widget; //original data

			if ($this->view->html){				
				if (!empty($block['system']['visual_editor'])){
					$links['widget'] = $this->slug('Widget::preview');
					$links['widget'] .= '?cors='. @$this->hashify($links['widget'] .'::'. $_SESSION['token']);

					//$links['genai'] = $this->slug('Assistant::action', ['action' => 'generate']);
					//$links['genai'] .= '?cors='. @$this->hashify($links['genai'] .'::'. $_SESSION['token']);
					$links['snippet'] = $this->slug('Template::snippet');
					$links['snippet'] .= '?cors='. @$this->hashify($links['snippet'] .'::'. $_SESSION['token']);
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
					$main['api']['widgets'] = !empty($group)? $group : [];

					$next_actions['Template::getSnippets'] = [];
				}	

				$links['editor'] = $this->config['system']['edit_url'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
				$links['delete_location']  = $this->slug('Widget::deleteLocation', []);
				$links['delete_location'] .= '?cors='. @$this->hashify($links['delete_location'] .'::'. $_SESSION['token']);
				$links['lookup']  = $this->slug('Lookup::now');
				$links['lookup'] .= '?cors='. @$this->hashify($links['lookup'] .'::'. $_SESSION['token']);
				$links['site']    = $this->slug('Site::main') .'/'. $this->config['site']['id'];
				$links['main'] = $this->slug($this->class .'::main');
				$links['main'] .= !empty($this->config['system']['sgframe'])? '?sgframe=1' : '';

				$main['links'] = $links;	
				$main['html']['sections'] = $this->core_blocks;
				foreach ($this->core_blocks AS $blk) { 
					$main['html']['sections'][ ] = 'content_'. $blk;
				}
				//$main['html']['language'] = $this->config['site']['language'];
				$main['template']['file'] = "widget_edit";		
			}

			$this->view->addBlock('main', $main, 'Widget::edit');
			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}
		}					
	}
	public function copy($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('copy');
		} else {
			$widget_info = $this->read($id);
			if ($widget_info){
				$widget_info['name'] .= " (Copied)";

				$data = $this->prepareData($widget_info);
				//$data['data'] = $widget_info['data']; // little hack to make it work

				$new_id = $this->db
							   ->table($this->table)
							   ->insertGetId($data);
				if ($new_id) {
					$status['result'] = 'success';
					$status['message'][] = $this->trans(':item copied successfully', ['item' => 'Widget']);
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not copied', ['item' => 'Widget']);					
				}
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('Original :item cannot be read', ['item' => 'Widget']);						
			}

			!empty($status) && $this->view->setStatus($status);							
			$this->logActivity( implode('. ', $status['message']), $this->class .'.', $new_id );

			if ($this->view->html AND !empty($new_id)) {				
				$this->edit($new_id);
			}				
		}		
	}
	
	protected function getWidgetClass($slug) {
		return str_replace('Core', 'Widget\\', __NAMESPACE__) . $this->formatAppName($slug);
	}

	protected function prepareData($widget) {
		$data['name'] = (!empty($widget['name']))? $widget['name'] : "Widget ". date("H.i");
		$data['type'] = (!empty($widget['type']))? $this->formatAppName($widget['type']) : "Text";

		$app = $this->getAppInfo($data['type'], 'Widget');
		$Class = $app['class'];
		if ( !empty($app['app_handler']) AND substr($app['app_handler'], 0, 4) == 'http') {
			$this->checkActivation($Class);			
			$response = $this->httpPost($app['app_handler'] . 'update', [
				"json" => json_encode(['site_config' => $this->config['site'] ] + $widget)
			]);
			$response = json_decode($response??'', true);
			//var_dump($response);exit;
		} elseif (class_exists($Class)) {		
			$instance = new $Class($this->config, $this->view, $this->dbm, $this->router, $this->user);			
			$this->checkActivation($instance);			
			$response = call_user_func([$instance, 'update'], $widget);	
		}	

		if (isset($response['result']) AND $response['result'] == 'success') {
			$data['data'] = json_encode($response['data']??[]);
		}
		//we may receive cache directly from widget editor or update process
		if (!empty($response['cache']) OR !empty($widget['cache'])) {
			$data['cache'] = json_encode($response['cache']? $response['cache'] : $widget['cache']);
		} else {
			$data['cache'] = ''; //clear cache whenever widget is updated
		}
		if (!empty($response['expire']) OR !empty($widget['expire'])) {
			$data['expire'] = ($response['expire'])? intval($response['expire']) : intval($widget['expire']);
		}					
		return $data;		
	}
	
	protected function getLocations($id) {
		$data = [];
		$results = $this->db
			->table($this->site_prefix .'_location AS l')
			->leftJoin($this->site_prefix .'_page AS p', 'p.id', '=', 'l.page_id')
			->where('app_type', 'widget')
			->where('app_id', $id)
			->select('l.id', 'app_id', 'location', 'section', 'page_id', 'name', 'published', 'sort')
			->get()->all();
		foreach ($results AS $location) {
			$location['name'] = $this->getRightLanguage(json_decode($location['name']??'', true));
			$data[] = $location;
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
				->where('app_type', 'widget')
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
			$status['message'][] = $this->trans('You cannot delete an in-use widget');
		}

		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);								
	}

	public function generateRoutes($extra = []) {
    	$name = strtolower($this->class);
		$extra['deleteLocation'] = ['POST', '/[i:site_id]/'. $name .'/delete/location.[json:format]?[POST:id]?'];
		$extra['preview'] = ['POST', '/[i:site_id]/'. $name .'/preview.[json:format]?[POST:id]?'];
		$extra['action'] = ['GET|POST', '/[i:site_id]/'. $name .'/[edit|copy:action]/[*:id]?.[json:format]?']; // widget accepts widget/edit/text = new text widget. Change to * to support encode(text)
		return $this->trait_generateRoutes($extra);
	}

	protected function prepareWidget($widget) {
		if ($widget) {
			if (!empty($widget['cache']) AND (empty($widget['expire']) OR $widget['expire'] > time()) ) {
				// cache may support multiple languages
				//$widget['cache'] = json_decode($widget['cache']??'', true);
				$set_lang = $this->config['site']['locale']??$this->config['site']['language'];
				$set_lang = '<script>var language = "'. $set_lang .'"</script>';
				$block['output'] = is_array($widget['cache'])? $this->getRightLanguage($widget['cache']) : $set_lang . $widget['cache'];
			} else {
				$app = $this->getAppInfo($widget['type'], 'Widget');
				if ($app){
					$Class = $app['class'];
					unset($widget['cache']); //no need to send it

					$response = []; //make sure result is clear after each iteration
					if ( !empty($app['app_handler']) AND substr($app['app_handler'], 0, 4) === 'http' ){
						$this->checkActivation($Class);			
						$response = $this->httpPost($app['app_handler'] . 'render', [
							"json" => json_encode(['site_config' => $this->config['site'] ] + $widget)
						]);
						$response = json_decode($response??'', true);
					} elseif (class_exists($Class)) {
						if (empty($this->handlers[$Class])) {
                    		$this->handlers[$Class] = new $Class($this->config, $this->view, $this->dbm, $this->router, $this->user);
							$this->checkActivation($this->handlers[$Class]);			
     					}
						$response = call_user_func([$this->handlers[$Class], 'render'], $widget);
					}
					if (isset($response['result']) AND $response['result'] === 'success') {
						if ( ! empty($response['output']) ){
							$block['output'] = $response['output'];
							if ( !empty($response['expire']) OR empty($widget['cache']) OR ( !empty($widget['expire']) AND $widget['expire'] < (time() - 7*24*3600) ) ){ //should cache at least once to provide it to getSnippet and update it after 7 days
								$cache['expire'] = $response['expire']?? time();//expire now so cache isnt used for other ops
								$cache['cache']  = json_encode($response['output']); 
								$this->db
								 	 ->table($this->table)
								 	 ->where('id', $widget['id'])
								 	 ->update($cache);
							}
						} elseif ( ! empty($response['block']) ){
							$block = $response['block'];
							if ( !empty($app['app_templates']['widget'])) {//override 
								$block['template']['file'] = $app['app_templates']['widget'];
							}
							if ( !empty($block['template']['file']) ){ //can be in app folder or system template folder 
								$block['template']['directory'] = 'resources/protected/app/'. strtolower($app['name']) . ($app['id']??''); 
							}
						}	
					} else {
						//$widget['output'] = '';
					}
				}	
			}			
		}
		return $block??[];
	}

	public function render($page_id, $app = 'Page') {
		$results = $this->db
			->table($this->table .' AS w')
			->join($this->site_prefix .'_location AS l', 'w.id' ,'=', 'l.app_id')
			->where('app_type', 'widget')
			->where(function ($query) use ($page_id, $app) {
		  		$query->where('location', 'Site')
		  			  ->orWhere('page_id', $page_id)	
		  			  ->orWhere(function ($query) use ($app) {
		  					$query->where('location', $app)
		  			  			  ->whereNull('page_id');
		  				});
		  	})
		  	->select('w.id', 'type', 'section', 'data', 'cache', 'expire')
		  	->distinct() //no duplicate content at the same section
		  	->orderBy('sort')
		  	->get()->all();						

		foreach ($results AS $widget) {
			if(!empty($widget['data'])){
				$widget['data'] = json_decode($widget['data']??'', true);
			}
			if(!empty($widget['cache'])){
				$widget['cache'] = json_decode($widget['cache']??'', true);
			}
			$block = $this->prepareWidget($widget);
			if (!empty($block) ){
				$this->view->addBlock($widget['section'], $block, "Widget::". $widget['type'] ."-". $widget['id'] ."::render");
			}	
		}
	}
	//preview a widget api
	public function preview($id) { 
		$snippet = $this->read($id);
		if ($snippet) {
			$block = $this->prepareWidget($snippet);
			
			if ($this->view->html){				
				$this->view->setLayout('blank');
			} else { //api
				if ( !empty($block['output']) ){
					$block['api']['snippet']['output'] = $block['output'];
				} else { //we have to use whatever cache has anyway as api mode doesnt have smarty to render block
					$block['api']['snippet']['output'] = is_array($snippet['cache'])? $this->getRightLanguage($snippet['cache']) : $snippet['cache'];
				}						
			}

			$this->view->addBlock('main', $block, 'Widget::preview');
		} else {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('No such :item', ['item' => 'snippet']);
		}

		//$status['api_endpoint'] = 1;
		!empty($status) && $this->view->setStatus($status);	
	}
}