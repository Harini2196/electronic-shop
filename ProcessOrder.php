<?php
// ProcessOrder.php (Orange file: no HTML, POST-only)

include('functions.php');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
	setCookieMessage("Invalid request method.", true);
	header("Location: ViewCart.php");
	exit;
}

if (!isset($_POST['ConfirmOrderButton']))
{
	setCookieMessage("Invalid request.", true);
	header("Location: ViewCart.php");
	exit;
}

// Must have username
$userName = isset($_POST['UserName']) ? trim($_POST['UserName']) : "";

if ($userName === "")
{
	setCookieMessage("Please enter a username to confirm your order.", true);
	header("Location: ViewCart.php");
	exit;
}

// Must have items in cart
if (!isset($_COOKIE['ShoppingCart']) || trim($_COOKIE['ShoppingCart']) === "")
{
	echo "SYSTEM ERROR: No items in cart.";
	exit;
}

// Parse cart cookie
$cartRaw = trim($_COOKIE['ShoppingCart']);
$cartParts = explode(",", $cartRaw);
$productIDs = [];

foreach ($cartParts as $p)
{
	$p = trim($p);
	if ($p !== "")
	{
		$productIDs[] = $p;
	}
}

// Remove duplicates
$productIDs = array_values(array_unique($productIDs));

if (count($productIDs) === 0)
{
	echo "SYSTEM ERROR: No items in cart.";
	exit;
}

try
{
	$dbh = connectToDatabase();
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// Transaction = all-or-nothing (safe)
	$dbh->beginTransaction();

	// 1) SELECT CustomerID for given Username
	$stmtCustomer = $dbh->prepare(
		"SELECT CustomerID
		 FROM Customers
		 WHERE UserName = ?;"
	);
	$stmtCustomer->bindValue(1, $userName, PDO::PARAM_STR);
	$stmtCustomer->execute();

	$customerRow = $stmtCustomer->fetch(PDO::FETCH_ASSOC);

	if (!$customerRow)
	{
		$dbh->rollBack();
		setCookieMessage("Order failed: username not found.", true);
		header("Location: ViewCart.php");
		exit;
	}

	$customerID = intval($customerRow['CustomerID']);

	// 2) INSERT new Order
	// Using CURRENT_TIMESTAMP for SQLite
	$stmtOrder = $dbh->prepare(
		"INSERT INTO Orders (CustomerID, TimeStamp)
		 VALUES (?, CURRENT_TIMESTAMP);"
	);
	$stmtOrder->bindValue(1, $customerID, PDO::PARAM_INT);
	$stmtOrder->execute();

	$orderID = intval($dbh->lastInsertId());

	// 3) INSERT OrderProducts (Quantity always 1)
	$stmtOrderProducts = $dbh->prepare(
		"INSERT INTO OrderProducts (OrderID, ProductID, Quantity)
		 VALUES (?, ?, 1);"
	);

	foreach ($productIDs as $pid)
	{
		$stmtOrderProducts->bindValue(1, $orderID, PDO::PARAM_INT);
		$stmtOrderProducts->bindValue(2, $pid, PDO::PARAM_STR);
		$stmtOrderProducts->execute();
	}

	$dbh->commit();

	// Clear cart after successful order
	setcookie("ShoppingCart", "", time() - 3600, "/");

	// Success message + redirect to receipt page
	setCookieMessage("Order placed successfully!");
	header("Location: ViewOrderDetails.php?OrderID=" . urlencode($orderID));
	exit;
}
catch (Exception $ex)
{
	// Rollback if something breaks mid-way
	if (isset($dbh) && $dbh->inTransaction())
	{
		$dbh->rollBack();
	}

	setCookieMessage("Order failed: " . $ex->getMessage(), true);
	header("Location: ViewCart.php");
	exit;
}
