<?php
// EmptyCart.php (Orange file: no HTML, POST-only)

include('functions.php');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
	setCookieMessage("Invalid request method.", true);
	header("Location: ViewCart.php");
	exit;
}

// Only delete cart if the correct button name was submitted
if (!isset($_POST['EmptyCartButton']))
{
	setCookieMessage("Invalid request.", true);
	header("Location: ViewCart.php");
	exit;
}

// Delete cookie (set expiry in the past)
setcookie("ShoppingCart", "", time() - 3600, "/");

// Message for user
setCookieMessage("Your cart has been emptied.");

// Redirect back
header("Location: ViewCart.php");
exit;
