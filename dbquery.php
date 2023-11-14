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


if(isset($_REQUEST["orderNumber"])){
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

$orderNumber_str="SELECT DISTINCT orders.orderNumber, orderdetails.orderNumber FROM orders JOIN orderdetails ON orders.orderNumber = orderdetails.orderNumber ORDER BY orders.orderNumber";
$orderDate_str="SELECT DISTINCT orderDate FROM orders";

echo "<style>section{display:flex;} .part1, .part2{margin-left:2rem;} form{display:flex;flex-direction:column;} .checkbox{display:block;} </style>";
echo "<h1>Query</h1>";
echo "<form action='dbquery.php' method='POST'>"; 
echo "<section>";

echo "<div class='part1'>";
echo "<h2>Select Order Parameters</h2>";

// echo "Order Number: <input type='text' name='orderNumber' value='" .($orderNumber) . "'><br>";
echo "Order Number: <select name=orderNumber value=''>";
echo "<option value=$_POST[orderNumber]></option>";
foreach ($db->query($orderNumber_str) as $row){
    echo "<option value=$row[orderNumber]>$row[orderNumber]</option>";
}
echo "</select><br>";
// echo "Order Date (YYYY-MM-DD): <input type='text' name='orderDateFrom' placeholder='from'> to <input type='text' name='orderDateTo' placeholder='to'><br>";
echo "Order Date (YYYY-MM-DD): <br>from: <select name=orderDate value=''>";
foreach ($db->query($orderDate_str) as $row){
    echo "<option value=$row[orderDate]>$row[orderDate]</option>";
}
echo "</select>";
echo"to: <select name=orderDate value=''>";
foreach ($db->query($orderDate_str) as $row){
    echo "<option value=$row[orderDate]>$row[orderDate]</option>";
}
echo "</select><br>";
echo "<input type='submit' name='submit' value='Submit'>";
echo "</div>";

echo "<div class='part2'>";
echo "<h2>Select Columns to Display</h2>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='orderNumber'> Order Number</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='orderDate'> Order Date</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='shippedDate'> Shipped Date</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='productLine'> Product Name</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='productDes'> Product Description</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='quanOrder'> Quantity Ordered</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='priceEach'> Price Each</label>";
echo "</div>";
echo "</section>";
echo "</form>";




if (!isset($_REQUEST["submit"]))
    exit();
$query_str1 = "SELECT * FROM orders INNER JOIN orderdetails ON orders.orderNumber = orderdetails.orderNumber INNER JOIN products on products.productCode = orderdetails.productCode WHERE orders.orderNumber =?";
$orderNumber = $_REQUEST['orderNumber'];
$stmt = mysqli_prepare($db, $query_str1);
if(!$stmt){
    echo mysqli_error($db);
  }
  
  mysqli_stmt_bind_param($stmt,"i", $orderNumber);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  echo "<table border='1' style='text-align:center;'><tbody><tr style='background-color:lightgrey;';><th>Order Number</th><th>Order Date</th><th>Shipped Date</th><th>Product Name</th> <th>Product Description</th><th>Quantity Ordered</th><th>Price Each</th></tr>";
  while($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>".$row["orderNumber"]."</td>";
    echo "<td>".$row["orderDate"]."</td>";
    echo "<td>".$row["shippedDate"]."</td>";
    echo "<td>".$row["productName"]."</td>";
    echo "<td style='width:25%;'>".$row["productDescription"]."</td>";
    echo "<td>".$row["quantityOrdered"]."</td>";
    echo "<td>".$row["priceEach"]."</td></tr>";

  }
  echo "</tbody></table>";
  mysqli_free_result($result);
// echo $query_str1;
// $res = $db->query($query_str1);

// echo "<p>Number of orders: " . $res->num_rows . "</p>";
// $res->free_result();
$db->close();
?>
