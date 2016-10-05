<?php 

// These scripts are contained in the repository templates (in a separate Git)
include('../utilities/remote.php');
include('../utilities/utilities.php');
include('../utilities/extract_read_and_connect.php');

// DBpedia basic url
$dbpedia_url = "http://dbpedia.org/resource/";
// connect to the database tabResponsabilita to extract names

// retrieve new IDs
$query = "SELECT * FROM $new_db.tabResponsabilita AS new WHERE new.IDResponsabilita > 
(SELECT MAX(IDResponsabilita) FROM $old_db.tabResponsabilita) AND new.IDResponsabilita NOT IN 
(SELECT IDResponsabilita FROM $new_db.DBpedia_autori)";
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
			// if it exists store the result in a new table, called DBpedia_autori (id, URL)
			mysqli_query($conn,"INSERT INTO $old_db.DBpedia_autori(IDResponsabilita,DBpediaURL) VALUES('$id','$url')");
		}
	}
mysqli_close($conn);

?>