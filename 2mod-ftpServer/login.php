<?php
    session_start();
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
            $_SESSION['user'] = isset($_POST['id']) ? $_POST['id'] : 'default';
            
            if(strpos($USERS, $_SESSION['user']) === true) {
                echo "Welcome back!"; }
            else {
                file_put_contents($USERFILE, $_SESSION['user']);
                mkdir("./".$_SESSION['user']);
                fopen("./".$_SESSION['user']."Welcome!.TXT","w");
                echo "You are now signed up."; }
        ?>
    </p>
    
    <p>
        <header>Your Files</header>
        <?php
            $_SESSION['files'] = scandir("./".$_SESSION['user']);
            print_r($_SESSION['files']);
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
