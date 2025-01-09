<?php
namespace SiteGUI\Core;

class Site {
	use Traits\Application;
	protected $sites;

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user); //construct Application
		//$this->db = $dbm->getConnection('central');
		if (!empty($this->config['system']['base_dir'])) {
			$this->path = $this->config['system']['base_dir'] .'/resources/public/templates/site/';
		} else {
			echo "Template directory is not defined!";
		}
		$this->table = $this->table_prefix ."_sites"; //no site_id here
		$this->requirements['UPDATE'] = "Site::update";
		$this->requirements['SiteManager'] = "Role::SiteManager"; 	//Manage/own site
		$this->requirements['SystemAdmin'] = "Role::SystemAdmin"; 	//Manage/own site
	}
	public function clientMain($current_app ='') {
		if ($this->view->html){		
			//get all activated apps
			$activation = array_merge($this->config['activation'], $this->getActivation($this->config['site']['id']) );	

    		$routes = $this->router->getRoutes();
    		foreach ($activation as $class => $visibility) {
				$type = strtok($this->trimRootNs($class), '\\'); //remove root namespace => Core, App...
				$app  = strtok('\\');
				$display = $visibility[4]??$this->formatAppLabel($app);
				$item = []; //reset it
				$abbr = str_replace(' ', '', $display);
				if ($abbr){
					$r = mb_ord($abbr[0])*30 % 255; //*30 to expand the color gap between letter
					$g = mb_ord($abbr[ (int) floor(strlen($abbr)/2) ])*30 % 255;
					$b = mb_ord($abbr[ strlen($abbr) - 3 ])*30 % 255;
					$item['style']['bg'] = "rgb($r, $g, $b)";
					$item['style']['color'] = ($r*0.299 + $g*0.587 + $b*0.114) > 149? '#000000' : '#FFFFFF';
				}
				if ( str_contains($display, ' ') ){
					$abbr = implode('', array_map(function($v) { 
						return mb_substr($v, 0, 1, 'utf-8'); 
					}, explode(' ', $display)));
				}
				$item['style']['abbr'] = strtoupper(substr($abbr, 0, 4));
	
				//sqrt(pow($r, 2) * 0.241 + pow($g, 2) * 0.691 + pow($b, 2) * 0.068) >= 140? '#000000' : '#FFFFFF'; //https://stackoverflow.com/questions/3942878/how-to-decide-font-color-in-white-or-black-depending-on-background-color

				if ($app == $current_app) {
					//$item['active'] = 1; 
					if ( $this->view->html AND (empty($visibility[1]) OR str_contains($visibility[1], 'client_readonly')) ){ //app_readonly to hide create new button
						$html['app_readonly'] = 1;
					}
					if ( !empty($display) ){
						$html['app_label'] = $this->trans($display);
						$html['app_label_plural'] = $this->trans($this->pluralize($display));
					}
				}

				if ( !empty($visibility[1]) AND str_contains($visibility[1], 'client') AND 
				   ( empty($visibility[5]['client_read']) OR $this->user->has($visibility[5]['client_read']) )
				){
					if ($type == 'App') {	
						$item['name'] = $display;
						$item['slug'] = $this->slug('App::clientMain', [
							'app' => strtolower($app),	
						]);
						if ($app == $current_app) {
							$item['active'] = 1; 
						}							
						if (count($menu_apps??[]) < 6 OR isset($menu_apps[ $visibility[0]?:'Management' ]) ){ //6 categories
							$menu_apps[ $visibility[0]?:'Management' ]['children'][ ] = $item;
						} else {
							$menu_apps['Apps']['children'][ ] = $item;
						}
						if ( !str_contains($visibility[1], 'client_readonly') AND 
				   		   ( empty($visibility[5]['client_write']) OR $this->user->has($visibility[5]['client_write']) )
						){
							$item['name'] = $display;
							$item['slug'] = $this->slug('App::clientView', [
								'id' => strtolower($app),
							]);
							if (count($menu_create??[]) < 6 OR isset($menu_create[ $visibility[0]?:'Management' ]) ){ //6 categories
								$menu_create[ $visibility[0]?:'Management' ]['children'][ ] = $item;
							} else {
								$menu_create['Apps']['children'][ ] = $item;
							}
						}
					} elseif ( $app != 'App' AND $app != 'Site' ){//Core apps except App.php and Site.php
						$category = !empty($visibility[0])? $visibility[0] : 'Management'; //due to mix value

						if ( isset($routes[ $app .'::clientMain']) ){
							$item['name'] = $display; //$this->pluralize($display); 
							$item['slug'] = $this->slug($app .'::clientMain');
							if ($app == $current_app) {
								$item['active'] = 1; 
							}
							if (!isset($allowed_apps[$app]) AND !$this->user->has($this->requirements['SiteManager'])) { //hide disallowed apps - remove later
								$item['icon'] = 'bi bi-dash-circle-fill';
							}
							if (count($menu_apps??[]) < 6 OR isset($menu_apps[ $category ]) ){ //6 categories
								$menu_apps[ $category ]['children'][ ] = $item;
							} else {
								$menu_apps[ 'Management' ]['children'][ ] = $item;
							}	
						} 

						if ( !str_contains($visibility[1], 'client_readonly') AND isset($routes[ $app .'::clientView']) AND 
				   		   ( empty($visibility[5]['client_write']) OR $this->user->has($visibility[5]['client_write']) )
				   		){
							$item['name'] = $display;
							$item['slug'] = $this->slug($app .'::clientView'); 	
							if (count($menu_create??[]) < 6 OR isset($menu_create[ $category ]) ){ //6 categories
								$menu_create[ $category ]['children'][ ] = $item;
							} else {
								$menu_create[ 'Management' ]['children'][ ] = $item;
							}	
						} 
					}
				}		
			}

			$html['current_app'] = $current_app; //wont translate, use label
			if ( empty($html['app_label']) ){
				$html['app_label'] = $this->trans($this->formatAppLabel($current_app?: 'App'));
				$html['app_label_plural'] = $this->trans($this->formatAppLabel($this->pluralize($current_app?: 'App')));
			}
			$block['html'] = $html;

			$block['menu']['apps'  ] = [
				'name' => $html['app_label_plural'], 
				'icon' => 'bi bi-ui-checks-grid', 
				'slug' => $this->slug($current_app .'::clientMain'), 
				'children' => $menu_apps??[] 
			];
			if (!empty($menu_create)) {
				$block['menu']['create'] = [
					'name' => '', 
					'icon' => 'bi bi-plus-square', 
					'slug' => '#', 
					'children' => $menu_create 
				];
			}
			if (!empty($menu_config)) {
				$block['menu']['config'] = [
					'name' => '', 
					'icon' => 'bi bi-sliders', 
					'slug' => '#', 
					'children' => $menu_config 
				];
			}
			
			$this->view->addBlock('top', $block, 'Site::clientMain');

			if ($current_app == 'Site'){
				$block_main['template']['file'] = 'site_client';
				$this->view->addBlock('main', $block_main, 'Site::clientMain');
			}
		}
	}

/**
 * create a new site
 * @param  [type] $site
 * @param  string $sample_pages
 * @return [type]
 */
	public function create($site, $set_owner = null, $sample_pages = true) {
	}

	protected function read($id){
		//$this->db->getDatabaseManager()->setDefaultConnection('central');
		if ( ! $this->user->has($this->requirements['UPDATE']) ){ //this is redundant check to save us incase this function is public
			$this->denyAccess('update site');
		} else {		
			$data = $this->dbm->getConnection('central')
				->table($this->table)
				->where('id', intval($id))
				->first();

			if ($data['other_settings']){
				$data['other_settings'] = json_decode($data['other_settings'], true);
				$data = array_merge($data, $data['other_settings']);
				unset($data['other_settings']);
			}
			return $data;
		}			
	}

	//get activated apps for this site. App visibility is stored per site, deactivate/re-activate is needed to apply visibility change
	protected function getActivation($id) {
		return [];								
	}
	/**
	 * render site header
	 * @return none
	 */
	public function render($current_app) {
		//install default apps for newly created site, except when we delete it
        if ( $this->user->has($this->requirements['UPDATE']) AND 
        	$current_app != 'Site' AND 
        	!empty($this->config['site']['id']) AND 
        	(empty($this->config['site']['version']) OR !empty($this->config['site']['change_tier'])) 
        ){
        	foreach ( $this->config['activation'] AS $Class => $v ){
        		if ( strpos($Class, __NAMESPACE__) !== false ){ //Core Apps only
        			if ( method_exists($Class, 'install') ){
						$success[] = (new $Class($this->config, $this->dbm, $this->router, $this->view, $this->user))->install();
					}
        		}
        	}
        	if ( !empty($success) AND empty($this->config['site']['version']) ){
        		$status['message'][] = $this->trans('Thanks for your patience while your site is being initialized for the first time.');
        		$this->view->setStatus($status);
        	}
        	if ( empty($success) OR array_product($success) ){ //set version
        		$this->dbm->getConnection('central')
					->table($this->table_prefix .'_sites')
					->where('id', $this->config['site']['id'])
					->when(empty($this->config['site']['version']), function($query){ //new site
						return $query->whereNull('version')
							->update([
								'version' => $this->config['system']['version']??1,
							]);	
					}, function($query){ //change tier
						return $query->update([
							'other_settings' => $this->db->raw("JSON_REMOVE(other_settings, '$.change_tier')"),
						]);	
					});	
        	}
        }	
        	
		if ($this->view->html){		
		    $routes = $this->router->getRoutes();

				if ( !empty($this->config['site']['id']) && $s = $this->config['site'] ){				
					$label_sites = $s['name'];
					
					//only prepare app menu if user has access to site as site managers may access site1/cart to purchase app
					$html['file_manager'] = $this->slug('File::main', [ "site_id" => $this->config['site']['id'] ]);
					//$links['notifier'] = $this->slug('Notification::main', [ "site_id" => $this->config['site']['id'] ]);
					//check user's onboarding (when in a site only) - html in message.tpl
					if ( !($html['onboard_site'] = $this->user->meta('onboard_site')) ){
						unset($html['onboard_site']);
					}	
					$links['onboard'] = $this->slug('Staff::onboard');
					$links['onboard'] .= '?cors='. $this->hashify($links['onboard'] .'::'. $_SESSION['token']);

					$activation = array_merge($this->config['activation'], $this->getActivation($this->config['site']['id']) );
					//resolve permissions for this user to enable appropriate menu
		    		$allowed_apps = $this->dbm->getConnection('central')
		    			  ->table($this->table_prefix .'_system')
		    			  ->where('type', 'permission')	
		    			  ->whereIn('property', array_keys($this->user->getPermissions()))
		    			  ->pluck('value')
		    			  ->all();
		    		$allowed_apps = array_flip(explode(',', implode(',', $allowed_apps))); //Array([Page] => 20 ...[Widget] => 22 [Site] => 41)	 
		    		foreach ($activation as $class => $visibility) { //if no visibility, app wont be included in menu but when it is current_app, the visibility is treated like staff_readonly and a configure link should be prepared
		    			$class = $this->trimRootNs($class); //remove root namespace
						$type = strtok($class, '\\'); //Core, App...
						$app  = strtok('\\');
						$item = []; //reset it
						if ($app == $current_app) {
							$item['active'] = 1; 
							if ( $this->view->html ){ //app menu_config
								if ( empty($visibility[1]) OR str_contains($visibility[1], 'staff_readonly') ){
									$html['app_readonly'] = 1;
								}

								if ( ($type == 'App' OR !empty($visibility[2]) ) AND $this->user->has($this->requirements['UPDATE']) ){ //configurable => link
									$links['configure'] = $this->slug('Appstore::configure') .'?sgframe=1&name='. $type .'/'. $app;//need \\\\	
								} 
							}
						}

						$display = $visibility[4]??$this->formatAppLabel($app); //use app name instead of slug
						$display_plural = $this->trans($this->pluralize($display));
						$display = $this->trans($display);
						if ($app == $current_app && !empty($display)) {
							$html['app_label'] = $display;
							$html['app_label_plural'] = $display_plural;
						}
		    			if ( (empty($visibility[1]) OR !str_starts_with($visibility[1], 'staff')) OR 
							( !empty($visibility[5]['staff_read']) AND !$this->user->has($visibility[5]['staff_read']) )
		    			) continue; //after links[configure] and app_label so it is present for hidden current app which is accessed now
							
						if ($type == 'App') {
							$item['name'] = $display;
							$item['type'] = $this->formatAppName($app); //for location attachment in menu/widget
							if ( !str_starts_with($visibility[1], 'staff_readonly') AND 
							   ( empty($visibility[5]['staff_write']) OR $this->user->has($visibility[5]['staff_write']) )
							){
								$item['slug'] = $this->slug('App::action', [
									'action' => 'edit',
									'id' => strtolower($app),
								]);
								if ( empty($menu_create['Apps']['children'][11]) ) {
									$menu_create['Apps']['children'][ ] = $item;
								} else {
									$menu_create['More Apps']['children'][ ] = $item;
								}	
							}

							//$item['name'] = $display;//$this->pluralize($display); 
							$item['slug'] = $this->slug('App::main', [
								'app'	  => strtolower($app),	
							]);
							if ( empty($menu_apps['Apps']['children'][11]) ) {
								$menu_apps['Apps']['children'][ ] = $item;
							} else {
								$menu_apps['More Apps']['children'][ ] = $item;								
							}
						} elseif ($type == 'Widget') {
							$item['name'] = $display;
							$item['slug'] = $this->slug('Widget::action', [
								'action' => 'edit',
								'id' => strtolower($app),
							]); 	
							$menu_widget['children'][ ] = $item;
						} elseif ($type == 'Core') {
							if (!isset($allowed_apps[$app]) AND !$this->user->has($this->requirements['SiteManager']) ){ 
								continue; //hide disallowed apps
							}

							$category = !empty($visibility[0])? $visibility[0] : 'Management'; //due to mix value
							if ( !empty($this->config['site']['role_site']) AND $category == 'Management' ){
								if (in_array($app, ['Staff' , 'Role']) OR (!empty($this->config['site']['user_site']) AND $app == 'User') ){
									continue;
								}
							}
							if (!str_starts_with($visibility[1], 'staff_readonly') AND 
								isset($routes[ $app .'::action']) AND 
								strpos($routes[ $app .'::action'], 'edit') AND 
							   ( empty($visibility[5]['staff_write']) OR $this->user->has($visibility[5]['staff_write']) )
							){
								$item['name'] = $display;
								$item['slug'] = $this->slug($app .'::action', ['action' => 'edit'] ); 	
								if ($category != 'Management') {
									$menu_create[ $category ]['children'][ ] = $item;
								} else {
									$menu_settings['create']['children'][ ] = $item; 
								}	
							} 

							if (isset($routes[ $app .'::main']) ) {
								if ($app == 'Site') {
									$item['name'] = $display; 
									$item['slug'] = $this->slug($app .'::action', ['action' => 'edit', 'id' => $this->config['site']['id']] );
								} else {
									$item['name'] = $display_plural; 
									$item['slug'] = $this->slug($app .'::main');
								}

								if ($category != 'Management') {
									$menu_apps[ $category ]['children'][ ] = $item;
								} else {
									$menu_settings['apps']['children'][ ] = $item; 
								}
							} 
						}	
					}
				}							

			$html['current_app'] = $current_app; //wont translate, use label
			if ( empty($html['app_label']) ){
				$html['app_label'] = $this->trans($this->formatAppLabel($current_app?: 'App'));
				$html['app_label_plural'] = $this->trans($this->formatAppLabel($this->pluralize($current_app?: 'App')));
			}

			$block['links'] = $links??[];
			$block['html'] = $html;

			!empty($menu_settings['apps']) && $menu_apps['Management'] = $menu_settings['apps']; //Manage always come last
			if ( !empty($menu_apps) ){
				$block['menu']['apps'] = [
					'name' => $html['app_label_plural'], 
					'icon' => 'bi bi-ui-checks-grid', 
					'slug' => '#', 
					'children' => $menu_apps,
				];
			}

			!empty($menu_widget) && $menu_create['Widget'] = $menu_widget; 
			!empty($menu_settings['create']) && $menu_create['Management'] = $menu_settings['create'];
			if ( !empty($menu_create) ){
				$block['menu']['create'] = [
					'name' => '',//$this->trans('New'), 
					'icon' => 'bi bi-plus-square-fill text-lime', 
					'slug' => '#', 
					'children' => $menu_create,
				];	
			}
			$this->view->addBlock('top', $block, 'Site::render');
		}
	}
		
	public function generateRoutes($extra = []) {
	}
}	
?>