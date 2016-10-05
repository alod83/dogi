<?php
// read from input and connect to database
include('../utilities/filter_read_and_connect.php');

$q = "SELECT opere.IDViaf, Titolo FROM $old_db.tabOpereVIAF AS opere, $old_db.VIAF_autori AS viaf WHERE opere.IDViaf = viaf.IDViaf AND viaf.Filtered = 'TOBECHECKED'";

$arg = array('conn' => $conn, 'old_db'=> $old_db);
mysqlquery($conn,$q,$arg, function ($aRow, $arg)
{
	$conn = $arg['conn'];
	$old_db = $arg['old_db'];
		
	$sIDViaf = $aRow['IDViaf'];
	$aTokens = extract_tokens($aRow['Titolo']);
	foreach($aTokens as $sToken)
	{
		// check whether the token is already in the table
		$oInternalResult = mysqli_query($conn,"SELECT valore, idToken FROM $old_db.tabParoleVIAF WHERE valore = '$sToken'");
		if($oInternalResult !== false && ($aInternalRow = mysqli_fetch_row($oInternalResult)))
		{
			$iIdToken = intval($aInternalRow[1]);
			mysqli_query($conn,"INSERT INTO $old_db.legParoleVIAF(idViaf,idToken) VALUES('$sIDViaf', '$iIdToken')");
		}
		else
		{						
			mysqli_query($conn,"INSERT INTO $old_db.tabParoleVIAF(valore) VALUES('$sToken')");
			$iIdToken = mysqli_insert_id($conn);
			mysqli_query($conn,"INSERT INTO $old_db.legParoleVIAF(idViaf,idToken) VALUES('$sIDViaf', '$iIdToken')");
		}
	}
});

mysqli_close($conn);

?>