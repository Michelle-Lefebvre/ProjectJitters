<?php
require_once '_setup.php';


// $sql = "SELECT itemId, itemName, price FROM items";
// $result = DB::query($sql);
$itemList = DB::queryFirstRow("SELECT * FROM items");  
if ($itemList) {
    echo "connected to db";
    print_r($itemList);
}
// if ($itemList > 0) {
//   // output data of each row
//   while($row = fetch_assoc()) {
//     echo "id: " . $row["itemId"]. " - Name: " . $row["itemName"]. " " . $row["price"]. "<br>";
//   }
// } else {
//   echo "0 results";
// }

?>