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

class Worker {
    use \LiteGUI\Traits\Controller;
    private $db_config;

	public function __construct(){
		if (PHP_SAPI != "cli") {
		    exit;
		}	
		if (empty($_SERVER['argv'][1])){
			echo 'Please specify staff or client queue'. PHP_EOL;
			return; //require target app	
		} else {
			$queue = $_SERVER['argv'][1];
		}
		$settings = [
            'passport' => $queue .'Worker', //as unique as possible, we dont want to share this with another app'
            'base_path' => ($queue == 'staff')? '/siteadmin' : '/account', //should start with / and no trailing / 
            'template_dir' => 'public/templates/admin', //relative to resources dir and no beginning and trailing /
            'routes' => $this->getRoutes(), 
        ];
        $_SERVER['SERVER_NAME'] = $_SERVER['REQUEST_URI'] = '';
        $_SERVER['HTTP_ACCEPT'] = 'application/json'; //force API view
		$this->controller_init($settings);
		$this->db_config = (function() { 
			return $this->config; 
		})->call($this->dbm->getConnection());

		$n = 0;
		$results = [];
		while ($n++ < 100){
 	    	if ($job = $this->redis->lPop('queue_'. $queue)){
	 	    	$job = json_decode($job, true);
    			$this->prepareSite($job['site']['id']);
      			$results[] = $this->runJob($job, $job['user']['id'], $queue);
            } else {
            	break;
            }	
		}
		//process retries
		$now = time();
		$retries = $this->redis->multi()
			->zrangebyscore(   'retry_jobs_'. $queue, '-inf', $now)
			->zremrangebyscore('retry_jobs_'. $queue, '-inf', $now)
			->exec();
		
		foreach ($retries[0]??[] AS $retry){
			$site_id = strtok($retry, '.');
			$log_id  = strtok('.');
    		$this->prepareSite($site_id);
			if ($job = $this->db
			    ->table($this->config['system']['table_prefix'] . $site_id .'_activity')
			    ->where('id', $log_id)
			    ->whereNull('processed')
			    ->select('meta', 'creator')
			    ->first()
			){
				$job['meta'] = json_decode($job['meta']??'', true);
				//for retrying we'll use $log_id as the $run parameter
				$job['meta']['params'][] = $log_id;
				$results[] = $this->runJob($job['meta'], $job['creator'], $queue);
			}    
		}	

		//print_r($job);		        
		echo "The array contain return and output from each job:". PHP_EOL;
		print_r($results??[]);
	}	
	protected function runJob($job, $user_id, $queue = 'client'){
		ob_start(); //suppress header produced by controller_run()  
		$_SESSION = []; //reset before each run
        if ($queue == 'staff'){
			$user = new StaffWorker($this->config, $this->dbm, $this->router, $this->view, $this->passport);
        } else {
        	$user = new ClientWorker($this->config, $this->dbm, $this->router, $this->view, $this->passport);
        }
        $user->impersonate($user_id);
		$this->controller_requireUser($user);

        //Reset queue for each job
        $this->router->emptyQueue(); //clear queue
        $this->container = []; //clear instance cache
        $this->router->registerQueue([
        	$job['target'] => $job['params'],
        ]);
        $result['return'] = $this->controller_dispatch();

    	$result['content'] = ob_get_contents();//, true);
		ob_end_clean();
        return $result;
        /*/Log only when hooks returns error or message, otherwise no hook running
        if ( !empty($results[0]['result']) || !empty($results[0]['message'][0]??$message) ){
            //As runHook process custom inputs, we can only catch the app and id sometimes
            //$job['params'][0] is hook_name, $job['params'][1] is the hook params, $job['params'][1][0] is the 1st hook param
            if ( !empty($job['params'][1][0]['id']) AND str_contains($job['params'][0], '::') ){
	            if ( str_starts_with($job['params'][0], "Product::") ){ //Product	
	            	$type = 'Product.';
	            } elseif ( str_starts_with($job['params'][0], "App::") ){
	            	$type = strtok(substr($job['params'][0], 5), '::');
	            } else {
	            	$type = strtok($job['params'][0], '::') .'.';
	            }
            } else {
            	$type = 'Hook.';
            }

            $outputs[] = $log = [
	            'app_type' => $type,
	            'app_id' => $job['params'][1][0]['id']??null,
	            'message' => $results[0]['message'][0]??$message,
	            'level' => 'Info',
	            'user_id' => $job['user']['id'],
	            'created' => $job['triggered'],
	            'processed' => (empty($results[0]['result']) || $results[0]['result'] != 'error')? time() : null,
	        ];
	        $log['meta'] = json_encode($job);
			print_r($job);		        

			print_r($results);		        
	        $this->db
	        	->table($this->config['system']['table_prefix'] . $site['id'] .'_activity')
	            ->insert($log);
	    } */ 		
	}
    protected function getRoutes() {
        $routes[] = ['GET|POST',  '/*', $_SERVER['argv'][1]. '::worker', $_SERVER['argv'][1] .'::worker']; //need one default route
    	return $routes;
    }

	protected function activated($site_id, $class){
		try {
			if ( !empty($this->config['site_activation'][ $class ]) OR $this->dbm->getConnection()
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

	protected function prepareSite($site_id){
		$this->config['site'] = $site = $this->controller_getSiteConfig($site_id);
		//set timezone
        if ( !empty($this->config['site']['timezone']) ){
            date_default_timezone_set($this->config['site']['timezone']);    
        }
		//modify activation first
        if ( !empty($this->config['activation']) ){
            $tier = $this->config['site']['tier']??1;            
            $this->config['site_activation'] = array_filter($this->config['activation'], function($v) use ($tier) {
                return empty($v[3]) || $v[3] <= $tier;
            });
        }

   		if ( !empty($this->config['servers']) AND !empty($site['server']) AND !empty($this->config['servers'][ $site['server'] ]) ){
			try {
	            $this->db = $this->dbm->getConnection($this->config['servers'][ $site['server'] ]); //this may produce exception
	            $this->dbm->getDatabaseManager()->setDefaultConnection($this->config['servers'][ $site['server'] ]);//this does not produce error
			} catch (\Exception $e) {
    			$this->dbm->addConnection(
	            	[
		                'driver'    => 'mysql',
		                'host'      => $this->config['servers'][ $site['server'] ],
		                'database'  => $this->db_config['database'],
		                'username'  => $this->db_config['username'],
		                'password'  => $this->db_config['password'],
		                'charset'   => 'utf8mb4',
		                'collation' => 'utf8mb4_unicode_520_ci',
		                'prefix'    => '',
	            	], 
	                $this->config['servers'][ $site['server'] ]
	            );
            	$this->dbm->getDatabaseManager()->setDefaultConnection($this->config['servers'][ $site['server'] ]);
	        }    
        } else {
            $this->dbm->getDatabaseManager()->setDefaultConnection('central');
        }
       	$this->db = $this->dbm->getConnection();
	}	
}

class ClientWorker extends User {
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
class StaffWorker extends Staff {
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

new Worker();	
?>