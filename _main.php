<?php

require_once '_setup.php';

// renders template
$app->get('/internalerror', function ($request, $response, $args) {
    return $this->view->render($response, 'error_internal.html.twig');
});

// TODO: Define app routes
// Define app routes
require __DIR__ . '/vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$app->get('/', function ($request, $response, $args) {
    // return $response->write('this is main'); //for testing
    $loader = new FilesystemLoader(__DIR__ . '/templates');
    $twig = new Environment($loader);
    // return $twig->render('index.html.twig');

    // return $twig->render('register.html.twig');
    return $twig->render('login.html.twig');
    // return $twig->render('login_success.html.twig');

    // return $twig->render('menu.html.twig');

    // return $twig->render('/admin/adminmenu.html.twig');
    // return $twig->render('/admin/additem.html.twig');
    // return $twig->render('/admin/additem_success.html.twig');

    // return $twig->render('error_internal.html.twig');
    // return $twig->render('error_access_denied.html.twig');
});

$app->get('/session', function ($request, $response, $args) {
    echo '<pre>\n';
    print_r($_SESSION);
    return $response->write('');
});