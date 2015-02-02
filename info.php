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
    $do = $_POST['do'];
    
    switch($do) {
        case "add"
            echo $a+$b;
            break;
        case "subtract"
            echo $a-$b;
            break;
        case "mult"
            echo $a*$b;
            break;
        case "div"
            echo $a/$b;
            break;
    }
    
?>
</p> 
</body>
</html>
