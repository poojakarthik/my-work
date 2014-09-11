<?php
//----------------------------------------------------------------------------//
// ModuleInbound
//----------------------------------------------------------------------------//
/**
 * ModuleInbound
 *
 * Models an Inbound service that is currently defined in the database
 *
 * Models an Inbound service that is currently defined in the database
 *
 * @package	ui_app
 * @class	ModuleInbound
 * @extends	ModuleService
 */
class ModuleInbound extends ModuleService
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
		return false;
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
