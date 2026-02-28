<?php
// AddToCart.php
// This file must NOT output any HTML

include('functions.php');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
	setCookieMessage("Invalid request method.", true);
	header("Location: ProductList.php");
	exit;
}

// ProductID must come from the URL
if (!isset($_GET['ProductID']))
{
	setCookieMessage("No product specified.", true);
	header("Location: ProductList.php");
	exit;
}

$productID = trim($_GET['ProductID']);

if ($productID === "")
{
	setCookieMessage("Invalid product.", true);
	header("Location: ProductList.php");
	exit;
}

// Get existing cart
if (isset($_COOKIE['ShoppingCart']))
{
	$cart = $_COOKIE['ShoppingCart'];

	// Prevent duplicate entries
	if (!stringContains($cart, $productID))
	{
		$cart .= "," . $productID;
	}
}
else
{
	// Create new cart
	$cart = $productID;
}

// Store cart for 7 days
setcookie(
	"ShoppingCart",
	$cart,
	time() + (60 * 60 * 24 * 7),
	"/"
);

// Success message
setCookieMessage("Product added to cart.");

// Redirect back to ViewProduct.php
header("Location: ViewProduct.php?ProductID=" . urlencode($productID));
exit;
