<?php

require_once("../../flex.require.php");

$selRequests	= new StatementSelect("Service JOIN Request ON Service.Id = Request.Service", "Request.*", "FNN = <FNN>");

$arrIgnoreList	= Array();
$ptrFile		= fopen('/home/richdavis/Desktop/UnitelVTIgnore.csv', 'r');
while (!feof($ptrFile))
{
	if ($intFNN	= (int)fgets($ptrFile))
	{
		$arrIgnoreList[]	= "0".$intFNN;
		//CliEcho("\t + 0".$intFNN);
	}
}

foreach ($arrIgnoreList as $strFNN)
{
	CliEcho("\n * Service #$strFNN...");
	
	$selRequests->Execute(Array('FNN' => $strFNN));
	while ($arrRequest = $selRequests->Fetch())
	{
		$strOutput	=	GetConstantDescription($arrRequest['RequestType'], 'provisioning_type');
		$strOutput	.=	" : " . $arrRequest['RequestDateTime'];
		$strOutput	.=	" : " . GetConstantDescription($arrRequest['Status'], 'provisioning_request_status');
		CliEcho("\t + $strOutput");
	}
}

?>