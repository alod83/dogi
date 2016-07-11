<?php

/*
 * This script is used by all the linkers to read from command line and connect to mysql db
 */
require_once('../../templates/php/utilities/utilities.php');

// select from command line the two databases to match
$input = get_input("n:o:","u:p:");

$new_db = $input['n'];
$old_db = $input['o'];

include('../utilities/config.php');
?>