<?php

// Framework
require_once("../../flex.require.php");

$strPath	= FILES_BASE_PATH."invoices/pdf/";

// Get list of "Year" directories
CliEcho("\n * Fixing FileName in '{$strPath}'...");
$arrYearDirs	= glob($strPath.'*', GLOB_ONLYDIR);
foreach ($arrYearDirs as $strYearDir)
{
	// Get list of "Month" directories
	$strYearPath	= rtrim($strYearDir, '/');
	$arrMonthDirs	= glob($strYearPath.'/*', GLOB_ONLYDIR);
	foreach ($arrMonthDirs as $strMonthDir)
	{
		CliEcho("\n + Directory: '{$strMonthDir}'");
		
		// Get list of PDFs for this InvoiceRun
		$strMonthPath	= rtrim($strMonthDir, '/');
		$arrPDFPaths	= glob($strMonthPath.'/*.pdf');
		foreach ($arrPDFPaths as $strPDFPath)
		{
			// Is this filename incorrect?
			if (preg_match("/\d{10}_\d{11}.pdf/", basename($strPDFPath)))
			{
				// Correctly form this PDF Filename
				$strFileName	= basename($strPDFPath, '.pdf');
				$arrFileName	= explode('_', $strFileName);
				$strNewFileName	= (int)$arrFileName[0].'_'.(int)$arrFileName[1].'.pdf';
				
				if (preg_match("/d{10}_\d{10}.pdf/", $strNewFileName))
				{
					CliEcho("\t + Renaming '{$strFileName}.pdf' to '{$strNewFileName}'...");
					rename($strMonthPath.'/'.$strFileName.'.pdf', $strMonthPath.'/'.$strNewFileName);
				}
			}
		}
	}
}

?>