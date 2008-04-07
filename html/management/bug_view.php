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
	$arrPage['Modules']		= MODULE_BASE | MODULE_BUG ;
	
	// call application
	require ('config/application.php');
	
	// Pull documentation information
	$docDocumentation->Explain ('Bugs');
	
	
	if($_POST['Id'])
	{
		$intBug = $_POST['Id'];
		$strBugComment 	= $_POST['Comment']; 
		if($strBugComment)
		{
			$bugNewComment = new BugComments();
			$bugNewComment->AddComment($athAuthentication->AuthenticatedEmployee(), $intBug, $strBugComment);
		}
	}
	elseif($_GET['Id'])
	{
		$intBug = $_GET['Id'];
	}
	
	// Pull the Information about the Bug
	try
	{
		$bugDetails = $Style->attachObject (new Bug($intBug));
	}
	catch (Exception $e)
	{
		$Style->Output ('xsl/content/bug/notfound.xsl');
		exit;
	}
	
		$bugComments = $Style->attachObject (new BugComments($intBug));
	
	
		// Output the Bug View
	$Style->Output (
		'xsl/content/bug/view.xsl'
		);
		
	
?>
