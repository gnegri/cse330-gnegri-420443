<?php
    session_start
?>
<!DOCTYPE html>

<html>
<head>
    <title>Logged In</title>
</head>

<body>
    <p>
        <?php
            $USERFILE = '/srv/2usr/users.txt';
            $USERS = file_get_contents($USERFILE);
            $_SESSION["user"] = isset($_POST['id']) ? $_POST['id'] : 'default';
            
            if(strpos($USERS, $_SESSION["user"]) === true) {
                file_put_contents($USERFILE, $_SESSION["user"]);
                echo "You are now signed up."; }
            else { echo "Welcome back!"; }
        ?>
    </p>
    
    <p>
        <header>Your Files</header>
        <?php
            $_SESSION["files"] = scandir("./".$_SESSION["user"]);
            print_r($_SESSION["files"]);
        ?>
    </p>
    
    <p>
        <header>Upload</header>
        
    </p>
        
    <form action="logout.php" method="POST">
        <p>
            <input type="submit" name="Log Out" />
        </p>
    </form>
</body>
</html>
