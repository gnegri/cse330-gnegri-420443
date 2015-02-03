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
        $USERFILE = '/srv/2usr/users.txt';
        $USERS = (string) file_get_contents($USERFILE);
        $USERS = str_replace($_SESSION['user'],"",$USERS);
	$PATH = '/srv/2usr/'.$_SESSION['user'];
	file_put_contents($USERFILE,$USERS);
	$files = scandir($PATH);
	unset($files[0]);
	unset($files[1]);
	foreach($files as $file) {
	    unlink('/srv/2usr/'.$_SESSION['user'].'/'.$file);
	}
	rmdir($PATH);
	session_destroy();
        echo "Gone. Now go sign up again.";
    ?>
    
    <form action="login.html">
        <input type="submit" value="Back">
    </form>
        
</body>
</html>