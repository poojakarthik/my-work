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
	$arrPage['Permission']	= Array(PERMISSION_ADMIN, PERMISSION_ACCOUNTS);
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
		// Is this an email report?
		if ($rptReport->Pull('RenderMode')->getValue() == REPORT_RENDER_EMAIL)
		{
			// Add to DataReportSchedule table
			$arrInsertData = Array();
			$arrInsertData['DataReport']	= $rptReport->Pull('Id')->getValue();
			$arrInsertData['Employee']		= $athAuthentication->AuthenticatedEmployee()->Pull('Id')->getValue();
			$arrInsertData['CreatedOn']		= new MySQLFunction("NOW()");
			$arrInsertData['SQLSelect']		= serialize($_POST['select']);
			$arrInsertData['SQLWhere']		= serialize($rptReport->ConvertInput($_POST['input']));
			$arrInsertData['RenderTarget']	= ($_POST['outputcsv']) ? REPORT_TARGET_CSV : REPORT_TARGET_XLS;
			$arrInsertData['Status']		= REPORT_WAITING;
			Debug($arrInsertData);
			
			//$insDataReportSchedule = new StatementInsert("DataReportSchedule", $arrInsertData);
			//$insDataReportSchedule->Execute($arrInsertData);
			
			// TODO: Some form of confirmation?
			Debug("Emailed!");
			exit;
		}
		
		$selResult = $rptReport->Execute ($_POST ['select'], $_POST ['input'], $_POST ['limit']);
		
		// Should be be outputting in CSV instead of XLS?
		if ($_POST['outputcsv'])
		{
	        // Yes
	        header('Content-type: text/csv');
	        header('Content-Disposition: attachment; filename="' . $rptReport->Pull ('Name')->getValue () . ' - ' . date ("Y-m-d h-i-s A") . '.csv"');
	        
	        echo CSVStatementSelect ($selResult);
	        exit;
		}
		
		// No, export in XLS :)		
		header('Content-type: application/x-msexcel');
		header('Content-Disposition: attachment; filename="' . $rptReport->Pull ('Name')->getValue () . ' - ' . date ("Y-m-d h-i-s A") . '.xls"');

		// Generate Excel 5 Workbook
		$wkbWorkbook = new Spreadsheet_Excel_Writer();
		$wkbWorkbook->send($rptReport->Pull ('Name')->getValue () . " - " . date ("Y-m-d h-i-s A") . ".xls");
		$wksWorksheet =& $wkbWorkbook->addWorksheet();
		
		// Set up formatting styles
		$fmtTitle =& $wkbWorkbook->addFormat();
		$fmtTitle->setBold();
		$fmtTitle->setFgColor(22);
		$fmtTitle->setBorder(1);
		
		// Currency format
		$fmtCurrency =& $wkbWorkbook->addFormat();
		$fmtCurrency->setNumFormat('$#,##0.00;-$#,##0.00');
		
		// Integer format (make sure it doesn't show exponentials for large ints)
		$fmtInteger =& $wkbWorkbook->addFormat();
		$fmtInteger->setNumFormat('00');
		
		// Add in the title row
		$mdtMetaData = $selResult->MetaData();
		$arrTitles = $mdtMetaData->fetch_fields();
		foreach ($arrTitles as $intKey=>$objTitle)
		{
			$wksWorksheet->write(0, $intKey, $objTitle->name, $fmtTitle);
		}

		// Add in remaining rows
		$arrData = $selResult->FetchAll();
		foreach ($arrData as $intRow=>$arrRow)
		{
			$intCol = 0;
			foreach ($arrRow as $mixField)
			{
				if (preg_match('/^\d+\.\d+$/misU', $mixField))
				{
					// Currency/float
					$wksWorksheet->write($intRow+1, $intCol, $mixField, $fmtCurrency);
				}
				elseif (is_int($mixField))
				{
					// Integer
					$wksWorksheet->write($intRow+1, $intCol, (int)$mixField, $fmtInteger);
				}
				else
				{
					$wksWorksheet->writeString($intRow+1, $intCol, $mixField);
				}
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
