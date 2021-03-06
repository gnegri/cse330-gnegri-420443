<?php
    session_start();
?>
<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8" />
    <title>Logged In</title>
</head>

<body>
    <?php
        $USERFILE = '/srv/2usr/users.txt';
        $USERS = (string) file_get_contents($USERFILE);
        $_SESSION['user'] = $_POST['id']."dir";
        $pos = strpos($USERS, $_SESSION['user']);
        
        if(isset($_POST['dld'])) {
            if(!file_exists('tmp')) {
                fopen('./tmp', "w");
            }
            
            unlink('./'.$_SESSION['tmp']);
            unset($_SESSION['tmp']);
        }

        if($pos === false) {
            $stream=fopen($USERFILE,"a");
            fwrite($stream, " ".$_SESSION['user']);
            fclose($stream);
            if(!file_exists('/srv/2usr/'.$_SESSION['user'])) {
                mkdir('/srv/2usr/'.$_SESSION['user']);
                fopen('/srv/2usr/'.$_SESSION['user'].'/'.'welcome.TXT','w');
            }
            echo "You are now signed up."; }
        else {
            echo "Welcome back!"; }
    ?>
       
    <p>
        <strong>Your Files:</strong>
    </p>
    
    <?php
        $_SESSION['files'] = scandir('/srv/2usr/'.$_SESSION['user']);
        unset($_SESSION['files'][0]);
        unset($_SESSION['files'][1]);
        
        foreach ($_SESSION['files'] as $value) {
            $file = '/srv/2usr/'.$_SESSION['user'].'/'.$value;
            echo $value;
            echo '<form action="download.php" method="POST">
                    <input type="hidden" value='.$value.' name="filedown" />
                    <input type="submit" value="Download" />
                </form>';
            echo '<form action="send.php" method="POST">
                <input type="hidden" value='.$value.' name="filesend" />
                <input type="submit" value="Send" />
            </form>';
            echo '<form action="delete.php" method="POST">
                    <input type="hidden" value='.$value.' name="filedel" />
                    <input type="submit" value="Delete" />
                </form><br>';
         }
    ?>
    
    <form action="upload.php" method="POST" enctype="multipart/form-data">
            <label for="fileup"><strong>Upload:</strong></label><br>
            <input type="file" name="fileup" id="fileup" />
            <input type="submit" value="Upload" name="submit" />
    </form>
    
    <form action="logout.php" method="POST">
        <p>
            <input type="submit" value="Log Out" name="logout" />
        </p>
    </form>
    
    <form action="deleteme.php" method="POST">
        <p>
            <input type="hidden" value='.$USERFILE.' name="delme" />
            <input type="submit" value="Delete Me" />
        </p>
    </form>
    
</body>
</html>
