<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= TRUE;
	$arrPage['Permission']	= PERMISSION_OPERATOR_EXTERNAL;
	$arrPage['Modules']		= MODULE_BASE;
	
	// call application
	require ('config/application.php');
	
	// Get each Field for this Entity
	$selField = new StatementSelect ('Documentation', '*', 'Entity = <Entity> AND Field = <Field>');
	$selField->Execute(Array('Entity' => $_GET ['Entity'], 'Field' => $_GET ['Field']));
	
	$oblarrDocumentation = $Style->attachObject (new dataArray ('DocumentationDetails'));
	$oblarrDocumentation->Push (new DocumentationField ($selField->Fetch ()));
	
	// Output the Account View
	$Style->Output ('xsl/content/documentation/view.xsl');
	
?>
