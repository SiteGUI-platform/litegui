<?php
namespace SiteGUI\Core;

class Tax {
	use Traits\Application { generateRoutes as trait_generateRoutes; }
	protected $object;

	public function __construct($config, $dbm, $router, $view, $user){
		$this->app_construct($config, $dbm, $router, $view, $user); //construct Application
		$this->object = $this->trimRootNs(__CLASS__); //remove root namespace
		$this->table = $this->site_prefix ."_config";
		$this->requirements['CREATE'] 	= "Product::publish";
	}

	public static function config($property = '') {
		$config['app_visibility'] = 'staff';
	    $config['app_configs'] = [
	        'tax_inclusive' => [
	            'label' => 'Tax Inclusive',
	            'type' => 'checkbox',
	            'description' => 'All prices include the tax amount already',
	            'value' => 0,
	        ],
	        'tax_shipping' => [
	            'label' => 'Tax on Shipping',
	            'type' => 'checkbox',
	            'description' => 'Charge tax on shipping fee',
	            'value' => 1,
	        ],
	        'item_rounding' => [
	            'label' => 'Rounding',
	            'type' => 'checkbox',
	            'description' => 'Rounding tax for each item',
	            'value' => 0,
	        ],
	    ];
    	return ($property)? ($config[ $property ]??null) : $config;		
    }
	// this method can be used by customer, no permission required
	public function list($filter = 'inactive') { //method hint Tax::list just returns inactive/alternate rates for selection
		$taxes = $this->db
			->table($this->table)
			->select('id', 'property AS name', 'name AS country', 'description AS state', 'value')
			->where('type', 'db')
			->where('object', $this->object)
			->get()->all(); 

		if( !empty($taxes) ){
			foreach ($taxes AS $key => $tax){
				$columns = json_decode($tax['value']??'', true);
				if ($filter == 'inactive' AND !empty($columns['active']) ){
					unset($taxes[ $key ]);
					continue;
				} elseif ( $filter == 'display' ){
					unset($columns['compound'],  $columns['shipping']);
					$columns['active'] = !empty($columns['active'])? '✓' : '';
				}	
				unset($taxes[ $key ]['value']);
				//add other columns to $result
				if (is_array($columns)){
					$taxes[ $key ] += $columns;
				}
			}			
		}
		return $taxes??null;	
	}
	//return tax rate + info for specified country/state
	public function locate($country = '', $state = ''){
		$taxes = $this->list('all');
		$tax_info['settings'] = self::getMyConfig();
		foreach ($taxes AS $index => $tax) { //get taxes for buyer's country/state (both active/alternate rates)
			if ( !empty($tax['country']) ){
				if ( $tax['country'] != $country OR 
				   ( !empty($tax['state']) AND $tax['state'] != $state )
				){
					continue; //non applicable rate, pass
				} 
			} 
			// there may be several tax rates matching buyer's country/state, choose one ACTIVE rate per level
			// that is more specific (has state) or have higher rate than other ones in the same level
			// alternate/inactive rates are left intact as they may be set as the tax rate for some products
			if ( !empty($tax['active']) ){
				$level = $tax['level'];
				if ( !empty($tax_info['level'][ $level ]) ) {
					$chosen = $tax_info['level'][ $level ];
					//compare with the last chosen one replace if necessary
					$higher = ( round(10000*$tax['rate']) > round(10000*$chosen['rate']) ); 
					if ( !empty($chosen['country']) ){
						if ( !empty($chosen['state']) ){
							if ( !empty($tax['country']) AND !empty($tax['state']) AND $higher ){
								$tax_info['level'][ $level ] = $tax;
							}		
						} elseif ( !empty($tax['country']) AND ( !empty($tax['state']) OR $higher) ){
							$tax_info['level'][ $level ] = $tax;
						}
					} elseif ( !empty($tax['country']) OR $higher) {
						$tax_info['level'][ $level ] = $tax;
					}	
				} else {
					$tax_info['level'][ $level ] = $tax;
				}
			} else {
				$tax_info['alternate'][ $tax['id'] ] = $tax; //index alternate tax by id for easy retrieval when needed
			}	
		}

		return $tax_info;		
	}

    //find item (compound) tax rate
    public function taxRate($tax_info, $rate_id = NULL) {
		$tax = [
			'name' => '',
			'rate' => 0, //use as exclusive tax rate to calculate tax from amount on GUI
			'calculated_rate' => 0, //use for calculating both inclusive and exclusive tax
		];
		//tax rate can be 9.975
		if ( !empty($rate_id) AND !empty($tax_info['alternate'][ $rate_id ]) ){ //using specified tax
			$level = $tax_info['alternate'][ $rate_id ];
			$tax['name'] = $level['name'] .' '. $level['rate'] .'%';
			$tax['rate'] = $level['rate'];	
			if ( !empty($tax_info['settings']['tax_inclusive']) ){
				//total = price(1+rate/100) => price = total/(1+rate/100) = 100*total/(100+rate)
				//tax amount = total - price = total - 100*total/(100+rate) = total(100+rate - 100)/(100+rate)
				//tax amount = total*rate/(100+rate)
				$tax['calculated_rate'] = $level['rate']/($level['rate'] + 100);
			} else {
				$tax['calculated_rate'] = $level['rate']/100;
			}
		} elseif ( !empty($tax_info['level']) ){
			foreach ( $tax_info['level'] AS $index => &$level ){ //& to keep compound rate	
				//recalculate rate for compound tax 
				if ($index > 1){ 
					if ( !empty($level['compound']) ){  
						$tax['name'] .= ' * '. $level['name'] .' '. $level['rate'] .'%'; //before changing rate
						//compound rate (%) = (100 + prior rate)(100 + rate)/100 - 100 
						$level['rate'] = (100 + $tax_info['level'][ ($index - 1) ]['rate'] ) * (100 + $level['rate'] )/100 - 100;
					} else {
						$tax['name'] .= ' + '. $level['name'] .' '. $level['rate'] .'%';
					}	
				} else {
					$tax['name'] .= $level['name'] .' '. $level['rate'] .'%'; 
				}
				//sum up calculated tax 
				if ( !empty($tax_info['settings']['tax_inclusive']) ){
					$tax['calculated_rate'] += $level['rate']; //=sum of rates, will calculate rate after foreach
				} else {
					$tax['calculated_rate'] += $level['rate'];
				}
			}
			$tax['rate'] = $tax['calculated_rate'];	
			if ( !empty($tax_info['settings']['tax_inclusive']) ){
				//same formula as single tax rate: tax amount = total*rate/(100+rate)
				$tax['calculated_rate'] = $tax['calculated_rate']/($tax['calculated_rate'] + 100);
			} else {
				$tax['calculated_rate'] = $tax['calculated_rate']/100;					
			}
		}
		return $tax;   	
    }
			
	public function main() {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('list');
		} else {
			$taxes = $this->list('display'); 

			if( !empty($taxes) ){
				if ($this->view->html){				
					foreach ( $taxes[0] AS $col => $value ){
						$block['html']['table_header'][ $col ] = $this->trans(($col == 'id')? 'ID' : ucfirst($col)); 
					}	
					$block['html']['table_header']['action'] = $this->trans('Action'); 

					$links['edit'] = $this->slug('Tax::action', ["action" => "edit"] );
					$links['copy'] = $this->slug('Tax::action', ["action" => "copy"] );
					$links['delete'] = $this->slug('Tax::action', ["action" => "delete"] );
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
		
			$block['api']['total'] = count($taxes);					
			$block['api']['rows'] = $taxes;					

			!empty($status) && $this->view->setStatus($status);							
			$this->view->addBlock('main', $block, $this->class .'::main'); 
		}
	}
	
	protected function prepareData($data) {
		$result['property'] = !empty($data['name'])? $data['name'] : 'Tax rate '. time();	
		$result['value']['rate'] = is_numeric($data['value']['rate'])? $data['value']['rate'] : 0;
		foreach (['level', 'compound', 'shipping', 'active'] AS $col) {
			$result['value'][ $col ] = $data['value'][ $col ]??''; //default = empty 
		}
		$result['value'] = json_encode($result['value']);
		//use name for country and description for state
		if ( !empty($data['country']) ){
			$result['name'] = $data['country'];
		} else {
			$result['name'] = '';
		}
		if ( !empty($data['state']) ){
			$result['description'] = $data['state'];
		} else {
			$result['description'] = '';
		}
		return $result;		
	}

	protected function create($tax) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('create');
		} else {
			$data = $this->prepareData($tax);
			$data['type'] = 'db';
			$data['object'] = $this->object;
			try {
				return $this->db
					->table($this->table)
					->insertGetId($data);
			} catch (\Exception $e) {
				return false; //duplicate entry
			}			
		}		
	}

	protected function read($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('read');
		} else {
			$result = $this->db
				->table($this->table)
				->select('id', 'property AS name', 'name AS country', 'description AS state', 'value')
				->where('id', $id)
				->where('type', 'db')
				->where('object', $this->object) 
				->first();
			if( !empty($result['value']) ){
				$result['value'] = json_decode($result['value']??'', true);
			}	
			return $result??[];
		}	
	}
	public function update($tax) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('update');
		}
	
		if (empty($tax['id'])){
			if ($tax['id'] = $this->create($tax)) { // create a new tax	
				$status['message'][] = $this->trans(':item created successfully', ['item' => 'Tax']);						
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not created', ['item' => 'Tax']);						
			}		
		} else {
			$data = $this->prepareData($tax);
			try {
				if ($this->db
					->table($this->table)
					->where('id', $tax['id'])
					->where('type', 'db')
					->where('object', $this->object) 
					->update($data) 
				){
					$status['message'][] = $this->trans(':item updated successfully', ['item' => 'Tax']);		
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not updated', ['item' => 'Tax']);										
				}
			} catch (\Exception $e){
				$status['result'] = 'error';
				$status['message'][] = $this->trans('Duplicated :item', ['item' => 'Tax rate']);	
			}		
		}

		if ( !empty($tax['product_id']) AND !empty($tax['id']) ){
			$location['app_type'] = "tax";
			$location['app_id'] = $tax['id'];
			$location['page_id'] = $tax['product_id'];
			if ( $this->upsert($this->site_prefix . '_location', array_keys($location), $location) ){
				$status2['message'] = $this->trans('Tax rate applies to the product successfully');						
				if ( !empty($status['result']) AND $status['result'] == 'error' ){
					unset($status);	//other tax fields are not changed	
				}
			} else {	
				$status2['result'] = 'error';
				$status2['message'] = $this->trans('Tax rate did not apply to the product');	
			}	
			$this->view->setStatus($status2); 			
		}

		if ( !empty($status) ){
			$this->view->setStatus($status); //required here, after this line widget location may produce other status	
			$this->logActivity( implode('. ', $status['message']), $this->class .'.', $tax['id']);
		}

		if ($this->view->html) {				
			$this->edit($tax['id']);
		}	
	}

	public function delete($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('delete');
		} 

		$assigned = $this->db
			->table($this->site_prefix . '_location')
			->select('id')
			 ->where("app_id", $id)
			->where("app_type", "tax")
			->first();
		if ( !empty($assigned) ){ //Assigned tax rate cannot be deleted 		
			$status['result'] = 'error';
			$status['message'][] = $this->trans('Tax rate that is still associated with a product cannot be deleted');
		} else {
			$result = $this->db
				->table($this->table)
				->where('type', 'db')
				->where('object', $this->object) 
				->delete($id);
			
			if ( !empty($result) ){
				$status['message'][] = $this->trans(':item deleted successfully', ['item' => 'Tax rate']);				
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not deleted', ['item' => 'Tax rate']);				
			}
		}

		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);	

		$this->logActivity( implode('. ', $status['message']) , $this->class .'.', $id);

		if ($this->view->html){				
			$this->main();
		}								
	}

	/**
	 * print out edit form
	 * @param  integer $id [description]
	 * @return [type]           [description]
	 */
	public function edit($id = 0){
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess($id? 'edit' : 'create');
		} else {
			$block['api']['tax'] = $this->read($id);
			if ($id AND empty($block['api']['tax']) ){
				$status['result'] = 'error';
				$status['message'][] = $this->trans('No such :item', ['item' => $this->class ]);
				$this->view->setStatus($status);
			} elseif ($this->view->html){								
				$links['lookup']  = $this->slug('Lookup::now');
				$links['update'] = $this->slug($this->class .'::update');
				$links['main']   = $this->slug($this->class .'::main');
				if ( !empty($this->config['system']['sgframe']) ){
					$links['update'] .= '?sgframe=1';
					$links['main']   .= '?sgframe=1';
				}

				$block['links'] = $links;			
				$block['template']['file'] = "tax_edit";		
			}

			$this->view->addBlock('main', $block, 'Tax::edit');	
		}					
	}
	public function copy($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ) {
			$this->denyAccess('copy');
		} else {
			$tax_info = $this->read($id);
			if ($tax_info){
				$tax_info['name'] .= " (Copied)";
				$new_id = $this->create($tax_info);
				if ($new_id) {
					$status['result'] = 'success';
					$status['message'][] = $this->trans(':item copied successfully', ['item' => 'Tax rate']);
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not copied', ['item' => 'Tax rate']);					
				}
				$this->logActivity( implode('. ', $status['message']), $this->class .'.', $new_id );
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans('Original :item cannot be read', ['item' => 'Tax rate']);						
			}

			!empty($status) && $this->view->setStatus($status);							

			if ($this->view->html AND !empty($new_id)) {				
				$this->edit($new_id);
			}				
		}		
	}
}