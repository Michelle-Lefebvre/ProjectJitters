<?php
require_once '_setup.php';

$itemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items");  
if ($itemList) {
    // echo "connected to db";
    // print_r($itemList);
    // print_r("<br><br><br><br>");
}

$app->get('/menu', function ($request, $response, $args) {
$categoryList = DB::query("SELECT categoryCode itemName, price, priceMed, priceLrg FROM items;");
    if (!$categoryList) { 
        $errorList[] = "Category Code does not exist";
    } 
    
    if ($errorList) {
        return $this->view->render($response, 'error_internal.html.twig',
                ['errorList' => $errorList ]);

    } else {

    foreach ($categoryList as &$category) {
        $itemName = $category['itemName'];
        $price = $category['price'];
        $priceMed = $category['priceMed'];
        $priceLrg = $category['priceLrg'];
        $categoryCode = $category['CAFE'];    
    }
   
    return $this->view->render($response, 'menu.html.twig');
    // return $this->view->render($response, 'menu.html.twig', ['CAFE' => $categoryCode, 'categoryList' => $categoryList]);
    
    while ($row = mysqli_fetch_assoc($categoryCode)) {
        echo '<tr>';
        echo '<td>'. $row['itemName'] .'</td>';
        echo '<td>'. $row['price'] .'</td>';
        echo '<td>'. $row['priceMed'] .'</td>';
        echo '</tr>';
    }

    
}
});
