<?php
require_once '_setup.php';
# this file is for /admin/* URL handlers

// menu for administration functions
$app->get('/adminmenu', function ($request, $response, $args) {
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
        return $this->view->render($response, '/admin/adminmenu.html.twig');
    }

});


$app->get('/admin/items/list', function ($request, $response, $args) {
    $itemsList = DB::query("SELECT * FROM items");
    return $this->view->render($response, 'admin/items_list.html.twig', ['itemsList' => $itemsList]);
});


// STATE 1: first display
$app->get('/admin/items/{op:edit|add}[/{itemId:[0-9]+}]', function ($request, $response, $args) {
    // either op is add and id is not given OR op is edit and id must be given
    if ( ($args['op'] == 'add' && !empty($args['itemId'])) || ($args['op'] == 'edit' && empty($args['itemId'])) ) {
        //$response = $response->withStatus(404);
        //return $this->view->render($response, 'admin/not_found.html.twig');
        ///internalerror

        $response = $response->withStatus(404);
        return $this->view->render($response, 'error_access_denied.html.twig');


    }
    if ($args['op'] == 'edit') {
        $items = DB::queryFirstRow("SELECT * FROM items WHERE itemId=%d", $args['itemId']);
        if (!$items) {
            $response = $response->withStatus(404);
            return $this->view->render($response, 'admin/not_found.html.twig');
        }
    } else {
        $items = [];
    }
    return $this->view->render($response, 'admin/items_addedit.html.twig', ['v' => $items, 'op' => $args['op']]);
});

// STATE 2&3: receiving submission
$app->post('/admin/items/{op:edit|add}[/{itemId:[0-9]+}]', function ($request, $response, $args) {
    $op = $args['op'];
    // either op is add and id is not given OR op is edit and id must be given
    if ( ($op == 'add' && !empty($args['itemId'])) || ($op == 'edit' && empty($args['itemId'])) ) {
        $response = $response->withStatus(404);
        return $this->view->render($response, 'admin/not_found.html.twig');
    }
    $itemId = $request->getParam('itemId');
    $itemName = $request->getParam('itemName');
    $description = $request->getParam('description');
    $inventoryFlag = $request->getParam('inventoryFlag');
    $quantityOnHand = $request->getParam('quantityOnHand');
    $categoryCode = $request->getParam('categoryCode');
    $price = $request->getParam('price');
    $priceMed = $request->getParam('priceMed');
    $priceLrg = $request->getParam('priceLrg');
    $photofilepath = $request->getParam('photofilepath');
    
    // sanitize description
    $description = strip_tags($description, "<p><ul><li><em><strong><i><b><ol><h3><h4><h5><span>");
    //
    $errorList = array();

    if (strlen($itemName) < 3 || strlen($itemName) > 100 ) {
        $errorList[] = "Name must be between 3 and 100 characters long";
    }

    if (strlen($description) < 10  || strlen($description) > 250) {
        $errorList[] = "Description must be between 10 and 250 characters long";
    }
        
    // validate category code
    $categoryList = DB::queryFirstRow("SELECT categoryCode FROM categorycodes
         WHERE categoryCode = %s", $categoryCode);  

    if (!$categoryList) { 
        $errorList[] = "Category Code does not exist";
    } 
    
    
    if ($errorList) {
        return $this->view->render($response, 'admin/items_addedit.html.twig',
                [ 'errorList' => $errorList,'v' => ['itemId' => $itemId, 'itemName' => $itemName, 'description' => $description,  
                'inventoryFlag' => $inventoryFlag, 'quantityOnHand' => $quantityOnHand, 'categoryCode' => $categoryCode, 'price' => $price, 
                'priceMed' => $priceMed,'priceLrg' => $priceLrg], 'op' => $op ]);
    } else { 

        if ($op == 'add') {
            DB::insert('items', ['itemName' => $itemName, 'description' => $description,  
                        'inventoryFlag' => $inventoryFlag, 'quantityOnHand' => $quantityOnHand, 'categoryCode' => $categoryCode, 'price' => $price, 
                        'priceMed' => $priceMed,'priceLrg' => $priceLrg  ]);
            return $this->view->render($response, 'admin/items_addedit_success.html.twig', ['op' => $op ]);
        } else {
            $data = ['itemName' => $itemName, 'description' => $description, 'categoryCode' => $categoryCode, 
            'inventoryFlag' => $inventoryFlag, 'quantityOnHand' => $quantityOnHand, 'price' => $price, 
            'priceMed' => $priceMed,'priceLrg' => $priceLrg ];
            
            DB::update('items', $data, "itemId=%d", $args['itemId']);
            return $this->view->render($response, 'admin/items_addedit_success.html.twig', ['op' => $op ]);
        }
    } 
}  ); 

// STATE 1: first display  -- this is a get  -- just need to render the template
$app->get('/additem', function ($request, $response, $args) {
    return $this->view->render($response, '/admin/additem.html.twig');
});

//STATE 2&3 receiving submission
$app->post('/additem', function ($request, $response, $args) {
    
    // $app->post will display the form, following code will handle the form
    $itemName = $request->getParam('itemName');
    $description = $request->getParam('description');
    $categoryCode = $request->getParam('categoryCode');
    $inventoryFlag = $request->getParam('inventoryFlag');
    $quantityOnHand = $request->getParam('quantityOnHand');
    $price = $request->getParam('price');
    $photofilepath = $request->getParam('photofilepath');
    
    
    // sanitize description
    $description = strip_tags($description, "<p><ul><li><em><strong><i><b><ol><h3><h4><h5><span>");
               
    $errorList = array();

    if (strlen($itemName) < 10 || (strlen($itemName) > 100) ) {
        $errorList[] = "Name must be between 10  and 100 characters long";
    }
    if (strlen($description) < 10  || strlen($description) > 250) {
        $errorList[] = "Description must be between 10 and 250 characters long";
    }
        
    // validate category code
    $categoryList = DB::queryFirstRow("SELECT categoryCode FROM categorycodes
         WHERE categoryCode = %s", $categoryCode);  

    if (!$categoryList) { 
        $errorList[] = "Category Code does not exist";
    } 
    
    if ($errorList) {
        return $this->view->render($response, '/admin/additem.html.twig',
                ['errorList' => $errorList ]);

    } else {
       DB::insert( 'items', ['itemid' => NULL, 'itemName' => $itemName,  'description' => $description,
                   'categoryCode' => $categoryCode, 'inventoryFlag' => $inventoryFlag, 
                   'quantityOnHand' => $quantityOnHand, 'price' => $price, 'photofilepath' => $photofilepath
       
       ]);
        return $this->view->render($response, '/admin/additem_success.html.twig');
    }
});


// ADMIN USER CRUD
// USERS List
$app->get('/admin/users/list', function ($request, $response, $args) {
    $usersList = DB::query("SELECT * FROM users");
    return $this->view->render($response, 'admin/users_list.html.twig', ['usersList' => $usersList]);
});

// STATE 1: first display
$app->get('/admin/users{id:[0-9]+}/edit', function ($request, $response, $args) {
    $user = DB::queryFirstRow("SELECT * FROM users WHERE id=%d", $args['userId']);
    if(!$user) {
        $response = $response->withStatus(404);
        return $this->view->render($response, 'admin/error_not_found.html.twig');
    }
    return $this->view->render($response, 'admin/users_edit.html.twig', ['user' => $user]);
});

// STATE 2&3: receiving submission  
$app->post('/admin/users{id:[0-9]+}/edit', function ($request, $response, $args) {
    
    $firstName = $request->getParam('firstName');
    $lastName = $request->getParam('lastName');
    $nickname = $request->getParam('nickname');
    $email = $request->getParam('email');
    $pass1 = $request->getParam('pass1');
    $pass2 = $request->getParam('pass2');
    //
    $errorList = array();
    if (preg_match('/^[a-zA-Z0-9\ \\._\'"-]{4,50}$/', $firstName) != 1) { // no match
        array_push($errorList, "Names and nickname must be 4-50 characters long and consist of letters, digits, "
            . "spaces, dots, underscores, apostrophies, or minus sign.");
        $firstName = "";
    }
    if (preg_match('/^[a-zA-Z0-9\ \\._\'"-]{4,50}$/', $lastName) != 1) { // no match
        array_push($errorList, "Names and nickname must be 4-50 characters long and consist of letters, digits, "
            . "spaces, dots, underscores, apostrophies, or minus sign.");
        $lastName = "";
    }
    if (preg_match('/^[a-zA-Z0-9\ \\._\'"-]{4,50}$/', $nickname) != 1) { // no match
        array_push($errorList, "Nickname must be 4-50 characters long and consist of letters, digits, "
            . "spaces, dots, underscores, apostrophies, or minus sign.");
      
        $nickname = "";
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
                [ 'errorList' => $errorList, 'v' => [$firstName, 'lastName' => $lastName,  'nickname' => $nickname, 'email' => $email ]  ]);
    } else {
        DB::insert('users', ['firstName' => $firstName, 'lastName' => $lastName, 'nickname' => $nickname, 'email' => $email, 'password' => $pass1]);
        return $this->view->render($response, 'register_success.html.twig');
    }
});





?>