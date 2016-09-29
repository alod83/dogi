<?php
	
// These scripts are contained in the repository templates (in a separate Git)
include('../utilities/remote.php');
include('../utilities/utilities.php');

/*
 * Include file to read from command line and connect to database
 */
include('../utilities/extract_read_and_connect.php');

$basic_url= "http://viaf.org/viaf/";
$search_url = $basic_url."search?query=local.names=\"";
	
$q = build_query($old_db,$new_db,"tabVIAF");
$arg = array('conn' => $conn, 'new_db'=> $new_db, 'su' => $search_url);

mysqlquery($conn,$q,$arg, function ($aRow, $arg)
{
	$search_url = $arg['su'];
	$conn = $arg['conn'];
	$new_db = $arg['new_db'];
	$sSearchString = urlencode(trim($aRow['PrimoElemento'])." ".trim($aRow['SecondoElemento']))."\"";
	echo $search_url.$sSearchString."\n";
	$oCh = curl_init();
	curl_setopt($oCh, CURLOPT_URL, $search_url.$sSearchString);
	curl_setopt($oCh, CURLOPT_RETURNTRANSFER, 1);
	$sResponse = curl_exec($oCh);
		
	if(!is_null($sResponse))
	{
		$oDOM = connect_DOM($sResponse, false, "HTML" );
		if(!$oDOM)
			echo "Impossibile connettersi al DOM\n";
		$oNodeList = XPATH($oDOM, "//td[@class='recName']/a");
		$iIDResponsabilita = intval($aRow['IDResponsabilita']);
			
		foreach($oNodeList as $cNode)
		{
			// take the first element
			foreach ( $cNode->attributes as $oAttribute )
			{
				$sViafId = substr($oAttribute->value, strlen("/viaf/"), strpos($oAttribute->value, "#")-strlen('/viaf/')-1);
				echo $sViafId."\n";
				mysqli_query($conn, "INSERT INTO $new_db.tabVIAF(IDResponsabilita,IDViaf) VALUES('$iIDResponsabilita', '$sViafId')");
			}
			break;
		}
	}
});
		
mysqli_close($conn);
	
		
?>