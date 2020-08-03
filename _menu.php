<?php
require_once '_setup.php';


$app->get('/menu(/{catCode})', function() {
    $catCode = 'CAFE';
    $itemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = %s", $catCode);
    
$menu = [
    $itemName = 'itemName',
    $price = 'price',
    $priceMed = 'priceMed',
    $priceLrg = 'priceLrg'
];
return $this->view->render($itemList, 'menu.html.twig', $menu);

});

//test if db table was reached
if ($itemList) {
    echo "connected to db" . "<br>";
    // print_r($cafeItemList);
    // print_r("<br><br><br><br>");
} 

$app->get('/menu', function ($request, $response, $args) {
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
