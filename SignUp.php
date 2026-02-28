<?php
// SignUp.php (Green page)

include('functions.php');
$cookieMessage = getCookieMessage();
?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8" />
	<title>Sign Up</title>
	<link rel="stylesheet" type="text/css" href="shopstyle.css" />
</head>
<body>

	<nav class="navbar">
		<a class="navLink" href="ProductList.php">Products</a>
		<a class="navLink" href="ViewCart.php">Cart</a>
		<a class="navLink" href="Homepage.php">Home</a>
	</nav>

	<h1>Create an Account</h1>

	<?php if ($cookieMessage !== ""): ?>
		<div class="cookieMessage"><?php echo $cookieMessage; ?></div>
	<?php endif; ?>

	<form action="AddNewCustomer.php" method="post" class="signupForm">

		<label for="UserName">User Name</label><br />
		<input type="text" name="UserName" id="UserName" required /><br /><br />

		<label for="FirstName">First Name</label><br />
		<input type="text" name="FirstName" id="FirstName" required /><br /><br />

		<label for="LastName">Last Name</label><br />
		<input type="text" name="LastName" id="LastName" required /><br /><br />

		<label for="Address">Address</label><br />
		<input type="text" name="Address" id="Address" required /><br /><br />

		<label for="City">City</label><br />
		
		<input type="text" name="City" id="City" required /><br /><br />

		<input type="submit" value="Register"  href="ProductList.php"/>

	</form>

</body>
</html>
