<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	require ('config/application_loader.php');
	
	// If the User is not logged into the system
	if (!$athAuthentication->isAuthenticated ())
	{
		// Foward to Login Interface
		header ('Location: login.php'); exit;
	}
	
	// Get each Field for this Entity
	$selField = new StatementSelect ('Documentation', '*', 'Entity = <Entity> AND Field = <Field>');
	$selField->Execute(Array('Entity' => $_GET ['Entity'], 'Field' => $_GET ['Field']));
	
	$oblarrDocumentation = $Style->attachObject (new dataArray ('DocumentationDetails'));
	$oblarrDocumentation->Push (new DocumentationField ($selField->Fetch ()));
	
	// Output the Account View
	$Style->Output ('xsl/content/documentation/view.xsl');
	
?>
