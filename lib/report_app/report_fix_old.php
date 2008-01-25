<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Changes the old Data Reports to match the new format
//----------------------------------------------------------------------------//

// load application
require_once('require.php');

// Statements
$selReports = new StatementSelect("DataReport", "*");
$ubiReport	= new StatementUpdateById("DataReport", Array('SQLSelect' => NULL));

echo "\n[ UPDATING DATA REPORTS ]\n\n";

// Get the Reports
$selReports->Execute();
while ($arrReport = $selReports->Fetch())
{
	echo " + {$arrReport['Name']}...\t\t\t";
	ob_flush();
	
	// Is this an old Report?
	$mixSQLSelect = unserialize($arrReport['SQLSelect']);
	if (is_array($mixSQLSelect[0]))
	{
		// This is a new Report -> Skip
		echo "[  SKIP  ]\n";
		continue;
	}
	
	// Fix SQLSelect
	$arrSQLSelect = Array();
	foreach ($mixSQLSelect as $strAlias=>$strValue)
	{
		if (!is_array($strValue))
		{
			$arrSQLSelect[$strAlias]['Value']	= $strValue;
		}
	}
	
	// Save
	$arrReport['SQLSelect']	= serialize($arrSQLSelect);
	$ubiReport->Execute($arrReport);
	echo "[   OK   ]\n";
}

echo "\nDone.\n\n";

?>