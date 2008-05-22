<?php


// The ModuleService object models a service for a particular Account
// The service is identified by any of the Ids of the Service records which
// were used to model this service for the account that the records belong to
// The service is also identifiable with an FNN and Account Id


class ModuleService
{
	protected $_intId				= NULL;
	protected $_strErrorMsg			= NULL;
	
	// You can't change the status unless everything else is saved
	protected $_bolSaved			= NULL;
	
	protected $_strFNN	= NULL;
	protected $_intServiceType	= NULL;
	protected $_intCostCentre	= NULL;
	protected $_intAccount		= NULL;
	protected $_intAccountGroup	= NULL;
	protected $_bolForceInvoiceRender = NULL;
	
	protected $_arrHistory				= NULL;
	protected $_arrCurrentPlan			= NULL;
	protected $_arrFuturePlan			= NULL;
	protected $_arrRateGroupDetails		= NULL;
	protected $_arrAdjustments			= NULL;
	protected $_arrRecurringAdjustments	= NULL;
	protected $_arrCostCentre			= NULL;
	
/******************************************************************************/
// Constructor
/******************************************************************************/
	// The user can supply a single Service Id OR
	// $mixService represents a service object as a multi-dimensional array
	// TODO! define the structure that the multi-dimensional array should be in
	function __construct($mixService)
	{
		if (is_array($mixService))
		{
			// $mixService represents a service object as a multi-dimensional array
			if (!$this->parseServiceArray($mixService))
			{
				throw new Exception($this->_strErrorMsg);
			}
		}
		else
		{
			// Service Id has been supplied
			// Retrieve its details
			if (!$this->_LoadDetails())
			{
				throw new Exception($this->_strErrorMsg);
			}
		}
	}

	// Loads in the service from the database (doesn't load extra details)
	private function _LoadDetails()
	{
		$intService = $this->_intId;
		$strTables	= "Service"; 
		$arrColumns	= Array("Id" 				=> "Id",
							"FNN"				=> "FNN",
							"ServiceType"		=> "ServiceType",
							"Account"			=> "Account",
							"Status"		 	=> "Status",
							"LineStatus"		=> "LineStatus",
							"LineStatusDate"	=> "LineStatusDate",
							"CreatedOn"			=> "CreatedOn", 
							"ClosedOn"			=> "ClosedOn",
							"CreatedBy"			=> "CreatedBy", 
							"ClosedBy"			=> "ClosedBy",
							"NatureOfCreation"	=> "NatureOfCreation",
							"NatureOfClosure"	=> "NatureOfClosure",
							"NextOwner"			=> "NextOwner", 
							"PreviousOwner"		=> "PreviousOwner",
							"AccountGroup"		=> "AccountGroup",
							"ForceInvoiceRender"	=> "ForceInvoiceRender"
							);
		$strWhere	= "	Account = (SELECT Account FROM Service WHERE Id = <ServiceId>)
						AND
						FNN = (SELECT FNN FROM Service WHERE Id = <ServiceId>)";
		$arrWhere	= Array("ServiceId" => $intService);
		$strOrderBy	= ("Id DESC");
		
		$selServices	= new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy);
		$mixResult		= $selServices->Execute($arrWhere);
		
		if ($mixResult === FALSE)
		{
			// Database error occured
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve details for service with Id: $intService";
			return FALSE;
		}
		
		$arrRecord						= $selServices->Fetch();
		$this->_strFNN 					= $arrRecord['FNN'];
		$this->_intServiceType			= $arrRecord['ServiceType'];
		$this->_intCostCentre			= $arrRecord['CostCentre'];
		$this->_intAccount				= $arrRecord['Account'];
		$this->_intAccountGroup			= $arrRecord['AccountGroup'];
		$this->_bolForceInvoiceRender	= (bool)$arrRecord['ForceInvoiceRender'];
		
		//TODO! I got up to here on Thursday
		
		// Add this record's details to the history array
		$arrService['History']		= Array();
		$arrService['History'][]	= Array	(
												"ServiceId"	=> $arrRecord['Id'],
												"CreatedOn"	=> $arrRecord['CreatedOn'],
												"ClosedOn"	=> $arrRecord['ClosedOn'],
												"CreatedBy"	=> $arrRecord['CreatedBy'],
												"ClosedBy"	=> $arrRecord['ClosedBy'],
												"Status"	=> $arrRecord['Status'],
												"LineStatus"		=> $arrRecord['LineStatus'],
												"LineStatusDate"	=> $arrRecord['LineStatusDate'],
											);
		 
		
		// If multiple Service records relate to the one actual service then they will be consecutive in the RecordSet
		// Find each one and add it to the Status history
		while (($arrRecord = $selServices->Fetch()) !== FALSE)
		{
			// This record relates to the same Service
			$arrService['History'][]	= Array	(
													"ServiceId"	=> $arrRecord['Id'],
													"CreatedOn"	=> $arrRecord['CreatedOn'],
													"ClosedOn"	=> $arrRecord['ClosedOn'],
													"CreatedBy"	=> $arrRecord['CreatedBy'],
													"ClosedBy"	=> $arrRecord['ClosedBy'],
													"Status"	=> $arrRecord['Status'],
													"LineStatus"		=> $arrService['LineStatus'],
													"LineStatusDate"	=> $arrService['LineStatusDate'],
												);
		}
		
		
		
		$selCurrentServiceId = new StatementSelect("Service", Array("Id"=>"Max(Id)"), "Account = (SELECT Account FROM Service WHERE Id = <ServiceId>) AND FNN = (SELECT FNN FROM Service WHERE Id = <ServiceId>)");
		$mixResult = $selCurrentServiceId->Execute(Array("ServiceId" => $mixService));
		
		if ($mixResult === FALSE)
		{
			// Database failure
			$this->_strErrorMsg = "Database failure when trying to find the most recent Service Record modelling this service";
			throw new Exception($this->_strErrorMsg);
		}
		
		// Save the Id of the mose recent Service Record which models the same service as $intService
		$arrRecord = $selCurrentServiceId->Fetch();
		$this->_intId = $arrRecord['Id'];
		if ($this->_intId == NULL)
		{
			// Service record with Id == $intService could not be found
			$this->_strErrorMsg = "Service record with Id = $mixService, could not be found";
			throw new Exception($this->_strErrorMsg);
		}
	}

	// Builds the Service object based on $arrService, instead of retrieving the details from the database
	function ParseServiceArray($arrService)
	{
		//TODO!
		$this->_strErrorMsg = "ParseServiceArray() functionality has not been implemented yet";
		return FALSE;
	}
	
	// Refresh the Service Object from the database
	function Refresh()
	{
	}
	
	function LoadPlanDetails()
	{
	}
	
	function LoadAdjustmentDetails()
	{
	}
	
	function LoadProvisioningHistory()
	{
	}
	
/******************************************************************************/
// Error Reporting methods
/******************************************************************************/

	// Returns the last error msg that was set
	function GetErrorMsg()
	{
		return $this->_strErrorMsg;
	}
	
	function IsOK()
	{
		return (bool)($this->_strErrorMsg === NULL);
	}
	
/******************************************************************************/
// Accessor Methods
/******************************************************************************/
	
	function __get($strProperty)
	{
		if (isset($this->_arrServiceRecord[$strProperty]))
		{
			return $this->_arrServiceRecord[$strProperty];
		}
		else
		{
			$this->_strErrorMsg = "$strProperty is not set in service object";
			throw new Exception("ERROR: $strProperty is not set in service object");
		}
	}
	
	function IsSet($strProperty)
	{
		return isset($this->_arrServiceRecord[$strProperty]);
	}
	
	// Returns a multi-dimensional array representing the Service and all its currently loaded associated components
	function GetArray()
	{
		//TODO!
		$this->_strErrorMsg = "GetArray() functionality has not been implemented yet";
		return FALSE;
		
		$arrService					= $this->_arrServiceRecord;
		$arrService['History']		= $this->_arrHistory;
		$arrService['Plans']		= $this->_arrPlanDetails;
		$arrService['Adjustments']	= $this->_arrAdjustments;
		
		return $arrService;
	}
	
/******************************************************************************/
// Mutator Methods
/******************************************************************************/
	function SetFNN($strFNN)
	{
		//TODO!
		$this->_strErrorMsg = "SetFNN() functionality has not been implemented yet";
		return FALSE;
	}
	
	function SetCostCentre($intCostCentre)
	{
		//TODO!
		$this->_strErrorMsg = "SetCostCentre() functionality has not been implemented yet";
		return FALSE;
	}
	
	
/******************************************************************************/
// Change Of Lessee related Methods
/******************************************************************************/
	
	function HasServiceMoveScheduled()
	{
		//TODO!
		$this->_strErrorMsg = "HasServiceMoveScheduled() functionality has not been implemented yet";
		return FALSE;
	}
	
/******************************************************************************/
// Static Methods
/******************************************************************************/
	
	// Returns a service object modelling a new service
	static function CreateNewService($strFNN, $intAccount, $strEffectiveFromDatetime, $bolIsIndial=NULL)
	{
		// TODO! Validate the FNN and work out what ServiceType it is for
		// TODO! Check that the FNN is available from $strEffectiveFromDatetime
		
		// Insert the Service Record and then build a ServiceObject of the correct derived type, passing
		// an array of defined details to it
		
		// Return an object of the appropriate derived class (ModuleLandLine, ModuleMobile, etc)
	}
	
	// Performs a change of lessee, or Service Move
	// This should probably be done at the Account Object Level, but I don't think it's too ilogical for a service
	// to manage the history of who owns it
	function MoveToAccount($intServiceId, $intAccountId, $bolIsLesseeChange)
	{
		//TODO!
		throw new Exception("MoveToAccount() functionality has not been implemented yet");
	}
	
}

class ModuleLandLine extends ModuleService
{
	private $_arrAddress;
	private	$_bolELB;
	
	// Builds a LandLine Service Object
	// can pass in nothing, or a Serivice Id (integer), or an associated array modelling the ServiceRecord
	function __construct($mixService=NULL)
	{
		parent::__construct($mixService);
	}
	
	// Does a clean load of whatever parts you specify
	function Load($intParts)
	{
		$bolSuccess = parrent::Load($intParts);
		
		if (!$bolSuccess)
		{
			return FALSE;
		}
		
		// Check if any LandLine extra details need to be loaded
		if (($intParts & SERVICE_PART_EXTRA_DETAILS) == SERVICE_PART_EXTRA_DETAILS)
		{
			$selServiceAddress = new StatementSelect("ServiceAddress", "*", "Service = <ServiceId>");
			if (($mixResult = $selServiceAddress->Execute(Array("ServiceId" => $this->_arrServiceRecord['Id']))) === FALSE)
			{
				// Database error
				$this->_strErrorMsg = "Unexpected database error when trying to retrieve Service Address record";
				return FALSE;
			}
			$this->_arrAddress = ($mixResult)? $selServiceAddress->Fetch() : NULL;
		}
		
		return TRUE;
	}
	
	
	// Returns the Service as a multi dimensional array
	function GetArray()
	{
		// Call base class GetArray
		//TODO!
		
		// Load LandLine specific extra details
		//TODO!
		
		// Return the array
	}
}

class ModuleMobile extends ModuleService
{
}

class ModuleADSL extends ModuleService
{
}

?>
