<?php
require_once '_setup.php';

// $app->get('/menu', function ($request, $response, $args) {
//     $itemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'CAFE';");

//     return $this->view->render($response, 'menu.html.twig', ['list' => $itemList]);

//     });
        $app->get('/menu[/{catCode}]', function($request, $response, $args = '') {
            $catCode= $args;
            $itemListCafe = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = %s;", $catCode);
        
        $itemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 's%';",$catCode);
      
        
        return $this->view->render($response, 'menu.html.twig', ['CAFE' =>$catCode, 'list' => $itemListCafe]);
        return $this->view->render($response, 'menu.html.twig', ['TEAS' =>$catCode, 'teas' => $itemListCafe]);
        return $this->view->render($response, 'menu.html.twig', ['BAKE' =>$catCode, 'food' => $itemListCafe]);
        return $this->view->render($response, 'menu.html.twig', ['SAND' =>$catCode, 'sand' => $itemList]);
    
        });

 $app->get('/menu_mb', function ($request, $response, $args) {
     // 
     $cafeList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg 
                FROM items WHERE categoryCode = 'CAFE';");
    $bakeList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg 
                FROM items WHERE categoryCode = 'BAKE';");

    //print_r($cafeList);
    //print_r($bakeList);
     return $this->view->render($response, 'menu_mb.html.twig', ['cafeList' => $cafeList,'bakeList' => $bakeList ]);

     });

?>