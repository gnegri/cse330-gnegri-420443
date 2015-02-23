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
    
    if(isset($_POST['sid'])){
        $sid = $_POST['sid'];
        
        if(isset($_SESSION['token'])) {
            $tmpuid = $_SESSION['uid'];
            ?>
            <form class="inline" action="userpage.php">
                <input type="submit" value="View My User Page">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
            </form>
            <form class="inline" action="logout.php">
                <input type="submit" name="logout" value="Log Out">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
            </form><br><br>
            <?php
            
            if(isset($_POST['update'])) {
                $newReply = $_POST['replyedit']." (Edited)";
                $rid = $_POST['rid'];
                
                $stmt3 = $mysqli->prepare("UPDATE responses SET response=? WHERE (id, sid, uid) = (?, ?, ?)");      
                if(!$stmt3){
                    printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
                } else {
                    // Send the query
                    echo $rid.$sid.$tmpuid;
                    $stmt3->bind_param('sddd', $newReply, $rid, $sid, $tmpuid);
                    $stmt3->execute();
                    $stmt3->close();
                    
                    
                    printf(htmlentities("Post edited."));
                    echo '<br><br>';
                }
            }
            
            if(isset($_POST['updatestory'])) {
                $newReplyStory = $_POST['replystory']." (Edited)";
                
                $stmt7 = $mysqli->prepare("UPDATE stories SET description=? WHERE (id, uid) = (?, ?)");      
                if(!$stmt7){
                    printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
                } else {
                    // Send the query
                    $stmt7->bind_param('sdd', $newReplyStory, $sid, $tmpuid);
                    $stmt7->execute();
                    $stmt7->close();
                    
                    printf(htmlentities("Post edited."));
                    echo '<br><br>';
                }
            }
            
        }
    
        // Load Story
        $stmt = $mysqli->prepare("SELECT uid, title, url, description FROM stories where id=?");      
        if(!$stmt){
            printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
            exit;
        } else {
         
            // Send the query
            $stmt->bind_param('d', $sid);
            $stmt->execute();
            
            // Get  the results of the query
            $stmt->bind_result($uid, $title, $url, $desc);
            $stmt->fetch();
            $stmt->close();
            
            printf("
            <div class='story'>
                <p class='story_title' >
                    $sid: <a class='inline' href=$url>$title</a>");
            if(isset($_SESSION['token'])) {
                if($uid==$tmpuid) { ?>
                    <form class="inline" float="right" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method='POST'>
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                        <input type="hidden" name="sid" value="<?php echo $sid; ?>" />
                        <input type="submit" name="editstory" value="Edit" />
                    </form>
                    <form class="inline" float="right" action="edit.php" method='POST'>
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                        <input type="hidden" name="sid" value="<?php echo $sid; ?>" />
                        <input type="submit" name="deletestory" value="Delete" />
                    </form><br><br>
                    <?php
                    if(isset($_POST['editstory'])) {
                        ?>
                        <form method="POST" id="replystory" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
                            <p>
                                <textarea style="width:90%; height:10%" name="replystory" form="replystory"><?php echo $desc; ?></textarea>
                                <input type="submit" name="updatestory" value="Update" float="left" />
                            </p>
                            <input type="hidden" name="sid" value="<?php echo $sid; ?>" />
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                        </form>
                        
                        <?php
                    }
                }
            }        
            printf("
                 </p>
                <p class='story_desc'>
                    $desc
                </p>
            </div>
            <br>");
        }
        
        // Add comments
        if(isset($_SESSION['token'])) { ?>
        <div class="story">
            <strong>Post a new reply:</strong>
            <form method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
                <p>
                    <textarea style="width:90%; height:10%" name="reply" id="reply"
                              placeholder="Reply"></textarea>
                    <input type="submit" name="post" value="Post" float="left" />
                </p>
                <input type="hidden" name="sid" value="<?php echo $sid; ?>" />
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
            </form>
        </div>
        <br>
        <?php }
        
        if(isset($_POST['post']) && isset($_POST['reply'])) {
            if(empty($_POST['reply'])) {
                printf(htmlentities("Please enter a reply."));
                exit;
            } else {
                $response = $_POST['reply'];
                
                // Ready the query
                $stmt1 = $mysqli->prepare("INSERT INTO responses (sid, uid, response) values (?, ?, ?)");      
                if(!$stmt1){
                    printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
                    exit;
                } else {
                
                    // Send the query
                    $stmt1->bind_param('dds', $sid, $tmpuid, $response);
                    $stmt1->execute();
                    $stmt1->close();
                    
                    printf(htmlentities("Success!"));
                }
            }
        }
        
        // Load Comments
        $stmt2 = $mysqli->prepare("SELECT id, uid, response FROM responses WHERE sid=? ORDER BY id DESC");      
        if(!$stmt2){
            printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
            exit;
        } else {
            // Send the query
            $stmt2->bind_param('d', $sid);
            $stmt2->execute();
            
            // Get  the results of the query
            $stmt2->bind_result($rid, $uid, $response);
            
            printf("<div class='replies'>");
            while($stmt2->fetch()) {
                printf("
                    <p class='reply'>
                        %d: %s
                    </p>",
                $rid, $response);
                if(isset($_SESSION['token'])) {
                    if($uid == $tmpuid) { ?>
                        <form class="inline" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                            <input type="hidden" name="sid" value="<?php echo $sid; ?>" />
                            <input type="submit" name="edit" value="Edit" />
                        </form>
                        <form class="inline" action="edit.php" method="POST">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                        <?php   printf('<input type="hidden" name="rid" value=%d />', $rid); ?>
                            <input type="hidden" name="sid" value="<?php echo $sid; ?>" />
                            <input type="submit" name="delete" value="Delete" />
                        </form> 
                        <?php
                        if(isset($_POST['edit'])) {
                            ?>
                            <form method="POST" id="replyedit" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
                                <p>
                                    <textarea style="width:90%; height:10%" name="replyedit" form="replyedit"><?php echo $response; ?></textarea>
                                    <input type="submit" name="update" value="Update" float="left" />
                                </p>
                                <input type="hidden" name="sid" value="<?php echo $sid; ?>" />
                                <?php   printf('<input type="hidden" name="rid" value=%d />', $rid); ?>
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                            </form><?php
                        }
                    }
                
                    
                }
            }
            printf("<div>");
            $stmt2->close();
        }
    }
    ?>
    
</body>
</html>