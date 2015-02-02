<?php
    session_start();
?>
<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8" />
    <title>Upload</title>
</head>

<body>
    <?php
        $idtmp = str_replace("dir","",$_SESSION['user']);
        $dest = str_replace(" ","_",'/srv/2usr/'.$_SESSION['user'].'/'.basename($_FILES['fileup']['name']));
        if(!file_exists($dest)) {
            if(move_uploaded_file($_FILES['fileup']['tmp_name'], $dest)) {
                echo "File upload successful!";
            } else {
                echo "Sorry, an error occurred: Upload failed";
            }
        } else {
            echo "Sorry, an error occurred: File already in file system";
        }
    ?>
    
    <form action="login.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $idtmp ?>">
        <input type="submit" value="Back">
    </form>
        
</body>
</html>