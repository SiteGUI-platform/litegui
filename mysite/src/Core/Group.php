<?php
namespace SiteGUI\Core;

class Group {
	use Traits\Application;

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user); //construct Application
		$this->table = $this->site_prefix .'_config';
		$this->requirements['MANAGE'] = "User::manage";
	}

	/**
	* list all groups
	* @return none
	*/
	public function main() {
		if ( ! $this->user->has($this->requirements['MANAGE']) ){
			$this->denyAccess('list');
		} elseif ( !empty($this->config['site']['user_site']) ){
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][] = $this->trans('Client Group should be managed on Site: ') . $this->config['site']['user_site'];
		} else {
			$query = $this->db
				->table($this->table)
				->select('id', 'property AS name', 'description')
				->selectRaw('CONCAT("Group::", value) AS permission')
				->where('type', 'db')
				->where('object', $this->class); 
			$block = $this->pagination($query);
			
			if ( $block['api']['total'] ){
				if ($this->view->html){				
					$block['html']['table_header'] = [
						'id' => $this->trans('ID'),
						'name' => $this->trans('Name'),
						'description' => $this->trans('Description'),
						'permission' => $this->trans('Permission'),
						'action' => $this->trans('Action'),
					];
					$links['api'] = $this->slug($this->class .'::main');
					$links['edit'] = $this->slug($this->class .'::action', ["action" => "edit"] );
					$links['delete'] = $this->slug($this->class .'::action', ["action" => "delete"] );
					$block['links'] = $links;
					$block['template']['file'] = "datatable";		
				}
			} else {
				$status['result'] = "error";
				$status['message'][] = $this->trans('You have not created any :type', ['type' => $this->class]);
				
				if ($this->view->html){				
					$status['html']['message_type'] = 'info';
					$status['html']['message_title'] = $this->trans('Information');	
					$link = $this->slug($this->class .'::action', ["action" => "edit"] );
			        $status['message'][ $link ] = $this->trans('Click here to create a new :type', ['type' => $this->class] );
				}
			}			
			$this->view->addBlock('main', $block, $this->class .'::main');
		}						
		!empty($status) && $this->view->setStatus($status);							
	}

	public function create($group) {
		if ( ! $this->user->has($this->requirements['MANAGE']) ){
			$this->denyAccess('create');
		} else {
			try {
				$data = $this->prepareData($group);
				if (!empty($data['property']) ){
					$data['type'] = 'db';
					$data['object'] = $this->class;
					
					return $this->db
						->table($this->table)
						->insertGetId($data);	
				}
			} catch (\Exception $e){
				if ($e->getCode() == 23000) {
					return 0; //duplicated group
				} 
			}			
		}
		return false;		
	}

	protected function read($id) {
		return $this->db
			->table($this->table)
			->where('id', $id)
			->where('type', 'db')
			->where('object', $this->class)
			->select('id', 'property AS name', 'value AS key', 'description')
			->first();	
	}
					
	/**
	 * print out edit form
	 * @param  integer $id [description]
	 * @return [type]           [description]
	 */
	public function edit($id = 0){
		if ( ! $this->user->has($this->requirements['MANAGE']) ){
			$this->denyAccess($id? 'edit' : 'create');
		} elseif ( !empty($this->config['site']['user_site']) ){
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][] = $this->trans('Client Group should be managed on Site: ') . $this->config['site']['user_site'];
		} else {	
			if ($id) {
				$group_info = $this->read($id);

				if ( ! $group_info) {
					$status['result'] = 'error';
					$status['message'][] = $this->trans('No such :item', ['item' => $this->class ]);
					$status['html']['message_title'] = $this->trans('Information');	
				} else { //via datatable 
					$query = $this->db
						->table($this->table_user .' AS user')
						->join(str_replace('_user', '_config', $this->table_user) .' AS meta', 'meta.object', '=', 'user.id')
						->join(str_replace('_user', '_config', $this->table_user) .' AS config', 'meta.property', '=', 'config.id')
						->where('config.id', $group_info['id'])
						->where('config.type', 'db')
						->where('config.object', 'Group')
						->where('meta.type', 'group')
						->where('meta.value', 'Active')
						->select('user.id', 'user.name', 'user.email', 'user.status');
					$block = $this->pagination($query);	
					if ( $this->view->html ) {							
						$links['edit'] = $this->slug('User::action', ["action" => "edit"] );
						if ($block['api']['total']){				
							$block['html']['table_header'] = [
								'id' => $this->trans('ID'),
								'name' => $this->trans('Name'),
								'email' => $this->trans('Email'),
								'status' => $this->trans('Status'), 
								'action' => $this->trans('Action'),
							];

							$links['api'] = $this->slug($this->class .'::action', ['action' => 'edit', 'id' => $id]);
						}
					}
				}					
			}

			if ($this->view->html){
				$links['update'] = $this->slug($this->class .'::update');
				$links['main']   = $this->slug($this->class .'::main');
				if ( !empty($this->config['system']['sgframe']) ){
					$links['update'] .= '?sgframe=1';
					$links['main']   .= '?sgframe=1';
				}
				$block['links'] = $links;	
			}	

			$block['template']['file'] = "group_edit";
			$block['api']['group'] = $group_info??null;
			$this->view->addBlock('main', $block, $this->class .'::edit');
		}	
		!empty($status) && $this->view->setStatus($status);					
	}
	

	protected function prepareData($data) {
		//group name
		$property = $this->formatAppLabel($this->sanitizeFileName($data['name']));
		if (!empty($property)) {
			$group['property'] = $property;
			$group['value'] = str_replace(' ', '_', $property);
		}
		if (!empty($data['description'])) {
			$group['description'] = $data['description'];
		}

		return $group;		
	}

	public function update($group) {
		if ( ! $this->user->has($this->requirements['MANAGE']) ){
			$this->denyAccess('update');
		} elseif ( !empty($this->config['site']['user_site']) ){
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][] = $this->trans('Client Group should be managed on Site: ') . $this->config['site']['user_site'];
		} else {
			if (empty($group['id'])) {
				if ($group['id'] = $this->create($group)) { // create a new group	
					$status['message'][] = $this->trans(':item created successfully', ['item' => $this->class]);	
				} elseif ($group['id'] === 0) { //Duplicated	
					$status['result'] = 'error';
					$status['message'][] = $this->trans('Duplicated :item', ['item' => $this->class]);						
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not created', ['item' => $this->class]);						
				}		
			} else {
				try {
					$data = $this->prepareData($group);
					if ( !empty($data['property']) ){ 
						$result = $this->db
							->table($this->table)
							->where('id', intval($group['id']) )
							->where('type', 'db')
							->where('object', $this->class)
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
			}

			$this->logActivity( implode('. ', $status['message']), $this->class .'.', $group['id']);
			if ($this->view->html) {
				if ( !empty($status['result']) AND $status['result'] == 'error'){
					$status['html']['request'] = $group;
				}				
				$next_actions[ $this->class .'::edit'] = [$group['id']];					
			}
			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}
		}		
		!empty($status) && $this->view->setStatus($status);
	}

	public function delete($id) {
		if ( ! $this->user->has($this->requirements['MANAGE']) ){
			$this->denyAccess('delete');
		} elseif ( !empty($this->config['site']['user_site']) ){
			$status['html']['message_title'] = $this->trans('Information');
			$status['message'][] = $this->trans('Client Group should be managed on Site: ') . $this->config['site']['user_site'];
		} else {		
			if ($this->db
				->table($this->table)
				->where('type', 'db')
				->where('object', $this->class)
				->delete(intval($id)) 
			){
				$status['result'] = 'success';
				$status['message'][] = $this->trans(':item deleted successfully', ['item' => $this->class]);				
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not deleted', ['item' => $this->class]);
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

	public function updateUserGroups($user_id, $group_ids, $status = 'Active'){
		//allow Order process() which runs as the buyer to update buyer groups
		if ($this->user->has($this->requirements['MANAGE']) OR $this->user->getId() == $user_id){
			$valid_gids = $this->db->table($this->site_prefix .'_config')
					->where('type', 'db')
					->where('object', 'Group') //Class Group
					->whereIn('id', $group_ids)
					->pluck('id')
					->all();
			foreach ($valid_gids AS $gid){
				$upsert[] = 'group'; //type
				$upsert[] = $user_id; //object
				$upsert[] = $gid; //property
				$upsert[] = $status; //value
			}	
			if (!empty($upsert) AND $this->upsert($this->site_prefix .'_config', ['type', 'object', 'property', 'value'], $upsert, ['value']) 
			){
				$result = 1;
			}
			//delete existing [unselected, invalid] group 
			if ($this->db
				->table($this->site_prefix .'_config')
				->where('type', 'group')
				->where('object', $user_id)
				->whereNotIn('property', $valid_gids?:[])
				->delete()
			){
				$result = 1;
			}
		}
		return $result??false;		
	}		
}
