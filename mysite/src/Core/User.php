<?php
namespace SiteGUI\Core;
//site users/customers
class User extends \LiteGUI\User {
	use Traits\Application { generateRoutes as trait_generateRoutes; }

	protected $table_system;

	public function __construct($config, $dbm, $router, $view, $passport = 'user') {
		//this class could be dynamically invoked and a User/Admin object could be passed to $passport
		if ($passport instanceof \LiteGUI\User) {
			$user = $passport;
		} else {
			$user = $this; //$this will be populate by parent's contruct below
		}
		$this->app_construct($config, $dbm, $router, $view, $user);
		if ( (($this->config['site']['id']??null) == 1) || (($this->config['site']['user_site']??null) == 1) ){
			//site 1 and sites using mysite1_user can be on satelite server, so we should connect to central
			$this->db = $this->dbm->getConnection('central');
		}
		$this->table_system  = $this->table_prefix .'_system'; 

		//construct User - should be after other constructions as the base __construct may invoke other methods which require parameters set
		if (is_string($passport)) { //invoke parent's contruct only if $passport is not User/Admin object
			parent::__construct(null, null, $passport); //no need to set config & dbm again
		} else {
			//in this case $this != $this->user, hence $this->id is (and should) not be set to avoid confusion (2 different User objects)
			$this->passport = $this->user->passport;
		}
		//Must use $this->user->getId() to get current user's id from here as $this->id is not set if $user is not set to $this

		$this->requirements['MANAGE'] = "User::manage";
	}

####### This section is for User trait, User application's methods are at the end #######
	//$user->authenticate->logout if instructed
	//					 ->verifyLogin->is known as specified user
	//	   				 ->showLoginForm if login is unsuccessful
	//					 ->loadPermissions for identified site
	//get login information and verify against database
	public function authenticate() {	
		//check if table user has been initialized
		if ( ! $this->db->table('information_schema.tables')
			->where('table_schema', $this->db->raw('DATABASE()') )
			->where('table_name', $this->table_user)
			->get('table_schema')
			->all()
		){
			unset($_REQUEST, $_POST, $_GET); //cancel all user operations
			$status['message'][] = $this->trans('User Management app has not been activated');
			$this->view->setStatus($status);
		}	

     	//User Logout - Must be BEFORE authentication check so when user logs out, login is required immediately otherwise it is only required next visit.
     	if (isset($_REQUEST['logout']) AND $_REQUEST['logout'] === 'true') {
     		$this->id = 0;
	        unset($_SESSION[ $this->passport ]);
	        session_destroy();
	        //Redirection: prevent host poisoning by making sure SERVER_NAME should be either $system.url, [www|my].$site.url 
	        if (in_array($_SERVER['SERVER_NAME'], [
	            	parse_url($this->config['system']['url'], PHP_URL_HOST),
	            	$this->config['site']['url'],
	            	'www.'. $this->config['site']['url'],
	            	'my.' . $this->config['site']['url'],
	            	str_replace($this->config['subdomain'], '.my'. $this->config['subdomain'], $this->config['site']['url']),
	         	]) 
	      	){
	     		if ( !empty($this->config['system']['sso_provider']) AND $this->config['system']['sso_provider'] != $this->config['system']['url'] ){
 					header('Location: '. $this->config['system']['sso_provider'] . $this->config['system']['base_path'] .'?logout=true&initiator='. $this->encode($this->config['system']['url'] . $this->config['system']['base_path']) );
	     		} elseif ( !empty($_GET['initiator']) ){
	 				header('Location: '. $this->decode($_GET['initiator']) );
	     		} else {
	 				header('Location: https://'. $_SERVER['SERVER_NAME'] . $this->config['system']['base_path']);
	     		}
 				exit;
         	}
     	} 
		
      	if ( isset($_REQUEST['user_register']) AND !empty($this->site_id) AND self::getMyConfig('open_registration') ){//User Registration 
			$this->register($_POST);
		} elseif ( isset($_REQUEST['user_activate']) AND isset($_REQUEST['email']) ){ //User Activation
			//$this->activateUser();
			$user_info = self::read($_REQUEST['email'], 'email');
			if ($user_info) {
				if ($user_info['status'] == 'Active') {
					$status['message'][] = $this->trans('Your account has already been activated');
				} elseif ( hash_equals($this->hashify($user_info['email'] . $user_info['id'], 'static'), $_REQUEST['user_activate'] ) ) {
					if ($this->db
						->table($this->table_user)
					 	->where('id', $user_info['id'])
					 	->update(['status' => 'Active'])
					){
						$status['message'][] = $this->trans('Your account is now active');
						unset($_SESSION[ $this->passport ]['status']); //remove status as it is only set for Activation CTA
					} else {
						$status['message'][] = $this->trans('Activation went wrong');						
					}	
				} else {
					$status['message'][] = $this->trans('Invalid activation code');
				}	 	
			} else {
				$status['message'][] = $this->trans('The account cannot be located');
			}
			!empty($status) && $this->view->setStatus($status);			
		} elseif ( isset($_REQUEST['user_recover']) ){ 
        	//Password Recover. Step 1: send code, step 2: show form, step 3: verify and change the password, also activate if Unverified
			$expiryMin = 90;
        	if ($_REQUEST['user_recover'] == 1) { //step1
				if ( filter_var($_REQUEST['username'], FILTER_VALIDATE_EMAIL) ){
					$user_info = self::read($_REQUEST['username'], 'email');
				} else {
					$user_info = self::read($_REQUEST['username'], 'mobile');
					$recover['mobile'] = true;
				}	
				if ( ! $user_info ){
					$status['message'][] = $this->trans('The account cannot be located');
				} elseif ( in_array($user_info['status'], ['Active', 'Unverified']) ){
					$meta = []; //reset global var
					if ( !empty($recover['mobile']) AND $user_info['mobile'] ){
						//Mobile OTP
						$meta[] = $user_info['id'];
						$meta[] = 'mobile_otp';
						//combine both user_otp and mobile_otp as one OTP
						$status['html']['user_otp'] = bin2hex(openssl_random_pseudo_bytes(32));
						$recover['mobile_otp'] = random_int(100000, 999999); //cryptographic random generator
						$meta[] = $this->hashify($status['html']['user_otp'] . $recover['mobile_otp'], 'static');
						$meta[] = $user_info['mobile'];
						$meta[] = time() + 5*60;
					}
					if ( $user_info['email'] ){ //always send to email if user has email and notification has an email channel
						$meta[] = $user_info['id'];
						$meta[] = 'reset_token';
						$recover['reset_token'] = bin2hex(openssl_random_pseudo_bytes(32));
						$meta[] = $this->hashify($recover['reset_token'], 'static');
						$meta[] = $user_info['email'];
						$meta[] = time() + $expiryMin*60;
						$recover['cta_url'] = $this->config['system']['url'] . $this->config['system']['base_path'] .'?user_recover='. $recover['reset_token'];
					}
					
					if ( !empty($meta) ){
						if ($this->upsert($this->table_user .'meta', ['user_id', 'property', 'value', 'name', 'description'], $meta, ['value', 'description']) 
						){ //update value & expiry when duplicate
							$recover['recipient'] = $user_info['name'];
							//we need to do this as this is pre-auth action, unauthenticated user wont go pass login screen
							//config[site] may not be available at this point, Notification rely on it for querying fromEmail
							$notifier = new Notification($this->config, $this->dbm, $this->router, $this->view, $this->user);
							$notifier = $notifier->sendMultiChannels($user_info, "Password Reset", 'Password_Reset', $recover, false, 'run');
							if ( !empty($recover['mobile']) AND !empty($notifier['message']) ){
								$status['message'][] = $this->trans('An OTP password has been sent to your number (via :providers). Please use it as the password to login within 5 minutes', ['providers' => implode(', ', $notifier['via']['message']) ]);
							} elseif ( !empty($notifier['mail']) ){ //mail 
								$status['message'][] = $this->trans('Password reset link has been sent to your email. Please check and confirm within :min minutes', ['min' => $expiryMin]);
							} else {
								$status['message'][] = $this->trans('Sending failed');
							}
						} else {
							$status['message'][] = $this->trans('Unable to store nounce for this account');
						}	
					}	
				} else {
					$status['message'][] = $this->trans('The account cannot be changed');
				}
        	} else { //step 2,3
        		$token = $this->db->table($this->table_user .'meta')
					->where('property', 'reset_token') 
					->where('value', $this->hashify($_REQUEST['user_recover'], 'static'))
					->first();
        		if ( $token AND time() <= intval($token['description'])) {
        			$status['html']['do_recover'] = 1; 
        			if (empty($_POST['password'])) { //step2 - token provided
        				$_POST['username'] = $token['name'];
        			} elseif ( strlen($_POST['password']) < 8 ){
        				$status['result'] = 'error';
						$status['code'] = 403;
						$status['message'][] = $this->trans('Password should be at least 8 characters');  
        			} elseif ($_POST['password'] == $_POST['password2']) {//step 3
        				if ($this->db->table($this->table_user)
        					->where('id', $token['user_id']) 
        					->update([ 
        						'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        						'status'   => 'Active', 
        					])
        				){
        					//remove nounce
        					$this->db->table($this->table_user .'meta')->delete($token['id']);
							$status['message'][] = $this->trans(':item updated successfully', ['item' => 'Password']);
							unset($status['html']['do_recover']); //done
							$this->is(['id' => $token['user_id'] ]);
        				} else {
        					$status['result'] = 'error';
							$status['code'] = 403;
							$status['message'][] = $this->trans(':item was not updated', ['item' => 'Password']);			
        				}	
        			} else {
						$status['result'] = 'error';
						$status['code'] = 403;
						$status['message'][] = $this->trans('Passwords do not match. Please enter correctly');
        			}	
        		} else {
					$status['result'] = 'error';
					$status['code'] = 403;
					$status['message'][] = $this->trans('Expired or invalid code. Please try again');
				}
				unset($_POST['password']);//avoid login all cases
        	} 

			$status['html']['message_title'] = $this->trans('Information');
			$this->view->setStatus($status);	
		}

     	//Oauth login step 2 at central site: central may already logged in, just mark for redirection  
		if ( !empty($_GET['oauth']) AND !empty($_GET['login']) ){ 
        	if ( !empty($_GET['requester']) AND !empty($_GET['token']) ){//at central - subsite sso request callback 
	        	$_SESSION['sso']['requester'] = $this->decode($_GET['requester']);
	        	$_SESSION['sso']['token'] = $_GET['token'];
        	} elseif ( empty($_SESSION[ $this->passport ]['auth_request_from']) ){//Step 1 - all sites: should set before any redirection for step 2
        		$_SESSION[ $this->passport ]['auth_request_from'] = str_replace('logout=', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) );
        	}
		}

		//User already logged in
		if (!empty($_SESSION[ $this->passport ]['id'])) {
			//do it once after login completed
		} elseif ( !empty($_POST['user_login']) AND !empty($_POST['username']) AND !empty($_POST['password']) ){ 
			//User login submitted via form post
			$provided['username'] = $_POST['username'];
			$provided['password'] = $_POST['password'];	
			$provided['client_id'] = $_POST['client_id']??null; //exchange password for token: client_id required
			$provided['grant_type'] = $_POST['grant_type']??null; //exchange password for token
			if ( !empty($_POST['user_otp']) ){
				$provided['user_otp'] = $_POST['user_otp'];
			}		
			$this->verifyLogin($provided);
		} elseif ( !empty($api_token = $this->bearerToken()) OR !empty($_REQUEST['api_token']) ) { 
			//api access via Bearer authorization header or api_token param
			$provided['api_token'] = $api_token??$_REQUEST['api_token']; //fallback to $_REQUEST if $api_token is null
			$this->verifyLogin($provided);
		} elseif ( !empty($_GET['oauth']) AND ( !empty($_GET['code']) OR !empty($_GET['login']) ) ){ //oauth process to identify user
	
		}
    		
		//if no valid user at this point, show login form and stop immediately
      	if (empty($_SESSION[ $this->passport ]['id'])){ 
			$this->showLoginForm();
         	$this->view->render();
         	exit();
      	} else {
      		if ( !empty($_SESSION[ $this->passport ]['auth_request_from']) ){ //for authentication process only
	         	if ( str_starts_with($this->config['system']['url'], 'https://my.') OR 
	         		   str_ends_with($this->config['system']['url'], '.my'. $this->config['subdomain'])
	         	){ //my.account => sso to frontend as well (iframe?) 
	         		if ($this->view->html){ //not when ajax login
		         		$url  = 'https://'. $this->config['site']['url']; 
		         		$url .= '/?oauth=sso'; //need one provider to get central url, will be set to actual provider by central
		         		$url .= '&login=step1';
		         		$url .= '&initiator='. $this->encode($this->config['system']['url'] . $_SESSION[ $this->passport ]['auth_request_from']); //redirect back
		         	}
	         	} else {
	            	$url  = $_SESSION[ $this->passport ]['auth_request_from']; //original location (also hide oauth params)
	           	}	
            	unset($_SESSION[ $this->passport ]['auth_request_from']);
	           	if ( !empty($url) ){
	        		header('Location: '. $url);
    	       		exit;
    	       	}	
        	} elseif ( !empty($_GET['oauth']) AND !empty($_GET['code']) ){//already logged in but url contains oauth
        		header('Location: '. parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) );
    	       	exit;
        	}     	
      	}
	
		//If site_id has been identified, let's access and see what role and permission the user has
		if (!empty($this->config['site']['id'])) {
			$this->loadPermissions($this->config['site']['id']);
		}		
	}
	//get Site's oauth settings
	public function oauthProviders($id = false){
    	//we need this for subsites that do not have oauth configured, this site 1's config is also saved to _system table
    	$central_oauth =  $this->db
    		->table($this->table_prefix .'_system')
			->where('type', 'config')
		  	->where('object', $this->trimRootNs(__CLASS__))
		  	->where('property', 'oauth')
		  	->value('value');
		$central_oauth = json_decode($central_oauth??'', true);
		$names = self::config('app_configs')['oauth']['fields']['oauth_name']['options']??[];
		foreach($central_oauth??[] AS $oa){
			if ( !empty($oa['enable']) AND !empty($oa['oauth_name']) AND !empty($names[ $oa['oauth_name'] ]) ){
 		        $oa['name'] = $names[ $oa['oauth_name'] ];
 		        $oa['oauth_secret'] = $this->decode($oa['oauth_secret'], 'static');
 				$providers[ $oa['oauth_name'] ] = $oa;
	        }	
		}
    	//get oauth config for this site
    	if ( !empty($this->site_id) AND $this->site_id != 1 AND ($this->config['site']['user_site']??null) != 1 ){ //siteadmin hasn't loaded site config at this point
        	$app_config = self::getMyConfig();  
        	foreach ($app_config['oauth']??[] AS $oa) {
	        	if ( !empty($oa['oauth_name']) AND !empty($names[ $oa['oauth_name'] ]) ){
		        	if ( !empty($oa['enable']) AND !empty($oa['oauth_key']) AND !empty($oa['oauth_secret']) ){
			        	$oa['name'] = $names[ $oa['oauth_name'] ];
			        	$oa['direct'] = true; //direct oauth, not sso to central
			        	$providers[ $oa['oauth_name'] ] = $oa;
			        } elseif ( !empty($providers[ $oa['oauth_name'] ]) AND empty($oa['enable']) ) {
			        	unset($providers[ $oa['oauth_name'] ]);
			        }
			    }    	
        	}
    	} 
    	return $id? ($providers[ $id ]??[]) : ($providers??[]); 
	}
	// we verify provided login info here, also generate access_token when needed
	protected function verifyLogin($provided) {
		if (!empty($this->db)) {
			if ( !empty($provided['api_token']) ){ //api access
				//get user_id for this token first
				if (str_contains($provided['api_token'], '_access_')){ //mobile app: use clientid for multiple devices
					$provided['api_token'] = explode('_access_', $provided['api_token']);
					$provided['client_id'] = $provided['api_token'][0];
					$provided['api_token'] = $provided['api_token'][1]??'';
				} elseif (str_contains($provided['api_token'], '_refresh_')){ //refresh token
					$provided['api_token'] = explode('_refresh_', $provided['api_token']);
					$provided['client_id'] = $provided['api_token'][0];
					$provided['refresh_token'] = $provided['api_token'][1]??'';
				}
				$stored = $this->db->table($this->table_user . 'meta')
					->join($this->table_user .' AS user', 'user_id', '=', 'user.id')
					->where(function($query) use ($provided) {
						if (!empty($provided['refresh_token']) ){ //handle refresh token
							$query->where('property', 'refresh_token_'. ($provided['client_id']??'') );
						} else {
							$query->where('property', 'api_token') //token for API client
								->orWhere('property', 'access_token_'. ($provided['client_id']??'') ); //token generated automatically to replace password authentication, just need last 8 characters
						}		
					})
					->where('value', $this->hashify($provided['refresh_token']??$provided['api_token'], 'static'))
					->where('status', 'Active') //not allow unverified user
					->select('user.id', 'user.name', 'image', 'language', 'timezone', 'description as expiry')
					->first();
				if ( !empty($stored['expiry']) AND $stored['expiry'] < time() ) {//expired token
					$status['result'] = "error";
					$status['code'] = 401;
					$status['message'][] = $this->trans('Expired API token');
				} elseif ( !empty($stored['id']) AND is_int($stored['id']) ) {
					if (!empty($provided['refresh_token']) ){
						$this->setAccessToken($stored['id'], $provided['client_id']);
					} 	
					$stored['type'] = 'API';
					$this->is($stored); //set system user
				} else {
					$status['result'] = "error";
					$status['code'] = 401;
					$status['message'][] = $this->trans('Invalid API token');
				}	
			} elseif ( !empty($provided['username']) AND !empty($provided['password']) ){//if username and password provided by login form
				if ( !empty($provided['user_otp']) AND is_numeric($provided['username']) ){ //mobile otp
					//get user_id for this token first
					$stored = $this->db->table($this->table_user . 'meta AS meta')
						->join($this->table_user .' AS user', 'user_id', '=', 'user.id')
						->where('meta.name', $provided['username'])
						->where('property', 'mobile_otp')
						->where('value', $this->hashify($provided['user_otp'] . $provided['password'], 'static'))
						->whereIn('status', ['Active', 'Unverified'])
						->select('user.id', 'user.name', 'image', 'language', 'timezone', 'status', 'description as expiry')
						->first();
					if ( !empty($stored['expiry']) AND $stored['expiry'] < time() ) {//expired token
						$status['result'] = "error";
						$status['code'] = 401;
						$status['message'][] = $this->trans('Expired or invalid code. Please try again');
					} elseif ( is_int($stored['id']) AND $stored['id'] > 0 ) {
						$stored['type'] = 'System';
						//remove otp
        				$this->db->table($this->table_user .'meta')
        					->where('name', $provided['username'])
        					->where('property', 'mobile_otp')
        					->delete();
						$this->is($stored); //set system user
					} else {
						$status['result'] = "error";
						$status['code'] = 401;
						$status['message'][] = $this->trans('Expired or invalid code. Please try again');
					}	
				} else {
					//first get password hash for given username 
					$accounts = $this->db->table($this->table_user)
						->when(is_numeric($provided['username']), function ($query) use ($provided) {
							//mobile can be in multiple accounts, cant use hash to filter, load them all
							return $query->where('mobile', $provided['username']); 
						}, function ($query) use ($provided) {
							return $query->where('email', $provided['username']);
						})
						->whereIn('status', ['Active', 'Unverified']) //allow unverified user to login
						->select('id', 'password', 'name', 'image', 'language', 'timezone', 'status')
						->get()->all();
					//use password_verify to actually verify the submited password matches hash stored in db	
					if ( !empty($accounts) ){
						foreach($accounts AS $stored){
							if (password_verify($provided['password'], $stored['password'])) {
								unset($stored['password']);
								$stored['type'] = 'System';
								$correct_password = true;
								$this->is($stored); //set system user
								//handle api login
								if ( !empty($provided['grant_type']) AND $provided['grant_type'] == 'password' ){
									if ( !empty($provided['client_id']) ){
										$this->setAccessToken($stored['id'], substr($provided['client_id'], -8) );
									} else {
										$status['message'][] = $this->trans('Please provide Client ID for token exchange');
									}			
								}
								continue;								
							}
						}
						if (empty($correct_password)){
							$status['result'] = "error";
							$status['code'] = 401;
							$status['message'][] = $this->trans('Incorrect Password');	
						}
					} else {
						$status['result'] = "error";
						$status['code'] = 401;
						$status['message'][] = $this->trans('The account is not found or active');
					}
				}
				if ($this->getId() AND !empty($_SESSION['oauth']['id']) AND !empty($_SESSION['oauth']['type']) AND $this->canUse($_SESSION['oauth']['id'], 'oauth_id') 
				){//allow OAuth account linking
					$data['oauth_id']   = $_SESSION['oauth']['id'];
					$data['oauth_type'] = $_SESSION['oauth']['type'];
					$this->db->table($this->table_user)
						->where('id', $stored['id'])
						->update($data); 
					unset($_SESSION['oauth']);	
				}				  			  
			} elseif ( !empty($provided['type']) AND !empty($provided['id']) ){ //login using verified oauth_id and type
				$stored = $this->db->table($this->table_user)
					->where('oauth_id', $provided['id'])
					->where('oauth_type', $provided['type'])
					->whereIn('status', ['Active', 'Unverified'])
					->select('id', 'name', 'email', 'mobile', 'image', 'language', 'timezone', 'oauth_type AS type', 'status')
					->first(); //alert empty email&mobile
				if ( !empty($stored['id']) AND is_int($stored['id']) ){
					if (empty($_SESSION['cart'. $this->site_id]) AND empty($stored['email']) AND empty($stored['mobile']) ){
						$stored['status'] = "Incompleted";
					}	
					$this->is($stored); //set system user
				} else {
					//just store the oauth info here so user may connect their oauth using other method	later	  
					$_SESSION['oauth'] = $provided;
					//if self registering oauth user is allowed
					if ( !empty($_SESSION['register_oauth_user']) OR ( !empty($this->site_id) AND self::getMyConfig('open_registration') ) ){
						$this->register($_POST??null);
					} else {
						$status['result'] = "warning";
						$status['message'][] = $this->trans('Your account has yet been linked, continue to link it');
					}	
				}
			}	
			!empty($status) && $this->view->setStatus($status);
		}
		return false;
	}

	//get bearer token
	protected function bearerToken() {
		if (!empty($_SERVER['Authorization'])) {
         	$token = trim($_SERVER["Authorization"]);
     	} else if (!empty($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
         	$token = trim($_SERVER["HTTP_AUTHORIZATION"]);
     	} elseif (function_exists('apache_request_headers')) {
         	$requestHeaders = apache_request_headers();
         	// Server-side fix for bug in old Android versions 
         	$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
         	if (!empty($requestHeaders['Authorization'])) {
             	$token = trim($requestHeaders['Authorization']);
         	}
     	}

     	if (!empty($token)) {
     		$position = strrpos($token, 'Bearer ');
			if ($position !== false) {
         		$token = substr($token, $position + 7);
         		return strtok($token, ',');
     		}
    	}
    	return null; //null to help null coalescing
	}
	//Set access token
	protected function setAccessToken($user_id, $client_id){
		$meta[] = $user_id;
		$meta[] = 'access_token_'. $client_id; //just need 8 chars
		$access = bin2hex(openssl_random_pseudo_bytes(32));
		$meta[] = $this->hashify($access, 'static');
		$meta[] = 'Access Token';
		$meta[] = $expires = time() + 30*86400; //expire in 1 month

		$meta[] = $user_id;
		$meta[] = 'refresh_token_'. $client_id; //just need 8 chars
		$refresh = bin2hex(openssl_random_pseudo_bytes(32));
		$meta[] = $this->hashify($refresh, 'static');
		$meta[] = 'Refresh Token';
		$meta[] = time() + 3*30*86400; //expire in 3 month

		if ( $this->upsert($this->table_user .'meta', ['user_id', 'property', 'value', 'name', 'description'], $meta, ['value']) ){
			$block['api']['access_token'] = $client_id .'_access_'. $access;
			$block['api']['refresh_token'] = $client_id .'_refresh_'. $refresh;
			$block['api']['expires'] = $expires;
			$block['api_endpoint'] = 1;
			$this->view->addBlock('main', $block, 'User::authenticate');
			$this->db->table($this->table_user .'meta')
				->where('user_id', $user_id)
				->where('property', 'LIKE', 'access_token_%')
				->where('description', '<', time() - 30*86400)
				->delete(); //delete tokens expired more than 1 month
		}
	}
	
	protected function is($user) {
		if (!isset($user['name']) OR !isset($user['language'])){ //user's name has not been set, empty name will pass
			$stored = $this->db->table($this->table_user)
				->where('id', $user['id'])
				//->where('status', 'Active') //newly registered account is Unverified and this is just extra information
				->select('name', 'image', 'language', 'timezone')
				->first();	
			if ($stored) {	
				$user = array_merge($user, $stored);	
			}	
		}
		parent::is($user);
		if ($this->view->html){
			if ( empty($_REQUEST['subapp']) AND empty($_REQUEST['rowCount']) ){	//no need for subapp request			
				$this->view->assign('user', $user);
			}	
			//do not show message during ordering or activation/update process
			if ( empty($_REQUEST['user_activate']) AND empty($_REQUEST['user']['email']) AND empty($_REQUEST['user']['mobile']) AND empty($_SESSION['cart'. $this->site_id]) AND !empty($user['status']) ){
				//remind when login only, not always $_SESSION[ $this->passport ]['status'] = $user['status']; //for Activation CTA
				if ( str_starts_with($_SERVER['SERVER_NAME'], 'my.') OR str_ends_with($_SERVER['SERVER_NAME'], '.my'. $this->config['subdomain']) ){ //clientarea
					$link = $this->slug('User::clientView');
				}				
				if ($user['status'] == 'Incompleted') {
					$status['result'] = "warning";
					$status['html']['message_title'] = $this->trans('Information');
					$status['message'][ $link??0 ] = $this->trans('Your account has been linked without an email address or a phone number. Please update your contact information to receive notifications and enable account recovery');
					unset($_SESSION[ $this->passport ]['auth_request_from']); //stop sso to frontend to show message
				} elseif ( $user['status'] == 'Unverified' AND empty(self::getMyConfig('email_optional')) ){
					$status['message'][ $link??0 ] = $this->trans('Your email address has not been verified. Please check your email and follow the instruction to verify it');
				}

				!empty($status) && $this->view->setStatus($status);		
			}
		}
	}

	protected function loadPermissions($site_id) {
		$this->roles[0] = $this->class;//only set when user is already logged in
		if (!empty($this->db) AND 
			!empty($this->user->getId()) AND 
			!empty($site_id) AND 
			$this->config['site']['status'] == 'Active'
		){
			$data = $this->dbm->getConnection('central')
				->table($this->table_system)
				->where('type', 'role')
				->where('object', 'global')
				->where('property', 'Customer')
				->select('id', 'property AS role', 'value')
				->first();
					 
			if (!empty($data)) {
				$this->roles[0] = $data['role'];
					
				$permissions = explode(',', trim($data['value'], ',') );
				foreach ($permissions AS $key){
					//$this->permissions[ $item['id'] ] = 1; //we used both permid and name to check permission, stop using permid as we dont want to do extra step to check for SystemAdmin id 
					$key AND ($this->permissions[ $key ] = 1);
				}
			}
			$groups = $this->getGroups();
			foreach ($groups??[] AS $key){
				//As $key can be specified by customers, make sure it does not have special characters 
				if ( ! preg_match('/[^a-z_\-0-9]/i', $key) ){ //preg_match is true if $key has a character other than the search
					$this->permissions[ 'Group::'. $key ] = 1; //Group::key_name
					$this->roles[] = $key; //roles is for api
				}
			}	
		}
	
		if ($this->view->html){				
			$this->view->append('user', ['roles' => $this->roles], true);
		}
	}

   	//this method is a bit tricky because it relies on method create/read which could be overloaded in child class
   	//better be careful by using self instead of this for any dependent methods that could be overloaded
	public function register($registration) {
		if ( ! $this->user->isLoggedIn() ) {
			$provided = [];
			//oauth may be merged with local account	
			if ( !empty($registration['username']) ){ //non-existent or invalid password - allow register without password
				$registration['username'] = str_replace(' ', '', $registration['username']);
				 
				if (!empty($registration['password2']) AND $registration['password2'] != ($registration['password']??'') ){
					$status['result'] = 'error';
					$status['code'] = 401;
					$status['message'][] = $this->trans('Passwords do not match. Please enter correctly');
				} elseif ( $this->canUse($registration['username']) ){
					if ( filter_var($registration['username'], FILTER_VALIDATE_EMAIL) ){
						$provided['email'] = $registration['username'];
						if ( !empty($registration['mobile']) AND is_numeric($registration['mobile']) AND $this->canUse($registration['mobile'], 'mobile') ){
							$provided['mobile'] = $registration['mobile'];
						}
					} elseif ( is_numeric($registration['username']) ){
						$provided['mobile'] = $registration['username'];
						if ( !empty($registration['email']) AND filter_var($registration['email'], FILTER_VALIDATE_EMAIL) AND $this->canUse($registration['email'], 'email') ){
							$provided['email'] = $registration['email'];
						}
					}
					$provided['password'] = $registration['password']??bin2hex(random_bytes(10));	
					$provided['oauth_type'] = 'System';		
					if ( !empty($registration['name']) ){
						$provided['name'] = $registration['name'];
					}
					if ( !empty($registration['guest_checkout']) ){
						$provided['guest'] = 1;
					}
				} else {
					$status['result'] = 'error';
					$status['code'] = 401;
					$status['message'][] = $this->trans('Existing account! Please provide the correct password');
				}
			}
			//should be processed here to override oauth_type above
			if (!empty($_SESSION['oauth']['id']) AND !empty($_SESSION['oauth']['type']) ) { //oauth_id not stored in db
				//auth register
				if ( $this->canUse($_SESSION['oauth']['id'], 'oauth_id') ){
					$provided['oauth_id']   = $_SESSION['oauth']['id'];
					$provided['oauth_type'] = $_SESSION['oauth']['type'];
					if ( !empty($_SESSION['oauth']['email']) AND $this->canUse($_SESSION['oauth']['email'], 'email') ){
						$provided['oauth_account'] = $_SESSION['oauth']['email'];
						$provided['email'] = $_SESSION['oauth']['email'];
					}
					if ( !empty($_SESSION['oauth']['name']) ) {
						$provided['name'] = $_SESSION['oauth']['name'];
					}
					if ( !empty($_SESSION['oauth']['image']) ) {
						$provided['image'] = $_SESSION['oauth']['image'];
					}					
				} else {
					$status['result'] = 'error';
					$status['code'] = 401;
					$status['message'][] = $this->trans('Inactive account! Please contact support');				
				}
			}		
			//now we can use the method 'create' defined here (self) to create user, child's create method may create sth else.
			if (!empty($provided) AND empty(self::create($provided, 'switch_to_newly_created_user')) ){
				$status['result'] = 'error';
				$status['code'] = 403;
				$status['message'][] = $this->trans(':item was not created', ['item' => 'Account']);
			} else {
				unset($_SESSION['oauth']); //no longer needed
				if ( !empty($provided['username']) ){
					unset($_SESSION[ $this->passport ]['auth_request_from']); //stop sso for non-oauth registration for smooth checkout
				}
			}
			!empty($status) && $this->view->setStatus($status);
		}
	}

	public function create($data, $switch = false) {
		if ( !empty($this->config['site']['user_site']) ){
			//should allow dependent site to create account via checkout
			//return false;
			//$status['message'][] = $this->trans('User account must be updated through ') . $this->config['site']['user_site'];
		}
		if ( !empty($data['guest']) ) {
			$guest = 1; //wont send activation email and switch for guest account
		}
		$data = self::prepareData($data);
		$data['status'] = !empty($data['oauth_id']) && !empty($data['oauth_account'])? 'Active' : 'Unverified'; //always set newly created account to Unverified and let user to activate it except verified oauth 
		$data['registered'] = time();

		if ( !empty($data['email']) OR !empty($data['mobile']) OR ( !empty($data['oauth_id']) AND !empty($data['oauth_type']) ) ) {
			$data['id'] = $this->db
			   ->table($this->table_user)
			   ->insertGetId($data);
			if ($data['id'] AND $this->config['site']['id'] == 2 ){	//set to onboard siteadmin user
				$meta = [
					[
						'user_id' => $data['id'],
						'property' => 'onboard_site',
						'value' => 1,
					],
					[
						'user_id' => $data['id'],
						'property' => 'onboard_page',
						'value' => 1,
					],
					[
						'user_id' => $data['id'],
						'property' => 'onboard_product',
						'value' => 1,
					],
					[
						'user_id' => $data['id'],
						'property' => 'onboard_wysiwyg',
						'value' => 1,
					],	
				];
				$this->db
					->table($this->table_user .'meta')
					->insert($meta);
			}	
			if ($data['id'] AND empty($guest) ){
				if ($switch) {
					if ( empty($data['email']) AND empty($data['mobile']) ){
						$data['status'] = 'Incompleted'; //For CTA 
					}
					$this->is($data); //switch to newly created user
				}	
				//send right here instead of queuing as SSO to frontend may kick in before queue is run
				$notifier = new Notification($this->config, $this->dbm, $this->router, $this->view, $this->user);
				$notifier->sendMultiChannels($data, "Account Activation", 'Account_Activation', [
					'recipient' => ($data['name']??''),
					'cta_url'  => $this->config['system']['url'] . $this->config['system']['base_path'] .'?email='. $data['email'] .'&user_activate='. $this->hashify($data['email'] . $data['id'], 'static'), //nounce is not neccessary
				], 'email_only');
			}						
		} 
		return $data['id']??false;					
	}

	public function saveName($name) {
		if ( $this->user->isLoggedIn() ){
			$this->db
				->table($this->table_user)
				->where('id', $this->user->getId() )
				->update([
					'name' => $name, 
				]);
			//update Session
			$this->name = $_SESSION[ $this->passport ]['name'] = $name;
			return true;		
		}
		return false;
	}

	public function saveEmail($email) {
		$valid_email = filter_var($email, FILTER_SANITIZE_EMAIL);
		if ( $this->user->isLoggedIn() AND $valid_email ){
			try {
				$this->db
					->table($this->table_user)
					->where('id', $this->user->getId() )
					->update([
						'email' => $valid_email, 
					]);
				if ( !empty($_SESSION[ $this->passport ]['status']) AND $_SESSION[ $this->passport ]['status'] == 'Incompleted'){
					unset($_SESSION[ $this->passport ]['status']); //remove status as it is only set for Activation CTA	
				}	
				return true;	
			} catch (\Exception $e){
				
			}		
		}
		return false;
	}

	public function saveMobile($number) {
		$valid_number = filter_var($number, FILTER_SANITIZE_NUMBER_INT);
		if ( $this->user->isLoggedIn() AND $valid_number ){
			$this->db
				->table($this->table_user)
				->where('id', $this->user->getId() )
				->update([
					'mobile' => $valid_number, 
				]);
			if ( !empty($_SESSION[ $this->passport ]['status']) AND $_SESSION[ $this->passport ]['status'] == 'Incompleted'){
				unset($_SESSION[ $this->passport ]['status']); //remove status as it is only set for Activation CTA	
			}
			return true;		
		}
		return false;
	}

	public function getAddress($id = null, $single = false) {
		$addresses = [];
		if ( $this->user->isLoggedIn() ){
			$results = $this->db
				->table($this->site_prefix ."_page")
				->where('type', 'App')
				->where('subtype', 'Address')
				->where('creator', $id??$this->user->getId())
				->orderBy('updated','desc')
				->take($single? 1 : 5)
				->pluck('id')
				->all();
			if ($results){	
				$results = $this->db
					->table($this->site_prefix ."_pagemeta")
					->whereIn('page_id', $results) //no subquery because it does not work with LIMIT
					->orderBy('page_id', 'desc')
					->get(['page_id', 'property', 'value'])
					->all();
				foreach ($results??[] AS $r){
					$addresses[ $r['page_id'] ][ $r['property'] ] = $r['value'];
				}
			}	
		}	
		return $single? current($addresses) : array_values($addresses); //array_values to get the first address easier
	}

	public function saveAddress($address) {
		if ( $this->user->isLoggedIn() ){
			if ( !empty($address['street']) ){
				$data['name'] = json_encode([ 'en' => $address['street'] ]);
				$data['type'] ='App';
				$data['subtype'] = 'Address';
				$data['creator'] = $this->user->getId();
				//checking existing
				if ( ! $this->db
					->table($this->site_prefix ."_page")
					->where('type', $data['type'])
					->where('subtype', $data['subtype'])
					->where('creator', $data['creator'])
					->where('name', $data['name'])
					->first()
				){
					$data['created'] = $data['updated'] = time();
					$id = $this->db
						->table($this->site_prefix .'_page')
						->insertGetId($data);
					if ($id) {
						$sort = 0;
						if ( !empty($address['recipient']) OR !empty($address['name']) ){
							$meta[] = $id;
							$meta[] = 'recipient';
							$meta[] = $address['recipient']??$address['name'];
							$meta[] = $sort++;
						}
						if ( !empty($address['company']) ){
							$meta[] = $id;
							$meta[] = 'company';
							$meta[] = $address['company'];
							$meta[] = $sort++;
						}
						if ( !empty($address['phone']) OR !empty($address['mobile']) ){
							$meta[] = $id;
							$meta[] = 'phone';
							$meta[] = $address['mobile']??$address['phone'];
							$meta[] = $sort++;
						}
						$meta[] = $id;
						$meta[] = 'street';
						$meta[] = $address['street'];
						$meta[] = $sort++;
						if ( !empty($address['street2']) ){
							$meta[] = $id;
							$meta[] = 'street2';
							$meta[] = $address['street2'];
							$meta[] = $sort++;
						}
						if ( !empty($address['city']) ){
							$meta[] = $id;
							$meta[] = 'city';
							$meta[] = $address['city'];
							$meta[] = $sort++;
						}
						if ( !empty($address['state']) ){
							$meta[] = $id;
							$meta[] = 'state';
							$meta[] = $address['state'];
							$meta[] = $sort++;
						}
						if ( !empty($address['zip']) ){
							$meta[] = $id;
							$meta[] = 'zip';
							$meta[] = $address['zip'];
							$meta[] = $sort++;
						}
						if ( !empty($address['country']) ){
							$meta[] = $id;
							$meta[] = 'country';
							$meta[] = $address['country'];
							$meta[] = $sort++;
						}
						return $this->upsert($this->site_prefix .'_pagemeta', ['page_id', 'property', 'value', '`order`'], $meta, ['value']);
					}						
				}
			}
		}
		return false;	
	}
		
	protected function read($id, $column = 'id') {
		$user = $this->db->table($this->table_user) //table_user is already changed if $this->config['site']['user_site']
			->where($column, $id)
			->first();
		unset($user['password']);
		return $user;			  
	}

	public function canUse($username, $column = 'mobile') {
		if ( $column == 'mobile' AND filter_var($username, FILTER_VALIDATE_EMAIL) ){
			$column = 'email';
		}
		return (self::read($username, $column))? false : true;
	}

	public function meta($property = null) {
		$meta = $this->db
			->table($this->table_user .'meta')
			->where('user_id', $this->user->getId() )
			->when($property, function ($query) use ($property) {
				return $query->where('property', $property);
			})
			->pluck('value', 'property')
			->all();

		foreach( ($meta??[]) AS $key => $value) {
			$meta[$key] = json_decode($value??'', true)?? $value; //both json and string are ok, except null -- must use null coalescing operator 
		}
		return $property? ($meta[ $property ]??null) : ($meta??[]);	
	}

	public function getInfo($id = null, $column = 'id') {
		return $this->db->table($this->table_user)
			->where($column, $id??$this->user->getId() )
			->select('id', 'name', 'email', 'mobile', 'image', 'registered', 'status')
			->first();
	}

	//return group id => permissions instead of names
	public function getGroups($id = null){
		$table_group = str_replace('_user', '_config', $this->table_user);
		return $this->db
			->table($table_group .' AS group')
			->join( $table_group .' AS config', 'config.id', '=', 'group.property')
			->where('group.type', 'group')
			->where('group.object', $id??$this->user->getId())
			->where('group.value', 'Active')
			->where('config.type', 'db')
			->where('config.object', 'Group')
			->pluck('config.value AS permission', 'group.property AS id') //we just need permission, name provided by all groups
			->all();	
	}

	protected function prepareData($user) {
		unset($user['id']);
		foreach ($user AS $key => $value){
			if ($key == 'password' AND !empty($value)) {
				$data['password'] = password_hash($value, PASSWORD_DEFAULT);
			} elseif ( ($key == 'email' OR $key == 'username') AND filter_var($value, FILTER_VALIDATE_EMAIL) ) {
				$data['email'] = filter_var($value, FILTER_VALIDATE_EMAIL);
			} elseif ( ($key == 'mobile' OR $key == 'username') AND (is_numeric($value) OR empty($value)) ){
				$data['mobile'] = $value;
			} elseif ($key == 'image') {
				$data['image'] = filter_var(is_array($value)? $value[0] : $value, FILTER_VALIDATE_URL)? : '';
			} elseif ($key == 'status' AND in_array($value, ['Active', 'Inactive', 'Unverified'])) {
				$data['status'] = $value;
			} elseif ( in_array( $key, ['name', 'language', 'timezone', 'oauth_id', 'oauth_type', 'oauth_account'] ) ){
				$data[$key] = $value; 
			}
		}
		return $data;		
	}

####### End User trait, start User application #######
##### APP CONFIG #####
	public static function config($property = '') {
	    $config['app_visibility'] = 'staff';
	    $config['app_category'] = 'Management';   
	    $config['app_configs'] = [
	        'open_registration' => [
	            'label' => 'Open Registration',
	            'type' => 'checkbox',
	            'value' => '0',
	            'description' => 'User account can be created without buying a product or service',
	        ],
	        'email_optional' => [
	            'label' => 'Email Not Required',
	            'type' => 'checkbox',
	            'value' => '0',
	            'description' => 'Email is optional as messages including OTP are sent via mobile/app notification',
	        ],
	        'oauth' => [
	            'label' => 'Oauth Provider',
	            'type' => 'fieldset',
	            'size' => '6',
	            'value' => '',
	            'description' => 'This enables social login buttons such as Login with Google, Apple on the login screen. This requires a Developer license of the User Management app which you can purchase at https://app.sitegui.com/store/user',
	            'fields' => [
			        // a text field type allows for single line text input
			        'oauth_name' => [
			            'label' => 'Oauth Provider Name',
			            'type' => 'select',
			            'size' => '6',
			            'value' => '',
			            'description' => 'Select your Oauth Provider',
			            'options' => [
			            	'google' => 'Google', //use OAuth2 Service class name so it can be chosen automatically
			            	'facebook' => 'Facebook', 
			            	'microsoft' => 'Microsoft',//not work yet 
			            	'linkedin' => 'Linkedin',
			            	'github' => 'GitHub',
			            	'x' => 'X/Twitter',
			            	'tiktok' => 'Tiktok',
			            	'zalo' => 'Zalo',
			            ],
			        ],
			        'oauth_key' => [
			            'label' => 'Oauth ID',
			            'type' => 'text',
			            'size' => '6',
			            'value' => '',
			            'description' => 'Enter your Oauth Client ID here',
			        ],
			        'oauth_secret' => [
			            'label' => 'Oauth Secret',
			            'type' => 'password',
			            'size' => '6',
			            'value' => '',
			            'description' => 'Enter your Oauth Client Secret here',
			        ],
			        'enable' => [
			            'label' => 'Enable',
			            'type' => 'checkbox',
			            'size' => '6',
			            'value' => '',
			            'description' => '',
			        ],
	            ],
	        ],
	    ];

    	return ($property)? ($config[ $property ]??null) : $config;
	}

	public function install() {
		try {
			$query  = 'CREATE TABLE IF NOT EXISTS `'. $this->table_user .'` (
				`id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`oauth_id` TEXT,
				`oauth_type` TEXT,
				`oauth_account` TEXT, 
				`email` VARCHAR(191),
				`password` VARCHAR(191),
				`mobile` VARCHAR(18),
				`name` VARCHAR(191),
				`image` TEXT,
				`handle` VARCHAR(63),
				`language` CHAR(2),
				`timezone` VARCHAR(50),
				`registered` TEXT,
				`status` TEXT,
				UNIQUE INDEX email (email)
			) DEFAULT CHARACTER SET utf8mb4';
			$result = $this->db->statement($query);
			//We set initial value for user id to match site id 
			$query  = 'ALTER TABLE `'. $this->table_user .'` AUTO_INCREMENT='. (substr($this->site_id * 100000, 0, 9) + 1); 
			$result = $this->db->statement($query);

			$query  = 'CREATE TABLE IF NOT EXISTS `'. $this->table_user .'meta` (
				`id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`user_id` INT(1) UNSIGNED,
				`property` VARCHAR(191),
				`value` TEXT, 
				`name` TEXT,
				`description` TEXT,
				`amount` DECIMAL(12,2) NULL, 
				`order` INT(1),
				UNIQUE INDEX user_property (user_id, property) 
			) DEFAULT CHARACTER SET utf8mb4';
			$result = $this->db->statement($query);

			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	public function generateRoutes($extra = []) {
		$routes = $this->trait_generateRoutes($extra);

        $routes['SiteUser']['User::clientUpdate'] = ['POST', '/update.[json:format]?[POST:user]?'];
        $routes['SiteUser']['User::clientView']   = ['GET|POST',  '/view.[json:format]?'];

		return $routes;
	}	
##### END APP CONFIG #####

	/**
	* list site's users
	* @return none
	*/

	public function main() {
		if ( ! $this->user->has($this->requirements['MANAGE'])){
			$this->denyAccess('list');
		} elseif ( !empty($this->config['site']['user_site']) ){
			$user_site = $this->db
				->table($this->table_prefix .'_sites')
				->where('id', $this->config['site']['user_site'])
				->first();
			$uri = $this->slug('User::main', ["site_id" => $this->config['site']['user_site'] ]);	
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][ $uri ] = $this->trans('User account must be updated through ') . ($user_site['url']??'');
		} else {
			$query = $this->db->table($this->table_user)
				->select('id', 'name', 'email', 'mobile', 'oauth_type AS type', 'registered', 'status'); //->where('status', 'Paid')
			//override default search 	
			if ( !empty($_REQUEST['searchPhrase']) ){
				//$search = '%'. filter_var($_REQUEST['searchPhrase'], FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_HIGH) .'%';
				$search = '%'. trim(str_replace('\\', '\\\\', json_encode($_REQUEST['searchPhrase'])), '"') .'%'; //utf-8 search
				$query = $query->when( $search, function($query) use ($search) {
					if (str_contains($_REQUEST['searchPhrase'], '@')){
						$query = $query->where('email', 'LIKE', $search);
					} elseif (is_numeric($_REQUEST['searchPhrase']) ){
						$query = $query->where('mobile', 'LIKE', $search);
					} else {
						$query = $query->where('name', 'LIKE', $search);
					}
				});
				unset($_REQUEST['searchPhrase']);
			}
			$block = $this->pagination($query);
			if ( $block['api']['total'] ) {
				if ($this->view->html){				
					$block['html']['table_'] = 'users';
					$block['html']['table_header'] = [
						'id' => $this->trans('ID'), 
						'name' => $this->trans('Name'), 
						'email' => $this->trans('Email'), 
						'mobile' => $this->trans('Mobile'),
						'type' => $this->trans('Type'), 
						'registered' => $this->trans('Registered'), 
						'status' => $this->trans('Status'), 
						'action' => $this->trans('Action'),
					];
					$block['html']['column_type'] = ['registered' => 'date'];

					$links['api']   = $this->slug('User::main');
					$links['edit']   = $this->slug('User::action', ["action" => "edit"] );
					$links['delete'] = $this->slug('User::action', ["action" => "delete"] );
					$block['links'] = $links;					
					$block['template']['file'] = "datatable"; 
				}
			} else {
				$status['result'] = "error";
				$status['message'][] = $this->trans('We have yet to have any :item', ['item' => 'User']);
				
				if ($this->view->html){				
					$status['html']['message_type'] = 'info';
					$status['html']['message_title'] = $this->trans('Information');	
				}
			}			

			$this->view->addBlock('main', $block, 'User::main'); //substr(__METHOD__, strlen(__NAMESPACE__) + 1);
		}						
		!empty($status) && $this->view->setStatus($status);							
	}

	public function showLoginForm() {
		foreach ( $this->oauthProviders() AS $spid => $oa ){
			$block['api']['oauth'][ $spid ] = $oa['name'];
		}

     	$block['template']['file'] = 'user_login';
     	$this->view->setLayout('blank');
     	$status['code'] = 401; 
     	$status['result'] = 'error';
     	if ($this->view->html){ 
			stream_context_set_default([
				'ssl' => [
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			    ],
			    'http' => [
			        'method' => 'HEAD'
			    ],
			]);
			//$unsplash = "https://source.unsplash.com/collection/wallpapers/1920x1080/?nature,landscape,tree";
			//$headers = get_headers($unsplash, true);
	      	//$status['html']['background'] = ($headers['Location'])? $headers['Location'] : $unsplash; 			
			$unsplash = "https://api.unsplash.com/photos/random?collections=162468&orientation=landscape&client_id=6fa91622109e859b1c40218a5dead99f7262cf4f698b1e2cb89dd18fc5824d15"; //client_id from postman unsplash
			$unsplash = json_decode($this->httpGet($unsplash, null)??'');
	        $status['html']['background'] = ($unsplash?->urls?->raw)? $unsplash->urls->raw .'&w=1920&h=1080&crop=entropy' : 'https://images.unsplash.com/photo-1543039625-14cbd3802e7d?ixid=MnwxOTAzOTR8MHwxfHJhbmRvbXx8fHx8fHx8fDE2MzYwMTg5OTc\u0026ixlib=rb-1.2.1&w=1920&h=1080&crop=entropy';

	     	$status['html']['title'] = $this->trans('Login required'); 
	     	$block['html']['request'] = ($_POST??[]) + ($_GET??[]); //exclude $_COOKIE, post is preferred
	    	if ( !empty(self::getMyConfig('open_registration')) ){
	    		$block['html']['request']['user_register'] = 1; //show Register button
	    	} 
	     	if ( empty($_SESSION[ $this->passport ]['auth_request_from']) AND strpos($_SERVER['SERVER_NAME'], parse_url($this->config['system']['url'], PHP_URL_HOST)) !== false ){ //set for main system URL only, edit_url may redirect unnecessary
	        	$_SESSION[ $this->passport ]['auth_request_from'] = str_replace('logout=', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) );
	      	}	
     	} else {//api only
     		$status['message'][] = $this->trans('Login required'); 
     	}
     	!empty($status) && $this->view->setStatus($status);
     	$this->view->addBlock('main', $block, 'User::login');     
	}

	public function reloadName(){
		//force reloading user name next time
		unset($_SESSION[ $this->passport ]['name']);
	}
	
	public function update($user, Group $groupObj = null) {
		if ( ! ($this->user->has($this->requirements['MANAGE']) OR 
			  ( $this->user->getId() == intval($user['id']) AND ($this->config['site']['id'] == 1 OR !empty($user[ 'isAdmin_'. $this->config['salt'] ])) ) ) 
		){ //Allow System user to update their own account
			$this->denyAccess('update');
			return false;
		} 
		$error = 0; //use to count error when updating groups only, 
		if ( !empty($this->config['site']['user_site']) ){
			$user_site = $this->db
				->table($this->table_prefix .'_sites')
				->where('id', $this->config['site']['user_site'])
				->first();
			$uri = $this->slug('User::action', ['site_id' => $this->config['site']['user_site'], 'action' => 'edit/'. ($user['id']??'') ]);	
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][ $uri ] = $this->trans('User account must be updated through ') . ($user_site['url']??'');
		} elseif (!empty($user['password']) AND $user['password'] != $user['password2']) {
			$status['result'] = 'error';
			$status['code'] = 403;
			$status['message'][] = $this->trans('Passwords do not match. Please enter correctly');		
		} else {
			$this->runHook($this->class .'::'. __FUNCTION__, [ &$user ]);

			if (empty($user['id'])) { // create a new user	
				if ($user['id'] = self::create($user)) { 
					$status['message'][] = $this->trans(':item added successfully', ['item' => 'User']);						
				} else {
					$status['result'] = 'error';
					$status['code'] = 403;
					$status['message'][] = $this->trans(':item was not added', ['item' => 'User']);						
				}
			} else {
				$data = self::prepareData($user);
				$user_info = $this->read($user['id']);
				if ( !empty($data['email']) AND $data['email'] != $user_info['email'] AND !$this->canUse($data['email'], 'email') ){
					$status['result'] = 'error';
					$status['code'] = 409;
					$status['message'][] = $this->trans('Email address is already registered to another account');	
				} else {
					if ($this->user->getId() == intval($user['id']) AND ($this->config['site']['id'] == 1 OR ($this->config['site']['user_site']??null) == 1 OR !empty($user[ 'isAdmin_'. $this->config['salt'] ]))) { 
						//System user should not update his status, isAdmin_salt can be set correctly by Staff update only
						unset($data['status']);
						$db = $this->dbm->getConnection('central');
					} else {
						$db = $this->db;
					}	

					if ($db->table($this->table_user)
						->where('id', $user['id'])
						->update($data) 
					) {
						$status['message'][] = $this->trans(':item updated successfully', ['item' => $this->class ]);	
						$this->reloadName();
					} else {
						$error++;				
					}
				}	
			}
			//update group 
			if ( !empty($user['id']) AND (empty($status['result']) OR ($status['result'] != 'error') ) ){
				//check valid groups first
				if (empty($user['groups']) OR !is_array($user['groups'])){
					$user['groups'] = []; //make sure it is array
				}
				if ($groupObj && $groupObj->updateUserGroups($user['id'], $user['groups'], 'Active', 'remove_others') ){
					$error--;
					$status['message'][] = $this->trans(':item updated successfully', ['item' => $this->class ]);
				}
			}
		}
		if ($error > 0){
			$status['result'] = 'error';
			$status['code'] = 403;
			$status['message'][0] = $this->trans(':item was not updated', ['item' => 'User']);	
		}

		!empty($status) && $this->view->setStatus($status);							
		$this->logActivity( implode('. ', $status['message']??[]), $this->class .'.', $user['id']);

		if ($this->view->html AND empty($user[ 'isAdmin_'. $this->config['salt'] ])) {				
			$next_actions[ $this->class .'::edit'] = [$user['id']];					
		}
		if (!empty($next_actions)){
			$this->router->registerQueue($next_actions);	
		}		
	}

	public function clientUpdate($user) {
		if ( empty($user['id']) OR $this->user->getId() != intval($user['id']) ){ //Allow user to update their own account
			$this->denyAccess('update');
		} 

		$user_info = $this->read($user['id']);
		if ( empty($user_info) OR (!empty($user_info['status']) AND $user_info['status'] != 'Active' AND $user_info['status'] != 'Unverified') ){
			$status['result'] = 'error';
			$status['code'] = 401;
			$status['message'][] = $this->trans('The account is not found or active');						
		} elseif (!empty($user['password']) AND $user['password'] != $user['password2']) {
			$status['result'] = 'error';
			$status['code'] = 403;
			$status['message'][] = $this->trans('Passwords do not match. Please enter correctly');		
		} else {
			//handle once at the first run to correctly map attachments to fields 
			//handle files upload - file will be prefixed with folder name e.g: 213 = 21Q3 so we can store the file name only
			if ( !empty($_FILES) ){
				$this->prepareUploads($_FILES, $user, null, 'public', $this->config['site']['user_site']??null); //store in public folder (at $this->config['site']['user_site'] if present)
				unset($_FILES); //prevent sub process to run this again
			}

			$data = self::prepareData($user);
			unset($data['status']);
			if ( !empty($data['email']) AND $data['email'] != $user_info['email'] ){
				$update_email = true;
			} 

			if ( !empty($update_email) AND !$this->canUse($data['email'], 'email') ){
				$status['result'] = 'error';
				$status['code'] = 409;
				$status['message'][] = $this->trans('Email address is already registered to another account');	
			} else {
				if ( empty(self::getMyConfig('email_optional')) ){
					if ( !empty($update_email) ){
						$data['status'] = 'Unverified'; //set it to Unverified and force user to verify when login next time
					} elseif ( empty($user_info['email']) ){
						$status['result'] = 'error';
						$status['message'][] = $this->trans(':Item is required', ['item' => 'Email']);
						unset($data);	
					}
				} elseif ( empty($user_info['mobile']) AND empty($data['mobile']) ){
					$status['result'] = 'error';
					$status['code'] = 403;
					$status['message'][] = $this->trans(':Item is required', ['item' => 'Mobile']);
					unset($data);	
				}	

				if ( !empty($data) AND $this->db->table($this->table_user)
					->where('id', $user['id'])
					->update($data) 
				) {
					$status['message'][] = $this->trans(':item updated successfully', ['item' => 'Account' ]);
					if ( !empty($this->config['site']['user_site']) ){ //allow user to self update - better UX
						$user_site = $this->db
							->table($this->table_prefix .'_sites')
							->where('id', $this->config['site']['user_site'])
							->first();
						$status['html']['message_title'] = $this->trans('Information');
						$status['message'][] = $this->trans('Your account is also updated on :site and its related sites', ['site' => $user_site['url']??'' ]);
					} 

					$this->reloadName();
					if ( !empty($data['status']) AND $data['status'] == 'Unverified' ){//send verification email
						$next_actions['Notification::sendMultiChannels'] = [
							"users" => $data, 
							"subject" => "Email Verification",
							"file" => "Email_Verification",
							"data" => [
								'recipient' => ($data['name']??''),
								'cta_url'  => $this->config['system']['url'] . $this->config['system']['base_path'] .'?email='. $data['email'] .'&user_activate='. $this->hashify($data['email'] . $user['id'], 'static'), //nounce is not neccessary
							],
							'email_only'	
						];
						$this->router->registerQueue($next_actions);
						$_SESSION[ $this->passport ]['status'] = 'Unverified';
						$status['message'][] = $this->trans('Your email address has not been verified. Please check your email and follow the instruction to verify it');
					} elseif ( !empty($_SESSION[ $this->passport ]['status']) AND (!empty($data['email']) OR !empty($data['mobile']) ) ){
						unset($_SESSION[ $this->passport ]['status']); //remove status as it is only set for Activation CTA
					}	
				} else {
					$status['result'] = 'error';
					$status['code'] = 403;
					$status['message'][] = $this->trans(':item was not updated', ['item' => 'Account']);
				}
			}	
		}

		!empty($status) && $this->view->setStatus($status);							
		$this->logActivity( implode('. ', $status['message']), $this->class .'.', $user['id']);

		if ($this->view->html ){				
			$next_actions[ $this->class .'::clientView'] = [$user['id']];					
		}
		if (!empty($next_actions)){
			$this->router->registerQueue($next_actions);	
		}		
	}

	public function edit($id = 0) {
		if ( ! $this->user->has($this->requirements['MANAGE']) ){
			$this->denyAccess($id? 'edit' : 'create');
		// read is allowed to quickly show user info} elseif ( !empty($this->config['site']['user_site']) ){
		//	$status['html']['message_title'] = $this->trans('Information');
		//	$status['message'][] = $this->trans('User account must be updated through ') . $this->config['site']['user_site'];
		} else {
			$table_group = str_replace('_user', '_config', $this->table_user);
			if($id > 0){
				$user_info = $this->read($id);
				if($user_info){
					if ($this->checkActivation(__NAMESPACE__ .'\\Order', true) ){
						$block['api']['app']['sub']['Orders'] = [
							'display' => 'editable',
							'link_api' => $this->slug('Order::main') .'.json?html=1&user='. $id,
							//'link_edit' => $this->slug('Order::action', ['action' => 'edit']) .'?sgframe=1&user='. $id,
						];
					}	
					if ($this->checkActivation(str_replace('Core', 'App', __NAMESPACE__) .'\\Ticket', true) ){
						$block['api']['app']['sub']['Tickets'] = [
							'display' => 'editable',
							'link_api' => $this->slug('App::main', ['app' => 'ticket']) .'.json?html=1&user='. $id,
							'link_edit' => $this->slug('App::action', ['action' => 'edit', 'id' => 'ticket']) .'?sgframe=1&user='.  $id,
						];						
					}

					if ($this->checkActivation(__NAMESPACE__ .'\\Wallet', true) ){
						$block['api']['app']['sub']['Wallets'] = [
							'display' => 'readonly',
							'link_api' => $this->slug('Wallet::main') .'.json?html=1&user='. $id,
						];
					}
					if ($this->checkActivation(__NAMESPACE__ .'\\Invoice', true) ){	
						$block['api']['app']['sub']['Invoices'] = [
							'display' => 'editable',
							'link_api' => $this->slug('Invoice::main') .'.json?html=1&user='. $id,
							'link_edit' => $this->slug('Invoice::action', ['action' => 'edit']) .'?sgframe=1&user='. $id,
						];
					}
					if ($this->checkActivation(__NAMESPACE__ .'\\Transaction', true) ){	
						$block['api']['app']['sub']['Transactions'] = [
							'display' => 'editable',
							'link_api' => $this->slug('Transaction::main') .'.json?html=1&user='. $id,
							'link_edit' => $this->slug('Transaction::action', ['action' => 'edit']) .'?sgframe=1&user='. $id,
						];
					}
					if ($this->checkActivation(str_replace('Core', 'App', __NAMESPACE__) .'\\Address', true) ){	
						$block['api']['app']['sub']['Address'] = [
							'display' => 'editable',
							'link_api' => $this->slug('App::main', ['app' => 'address']) .'.json?html=1&user='. $id,
							'link_edit' => $this->slug('App::action', ['action' => 'edit', 'id' => 'address']) .'?sgframe=1&user='. $id,
						];
					}
					$block['html']['ajax'] = 1; //enable ajax loading for subapp
					$block['system']['data_onclick'] = true;
					//$app = $this->getAppInfo('User', 'Core');
					//$this->editSubApps(null, $app, false, null, null);
					$user_info['groups'] = $this->getGroups($user_info['id']);
					$block['api']['user'] = $user_info;
				} else {
					$status['result'] = 'error';
					$status['code'] = 403;
					$status['message'][] = $this->trans('No such :item', ['item' => $this->class ]);
				}
			}
			$block['api']['groups'] = $this->db
				->table($table_group)
				->where('type', 'db')
				->where('object', 'Group')
				->pluck('property AS name', 'id')
				->all() ?: [ $this->trans("Please create client groups first") ];

			if ($this->view->html){	
				$block['template']['file'] = "user_edit";		
				$links['update'] = $this->slug($this->class .'::update');
				$links['main']   = $this->slug($this->class .'::main');
				$links['group']  = $this->slug('Group::action', ['action' => 'edit']);
				$links['lookup']  = $this->slug('Lookup::now');
				if ( !empty($this->config['system']['sgframe']) ){
					$links['update'] .= '?sgframe=1';
					$links['main']   .= '?sgframe=1';
				}
		
				$block['links'] = $links;
				$block['html']['languages'] = $this->getLanguages();
				$block['html']['timezones'] = $this->getTimezones();

				if ((empty($user_info) AND !empty($_REQUEST['user']))) { //create user failed, populate with submitted info
					$block['api']['user'] = $_REQUEST['user'];	
				}
				if ( !empty(self::getMyConfig('email_optional')) ){
		    		$block['html']['email_optional'] = 1;
		    	} 
			}

			$this->view->addBlock('main', $block, $this->class .'::edit');

			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}	
			!empty($status) && $this->view->setStatus($status);							
			$this->runHook($this->class .'::'. __FUNCTION__, [ $user_info??null ]);
		}					
	}

	public function clientView() {
		if ( $this->user->getId() ){
			$user_info = $this->read($this->user->getId());
			if($user_info){
				$block['api']['app']['sub']['Address'] = [
					'display' => 'editable',
					'link_api'  => $this->slug('App::clientMain', ['app' => 'address']) .'.json?html=1',
					'link_edit' => $this->slug('App::clientView', ['id' => 'address']) .'?sgframe=1',
				];
				$block['html']['ajax'] = 1; //enable ajax loading for subapp
				$block['system']['data_onclick'] = true;
				//$app = $this->getAppInfo('User', 'Core');
				//$this->editSubApps(null, $app, false, null, null);

				$block['api']['user'] = $user_info;
				//$this->registerHook($this->class .'::clientViewed', [ $user_info['id'] ]);
			} else {
				$status['result'] = 'error';
				$status['code'] = 403;
				$status['message'][] = $this->trans('The account cannot be located');
			}

			if ($this->view->html){	
				$block['template']['file'] = "user_edit";		
				$links['update'] = $this->slug($this->class .'::clientUpdate');
				$links['update'] .= !empty($this->config['system']['sgframe'])? '?sgframe=1' : '';
		
				$block['links'] = $links;
				$block['html']['languages'] = $this->getLanguages();
				$block['html']['timezones'] = $this->getTimezones();
				if ( !empty(self::getMyConfig('email_optional')) ){
		    		$block['html']['email_optional'] = 1;
		    	} 
			}

			$this->view->addBlock('main', $block, $this->class .'::clientView');

			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}	
		}					
		!empty($status) && $this->view->setStatus($status);							
		$this->runHook($this->class .'::'. __FUNCTION__, [ $user_info??null ]);
	}

	public function delete($id) {
		if ( ! $this->user->has($this->requirements['MANAGE']) ){
			$this->denyAccess('delete');
		} elseif ( !empty($this->config['site']['user_site']) ){
			$user_site = $this->db
				->table($this->table_prefix .'_sites')
				->where('id', $this->config['site']['user_site'])
				->first();
			$uri = $this->slug('User::main', ["site_id" => $this->config['site']['user_site'] ]);	
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][ $uri ] = $this->trans('User account must be updated through ') . ($user_site['url']??'');
		} else {
			if ($this->user->getId() == intval($id) AND $this->config['site']['id'] == 1) { //System user should not delete his own account
				$status['result'] = 'error';
				$status['code'] = 403;
				$status['message'][] = $this->trans('Self delete is not allowed');		
			} else {
				if ($this->db->table($this->table_user)->delete($id)) {
					$this->deleteMeta($id);
					$status['result'] = 'success';
					$status['message'][] = $this->trans(':item deleted successfully', ['item' => $this->class ]);			
					$this->runHook($this->class .'::deleted', [ $id ]);
				} else {
					$status['result'] = 'error';
					$status['code'] = 403;
					$status['message'][] = $this->trans(':item was not deleted', ['item' => 'User']);				
				}
			}

			$status['api_endpoint'] = 1;
			$this->logActivity( implode('. ', $status['message']), $this->class .'.', $id);

			if ($this->view->html) {				
				$next_actions[ $this->class .'::main'] = [];					
			}
			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}
		}									
		!empty($status) && $this->view->setStatus($status);							
	}

	//delete user meta
	protected function deleteMeta($id) {
		return $this->db
			->table($this->table_user .'meta')
			->where('user_id', $id)
			->delete();
	}
}