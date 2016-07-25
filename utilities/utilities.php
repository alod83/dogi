<?php 

/*
 * Utilities for all dogi scripts
 */

/* print help menu
function print_help()
{
	echo "-n new database\n";
	echo "-o old database\n";
}*/

/* build a query to extract all the new entries to be processed
 * exspect the table name as parameter
 */
function build_query($old_db,$new_db,$t)
{
	return "SELECT * FROM $new_db.tabResponsabilita AS new WHERE new.IDResponsabilita >
	(SELECT MAX(IDResponsabilita) FROM $old_db.tabResponsabilita) AND new.IDResponsabilita NOT IN
	(SELECT IDResponsabilita FROM $new_db.$t)";
}

?>