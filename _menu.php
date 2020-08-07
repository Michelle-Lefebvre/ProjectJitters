<?php
require_once '_setup.php';

 $app->get('/menu', function ($request, $response, $args) {
     // 
     $cafeList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg 
                FROM items WHERE categoryCode = 'CAFE';");
    $bakeList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg 
                FROM items WHERE categoryCode = 'BAKE';");
    $teaList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg 
    FROM items WHERE categoryCode = 'TEAS';");
    $sandList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg 
    FROM items WHERE categoryCode = 'SAND';");

    //print_r($cafeList);
    //print_r($bakeList);
     return $this->view->render($response, 'menu.html.twig', ['cafeList' => $cafeList,'bakeList' => $bakeList,'teaList' => $teaList,'sandList' => $sandList ]);

     });

?>