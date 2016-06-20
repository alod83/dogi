<?php 

// These scripts are contained in the repository templates (in a separate Git)
include('../../templates/php/utilities/remote.php');
include('../../templates/php/utilities/utilities.php');

// DBpedia basic url
$dbpedia_url = "http://dbpedia.org/resource/";
// connect to the database tabResponsabilita to extract names
$conn = mysqlconnect("root", NULL, "DoGi");
$qr = mysqli_query($conn, "SELECT * FROM tabResponsabilita WHERE checkDBpedia = 0");
if($qr != false && mysqli_num_rows($qr) > 0)
	while($row = mysqli_fetch_assoc($qr))
	{
		// search if the DBpedia page (name_surname) exists
		$url = $dbpedia_url.str_replace(" ", "_",$row['SecondoElemento']."_".$row['PrimoElemento']);
		$id = $row['IDResponsabilita'];
			
		$result = connect_curl($url);
		$http_response = intval($result['http_info']['http_code']);
		if($http_response == 200) // the url exists
		{
			// if it exists store the result in a new table, called tabDBpedia (id, URL)
			mysqli_query($conn,"INSERT INTO tabDBpedia(IDResponsabilita,DBpediaURL) VALUES('$id','$url')");
		}
		// update the field checkDBPedia
		mysqli_query($conn,"UPDATE tabResponsabilita SET checkDBpedia = '1' WHERE IDResponsabilita = '$id'");
	}


?>