<?php 

require_once('../utilities/utilities.php');

// MySQL configuration
$user = "root";
$password = "";
if(isset($input['u']))
	$user = $input['u'];
if(isset($input['p']))
	$password = $input['p'];
$conn = mysqlconnect($user, $password);

// Database dogi support
$support_db = "dogi_support"
?>