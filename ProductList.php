<!doctype html>
<html>
<head>
	<meta charset="UTF-8" /> 
	<title>My First SQL Page</title>
	<link rel="stylesheet" type="text/css" href="shopstyle.css" />
</head>
<body>
	<h1>Products List</h1>
	
	<body>
		<header class="siteHeader">
			<div class="container headerRow">
				<nav class="nav">
						<a class="navLink" href="Homepage.php">Home</a>
				</nav>
			</div>
		</header>
		<main class="container">
	<?php
		
		// include some functions from another file.
		include('./functions.php');
		
		// if the user provided a search string.
		if(isset($_GET['search']))
		{
			$searchString = $_GET['search'];
		}
		// if the user did NOT provided a search string, assume an empty string
		else
		{
			$searchString = "";
		}
				
		$safeSearchString = htmlspecialchars($searchString, ENT_QUOTES,"UTF-8"); 
		$SqlSearchString = "%$safeSearchString%"; 

		
		echo "<form>";		
		echo "<input name = 'search' type = 'text' value = '$safeSearchString' />";
		echo "<input type = 'submit' value = 'Search'/>"; 
		echo "</form>"; 
		
		if(isset($_GET['page']))
		{
			$currentPage = intval($_GET['page']);
		}
		else
		{
			$currentPage = 0;
		}
				
		echo "<form>";
		echo "<input name = 'page' type = 'text'  value = '$currentPage' size='1' />";
		echo "<input type = 'submit' value = 'Go'/>";
		echo "</form>";
		
		$nextPage =  $currentPage + 1;

		
		// connect to the database using our function (and enable errors, etc)
		$dbh = connectToDatabase();
		
		$statement = $dbh->prepare('SELECT Products.ProductID, Products.Price, Products.Description FROM Products LEFT JOIN OrderProducts 
			ON OrderProducts.ProductID = Products.ProductID
			WHERE Products.Description LIKE ? 
			GROUP BY Products.ProductID 
			ORDER BY COUNT(OrderProducts.OrderID) DESC
			LIMIT 10 
			OFFSET ? * 10
			;'); 

		
		$statement->bindValue(1,$SqlSearchString);
		$statement->bindValue(2,$currentPage); 
			
		
		//execute the SQL.
		$statement->execute();

		// get the results
		while($row = $statement->fetch(PDO::FETCH_ASSOC))
		{
			$ProductID = htmlspecialchars($row['ProductID'], ENT_QUOTES, 'UTF-8'); 
			$Price = htmlspecialchars($row['Price'], ENT_QUOTES, 'UTF-8'); 
			$Description = htmlspecialchars($row['Description'], ENT_QUOTES, 'UTF-8'); 
			
			echo "<div class = 'productBox'>";
			echo "<a href='./ViewProduct.php?ProductID=$ProductID'><img src='./IFU_Assets/ProductPictures/$ProductID.jpg' alt ='' /></a>  ";
			echo "$Description <br/>";
			echo "$Price <br/>";
			echo "</div> \n";			
		}

		echo "<div class='pagination'>";
	

		$prevPage = $currentPage - 1;
		if ($prevPage >= 0)
		{
			echo "<a class='pageLink' href='ProductList.php?page=$prevPage&search=" . urlencode($searchString) . "'>« Previous</a>";
		}
		echo "<span class='pageNumber'>Page " . ($currentPage + 1) . "</span>";

		echo "<a class='pageLink' href='ProductList.php?page=$nextPage&search=" . urlencode($searchString) . "'>Next »</a>";

		echo "</div>";
	?>
</body>
</html>