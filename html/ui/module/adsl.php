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
	// CanBeProvisioned
	//------------------------------------------------------------------------//
	/**
	 * CanBeProvisioned()
	 *
	 * Returns TRUE if the service can be provisioned, ELSE FALSE 
	 * 
	 * Returns TRUE if the service can be provisioned, ELSE FALSE
	 *
	 * @return	bool	TRUE if the service can be provisioned
	 * @method
	 */
	public function CanBeProvisioned()
	{
		return true;
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
