<?php
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
?>
