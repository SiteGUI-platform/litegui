<?php
namespace SiteGUI\Core;

class Role {
	use Traits\Application;

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user); //construct Application
		$this->table = $this->table_prefix .'_system';

		$this->requirements['SystemAdmin'] = "Role::SystemAdmin"; 	//Do anything
		$this->requirements['SiteManager'] = "Role::SiteManager"; 	//Manage/own site
		$this->requirements['ApiUser']	   = "Role::ApiUser"; 	//API access
		$this->requirements['Server2Server'] = "Role::Server2Server"; //API access to blocks      
		$this->requirements['Staff']	   = "Role::Staff";
		$this->requirements['Customer']    = "Role::Customer"; 	//Site Customer
		$this->requirements['Supplier']    = "Role::Supplier"; 	//Supplier/Vendor

		//$this->requirements['Developer'] 		= "Role::Developer";   		//Add to site but not Publish 	
		$this->requirements['Partner'] 			= "Role::Partner"; 			//Partner/reseller
		$this->requirements['Engineer'] 		= "Role::Engineer";			//Perform technical activities
		$this->requirements['Salesperson'] 		= "Role::Sales";		//Perform sales activities
		$this->requirements['Accountant'] 		= "Role::Accountant";		//Perform billing activities
		$this->requirements['Marketing'] 		= "Role::Marketing";		//Perform billing activities
		$this->requirements['HR'] 				= "Role::HR";		//Perform billing activities
		$this->requirements['SeniorEngineer'] 	= "Role::SeniorEngineer";	//Manage technical activities
		$this->requirements['TechnicalManager'] = "Role::TechnicalManager";	//Manage technical activities
		$this->requirements['SalesManager'] 	= "Role::SalesManager";		//Manage sales activities
		$this->requirements['AccountingManager']= "Role::AccountingManager";	//Manage billing activities
		$this->requirements['MarketingManager']	= "Role::MarketingManager";	//Manage billing activities
		$this->requirements['HRManager'] 		= "Role::HRManager";		//Perform billing activities
		$this->requirements['Supervisor'] 		= "Role::Supervisor";		//Team leader 
		$this->requirements['Manager'] 			= "Role::Manager";			//Manager level officer
		$this->requirements['C*O'] 				= "Role::C*O";				//C level officer
	}

	/**
	* list all site roles
	* @return none
	*/
	public function main() {
		if ( ! $this->user->has($this->requirements['SiteManager']) ){
			$this->denyAccess('list');
		} elseif ( !empty($this->config['site']['role_site']) ){
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][] = $this->trans('Staff and Roles should be managed on Site: ') . $this->config['site']['role_site'];
		} else {
			$query = $this->dbm->getConnection('central')
				->table($this->table)
				->select('id', 'property as role', 'description', 'value as permissions')
				->where('type', 'role')
				->whereIn('object', ['global', 'site'. $this->site_id]);
			if ( empty($_REQUEST['current']) ){ //force loading roles for permission display
				$_REQUEST['current'] = 1;
				$_REQUEST['rowCount'] = 20;
			}  
			$block = $this->pagination($query);
			
			if ( $block['api']['total'] ){
				foreach ($block['api']['rows'] as $index => $role) { //remove role that has permission the current staff don't have
					$checks = explode(',', trim($role['permissions'], ',') );
					foreach ($checks as $check) {
						if ( $check AND ! $this->user->has($check) ){
							unset($block['api']['rows'][ $index ]);
							$block['api']['total']--;
							continue;
						}
					}
				}
				if ($this->view->html){				
					//additional table
					$data = $this->dbm->getConnection('central')
						->table($this->table)
						->select('property as permission')
						->where('type', 'permission')
						->orderBy('property')
						->get()->all();
					$permissions = [];			 
					foreach($data AS $p) {
						//Only SystemAdmin role can assign special SiteManager role
						if ( $p['permission'] == $this->requirements['SystemAdmin'] OR ( $p['permission'] == $this->requirements['SiteManager'] AND ! $this->user->has($this->requirements['SystemAdmin']) ) ) {
							continue;
						}
						foreach ($block['api']['rows'] as $role) {
							if (//strpos($role['value'], ','. $this->requirements['SiteManager'] .',') !== false OR 
								strpos($role['permissions'], ','. $this->requirements['SystemAdmin'] .',') !== false OR 
								strpos($role['permissions'], ','. $p['permission'] .',') !== false
							) {
								$p[ $role['role'] ] = '✔️';
							} else {
								$p[ $role['role'] ] = '';
							}
						}
						$permissions[] = $p;
					}
					$block['html']['permissions'] = $permissions;					
					$block['html']['permissions_header'] = array_keys($p);
					//$block['html']['rowCount'] = -1; //for bootgrid, means all records
					//end additional table

					$block['html']['table_header'] = [
						'id' => $this->trans('ID'),
						'role' => $this->trans('Role'),
						'description' => $this->trans('Description'),
						'action' => $this->trans('Action'),
					];
					$links['api'] = $this->slug('Role::main');
					$links['edit'] = $this->slug('Role::action', ["action" => "edit"] );
					//$links['copy'] = $this->slug('Role::action', ["action" => "copy"] );
					$links['delete'] = $this->slug('Role::action', ["action" => "delete"] );
					$block['links'] = $links;
					$block['template']['file'] = "role_main";		
				}
			} else {
				$status['result'] = "error";
				$status['message'][] = $this->trans('No role defined!');
				
				if ($this->view->html){				
					$status['html']['message_type'] = 'info';
					$status['html']['message_title'] = $this->trans('Information');	
					$link = $this->slug($this->class .'::action', ["action" => "edit"] );
			        $status['message'][ $link ] = $this->trans('Click here to create a new :type', ['type' => $this->class] );
				}
			}			
			$this->view->addBlock('main', $block, 'Role::main');
		}						
		!empty($status) && $this->view->setStatus($status);							
	}

	public function create($role) {
		if ( ! $this->user->has($this->requirements['SiteManager']) ){
			$this->denyAccess('create');
		} else {
			try {
				$data = $this->prepareData($role);
				$data['type'] = "role";
				//only Sysadmin can create global role
				if ( !empty($role['object']['global']) AND $this->user->has($this->requirements['SystemAdmin']) ) {
					$data['object'] = 'global'; 
				} else {
					$data['object'] = 'site'. $this->site_id;
				}

				return $this->dbm->getConnection('central')
					->table($this->table)
					->insertGetId($data);	
			} catch (\Exception $e){
				if ($e->getCode() == 23000) {	
					return 0;
				}
			}			
		}
		return false;		
	}

	protected function read($id) {
		$role_info = $this->dbm->getConnection('central')
			->table($this->table)
			->where('id', $id)
			->where('type', 'role')
			->whereIn('object', ['global', 'site'. $this->site_id])
			->select('id', 'object', 'property AS name', 'value AS permissions', 'description')
			->first();	

		if ($role_info){	
			$permissions = explode(',', trim($role_info['permissions'], ',') );
			foreach ($permissions as $permission) {
				if ( $permission AND ! $this->user->has($permission) ){
					return false;
				}
			}
		}	
		return $role_info;					
	}
					
	public function update($role) {
		if ( ! $this->user->has($this->requirements['SiteManager']) ){
			$this->denyAccess('update');
		} elseif ( !empty($this->config['site']['role_site']) ){
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][] = $this->trans('Staff and Roles should be managed on Site: ') . $this->config['site']['role_site'];
		} else {
			if (empty($role['id'])) {
				if ($role['id'] = $this->create($role)) { // create a new role	
					$status['message'][] = $this->trans(':item created successfully', ['item' => $this->class]);
				} elseif ($role['id'] === 0) { // create a new role	
					$status['result'] = 'error';
					$status['message'][] = $this->trans("Duplicated :item", ['item' => $this->class]);					
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not created', ['item' => $this->class]);						
				}		
			} else {
				$role_info = $this->read($role['id']);
				//check if this user can update this role			 
				if ($role_info) {
					if ($this->user->has($this->requirements['SystemAdmin']) OR $role_info['object'] == 'site'. $this->site_id ) {
						try { 
							$data = $this->prepareData($role);
							if ( !empty($data) ){ 
								$result = $this->dbm->getConnection('central')
									->table($this->table)
									->where('id', intval($role['id']) )
									->where('type', 'role')
									->update($data);
							}
									
							if (!empty($result)) {
								$status['message'][] = $this->trans(':item updated successfully', ['item' => $this->class]);	
							} else {
								$status['result'] = 'error';
								$status['message'][] = $this->trans(':item was not updated', ['item' => $this->class]);						
							}
						} catch (\Exception $e){
							if ($e->getCode() == 23000) {
								$status['result'] = 'error';
								$status['message'][] = $this->trans("Duplicated :item", ['item' => $this->class]);
							} 
						}	
					} else {
						$status['result'] = 'error';
						$status['message'][] = $this->trans('System role cannot be updated');
					}	
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans('No such :item', ['item' => $this->class ]);
					$status['html']['message_title'] = $this->trans('Information');	
				}	
			}

			$this->logActivity( implode('. ', $status['message']), $this->class .'.', $role['id']);
			if ($this->view->html) {
				if ( !empty($status['result']) AND $status['result'] == 'error'){
					$status['html']['request'] = $role;
				}				
				$next_actions[ $this->class .'::edit'] = [$role['id']];					
			}
			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}
		}		
		!empty($status) && $this->view->setStatus($status);
	}

	public function delete($id) {
		if ( ! $this->user->has($this->requirements['SiteManager']) ){
			$this->denyAccess('delete');
		} elseif ( !empty($this->config['site']['role_site']) ){
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][] = $this->trans('Staff and Roles should be managed on Site: ') . $this->config['site']['role_site'];
		} else {		
			$role_info = $this->read($id);
			if ($this->user->has($this->requirements['SystemAdmin']) OR $role_info['object'] == 'site'. $this->site_id ) {
				if ($this->dbm->getConnection('central')
					->table($this->table)
					->where('type', 'role')
					->delete(intval($id)) 
				){
					$status['result'] = 'success';
					$status['message'][] = $this->trans(':item deleted successfully', ['item' => $this->class]);				
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not deleted', ['item' => $this->class]);
				}
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('System role cannot be deleted');
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
	/**
	 * print out edit form
	 * @param  integer $id [description]
	 * @return [type]           [description]
	 */
	public function edit($id = 0){
		if ( ! $this->user->has($this->requirements['SiteManager']) ){
			$this->denyAccess($id? 'edit' : 'create');
		} elseif ( !empty($this->config['site']['role_site']) ){
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][] = $this->trans('Staff and Roles should be managed on Site: ') . $this->config['site']['role_site'];
		} else {	
			if ($id) {
				$role_info = $this->read($id);

				if ( ! $role_info) {
					$status['result'] = 'error';
					$status['message'][] = $this->trans('No such :item', ['item' => $this->class ]);
					$status['html']['message_title'] = $this->trans('Information');	
				} else {
					$query = $this->dbm->getConnection('central')
						->table($this->table_prefix .'_admin AS staff')
						->join($this->table_prefix .'1_user AS user', 'user_id', '=', 'user.id')
						->where('site_id', $this->site_id)
						->where('role_id', $role_info['id'])
						->select('user_id AS id', 'user.name', 'user.email', 'staff.status');
					$block = $this->pagination($query);	
					if ( $this->view->html ) {							
						$links['edit'] = $this->slug('Staff::action', ["action" => "edit"] );
						if ($block['api']['total']){				
							$block['html']['table_header'] = [
								'id' => $this->trans('ID'),
								'name' => $this->trans('Name'),
								'email' => $this->trans('Email'),
								'status' => $this->trans('Status'), 
								'action' => $this->trans('Action'),
							];

							$links['api']  = $this->slug('Role::action', ['action' => 'edit', 'id' => $id]);
							//$links['delete'] = $this->slug('Staff::action', ["action" => "delete"] );
						}
					}
				}					
			}

			$data = $this->dbm->getConnection('central')
				 ->table($this->table)
				 ->select('property', 'value', 'name')
				 ->where('type', 'permission')
				 ->get()->all();

			foreach($data AS $p) {
				//Only SystemAdmin role can assign special SiteManager role
				if ( $p['property'] == $this->requirements['SystemAdmin'] OR ($p['property'] == $this->requirements['SiteManager'] AND ! $this->user->has($this->requirements['SystemAdmin']) ) ) {
					continue;
				}	
				if ($this->user->has($p['property'])) { //user can enable only permissions user has access 
					if ( !empty($role_info['permissions']) AND
						(
							strpos($role_info['permissions'], ','. $this->requirements['SystemAdmin'] .',') !== false OR 
							strpos($role_info['permissions'], ','. $p['property'] .',') !== false
						)
					){
						$p['enabled'] = 1;
					}
					$p['value'] = str_replace(",", ", ", trim($p['value'], ',')); //for displaying
					$permissions[ $p['property'] ] = $p;
				} 
			}
			$role_info['permissions'] = $permissions;

			$block['template']['file'] = "role_edit";
			$block['api']['role'] = $role_info;

			if ($this->view->html){
				$block['html']['global_role'] = ($id == 0 && !empty($permissions[ $this->requirements['SiteManager'] ]) )? 1 : 0;
				$links['update'] = $this->slug($this->class .'::update');
				$links['main']   = $this->slug($this->class .'::main');
				if ( !empty($this->config['system']['sgframe']) ){
					$links['update'] .= '?sgframe=1';
					$links['main']   .= '?sgframe=1';
				}
				$block['links'] = $links;	
			}			
			$this->view->addBlock('main', $block, $this->class .'::edit');
		}	
		!empty($status) && $this->view->setStatus($status);					
	}
	

	protected function prepareData($data) {
		//role name
		$property = $this->sanitizeFileName($data['name']);
		if (!empty($property)) {
			$role['property'] = $property;
		}
		if (!empty($data['description'])) {
			$role['description'] = $data['description'];
		}
		// role's permissions
		$role['value'] = ''; 
		foreach ( ($data['permissions']??[]) AS $key => $value) {
			//Only SystemAdmin role can assign special SiteManager role
			if ($key == $this->requirements['SystemAdmin'] OR ($key == $this->requirements['SiteManager'] AND ! $this->user->has($this->requirements['SystemAdmin']) ) ) {
				continue;
			}
			if ($this->user->has($key)) {
				$role['value'] .= ','. $key;
			}
		}
		if (!empty($role['value'])) {
			$role['value'] .= ','; // ,11,32,
		}

		return $role;		
	}		
}