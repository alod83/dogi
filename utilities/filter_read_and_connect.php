<?php

/*
 * This script is used by all the linkers to read from command line and connect to mysql db
 */
require_once('../utilities/utilities.php');

function print_help()
{
	echo "USAGE\n";
	echo "-n old_db\n";
	echo "-u user\n";
	echo "-p password\n";
}

// select from command line the database where to operate
$input = get_input("o:","u:p:");

$old_db = $input['o'];

include('../utilities/config.php');
?>