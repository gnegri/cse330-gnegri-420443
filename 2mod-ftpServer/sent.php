<?php
    session_start();
?>
<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8" />
    <title>File Sent</title>
</head>

<body>
    <?php
        $idtmp = str_replace("dir","",$_SESSION['user']);
        $USERFILE = '/srv/2usr/users.txt';
        $USERS = (string) file_get_contents($USERFILE);
        $FILE = (string) $_POST['send'];
        $src = '/srv/2usr/'.$_SESSION['user'].'/'.$FILE;
        $dest = '/srv/2usr/'.$_POST['target'].'dir/'.$FILE;
        $isin = strpos($USERS, $_POST['target'].'dir');
        
        if($_POST['target'] == $idtmp) {
            echo "You can't send a file to yourself.<br><br>";
        } else {        
            if($isin === false) {
                echo $_POST['target']." is not a valid user.<br><br>";
            } else {
                if(file_exists($dest)) {
                    echo $_POST['target']." already has a copy of that file.<br><br>";
                } else {
                    copy($src, $dest);
                    echo "File copied to ".$_POST['target'].".<br><br>";
                }
            }
        }
    ?>
    
    <form action="send.php" method="POST">
        <p>
            <input type="hidden" value="<?php echo $FILE ?>" name="filesend" />
            <input type="submit" value="Send to Another User" />
        </p>
    </form>
    
    <form action="login.php" method="POST">
        <p>
            <input type="hidden" name="id" value="<?php echo $idtmp ?>">
            <input type="submit" value="Back to Main">
        </p>
    </form>
    
</body>
</html>