<?php

require_once('../../flex.require.php');

//----------------------------------------------------------------------------//
// Attempts a partial update of the CDR table on Catwalk
//----------------------------------------------------------------------------//

Debug("[ CDR PARTIAL UPDATE ]");
CliEcho(" * Finding Latest CATWALK.vixenworking CDR...\t\t\t", FALSE);

// Find Latest CDR on catwalk.vixenworking
$selLastCDR	= new StatementSelect("CDR", "*", "1", "Id", "1");
if ($selLastCDR->Execute())
{
	CliEcho("[   OK   ]");
	$arrLastCDR	= $selLastCDR->Fetch();
}
else
{
	CliEcho("[ FAILED ]\n");
	CliEcho("Exiting: Cannot find reference CDR Id\n");
	die;
}

// Try to find this CDR on catwalk.vixen
CliEcho(" * Attempting perfect match search...\t\t\t\t", FALSE);
$selPerfect	= new StatementSelect(	"CDR",
									"Id",
									"Id = <Id> AND Service = <Service> AND FNN = <FNN> AND StartDatetime = <StartDatetime>");
if ($selPerfect->Execute($arrLastCDR))
{
	// Match found! - Copy all CDRs after this from vixen to vixenworking
	CliEcho("[   OK   ]");
	CliEcho(" * Copy CDRs from vixen to vixenworking...\t\t\t\t", FALSE);
	$strQuery		=	"INSERT INTO vixenworking.CDR " .
						"(SELECT * FROM vixen.CDR" .
						"WHERE Id > {$arrLastCDR['Id']})";
	$selCopyCDRs	= new Query();
	$mixResult		= $selCopyCDRs->Execute($strQuery);
	if ($mixResult)
	{
		CliEcho("[   OK   ]\n");
	}
	else
	{
		CliEcho("[ FAILED ]\n");
		CliEcho("Exiting: CDR Copy failed\n");
		die;
	}
}
else
{
	CliEcho("[ FAILED ]\n");
	CliEcho("Exiting: Cannot find perfect match\n");
	die;
}

CliEcho("Exiting: Script Successful!\n");
?>