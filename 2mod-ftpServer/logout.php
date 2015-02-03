<?php
    session_start();
?>
<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8" />
    <title>Logged Out</title>
</head>

<body>
    <?php
	echo "You are now logged out.";
	session_destroy();
    ?>
    
    <form action="login.html">
	<input type="submit" value="Back">
    </form>
</body>
</html>
