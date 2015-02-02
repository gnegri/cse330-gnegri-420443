<?php
    session_start();
?>
<!DOCTYPE html>

<html>
<head>
    <title>Logged Out</title>
</head>

<body>
    <p>
	<?php
		echo "You are now logged out.";
            session_destroy();
        ?>
    </p> 
</body>
</html>
