<?php
require_once '_setup.php';

$itemList = DB::query("SELECT categoryCode, itemName, price, priceMed, priceLrg FROM items WHERE categoryCode = 'CAFE';");

if ($itemList) {
    // echo "connected to db"."<br>";
    // print_r($itemList);
    // print_r("<br><br><br><br>");
}


   /* <table border="0" width="40%">
        <tr>
            <?php
            $count = 0;
            $query = $itemList;
            foreach ($query as $row) {
                $count++;
            ?>  
            <tr>
                <td width="3%">
                    <input type="text" name="items[]" value="<?php echo $row["itemName"]; ?>">
                </td>
                <td width="3%">
                    <?php echo $row["price"]; ?>
                </td>
                <td width="3%">
                    <?php echo $row["priceMed"]; ?>
                </td>
                <td width="3%">
                    <?php echo $row["priceLrg"]; ?>
                </td>
                </tr>
            <?php
                if ($count == 3) { // three items in a row
                    echo '</tr><tr>';
                    $count = 0;
                }
            } ?>
        </tr>
    </table>
    */
    ?>