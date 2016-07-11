<?php

/*
 * This script is used by all the linkers to read from command line and connect to mysql db
 */
require_once('../../templates/php/utilities/utilities.php');

function print_help()
{
	echo "USAGE\n";
	echo "-n new database\n";
	echo "-u user\n";
	echo "-p password\n";
}

// select from command line the two databases to match
$input = get_input("n:","u:p:");

$new_db = $input['n'];

include('../utilities/config.php');
?>