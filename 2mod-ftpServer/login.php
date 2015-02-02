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
    <p>
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
                fwrite($stream, $_SESSION['user']);
                fclose($stream);
                mkdir('/srv/2usr/'.$_SESSION['user']);
                fopen('/srv/2usr/'.$_SESSION['user'].'/'.'welcome.TXT','w');
		echo "You are now signed up."; }
	    else {
		echo "Welcome back!"; }
        ?>
    </p>
       
    <p>
        <header><strong>Your Files:</strong></header>
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
                    echo '<form action="delete.php" method="POST">
                            <input type="hidden" value='.$value.' name="filedel" />
                            <input type="submit" value="Delete" />
                        </form>';
                 }
            ?>
    
    </p>
    
    <p>
	<form action="upload.php" method="POST"  enctype="multipart/form-data">
            <label for="fileup"><strong>Upload:</strong></label><br>
            <input type="file" name="fileup" id="fileup" />
            <input type="submit" value="Upload" name="submit" />
        </form>
    </p>
    
    <p>
        <form action="logout.php" method="POST">
            <input type="submit" value="Log Out" name="logout" />
        </form>
    </p>
</body>
</html>
