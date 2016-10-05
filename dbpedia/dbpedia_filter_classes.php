<?php

/* This script limits DBPedia people to those belonging to classes related
 * to the juridical field.
 */

include('../utilities/utilities.php');


$classes = array(
	"yago:Lawyer110249950",
	"yago:Scholar110557854",
	"yago:Politician110451263",
	"yago:HarvardLawSchoolAlumni",
	"yago:AmericanPoliticalTheorists",
	"yago:LawClerksOfTheSupremeCourtOfTheUnitedStates",
	"yago:YaleLawSchoolAlumni",
	"yago:Alumnus109786338",
	"yago:Professional110480253",
	"dbo:Scientist",
	"yago:Philosopher110423589",
	"yago:Theorist110706812"
);

$sparql_endpoint = "http://dbpedia.org/sparql?query=";

include('../utilities/filter_read_and_connect.php');

// connect to the db to take the urls to be checked
$qr = mysqli_query($conn, "SELECT * FROM $support_db.DBpedia_autori WHERE Filtered = 'TOBECHECKED'");
if($qr != false && mysqli_num_rows($qr) > 0)
	while($row = mysqli_fetch_assoc($qr))
	{
		$dbpedia_url = $row['DBpediaURL'];
		echo $dbpedia_url."\n";
		// check whether the person belongs to the given classes
		$query = "ask where {<".$dbpedia_url."> a ?type . FILTER(?type IN (";
		
		foreach($classes as $class)
			$query .= $class.",";
		// remove the final comma and code the url
		$query = urlencode(substr($query, 0, strlen($query)-1)."))}");
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $sparql_endpoint.$query);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		
		$filtered = "NO";
		if($response == "true")
			$filtered = "YES"; 
		mysqli_query($conn, "UPDATE $support_db.DBpedia_autori SET Filtered='$filtered' WHERE DBpediaURL='$dbpedia_url'");
	}
mysqli_close($conn);

?>