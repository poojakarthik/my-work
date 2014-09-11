<?php

// Load Framework
require_once("../../flex.require.php");

// Get list of tables from the Database
$qryShowTables	= new Query();
$qryShowColumns	= new Query();
$qryAlterTable	= new Query();
if ($mixTables = $qryShowTables->Execute("SHOW FULL TABLES WHERE Table_type != 'VIEW'"))
{
	while ($arrTable = $mixTables->fetch_array())
	{
		// Check the fields in this table
		$strTable	= $arrTable[0];
		
		CliEcho(str_pad(" + $strTable...", 40, ' ', STR_PAD_RIGHT), FALSE);
		
		if ($qryShowColumns->Execute("SHOW COLUMNS FROM $strTable WHERE Field = 'InvoiceRun'")->num_rows)
		{			
			// Modify this field
			if ($qryAlterTable->Execute("ALTER TABLE $strTable MODIFY InvoiceRun VARCHAR(32)"))
			{
				CliEcho("[   OK   ]");
			}
			else
			{
				CliEcho("[ FAILED ]");
				CliEcho("\t -- ".$qryAlterTable->Error());
			}
		}
		else
		{
			CliEcho("[  SKIP  ]");
		}
	}
}





?>