<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// include XLS generator
	require_once('Spreadsheet/Excel/Writer.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_ADMIN;
	$arrPage['Modules']		= MODULE_ALL;
	
	// call application
	require ('config/application.php');
	
	// Get the Requested Report
	try
	{
		$rptReport = $Style->attachObject (new DataReport (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/datareport/notfound.xsl');
		exit;
	}
	
	if ($_POST ['Confirm'])
	{
		$selResult = $rptReport->Execute ($_POST ['select'], $_POST ['input'], $_POST ['limit']);
		
		header('Content-type: application/x-msexcel');
		header('Content-Disposition: attachment; filename="' . $rptReport->Pull ('Name')->getValue () . ' - ' . date ("Y-m-d h-i-s A") . '.xls"');
		

		// Generate Excel 5 Workbook
		$wkbWorkbook = new Spreadsheet_Excel_Writer();
		$wkbWorkbook->send($rptReport->Pull ('Name')->getValue () . " - " . date ("Y-m-d h-i-s A") . ".xls");
		$wksWorksheet =& $wkbWorkbook->addWorksheet();
		
		// Set up formatting styles
		$fmtTitle =& $wkbWorkbook->addFormat();
		$fmtTitle->setBold();
		$fmtTitle->setFgColor(48);
		$fmtTitle->setBorder(1);
		
		// Add in the title row
		$mdtMetaData = $selResult->MetaData();
		$arrTitles = $mdtMetaData->fetch_fields();
		foreach ($arrTitles as $intKey=>$strTitle)
		{
			$wksWorksheet->write(0, $intKey, $strTitle, $fmtTitle);
		}
		Debug($arrData);
		// Add in remaining rows
		$arrData = $selResult->FetchAll();
		foreach ($arrData as $intRow=>$arrRow)
		{
			$intCol = 0;
			foreach ($arrRow as $objField)
			{
				$wksWorksheet->write($intRow, $intCol, (string)$mixField);
				$intCol++;
			}
		}
		
		// TODO: Add totals, if specified
		// use $wksWorksheet->writeFormula

		// Send the XLS file
		$wkbWorkbook->close();
		exit;
	}
	
	$Style->attachObject ($rptReport->Selects ());
	$Style->attachObject ($rptReport->Inputs ());
	
	// In terms of Documentation, we want to show the 
	// Report documentation, along with any documentation
	// that is associated with the Report we are running
	$docDocumentation->Explain ('Report');
	
	// Explain the Fundamentals for the Report
	$arrDocumentation = $rptReport->Documentation ();
	foreach ($arrDocumentation as $strDocumentation)
	{
		$docDocumentation->Explain ($strDocumentation);
	}
	
	$Style->Output ('xsl/content/datareport/run_input.xsl');
	
?>
