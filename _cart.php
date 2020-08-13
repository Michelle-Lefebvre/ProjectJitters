<?php

require_once '_setup.php';


// STATE 1: first display  -- this is a get  -- just need to render the template
$app->get('/cart', function ($request, $response, $args) {
print_r( session_id() );


    $userId = $_SESSION['user']['userId'];    
    $sessionId = session_id();

print_r($userId);
   // link cartitems to session using session id and user id
$cartitemList = DB::query("SELECT * FROM cartitems WHERE sessionId=%s AND userId=%s", $sessionId, $userId);
    if ($cartitemList) {
        return $this->view->render($response, '/cart.html.twig', ['cartitemList' => $cartitemList]);
        
    } else {
        // display using  twig   - cartitems not found
    }
   

    
});


//STATE 2&3 receiving submission
$app->post('/cart', function ($request, $response, $args) {
    
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


// pass the price(one of three) and the item id and size from the menu
// add to cart or update quantity if item is already in the cart
   $app->get('/cartadditem/{id:[0-9]+}/{price}/{size}', function ($request, $response, $args) {

    $itemId = $args['id'];
    $price = $args['price'];
    $size = $args['size'];
    $quantity = 1;   //  one click = quantity one
    $userId = $_SESSION['user']['userId'];    
    $sessionId = session_id();

    $item = DB::queryFirstRow("SELECT * FROM cartitems WHERE itemId=%d AND sessionId=%s AND userId=%s AND size =%s", 
                                $itemId, $sessionId, $userId, $size);

    // if the item is in the cart update the quantity
    // otherwise add a new item
    if ($item) {
        $quantity = $quantity + $item['quantity'];
        DB::update('cartitems', array(
            'quantity' =>  $quantity
        ), "itemId=%d",  $itemId, "sessionId=%s", $sessionId, "userId=%s", $userId,  "size =%s", $size);
    } else {
        DB::insert('cartitems', array(
            'sessionId' => $sessionId,
            'userId' => $userId,
            'itemId' => $itemId,
            'price' => $price,
            'size' => $size,
            'quantity' => $quantity
        ));
    }
});

    ?>
