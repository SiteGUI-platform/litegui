<?php
namespace LiteGUI;

class View{
	public $html = false;
   public $server2server = false;
	protected $debug = false;
	protected $blocks = [
		"main" => [
			"Status" => [
				"code" => 501, //default is Not Implemented 
				"api"  => [
					"status" => [
						"result" => "Not Implemented"
					]
				]
			]
		]
	];
	protected $config;
	protected $template;
	protected $layout;

	public function __construct($config){
		$this->config = $config;
	}

	public function assign($key, $value){
		$this->blocks['main']['internal']['api'][$key] = $value;
	}
	public function append($key, $value, $merge = false){
		if ($key == 'html' OR $key == 'links'){
			$this->blocks['main']['internal'][$key] = array_merge( $this->blocks['main']['internal'][$key]??[], $value??[] );
		} else {
			$this->blocks['main']['internal']['api'][$key] = array_merge( $this->blocks['main']['internal']['api'][$key]??[], $value??[] );
		}
	}
	public function setTemplate($template){
		$this->template = $template;
	}
	public function setLayout($layout){
		$this->layout = $layout;
	}	

	public function setDebug($true_false){
		$this->debug = ($true_false === TRUE)? TRUE : FALSE;
	}

	public function setStatus($status){
		$block = $this->getBlock('main', 'Status');

		if ( ! empty($status['html'])) {
			$block['html'] = array_merge( $block['html']??[], $status['html']);
		}
		if ( ! empty($status['api_endpoint'])) {
			$block['api_endpoint'] = 1; //only status has this property
		}
		if ( ! empty($status['template']['file'])) {
			$block['template']['file'] = $status['template']['file'];
		}
		if ( ! empty($status['message'])) {
			if ( is_array($status['message']) ){
				$block['api']['status']['message'] = array_merge($block['api']['status']['message']??[], $status['message']);
			} else {
				$block['api']['status']['message'][ ] = $status['message'];
			}	
		}
		if ($block['api']['status']['result'] != 'error' AND !empty($status['result']) ) { //once error, always error
			$block['api']['status']['result'] = $status['result'];
			if ( !empty($status['code']) OR $status['result'] == 'error') {
				$block['code'] = $status['code']??200; //Use code 200 and let client handle the error using the status
			}	
		} 
		$this->addBlock('main', $block, 'Status');				
	}	

	public function addBlock($location, $block, $name = '', $prepend = false){
		if ( ! empty($block) ){
			if ( ! empty($prepend) ) {
				$this->blocks[ $location ] = array_reverse($this->blocks[ $location ]);
				$this->blocks[ $location ][ $name ] = $block;
				$this->blocks[ $location ] = array_reverse($this->blocks[ $location ]);	
			} else {
				$this->blocks[ $location ][ $name ] = $block;
			}		
		} 
	}
	public function getBlock($location, $name){
		return $this->blocks[ $location ][ $name ]??null;
	}			

	public function render(){
		$links = [];
		//change default Not Implemented to successful Implemented if no error and there is other message/block beside Status
		if ($this->blocks['main']['Status']['api']['status']['result'] != 'error' AND 
			$this->blocks['main']['Status']['code'] == 501 AND (
				count($this->blocks) > 1 OR 
				count($this->blocks['main']) > 1 OR 
				array_key_exists('message', $this->blocks['main']['Status']['api']['status']) 
			) 
		){
			$this->blocks['main']['Status']['code'] = 200;
			if ($this->blocks['main']['Status']['api']['status']['result'] == 'Not Implemented'){
				$this->blocks['main']['Status']['api']['status']['result'] = 'success';
			}	
      }

      if ( $this->server2server ){//return json containing blocks instead of just api/html vars
			$output = $this->blocks;
		} else {
   		foreach ($this->blocks AS $section => $blocks){
	   		foreach ($blocks AS $name => $block){
		   		//should not use array_merge because we want to preserve key 'status' 
			   	$output = array_merge( $output??[], $block['api']??[] );
				   if (!empty($_REQUEST['html'])) { //with html
					   $output['links'] = array_merge( $output['links']??[], $block['links']??[] );
   					$output['html'] = array_merge( $output['html']??[], $block['html']??[] );
	   				if ( str_starts_with($name, 'Widget::') AND str_ends_with($name, '::render') ){ //widget helper => show section and vars
		   				$w = [
			   				'type' => substr($name, 8, strrpos($name, "-") - 8)
				   		]; //reset
					   	if ( !empty($block['output']) ){
						   	$w['output'] = $block['output'];
   						}
	   					if ( !empty($block['api']) ){
		   					$w['vars'] = array_keys($block['api']);
			   			}	
				   		$widgets[ $section ][ ] = $w;
					   }
   				}	
	   			if (!empty($block['api_endpoint'])){ //has one foreach before, need to re-evaluate for nested foreachs
		   			break;
			   	}
   			}
	   	}	
		   if ( !empty($widgets) ){
			   $output['__widget_helper'] = $widgets;	
   		}
      }
		//header('Access-Control-Allow-Origin: *'); //might be needed - needs evaluation
		http_response_code($this->blocks['main']['Status']['code']);
		header('Content-Type: application/json; charset=utf-8'); // required to prevent XSS included in json data e.g: <img onerror=alert(1)>
		if (!empty($_REQUEST['callback'])) { //jsonp
			echo $_REQUEST['callback'] .'('. json_encode($output) .')';
		} elseif (!empty($_REQUEST['p'])) { //pretty print
			print_r($output);
		} else {
			echo json_encode($output);
		}	
	}				
}
?>