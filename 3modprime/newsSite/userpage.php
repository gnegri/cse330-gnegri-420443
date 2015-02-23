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
    ?>
    
    <strong>Welcome to your User Console, <?php echo $_SESSION['uname']; ?>.</strong>
    <br>
    <br>
    <strong>Your Stories:</strong>
        <?php
        // Get stories
        $stmt0 = $mysqli->prepare("SELECT id, title, url, description FROM stories WHERE uid=? ORDER BY id DESC");      
        
        if(!$stmt0){
            printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
            exit;
        } else {
        
            // Send the query
            $stmt0->bind_param('d', $_SESSION['uid']);
            $stmt0->execute();
            
            // Get  the results of the query
            $stmt0->bind_result($sid, $title, $url, $desc);
            
            while($stmt0->fetch()) {
                printf("
                <br>
                <div class='story'>
                    <p class='story_title'>
                        %d: <a href=%s>%s</a>                    
                    </p>
                    <p class='story_desc'>
                        %s
                    </p>
                    <form action='story.php' method='POST'>
                        <input type='hidden' name='sid' value='%d' />",
                    $sid, $url, $title, $desc, $sid); ?>
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                        <input type='submit' value='Discuss' />
                    </form>
                </div> <?php
            }
            
            $stmt0->close();
        } ?>
    <br>
    <br>    
    <strong>Your Responses:</strong>
    <?php
        // Get responses
        $stmt1 = $mysqli->prepare("SELECT id, sid, response FROM responses WHERE uid=? ORDER BY id DESC");      
        
        if(!$stmt1){
            printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
            exit;
        } else {        
            // Send the query
            $stmt1->bind_param('d', $_SESSION['uid']);
            $stmt1->execute();
            
            // Get  the results of the query
            $stmt1->bind_result($rid, $sid, $response);
            
            while($stmt1->fetch()) {                    
                printf("
                    <div class='story'>
                        <p class='reply'>
                            %d: %s
                        </p>
                        <form action='story.php' method='POST'>
                            <input type='hidden' name='sid' value='%d' />",
                    $rid, $response, $sid); ?>
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                            <input type='submit' value='See Context' />
                        </form>
                    </div><br> <?php
                    
            }
            
            $stmt1->close();
        }
        ?>
    
</body>
</html>