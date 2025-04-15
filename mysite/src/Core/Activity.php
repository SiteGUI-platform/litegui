<?php
namespace SiteGUI\Core;

class Activity {
	use Traits\Application;

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user);
		$this->table = $this->site_prefix ."_activity";
		$this->requirements['VIEW'] = "Site::update";
	}

	public static function config($property = '') {
		$config['app_visibility'] = 'staff_readonly';
		$config['app_category'] = 'Management';
	    $config['app_permissions'] = [
	    	'staff_read'   => 1,
	    	'staff_write'  => 1,
	    	'staff_manage' => 0,
	    	'staff_read_permission'   => "Site::update",
	    	'staff_write_permission'  => "Site::update",
	    	'staff_manage_permission' => "",
	    	'client_read' => 0,
	    	'client_write' => 0,
	    ];

    	return ($property)? ($config[ $property ]??null) : $config;
    }

	public function main() {
		$query = $this->db
			->table($this->table .' AS page')
			->when(!empty($_REQUEST['for']), function($query){
				return $query->where('page.app_type', $_REQUEST['for']);
			})
			->when(!empty($_REQUEST['fid']), function($query){
				return $query->where('page.app_id', $_REQUEST['fid']);
			})
			->when( !$this->user->has($this->requirements['VIEW']), function($query){
				return $query->where('page.creator', $this->user->getId());
			})
			->select('page.id', 'page.message', 'page.app_type', 'page.level', 'page.created', 'page.processed', 'page.retry', 'page.meta');
		$block = $this->prepareMain($query, ['app_columns' => !empty($_REQUEST['guest'])? [] : ['creator' => 1 ]], true);//show HTML tag, 'strip_tags');
	
		if ( $block['api']['total'] ){
			if ($this->view->html){				
				$block['html']['table_header'] = [
					'id' => $this->trans('ID'),
					'message' => $this->trans('Message'),
					'app_type' => $this->trans('App'),
					//'level' => $this->trans('Level'),
					'creator' => $this->trans('User'), 
					'created' => $this->trans('Created'), 
					'processed' => $this->trans('Processed'), 
					//'retry' => $this->trans('Retry'),
					'meta' => $this->trans('Details'),
					'action' => $this->trans('Action')
				];
				if ( !empty($_REQUEST['for']) ){
					unset($block['html']['table_header']['app_type'], $block['html']['table_header']['meta']);
				}
				if ( !empty($_REQUEST['guest']) ){
					unset($block['html']['table_header']['creator']);
				}
				$block['html']['column_type']['created'] = 'time';
				$block['html']['column_type']['processed'] = 'time';

				$block['links']['custom_api'] = $this->slug($this->class .'::main') .'.json?html=1&guest='. ($_REQUEST['guest']??0);

				$block['links']['edit'] = $this->slug($this->class .'::action', ["action" => "edit"] );
				$block['template']['file'] = "datatable";		
			}
		} else {
			$status['result'] = "error";
			$status['message'][] = $this->trans('We have yet to have any :item', ['item' => $this->class]);
			
			if ($this->view->html){				
				$status['html']['message_type'] = 'info';
				$status['html']['message_title'] = $this->trans('Information');	
			}
		}
		$this->view->addBlock('main', $block, $this->class .'::edit');
		!empty($status) && $this->view->setStatus($status);							
	}

	public function update($page) {

	}

	/**
	 * print out edit form
	 * @param  integer $id [description]
	 * @return [type]           [description]
	 */
	public function edit($id = 0){
		if ( $this->user->has($this->requirements['VIEW']) ){
			$activity = $this->db
				->table($this->table .' AS page')
				->where('id', $id)
				->select('page.id', 'page.app_type AS app', 'page.app_id', 'page.level', 'page.message', 'page.creator', 'page.creator AS creator_name', 'page.created', 'page.processed', 'page.retry', 'page.meta')
				->first();

			if ($activity['created']){
				$activity['created'] = date("M d, Y H:i", $activity['created']);	
			}
			if ($activity['processed']){
				$activity['processed'] = date("M d, Y H:i", $activity['processed']);	
			}	
			$activity['meta'] = json_decode($activity['meta']??'', true);

			if ( !empty($activity['creator']) ){
				$creator = $this->lookupUsers($activity['creator'])?: $this->lookupAdmins($activity['creator']);
				$activity['creator_name'] = $creator[0]['name']??'';
			}

			if ($this->view->html){				
				$block['links']['main'] = $this->slug($this->class .'::main');
				if ( $activity['app_id'] ){
					$block['html']['ref'] = $activity['app'] .' #'. $activity['app_id'];
					if ( str_ends_with($activity['app'], '.') ){
						$block['links']['ref'] = $this->slug(trim($activity['app'], '.') .'::action', [
							'action' => 'edit',
							'id' => $activity['app_id']
						]);
					} else {
						$block['links']['ref'] = $this->slug('App::action', [
							'action' => 'edit',
							'id' => strtolower($activity['app']) .'/'. $activity['app_id']
						]);
					}
				}	
				$activity = print_r($activity, true);
				$block['template']['file'] = 'activity_edit';
			}
			$block['api']['activity'] = $activity;
			$this->view->addBlock('main', $block, $this->class .'::edit');
		} else {
			$this->denyAccess($id? 'edit' : 'create');			
		}					
	}

	protected function preparePage($page, $slugify = 1) {
		if ( empty($page['meta']) ){
			$page['meta'] = '';
		}
		return $page;
	}		
}