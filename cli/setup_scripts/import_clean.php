#!/usr/bin/php
<?=system ("clear");?>

	=====================================================================================================
	viXen : Clean Import (version 1.0)
	=====================================================================================================
	
<?php

// ---------------------------------------------------------------------------//
// CRAP THAT NEEDS TO GO AT THE TOP !
// ---------------------------------------------------------------------------//

	set_time_limit (0);
	
	Define('USER_NAME', 'Import');
	
	// load framework
	$strFrameworkDir = "../framework/";
	require_once($strFrameworkDir."framework.php");
	require_once($strFrameworkDir."functions.php");
	require_once($strFrameworkDir."definitions.php");
	require_once($strFrameworkDir."config.php");
	require_once($strFrameworkDir."db_access.php");
	require_once($strFrameworkDir."report.php");
	require_once($strFrameworkDir."error.php");
	require_once($strFrameworkDir."exception_vixen.php");
	
	
	
	$strImportDir = "../screen_scrape/";

	// instanciate the import object
	require_once($strImportDir.'vixen_import.php');
	$objImport = new VixenImport($arrConfig);

// ---------------------------------------------------------------------------//
// SCRIPT
// ---------------------------------------------------------------------------//
	
	// setup a db query object
	$sqlQuery = new Query();
	
	// Truncate Tables
	echo "Truncating Tables\n";
	$objImport->Truncate('Destination');
	$objImport->Truncate('DestinationTranslation');
	$objImport->Truncate('RecordType');
	$objImport->Truncate('RecordTypeTranslation');
	
	// clean import array
	$arrImport = Array();
	
	// Import Destinations
	$arrImport['/home/vixen/vixen_seed/Destination/IDD/Destination.csv'] = 'Destination';
	$arrImport['/home/vixen/vixen_seed/Destination/Destination.csv'] = 'Destination';
	
	// Import Destination Translations
	$arrImport['/home/vixen/vixen_seed/DestinationTranslation/IDD/DestinationTranslation.csv'] = 'DestinationTranslation';
	$arrImport['/home/vixen/vixen_seed/DestinationTranslation/DestinationTranslation.csv'] = 'DestinationTranslation';
	
	// Import Record Types
	$arrImport['/home/vixen/vixen_seed/RecordType/RecordType.csv'] = 'RecordType';
	
	// Import Record Type Translations
	$arrImport['/home/vixen/vixen_seed/RecordTypeTranslation/RecordTypeTranslation.csv'] = 'RecordTypeTranslation';
	
	// Do Imports
	foreach ($arrImport AS $strFilePath=>$strTable)
	{
		$strFileName = basename($strFilePath, '.csv');
		echo "Importing $strTable : $strFileName\n";
		if (!$objImport->ImportCSV($strTable, $strFilePath))
		{
			echo "FATAL ERROR : Import $strFileName FAILED\n";
			Die();
		}
	}

	// Link destination translations to destinations
	$strQuery  = "UPDATE DestinationTranslation, Destination ";
	$strQuery .= "SET DestinationTranslation.Code = Destination.Code ";
	$strQuery .= "WHERE DestinationTranslation.Description LIKE Destination.Description ";
	if(!$sqlQuery->Execute($strQuery))
	{
		echo "FATAL ERROR : Could not Link DestinationTranslations to Destinations\n";
		Die();
	}
	
	$strQuery  = "
	UPDATE DestinationTranslation, Destination 
	SET DestinationTranslation.Code = Destination.Code 
	WHERE DestinationTranslation.Code = 0
	AND LEFT(Destination.Description, CHAR_LENGTH(Destination.Description) - 9)
	LIKE LEFT(DestinationTranslation.Description, CHAR_LENGTH(DestinationTranslation.Description) - 7)
	AND Destination.Description LIKE '% - Mobile'
	AND DestinationTranslation.Description LIKE '%-MOBILE'
	";
	if(!$sqlQuery->Execute($strQuery))
	{
		echo "FATAL ERROR : Could not Link DestinationTranslations to Destinations\n";
		Die();
	}
	
	$strQuery  = "
	UPDATE DestinationTranslation, Destination 
	SET DestinationTranslation.Code = Destination.Code 
	WHERE DestinationTranslation.Code = 0
	AND LEFT(Destination.Description, CHAR_LENGTH(Destination.Description) - 9)
	LIKE LEFT(DestinationTranslation.Description, CHAR_LENGTH(DestinationTranslation.Description) - 2)
	AND Destination.Description LIKE '% - Mobile'
	AND DestinationTranslation.Description LIKE '% M'
	";
	if(!$sqlQuery->Execute($strQuery))
	{
		echo "FATAL ERROR : Could not Link DestinationTranslations to Destinations\n";
		Die();
	}
	
	$strQuery  = "
	UPDATE DestinationTranslation, Destination 
	SET DestinationTranslation.Code = Destination.Code 
	WHERE DestinationTranslation.Code = 0
	AND LEFT(Destination.Description, CHAR_LENGTH(Destination.Description) - 9)
	LIKE LEFT(DestinationTranslation.Description, CHAR_LENGTH(DestinationTranslation.Description) - 7)
	AND Destination.Description LIKE '% - Mobile'
	AND DestinationTranslation.Description LIKE '% Mobile'
	";
	if(!$sqlQuery->Execute($strQuery))
	{
		echo "FATAL ERROR : Could not Link DestinationTranslations to Destinations\n";
		Die();
	}
	
	$strQuery  = "
	UPDATE DestinationTranslation, Destination 
	SET DestinationTranslation.Code = Destination.Code 
	WHERE DestinationTranslation.Code = 0
	AND LEFT(Destination.Description, CHAR_LENGTH(Destination.Description) - 9)
	LIKE LEFT(DestinationTranslation.Description, CHAR_LENGTH(DestinationTranslation.Description) - 4)
	AND Destination.Description LIKE '% - Mobile'
	AND DestinationTranslation.Description LIKE '% Mob'
	";
	if(!$sqlQuery->Execute($strQuery))
	{
		echo "FATAL ERROR : Could not Link DestinationTranslations to Destinations\n";
		Die();
	}
	
	$strQuery  = "
	UPDATE DestinationTranslation, Destination 
	SET DestinationTranslation.Code = Destination.Code 
	WHERE DestinationTranslation.Code = 0
	AND LEFT(Destination.Description, CHAR_LENGTH(Destination.Description) - 9)
	LIKE MID(DestinationTranslation.Description, 11, CHAR_LENGTH(DestinationTranslation.Description) - 19)
	AND Destination.Description LIKE '% - Mobile'
	AND DestinationTranslation.Description LIKE 'Mobile to % - Mobile'
	";
	if(!$sqlQuery->Execute($strQuery))
	{
		echo "FATAL ERROR : Could not Link DestinationTranslations to Destinations\n";
		Die();
	}
	
	$strQuery  = "
	UPDATE DestinationTranslation, Destination 
	SET DestinationTranslation.Code = Destination.Code 
	WHERE DestinationTranslation.Code = 0
	AND LEFT(Destination.Description, 2) = 'UK'
	AND LEFT(DestinationTranslation.Description, 14) = 'United Kingdom'
	AND MID(Destination.Description, 5)
	LIKE MID(DestinationTranslation.Description, 15)
	";
	if(!$sqlQuery->Execute($strQuery))
	{
		echo "FATAL ERROR : Could not Link DestinationTranslations to Destinations\n";
		Die();
	}
	
	$strQuery  = "
	UPDATE DestinationTranslation, Destination 
	SET DestinationTranslation.Code = Destination.Code 
	WHERE DestinationTranslation.Code = 0
	AND Destination.Description = 'USA'
	AND 
	(
	DestinationTranslation.Description = 'United States of America'
	OR DestinationTranslation.Description = 'U.S.A.'
	)
	";
	if(!$sqlQuery->Execute($strQuery))
	{
		echo "FATAL ERROR : Could not Link DestinationTranslations to Destinations\n";
		Die();
	}
	
	// Link more destination translations to destinations (based on december run)
	$arrDesTran = Array();

	// Unitel Usage/S&E/OC&C :
	
	$arrDesTran[1]['829']	= 'New Zealand - Mobile (TNZ)';		//  -  61 - New Zealand - Mobile			New Zealand - Mobile (Other) | New Zealand - Mobile (TNZ)
	$arrDesTran[1]['6071']	= 'Telstra to Vietnam';				//  -  7 - Telstra to Vietnam - Mobile		Telstra to Vietnam | Vietnam - Mobile
	$arrDesTran[1]['7746']	= '';								//  -  2 - International Freephone Service	NFI
	$arrDesTran[1]['3489']	= '';								//  -  2 - missing
	$arrDesTran[1]['3375']	= '';								//  -  2 - missing
	$arrDesTran[1]['21']	= '';								//  -  1 - missing
	
	//Unitel Mobile Usage :
	
	$arrDesTran[1]['7584']	= 'China';							//  -  5 - China M							China | Telstra to China - Mobile | Off-Net to China 
	$arrDesTran[1]['7636']	= 'Hong Kong';						//  -  27 - Hong Kong
	//$arrDesTran[1]['7636']	= 'Hong Kong - Mobile';			//  -  27 - Hong Kong M
	$arrDesTran[1]['7529']	= 'UK';								//  -  1 - UK
	$arrDesTran[1]['7621']	= 'Ghana';							//  -  21 - Ghana
	$arrDesTran[1]['7531']	= 'USA';							//  -  2 - USA Califor
	//$arrDesTran[1]['7531']	= 'USA';						//  -  2 - USA Marylnd
	$arrDesTran[1]['8234']	= 'New Zealand - Mobile (TNZ)';		//  -  8 - NZ M								New Zealand - Mobile (Other) | New Zealand - Mobile (TNZ)
	$arrDesTran[1]['7420']	= 'Kenya - Mobile';					//  -  1 - Kenya M
	$arrDesTran[1]['7536']	= 'Vietnam - Mobile';				//  -  1 - Vietnam M
	$arrDesTran[1]['7432']	= 'Liechtenstein';					//  -  2 - Liechtenstn
	$arrDesTran[1]['8240']	= 'South Africa - Mobile';			//  -  3 - S. Africa M
	$arrDesTran[1]['7438']	= 'Malaysia';						//  -  4 - Malaysia
	//$arrDesTran[1]['7438']	= 'Malaysia - Mobile';			//  -  4 - Malaysia M
	$arrDesTran[1]['7513']	= 'Taiwan';							//  -  1 - Taiwan
	$arrDesTran[1]['7611']	= 'Fiji';							//  -  6 - Fiji
	//$arrDesTran[1]['7611']	= 'Fiji - Mobile';				//  -  6 - Fiji M
	$arrDesTran[1]['7463']	= 'New Zealand';					//  -  7 - New Zealand
	$arrDesTran[1]['8231']	= 'Japan - Mobile';					//  -  2 - Japan M
	$arrDesTran[1]['7556']	= 'Austria';						//  -  1 - Austria
	
	//Optus Usage :
	
	$arrDesTran[2]['1165']	= 'New Zealand - Mobile (TNZ)';		//  -  241 - NZ Mobile						New Zealand - Mobile (Other) | New Zealand - Mobile (TNZ)
	$arrDesTran[2]['1059']	= 'Comoros';						//  -  1 - Comoros Mobile					Comoros | Telstra to Comoros | Off-net to Comoros
	$arrDesTran[2]['188']	= 'Russia';							//  -  9 - Russian Fed.
	$arrDesTran[2]['1231']	= 'United Arab Emirates - Mobile';	//  -  25 - U.A.E. Mobile
	$arrDesTran[2]['157']	= 'USA - Tollfree';					//  -  50 - N/Am. Paid 800					USA - Tollfree
	$arrDesTran[2]['86']	= 'Macedonia';						//  -  7 - FYR Macedonia
	$arrDesTran[2]['1127']	= 'Laos';							//  -  5 - Laos Mobile						Laos | Telstra to Laos | Off-net to Laos
	$arrDesTran[2]['1043']	= 'Botswana';						//  -  3 - Botswana Mobile					Botswana | Telstra to Botswana | Off-net to Botswana
	$arrDesTran[2]['246']	= 'Serbia';							//  -  4 - Yugoslavia						is this correct
	$arrDesTran[2]['1120']	= 'Kazakhstan - Mobile';			//  -  1 - Kazakhstan Mob
	$arrDesTran[2]['1188']	= 'Russia - Mobile';				//  -  1 - Russia Fed Mob

	//AAPT Usage :
	
	$arrDesTran[3]['7063']	= 'Philippines - Mobile';			//  -  11 - PHILLIP'S(M)
	$arrDesTran[3]['8027']	= 'South Africa';					//  -  9 - SOUTH AFRICA
	$arrDesTran[3]['8064']	= 'New Zealand';					//  -  5 - NEW ZEALAND
	$arrDesTran[3]['7267']	= 'Botswana';						//  -  3 - BOTSWANA(M)						Botswana | Telstra to Botswana | Off-net to Botswana
	$arrDesTran[3]['7064']	= 'New Zealand - Mobile (TNZ)';		//  -  3 - NEWZEALAND(M)					New Zealand - Mobile (Other) | New Zealand - Mobile (TNZ)
	$arrDesTran[3]['8062']	= 'Indonesia - Jakarta';			//  -  2 - INDONESIA
	$arrDesTran[3]['9677']	= 'Solomon Islands';				//  -  1 - SOLOMON IS.
	$arrDesTran[3]['8041']	= 'Switzerland';					//  -  1 - SWITZERLAND
	
	$arrDefine = Array();
	$arrDefine['Code'] = TRUE;
 	$updDestinationTranslation 	= new StatementUpdate("DestinationTranslation", "Code = 0 AND Carrier = <Carrier> AND CarrierCode = <CarrierCode>", $arrDefine);
	$insDestinationTranslation	= new StatementInsert("DestinationTranslation");
	$selDestination 			= new StatementSelect("Destination", "Code", "Description = <Description>", NULL, 1);
	
	$arrData = Array();
	foreach($arrDesTran AS $intCarrier=>$arrDestination)
	{
		$arrData['Carrier'] 			= $intCarrier;
		foreach ($arrDestination AS $strCarrierCode=>$strDescription)
		{
			if ($strDescription)
			{
				$arrData['CarrierCode'] = $strCarrierCode;
				$arrData['Description'] = $strDescription;
				// get viXen Code
				$selDestination->Execute($arrData);
				$arrCode = $selDestination->Fetch();
				if (is_array($arrCode))
				{
					$intCode = $arrCode['Code'];
				}
				else
				{
					echo "Find Destination FAILED : $strDescription\n";
					Die();
				}
				$arrData['Code'] 		= $intCode;
				
				// try to update
				if (!$updDestinationTranslation->Execute($arrData, $arrData))
				{
					// or insert
					if (!$insDestinationTranslation->Execute($arrData))
					{
						echo "Add Destination Translation FAILED : $strDescription\n";
						Die();
					}
				}
				echo "Destination Translation added : $strDescription\n";
			}
		}
	}
	
	
	
	
	//finish
	echo "Done\n";
	Die ();
	

?>
