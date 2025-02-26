<?php
namespace SiteGUI\Widget;

class Editor {
	protected $site_config;
	protected $view;

	public function __construct($site_config, $view){
		$this->site_config = $site_config['site'];
		$this->view	= $view;
	}

	public function edit($widget) {
		$response['result'] = 'success';
		//$response['block']['api']['text'] = $widget['data']??null; //store any processed data here to use with the template below, original widget is still available thru $api.widget
		if ('wysiwyg' == ($_REQUEST['frame']??null) ){
			$response['block']['template']['file'] = 'page_wysiwyg'; //can be in protected/app/ or admin template folder
			$response['block']['template']['layout'] = 'blank'; //can be in protected/app/ or admin template folder
			$response['block']['system']['hide_editor_toolbar'] = true;
			$response['block']['system']['visual_editor'] = true;
		} else {
			$response['block']['html']['sidebar'] = 1;
			$response['block']['template']['file'] = 'widget_editor_edit';
		}	
		return $response;
	}

	public function update($widget){
		if(!empty($widget['data'])) { //$widget['data'] contains multilingual string
			$response['result'] = 'success';
			$response['data']   = $widget['data']; 
			$response['cache']  = $widget['data']; //always use cache for text
			$response['expire'] = 2147483647; //max int 			
		} else {
			$response['result']  = 'error';
			$response['message'] = 'Invalid widget data';
			//print_r($widget, true);			
		}
		return $response;
	}

	public function render($widget){
		if(!empty($widget['data'])) { //$widget['data'] contains ['json'] & ['html']
			$response['result'] = 'success';
			$response['output'] = html_entity_decode($this->getRightLanguage($widget['data']), ENT_QUOTES);
		} else {
			$response['result']  = 'error';
			$response['message'] = 'Invalid widget data';
			//print_r($widget, true);			
		}
		return $response;
	}

	protected function getRightLanguage($data) {
		if ($data) {
			$c = $this->site_config;
			$language = strtolower( $c['locale']??$c['language'] );
			if (isset($data[$language])) { //use isset just in case the var is empty
				return $data[$language];
			} elseif (isset($data['en'])) {
				return $data['en'];
			} elseif (isset($data[0])) {
				return $data[0];
			} else {
				return 'Error: Cannot retrieve translated string';
			}
		}
		return '';		
	}	
}	
?>