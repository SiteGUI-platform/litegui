<?php
namespace SiteGUI\Core;
require __DIR__ .'/vendor/LiteGUI/Traits/Controller.php'; //require config.php

class SiteIndex {
	use \LiteGUI\Traits\Controller;

    public function __construct() {
    	if ( str_starts_with($_SERVER['SERVER_NAME'], 'my.') ){ //stop subdomain my. from accessing this app
    		header('Location: '. filter_var($_SERVER['REQUEST_SCHEME'] .'://'. substr($_SERVER['SERVER_NAME'], 3) . $_SERVER['REQUEST_URI'], FILTER_VALIDATE_URL) );
    		exit;
    	}
        //Let's setup router, view, dbm  
        $settings = [
            'routes' => $this->getRoutes(),
            'locale' => 1,
            'escape_html' => true, //this app output raw HTML where applicable only
        ];
        $this->controller_init($settings);
        if ( str_ends_with($_SERVER['SERVER_NAME'], '.my'. $this->config['subdomain']) ){ //stop subdomain my. from accessing this app
            header('Location: '. filter_var($_SERVER['REQUEST_SCHEME'] .'://'. str_replace('.my'. $this->config['subdomain'], $this->config['subdomain'], $_SERVER['SERVER_NAME']) . $_SERVER['REQUEST_URI'], FILTER_VALIDATE_URL) );
            exit;
        }
        //Set original server for easier debug in multi-server mode
        header('SG-orig: '. strtok(gethostname(), '.') );
        //requires a valid site from here
		if (empty($this->config['site']['id'])) {
			echo '<div><a href="https://sitegui.com"><img src="https://cdn.sitegui.com/public/uploads/global/img/site404.png" style="max-height:95vh; margin:auto; display:block;"></a></div>';
			exit();
        } elseif ($this->config['site']['status'] != 'Active' OR 
            ($this->config['site']['tier'] == 1 AND !str_contains($this->config['site']['url'], $this->config['subdomain']) )
        ){
            unset($this->config['site']['template']); //use default template
            echo $this->trans("Site is not active. Please check back later. If you are the owner, please verify your domain to activate it.");
            exit();            
        } 

        $cf = $this->config;
        //unset sensitive info for public site
        unset($cf['site']['owner'], $cf['site']['tier'], $cf['site']['server'], $cf['site']['revision'], $cf['site']['version'], $cf['site']['auto_upgrade'], $cf['site']['role_site']);
        $template_dir = 'public/templates/site/'. $cf['site']['id'];
        //load system language here because we dont provide template_dir for Controller to load, use site language instead of system
        if (is_readable($this->config['system']['base_dir'] .'/resources/public/templates/admin/'. $this->config['system']['template'] .'/lang/'. basename($this->config['site']['language']??$this->config['system']['language']) .'.json')) {
            $this->config['lang'] = json_decode(file_get_contents($this->config['system']['base_dir'] .'/resources/public/templates/admin/'. $this->config['system']['template'] .'/lang/'. basename($this->config['site']['language']??$this->config['system']['language']) .'.json')??'', true)??[];
        }
    	if (!empty($cf['site']['template'])) { //site - use site.cdn
            if ( ! @is_dir($cf['system']['base_dir'] .'/resources/'. $template_dir .'/'. $cf['site']['template']) ) {
                //check if this global template is activated for this site
                $activated = $this->dbm->getConnection()
                    ->table($cf['system']['table_prefix'] . $cf['site']['id'] .'_config')
                    ->where('type', 'activation')
                    ->where('name', str_replace('Core', 'Template', __NAMESPACE__) .'\\'. $cf['site']['template'])
                    ->value('name');
                if ($activated) {
                    $template_dir = 'public/templates/global';
                } else {
                    echo $this->trans('This global template needs to be activated');
                }                 
            }   
            $real_template_dir = $cf['system']['base_dir'] .'/resources/'. $template_dir .'/'. $cf['site']['template'];
            //$this->view->setTemplateDir($real_template_dir);
            $this->view->setTemplate($cf['site']['template']);
        } elseif (!empty($cf['system']['default_template'])) { //site with no template set
            $template_dir = 'public/templates/global';
            $real_template_dir = $cf['system']['base_dir'] .'/resources/'. $template_dir .'/'. $cf['system']['default_template'];
            $this->view->setTemplate($cf['system']['default_template']);
        } else { 
            echo $this->trans('Template is not defined!');
        }

        if ( empty($real_template_dir) ){
            $real_template_dir = $cf['system']['base_dir'] .'/resources/'. $template_dir .'/'. $cf['site']['template'];
        }  
        if ($this->view instanceof \LiteGUI\View\Smarty) {
            $cf['site']['cdn'] = trim($cf['system']['cdn'], '/')  .'/'. $template_dir;
            $this->view->setTemplateDir($real_template_dir);
            $this->view->assign('site', $cf['site'] );
            $this->view->assign('system', [] ); //we don't need it for frontend
            if ( isset($_COOKIE['SGCartQty']) ){
                $this->view->append('html', ['SGCartQty' => intval($_COOKIE['SGCartQty']) ], TRUE);
            }
        }

        $user = $this->container[__NAMESPACE__ .'\\User'] = new User($this->config, $this->dbm, $this->router, $this->view, $this->passport);
        //locale > user's language > site's language
        if ( !empty($cf['site']['locale']) AND is_readable($real_template_dir .'/lang/'. basename($cf['site']['locale']) .'.json')) {
            $lang_file = basename($cf['site']['locale']) .'.json';
        } elseif ($user->getLanguage() AND is_readable($real_template_dir .'/lang/'. basename($user->getLanguage()) .'.json')) {
            $lang_file = basename($user->getLanguage()) .'.json';
        } elseif (is_readable($real_template_dir .'/lang/'. basename($cf['site']['language']) .'.json')) {
            $lang_file = basename($cf['site']['language']) .'.json';
        } 
        if ( !empty($lang_file) AND is_readable($real_template_dir .'/lang/'. $lang_file) ){
            $this->config['lang'] = (json_decode(file_get_contents($real_template_dir .'/lang/'. $lang_file)??'', true)??[]) + ($this->config['lang']??[]); //override
        }

        if ( !empty($_GET['oauth']) ){ //oauth processes
            if ( !empty($_GET['initiator']) && (($_GET['login']??'') === 'step1') ){ 
                //sso login step 1 already started by my.account 
                //sso login step 2: at frontend - redirect back to my.account after getting frontend token
                $url = $this->decode($_GET['initiator']); //this is my.domain
                if ( str_starts_with($url, $this->config['system']['url'] .'/') OR str_starts_with($url, 'https://'. $this->config['site']['account_url'] .'/account') ){ //ensure it is actually my.account or system.url (unpublished preview)
                    $url .= '?oauth='. $_GET['oauth'] 
                         .'&login=step2'
                         .'&token='. $_SESSION['token'] . str_replace('/', '', $_SESSION[ $this->passport ]['auth_request_from']??$this->config['site']['id'] )
                         .'&requester='. $this->encode('https://'. $this->config['site']['url'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) );
                    if ( empty($_GET['preview']) ){
                        $url .= '&initiator='. $_GET['initiator']; //redirect back to my.account when it is started by my.account                        
                    }     
                    header('Location: '. $url);
                    exit;
                }    
            }
            //step 5: sso callback from my.account after successful authentication -> log user in
            //failed? -> required login anyway
            $this->controller_requireLogin($user);  
        } else {
            $this->controller_requireUser($user);
        }
        $this->controller_throttleRequest($this->config['rate_limits']['public']??null, $this->config['rate_limits']['api']??null);

        //Dispatch request to corresponding target/class
        //$this->controller_dispatch();
	    try {
	        while ($queue = $this->router->pollQueue()) {
	            //Resolve target and type hinting in parameters
	            $queue = $this->controller_resolveParameters($queue);
				//this is the main target, get page_id to handle page not found and hook other blocks
				if (!empty($queue['current_app'])) { 
					$page = call_user_func_array($queue['target'], array_values($queue['params']??[]) );
					
					if ($page === FALSE) { // main app may return 0 if 404 is already handled
                        $classPage = __NAMESPACE__ .'\\Page';
                        //GIVE App a chance to process index page after Page not found
                        if (get_class($queue['target'][0]) == $classPage) { 
                            $classApp = __NAMESPACE__ .'\\App';
                            if (empty($this->container[$classApp])) {
                                $this->container[$classApp] = new $classApp($this->config, $this->dbm, $this->router, $this->view, $this->user);
                            }
                            $render404[0] = $this->container[$classApp];
                            $render404[1] = 'render';
                            $page = call_user_func_array($render404, array_values($queue['params']??[]) ); //load app's index
                        }  
                        //Hurray!!! There is a matching app, set it the current app 
                        if ($page !== FALSE) { 
                            $queue['current_app'] = $this->formatAppName($queue['params']['slug']);
                        } else { //load default 404 page  
                            $status['result'] = 'error';
                            $status['message'][] = $this->trans('No such :item', ['item' => $queue['current_app'] ]); //this method gets invoked when Page not found
                            $this->view->setStatus($status);

    						header('HTTP/1.0 404 Not Found');
    						header('Status: 404 Not Found!!!');
    						if (empty($this->container[$classPage])) {
    	                   		$this->container[$classPage] = new $classPage($this->config, $this->dbm, $this->router, $this->view, $this->user);
    	                   	}
    						$render404[0] = $this->container[$classPage];
    						$render404[1] = 'render';
    						$page = call_user_func_array($render404, ['404'] );
                            
                        }    
					}
					// render extra HTML blocks for the main target
					if ($this->view->html AND empty($_REQUEST['subapp']) ){				
                        if ( !empty($page['id']) ){
						  //$this->view->assign('page_id', $page['id']); //used for indicating active page 
                        }  

						$next_actions = []; //empty it first
                        //$page['id'] should not be NULL to avoid "OR page_id is NULL" while we expect "OR page_id = id"
						$next_actions['Menu::render'] =   ["page_id" => $page['id']??0, "app" => $queue['current_app']];
						$next_actions['Widget::render'] = ["page_id" => $page['id']??0, "app" => $queue['current_app']];
                        if ( !empty($this->config['site']['locales']) ){
                            $next_actions['Page::renderLocales'] = ["page_id" => $page['id']??0 ]; //no need current_app                
                        }   
						if ( !empty($page['breadcrumb']) ) {
                            $next_actions['Page::renderBreadcrumb'] = ["page_id" => $page['id']??0, "app" => $queue['current_app']];
                            if ($page['type'] == 'App' OR ($page['type'] == 'Collection' AND strpos($page['subtype'], 'App::') !== false) ){
                                $next_actions['Page::renderBreadcrumb']['app_render'] = 1; //not core app - slug via App::main
                            }
                        }    

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
        $this->view->render();
	}
   
    protected function getRoutes() {
        $routes[] = ['GET', '/store/[*:slug]?.[html|json:format]?', 'Product::render', 'Product::render'];
        $routes[] = ['GET', '/store[/:slash]?.[html|json:format]?', 'Product::renderIndex', 'Product::renderIndex']; //special case
        $routes[] = ['GET',	'/collection/[*:slug]?.[html|json:format]?', 'Product::renderCollection', 'Product::renderCollection'];
        $routes[] = ['GET', '/category/[*:slug]?.[html|json:format]?', 'Page::renderCollection', 'Page::renderCollection'];

        $routes[] = ['POST', '/account/cart/add/[POST:item]?.[json:format]?', 'Cart::add', 'Cart::add'];
        $routes[] = ['GET|POST', '/check_subdomain/[*:subdomain2]?.[json:format]?[POST:subdomain]?', 'Site::exist', 'Site::exist']; //special route for site 1 to check subdomain existence
        $routes[] = ['GET', '/file/view/[*:id]?.[html|json:format]?', 'File::clientView', 'File::clientView'];
        //wildcard routes should be here as they should be the last routes to match (more specific routes before less specific ones)
        $routes[] = ['GET',	'/[*:app]/category/[*:slug]?.[html|json:format]?', 'App::renderCollection', 'App::renderCollection'];
        $routes[] = ['GET',	'/[*:app]/[*:slug].[json:format]?', 'App::render', 'App::render']; //slug cant be optional, no html format
        //$routes[] = ['GET', '/[docs|other_app:app]/', 'App::render', 'App::renderIndex']; //must specify app name or let Page::render handle this case
        $routes[] = ['GET|POST', '/inventory.[json:format]?/[POST:item]?', 'Inventory::render', 'Inventory::render'];
        $routes[] = ['GET', '/sitemap.xml', 'Page::renderSiteMap', 'Page::renderSiteMap'];
        $routes[] = ['GET|POST', '/[*:slug]?.[json:format]?', 'Page::render', 'Page::render']; //no html format to force index.html as the default index page

        return $routes;
    }
}
new SiteIndex();    	 
?>