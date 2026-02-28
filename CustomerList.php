<?php
// CustomerList.php (Admin page)
// Shows ALL customers and ALL their details in a table

include('functions.php');
$cookieMessage = getCookieMessage();
?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8" />
	<title>Customer List</title>
	<link rel="stylesheet" type="text/css" href="shopstyle.css" />
</head>
<body>

	<!-- Simple nav (edit to match your Homepage.php nav items/paths) -->
	<nav class="navbar">
		<a href="Homepage.php">Home</a>
		<a href="ProductList.php">Products</a>
	</nav>

	<h1>Customer List</h1>

	<?php if ($cookieMessage !== ""): ?>
		<div class="cookieMessage"><?php echo $cookieMessage; ?></div>
	<?php endif; ?>

	<?php
	try
	{
		$dbh = connectToDatabase();

		// Required: 1 SELECT to get all customer details
		$statement = $dbh->prepare(
			"SELECT CustomerID, UserName, FirstName, LastName, Address, City
			 FROM Customers
			 ORDER BY CustomerID ASC;"
		);

		$statement->execute();

		echo "<table class='dataTable'>";
		echo "<thead>";
		echo "<tr>";
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

			$customerID = htmlspecialchars($row['CustomerID'], ENT_QUOTES, 'UTF-8');
			$userName   = htmlspecialchars($row['UserName'], ENT_QUOTES, 'UTF-8');
			$firstName  = htmlspecialchars($row['FirstName'], ENT_QUOTES, 'UTF-8');
			$lastName   = htmlspecialchars($row['LastName'], ENT_QUOTES, 'UTF-8');
			$address    = htmlspecialchars($row['Address'], ENT_QUOTES, 'UTF-8');
			$city       = htmlspecialchars($row['City'], ENT_QUOTES, 'UTF-8');

			echo "<tr>";
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
			echo "<p>No customers found.</p>";
		}
	}
	catch (Exception $ex)
	{
		showErrorMessage($ex->getMessage());
	}
	?>

</body>
</html>
