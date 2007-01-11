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
	$arrPage['Permission']	= PERMISSION_ADMIN;
	$arrPage['Modules']		= MODULE_BASE | MODULE_EMPLOYEE;
	
	// call application
	require ('config/application.php');
	
	
	// Start a new Employee Search
	$emsEmployees = $Style->attachObject (new Employees);
	
	// Constrain against the requested fields
	if (isset ($_GET ['constraint']))
	{
		foreach ($_GET ['constraint'] as $strConstraintName => $arrConstraintRules)
		{
			if ($arrConstraintRules ['Value'] != '')
			{
				$emsEmployees->Constrain (
					$strConstraintName,
					$arrConstraintRules ['Operator'],
					$arrConstraintRules ['Value']
				);
			}
		}
	}
	
	// Order by the requested field
	if (isset ($_GET ['Order']['Column']))
	{
		$emsEmployees->Order (
			$_GET ['Order']['Column'],
			isset ($_GET ['Order']['Method']) ? $_GET ['Order']['Method'] == 1 : TRUE
		);
	}
	
	// Pull a Sample
	$emsEmployees->Sample (
		isset ($_GET ['rangePage']) ? $_GET ['rangePage'] : 1, 
		isset ($_GET ['rangeLength']) ? $_GET ['rangeLength'] : null
	);
	
	// Output the Result
	$Style->Output ('xsl/content/employee/list.xsl');
	
?>
