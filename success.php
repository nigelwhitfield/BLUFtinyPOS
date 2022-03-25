<?php
// TinyPOS success page

require('config.php') ;

mail($storeemail,'BLUF tiny POS order confirmed','Order confirmed for reference ' . $_REQUEST['ref']) ;

?>
<html>
<head>
	<meta http-equiv="Cache-control" content="no-cache">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>BLUF tiny POS - Checkout complete</title>
	<link rel="stylesheet" href="pos.css">
</head>
<body>
	<h1>Checkout complete</h1>
	<p>Congratulations! You have checked out successfully.</p>
	<p>Please show this screen to one of our staff members, so they can prepare your items.</p>
	<p id="orderref">Order reference <?php echo $_REQUEST['ref']; ?></p>
</body>
</html>