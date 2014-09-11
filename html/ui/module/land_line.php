<?php
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
?>
