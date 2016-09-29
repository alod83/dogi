<?php

/* 	This script contains some functions to perform remote connections
	(i.e. by URL). */

// Connection through CURL
function connect_curl($sURL)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $sURL);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60); //timeout in seconds
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	
	$aReturn = array();
	$aReturn['response'] = curl_exec($ch);
	$aReturn['http_info'] = curl_getinfo($ch);
	$aReturn['correct_location'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
	if(strpos($aReturn['correct_location'], "missingurl="))
		$aReturn['correct_location'] = null;
	
	return $aReturn;
}

// Connection to a DOM document (XML or HTML)
function connect_DOM($sURL, $sXPATH = false, $sType = "XML")
{
	// rimuovo tutti i caratteri che danno problemi
	strip_tags(preg_replace("/&/", "",$sURL));
				
	$oDOM = new DOMDocument();
	if($sType == "HTML")
		@$oDOM->loadHTML($sURL);
	else
		@$oDOM->load($sURL);
	if(!$sXPATH)
		return $oDOM;
	$oDomXPATH = new DOMXPath($oDOM);
	$oNodeList = $oDomXPATH->query( $sXPATH );
	return $oNodeList;
}

// Run a XPATH query
function XPATH($oDOM, $sXPATH)
{
	$oDomXPATH = new DOMXPath($oDOM);
	$oNodeList = @$oDomXPATH->query( $sXPATH );
	return $oNodeList;
}

// This function gets nodes values from a node list where each node contains a text
// The variable $ev contains an array of values to be excluded from the result
function get_nv_from_nl($nodes_list, $ev = false)
{
	// array of nodes values
	$nv = array();
	foreach($nodes_list as $node)
	{
		$property = trim(addslashes($node->nodeValue));
		if($ev)
		{
			if(!in_array($property,$ev))
				$nv[] = $property;
		}
		else 
			$nv[] = $property;
			
	}
	return $nv;
}

// This function a specific attribute value, given an attribute name
function get_attrv_from_nl($nodes_list, $attrn)
{
	// array of attribute values
	$attrv = array();
	foreach($nodes_list as $node)
	{
		foreach($node->attributes as $attr)
		{
			if(strcmp($attr->name, $attrn) == 0)
				$attrv [] = $attr->value;
		}
	}
	return $attrv;
}
?>