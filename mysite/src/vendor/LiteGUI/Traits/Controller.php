<?php
namespace LiteGUI\Traits;
use \LiteGUI\User;
use \LiteGUI\Router;
use \LiteGUI\View;
use \LiteGUI\View\Smarty;
use \Illuminate\Database\Capsule\Manager as DBM;
require_once(__DIR__ .'/Helper.php');         
/*
1. controller_init helps customize common controller properties like config, dbm, router, view 
2. controller_init is all required to match the request and register queue. Apps can have their own modifyMatch method to change $match
3. User and user login may be added using controller_requireUser/Login(User $user) => set $passport to a unique name
4. after controller_init, you may add code as you like before dispatching request to target class [PRE_ACTION]
5. after controller_dispatch, view's variables are ready, you may change them before displaying using $this->view->render [POST_ACTION]    
*/
trait Controller {
    use Helper;
    protected $config;     
    protected $container;
    protected $dbm;
    protected $router;
    protected $match;
    protected $passport; //used as SESSION key
    protected $user;
    protected $view;
    protected $lang;
    protected $redis;

    //This pseudo constructor should be called in the implementing class's constructor
    public function controller_init($switch = []){
        //use provided config_file or default one
        if (!empty($switch['config_file'])) {
            require $switch['config_file'];
        } else {
            require __DIR__ .'/../../../config.php';
        }
        if ( empty($config) ) {
           throw new \Exception('$config must be passed to '. __CLASS__);
        }
        $this->config = $config;  
        if ( empty($this->config['salt']) ){
            $this->config['salt'] = md5(json_encode($config['db'])); //used as salt for hashify, use db so config.local can be used, if the db has to be changed, set to use the old salt 
        }    

        $this->redis  = $config['redis']??null;
        //system default values
        $system = [
            'passport'  => 'user', //used as SESSION key - base_path can be used but that means 2 different apps may not use one passport
            'locale' => 0, //if support locale in url
            'csrf'   => 1, //always enable csrf check
            'routes' => '', //placeholder as it may not present in config file
            'template_dir' => '', //placeholder as it may not present in config file
            'escape_html'  => 1, //always escape HTML for template variables
        ];
        $this->config['system'] += $system; //values in config[system] may override default values
        //and values in $switch may override all
        if (!empty($switch)) {
            foreach ($switch as $key => $value) {    
                if (array_key_exists($key, $this->config['system'])) { 
                    $this->config['system'][$key] = $value; //switch's empty value like 0, [] , ' ' are accepted
                } 
            }
        }
        $this->passport = $this->config['system']['passport'];

        ############### CONTAINER  (required: db_config) ##################
        $this->container = []; //new \Pimple\Container();

        ############### DB  (required: config) ##################
        // Always use $container['dbm']->getConnection() to choose db connection
        // DO NOT use $container['dbm']->connection() as it returns a static connection and unexpected results (doesnt honor PDO fetchmode)
        // We inject $container['dbm'] into application, from there application could change default connection using
        // $container['dbm']->getDatabaseManager()->setDefaultConnection('central'); and set $db = $container['dbm']->getConnection()
        if ( !empty($this->config['db']['password']) ) {
            $this->dbm = new DBM();
            //$this->dbm->setFetchMode(\PDO::FETCH_ASSOC);//no longer work after laravel 5.4
            $_dispatcher = new \Illuminate\Events\Dispatcher;
            $_dispatcher->listen(\Illuminate\Database\Events\StatementPrepared::class, function ($event) {
                $event->statement->setFetchMode(\PDO::FETCH_ASSOC);
            });
            $this->dbm->setEventDispatcher($_dispatcher);            
            $this->dbm->addConnection(
                $this->config['db'] 
                + 
                [
                    'driver'    => 'mysql',
                    'sticky'    => true,
                    'charset'   => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_520_ci',
                    'prefix'    => '',
                ], 
                'central'
            );
            $this->dbm->getDatabaseManager()->setDefaultConnection('central');
        }    
        //print_r($container['dbm']->getConnection()->getQueryLog());
        //$container['dbm'] = $capsule;
        //unset($db_config);    
        
        ############### SITE_LOCALE (required: container, dbm) ##################
        //Site may have locale i.e: /en, /vn prefix, prepend it to the router's basePath to match the URL correctly
        //If this site is known by the hostname, load its setting to get the locale. 
        //If site isn't known, we load the settings after the router has matched the request to get site_id
        if ( !empty($this->config['system']['locale']) ) {                        
            $this->config['site'] = $this->controller_getSiteConfig();
            $this->config['system']['base_path'] .= !empty($this->config['site']['locale'])? '/'. $this->config['site']['locale'] : '';
        }    

        ############### ROUTER (required: container, config->locale) ##################
        $this->router = new Router();
        $this->router->setBasePath($this->config['system']['base_path']);
        //get stored routes for this app using passport as identifier
        $stored_routes = $this->controller_getRoutes($this->passport);
        if ( is_array($stored_routes) ) $this->router->addRoutes($stored_routes);
        //user provided routes or routes stored in config
        if ( is_array($this->config['system']['routes']) ) {
            $this->router->addRoutes($this->config['system']['routes']);
            unset($this->config['system']['routes']); //keep it clean
        } 

        //Let's match to see which route to take
        $match = $this->router->match();
        //print_r($match);
        if ($match && $match['target']){
            if (is_array($match['target'])) {
                $match['current_app'] = strtok($match['name'], '::'); //multiple targets, main app defined by route name    
            } else {
                $match['current_app'] = strtok($match['target'], '::'); //registered as the main app   
            }    
            
            //Wildcard action matching 
            if (!empty($match['params']['action'])) {
                $match['target'] = str_replace('::action', '::'. $match['params']['action'], $match['target']); //map actual action
                unset($match['params']['action']);
            }

            //Wildcard App matching 
            if ($match['current_app'] == 'App' AND isset($match['params']['app']) ) {
                $match['params']['app'] = urldecode($match['params']['app']); //for app named by emoji
                $match['current_app'] = $this->formatAppName($match['params']['app']);

                if ( isset($match['params']['slug']) ) { //render App item
                    $match['params']['slug'] = $match['params']['app'] .'::'. $match['params']['slug']; //change slug to app::slug
                    unset($match['params']['app']);
                }
                if ( isset($match['params']['id']) ) { //delete App item
                    unset($match['params']['app']);
                }
            }

            //Let applications modify $match if they want
            if (method_exists($this, 'modifyMatch')) {
                $match = $this->modifyMatch($match);
            }
        } elseif ( http_response_code() ) {//for cron to work
            // ultimate 404 error here as no route was defined to handle it
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8'); 
            $status['status'] = [
                'result' => 'error',
                'message' => 'Invalid Request! Check the URI and try again!'
            ];
            echo json_encode($status);
            exit;
        }      
        ############### END ROUTER (required: container) ##################

        ############### SITE_CONFIG (required: site_id, router, match, dbm) ##################
        //$this->config['site'] may be set through modifyMatch
        if ( !isset($this->config['site']) ) {//$this->config['site'] may be empty which is fine
            $this->config['site'] = $this->controller_getSiteConfig();
        }
        //If site is on a satelite server (remote, IP must not be central db), we use the remote db server for this site
        if ( !empty($this->config['servers']) AND !empty($this->config['site']['server']) AND !empty($this->config['servers'][ $this->config['site']['server'] ]) AND !in_array($this->config['servers'][ $this->config['site']['server'] ], $this->config['db']['write']['host']) ){
            $this->dbm->addConnection(
                [
                    'driver'    => 'mysql',
                    'host'      => $this->config['servers'][ $this->config['site']['server'] ],
                    'database'  => $this->config['db']['database'],
                    'username'  => $this->config['db']['username'],
                    'password'  => $this->config['db']['password'],
                    'charset'   => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_520_ci',
                    'prefix'    => '',
                ], 
                'remote'
            );
            $this->dbm->getDatabaseManager()->setDefaultConnection('remote');
            //var_dump($this->dbm->getConnection()->getRawPdo());
        }
        //set timezone
        if ( !empty($this->config['site']['timezone']) ){
            date_default_timezone_set($this->config['site']['timezone']);    
        }
        //load language, use site language instead of system
        if (is_readable($this->config['system']['base_dir'] .'/resources/'. $this->config['system']['template_dir'] .'/'. $this->config['system']['template'] .'/lang/'. basename($this->config['site']['language']??$this->config['system']['language']) .'.json')) {
            $this->config['lang'] = json_decode(file_get_contents($this->config['system']['base_dir'] .'/resources/'. $this->config['system']['template_dir'] .'/'. $this->config['system']['template'] .'/lang/'. basename($this->config['site']['language']??$this->config['system']['language']) .'.json')??'', true)??[];
        }

        //At this stage we can unset sensitive credentials as they are no longer needed
        unset($config['db'], $config['redis'], $this->config['db']);
        ############### END SITE_CONFIG (required: site_id, router) ##################

        ############### VIEW (required: config, router match) ##################
        if ( ($match['params']['format']??null) == 'json' OR 
             ($_POST['format']??null) == 'json' OR 
             strpos($_SERVER['HTTP_ACCEPT']??'', 'application/json') !== false 
        ){ //json request
            //no csrf check for api access (non-cookie authentication because api can be accessed via cookie authentication i.e: js on pageee.com can ajax post without csrf check)
            if ( empty($_COOKIE) ){
                $this->config['system']['csrf'] = 0;
            }     
            $this->view = new View($this->config);
            if (!empty($_REQUEST['html'])) { //html var is requested
                $this->view->html = true;
            }
            //format should be removed from $match params
            unset($match['params']['format']);
            unset($_POST['format']);    
        } else {
            $this->view = new Smarty($this->config);
            if ( !empty($_REQUEST['sgframe']) ){ //preview using dynamicModal
                $this->view->setLayout('blank');
                $this->config['system']['sgframe'] = 1;
            }
            // init template vars
            $system = $this->config['system']; //make it short, no confusing pls. Use inside this condition only
            
            if ( !empty($system['escape_html']) ) {
                $this->view->escapeHtml(true);
            } else $this->view->escapeHtml(false);
            
 
            if (!empty($system['base_dir'])) {
                $this->view->setCompileDir( $system['base_dir'] .'/resources/templates_c' ); 
                
                if ( !empty($system['template_dir']) AND !empty($system['template']) ) { 
                    $system['cdn'] = trim($system['cdn'], '/') .'/'. $system['template_dir']; //system.cdn here
                    $this->view->setTemplate($system['template']);                
                    $this->view->setTemplateDir($system['base_dir']  .'/resources/'. $system['template_dir'] .'/'. $system['template']); //use set instead of add
                } 
                //base_dir does not need to be present in template var, it is still present in $config though
                unset($system['base_dir']);
            } else {
                echo 'Template directory is not defined!';
            }

            $this->view->assign('sitegui', TRUE); //required as the signature of page generated by this platform
            $this->view->assign('token',  $_SESSION['token'] );
            $this->view->assign('system', $system );
            $this->view->assign('site',   $this->config['site'] );
            //$this->view->assign('LANG',   $this->config['LANG'] );
            $this->view->registerPlugin("modifier", "trans", [$this, "trans"]); //{$qty = 3}{["There is :qty :fruit", "There are :qty :fruits"]|trans:["qty"=> $qty, "fruit" => "apple"]:$qty}

            if (isset($_REQUEST['nam']) AND $_REQUEST['nam'] == 'debug') {
                $this->view->setDebug(true);  
            } 
        }   
        ############### END VIEW ##################

        // CSRF check, NO ACTION should be defined before this line
        // common case: csrf_token doesn't match what we store in $_SESSION['token']
        // to limit action to a specified id, add it to the uri or csrf_token and let the target method check it
        // post from edit_url: csrf_token of edit_url doesn't match hash of the request_uri + ?for=id and $_SESSION['token'] of sitegui
        // ajax from edit_url: onetime cors token doesn't match hash of the _stripped_ request_uri and ($id)csrf_token of pageee
        $_SERVER['REQUEST_URI'] = urldecode($_SERVER['REQUEST_URI']); //for emoji named app update
        if ( !empty($this->config['system']['csrf']) AND !empty($_POST) AND 
            ($_POST['csrf_token']??null) != $_SESSION['token'] AND 
            ($_POST['csrf_token']??null) != @$this->hashify($_SERVER['REQUEST_URI'] .'::'. $_SESSION['token']) AND 
            ( empty($_GET['cors']) OR $_GET['cors'] != @$this->hashify(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) .'::'. ($_POST['csrf_token']??'')) )
        ){
            $status['result'] = 'error';
            $status['message'][] = $this->trans('Invalid CSRF token. Please try again');//. $this->hashify(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) .'::'. $_POST['csrf_token']);                     
            if ($this->view->html) {
                //$status['template']['file'] = 'message';
                $status['html']['message_type'] = 'info';
                $status['html']['message_title'] = $this->trans('Information'); 
            }
            $this->view->setStatus($status);
            //unset($match);
            $match = ['current_app' => $match['current_app'] ]; //remove match's params except 'current_app'
            unset($_POST);
        } else {
            //Multiple target route, modify match target before registering, keep other things intact
            $hook_params = $match['params'];
            $hook_params[] = $this->config['site']; //add site info for global hook 
            if (is_array($match['target'])) {
                $targets = $match['target'];
                foreach ($targets as $target) {
                    $match['target'] = $target; 
                   // Register pre-hooks
                    if ( !empty($this->config['hooks'][ $match['target'] ]) AND is_array($this->config['hooks'][ $match['target'] ])) {
                        foreach ($this->config['hooks'][ $match['target'] ] as $index => $hook) {
                            $next_actions[ $match['target'] .'_prehook'. $index ] = is_array($hook)? $hook : ['target' => $hook, 'params' => $hook_params];
                        }   
                    }  
                    $next_actions[ $match['target'] ] = $match; //['current_app'] is visible for all targets
                }
            } else { //single target
                // Register pre-hooks
                if ( !empty($this->config['hooks'][ $match['target'] ]) AND is_array($this->config['hooks'][ $match['target'] ]) ){
                    foreach ($this->config['hooks'][ $match['target'] ] as $index => $hook) {
                        $next_actions[ $match['target'] .'_prehook'. $index ] = is_array($hook)? $hook : ['target' => $hook, 'params' => $hook_params];
                    }   
                }   
                $next_actions[ $match['target'] ] = $match; //fully defined so we can use $queue['current_app'] later in index.php
            }
        }
        /*/test cors for Safari preflight OPTIONS query, remove when done
        header('Access-Control-Allow-Origin: '. $this->config['system']['edit_url']);
        //header('Access-Control-Allow-Method: POST, OPTION'); 
        header('Access-Control-Allow-Headers: X-Requested-With');
        header('Access-Control-Allow-Credentials: true');*/


        ############### ROUTER DISPATCH  ##################
        //register App routes (while we don't have AppCenter)
        //$next_actions['Appstore::registerConfig'] = ['params' => []];
        //end App activation
        if ( !empty($next_actions) ){
            $this->router->registerQueue($next_actions);
        }    

        //for multiple targets, main target is defined by route name or the last target 
        $this->match = ( !empty($match['name']) AND !empty($next_actions[ $match['name'] ]) )? 
            $next_actions[ $match['name'] ] : $match;
    }

    // Process queue FIFO, each queue runs once and pass results to View
    public function controller_dispatch(){     
        try {      
            while ($queue = $this->router->pollQueue()) {
                //Resolve target and type hinting in parameters
                //echo '<pre>Running queue: '. print_r($queue) .'</pre>';
                $queue = $this->controller_resolveParameters($queue);
                $results[] = call_user_func_array($queue['target'], array_values($queue['params']??[]) ); //array_values due to PHP8 uses named parameters
            }  
            return $results??null; 
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }
    // Short hand to process queues and render View output
    public function controller_run(){           
        $this->controller_dispatch();
        $this->view->render();  
    }

    //Resolve target and type hinted parameters 
    protected function controller_resolveParameters($queue){
        /* queue and route can be defined like this
        $next_actions['anon::mous'] = ['target' => function ($vars) use ($id) {
                echo "<pre>Closure defined via next_actions";
                print_r($vars); 
                echo "</pre>";
                        },
            'params' => ["Site::edit" => [25]],
            'name'   => 'anonymous',
        ];
        */
        try {
            //Resolve target class and create object if required
            $namespace = (new \ReflectionClass($this))->getNamespaceName(); //get the namespace of the running class 
            if (!is_callable($queue['target']) AND substr_count($queue['target'], '::') === 1) { //must be 'Class::action'
                $queue['target'] = explode('::', $queue['target']);
                $class_name = $queue['target'][0];
                if (strpos($class_name, '\\') === false) { // no namespace defined
                    $class_name = $namespace .'\\'. $class_name; // dynamic class name is always created in global namespace, prefix it
                }

                if (empty($this->container[$class_name])) {
                    $this->container[$class_name] = new $class_name($this->config, $this->dbm, $this->router, $this->view, $this->user);
                    if (method_exists($this->container[$class_name], 'checkActivation')) {
                        $this->container[$class_name]->checkActivation();
                    }
                }
                $queue['target'][0] = $this->container[$class_name];
            }
            //Resolve parameters - replace them with corresponding values
            $reflection = is_array($queue['target']) ? new \ReflectionMethod($queue['target'][0], $queue['target'][1]) :
                                                       new \ReflectionFunction($queue['target']);

            foreach ($reflection->getParameters() as $index => $parameter) {
                $callback = null; //reset this and $param_class each iteration
                //$param_class = $parameter->getClass()->name; // if typehint'ed, we'll have class name here - before PHP8.0
                $param_class = ($parameter->getType() && !$parameter->getType()->isBuiltin() )? $parameter->getType()->getName() : NULL; //since PHP8.0
                if ( !empty($queue['params']) AND !empty( array_keys($queue['params'])[$index] ) ){
                    $index = array_keys($queue['params'])[$index]; //$queue['params'][$index] may contain callback key like 'Class:action'
                } 

                if (substr_count($index, '::') === 1) { //indicate this is a callback via array key
                    $callback = explode('::', $index);
                    $param_class = $callback[0];
                } else if ($parameter->isOptional() AND is_string($parameter->getDefaultValue()) AND @substr_count($parameter->getDefaultValue(), '::') === 1) {
                    //typehint'ed method using default value i.e: function show(show = 'Site:render')
                    $callback = explode('::', $parameter->getDefaultValue());
                    $param_class = $callback[0];
                }

                if ($param_class AND strpos($param_class, '\\') === false) { // no namespace defined
                    $param_class = $namespace .'\\'. $param_class; 
                }

                if ($param_class AND !(is_a($queue['params'][$index]??null, $param_class))) { //param is not an instance of class
                    if (empty($this->container[$param_class])) {
                        $this->container[$param_class] = new $param_class($this->config, $this->dbm, $this->router, $this->view, $this->user);
                        // consider using https://github.com/gonzalo123/new/blob/master/src/Builder.php to feed parameters
                        if (method_exists($this->container[$param_class], 'checkActivation')) {
                            $this->container[$param_class]->checkActivation();
                        }
                    }
                    if (!empty($callback) AND $callback[1] != '__construct') { //pass callback result when the method is not __construct
                        $callback[0] = $this->container[$param_class];
                        $queue['params'][$index] = call_user_func_array($callback, $queue['params'][$index]??[] );
                    } else { //typehint'ed => pass an instance of class 
                        $queue['params'][$index] = $this->container[$param_class];
                    }   
                }
                
                // callback value is not present, set passed value to default param value if it is optional and not a callback 
                // we need this incase $queue['params'][$index] is not the last param
                if ( !array_key_exists($index, $queue['params']) AND $parameter->isOptional() AND $parameter->getDefaultValue() !== false AND @substr_count($parameter->getDefaultValue(), '::') < 1 ) {
                    $queue['params'][$index] = $parameter->getDefaultValue();
                }
            }

            return $queue;
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }    
    }  

    // if user access is required, create user object
    public function controller_requireUser(User $user = NULL) {
        ############### USER (required: dbm, config (router match()) ##################
        if ($user instanceof User) {
            $this->user = $user;
        } else {
            if (!empty($user)) {
                throw new \Exception("$user is not an instance of User class");                
            }
            $this->user = new User($this->config, $this->dbm, $this->passport);
        }    
    }

    // if authentication is required, enforce it here
    public function controller_requireLogin(User $user = NULL) {
        if ($user instanceof User) {
            $this->user = $user;
        } else {
            if (!empty($user)) {
                throw new \Exception("$user is not an instance of User class");                
            }
            $this->controller_requireUser();
        }    
        //IMPORTANT - It's time to check user authentication
        $this->user->authenticate();
        //At this stage we can unset sensitive oauth credentials as they are no longer needed
        unset($this->config['oauth']);
    }

    //Enforce rate limit (except local connections)
    public function controller_throttleRequest($options = null, $api_options = null) {
        if ( empty($this->redis) OR 
            (empty($options['max']) AND empty($api_options['max']) ) OR 
            $_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR'] OR  
            $_SERVER['REMOTE_ADDR'] == '127.0.0.1'  
        ){
            return false;
        } else {    
            //most api requests are authenticated request, using user ID is reliable
            //unauthenticated requests' headers cannot be trusted except REMOTE_ADDR but REMOTE_ADDR is CFlare IP when proxied
            //for browser based we use time cookie to verify the HTTP_X_FORWARDED_FOR without user notice
            //Abusers have to reuse verifier/forged IP (and are tracked) unless they retrieve the verifier everytime they change IP
            if ( $this->user->getId() ){
                $key = 'rate_'. $this->user->getId();
            } elseif ( $key = trim($_REQUEST["username"]??'') ){
                //use username
            } elseif ( $key = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ){
                if ( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ){ //REMOTE_ADDR = CloudFlare/proxies IP
                    $client_ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
                    $expiry = date('H');
                    $expiry = $expiry < 8? 'MN' : ($expiry < 17? 'AF' : 'EV');
                    $expiry = date('ymd') . $expiry;
                    $ip_hash = @hash('sha256', $client_ip . substr($this->config['salt'], -5) . $expiry );
                    if ( !empty($_COOKIE['sgrip']) AND hash_equals($_COOKIE['sgrip'], $ip_hash) ){
                        $key = $client_ip; //use the forwarded IP even if it is forged
                    } else { //DOS potential but it can just affect the first browsing request only, next requests have cookie
                        $key .= substr($_SERVER['DOCUMENT_URI'], -16); //the last 16 character of URI (no query string)
                        setcookie("sgrip", $ip_hash, 0, '/', '.'. trim($this->config['site']['url']) );
                    }    
                }
            }

            if ( !empty($key) ){
                if ( empty($_COOKIE) AND !empty($api_options['max']) ){ //API, OK to apply to 1st browser request with no cookie
                    $options = $api_options; //abusers may send cookie to bypass API limit but CSRF token is required when POSTing with cookie
                } elseif ( empty($options['max']) ){
                    return false; //throttle not enabled 
                }
                if (empty($options['time'])) $options['time'] = 300;

                $current_time = $this->redis->time(); //0: timestamp, 1: microseconds 
                $window_start = $current_time[0] - ($options['time']);
                $this->redis->zremrangebyscore($key, '-inf', $window_start); //remove timestamps outside the window
                $accumulation = $this->redis->zcard($key) + 1; //get all timestamps plus this one

                //header('X-RateLimit-Key: '. $key);
                if ($accumulation > $options['max']){
                    header('X-RateLimit-Limit: '. $options['max'] );
                    header('X-RateLimit-Remaining: '. $options['max'] - $accumulation); 
                    header('X-RateLimit-Reset: '. $options['time']); 
                    $status['result'] = 'error';
                    $status['code'] = 429;
                    $status['message'][] = $this->trans('You have reached the connection limit');                    
                    if ($this->view->html) {
                        $status['html']['message_type'] = 'warning';
                        $status['html']['message_title'] = $this->trans('Information'); 
                    }
                    $this->view->setStatus($status);
                    $this->view->render();
                    exit;
                } else { //add milisecond as member with timestamp as the score, let the key expire automatically
                    $this->redis->multi()
                        ->zadd($key, $current_time[0], $current_time[0] . round($current_time[1]/1000))
                        ->expire($key, $options['time'])
                        ->exec();
                }
            }
        }
    }        
    //Get site's config using id or hostname
    protected function controller_getSiteConfig($id = 0){
        //require dbm and config
        if (empty($this->dbm) AND empty($this->config)) return null;

        $site = $this->config['site'];
        if ( !empty($site) ){
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
            if ( str_ends_with($site['url'], $this->config['subdomain']) AND !str_starts_with($_SERVER['SERVER_NAME'], 'my.') ){
                $site['account_url'] = str_replace($this->config['subdomain'], '.my'. $this->config['subdomain'], $site['url']);
            } else {
                $site['account_url'] = 'my.'. $site['url'];
            }
            $languages = $this->getLanguages();
            $url = $site['url'] .'/'. trim($_SERVER['REQUEST_URI'], '/') . '/';
            if (!empty($site['locales'])) {
                foreach ($site['locales'] as $key => $short) {
                    //$short = strtok($locale, '/');
                    $long  = $languages[ $short];
                    unset($site['locales'][$key]);
                    $site['locales'][ $short ] = $long;
                    // detect active language specified in the url
                    if (empty($id) AND 
                        strpos($url, str_replace(['http://', 'https://'], '', trim($site['url'], '/')) .'/'. $short .'/')  !== false) {
                        $site['locale'] = $short;
                    }
                }       
            }
            if ( !empty($site['language']) ){
                //make the default language  the first - required for page_edit as it loops through locales
                $site['locales'] = [$site['language'] => $languages[ $site['language'] ] ] + ($site['locales']??[]); 
            } 
            if ( empty($site['currency']['code']) ){
                $site['currency'] = [
                    'code' => 'USD',
                    'prefix' => '$',
                    'suffix' => '',
                    'precision' => 2,
                ];
            }   
        }
            
        return $site;               
    }

    //Get routes stored in database
    protected function controller_getRoutes($passport){
        //require dbm and config
        if (empty($this->dbm) AND empty($this->config)) return null;
        //central read is from replicated localhost server
        $results = $this->dbm->getConnection('central')
            ->table($this->config['system']['table_prefix'] .'_system')
            ->select('property', 'value')
            ->where('type', $passport .'_route')
            //->orWhere('type', 'admin_route') //remove later
            ->orderBy('id')
            ->get()->all();

        foreach ($results AS $r){
            $route = json_decode($r['value']??'', true); //return array
            if (empty($route[2])) { //target
                $route[2] = $r['property'];
            }
            if (empty($route[3])) { //name
                $route[3] = $r['property'];
            }
            $routes[ ] = $route;
        }
        return $routes??[];               
    }
}
?>
