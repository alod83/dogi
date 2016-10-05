<?php

/*
 * This script is used by all the linkers to read from command line and connect to mysql db
 */
require_once('../utilities/utilities.php');

function print_help()
{
	echo "USAGE\n";
	echo "-u user\n";
	echo "-p password\n";
}

// select from command line the database where to operate
$input = get_input("u:p:");

include('../utilities/config.php');
?>