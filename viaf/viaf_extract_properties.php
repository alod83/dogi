<?php

include('../../templates/php/utilities/utilities.php');
include('../../templates/php/utilities/remote.php');


$properties = array(
	"name",
	"birthDate",
	"deathDate",
	"sameAs/rdf:Description[@rdf:about]",
	"alternateName",
	"givenName",
	"familyName",
	"description"
);
	
include('../utilities/filter_read_and_connect.php');

$q = "SELECT * FROM $new_db.tabVIAF WHERE checkProperties = '0' AND IDViaf NOT IN (SELECT IDViaf FROM $new_db.tabSameAsVIAF)";

echo $q."\n";
$arg = array('conn' => $conn, 'new_db'=> $new_db,'url' => 'http://viaf.org/viaf/', 'properties' => $properties);
mysqlquery($conn,$q,$arg, function ($aRow, $arg)
{
	$sIDViaf = $aRow['IDViaf'];
	$search_string = $arg['url'].$sIDViaf."/rdf.xml";
	$conn = $arg['conn'];
	$new_db = $arg['new_db'];
	echo $search_string."\n";
	$oDOM = connect_DOM($search_string, false, "XML" );
			
	foreach($arg['properties'] as $sProperty)
	{
		$oNodeList = XPATH($oDOM, "//schema:".$sProperty);
		foreach($oNodeList as $cNode)
		{
			if(strcmp($sProperty,"sameAs/rdf:Description[@rdf:about]")==0)
			{
				foreach ( $cNode->attributes as $oAttribute )
				{
					$sValue = $oAttribute->value;
					mysqli_query($conn,"INSERT INTO $new_db.tabSameAsVIAF(IDViaf,SameAs) VALUES('$sIDViaf', '$sValue')");
				}
			}
			else
			{
				$sValue = $cNode->nodeValue;
				if(!empty($sValue))
				{
					switch($sProperty)
					{
						case 'name': 
						case 'alternateName':
						case 'givenName':
						case 'familyName':
							mysqli_query($conn, "INSERT INTO $new_db.tabVariantiVIAF(IDViaf,NomeAlternativo) VALUES('$sIDViaf', '$sValue')");
							break;
						default:
							mysqli_query($conn,"UPDATE $new_db.tabVIAF SET $sProperty = '$sValue' WHERE IDViaf = '$sIDViaf'");
							break;
					}	
				}
			}
		}
	}	 
	mysqli_query($conn,"UPDATE $new_db.tabVIAF SET checkProperties = '1' WHERE IDViaf = '$sIDViaf'");
});
	
?>