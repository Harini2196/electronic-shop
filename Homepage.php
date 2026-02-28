<?php // <--- do NOT put anything before this PHP tag
	include('functions.php');
	$cookieMessage = getCookieMessage();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>Online Shop | Home</title>
	<link rel="stylesheet" type="text/css" href="shopstyle.css" />
</head>
<body>

	<header class="siteHeader">
		<div class="container headerRow">
			<div class="brand">
				<a class="brandLogo" href="Homepage.php" aria-label="Home">🛍️</a>
				<div class="brandText">
					<div class="brandName">LaTrobe Online Electronic Shop</div>
					<div class="brandTag">Simple. Fast. Clean.</div>
				</div>
			</div>

			<nav class="nav">
				<a class="navLink" href="ProductList.php">Browse Products</a>
				<a class="navLink" href="ViewCart.php">View Cart</a>
				<a class="navLink" href="SignUp.php">Sign Up</a>
				<a class="navLink" href="CustomerList.php">Customers</a>
				<a class="navLink" href="OrderList.php">Orders</a>
			</nav>
		</div>
	</header>

	<main class="container">
		<?php if(trim($cookieMessage) !== ''): ?>
			<div class="message successMessage">
				<?php echo $cookieMessage; ?>
			</div>
		<?php endif; ?>

		<section class="hero">
			<div class="heroLeft">
				<h1 class="heroTitle">Find what you need, faster.</h1>
				<p class="heroSub">
					Search our catalogue and add items to your cart in one click.
				</p>

				<form class="searchForm" action="ProductList.php" method="GET">
					<input
						class="searchInput"
						type="text"
						name="search"
						placeholder="Search products (e.g. 'radio', 'phone', 'laptop')"
						autocomplete="off"
					/>
					<button class="btn btnPrimary" type="submit">Search</button>
				</form>

				<div class="quickLinks">
					<a class="chip" href="ProductList.php">Browse all</a>
					<a class="chip" href="ProductList.php?search=New">Try “New”</a>
				</div>
			</div>
		</section>

		<section class="section">
			<div class="sectionHeader">
				<h2 class="sectionTitle">Popular products</h2>
				<p class="sectionSub">Based on how often products appear in orders.</p>
			</div>

			<div class="card">
				<?php
					// Popular products (safe even if no orders exist)
					$dbh = connectToDatabase();

					$sql = "
						SELECT
							Products.ProductID,
							Products.Description,
							Products.Price,
							COALESCE(SUM(OrderProducts.Quantity), 0) AS Popularity
						FROM Products
						LEFT JOIN OrderProducts ON OrderProducts.ProductID = Products.ProductID
						GROUP BY Products.ProductID, Products.Description, Products.Price
						ORDER BY Popularity DESC, Products.Description ASC
						LIMIT 5
					";

					$stmt = $dbh->prepare($sql);
					$stmt->execute();
					$rows = $stmt->fetchAll();
				?>

				<?php if(count($rows) === 0): ?>
					<p class="muted">No products found.</p>
				<?php else: ?>
					<table class="table">
						<thead>
							<tr>
								<th>Product</th>
								<th class="right">Price</th>
								<th class="right">Popularity</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($rows as $row): ?>
								<tr>
									<td>
										<?php
											$ProductID = $row['ProductID'];
										?>
										<a class="tableLink"href='./ViewProduct.php?ProductID=<?php echo $ProductID; ?>'><img src='./IFU_Assets/ProductPictures/<?php echo $ProductID; ?>.jpg' alt='<?php echo htmlspecialchars($row['Description']); ?>' />
											<?php echo htmlspecialchars($row['Description']); ?>
										</a>
										<div class="tiny muted">ID: <?php echo htmlspecialchars($row['ProductID']); ?></div>
									</td>
									<td class="right">$<?php echo number_format((float)$row['Price'], 2); ?></td>
									<td class="right"><?php echo (int)$row['Popularity']; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</section>
	</main>

</body>
</html>
