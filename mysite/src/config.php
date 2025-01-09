<?php
//Remove error reporting and set correct timezone in production 
//error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 'Off');
date_default_timezone_set('America/New_York');
//This is the central db which may be replicated to multiple slaves. 
//Slaves read from replicated servers (localhost), write to central db
$config['db']['read' ]['host'] = ['localhost']; //replicated central db
$config['db']['write']['host'] = ['localhost']; //central db
$config['db']['database'] = 'sitegui_mysite';
$config['db']['username'] = 'sitegui_mysql';
$config['db']['password'] = 'set-in-config.local';
#System config
$config['system']['url']        = 'https://admin.litegui.com';
$config['system']['edit_url']   = 'https://edit.litegui.com'; //use a different domain is recommended
$config['system']['base_path']  = ''; //no trailing slash, should be set and update by each application
$config['system']['base_dir']   = dirname(__DIR__); //up one folder as this file in src folder
$config['system']['template']   = 'bootstrap5';
$config['system']['cdn']        = 'https://cdn.litegui.com'; //no trailing slash
$config['system']['language']   = 'en';
$config['system']['table_prefix'] = 'mysite'; 
$config['system']['default_template'] = 'lumen'; //default template for websites that haven't choosen a template
$config['system']['single'] = 1; //stand-alone system
$config['subdomain'] = 'not.in.use';

//Site Config
$config['site']['id'] = 16; //id <= 15 are reserved for future usage
$config['site']['name'] = 'litegui.com';
$config['site']['url']  = 'litegui.com';
$config['site']['status'] = 'Active';
$config['site']['language'] = 'en'; //default site language
$config['site']['locales'] = ['en' => 'en']; //put other languages here if site is multi-lingual
$config['site']['timezone'] = ''; //specify custom timezone or just use the system timezone
$config['site']['template'] = 'lumen'; //site template which is stored under resources/public/templates/site/16
$config['site']['editor'] = 'wysiwyg'; //enable WYSIWYG editor
$config['site']['tier'] = 20; //Site tier, do not change
$config['site']['version'] = '25.01.01.01';
$config['site']['owner'] = 1023; //Owner ID

//Redis Config for async logging and rate limits
try {
    $config['redis'] = new \Redis();
    $config['redis']->connect('127.0.0.1', 6379);
    $config['rate_limits'] = [
        'api' => [
            'max' => 600, //max request, 0 to disable
            'time' => 300, //second
        ],
        'staff' => [
            'max' => 1000, 
            'time' => 300, 
        ],
        'client' => [
            'max' => 1000, 
            'time' => 300, 
        ],
        'public' => [
            'max' => 1500, 
            'time' => 300, 
        ],    
    ];
} catch(\Exception $e){    
    unset($config['redis']);
}

//Put default apps here and then register/install them through App Center to have routes and DB table (if included) activated
/*Value format is [
    'category', 
    'visibility' => 'staff(_readonly)|client(_readonly)', //leave blank for hidden apps
    'configurable' => 'configurable|developer', //leave blank if configuration not required
    tier, //
    'Display Name', //Set display name instead of the original app name
    'permission' => 'Product::create' //hide app for users not having the permission
]*/
$config['activation'] = [
    'LiteGUI\User'                  => ['Management', '', '', 1],
    'SiteGUI\Core\Page'             => ['CMS', 'staff', 'developer', 1],
    'SiteGUI\Core\Product'          => ['CMS', 'staff', 'developer', 1],    
    'SiteGUI\Core\Menu'             => ['CMS', 'staff', '', 1],
    'SiteGUI\Core\File'             => ['CMS', 'staff', '', 1],
    'SiteGUI\Core\Collection'       => ['CMS', 'staff', '', 1],
    'SiteGUI\Core\Widget'           => ['CMS', 'staff', '', 1],
    'SiteGUI\Core\Tax'              => ['Commerce', '', 'configurable', 1],    
    'SiteGUI\Core\Site'             => ['Management', 'staff_readonly', '', 1],
    'SiteGUI\Core\App'              => ['Management', 'staff', '', 1],
    'SiteGUI\Core\Appstore'         => ['Management', 'staff', '', 1],
    'SiteGUI\Core\Template'         => ['Management', 'staff', '', 1],
    'SiteGUI\Core\Layout'           => ['Management', 'staff', '', 1],
    'SiteGUI\Core\Staff'            => ['Management', 'staff', '', 10],
    'SiteGUI\Core\Role'             => ['Management', 'staff', '', 10],
    'SiteGUI\Core\Upgrade'          => ['Management', '', '', 20],
    'SiteGUI\Core\User'             => ['Management', 'staff', 'configurable', 20],
    'SiteGUI\Core\Group'            => ['Management', 'staff', '', 20],
    'SiteGUI\Core\Activity'         => ['Management', 'staff_readonly', '', 10],    
    'SiteGUI\Core\Notification'     => ['Management', '', 'configurable', 1],
    'SiteGUI\Notification\Phpmail'    => ['Management', '', 'configurable', 1],
    'SiteGUI\Notification\Smtpserver' => ['Management', '', 'configurable', 1],
    'SiteGUI\App\Blog'              => ['CMS', 'staff', 'developer', 1],
    'SiteGUI\Widget\Text'           => ['CMS', 'staff', '', 1],
    'SiteGUI\Widget\Editor'         => ['CMS', 'staff', '', 1, 'WYSIWYG Editor'],
];

//Do not change anything below 
ini_set('session.cookie_httponly', true); 
ini_set('session.cookie_secure', true); 
ini_set('session.serialize_handler', 'php_serialize'); //use php_serialize so we can unserialize session data when needed
if(PHP_VERSION_ID < 70300) {
    ini_set('session.cookie_path', '/; samesite=None'); //fix for PHP < 7.3
} else {
    ini_set('session.cookie_samesite', 'None'); 
} 
session_start();
if (empty($_SESSION['token'])) {
    if (function_exists('mcrypt_create_iv')) {
        $_SESSION['token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
    } else {
        $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}
require __DIR__ .'/vendor/Illuminate/autoload.php';
require __DIR__ .'/vendor/Smarty/Smarty.class.php';
if (is_readable(__DIR__ .'/../../config.local.php') ){
   include __DIR__ .'/../../config.local.php';
}
//Register hooks
function addHook($hook, $priority = 10, $f) {
    if (!empty($hook)) {
        $GLOBALS['hooks'][$hook][] = $f;
    }
}
//Register hooks
foreach (glob(__DIR__ ."/hook-file/*.php") as $hookfile) {
    include_once($hookfile); 
}
if ( !empty($GLOBALS['hooks']) ){
    $config['hooks'] = $GLOBALS['hooks'];
    unset($GLOBALS['hooks']);
};
//Autoloading classes
spl_autoload_register(function ($class_name) {
    $class_name = ltrim($class_name, '\\');
    if ($lastNsPos = strrpos($class_name, '\\')) {
        $namespace = substr($class_name, 0, $lastNsPos);
        $class_name = substr($class_name, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName = ($fileName??''). str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
    $fileName = str_replace('SiteGUI/', '', $fileName);

    if (is_readable(__DIR__ . '/' . $fileName)) { //Project root folder
        require __DIR__ . '/' . $fileName;
    } elseif (is_readable(__DIR__ .'/vendor/'. $fileName)) { //vendor/
        require __DIR__ .'/vendor/'. $fileName;
    } 
}); 
?>