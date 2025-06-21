<?php
if (!defined('ECLO')) die("Hacking attempt");

use ECLO\App;

$env = parse_ini_file('.env');
$getlang = $_COOKIE['lang'] ?? 'vi';
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/etpl.php';


$dbConfig = [
	'type' => $env['DB_TYPE'] ?? 'mysql',
	'host' => $env['DB_HOST'] ?? 'localhost',
	'database' => $env['DB_DATABASE'] ?? 'default_database',
	'username' => $env['DB_USERNAME'] ?? 'default_user',
	'password' => $env['DB_PASSWORD'] ?? '',
	'charset' => $env['DB_CHARSET'] ?? 'utf8mb4',
	'collation' => $env['DB_COLLATION'] ?? 'utf8mb4_general_ci',
	'port' => (int) ($env['DB_PORT'] ?? 3306),
	'prefix' => $env['DB_PREFIX'] ?? '',
	'logging' => filter_var($env['DB_LOGGING'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
	'error' => constant('PDO::' . ($env['DB_ERROR'] ?? 'ERRMODE_SILENT')),
	'option' => [
		PDO::ATTR_CASE => PDO::CASE_NATURAL,
	],
	'command' => [
		'SET SQL_MODE=ANSI_QUOTES'
	]
];
$app = new App($dbConfig);
$jatbi = new Jatbi($app);
$etpl = new etpl();
$view = view::get_instance();
$setting = [
	"url" 		=> $env['SITE_URL'] ?? '',
	"name"		=> $env['SITE_NAME'] ?? '',
	"page"		=> 24,
	"template"	=> $env['TEMPLATE'] ?? '/templates',
	"secret-key" =>  $env['JWT_SECRET_KEY'],
	"verifier"	=> $env['JWT_VERIFIER'],
	"cookie"	=> (3600 * 24 * 30) * 12, // 1 năm
	"lang" 		=> $getlang,
	"backend"	=> $env['ADMIN_PATH'] ?? "/manager",
	"domain"	=> $env['DOMAIN'] ?? 'http://localhost:8080',
	"mediaUrl"	=> "/datas/imgs/",
	"maxUploadSize" => 5 * 1024 * 1024, // 5MB
];
$app->setValueData('setting', $setting);
$app->setValueData('jatbi', $jatbi);
$app->setValueData('etpl', $etpl);
$app->setValueData('common', $common);
$app->setValueData('view', $view);
$app->JWT($setting['secret-key'], 'HS256');

$router = explode('/', $_SERVER['REQUEST_URI']);
if ($router[1] == explode("/", $setting['backend'])[1]) {
	// lang backend
	$langFile = dirname(__DIR__, 2) . "/templates/lang/backend/$getlang.php";
	if (file_exists($langFile)) {
		include $langFile; // sẽ gán biến $lang
	}

	$app->setGlobalFile(__DIR__ . '/global.php');
	require_once __DIR__ . '/requests.php';
	$jatbi->checkAuthenticated($requests);
	$app->setValueData('permission', $SelectPermission);
	foreach ($setRequest as $items) {
		$app->request($items['key'], $items['controllers']);
	}
} else {
	// lang frontend
	$langFile = dirname(__DIR__, 2) . "/templates/lang/frontend/$getlang.php";
	if (file_exists($langFile)) {
		include $langFile; // sẽ gán biến $lang
	}

	require_once __DIR__ . '/requests-frontend.php';
	foreach ($setRequest as $items) {
		$app->request($items['key'], $items['controllers']);
	}
}

require_once __DIR__ . '/components.php';
