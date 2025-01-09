<?php
namespace LiteGUI;

class Router extends \AltoRouter {
	protected $match = [];
	protected $queue = [];
	/**
	* Create router in one call from config.
	*
	* @param array $routes
	* @param string $basePath
	* @param array $matchTypes
	*/
	public function __construct( $routes = [], $basePath = '', $matchTypes = [] ) {
		parent :: __construct($routes, $basePath, $matchTypes);
	}

	public function match($requestUrl = null, $requestMethod = null) {

		$params = [];
		$match = false;

		// set Request Url if it isn't passed as parameter
		if($requestUrl === null) {
			$requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
		}

		// strip base path from request url
		$requestUrl = substr($requestUrl, strlen($this->basePath));

		// Strip query string (?a=b) from Request Url
		if (($strpos = strpos($requestUrl, '?')) !== false) {
			$requestUrl = substr($requestUrl, 0, $strpos);
		}
		// Strip trailing / from Request URL, we also need to strip it from compiled route as well [Nam]
		$requestUrl	= rtrim($requestUrl, "/");
		// set Request Method if it isn't passed as a parameter
		if($requestMethod === null) {
			$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		}

		// Force request_order to be GP
		// http://www.mail-archive.com/internals@lists.php.net/msg33119.html
		$_REQUEST = array_merge($_GET, $_POST);

		foreach($this->routes as $handler) {
			list($method, $_route, $target, $name) = $handler;

			$methods = explode('|', $method);
			$method_match = false;

			// Check if request method matches. If not, abandon early. (CHEAP)
			foreach($methods as $method) {
				if (strcasecmp($requestMethod, $method) === 0) {
					$method_match = true;
					break;
				}
			}

			// Method did not match, continue to next route.
			if(!$method_match) continue;

			// Check for a wildcard (matches all)
			if ($_route === '*') {
				$match = true;
			} elseif (isset($_route[0]) && $_route[0] === '@') {
				$pattern = '`' . substr($_route, 1) . '`u';
				$match = preg_match($pattern, $requestUrl, $params);
			} else {
				$route = null;
				$regex = false;
				$j = 0;
				$n = isset($_route[0]) ? $_route[0] : null;
				$i = 0;

				// Find the longest non-regex substring and match it against the URI
				while (true) {
					if (!isset($_route[$i])) {
						break;
					} elseif (false === $regex) {
						$c = $n;
						$regex = $c === '[' || $c === '(' || $c === '.';
							if (false === $regex && false !== isset($_route[$i+1])) {
								$n = $_route[$i + 1];
								$regex = $n === '?' || $n === '+' || $n === '*' || $n === '{';
							}
							if (false === $regex && $c !== '/' && (!isset($requestUrl[$j]) || $c !== $requestUrl[$j])) {
								continue 2;
							}
							$j++;
						}
						$route .= $_route[$i++];
					}

					$regex = $this->compileRoute($route);
					$match = preg_match($regex, $requestUrl, $params);
				}

				if(($match == true || $match > 0)) {
					// Handling $_POST type [Nam]				
					if (preg_match_all('@\[POST:([^:\]]*+)\]@', $route, $matches, PREG_SET_ORDER)) {
						foreach($matches as $m) {
							$key = trim($m[1]);
							if (!empty($key)) {
								$params[$key] = $_POST[$key]??null;
							}
						}	
					}

					if($params) {
						foreach($params as $key => $value) {
							if(is_numeric($key)) unset($params[$key]);
						}
					}
				
					$this->match = [
						'target' => $target,
						'params' => $params,
						'name' => $name
					];
					return $this->match;
				}
			}
			return false;
		}

	/**
	* Compile the regex for a given route (EXPENSIVE)
	*/
	private function compileRoute($route) {
		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {

			$matchTypes = $this->matchTypes;
			foreach($matches as $match) {
				list($block, $pre, $type, $param, $optional) = $match;
				if (isset($matchTypes[$type])) {
					$type = $matchTypes[$type];
				}
				if ($pre === '.') {
					$pre = '\.';
				}

				//Older versions of PCRE require the 'P' in (?P<named>)
				$pattern = '(?:'
							. ($pre !== '' ? $pre : null)
							. '('
							. ($param !== '' ? "?P<$param>" : null)
							. $type
							. '))'
							. ($optional !== '' ? '?' : null);

				$route = str_replace($block, $pattern, $route);
			}

		}
		// Handling trailing slash [Nam]	
		$route = rtrim($route, "/");
		return "`^$route$`u";
	}

	public function getMatch()
	{
		return $this->match;
	}

	public function registerQueue($actions = [], $prepend = false) {
		// queue is processed as First In First Out unless $prepend is set to jump to the first in queue
		// in that case reverse $action so that the first in $action will also be the fist in queue
		/* Sample queue
		$queue['Collection::getCollectionsByPageId'] = array(
			"target" => optional, use array key if not defined, can be a callable closure
			"params" => array($id,
							  'NewApp::newMethod' => array('params' => array (1, 4, 9)),
							  $paid
						)
			"name" => optional, use array key if not defined
		);		
		 */
		if ($prepend === 'prepend' AND count($actions) > 1) { //first element should be prepended last to queue 
			$actions = array_reverse($actions);
		}
		foreach ( ($actions??[]) AS $name => $queue) { //$queue could be defined fully or just name => params (short hand)
			try {
				if (!empty($queue['target']) OR substr_count($name, '::') === 1) { //either target defined or $name must be "Class::action"
					if (empty($queue['target'])) { //$name is "Class::action" 
						if (!empty($queue['params'])) {
							$queue = ['target' => $name] + $queue; //make 'target' the first
						} else {  // short hand	
							$queue = ['target' => $name,
									  'params' => $queue,
									 ]; 				 
						} 
					}

					if (empty($queue['name'])) {	 
						$queue['name'] = $name;
					}
						
					if ($prepend === 'prepend') {
						array_unshift($this->queue, $queue); //add to the beginning of the queue
					} else {
						$this->queue[] = $queue;						
					}
				} else {
					throw new \Exception('Queue action "'. $name .'" not defined', 1);				
				}
			} catch ( \Exception $e ) {
				echo $e->getMessage();	
			}
		}	
	}

	public function pollQueue()
	{
		return array_shift($this->queue);
	}
	public function emptyQueue()
	{
		$this->queue = [];
	}

	public function getRoutes()
	{
		return $this->namedRoutes;
	}
}