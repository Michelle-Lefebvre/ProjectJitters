<?php
session_start();
require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel can have different loggers for different media
$log = new Logger('main');
$log->pushHandler(new StreamHandler(dirname(__FILE__) . '/logs/everything.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler(dirname(__FILE__) . '/logs/errors.log', Logger::ERROR));

if (strpos($_SERVER['HTTP_HOST'], "ipd21.com") !== false) {
    DB::$dbName = 'cp4976_jitters'; 
    DB::$user = 'cp4976_jitters';   
    DB::$password = 'TUwSaMWzXbLY'; 
} else {
    DB::$dbName = 'day06slimblog'; 
    DB::$user = 'day06slimblog';   
    DB::$password = 'SGcGJ1jLzmN0LSaP'; 
    DB::$port = 3333;
}


DB::$error_handler = 'db_error_handler'; // runs on mysql query errors
DB::$nonsql_error_handler = 'db_error_handler'; // runs on library errors (bad syntax, etc)

function db_error_handler($params) {
    echo "Database error";
    global $log;
    //log first
    $log->error("Database error: " . $params['error']);
    if (isset($params['query'])) {
        $log->error("SQL query: " . $params['query']);
    }
    // redirect
    header("Location: /internalerror");
    die;
}

// Create and configure Slim app
$config = ['settings' => [
    'addContentLengthHeader' => false,
    'displayErrorDetails' => true
]];
$app = new \Slim\App($config);

// Fetch DI Container
$container = $app->getContainer();

// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig(dirname(__FILE__) . '/templates', [
        'cache' => dirname(__FILE__) . '/cache',
        'debug' => true, // This line should enable debug mode
    ]);
    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    return $view;
};


// All templates will be given userSession variable Note@ subpresses errors
$container['view']->getEnvironment()->addGlobal('userSession', $_SESSION['user'] ?? null);




?>