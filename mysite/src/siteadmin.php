<?php
namespace SiteGUI\Core;
require __DIR__ .'/vendor/LiteGUI/Traits/Controller.php'; //require config.php

//Define the application
class SiteAdmin {
    use \LiteGUI\Traits\Controller;

    public function __construct() {
        //configure controller switches
        $settings = [
            'passport' => 'SiteAdmin', //as unique as possible, we dont want to share this with another app'
            'base_path' => '/siteadmin', //should start with / and no trailing / 
            'template_dir' => 'public/templates/admin', //relative to resources dir and no beginning and trailing / 
            'routes' => $this->getRoutes(),
        ];
        //template modification on the fly
        if (!empty($_GET['systpl'])) $settings['template'] = basename($this->sanitizeFileName($_GET['systpl'])); 
        //Let's setup router, view, dbm  
        $this->controller_init($settings);   
        
        //add frontend routes     
        $site_routes = $this->controller_getRoutes('SiteUser');
        foreach ($site_routes AS $key => $site_route) {
            $site_routes[ $key ][1] = '/view?'. $site_route[1];
        }
        if ( is_array($site_routes) ) $this->router->addRoutes($site_routes);

        //If login required, setup user and enforce authentication here
        $user = $this->container[__NAMESPACE__ .'\\Staff'] = new Staff($this->config, $this->dbm, $this->router, $this->view, $this->passport);
        //user's language > site's language        
        $real_template_dir = $this->config['system']['base_dir'] .'/resources/'. $this->config['system']['template_dir'] .'/'. $this->config['system']['template'];
        if ($user->getLanguage() AND $user->getLanguage() != ($this->config['site']['language']??$this->config['system']['language']) AND is_readable($real_template_dir .'/lang/'. basename($user->getLanguage()) .'.json')) {
            $this->config['lang'] = $user->getLanguage() == 'en'? [] : (json_decode(file_get_contents($real_template_dir .'/lang/'. basename($user->getLanguage()) .'.json')??'', true)??[]) + ($this->config['lang']??[]); //if en, override with empty otherwise override keys
        } 

        if ( !empty($_GET['oauth']) AND !empty($_GET['initiator']) AND (($_GET['login']??'') === 'step1') ){ 
            //sso login step 1 already started by my.sitegui.com/account for SSO from clientarea 
            //sso login step 2: at backend - redirect back to my.account after getting backend token
            $url = $this->decode($_GET['initiator']); //this is my.account
            if ($url == 'https://my.sitegui.com/account' || $url == 'https://my.pageee.com/account' ){ //ensure it is actually my.account 
                $url .= '?oauth='. $_GET['oauth'] 
                     .'&login=step2'
                     .'&token='. $_SESSION['token'] . str_replace('/', '', $_SESSION[ $this->passport ]['auth_request_from']??$this->config['site']['id'] )
                     .'&requester='. $this->encode($this->config['system']['url'] . $this->config['system']['base_path']);
                header('Location: '. $url);
                exit;
            }    
        }
        $this->controller_requireLogin($user);
        if ( !empty($_REQUEST['server2server']) AND $this->user->has('Role::Server2Server')){ //allow blocks transfer s2s
            $this->view->server2server = 1;
        }
        $this->controller_throttleRequest($this->config['rate_limits']['staff']??null, $this->config['rate_limits']['api']??null);

        //Let's make some modifications to router's queue before dispatching 
        $this->router->registerQueue([ 'Site::render' => [ $this->match['current_app'] ] ], 'prepend');//prepend to render menu before denyAccess
        if ( !empty($this->config['activation'][ __NAMESPACE__ .'\\Report'] ) AND !empty($this->match['target']) AND str_ends_with($this->match['target'], '::main') AND $this->match['target'] != 'Site::main'){ 
            //Site::main on remote server do not have access to table widget
            $this->router->registerQueue([ 'Report::render' => [ $this->match['current_app'] ] ]);
        }    

        //Dispatch request to corresponding target/class
        $this->controller_dispatch();

        //Let's make some modifications to view 
        if ($this->view->html AND !empty($this->match['target']) ){ 
            $words = explode("::", $this->match['target']);
            if ($words[0] == "Appstore") {
                $words[0] .= ($words[1] == "main")? '' : ' Listing';
            }
            if ($words[1] == "main") {
                $words[1] = ($words[0] == "Appstore")? '' : 'Manage';
                if ($words[0] == 'App' AND isset($this->match['params']['app'])) {
                    $words[0] = $this->formatAppLabel($this->match['params']['app']);
                }
                $words[0] = $this->pluralize($words[0]);
            } elseif ($words[1] == 'edit' AND empty($this->match['params']['id']) ){
                $words[1] = "New";
            }

            $this->view->append('html', ['title' => $this->trans(ucfirst($words[1]) ." :item", ['item' => $words[0]]) ], TRUE);  
            //generate random background rgb color
            $rgb = ($words[1] == 'Manage')? [0,  120, rand(0,150)] : [0,  255, rand(0,255)];
            shuffle($rgb);
            if ($words[1] == "store" OR $words[0] == 'Cart') $rgb = [0,50,50];
            $this->view->append('html', ['rgb' => implode(',', $rgb) ], TRUE); 
        }
        $this->view->render();
        //or use $app->controller_run() to dispatch + render at once  
    }

    protected function modifyMatch($match) {   
        if ($this->config['site'] AND empty($this->config['system']['single']) ){
            if ($this->config['site']['id'] != 1 AND $match['target'] != 'Site::delete'){
                $this->config['system']['sso_provider'] = 'https://'. parse_url($this->config['system']['sso_provider']??$this->config['system']['url'], PHP_URL_HOST);
                $this->config['system']['url'] = 'https://w'. $this->config['site']['owner'] .'.'. substr($this->config['system']['url'], 8);
                //$this->config['system']['edit_url'] = 'https://mz.'. $this->config['site']['url']; 
            } 
            //prevent users from accessing other sites at their sub-domains so XSS on one user cannot go to others
            if ($_SERVER['SERVER_NAME'] != substr($this->config['system']['url'], 8) AND !str_contains($_SERVER['SERVER_NAME'], parse_url($this->config['system']['edit_url'], PHP_URL_HOST)) ){
                header('Location: '. filter_var($this->config['system']['url'] . $_SERVER['REQUEST_URI'], FILTER_VALIDATE_URL) );
                exit;
            }   
            //this is the ealiest point to change $this->passport, only controller_getRoutes($this->passport) before this point and it's expected
            //$this->passport .= $this->config['site']['id']; //make sure user have access to specific site only
        }

        //id matching in onetime hash-based access when editing widget at edit_url 
        if (!empty($match['params']['id']) AND 
            str_contains($_SERVER['SERVER_NAME'], parse_url($this->config['system']['edit_url'], PHP_URL_HOST))
        ){ 
            // hash::expiredTime::userId::widgetIdOrType::crsfToken
            $hash = explode('::', @$this->decode($match['params']['id'])); 
            //strip query string from REQUEST_URI
            if ( hash_equals($hash[0], @$this->hashify($hash[1] .'::'. $hash[2] .'::'. $hash[3] .'::'. $this->config['system']['edit_url'] . dirname(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) ) AND time() < $hash[1]
            ) {// hash is OK and time is OK
                if ( !isset($_SESSION[ $this->passport ]) ) $_SESSION[ $this->passport ] = []; //initialize it - required
                $_SESSION[ $this->passport ]['id'] = $hash[2]; //we dont need to set User here, but User must be init with $_SESSION[ $this->passport ]['id']
                $_SESSION['token'] = $hash[4]; //set csrf_token for pageee - used for ajax requests    
                $match['params']['id'] = $hash[3];
            }
        }
        //modify current_app when edit app record
        if ($match['current_app'] == 'App') { 
            if ( isset($match['params']['id']) AND ! is_numeric($match['params']['id']) ){//edit app item
                $match['current_app'] = $this->formatAppName(strtok($match['params']['id'], '/'));                
            }  
            if ( !empty($match['params']['page']['subtype']) ){
                $match['current_app'] = $this->formatAppName($match['params']['page']['subtype']);
            } 
        }
        //set headers to allow cross site ajax if the URI is widget/delete/location, lookup
        if ( !empty($_GET['cors']) AND in_array(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), 
            [
                $this->router->generate('Template::snippet', ['site_id' => $this->config['site']['id'] ]),
                $this->router->generate('Widget::preview', ['site_id' => $this->config['site']['id'] ]),
                $this->router->generate('Widget::deleteLocation', ['site_id' => $this->config['site']['id'] ]),
                $this->router->generate('Collection::leave', ['site_id' => $this->config['site']['id'] ]),
                //$this->router->generate('Lookup::now', ['site_id' => $this->config['site']['id'] ]),            
                //$this->router->generate('Notification::main', ['site_id' => $this->config['site']['id'] ]),            
                $this->router->generate('Staff::onboard', ['site_id' => $this->config['site']['id'] ]),            
                //$this->router->generate('Assistant::action', ['site_id' => $this->config['site']['id'], 'action' => 'generate' ]),
                //$this->router->generate('Activity::main', ['site_id' => $this->config['site']['id'] ]),
            ]) 
        ){
            header('Access-Control-Allow-Origin: '. $this->config['system']['edit_url']);
            header('Access-Control-Allow-Headers: X-Requested-With');
            header('Access-Control-Allow-Credentials: true');
        }
        return $match;
    }

    protected function getRoutes() {
        $routes[] = ['GET',  '/app/[edit|copy|manage:action]/[*:id]?.[json:format]?', 'App::action', 'App::action'];
        $routes[] = ['POST', '/app/update.[json:format]?/[POST:page]?', 'App::update', 'App::update'];
        $routes[] = ['POST', '/app/delete/[*:app].[json:format]?/[POST:id]?', 'App::delete', 'App::delete'];
        $routes[] = ['GET',  '/app/[*:app]?.[json:format]?', 'App::main', 'App::main'];
        $routes[] = ['POST', '/appstore/register.[json:format]?/[POST:name]?', 'Appstore::register', 'Appstore::register'];
        $routes[] = ['POST', '/appstore/deregister.[json:format]?/[POST:name]?', 'Appstore::deregister', 'Appstore::deregister'];
        $routes[] = ['GET|POST', '/appstore/configure.[json:format]?[POST:name]?[POST:page]?', 'Appstore::configure', 'Appstore::configure'];
        $routes[] = ['GET',  '/appstore.[json:format]?', 'Appstore::main', 'Appstore::main'];
        $routes[] = ['GET',  '/oauth.[json:format]?', 'Appstore::configure', 'Appstore::oauth']; //wildcard oauth URL 

        $routes[] = ['GET|POST',  '/upgrade/site.[json:format]?', 'Upgrade::site', 'Upgrade::site'];
        $routes[] = ['GET|POST',  '/upgrade.[json:format]?', 'Upgrade::main', 'Upgrade::main'];
        $routes[] = ['GET',  '/[*:name]/edit/[a:id]?.[json:format]?', 'App::edit2', 'App::edit2'];
        $routes[] = ['GET|POST', '[/:slug]?.[html|json:format]?', 'App::main', 'App::default'];
        return $routes;
    }
}
//Launch the application
new SiteAdmin();  
?>
