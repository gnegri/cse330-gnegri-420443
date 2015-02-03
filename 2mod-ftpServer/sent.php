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
        
        if($isin === false) {
            echo "Not a valid user";
        } else {
            copy($src, $dest);
            echo "File copied to ".$_POST['target'];
        }
    ?>
    
    <form action="login.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $idtmp ?>">
        <input type="submit" value="Back">
    </form>
    
</body>
</html>