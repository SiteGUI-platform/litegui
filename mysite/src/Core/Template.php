<?php
namespace SiteGUI\Core;

class Template {
	use Traits\Application { generateRoutes as trait_generateRoutes; }

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user); //construct Application
		if (!empty($this->config['system']['base_dir'])) {
			$this->path = $this->config['system']['base_dir'] .'/resources/public/templates/site/'. ($this->config['site']['id']??'');
		} else {
			echo "Template directory is not defined!";
		}
		$this->requirements['CREATE'] 	= "Page::design";
		$this->requirements['PUBLISH'] 	= "Page::publish";	
	}
	
	/**
	* list all templates 
	* @return none
	*/
	public function main($template = '') {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('list');
		} else {
			if ( !empty($_REQUEST['searchPhrase']) ){
				$pattern = '*'. $this->sanitizeFileName($_REQUEST['searchPhrase']) .'*';
			} else {
				$pattern = '*';
			}	
			if ($template) { //list specific template's files + layouts
				$_layouts = @glob($this->path .'/'. $template .'/layouts/'. $pattern .'.tpl');
				$_files = @glob($this->path .'/'. $template .'/'. $pattern .'.tpl'); 
			} else { //list template names only
				$_layouts = [];
				$_files = glob($this->path .'/'. $pattern, GLOB_ONLYDIR); 
			}	
			$block['api']['total'] = count($_files) + count($_layouts);					
					
			if ( $block['api']['total'] ){
				if ( !empty($_REQUEST['current']) OR $block['api']['total'] > 30 ){ //pagination
					$block['api']['current'] = empty($_REQUEST['current'])? 1 : intval($_REQUEST['current']);
					if ( !empty($_REQUEST['rowCount']) ){
						$block['api']['rowCount'] = intval($_REQUEST['rowCount']);
					} else {
						$block['api']['rowCount'] = 30;
					}
					if ( !empty($_REQUEST['sort']) AND current($_REQUEST['sort']) == 'desc'){
						$sort['order'] = 'desc';
						$_layouts = array_reverse($_layouts);
						$_files = array_reverse($_files);
					}

					if ( !empty($_REQUEST['current']) OR !$this->view->html ){ //api or specified current 
						if ($template) { //has $_layouts 
							if ($sort['order'] == 'desc') { //layout at end
								$_gap = $block['api']['current'] * $block['api']['rowCount'] - count($_files); 
								if ( $_gap <= 0 ){ //pagination within $_files, $_layouts wont be used
									$_files = array_slice($_files, ($block['api']['current'] - 1) * $block['api']['rowCount'], $block['api']['rowCount'], true);
									$_layouts = [];
								} elseif ( $_gap > $block['api']['rowCount'] ){ //offset in $_layouts
									$_files = [];
									$_layouts = array_slice($_layouts, $_gap - $block['api']['rowCount'], $block['api']['rowCount'], true);
								} else {
									$_files = array_slice($_files, ($block['api']['current'] - 1) * $block['api']['rowCount'], $block['api']['rowCount'] - $_gap, true);
									$_layouts = array_slice($_layouts, 0, $_gap, true);
								}
							} else { //layout at the beginning
								$_gap = $block['api']['current'] * $block['api']['rowCount'] - count($_layouts); 
								if ( $_gap <= 0 ){ //pagination within $_layouts, $_files wont be used
									$_files = [];
									$_layouts = array_slice($_layouts, ($block['api']['current'] - 1) * $block['api']['rowCount'], $block['api']['rowCount'], true);
								} elseif ( $_gap > $block['api']['rowCount'] ){ //offset in $_files
									$_files = array_slice($_files, $_gap - $block['api']['rowCount'], $block['api']['rowCount'], true);
									$_layouts = [];
								} else {
									$_files = array_slice($_files, 0, $_gap, true);
									$_layouts = array_slice($_layouts, ($block['api']['current'] - 1) * $block['api']['rowCount'], $block['api']['rowCount'] - $_gap, true);
								}
							}
						} else {
							$_files = array_slice($_files, ($block['api']['current'] - 1) * $block['api']['rowCount'], $block['api']['rowCount'], true);
						}
					} else {
						$_layouts = $_files = []; //rows will be provided via api for web access when there are many rows
					}	
				} //else show all files because not many
				if ($template) { 
					$block['api']['rows'] = $items = [];
					foreach ($_files as $f) {
						$item['id'] = $template ."/". substr(basename($f), 0, -4); 
						$item['name'] = basename($f);
						$item['updated'] = filemtime($f); 	
						$block['api']['rows'][] = $item;				
					}
					foreach ($_layouts as $f) {
						$item['id'] = $template ."/layouts/". substr(basename($f), 0, -4); 
						$item['name'] = 'layouts/'. basename($f);
						$item['updated'] = filemtime($f); 	
						$items[] = $item;					
					}
					if ( isset($sort['order']) AND $sort['order'] == 'desc' ){ //layout at end
						$block['api']['rows'] = array_merge( $block['api']['rows'], $items); //append by merge
					} else {
						$block['api']['rows'] = array_merge( $items, $block['api']['rows']); //prepend at the beginning
					}						
				} else { //list template names only
					foreach ($_files as $folder) {
						$item['id'] = $item['name'] = basename($folder); 
						$item['updated'] = filemtime($folder); 	
						$block['api']['rows'][] = $item;				
					}	
				}

				if ($this->view->html){				
					$block['html']['table_header'] = [
						'id' => $this->trans('ID'),
						'name' => $this->trans('Name'),
						'updated' => $this->trans('Modified'),
						'action' => $this->trans('Action')
					];
					$block['html']['column_type'] = ['updated' => 'date'];
					if ($template) {
						$block['html']['app_menu'][ ] = ['name' => $template];
						$links['api'] = $this->slug('Template::main', ["template" => $template] );
						$links['edit'] = $this->slug('Template::action', ["action" => "edit"] );
						$links['edit2'] = '/'. $template;
					} else { //list template's files
						$links['api'] = $this->slug('Template::main');
						$links['edit'] = $this->slug('Template::main');
						$links['edit2'] = '/edit';
					}
					$links['copy'] = $this->slug('Template::action', ["action" => "copy"] );
					$links['delete'] = $this->slug('Template::action', ["action" => "delete"] );
					$block['links'] = $links;	
					$block['template']['file'] = "datatable";		
				}
			} else {
				$status['result'] = "error";
				$status['message'][] = $this->trans('You have not created any :type', [
					'type' => 'Template'. ($template)? ' File' : ''
				]);

				if ($this->view->html){				
					$status['html']['message_type'] = 'info';
					$status['html']['message_title'] = $this->trans('Information');	
					$link = $this->slug($this->class .'::action', ["action" => "edit"] );
			        $status['message'][ $link ] = $this->trans('Click here to create a new :type', ['type' => $this->class] );
				}
			}	

			!empty($status) && $this->view->setStatus($status);							
			$this->view->addBlock('main', $block, 'Template::main');
		}						
	}

	public function create($template, $file_name = NULL) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('create');
		} else {
			$template = $this->sanitizeFileName(basename($template));
			if (!empty($template)) {
				if (!empty($file_name)) { //create a template file
					$file_name = $this->sanitizeFileName(basename($file_name));
					$target = $this->path .'/'. $template .'/'. $file_name .'.tpl';
					if (@touch($target)){
						@chmod($target, 0664);
						return $template .'/'. $file_name;	
					}
				} else { //create a template	
					$target = $this->path .'/'. $template;					
					if (@mkdir($target, 0775, true)) {
						//@chmod($target, 0775);
						return $template;		
					}
				}	
			} 
		}	
		return false;		
	}

	public function read() {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('read');
		} else {
			$data = [];
			return $data;
		}	
	}

	public function update($template) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('update');
			return false;
		} 

		if (!empty($template['id'])) {
			$template = $this->prepareData($template);
			$file_name = $this->path .'/'. $template['name'] .'/'. $template['file_name'] .'.tpl';

			if (@is_writable($file_name) AND @file_put_contents($file_name, htmlspecialchars_decode($template['content'], ENT_QUOTES)) !== false) {
				$status['result'] = 'success';
				$status['message'][] = $this->trans(':item updated successfully', ['item' => 'Template']);		
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not updated', ['item' => 'Template']);			
			}
		} elseif (!empty($template['file_name']) AND !empty($template['name'])) {
			if ($template['id'] = $this->create($template['name'], $template['file_name'])) { // create a new template file
				$status['result'] = 'success';
				$status['message'][] = $this->trans(':item created successfully', ['item' => 'Template file']);						
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not created', ['item' => 'Template file']);						
			}
		} elseif (!empty($template['new_name']) AND $new = $this->sanitizeFileName(basename($template['new_name'])) ){
			if ( !empty($template['clone']) ){
				$source = $this->path .'/../../global/'. $this->config['system']['default_template'];
				$new 	= $this->path .'/'. $new;

				if ( $this->copyFolder($source, $new) ){
					$template['id'] = $new;
					$status['message'][] = $this->trans(':item copied successfully', ['item' => 'Template']);
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not copied', ['item' => 'Template']);					
				}
			} elseif ($template['id'] = $this->create($template['new_name'])) { // create a new template
				$status['message'][] = $this->trans(':item created successfully', ['item' => 'Template']);						
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not created', ['item' => 'Template']);						
			}			
		} else {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('Template is not specified or readable!');			
		}

		!empty($status) && $this->view->setStatus($status);							
		$this->logActivity( implode('. ', $status['message']), $this->class .'.'. $template['name'] .'/'. $template['file_name'], 1);

		if ($this->view->html AND !empty($template['id']) ){				
			$this->edit($template['id']);
		}	
	}


	function delete($id) {
		if ( ! $this->user->has($this->requirements['PUBLISH'])){	//Template cannot be deleted by unauthorized admin		
			$status['result'] = 'error';
			$status['message'][] = $this->trans('You cannot delete a template');
			$this->denyAccess('delete');
		} else {			
			$template = $this->sanitizeFileName(basename(strtok($id, '/')));
			$file_name 	= $this->sanitizeFileName(basename(strtok('/')));
			if ($file_name === 'layouts') {
				$file_name .= '/'. $this->sanitizeFileName(basename(strtok('/')));
			}

			if (!empty($file_name)) { //delete a template file
				$result = @unlink($this->path .'/'. $template .'/'. $file_name .'.tpl');
			} elseif (!empty($template)) { //delete the every files under this template 
				$result = self::deleteFolder($this->path .'/'. $template);
			}	

			if ($result) {
				$status['result'] = 'success';
				$status['message'][] = $this->trans(':item deleted successfully', ['item' => 'Template']);				
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not deleted', ['item' => 'Template']);				
			}	
		}

		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);	

		$this->logActivity( implode('. ', $status['message']), $this->class .'.'. $template .'/'. $file_name, 1);

		if ($this->view->html){	
			if (!empty($file_name)) { 
				$this->edit($template);
			} else {				
				$this->main();
			}	
		}								
	}


	/**
	 * print out edit form
	 * @param  integer $id [description]
	 * @return [type]           [description]
	 */
	public function edit($id = ''){ //$id is template/file_name or template/layouts/file_name (no extension)
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess($id? 'edit' : 'create');
		} else {
			$template = $this->sanitizeFileName(basename(strtok($id, '/')));
			$file_name 	= $this->sanitizeFileName(basename(strtok('/')));
			if ($file_name === 'layouts') {
				$file_name .= '/'. $this->sanitizeFileName(basename(strtok('/')));
			}

			//template name specified, edit file_name if exist or edit template
			if (!empty($template) AND @is_dir($this->path .'/'. $template)) { 
				//file_name given and exist -> edit a template file
				if (!empty($file_name) AND @is_readable($this->path .'/'. $template .'/'. $file_name .'.tpl')) { 
					$api['id'] = $id;
					$api['file_name'] = $file_name .'.tpl'; 	
					$api['content'] = @file_get_contents($this->path .'/'. $template .'/'. $file_name .'.tpl');
				} elseif ( ! $this->view->html ){				
					$status['result'] = "error";
					$status['message'][] = $this->trans('You have not created any :type', ['type' => 'Template file']);
				}

				$api['name'] = $template;
				$block['api']['template'] = $api;	
			} else { //no id given or template not exist -> show forms for creating template and file
				if ($this->view->html){	
					$this->getSiteTemplates('localOnly');
				} else {
					$status['result'] = "error";
					$status['message'][] = $this->trans('Template is not specified or readable!');
				}
			}

			if ($this->view->html){				
				$block['template']['file'] = "template_edit"; 	
				$links['update'] = $this->slug($this->class .'::update');
				$links['main']   = $this->slug($this->class .'::main');
				//$links['copy']   = $this->slug('Copy::action', ['action' => 'edit']);
				if ( !empty($this->config['system']['sgframe']) ){
					$links['update'] .= '?sgframe=1';
					$links['main']   .= '?sgframe=1';
				}

				$block['links'] = $links;
			}		
			!empty($status) && $this->view->setStatus($status);							
			$this->view->addBlock('main', $block, 'Template::edit');				
		}					
	}

	public function getSiteTemplates($localOnly = false) {
		$templates = [];
		if ( ! $localOnly) {
			//bought global templates
			$_namespace = str_replace(['Core', '\\'], ['Template', '\\\\'], __NAMESPACE__); 
			try {
				//throw error when creating a new site as table config does not exist
				$templates = $this->db->table($this->site_prefix ."_config")
					->where('type', 'activation')
					->where('name', 'LIKE', $_namespace .'%') //4 \ is required to escape twice, php then mysql
					->selectRaw('SUBSTRING(name, '. strlen($_namespace .'\\') .') AS template')
					->pluck('template')
					->all();
			} catch (\Exception $e) {

			}		
		}		
		foreach (glob($this->path .'/*', GLOB_ONLYDIR) as $folder) {
			$templates[] = basename($folder); 	
		}

		$block['html']['templates'] = $templates;
		$this->view->addBlock('main', $block, 'Template::getSiteTemplates');
	}

	public function getTemplateDir($template = '') {
        $cf = $this->config;
        $base_dir = $cf['system']['base_dir'] .'/resources/public/templates';
       	$template = empty($template)? basename($cf['site']['template']) : basename($template);	
        $template_dir = $base_dir .'/site/'. $cf['site']['id'] .'/'. $template;
    	
    	if (!empty($template)) {
            if ( ! @is_dir($template_dir) ){
        	    //check if this global template is activated for this site
                $activated = $this->db
                    ->table($this->site_prefix ."_config")
                    ->where('type', 'activation')
                    ->where('name', str_replace('Core', 'Template', __NAMESPACE__) .'\\'. $template)
                    ->value('name');
                if ($activated) {
                    $template_dir = $base_dir .'/global/'. $template;
                }             
            }   
        } elseif (!empty($cf['system']['default_template'])) { //site with no template set
            $template_dir = $base_dir .'/global/'. $cf['system']['default_template'];
        } 
        return $template_dir;
	}

	public function getSnippets($template = '') {
		//template snippets
		$template_dir = $this->getTemplateDir($template);
        if (is_file($template_dir .'/snippets/index.json')) {
        	//id, name, icon, category/type
        	$snippets = json_decode(@file_get_contents($template_dir .'/snippets/index.json')??'', true);
        } else {
        	$snippets = [];
        }
		$block['api']['snippets']['template'] = $snippets;
        if (is_file($template_dir .'/snippets/index.html')) {
        	$block['api']['snippets']['resources'] = @file_get_contents($template_dir .'/snippets/index.html');
        } 
		//admin template snippets
		$template_dir = $this->config['system']['base_dir'] .'/resources/public/templates/admin/'. $this->config['system']['template'];
        if (is_file($template_dir .'/snippets/index.json')) {
        	//id, name, icon, category/type
        	$snippets = json_decode(@file_get_contents($template_dir .'/snippets/index.json')??'', true);
        } else {
        	$snippets = [];
        }
		$block['api']['snippets']['system'] = $snippets;
		$this->view->addBlock('main', $block, 'Template::getSnippets');
	}

	public function snippet($id) { //system__.template.snippet & template.snippet or .snippet for default template
		if (strpos($id, 'system__.') !== false) { //system__.template.snippet
			$template = strtok(substr($id, 9), '.');
  			$id = strtok('.');
			//admin template snippets
			$template_dir = $this->config['system']['base_dir'] .'/resources/public/templates/admin/'. basename($template);
		} else {
			if (strpos($id, '.') > 0) { //template.snippet
				$template = strtok($id, '.');
	  			$id = strtok('.');			
			} else { //.snippet
				$template = '';
				$id = strtok($id, '.');
			}
			$template_dir = $this->getTemplateDir($template);
		}		

		$snippet['output'] = @file_get_contents($template_dir .'/snippets/'. basename($id) .'.html');

		if ($snippet['output']) {
			$block['api']['snippet'] = $snippet;					

			if ($this->view->html){				
				$this->view->setLayout('blank');
				$block['output'] = $snippet['output']; //direct output, no template needed					
			}

			$this->view->addBlock('main', $block, 'Template::snippet');
		} else {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('No such :item', ['item' => 'snippet']);
		}

		//$status['api_endpoint'] = 1;
		!empty($status) && $this->view->setStatus($status);			
	}
	public function copy($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('copy');
		} else {
			$template = $this->sanitizeFileName(basename(strtok($id, '/')));
			$file_name 	= $this->sanitizeFileName(basename(strtok('/')));
			if ($file_name === 'layouts') {
				$file_name .= '/'. $this->sanitizeFileName(basename(strtok('/')));
			}
			
			if (!empty($file_name)) { //copy a template file
				$source = $this->path .'/'. $template .'/'. $file_name .'.tpl';
				$new 	= $this->path .'/'. $template .'/'. $file_name .'-copy.tpl';
			} elseif (!empty($template)) {
				$source = $this->path .'/'. $template;
				$new 	= $this->path .'/'. $template .'-copy';
			}

			if ($source AND $new AND $this->copyFolder($source, $new)) {
				$new_id = $id .'-copy';
				$status['result'] = 'success';
				$status['message'][] = $this->trans(':item copied successfully', ['item' => 'Template']);
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not copied', ['item' => 'Template']);					
			}

			$this->view->setStatus($status);							
			$this->logActivity( implode('. ', $status['message']), $this->class .'.'. $id, 1);

			if ($this->view->html AND !empty($new_id)) {				
				$this->edit($new_id);
			}				
		}		
	}	

	protected function prepareData($data) {	
		if (empty($data['id'])) { //new template
			$template['name'] = $this->config['site']['template'];
			if ($data['file_name']) {
				$template['file_name'] = $this->sanitizeFileName(basename($data['file_name']));
			} else {
				$template['file_name'] = "example-". mt_rand(1,1000);
			}		
		} else { //update template			
			$template['name'] = $this->sanitizeFileName(basename(strtok($data['id'], '/')));
			$template['file_name'] = $this->sanitizeFileName(basename(strtok('/')));
			if ($template['file_name'] === 'layouts') {
				$template['file_name'] .= '/'. $this->sanitizeFileName(basename(strtok('/')));
			}		
		}

		$template["id"] = $template['name'] ."/". $template['file_name'];
		$template['content'] = $data['content'];		

		return $template;		
	}

	public function generateRoutes($extra = []) {
		$extra['action']  = ['GET|POST', '/[i:site_id]/template/[edit|copy:action]/[*:id]?.[json:format]?'];	
		$extra['snippet'] = ['GET|POST', '/[i:site_id]/template/snippet.[json:format]?[POST:id]?'];	
		$extra['main'] = ['GET|POST', '/[i:site_id]/template/[*:template]?.[json:format]?'];	

		return $this->trait_generateRoutes($extra);
	}
}	