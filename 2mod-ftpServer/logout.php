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
    <p>
	<?php
	    echo "You are now logged out.";
            session_destroy();
        ?>
    </p>
    
    <p>
        <form action="login.html">
            <input type="submit" value="Back">
        </form>
    </p>
</body>
</html>
