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
	$oblstrAmount			= $oblarrChargeType->Push (new dataString ('Amount', ''));
	$natNature				= $oblarrChargeType->Push (new Natures ());
	$oblbolFixed			= $oblarrChargeType->Push (new dataBoolean ('Fixed', FALSE));
	
	if ($_SERVER ['REQUEST_METHOD'] == "POST")
	{
		$oblstrChargeType		->setValue ($_POST ['ChargeType']);
		$oblstrDescription		->setValue ($_POST ['Description']);
		$oblstrAmount			->setValue ($_POST ['Amount']);
		$natNature				->setValue ($_POST ['Nature']);
		$oblbolFixed			->setValue ($_POST ['Fixed']);
		
		if (!$natNature->setValue ($_POST ['Nature']))
		{
			// error here
			$oblstrError->setValue ('Nature');
		}
		else if (empty ($oblstrChargeType))
		{
			// The Charge Type cannot be Empty
			$oblstrError->setValue ('CType-Blank');
		}
		else if (empty ($oblstrDescription))
		{
			// The Description cannot be Empty
			$oblstrError->setValue ('Descr-Blank');
		}
		else if ($cgtChargesTypes->UnarchivedChargeType ($_POST ['ChargeType']) !== false)
		{
			// A unarchived Charge Type must be unique
			$oblstrError->setValue ('CType-Exists');
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
