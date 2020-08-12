<?php

require_once '_setup.php';
/*
// product is our items -- cartitems joined to items
$app->get('/cart', function() use ($app) {
	// TODO: retrieve addProdId and quantity from URL *IF* present
    $cartitemList = DB::query(
                    "SELECT cartitems.ID as ID, productID, quantity,"
                    . " name, description, imagePath, price "
                    . " FROM cartitems, products "
                    . " WHERE cartitems.productID = products.ID AND sessionID=%s", session_id());
    $app->render('cart.html.twig', array(
        'cartitemList' => $cartitemList
    ));
});
*/



// STATE 1: first display  -- this is a get  -- just need to render the template
$app->get('/cart', function ($request, $response, $args) {

    //$cartitemList = DB::query("SELECT * FROM cartitems");
    $cartitemList = DB::query("SELECT * FROM cartitems WHERE sessionid=%s, [$_SESSION.userid]" );

// link cartitems to session using session id and user id
    

    print_r( $_SESSION );

    return $this->view->render($response, '/cart.html.twig', ['cartitemList' => $cartitemList]);
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

    print_r($args);
    print_r($args['id']);
    print_r(session_id());

    $itemId = $args['id'];
    $price = $args['price'];
    $size = $args['size'];
    $quantity =1;

    $item = DB::queryFirstRow("SELECT * FROM cartitems WHERE itemId=%d AND sessionId=%s", $itemId, session_id());
    if ($item) {
        DB::update('cartitems', array(
            'sessionId' => session_id(),
            'itemId' => $itemId,
            'quantity' => $item['quantity'] + $quantity
                ), "itemId=%d AND sessionId=%s", $itemId, session_id());
    } else {
        DB::insert('cartitems', array(
            'sessionId' => session_id(),
            'itemId' => $itemId,
            'quantity' => $quantity
        ));
    }



});


/*
// putting item into cart
$app->post('/cart', function() use ($app) {
    $productID = $app->request()->post('productID');
    $quantity = $app->request()->post('quantity');
    // FIXME: make sure the item is not in the cart yet
    $item = DB::queryFirstRow("SELECT * FROM cartitems WHERE productID=%d AND sessionID=%s", $productID, session_id());
    if ($item) {
        DB::update('cartitems', array(
            'sessionID' => session_id(),
            'productID' => $productID,
            'quantity' => $item['quantity'] + $quantity
                ), "productID=%d AND sessionID=%s", $productID, session_id());
    } else {
        DB::insert('cartitems', array(
            'sessionID' => session_id(),
            'productID' => $productID,
            'quantity' => $quantity
        ));
    }
    */

    ?>
