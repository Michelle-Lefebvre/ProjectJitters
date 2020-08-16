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

    // Templates
    // return $twig->render('error_access_denied.html.twig'); //ss
    // return $twig->render('error_internal.html.twig'); //ss
    // return $twig->render('error_not_found.html.twig');  //ss

    // return $twig->render('index.html.twig'); //ss

    // return $twig->render('login_success.html.twig'); //ss
    // return $twig->render('login.html.twig'); //ss
    // return $twig->render('logout.html.twig'); //ss
    // return $twig->render('master.html.twig');
    // return $twig->render('menu.html.twig'); //ss
    // return $twig->render('not_logged_in.html.twig'); //ss

                // return $twig->render('profile_edit_success.html.twig');
                // return $twig->render('profile.html.twig');

    // return $twig->render('cart.html.twig');
    // return $twig->render('cartadditem.html.twig'); //ss
    // return $twig->render('cartitems_delete_success.html.twig');
    // return $twig->render('cartitems_delete.html.twig');
    // return $twig->render('cartitems_not_found.html.twig'); //ss

    // return $twig->render('register_success.html.twig'); //ss
    // return $twig->render('register.html.twig'); //ss
    // return $twig->render('rewards.html.twig'); //ss

    // Admin Templates
                                    // not been tested
                                    // return $twig->render('/admin/adminmenu_notallowed.html.twig');
    return $twig->render('/admin/adminmenu.html.twig'); //ss

    // return $twig->render('/admin/error_access_denied.html.twig'); //ss
    // return $twig->render('/admin/error_not_found.html.twig'); //ss
    // return $twig->render('/admin/coming_soon.html.twig'); //ss

    // return $twig->render('/admin/items_addedit_success.html.twig'); //ss
    // return $twig->render('/admin/items_addedit.html.twig');
    // return $twig->render('/admin/items_delete_success.html.twig');
    // return $twig->render('/admin/items_delete.html.twig');
    // return $twig->render('/admin/items_list.html.twig');

    return $twig->render('/admin/master.html.twig');
    return $twig->render('/admin/not_found.html.twig');

    return $twig->render('/admin/user_addedit.html.twig');
    return $twig->render('/admin/user_delete.html.twig');
    return $twig->render('/admin/users_addedit_success.html.twig');
    return $twig->render('/admin/users_delete_success.html.twig');
    return $twig->render('/admin/users_list.html.twig');
    return $twig->render('/admin/users_master.html.twig');
});

$app->get('/session', function ($request, $response, $args) {
    echo '<pre>\n';
    print_r($_SESSION);
    return $response->write('');
});
