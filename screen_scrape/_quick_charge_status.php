<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	
	// Set these variables (mktime)
	// Remember: 			mktime (	hour,	minute,	second,	month,	day,	year	)
	$intStartDatetime	=	mktime (	0,		0,		0,		2,		1,		2007	);
	$intEndDatetime		=	mktime (	23,		59,		59,		2,		28,		2007	);
	
	
	// Update all the Charges to be "Invoiced"
	$arrCharges = Array (
		"Status"		=> CHARGE_INVOICED
	);
	
	$updCharges = new StatementUpdate ('Charge', '1', $arrCharges);
	$intChanges = $updCharges->Execute (
		$arrCharges,
		Array ()
	);
	
	echo "Updated All Charges Reflecting 'Invoiced': " . $intChanges . "\n";
	
	// Set the Charges between a Particular Date to be of status "Approved"
	$arrCharges = Array (
		"Status"		=> CHARGE_APPROVED
	);
	
	$updCharges = new StatementUpdate ('Charge', 'CreatedOn BETWEEN <StartDatetime> AND <EndDatetime>', $arrCharges);
	$intChanges = $updCharges->Execute (
		$arrCharges,
		Array (
			"StartDatetime"		=> date ("Y-m-d", $intStartDatetime),
			"EndDatetime"		=> date ("Y-m-d", $intEndDatetime),
		)
	);
	
	echo "Updated All Charges Reflecting 'Approved': " . $intChanges . "\n";
	
?>
