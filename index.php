<?php
session_start(); // enable Sessions mechanism for the entire web application
# first line must be opening php tag
// load only classes that are necessary

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

// renders template
$app->get('/internalerror', function ($request, $response, $args) {
    return $this->view->render($response, 'error_internal.html.twig');
});

// TODO: Define app routes
// Define app routes
/** ****************************** ARTICLES **************************************** */
$app->get('/', function ($request, $response, $args) {
    $articleList = DB::query("SELECT a.id, a.authorId, a.creationTS, a.title, a.body, u.name "
        . "FROM articles as a, users as u WHERE a.authorId = u.id ORDER BY a.id DESC");
        // if sql fails don't show the error, create a custom error handler to handle this
    foreach ($articleList as &$article) {
        // format posted date
        $datetime = strtotime($article['creationTS']);
        $postedDate = date('M d, Y \a\t H:i:s', $datetime );
        $article['postedDate'] = $postedDate;
        // only show the beginning of body if it's long, also remove html tags
        $fullBodyNoTags = strip_tags($article['body']);
        $bodyPreview = substr(strip_tags($fullBodyNoTags), 0, 100); // FIXME
        $bodyPreview .= (strlen($fullBodyNoTags) > strlen($bodyPreview)) ? "..." : "";
        $article['body'] = $bodyPreview;
    }
    return $this->view->render($response, 'index.html.twig', ['list' => $articleList]);
    //print_r($articleList);
    //return $response->write("");
});

$app->get('/article/{id:[0-9]+}', function ($request, $response, $args) {
    $article = DB::queryFirstRow("SELECT a.id, a.authorId, a.creationTS, a.title, a.body, u.name "
            . "FROM articles as a, users as u WHERE a.authorId = u.id AND a.id = %d", $args['id']);
    if (!$article) { // TODO: use Slim's default 404 page instead of our custom one
        $response = $response->withStatus(404);
        return $this->view->render($response, 'article_not_found.html.twig');
    }
    $datetime = strtotime($article['creationTS']);
    $postedDate = date('M d, Y \a\t H:i:s', $datetime );
    $article['postedDate'] = $postedDate;
    return $this->view->render($response, 'article.html.twig', ['a' => $article]);
});

/** ****************************** ADD ARTICLE **************************************** */
// STATE 1: first display
$app->get('/addarticle', function ($request, $response, $args) {
    if (!isset($_SESSION['user'])) { // refuse if user not logged in
        $response = $response->withStatus(403);
        return $this->view->render($response, 'error_access_denied.html.twig');
    }
    return $this->view->render($response, 'addarticle.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/addarticle', function ($request, $response, $args) {
    if (!isset($_SESSION['user'])) { // refuse if user not logged in
        $response = $response->withStatus(403);
        return $this->view->render($response, 'error_access_denied.html.twig');
    }
    $title = $request->getParam('title');
    $body = $request->getParam('body');
    //
    $errorList = array();
    if (strlen($title) < 2 || strlen($title) > 100) {
        array_push($errorList, "Title must be 2-100 characters long");
        // keep the title even if invalid
    }
    if (strlen($body) < 2 || strlen($body) > 10000) {
        array_push($errorList, "Body must be 2-10000 characters long");
        // keep the body even if invalid
    }
    //
    if ($errorList) {
        return $this->view->render($response, 'addarticle.html.twig',
                [ 'errorList' => $errorList, 'v' => ['title' => $title, 'body' => $body ]  ]);
    } else {
        $authorId = $_SESSION['user']['id'];
        DB::insert('articles', ['authorId' => $authorId, 'title' => $title, 'body' => $body]);
        $articleId = DB::insertId();
        return $this->view->render($response, 'addarticle_success.html.twig', ['id' => $articleId]);
    }
});

/** ****************************** REGISTER **************************************** */
// STATE 1: first display
$app->get('/register', function ($request, $response, $args) {
    return $this->view->render($response, 'register.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/register', function ($request, $response, $args) {
    $name = $request->getParam('name');
    $email = $request->getParam('email');
    $pass1 = $request->getParam('pass1');
    $pass2 = $request->getParam('pass2');
    //
    $errorList = array();
    if (preg_match('/^[a-zA-Z0-9\ \\._\'"-]{4,50}$/', $name) != 1) { // no match
        array_push($errorList, "Name must be 4-50 characters long and consist of letters, digits, "
            . "spaces, dots, underscores, apostrophies, or minus sign.");
        $name = "";
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == FALSE) {
        array_push($errorList, "Email does not look valid");
        $email = "";
    } else {
        // is email already in use?
        $record = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
        if ($record) {
            array_push($errorList, "This email is already registered");
            $email = "";
        }
    }
    if ($pass1 != $pass2) {
        array_push($errorList, "Passwords do not match");
    } else {
        if ((strlen($pass1) < 6) || (strlen($pass1) > 100)
                || (preg_match("/[A-Z]/", $pass1) == FALSE )
                || (preg_match("/[a-z]/", $pass1) == FALSE )
                || (preg_match("/[0-9]/", $pass1) == FALSE )) {
            array_push($errorList, "Password must be 6-100 characters long, "
                . "with at least one uppercase, one lowercase, and one digit in it");
        }
    }
    //
    if ($errorList) {
        return $this->view->render($response, 'register.html.twig',
                [ 'errorList' => $errorList, 'v' => ['name' => $name, 'email' => $email ]  ]);
    } else {
        DB::insert('users', ['name' => $name, 'email' => $email, 'password' => $pass1]);
        return $this->view->render($response, 'register_success.html.twig');
    }
});

// used via AJAX
$app->get('/isemailtaken/[{email}]', function ($request, $response, $args) {
    $email = isset($args['email']) ? $args['email'] : "";
    $record = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    if ($record) {
        return $response->write("Email already in use");
    } else {
        return $response->write("");
    }
});

/** ****************************** LOGIN **************************************** */
// STATE 1: first display
$app->get('/login', function ($request, $response, $args) {
    return $this->view->render($response, 'login.html.twig');
});

// STATE 2&3: receiving submission
$app->post('/login', function ($request, $response, $args) use($log) { // log for mono
    $email = $request->getParam('email');
    $password = $request->getParam('password');
    //
    $record = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    $loginSuccess = false;
    if ($record) {
        if ($record['password'] == $password) {
            $loginSuccess = true;
        }        
    }
    //
    if (!$loginSuccess) {
        $log->info(sprintf("Login failed for email %s from %s", $email, $_SERVER['REMOTE_ADDR']));
        return $this->view->render($response, 'login.html.twig', [ 'error' => true ]);
    } else {
        unset($record['password']); // for security reasons remove password from session
        $_SESSION['user'] = $record; // remember user logged in
        $log->debug(sprintf("Login successful for email %s, uid-%id, from %s", $email, $record['id'], $_SERVER['REMOTE_ADDR']));

        return $this->view->render($response, 'login_success.html.twig', ['userSession' => $_SESSION['user']]);
    }
});

/** ****************************** LOGOUT **************************************** */
// STATE 1: first display
$app->get('/logout', function ($request, $response, $args) use ($log) {
    $log->debug(sprintf("Logout successful for uid=%d, from %s", @$_SESSION['user']['id'], $_SERVER['REMOTE_ADDR']));
    unset($_SESSION['user']);
    return $this->view->render($response, 'logout.html.twig', ['userSession' => null ]);
});

$app->get('/session', function ($request, $response, $args) {
    echo "<pre>\n";
    print_r($_SESSION);
    return $response->write("");
});

// NOTE: $_SESSION or $_FILES work the same way as they did before

/** ****************************** COMMENTS **************************************** */


// Run app
$app->run();