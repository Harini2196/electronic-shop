<?php
include('functions.php');
$cookieMessage = getCookieMessage();

$orderID = isset($_GET['OrderID']) ? intval($_GET['OrderID']) : 0;
?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8" />
	<title>Order Details</title>
	<link rel="stylesheet" type="text/css" href="shopstyle.css" />
</head>
<body>

	<nav class="navbar">
		<a href="Homepage.php">Home</a>
		<a href="ProductList.php">Products</a>
		<a href="ViewCart.php">Back to Cart</a>
		<a href="OrderList.php">Orders</a>
	</nav>

	<h1>Order Details</h1>

	<?php if ($cookieMessage !== ""): ?>
		<div class="cookieMessage"><?php echo $cookieMessage; ?></div>
	<?php endif; ?>

	<?php
	if ($orderID <= 0)
	{
		showErrorMessage("No OrderID provided.");
	}
	else
	{
		try
		{
			$dbh = connectToDatabase();

			// -------------------------
			// SELECT #1: Order + Customer
			// -------------------------
			$statement1 = $dbh->prepare(
				"SELECT
					Orders.OrderID,
					Orders.TimeStamp,
					Customers.CustomerID,
					Customers.UserName,
					Customers.FirstName,
					Customers.LastName,
					Customers.Address,
					Customers.City
				FROM Orders
				INNER JOIN Customers ON Customers.CustomerID = Orders.CustomerID
				WHERE Orders.OrderID = ?;"
			);

			$statement1->bindValue(1, $orderID, PDO::PARAM_INT);
			$statement1->execute();

			$orderRow = $statement1->fetch(PDO::FETCH_ASSOC);

			if (!$orderRow)
			{
				showErrorMessage("Unknown OrderID.");
			}
			else
			{
				$safeOrderID   = htmlspecialchars($orderRow['OrderID'], ENT_QUOTES, 'UTF-8');
				$safeTimeStamp = htmlspecialchars($orderRow['TimeStamp'], ENT_QUOTES, 'UTF-8');

				$safeCustomerID = htmlspecialchars($orderRow['CustomerID'], ENT_QUOTES, 'UTF-8');
				$safeUserName   = htmlspecialchars($orderRow['UserName'], ENT_QUOTES, 'UTF-8');
				$safeFirstName  = htmlspecialchars($orderRow['FirstName'], ENT_QUOTES, 'UTF-8');
				$safeLastName   = htmlspecialchars($orderRow['LastName'], ENT_QUOTES, 'UTF-8');
				$safeAddress    = htmlspecialchars($orderRow['Address'], ENT_QUOTES, 'UTF-8');
				$safeCity       = htmlspecialchars($orderRow['City'], ENT_QUOTES, 'UTF-8');

				echo "<h2>Receipt</h2>";
				echo "<p><strong>Order ID:</strong> $safeOrderID</p>";
				echo "<p><strong>Time Placed:</strong> $safeTimeStamp</p>";

				echo "<h3>Customer Details</h3>";
				echo "<div class='customerDetails'>";
				echo "<p><strong>Customer ID:</strong> $safeCustomerID</p>";
				echo "<p><strong>Username:</strong> $safeUserName</p>";
				echo "<p><strong>Name:</strong> $safeFirstName $safeLastName</p>";
				echo "<p><strong>Address:</strong> $safeAddress, $safeCity</p>";
				echo "</div>";

				// -------------------------
				// SELECT #2: Products in this order
				// -------------------------
				$statement2 = $dbh->prepare(
					"SELECT
						Products.ProductID,
						Products.Description,
						Products.Price,
						OrderProducts.Quantity,
						Brands.BrandID,
						Brands.BrandName
					FROM OrderProducts
					INNER JOIN Products ON Products.ProductID = OrderProducts.ProductID
					INNER JOIN Brands ON Brands.BrandID = Products.BrandID
					WHERE OrderProducts.OrderID = ?;"
				);

				$statement2->bindValue(1, $orderID, PDO::PARAM_INT);
				$statement2->execute();

				echo "<h3>Items</h3>";

				echo "<table class='dataTable'>";
				echo "<thead>";
				echo "<tr>";
				echo "<th>Product</th>";
				echo "<th>Description</th>";
				echo "<th>Brand</th>";
				echo "<th>Price</th>";
				echo "<th>Qty</th>";
				echo "<th>Line Total</th>";
				echo "</tr>";
				echo "</thead>";
				echo "<tbody>";

				$total = 0.0;
				$foundItems = false;

				while ($row = $statement2->fetch(PDO::FETCH_ASSOC))
				{
					$foundItems = true;

					$productID = $row['ProductID'];
					$desc      = $row['Description'];
					$price     = floatval($row['Price']);
					$qty       = intval($row['Quantity']);
					$brandID   = $row['BrandID'];
					$brandName = $row['BrandName'];

					$lineTotal = $price * $qty;
					$total += $lineTotal;

					$safeProductID = htmlspecialchars($productID, ENT_QUOTES, 'UTF-8');
					$safeDesc      = htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
					$safeBrandID   = htmlspecialchars($brandID, ENT_QUOTES, 'UTF-8');
					$safeBrandName = htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8');

					$productIDUrl = urlencode($productID);

					echo "<tr>";

					// Product picture as hyperlink to ViewProduct.php
					echo "<td>";
					echo "<a href='ViewProduct.php?ProductID=$productIDUrl'>";
					echo "<img src='./IFU_Assets/ProductPictures/$safeProductID.jpg' alt='' style='height:60px;' />";
					echo "</a>";
					echo "</td>";

					echo "<td>$safeDesc</td>";

					// Brand picture + name
					echo "<td>";
					echo "<img src='./IFU_Assets/BrandPictures/$safeBrandID.jpg' alt='' style='height:40px; vertical-align:middle;' />";
					echo " <span>$safeBrandName</span>";
					echo "</td>";

					echo "<td>$" . number_format($price, 2) . "</td>";
					echo "<td>$qty</td>";
					echo "<td>$" . number_format($lineTotal, 2) . "</td>";

					echo "</tr>";
				}

				echo "</tbody>";
				echo "</table>";

				if (!$foundItems)
				{
					echo "<p>No products found for this order.</p>";
				}

				echo "<h3>Total: $" . number_format($total, 2) . "</h3>";
			}
		}
		catch (Exception $ex)
		{
			showErrorMessage($ex->getMessage());
		}
	}
	?>

</body>
</html>
