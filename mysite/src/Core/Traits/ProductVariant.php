<?php
/*
Product Information: Title, Slug, Collection, Description, Brand, Images, Thumbnail, Tax
Inventory (variants): ID,     ProductID, SKU, Price,             Stock, Shipping, Options {Title, Color, Size} 
----------------------------|----------|----|------|-----------|------|---------|---------------|------|------|
										 1		23				   3							   Red
----------------------------|----------|----|------|-----------|------|---------|---------------|------|------|
										 2		89				   5							   		 Large
----------------------------|----------|----|------|-----------|------|---------|---------------|------|------|

*/
namespace SiteGUI\Core\Traits;

trait ProductVariant
{
	protected $table_product;
	protected $subtype = ['Core', 'App', 'Widget', 'Template', 'Gateway', 'Delivery', 'Fulfillment', 'Notification', 'Hook']; //for Appstore listing (all sites), Product's subtype edit (site 1) -> also update Invoice.php

	final function getTable($table) {
		if ( empty($this->table_product) ) {
           throw new \Exception('Property $table_product must be defined in '. __CLASS__);
       	}
       	return ($table == 'product')? $this->table_product : str_replace('product', $table, $this->table_product);
	}

	protected function updateVariants($pid, $variants) {
		if (!empty($variants)) {
			//$insert_query = $update_query = [];
			$sort = 0;
			foreach($variants AS $variant) {				
				$item = []; //reset $item
				foreach ($variant AS $key => $value) {
					//allow update empty value, do not add 'id' here
					if ( in_array($key, ['price', 'was', 'sku', 'stock', 'shipping', 'images']) ){
						continue;
					}

					if (strpos($key, '@') === 0){
						$item['meta'][ substr($key, 1) ] = $value; //hidden options
					} elseif ($key != 'id') {
						$item['options'][$key] = $value; //combine other options
					}
				}
				//dont add anything $item before this point
				$item['pid'] = intval($pid); //page/product ID
				$item['sku'] = $variant['sku']??null; 
				$item['stock'] = $variant['stock']??null; 
				$item['price'] = round((float) ($variant['price']??0), 2); 
				$item['was'] = round((float) ($variant['was']??0), 2); 
				$item['shipping'] = json_encode( $this->array_remove_by_values($variant['shipping']??[], ['', null, 0]) );
				$item['images'] = json_encode( $this->array_remove_by_values($variant['images']??[], ['', null, 0]) );
				$item['meta'] = json_encode($item['meta']??null);
				$item['options'] = json_encode($item['options']??null);
				$item['`order`'] = $sort++;	
				//dont add anything except id to $item after this point
				$_id = 0; //reset before each iteration				
				if (!empty($variant['id'])) { //update existing variant
					//check if this variant id really belongs to this pid to prevent data tampering
					$_id = $this->db
						->table($this->getTable('product'))
						->where('id', $variant['id'])
						->where('pid', $pid)
						->value('id');
				}
						
				if ($_id) {	
					if (empty($update_columns)) { //need it once
						$update_columns = $update_duplicate = array_keys($item);
						$update_columns[ ] = 'id'; //$item['id'] is not set at this point
					}
					$item['id'] = $_id;
					//$update_args[] = '('. substr(str_repeat(',?', count($item)), 1) .')';
					foreach ($item as $it) {
						$update_data[] = $it;
					}
				} else {//brand new product or copied product
					if (empty($insert_columns)) {
						$insert_columns = array_keys($item);
					}	
					//$insert_args[] = '('. substr(str_repeat(',?', count($item)), 1) .')';
					foreach ($item as $it) {
						$insert_data[] = $it;
					}					
				}
			}
			// both existing and new variants submitted in a single post, run both update and insert 
			if (!empty($insert_data)) {
				//$insert_query = 'INSERT INTO '. $this->getTable('product') .'('. $insert_columns .') VALUES'. implode(",", $insert_args); 
				if ($this->upsert($this->getTable('product'), $insert_columns, $insert_data, ['id' => 'id']) ) {
					//$status['message'][] = $this->trans('Products added successfully');						
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not added', ['item' => 'Product variant']);	
				}
			}

			if (!empty($update_data)) {
				try {
					if ($this->upsert($this->getTable('product'), $update_columns, $update_data, $update_duplicate)) {
						//$status['message'][] = $this->trans(':item updated successfully', ['item' => 'Products']);						
					}											
				} catch (\Exception $e) {
					//print_r($e);
					$status['result'] = 'error';
					$status['message'][] = $this->trans(':item was not updated', ['item' => 'Product variant']);	
				}							
			}
			!empty($status) && $this->view->setStatus($status);		
		}	
	}
	//increase/decrease stock for variant
	public function changeStock($id, $change = 0) {
		if ($change) {
			try {
				return $this->db
					->table($this->getTable('product'))
					->where('id', $id)
					->when($change > 0, function ($query) use ($change) {
						return $query->increment('stock', $change);
					}, function ($query) use ($change) {
						return $query->decrement('stock', -$change);
					});
			} catch (\Exception $e){
				if ($e->getCode() == 22003) { //negative number for unsigned stock 
					return $this->db
						->table($this->getTable('product'))
						->where('id', $id)
						->update(['stock' => 0]);
				}	
			}		
		}
		return false;
	}	
	//copy variants from one product to another
	protected function copyVariants($old_pid, $new_pid) {
		return $this->cloneRows( $this->getTable('product'), null, 'sku, price, stock, shipping, options, meta, images, `order`', 'pid', $old_pid, $new_pid );
	}

	//delete specified variant
	protected function deleteThisVariant($id, $pid = null) { //ugly name as trait method replaced by class method
		if ($this->db
			->table($this->getTable('product'))
			->when($pid, function($query, $pid){
				return $query->where('pid', $pid);
			})
			->delete(intval($id)) 
		){
			return true;	
		} else {
			return false;		
		}
	}

	//delete all variants of specified product
	protected function deleteVariants($pid) {			
		if ($this->db
			->table($this->getTable('product'))
			->where('pid', intval($pid))
			->delete() 
		){
			return true;	
		} else {
			return false;		
		}
	}

	//returns specified variant
	protected function getVariant($id, $with_info = 0) {
		$item = $this->db
			->table($this->getTable('product'))
			->where('id', intval($id))
			->select('id', 'pid', 'sku', 'price', 'was', 'stock', 'shipping', 'options', 'meta', 'images')
			->first();
		if (!empty($item['shipping'])) {
			$item['shipping'] = json_decode($item['shipping']??'', true);
		}
		if (!empty($item['options'])) {
			$item['options'] = json_decode($item['options']??'', true);
		}
		if (!empty($item['meta'])) {
			$item['meta'] = json_decode($item['meta']??'', true);
		}
		if (!empty($item['images'])) {
			$item['images'] = json_decode($item['images']??'', true);
		} 	
		
		if ( !empty($with_info) AND !empty($item['pid'])) { //for Cart
			unset($item['meta']); //remove hidden options
			$product = $this->db
				->table($this->getTable('page'))
				->select('slug', 'name', 'type', 'subtype', 'image', 'creator')
				->where('id', intval($item['pid']))
				//support any type ->where('type', 'Product')
				->first();
			$collections = $this->db
				->table($this->getTable('location'))
				->where('page_id', $item['pid'])
				->where('app_type', 'collection')
				->pluck('app_id')
				->all();
			//$product['data'] = json_decode($product['data'], true);
			$item['product']['subtype'] = $product['subtype'];
			$item['product']['slug'] = $product['slug']; //for SG template order
			$item['product']['creator'] = $product['creator']; 
			$item['product']['name'] = $this->getRightLanguage(json_decode($product['name']??'', true));
			if ( !empty($item['options']) ){
				$item['product']['name'] .= ' - '. implode(' - ', $item['options']);
			}  
			$item['product']['collections'] = $collections;
			$item += (array) $this->db
				->table($this->getTable('page'). 'meta')
				->where('page_id', intval($item['pid']))
				->whereIn('property', ['tax_rate', 'shipping_fee', 'dynamic_price', 'auto_setup', 'product_fields'])
				->pluck('value', 'property')
				->all();
			if ( !empty($item['product_fields']) ){
				$item['product_fields'] = json_decode($item['product_fields']??'', true);
				foreach ($item['product_fields'] AS $key => $field ){
					//remove fields other than client_editable/readonly
					if ($field['visibility'] != 'client_editable' AND $field['visibility'] != 'client_readonly') {
						unset($item['product_fields'][ $key ]);
						continue;
					} elseif ( $field['visibility'] == 'client_readonly' ){ //adjust visibility for displaying purpose
						$item['product_fields'][ $key ]['visibility'] = 'readonly';
					}
				}	
			}	
			$item['images'] = [ $item['images'][0]??$product['image'] ]; //just need one image
		}			
		
		return !empty($item)? $item : null;	
	}

	//returns an array of variants for specified product
	protected function getVariants($pid, $with_meta = 0) {
		$results = $this->db
			->table($this->getTable('product'))
			->select('id', 'sku', 'price', 'was', 'stock', 'shipping', 'options', 'images')
			->when($with_meta, function($query){
				return $query->addSelect('meta');
			})
			->where('pid', intval($pid))
			->orderBy('order')
			->get()->all();
		foreach ($results AS $item) {
			if (!empty($item['shipping'])) {
				$item['shipping'] = json_decode($item['shipping']??'', true);
			}
			if (!empty($item['options'])) {	
				$item['options'] = json_decode($item['options']??'', true);
			}	
			if (!empty($item['meta'])) {
				$item['meta'] = json_decode($item['meta']??'', true);
			}
			if (!empty($item['images'])) {
				$item['images'] = json_decode($item['images']??'', true);
			}
			if (!empty($item['options']) AND !$with_meta){	
				$i = 1;
				$item['options_hex'] = '';
				foreach( $item['options']??[] AS $key => $opt ){
					$item['options_hex'] .= " opt-". $i++ ."-". bin2hex($opt) ."-x"; //hex used by frontend for filtering variants
					//let frontend decide if ($i > 3) break;
				}
			}	
			$variants[] = $item; //do not set key for the variant array
		}
		return !empty($variants)? $variants : null;	
	}

	//Below are methods accepting input as an array of products
	//returns an array of variants for multiple products, shipping/meta may not needed
	protected function getProductsVariants($pids_array) {
		$results = $this->db
			->table($this->getTable('product'))
			->select('id', 'pid', 'sku', 'price', 'was', 'stock', 'options', 'images')
			->whereIn('pid', $pids_array)
			->where('price', '>=', 0) //excluding negative value
			->orderBy('order')
			->get()->all();
		foreach ($results AS $item) {
			if (!empty($item['shipping'])) {
				$item['shipping'] = json_decode($item['shipping']??'', true);
			}
			if (!empty($item['options'])) {	
				$item['options'] = json_decode($item['options']??'', true);
			}
			if (!empty($item['meta'])) {
				$item['meta'] = json_decode($item['meta']??'', true);
			}
			if (!empty($item['images'])) {
				$item['images'] = json_decode($item['images']??'', true);
			}
			$variants[$item['pid']][] = $item;
		}
		return !empty($variants)? $variants : null;	
	}

	//returns an array of mix options for a specified product => may not needed
	protected function getOptionsVariants($pid) {
		$results = $this->db
			->table($this->getTable('product'))
			->select('id', 'sku', 'price', 'stock', 'options', 'images')
			->where('pid', $pid)
			->where('price', '>=', 0) //excluding negative value
			->orderBy('order')
			->get()->all();
		$variants = [];	

		foreach ($results AS $item) {
			$item['options'] = json_decode($item['options']??'', true);
			if (!empty($item['images'])) {
				$item['images'] = json_decode($item['images']??'', true);
			}
			//turn options into nested option array, keep minimum levels
			$ref = & $variants;
			foreach ( ($item['options']??[]) AS $option => $value ){
				if ( !isset($ref['name']) ){
					$ref['name'] = $option;
				} 				
				$ref = & $ref[ $value ];				
			}
			$ref['variant'] = $item['id'];
		}
		return $variants??null;	
	}

	//returns an array of cheapest variants, i.e: starting price of each product, complex join to get associated column of min
	protected function getProductsMinPrice($pids_array) {
		$results = $this->db
			->table($this->getTable('product') .' AS p1')
			->leftJoin($this->getTable('product') .' AS p2', function ($join) {
	            $join->on('p1.pid', '=', 'p2.pid')
	        	  ->on('p1.price', '>', 'p2.price');
	        })
			->select('p1.pid', 'p1.price', 'p1.was')
			->whereIn('p1.pid', $pids_array)
			->whereNull('p2.price') //so p1.price is min
			->get()->all();
		if ($results) {
			$results = array_column($results, null, 'pid');
		}
		return $results;	
	}

	//returns an array of all variant options, so we can filter them
	protected function getProductsOptions($pids_array) {
		$results = $this->db
						->table($this->getTable('product'))
						->select('pid', 'options')
						->whereIn('pid', $pids_array)
						->where('price', '>=', 0) //excluding negative value
						->get()->all();
		foreach ($results AS $item) {
			$item['options'] = json_decode($item['options']??'', true);
			foreach ( ($item['options']??[]) as $key => $value) {
				!empty($value) && ($options[$key][$value] = $value);
			}
		}
		return !empty($options)? $options : null;		
	}	

	public function currencyFormat($view = false) {
		$settings = $this->getAppConfigValues('Core\\Product', null);
		$block['html']['currency']['code']   	= $settings['currency']??'USD';	   
		$block['html']['currency']['prefix'] 	= $settings['prefix']??'$';	   
		$block['html']['currency']['suffix'] 	= $settings['suffix']??'';	   
		$block['html']['currency']['precision'] 	= $settings['precision']??2;
		
		if ($view){
			$this->view->addBlock('main', $block, 'Product::currencyFormat'); 
		}	
		return $block['html']['currency'];
	}
}