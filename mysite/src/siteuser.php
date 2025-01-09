<?php
namespace SiteGUI\Core;
require __DIR__ .'/vendor/LiteGUI/Traits/Controller.php'; //require config.php

class SiteUser {
	use \LiteGUI\Traits\Controller;

    public function __construct() {
    	if ( !str_starts_with($_SERVER['SERVER_NAME'], 'my.') AND 
             !str_starts_with($_SERVER['SERVER_NAME'], 'mz.') AND 
             !str_contains($_SERVER['SERVER_NAME'], '.my.')  AND 
             !str_contains($_SERVER['SERVER_NAME'], '.mz.') 
         ){
            echo "Incorrect Domain. It should start with 'my'";
            if ($_SERVER['SERVER_ADDR'] != trim($_SERVER['SERVER_NAME'], '[]')) {
                echo ' e.g: '. htmlspecialchars( filter_var('https://my.'. $_SERVER['SERVER_NAME'], FILTER_VALIDATE_URL) );
            } 
    		exit;   
    	}
        //Let's setup router, view, dbm  
        $settings = [
            'passport' => 'SiteUser', //as unique as possible, we dont want to share this with another app'
            'base_path' => '/account', //should start with / and no trailing / 
            'template_dir' => 'public/templates/admin', //relative to resources dir and no beginning and trailing / 
            'routes' => $this->getRoutes(),
            'locale' => 0,
        ];
        $this->controller_init($settings);           
        
        $user = $this->container[__NAMESPACE__ .'\\User'] = new User($this->config, $this->dbm, $this->router, $this->view, $this->passport);
        //user's language > site's language. DO NOT load site's lang file because trans() does not escape XSS when using for JS code       
        $real_template_dir = $this->config['system']['base_dir'] .'/resources/'. $this->config['system']['template_dir'] .'/'. $this->config['system']['template'];
        if ($user->getLanguage() AND $user->getLanguage() != ($this->config['site']['language']??$this->config['system']['language']) AND is_readable($real_template_dir .'/lang/'. basename($user->getLanguage()) .'.json')) {
            $this->config['lang'] = $user->getLanguage() == 'en'? [] : (json_decode(file_get_contents($real_template_dir .'/lang/'. basename($user->getLanguage()) .'.json')??'', true)??[]) + ($this->config['lang']??[]); //if en, override with empty otherwise override keys
        }

        if ($this->match['current_app'] == 'Cart') {
	        $this->controller_requireUser($user);
            header('Access-Control-Allow-Origin: https://'. $this->config['site']['url']); //must specify for ajax to send cookie
            //header('Access-Control-Allow-Method: POST, OPTION'); 
            header('Access-Control-Allow-Headers: X-Requested-With');
            header('Access-Control-Allow-Credentials: true');
        } else {
	        $this->controller_requireLogin($user);
           if ( !empty($_REQUEST['server2server']) AND $this->user->has('Role::Server2Server')){ //allow blocks transfer s2s
              $this->view->server2server = 1;
           }
        } 
        $this->controller_throttleRequest($this->config['rate_limits']['client']??null, $this->config['rate_limits']['api']??null);

        if ( $this->user->getId() ){ //show menu for logged in user only
            $this->router->registerQueue([ 'Site::clientMain' => [ $this->match['current_app'] ] ], 'prepend');
        }
        if ($this->view instanceof \LiteGUI\View\Smarty) {
            $this->view->setLayout('account'); //set layout for this app before dispatching so denyAccess will use the layout
        } 

        //Dispatch request to corresponding target/class
        try {
            while ($queue = $this->router->pollQueue()) {
                //Resolve target and type hinting in parameters
                $queue = $this->controller_resolveParameters($queue);
                //echo '<pre>Running queue: '. $queue['name'] .'</pre>';			
    			//this is the main target, get page_id to handle page not found and hook other blocks
    			if (!empty($queue['current_app'])) { 
    				$page_id = call_user_func_array($queue['target'], array_values($queue['params']??[]) );
    				
    				// render extra HTML blocks for the main target
    				if ($this->view->html){				
    					$next_actions = []; //empty it first
    					$this->router->registerQueue($next_actions);
    				}
    			} else {
    				call_user_func_array($queue['target'], array_values($queue['params']??[]) );
    			}
            } 
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }              
         //Let's make some modifications to view 
        if ($this->view->html AND !empty($this->match['target']) ){ 
            $words = explode("::", $this->match['target']);
            $words[1] = str_replace('client', '', $words[1]);
            if ($words[1] == "Main") {
                $words[1] = '';
                if ($words[0] == 'App' AND isset($this->match['params']['app'])) {
                    $words[0] = $this->formatAppLabel($this->match['params']['app']);
                } 
                $words[0] = $this->pluralize($words[0]);
                if ($words[0] == 'Sites'){
                    $words[0] = 'Account Manager';
                }
            } elseif ($words[1] == 'View' AND empty($this->match['params']['id']) ){
                $words[1] = 'View';
            }

            $this->view->append('html', ['title' => $this->trans(ucfirst($words[1]) ." :item", ['item' => $words[0]]) ], TRUE);  
        }
            
        $this->view->render();
        //Must be AFTER authentication check so user is allowed onetime only, authentication is required on the next visit.
        if (strpos($_SERVER['SERVER_NAME'], parse_url($this->config['system']['edit_url'], PHP_URL_HOST)) !== false) { //onetime token based access, user has been identified -> remove session so it's really used once.
           unset($_SESSION[ $this->passport ]);
           session_destroy();
        }        
	}

    protected function modifyMatch($match) {
        $this->config['site'] = $this->controller_getSiteConfig();
        if ($this->config['site']){
            if ($this->config['site']['status'] != 'Active' OR 
                ($this->config['site']['tier'] == 1 AND !str_contains($this->config['site']['url'], $this->config['subdomain']) )
            ){
                echo $this->trans("Site is not active. Please check back later. If you are the owner, please verify your domain to activate it.");
                exit();            
            } 

            $this->config['system']['sso_provider'] = 'https://my.'. parse_url($this->config['system']['sso_provider']??$this->config['system']['url'], PHP_URL_HOST);
            if ($this->config['site']['id'] == 2){
                $this->config['system']['manage_url'] = $this->config['system']['url'] .'/siteadmin' ;
            }

            if ( str_starts_with($_SERVER['SERVER_NAME'], 'my.') OR str_starts_with($_SERVER['SERVER_NAME'], 'mz.') ){
                $this->config['system']['url'] = 'https://'. $this->config['site']['account_url'];
                $this->config['system']['edit_url'] = 'https://mz.'. $this->config['site']['url']; 
            } else { //subdomain.my.sitegui.co
                $this->config['system']['url'] = 'https://'. str_replace($this->config['subdomain'], '.my'. $this->config['subdomain'], $this->config['site']['url']);
                $this->config['system']['edit_url'] = 'https://'. str_replace($this->config['subdomain'], '.mz'. $this->config['subdomain'], $this->config['site']['url']);              
            }   
            //this is the ealiest point to change $this->passport, only controller_getRoutes($this->passport) before this point and it's expected
            $this->passport .= $this->config['site']['id']; //make sure user have access to specific site only
            //reevaluate - unset sensitive info for public site
            //unset($this->config['site']['owner'], $this->config['site']['tier'], $this->config['site']['revision'], $this->config['site']['version'], $this->config['site']['auto_upgrade'], $this->config['site']['role_site']);
        }
        //id matching in onetime hash-based access when editing widget at edit_url 
        if (!empty($match['params']['id']) AND 
            strpos($_SERVER['SERVER_NAME'], parse_url($this->config['system']['edit_url'], PHP_URL_HOST)) !== false
        ){ 
            // hash::expiredTime::userId::widgetIdOrType::crsfToken
            $hash = explode('::', @$this->decode($match['params']['id'])); 

            //strip query string from REQUEST_URI
            if ($hash[0] === @$this->hashify($hash[1] .'::'. $hash[2] .'::'. $hash[3] .'::'. $this->config['system']['edit_url'] . dirname(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) AND time() < $hash[1]
            ) {// hash is OK and time is OK
                if ( !isset($_SESSION[ $this->passport ]) ) $_SESSION[ $this->passport ] = []; //initialize it - required
                $_SESSION[ $this->passport ]['id'] = $hash[2]; //we dont need to set User here, but User must be init with $_SESSION[ $this->passport ]['id']
                $_SESSION['token'] = $hash[4]; //set csrf_token for pageee - used for ajax requests    
                $match['params']['id'] = $hash[3];
            }
        }
        //modify current_app when edit app record
        if ($match['current_app'] == 'App' AND isset($match['params']['id']) ) { 
            if ( ! is_numeric($match['params']['id']) ){//edit app item                  
                $match['current_app'] = $this->formatAppName(strtok($match['params']['id'], '/'));                
            }   
        }

        return $match;
    }    
    protected function getRoutes() {
        $routes[] = ['GET|POST', '/cart/[add|update|clear:action]/[POST:item]?.[json:format]?', 'Site::clientMain', 'Cart::action'];
        $routes[] = ['POST', '/product/update.[json:format]?/[POST:page]?', 'Product::clientUpdate', 'Product::clientUpdate'];
        $routes[] = ['POST', '/app/update.[json:format]?/[POST:page]?', 'App::clientUpdate', 'App::clientUpdate'];
        $routes[] = ['POST', '/app/[*:app]/delete.[json:format]?/[POST:id]?', 'App::clientDelete', 'App::clientDelete'];
        $routes[] = ['GET',  '/app/view/[*:id]?.[json:format]?', 'App::clientView', 'App::clientView'];
        $routes[] = ['GET',  '/[i:site_id]/app/[edit|copy|manage:action]/[workaround:id]?.[json:format]?', 'App::clientView', 'App::action']; //workaround for Admin route
        $routes[] = ['GET',  '/app/[*:app]?.[json:format]?', 'App::clientMain', 'App::clientMain'];
        $routes[] = ['GET|POST', '[/:slug]?.[html|json:format]?', 'Site::clientMain', 'Site::clientMain'];

        return $routes;
    }
}
new SiteUser();
?>
