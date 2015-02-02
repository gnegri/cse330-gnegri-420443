<?php
    session_start
?>
<!DOCTYPE html>

<html>
<head>
    <title>Logged Out</title>
</head>

<body>
    <p>
        <?php
            session_destroy();
        ?>
    </p> 
</body>
</html>