 <?php

/*
 * This script searches all the works related to a person on VIAF.
 * It assumes that all the VIAF IDs are already available.
 */
include('../utilities/utilities.php');
include('../utilities/remote.php');

// read from input and connect to database
include('../utilities/filter_read_and_connect.php');

$q = "SELECT * FROM $new_db.tabVIAF WHERE checkOpere = '0' AND Filtered = 'TOBECHECKED'";
$arg = array('conn' => $conn, 'new_db'=> $new_db);
mysqlquery($conn,$q,$arg, function ($aRow, $arg)
{
	$sIDViaf = $aRow['IDViaf'];
	$new_db = $arg['new_db'];
	$conn = $arg['conn'];
	$search_string = "http://viaf.org/viaf/".$sIDViaf."/viaf.xml";
	echo $search_string."\n";
	$oDOM = connect_DOM($search_string, false, "XML" );
	
	$oNodeList = XPATH($oDOM, "//ns1:work/ns1:title");
	if($oNodeList)
	{
		foreach($oNodeList as $cNode)
		{
			$sValue = mysqli_escape_string($conn,$cNode->nodeValue);
			if(!empty($sValue))
			{
				mysqli_query($conn, "INSERT INTO $new_db.tabOpereVIAF(IDViaf,Titolo) VALUES('$sIDViaf', '$sValue')");
			}
		}
	}
	mysqli_query($conn,"UPDATE $new_db.tabVIAF SET checkOpere = '1' WHERE IDViaf = '$sIDViaf'");
	
});

mysqli_close($conn);

	
?>