<?php session_start(); ?>
<!DOCTYPE html>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="./feelu.css" />
    <meta charset="utf-8" />
    <title>feel u.</title>
</head>

<body>
    <div class="banner">
        <a href="home.php">we feel u.</a>
    </div>
    
    <?php
    require 'database.php';
    
    if(!isset($_SESSION['token'])) { ?>
	<strong>Log In or Register</strong>
	<div>
	    <form method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
		<p>
		    <input type="text" name="uname" id="uname" min="1" max="32" placeholder="Username" />
		</p>
		<p>
		    <input type="password" name="upass1" id="upass1" placeholder="Password" />
		    <input type="submit" name="login" value="Log In" float="left"/>
		</p>
		<p>
		    <input type="password" name="upass2" id="upass2" min="6" placeholder="Retype Password" />
		    <input type="submit" name="register" value="Register" float="left" />
		</p>
	    </form>
	</div>
	<?php
	if(isset($_POST['uname']) && isset($_POST['upass1']) && isset($_POST['login'])) {
	    
	    if(empty($_POST['uname'])) {
		printf(htmlentities("Please enter a username."));
		echo '<br><br>';
	    } elseif(strlen($_POST['uname'])>32) {
		printf(htmlentities("Usernames must be shorter than 32 characters."));
		echo '<br><br>';
	    } else {
	    
		$username = $_POST['uname'];
		$pass_entered = $_POST['upass1'];
		
		// Ready the query
		$stmt = $mysqli->prepare("SELECT id, name, pass FROM accounts WHERE name =?");      
		
		if(!$stmt){
		    printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
		    exit;
		} else {
		
		    // Send the query
		    $stmt->bind_param('s', $username);
		    $stmt->execute();
		    
		    // Get  the results of the query
		    $stmt->bind_result($uid, $uname, $upass);
		    $stmt->fetch();
		    $stmt->close();
	    
		    // Compare the submitted password to the actual password hash
		    if(crypt($pass_entered, $upass) == $upass) {
			$_SESSION['uid'] = $uid;
			$_SESSION['uname'] = $uname;
			$_SESSION['token'] = substr(md5(rand()),0,10);
			printf(htmlentities("Login successful! Welcome back."));
			echo '<br><br>';
		    } else {
			printf(htmlentities("Incorrect username or password."));
			echo '<br><br>';
		    }
		}
	    }

	} elseif(isset($_POST['uname']) && isset($_POST['upass1']) && isset($_POST['upass2']) && isset($_POST['register'])) {
	    
	    if(empty($_POST['uname'])) {
		printf(htmlentities("Please enter a username."));
		echo '<br><br>';
	    } elseif(empty($_POST['upass1']) || empty($_POST['upass2'])) {
		printf(htmlentities("Please enter a password."));
		echo '<br><br>';
	    } elseif($_POST['upass1'] != $_POST['upass2']) {
		printf(htmlentities("Entered passwords must match."));
		echo '<br><br>';
	    } elseif(strlen($_POST['uname']) > 32) {
		printf(htmlentities("Usernames must be shorter than 32 characters."));
		echo '<br><br>';
	    } elseif(strlen($_POST['upass1']) < 6) {
		printf(htmlentities("Passwords must be longer than 6 characters."));
		echo '<br><br>';
	    } else {
	    
		$username = $_POST['uname'];
		$pass_hashed = crypt($_POST['upass1'], '$2a$10$oIoNEEDoTOoSLEEPoMOREo');
		
		// Check if username is already in use
		$stmt0 = $mysqli->prepare("SELECT name FROM accounts WHERE name=?");
		if(!$stmt0){
		    printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
		    exit;
		}
		
		// Send the query
		$stmt0->bind_param('s', $username);
		$stmt0->execute();
		
		// Get  the results of the query
		$stmt0->bind_result($tmp);
		$stmt0->fetch();
		$stmt0->close();
		
		if(!empty($tmp)) {
		    printf(htmlentities("Username already in use."));
		    echo '<br><br>';
		} else {
		    // Username isn't in use
		    $stmt = $mysqli->prepare("INSERT INTO accounts (name, pass) values (?, ?)");      
		    if(!$stmt){
			printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
		    } else {
			// Send the query
			$stmt->bind_param('ss', $username, $pass_hashed);
			$stmt->execute();
			$stmt->close();
		
			// Initialize the new user's session
			$_SESSION['uid'] = $mysqli->insert_id;
			$_SESSION['uname'] = $username;
			$_SESSION['token'] = substr(md5(rand()),0,10);
		
			printf(htmlentities("Registration successful! Welcome ".$_SESSION['uname'].". We feel u."));
			echo '<br><br>';
		    }
		}
	    }
	}
	
    } else {
	?>

	<form class="inline" action="userpage.php">
	    <input type="submit" value="View My User Page">
	    <input type="hidden" name="token" value="'.$_SESSION['token'].'" />
	</form>
	
	<form class="inline" action="logout.php">
	    <input type="submit" name="logout" value="Log Out">
	    <input type="hidden" name="token" value="'.$_SESSION['token'].'" />
	</form><br><br>
	
	<div>
	    <strong>Post a new story:</strong>
	    <form method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
		<input type="text" style="width:500px" name="title" id="title" placeholder="Title" />
		<p>
		    <input type="url" style="width:450px" name="url" id="url" placeholder="URL" />
		    <input type="submit" name="post" value="Post" float="left" />
		</p>
		<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
		<input type="hidden" name="uid" value="<?php echo $_SESSION['uid']; ?>" />
		<p>
		    <textarea style="width:500px; height:100px;" name="desc" id="desc" placeholder="Description"></textarea>
		</p>
	    </form>
	</div>
    
	<?php
	// Add new post
	if(isset($_POST['title']) && isset($_POST['url']) && isset($_POST['desc'])) {
	    
	     if(empty($_POST['title'])) {
		printf(htmlentities("Please enter a title."));
		exit;
	    }
	    
	    if(strlen($_POST['title'])>255) {
		printf(htmlentities("Titles can be no longer than 255 characters."));
		exit;
	    }
	    
	    $uidtmp = $_SESSION['uid'];
	    $title = $_POST['title'];
	    $url = $_POST['url'];
	    $desc = $_POST['desc'];
	    
	    // Ready the query
	    $stmt = $mysqli->prepare("INSERT INTO stories (uid, title, url, description) values (?, ?, ?, ?)");      
	    if(!$stmt){
		printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
		exit;
	    }
	    
	    // Send the query
	    $stmt->bind_param('dsss', $uidtmp, $title, $url, $desc);
	    $stmt->execute();
	    $stmt->close();
	    
	    printf(htmlentities("Success!"));
	}
    }
    ?>
    
    <strong>The 20 Most Recent Stories</strong>
    
    <?php
    
    // Get stories
    $stmt1 = $mysqli->prepare("SELECT * FROM stories ORDER BY id DESC");      
    
    if(!$stmt1){
	printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
	exit;
    } else {
    
	// Send the query
	$stmt1->execute();
	
	// Get  the results of the query
	$stmt1->bind_result($sid, $uid, $title, $url, $desc);
	
	$i = 1;
	while($stmt1->fetch() && $i < 20) {
	    printf("
            <div class='story'>
                <p class='story_title' >
                    %d: <a class='inline' href=%s>%s</a>", $sid, $url, $title);
            if(isset($_SESSION['token'])) {
		if($uid==$_SESSION['uid']) { ?>
		    <form class="inline" float="right" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method='POST'>
			<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
			<?php printf('<input type="hidden" name="sid" value=%d />', $sid); ?>
			<input type="submit" name="editstory" value="Edit" />
		    </form>
		    <form class="inline" float="right" action="edit.php" method='POST'>
			<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
			<?php printf('<input type="hidden" name="sid" value=%d />', $sid); ?>
			<input type="submit" name="deletestory" value="Delete" />
		    </form><br><br>
		    <?php
		    if(isset($_POST['editstory'])) {
			?>
			<form method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
			    <p>
				<textarea style="width:90%; height:10%" name="replystory" id="replystory"><?php echo $desc; ?></textarea>
				<input type="submit" name="updatestory" value="Update" float="left" />
			    </p>
			    <?php printf('<input type="hidden" name="sid" value=%d />', $sid); ?>
			    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
			</form>
			
			<?php
			if(isset($_POST['updatestory'])) {
			    $newReplyStory = $_POST['replystory']." (Edited)";
			    $stmt7 = $mysqli->prepare("UPDATE stories SET description=? WHERE (id, uid) = (?, ? cr45)");      
			    if(!$stmt7){
				printf(htmlentities("Query Prep Failed: %s\n", $mysqli->error));
			    } else {
				// Send the query
				$stmt7->bind_param('sdd', $newReplyStory, $sid, $_SESSION['uid']);
				$stmt7->execute();
				$stmt7->close();
				
				printf(htmlentities("Post edited."));
				echo '<br><br>';
			    }
			}
		    }
		}
            }        
            printf("
		</p>
		<p class='story_desc'>
		    %s
		</p>
		<form action='story.php' method='POST'>
		    <input type='hidden' name='sid' value='%d' />", $desc, $sid); ?>
		    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
		    <input type='submit' value='Discuss' />
		</form>
	    </div> <?php
	    $i = $i+1;
	}
	
	$stmt1->close();
    }
    ?>
    
</body>
</html>