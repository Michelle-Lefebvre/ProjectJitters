<?php
require_once '_setup.php';

$app->get('/menu', function ($request, $response, $args) {
    //$article = DB::queryFirstRow("SELECT a.id, a.authorId, a.creationTS, a.title, a.body, u.name "
    //. "FROM articles as a, users as u WHERE a.authorId = u.id AND a.id = %d", $args['id']);
    
    $itemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'CAFE';");
  
    foreach ($itemList as &$item) {
        //formatting for each field done here
        // no formatting yet.....

    }

    return $this->view->render($response, 'menu_mb.html.twig', ['list' => $itemList]);

    });

    

?>

