<?php
namespace SiteGUI\App;
require __DIR__ .'/vendor/LiteGUI/Traits/Controller.php'; //require config.php

//Define the application
class WidgetProxy {
    use \LiteGUI\Traits\Controller;

    public function __construct()
    {
        //Let's setup router, view, dbm  
        $switch = [
            "routes" => $this->getRoutes(),
            "base_path" => '/w',
            "admin"  => 0,
            "locale" => 0,
            "cors"   => 0,
            "csrf"   => 0, //must be disabled to work
            "db"	 => 0,
        ];
        $this->controller_init($switch);    
   
		while ($queue = $this->router->pollQueue()) {
			// decode the value of parameter json (json string) to array
			$json = json_decode($queue['params']['json']??'', true);
			if (!empty($json['site_config'])) {
				$site_config = $json['site_config'];
				unset($json['site_config']);
			} else {
				$site_config['site'] = [];
			}	
			
			if (substr_count($queue['target'], '::') === 1) { //must be "Class::action"
				$queue['target'] = explode('::', $queue['target']);
				$class_name = (($queue['params']['type']??null) == 'widget')? str_replace('App', 'Widget', __NAMESPACE__) : __NAMESPACE__;
				if (strpos($queue['target'][0], '\\') === false) { // no namespace defined
					$class_name .= "\\". $queue['target'][0]; // dynamic class name is always created in global namespace, prefix it
				}

				if (empty($this->container[$class_name])) {
                	$this->container[$class_name] = new $class_name($site_config, $this->view);
				}
				$queue['target'][0] = $this->container[$class_name];
			}

			/*echo '<pre>';
			var_dump($json);
			echo '</pre>';
			*/
			$result = (array) call_user_func($queue['target'], $json); 
			if(empty($result['result'])) {
				$result['result']  = 'error';
				$result['message'] = 'Invalid returned value by method: '. $class_name .'::'. $queue['target'][1];
			}
			//$result['html'] = print_r($json, true) . $result['html'];
		}
		header('Content-Type: application/json');
		echo json_encode($result);
		//$this->view->render();
    }
    protected function getRoutes() {
        $routes[] = ['POST|GET', '/[a:type]/text/edit/[POST:json]?', 'Text::edit', 'Text::edit']; 
        $routes[] = ['POST|GET', '/[a:type]/text/update/[POST:json]?', 'Text::update', 'Text::update']; 
        $routes[] = ['POST|GET', '/[a:type]/text/render/[POST:json]?', 'Text::render', 'Text::render']; 

        $routes[] = ['POST|GET', '/[a:type]/ism/edit/[POST:json]?', 'Ism::edit', 'Ism::edit']; 
        $routes[] = ['POST|GET', '/[a:type]/ism/update/[POST:json]?', 'Ism::update', 'Ism::update']; 
        $routes[] = ['POST|GET', '/[a:type]/ism/render/[POST:json]?', 'Ism::render', 'Ism::render']; 

        $routes[] = ['POST|GET', '/[a:type]/blog/config/[POST:json]', 'Blog::config', 'Blog::config']; 
        $routes[] = ['POST|GET', '/[a:type]/blog/main/[POST:json]?', 'Blog::main', 'Blog::main']; 
        $routes[] = ['POST|GET', '/[a:type]/blog/edit/[POST:json]?', 'Blog::edit', 'Blog::edit']; 
        $routes[] = ['POST|GET', '/[a:type]/blog/update/[POST:json]?', 'Blog::update', 'Blog::update']; 
        $routes[] = ['POST|GET', '/[a:type]/blog/render/[POST:json]?', 'Blog::render', 'Blog::render']; 
        $routes[] = ['POST|GET', '/[a:type]/blog/renderCollection/[POST:json]?', 'Blog::renderCollection', 'Blog::renderCollection']; 
        $routes[] = ['GET',	'/[*:slug]?.[html|json:format]?', 'Blog::edit', 'Blog::edit2'];
        return $routes;
    }    
}
new WidgetProxy();
?>