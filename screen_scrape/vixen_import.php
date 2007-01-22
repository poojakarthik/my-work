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
				$this->AddCustomerServiceRateGroup($arrCustomer['ServiceRateGroup'], $arrServices);
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
				$this->AddCustomerServiceRateGroup($arrCustomer['ServiceRateGroup'], $arrServices);
			}
		}
	}
	
	AddCustomerServiceRatePlan($arrServiceRateGroups, $arrServices)
	{
		// for each service
		foreach($arrServiceRateGroups AS $arrServiceRateGroup)
		{
				// for each recordtype
						// for each RatePlan
							// could we be on this rate plan ?
							
		// get highest scoring RatePlan
		$arrConfig['RatePlan']['PlanName'][17] 		= 'Local-14';
	}
	
	// add all service RateGroup & RatePlan records for a customer
	function AddCustomerRatePlanRateGroup($arrServiceRateGroups, $arrServices)
	{
		// clean the plan scores array
		$arrPlanScores = Array();
				
		// ADD RATE GROUPS
		foreach($arrServiceRateGroups AS $arrServiceRateGroup)
		{
			// get the RateGroup Id
			if (!$arrServiceRateGroup['RateGroup'])
			{
				$arrServiceRateGroup['RateGroup'] = $this->FindRateGroup($arrServiceRateGroup['RateGroupName'], $arrServiceRateGroup['RecordType']);
			}
			
			// get the Service Id
			if (!$arrServiceRateGroup['Service'] && is_array($arrServices))
			{
				// get services id
				$arrServiceRateGroup['Service'] = $arrServices[$arrServiceRateGroup['FNN']];
			}
			else (!$arrServiceRateGroup['Service'])
			{
				//TODO!flame! LATER
			}
			
			
			if ($arrServiceRateGroup['Service'] && $arrServiceRateGroup['RateGroup'])
			{
				// insert the record
				$this->AddServiceRateGroup($arrServiceRateGroup['Service'], $arrServiceRateGroup['RateGroup']);
				
				// stuff needed to find the ratePlan
				$intService 		= $arrServiceRateGroup['Service'];
				$strRecordType 		= $arrServiceRateGroup['RecordType'];
				$strRateGroupName 	= $arrServiceRateGroup['RateGroupName'];
				
				if (!$strRecordType)
				{
					//TODO!flame! LATER - find RecordType.Name
					continue;
				}
				
				if (!$strRateGroupName)
				{
					//TODO!flame! LATER - find RateGroup.Name
					continue;
				}
				
				// add to RatePlan scores
				// for each plan
				foreach ($this->arrConfig['RatePlan'] AS $strPlan=>$arrRateGroups)
				{
					// is this RateGroup part of this plan
					if ($arrRateGroups[$strRecordType] == $strRateGroupName)
					{
						// if so, score a goal for this plan
						$arrPlanScores[$intService][$strPlan]++;
					}
				}
			}
		}
		
		// ADD RATE PLANS
		
		// for each service
		foreach ($arrPlanScores AS $intService=>$arrPlan)
		{
			// sort the array of plans
			$arrPlan = asort($arrPlan);
			
			// get the highest scoring plan
			
		}
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
	}
	
	// create all RatePlanRateGroup records
	function CreateRatePlanRateGroup()
	{
		// for each defined RatePlan
		if (is_array($this->arrConfig['RatePlan']))
		{
			foreach ($this->arrConfig['RatePlan'] as $intRatePlan=>$arrRatePlan)
			{
				// get the RatePlan Id
				if ((int)$intRatePlan != $intRatePlan)
				{
					$strQuery = "SELECT Id FROM RatePlan WHERE Name = '$intRatePlan' LIMIT 1";
					$sqlRatePlan = $this->sqlQuery->Execute($strQuery);
					$arrPlan = $sqlRatePlan->fetch_assoc();
					$intRatePlan = $arrPlan['Id'];
					if (!(int)$intRatePlan)
					{
						//error
						$this->Error("RatePlan $intRatePlan not found");
						continue;
					}
				}
					
				// for each RecordType within the RatePlan
				foreach ($arrRatePlan as $intRecordType=>$intRateGroup)
				{
					// get RecordType Id
					if ((int)$intRecordType != $intRecordType)
					{
						$strQuery = "SELECT Code FROM RecordType WHERE Name = '$intRecordType' LIMIT 1";
						$sqlRecordType = $this->sqlQuery->Execute($strQuery);
						$arrRecordType = $sqlRecordType->fetch_assoc();
						$intRecordType = $arrRecordType['Code'];
						if (!(int)$intRecordType)
						{
							//error
							$this->Error("RecordType $intRecordType not found");
							continue;
						}
					}
					
					// get the RateGroup Id
					if ((int)$intRateGroup != $intRateGroup)
					{
						$strQuery = "SELECT Id FROM RateGroup WHERE RecordType = $intRecordType AND Name = '$intRateGroup' LIMIT 1";
						$sqlRateGroup = $this->sqlQuery->Execute($strQuery);
						$arrRateGroup = $sqlRateGroup->fetch_assoc();
						$intRateGroup = $arrRateGroup['Id'];
						if (!(int)$intRateGroup)
						{
							//error
							$this->Error("RateGroup $intRateGroup not found");
							continue;
						}
					}
					
					// link RatePlan to RateGroup
					$this->AddRatePlanRateGroup($intRatePlan, $intRateGroup);
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
	
	function ImportRate()
	{
	
	}
	
	function ImportRateGroup()
	{
	
	}
	
	function ImportRatePlan()
	{
	
	}
	
	
	// ------------------------------------//
	// FIND 
	// ------------------------------------//
	
	// find rate group
	function FindRateGroup($strRateGroupName, $intRecordType)
	{
		// check if we have a cache of rate groups
		if (!is_array($this->_arrRateGroups))
		{
			// get an array of rate groups
			//TODO!rich! get rate groups from the db and put them in an array
			// $this->_arrRateGroups[RateGroup.RecordType][RateGroup.Name] = RateGroup.Id
		}
		
		// return the rate group Id
		return $this->_arrRateGroups[$intRecordType][$strRateGroupName];
	}
}


?>
