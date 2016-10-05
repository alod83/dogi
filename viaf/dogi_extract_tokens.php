<?php

// read from input and connect to database
include('../utilities/extract_read_and_connect.php');
include('../utilities/stopwords_it.php');

$q = "SELECT Testo FROM $new_db.tabTesti AS new WHERE new.IDTesto >
	(SELECT MAX(IDTesto) FROM $old_db.tabTesti) AND Tipo = '0'";

$arg = array('conn' => $conn, 'old_db'=> $old_db, 'stopwords_it' => $stopwords_it);
mysqlquery($conn,$q,$arg, function ($aRow, $arg)
{
	$sTitle = $aRow['Testo'];	
	$conn = $arg['conn'];
	$stopwords_it = $arg['stopwords_it'];
	$old_db = $arg['old_db'];
	$aTokens = explode(" ", strtolower(str_replace("'"," ",$sTitle)));
	foreach($aTokens as $sToken)
	{
		if(!in_array($sToken, $stopwords_it))
		{
			// check whether the token is already in the table
			$oInternalResult = mysqli_query($conn,"SELECT valore, quantita FROM $old_db.tabParole WHERE valore = '$sToken'");
			if($oInternalResult !== false && ($aInternalRow = mysqli_fetch_row($oInternalResult)))
			{
				$iQuantita = intval($aInternalRow[1]) + 1;
				mysqli_query($conn,"UPDATE $old_db.tabParole SET quantita = '$iQuantita' WHERE valore = '$sToken'");
			}
			else
			{
				mysqli_query($conn,"INSERT INTO $old_db.tabParole(valore,quantita) VALUES('$sToken', '1')");
			}
		}
	}		
});

mysqli_close($conn);
?>