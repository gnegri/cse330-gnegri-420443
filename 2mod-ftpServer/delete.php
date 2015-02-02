<?php
    session_start();
?>
<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8" />
    <title>File Deletion</title>
</head>

<body>
    <?php
        $idtmp = (string) str_replace("dir","",$_SESSION['user']);
        $todel = (string) '/srv/2usr/'.$_SESSION['user'].'/'.$_POST['filedel'];
        unlink($todel);
        echo $_POST['filedel']." deleted"
    ?>
    
    <form action="login.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $idtmp ?>" />
        <input type="submit" value="Back" />
    </form>
        
</body>
</html>