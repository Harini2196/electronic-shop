<?php
// OrderList.php (Admin page)
// Shows all orders + the customer who placed each order
// OrderID links to ViewOrderDetails.php

include('functions.php');
$cookieMessage = getCookieMessage();
?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8" />
	<title>Order List</title>
	<link rel="stylesheet" type="text/css" href="shopstyle.css" />
</head>
<body>	

	<!-- Simple nav (edit link names/paths to match your Homepage.php) -->
	<nav class="navbar">
		<a class="navLink"  href="Homepage.php">Home</a>
		<a class="navLink" href="ProductList.php">Products</a>
		<a class="navLink" href="ViewCart.php">View Cart</a>
		<a class="navLink" href="SignUp.php">Sign Up</a>
		<a class="navLink" href="CustomerList.php">Customers</a>
		<a class="navLink" href="OrderList.php">Orders</a>
	</nav>

	<h1>Order List</h1>

	<?php if ($cookieMessage !== ""): ?>
		<div class="cookieMessage"><?php echo $cookieMessage; ?></div>
	<?php endif; ?>

	<?php
	try
	{
		$dbh = connectToDatabase();

		// Required: 1 SELECT statement to select all Order + Customer details
		// Note: column names may be OrderTime / TimePlaced depending on your DB.
		// If you get "no such column" change Orders.TimePlaced to your actual column.
		$statement = $dbh->prepare(
			"SELECT
				Orders.OrderID,
				Orders.TimeStamp AS TimePlaced,
				Customers.CustomerID,
				Customers.UserName,
				Customers.FirstName,
				Customers.LastName,
				Customers.Address,
				Customers.City
			FROM Orders
			INNER JOIN Customers ON Customers.CustomerID = Orders.CustomerID
			ORDER BY Orders.OrderID DESC;"
		);

		$statement->execute();

		echo "<table class='dataTable'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>OrderID</th>";
		echo "<th>Time Placed</th>";
		echo "<th>CustomerID</th>";
		echo "<th>UserName</th>";
		echo "<th>First Name</th>";
		echo "<th>Last Name</th>";
		echo "<th>Address</th>";
		echo "<th>City</th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";

		$found = false;

		while ($row = $statement->fetch(PDO::FETCH_ASSOC))
		{
			$found = true;

			$orderID    = htmlspecialchars($row['OrderID'], ENT_QUOTES, 'UTF-8');
			$timePlaced = htmlspecialchars($row['TimePlaced'], ENT_QUOTES, 'UTF-8');

			$customerID = htmlspecialchars($row['CustomerID'], ENT_QUOTES, 'UTF-8');
			$userName   = htmlspecialchars($row['UserName'], ENT_QUOTES, 'UTF-8');
			$firstName  = htmlspecialchars($row['FirstName'], ENT_QUOTES, 'UTF-8');
			$lastName   = htmlspecialchars($row['LastName'], ENT_QUOTES, 'UTF-8');
			$address    = htmlspecialchars($row['Address'], ENT_QUOTES, 'UTF-8');
			$city       = htmlspecialchars($row['City'], ENT_QUOTES, 'UTF-8');

			$orderIDUrl = urlencode($row['OrderID']);

			echo "<tr>";
			echo "<td><a href='ViewOrderDetails.php?OrderID=$orderIDUrl'><u><b>$orderID</b></u></a></td>";
			echo "<td>$timePlaced</td>";
			echo "<td>$customerID</td>";
			echo "<td>$userName</td>";
			echo "<td>$firstName</td>";
			echo "<td>$lastName</td>";
			echo "<td>$address</td>";
			echo "<td>$city</td>";
			echo "</tr>";
		}

		echo "</tbody>";
		echo "</table>";

		if (!$found)
		{
			echo "<p>No orders found.</p>";
		}
	}
	catch (Exception $ex)
	{
		showErrorMessage($ex->getMessage());
	}
	?>

</body>
</html>
