<?php
namespace SiteGUI\App;
 
class Blog {
   protected $config; 
   protected $view; 
   
   public function __construct($site_config, $view){
      $this->config = $site_config;
      $this->view	= $view;
   }

   public static function config($property = null) {
      $config['app_permissions'] = [
         'staff_read' => 1,
         'staff_write' => 1,
         'staff_manage' => 1,
         'client_read' => 1,
      ];
      $config['app_category'] = 'CMS';
      $config['app_handler'] = '';
      $config['app_users'] = 'staff_client_guest';
      $config['app_hide']['tabapp'] = 1;

      /* a text field type allows for single line text input */
      $config['app_configs'] = [
         'name' => [
            'label' => 'Blog Name',
            'type' => 'text',
            'size' => '6',
            'value' => '',
            'description' => '',
         ],
         'banner' => [
            'label' => 'Banner',
            'type' => 'file',
            'size' => '6',
            'description' => 'Choose an image as the banner for your Blog',
         ],         
         'show_author' => [
            'label' => 'Show Author Name and Date',
            'type' => 'checkbox',
            'description' => '',
            'value' => 0,
         ],
         'grid_mode' => [
            'label' => 'Grid Mode',
            'type' => 'checkbox',
            'description' => 'Display blog entries in Grid mode',
            'value' => 0,
         ],
         'grid_column' => [
            'type' => 'select',
            'label' => 'Columns',
            'description' => 'Number of columns when blog entries are displayed in Grid mode',
            'is' => 'required',
            'value' => 3,
            'options' => [
               '1' => '1',
               '2' => '2',
               '3' => '3',
               '4' => '4',
            ],
         ],   
      ];
      if ($property AND isset($config[ $property ]) ) {
         $response[ $property ] = $config[ $property ];
      } else {
         $response['config'] = $config;
      } 

      $response['result'] = 'success';
      return $response;
   }        

   function edit($page) {		
      if ( !empty($page) ){
         $app['hide'] = [
            'description' => 1, 
            /* 'content' => 1, */
         ];
         /*$app['fields'] = [
            // a text field type allows for single line text input 
            'username' => [
               'label' => 'Author',
               'type' => 'text',
               'size' => '6',
               'value' => '',
               'description' => 'Enter your name here',
            ],
         ];*/

         /*$response['blocks']['main']['template']['layout'] = "blank"; 	
         $response['blocks']['main']['template']['file'] = "blog_edit";*/
         $response['blocks']['main']['api']['page'] = $page;
         //$response['blocks']['main']['api']['app'] = $app;
         /*$response['blocks']['header']['output'] = 'Extra content';*/				
      }

      $response['result'] = 'success';
      return $response;
   }

   function update($page){
      if ( !empty($page) ){
         $response['result'] = 'success';
         $response['page']   = $page;	
      } else {
         $response['result']  = 'error';
         $response['message'] = 'Invalid submitted data';
      }

      return $response;
   }

   function render($page){
      if ( !empty($page) ){ 
         $page['title'] .= ' - '. $this->config['site']['name'] .' '. $page['subtype']; 
         $response['result'] = 'success';
         /* $page['content'] = html_entity_decode($page['content']); 
         $block['template']['string'] = base64_encode('<h2>{$api.page.name}</h2>
            {if $api.page.image}
               <div class=""><img src="{$api.page.image}"></div>
            {/if}
            {$api.page.content nofilter}
            Collection: 
            {foreach $api.collections as $collection}
               <a href="{$collection.slug}" rel="tag">{$collection.name}</a>
            {/foreach}
         ');*/
         $block['api']['page'] = $page;
         $block['api']['config']['name']   = $this->config['app']['name']??'';
         $block['api']['config']['banner'] = $this->config['app']['banner']??[];
         $block['html']['show_author'] = $this->config['app']['show_author']??0;

         $block['template']['file'] = 'blog_page';
         $response['blocks']['main'] = $block;
         /* $response['blocks']['top']['order'] = 0;
         $response['blocks']['top']['api']['order'] = 1;
         $response['blocks']['top']['menu'] =  [ 
            0 => [
               'name' => 'Blog Add',
               'slug' => '/category/blog_add',
               'children' => [
                  19 => [
                     'type' => 'Page',
                     'name' => 'New Post',
                     'slug' => '/new-custom-post'
                  ],
                  31 => [
                     'type' => 'Page::Collection',
                     'name' => 'News',
                     'slug' => '/category/news-category'
                  ]
               ] 
            ]    
         ];

         $response['blocks']['footer']['output'] = 'Extra footer content';			
         $response['next_actions']['Collection::getCollectionsByPageId'] = ["pid" => $page["id"], "slug" => 1]; */
         /* $response['next_actions']['Collection::getRelatedItems']        = ["pid" => $page["id"], "slug" => 1]; */
      } else {
         $response['result']  = 'error';
         $response['message'] = 'Invalid submitted data';
      }

      return $response;
   }

   function renderIndex($block){
      return $this->renderCollection($block);
   }  

   function renderCollection($block){
      if ( !empty($block) ){ 
         $response['result'] = 'success';
         $block['api']['config']['name']   = $this->config['app']['name']??'';
         $block['api']['config']['banner'] = $this->config['app']['banner']??[];
         $block['html']['show_author'] = $this->config['app']['show_author']??0;
         $block['html']['grid_mode']   = $this->config['app']['grid_mode']??0;
         $block['html']['grid_column'] = $this->config['app']['grid_column']??3;

         $block['template']['file'] = 'blog_collection';
         $response['blocks']['main'] = $block;
      } else {
         $response['result']  = 'error';
         $response['message'] = 'Invalid submitted data';
      }

      return $response;
   }	
}
