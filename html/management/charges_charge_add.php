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
	$arrPage['Modules']		= MODULE_BASE | MODULE_CHARGE | MODULE_CHARGE_TYPE;
	
	// call application
	require ('config/application.php');
	
	$cgtChargesTypes = new ChargeTypes ();
	
	$docDocumentation->Explain ('Charge Type');
	
	$oblstrError			= $Style->attachObject (new dataString ('Error', ''));
	
	$oblarrChargeType		= $Style->attachObject (new dataArray ('ChargeType'));
	
	$oblstrChargeType		= $oblarrChargeType->Push (new dataString ('ChargeType', ''));
	$oblstrDescription		= $oblarrChargeType->Push (new dataString ('Description', ''));
	$oblfltAmount			= $oblarrChargeType->Push (new dataFloat ('Amount', ''));
	$natNature				= $oblarrChargeType->Push (new Natures ());
	$oblbolFixed			= $oblarrChargeType->Push (new dataBoolean ('Fixed', FALSE));
	
	if ($_SERVER ['REQUEST_METHOD'] == "POST")
	{
		$oblstrChargeType->setValue		($_POST ['ChargeType']);
		$oblstrDescription->setValue	($_POST ['Description']);
		$natNature->setValue			($_POST ['Nature']);
		$oblbolFixed->setValue			($_POST ['Fixed']);
		
		$bolAmountValid = $oblfltAmount->setValue ($_POST ['Amount']);
		
		if (!$natNature->setValue ($_POST ['Nature']))
		{
			// error here
			$oblstrError->setValue ('Nature');
		}
		else if (!$_POST ['ChargeType'])
		{
			// The Charge Type cannot be Empty
			$oblstrError->setValue ('CType-Blank');
		}
		else if (!$_POST ['Description'])
		{
			// The Description cannot be Empty
			$oblstrError->setValue ('Descr-Blank');
		}
		else if (!$_POST ['Amount'] || !$bolAmountValid || $oblfltAmount->getValue () == 0)
		{
			// The Amount cannot be Empty
			$oblstrError->setValue ('Amount Invalid');
		}
		else if (($intExistingChargeTypeId = $cgtChargesTypes->UnarchivedChargeType($_POST['ChargeType'])) !== FALSE)
		{
			// An unarchived ChargeType exists with the exact same name
			$objExistingChargeType = new ChargeType($intExistingChargeTypeId);
			if ($objExistingChargeType->Pull('automatic_only')->getValue() == TRUE)
			{
				// The existing charge type is an automatic only one (can only be applied by a backend process), and cannot be archived by the user
				$oblstrError->setValue ('CType-Exists-And-Is-Automatic-Only');
			}
			else
			{
				// The existing charge type is not an automatic only one, and can therefore be archived by the user
				$oblstrError->setValue ('CType-Exists');
			}
			
			
		}
		else
		{
			try
			{
				// Add it to the Database
				$intChargeType = $cgtChargesTypes->Add (
					Array (
						"ChargeType"			=> $_POST ['ChargeType'],
						"Description"			=> $_POST ['Description'],
						"Amount"				=> $_POST ['Amount'],
						"Nature"				=> $_POST ['Nature'],
						"Fixed"					=> $_POST ['Fixed'],
					)
				);
				
				header ("Location: charges_charge_added.php"); exit;
			}
			catch (Exception $e)
			{
				$oblstrError->setValue ($e->getMessage ());
			}
		}
	}
	
	$Style->Output ('xsl/content/charges/charges/add.xsl');
	
?>
