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
    
    <?php
    require 'database.php';
    
    if(isset($_POST['delete'])) {
        $rid = $_POST['rid'];
        $sid = $_POST['sid']; 
        
        $stmt5 = $mysqli->prepare("DELETE FROM responses WHERE (id, sid, uid) = (?, ?, ?)");      
        if(!$stmt5){
            printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
        } else {
            // Send the query
            $stmt5->bind_param('ddd', $rid, $sid, $_SESSION['uid']);
            $stmt5->execute();
            $stmt5->close();
            
            printf(htmlentities("Post deleted."));
            echo '<br><br>';
            ?>
            <form action='story.php' method='POST'>
                <input type='hidden' name='token' value='<?php echo $_SESSION['token']; ?>' />
                <input type='hidden' name='sid' value='<?php echo $sid ?>'/>
                <input type='submit' value='Back to Story' />
            </form>
            <?php
        }
    } elseif(isset($_POST['deletestory'])) {
        $sid = $_POST['sid'];
        
        $stmt3 = $mysqli->prepare("DELETE FROM responses WHERE sid=?");      
        if(!$stmt3){
            printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
        } else {
            // Send the query
            $stmt3->bind_param('d', $sid);
            $stmt3->execute();
            $stmt3->close();
        }
        
        $stmt4 = $mysqli->prepare("DELETE FROM stories WHERE (id, uid) = (?, ?)");      
        if(!$stmt4){
            printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
        } else {
            // Send the query
            $stmt4->bind_param('dd', $sid, $_SESSION['uid']);
            $stmt4->execute();
            $stmt4->close();
    
            printf(htmlentities("Post deleted."));
            echo '<br><br>';
        }
        
        ?>
        <form class="inline" action="userpage.php">
	    <input type="submit" value="View My User Page">
	    <input type="hidden" name="token" value="'.$_SESSION['token'].'" />
	</form>
        <?php
    }
    ?>


        
        
</body>
</html>