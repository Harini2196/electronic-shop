<?php
include('functions.php');
$cookieMessage = getCookieMessage();

// Read cart cookie (comma-separated ProductIDs)
$cartItems = [];

if (isset($_COOKIE['ShoppingCart']))
{
	$raw = trim($_COOKIE['ShoppingCart']);

	if ($raw !== "")
	{
		$parts = explode(",", $raw);

		foreach ($parts as $p)
		{
			$p = trim($p);
			if ($p !== "")
			{
				$cartItems[] = $p;
			}
		}

		// Prevent duplicates
		$cartItems = array_values(array_unique($cartItems));
	}
}
?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8" />
	<title>View Cart</title>
	<link rel="stylesheet" type="text/css" href="shopstyle.css" />
</head>
<body>

	<nav class="navbar">
		<a href="Homepage.php">Home</a>
		<a href="ProductList.php">Products</a>
		<a href="ViewCart.php">View Cart</a>
		<a href="SignUp.php">Sign Up</a>
		
	</nav>

	<h1>Your Shopping Cart</h1>

	<?php if ($cookieMessage !== ""): ?>
		<div class="cookieMessage"><?php echo $cookieMessage; ?></div>
	<?php endif; ?>

	<?php
	if (count($cartItems) === 0)
	{
		echo "<p>Your cart is empty.</p>";
	}
	else
	{
		try
		{
			$dbh = connectToDatabase();

			// Build placeholders (?, ?, ?, ...) for the IN clause
			$placeholders = implode(",", array_fill(0, count($cartItems), "?"));

			// Required: 1 SELECT statement for the product details in the user's cart
			$statement = $dbh->prepare(
				"SELECT
					Products.ProductID,
					Products.Description,
					Products.Price,
					Brands.BrandID,
					Brands.BrandName
				FROM Products
				INNER JOIN Brands ON Brands.BrandID = Products.BrandID
				WHERE Products.ProductID IN ($placeholders);"
			);

			// Bind all ProductIDs
			for ($i = 0; $i < count($cartItems); $i++)
			{
				$statement->bindValue($i + 1, $cartItems[$i], PDO::PARAM_STR);
			}

			$statement->execute();

			$total = 0.0;
			$foundAny = false;

			echo "<table class='dataTable'>";
			echo "<thead>";
			echo "<tr>";
			echo "<th>Product</th>";
			echo "<th>Description</th>";
			echo "<th>Brand</th>";
			echo "<th>Price</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";

			while ($row = $statement->fetch(PDO::FETCH_ASSOC))
			{
				$foundAny = true;

				$productID = $row['ProductID'];
				$desc      = $row['Description'];
				$price     = floatval($row['Price']);
				$brandID   = $row['BrandID'];
				$brandName = $row['BrandName'];

				$total += $price;

				$safeProductID = htmlspecialchars($productID, ENT_QUOTES, 'UTF-8');
				$safeDesc      = htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
				$safeBrandID   = htmlspecialchars($brandID, ENT_QUOTES, 'UTF-8');
				$safeBrandName = htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8');

				$productIDUrl = urlencode($productID);

				echo "<tr>";

				// Product picture + link to ViewProduct.php
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

				echo "</tr>";
			}

			echo "</tbody>";
			echo "</table>";

			if (!$foundAny)
			{
				echo "<p>Could not find product details for items in your cart.</p>";
			}

			echo "<h3>Total: $" . number_format($total, 2) . "</h3>";

			// Two separate forms (only if cart has items)
			?>
			<!-- Confirm Order -->
			<form action="ProcessOrder.php" method="post" class="cartForm">
				<label for="UserName"><strong>UserName:</strong></label>
				<input type="text" name="UserName" id="UserName" required />
				<input type="submit" name="ConfirmOrderButton" value="Confirm Order" />
			</form>

			<!-- Empty Cart -->
			<form action="EmptyCart.php" method="post" class="cartForm">
				<input type="submit" name="EmptyCartButton" value="Empty Cart" />
			</form>
			<?php
		}
		catch (Exception $ex)
		{
			showErrorMessage($ex->getMessage());
		}
	}
	?>

</body>
</html>
