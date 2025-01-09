<?php
namespace SiteGUI\Core;

class Staff extends User {
	protected $table_staff;

	public function __construct($config, $dbm, $router, $view, $passport = 'staff') {
		parent::__construct($config, $dbm, $router, $view, $passport);
		$this->table_user    = $this->table_prefix .'1_user'; //mysite1_user
		$this->table_staff   = $this->table_prefix ."_admin";

		$this->requirements['SystemAdmin'] = "Role::SystemAdmin"; 	//Do anything
		$this->requirements['ApiUser'] = "Role::ApiUser"; 	//API access
		$this->requirements['MANAGE'] = "Role::SiteManager"; 	//Manage/own site
		//Staff's id hasn't been set at this point, must call authenticate() to get set 
	}

####### This section is for User trait, User application's methods are at the end #######	
	public function authenticate() {
		//Staff invitation
      	if (isset($_REQUEST['user_invite'])) {
			//store it and authenticate/register account first
			$_SESSION[ $this->passport ]['user_invite'] = $_REQUEST['user_invite'];
		}

		parent::authenticate();

		//now we can process staff invitation
		if ($this->user->getId() AND isset($_SESSION[ $this->passport ]['user_invite'])) {
			$this->welcome($_SESSION[ $this->passport ]['user_invite']);
			unset($_SESSION[ $this->passport ]['user_invite']);
		}

		//Must be AFTER authentication check so user is allowed onetime only, authentication is required on the next visit.
		if (strpos($_SERVER['SERVER_NAME'], parse_url($this->config['system']['edit_url'], PHP_URL_HOST)) !== false) { //onetime token based access, user has been identified -> remove session so it's really used once.
		   unset($_SESSION[ $this->passport ]);
		   session_destroy();
		}		
	}	
	//we need this as $this->table_user has not been set to mysite1_user when is() is used at LiteGUI/User.php(26)
	protected function is($user) {
		if (!isset($user['name']) OR !isset($user['language'])){ //user's name has not been set, empty name will pass
			$stored = $this->db->table($this->table_prefix .'1_user')
				->where('id', $user['id'])
				//->where('status', 'Active') //newly registered account is Inactive and this is just extra information
				->select('name', 'image', 'language', 'timezone')
				->first();	
			if ($stored) {	
				$user = array_merge($user, $stored);
			}	
		}
		if ( !isset($user['name']) ){
			$user['name'] = ''; //so User wont query db for it
		}
		if ( !isset($user['language']) ){
			$user['language'] = ''; //so User wont query db for it and let rely on site's language for this
		}
		//$this->staff = $user['staff'] = true;
		parent::is($user);
	}	

	public function has($permission, $and_or = "AND" ) {
		//SystemAdmin has all permissions and SiteManager has all permissions except SystemAdmin permission and its id 
		return ( parent::has($this->requirements['SystemAdmin']) )? true : parent::has($permission, $and_or);
	}			

	//site must be active
	//I want to access this site, I am this role and have the following permissions
	protected function loadPermissions($site_id) {
		unset($this->roles[0]);//we will specify 
		if (
			!empty($this->db) AND 
			!empty($this->user->getId()) AND 
			!empty($site_id) AND 
			($this->config['site']['status'] == 'Active' OR $this->config['site']['status'] == 'Unverified') 
		){
			//Use role from other site
			if ( !empty($this->config['site']['role_site']) ){
				$site_id = intval($this->config['site']['role_site']);
			}
			$roles = $this->dbm->getConnection('central')
				->table($this->table_staff .' AS staff')
				->join($this->table_system .' AS config', 'role_id', '=', 'config.id')
				->where('config.type', 'role')
				->where('user_id', $this->user->getId())
				->where('staff.status', 'Active') 
				->where('site_id', $site_id)
				->select('admin_id', 'role_id', 'property AS role_name', 'value AS permissions')
				->get()->all();

			$permissions = '';		 
			foreach ($roles??[] AS $r ){
				$this->roles[ $r['role_id'] ] = $r['role_name'];
				$permissions .= ','. $r['permissions'];
			}
			if ( !empty($permissions) ){	
				if ( strpos($permissions, ','. $this->requirements['SystemAdmin'] .',') !== false ){//SystemAdmin
					$this->permissions = $this->dbm->getConnection('central')
						 ->table($this->table_prefix .'_system')
						 ->select('property as permission', $this->db->raw('1 AS constant'))
						 ->where('type', 'permission')
						 ->pluck('constant', 'permission')
						 ->all(); //[ 'App::permission' => 1 ]
				} else {
					$permissions = explode(',', trim(str_replace(',,,', ',', $permissions), ',') );
					//$this->permissions[ $item['id'] ] = 1; //we used both permid and name to check permission, stop using permid as we dont want to do extra step to check for SystemAdmin id 
					$this->permissions = array_fill_keys($permissions, 1); //[ 'App::permission' => 1 ]	
				}
				//Set as staff only when having permissions/roles
				$this->staff = true;
				$append['staff'] = true;	
			}
		}
		if ($this->view->html){	
			$append['roles'] = $this->roles;
			$this->view->append('user', $append, true);
		}
	}

	public function generateRoutes($extra = []) {
		$name = strtolower($this->class);
		$extra['onboard'] = ['GET|POST', '/[i:site_id]/'. $name .'/onboard/[POST:done]?.[json:format]?'];
		$routes = $this->trait_generateRoutes($extra);

		return $routes;
	}
	public function install() { //override User method so it wont be run by auto-activation
		return true;
	}	
####### End User trait, start User application #######
	public function main() {
		if ( ! $this->user->has($this->requirements['MANAGE']) ){
			$this->denyAccess('list');
		} elseif ( !empty($this->config['site']['role_site']) ){
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][] = $this->trans('Staff and Roles should be managed on Site: ') . $this->config['site']['role_site'];
		} else {
			$query = $this->dbm->getConnection('central')
				->table($this->table_staff .' AS staff')
				->join($this->table_user .' AS user', 'user_id', '=', 'user.id')
				->join($this->table_system .' AS config', 'role_id', '=', 'config.id')
				->where('site_id', $this->site_id)
				->select('user_id AS id', 'user.name')
				->addSelect($this->db->raw('GROUP_CONCAT(property SEPARATOR ", ") as roles'))
				->addSelect('user.email', 'oauth_type', 'registered', 'staff.status')
				->groupBy('user_id');

			$block = $this->pagination($query);
			if ( $block['api']['total'] ) {														
				if ($this->view->html){				
					$block['html']['table_header'] = [
						'id' => $this->trans('ID'), 
						'name' => $this->trans('Name'),
						'roles' => $this->trans('Roles'),
						'email' => $this->trans('Email'),
						'oauth_type' => $this->trans('Type'),
						'registered' => $this->trans('Registered'),
						'status' => $this->trans('Status'),
						'action' => $this->trans('Action')
					];
					$block['html']['column_type'] = ['registered' => 'date'];

					$links['api']  = $this->slug('Staff::main');
					$links['edit'] = $this->slug('Staff::action', ["action" => "edit"] );
					$links['delete'] = $this->slug('Staff::action', ["action" => "delete"] );
					$block['links'] = $links;	
					$block['template']['file'] = "datatable";		
				}
			} else {
				$status['result'] = "error";
				$status['message'][] = $this->trans('This site has yet been assigned any staff!');
				
				if ($this->view->html){				
					$status['html']['message_type'] = 'info';
					$status['html']['message_title'] = $this->trans('Information');	
					$link = $this->slug($this->class .'::action', ["action" => "edit"] );
			        $status['message'][ $link ] = $this->trans('Click here to create a new :type', ['type' => $this->class] );
				}
			}

			$this->view->addBlock('main', $block, 'Staff::main');
		}						
		!empty($status) && $this->view->setStatus($status);							
	}

	/*
	* return 0 if staff already added, false if failed
	*/
	public function create($staff, $switch = false) {
		if ( !empty($staff['role_ids']) AND !empty($staff['email']) ) {
			$user_info = parent::read($staff['email'], 'email');
			if ($user_info) {//user exists
				$staff_info = $this->read($user_info['id'], 'user_id');
								
				if ($staff_info) { //user is already an staff here
					return 0;
				} else {
					$staff['user_id'] = $user_info['id'];
					$staff['status']  = 'Active';
				}
			} else { //create the invitation
				//$user_info = parent::create($staff);
				$inviteToken = bin2hex(openssl_random_pseudo_bytes(32));
				$staff['user_id'] = 'Invite-'. $this->hashify($inviteToken, 'static');
				$staff['status']  = 'Invited';
			}
			//check if current staff can assign the given role
			$data = $this->prepareData($staff);
			if ( !empty($data) ) {
				$valid_role_ids = $data['valid_role_ids'];
				unset($data['valid_role_ids']); //unset for upsert to work properly

				if ( $this->upsert($this->table_staff, ['site_id', 'user_id', 'role_id', 'status'], $data, 'status', 'central') ) {
					//if ($switch) $this->is($user); //user takes staff role, switch should work on user instead of staff 
					if ( !empty($staff['email']) ) { //send activation email
						$roles = $this->dbm->getConnection('central')
							->table($this->table_system)
							->where('type', 'role')
							->whereIn('id', $valid_role_ids)
							->groupBy('type')
							->select($this->db->raw('GROUP_CONCAT(property SEPARATOR ", ") AS roles'))
							->value('roles');
						
						$mail_vars = [
							'recipient' => $staff['name'],
							'inviter' => $this->user->getName(),
							'roles' => $roles,
							'cta_url' => $this->config['system']['url'] . $this->slug('Site::main'), //setup on site 1
						];
						if ($data[3] == 'Invited') { //existing user does not need to confirm							
							$mail_vars['cta_url'] .= '?user_invite='. $inviteToken .'&name='. urlencode($staff['name']??'');
						} else {
							$mail_vars['existing_staff'] = 1;
						}	

						$next_actions['Notification::sendMultiChannels'] = [
							"users" => $staff, 
							"subject" => "You Have Been Added as a Staff",
							"file" => "You_Have_Been_Added_as_a_Staff",
							"data" => $mail_vars,
							"email_only"	
						];
						$this->router->registerQueue($next_actions);
					}
					return $staff['user_id']; //return actual user_id instead of admin_id	
				}
			}							
		} 
		return false;		
	}

	protected function read($id, $column = 'user_id') { //user may have multiple roles
		return $this->dbm->getConnection('central')
			->table($this->table_staff .' AS staff')
			->join($this->table_user .' AS user', 'user_id', '=', 'user.id')
			->where('site_id', $this->site_id)
			->where('staff.'. $column, $id)
			->select('admin_id', 'user_id', 'user.name', 'user.email')
			->addSelect($this->db->raw('GROUP_CONCAT(role_id) AS role_ids'))
			->addSelect('staff.status')
			->groupBy('user_id')
			->first();
	}

	protected function prepareData($staff) {
		$data = [];
		if ($staff['status'] == 'Active') {
			$status = 'Active';
		} elseif ($staff['status'] == 'Invited'){
			$status = 'Invited';			
		} else {
			$status = 'Inactive';			
		}
		// check if this role is within user's permissions
		if (!empty($staff['role_ids'])) {
			$staff['role_ids'] = $this->dbm->getConnection('central')
			  ->table($this->table_system)
			  ->where('type', 'role')
			  ->whereIn('id', $staff['role_ids'])
			  ->whereIn('object', ['global', 'site'. $this->site_id])
			  ->select('id', 'value')
			  ->get()->all();

			foreach ( ($staff['role_ids']??[]) AS $role) {
				$permissions = explode(',', trim($role['value'], ',') );
				foreach ($permissions as $permission) {
					if ( $permission AND ! $this->user->has($permission) ){
						$break_this_iteration = true;
						unset($role); //precautious
						continue; //doesn't have this permission, skip this role
					}
				}
				if ( !empty($break_this_iteration) ){
					$break_this_iteration = false;
					continue;
				}
				//site_id, user_id, role_id, status
				$data['valid_role_ids'][] = $role['id']; //need to be removed
				$data[] = $this->site_id;
				$data[] = $staff['user_id'];
				$data[] = $role['id'];
				$data[] = $status;
			}	
		}
		return $data;		
	}

	public function update($staff, Group $groupObj = null) {
		if ( ! ($this->user->has($this->requirements['MANAGE']) OR 
			(!empty($_REQUEST['user']['id']) AND $this->user->getId() == $_REQUEST['user']['id'] ) ) OR 
			( empty($_REQUEST['user']['id']) AND !empty($this->config['site']['tier']) AND $this->config['site']['tier'] < 10 ) //tier >= 10 only
		){ //allow staff to update their own account
			$this->denyAccess('update');
			return false;
		}
		$staff_info = $this->read($this->user->getId(), 'user_id'); //current staff
		//let staff update his own user account at site1
		if ( !empty($_REQUEST['user']['id']) AND $this->user->getId() == $_REQUEST['user']['id'] ){
			//update through User, indicate isAdmin_salt to
			parent::update($_REQUEST['user'] + ['isAdmin_'. $this->config['salt'] => 1]);	
			$staff['user_id'] = $staff_info['user_id']; //$staff is empty in this case
		} elseif ( !empty($this->config['site']['role_site']) ){
			$status['html']['message_title'] = $this->trans('Information');
			//edit will show $status['message'][] = $this->trans('Staff and Roles should be managed on Site: ') . $this->config['site']['role_site'];
		} elseif ( !empty($staff['token']) AND !empty($staff['user_id']) ){ //create or delete api token for current user by current user only, cant do that on behalf of another user as token = password (if staff is added as staff on another site, site owner may set token for that account and gain access to our site via that staff) 
			if ( $staff['user_id'] == $this->user->getId() AND $this->user->has($this->requirements['ApiUser']) ){
				$valid_user_id = $this->user->getId();
				if ( empty($staff['token']['id']) ){ //create token
					$token_exists = $this->db
						->table($this->table_user .'meta')
						->where('user_id', $valid_user_id)
						->where('property', 'api_token')
						->value('id');
					if ( empty($token_exists) ){
						$meta['user_id'] = $valid_user_id;
						$meta['property'] = 'api_token';
						$block['api']['token']['secret'] = bin2hex(openssl_random_pseudo_bytes(32));
						$meta['value'] = $this->hashify($block['api']['token']['secret'], 'static');
						$meta['name'] = $staff['token']['name'] .' ('. substr($block['api']['token']['secret'], 0, 3) .'...'. substr($block['api']['token']['secret'], -3) .')';
						if ( !empty($staff['token']['expiry']) ){
							if ( is_numeric($staff['token']['expiry']) ){ //already timestamp
								$meta['description'] = $staff['token']['expiry'];
							} else {	
								$date = new \DateTime($staff['token']['expiry']); 
								$meta['description'] = $date->format('U');
							}	
						}

						$block['api']['token']['id'] = $this->db
							->table($this->table_user .'meta')
							->insertGetId($meta);

						if ($block['api']['token']['id']) {
							$this->view->addBlock('main', $block, $this->class .'::update');
						} else {
							$status['result'] = 'error';
							$status['message'][] = $this->trans(':item was not created', ['item' => 'Token']);	
						}								
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans('Token exists');	
					}
				} else { //delete token
					$result = $this->db
						->table($this->table_user .'meta')
						->where('user_id', $valid_user_id)
						->where('property', 'api_token')
						->where('id', $staff['token']['id'])
						->delete();
					if ($result) {
						$status['message'][] = $this->trans(':item deleted successfully', ['item' => 'Token']);
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans('Token removal failed');	
					}	
				}									
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('API management permission required');	
			}
		} elseif ( empty($staff['user_id']) ){ // create a new staff	
			$total = $this->db->table($this->table_staff)
				->where('site_id', $this->site_id)
				->distinct()
				->count('user_id'); //count produces wrong number when use with groupBy
			if ($total >= ($this->config['site']['seats']??$this->config['site']['tier']) ){ //adding seats hasnt been coded
				$status['result'] = 'error';
				$status['message'][] = $this->trans('You have reached the maximum number of staff for your plan');		
			} elseif ($staff['user_id'] = $this->create($staff)) { 
				$status['message'][] = is_numeric($staff['user_id'])? 
					$this->trans(':item added successfully', ['item' => 'Staff']) : 
					$this->trans('Invitation has been sent to :email', ['email' => $staff['email'] ]);						
			} elseif ($staff['user_id'] === 0) {//0 returned by create 
				$status['result'] = 'error';
				$status['message'][] = $this->trans('Staff was already added');						
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not added', ['item' => 'Staff' ]);							
			}
		} else {//update staff
			if ($staff_info['user_id'] == $staff['user_id'] ) { //staff should not update his own role
				$status['result'] = 'error';
				$status['message'][] = $this->trans('Self update is not allowed');		
			} elseif ( $valid_staff = $this->read($staff['user_id'], 'user_id') ){ //to-be-updated staff is valid
				$data = $this->prepareData($staff); //returns valid role_ids, also remove invalid role in $staff using ref

				if ( !empty($data) ){ //must have at least one valid role
					$valid_role_ids = $data['valid_role_ids'];
					unset($data['valid_role_ids']);//unset for upsert to work properly
					//delete existing [unselected, invalid] roles 
					$this->dbm->getConnection('central')
						->table($this->table_staff)
						->where('site_id', $this->site_id)
						->where('user_id', $staff['user_id'])
						->whereNotIn('role_id', $valid_role_ids)
						->delete();
					if ( $this->upsert($this->table_staff, ['site_id', 'user_id', 'role_id', 'status'], $data, 'status', 'central') ){
						$status['message'][] = $this->trans(':item updated successfully', ['item' => $this->class ]);
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans('Cannot update due to database error');
					}			
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not updated', ['item' => 'Staff']);										
				}
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('No such :item', ['item' => $this->class ]);									
			}		
		}

		if ($this->view->html ){ 
			if ( !empty($staff['user_id']) AND is_numeric($staff['user_id']) ){				
				$next_actions[ $this->class .'::edit'] = [$staff['user_id']];
			} else {
				$next_actions[ $this->class .'::main'] = [];// invited staff
			}						
		}
		if (!empty($next_actions)){
			$this->router->registerQueue($next_actions);	
		}		
		!empty($status) && $this->view->setStatus($status);							
		$this->logActivity( implode('. ', $status['message']??[]), $this->class .'.', $staff['user_id']??null );
	}	

	public function edit($id = 0) {
		if ( ! ($this->user->has($this->requirements['MANAGE']) OR 
			$this->user->getId() == $id) OR //allow staff to edit their own account
			( empty($id) AND !empty($this->config['site']['tier']) AND $this->config['site']['tier'] < 10 ) //tier >= 10 only
		){ 
			$this->denyAccess($id? 'edit' : 'create');
		} else {
			if ( !empty($id) ){ //$id could be 'Invite-id'
				$staff_info = $this->read($id);
				if($staff_info){
					$staff_info['role_ids'] = explode(',', $staff_info['role_ids']);
					$block['api']['staff'] = $staff_info;
					if ($staff_info['user_id'] == $this->user->getId()) {
						$block['api']['user'] = parent::read($this->user->getId());	
					}	
				} elseif (empty($this->config['site']['role_site'])) {
					$status['result'] = 'error';
					$status['message'][] = $this->trans('No such :item', ['item' => $this->class ]);
				}
			}			

			if ($this->view->html){	
				//Staff self edit his user account information, use User method for now
				if ( !empty($staff_info['user_id']) AND $staff_info['user_id'] == $this->user->getId()) {
					$block['html']['languages'] = $this->getLanguages();
					$block['html']['timezones'] = $this->getTimezones();
					$block['template']['file'] = "user_edit";		
					// Check if the current staff has api role 
					if ( $this->user->has($this->requirements['ApiUser']) ){
						$block['html']['tab_api'] = 1;
					} 
				} elseif ( !empty($this->config['site']['role_site']) ){
					$status['html']['message_title'] = $this->trans('Information');
					$status['message'][] = $this->trans('Staff and Roles should be managed on Site: ') . $this->config['site']['role_site'];
				} else {				
					$roles = $this->dbm->getConnection('central')
						->table($this->table_system)
						->select('id', 'property as name', 'value')
						->where('type', 'role')
						->whereIn('object', ['global', 'site'. $this->site_id])
						->get()->all();
					foreach ($roles as $index => $role) { //remove role that has permission the current staff don't have
						$checks = explode(',', trim($role['value'], ',') );
						foreach ($checks as $check) {
							if ( $check AND ! $this->user->has($check) ){
								unset($roles[ $index ], $role);
								$break_this_iteration = true;
								continue;
							}
						}
						if ( !empty($break_this_iteration) ){
							$break_this_iteration = false;
							continue;
						}

						if ( !empty($roles[ $index ]) AND !empty($staff_info['role_ids']) ){
							if (in_array($role['id'], $staff_info['role_ids']) ){ //user roles
								$roles[ $index ]['selected'] = 1;
							}	
						}
					}		  
					$block['html']['roles'] = $roles;
					$block['template']['file'] = "staff_edit";	
				}
				if ( !empty($block['html']['tab_api']) ){
					$block['html']['api_tokens'] = $this->dbm->getConnection('central')
						->table($this->table_user .'meta')
						->where('user_id', $staff_info['user_id'])
						->where('property', 'api_token')
						->select('id', 'name', 'description')
						->get()->all();
				}		
				
				$links['update'] = $this->slug($this->class .'::update');
				$links['main'] = $this->slug($this->class .'::main');
				if ( !empty($this->config['system']['sgframe']) ){
					$links['update'] .= '?sgframe=1';
					$links['main']   .= '?sgframe=1';
				}

				$block['links'] = $links;	

				if ((empty($staff_info) AND !empty($_REQUEST['staff']))) { //create staff failed, populate with submitted info
					$block['api']['staff'] = $_REQUEST['staff'];	
				}
			}

			$this->view->addBlock('main', $block, $this->class .'::edit');
			$this->runHook($this->class .'::'. __FUNCTION__, [ $staff_info??null ]); //wont change block or anything

			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}	
			!empty($status) && $this->view->setStatus($status);
		}					
	}

	public function delete($id) {
		if ( ! $this->user->has($this->requirements['MANAGE']) ){
			$this->denyAccess('delete');
		} elseif ( !empty($this->config['site']['role_site']) ){
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][] = $this->trans('Staff and Roles should be managed on Site: ') . $this->config['site']['role_site'];
		} else {
			$staff_info = $this->read($id);
			if ( !empty($staff_info) ){				
				if ($staff_info['user_id'] == $this->user->getId() ) { //user should not delete his own role
					$status['result'] = 'error';
					$status['message'][] = $this->trans('Self delete is not allowed');		
				} else {
					if ($this->dbm->getConnection('central')
						->table($this->table_staff)
						->where('site_id', $this->site_id)
						->where('user_id', $id)
						->delete()
					){
						$status['result'] = 'success';
						$status['message'][] = $this->trans(':item deleted successfully', ['item' => $this->class ]);			
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans(':item was not deleted', ['item' => 'Staff']);				
					}
				}
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('No such :item', ['item' => $this->class ]);				
			}	
			$status['api_endpoint'] = 1;
			$this->logActivity( implode('. ', $status['message']), $this->class .'.', $staff_info['user_id']??null);

			if ($this->view->html){				
				$next_actions[ $this->class .'::main' ] = [];
			}
			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}
		}									
		!empty($status) && $this->view->setStatus($status);
	}
	//Staff can join to a site with an invitation code
	public function welcome($id) {
		if ($this->user->getId()){
			$data['user_id'] = $this->user->getId();
			$data['status']  = 'Active';

			try {
				if ($this->dbm->getConnection('central')
					->table($this->table_staff)
					//->where('site_id', $this->site_id)
					->where('status', 'Invited')
					->where('user_id', 'Invite-'. $this->hashify($id, 'static'))
					->update($data)
				){
					$status['result'] = 'success';
					$status['message'][] = $this->trans('Welcome. You are now a staff');	
					//Reload permissions for this staff
					$this->loadPermissions($this->site_id);	
					if ($this->view->html AND $this->user->has($this->requirements['MANAGE'])){  				
						$next_actions[ $this->class .'::main' ] = [];
					}
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans('Sorry. The invitation code is no longer valid');						
				}
			} catch (\Exception $e){
	            if($e->errorInfo[1] == '1062'){
					$status['result'] = 'error';
					$status['message'][] = $this->trans('You are already a staff for this site');						
	            }
			}						
		} 

		$status['html']['message_title'] = $this->trans('Information');
		$this->view->setStatus($status);	

		if (!empty($next_actions)){
			$this->router->registerQueue($next_actions);	
		}
	}

	public function onboard($done = null) {
		if ($done){
			if ( $this->dbm->getConnection('central')
				->table($this->table_user .'meta')
				->where('user_id', $this->user->getId() )
				->where('property', 'onboard_'. $done)
				->delete() 
			){
				$status['result'] = 'success';
			} else {
				$status['result'] = 'error';
			}
			$this->view->setStatus($status);
		}
	}	
}