<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// vixen_import
//----------------------------------------------------------------------------//
/**
 * vixen_import
 *
 * Contains classes for importing a new Telco into vixen
 *
 * Contains classes for importing a new Telco into vixen
 *
 * @file		vixen_import.php
 * @language	PHP
 * @package		vixen_import
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenImport
//----------------------------------------------------------------------------//
/**
 * VixenImport
 *
 * Vixen import Module
 *
 * Vixen import Module
 *
 *
 * @prefix		obj
 *
 * @package		vixen_import
 * @class		VixenImport
 */
class VixenImport extends ApplicationBaseClass
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Importer
	 *
	 * Constructor for the Importer
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			Application
	 *
	 * @method
	 */
	function __construct($arrConfig)
 	{
		parent::__construct();
		
		// config
		$this->arrConfig = $arrConfig;
		
		// db access
		$this->insNote			 		= new StatementInsert("Note");
		$this->insRateGroupRate 		= new StatementInsert("RateGroupRate");
		$this->insRatePlanRateGroup 	= new StatementInsert("RatePlanRateGroup");
		$this->insServiceRateGroup		= new StatementInsert("ServiceRateGroup");
		$this->insServiceRatePlan		= new StatementInsert("ServiceRatePlan");
		
		$this->_insWithIdAccountGroup	= new StatementInsert("AccountGroup", NULL, TRUE);
		$this->_insWithIdAccount		= new StatementInsert("Account", NULL, TRUE);
		
		$this->_insAccountGroup			= new StatementInsert("AccountGroup");
		$this->_insAccount				= new StatementInsert("Account");
		$this->_insService				= new StatementInsert("Service");
		$this->_insContact				= new StatementInsert("Contact");
		$this->_insCreditCard			= new StatementInsert("CreditCard");
		
		$this->sqlQuery 				= new Query();
		$this->selServicesByType		= new StatementSelect(	"Service",
														"Id, FNN",
														"Account = <Account> AND ServiceType = <ServiceType>");
	}
	
	// ------------------------------------//
	// Add Records
	// ------------------------------------//
	
	function AddCustomer($arrCustomer)
	{
		// insert account group
		$intAccountGroup = InsertAccountGroup($arrCustomer['AccountGroup'][0]);
		
		// insert credit card
		if (is_array($arrCustomer['CreditCard']))
		{
			foreach ($arrCustomer['Account'] AS $arrCreditCard)
			{
				$arrCreditCard['AccountGroup'] = $intAccountGroup;
				$arrCreditCardId[] = $this->InsertCreditCard($arrCustomer['CreditCard']);
			}
			//TODO!flame!link this to the account
		}
		
		// insert accounts
		if (is_array($arrCustomer['Account']))
		{
			foreach ($arrCustomer['Account'] AS $arrAccount)
			{
				$arrAccount['AccountGroup'] = $intAccountGroup;
				$intAccount = $this->InsertWithIdAccount($arrAccount);
			}
		}
		
		// insert contacts
		if (is_array($arrCustomer['Contact']))
		{
			foreach ($arrCustomer['Contact'] AS $arrContact)
			{
				$arrContact['Account'] = $intAccount;
				$arrContact['AccountGroup'] = $intAccountGroup;
				$this->InsertContact($arrContact);
			}
		}
		
		// insert services
		if (is_array($arrCustomer['Service']))
		{
			foreach ($arrCustomer['Service'] AS $strFNN=>$arrService)
			{
				$arrServices[$strFNN] = $this->InsertService($arrService);
			}
			
			// insert service RateGroups
			if (is_array($arrCustomer['ServiceRateGroup']))
			{
				$this->AddCustomerRatePlanRateGroup($arrCustomer['ServiceRateGroup'], $arrServices);
			}
		}
	}
	
	function AddCustomerWithId($arrCustomer)
	{
		// insert account group
		InsertWithIdAccountGroup($arrCustomer['AccountGroup'][0]);
		
		// insert credit card
		if (is_array($arrCustomer['CreditCard']))
		{
			foreach ($arrCustomer['Account'] AS $arrCreditCard)
			{
				$intCreditCard[] = $this->InsertCreditCard($arrCustomer['CreditCard']);
			}
			//TODO!flame!link this to the account
		}
		
		// insert accounts
		if (is_array($arrCustomer['Account']))
		{
			foreach ($arrCustomer['Account'] AS $arrAccount)
			{
				$this->InsertWithIdAccount($arrAccount);
			}
		}
		
		// insert contacts
		if (is_array($arrCustomer['Contact']))
		{
			foreach ($arrCustomer['Contact'] AS $arrContact)
			{
				$this->InsertContact($arrContact);
			}
		}
		
		// insert services
		if (is_array($arrCustomer['Service']))
		{
			foreach ($arrCustomer['Service'] AS $strFNN=>$arrService)
			{
				$arrServices[$strFNN] = $this->InsertService($arrService);
			}
			
			// insert service RateGroups
			if (is_array($arrCustomer['ServiceRateGroup']))
			{
				$this->AddCustomerRatePlanRateGroup($arrCustomer['ServiceRateGroup'], $arrServices);
			}
		}
	}
	
	// add all service RateGroup & RatePlan records for a customer
	function AddCustomerRatePlanRateGroup($arrServiceRateGroups, $arrServices)
	{
		// clean the plan scores array
		$arrPlanScores = Array();
				
		// ADD RATE GROUPS
		foreach($arrServiceRateGroups AS $arrServiceRateGroup)
		{
			// get the ServiceType
			$intServiceType 	= $arrServiceRateGroup['ServiceType'];
			
			// get the RecordType
			$strRecordType 		= $arrServiceRateGroup['RecordTypeName'];
			$intRecordType 		= FindRecordType($strRecordType, $intServiceType);
			
			// get the RateGroup Id
			$strRateGroupName 	= $arrServiceRateGroup['RateGroupName'];
			$intRateGroup 		= $arrServiceRateGroup['RateGroup'];
			if (!$intRateGroup)
			{
				$intRateGroup 	= $this->FindRateGroup($arrServiceRateGroup['RateGroupName'], $intRecordType);
			}
			
			// get the Service Id
			$intService 		= $arrServiceRateGroup['Service'];
			if (!$intService && is_array($arrServices))
			{
				// get services id
				$intService = $arrServices[$arrServiceRateGroup['FNN']];
			}
			elseif (!$arrServiceRateGroup['Service'])
			{
				//TODO!flame! LATER
			}
			
			
			if ($intService && $intRateGroup)
			{
				// insert the record
				$this->AddServiceRateGroup($intService, $intRateGroup);
				
				
				if (!$strRecordType)
				{
					//TODO!flame! LATER - find RecordType.Name
					continue;
				}
				
				if (!$intServiceType)
				{
					//TODO!flame! LATER - find ServiceType.Name
					continue;
				}
				
				if (!$strRateGroupName)
				{
					//TODO!flame! LATER - find RateGroup.Name
					continue;
				}
				
				// add to RatePlan scores
				// for each plan
				foreach ($this->arrConfig['RatePlan'][$intServiceType] AS $strPlan=>$arrRateGroups)
				{
					// is this RateGroup part of this plan
					if ($arrRateGroups[$strRecordType] == $strRateGroupName)
					{
						// if so, score a goal for this plan
						$arrPlanScores[$intServiceType][$intService][$strPlan]++;
					}
				}
			}
		}
		
		// ADD RATE PLANS
		
		// for each service type
		foreach ($arrPlanScores AS $intServiceType=>$arrService)
		{
			// for each service
			foreach ($arrService AS $intService=>$arrPlan)
			{
				// sort the array of plans & get the highest scoring plan
				$strRatePlanName = array_pop(array_keys(asort($arrPlan)));
				
				// get the RatePlan ID
				$intRatePlan = $this->FindRatePlan($strRatePlanName, $intServiceType);
				
				// insert the record
				if ($intService && $intRatePlan)
				{
					$this->AddServiceRateGroup($intService, $intRatePlan);
				}
			}
		}
	}
	
	// add all notes for a customer
	function AddCustomerNote($arrNotes)
	{
		foreach ($arrNotes as $arrNote)
		{
			if (!$arrNote['Employee'])
			{
				if($arrNote['EmployeeName'])
				{
					$arrNote['Employee'] = $this->FindEmployee($arrNote['EmployeeName']);
				}
				elseif($arrNote['EmployeeFirstName'] && $arrNote['EmployeeLastName'])
				{
					$arrNote['Employee'] = $this->FindEmployee($arrNote['EmployeeFirstName'], $arrNote['EmployeeLastName']);
				}
			}
			$this->insNote->Execute($arrNote);
		}
	}

	// add a single note
	function AddNote($arrNote)
	{
		if (!$arrNote['Employee'])
		{
			if($arrNote['EmployeeName'])
			{
				$arrNote['Employee'] = $this->FindEmployee($arrNote['EmployeeName']);
			}
			elseif($arrNote['EmployeeFirstName'] && $arrNote['EmployeeLastName'])
			{
				$arrNote['Employee'] = $this->FindEmployee($arrNote['EmployeeFirstName'], $arrNote['EmployeeLastName']);
			}
		}
		return $this->insNote->Execute($arrNote);
	}
	
	// add a single RateGroupRate record 
	function AddRateGroupRate($intRateGroup, $intRate)
	{
		$arrRateGroupRate['RateGroup']	= $intRateGroup;
		$arrRateGroupRate['Rate']		= $intRate;
		return $this->insRateGroupRate->Execute($arrRateGroupRate);
	}
	
	// add a single RatePlanRateGroup record 
	function AddRatePlanRateGroup($intRatePlan, $intRateGroup)
	{
		// Link RateGroup to RatePlan
		$arrRatePlanRateGroup['RateGroup']	= $intRateGroup;
		$arrRatePlanRateGroup['RatePlan']	= $intRatePlan;
		return $this->insRatePlanRateGroup->Execute($arrRatePlanRateGroup);
	}
	
	// add a single RatePlanRateGroup record 
	function AddServiceRateGroup($intService, $intRateGroup)
	{
		// insert into ServiceRateGroup
		$arrData['Service']			= $intService;
		$arrData['RateGroup']		= $intRateGroup;
		$arrData['CreatedBy']		= 22;	// Rich ;)
		$arrData['CreatedOn']		= date("Y-m-d");
		$arrData['StartDatetime']	= "2006-01-01 11:57:40";
		$arrData['EndDatetime']		= "2030-11-30 11:57:45";
		$this->insServiceRateGroup->Execute($arrData);
	}
	
	// add a single ServiceRatePlan record
	function AddServiceRatePlan($intService, $intRatePlan)
	{
		// insert into ServiceRatePlan
		$arrData['Service']			= $intService;
		$arrData['RatePlan']		= $intRatePlan;
		$arrData['CreatedBy']		= 22;	// Rich ;)
		$arrData['CreatedOn']		= date("Y-m-d");
		$arrData['StartDatetime']	= "2006-01-01 11:57:40";
		$arrData['EndDatetime']		= "2030-11-30 11:57:45";
		$this->insServiceRatePlan->Execute($arrData);
	}
	
	// ------------------------------------//
	// Create Links
	// ------------------------------------//
	
	// create all RatePlanRecurringCharge records
	function CreateRatePlanRecurringCharge()
	{
		//TODO!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		return TRUE;
	}
	
	// Create all RateGroupRate records
	function CreateRateGroupRate()
	{
		// get list of all rate groups
		$strQuery = "SELECT * FROM RateGroup";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		While ($arrRateGroup = $sqlResult->fetch_assoc())
		{
			if ($arrRateGroup['RecordType'] != 27 && $arrRateGroup['RecordType'] != 27)
			{
				// NON-IDD Rates
				// look for a matching rate
				$strQuery = "SELECT Id FROM Rate WHERE RecordType = {$arrRateGroup['RecordType']} AND Name LIKE '{$arrRateGroup['Name']}' LIMIT 1";
				$sqlRate = $this->sqlQuery->Execute($strQuery);
				$arrRate = $sqlRate->fetch_assoc();
				if ($arrRate['Id'])
				{
					// save the link
					$this->AddRateGroupRate($arrRateGroup['Id'], $arrRate['Id']);
				}
				else
				{
					// single rate not found, look for a group of matching rates
					$strQuery = "SELECT Id FROM Rate WHERE RecordType = {$arrRateGroup['RecordType']} AND Name LIKE '{$arrRateGroup['Name']} - %'";
					$sqlRate = $this->sqlQuery->Execute($strQuery);
					While ($arrRate = $sqlRate->fetch_assoc())
					{
						// save the links
						$this->AddRateGroupRate($arrRateGroup['Id'], $arrRate['Id']);
					}
				}
			}
			else
			{
				// IDD Rates
				// look for a group of matching rates
				$strQuery = "SELECT Id FROM Rate WHERE RecordType = {$arrRateGroup['RecordType']} AND Name LIKE '{$arrRateGroup['Name']} : %'";
				$sqlRate = $this->sqlQuery->Execute($strQuery);
				While ($arrRate = $sqlRate->fetch_assoc())
				{
					// save the links
					$this->AddRateGroupRate($arrRateGroup['Id'], $arrRate['Id']);
				}
			}
		}
		
		// add stuff from the config array
		// for each defined RatePlan
		if (is_array($this->arrConfig['RateGroup']))
		{
			foreach ($this->arrConfig['RateGroup'] AS $intServiceType=>$arrGroups)
			{
				foreach ($arrGroups as $strRatePlan=>$arrRatePlan)
				{
					// get the RatePlan Id
					$intRatePlan = $this->FindRatePlan($strRatePlan, $intServiceType);
					if (!(int)$intRatePlan)
					{
						//error
						$this->Error("RatePlan $intRatePlan not found");
						continue;
					}
						
					// for each RecordType within the RatePlan
					foreach ($arrRatePlan as $strRecordType=>$strRateGroup)
					{
						// get RecordType Id
						$intRecordType = $this->FindRecordType($strRecordType, $intServiceType);
						if (!(int)$intRecordType)
						{
							//error
							$this->Error("RecordType $intRecordType not found : $strRecordType - $intServiceType");
							continue;
						}
						
						// get the RateGroup Id
						$intRateGroup = $this->FindRateGroup($strRateGroup, $intRecordType);
						if (!(int)$intRateGroup)
						{
							//error
							$this->Error("RateGroup $intRateGroup not found : $strRateGroup - $intRecordType");
							continue;
						}
						
						// link RatePlan to RateGroup
						$this->AddRatePlanRateGroup($intRatePlan, $intRateGroup);
					}
				}
			}
		}
		
		return TRUE;
	}
	
	// create all RatePlanRateGroup records
	function CreateRatePlanRateGroup()
	{
		// for each defined RatePlan
		if (is_array($this->arrConfig['RatePlan']))
		{
			foreach ($this->arrConfig['RatePlan'] AS $intServiceType=>$arrPlans)
			{
				foreach ($arrPlans as $strRatePlan=>$arrRatePlan)
				{
					// get the RatePlan Id
					$intRatePlan = $this->FindRatePlan($strRatePlan, $intServiceType);
					if (!(int)$intRatePlan)
					{
						//error
						$this->Error("RatePlan $intRatePlan not found");
						continue;
					}
						
					// for each RecordType within the RatePlan
					foreach ($arrRatePlan as $strRecordType=>$strRateGroup)
					{
						// get RecordType Id
						$intRecordType = $this->FindRecordType($strRecordType, $intServiceType);
						if (!(int)$intRecordType)
						{
							//error
							$this->Error("RecordType $intRecordType not found : $strRecordType - $intServiceType");
							continue;
						}
						
						// get the RateGroup Id
						$intRateGroup = $this->FindRateGroup($strRateGroup, $intRecordType);
						if (!(int)$intRateGroup)
						{
							//error
							$this->Error("RateGroup $intRateGroup not found : $strRateGroup - $intRecordType");
							continue;
						}
						
						// link RatePlan to RateGroup
						$this->AddRatePlanRateGroup($intRatePlan, $intRateGroup);
					}
				}
			}
		}
		else
		{
			$this->Error("No Rate Plans Found");
			return FALSE;
		}
		
		return TRUE;
	}
	
	// ------------------------------------//
	// Errors
	// ------------------------------------//
	
	// report an error
	function Error($strError)
	{
		$this->strLastError = "$strError \n";
		$this->strErrorLog .= "$strError \n";
	}
	
	// return the error log
	function ErrorLog()
	{
		return $this->strErrorLog;
	}
	
	// return the last error
	function LastError()
	{
		return $this->strLastError;
	}
	
	// ------------------------------------//
	// INSERT WITH ID
	// ------------------------------------//
	
	function InsertWithIdAccountGroup($arrAccountGroup)
	{
		return $this->_insAccountGroup->Execute($arrAccountGroup);
	}
	
	function InsertWithIdAccount($arrAccount)
	{
		return $this->_insAccount->Execute($arrAccount);
	}
	
	// ------------------------------------//
	// INSERT
	// ------------------------------------//
	
	function InsertAccountGroup($arrAccountGroup)
	{
		return $this->_insAccountGroup->Execute($arrAccountGroup);
	}
	
	function InsertAccount($arrAccount)
	{
		return $this->_insAccount->Execute($arrAccount);
	}
	
	function InsertContact($arrContact)
	{
		return $this->_insContact->Execute($arrContact);
	}
	
	function InsertService($arrService)
	{
		return $this->_insContact->Execute($arrService);
	}
	
	function InsertCreditCard($arrCreditCard)
	{
		return $this->_insCreditCard->Execute($arrCreditCard);
	}
	
	// ------------------------------------//
	// IMPORT
	// ------------------------------------//

	// import a CSV file
	function ImportCSV($strTable, $strFullPath, $strSeparator=';', $strTerminator='\n', $intSkipRecords=1)
	{
		$strQuery	=	"LOAD DATA INFILE '$strFullPath' \n" .
						"INTO TABLE `$strTable` \n" .
						"FIELDS TERMINATED BY '$strSeparator' ENCLOSED BY '\"' ESCAPED BY '\\\\' \n" .
						"LINES TERMINATED BY '$strTerminator' \n" .
						"IGNORE $intSkipRecords LINES";			
		$qryImportCSV = new Query();
		$intResult = $qryImportCSV->Execute($strQuery);
		if (!$intResult)
		{
			//echo $qryImportCSV->Error();
		}
		return $intResult;
	}
	
	// ------------------------------------//
	// TRUNCATE
	// ------------------------------------//
	
	function Truncate($strTableName)
	{
		$strTableName = trim($strTableName);
		$qryTruncate = new QueryTruncate();
		return $qryTruncate->Execute($strTableName);
	}
	
	// ------------------------------------//
	// FIND 
	// ------------------------------------//
	
	// find RecordType
	function FindRecordType($strRecordType, $intServiceType)
	{
		// check if we have a cache of record types
		if (!is_array($this->_arrRecordTypes))
		{
			// get an array of recordTypes
			$selFindRecordType = new StatementSelect("RecordType", "ServiceType, Code, Id");
			$selFindRecordType->Execute();
			while($arrRecordType = $selFindRecordType->Fetch())
			{
				$this->_arrRecordTypes[$arrRecordType['ServiceType']][$arrRecordType['Code']] = $arrRecordType['Id'];
			}
		}
		
		// return the record Type Id
		return $this->_arrRecordTypes[$intServiceType][$strRecordType];
	}
	
	// find rate group
	function FindRateGroup($strRateGroupName, $intRecordType)
	{
		// check if we have a cache of rate groups
		if (!is_array($this->_arrRateGroups))
		{
			// get an array of rate groups
			$selFindRateGroup = new StatementSelect("RateGroup", "RecordType, Name, Id");
			$selFindRateGroup->Execute();
			while($arrRateGroup = $selFindRateGroup->Fetch())
			{
				$this->_arrRateGroups[$arrRateGroup['RecordType']][$arrRateGroup['Name']] = $arrRateGroup['Id'];
			}
		}
		
		// return the rate group Id
		return $this->_arrRateGroups[$intRecordType][$strRateGroupName];
	}
	
	// find rate plan
	function FindRatePlan($strRatePlanName, $intServiceType)
	{
		// check if we have a cache of rate plans
		if (!is_array($this->_arrRatePlans))
		{
			// get an array of rate plans
			$selFindRatePlan = new StatementSelect("RatePlan", "ServiceType, Name, Id");
			$selFindRatePlan->Execute();
			while($arrRatePlan = $selFindRatePlan->Fetch())
			{
				$this->_arrRatePlans[$arrRatePlan['ServiceType']][$arrRatePlan['Name']] = $arrRatePlan['Id'];
			}
		}
		
		// return the rate group Id
		return $this->_arrRatePlans[$intServiceType][$strRatePlanName];
	}
	
	// find Employee
	function FindEmployee($strFirstName, $strLastName='NULL')
	{
		// break apart name to get last name if needed
		if (!$strLastName)
		{
			$arrName = explode(' ', $strFirstName, 2);
			$strFirstName = $arrName[0];
			$strLastName = trim($arrName[1]);
		}
		
		// check if we have a cache of employees
		if (!is_array($this->_arrEmployee))
		{
			// get an array of employees
			$selFindEmployee = new StatementSelect("Employee", "FirstName, LastName, Id");
			$selFindEmployee->Execute();
			while($arrEmployee = $selFindEmployee->Fetch())
			{
				$this->_arrEmployee[$arrEmployee['LastName']][$arrEmployee['FirstName']] = $arrEmployee['Id'];
			}
		}
		
		// return the employee Id
		return $this->_arrEmployee[$arrEmployee['LastName']][$arrEmployee['FirstName']];
	}
}


?>
