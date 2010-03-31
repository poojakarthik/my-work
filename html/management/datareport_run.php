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
		$arrInsertData = Array();
		$arrInsertData['DataReport']	= $rptReport->Pull('Id')->getValue();
		$arrInsertData['Employee']		= $athAuthentication->AuthenticatedEmployee()->Pull('Id')->getValue();
		$arrInsertData['CreatedOn']		= new MySQLFunction("NOW()");
		$arrInsertData['SQLSelect']		= serialize($_POST['select']);
		$arrInsertData['SQLWhere']		= serialize($rptReport->ConvertInput($_POST['input']));
		//$arrInsertData['SQLOrder']		= serialize($rptReport->ConvertInput($_POST['order']));
		$arrInsertData['SQLLimit']		= serialize((int)$rptReport->ConvertInput($_POST['limit']));
		$arrInsertData['RenderTarget']	= ($_POST['outputcsv']) ? REPORT_TARGET_CSV : REPORT_TARGET_XLS;
		$arrInsertData['Status']		= REPORT_WAITING;
		
		// Is this an email report?
		if ($rptReport->Pull('RenderMode')->getValue() == REPORT_RENDER_EMAIL)
		{
			// Add to DataReportSchedule table
			$insDataReportSchedule = new StatementInsert("DataReportSchedule", $arrInsertData);
			$insDataReportSchedule->Execute($arrInsertData);
			
			// Tell the user that their Report has been scheduled
			$Style->Output ('xsl/content/datareport/scheduled.xsl');
			exit;
		}

		// Generate on the fly
		$selResult = $rptReport->Execute ($_POST ['select'], $_POST ['input'], $_POST ['limit']);
		if ($arrResult = $selResult->FetchAll())
		{
			// Load Report Application
			$arrConfig = LoadApplication("lib/report");
			$appReport = new ApplicationReport(Array('Display' => FALSE));
			
			// Prepare Columns
			$arrColumns	= Array();
			$arrReportData['SQLSelect'] = $rptReport->Pull('SQLSelect')->getValue();
			$arrValues	= unserialize($rptReport->Pull('SQLSelect')->getValue());
			foreach ($_POST['select'] as $strAlias)
			{
				$arrColumns["'$strAlias'"] = $arrValues[$strAlias]['Value'];
			}
			
			// Merge with StatementSelect results, because ObLib sucks dick
			$selReport = new StatementSelect("DataReport", "*", "Id = <Id>");
			$selReport->Execute(Array('Id' => $rptReport->Pull('Id')->getValue()));
			$arrReport = array_merge($arrReportData, $selReport->Fetch());
			
			// Should be be outputting in CSV instead of XLS?
			//Debug($_POST['outputcsv']);
			//die;
			if ($_POST['outputcsv'])
			{
		        // Yes
		        $arrCSV = $appReport->ExportCSV($arrResult, $arrReport, $arrInsertData, FALSE);
		        
		        header('Content-type: text/csv');
		        header('Content-Disposition: attachment; filename="' . $arrCSV['FileName'] .'"');
		        
		        echo $arrCSV['Output'];
		        exit;
			}
			else
			{
				// No, export in XLS :)		
				//header('Content-type: application/x-msexcel');
				//header('Content-Disposition: attachment; filename="' . $rptReport->Pull('Name')->getValue() . ' - ' . date("Y-m-d h-i-s A") . '.xls"');
				
				$appReport->ExportXLS($arrResult, $arrReport, $arrInsertData, FALSE);
				exit;
			}
		}
		
		// Else, No results
		$Style->attachObject(new dataBoolean('NoResults', TRUE));
	}
	
	$Style->attachObject ($rptReport->Selects ());
	$Style->attachObject ($rptReport->Inputs ());
	$Style->attachObject(new dataInteger('ForceRenderTarget', $rptReport->Pull('RenderTarget')->getValue()));
	
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
