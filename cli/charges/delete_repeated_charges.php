<?php

	//----------------------------------------------------------------------------//
	// removed duplicated recurring charges
	//----------------------------------------------------------------------------//
	
	// load framework
	$strFrameworkDir = "../framework/";
	require_once($strFrameworkDir."framework.php");
	require_once($strFrameworkDir."functions.php");
	require_once($strFrameworkDir."definitions.php");
	require_once($strFrameworkDir."config.php");
	require_once($strFrameworkDir."db_access.php");
	require_once($strFrameworkDir."report.php");
	require_once($strFrameworkDir."error.php");
	require_once($strFrameworkDir."exception_vixen.php");
	
	// create framework instance
	$GLOBALS['fwkFramework'] = new Framework();
	$framework = $GLOBALS['fwkFramework'];
	
	// Init Statements/Queries
	$selDuplicateCharges	= new StatementSelect(	"Charge",
													"*",
													"ChargedOn = '2007-02-02' AND Nature = 'DR' AND ChargeType = ''");
													
	$arrColumns = Array();
	$arrColumns['TotalCharged']		= new MySQLFunction("TotalCharged - <Amount>");
	$arrColumns['TotalRecursions']	= new MySQLFunction("TotalRecursions - 1");
	$updRecurringCharge				= new StatementUpdate(	"RecurringCharge",
															"Account = <Account> AND " .
															"CreatedOn = <CreatedOn> AND " .
															"Description = <Description> AND " .
															"Nature = <Nature> AND " .
															"ChargeType = <ChargeType>",
															$arrColumns);
															
	$qryDelete	= new Query();
	
	// Get the duplicate charges
	
	
	echo "\n".str_pad("Retrieving Charges to Remove...", 70, " ", STR_PAD_RIGHT);
	ob_flush();
											
	if ($selDuplicateCharges->Execute() === FALSE)
	{
		echo "[ FAILED ]\n\n".$selDuplicateCharges->Error()."\n\n";
		die;		
	}
	echo "[   OK   ]\n";
	
	$arrCharges = $selDuplicateCharges->FetchAll();
	
	foreach ($arrCharges as $arrCharge)
	{
		echo "Correcting Charge #".$arrCharge['Id']."...";
		
		echo str_pad("\t+ Updating RecurringCharge...", 70, " ", STR_PAD_RIGHT);
		
		$arrColumns = Array();
		$arrColumns['TotalCharged']		= new MySQLFunction("TotalCharged - <Amount>", Array('Amount' => $arrCharge['Amount']));
		$arrColumns['TotalRecursions']	= new MySQLFunction("TotalRecursions - 1");
		if ($updRecurringCharge->Execute($arrColumns, $arrCharge) === FALSE)
		{
			echo "[ FAILED ]\n\n".$updRecurringCharge->Error()."\n\n";
			die;
		}
		echo "[   OK   ]\n";
		
		echo str_pad("\t+ Removing Charge...", 70, " ", STR_PAD_RIGHT);
		
		if ($qryDelete->Execute("DELETE FROM Charge WHERE Id = ".$arrCharge['Id']) === FALSE)
		{
			echo "[ FAILED ]\n\n".$qryDelete->Error()."\n\n";
			die;
		}
		echo "[   OK   ]\n";
		
		ob_flush();
	}
	
	echo "\nCOMPLETE!\n\n";
	die;
?>