<?php
//set up connection to server
$dbserver = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "classicmodels";

@$db = new mysqli($dbserver, $dbuser, $dbpass, $dbname);
if ($db->connect_errno) {
    echo "Database connection error: " . mysqli_connect_errno();
    exit();
}


// Check if orderNumber, checkbox columns, start and end date is set in the request and sanitize it
$orderNumber = isset($_REQUEST["orderNumber"]) ? $db->real_escape_string($_REQUEST["orderNumber"]) : '';
$orderDateStart = isset($_REQUEST["start"]) ? $db->real_escape_string($_REQUEST["start"]) : '';
$orderDateEnd = isset($_REQUEST["end"]) ? $db->real_escape_string($_REQUEST["end"]) : '';

if(isset($_REQUEST['columns'])) {
    $checkbox = ($_REQUEST['columns']);
}
else {
    $checkbox = [];
}

//select data in SQL database
$orderNumber_str = "SELECT DISTINCT orders.orderNumber, orderdetails.orderNumber FROM orders JOIN orderdetails ON orders.orderNumber = orderdetails.orderNumber ORDER BY orders.orderNumber";
$orderDate_str = "SELECT DISTINCT orderDate FROM orders";

//display form
echo "<style>section{display:flex;} .part1, .part2{margin-left:2rem;} form{display:flex;flex-direction:column;} .checkbox{display:block;} </style>";
echo "<h1>Query</h1>";
echo "<form action='dbquery.php' method='POST'>";
echo "<section>";

echo "<div class='part1'>";
echo "<h2>Select Order Parameters</h2>";

//display all order numbers from the database
echo "Order Number: <select name=orderNumber>";
echo "<option selected></option>";
foreach ($db->query($orderNumber_str) as $row) {
    echo "<option value=\"" . $row['orderNumber'] . "\"";
    //match order number with selected order number and display as selected
    if ($orderNumber == $row['orderNumber'])
        echo 'selected';
    echo ">" . $row['orderNumber'] . "</option>";
}
echo "</select><br>";

//display all order dates from the database
echo "Order Date (YYYY-MM-DD): <br>from: <select name=start value=''>";
echo "<option selected></option>";
foreach ($db->query($orderDate_str) as $row) {
    echo "<option value=\"" . $row['orderDate'] . "\"";
    
    //match order date with selected order date and display as selected
    if ($orderDateStart == $row['orderDate'])
        echo 'selected';
    echo ">" . $row['orderDate'] . "</option>";

}

//display all order dates from the database
echo "</select>";
echo "to: <select name=end value=''>";
echo "<option selected></option>";
foreach ($db->query($orderDate_str) as $row) {
    echo "<option value=\"" . $row['orderDate'] . "\"";
    
    //match order date with selected order date and display as selected
    if ($orderDateEnd == $row['orderDate'])
        echo 'selected';
    echo ">" . $row['orderDate'] . "</option>";
}
echo "</select><br>";
echo "<input type='submit' name='submit' value='Submit'>";
echo "</div>";

echo "<div class='part2'>";
echo "<h2>Select Columns to Display</h2>";

//display the columns checkboxes
//if selected mark the checkbox as checked
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='orderNumber' ";
foreach($checkbox as $selected) {
    if ( $selected == 'orderNumber') {
        echo "checked ='checked'";
    }
}
echo "> Order Number</label>";

echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='orderDate' ";
foreach($checkbox as $selected) {
    if ( $selected == 'orderDate') {
        echo "checked ='checked'";
    }
}
echo "> Order Date</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='shippedDate'";
foreach($checkbox as $selected) {
    if ( $selected == 'shippedDate') {
        echo "checked ='checked'";
    }
}
echo "> Shipped Date</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='productName'";
foreach($checkbox as $selected) {
    if ( $selected == 'productName') {
        echo "checked ='checked'";
    }
}
echo "> Product Name</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='productDescription'";
foreach($checkbox as $selected) {
    if ( $selected == 'productDescription') {
        echo "checked ='checked'";
    }
}
echo "> Product Description</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='quantityOrdered'";
foreach($checkbox as $selected) {
    if ( $selected == 'quantityOrdered') {
        echo "checked ='checked'";
    }
}
echo "> Quantity Ordered</label>";
echo "<label class='checkbox'><input type='checkbox' name='columns[]' value='priceEach'";
foreach($checkbox as $selected) {
    if ( $selected == 'priceEach') {
        echo "checked ='checked'";
    }
}
echo "> Price Each</label>";
echo "</div>";
echo "</section>";
echo "</form>";



//select range from selected start and end date
$query_str2="SELECT * FROM orders INNER JOIN orderdetails ON orders.orderNumber = orderdetails.orderNumber INNER JOIN products on products.productCode = orderdetails.productCode WHERE orderDate BETWEEN '$orderDateStart' AND '$orderDateEnd'";

//select the chosen order number
$query_str1 = "SELECT * FROM orders INNER JOIN orderdetails ON orders.orderNumber = orderdetails.orderNumber INNER JOIN products on products.productCode = orderdetails.productCode WHERE orders.orderNumber =?";
if (!isset($_REQUEST["submit"]))
    exit();
else {
    //prepare connection
    $stmt = mysqli_prepare($db, $query_str1);
    if (!$stmt) {
        echo mysqli_error($db);
    }
    // ... previous code ...

// Assume $orderNumber, $orderDateStart, and $orderDateEnd are already sanitized and validated for display

// Display the SQL query text
if (isset($_REQUEST["submit"])) {
    // Display the query based on what parameters were provided
    if (!empty($orderNumber)) {
        // For displaying the order number query
        $displayQuery = "SELECT * FROM orders INNER JOIN orderdetails ON orders.orderNumber = orderdetails.orderNumber INNER JOIN products on products.productCode = orderdetails.productCode WHERE orders.orderNumber = " . $orderNumber;
    } elseif (!empty($orderDateStart) && !empty($orderDateEnd)) {
        // For displaying the date range query
        $displayQuery = "SELECT * FROM orders INNER JOIN orderdetails ON orders.orderNumber = orderdetails.orderNumber INNER JOIN products on products.productCode = orderdetails.productCode WHERE orderDate BETWEEN '" . $orderDateStart . "' AND '" . $orderDateEnd . "'";
    }
    echo "<p>SQL Query: <pre>" . $displayQuery . "</pre></p>";
}

    //bind the selected order number with the ?
    mysqli_stmt_bind_param($stmt, "i", $orderNumber);
    mysqli_stmt_execute($stmt);

    //get the results from the order number 
    //get the results from the order dates
    $result = mysqli_stmt_get_result($stmt);
    $result1 = mysqli_query($db, $query_str2);

    //display resulting table
            echo "<table border='1' style='text-align:center;'><tbody>";
            echo "<tr style='background-color:lightgrey;'>";
            //show the selected columns
            for ($i = 0; $i < count($checkbox); $i++) {
                if ($checkbox[$i] == 'orderNumber') {
                    echo "<th>Order Number</th>";
                }
                if ($checkbox[$i] == 'orderDate') {
                    echo '<th>Order Date</th>';
                }
                if ($checkbox[$i] == 'shippedDate') {
                    echo '<th>Shipped Date</th>';
                }
                if ($checkbox[$i] == 'productName') {
                    echo '<th>Product Name</th>';
                }
                if ($checkbox[$i] == 'productDescription') {
                    echo '<th>Product Description</th>';
                }
                if ($checkbox[$i] == 'quantityOrdered') {
                    echo '<th>Quantity Ordered</th>';
                }

                if ($checkbox[$i] == 'priceEach') {
                    echo '<th>Price Each</th>';
                }

            }
            echo '</tr>';

            //fetching rows from either the results from order date or order number
            while ($row = mysqli_fetch_assoc($result) or $row = mysqli_fetch_assoc($result1)) {
                echo '<tr>';
                foreach ($checkbox as $selected) {
                    //display rows according to the selected columns
                    if ($selected == 'orderNumber') {
                        echo "<td>" . $row["orderNumber"] . "</td>";
                    }
                    if ($selected == 'orderDate') {
                        echo "<td>" . $row["orderDate"] . "</td>";
                    }
                    if ($selected == 'shippedDate') {
                        echo "<td>" . $row["shippedDate"] . "</td>";
                    }
                    if ($selected == 'productName') {
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

                }
                echo '</tr>';
            }

        
    echo "</tbody></table>";

    mysqli_free_result($result);

    $db->close();
}
?>