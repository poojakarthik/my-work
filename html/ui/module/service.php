<?php


// The ModuleService object models a service for a particular Account
// The service is identified by any of the Ids of the Service records which
// were used to model this service for the account that the records belong to
// The service is also identifiable with an FNN and Account Id



//TODO! 
// I was also thinking of moving most of the ModuleService::_LoadDetails() code into 
// the derived classes, so they can retrieve their extended details in the same query, 
// and maybe also the plan details.  It would certainly make sense to download the 
// ServiceType specific details, that way, when you create a ServiceMobile object, you can
// guarantee that all the required details have been downloaded
// Maybe keep the Plan Details separate, so that they have to be specifically 
// retrieved from the database.  Plan Details retrieval can be kept in the base class because
// it is exactly the same for all ServiceTypes
// OR MAYBE leave the ModuleService::_LoadDetails the way it is, and make a separate select query
// in the Derived class' _LoadDetails method to load the ServiceType specific details.
// All the required StatementSelect objects could be defined in the constructors to save time
// as the Refresh function also calls _LoadDetails.


//----------------------------------------------------------------------------//
// ModuleService
//----------------------------------------------------------------------------//
/**
 * ModuleService
 *
 * Models a service while used by a specific Account, that is currently defined in the database
 *
 * Models a service while used by a specific Account, that is currently defined in the database
 * This is an abstract class which is extended by the classes
 * ModuleLandLine, ModuleMobile, ModuleADSL, ModuleInbound
 * It is important to note that this does not model the entire history of a physical service's use in Flex,
 * But instead only the portion where the particular account owned the service
 *
 * @package	ui_app
 * @class	ModuleService
 * @abstract
 */
abstract class ModuleService
{
	protected $_strErrorMsg				= NULL;
	
	// You can't change the status unless everything else is saved
	protected $_bolHasUnsavedChanges	= NULL;
	
	protected $_strFNN					= NULL;
	protected $_intServiceType			= NULL;
	protected $_intCostCentre			= NULL;
	protected $_intAccount				= NULL;
	protected $_intAccountGroup			= NULL;
	protected $_bolForceInvoiceRender	= NULL;
	protected $_bolIndial100			= NULL;
	
	// This will always reflect $_arrServiceRecords[0]['ServiceId']
	protected $_intCurrentId			= NULL;
	
	// This will be set to the newest most Service Id related to this service (on this account)
	// It will be reset to NULL if Refresh() is called.  In which case $_intCurrentId should be up to date
	protected $_intNewId				= NULL;
	
	protected $_arrServiceRecords		= NULL;
	
	protected $_arrCurrentPlan			= NULL;
	protected $_arrFuturePlan			= NULL;
	protected $_bolPlanDetailsLoaded	= NULL;
	
	protected $_arrRateGroupDetails		= NULL;
	protected $_arrAdjustments			= NULL;
	protected $_arrRecurringAdjustments	= NULL;
	protected $_arrCostCentre			= NULL;
	
/******************************************************************************/
// Constructor
/******************************************************************************/

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the ModuleService class 
	 * 
	 * Constructor for the ModuleService class
	 * 
	 * @param	int		$intService		Id of the service that this object will model
	 * 									PRE: Id must reference a valid service in the Service table of the database.
	 * 									It does not have to reference the most recently added Service record modelling this
	 * 									FNN on this Account, but the object will logically model the use of this service
	 * 									record's FNN on this service record's Account
	 *
	 * @throws	Exception on error
	 *
	 * @return	void
	 * @method
	 */
	function __construct($intService)
	{
		// Service Id has been supplied
		// Retrieve its details
		$this->_intCurrentId = $intService;
		if (!$this->_LoadDetails())
		{
			throw new Exception($this->_strErrorMsg);
		}
	}

	//------------------------------------------------------------------------//
	// _LoadDetails
	//------------------------------------------------------------------------//
	/**
	 * _LoadDetails()
	 *
	 * Loads the details that are generic of all services 
	 * 
	 * Loads the details that are generic of all services
	 * Loads the basic Service Record details.  It does not load the Service's address details
	 * It will load in the Service Table Record relating to the most recently added record
	 * that models the FNN for the Account of the ServiceId currently stored in $this->_intCurrentId,
	 * _intCurrentId will then be changed to the Id of this service
	 * 
	 * @return	bool		TRUE on success, FALSE on failure
	 * @method
	 */
	protected function _LoadDetails()
	{
		// If a new Service Record is being referenced by $this->_intNewId then use it, else use the CurrentId
		$intService = ($this->_intNewId !== NULL)? $this->_intNewId : $this->_intCurrentId;
		
		$strWhere	= "	Account = (SELECT Account FROM Service WHERE Id = <ServiceId>)
						AND
						FNN = (SELECT FNN FROM Service WHERE Id = <ServiceId>)";
		$arrWhere	= Array("ServiceId" => $intService);
		
		$selServices	= new StatementSelect("Service", "*", $strWhere, "Id DESC");
		$mixResult		= $selServices->Execute($arrWhere);
		
		if ($mixResult === FALSE)
		{
			// Database error occured
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve details for service with Id: $intService";
			return FALSE;
		}
		elseif ($mixResult == 0)
		{
			// No records were returned
			$this->_strErrorMsg = "No Service records could be found in the database relating to Service Id: $intService";
			return FALSE;
		}
		
		// Save details from the most recent Service Record modelling this FNN for this account
		$arrRecord						= $selServices->Fetch();
		$this->_strFNN 					= $arrRecord['FNN'];
		$this->_intServiceType			= $arrRecord['ServiceType'];
		$this->_intCostCentre			= $arrRecord['CostCentre'];
		$this->_intAccount				= $arrRecord['Account'];
		$this->_intAccountGroup			= $arrRecord['AccountGroup'];
		$this->_bolForceInvoiceRender	= (bool)$arrRecord['ForceInvoiceRender'];
		$this->_bolIndial100			= (bool)$arrRecord['Indial100'];
		$this->_intCurrentId			= $arrRecord['Id'];
		$this->_intNewId				= NULL;
		
		// Build the ServiceRecords array
		$this->_arrServiceRecords = Array();
		do
		{
			$this->_arrServiceRecords[] = Array(
											"ServiceId"				=> $arrRecord['Id'],
											"CreatedOn"				=> $arrRecord['CreatedOn'],
											"ClosedOn"				=> $arrRecord['ClosedOn'],
											"CreatedBy"				=> $arrRecord['CreatedBy'],
											"ClosedBy"				=> $arrRecord['ClosedBy'],
											"NatureOfCreation"		=> $arrRecord['NatureOfCreation'],
											"NatureOfClosure"		=> $arrRecord['NatureOfClosure'],
											"Status"				=> $arrRecord['Status'],
											"LineStatus"				=> $arrRecord['LineStatus'],
											"LineStatusDate"			=> $arrRecord['LineStatusDate'],
											"PreselectionStatus"		=> $arrRecord['PreselectionStatus'],
											"PreselectionStatusDate"	=> $arrRecord['PreselectionStatusDate'],
											"NextOwner"				=> $arrRecord['NextOwner'],
											"LastOwner"				=> $arrRecord['LastOwner'],
											"CappedCharge"			=> $arrRecord['CappedCharge'],
											"UncappedCharge"		=> $arrRecord['UncappedCharge'],
											"Carrier"				=> $arrRecord['Carrier'],
											"CarrierPreselect"		=> $arrRecord['CarrierPreselect']
										);
			$arrRecord = $selServices->Fetch();
		} while ($arrRecord !== FALSE);
		
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// Refresh
	//------------------------------------------------------------------------//
	/**
	 * Refresh()
	 *
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested) 
	 * 
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested)
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function Refresh()
	{
		if ($this->_LoadDetails() === FALSE)
		{
			return FALSE;
		}
		
		// Refresh the plan details if they have already been loaded
		if ($this->_bolPlanDetailsLoaded)
		{
			if (!$this->LoadPlanDetails())
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// LoadPlanDetails
	//------------------------------------------------------------------------//
	/**
	 * LoadPlanDetails()
	 *
	 * Retrieves plan details from the database and stores them in the object 
	 * 
	 * Retrieves plan details from the database and stores them in the object
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function LoadPlanDetails()
	{
		// Get the Id of the most recent Service Record
		$intServiceId = $this->_intCurrentId;
		
		$strTables	= "	Service AS S 
						LEFT JOIN ServiceRatePlan AS SRP1 ON S.Id = SRP1.Service AND SRP1.Id = (SELECT SRP2.Id 
								FROM ServiceRatePlan AS SRP2 
								WHERE SRP2.Service = S.Id AND NOW() BETWEEN SRP2.StartDatetime AND SRP2.EndDatetime
								ORDER BY SRP2.CreatedOn DESC
								LIMIT 1
								)
						LEFT JOIN RatePlan AS RP1 ON SRP1.RatePlan = RP1.Id
						LEFT JOIN ServiceRatePlan AS SRP3 ON S.Id = SRP3.Service AND SRP3.Id = (SELECT SRP4.Id 
								FROM ServiceRatePlan AS SRP4 
								WHERE SRP4.Service = S.Id AND SRP4.StartDatetime BETWEEN NOW() AND SRP4.EndDatetime
								ORDER BY SRP4.CreatedOn DESC
								LIMIT 1
								)
						LEFT JOIN RatePlan AS RP2 ON SRP3.RatePlan = RP2.Id";
		$arrColumns	= Array("CurrentPlanId" 			=> "RP1.Id",
							"CurrentPlanName"			=> "RP1.Name",
							"FuturePlanId"				=> "RP2.Id",
							"FuturePlanName"			=> "RP2.Name",
							"FuturePlanStartDatetime"	=> "SRP3.StartDatetime");
		$strWhere	= "	S.Id = <ServiceId>";
		$arrWhere	= Array("ServiceId" => $intServiceId);
		$selPlans	= new StatementSelect($strTables, $arrColumns, $strWhere);
		
		$mixResult	= $selPlans->Execute($arrWhere);
		
		if ($mixResult === FALSE)
		{
			// Database error occured
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve Plan details for service with Id: $intServiceId";
			return FALSE;
		}
		
		$arrRecord = $selPlans->Fetch();
		$this->_arrCurrentPlan = NULL;
		if ($arrRecord['CurrentPlanId'] != NULL)
		{
			// The service has a current plan defined
			$this->_arrCurrentPlan = Array(
											"PlanId"	=> $arrRecord['CurrentPlanId'],
											"Name"		=> $arrRecord['CurrentPlanName']
											);
		}
		
		$this->_arrFuturePlan = NULL;
		if ($arrRecord['FuturePlanId'] != NULL)
		{
			// The service has a plan scheduled to start some time in the future
			$this->_arrFuturePlan = Array(
											"PlanId"		=> $arrRecord['FuturePlanId'],
											"Name"			=> $arrRecord['FuturePlanName'],
											"StartDatetime"	=> $arrRecord['FuturePlanStartDatetime']
											);
		}
		
		$this->_bolPlanDetailsLoaded = TRUE;
		
		return TRUE;
	}
	
	function LoadAdjustmentDetails()
	{
		//TODO!
		$this->_strErrorMsg = "LoadAdjustmentDetails() functionality has not been implemented yet";
		return FALSE;
	}
	
	function LoadProvisioningHistory()
	{
		//TODO!
		$this->_strErrorMsg = "LoadProvisioningHistory() functionality has not been implemented yet";
		return FALSE;
	}
	
/******************************************************************************/
// Error Reporting methods
/******************************************************************************/

	// Returns the last error msg that was set
	function GetErrorMsg()
	{
		return $this->_strErrorMsg;
	}
	
	function IsOk()
	{
		return (bool)($this->_strErrorMsg === NULL);
	}
	
/******************************************************************************/
// Accessor Methods
/******************************************************************************/
	
	// Returns a multi-dimensional array representing the Service and all its currently loaded associated components
	function GetServiceAsArray()
	{
		//TODO!
		$this->_strErrorMsg = "GetArray() functionality has not been implemented yet";
		return FALSE;
	}
	
	function GetId()
	{
		return $this->_intCurrentId;
	}
	
	function GetFNN()
	{
		return $this->_strFNN;
	}
	
	function GetAccount()
	{
		return $this->_intAccount;
	}

	function GetAccountGroup()
	{
		return $this->_intAccountGroup;
	}

	function GetCostCentre()
	{
		return $this->_intCostCentre;
	}
	
	function GetServiceType()
	{
		return $this->_intServiceType;
	}
	
	function GetCurrentPlan($bolForceReload=FALSE)
	{
		// Check if the plan details have been loaded, or if the user is forcing a reload
		if (!$this->_bolPlanDetailsLoaded || $bolForceReload)
		{
			if ($this->LoadPlanDetails() === FALSE)
			{
				// An error occurred
				return FALSE;
			}
		}
		return $this->_arrCurrentPlan;
	}

	function GetFuturePlan($bolForceReload=FALSE)
	{
		// Check if the plan details have been loaded, or if the user is forcing a reload
		if (!$this->_bolPlanDetailsLoaded || $bolForceReload)
		{
			if ($this->LoadPlanDetails() === FALSE)
			{
				// An error occurred
				return FALSE;
			}
		}
		return $this->_arrFuturePlan;
	}
	
	function GetStatus()
	{
		return $this->_arrServiceRecords[0]['Status'];
	}
	
	// Returns the Datetime that the status was last modified
	function GetStatusLastModified()
	{
		return ($this->_arrServiceRecords[0]['ClosedOn'] != NULL)? $this->_arrServiceRecords[0]['ClosedOn'] : $this->_arrServiceRecords[0]['CreatedOn'];
	}
	
	//------------------------------------------------------------------------//
	// GetLastEvent
	//------------------------------------------------------------------------//
	/**
	 * GetLastEvent()
	 *
	 * Returns details regarding the last Event to have taken place on the service 
	 * 
	 * Returns details regarding the last Event to have taken place on the service
	 *
	 * @return	array		$arrAction	['Status']		= SERVICE_ACTIVE|SERVICE_DISCONNECTED|SERVICE_ARCHIVED
	 * 									['Employee']	= Id of the employee who performed the action
	 * 									['TimeStamp']	= if SERVICE_ACTIVE then the CreatedOn datetime, else the ClosedOn datetime
	 * 									['Event']		= if SERVICE_ACTIVE then the last NatureOfCreation value or if there isn't one then
	 * 														it will set it to either SERVICE_CREATION_NEW or SERVICE_CREATION_ACTIVATED
	 * 														if it can work out which one it should be
	 * 													if Status != SERVICE_ACTIVE then the last NatureOfClosure value or if there isn't one
	 * 														then it will set it to either SERVICE_CLOSURE_DISCONNECTED or SERVICE_CLOSURE_ARCHIVED
	 * 														based on the Status
	 * @method
	 */
	function GetLastEvent()
	{
		$arrService	= $this->_arrServiceRecords[0];
		$arrAction	= Array("Status" => $arrService['Status']);
		
		if ($arrService['ClosedOn'] != NULL)
		{
			// The service is closed
			$arrAction['TimeStamp'] = $arrService['ClosedOn'];
			$arrAction['Employee']	= $arrService['ClosedBy'];

			if ($arrService['NatureOfClosure'] != NULL)
			{
				// The nature of closure is known
				$arrAction['Event'] = $arrService['NatureOfClosure'];
			}
			elseif ($arrService['Status'] == SERVICE_DISCONNECTED)
			{
				// Assume the nature of closure is SERVICE_CLOSURE_DISCONNECTED
				$arrAction['Event'] = SERVICE_CLOSURE_DISCONNECTED;
			}
			elseif ($arrService['Status'] == SERVICE_ARCHIVED)
			{
				// Assume the nature of closure is SERVICE_CLOSURE_ARCHIVED
				$arrAction['Event'] = SERVICE_CLOSURE_ARCHIVED;
			}
		}
		else
		{
			// The service is currently active
			$arrAction['TimeStamp'] = $arrService['CreatedOn'];
			$arrAction['Employee']	= $arrService['CreatedBy'];

			if ($arrService['NatureOfCreation'] != NULL)
			{
				// The nature of creation is known
				$arrAction['Event'] = $arrService['NatureOfCreation'];
			}
			else
			{
				// The nature is not known
				if (count($this->_arrServiceRecords) > 1)
				{
					// There is more than one service record modelling this service, therefore
					// it is safe to assume that the last action performed on this service was an activation
					$arrAction['Event'] = SERVICE_CREATION_ACTIVATED;
				}
				else
				{
					// There must only be one service record mofelling this service, therefore
					// it is safe to assume that this service has just been created
					$arrAction['Event'] = SERVICE_CREATION_NEW;
				}
			}
		}
		
		return $arrAction;
	}
	
	//------------------------------------------------------------------------//
	// GetHistory
	//------------------------------------------------------------------------//
	/**
	 * GetHistory()
	 *
	 * Returns an array detailing the history of the FNN/Account of the service that the object models 
	 * 
	 * Returns an array detailing the history of the FNN/Account of the service that the object models
	 *
	 * @return	mixed		FALSE	: An error occurred while building the history
	 *						Array	: $arrHistory[]	['ServiceId']			Id of the Service record that this particular Historical event is associated with
	 *												['IsCreationEvent']		TRUE if the action falls under the SERVICE_CREATION_ group of actions/events
	 *						 												FALSE if the action falls under the SERVICE_CLOSURE_ group of actions/events
	 *												['Event']				References constant from the ServiceCreation ConstantGroup if IsCreationAction == TRUE
	 *																		References constant from the ServiceClosure ConstantGroup if IsCreationAction == FALSE
	 *												['TimeStamp']			Time at which the event occured
	 * 												['Employee']			Id of the employee who instigated the event
	 *												['RelatedAccount']		Id of the other Account if the Event is a LESSEE_CHANGE, ACCOUNT_CHANGE, or REVERSAL
	 *									The most recent event is first and the oldest event is last
	 * @method
	 */
	function GetHistory()
	{
		return $this->_GetHistory($this->_arrServiceRecords);
	}

	//------------------------------------------------------------------------//
	// _GetHistory
	//------------------------------------------------------------------------//
	/**
	 * _GetHistory()
	 *
	 * Returns an array detailing the history of the FNN/Account of the service that the object models 
	 * 
	 * Returns an array detailing the history of the FNN/Account of the service that the object models
	 *
	 * @param	array		$arrServiceRecords	array of service records modelling an FNN 
	 * 											for a single account.  With the most recently used Service Record
	 * 											being the first in the array, and the earliest record being the last
	 * 
	 * @return	mixed		FALSE	: An error occurred while building the history
	 *						Array	: $arrHistory[]	['ServiceId']			Id of the Service record that this particular Historical event is associated with
	 *												['IsCreationEvent']		TRUE if the action falls under the SERVICE_CREATION_ group of actions/events
	 *						 												FALSE if the action falls under the SERVICE_CLOSURE_ group of actions/events
	 *												['Event']				References constant from the ServiceCreation ConstantGroup if IsCreationAction == TRUE
	 *																		References constant from the ServiceClosure ConstantGroup if IsCreationAction == FALSE
	 *												['TimeStamp']			Time at which the event occured
	 * 												['Employee']			Id of the employee who instigated the event
	 *												['RelatedAccount']		Id of the other Account if the Event is a LESSEE_CHANGE, ACCOUNT_CHANGE, or REVERSAL
	 *									The most recent event is first and the oldest event is last
	 * @method
	 * @static
	 */
	static private function _GetHistory($arrServiceRecords)
	{
		$arrHistory = Array();		
		// Iterate through the Service records stored in $this->_arrHistory
		foreach ($arrServiceRecords as $intIndex=>$arrServiceRecord)
		{
			if ($arrServiceRecord['ClosedOn'] != NULL)
			{
				$arrHistoryItem = Array("ServiceId"			=> $arrServiceRecord['ServiceId'],
										"IsCreationEvent"	=> FALSE,
										"TimeStamp"			=> $arrServiceRecord['ClosedOn'],
										"Employee"			=> $arrServiceRecord['ClosedBy']
										);
				if ($arrServiceRecord['NatureOfClosure'] != NULL)
				{
					// Nature of Closure is known
					$arrHistoryItem['Event'] = $arrServiceRecord['NatureOfClosure'];
				}
				else
				{
					// Nature of Closure is unknown.  Have a guess
					switch ($arrServiceRecord['Status'])
					{
						case SERVICE_DISCONNECTED:
							$arrHistoryItem['Event'] = SERVICE_CLOSURE_DISCONNECTED;
							break;
						case SERVICE_ARCHIVED:
							$arrHistoryItem['Event'] = SERVICE_CLOSURE_ARCHIVED;
							break;
						default:
							// This should never occur
							$arrHistoryItem['Event'] = NULL;
					}
				}
				
				// If the nature of closure relates to this Service being moved to another account
				// then store a reference to the Account which is related to the event
				if ($arrServiceRecord['NatureOfClosure'] == SERVICE_CLOSURE_LESSEE_CHANGED ||
					$arrServiceRecord['NatureOfClosure'] == SERVICE_CLOSURE_ACCOUNT_CHANGED ||
					$arrServiceRecord['NatureOfClosure'] == SERVICE_CLOSURE_LESSEE_CHANGE_REVERSED ||
					$arrServiceRecord['NatureOfClosure'] == SERVICE_CLOSURE_ACCOUNT_CHANGE_REVERSED)
				{
					// The event marks the moving of the service to another account
					// Include a reference to the Account
					$arrHistoryItem['RelatedAccount'] = $arrServiceRecord['NextOwner'];
				}
				
				// Add the history item to the history
				$arrHistory[] = $arrHistoryItem;
			}
			
			// If CreatedOn and ClosedOn are the same AND NatureOfCreation == SERVICE_CREATION_STATUS_CHANGED, 
			// then it means the Service Record was only added
			// so that the Status could be updated.  Don't bother storing details as to why this 
			// Service Record was created
			if ($arrServiceRecord['CreatedOn'] == $arrServiceRecord['ClosedOn'] && $arrServiceRecord['NatureOfCreation'] == SERVICE_CREATION_STATUS_CHANGED)
			{
				continue;
			}
			
			// Store the creation of this service record as a historical event
			$arrHistoryItem = Array("ServiceId"			=> $arrServiceRecord['ServiceId'],
									"IsCreationEvent"	=> TRUE,
									"TimeStamp"			=> $arrServiceRecord['CreatedOn'],
									"Employee"			=> $arrServiceRecord['CreatedBy']
									);
			if ($arrServiceRecord['NatureOfCreation'] != NULL)
			{
				// Nature of Creation is known
				$arrHistoryItem['Event'] = $arrServiceRecord['NatureOfCreation'];
			}
			else
			{
				// Nature of Creation is unknown.  Have an educated guess
				if ($intIndex == count($arrServiceRecords)-1)
				{
					// This is the earliest record.  It is safe to assume that the NatureOfCreation
					// of this service record is SERVICE_CREATION_NEW
					$arrHistoryItem['Event'] = SERVICE_CREATION_NEW;
				}
				else
				{
					// This is not the earliest record.  It is safe to assume that the NatureOfCreation
					// of this service record is SERVICE_CREATION_ACTIVATED
					$arrHistoryItem['Event'] = SERVICE_CREATION_ACTIVATED;
				}
			}
			
			// If the nature of creation relates to this Service having been moved from another account
			// then store a reference to the other Account
			if ($arrServiceRecord['NatureOfCreation'] == SERVICE_CREATION_LESSEE_CHANGED ||
				$arrServiceRecord['NatureOfCreation'] == SERVICE_CREATION_ACCOUNT_CHANGED ||
				$arrServiceRecord['NatureOfCreation'] == SERVICE_CREATION_LESSEE_CHANGE_REVERSED ||
				$arrServiceRecord['NatureOfCreation'] == SERVICE_CREATION_ACCOUNT_CHANGE_REVERSED)
			{
				// The event marks the moving of the service from another account
				// Include a reference to the account
				$arrHistoryItem['RelatedAccount'] = $arrServiceRecord['LastOwner'];
			}
			
			// Add the history item to the history
			$arrHistory[] = $arrHistoryItem;
		}
		
		return $arrHistory;
		
	}
	
	// Returns TRUE if the Account associated with this Service Object is the most recent(newest most) owner of this service (FNN)
	// returns NULL on Error 
	function IsNewestOwner()
	{
		$intFinalOwner = ModuleService::GetNewestOwner($this->_strFNN);
		if ($intFinalOwner === FALSE)
		{
			// An error occurred, and the message has already been set
			return NULL;
		}
		
		return ($this->_intAccount == $intFinalOwner) ? TRUE : FALSE;
	}
	
	
	// Returns the Account that most recently previously owned this service, before this 
	// account most recently owned this service
	// returns NULL if there was no previous owner
	// Note that if this account (account A) disconnects the service, and then the service is created on
	// another account (account B), then closed on that account, and activated again on the original account (A),
	// then this function will return account B as the previous owner
	/* If $bolReturnAllDetails == TRUE then the following details are returned relating to the PreviousOwner ServiceRecord
	 *	$arrPreviousOwner	['ServiceId']
	 *						['Account']
	 *						['AccountGroup']
	 *						['LastOwner']
	 */
	function GetPreviousOwner($bolReturnAllDetails=FALSE)
	{
		// Retrieve all Service Records that have a createdOn date less than that of the most recent Service Record
		// for this FNN/Account
		// Only include those records where CreatedOn <= ClosedOn
		// Only include records not belonging to this account
		$strFNNIndialRange = substr($this->_strFNN, 0, 8) . "__";
		
		$strWhere		= "(FNN = <FNN> OR (Indial100 = 1 AND FNN LIKE <FNNIndialRange>)) AND Account != <Account> AND CreatedOn < <LastCreatedOn> AND ClosedOn IS NOT NULL AND CreatedOn <= ClosedOn";
		$arrWhere		= array(
								"FNN"				=> $this->_strFNN, 
								"FNNIndialRange"	=> $strFNNIndialRange, 
								"Account"			=> $this->_intAccount,
								"LastCreatedOn"		=> $this->_arrServiceRecords[0]['CreatedOn']
								);
		$strOrderBy		= "Id DESC";
		$arrColumns		= array("Id", "Account", "AccountGroup", "CreatedOn", "NatureOfCreation", "ClosedOn", "NatureOfClosure", "LastOwner");
		$selServices	= new StatementSelect("Service", $arrColumns, $strWhere, $strOrderBy, "1");
		if ($selServices->Execute($arrWhere) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred";
			return FALSE;
		}
		if (($arrService = $selServices->Fetch()) === FALSE)
		{
			// There was no previous owner before this one
			return NULL;
		}
		if ($bolReturnAllDetails)
		{
			return array(	"ServiceId"		=> $arrService['Id'],
							"Id"			=> $arrService['Account'],
							"AccountGroup"	=> $arrService['AccountGroup'],
							"LastOwner"		=> $arrService['LastOwner']
						);
		}
		else
		{
			return $arrService['Account'];
		}
	}
	
	// Returns the datetime at which this Account most recently took ownership of the service
	// Note that this does not take into account when this Account relenquishes the Service
	// Although it might look like it could, this function can't return NULL
	function GetTimeOfAcquisition()
	{
		$strFNNIndialRange = substr($this->_strFNN, 0, 8) . "__";
		
		$strWhere		= "(FNN = <FNN> OR (Indial100 = 1 AND FNN LIKE <FNNIndialRange>)) AND Account != <Account> AND CreatedOn < <LastCreatedOn> AND ClosedOn IS NOT NULL AND CreatedOn <= ClosedOn";
		$arrWhere		= array(
								"FNN"				=> $this->_strFNN, 
								"FNNIndialRange"	=> $strFNNIndialRange, 
								"Account"			=> $this->_intAccount, 
								"LastCreatedOn"		=> $this->_arrServiceRecords[0]['CreatedOn']
								);
		$strOrderBy		= "Id DESC";
		$arrColumns		= array("Id", "Account", "CreatedOn", "NatureOfCreation", "ClosedOn", "NatureOfClosure");
		$selServices	= new StatementSelect("Service", $arrColumns, $strWhere, $strOrderBy, "1");
		if ($selServices->Execute($arrWhere) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred";
			return FALSE;
		}
		if (($arrService = $selServices->Fetch()) === FALSE)
		{
			// There was no previous owner before this one
			// Time of Acquisition is the oldest CreatedOn time for this service on this account (Time Of Creation)
			$strTimeOfCreation =  $this->_arrServiceRecords[count($this->_arrServiceRecords)-1]['CreatedOn'];
			return $strTimeOfCreation;
		}
		
		$strClosedOn = $arrService['ClosedOn'];
		
		// Search through $this->_arrServiceRecords to find the earliest CreatedOn which is >= $strClosedOn
		$strCreatedOn = NULL;
		foreach ($this->_arrServiceRecords as $arrServiceRecord)
		{
			if ($arrServiceRecord['CreatedOn'] >= $strClosedOn)
			{
				$strCreatedOn = $arrServiceRecord['CreatedOn'];
			}
			else
			{
				break;
			}
		}
		
		// At this stage $strCreatedOn should always be set
		return $strCreatedOn;
	}
	
	// Returns NULL if the NatureOfCreation at the time of Acquisition is unknown 
	// Returns the NatureOfCreation at the time of Acquisition if it is known
	// Returns FALSE on Error 
	function GetNatureOfAcquisition()
	{
		// Get the time of acquisition
		if (($strTimeOfAcquisition = $this->GetTimeOfAcquisition()) === FALSE)
		{
			return FALSE;
		}
		
		// Find the most recent Service Record where $strTimeOfAcquisition == CreatedOn TimeStamp
		// and NatureOfCreation == (SERVICE_CREATION_LESSEE_CHANGED || SERVICE_CREATION_ACCOUNT_CHANGED)
		$intNatureOfAcquisition = NULL;
		foreach ($this->_arrServiceRecords as $arrServiceRecord)
		{
			if ($arrServiceRecord['CreatedOn'] == $strTimeOfAcquisition && ($arrServiceRecord['NatureOfCreation'] == SERVICE_CREATION_LESSEE_CHANGED || $arrServiceRecord['NatureOfCreation'] == SERVICE_CREATION_ACCOUNT_CHANGED))
			{
				// Found it
				$intNatureOfAcquisition = $arrServiceRecord['NatureOfCreation'];
				break;
			}
		}
		
		return $intNatureOfAcquisition;
	}
	
	//------------------------------------------------------------------------//
	// GetEarliestAllowableMoveTime
	//------------------------------------------------------------------------//
	/**
	 * GetEarliestAllowableMoveTime()
	 *
	 * Returns the Earliest Allowable time that the Service can be moved from this Account to any other one   
	 * 
	 * Returns the Earliest Allowable time that the Service can be moved from this Account to any other one
	 * Keep in mind that the service should only be moved from this account, if this account is the Newest owner
	 * of the service, and the account's ownership of the service has come into effect (it doesn't check these facts)
	 *
	 * @return	mixed		FALSE	: An error occurred.
	 * 						string	: ISO Datetime defining the Earliest Allowable time that the Service
	 * 									can be moved from this Account to any other one.
	 * 									Note that this can be in the future
	 * 
	 * @method
	 */
	function GetEarliestAllowableMoveTime()
	{
		$strNow = GetCurrentISODateTime();
		
		// Find out when this service was most recently acquired by this Account
		$strEarliestPossibleMoveTime = $this->GetTimeOfAcquisition();
		
		if ($strEarliestPossibleMoveTime > $strNow)
		{
			// This account's acquisition of this service has not actually come into effect yet
			// It is safe to assume it hasn't yet been billed, because we can't bill into the future
			return $strEarliestPossibleMoveTime;
		}
		
		// Find out when this account was last billed
		$selLastBilled = new StatementSelect("Account", "LastBilled", "Id = <Account>");
		if ($selLastBilled->Execute(array("Account" => $this->_intAccount)) === FALSE)
		{
			// Database error occured
			$this->_strErrorMsg = "Unexpected Database error occurred";
			return FALSE;
		}
		if (($arrLastBilled = $selLastBilled->Fetch()) === FALSE)
		{
			// The Account record could not be found
			$this->_strErrorMsg = "Could not find the service's owner, Account: {$this->_intAccount}";
			return FALSE;
		}
		$strLastBilled = $arrLastBilled['LastBilled'];
		
		if ($strLastBilled === NULL)
		{
			// The account has not yet been billed
			return $strEarliestPossibleMoveTime;
		}
				
		// The LastBilled value is an ISO date.  Append 00:00:00 to it
		// I am relying on the assumption that no cdrs from the LastBilled date are actually included on the last bill
		// which is a pretty safe assumption
		$strLastBilled .= " 00:00:00";
		
		if ($strLastBilled > $strEarliestPossibleMoveTime)
		{
			// The account has been billed
			// You cannot  retroactivate a "Service Movement operation" before this time
			return $strLastBilled;
		}
		else
		{
			// The account has not been billed since it last took acquisition of this service
			return $strEarliestPossibleMoveTime;
		}
	}
	
	//------------------------------------------------------------------------//
	// CanReverseMove
	//------------------------------------------------------------------------//
	/**
	 * CanReverseMove()
	 *
	 * Checks whether a Service Move can be reversed   
	 * 
	 * Checks whether a Service Move can be reversed
	 * It assumes there is a previous owner (which can be found with ModuleService::GetPreviousOwner).  This function will not check this fact
	 * It also assumes this Service object is associated with the newest owning account of the service.  This function will not check this fact
	 * This is probably a useless function because if there is a reason why you can't reverse a move, 
	 * you want to be able to notify the user why, so all these checks should be played out in the AppTemplate Method
	 *
	 * @return	mixed		TRUE	: The Reverse Move can be performed
	 * 						FALSE	: The Reverse Move can no be performed
	 * 						NULL	: An error occurred
	 * 
	 * @method
	 */
	function CanReverseMove()
	{
		if (($strEarliestAllowableMoveTime = $this->GetEarliestAllowableMoveTime()) === FALSE)
		{
			// Error
			return NULL;
		}
		if (($strTimeOfAcquisition = $this->GetTimeOfAcquisition()) === FALSE)
		{
			// Error
			return NULL;
		}
		if (($intNatureOfAcquisition = $this->GetNatureOfAcquisition()) === FALSE)
		{
			// Error
			return NULL;
		}
		
		if ($intNatureOfAcquisition === NULL)
		{
			// The nature of Acquisition is unknown
			return FALSE;
		}
		
		if ($strEarliestAllowableMoveTime == $strTimeOfAcquisition && ($intNatureOfAcquisition == SERVICE_CREATION_LESSEE_CHANGED || $intNatureOfAcquisition == SERVICE_CREATION_ACCOUNT_CHANGED))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	//------------------------------------------------------------------------//
	// MoveToAccount
	//------------------------------------------------------------------------//
	/**
	 * MoveToAccount()
	 *
	 * Performs a "Change Of Lessee" or "Account Move" on the service
	 * 
	 * Performs a "Change Of Lessee" or "Account Move" on the service
	 * (Copies all data pertinent to the service and references the new Account/Service records)
	 * NOTE: THIS METHOD HAS TO BE RUN FROM WITHIN A TRANSACTION
	 * It is the responsibility of the calling code to manage this transaction including
	 * committing on success and rolling back on error
	 * On success, the Service object will be Refreshed so as to reflect its new owner and history
	 *
	 * @param	int		$intNewOwningAccount	Id of the Account which the service will be moving to
	 * @param	bool	$bolChangeOfLessee		TRUE if you want the move to be flagged as a "Change Of Lessee"
	 * 											FALSE if you want the move to be flagged as an "Account Move"
	 * @param	string	$strEffectiveDateTime	DateTime at which the Account should take ownership of the service
	 * 											PRE: this TimeStamp must be a valid TimeStamp for the move to take place
	 * @param	bool	$bolMoveCDRs			set to TRUE to renormalise unbilled CDRs so that the new account can own them, if their StartDatetime > EffectiveOn
	 * @param	bool	$bolMovePlan			set to TRUE to move the service's plan details to the new owning account
	 * @param	int		$intEmployee			Id of the employee performing the move operation
	 *
	 * @return	mix								int		: Id of the newest Service record created (referencing the new owner)
	 * 											bool	: FALSE on error (the error message can be retrieved using GetErrorMsg())
	 * @method
	 */
	function MoveToAccount($intNewOwningAccount, $bolChangeOfLessee, $strEffectiveDateTime, $bolMoveCDRs, $bolMovePlan, $intEmployee)
	{
		$strNow = GetCurrentISODateTime();
		
		if ($this->_bolHasUnsavedChanges)
		{
			// There are unsaved changes.  Save them now
			if ($this->SaveService() === FALSE)
			{
				return FALSE;
			}
		}
		
		if ($this->_arrServiceRecords[0]['ClosedOn'] !== NULL)
		{
			// The Service has to be "Active" to do an account move, and it is currently deactivated
			$this->_strErrorMsg = "Service must be active to perform ". (($bolChangeOfLessee)? "a Change of Lessee":"an Account Change");
			return TRUE;
		}
		
		// Close the current service record
		$intNatureOfClosure = ($bolChangeOfLessee)? SERVICE_CLOSURE_LESSEE_CHANGED : SERVICE_CLOSURE_ACCOUNT_CHANGED;
		$arrUpdateColumns = array(
									"Id"				=> $this->_intCurrentId,
									"ClosedOn"			=> NULL,
									"ClosedBy"			=> $intEmployee,
									"NatureOfClosure"	=> $intNatureOfClosure,
									"NextOwner"			=> $intNewOwningAccount,
									"Status"			=> SERVICE_DISCONNECTED
								);
		$updService = new StatementUpdateById("Service", $arrUpdateColumns);
		if ($this->_arrServiceRecords[0]['CreatedOn'] <= $strEffectiveDateTime)
		{
			// The Service's CreatedOn TimeStamp is less than the EffectiveDateTime
			// The ClosedOn details of this record can be set without making it an invalid Service Record (CreatedOn > ClosedOn)
			$arrUpdateColumns['ClosedOn'] = $strEffectiveDateTime;
			if ($updService->Execute($arrUpdateColumns) === FALSE)
			{
				$this->_strErrorMsg = "Unexpected database error occurred while trying to update Service record with Id: {$arrUpdateColumns['Id']}";
				return FALSE;
			}
			$intOldService = $this->_intCurrentId;
		}
		else
		{
			// The Service's CreatedOn TimeStamp is greater than the EffectiveDateTime
			// Setting the ClosedOn details of this record to EffectiveDateTime will render the record invalid (CreatedOn > ClosedOn)
			// Set the ClosedOn details to 'Now' and make a new Record where CreatedOn = ClosedOn = EffectiveDateTime
			$arrUpdateColumns['ClosedOn'] = $strNow;
			if ($updService->Execute($arrUpdateColumns) === FALSE)
			{
				$this->_strErrorMsg = "Unexpected database error occurred while trying to update Service record with Id: {$arrUpdateColumns['Id']}";
				return FALSE;
			}
			
			$arrServiceRecord = Array(	"FNN"					=> $this->_strFNN,
										"ServiceType"			=> $this->_intServiceType,
										"Indial100"				=> $this->_bolIndial100,
										"AccountGroup"			=> $this->_intAccountGroup,
										"Account"				=> $this->_intAccount,
										"CostCentre"			=> $this->_intCostCentre,
										"CreatedOn"				=> $strEffectiveDateTime,
										"CreatedBy"				=> $intEmployee,
										"NatureOfCreation"		=> SERVICE_CREATION_STATUS_CHANGED,
										"ClosedOn"				=> $strEffectiveDateTime,
										"ClosedBy"				=> $intEmployee,
										"NatureOfClosure"		=> $intNatureOfClosure,
										"Carrier"				=> $this->_arrServiceRecords[0]['Carrier'],
										"CarrierPreselect"		=> $this->_arrServiceRecords[0]['CarrierPreselect'],
										"ForceInvoiceRender"	=> $this->_bolForceInvoiceRender,
										"LastOwner"				=> $this->_arrServiceRecords[0]['LastOwner'],
										"NextOwner"				=> $intNewOwningAccount,
										"Status"				=> SERVICE_DISCONNECTED
									);
			
			$insService	= new StatementInsert("Service", $arrServiceRecord);
			$mixResult	= $insService->Execute($arrServiceRecord);
			if ($mixResult === FALSE)
			{
				$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert a new record into the Service table";
				return FALSE;
			}
			$this->_intNewId = $mixResult;
			
			// Copy the Plan Details
			if ($this->_CopyPlanDetails($this->_intNewId, $strEffectiveDateTime) === FALSE)
			{
				return FALSE;
			}
			
			// Copy the ServiceType specific details
			if ($this->_CopySupplementaryDetails($this->_intNewId, $this->_intAccount, $this->_intAccountGroup) === FALSE)
			{
				return FALSE;
			}
			
			$intOldService = $this->_intNewId;
		}
		
		// Get the details of the new account
		$selAccount = new StatementSelect("Account", "Id, AccountGroup", "Id = <AccountId>");
		if ($selAccount->Execute(array("AccountId" => $intNewOwningAccount)) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve details of the new owning Account with Id: $intNewOwningAccount";
			return FALSE;
		}
		if (($arrAccount = $selAccount->Fetch()) === FALSE)
		{
			$this->_strErrorMsg = "Can't find the new owning Account with Id: $intNewOwningAccount, in the database";
			return FALSE;
		}
		
		// Build the details for the new Service Record
		$arrNewService = array(
								"FNN"					=> $this->_strFNN,
								"ServiceType"			=> $this->_intServiceType,
								"Indial100"				=> $this->_bolIndial100,
								"AccountGroup"			=> $arrAccount['AccountGroup'],
								"Account"				=> $intNewOwningAccount,
								"CreatedOn"				=> $strEffectiveDateTime,
								"CreatedBy"				=> $intEmployee,
								"NatureOfCreation"		=> ($bolChangeOfLessee)? SERVICE_CREATION_LESSEE_CHANGED : SERVICE_CREATION_ACCOUNT_CHANGED,
								"Carrier"				=> $this->_arrServiceRecords[0]['Carrier'],
								"CarrierPreselect"		=> $this->_arrServiceRecords[0]['CarrierPreselect'],
								"ForceInvoiceRender"	=> $this->_bolForceInvoiceRender,
								"LastOwner"				=> $this->_intAccount,
								"Status"				=> SERVICE_ACTIVE
							);
		
		// Insert the new Service Record
		$insService	= new StatementInsert("Service", $arrNewService);
		$mixResult	= $insService->Execute($arrNewService);
		if (!$mixResult)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert the new service record into the service table";
			return FALSE;
		}
		
		// Store the new Service Record's Id
		$intNewService = $mixResult;
		
		// Copy the Plan Details
		if ($bolMovePlan)
		{
			if ($this->_CopyPlanDetails($intNewService, $strEffectiveDateTime) === FALSE)
			{
				return FALSE;
			}
		}
		
		// Renormalise Unbilled CDRs
		if ($bolMoveCDRs)
		{
			if ($this->_RenormaliseUnbilledCDRs($strEffectiveDateTime) === FALSE)
			{
				return FALSE;
			}
		}
		
		// Copy the ServiceType specific details
		if ($this->_CopySupplementaryDetails($intNewService, $intNewOwningAccount, $arrAccount['AccountGroup']) === FALSE)
		{
			return FALSE;
		}
		
		// The Account move is complete.  Refresh the object
		$this->Refresh();
		
		return $intNewService;
	}
	
	//------------------------------------------------------------------------//
	// ReverseMove
	//------------------------------------------------------------------------//
	/**
	 * ReverseMove()
	 *
	 * Reverses the last "move" operation
	 * 
	 * Reverses the last "move" operation
	 * (Copies all data pertinent to the service)
	 * NOTE: THIS METHOD HAS TO BE RUN FROM WITHIN A TRANSACTION
	 * It is the responsibility of the calling code to manage this transaction including
	 * committing on success and rolling back on error
	 * POST:	On success, the Service object will be refreshed and will now reference the new owner
	 *
	 * @param	int		$intEmployee			Id of the employee performing the move operation
	 *
	 * @return	mix								int		: Id of the newest Service record created (referencing the new owner)
	 * 											bool	: FALSE on error (the error message can be retrieved using GetErrorMsg())
	 * @method
	 */
	function ReverseMove($intEmployee)
	{
		if (($strEarliestAllowableMoveTime = $this->GetEarliestAllowableMoveTime()) === FALSE)
		{
			// Error
			return NULL;
		}
		if (($strTimeOfAcquisition = $this->GetTimeOfAcquisition()) === FALSE)
		{
			// Error
			return NULL;
		}
		if (($intNatureOfAcquisition = $this->GetNatureOfAcquisition()) === FALSE)
		{
			// Error
			return NULL;
		}
		
		if ($intNatureOfAcquisition != SERVICE_CREATION_LESSEE_CHANGED && $intNatureOfAcquisition != SERVICE_CREATION_ACCOUNT_CHANGED)
		{
			// The nature of Acquisition was not by means of a "Move" operation
			$this->_strErrorMsg = "Cannot establish the nature of acquisition";
			return FALSE;
		}
		
		if ($strEarliestAllowableMoveTime != $strTimeOfAcquisition)
		{
			// The "Move" can no longer be reversed
			$this->_strErrorMsg = "Cannot reverse as this account has been billed for this service";
			return FALSE;
		}
		
		// Get the details of the incoming owner
		if (($arrIncomingOwner = $this->GetPreviousOwner(TRUE)) === FALSE)
		{
			// Database error
			return FALSE;
		}
		if ($arrIncomingOwner === NULL)
		{
			$this->_strErrorMsg = "Cannot find the previous owner";
			return FALSE;
		}
		
		$intServiceId = $this->_intCurrentId;
		
		// Make the new service record for the IncomingOwner
		$arrNewService = array(
								"FNN"					=> $this->_strFNN,
								"ServiceType"			=> $this->_intServiceType,
								"Indial100"				=> $this->_bolIndial100,
								"AccountGroup"			=> $arrIncomingOwner['AccountGroup'],
								"Account"				=> $arrIncomingOwner['Id'],
								"CreatedOn"				=> $strTimeOfAcquisition,
								"CreatedBy"				=> $intEmployee,
								"NatureOfCreation"		=> ($intNatureOfAcquisition == SERVICE_CREATION_LESSEE_CHANGED)? SERVICE_CREATION_LESSEE_CHANGE_REVERSED : SERVICE_CREATION_ACCOUNT_CHANGE_REVERSED,
								"Carrier"				=> $this->_arrServiceRecords[0]['Carrier'],
								"CarrierPreselect"		=> $this->_arrServiceRecords[0]['CarrierPreselect'],
								"ForceInvoiceRender"	=> $this->_bolForceInvoiceRender,
								"LastOwner"				=> $arrIncomingOwner['LastOwner'],
								"Status"				=> SERVICE_ACTIVE
							);
		
		// Insert the new Service Record
		$insService	= new StatementInsert("Service", $arrNewService);
		$mixResult	= $insService->Execute($arrNewService);
		if (!$mixResult)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert the new service record into the service table";
			return FALSE;
		}
		
		// Store the new Service Record's Id
		$intNewService = $mixResult;
		
		// Copy the Plan Details
		if ($this->_CopyPlanDetails($intNewService, $strTimeOfAcquisition) === FALSE)
		{
			return FALSE;
		}
		
		// Renormalise Unbilled CDRs
		if ($this->_RenormaliseUnbilledCDRs() === FALSE)
		{
			return FALSE;
		}
		
		// Copy the ServiceType specific details
		if ($this->_CopySupplementaryDetails($intNewService, $arrIncomingOwner['Id'], $arrIncomingOwner['AccountGroup']) === FALSE)
		{
			return FALSE;
		}
		
		// Update all the Service records relating to the Outgoing owner that have CreatedOn >= $strTimeOfAcquisition
		// Find the Ids of all the service records that need to be closed
		$arrServiceRecordsToClose = array();
		foreach ($this->_arrServiceRecords as $arrServiceRecord)
		{
			if ($arrServiceRecord['CreatedOn'] >= $strTimeOfAcquisition)
			{
				// This record needs to be updated
				$arrServiceRecordsToClose[] = $arrServiceRecord['ServiceId'];
			}
		}
		
		if (count($arrServiceRecordsToClose) > 0)
		{
			// There are service records to update
			// Set the ClosedOn to 1 second before the TimeOfAcquisition
			$strClosedOn = date("Y-m-d H:i:s", strtotime($strTimeOfAcquisition) - 1);
			$intNatureOfClosure = ($intNatureOfAcquisition == SERVICE_CREATION_LESSEE_CHANGED)? SERVICE_CLOSURE_LESSEE_CHANGE_REVERSED : SERVICE_CLOSURE_ACCOUNT_CHANGE_REVERSED;
			
			$strWhere	= "Id IN (". implode(", ", $arrServiceRecordsToClose) .")";
			$arrUpdate	= array(
								"ClosedOn"			=> $strClosedOn,
								"ClosedBy"			=> $intEmployee,
								"NatureOfClosure"	=> $intNatureOfClosure,
								"Status"			=> SERVICE_ARCHIVED
								);
			$updOutgoingService = new StatementUpdate("Service", $strWhere, $arrUpdate);
			if ($updOutgoingService->Execute($arrUpdate, array()) === FALSE)
			{
				// Database error
				$this->_strErrorMsg = "Could not update the Service Records of the Outgoing Account";
				return FALSE;
			}
		}
		
		// The reverse has been successful
		// Refresh this Service object so that it now reflects the usage of the service, on the new Account (The account that the service was reversed to)
		$this->_intNewId = $intNewService;
		$this->Refresh();
		
		return $intNewService;
	}
	
	//------------------------------------------------------------------------//
	// _CopySupplementaryDetails
	//------------------------------------------------------------------------//
	/**
	 * _CopySupplementaryDetails()
	 *
	 * Copies ServiceType Specific Supplementary details of a service to a Destination service
	 * 
	 * Copies ServiceType Specific Supplementary details of a service to a Destination service
	 * All Supplementary details associated with the current service will be copied and 
	 * associated with the new service id and its Account
	 *
	 * @param	int		$intDestServiceId		Id of the Destination Service
	 * @param	int		$intDestAccountId		Id of the Destination Account
	 * @param	int		$intDestAccountGroup	Id of the Destination AccountGroup
	 * 
	 * @return	bool							TRUE on success, FALSE on Failure
	 * 		
	 * @method
	 * @protected
	 * @abstract
	 */
	abstract protected function _CopySupplementaryDetails($intDestServiceId, $intDestAccountId, $intDestAccountGroup);
	
	//------------------------------------------------------------------------//
	// _CopyPlanDetails
	//------------------------------------------------------------------------//
	/**
	 * _CopyPlanDetails()
	 *
	 * Makes a Copy of the plan details associated with the current Service Id, and associates them with $intDestServiceId
	 * 
	 * Makes a Copy of the plan details associated with the current Service Id, and associates them with $intDestServiceId
	 * TODO! Currently this retains the State of the LastChargedOn property.  There might be situations where you don't want this to be the case.  Such as ChangeOfLessee's and AccountChanges
	 *
	 * @param	int		$intDestServiceId					Id of the Destination Service
	 * @param	string	$strEarliestAllowableEndDateTime	optional, Defaults to NULL.  If NULL then
	 * 														All ServiceRatePlan and ServiceRateGroup records will be copied
	 * 														across.  If set to a DateTime, then only those records where
	 * 														EndDateTime > $strEarliestAllowableEndDateTime will be copied across
	 * 
	 * @return	bool										TRUE on success, FALSE on Failure
	 * @method
	 */
	protected function _CopyPlanDetails($intDestServiceId, $strEarliestAllowableEndDateTime=NULL)
	{
		$intNewServiceId			= $intDestServiceId;
		$intOldServiceId			= $this->_intCurrentId;
		$strEndDateTimeCondition	= "";
		if ($strEarliestAllowableEndDateTime !== NULL)
		{
			$strEndDateTimeCondition = "AND EndDatetime > '$strEarliestAllowableEndDateTime'";
		}
		
		// Copy all valid ServiceRatePlan records across from the old service
		$strCopyServiceRatePlanRecordsToNewService =	"INSERT INTO ServiceRatePlan (Id, Service, RatePlan, CreatedBy, CreatedOn, StartDatetime, EndDatetime, LastChargedOn, Active) ".
														"SELECT NULL, $intNewServiceId, RatePlan, CreatedBy, CreatedOn, StartDatetime, EndDatetime, LastChargedOn, Active ".
														"FROM ServiceRatePlan WHERE Service = $intOldServiceId $strEndDateTimeCondition AND StartDatetime < EndDatetime";
		$qryInsertServicePlanDetails = new Query();

		if ($qryInsertServicePlanDetails->Execute($strCopyServiceRatePlanRecordsToNewService) === FALSE)
		{
			// Inserting the records into the ServiceRatePlan table failed
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert records into the ServiceRatePlan table";
			return FALSE;
		}

		// Copy all valid ServiceRateGroup records across from the old service
		$strCopyServiceRateGroupRecordsToNewService =	"INSERT INTO ServiceRateGroup (Id, Service, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active) ".
														"SELECT NULL, $intNewServiceId, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active ".
														"FROM ServiceRateGroup WHERE Service = $intOldServiceId $strEndDateTimeCondition AND StartDatetime < EndDatetime";

		if ($qryInsertServicePlanDetails->Execute($strCopyServiceRateGroupRecordsToNewService) === FALSE)
		{
			// Inserting the records into the ServiceRateGroup table failed
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert records into the ServiceRateGroup table";
			return FALSE;
		}
		return TRUE;
	}
	
	// Returns TRUE on success and FALSE on failure
	// if $strEffectiveDateTime is not supplied then it will renormalise ALL CDRs, not just those that have StartDatetime > $strEffectiveDateTime
	protected function _RenormaliseUnbilledCDRs($strEffectiveDateTime=NULL)
	{
		$arrUpdate	= array("Status" => CDR_READY);
		
		$strFNN = ($this->_bolIndial100)? substr($this->_strFNN, 0, 8) . "__" : $this->_strFNN;
		
		$arrExemptCDRs = array(CDR_READY, CDR_INVOICED, CDR_TEMP_INVOICE);
		$strExemptCDRs = implode(", ", $arrExemptCDRs);
		
		if ($strEffectiveDateTime !== NULL)
		{
			$strWhere	= "FNN LIKE <FNN> AND StartDatetime >= <EffectiveFrom> AND Status NOT IN ($strExemptCDRs)";
		}
		else
		{
			$strWhere	= "FNN LIKE <FNN> AND Status NOT IN ($strExemptCDRs)";
		}
		$arrWhere	= array("FNN" => $strFNN, "EffectiveFrom" => $strEffectiveDateTime);
		$updCDR		= new StatementUpdate("CDR", $strWhere, $arrUpdate);
		if ($updCDR->Execute($arrUpdate, $arrWhere) === FALSE)
		{
			// An unexpected database error occurred
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to flag unbilled CDRs for renormalisation";
			return FALSE;
		}
		return TRUE;
	}
	
/******************************************************************************/
// Mutator Methods
/******************************************************************************/
	function SetFNN($strFNN)
	{
		$this->_bolHasUnsavedChanges = TRUE;
		$this->_strFNN = $strFNN;
		return TRUE;
	}
	
	function SetCostCentre($intCostCentre)
	{
		$this->_bolHasUnsavedChanges = TRUE;
		$this->_intCostCentre = $intCostCentre;
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// SaveService
	//------------------------------------------------------------------------//
	/**
	 * SaveService()
	 *
	 * Saves the changes made to the Service Record relating to this Service 
	 * 
	 * Saves the changes made to the Service Record relating to this Service
	 * Saves the details to the most recent Service Record modelling this Service for this account
	 * Note that this will never create a new record
	 * It assumes $this->_intCurrentId is the id of the most recent Service Record modelling this service for this account
	 * The user can change the FNN, CostCentre and the ForceInvoiceRender properties
	 * 
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function SaveService()
	{
		$arrUpdate = Array(	"FNN"					=> $this->_strFNN,
							"CostCentre"			=> $this->_intCostCentre,
							"ForceInvoiceRender"	=> $this->_bolForceInvoiceRender
							);
		$strWhere	= "Id = <ServiceId>";
		$updService	= new StatementUpdate("Service", $strWhere, $arrUpdate);
		$mixResult	= $updService->Execute($arrUpdate, Array("ServiceId" => $this->_intCurrentId));
		
		if ($mixResult === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to update the Service Record details for service with Id '{$this->_intCurrentId}'";
			return FALSE;
		}
		
		$this->_bolHasUnsavedChanges = FALSE;
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// ChangeStatus
	//------------------------------------------------------------------------//
	/**
	 * ChangeStatus()
	 *
	 * Changes the status of the Service (active/disconnected/archived) 
	 * 
	 * Changes the status of the Service (active/disconnected/archived)
	 * Before processing the StatusChange it will run SaveService() if there are 
	 * any currently unsaved changes made to the service.
	 * If a new Service record is required to model the change of status, then the plan details (current and future)
	 * of the last service record, will be copied and reference this new record.
	 * All these new details will be reloaded in the object, if it is necessary
	 * 
	 * @param	int		$intStatus		The new Service Status to set the service to (SERVICE_ACTIVE, SERVICE_DISCONNECTED, SERVICE_ARCHIVED)
	 * @param	string	$strTimeStamp	optional, TimeStamp at which the Status Change will be recorded as having been made
	 * 									This should not be in the past.  Defaults to NOW() 
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function ChangeStatus($intStatus, $strTimeStamp=NULL)
	{
		$strTimeStamp = ($strTimeStamp == NULL)? GetCurrentISODateTime() : $strTimeStamp;
		
		if ($this->_bolHasUnsavedChanges)
		{
			// There are unsaved changes.  Save them now
			if ($this->SaveService() === FALSE)
			{
				return FALSE;
			}
		}
		
		if ($intStatus == SERVICE_ACTIVE)
		{
			// The service is being activated
			if ($this->_Activate($strTimeStamp) === FALSE)
			{
				return FALSE;
			}
		}
		else
		{
			// The service is being deactivated (disconnected or archived)
			if ($this->_Deactivate($intStatus, $strTimeStamp) === FALSE)
			{
				return FALSE;
			}
		}
		
		// If a new Service record was made, then we have to make a copy the Plan details which references it
		// and we also have to make a copy of the ServiceType specific details which references it
		if ($this->_intNewId)
		{
			// Copy the Plan Details
			if ($this->_CopyPlanDetails($this->_intNewId, $strTimeStamp) === FALSE)
			{
				return FALSE;
			}
			
			// Copy the ServiceType specific details
			if ($this->_CopySupplementaryDetails($this->_intNewId, $this->_intAccount, $this->_intAccountGroup) === FALSE)
			{
				return FALSE;
			}
			
			// Refresh the object
			return $this->Refresh();
		} 
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _Activate
	//------------------------------------------------------------------------//
	/**
	 * _Activate()
	 *
	 * Activates the service 
	 * 
	 * Activates the service
	 * If the Service is scheduled to close at a future time then the current 
	 * Service record is updated to specify that it is active.
	 * If the Service has already closed then a new Service record will be added,
	 * based on the current Service record in the database
	 * It will not copy across unbilled CDRs, Charges, recurring charges or the plan details
	 * 
	 * @param	string	$strTimeStamp	TimeStamp at which the Activation will be recorded as having been made.
	 * 									This should not be in the past 
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	private function _Activate($strTimeStamp)
	{
		$intService		= $this->_intCurrentId;
		$strFNN			= $this->_strFNN;
		$bolIsIndial	= $this->_bolIndial100;
		
		// The most recent values for CreatedOn and ClosedOn
		$strCreatedOn	= $this->_arrServiceRecords[0]['CreatedOn'];
		$strClosedOn	= $this->_arrServiceRecords[0]['ClosedOn'];
		
		if ($strClosedOn === NULL)
		{
			$this->_strErrorMsg = "This service is already active";
			return FALSE;
		}
		
		if ($strClosedOn < $strCreatedOn)
		{
			$strCreatedOn	= OutputMask()->LongDateAndTime($strCreatedOn);
			$strClosedOn	= OutputMask()->LongDateAndTime($strClosedOn);
			//TODO! I think this will have to be changed to check into the nature of closure
			$this->_strErrorMsg = "This service cannot be activated as its CreatedOn TimeStamp ($strCreatedOn) is greater than its ClosedOn TimeStamp ($strClosedOn) signifying that it was never actually used by this account";
			return FALSE;
		}
		
		// Check if the FNN is currently in use
		$arrWhere					= Array();
		$arrWhere['FNN']			= ($bolIsIndial) ? substr($strFNN, 0, -2) . "__" : $strFNN; 
		$arrWhere['IndialRange']	= substr($strFNN, 0, -2) . "__";
		$arrWhere['ClosedOn']		= $strClosedOn;
		
		$selFNNInUse = new StatementSelect("Service", "Id", "(FNN LIKE <FNN> OR (FNN LIKE <IndialRange> AND Indial100 = 1)) AND (ClosedOn IS NULL OR (ClosedOn >= CreatedOn AND NOW() <= ClosedOn))");
		if ($selFNNInUse->Execute($arrWhere))
		{
			// At least one record was returned, which means the FNN is currently in use by an active service
			if ($bolIsIndial)
			{
				$this->_strErrorMsg = "Cannot activate this service as at least one of the FNNs in the Indial Range is currently being used by another service.  The other service must be disconnected or archived before this service can be activated.";
			}
			$this->_strErrorMsg = "Cannot activate this service as the FNN: $strFNN is currently being used by another service.  The other service must be disconnected or archived before this service can be activated";
			return FALSE;
		}
		
		// If the Service hasn't closed yet, then just update the ClosedOn and Status properties
		// You do not need to create a new record, or renormalise CDRs
		if ($strClosedOn >= $strTimeStamp)
		{
			// Just update the record
			$arrUpdate = Array	(	"Id"				=> $intService,
									"ClosedOn"			=> NULL,
									"Status"			=> SERVICE_ACTIVE,
									"NatureOfClosure"	=> NULL
								);
			$updService = new StatementUpdateById("Service", $arrUpdate);
			if ($updService->Execute($arrUpdate) === FALSE)
			{
				// There was an error while trying to activate the service
				$this->_strErrorMsg = "Unexpected Database error occurred while trying to activate the Service with Id '{$this->_intCurrentId}'";
				return FALSE;
			}
			
			// Update the corresponding record in the _arrServiceRecords array
			$this->_arrServiceRecords[0]['ClosedOn']		= NULL;
			$this->_arrServiceRecords[0]['Status']			= SERVICE_ACTIVE;
			$this->_arrServiceRecords[0]['NatureOfClosure']	= NULL;
			
			// Service was activated successfully
			return TRUE;
		}
		
		// Activating the service requires the creation of a new service record, based on the old service record
		$intOldServiceId	= $intService;
		$intUserId			= AuthenticatedUser()->_arrUser['Id'];
		
		$arrServiceRecordData = Array(	"FNN"						=> $this->_strFNN,
										"ServiceType"				=> $this->_intServiceType,
										"Indial100"					=> $this->_bolIndial100,
										"AccountGroup"				=> $this->_intAccountGroup,
										"Account"					=> $this->_intAccount,
										"CostCentre"				=> $this->_intCostCentre,
										"CreatedOn"					=> $strTimeStamp,
										"CreatedBy"					=> $intUserId,
										"NatureOfCreation"			=> SERVICE_CREATION_ACTIVATED,
										"Carrier"					=> $this->_arrServiceRecords[0]['Carrier'],
										"CarrierPreselect"			=> $this->_arrServiceRecords[0]['CarrierPreselect'],
										"ForceInvoiceRender"		=> $this->_bolForceInvoiceRender,
										"LastOwner"					=> $this->_arrServiceRecords[0]['LastOwner'],
										"NextOwner"					=> $this->_arrServiceRecords[0]['NextOwner'],
										"Status"					=> SERVICE_ACTIVE
										);
		
		$insService	= new StatementInsert("Service", $arrServiceRecordData);
		$mixResult	= $insService->Execute($arrServiceRecordData);
		if ($mixResult === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert a new record into the Service table";
			return FALSE;
		}
		
		// Store the Id of the new Service Record
		$this->_intNewId = $mixResult;

		// Activating the service was successfull
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _Deactivate
	//------------------------------------------------------------------------//
	/**
	 * _Deactivate()
	 *
	 * Deactivate the service (Sets status to DISCONNECTED or ARCHIVED)
	 * 
	 * Deactivate the service (Sets status to DISCONNECTED or ARCHIVED)
	 * This will create a new Service record, if the status is being changed between Disconnected and Archived 
	 * It will not copy across unbilled CDRs, Charges, recurring charges or the plan details
	 * 
	 * @param	int		$intStatus		Status to change the service to (SERVICE_DISCONNECTED or SERVICE_ARCHIVED)
	 * @param	string	$strTimeStamp	TimeStamp at which the Activation will be recorded as having been made.
	 * 									This should not be in the past 
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	private function _Deactivate($intStatus, $strTimeStamp)
	{
		$intService		= $this->_intCurrentId;
		
		// The most recent values for CreatedOn and ClosedOn
		$strCreatedOn	= $this->_arrServiceRecords[0]['CreatedOn'];
		$strClosedOn	= $this->_arrServiceRecords[0]['ClosedOn'];

		// Work out the nature of the closure
		$intNatureOfClosure	= ($intStatus == SERVICE_DISCONNECTED)? SERVICE_CLOSURE_DISCONNECTED : SERVICE_CLOSURE_ARCHIVED;
		$intUserId			= AuthenticatedUser()->_arrUser['Id'];

		if ($strClosedOn === NULL)
		{
			// A ClosedOn TimeStamp has not been set for the current Service record
			// Just update the record
			$arrUpdate = Array	(	"Id"				=> $intService,
									"ClosedOn"			=> $strTimeStamp,
									"ClosedBy"			=> $intUserId,
									"Status"			=> $intStatus,
									"NatureOfClosure"	=> $intNatureOfClosure
								);
			$updService = new StatementUpdateById("Service", $arrUpdate);
			if ($updService->Execute($arrUpdate) === FALSE)
			{
				// There was an error while trying to activate the service
				$this->_strErrorMsg = "Unexpected Database error occurred while trying to ". (($intStatus == SERVICE_DISCONNECTED)? "Disconnect" : "Archive") ." the Service with Id '{$this->_intCurrentId}'";
				return FALSE;
			}
			
			// Update the corresponding record in the _arrServiceRecords array
			$this->_arrServiceRecords[0]['ClosedOn']		= $strTimeStamp;
			$this->_arrServiceRecords[0]['ClosedBy']		= $intUserId;
			$this->_arrServiceRecords[0]['Status']			= $intStatus;
			$this->_arrServiceRecords[0]['NatureOfClosure']	= $intNatureOfClosure;
			
			// Service was deactivated successfully
			return TRUE;
		}
		
		if ($strClosedOn < $strCreatedOn)
		{
			$strCreatedOn	= OutputMask()->LongDateAndTime($strCreatedOn);
			$strClosedOn	= OutputMask()->LongDateAndTime($strClosedOn);
			//TODO! I think this will have to be changed to check into the nature of closure
			$this->_strErrorMsg = "This service cannot be ". GetConstantDescription($intStatus, "Service") ." as its CreatedOn TimeStamp ($strCreatedOn) is greater than its ClosedOn TimeStamp ($strClosedOn) signifying that it was never actually used by this account";
			return FALSE;
		}
		
		// In order to "deactivate" this service, a new Service Record must be added
		// In which CreatedOn and ClosedOn == NOW()
		$intOldServiceId = $intService;
		$arrServiceRecordData = Array(	"FNN"						=> $this->_strFNN,
										"ServiceType"				=> $this->_intServiceType,
										"Indial100"					=> $this->_bolIndial100,
										"AccountGroup"				=> $this->_intAccountGroup,
										"Account"					=> $this->_intAccount,
										"CostCentre"				=> $this->_intCostCentre,
										"CreatedOn"					=> $strTimeStamp,
										"CreatedBy"					=> $intUserId,
										"NatureOfCreation"			=> SERVICE_CREATION_STATUS_CHANGED,
										"ClosedOn"					=> $strTimeStamp,
										"ClosedBy"					=> $intUserId,
										"NatureOfClosure"			=> $intNatureOfClosure,
										"Carrier"					=> $this->_arrServiceRecords[0]['Carrier'],
										"CarrierPreselect"			=> $this->_arrServiceRecords[0]['CarrierPreselect'],
										"ForceInvoiceRender"		=> $this->_bolForceInvoiceRender,
										"LastOwner"					=> $this->_arrServiceRecords[0]['LastOwner'],
										"NextOwner"					=> $this->_arrServiceRecords[0]['NextOwner'],
										"Status"					=> $intStatus
										);
		
		$insService	= new StatementInsert("Service", $arrServiceRecordData);
		$mixResult	= $insService->Execute($arrServiceRecordData);
		if ($mixResult === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert a new record into the Service table";
			return FALSE;
		}
		
		// Store the Id of the new Service Record
		$this->_intNewId = $mixResult;

		// Deactivating the service was successfull
		return TRUE;
		
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
	// (factory)
	static function CreateNewService($strFNN, $intAccount, $strEffectiveFromDatetime, $bolIsIndial=NULL)
	{
		// TODO! Validate the FNN and work out what ServiceType it is for
		// TODO! Check that the FNN is available from $strEffectiveFromDatetime
		
		// Insert the Service Record and then build a ServiceObject of the correct derived type, passing
		// an array of defined details to it
		
		// Return an object of the appropriate derived class (ModuleLandLine, ModuleMobile, etc)
	}
	
	//------------------------------------------------------------------------//
	// GetServiceById
	//------------------------------------------------------------------------//
	/**
	 * GetServiceById()
	 *
	 * Creates and returns the ServiceType specific ModuleService derived object modelling the service specified
	 * 
	 * Creates and returns the ServiceType specific ModuleService derived object modelling the service specified
	 * (Factory method for creating Service objects specific to the service's ServiceType)
	 * 
	 * @param	int		$intServiceId		Id of the service to create an object of.  Must reference a valid service
	 * 										record in the database
	 * @param	int		$intServiceType		optional, the proper ServiceType for the service.  If not supplied, then
	 * 										it is calculated, but this requires an extra interaction with the database
	 *
	 * @return	mixed						FALSE	: Error has occurred
	 * 										NULL	: The Service could not be found in the database
	 * 										object	: one of either ModuleLandLine, ModuleMobile, ModuleADSL or ModuleInbound
	 * 													modelling the service
	 * @method
	 * @static
	 */
	static function GetServiceById($intServiceId, $intServiceType=NULL)
	{ 
		static $selServiceType;
		if ($intServiceType === NULL)
		{
			// The ServiceType is not known, find out what it is
			if (!isset($selServiceType))
			{
				// Create the prepared statement
				$selServiceType = new StatementSelect("Service", "ServiceType", "Id = <ServiceId>");
			}
			$mixResult = $selServiceType->Execute(Array("ServiceId"=>$intServiceId));
			if ($mixResult === FALSE)
			{
				// An error occurred
				return FALSE;
			}
			if ($mixResult == 0)
			{
				// The service could not be found
				return NULL;
			}
			$arrRecord = $selServiceType->Fetch();
			$intServiceType = $arrRecord['ServiceType'];
		}
		
		// Instanciate the object
		try
		{
			switch ($intServiceType)
			{
				case SERVICE_TYPE_LAND_LINE:
					$objService = new ModuleLandLine($intServiceId);
					break;
					
				case SERVICE_TYPE_MOBILE:
					$objService = new ModuleMobile($intServiceId);
					break;
					
				case SERVICE_TYPE_ADSL:
					$objService = new ModuleADSL($intServiceId);
					break;
					
				case SERVICE_TYPE_INBOUND:
					$objService = new ModuleInbound($intServiceId);
					break;
					
				default;
					return FALSE;
			}
		}
		catch (Exception $e)
		{
			return FALSE;
		}
		
		
		return $objService;
	}

	//------------------------------------------------------------------------//
	// GetServiceByFNN
	//------------------------------------------------------------------------//
	/**
	 * GetServiceByFNN()
	 *
	 * Creates and returns the ServiceType specific ModuleService derived object modelling the service specified
	 * 
	 * Creates and returns the ServiceType specific ModuleService derived object modelling the service specified
	 * (Factory method for creating Service objects specific to the service's ServiceType)
	 * The Service object returned will model the Service as used by the Account specified,
	 * If an Account is not specified, then it will model the Service as used by the most recent owning account of the service
	 * If the FNN suplied is part of an Indial100 Service then the Service object returned will reference the primary
	 * number of the Indial100 range
	 * 
	 * @param	string	$strFNN				FNN of the Service
	 * @param	int		$intAccount			optional, defaults to NULL.  If supplied then the returned object will model
	 * 										the service as used by the Account.  If not supplied then the returned object
	 * 										will model the service as used by the current owning account (the newest owner)
	 * 										If specified, but the Account never owned the service then the function will 
	 * 										return NULL
	 *
	 * @return	mixed						FALSE	: Error has occurred
	 * 										NULL	: The desired Service could not be found in the database
	 * 										object	: one of either ModuleLandLine, ModuleMobile, ModuleADSL or ModuleInbound
	 * 													modelling the service
	 * @method
	 * @static
	 */
	static function GetServiceByFNN($strFNN, $intAccount=NULL)
	{ 
		$strFNNIndial			= substr($strFNN, 0, 8). "__";
		$strAccountCondition	= ($intAccount)? " AND Account = <Account>" : "";
		$strWhere				= "(FNN = <FNN> OR (Indial100 = 1 AND FNN LIKE <FNNIndial>)) $strAccountCondition AND (ClosedOn IS NULL OR ClosedOn >= CreatedOn)";
		$arrWhere				= array(
										"FNN"		=> $strFNN,
										"FNNIndial"	=> $strFNNIndial,
										"Account"	=> $intAccount
										);
		$selService = new StatementSelect("Service", "Id, ServiceType", $strWhere, "Id DESC", "1");
		if ($selService->Execute($arrWhere) === FALSE)
		{
			// An error occurred
			return FALSE;
		}
		if (($arrService = $selService->Fetch()) === FALSE)
		{
			// Could not find the FNN
			return NULL;
		}
		
		return ModuleService::GetServiceById($arrService['Id'], $arrService['ServiceType']);
	}
	
	// Returns the history for a service which is described as an indexed array of Service Records
	// Ordered in descending order of Id
	static function GetHistoryForAnonymous($arrServiceRecords)
	{
		return ModuleService::_GetHistory($arrServiceRecords);
	}
	
	// Returns the Account Id of the most recent(newest most) owner of this service (FNN)
	static function GetNewestOwner($strFNN)
	{
		// Account for $strFNN being within an Indial100 range
		$strFNNIndialRange = substr($strFNN, 0, 8) . "__";
		
		// Find the Service Record modelling this service with the highest Id		
		$strWhere		= "(FNN = <FNN> OR (Indial100 = 1 AND FNN LIKE <FNNIndialRange>)) AND (ClosedOn IS NULL OR CreatedOn < ClosedOn)";
		$arrWhere		= array("FNN" => $strFNN, "FNNIndialRange" => $strFNNIndialRange);
		$strOrderBy		= "Id DESC";
		$selFinalOwner	= new StatementSelect("Service", "Account", $strWhere, $strOrderBy, "1");
		if ($selFinalOwner->Execute($arrWhere) === FALSE)
		{
			return FALSE;
		}
		if (($arrFinalOwner = $selFinalOwner->Fetch()) === FALSE)
		{
			return FALSE;
		}
		
		return $arrFinalOwner['Account'];
	}

	// Returns the Account that owned the Service (FNN) at time $strDateTime
	// PRE: $strDateTime is a valid Date or DateTime value in ISO format
	// Returns FALSE on ERROR, NULL if there was no owner, or AccountId of the owner if it could be found
	static function GetOwnerAtTime($strFNN, $strDateTime)
	{
		// Account for $strFNN being within an Indial100 range
		$strFNNIndialRange = substr($strFNN, 0, 8) . "__";
		
		// Find the Service Record modelling this service with the highest Id, which was active at $strDateTime
		$strWhere	= "(FNN = <FNN> OR (Indial100 = 1 AND FNN LIKE <FNNIndialRange>)) AND ((ClosedOn IS NULL AND <Time> >= CreatedOn) OR (<Time> BETWEEN CreatedOn AND ClosedOn))";
		$arrWhere	= array("FNN" => $strFNN, "FNNIndialRange" => $strFNNIndialRange, "Time" => $strDateTime);
		$selOwner	= new StatementSelect("Service", "Account", $strWhere, "Id DESC", "1");
		if ($selOwner->Execute($arrWhere) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred";
			return FALSE;
		}
		if (($arrOwner = $selOwner->Fetch()) === FALSE)
		{
			// There was no owner at this point in time
			return NULL;
		}
		
		return $arrOwner['Account'];
	}

	
}

//----------------------------------------------------------------------------//
// ModuleLandLine
//----------------------------------------------------------------------------//
/**
 * ModuleLandLine
 *
 * Models a LandLine service that is currently defined in the database
 *
 * Models a LandLine service that is currently defined in the database
 *
 * @package	ui_app
 * @class	ModuleLandLine
 * @extends	ModuleService
 */
class ModuleLandLine extends ModuleService
{
	// Stores the address associated with this service
	private $_arrAddress		= NULL;
	
	// Used to keep track of whether or not the Service's address has been loaded
	private $_bolAddressLoaded	= NULL;
	
	// TRUE if the LandLine service has Extension Level Billing turned on
	// FALSE if it is not turned on
	private	$_bolELB			= NULL;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the ModuleLandLine class 
	 * 
	 * Constructor for the ModuleLandLine class
	 * 
	 * @param	int		$intService		Id of the LandLine service that this object will model
	 * 									PRE: Id must reference a valid landline service in the Service table of the database.
	 * 									It does not have to reference the most recently added Service record modelling this
	 * 									FNN on this Account, but the object will logically model the use of this service
	 * 									record's FNN on this service record's Account
	 *
	 * @throws	Exception on error or if $intService doesn't reference a LandLine service in the database
	 *
	 * @return	void
	 * @method
	 */
	function __construct($intService)
	{
		parent::__construct($intService);
		
		if ($this->_intServiceType != SERVICE_TYPE_LAND_LINE)
		{
			throw new Exception("Service with Id '{$this->_intCurrentId}' is not a landline service");
		}
	}
	
	//------------------------------------------------------------------------//
	// _LoadDetails
	//------------------------------------------------------------------------//
	/**
	 * _LoadDetails()
	 *
	 * Loads the details for the LandLine 
	 * 
	 * Loads the details for the LandLine
	 * Loads the basic Service Record details, and works out if ELB is turned on if the service is an Indial100
	 * It does not load the Service's address details
	 * 
	 * @return	bool		TRUE on success, FALSE on failure
	 * @method
	 */
	protected function _LoadDetails()
	{
		if (parent::_LoadDetails() === FALSE)
		{
			return FALSE;
		}
		
		// Check if Extension Level Billing is turned on or off
		// ELB is considered to be turned on if the Service is an Indial 100 and has records in the ServiceExtension table
		if ($this->_bolIndial100)
		{
			$selELB		= new StatementSelect("ServiceExtension", "Id", "Service = <ServiceId>", "Id", "1");
			$mixResult	= $selELB->Execute(Array("ServiceId" => $this->_intCurrentId));
			
			if ($mixResult === FALSE)
			{
				// Database error occured
				$this->_strErrorMsg = "Unexpected Database error occurred while trying to work out if the service '{$this->_intCurrentId}' has Extension Level Billing";
				return FALSE;
			}
			$this->_bolELB = ($mixResult == 1)? TRUE : FALSE;
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// ChangeStatus
	//------------------------------------------------------------------------//
	/**
	 * ChangeStatus()
	 *
	 * Changes the status of the Landline (active/disconnected/archived) 
	 * 
	 * Changes the status of the Landline (active/disconnected/archived)
	 * Before processing the StatusChange it will run SaveService() if there are 
	 * any currently unsaved changes made to the service.
	 * If a new Service record is required to model the change of status, then a new ServiceAddress record
	 * will also be made (if the service currently has a ServiceAddress) and new records will be added to the
	 * ServiceExtension table if ELB is turned on.  All these new details will be reloaded in the object, if it is
	 * necessary
	 * 
	 * @param	int		$intStatus		The new Service Status to set the service to (SERVICE_ACTIVE, SERVICE_DISCONNECTED, SERVICE_ARCHIVED)
	 * @param	string	$strTimeStamp	optional, TimeStamp at which the Status Change will be recorded as having been made
	 * 									This should not be in the past.  Defaults to NOW() 
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function ChangeStatus($intStatus, $strTimeStamp=NULL)
	{
		$strTimeStamp = ($strTimeStamp == NULL)? GetCurrentISODateTime() : $strTimeStamp;
		
		if (parent::ChangeStatus($intStatus, $strTimeStamp) === FALSE)
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Refresh
	//------------------------------------------------------------------------//
	/**
	 * Refresh()
	 *
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested) 
	 * 
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested)
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function Refresh()
	{
		if (parent::Refresh() === FALSE)
		{
			return FALSE;
		}
		
		// Reload the ServiceAddress record, if it has previously been loaded
		if ($this->_bolAddressLoaded)
		{
			$mixResult = GetAddress(TRUE);
			return (bool)($mixResult !== FALSE);
		}
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// GetAddress
	//------------------------------------------------------------------------//
	/**
	 * GetAddress()
	 *
	 * Retrieves the LandLine's current ServiceAddress record, if there is one 
	 * 
	 * Retrieves the LandLine's current ServiceAddress record, if there is one
	 *
	 * @param	bool	$bolForceRefresh	optional, defaults to FALSE.  If TRUE then the ServiceAddress
	 * 										record will be retrieved from the database.  If FALSE then
	 * 										the method will only retrieve the Address from the database, if it
	 * 										isn't already being stored in the object, from a previous call to this method
	 *
	 * @return	mixed						FALSE	: Error occurred
	 * 										NULL	: The service doesn't have an associated ServiceAddress record
	 * 										Array	: The ServiceAddress record
	 * @method
	 */
	function GetAddress($bolForceRefresh=FALSE)
	{
		if ($this->_bolAddressLoaded && !$bolForceRefresh)
		{
			// The address has already been loaded, and we are not forcing a refresh
			return $this->_arrAddress;
		}
		
		$selAddress	= new StatementSelect("ServiceAddress", "*", "Service = <ServiceId>", "Id DESC", "1");
		$mixResult	= $selAddress->Execute(Array("ServiceId" => $this->_intCurrentId));
		if ($mixResult === FALSE)
		{
			// Database error occured
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve the ServiceAddress record";
			return FALSE;
		}
		if ($mixResult == 0)
		{
			// The service doesn't have a ServiceAddress record
			return NULL;
		}
		
		// Save the address record
		$this->_arrAddress			= $selAddress->Fetch();
		$this->_bolAddressLoaded	= TRUE;
		return $this->_arrAddress;
	}
	
	
	//------------------------------------------------------------------------//
	// IsIndial100
	//------------------------------------------------------------------------//
	/**
	 * IsIndial100()
	 *
	 * Returns TRUE if the LandLine is an Indial100, ELSE FALSE 
	 * 
	 * Returns TRUE if the LandLine is an Indial100, ELSE FALSE
	 *
	 * @return	bool	TRUE if the LandLine is an Indial100, ELSE FALSE
	 * @method
	 */
	function IsIndial100()
	{
		return (bool)$this->_bolIndial100;
	}
	
	//------------------------------------------------------------------------//
	// _CopySupplementaryDetails
	//------------------------------------------------------------------------//
	/**
	 * _CopySupplementaryDetails()
	 *
	 * Copies ServiceType Specific Supplementary details of a service to a Destination service
	 * 
	 * Copies ServiceType Specific Supplementary details of a service to a Destination service
	 * All Supplementary details associated with the current service will be copied and 
	 * associated with the new service id and its Account
	 * NOTE: this only copies the ServiceAddress Record if $intDestAccountId == $this->_intAccount
	 *
	 * @param	int		$intDestServiceId		Id of the Destination Service
	 * @param	int		$intDestAccountId		Id of the Destination Account
	 * @param	int		$intDestAccountGroup	Id of the Destination AccountGroup
	 * 
	 * @return	bool							TRUE on success, FALSE on Failure
	 * 		
	 * @method
	 * @protected
	 */
	protected function _CopySupplementaryDetails($intDestServiceId, $intDestAccountId, $intDestAccountGroup)
	{
		// Check if the Destination account is the same as the current account
		if ($intDestAccountId == $this->_intAccount)
		{
			// It is safe to copy the ServiceAddress record
			if ($this->_CopyAddressDetails($intDestServiceId, $intDestAccountId, $intDestAccountGroup) === FALSE)
			{
				// An error occured
				return FALSE;
			}
		}
		
		// Copy the ServiceExtension records if ELB is turned on
		if ($this->_bolELB)
		{
			$strServiceExtensionQuery = "	INSERT INTO ServiceExtension (Service, Name, RangeStart, RangeEnd, Archived)
											SELECT $intDestServiceId, Name, RangeStart, RangeEnd, Archived
											FROM ServiceExtension
											WHERE Service = {$this->_intCurrentId}
										";
			$qryServiceExtension = new Query();
			if ($qryServiceExtension->Execute($strServiceExtensionQuery) === FALSE)
			{
				$this->_strErrorMsg = "Unexpected database error while trying to copy ServiceExtension records";
				return FALSE;
			}
		}
	}
	
	//------------------------------------------------------------------------//
	// _CopyAddressDetails
	//------------------------------------------------------------------------//
	/**
	 * _CopyAddressDetails()
	 *
	 * Copies the ServiceAddress record
	 * 
	 * Copies the ServiceAddress record
	 *
	 * @param	int		$intDestServiceId		Id of the Destination Service
	 * @param	int		$intDestAccountId		Id of the Destination Account
	 * @param	int		$intDestAccountGroup	Id of the Destination AccountGroup
	 * 
	 * @return	bool							TRUE on success, FALSE on Failure
	 * 		
	 * @method
	 * @protected
	 */
	protected function _CopyAddressDetails($intDestServiceId, $intDestAccountId, $intDestAccountGroup)
	{
		$selAddress = new StatementSelect("ServiceAddress", "*", "Service = <ServiceId>");
		if ($selAddress->Execute(array("ServiceId" => $this->_intCurrentId)) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected database error occurred while trying to make a copy of the ServiceAddress record for service Id: {$this->_intCurrentId}";
			return FALSE;
		}
		
		// Check that there actually is a ServiceAddress record, as not all landlines have them defined
		if (($arrAddress = $selAddress->Fetch()) === FALSE)
		{
			// There is no ServiceAddress record
			return NULL;
		}
		
		// A ServiceAddress record exists
		// make a copy, referencing the new Service
		$arrAddress['Id']			= NULL;
		$arrAddress['Service']		= $intDestServiceId;
		$arrAddress['Account']		= $intDestAccountId;
		$arrAddress['AccountGroup']	= $intDestAccountGroup;
		$insAddress					= new StatementInsert("ServiceAddress");
		
		if ($insAddress->Execute($arrAddress) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected database error occurred while trying to insert a record into the ServiceAddress table";
			return FALSE;
		}
		
		return TRUE;
	}
	
}

//----------------------------------------------------------------------------//
// ModuleMobile
//----------------------------------------------------------------------------//
/**
 * ModuleMobile
 *
 * Models a Mobile service that is currently defined in the database
 *
 * Models a Mobile service that is currently defined in the database
 *
 * @package	ui_app
 * @class	ModuleMobile
 * @extends	ModuleService
 */
class ModuleMobile extends ModuleService
{
	// While there is a ModuleService->_bolHasUnsavedChanges property, I should work out when I need 
	// to save this details, and when I don't
	// If this is NULL and $_bolExtraDetailsLoaded == TRUE, then one can conclude that the Service doesn't even have
	// a ServiceMobileDetail record associated with it
	private $_arrExtraDetails		= NULL;
	
	// Used to keep track of whether or not the Mobile's extra details have been loaded from the database
	private $_bolExtraDetailsLoaded	= NULL;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the ModuleMobile class 
	 * 
	 * Constructor for the ModuleMobile class
	 * 
	 * @param	int		$intService		Id of the Mobile service that this object will model
	 * 									PRE: Id must reference a valid Mobile service in the Service table of the database.
	 * 									It does not have to reference the most recently added Service record modelling this
	 * 									FNN on this Account, but the object will logically model the use of this service
	 * 									record's FNN on this service record's Account
	 *
	 * @throws	Exception on error or if $intService doesn't reference a Mobile service in the database
	 *
	 * @return	void
	 * @method
	 */
	function __construct($intService)
	{
		parent::__construct($intService);
		
		if ($this->_intServiceType != SERVICE_TYPE_MOBILE)
		{
			throw new Exception("Service with Id '{$this->_intCurrentId}' is not a mobile service");
		}
	}
	
	//------------------------------------------------------------------------//
	// _LoadDetails
	//------------------------------------------------------------------------//
	/**
	 * _LoadDetails()
	 *
	 * Loads the details for the Mobile 
	 * 
	 * Loads the details for the Mobile
	 * Loads the basic Service Record details
	 * It does not load the Service's extra details (ServiceMobileDetail record)
	 * 
	 * @return	bool		TRUE on success, FALSE on failure
	 * @method
	 */
	protected function _LoadDetails()
	{
		return parent::_LoadDetails();
	}
	
	// TODO! implement this properly
	function SaveService()
	{
		parent::SaveService();
		
		// Save the Mobile Extra details (this could involve updating an existing record, or inserting a new one)
		//TODO!
		
		$this->_bolHasUnsavedChanges = TRUE;
	}
	
	//------------------------------------------------------------------------//
	// ChangeStatus
	//------------------------------------------------------------------------//
	/**
	 * ChangeStatus()
	 *
	 * Changes the status of the Mobile (active/disconnected/archived) 
	 * 
	 * Changes the status of the Mobile (active/disconnected/archived)
	 * Before processing the StatusChange it will run SaveService() if there are 
	 * any currently unsaved changes made to the service.
	 * If a new Service record is required to model the change of status, then a new ServiceMobileDetail record
	 * will also be made.  These new details will be reloaded in the object, if it is necessary
	 * The plan details will also be copied across
	 * 
	 * @param	int		$intStatus		The new Service Status to set the service to (SERVICE_ACTIVE, SERVICE_DISCONNECTED, SERVICE_ARCHIVED)
	 * @param	string	$strTimeStamp	optional, TimeStamp at which the Status Change will be recorded as having been made
	 * 									This should not be in the past.  Defaults to NOW() 
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function ChangeStatus($intStatus, $strTimeStamp=NULL)
	{
		$strTimeStamp = ($strTimeStamp == NULL)? GetCurrentISODateTime() : $strTimeStamp;
		
		if (parent::ChangeStatus($intStatus, $strTimeStamp) === FALSE)
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Refresh
	//------------------------------------------------------------------------//
	/**
	 * Refresh()
	 *
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested) 
	 * 
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested)
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function Refresh()
	{
		if (parent::Refresh() === FALSE)
		{
			return FALSE;
		}
		
		// Reload the ServiceMobileDetail record, if it has previously been loaded
		if ($this->_bolExtraDetailsLoaded)
		{
			$mixResult = GetMobileSpecificDetails(TRUE);
			return (bool)($mixResult !== FALSE);
		}
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// GetMobileSpecificDetails
	//------------------------------------------------------------------------//
	/**
	 * GetMobileSpecificDetails()
	 *
	 * Retrieves the Mobile's current ServiceMobileDetail record, if there is one 
	 * 
	 * Retrieves the Mobile's current ServiceMobileDetail record, if there is one
	 *
	 * @param	bool	$bolForceRefresh	optional, defaults to FALSE.  If TRUE then the ServiceMobileDetail
	 * 										record will be retrieved from the database.  If FALSE then
	 * 										the method will only access the database, if it
	 * 										isn't already being stored in the object, from a previous call to this method
	 *
	 * @return	mixed						FALSE	: Error occurred
	 * 										NULL	: The service doesn't have an associated ServiceMobileDetail record
	 * 										Array	: The ServiceMobileDetail record
	 * @method
	 */
	function GetMobileSpecificDetails($bolForceRefresh=FALSE)
	{
		if ($this->_bolExtraDetailsLoaded && !$bolForceRefresh)
		{
			// The details have already been loaded, and we are not forcing a refresh
			return $this->_arrExtraDetails;
		}
		
		$selExtraDetails	= new StatementSelect("ServiceMobileDetail", "*", "Service = <ServiceId>", "Id DESC", "1");
		$mixResult			= $selExtraDetails->Execute(Array("ServiceId" => $this->_intCurrentId));
		if ($mixResult === FALSE)
		{
			// Database error occured
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve the ServiceMobileDetail record";
			return FALSE;
		}
		if ($mixResult == 0)
		{
			// The service doesn't have a ServiceMobileDetail record
			return NULL;
		}
		
		// Save the extra details
		$this->_arrExtraDetails			= $selExtraDetails->Fetch();
		$this->_bolExtraDetailsLoaded	= TRUE;
		return $this->_arrExtraDetails;
	}
	
	//------------------------------------------------------------------------//
	// _CopySupplementaryDetails
	//------------------------------------------------------------------------//
	/**
	 * _CopySupplementaryDetails()
	 *
	 * Copies ServiceType Specific Supplementary details of a service to a Destination service
	 * 
	 * Copies ServiceType Specific Supplementary details of a service to a Destination service
	 * All Supplementary details associated with the current service will be copied and 
	 * associated with the new service id and its Account
	 *
	 * @param	int		$intDestServiceId		Id of the Destination Service
	 * @param	int		$intDestAccountId		Id of the Destination Account
	 * @param	int		$intDestAccountGroup	Id of the Destination AccountGroup
	 * 
	 * @return	bool							TRUE on success, FALSE on Failure
	 * 		
	 * @method
	 * @protected
	 */
	protected function _CopySupplementaryDetails($intDestServiceId, $intDestAccountId, $intDestAccountGroup)
	{
		// Copy the ServiceMobileDetail record to the new Service
		$selExtraDetail = new StatementSelect("ServiceMobileDetail", "*", "Service = <ServiceId>");
		if ($selExtraDetail->Execute(array("ServiceId" => $this->_intCurrentId)) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected database error occurred while trying to make a copy of the ServiceMobileDetail record for service Id: {$this->_intCurrentId}";
			return FALSE;
		}
		
		// Check that there actually is a ServiceMobileDetail record
		if (($arrExtraDetail = $selExtraDetail->Fetch()) === FALSE)
		{
			// There is no ServiceMobileDetail record
			return NULL;
		}
		
		// A ServiceMobileDetail record exists
		// make a copy, referencing the new Service
		$arrExtraDetail['Id']		= NULL;
		$arrExtraDetail['Service']	= $intDestServiceId;
		$insExtraDetail				= new StatementInsert("ServiceMobileDetail");
		
		if ($insExtraDetail->Execute($arrExtraDetail) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected database error occurred while trying to insert a record into the ServiceMobileDetail table";
			return FALSE;
		}
		
		return TRUE;
	}
	
}

//----------------------------------------------------------------------------//
// ModuleADSL
//----------------------------------------------------------------------------//
/**
 * ModuleADSL
 *
 * Models an ADSL service that is currently defined in the database
 *
 * Models an ADSL service that is currently defined in the database
 *
 * @package	ui_app
 * @class	ModuleADSL
 * @extends	ModuleService
 */
class ModuleADSL extends ModuleService
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the ModuleADSL class 
	 * 
	 * Constructor for the ModuleADSL class
	 * 
	 * @param	int		$intService		Id of the ADSL service that this object will model
	 * 									PRE: Id must reference a valid ADSL service in the Service table of the database.
	 * 									It does not have to reference the most recently added Service record modelling this
	 * 									FNN on this Account, but the object will logically model the use of this service
	 * 									record's FNN on this service record's Account
	 *
	 * @throws	Exception on error or if $intService doesn't reference an ADSL service in the database
	 *
	 * @return	void
	 * @method
	 */
	function __construct($intService)
	{
		parent::__construct($intService);
		
		if ($this->_intServiceType != SERVICE_TYPE_ADSL)
		{
			throw new Exception("Service with Id '{$this->_intCurrentId}' is not an ADSL service");
		}
	}
	
	//------------------------------------------------------------------------//
	// _LoadDetails
	//------------------------------------------------------------------------//
	/**
	 * _LoadDetails()
	 *
	 * Loads the details for the ADSL Service 
	 * 
	 * Loads the details for the ADSL Service
	 * Loads the basic Service Record details
	 * 
	 * @return	bool		TRUE on success, FALSE on failure
	 * @method
	 */
	protected function _LoadDetails()
	{
		return parent::_LoadDetails();
	}
	
	//------------------------------------------------------------------------//
	// ChangeStatus
	//------------------------------------------------------------------------//
	/**
	 * ChangeStatus()
	 *
	 * Changes the status of the ADSL (active/disconnected/archived) 
	 * 
	 * Changes the status of the ADSL (active/disconnected/archived)
	 * Before processing the StatusChange it will run SaveService() if there are 
	 * any currently unsaved changes made to the service.
	 * If a new Service record is required to model the change of status, then plan details will also be copied across
	 * 
	 * @param	int		$intStatus		The new Service Status to set the service to (SERVICE_ACTIVE, SERVICE_DISCONNECTED, SERVICE_ARCHIVED)
	 * @param	string	$strTimeStamp	optional, TimeStamp at which the Status Change will be recorded as having been made
	 * 									This should not be in the past.  Defaults to NOW() 
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function ChangeStatus($intStatus, $strTimeStamp=NULL)
	{
		$strTimeStamp = ($strTimeStamp == NULL)? GetCurrentISODateTime() : $strTimeStamp;
		
		if (parent::ChangeStatus($intStatus, $strTimeStamp) === FALSE)
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Refresh
	//------------------------------------------------------------------------//
	/**
	 * Refresh()
	 *
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested) 
	 * 
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested)
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function Refresh()
	{
		if (parent::Refresh() === FALSE)
		{
			return FALSE;
		}
		
		// There are no extra details to load for ADSL services
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _CopySupplementaryDetails
	//------------------------------------------------------------------------//
	/**
	 * _CopySupplementaryDetails()
	 *
	 * Copies ServiceType Specific Supplementary details of a service to a Destination service
	 * 
	 * Copies ServiceType Specific Supplementary details of a service to a Destination service
	 * All Supplementary details associated with the current service will be copied and 
	 * associated with the new service id and its Account
	 *
	 * @param	int		$intDestServiceId		Id of the Destination Service
	 * @param	int		$intDestAccountId		Id of the Destination Account
	 * @param	int		$intDestAccountGroup	Id of the Destination AccountGroup
	 * 
	 * @return	bool							TRUE on success, FALSE on Failure
	 * 		
	 * @method
	 * @protected
	 */
	protected function _CopySupplementaryDetails($intDestServiceId, $intDestAccountId, $intDestAccountGroup)
	{
		// ADSL services don't have any extra details
		return TRUE;
	}
	
}

//----------------------------------------------------------------------------//
// ModuleIndial
//----------------------------------------------------------------------------//
/**
 * ModuleIndial
 *
 * Models an Indial service that is currently defined in the database
 *
 * Models an Indial service that is currently defined in the database
 *
 * @package	ui_app
 * @class	ModuleIndial
 * @extends	ModuleService
 */
class ModuleIndial extends ModuleService
{
	// While there is a ModuleService->_bolHasUnsavedChanges property, I should work out when I need 
	// to save these details, and when I don't
	// If this is NULL and $_bolExtraDetailsLoaded == TRUE, then one can conclude that the Service doesn't even have
	// a ServiceInboundDetail record associated with it
	private $_arrExtraDetails		= NULL;
	
	// Used to keep track of whether or not the extra details have been loaded from the database
	private $_bolExtraDetailsLoaded	= NULL;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the ModuleInbound class 
	 * 
	 * Constructor for the ModuleInbound class
	 * 
	 * @param	int		$intService		Id of the Inbound service that this object will model
	 * 									PRE: Id must reference a valid Inbound service in the Service table of the database.
	 * 									It does not have to reference the most recently added Service record modelling this
	 * 									FNN on this Account, but the object will logically model the use of this service
	 * 									record's FNN on this service record's Account
	 *
	 * @throws	Exception on error or if $intService doesn't reference an Inbound service in the database
	 *
	 * @return	void
	 * @method
	 */
	function __construct($intService)
	{
		parent::__construct($intService);
		
		if ($this->_intServiceType != SERVICE_TYPE_INBOUND)
		{
			throw new Exception("Service with Id '{$this->_intCurrentId}' is not an Inbound service");
		}
	}
	
	//------------------------------------------------------------------------//
	// _LoadDetails
	//------------------------------------------------------------------------//
	/**
	 * _LoadDetails()
	 *
	 * Loads the details for the Inbound Service
	 * 
	 * Loads the details for the Inbound Service
	 * Loads the basic Service Record details
	 * It does not load the Service's extra details (ServiceInboundDetail record)
	 * 
	 * @return	bool		TRUE on success, FALSE on failure
	 * @method
	 */
	protected function _LoadDetails()
	{
		return parent::_LoadDetails();
	}
	
	// TODO! implement this properly
	function SaveService()
	{
		parent::SaveService();
		
		// Save the Inbound Extra details (this could involve updating an existing record, or inserting a new one)
		//TODO!
		
		$this->_bolHasUnsavedChanges = TRUE;
	}
	
	//------------------------------------------------------------------------//
	// ChangeStatus
	//------------------------------------------------------------------------//
	/**
	 * ChangeStatus()
	 *
	 * Changes the status of the Inbound (active/disconnected/archived) 
	 * 
	 * Changes the status of the Inbound (active/disconnected/archived)
	 * Before processing the StatusChange it will run SaveService() if there are 
	 * any currently unsaved changes made to the service.
	 * If a new Service record is required to model the change of status, then a new ServiceInboundDetail record
	 * will also be made.  These new details will be reloaded in the object, if it is necessary
	 * The plan details will also be copied across
	 * 
	 * @param	int		$intStatus		The new Service Status to set the service to (SERVICE_ACTIVE, SERVICE_DISCONNECTED, SERVICE_ARCHIVED)
	 * @param	string	$strTimeStamp	optional, TimeStamp at which the Status Change will be recorded as having been made
	 * 									This should not be in the past.  Defaults to NOW() 
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function ChangeStatus($intStatus, $strTimeStamp=NULL)
	{
		$strTimeStamp = ($strTimeStamp == NULL)? GetCurrentISODateTime() : $strTimeStamp;
		
		if (parent::ChangeStatus($intStatus, $strTimeStamp) === FALSE)
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Refresh
	//------------------------------------------------------------------------//
	/**
	 * Refresh()
	 *
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested) 
	 * 
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested)
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function Refresh()
	{
		if (parent::Refresh() === FALSE)
		{
			return FALSE;
		}
		
		// Reload the ServiceInboundDetail record, if it has previously been loaded
		if ($this->_bolExtraDetailsLoaded)
		{
			$mixResult = GetInboundSpecificDetails(TRUE);
			return (bool)($mixResult !== FALSE);
		}
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// GetInboundSpecificDetails
	//------------------------------------------------------------------------//
	/**
	 * GetInboundSpecificDetails()
	 *
	 * Retrieves the Inbound's current ServiceInboundDetail record, if there is one 
	 * 
	 * Retrieves the Inbound's current ServiceInboundDetail record, if there is one
	 *
	 * @param	bool	$bolForceRefresh	optional, defaults to FALSE.  If TRUE then the ServiceInboundDetail
	 * 										record will be retrieved from the database.  If FALSE then
	 * 										the method will only access the database, if it
	 * 										isn't already being stored in the object, from a previous call to this method
	 *
	 * @return	mixed						FALSE	: Error occurred
	 * 										NULL	: The service doesn't have an associated ServiceInboundDetail record
	 * 										Array	: The ServiceInboundDetail record
	 * @method
	 */
	function GetInboundSpecificDetails($bolForceRefresh=FALSE)
	{
		if ($this->_bolExtraDetailsLoaded && !$bolForceRefresh)
		{
			// The details have already been loaded, and we are not forcing a refresh
			return $this->_arrExtraDetails;
		}
		
		$selExtraDetails	= new StatementSelect("ServiceInboundDetail", "*", "Service = <ServiceId>", "Id DESC", "1");
		$mixResult			= $selExtraDetails->Execute(Array("ServiceId" => $this->_intCurrentId));
		if ($mixResult === FALSE)
		{
			// Database error occured
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve the ServiceInboundDetail record";
			return FALSE;
		}
		if ($mixResult == 0)
		{
			// The service doesn't have a ServiceInboundDetail record
			return NULL;
		}
		
		// Save the extra details
		$this->_arrExtraDetails			= $selExtraDetails->Fetch();
		$this->_bolExtraDetailsLoaded	= TRUE;
		return $this->_arrExtraDetails;
	}
	
	//------------------------------------------------------------------------//
	// _CopySupplementaryDetails
	//------------------------------------------------------------------------//
	/**
	 * _CopySupplementaryDetails()
	 *
	 * Copies ServiceType Specific Supplementary details of a service to a Destination service
	 * 
	 * Copies ServiceType Specific Supplementary details of a service to a Destination service
	 * All Supplementary details associated with the current service will be copied and 
	 * associated with the new service id and its Account
	 *
	 * @param	int		$intDestServiceId		Id of the Destination Service
	 * @param	int		$intDestAccountId		Id of the Destination Account
	 * @param	int		$intDestAccountGroup	Id of the Destination AccountGroup
	 * 
	 * @return	bool							TRUE on success, FALSE on Failure
	 * 		
	 * @method
	 * @protected
	 */
	protected function _CopySupplementaryDetails($intDestServiceId, $intDestAccountId, $intDestAccountGroup)
	{
		// Copy the ServiceInboundDetail record to the new Service
		$selExtraDetail = new StatementSelect("ServiceInboundDetail", "*", "Service = <ServiceId>");
		if ($selExtraDetail->Execute(array("ServiceId" => $this->_intCurrentId)) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected database error occurred while trying to make a copy of the ServiceInboundDetail record for service Id: {$this->_intCurrentId}";
			return FALSE;
		}
		
		// Check that there actually is a ServiceInboundDetail record
		if (($arrExtraDetail = $selExtraDetail->Fetch()) === FALSE)
		{
			// There is no ServiceMobileDetail record
			return NULL;
		}
		
		// A ServiceInboundDetail record exists
		// make a copy, referencing the new Service
		$arrExtraDetail['Id']		= NULL;
		$arrExtraDetail['Service']	= $intDestServiceId;
		$insExtraDetail				= new StatementInsert("ServiceInboundDetail");
		
		if ($insExtraDetail->Execute($arrExtraDetail) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected database error occurred while trying to insert a record into the ServiceInboundDetail table";
			return FALSE;
		}
		
		return TRUE;
	}
	
}

?>
