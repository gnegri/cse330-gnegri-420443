<?php
$mysqli = new mysqli('localhost', 'wustl_inst', 'wustl_pass', 'feelu');
 
if($mysqli->connect_errno) {
	printf(htmlentities("Connection Failed: %s\n", $mysqli->connect_error));
	exit;
}
?>