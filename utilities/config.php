<?php 

require_once('../utilities/utilities.php');

$user = "root";
$password = "";
if(isset($input['u']))
	$user = $input['u'];
if(isset($input['p']))
	$password = $input['p'];
$conn = mysqlconnect($user, $password);

?>