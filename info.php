<!DOCTYPE html>

<html>
<head>
    <title>Module 2 Calculator</title>
</head>

<body>
    <p>
<?php

    function add($x=0, $y=0){
        return $x+$y;
    }
    function subtract($x=0, $y=0){
        return $x-$y;
    }
    function mult($x=1, $y=1){
        return $x*$y;
    }
    function div($x=1, $y=1){
        return $x/$y;
    }
    
    $a = (double) $_POST['a'];
    $b = (double) $_POST['b'];
    $action = $_POST['do'];
    
    switch($action) {
        case "add"
            echo add($a,$b);
            break;
        case "subtract"
            echo subtract($a,$b);
            break;
        case "mult"
            echo mult($a,$b);
            break;
        case "div"
            echo div($a,$b);
            break;
    }
    
?>
</p> 
</body>
</html>