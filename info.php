<!DOCTYPE html>

<html>
<head>
    <title>Module 2 Calculator</title>
</head>

<body>
    <p>
<?php
    
    $a = (double) $_POST['a'];
    $b = (double) $_POST['b'];
    $do = isset($_POST['do']) ? $_POST['do'] : 'none';
	    
    switch($do) {
    case "add":
            echo $a+$b;
            break;
    case "subtract":
            echo $a-$b;
            break;
    case "mult":
            echo $a*$b;
            break;
    case "div":
            echo $a/$b;
	    break;
    case "none":
	echo htmlentities("Please select a function");
    }
    
?>
</p> 
</body>
</html>
