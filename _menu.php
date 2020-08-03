<?php
require_once '_setup.php';

// $app->get('/menu[/{catCode}]', function($catCode) {
//     $catCode = 'CAFE';
//     $itemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = %s", $catCode);
    
// $menu = [
//     $itemName = 'itemName',
//     $price = 'price',
//     $priceMed = 'priceMed',
//     $priceLrg = 'priceLrg'
// ];
// return $this->view->render($response, 'menu.html.twig', $menu);

// });
// $catCode = 'CAFE';
// $itemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = %s", $catCode);
//test if db table was reached
// if ($itemList) {
//     echo "connected to db" . "<br>";
//     print_r($itemList);
    // print_r("<br><br><br><br>");
// } 

$app->get('/menu', function ($request, $response, $args) {
    $catCode = 'categoryCode';
    $itemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = %s", $catCode);
    $catCode = 'CAFE';
$menu = [
    $itemName = 'itemName',
    $price = 'price',
    $priceMed = 'priceMed',
    $priceLrg = 'priceLrg'
];
return $this->view->render($response, 'menu.html.twig', $menu);

})
?>
