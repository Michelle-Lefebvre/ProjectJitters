<?php
require_once '_setup.php';

$cafeItemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'CAFE';");

$teaItemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'TEAS';");

$bakeItemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'BAKE';");

$sandItemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'SAND';");

//test if db table was reached
if ($cafeItemList) {
    echo "connected to db" . "<br>";
    // print_r($cafeItemList);
    // print_r("<br><br><br><br>");
}

$app->post('/menu', function ($request, $response, $args) {
    $cafeItemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'CAFE';");
    $query = $cafeItemList;
$menu = [
    $itemName = 'itemName',
    $price = 'price',
    $priceMed = 'priceMed',
    $priceLrg = 'priceLrg'
];
return $this->view->render($response, 'menu.html.twig', $menu);

})
?>