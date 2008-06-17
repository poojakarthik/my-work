<?php
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
?>
