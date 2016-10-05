<?php

// read from input and connect to database
include('../utilities/extract_read_and_connect.php');
include('../utilities/stopwords_it.php');


	 
 function fSearch($conn, $support_db,$sSearchString, $sIDRivista, $bMatch = false)
 {
 	$sBasicURL = "http://dati.camera.it/sparql?query=";
 	$aResult = array();
 	$oCh = curl_init();
	curl_setopt($oCh, CURLOPT_URL, $sBasicURL.$sSearchString."&format=json");
	curl_setopt($oCh, CURLOPT_RETURNTRANSFER, 1);
	$aResponse = json_decode(curl_exec($oCh),true);
	if(isset($aResponse['results']) && isset($aResponse['results']['bindings']) && isset($aResponse['results']['bindings'][0]))
	{
		if(isset($aResponse['results']['bindings'][0]['title']) && isset($aResponse['results']['bindings'][0]['title']['value']))
			$aResult['title'] = trim($aResponse['results']['bindings'][0]['title']['value']);
		if(isset($aResponse['results']['bindings'][0]['url']) && isset($aResponse['results']['bindings'][0]['url']['value']))
			$aResult['url'] = trim($aResponse['results']['bindings'][0]['url']['value']);
	}
	if(empty($aResult['url']))
		return false;
	$sBPR = $aResult['url'];
	$sBPRTitle = $aResult['title'];
	
	// insert the found match in the DB
	mysqli_query($conn, "INSERT INTO $support_db.BPR_riviste(IDRivista,uri,title,matchesatto) VALUES('$sIDRivista', '$sBPR','$sBPRTitle','$bMatch')");
	return true;
 }

// select all the journals in the new db, which are not contained in the old db
$q = "SELECT * FROM $new_db.tabRiviste WHERE IDRivista NOT IN (SELECT IDRivista FROM $old_db.BPR_riviste)
  AND IDRivista NOT IN (SELECT IDRivista FROM $old_db.tabRiviste)";
$arg = array('conn' => $conn, 'support_db'=> $support_db, 'stopwords_it' => $stopwords_it);

// foreach selected journal, search if it is also in BPR
mysqlquery($conn,$q,$arg, function ($aRow, $arg)
 {
 	$conn = $arg['conn'];
 	$support_db = $arg['support_db'];
 	$stopwords_it = $arg['stopwords_it'];
	
 	$sTitle = $aRow['Titolo'];
		
	$sSearchString = urlencode("select  ?url ?title where {?url dc:title \"$sTitle\"; dc:type \"periodico\"; dc:title ?title .}");
	$sIDRivista = $aRow['IDRivista'];
	
	// search the exact match, if there is not the exact match, search for a similar match
	if(!fSearch($conn,$old_db,$sSearchString,$sIDRivista, true))
	{
		// search for the non exact match
		// remove articles
		$aTokens = explode(" ", $sTitle);
		$sNormalizedTitle = "";
		foreach($aTokens as $sToken)
		{
			if(!in_array($sToken, $stopwords_it))
			{
				$sNormalizedTitle .= $sToken." ";
			}
		}
		$sNormalizedTitle = trim($sNormalizedTitle);
		echo $sNormalizedTitle."\n";
		$sSearchString = urlencode("select  ?url ?title where {?url dc:title ?title; dc:type \"periodico\" .
		FILTER regex(str(?title),\"$sNormalizedTitle\", \"i\") }");
		fSearch($conn,$old_db,$sSearchString,$sIDRivista);
		
	}
});

mysqli_close($conn);
	
?>