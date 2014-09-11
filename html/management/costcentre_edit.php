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
	$arrPage['Modules']		= MODULE_BASE | MODULE_COST_CENTRE;
	
	// call application
	require ('config/application.php');
	
	// Try to get the Cost Centre we are dealing with
	try
	{
		$ccrCostCentre = $Style->attachObject (new CostCentre (($_GET ['Id']) ? $_GET ['Id'] : $_POST ['Id']));
	}
	catch (Exception $e)
	{
		$Style->Output ("xsl/content/account/costcentre/notfound.xsl");
		exit;
	}
	
	// Error Handler
	$oblstrError = $Style->attachObject (new dataString ('Error'));
	
	// Start Remembering
	$oblarrUIValues	= $Style->attachObject	(new dataArray	('ui-values'));
	$oblstrName		= $oblarrUIValues->Push	(new dataString	('Name',		$ccrCostCentre->Pull ('Name')->getValue ()));
	
	// This is called when we are actually wishing to do the Update Part
	if (isset ($_POST ['Name']))
	{
		// Set the Value of the Name just incase we need to use it for Error Handling
		$oblstrName->setValue ($_POST ['Name']);
		
		// If the name is Blank, then display an Error
		if (!$_POST ['Name'])
		{
			$oblstrError->setValue ('Name Empty');
		}
		else
		{
			// Update the Cost Centre
			$ccrCostCentre->Update (
				Array (
					'Name'	=> $_POST ['Name']
				)
			);
			
			// Forward to the Confirmation Page
			header ('Location: costcentre_edited.php?Id=' . $ccrCostCentre->Pull ('Id')->getValue ());
			exit;
		}
	}
	
	$docDocumentation->Explain ('Cost Centre');
	
	$Style->Output (
		"xsl/content/account/costcentre/edit.xsl",
		Array (
			'Account'		=> $ccrCostCentre->Pull ('Account')->getValue ()
		)
	);
	
?>
