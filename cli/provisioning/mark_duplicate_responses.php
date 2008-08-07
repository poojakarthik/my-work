<?php

// Framework
require_once("../../flex.require.php");

$arrUpdate				= Array();
$arrUpdate['Status']	= RESPONSE_STATUS_DUPLICATE;
$ubiResponse			= new StatementUpdateById("ProvisioningResponse", $arrUpdate);
$selResponses			= new StatementSelect("ProvisioningResponse", "*", "1");
$selDuplicate			= new StatementSelect(	"ProvisioningResponse",
												"Id",
												"Id < <Id> AND Service = <Service> AND FNN = <FNN> AND Carrier = <Carrier> AND Type = <Type> AND Description = <Description> AND EffectiveDate = <EffectiveDate> AND Status = 402",
												NULL,
												1);

CliEcho("\n[ MARKING DUPLICATED RESPONSES ]\n");

// Get all Responses
$intUpdated		= 0;
$intCount		= 0;
$intTimeStart	= time();
if (($intTotal = $selResponses->Execute()) !== FALSE)
{
	while ($arrResponse = $selResponses->Fetch())
	{
		$intCount++;
		$intSplit	= time() - $intTimeStart;
		CliEcho(" + ($intCount/$intTotal @ $intSplit)#{$arrResponse['Id']}...", FALSE);
		
		// Is this a Duplicate?
		$mixResponse	= $selDuplicate->Execute($arrResponse);
		if ($mixResponse)
		{
			// Yes, it's a Duplicate
			$arrDuplicate	= $selDuplicate->Fetch();
			CliEcho(" is a Duplicate of #{$arrDuplicate['Id']}! Updating Response...\t", FALSE);
			
			// Update the Response
			$arrResponse['Status']	= RESPONSE_STATUS_DUPLICATE;
			if ($ubiResponse->Execute($arrResponse) === FALSE)
			{
				CliEcho("[ FAILED ]\n\t -- ", FALSE);
				CliEcho("ERROR: DB error with \$ubiResponse: ".$ubiResponse->Error());
				exit(3);
			}
			else
			{
				CliEcho("[   OK   ]");
				$intUpdated++;
			}
		}
		elseif ($mixResponse === FALSE)
		{
			CliEcho("ERROR: DB error with \$selDuplicate: ".$selDuplicate->Error());
			exit(2);
		}
		else
		{
			CliEcho();
		}
	}
}
else
{
	CliEcho("ERROR: DB error with \$selResponses: ".$selResponses->Error());
	exit(1);
}

CliEcho("\nUpdated $intUpdated of $intTotal Responses.\n");
exit(0);
?>