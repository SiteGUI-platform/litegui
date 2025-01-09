<?php
namespace SiteGUI\Core;
require __DIR__ .'/vendor/LiteGUI/Traits/Controller.php'; //require config.php
require_once __DIR__ .'/vendor/Smarty/bootstrap.php'; //Smarty cannot load internal classes
//Autoloading classes
spl_autoload_register(function ($class_name) {
    $class_name = ltrim($class_name, '\\');
    if ($lastNsPos = strrpos($class_name, '\\')) {
        $namespace = substr($class_name, 0, $lastNsPos);
        $class_name = substr($class_name, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName = ($fileName??''). str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
    $fileName = str_replace('SiteGUI/', '', $fileName);

    if (is_readable(__DIR__ . '/' . $fileName)) { //Project root folder
        require __DIR__ . '/' . $fileName;
    } elseif (is_readable(__DIR__ .'/vendor/'. $fileName)) { //vendor/
        require __DIR__ .'/vendor/'. $fileName;
    } 
});

class Cron {
    use \LiteGUI\Traits\Controller;

	public function __construct(){
		if (PHP_SAPI != "cli") {
		    exit;
		}	
		if (empty($_SERVER['argv'][1])){
			echo 'App with cron method is required!'. PHP_EOL;
			return; //require target app	
		} 
		$settings = [
            'passport' => 'CronUser', //as unique as possible, we dont want to share this with another app'
            'base_path' => '/siteadmin', //should start with / and no trailing / 
            'template_dir' => 'public/templates/admin', //relative to resources dir and no beginning and trailing /
            'routes' => $this->getRoutes(), 
        ];
        $_SERVER['SERVER_NAME'] = $_SERVER['REQUEST_URI'] = '';
        $_SERVER['HTTP_ACCEPT'] = 'application/json'; //force API view
		$this->controller_init($settings);

		$srv_ip = gethostbyname(gethostname());
		if ( empty($srv_ip) ) return;
		if (empty($this->config['site'])){
			$srv = array_flip($this->config['servers']??[])[ $srv_ip ]??null;
			if ( empty($srv) ) return;

			//print_r($this->match);
			//Get all the sites on this server
			$sites = $this->dbm->getConnection()
				->table($this->config['system']['table_prefix'] .'_sites')
			  	->where('server', $srv)
			  	->where('status', 'Active')
				->get()->all();
		} else {
			$sites[] = $this->config['site'];
		}		
		$results = [];	
		ob_start(); //suppress header produced by controller_run()
		foreach ($sites as $site) {	
			if ( !empty($site['timezone']) ){
		        date_default_timezone_set($site['timezone']);    
		    }
			//modify activation first
            if ( !empty($this->config['activation']) ){
                $tier = $site['tier']??1;            
                $this->config['activation'] = array_filter($this->config['activation'], function($v) use ($tier) {
                    return empty($v[3]) || $v[3] <= $tier;
                });
            }    

            if (!empty($site['other_settings'])){
                $site['other_settings'] = json_decode($site['other_settings'], true);
                $site = array_merge($site, $site['other_settings']);
                unset($site['other_settings']);
            }

            $languages = $this->getLanguages();
            if (!empty($site['locales'])) {
                foreach ($site['locales'] as $key => $short) {
                    //$short = strtok($locale, '/');
                    $long  = $languages[ $short];
                    unset($site['locales'][$key]);
                    $site['locales'][ $short ] = $long;
                }       
            }
            if ( !empty($site['language']) ){
                //make the default language  the first - required for page_edit as it loops through locales
                $site['locales'] = [$site['language'] => $languages[ $site['language'] ] ] + ($site['locales']??[]); 
            }
            if ( str_ends_with($site['url'], $this->config['subdomain']) ){
                $site['account_url'] = str_replace($this->config['subdomain'], '.my'. $this->config['subdomain'], $site['url']);
            } else {
                $site['account_url'] = 'my.'. $site['url'];
            }    
            $this->config['site'] = $site;
            /*/Reset queue for each site
            $this->router->emptyQueue(); //clear queue
            $this->container = []; //clear instance cache
            $this->router->registerQueue([
            	$this->match['target'] => $this->match,
            ]);
            $this->controller_run();*/
			$user = new CronUser($this->config, $this->dbm, $this->router, $this->view, $this->passport);
			$this->controller_requireUser($user);

            if ( !empty($_SERVER['argv'][2]) ){ //App
            	$Class = str_replace('Core', 'App', __NAMESPACE__) .'\\'. $_SERVER['argv'][2];
            	if ( !method_exists($Class, 'cron') ){
            		exit;
            	}
				if ( $this->activated($site['id'], $Class) ){
					$instance = new $Class($this->config['site'], $this->view);
					$response = call_user_func([$instance, 'cron'], $this->config, $this->dbm, $this->router, $this->view, $this->user);
				} else {
					continue;
				}	
            } else {
            	$Class = __NAMESPACE__ .'\\'. $_SERVER['argv'][1];
            	if ( !method_exists($Class, 'cron') ){
            		exit;
            	}
            	if ( $this->activated($site['id'], $Class) ){
					$instance = new $Class($this->config, $this->dbm, $this->router, $this->view, $this->user);
					$response = call_user_func([$instance, 'cron']);
				} else {
					continue;
				}	
            }	
            $results[ $site['id'] ] = ob_get_contents();//, true);
            ob_clean();
		}
		ob_end_clean();
		echo "The array contain response from each site:". PHP_EOL;
		print_r($results);
	}	

    protected function getRoutes() {
        $routes[] = ['GET|POST',  '/*', $_SERVER['argv'][1]. '::cron', $_SERVER['argv'][1] .'::cron']; //need one default route
    	return $routes;
    }

	protected function activated($site_id, $class){
		try {
			if ( !empty($this->config['activation'][ $class ]) OR $this->dbm->getConnection()
				->table($this->config['system']['table_prefix'] . $site_id .'_config')
				->where('type', 'activation')
				->where('name', $class) 
				->first()
			){
				return true;
			}
			return false;
		} catch (\Exception $e){
			return false;
		}	
	}	
}

class CronUser extends Staff {
	protected $id = 1;
	protected $type = 'System'; 
	protected $name = 'Cron';
	protected $roles = ['System Admin']; 
	protected $staff = 1;
	protected $permissions = ["Role::SystemAdmin" => 1];

	public function __construct($config, $dbm, $router, $view, $passport = 'staff') {
		parent::__construct($config, $dbm, $router, $view, $passport);
	}
	//impersonate staff, client should use another one
	public function impersonate($id) {
		$this->is([
			'id' => $id
		]);
		$this->loadPermissions($this->config['site']['id']);
	}	
}
new Cron();	
?>