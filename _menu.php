<?php
require_once '_setup.php';

$cafeItemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'CAFE';");

$teaItemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'TEAS';");

$bakeItemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'BAKE';");

$sandItemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'SAND';");

//test if db table was reached
// if ($cafeItemList) {
//     echo "connected to db"."<br>";
//     print_r($cafeItemList);
//     print_r("<br><br><br><br>");
// }

// $app->map(['GET', 'POST'], '/menu', function ($request, $response, $args) {

//     $cafeItemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'CAFE';");
//     $itemName = $request->getParam('itemName');
//     $price = $request->getParam('price');
//     $priceMed = $request->getParam('priceMed');
//     $priceLrg = $request->getParam('priceLrg');

//     if (!$cafeItemList) { // TODO: use Slim's default 404 page instead of our custom one
//         $response = $response->withStatus(404);
//         return $this->view->render($response, 'menu.html.twig');
//     } else {

//         //
//         $count = 0;
//         $query = $cafeItemList;
//         foreach ($query as $row) {

//             if ($count == 4) { // three items in a row
//                 echo '</tr><tr>';
//                 $count = 0;
//             }
//             $count++;
//             return $this->view->render($response, 'menu.html.twig', ['a' => $itemName, 'itemName' => $cafeItemList]);
//         }
//     }
// });

# menu display
function showCoffees()
{
?>
    <table width="50%">
        <caption>Tea menu</caption>
        <thead>
            <tr>
                <th scope="col"></th>
                <th scope="col">sm</th>
                <th scope="col">med</th>
                <th scope="col">lrg</th>
            </tr>
        </thead>
        <tr>
            <?php
            $count = 0;
            $query = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'CAFE';");
            foreach ($query as $row) {
                $count++;

            ?>

                <th scope="row"> {{ itemName }}</th>
                <td scope="row"> {{ price }} </td>
                <td scope="row"> {{ priceMed }} </td>
                <td scope="row"> {{ priceLrg }} </td>
        </tr>
    <?php
                if ($count == 4) { // three items in a row
                    echo '</tr><tr>';
                    $count = 0;
                }
            }
    ?>
    </tr>
    </table>
<?php

}
showCoffees();
?>