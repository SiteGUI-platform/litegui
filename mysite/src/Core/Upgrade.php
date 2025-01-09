<?php
namespace SiteGUI\Core;

class Upgrade {
	use Traits\Application;
	private $system_revision = 2;
	private $site_revision   = 2;
	private $revision; //=$current_[system_]revision so we can check $current_revision is set correctly to avoid running an upgrade twice

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user); //construct Application
		//$this->db = $dbm->getConnection('central');
		if (!empty($this->config['system']['base_dir'])) {
			$this->path = $this->config['system']['base_dir'] .'/resources/public/site/';
		} else {
			echo "Template directory is not defined!";
		}
		$this->table = $this->table_prefix ."_sites"; //no site_id here
		$this->requirements['UPDATE'] = "Site::update";
		$this->requirements['SystemAdmin'] = "Role::SystemAdmin";
	}
	/**
	 * show the main upgrade screen
	 * @return none
	 */
	public function main() {
		//This method can be used by super admin to upgrade system.
		if($this->user->has($this->requirements['SystemAdmin'])) { 
			//$this->setSystemConfig(['property' => 'revision', 'value' => 1, 'type' => 'system']);
			$current_system_revision = intval($this->getSystemConfig('revision', 'system'));
			if ($current_system_revision > 0 AND $current_system_revision < $this->system_revision) {	
				if ( !empty($_REQUEST['do']) AND $_REQUEST['do'] == 'upgrade' ){
					//upgrade the system
					if ( empty($_REQUEST['site']) ){
						while ($current_system_revision < $this->system_revision 
							AND method_exists($this, "upgradeSystem". ($current_system_revision + 1))
							AND $this->revision < $current_system_revision + 1
						) {
							//run upgradeSystem101, upgradeSystem102 continuously
							$upgradeSystemNo = "upgradeSystem". ($current_system_revision + 1);
							$current_system_revision = $this->$upgradeSystemNo();
						}
					}	

					if ($current_system_revision == $this->system_revision OR !empty($_REQUEST['site']) ){ //upgrade completed successfully
						//Perform upgrade for site that doesn't turn off auto-upgrade
						$sites = $this->dbm->getConnection('central')
							->table($this->table)
						  	->where('auto_upgrade', '<>', 0)
						  	->when( !empty($_REQUEST['site']), function($query){
						  		return $query->where('id', $_REQUEST['site']);
						  	})
							->select('id', 'tier', 'server', 'revision')
							->get()->all();
						//Use Closure::call to get protected property from $this->db (https://stackoverflow.com/questions/20334355/how-to-get-protected-property-of-object-in-php)	
						$db_config = (function() { 
							return $this->config; 
						})->call($this->db);
	
						foreach ($sites as $site) {
							$this->revision = 0; // reset for each site upgrade
							if ( !empty($this->config['servers']) AND !empty($site['server']) AND !empty($this->config['servers'][ $site['server'] ]) ){
								try {
									$this->db = $this->dbm->getConnection($this->config['servers'][ $site['server'] ]);
						            $this->dbm->getDatabaseManager()->setDefaultConnection($this->config['servers'][ $site['server'] ]); //this does not produce error
								} catch (\Exception $e) {
			            			$this->dbm->addConnection(
						            	[
							                'driver'    => 'mysql',
							                'host'      => $this->config['servers'][ $site['server'] ],
							                'database'  => $db_config['database'],
							                'username'  => $db_config['username'],
							                'password'  => $db_config['password'],
							                'charset'   => 'utf8mb4',
							                'collation' => 'utf8mb4_unicode_520_ci',
							                'prefix'    => '',
						            	], 
						                $this->config['servers'][ $site['server'] ]
						            );
						            $this->dbm->getDatabaseManager()->setDefaultConnection($this->config['servers'][ $site['server'] ]);
						        }    
					        } else {
					            $this->dbm->getDatabaseManager()->setDefaultConnection('central');
					        }	
				        	$this->db = $this->dbm->getConnection();
							echo "<br><br>Upgrade Site ID ". $site['id'];
							$this->upgradeSite($site);
						}			   
					}
				} else {
					$status['result'] = "success";			
					$block['template']['file'] = "";		
					if ($this->view->html){				
						//$block['html']['title'] = $this->trans("Site Manager");					
					}
				}
			} 
			!empty($status) && $this->view->setStatus($status);							
			!empty($block)  && $this->view->addBlock('main', $block, 'Upgrade::main');
		} else {
			$this->denyAccess('upgrade system');
		}						
	}	

	/**
	 * show the main upgrade screen for site
	 * @return none
	 */
	public function site() {
		// may merge with main() later
		if($this->user->has($this->requirements['UPDATE']) OR $this->user->has($this->requirements['SystemAdmin'])) {
			if ($this->config['site']['revision'] > 0 AND $this->config['site']['revision'] < $this->system_revision) {	
				if ($_REQUEST['do'] == 'upgrade' AND
					$this->upgradeSite($this->config['site']['id'], $this->config['site']['revision'])
				) {	
					$status['result'] = "success";				
				} else {
					$status['result'] = "success";			
					$block['template']['file'] = "";		
					if ($this->view->html){				
						//$block['html']['title'] = $this->trans("Site Manager");					
					}
				}
			} 
			!empty($status) && $this->view->setStatus($status);							
			$this->view->addBlock('main', $block, 'Upgrade::site');
		} else {
			$this->denyAccess('upgrade site');
		}						
	}

	/**
	 * do the upgrade for site
	 * @return none
	 */
	private function upgradeSite($site) {
		//This method can be used by super admin to upgrade system.
		if($this->user->has($this->requirements['UPDATE']) OR $this->user->has($this->requirements['SystemAdmin'])) {
			//$current_site_revision = $this->db->table($this->table)where('id', $site_id)->value('revision');
			$current_site_revision = $site['revision'];
			if ($current_site_revision > 0 AND $current_site_revision < $this->site_revision) {	
				while ($current_site_revision < $this->site_revision 
					AND method_exists($this, "upgradeSite". ($current_site_revision + 1))
					AND $this->revision < $current_site_revision + 1
				) {
					$upgradeSiteNo = "upgradeSite". ($current_site_revision + 1);
					$current_site_revision = $this->$upgradeSiteNo($site);
				}	
				if ($current_site_revision == $this->site_revision) { //upgrade completed successfully
					return true;
				}	
			}
		}
		return false;	 	
	}

	/** 
	* Perform single upgrade for system
	* @return $revision
	*/
	private function upgradeSystem1() {
		//this method is used to store past upgrades - for the time beging
		$this->revision = 1;
		echo "<br>Upgrade system ". $this->revision;
		return $this->revision;
	}

	/** 
	* Perform single upgrade for system
	* @return $revision
	*/
	private function upgradeSystem2() {
		//this upgrade will not bump up the revision in db so we can use it whenever we want to upgrade 
		$this->revision = 2;
		echo "<br><br>Upgrade system ". $this->revision;
		//$query  = 'ALTER TABLE mysite_sites ADD COLUMN `role_site` INT(1) NULL AFTER `template`';
		//$result = $this->db->statement($query);

		//print_r($result);

		return $this->revision;
	}
	///////////////// SITE UPGRADE ///////////////////
	/** 
	* Perform single upgrade for site
	* @return $revision
	*/
	private function upgradeSite1($site) {
		$this->revision = 1;
		echo "<br>Upgrade site ". $this->revision;

		return $this->revision;
	}

	/** 
	* Perform single upgrade for site
	* @return $revision
	*/
	private function upgradeSite2($site) {
		$this->revision = 2;
		$site_id = $site['id'];
		if ($site['tier'] > 10) {
		}	
		echo "<br>From version ". $this->revision ." @". $site['server'] ." - ". ($this->config['servers'][ $site['server'] ]??'') ." using connection: ". $this->db->getName() ."<br>";
		//$query[]  = 'ALTER TABLE mysite'. $site_id .'_activity CHANGE `user_id` `creator` INT(1) NOT NULL';

		try {
			foreach ($query??[] AS $q){
				$result = $this->db->statement($q);
				echo $result;
			}	
		} catch (\Exception $e) {
			echo "Site ". $site_id .' upgrade error: '. $e->getMessage() ;
		}

		return $this->revision;
	}	
}	
?>