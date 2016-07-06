<?php 

// These scripts are contained in the repository templates (in a separate Git)
include('../../templates/php/utilities/remote.php');
include('../../templates/php/utilities/utilities.php');
include('../utilities/utilities.php');

// select from command line the two databases to match
$input = get_input("n:o:","u:p:");

$new_db = $input['n'];
$old_db = $input['o'];

include('../utilities/config.php');
// DBpedia basic url
$dbpedia_url = "http://dbpedia.org/resource/";
// connect to the database tabResponsabilita to extract names

// retrieve new IDs
$query = "SELECT * FROM $new_db.tabResponsabilita AS new WHERE new.IDResponsabilita > 
(SELECT MAX(IDResponsabilita) FROM $old_db.tabResponsabilita) AND new.IDResponsabilita NOT IN 
(SELECT IDResponsabilita FROM $new_db.tabDBpedia)";
$qr = mysqli_query($conn, $query);
if($qr != false && mysqli_num_rows($qr) > 0)
	while($row = mysqli_fetch_assoc($qr))
	{
		// search if the DBpedia page (name_surname) exists
		$url = $dbpedia_url.str_replace(" ", "_",$row['SecondoElemento']."_".$row['PrimoElemento']);
		$id = $row['IDResponsabilita'];
		echo "IDResponsabilita=$id\n";
		
		$result = connect_curl($url);
		$http_response = intval($result['http_info']['http_code']);
		if($http_response == 200) // the url exists
		{
			echo "Found URL $url\n";
			// if it exists store the result in a new table, called tabDBpedia (id, URL)
			mysqli_query($conn,"INSERT INTO $new_db.tabDBpedia(IDResponsabilita,DBpediaURL) VALUES('$id','$url')");
		}
	}
mysqli_close($conn);

?>