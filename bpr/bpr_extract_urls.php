<?php

// read from input and connect to database
include('../utilities/extract_read_and_connect.php');
include('../../templates/php/utilities/stopwords_it.php');


	 
 function fSearch($conn, $new_db,$sSearchString, $sIDRivista, $bMatch = false)
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
	mysqli_query($conn, "INSERT INTO $new_db.tabBPR(IDRivista,uri,title,matchesatto) VALUES('$sIDRivista', '$sBPR','$sBPRTitle','$bMatch')");
	return true;
 }

 $q = "SELECT * FROM $new_db.tabRiviste WHERE IDRivista NOT IN (SELECT IDRivista FROM $old_db.tabBPR)
  AND IDRivista NOT IN (SELECT IDRivista FROM $old_db.tabRiviste)";
 $arg = array('conn' => $conn, 'new_db'=> $new_db, 'stopwords_it' => $stopwords_it);
 mysqlquery($conn,$q,$arg, function ($aRow, $arg)
 {
 	$conn = $arg['conn'];
 	$new_db = $arg['new_db'];
 	$stopwords_it = $arg['stopwords_it'];
	
 	$sTitle = $aRow['Titolo'];
		
	$sSearchString = urlencode("select  ?url ?title where {?url dc:title \"$sTitle\"; dc:type \"periodico\"; dc:title ?title .}");
	$sIDRivista = $aRow['IDRivista'];
	if(!fSearch($conn,$new_db,$sSearchString,$sIDRivista, true))
	{
		// cerco il match non esatto
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
		fSearch($conn,$new_db,$sSearchString,$sIDRivista);
		
	}
			//mysqli_query($conn, "UPDATE tabRiviste SET BPR= 1 WHERE IDRivista = '$iIDRivista'");
});

mysqli_close($conn);
	
?>