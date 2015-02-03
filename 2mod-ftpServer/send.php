<?php
    session_start();
?>
<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8" />
    <title>File Sharing</title>
</head>

<body>
    <?php
        $idtmp = str_replace("dir","",$_SESSION['user']);
        $file = (string) $_POST['filesend'];
    ?>
    
    <p>
        <Strong>File: <?php echo $file ?></Strong>
    </p>
    <form action="sent.php" method="POST">
        <label for="target">Send To:</label>
        <input type="hidden" name="send" value="<?php echo $file ?>" />
        <input type="text" name="target" id="target" />
    </form>
    
    <form action="login.php" method="POST">
        <p>
            <input type="hidden" name="id" value="<?php echo $idtmp ?>">
            <input type="submit" value="Back">
        </p>
    </form>
</body>
</html>