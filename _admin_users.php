<?php
require_once '_setup.php';
# this file is for /admin/* URL handlers

// menu for administration functions USERS
$app->get('/adminmenu2', function ($request, $response, $args) {
    // admin user must be both logged in and have adminuser set to yes
    //print_r($_SESSION['user']);

$errorList = array();
if (isset($_SESSION['user']))  {
    $adminuser = $_SESSION['user'] ['adminuser'];
    IF ($adminuser == false) {
        $errorList[] = "Only Admin users are authorized for this menu";
    }

} else {
    $errorList[] = "Only Logged in users are authorized for this menu";
}
    if ($errorList) {
        return $this->view->render($response, '/admin/adminmenu_notallowed.html.twig',
                ['errorList' => $errorList ]);
    } else {
        return $this->view->render($response, '/admin/adminmenu2.html.twig');
    }

});


// USERS List


$app->get('/admin/users/list', function ($request, $response, $args) {
    $usersList = DB::query("SELECT * FROM users");
    return $this->view->render($response, 'admin/users_list.html.twig', ['usersList' => $usersList]);
});

// Run app
$app->run();
?>