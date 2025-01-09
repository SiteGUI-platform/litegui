<?php
namespace SiteGUI\Core;

class Layout {
	use Traits\Application { generateRoutes as trait_generateRoutes; }

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user); //construct Application
		if (!empty($this->config['system']['base_dir'])) {
			$this->path = $this->config['system']['base_dir'] .'/resources/public/templates/site/'. $this->config['site']['id'];
		} else {
			echo "Template directory is not defined!";
		}
		$this->table = $this->site_prefix ."_page";
		$this->requirements['CREATE'] 	= "Page::design";
		$this->requirements['PUBLISH'] 	= "Page::publish";	
	}
	
	/**
	* list all layouts 
	* @return none
	*/
	public function main() {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('list layout');
		} else {
			if ( !empty($_REQUEST['searchPhrase']) ){
				$pattern = '*'. $this->sanitizeFileName($_REQUEST['searchPhrase']) .'*';
			} else {
				$pattern = '*';
			}					
			$_layouts = @glob($this->path .'/*/layouts/'. $pattern .'.tpl');
			$block['api']['total'] = count($_layouts);						

			if ( $block['api']['total'] ){				
				if ( !empty($_REQUEST['current']) OR $block['api']['total'] > 30 ){ //pagination
					$block['api']['current'] = empty($_REQUEST['current'])? 1 : intval($_REQUEST['current']);
					if ( !empty($_REQUEST['rowCount']) ){
						$block['api']['rowCount'] = intval($_REQUEST['rowCount']);
					} else {
						$block['api']['rowCount'] = 30;
					}
					if ( !empty($_REQUEST['sort']) AND current($_REQUEST['sort']) == 'desc'){
						$_layouts = array_reverse($_layouts);
					}

					if ( !empty($_REQUEST['current']) OR !$this->view->html ){ //api or specified current 
						$_layouts = array_slice($_layouts, ($block['api']['current'] - 1) * $block['api']['rowCount'], $block['api']['rowCount'], true);
					} else {
						$_layouts = []; //rows will be provided via api for web access when there are many rows
					}	
				} //else show all files because not many
				foreach ($_layouts as $_file) {
					$_folder = substr($_file, strlen($this->path) + 1);
					$_folder = basename(strtok($_folder, '/'));
					$_file = substr(basename($_file), 0, -4);

					$item['id'] = $_folder ."__". $_file; 
					$item['template'] = $_folder; 	
					$item['name'] = $_file; 
					$block['api']['rows'][] = $item;				
				}

				if ($this->view->html){				
					$block['html']['table_header'] = [
						'id' => $this->trans('ID'),
						'template' => $this->trans('Template'),
						'name' => $this->trans('Name'),
						'action' => $this->trans('Action'),
					];

					$links['api'] = $this->slug('Layout::main');
					$links['edit'] = $this->slug('Layout::action', ["action" => "edit"] );
					$links['copy'] = $this->slug('Layout::action', ["action" => "copy"] );
					$links['delete'] = $this->slug('Layout::action', ["action" => "delete"] );
					$block['links'] = $links;	
					$block['template']['file'] = "datatable";		
				}
			} else {
				$status['result'] = "error";
				$status['message'][] = $this->trans('You have not created any :type', ['type' => 'Layout']);
				
				if ($this->view->html){				
					$status['html']['message_type'] = 'info';
					$status['html']['message_title'] = $this->trans('Information');	
					$link = $this->slug($this->class .'::action', ["action" => "edit"] );
			        $status['message'][ $link ] = $this->trans('Click here to create a new :type', ['type' => $this->class] );
				}
			}	

			!empty($status) && $this->view->setStatus($status);							
			$this->view->addBlock('main', $block, 'Layout::main');
		}						
	}

	public function create($layout) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('create');
		} else {
			$layout = $this->prepareData($layout);
			$folder = $this->path ."/". $layout['template'] .'/layouts';
			$file = $folder .'/'. $layout['name'] .".tpl";
			if ( ! $this->user->has($this->requirements['PUBLISH']) AND @file_exists($file) ) {
				return false;
			}
			if (!is_dir($folder)) {
				@mkdir($folder, 0775, true);
			}
			if (@is_writable($folder)) { 
				// returns the number of bytes that were written to the file, or FALSE on failure. 
				// may also return a non-Boolean 0 value which evaluates to FALSE 
				$return = @file_put_contents($file, htmlspecialchars_decode($layout['content'], ENT_QUOTES));
				if ($return !== false) {
					@chmod($file, 0664);
					return $layout['id'];	
				}	
			} 
		}	
		return false;		
	}

	public function read($value, $column ="id", $admin = false) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('read');
		} else {
			$data = [];
			return $data;
		}	
	}

	public function update($layout) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('update');
			return false;
		} 

		if ( ! $this->user->has($this->requirements['PUBLISH'])) { //Existing layout can't be updated by unauthorized admin
			$this->logActivity('Layout Update Denied. Creating a new one', $this->class .'.'. $layout['name'], 1, 'Warning');
			$layout['name'] .= "-copy";
			unset($layout['id']); //force creating a new layout
		}

		if (empty($layout['id'])) {
			if ($layout['id'] = $this->create($layout)) { // create a new layout
				$status['result'] = 'success';
				$status['message'][] = $this->trans(':item created successfully', ['item' => 'Layout']);						
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not created', ['item' => 'Layout']);						
			}		
		} else {
			$layout = $this->prepareData($layout);
			$file = $this->path .'/'. $layout['template'] .'/layouts/'. $layout['name'] .'.tpl';

			if (@file_put_contents($file, htmlspecialchars_decode($layout['content'], ENT_QUOTES)) !== false) {
				$status['result'] = 'success';
				$status['message'][] = $this->trans(':item updated successfully', ['item' => 'Layout']);	
				// Remove old file - No way!!! It may be in used by other pages
				//if (!empty($layout['old_name'])) {
				//	@unlink($this->path .'/'. $layout['template'] .'/layouts/'. $layout['old_name'] .'.tpl');
				//}	
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not updated', ['item' => 'Layout']);			
			}
		}

		if ($this->view->html) {
			$status['html']['message_title'] = $this->trans('Information');
			$link = $this->slug('Layout::action', ['action' => 'edit', 'id' => $layout['id'] ]);
			$status['message'][ $link ] = $this->trans('Click here to keep editing');	
			//$this->main();//show message, otherwise edit will rediret $layout['id']);
		}	
		!empty($status) && $this->view->setStatus($status);							
		$this->logActivity( implode('. ', $status['message']), $this->class .'.'. $layout['name'], 1);
	}


	function delete($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('delete');
		} 

		if ( ! $this->user->has($this->requirements['PUBLISH'])){	//Layout cannot be deleted by unauthorized admin		
			$status['result'] = 'error';
			$status['message'][] = $this->trans('You cannot delete a layout');
		} else {			
			$template = basename(strtok($id, '__'));
			$name 	= basename(strtok('__')) .".tpl";
			$file_name = $this->path .'/'. $template .'/layouts/'. $name;
			if (@unlink($file_name)) {
				$status['result'] = 'success';
				$status['message'][] = $this->trans(':item deleted successfully', ['item' => 'Layout']);				
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not deleted', ['item' => 'Layout']);				
			}	
		}

		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);	

		$this->logActivity( implode('. ', $status['message']), $this->class .'.'. $id, 1);

		if ($this->view->html){				
			$this->main();
		}								
	}


	/**
	 * print out edit form
	 * @param  integer $id [description]
	 * @return [type]           [description]
	 */
	public function edit($id = ''){ //$id is template__layout_name which locates at template/layouts/layout_name.tpl
		// If template directory is empty, prompt to create a template first
		$templates = glob($this->path .'/*', GLOB_ONLYDIR);
		//print_r($templates);

		if (count($templates) == 0) {
			$status['result'] = "error";
			$status['message'][] = $this->trans('You have not created any :type', ['type' => 'Template']);
			
			if ($this->view->html){				
				$status['html']['message_type'] = 'info';
				$status['html']['message_title'] = $this->trans('Information');	
			}	
			$this->view->setStatus($status);

			$next_actions['Template::edit'] = [];
			$this->router->registerQueue($next_actions);
			return false;						
		}

		$links['update'] = $this->slug('Layout::update');
		$this->loadSandboxPage($this->slug('Layout::action', ["action"=> "edit"] ), $id, $links['update']);

		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess($id? 'edit' : 'create');
		} else {
			if(!empty($id)){ // load existing layout
				$layout['id'] = $id;
				$layout['template'] = basename(strtok($id, '__'));
				$layout['name'] 	= basename(strtok('__'));
				$layout['content']  = @file_get_contents($this->path .'/'. $layout['template'] .'/layouts/'. $layout['name'] .'.tpl');
			} else { // new layout
				if (!empty($this->config['site']['template'])) {
					$layout['template'] = $this->config['site']['template'];
				} else {
					$layout['template'] = basename($templates[0]); // always exists because we already check $templates earlier
				}				
				$layout['content'] = '';
			}	

			//wysiwyg frame
			if ( !empty($_REQUEST['frame']) AND $_REQUEST['frame'] == 'wysiwyg') {
				$this->view->setLayout('blank');

				$links['widget'] = $this->slug('Widget::preview');
				$links['widget'] .= '?cors='. @$this->hashify($links['widget'] .'::'. $_SESSION['token']);

				$links['snippet'] = $this->slug('Template::snippet');
				$links['snippet'] .= '?cors='. @$this->hashify($links['snippet'] .'::'. $_SESSION['token']);

				//get available widgets
				$widgets = $this->db
					->table($this->site_prefix ."_widget")
					->where('type', '<>', 'Latest')
					->where('type', '<>', 'Site::Report')
					->select('id', 'name', 'type')
					->get()->all();	
				foreach ($widgets as $widget) {
					$group[ $widget['type'] ]['name'] = $widget['type'];
					$group[ $widget['type'] ]['snippets'][] = $widget;
				}
				$block['api']['widgets'] = !empty($group)? $group : [];
				//Layout loads snippets/resources of template being edited
				$next_actions['Template::getSnippets'] = [ $layout['template'] ]; 
				// get site templates										
				$next_actions['Template::getSiteTemplates'] = [];
				$this->router->registerQueue($next_actions);
			} else { //control frame
				if ($this->view->html){
					//xss (if any) is less damage here as we are in sandbox url
					$links['editor'] = $this->config['system']['edit_url'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);	

					$block['html']['sidebar'] = 1;
				}				
			}
		
			if (!empty($id) AND ($layout['content'] === false OR trim($layout['content'])[0] != '{')) { // file does not exist
				$status['result'] = 'error';
				$status['message'][] = ($layout['content'] === false)? $this->trans('There is no such layout :name', ['name' => $layout['name'] ]) : $this->trans('The layout does not start with a block, please edit manually');							
				$this->view->setStatus($status);
			} else {
				$block['template']['file'] = "layout_edit";		
				$block['links'] = $links;
				$block['api']['layout']  = $layout;					

				$this->view->addBlock('main', $block, 'Layout::edit');	
			}
		}					
	}
	
	public function copy($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('copy');
		} else {
			$folder = $this->path .'/'. basename(strtok($id, '__')) .'/layouts';
			$file 	= basename(strtok('__'));

			if (@copy($folder .'/'. $file .'.tpl', $folder .'/'. $file .'-copy.tpl')){
				$new_id = $id .'-copy';
				@chmod($folder .'/'. $file .'-copy.tpl', 0664);
				$status['result'] = 'success';
				$status['message'][] = $this->trans(':item copied successfully', ['item' => 'Layout']);
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not copied', ['item' => 'Layout']);					
			}

			$this->view->setStatus($status);							
			$this->logActivity( implode('. ', $status['message']), $this->class .'.'. $layout_info['name'], 1);

			if ($this->view->html AND !empty($new_id)) {				
				$this->edit($new_id);
			}				
		}		
	}	

	protected function createLayout($data, $depth = 0, $parent_type = 'row') {
		$row_opened = 0;
		$html = ''; 
		foreach ($data AS $div) {
			$closeTag = '';
			$divId = !empty($div['id'])? $this->sanitizeFileName($div['id']) : "";
			$divId = str_replace('-', '_', $divId);
			$idStr = ($divId)? " id='". $divId ."'" : '';
			$itemFunc = !empty($div['func'])? ' data-sg-func="'. $this->sanitizeFileName($div['func']) .'"' : '';
			$itemTag = !empty($div['tag'])? $this->sanitizeFileName($div['tag']) : "div";
			
			$classes = [];
			if ( !empty($div['class']) ){
				foreach (explode(" ", $div['class']) as $value) {
					if (!empty($value)){
						$classes[] = $this->sanitizeFileName($value);
					}	
				}
			}	

			//if ($div['type'] == 'content') { 
			//	$classes[] = "px-0"; //content padding => let do it with sg-block-content e.g: #block_spotlight .sg-block-content
			//} else
			if ( !empty($div['type']) AND $div['type'] == 'row' ){
				$classes[] = "row";
			}	

			if ($parent_type != 'row' AND $div['type'] != 'row' AND empty($row_opened) ){ //first non-row-child of non-row-parent
				$row_opened = 1;
				$html .= str_repeat(' ', 2*$depth) ." <div class='row'>\xA";
			} elseif ($div['type'] == 'row' AND !empty($row_opened) ){ //row-child of non-row-parent that has non-row children ($row_opened=1)
				$row_opened = 0; //close opened row now 
				$html .= str_repeat(' ', 2*$depth) ." </div>\xA";
			}
			
			if ($depth == 0) { //level 0
				if ($div['type'] != 'container') {
					$classes[] = 'container-fluid';
				}
			}	
			$classes = ($classes)? " class='". implode(" ", array_unique($classes)) ."'" : '';

			$html .= str_repeat(' ', 2*($depth + $row_opened)) ." <". $itemTag . $idStr . $classes . $itemFunc .">\xA";
			$closeTag .= str_repeat(' ', 2*($depth + $row_opened)) ." </". $itemTag .">\xA";

			if (!empty($div['content'])) {
				$div['content'] = str_replace(' style="z-index: auto;"', '', $div['content']);
				$html .= str_repeat(' ', 2*($depth + $row_opened + 1)) .' '. $div['content'] ."\xA";
			} elseif (!empty($div['children']) ){	
				$html .= $this->createLayout($div['children'], $depth + $row_opened + 1, $div['type']);
			} elseif (!empty($divId)) {
				$html .= str_repeat(' ', 2*($depth + $row_opened)) 
				      ." <div class='sg-block-content'>{block name='". $divId ."'}{\$". $divId ." nofilter}{/block}</div>\xA";
			}

			$html .= $closeTag;	
			/*} else { //level 0
				if ($div['type'] == 'container') {
					$classes[] = 'container';
				} else {
					$classes[] = 'container-fluid';
				}
				$classes = " class='". implode(" ", array_unique($classes)) ."'";
				$html .= " <div". $idStr . $classes .">\xA";					
				$closeTag .= " </div>\xA";
	
				if (!empty($div['children'])) {
					$html .= $this->createLayout($div["children"], $depth + 1, $div['type']);
				} elseif (!empty($divId)) {
					$html .= str_repeat(' ', 3*$depth) 
					      ." <div class='sg-block-content row'>{block name='". $divId ."'} {\$". $divId ."} {/block}</div>\xA";
				}
				$html .= $closeTag;
			}*/
		}
		if ($row_opened) { //if row is still opened during this foreach, close it
			$html .= str_repeat(' ', 2*$depth) ." </div>\xA";
		}
		return $html; 	
	}	
	protected function prepareData($data) {	
		if (empty($data['id'])) { //new layout
			$layout['template'] = $this->sanitizeFileName($data['template']);
		} else { //update layout			
			$layout['template'] = $this->sanitizeFileName(basename(strtok($data['id'], '__')));
			$layout['name'] = $this->sanitizeFileName(basename(strtok('__')));		
		}

		// Layout file name can be changed
		if (!empty($data['name'])) {
			//if (!empty($layout['name']) AND $data['name'] != $layout['name']) {
			//	$layout['old_name'] = $layout['name'];
			//}
			$layout['name'] = $this->sanitizeFileName($data['name']);
		} elseif (empty($layout['name'])) {
			$layout['name'] = "layout-". mt_rand(1,1000);
		}

		$layout["id"] = $layout['template'] ."__". $layout['name'];

		$data['content'] = json_decode(html_entity_decode($data['content'])??'', true);
		$layout['content'] = "{block name='block_head'}<html><head>{\$block_head nofilter}</head>{/block}\xA{block name='block_body'}\xA<body>\xA";
		$layout['content'] .= $this->createLayout($data['content'], 0); //add new line		
		$layout['content'] .= "</body>\xA{/block}\xA{block name='block_script'}{\$block_script nofilter}{/block}\xA</html>";
		
		/*echo '<pre>';
		print_r($data);
		echo '</pre>';
		echo '<textarea  style="width: 932px; height: 596px;">'. $layout['content'] .'</textarea>';*/

		return $layout;		
	}

	public function getLayouts($template = '', $system_template = false) {
		if (empty($template)) { //use default system template
			$template = $this->config['system']['base_dir'] .'/resources/public/templates/global/'. $this->config['system']['default_template'];
		} elseif ($system_template) {
			$template = $this->config['system']['base_dir'] .'/resources/public/templates/admin/'. basename($template);
		} else {	
			$template = $this->path .'/'. basename($template);
		}

		$layouts = [];
		foreach (glob($template .'/layouts/*.{tpl,jpg,gif,png}', GLOB_BRACE) as $file) {
			$name = strtok(basename($file), '.');
			if (strtok('.') == 'tpl') {
				$layouts['names'][] = $name;
			} else {	
				$layouts['thumbnails'][$name] = basename($file); 
			}	 
		}
		$block['html']['layouts'] = $layouts;
		$this->view->addBlock('main', $block, 'Layout::getLayouts');
	}
	
	public function generateRoutes($extra = []) {
		$name  = strtolower($this->class);
		$extra['action'] = ['GET|POST', '/[i:site_id]/'. $name .'/[edit|copy:action]/[*:id]?.[json:format]?']; 
		return $this->trait_generateRoutes($extra);
	}
}	