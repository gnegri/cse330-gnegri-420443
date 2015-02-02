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
            $_SESSION['user'] = $_POST['id']."dir";
	    $pos = strpos($USERS, $_SESSION['user']);

            if($pos === false) {
                file_put_contents($USERFILE, $_SESSION['user']);
                mkdir("./".$_SESSION['user']);
                fopen("./".$_SESSION['user']."/"."Welcome!.TXT","w");
		echo "You are now signed up."; }
	    else {
		echo "Welcome back!"; }
        ?>
    </p>
    
    <p>
        <header>Your Files</header>
        <?php
	    $_SESSION['files'] = scandir("./".$_SESSION['user']);
	    
	    foreach ($_SESSION['files'] as $value) {
		    echo $value;
		    echo "<br>";
		    $path = "./".$_SESSION['user']."/".$value;
		    
	    }
		    
        ?>
    </p>
    
    <p>
        <header>Upload</header>
	<form action="upload.php" method="POST">
		<p>
			<label for="upl">Select a file</label>
			<input type="file" name="fileUp" id="fileup">
			<input type="submit" value="Upload" name="submit">
    		</p>
        
    <form action="logout.php" method="POST">
	<p>
            <input type="submit" name="logout" value="Log Out"/>
        </p>
    </form>
</body>
</html>
