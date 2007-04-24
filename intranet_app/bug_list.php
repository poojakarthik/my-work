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
	
	$objWhere = new Vixen_where();
	$objWhere->AddAnd('CreatedBy', 	$_GET['CreatedBy']);
	$objWhere->AddAnd('AssignedTo', 	$_GET['AssignedTo']);
	$objWhere->AddAnd('CreatedOn', 	Array($_GET['CreatedOnStart'], $_GET['CreatedOnEnd']), VIXEN_WHERE_BETWEEN);
	$objWhere->AddAnd('ClosedOn', 		Array($_GET['ClosedOnStart'], $_GET['ClosedOnEnd']), VIXEN_WHERE_BETWEEN);
	$objWhere->AddAnd('Status', 		$_GET['Status']);
	$objWhere->AddAnd('PageName', 		$_GET['PageName'], VIXEN_WHERE_SEARCH);
	$objWhere->AddAnd(Array('Comment', 'Resolution', 'BugReportComment.Comment'), 		$_GET['Search'], VIXEN_WHERE_SEARCH);
	$objWhere->WhereArray();	
	
	// Start a new Bug Search
	$Style->attachObject (new Bug_list(
		$objWhere,
		isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
		isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : 30));


	// Output the Result
	$Style->Output ('xsl/content/bug/list.xsl');
	
?>
