<?php
namespace LiteGUI;

class User{
    use Traits\Helper;
	protected $passport; //should be injected by controller
	protected $id = 0;
	protected $type; //authentication type: Self, Google, FB, Github
	protected $name = 'Guest';
	protected $roles = ['guest']; //can be multiple role
	protected $staff = false;
	protected $db;
	protected $config;
	protected $permissions = [];

	public function __construct($config = NULL, $dbm = NULL, $passport = 'user'){
		$this->passport = $passport;
		if (!empty($config)) {
			$this->config = $config;
		}
		// db is not required all the time	
		if (!empty($dbm)) {
			$this->db = $dbm->getConnection();
		}
		//Set user id if session exists, otherwise call authenticate() to authenticate user 
		if (!empty($_SESSION[ $this->passport ]['id'])) { //user already logged in
			$this->is($_SESSION[ $this->passport ]); 	
		}
	}
	//Oauth Provider config
	public function oauthConfig($provider = null){
	    $providers = [
        	'google' => [
        		'auth_url' => 'https://accounts.google.com/o/oauth2/auth?'.
        			'client_id={client_id}'.
        			'&redirect_uri={redirect_uri}'.
        			'&response_type=code'.
        			'&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email'.
        			'%20https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile'. 
        			'&access_type=online'.
        			'&include_granted_scopes=true'.
        			'&state={state}',
				'token_url' => 'https://oauth2.googleapis.com/token',
				'token_params' => 
					'client_id={client_id}'.
					'&client_secret={client_secret}'.
					'&code={code}'.
					'&grant_type=authorization_code'.
					'&redirect_uri={redirect_uri}',
        		'info_url' => 'https://www.googleapis.com/oauth2/v3/userinfo',
        		'info_headers' => 'Authorization: Bearer {access_token}',
        		'map' => [
        			'id' => 'sub',
        			'image' => 'picture',
        		],
        	],
        	'apple' => [ // https://developer.apple.com/documentation/signinwithapplerestapi/generate_and_validate_tokens
        	//https://developer.okta.com/blog/2019/06/04/what-the-heck-is-sign-in-with-apple
        		'auth_url' => 'https://appleid.apple.com/auth/authorize?'.
        			'client_id={client_id}'.
        			'&redirect_uri={redirect_uri}'.
        			'&response_type=code'.
        			'&scope=name%20email'. 
        			'&state={state}',
				'token_url' => 'https://appleid.apple.com/auth/token',
				'token_params' => 
					'client_id={client_id}'.
					'&client_secret={client_secret}'.
					'&code={code}'.
					'&grant_type=authorization_code'.
					'&redirect_uri={redirect_uri}',
				'token_headers' => 'Accept: application/json'.
					'&User-Agent: SiteGUI',
        		'map' => [
        			'id' => 'sub',
        			'image' => 'picture',
        		],
        	],
        	'facebook' => [
        		'auth_url' => 'https://www.facebook.com/v19.0/dialog/oauth?'.
        			'client_id={client_id}&redirect_uri={redirect_uri}&state={state}&scope=email',
				'token_url' => 'https://graph.facebook.com/v19.0/oauth/access_token?'.
					'client_id={client_id}'.
					'&redirect_uri={redirect_uri}'.
					'&client_secret={client_secret}'.
					'&code={code}',
				'token_request' => 'GET',
        		'info_url' => 'https://graph.facebook.com/me?access_token={access_token}&fields=email,name,picture',
				'info_request' => 'GET',
        		'map' => [
        			'image' => 'picture__url',
        		], 
        	],	
        	'microsoft' => [ //does not work with microsoft account
        		'auth_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?'.
            		'client_id={client_id}'.
            		'&redirect_uri={redirect_uri}'.
            		'&state={state}&scope=https%3A%2F%2Fgraph.microsoft.com%2Fmail.read'.
            		'&response_type=code&response_mode=query',
				'token_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
				'token_params' => 
					'client_id={client_id}'.
					'&redirect_uri={redirect_uri}'.
					'&client_secret={client_secret}'.
					'&scope=https%3A%2F%2Fgraph.microsoft.com%2Fmail.read'.
					'&grant_type=authorization_code'.
					'&code={code}',
        		'info_url' => 'https://graph.microsoft.com/oidc/userinfo',
				'info_headers' => 'Authorization: Bearer {access_token}',
        		'map' => [
        			'id' => 'sub',
        			'image' => 'picture',
        		], 
        	], 
        	'github' => [
        		'auth_url' => 'https://github.com/login/oauth/authorize?'.
        			'client_id={client_id}&redirect_uri={redirect_uri}&state={state}&scope=user:email',
				'token_url' => 'https://github.com/login/oauth/access_token',
				'token_params' => 'client_id={client_id}'.
					'&redirect_uri={redirect_uri}'.
					'&client_secret={client_secret}'.
					'&code={code}',
				'token_headers' => 'Accept: application/json&User-Agent: SiteGUI',
        		'info_url' => 'https://api.github.com/user',
        		'info_headers' => 'Authorization: Bearer {access_token}'.
        			'&Accept: application/json&User-Agent: SiteGUI',
				'info_request' => 'GET',
        		'map' => [
        			'image' => 'avatar_url',
        		],	
        	],
        	'x' => [ //https://developer.x.com/en/docs/authentication/oauth-2-0/user-access-token	
        		'auth_url' => 'https://twitter.com/i/oauth2/authorize?'.
        			'response_type=code&scope=users.read%20tweet.read'.
        			'&client_id={client_id}&redirect_uri={redirect_uri}&state={state}'.
        			'&code_challenge={code_challenge}&code_challenge_method=plain',
				'token_url' => 'https://api.twitter.com/2/oauth2/token',
				'token_params' => 'redirect_uri={redirect_uri}'.
					'&client_id={client_id}'.
					'&code={code}'.
					'&grant_type=authorization_code'.
					'&code_verifier={code_challenge}',
				//'token_headers' => 'Authorization: Basic Tmx=',//public client as Basic Auth doesnt work	
        		'info_url' => 'https://api.twitter.com/2/users/me?user.fields=id,name,profile_image_url',
        		'info_headers' => 'Authorization: Bearer {access_token}',
				'info_request' => 'GET',
        		'map' => [
        			'id' => 'data__id',
        			'name' => 'data__name',
        			'email' => 'data__email',
        			'image' => 'data__profile_image_url',
        		],
        	],
        	'tiktok' => [ //https://developer.x.com/en/docs/authentication/oauth-2-0/user-access-token	
        		'auth_url' => 'https://www.tiktok.com/v2/auth/authorize/?'.
        			'response_type=code&scope=user.info.basic'.
        			'&client_key={client_id}&redirect_uri={redirect_uri}&state={state}',
				'token_url' => 'https://open.tiktokapis.com/v2/oauth/token/',
				'token_params' => 'redirect_uri={redirect_uri}'.
					'&client_key={client_id}'.
					'&client_secret={client_secret}'.
					'&code={code}'.
					'&grant_type=authorization_code',
        		'info_url' => 'https://open.tiktokapis.com/v2/user/info/?fields=open_id,display_name,avatar_url',
        		'info_headers' => 'Authorization: Bearer {access_token}',
				'info_request' => 'GET',
        		'map' => [
        			'id' => 'data__user__open_id',
        			'name' => 'data__user__display_name',
        			'image' => 'data__user__avatar_url',
        		],
        	],
        	'linkedin' => [ 
        		'auth_url' => 'https://www.linkedin.com/oauth/v2/authorization?'.
        			'client_id={client_id}&redirect_uri={redirect_uri}&state={state}&response_type=code&scope=openid%20email%20profile',
				'token_url' => 'https://www.linkedin.com/oauth/v2/accessToken',
				'token_params' => 'redirect_uri={redirect_uri}'.
					'&client_id={client_id}'.
					'&client_secret={client_secret}'.
					'&code={code}'.
					'&grant_type=authorization_code',
        		'info_url' => 'https://api.linkedin.com/v2/userinfo',
        		'info_headers' => 'Authorization: Bearer {access_token}',
				'info_request' => 'GET',
        		'map' => [
        			'id' => 'sub',
        			'image' => 'picture',
        		],	
        	],
        	'zalo' => [ //work inside VN only
        		'auth_url' => 'https://oauth.zaloapp.com/v4/permission?'.
        			'app_id={client_id}&redirect_uri={redirect_uri}&state={state}',//&code_challenge={code_challenge}',
				'token_url' => 'https://oauth.zaloapp.com/v4/access_token',
				'token_params' => 'app_id={client_id}'.
					'&code={code}'.
					'&grant_type=authorization_code',
				'token_headers' => 'secret_key:{client_secret}',
        		'info_url' => 'https://graph.zalo.me/v2.0/me?fields=id,name,picture',
        		'info_headers' => 'access_token: {access_token}',
				'info_request' => 'GET',
        		'map' => [
        			'image' => 'picture__data__url',
        		],	
        	],
        ];

        return ($provider)? ($providers[ $provider ]??null) : $providers;
	}
	//$user->authenticate->logout if instructed
	//					 ->verifyLogin->is known as specified user
	//	   				 ->showLoginForm if login is unsucessful
	//					 ->loadPermissions for identified site
	//get login information and verify against database
	public function authenticate() {	
        //Must be BEFORE authentication check so when user logs out, login is required immediately otherwise it is only required next visit.
        if ($_GET['logout'] === 'true') {
        	$this->id = 0;
            unset($_SESSION[ $this->passport ]);
            session_destroy();
        } 

		if (!empty($_SESSION[ $this->passport ]['id'])) { 
			//now invoked at __construct $this->is($_SESSION[ $this->passport ]); //user already logged in, set $user->id 	
		} elseif (!empty($_POST['username']) AND !empty($_POST['password'])) { //User login submitted via form post
			//we may check submited info with what stored in $this->passport table if separate user table required
			$provided['username'] = $_POST['username'];
			$provided['password'] = $_POST['password'];			
			$this->verifyLogin($provided);
		} 

		//if no valid user at this point, show login form and stop immediately
        if (empty($_SESSION[ $this->passport ]['id'])){
			$this->showLoginForm();
            $this->view->render();
            exit();
        }  
	
		//If site_id has been identified, let's access and see what role and permission the user has
		if (!empty($this->config['site']['id'])) {
			$this->loadPermissions($this->config['site']['id']);
		}		
	}

	// we process login here
	protected function verifyLogin($provided) {
		//This is sample check, this should be done by child class
		//if username and password provided by login form
		if ( !empty($provided['username']) AND !empty($provided['password']) )	{
			//use password_verify to actually verify the submited password matches hash stored in db
			//load stored user['id','name', 'password'] to $stored
			if (password_verify($provided['password'], $stored['password'])) {
				$this->is($stored);
			} else {
				$status['result'] = "error";
				$status['message'][] = $this->trans('Incorrect Password');
			}			  
		} else if ( !empty($provided['type']) AND !empty($provided['id']) ) { //oauth login
			//get user id from user table or reuse oauth id if we don't have our own user table 
			$this->is($provided);				
		}	
	}

	//Hi, my name is ... This sets user identity after it is verified
	protected function is($user) {
		if (!empty($user['id'])){
			//prevent session fixation anyway
			if (empty($_SESSION[ $this->passport ]['id'])) {
				session_regenerate_id(true);
			}
			$this->id = $_SESSION[ $this->passport ]['id']   = $user['id'];
		}	
		if (!empty($user['type'])){
			$this->type = $_SESSION[ $this->passport ]['type'] = $user['type'];
		}
		if (isset($user['name'])){
			$this->name = $_SESSION[ $this->passport ]['name'] = $user['name'];
		}
		if (isset($user['image'])){
			$_SESSION[ $this->passport ]['image'] = $user['image'];
		}
		if (isset($user['language'])){
			$_SESSION[ $this->passport ]['language'] = $user['language'];
		}
		if (isset($user['timezone'])){
			$_SESSION[ $this->passport ]['timezone'] = $user['timezone'];
			$user['timezone'] && date_default_timezone_set($user['timezone']);    
		}
	}

	//I want to access this site, I am this role and have the following permissions
	protected function loadPermissions($site_id) {
		if (!empty($this->db) AND !empty($this->id) AND !empty($site_id)) {
			//implement it in child class
		}					
	}

	public function isLoggedIn() {
		return !empty($this->id)? true : false;
	}
	public function getName(){
		return $this->name;
	}
	public function getId(){
		return $this->id;
	}
	public function getType(){
		return $this->type;
	}
	public function getLanguage(){
		return $_SESSION[ $this->passport ]['language']??null;
	}
	public function getRoles(){
		return $this->roles;
	}
	public function isStaff(){
		return $this->staff;
	}
	public function showLoginForm(){

	}	
	// return all permissions I have
	public function getPermissions(){
		return $this->permissions;
	}
	
	/**
	* returns true if the user represented by the object has the permission to do the action(s)
	* given as a param.
	* @param permission mixed a string holding a single permission name, or an array of strings, each holding a permission name
	* @param and_or bool if true all permissions must be granted to the user. If false, any of them is sufficient
	* @return boolean true if the user can, false if he can't
	*/

	public function has( $permission, $and_or = "AND" ) {
		/* 
		  if an empty string or array is given, it may well be that an
		  abstraction method was used to check for permission, and we're in the
		  case where no permission is required to perform the subsequent action
		  WE HAVE TO return false here in case the permission isn't defined
		 */ 

		if ( empty( $permission ) ) return false;

		/* if $this->permissions isn't set, this method has not been called yet
		  for that script */

		if ( empty( $this->permissions ) ) {
		    /*
		     fetch permissions for this user from the database, and set the
		     User::permissions property, to allow caching.
		     User::permissions will be an array with permission names as keys,
		     and 1s as values.
		     */
		}

		/*
		 If a single permission is requested, as a string, just check whether
		 or not it's a key of the User::permissions array
		 All keys in User::permissions have been granted to the user, and have
		 1 as an attached value
		 All ungranted permissions do not appear as keys of User::permissions
		 */

		if ( is_string($permission) OR is_int($permission) ) {
		    return array_key_exists( $permission, $this->permissions );
		} else {
			/*
			 If not, we'll have to check for all of them, and AND/OR them,
			 depending on the value of the second, optional parameter.
			 First, we'll need an array with requested permissions as keys, and 0s
			 as values, in which we will set 1s for every granted permission.
			 Easy way to do so is to use array_intersect_key, getting the value of
			 each key of $this->permissions that is present in $permission, then
			 simply adding an array_fill_keys version of $permission.
			 */

			$checked_permissions = array_intersect_key($this->permissions, array_fill_keys($permission, 0)) 
									+ array_fill_keys($permission,0);

			/*
			  Now we will need array_sum if any of the requested permissions is
			  sufficient (giving a non-null sum if one of them at least is granted)
			  or array_product if all of them are needed (giving a null product if
			  one of them at least isn't granted)
			  */

			return (strtoupper($and_or) == 'ANY')? (bool) array_sum($checked_permissions) : (bool) array_product($checked_permissions);
		}	
	}			
}