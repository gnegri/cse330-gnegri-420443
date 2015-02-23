<?php session_start(); ?>
<!DOCTYPE html>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="./feelu.css" />
    <meta charset="utf-8" />
    <title>feel u.</title>
</head>

<body>
    <div class="banner">
        <a href="home.php">we feel u.</a>
    </div>

    <?php session_destroy(); ?>
    
    <p>You are now logged out.</p>

</body>
</html>
