<?php // <--- do NOT put anything before this PHP tag
// AddNewCustomer.php (Orange file: no HTML, POST-only)

include('functions.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
	setCookieMessage("Invalid request method.");
	redirect("SignUp.php");
}

// Validate required POST fields
$required = ['UserName', 'FirstName', 'LastName', 'Address', 'City'];
foreach ($required as $field)
{
	if (!isset($_POST[$field]))
	{
		echo "$field not provided, make sure your form is using POST";
		exit;
	}
}

// Trim inputs
$UserName  = trim($_POST['UserName']);
$FirstName = trim($_POST['FirstName']);
$LastName  = trim($_POST['LastName']);
$Address   = trim($_POST['Address']);
$City      = trim($_POST['City']);

// Basic empty checks
if ($UserName === "" || $FirstName === "" || $LastName === "" || $Address === "" || $City === "")
{
	setCookieMessage("All fields are required.");
	redirect("SignUp.php");
}

try
{
	$dbh = connectToDatabase();

	// Check if username is taken (case-insensitive)
	$statement = $dbh->prepare("SELECT CustomerID FROM Customers WHERE UserName = ? COLLATE NOCASE;");
	$statement->bindValue(1, $UserName, PDO::PARAM_STR);
	$statement->execute();

	if ($statement->fetch(PDO::FETCH_ASSOC))
	{
		setCookieMessage("The UserName '$UserName' is taken. Please choose another.");
		redirect("SignUp.php");
	}

	// Insert new customer (CustomerID is auto-generated)
	$statement2 = $dbh->prepare(
		"INSERT INTO Customers (UserName, FirstName, LastName, Address, City)
		 VALUES (?, ?, ?, ?, ?);"
	);

	$statement2->bindValue(1, $UserName, PDO::PARAM_STR);
	$statement2->bindValue(2, $FirstName, PDO::PARAM_STR);
	$statement2->bindValue(3, $LastName, PDO::PARAM_STR);
	$statement2->bindValue(4, $Address, PDO::PARAM_STR);
	$statement2->bindValue(5, $City, PDO::PARAM_STR);
	$statement2->execute();

	setCookieMessage("Welcome $FirstName! Registration successful.");
	redirect("Homepage.php");
}
catch (Exception $ex)
{
	setCookieMessage("Registration failed: " . $ex->getMessage());
	redirect("SignUp.php");
}
