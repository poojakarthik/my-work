<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	require ("decode_etech.php");
	
	$objDecode = new VixenDecode (Array ());
	
	// Create a new Report Object
	$rptReport = new Report (
		"+	COST CENTRE ALLOCATION RUNNER: " . date ("Y-m-d h:i:s A"),
		"bash@voiptelsystems.com.au"
	);
	
	// The following StatementSelect Object and While Loop 
	// deals with Caching the values of the Cost Centres
	// so that the information can be used for inserting
	// the ID into the database.
	
	$selCostCentres = new StatementSelect ("CostCentre", "*");
	$selCostCentres->Execute ();
	
	$arrCostCentres = Array ();
	
	while ($arrCostCentre = $selCostCentres->Fetch ())
	{
		$arrCostCentres [$arrCostCentre ['Name']] = $arrCostCentre ['Id'];
	}
	
	// Setup the Update Statement
	
	$arrUpdate = Array (
		"CostCentre" => ""
	);
	
	$updService = new StatementUpdate ('Service', 'Account = <Account> AND FNN = <FNN>', $arrUpdate);
	
	// Current Row
	$intCurrentRow = 0;
	
	// Loop through each of the Accounts
	while ($arrAccount = $objDecode->FetchCostCentre ())
	{
		++$intCurrentRow;
		$intUpdated = 0;
		
		// Loop through each of the Services
		foreach ($arrAccount ['DataArray'] as $arrService)
		{
			// If there is a Cost Centre defined, update the Service Cost Centre
			if ($arrService ['CostCentre'])
			{
				// Check the Cost Centre Exists
				if (!isset ($arrCostCentres [$arrService ['CostCentre']]))
				{
					echo "NOT FOUND: " . $arrService ['CostCentre'];
					exit;
				}
				
				// Update
				$arrUpdate ['CostCentre'] = $arrCostCentres [$arrService ['CostCentre']];
				
				$updService->Execute (
					$arrUpdate,
					Array (
						"Account"	=> $arrService ['Account'],
						"FNN"		=> $arrService ['FNN']
					)
				);
				
				++$intUpdated;
			}
		}
		
		$rptReport->AddMessageVariables (
			"+	<CurrentRow>		<CustomerID>	<Response>",
			Array (
				"<CurrentRow>"		=> $intCurrentRow,
				"<CustomerID>"		=> $arrAccount ['CustomerId'],
				"<Response>"		=> "ALLOCATION: " . $intUpdated . "/" . count ($arrAccount ['DataArray'])
			)
		);
	}
	
?>
