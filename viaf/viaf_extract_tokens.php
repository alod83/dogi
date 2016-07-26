<?php
// read from input and connect to database
include('../utilities/filter_read_and_connect.php');

$q = "SELECT opere.IDViaf, Titolo FROM $new_db.tabOpereVIAF AS opere, $new_db.tabVIAF AS viaf WHERE opere.IDViaf = viaf.IDViaf AND viaf.Filtered = 'TOBECHECKED'";

$arg = array('conn' => $conn, 'new_db'=> $new_db);
mysqlquery($conn,$q,$arg, function ($aRow, $arg)
{
	$conn = $arg['conn'];
	$new_db = $arg['new_db'];
		
	$sIDViaf = $aRow['IDViaf'];
	$aTokens = extract_tokens($aRow['Titolo']);
	foreach($aTokens as $sToken)
	{
		// check whether the token is already in the table
		$oInternalResult = mysqli_query($conn,"SELECT valore, idToken FROM $new_db.tabParoleVIAF WHERE valore = '$sToken'");
		if($oInternalResult !== false && ($aInternalRow = mysqli_fetch_row($oInternalResult)))
		{
			$iIdToken = intval($aInternalRow[1]);
			mysqli_query($conn,"INSERT INTO $new_db.legParoleVIAF(idViaf,idToken) VALUES('$sIDViaf', '$iIdToken')");
		}
		else
		{						
			mysqli_query($conn,"INSERT INTO $new_db.tabParoleVIAF(valore) VALUES('$sToken')");
			$iIdToken = mysqli_insert_id($conn);
			mysqli_query($conn,"INSERT INTO $new_db.legParoleVIAF(idViaf,idToken) VALUES('$sIDViaf', '$iIdToken')");
		}
	}
});

mysqli_close($conn);

?>