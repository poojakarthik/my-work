<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_BUG;
	
	// call application
	require ('config/application.php');
	
	// Pull documentation information
	$docDocumentation->Explain ('Bugs');
	
	$objWhere = new Vixen_where();
	$objWhere->AddAnd('CreatedBy', 		$_GET['CreatedBy']);
	$objWhere->AddAnd('AssignedTo', 	$_GET['AssignedTo']);
	$objWhere->AddAnd('CreatedOn', 		Array(date('Y-m-d H-i-s', strtotime($_GET['CreatedOnStartDay'] . "/" . $_GET['CreatedOnStartMonth'] . "/" . $_GET['CreatedOnStartYear'])),
										date('Y-m-d H-i-s', strtotime(strval(intval($_GET['CreatedOnEndDay'])+1) . "/" . $_GET['CreatedOnEndMonth'] . "/" . $_GET['CreatedOnEndYear']))),
										VIXEN_WHERE_BETWEEN);
	$objWhere->AddAnd('ClosedOn', 		Array(date('Y-m-d H-i-s', strtotime($_GET['ClosedOnStartDay'] . "/" . $_GET['ClosedOnStartMonth'] . "/" . $_GET['ClosedOnStartYear'])),
										date('Y-m-d H-i-s', strtotime(strval(intval($_GET['ClosedOnEndDay'])+1) . "/" . $_GET['ClosedOnEndMonth'] . "/" . $_GET['ClosedOnEndYear']))),
										VIXEN_WHERE_BETWEEN);
	$objWhere->AddAnd('Status', 		$_GET['Status']);
	$objWhere->AddAnd('PageName', 		$_GET['PageName'], VIXEN_WHERE_SEARCH);
	$objWhere->AddAnd(Array('Comment', 'Resolution', 'BugReportComment.Comment'), 		$_GET['Search'], VIXEN_WHERE_SEARCH);
	$objWhere->WhereArray();	
	
	// Start a new Bug Search
	$Style->attachObject (new Bug_list(
		$objWhere,
		isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
		isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 30));

			
			
	// Get list of employees that have created bugs.

	$arrCreatedByEmployeeColumns = Array();
	$arrCreatedByEmployeeColumns['CreatedById']		= "DISTINCT Employee.Id";
	$arrCreatedByEmployeeColumns['CreatedByName']	= "CONCAT(Employee.FirstName, ' ', Employee.LastName)";
	
	$strCreatedByEmployeeTables = 'BugReport JOIN Employee ON (BugReport.CreatedBy = Employee.Id)';
	
	$selCreatedByEmployee = new StatementSelect($strCreatedByEmployeeTables, $arrCreatedByEmployeeColumns);
	$intCount = $selCreatedByEmployee->Execute ($arrCreatedByEmployeeColumns);
	$arrCreatedByEmployeeResults = $selCreatedByEmployee->FetchAll ($this);
	$GLOBALS['Style']->InsertDOM($arrCreatedByEmployeeResults, 'CreatedByEmployees');

	// Get list of employees that are assigned to bugs.
	
	$arrAssignedToEmployeeColumns = Array();
	$arrAssignedToEmployeeColumns['AssignedToId']	= "DISTINCT Employee.Id";
	$arrAssignedToEmployeeColumns['AssignedToName']	= "CONCAT(Employee.FirstName, ' ', Employee.LastName)";
	
	$strAssignedToEmployeeTables = 'BugReport JOIN Employee ON (BugReport.AssignedTo = Employee.Id)';
	
	$selAssignedToEmployee = new StatementSelect($strAssignedToEmployeeTables, $arrAssignedToEmployeeColumns);
	$intCount = $selAssignedToEmployee->Execute ($arrAssignedToEmployeeColumns);
	$arrAssignedToEmployeeResults = $selAssignedToEmployee->FetchAll ($this);	
	$GLOBALS['Style']->InsertDOM($arrAssignedToEmployeeResults, 'AssignedToEmployees');
	
	// Get all the statuses
	
	$arrStatusesColumns = Array();
	$arrStatusesColumns['StatusId']	= "DISTINCT BugReport.Status";
	
	$strStatusesTables = 'BugReport';
	
	$selStatuses = new StatementSelect($strStatusesTables, $arrStatusesColumns);
	$intCount = $selStatuses->Execute ($arrStatusesColumns);
	$arrStatusesResults = $selStatuses->FetchAll ($this);	
	
	foreach ($arrStatusesResults as $intKey=>$arrResult)
		{
			$arrStatusesResults[$intKey]['Status'] = GetConstantDescription($arrStatusesResults[$intKey]['StatusId'], 'BugStatus');
		}
	//$arrStatusesResults['Status'] = GetConstantDescription($arrStatusesResults['StatusId'], 'BugStatus');
	$GLOBALS['Style']->InsertDOM($arrStatusesResults, 'Statuses');
	
	
	// Output the Result
	$Style->Output ('xsl/content/bug/list.xsl');
	
?>
