<?php
require_once '_setup.php';

$app->get('/menu', function ($request, $response, $args) {
    $itemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'CAFE';");
  
    foreach ($itemList as &$item) {
        //formatting for each field done here
        // no formatting yet.....

    }

    return $this->view->render($response, 'menu.html.twig', ['list' => $itemList]);

    });
?>