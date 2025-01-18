<?php
namespace SiteGUI\Core;
use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\SMTP;
use \PHPMailer\PHPMailer\Exception;

class Notification {
	use Traits\Application { generateRoutes as trait_generateRoutes; }
	protected $smarty;

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user); //construct Application
		$this->table = $this->table_prefix ."_notification";
		$this->requirements['RECEIVE'] 	= "Role::Staff";
	}
	public static function config($property = '') {
	    $config['app_configs'] = [
	        'channels' => [
	        	'label' => 'Notification Channels',
	            'type' => 'fieldset',
	            'fields' => [
			        'channel' => [
			            'label' => 'Channel',
			            'type' => 'select',
			            'is' => 'required',
			            'options' => [
			            	'From::lookup' => 'From::lookup',
			            	'activation',
			            	'Scope::Notification',
			            ],
			            'description' => '',
			            'value' => '',
			        ],
			    ],      
	        ],
	        'selection' => [
                'label' => 'Channel Selection',
                'type' => 'select',
                'is' => 'required',
                'options' => [
                    'one' => 'One Channel',
                    'two' => 'Two Channels',
                    'three' => 'Three Channels',
                    'all' => 'All Channels',
                ],
                'value' => 'one',
                'description' => 'Notifications can be sent via one, two, three or all channels depending on your selection. Channels are selected from top to bottom',
            ],
	        'header1' => [
	            'label' => 'Default configuration for email based channels',
	            'type' => 'header',
	        ],
	        'from_name' => [
	            'label' => 'Email From Name',
	            'type' => 'text',
	            'size' => '6',
	            'value' => '',
	            'description' => '',
	        ],
	        'from_mail' => [
	            'label' => 'Email From Address',
	            'type' => 'text',
	            'size' => '6',
	            'value' => '',
	            'description' => '',
	        ],
            'signature' => [
                'label' => 'Email Signature',
                'type' => 'textarea',
                'rows' => '3',
                'cols' => '60',
                'value' => 'With 💝 From All Of Us',
                'description' => 'Global signature for all email messages',
            ],
	    ];
    	return ($property)? $config[ $property ] : $config;		
    }

	public function main() {
		if ( ! $this->user->has($this->requirements['RECEIVE']) ){
			$this->denyAccess('list');
		} else {
			$query = $this->db
				->table($this->table)
				->select('id', 'title AS name', 'type', 'seen', 'created')
				->where('staff_id', $this->user->getId() )
				->where(function ($query) {
		          	$query->where('seen', NULL)
		              ->orWhere('seen', 0)
		              ->orWhere('seen', '>', time() - 10*3600);
		        })
		        ->when( !empty($_REQUEST['cors']), function($query){ //for dashboard prepend
		        	return $query
		        		->selectRaw('CASE WHEN seen < 1 THEN seen ELSE UNIX_TIMESTAMP() - seen END AS seen_ago')
		        		->orderBy('seen_ago', 'asc') //not seen then recent seen
		        		->orderBy('id', 'desc');
		        }); 
		    $block = $this->pagination($query);    
			if( $block['api']['total'] > 0 ){
				if ($this->view->html){				
					$block['html']['table_header'] = [
						'id' => $this->trans('ID'),
						'name' => $this->trans('Name'),
						'type' => $this->trans('Type'),
						'seen' => $this->trans('Seen'), 
						'created' => $this->trans('Created'),
						'action' => $this->trans('Action'),
					];
					$block['html']['column_type'] = ['seen' => 'date', 'created' => 'date'];

					$links['api']  = $this->slug($this->class .'::main');
					$links['edit'] = $this->slug($this->class .'::action', ["action" => "edit"] );
					$block['links'] = $links;	
					$block['template']['file'] = "datatable";		
				}
			} else {
				$status['result'] = "error";
				$status['message'][] = $this->trans('No records found');
				
				if ($this->view->html){				
					$status['html']['message_type'] = 'info';
					$status['html']['message_title'] = $this->trans('Information');
				}
			}				

			!empty($status) && $this->view->setStatus($status);							
			$this->view->addBlock('main', $block, $this->class .'::main'); 
		}
	}

	public function edit($id = 0){
		if ( ! $this->user->has($this->requirements['RECEIVE']) ){
			$this->denyAccess('view');
		} else {
			$block['api']['notification'] = $this->db
				->table($this->table)
				->where('staff_id', $this->user->getId() )
				->where('id', $id)
				->first();	
			if ( empty($block['api']['notification']) ){
				$status['result'] = 'error';
				$status['message'][] = $this->trans('No such :item', ['item' => $this->class ]);
				$this->view->setStatus($status);
			} else {
				ignore_user_abort(true); //keep running after user has redirect
				// Media type for Server Sent Events (SSE). 
				header('Content-Type: text/event-stream;charset=UTF-8');
				// Disables browser caching. 
				header('Cache-Control: no-cache, must-revalidate');
				// Disables Nginx buffering and compressing on the go. 
				header('X-Accel-Buffering: no');
				header('Connection: keep-alive');
				$block['api']['notification']['seen'] = time();	
				if ($this->view->html){	
					$url = json_decode($block['api']['notification']['url']??'', true); //url stored as Route name/params
					if ( is_array($url) ){
						$url = $this->slug(...$url);
					} else {
						$url = $block['api']['notification']['url']; //string
					}	
					//echo $url; exit;	
					header('Location: '. $url); //do not exit, need update seen
					//required flushing a string length = 4096 in order to avoid waiting for this method to finish running
					for ($i = 0; $i < 1; $i++){ //this does stream
						$string = str_repeat(' ', 4096);
						echo $string .'<br>\r\n'; 
						flush();
					}
				} else {
					$this->view->addBlock('main', $block, $this->class .'::edit'); 
					//allow updating seen via ajax
		            header('Access-Control-Allow-Origin: '. $this->config['system']['edit_url']);
		            //header('Access-Control-Allow-Method: POST, OPTION'); 
		            header('Access-Control-Allow-Headers: X-Requested-With');
		            header('Access-Control-Allow-Credentials: true');
                    $block['api']['status']['result'] = 'success';
                    echo 'data: '. json_encode($block['api']['notification']) . PHP_EOL . PHP_EOL;
                    echo 'data: [DONE]'. PHP_EOL . PHP_EOL;
                    echo str_repeat(' ', 4096) . PHP_EOL . PHP_EOL;
                    flush();
				}

				session_write_close(); //required for browser to redirect before page finish loading
				//sleep(3); //can use to test if no delay is observed at frontend
				//this will slow down 							
				$this->dbm->getConnection('central')
					->table($this->table)
					->where('staff_id', $this->user->getId() )
					->where('id', $id)
					->update([
						'seen' => $block['api']['notification']['seen']
					]);	
				exit;
			}
		}					
	}
    //$users can be one user, multiple users
	public function sendMultiChannels($users, $subject, $template, $data, $email_only = false, $run = false) {
		$return['mail'] = 0;
		$return['message'] = 0;

		if ( !$run AND !empty($this->redis) ){
			$this->enqueue($this->class .'::'. __FUNCTION__, $users, $subject, $template, $data, $email_only, 'run'); 
		} else {	
			$cfg = $this->getMyConfig();
			if ( empty($cfg['channels']) ){
				$cfg['channels']['Phpmail']['channel'] = 'Phpmail'; //default to local php mail()
			} else {	
				$cfg['channels'] = array_column($cfg['channels'], null, 'channel'); //remove duplicated
			}
			if ( empty($cfg['from_mail']) ){
				$cfg['from_mail'] = 'no-reply@'. parse_url('http://'. $this->config['site']['url'], PHP_URL_HOST);
			}
		    if ( empty($data['subject']) ){
		    	$data['subject'] = ($subject)? $subject : ucwords(trim(preg_replace('/([A-Z])|_/', ' \1', $template)));
		    }	
		    if (empty($data['signature']) AND !empty($cfg['signature'])) {
			    $data['signature'] = $cfg['signature'];
		    }

			$site_locale = $this->config['site']['language']??$this->config['system']['language'];
		    //Backup lang before switching to target's language
		    $this->config['lang_bk'] = $this->config['lang'];		    
		    //Recipients
	    	if ( !empty($users['email']) OR !empty($users['mobile']) ){ //single user - include recipient name
		    	//stop processing if no phone/email is present
		    	if ( empty($users['mobile']) ) $email_only = true; 
		    	if ( $email_only AND empty($users['email']) ) return;

		    	if ( empty($data['recipient']) ){
			    	$data['recipient'] = $users['name']??''; //only for single user		    		
		    	}
		    	$lang = !empty($users['language'])? $users['language'] : $site_locale; //$users['language'] might be not null but empty, site lang either null/set
	    		$batches[ $lang ]['to_name'] = $users['name']??''; //only for single user
	    		!empty($users['email'])  && ($batches[ $lang ]['to_mail'][] = $users['email']);
	    		!empty($users['mobile']) && ($batches[ $lang ]['to_mobile'][] = $users['mobile']); //for message
	    		//$batches[ $lang ]['to_others'][] = $users['']; //for other chat handles
	    		$batches[ $lang ]['body'] = $this->fetch($template .'.tpl', $data, $lang);
	    		//after fetch() so the correct $config['lang'] is loaded to be used for trans()
	    		$batches[ $lang ]['subject'] = $this->trans($data['subject']); 	    		
	    		$batches[ $lang ]['subject2'] = $data['name'][ $lang ]
	    			??$data['name'][ $site_locale ]
	    			??$data['name']
	    			??null; //($for && !is_array($name))? ': '. $for : ''; 
	    	} elseif (is_array($users) ){ //multiple users - generic message, no recipient name
		    	foreach ($users as $i => $u) {
	    			$lang = !empty($u['language'])? $u['language'] : $site_locale;
		    		if ( !empty($u['mobile']) ){
			    		$batches[ $lang ]['to_mobile'][] = $u['mobile'];
			    		$has_mobile = true;	    			
		    		} 
		    		if ( !empty($u['email']) ){
						$batches[ $lang ]['to_bcc'][] = $u['email'];
						$has_email = true;
		    		} elseif ($email_only OR empty($u['mobile']) ){
		    			unset($user[ $i ]); //remove from queue
		    			continue;
		    		}
	
					if ( !isset($batches[ $lang ]['body']) ){
						$batches[ $lang ]['body'] = $this->fetch($template .'.tpl', $data, $lang);
						$batches[ $lang ]['subject'] = $this->trans($data['subject']);
						$batches[ $lang ]['subject2'] = $data['name'][ $lang ]
							??$data['name'][ $site_locale ]
			    			??$data['name']
			    			??null;
					}	
		    	}
		    	//stop processing if no phone/email is present
		    	if ( empty($has_mobile) ) $email_only = true;
		    	if ( $email_only AND empty($has_email) ) return;
		    }

			$success = 0;
			foreach ($cfg['channels'] AS $app => $channel){
				$response = [];
				$app = $this->getAppInfo($app, 'Notification');
				if ($app){
					$this->checkActivation($app['class']);
					try {
						$site_config['app'] = $this->getAppConfigValues($this->trimRootNs($app['class']), $app['app_configs']);
						if ( !empty($app['app_configs']['oauth']['type']) AND $app['app_configs']['oauth']['type'] == 'oauth' ){
							$site_config['app']['app_secret'] = $this->getSystemConfig($this->trimRootNs($app['class']) .'::'. ($app['id']??$app['slug']??''), 'app_secret'); //append $app.id to avoid loading unintended secret
							if ($site_config['app']['app_secret']){
								$site_config['app']['app_secret'] = $this->decode($site_config['app']['app_secret'], 'static');
							}
						}		

						$site_config['site'] = $this->config['site'];
						//to support locale in Gateway
						$instance = new $app['class']($site_config);

						if ( method_exists($instance, 'email') ){//email based	
					    	foreach ($batches??[] as $batch) {
								if (empty($batch['to_mail']) AND empty($batch['to_bcc'])){
					    			continue;
					    		}
					    		if ( !empty($batch['subject2']) ){ 
					    			$batch['subject'] .= ': '. $batch['subject2'];
					    		}
							    $response = call_user_func([$instance, 'email'], 
							    	$cfg['from_name']??$cfg['from_mail'], //string
							    	$cfg['from_mail'], 		//string
							    	$batch['to_name']??'',	//array
							    	$batch['to_mail']??[],	//array
							    	$batch['to_bcc']??[],	//array
							    	$batch['subject']??'',	//string
							    	$batch['body']??''		//string
							    );
							    $response['method'] = 'mail';
								if ( !empty($response['new_tokens']) ){ //save app's new access/refresh token
									$new_tokens = $response['new_tokens'];					
								}
							}    
							//echo $mail->FromName .' '. $mail->From .'<br>'. $mail->Subject .'<br><br>'. $mail->Body .'<br><br>'. $mail->AltBody;	
						} elseif ( !$email_only AND method_exists($instance, 'message') ) {
							foreach ($batches??[] as $lang => $batch) {
								$batch['request_id'] = rand(0,10000);
								$response = call_user_func([$instance, 'message'], 
							    	$subject, 	//string		
							    	$template,	//string
							    	$data,		//array	(recipient, subject, signature, page data)					
							    	$batch,		//array (to_mobile, to_others, subject, subject2, body, to_name (single user))
							    	$lang 		//string: language of this message
								);

							    $response['method'] = 'message';
								if ( !empty($response['new_tokens']) ){ //save app's new access/refresh token
									$new_tokens = $response['new_tokens'];					
								}
							}	
						}
						//save new tokens returned by app
						if ( !empty($new_tokens) ){
							if (empty($appstoreObj) ){
								$appstoreObj = new Appstore($this->config, $this->dbm, $this->router, $this->view, $this->user);
							}
							$appstoreObj->saveAppConfig($app, $new_tokens, 'save_hidden');
						}

						if ( !empty($response['status']) AND $response['status'] == 'success' ){
							$success++;
							$return[ $response['method'] ]++; //let caller know which method
							$return['via'][ $response['method'] ][] = $app['name'];

							if ( empty($cfg['selection']) OR ( $cfg['selection'] == 'one' OR 
									($cfg['selection'] == 'two' AND $success == 2) OR 
									($cfg['selection'] == 'three' AND $success == 3)
								)
							){
								break; //stop, notification app should return success only when sent
							}	
						}	
					} catch (\Exception $e){
						$response['status'] = 'error';
						$response['message'] = $this->trans('Gateway exception');	
					}
				}
			}		
			//Restore lang
			$this->config['lang'] = $this->config['lang_bk'];
			unset($this->config['lang_bk']);
			if ( !$success AND !empty($cfg['channels']) ){
				$callable['log_id'] = $run; //$run = $log_id when retrying, false as 1st run
				$callable['target'] = $this->class .'::'. __FUNCTION__;
				$callable['params'] = [ $users, $subject, $template, $data, $email_only]; //log_id added as $run by retry worker
			    $this->logPendingJob($callable, $this->class .'.', $data['id']??null); //for retrying later
			} else {
				//logActivity
	        	$this->logActivity($response['message']??$data['subject'], $this->class .'.', null, 'Info', $return);
				if (is_numeric($run)){ //retry with log_id
					$this->markPendingJobDone($run);
				}	
			}
		}
			
		return $return; //['via'][mail,message], [mail],[message]
	}	
	//$users can be one user, multiple users or just an email address (will be ignored)
	public function notifyStaff($users, $subject, $template, $data, $run = false) {
		if ( !$run AND !empty($this->redis) ){ //run async
			$this->enqueue($this->class .'::'. __FUNCTION__, $users, $subject, $template, $data, 'run'); 
			return;
		}
			
    	if ( !empty($users['id']) ){ //single user
    		$users = [ $users ];
    	}

    	if ( is_array($users) ){ //multiple users
	    	foreach ($users as $user) {
	    		if ( !empty($user['id']) ){
	    			$lang = !empty($user['language'])? $user['language'] : ($this->config['site']['language']??$this->config['system']['language']);

	    			$insert[] = $subject;
	    			$insert[] = $data['name'][ $lang ]
    					??$data['name'][ $this->config['site']['language']??$this->config['system']['language'] ]
		    			??$data['name']
		    			??'';
	    			$insert[] = $data['url']??'';
	    			$insert[] = $user['id'];
	    			$insert[] = time();
	    		}	
	    	}
	    	if (!empty($insert)){
	    		if ( !$this->upsert($this->table, ['type', 'title', 'url', 'staff_id', 'created'], $insert, ['created', 'seen' => 0], 'central') 
	    		){				        	
					$callable['log_id'] = $run; //$run = $log_id when retrying, false as 1st run
					$callable['target'] = $this->class .'::'. __FUNCTION__;
					$callable['params'] = [ $users, $subject, $template, $data];
				    $this->logPendingJob($callable, $this->class .'.', $data['id']??null); //for retrying later
	    		} else {
	    			if (is_numeric($run)){ //retry with log_id
						$this->markPendingJobDone($run);
					}
	    		}
	    	}
	    }	
	}	
	//fetch template resource using provided data i.e: like twig render
	public function fetch($file, $data = [], $lang = 'en'){
		if (empty($this->smarty)) {
			$this->smarty = new \Smarty();
			$this->smarty->registerPlugin("modifier", "trans", [$this, "trans"]);
			if (\PHP_VERSION_ID > 80000) { //PHP 8
				$this->smarty->muteUndefinedOrNullWarnings(); //temporary
			}
		}
        // init template vars
        $cf = $this->config; //make it short, no confusing pls. Use inside this condition only

        if (!empty($cf['system']['base_dir'])) {
            $this->smarty->setCompileDir( $cf['system']['base_dir'] .'/resources/templates_c' );
            $this->config['lang'] = $this->config['lang_bk']; //reset first
            if ( !empty($cf['system']['template_dir']) AND !empty($cf['system']['template']) ) { 
                $cf['system']['cdn'] = trim($cf['system']['cdn'], '/') 
                	.'/'. $cf['system']['template_dir']; //system.cdn here
	            
	            $mail_dir = $cf['system']['base_dir'] 
	            	.'/resources/'
	            	. $cf['system']['template_dir'] 
	            	.'/'. $cf['system']['template'] 
	            	.'/mails/';
                if ($lang != 'en' AND is_readable($mail_dir . basename($lang) .'/'. basename($file) ) ){
                	$this->smarty->addTemplateDir($mail_dir . basename($lang) ); //mail in user language
                	if (is_readable($mail_dir .'/../lang/'. basename($lang) .'.json' ) ){
                		 $this->config['lang'] = (json_decode(file_get_contents($mail_dir .'/../lang/'. basename($lang) .'.json')??'', true)??[]) + ($this->config['lang']??[]); //override with language other than 'en'
                	}	 
                } else {
	                $this->smarty->addTemplateDir($mail_dir);
                }
            }
            //Site override
	    	if (!empty($cf['site']['template']) ){//site - use site.cdn  
	    		$cf['site']['cdn'] = trim($cf['system']['cdn'], '/') 
	    			.'/public/templates/site/'
	    			. $cf['site']['id'];
	    		
	    		$mail_dir = $cf['system']['base_dir'] 
	    			.'/resources/public/templates/site/'
	    			. $cf['site']['id'] 
	    			.'/'. $cf['site']['template'] 
	    			.'/mails/';
	    		if ( is_readable($mail_dir) ){ 
		            if ($lang != 'en' AND is_readable($mail_dir . basename($lang) .'/'. basename($file) ) ){
	                	$this->smarty->addTemplateDir($mail_dir . basename($lang) ); 
	                } elseif ( is_readable($mail_dir . basename($file) ) ){
	                	$this->smarty->addTemplateDir($mail_dir); 
	                }
	            }    
				if ( is_readable($mail_dir .'/../lang/'. basename($lang) .'.json' ) ){
                	$this->config['lang'] = (json_decode(file_get_contents($mail_dir .'/../lang/'. basename($lang) .'.json')??'', true)??[]) + ($this->config['lang']??[]); //override with site's own language file including 'en'
                }
	        }
        } else {
            echo 'Email template directory is not defined!';
        }
        //$this->smarty->assign('template', $cf['system']['template'] );
        unset($cf['system']['base_dir'], $cf['system']['passport']);
        $this->smarty->assign('system', $cf['system']);
        $this->smarty->assign('site',   $cf['site']);
		$this->smarty->assign('data', $data);
		//echo "<pre>";		
		//print_r($this->smarty->getTemplateVars());
		return $this->smarty->fetch($file);
	}

	public function addTemplateDir($dir) {
		if (empty($this->smarty)) {
			$this->smarty = new \Smarty();
			if (\PHP_VERSION_ID > 80000) { //PHP 8
				$this->smarty->muteUndefinedOrNullWarnings(); //temporary
			}
		}
		$this->smarty->addTemplateDir($dir);
	}

	public function generateRoutes($extra = []) {
		$routes = $this->trait_generateRoutes($extra);
		unset($routes[$this->class .'::update'], $routes[$this->class .'::delete']);

		return $routes;
	}
}	