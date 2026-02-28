<?php
// <--- do NOT put anything before this PHP tag
include('functions.php');
$cookieMessage = getCookieMessage();

$productID = isset($_GET['ProductID']) ? $_GET['ProductID'] : '';
$productID = trim($productID);
$safeProductIDForHtml = htmlspecialchars($productID, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8" />
	<title>View Product</title>
	<link rel="stylesheet" type="text/css" href="shopstyle.css" />
</head>
<body>
	<h1>Product Details</h1>

	<?php if($cookieMessage !== ""): ?>
		<div class="cookieMessage"><?php echo $cookieMessage; ?> </div>
	<?php endif; ?>

	<?php
	if($productID === "")
	{
		showErrorMessage("No ProductID provided.");
	}
	else
	{
		try
		{
			$dbh = connectToDatabase();

			$statement = $dbh->prepare(
				'SELECT
					Products.ProductID,
					Products.Description,
					Products.Price,
					Brands.BrandID,
					Brands.BrandName,
					Brands.Website
				FROM Products
				INNER JOIN Brands ON Brands.BrandID = Products.BrandID
				WHERE Products.ProductID = ?;'
			);

			$statement->bindValue(1, $productID, PDO::PARAM_STR);
			$statement->execute();

			if($row = $statement->fetch(PDO::FETCH_ASSOC))
			{	echo "<div class='container headerRow'>";
				echo "<nav class='nav	'>";
				echo "<a class='navLink' href='ProductList.php'>Back</a>";
				echo "<a class='navLink' href='Homepage.php'>Home</a>";
				echo "</nav>";
				echo "</div>";

				$desc = htmlspecialchars($row['Description'], ENT_QUOTES, 'UTF-8');
				$price = htmlspecialchars($row['Price'], ENT_QUOTES, 'UTF-8');
				$brandID = htmlspecialchars($row['BrandID'], ENT_QUOTES, 'UTF-8');
				$brandName = htmlspecialchars($row['BrandName'], ENT_QUOTES, 'UTF-8');
				$productIDUrl = urlencode($row['ProductID']);

				echo "<div class='addToCartForm'>";
				echo "<img src='./IFU_Assets/ProductPictures/$safeProductIDForHtml.jpg' alt='' />";
				echo "<div class='productMeta'>";
				echo "<h2>$desc</h2>";
				echo "<p class='productPrice'>$$price</p>";
				echo "<div class='brandBlock'>";
				echo "<img src='./IFU_Assets/BrandPictures/$brandID.jpg' alt='' />";
				echo "<div>";
				echo "<div class='brandName'>$brandName</div>";
				// Absolute URL allowed for brand website
				echo "<a href='https://$brandName.com' target='_blank' rel='noopener'>Visit brand website</a>";
				echo "</div>";

				// Add to cart button must disappear if already in cart
				$inCart = false;
				if(isset($_COOKIE['ShoppingCart']))
				{
					$inCart = stringContains($_COOKIE['ShoppingCart'], $productID);
				}

				if($inCart)
				{
					echo "<p class='infoMessage'>This item is already in your cart.</p>";
				}
				else
				{
					echo "<form action='AddToCart.php?ProductID=$productIDUrl' method='post'>";
					echo "<input type='submit' name='AddToCartButton' value='Add to Cart' />";
					echo "</form>";
				}
			}
			else
			{
				showErrorMessage("Unknown Product ID.");
			}
		}
		catch(Exception $ex)
		{
			showErrorMessage($ex->getMessage());
		}
	}
	?>
</body>
</html>
