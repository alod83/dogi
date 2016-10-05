<?php

/*
 * This script searches all the additional properties related to a person on VIAF.
 * It assumes that all the VIAF IDs are already available.
 */
include('../utilities/utilities.php');
include('../utilities/remote.php');

// array of properties to be searched
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
	
// read from input and connect to database
include('../utilities/filter_read_and_connect.php');

$q = "SELECT * FROM $old_db.VIAF_autori WHERE checkProperties = '0' AND Filtered = 'TOBECHECKED'";

$arg = array('conn' => $conn, 'old_db'=> $old_db, 'properties' => $properties);
mysqlquery($conn,$q,$arg, function ($aRow, $arg)
{
	$sIDViaf = $aRow['IDViaf'];
	$search_string = "http://viaf.org/viaf/".$sIDViaf."/rdf.xml";
	$conn = $arg['conn'];
	$old_db = $arg['old_db'];
	echo $search_string."\n";
	$oDOM = connect_DOM($search_string, false, "XML" );
	
	foreach($arg['properties'] as $sProperty)
	{
		$oNodeList = XPATH($oDOM, "//schema:".$sProperty);
		if(!empty($oNodeList))
		{
			foreach($oNodeList as $cNode)
			{
				if(strcmp($sProperty,"sameAs/rdf:Description[@rdf:about]")==0)
				{
					foreach ( $cNode->attributes as $oAttribute )
					{
						$sValue = $oAttribute->value;
						mysqli_query($conn,"INSERT INTO $old_db.tabSameAsVIAF(IDViaf,SameAs) VALUES('$sIDViaf', '$sValue')");
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
								mysqli_query($conn, "INSERT INTO $old_db.tabVariantiVIAF(IDViaf,NomeAlternativo) VALUES('$sIDViaf', '$sValue')");
								break;
							default:
								mysqli_query($conn,"UPDATE $old_db.VIAF_autori SET $sProperty = '$sValue' WHERE IDViaf = '$sIDViaf'");
								break;
						}	
					}
				}
			}
		}	 
	}
	mysqli_query($conn,"UPDATE $old_db.VIAF_autori SET checkProperties = '1' WHERE IDViaf = '$sIDViaf'");
});

mysqli_close($conn);

?>