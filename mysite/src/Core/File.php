<?php
namespace SiteGUI\Core;

class File {
	use Traits\Application { generateRoutes as trait_generateRoutes; }

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user); //construct Application

		$this->requirements['MANAGE']  = "File::manage";
		$this->requirements['PROTECT'] = "File::protect";
		$this->requirements['DESIGN']  = "Page::design";	
	}
	
	/**
	* list all  
	* @return none
	*/
	public function main() {
		if ( ! $this->user->has($this->requirements['MANAGE']) ){
			$this->denyAccess('manage files');
		} else {			
			$block['links']['file_api'] = $this->slug('File::action', ["action" => "manage"] );							
			$block['template']['file'] = "file_main";
			if ( !empty($_REQUEST['CKEditorFuncNum']) OR !empty($_REQUEST['sg-preview']) ){
				$this->view->setLayout('blank');
			}		

			//$this->view->setStatus($status);							
			$this->view->addBlock('main', $block, 'File::main');
		}						
	}

	//view attachment for admin
	public function view($id) {
		$this->clientView($id);
	}	
	//view attachment, require no permission other than being an authenticated user
	public function clientView($id) {
		$id = $this->sanitizeFileName($id);
		if ($id) {
			$file = $this->config['system']['base_dir'] .'/resources/protected/site/'. $this->config['site']['id'] .'/attachment/';
			$file .= substr($id, 0, 3) .'/'. $id; //detect folder from file name
			if ( @file_exists($file) AND @is_file($file) ){
				header('Content-Type: '. mime_content_type($file));
				header('Content-Disposition: inline; filename="'.basename($file).'"');
				header('Cache-Control: public, max-age=315360000'); //cache 10 years
			    header('Pragma: public');				
			    header('Content-Length: '. filesize($file));
				ob_clean();
	            flush();
	            readfile($file);
	            exit;
			}			
		}		
	}
	/**
	 * File API endpoint
	 * @param  integer $id [description]
	 * @return [type]           [description]
	 */
	public function manage($id = ''){
		if ( ! $this->user->has($this->requirements['MANAGE']) ){
			$block['api']['error'] = $this->trans("You don't have permissions to :action", ['action' => 'manage files']); //elfinder format
			$this->view->addBlock('main', $block, 'File::edit', 'prepend');
			$this->denyAccess('change anything here');
		} elseif (!empty($this->config['system']['base_dir'])) {
			include_once $this->config['system']['base_dir'] .'/src/vendor/elFinder/elFinderConnector.class.php';
			include_once $this->config['system']['base_dir'] .'/src/vendor/elFinder/elFinder.class.php';
			include_once $this->config['system']['base_dir'] .'/src/vendor/elFinder/elFinderVolumeDriver.class.php';
			include_once $this->config['system']['base_dir'] .'/src/vendor/elFinder/elFinderVolumeLocalFileSystem.class.php';
			// Required for MySQL storage connector
			// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
			// Required for FTP connector support
			// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';


			/** Does not seem to work - use attribute instead
			 * Simple function to demonstrate how to control file access using "accessControl" callback.
			 * This method will disable accessing files/folders starting from '.' (dot)
			 *
			 * @param  string  $attr  attribute name (read|write|locked|hidden)
			 * @param  string  $path  file path relative to volume root directory started with directory separator
			 * @return bool|null
			 *
			function access($attr, $path, $data, $volume) {
				return (strpos(basename($path), '.') === 0) ?   // if file/folder begins with '.' (dot)
					!($attr == 'read' || $attr == 'write')      // set read+write to false, other (locked+hidden) set to true
					:  null;                                    // else elFinder decide it itself
			}*/

			$path = $this->config['system']['base_dir'] .'/resources/public/uploads/site/'. $this->config['site']['id'];
			$url  = $this->config['system']['cdn']                .'/public/uploads/site/'. $this->config['site']['id'];
			$user_path = $this->config['system']['base_dir'] .'/resources/protected/user/'. $this->user->getId();
			if ( !is_dir( $user_path ) ){
			    @mkdir( $user_path, 0771, true );
			}			
			//create upload folder for this month/quarter
			$upload_dir = date("Y") .'Q'. ceil(date("n") / 3); //2021Q3
			if (!is_dir( $path .'/'. $upload_dir ) ){
			    @mkdir(  $path .'/'. $upload_dir, 0771, true );
			}
			//create protected folder if not exist (for secondary SiteAdmin)
			$protected_path = str_replace('public/uploads', 'protected', $path);
			if (!is_dir( $protected_path ) ){
			    @mkdir(  $protected_path, 0771, true );
			}
			//create template folder if not exist (for secondary SiteAdmin)
			$template_path = str_replace('uploads', 'templates', $path);
			if (!is_dir( $template_path ) ){
			    @mkdir(  $template_path, 0771, true );
			}
			// Documentation for connector options:
			// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
			$opts = [
				// 'debug' => true,
				'roots' => [
					'public' => [
						'driver' => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
						'path'   => $path,         // path to files (REQUIRED)
						'startPath' => $path .'/'. $upload_dir,
						'alias'	 => "Public Folder",
						'URL'    => $url, // URL to files (REQUIRED)
						'defaults' => ['read' => true, 'write' => true],
						'tmbSize' => 96,
			            'attributes' => [
			                [ 
			                    'pattern' => '/\/'. $upload_dir .'\//', //current upload folder - locked = false
			                    'read' => true,
			                    'write' => true,
			                    'hidden' => false,
			                    'locked' => false
			                ],
			                [ 
			                    'pattern' => '/\/\./', //hidden files
			                    'read' => false,
			                    'write' => false,
			                    'hidden' => true,
			                    'locked' => true
			                ],
			            ],
					],
					'protected' => [
						'driver' => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
						'path'   => $protected_path, //protected folder
						'alias'	 => 'Protected Folder',
						//'URL'    => $url, // URL to files (REQUIRED)
						'defaults' => ['read' => true, 'write' => true, 'locked' => false],
						'tmbSize' => 96,
			            'attributes' => [
			                [ 
			                    'pattern' => '/\/\./', //hidden files
			                    'read' => false,
			                    'write' => false,
			                    'hidden' => true,
			                    'locked' => true
			                ],
			            ],
					],
					'template' => [
						'driver' => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
						'path'   => $template_path,        // path to files (REQUIRED)
						'alias'	 => "Site Templates",
						'URL'    => str_replace('uploads', 'templates', $url), // URL to files (REQUIRED)
						'defaults' => ['read' => true, 'write' => true],
						'tmbSize' => 96,
			            'attributes' => [
			                [ 
			                    'pattern' => '/^\/'. $this->config['site']['template'] .'$/', //current site template - locked true
			                    'read' => true,
			                    'write' => true,
			                    'hidden' => false,
			                    'locked' => true
			                ],
			                [ 
			                    'pattern' => '/\/\./', //hidden files
			                    'read' => false,
			                    'write' => false,
			                    'hidden' => true,
			                    'locked' => true
			                ],
			            ],
					],
					'user' => [
						'driver' => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
						'path'   => $user_path, //protected folder
						'alias'	 => 'My Private Folder',
						//'URL'    => $url, // URL to files (REQUIRED)
						'defaults' => ['read' => true, 'write' => true, 'locked' => false],
						'tmbSize' => 96,
			            'attributes' => [
			                [ 
			                    'pattern' => '/\/\./', //hidden files
			                    'read' => false,
			                    'write' => false,
			                    'hidden' => true,
			                    'locked' => true
			                ],
			            ],
					]				
				]
			];

			if ( ! $this->user->has($this->requirements['PROTECT']) ){
				unset($opts['roots']['protected']); //remove protected folder
			}	
			if ( ! $this->user->has($this->requirements['DESIGN']) ){
				$opts['roots']['template']['defaults']['locked'] = true; //prevent accidental delete, user can still write to folder/file
				$opts['roots']['template']['attributes'][ ] = [ 
                    'pattern' => '/^\//', //template folder 
                    'read' => true,
                    'write' => false,
                    'hidden' => false,
                    'locked' => true
                ];
			}

			// run elFinder
			$connector = new \elFinderConnector(new \elFinder($opts));
			$connector->run();
		} else {
			echo "Upload directory is not defined!";
		}					
	}
	public function generateRoutes($extra = []) {
		$name = strtolower($this->class);

		$r[ $this->class .'::action'] = ['GET|POST', '/[i:site_id]/'. $name .'/[manage|view:action]/[*:id]?.[json:format]?'];
		$r[ $this->class .'::main']   = ['GET',      '/[i:site_id]/'. $name .'.[json:format]?'];

		$routes[ $this->config['system']['passport'] ] = $r;
        $routes['SiteUser']['File::clientView'] = ['GET', '/file/view/[*:id]?.[json:format]?'];

		return $routes;
	}	
}	