<?php
namespace LiteGUI\View;

class Smarty extends \LiteGUI\View
{
	protected $smarty;
	protected $core_blocks;
	protected $blocks = [
		"main" => [
			"Status" => [
				"code" => 200,
				"api"  => [
					"status" => [
						"result" => "success"
					]
				]
			]
		]
	];
	public function __construct($config){
		parent :: __construct($config);
		$this->html = true;
		$this->core_blocks = array('head', 'header', 'spotlight', 'left', 'right', 'top', 'bottom', 'main', 'footnote', 'footer');

		$this->smarty = new \Smarty();
		$my_security_policy = new \Smarty_Security($this->smarty);
		$my_security_policy->php_functions = null;
		//$my_security_policy->php_handling = \Smarty::PHP_REMOVE; //remove since Smarty 4
		//$my_security_policy->php_modifiers = null;//allow 'escape','count' modifier
		$my_security_policy->static_classes = null;
		$my_security_policy->allow_super_globals = false;
		$my_security_policy->allow_constants = false;
		$my_security_policy->allow_php_tag = false;
		$my_security_policy->streams = null;
		$my_security_policy->disabled_special_smarty_vars = ['template_object', 'current_dir', 'version'];
		$this->smarty->enableSecurity($my_security_policy);
		$this->smarty->caching = 0;
		$this->smarty->escape_html = true; //it wont work for all variables
		if (\PHP_VERSION_ID > 80000) { //PHP 8
			$this->smarty->muteUndefinedOrNullWarnings(); //temporary
		}
	}
	
	public function escapeHtml($switch = true){
		if (empty($switch)) {
			$this->smarty->escape_html = false;
		} else $this->smarty->escape_html = true;
	}

	public function assign($key, $value){
		$this->smarty->assign($key, $value);
	}
	public function append($key, $value, $merge = false){
		$this->smarty->append($key, $value, $merge);
	}
	public function registerPlugin($type, $name, $callback, $cacheable = false, $cache_attrs = false){
		$this->smarty->registerPlugin($type, $name, $callback, $cacheable, $cache_attrs);
	}
	public function setTemplate($template){
		$this->template = basename($template);
		$this->assign('template', $this->template);		
	}
	
	public function setTemplateDir($dir){
		if( !empty($dir) ) {
			$this->smarty->setTemplateDir($dir);
		}		
	}

	public function addTemplateDir($dir, $key = null){
		if( !empty($key) ) { //using a template from another dir e.g: plugin
			$this->smarty->addTemplateDir($dir, $key);	
		} else {
			$this->smarty->addTemplateDir($dir);
		}		
	}

	public function setCompileDir($dir){
		$this->smarty->setCompileDir($dir);	
	}

	//fetch template resource using provided data i.e: like twig render
	public function fetchLocal($template, $vars = []){
		$data = $this->smarty->createData();
		foreach ($vars as $key => $value) {
			// assign variable to data scope
			$data->assign($key, $value);
		}

		$tpl = $this->smarty->createTemplate($template, $data);

		return $tpl->fetch();
	}

	public function renderFile($file, $key = null){
		//file could be 'App/Blog/blog_page' or 'page_edit'
		if ( !empty($file) ){
			try {
				if( !empty($key) ) { //using a template from another dir e.g: plugin
					return $this->smarty->fetch("file:[". $key ."]". basename($file) .".tpl");
				} else {	
					return $this->smarty->fetch(basename($file) .".tpl");
				}	
			} catch (\Exception $e) {
				echo "Error: ". str_replace($this->smarty->getTemplateDir(), [], $e->getMessage() );
			}	
		}
	}		

	public function renderJson(){
		return parent::render();	
	}
		
	public function render(){
		$output = [];
		foreach ($this->blocks AS $position => $blocks){
			//we use uasort here as 'order' may not be set in all blocks 
			uasort($blocks, function ($item1, $item2) {
				//set 'order' to 0 if not defined otherwise negative value does not work
				if (empty($item1['order'])) $item1['order'] = 0;
				if (empty($item2['order'])) $item2['order'] = 0;

			    if ($item1['order'] == $item2['order']) return 0;
			    return $item1['order'] < $item2['order'] ? -1 : 1;
			});
			if ($position == 'main') { //block main should be the last one in order to use other $block_ output
				$block_main = $blocks;
				unset($this->blocks['main']); //remove it here and add it back after foreach 
			} else {
				$this->blocks[ $position ] = $blocks; //remember sorted blocks for later use
			}
			foreach ($blocks AS $block){
				if ( !empty($block['system']) AND is_array($block['system']) ){
					foreach( $block['system'] AS $key => $value) {
						$this->assign($key, $value);
					}
				}	
				//Smarty append should always be merged => which value in the new array will replace if keys are the same
				//like array_merge() but not re-index numerical keys
				if (!empty($block['api'])) {
					$this->append('api', $block['api'], TRUE);
				}

				if (!empty($block['links']) ){
					$this->append('links', $block['links'], TRUE);
				}	
				if (!empty($block['html'])) {
					$this->append('html', $block['html'], TRUE);
				}
				//menu last so it cant be overwritten by plugins var	
				if (!empty($block['menu'])) {
					//dont use array_merge_recursive as it could turn menu name from string to array 
					$menu[ $position .'_menu'] = array_merge($menu[ $position .'_menu']??[], $block['menu']);
				}
			}
		}
		//add block main back, main variables are overridden by other blocks' variables (main is first originally) but it can use other $block_ output
		$this->blocks['main'] = $block_main;

		if (!empty($menu)) {
			$this->append('html', $menu, TRUE);					
			//$this->assign('menu', $menu);	//this looks better but the variable may be incidentally overwritten
		}
			
		//debug show blocks, some are reassigned below but that's ok
		if (!empty($_GET['nam']) AND $_GET['nam'] == 'sgblock') {
			foreach ($this->core_blocks AS $position){
				$output[$position] = '<div class="add-content" style="border-style:dotted; width: 100%; text-align:center">Widgets for <b>'. ucwords($position) .'</b> shown here!</div>';
				$this->assign('block_'. $position, $output[$position]);
				$position = 'content_'. $position;
				$output[$position] = '<div class="add-content" style="border-style:dotted; width: 100%; text-align:center">Widgets for <b>'. ucwords($position, '_') .'</b> shown here!</div>';
				$this->assign($position, $output[$position]);
			}
		}

		foreach ($this->blocks AS $position => $blocks){
			!isset($output[$position]) && $output[$position] = ''; 
			foreach ($blocks AS $block){
				if (!empty($block['output'])) {
					$output[$position] .= $block['output']; 
				} elseif (!empty($block['template']['string'])) {
					$output[$position] .= $this->smarty->fetch('string:base64:'. $block['template']['string']);
				} elseif (!empty($block['template']['file'])){
					if (!empty($block['template']['directory'])){
						$this->addTemplateDir($this->config['system']['base_dir'] .'/'. $block['template']['directory']); //added to templateDir so the default template can be used if the template file isnt present in site's template folder
						//print_r($this->smarty->getTemplateDir());
					} 
					$output[$position] .= $this->renderFile($block['template']['file']);
				}
			}
			if ($this->debug){
				$output[$position] .= '<div class="add-content" style="border-style:dotted; width: 100%; text-align:center">Widgets for <b>'. ucwords($position) .'</b> shown here!</div>';
			}	
			if (!empty($output[$position])) {
				//these variables are not escaped, double check plugin output if use inside admin area
				if (in_array($position, $this->core_blocks)) {
					$this->assign('block_'. $position, $output[$position]); //core blocks are prefixed by "block_" in layout
				} else {
					$this->assign($position, $output[$position]); //non-core blocks are not prefixed
				}							
			}		
		}

		/*if ($this->debug) {
			foreach ($this->core_blocks as $block) {
				if (empty($output[$block])) {
					$this->assign('block_'. $block, '<div class="add-content" style="border-style:dotted; width: 100%; text-align:center">'. $block .'</div>');
				}
			}
		}*/	
		//$string = base64_encode('  {block name="block_11"}This is for {$spotlight_content}{$title}{/block}{block name="right_content"}This is a test: {$title}{/block}');
		//echo $this->smarty->fetch('string:{$content}');
		if (!empty($this->layout['content'])) {
			$layout = 'string:base64:' . $this->layout['content'];
			$compile_id = "site". $this->config['site']['id'] ."-". $this->layout['slug'] ."-". $this->layout['modified'];
		} elseif (is_string($this->layout)) {
			$layout = 'layouts/'. basename($this->layout) .'.tpl';
		} else $layout = 'layouts/default.tpl'; 

		try {
			http_response_code($this->blocks['main']['Status']['code']);
			echo $this->sanitizeOutput($this->smarty->fetch('extends:'. $layout .'|header.tpl|footer.tpl', '' , $compile_id??null));	
		} catch (\Exception $e) {
			//echo "<br>Smarty Exception: unable to fetch layout '". $layout ."' from template: ". $this->template;
			echo "Error: ". str_replace($this->smarty->getTemplateDir(), [], $e->getMessage() );
			//echo $e->getMessage();
		}		
	
		if ($this->debug) {
			echo "<div class='col-md-12'><pre>Smarty vars =>";
			$debug = $this->smarty->getTemplateVars();
			unset($debug['LANG']);
			print_r($debug);
			echo "<br><br>View blocks => ";
			print_r($this->blocks);
			echo "</pre></div>";
			echo <<<HERE
			<div id="editor-modal" class="modal fade backdrop-blur">
			  <div class="modal-dialog modal-lg">
			      <div class="modal-content">
			        <div class="modal-body">
						<label><i class="fa fa-bookmark-o"></i><input class="input-name input-lg" type="text" name="widget[name]" placeholder="Widget Name" value="" style="border: none;"></label>
					</div>						    			         
					<div role="tabpanel">
						<!-- Nav tabs -->
						<ul id="main-tab" class="nav nav-tabs" role="tablist">
							<li role="presentation"><a href="#tab-1" aria-controls="tab-1" role="tab" data-toggle="tab">Images</a></li>
							<li class="active" role="presentation"><a href="#tab-2" aria-controls="tab-2" role="tab" data-toggle="tab">Settings</a></li>			
						</ul>
						<!-- Tab panes -->
						<div class="tab-content">
							<div id="tab-1" class="tab-pane" role="tabpanel"> asdfsadfsadasd <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br>
							</div>
							<div id="tab-2" class="tab-pane active" role="tabpanel"> asdfasd <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br>
							</div>
						</div>
					</div>																		
			         <div class="modal-footer">
						<span  class="pull-left">
							Visibility
							<select id='location' name='widget[location]'>
								<option value='Page'>This Page</option>
								<option value='Product'>All Product Pages</option>
								<option value='Site'>All Pages</option>		
							</select>
							Position 
							<select name='widget[position]'>
								<option id='position' ></option>
							</select>
						</span>	
						<span class="pull-right">
							<a class="btn btn-default" data-dismiss="modal" aria-hidden="true">Cancel</a>
							<input type='submit' value='Save Widget' class='btn btn-primary' name='save_widget' id='save_widget'>
						</span>
			         </div>
			      </div>
			   </div>      
			</div>
<script type="text/javascript">
	$(document).ready(function() {
		$('div.add-content').click(function(ev) {
			var value = $(this).text();
			$('#editor-modal').find('#position').text(value);       
			$('#editor-modal').modal();
			return false;
		});
	});
</script>		
HERE;
		}					
	}
	protected function sanitizeOutput($buffer) {
		return $buffer; //not remove things for now
	    $search = array(
	        '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
	        '/[^\S ]+\</s',  // strip whitespaces before tags, except space
	        '/(\s)+/s'       // shorten multiple whitespace sequences
	    );

	    $replace = array(
	        '>',
	        '<',
	        '\\1'
	    );

	    $buffer = preg_replace($search, $replace, $buffer);

	    return $buffer;
	}					
}
/*
[main][status]
	  [Page::render][order] -> use for sorting (optional)
	  				[api]
	  				[system]
	  				[html]
	  				[menu]
	  				[template][layout] -> only one layout for page -> it is here for convenient - standalone prefer
	  				[template][directory] -> specify the directory to load template file
	  				[template][file] * -> $block_main .= ...
	  				[template][string] * -> $block_main .= ...
	  				[output] * -> $block_main .= ...
	  [Page::related][api]
[top][Menu::render][menu] -> $menu.top = raw data of menu with slug/name added 
				   [template][string] -> $block_top .= generated html for menu()
					([menu][top] merged by Menu::render and merged again here to $menu.top)	 						 
[footer][Menu::render][menu]
						 ->$menu.footer = raw data of menu
		[Widget::render][output]
 */		
?>