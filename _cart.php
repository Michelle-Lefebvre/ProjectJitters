<?php

require_once '_setup.php';


// STATE 1: first display  -- this is a get  -- just need to render the template
$app->get('/cart', function ($request, $response, $args) {
    //if (!isset($_SESSION['user'])) { // refuse if user not logged in
    if (!isset($_SESSION['user']['userId'])) { // refuse if user not logged in
        $response = $response->withStatus(403);
        return $this->view->render($response, 'not_logged_in.html.twig');
    }
    $userId = $_SESSION['user']['userId'];
    $sessionId = session_id();
    // link cartitems to session using session id and user id
    $cartitemList = DB::query("SELECT *, price * quantity as extprice  FROM cartitems WHERE sessionId=%s AND userId=%s", $sessionId, $userId);
    if ($cartitemList) {
        $cartitemSumAA = DB::query("SELECT sum( price * quantity) as sumprice  FROM cartitems WHERE sessionId=%s AND userId=%s", $sessionId, $userId);
        $cartitemSum = $cartitemSumAA['0']['sumprice'];
        // print the total cart out to the user
        print_r('                total of cart is: ');
        print_r($cartitemSum);
        return $this->view->render($response, '/cart.html.twig', ['cartitemList' => $cartitemList],  $cartitemSum);
    } else {
        // display using  twig   - cartitems not found
        return $this->view->render($response, 'cartitems_not_found.html.twig');
    }
});

/*  cart uses menu to do the add functionality */
// pass the price(one of three) and the item id and size from the menu
// add to cart or update quantity if item is already in the cart
$app->get('/cartadditem/{id:[0-9]+}/{price}/{size}/{itemName}', function ($request, $response, $args) {
    if (!isset($_SESSION['user'])) { // refuse if user not logged in
        $response = $response->withStatus(403);
        return $this->view->render($response, 'not_logged_in.html.twig');
    }

    $itemId = $args['id'];
    $itemName = $args['itemName'];
    $price = $args['price'];
    $size = $args['size'];
    $quantity = 1;   //  one click = quantity one
    $userId = $_SESSION['user']['userId'];
    $sessionId = session_id();
    $item = DB::queryFirstRow(
        "SELECT * FROM cartitems WHERE itemId=%d AND sessionId=%s AND userId=%s AND size =%s",
        $itemId,
        $sessionId,
        $userId,
        $size
    );

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
            'itemName' => $itemName,
            'price' => $price,
            'size' => $size,
            'quantity' => $quantity
        ));
    }

    return $this->view->render($response, '/cartadditem.html.twig');
});

// STATE 1: check if exists and confirm delete
$app->get('/cart/delete/{id:[0-9]+}', function ($request, $response, $args) {
    $cartitem = DB::queryFirstRow("SELECT * FROM cartitems WHERE cartId=%d", $args['id']);
    if (!$cartitem) {
        $response = $response->withStatus(404);
        return $this->view->render($response, 'cartitems_not_found.html.twig');
    }
    return $this->view->render($response, 'cartitems_delete.html.twig', ['v' => $cartitem]);
});

// STATE 2: this does the delete
$app->post('/cart/delete/{id:[0-9]+}', function ($request, $response, $args) {
    DB::delete('cartitems', "cartId=%d", $args['id']);
    return $this->view->render($response, 'cartitems_delete_success.html.twig');
});

$app->get('/rewards', function ($request, $response, $args) {

        return $this->view->render($response, 'rewards.html.twig');
    }
);

