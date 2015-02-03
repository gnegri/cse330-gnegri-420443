<?php
    session_start();
?>
<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8" />
    <title>File Download</title>
</head>

<body>
    <?php
        $idtmp = str_replace("dir","",$_SESSION['user']);
        $file = (string) $_POST['filedown'];
        $path = '/srv/2usr/'.$_SESSION['user'].'/'.$file;
        $_SESSION['tmp'] = './tmp'.$file;
                    
        copy($path, $_SESSION['tmp']);
        echo '<a href='.$_SESSION['tmp'].' download=ftp-'.$file.'>'.$file.'</a>';
    ?>
    
    <form action="login.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $idtmp ?>" />
        <input type="hidden" name="dld" value="set" />
        <input type="submit" value="Back" />
    </form>
    
</body>
</html>