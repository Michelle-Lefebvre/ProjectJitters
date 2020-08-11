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


// pass the price(one of three) and the item id from the items table
// add to cart these plus all others required
//$app->get('/additemstocart/{id:[0-9]+}]/price', function ($request, $response, $args) {
//    print_r($args);
//}
    
   // $user = DB::queryFirstRow("SELECT * FROM items WHERE id=%d", $args['id']);
   // if (!$user) {
   //     $response = $response->withStatus(404);
   //     return $this->view->render($response, 'admin/not_found.html.twig');
   // }

   //return $this->view->render($response, 'admin/not_found.html.twig');
  

   $app->get('/cartadditem/{id:[0-9]+}', function ($request, $response, $args) {

    if (!isset($_GET['price'])) {
        echo "Error: price missing in the URL";
        exit;
    }

    /*
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
*/


//[/{id:[0-9]+}]
$price = $request->getParam('price');
print_r($price);
    // this is for use on a post
    //$price = $request->getParam('price');

    //$cartitemList = DB::query("SELECT * FROM cartitems");
 echo $_GET['itemId'];
 echo $_GET['price'];
   // print_r( $_SESSION );
    //print_r($price);
    print_r($args);
    //return $this->view->render($response, '/cart.html.twig', ['cartitemList' => $cartitemList]);
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
