<?php

/* This script limits DBPedia people to those belonging to classes related
 * to the juridical field.
 */

// more
$aBasicClasses = array(
	"yago:Lawyer110249950",
	"yago:Scholar110557854",
	"yago:Politician110451263",
	"yago:HarvardLawSchoolAlumni",
	"yago:AmericanPoliticalTheorists",
	"yago:LawClerksOfTheSupremeCourtOfTheUnitedStates",
	"yago:YaleLawSchoolAlumni"
);

$aExtendedClasses = array(
	"yago:Alumnus109786338",
	"yago:Professional110480253",
	"dbo:Scientist",
	"yago:Philosopher110423589",
	"yago:Theorist110706812"
);

$sBasicTableField = "BasicClass";
$sExtendedTableField = "ExtendedClass";

$sCurrentTableField = $sExtendedTableField;
$aCurrentClasses = $aExtendedClasses;

// verifico che la risorsa $sResource appartenga alle classi passate come parametro in $aBasicClasses
function fCheckClasses($sResource, $aClasses)
{
	$sSPARQLEndpoint = "http://dbpedia.org/sparql?query=";
	$sQuery = "ask where {<".$sResource."> a ?type . FILTER(?type IN (";

	foreach($aClasses as $sClass)
		$sQuery .= $sClass.",";
	// rimuovo la virgola finale e codifico l'url
	$sQuery = urlencode(substr($sQuery, 0, strlen($sQuery)-1)."))}");
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $sSPARQLEndpoint.$sQuery);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = curl_exec($ch);
	
	return $response;
}

$oConn = mysqli_connect("localhost", "root", "", "DoGi");

if (!$oConn) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

$oResult = mysqli_query($oConn, "SELECT * FROM Person");
if($oResult != false && mysqli_num_rows($oResult) > 0)
	while($aRow = mysqli_fetch_assoc($oResult))
	{
		$sResource = $aRow['DBpedia'];
		$bResult = fCheckClasses($sResource, $aCurrentClasses);
		echo $sResource." ".$bResult."\n";
		mysqli_query($oConn, "UPDATE Person SET $sCurrentTableField=$bResult WHERE DBpedia='".$sResource."'");
	}
mysqli_close($oConn);

?>