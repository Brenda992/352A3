<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dbserver = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "classicmodels";

@$db = new mysqli($dbserver, $dbuser, $dbpass, $dbname);
if ($db->connect_errno) {
    echo "Database connection error: " . mysqli_connect_errno();
    exit();
}


if (isset($_REQUEST["orderNumber"])) {
    $order = $_REQUEST["orderNumber"];
}

// if(isset($_REQUEST['submit'])){
//     // if(isset($_REQUEST['columns'])){
//     // $columns = $_REQUEST['columns'];
//     // $columns_string = implode(",", $columns);
//     // }


// }

$query_str = "SELECT *
FROM orders INNER JOIN orderdetails ON
orders.orderNumber = orderdetails.orderNumber
";
// Check if orderNumber is set in the request and sanitize it
$orderNumber = isset($_REQUEST["orderNumber"]) ? $db->real_escape_string($_REQUEST["orderNumber"]) : '';


$res = $db->query($query_str);

$orderNumber_str = "SELECT DISTINCT orders.orderNumber, orderdetails.orderNumber FROM orders JOIN orderdetails ON orders.orderNumber = orderdetails.orderNumber ORDER BY orders.orderNumber";
$orderDate_str = "SELECT DISTINCT orderDate FROM orders";

echo "<style>section{display:flex;} .part1, .part2{margin-left:2rem;} form{display:flex;flex-direction:column;} .checkbox{display:block;} </style>";
echo "<h1>Query</h1>";
echo "<form action='dbquery.php' method='POST'>";
echo "<section>";

echo "<div class='part1'>";
echo "<h2>Select Order Parameters</h2>";

// echo "Order Number: <input type='text' name='orderNumber' value='" .($orderNumber) . "'><br>";
echo "Order Number: <select name=orderNumber>";
echo "<option selected></option>";
foreach ($db->query($orderNumber_str) as $row) {
    echo "<option value=\"" . $row['orderNumber'] . "\"";
    if ($orderNumber == $row['orderNumber'])
        echo 'selected';
    echo ">" . $row['orderNumber'] . "</option>";
}
echo "</select><br>";
// echo "Order Date (YYYY-MM-DD): <input type='text' name='orderDateFrom' placeholder='from'> to <input type='text' name='orderDateTo' placeholder='to'><br>";
echo "Order Date (YYYY-MM-DD): <br>from: <select name=orderDate value=''>";
foreach ($db->query($orderDate_str) as $row) {
    echo "<option value=$row[orderDate]>$row[orderDate]</option>";
}

$checked='';
echo "</select>";
echo "to: <select name=orderDate value=''>";
foreach ($db->query($orderDate_str) as $row) {
    echo "<option value=$row[orderDate]>$row[orderDate]</option>";
}
echo "</select><br>";
echo "<input type='submit' name='submit' value='Submit'>";
echo "</div>";

echo "<div class='part2'>";
echo "<h2>Select Columns to Display</h2>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='orderNumber' $checked>";
echo "Order Number</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='orderDate'> Order Date</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='shippedDate'> Shipped Date</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='productName'> Product Name</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='productDescription'> Product Description</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='quantityOrdered'> Quantity Ordered</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='priceEach'> Price Each</label>";
echo "</div>";
echo "</section>";
echo "</form>";





$query_str1 = "SELECT * FROM orders INNER JOIN orderdetails ON orders.orderNumber = orderdetails.orderNumber INNER JOIN products on products.productCode = orderdetails.productCode WHERE orders.orderNumber =?";
if (!isset($_REQUEST["submit"]))
    exit();
else{
$orderNumber = $_REQUEST['orderNumber'];

$stmt = mysqli_prepare($db, $query_str1);
if (!$stmt) {
    echo mysqli_error($db);
}

mysqli_stmt_bind_param($stmt, "i", $orderNumber);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);


if (isset( $_REQUEST['columns'])) {
    if (!empty( $_REQUEST['columns'])) {
        echo "<table border='1' style='text-align:center;'><tbody>";
$checkbox = $_REQUEST['columns'];
echo "<tr style='background-color:lightgrey;'>";
for($i=0;$i<count($checkbox);$i++){
if ($checkbox[$i]=='orderNumber') {
    echo"<th>Order Number</th>";
}
if ($checkbox[$i]=='orderDate') {
    echo'<th>Order Date</th>';
}
if ($checkbox[$i]=='shippedDate') {
    echo'<th>Shipped Date</th>';
}
if ($checkbox[$i]=='productName') {
    echo'<th>Product Name</th>';
}
if ($checkbox[$i]=='productDescription') {
    echo'<th>Product Description</th>';
}
if ($checkbox[$i]=='quantityOrdered') {
    echo'<th>Quantity Ordered</th>';
}
 
if ($checkbox[$i]=='priceEach') {
    echo'<th>Price Each</th>';
}

}
echo '</tr>';
while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';
            foreach($checkbox as $selected){
                
                if ( $selected == 'orderNumber') {
                    echo "<td>" . $row["orderNumber"] . "</td>";
                }
                if ($selected == 'orderDate') {
                    echo "<td>" . $row["orderDate"] . "</td>";
                }
                if ($selected == 'shippedDate') {
                    echo "<td>" . $row["shippedDate"] . "</td>";
                }
                if ($selected  == 'productName') {
                    echo "<td>" . $row["productName"] . "</td>";
                }
                if ($selected == 'productDescription') {
                    echo "<td>" . $row["productDescription"] . "</td>";
                }
                if ($selected == "quantityOrdered") {
                echo "<td>" . $row["quantityOrdered"] . "</td>";
                }
                if ($selected == "priceEach") {
                echo "<td>" . $row["priceEach"] . "</td>";
                }
               
            } echo '</tr>';
        }
        
        }
    }

echo "</tbody></table>";

mysqli_free_result($result);
// echo $query_str1;
// $res = $db->query($query_str1);

// echo "<p>Number of orders: " . $res->num_rows . "</p>";
// $res->free_result();
$db->close();
}
?>