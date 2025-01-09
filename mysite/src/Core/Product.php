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
namespace SiteGUI\Core;

class Product extends Page {
	use Traits\ProductVariant; //$table_product needs constructed

	public function __construct($config, $dbm, $router, $view, $user){
		parent::__construct($config, $dbm, $router, $view, $user);
		$this->table_product = $this->site_prefix ."_product";
	}

	public static function config($property = '') {
		$config['app_visibility'] = 'staff';
		$config['subapp_support'] = 1; //allow adding subapp via Appstore configure
	    $config['app_permissions'] = [
	    	'staff_read'   => 1,
	    	'staff_write'  => 1,
	    	'staff_manage' => 1,
	    	'staff_read_permission'   => "Product::create",
	    	'staff_write_permission'  => "Product::create",
	    	'staff_manage_permission' => "Product::publish",
	    ];	
	    $config['app_configs'] = [
	        'text_selection' => [
	            'label' => 'Variant Selection',
	            'type' => 'checkbox',
	            'description' => "Use variant's option name instead of variant's thumbnail for variant selection",
	            'value' => '',
	        ],
	    	'check_availability' => [
	            'label' => 'Check Stock Availability',
	            'type' => 'checkbox',
	            'description' => 'Allow checking stock availability across multiple store on the product page. Inventory app must be installed.',
	            'value' => '',
	        ],
	        'limited_stock' => [
	            'label' => 'Limited Stock Level',
	            'type' => 'text',
	            'description' => 'The stock level that is considered as Limited Stock',
	            'value' => '2',
	        ],
	        /*'currency' => [
	            'label' => 'Currency Code',
	            'type' => 'text',
	            'description' => 'Specify the currency for the product prices. Use the ISO 4217 currency code',
	            'value' => 'USD',
	        ],
	        'prefix' => [
	            'label' => 'Currency Prefix',
	            'type' => 'text',
	            'description' => 'Prefix for currency value e.g: $',
	            'value' => '$',
	        ],
	        'suffix' => [
	            'label' => 'Currency Suffix',
	            'type' => 'text',
	            'description' => 'Enter suffix if prefix is not used',
	            'value' => '',
	        ],
	        'precision' => [
	            'label' => 'Rounding Precision',
	            'type' => 'text',
	            'size' => '6',
	            'value' => '2',
	            'description' => 'Round to the number of digits after the decimal point',
	        ],
	        'attributes' => [
	            'label' => 'Product Attributes',
	            'type' => 'fieldset',
	            'size' => '6',
	            'value' => '',
	            'description' => 'Enter default attributes for your products here',
	            'fields' => [
			        'name' => [
			            'label' => 'Attribute Name',
			            'type' => 'text',
			            'size' => '6',
			            'value' => '',
			            'description' => '',
			        ],
	            ],
	        ],*/
	    ];
    	return ($property)? ($config[ $property ]??null) : $config;
    }

	public function generateRoutes($extra = []) {
		$extra['action'] 		= ['GET|POST', '/[i:site_id]/product/[edit|copy|manage:action]/[*:id]?.[json:format]?'];
		$extra['update']        = ['POST', '/[i:site_id]/product/update.[json:format]?/[POST:page]?'];
		$extra['deleteVariant'] = ['POST', '/[i:site_id]/product/variant/delete.[json:format]?/[POST:id]?'];
		$extra['updateStock'] 	= ['POST', '/[i:site_id]/product/stock.[json:format]?/[POST:variants]?'];

		$routes = $this->trait_generateRoutes($extra);
        //$routes['SiteUser']['Product::render']           = ['GET', '/store/[*:slug]?.[html|json:format]?'];
        //$routes['SiteUser']['Product::renderCollection'] = ['GET', '/collection/[*:slug]?.[html|json:format]?'];
        return $routes;
	}

	//From sandbox page: GET loads this action in the main page for deleting/unlinking through POST request (required valid CSRF token)
	public function manage($id) {
		if ( is_numeric($id) AND $this->view->html ){
			$links['delete'] = $this->slug('Product::deleteVariant');
			$links['delete'] .= !empty($this->config['system']['sgframe'])? '?sgframe=1' : '';
			$block['links'] = $links;	
			$block['api']['sub']['id'] = $id;				
			$this->view->setLayout('iframe'); 
			$this->view->addBlock('main', $block, $this->class .'::manage');	
		}		
	}
	//delete specified variant
	public function deleteVariant($id) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('delete');
		} 

		$variant = $this->getVariant($id);
		if ($variant) {
			$product = $this->read($variant['pid']);
			if ( !$this->user->has($this->requirements['PUBLISH']) AND $product["published"] == 1){	//Published page cannot be deleted by unauthorized admin		
				$status['result'] = 'error';
				$status['message'][] = $this->trans('You cannot delete variant of a published product');
			} else {			
				if ($this->deleteThisVariant(intval($id))) {
					$status['result'] = 'success';
					$status['message'][] = $this->trans(':item deleted successfully', ['item' => 'Variant']);				
				} else {
					$status['result'] = 'error';
					$status['message'][] = $this->trans('Product variant cannot be deleted');				
				}
			}
			$next_actions[$this->class .'::edit'] = [ $variant['pid'] ];
			$this->router->registerQueue($next_actions);	
		} else {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('No such :item', ['item' => 'variant']);			
		}	

		$status['api_endpoint'] = 1;
		$this->view->setStatus($status);	
		$this->logActivity( implode('. ', $status['message']), $this->class .'.', $variant['id']??null );
	}
	//update one or many variants' stock using sku
	public function updateStock($variants) {
		if ( ! $this->user->has($this->requirements['CREATE']) ){
			$this->denyAccess('delete');
		} 
		if ( !empty($variants) AND is_array($variants) ){
			foreach($variants AS $variant){
				if ( !empty($variant['sku']) AND !empty($variant['stocks']) ){
					$stocks = [];
					foreach($variant['stocks'] AS $s => $i){
						$stocks[ $s ] = intval($i);
					}
					$update[] = [
						'sku' => $variant['sku'],
						'stocks' => $stocks,
						//'action' => $variant['action']??'+',
						//'store' => $variant['store']??null
					];
				}
			}
			/*if ($this->db
				->table($this->getTable('product'))
				->where('sku', $sku)
				->update([
					'stock' => intval($stock) 
				])
			)/*/
			if ( !empty($update) ){
				$status['api']['update'] = $update;
				$status['message'][] = $this->trans(':item updated successfully', ['item' => 'Stock']);
			} else {
				$status['result'] = 'error';
				$status['message'][] = $this->trans(':item was not updated', ['item' => 'Stock']);
			}	
		} else {
			$status['result'] = 'error';
			$status['message'][] = $this->trans('Invalid SKU or stock count');
		}
		$this->view->setStatus($status);	
		//$this->logActivity( implode('. ', $status['message']) .'! Variant SKU '. $sku .'. Stock: '. $stock);
	}	
	//Pre-process custom fields before passing to Page
	public function update($page) {
		$page['meta']['product_fields'] = null;
		foreach ( ($page['product_fields']??[]) AS $f) {
			$field = [];
			$f['name'] = $this->sanitizeFileName($f['name']);
			if ($f['name'] AND $f['type']) {
				$field['type'] = $f['type'];
				$field['label'] = $f['label'];
				$field['description'] = $f['description'];
				$field['value'] = $f['value'];

				if (in_array($f['visibility'], [
					'client_editable', 
					'client_readonly', 
					'client_hidden', 
					'editable', 
					'readonly', 
					'hidden',
				]) ){
					$field['visibility'] = $f['visibility'];						
				}
				if (!empty($f['required'])) {
					$field['required'] = $f['required'];						
				}
				if (!empty($f['admin_only'])) {
					$field['admin_only'] = $f['admin_only'];						
				}					
				if (!empty($f['fieldset'])) {
					$field['fieldset'] = $f['fieldset'];						
				}															
				if ($f['type'] == 'lookup' AND !empty($f['multiple'])) {
					//$field['multiple'] = $f['multiple'];						
				}
				if (in_array($f['is'], ['optional', 'required', 'multiple'])) {
					$field['is'] = $f['is'];						
				}
				if (in_array($f['type'], ['select', 'radio', 'radio hover']) AND $f['options']) { //options value
					foreach ( ($f['options']??[]) AS $o) {
						$field['options'][ $o ] = $o;
					}
				} 

				if (!empty($f['fieldset'])) {
					$page['meta']['product_fields']['fieldset1']['type'] = 'fieldset';
					$page['meta']['product_fields']['fieldset1']['fields'][ $f['name'] ] = $field;
				} else {	
					$page['meta']['product_fields'][ $f['name'] ] = $field;
				}									
			}	
		}
		unset($page['product_fields']);
		if (($page['subtype']??null) != 'Shipping'){
			foreach ($page['variants'] AS $i => $variant){
				unset($page['variants'][ $i ]['shipping']);
			}
			$page['meta']['shipping_fee'] = '';
		}

		parent::update($page);
	}	
	/**
	 * print out edit form
	 * @param  integer $id [description]
	 * @return [type]           [description]
	 */
	public function edit($id = 0, $menus = 'Menu::getMenus', Tax $taxObj = null){
		if ( $this->user->has($this->requirements['CREATE']) OR ($this->user->has($this->requirements['READ']) AND $id) ){
			if ( strpos($id, 'variant/') !== false ){ //show product by variant id - for variant lookup field 
				$id = $this->db
					->table($this->table_product)
					->where('id', substr($id, 8) )
					->value('pid');
			}
			parent::edit($id, $menus); //Page->edit
			if ( empty($_REQUEST['frame']) ){
				$block = $this->view->getBlock('main', 'Product::edit');

				$page_info = $block['api']['page']??null;
				if( !empty($page_info['id']) AND $page_info['type'] == 'Product' ){
					$block['api']['variants'] = $this->getVariants($page_info['id'], 'with_meta'); 
				}
				if ( !empty($page_info['meta']['related']) ){ //related products
					$block['api']['page']['meta']['related'] = $this->lookupById('product', $page_info['meta']['related']);
				}
				if ( !empty($page_info['meta']['groups']) ){ //automatic buyer groups
					$groups = $this->lookupById('groups', $page_info['meta']['groups']);
					$block['api']['page']['meta']['groups'] = $groups['rows'];
					$block['links']['group'] = $groups['slug']??'';
				}
				$block['api']['groups'] = $this->db
					->table(str_replace('_user', '_config', $this->table_user))
					->where('type', 'db')
					->where('object', 'Group')
					->pluck('property AS name', 'id')
					->all() ?: [ $this->trans("Please create client groups first") ];
				if ( $this->site_id === 1 AND !empty($this->subtype) ){//for Site 1's Appstore
					$block['api']['fulfillments'] = array_combine($this->subtype, $this->subtype); //['value' => 'value']
				}
				$fulfillments = $this->db->table($this->site_prefix .'_config')
					->where('type', 'activation')
					->where('name', 'LIKE', '%\\\\Fulfillment\\\\%')
					->pluck('name')->all();
				foreach ($fulfillments as $fulfillment) {
					$fulfillment = substr($fulfillment, strrpos($fulfillment, '\\') + 1); //last part
					$block['api']['fulfillments'][ $fulfillment ] = $this->formatAppLabel($fulfillment);
				}

				$block['api']['taxes'] = $taxObj->list();
				$block['api']['app']['hide']['tabapp'] = 0;
				if ($this->view->html){				
					$block['template']['file'] = "product_edit";		
					$links['lookup'] = $this->slug('Lookup::now');
					$block['links']['lookup'] = $links['lookup'] .'?cors='. @$this->hashify($links['lookup'] .'::'. $_SESSION['token']);
					$block['links']['manage'] = $this->slug('Product::action', ['action' => 'manage']);
					$block['html']['config']['mass_unit'] = $this->getAppConfigValues('Core\\Shipping', null, 'mass_unit');
					$block['html']['config']['distance_unit'] = $this->getAppConfigValues('Core\\Shipping', null, 'distance_unit');
					//$block['html']['config']['currency'] = $this->currencyFormat();
					//check user's onboarding
					$block['html']['onboard_product'] = $this->user->meta('onboard_product');
					$block['links']['onboard'] = $this->slug('Staff::onboard');
					$block['links']['onboard'] .= '?cors='. $this->hashify($block['links']['onboard'] .'::'. $_SESSION['token']);
					if ( empty($page_info['meta']['upload_dir']) ){
						$upload_dir = 'products';
					} else {
						$upload_dir = $page_info['meta']['upload_dir'];
					}	
					$block['html']['upload_dir'] = 'elf_l1_'. rtrim(strtr(base64_encode($upload_dir), '+/=', '-_.'), '.');
				}

				$this->view->addBlock('main', $block, 'Product::edit');
			}	
		} else {
			$this->denyAccess($id? 'edit' : 'create');			
		}					
	}

	public function render($full_path_without_query = '') { //This method used for unauthenticated users, be careful
		$page = parent::render($full_path_without_query);
		
		if ( empty($_REQUEST['subapp']) AND !empty($page['id']) AND is_numeric($page['id']) ){ //except renderIndex where id = type
			$block = $this->view->getBlock('main', 'Product::render');
			$block['api']['variants'] = $this->getVariants( $page['id'] );
			if ( !empty($block['api']['page']['meta']['related']) ){ 
				$related = $this->lookupRelatedPages($block['api']['page']['meta']['related'], 'Product');
				if ( !empty($related) ){
					$variants = $this->getProductsVariants($block['api']['page']['meta']['related']);
					$prices   = $this->getProductsMinPrice($block['api']['page']['meta']['related']);

					foreach ($related AS $i => $product) {
						$product['price'] = (!empty($prices[ $product['id'] ]['price']) AND $prices[ $product['id'] ]['price'] > 0)? $prices[ $product['id'] ]['price'] : 0; //negative price
						$product['was'] = $prices[ $product['id'] ]['was']??null;
						$product['variants'] = $variants[ $product['id'] ]??null;
						$related[ $i ] = $product;	
					}
					$block['api']['page']['meta']['related'] = $related;
				}
    		}	
			$next_actions['Collection::getRelatedItems'] = [ $page['id'], "slug" => 1, "pricing" => 1 ];

			if ($this->view->html){
				$block['html']['text_selection'] = $this->getAppConfigValues('Core\\Product', [], 'text_selection');
				$block['html']['stock_checking'] = $this->getAppConfigValues('Core\\Cart', [], 'stock_checking');
				$block['html']['check_availability'] = $this->getAppConfigValues('Core\\Product', [], 'check_availability');
				if ($block['html']['check_availability']){
					$block['links']['inventory'] = $this->slug('Inventory::render'); 
				}
				$link = $this->slug('Cart::add');				
				$block['links']['cart_add'] = 'https://'. $this->config['site']['account_url'] . $link .'?cors='. $this->hashify($link .'::'. $_SESSION['token']);
			}
			$this->view->addBlock('main', $block, 'Product::render');
			//$this->currencyFormat('view');
		}

		if (!empty($next_actions)){
			$this->router->registerQueue($next_actions);	
		}
			
		return $page;
	}

	protected function renderIndex($app = '', $template = null) {
		return parent::renderIndex($app);
	}

	public function renderCollection($full_path_without_query = '') { //This method used for unauthenticated users, be careful
		$page = $this->readSlug(trim($full_path_without_query, '/'), 'Collection', $this->class);
		if ( !empty($page['id']) ){
			$query = $this->db
				->table($this->table .' AS page')
				->join($this->site_prefix .'_location', 'page_id', '=', 'page.id')
				->where('app_type', 'collection')
				->where('app_id', $page['id'])
				->where('page.type', $this->class)
				->select('page.id', 'page.type', 'page.subtype', 'page.slug', 'page.name', 'page.title', 'page.description', 'page.updated', 'page.image', 'page.public')
				->orderBy('updated', 'desc');
			$query = $this->getPublished($query);

			if ( empty($_REQUEST['current']) ){
				$_REQUEST['current'] = 1; //force non-ajax mode 
			}	
			$block = $this->pagination($query);
			
			if ( !empty($block['api']['rows']) ){
				foreach ($block['api']['rows'] AS $index => $item) {
					$item = $this->preparePage($item);
					//$pids_array[ $item['id'] ] = $item['id'];
					if ( !in_array($item['subtype'], $this->subtype) ){
						unset($item['subtype']); //for Appstore
					}		
					$item['price']    = & $pids_array[ $item['id'] ]['price']; //reference to a future value
					$item['was'] 	  = & $pids_array[ $item['id'] ]['was']; 
					$item['variants'] = & $pids_array[ $item['id'] ]['variants']; 
					$block['api']['rows'][ $index ] = $item;				
				}
				$pids_array_keys = array_keys($pids_array);
				//Get options before changing $pids_array values
				$page['collection_options'] = $this->getProductsOptions($pids_array_keys);				
				$variants = $this->getProductsVariants($pids_array_keys);
				$prices   = $this->getProductsMinPrice($pids_array_keys);

				foreach ($prices??[] as $index => $value) {
					$pids_array[ $index ]['price'] = ($value['price'] > 0)? $value['price'] : 0; //negative price
					$pids_array[ $index ]['was'] = $value['was'];
					$pids_array[ $index ]['variants'] = $variants[ $index ]??null;
				}
			}	
			!empty($status) && $this->view->setStatus($status);

			$block['template']['file'] = "product_collection";		
			$block['api']['page'] = $page;			
			
			if ($this->view->html){
				$block['html']['stock_checking'] = $this->getAppConfigValues('Core\\Cart', [], 'stock_checking');
				$link = $this->slug('Cart::add');				
				$block['links']['cart_add'] = 'https://'. $this->config['site']['account_url'] . $link .'?cors='. $this->hashify($link .'::'. $_SESSION['token']);
			}
			$this->view->addBlock('main', $block, $this->class .'::renderCollection');
			//$this->currencyFormat('view');

			$next_actions['Collection::getCollectionItems'] = [ $this->class, $page['id'], "slug" => 1 ];
			if (!empty($next_actions)){
				$this->router->registerQueue($next_actions);	
			}

			return $page;
		} else {
			return false;
		}
	}		
}