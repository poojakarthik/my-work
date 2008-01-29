<?php
	
	//----------------------------------------------------------------------------//
	// ProvisioningRequest.php
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningRequest.php
	 *
	 * File containing Provisioning Record Class
	 *
	 * File containing Provisioning Record Class
	 *
	 * @file		ProvisioningRequest.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ProvisioningRequest
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningRequest
	 *
	 * A Provisioning Record in the Database
	 *
	 * A Provisioning Record in the Database
	 *
	 *
	 * @prefix	pvr
	 *
	 * @package		intranet_app
	 * @class		ProvisioningRequest
	 * @extends		dataObject
	 */
	
	class ProvisioningRequest extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Provisioning Record
		 *
		 * Constructor for a new Provisioning Record
		 *
		 * @param	Integer		$intId		The Id of the Provisioning Request being Retrieved from the Provisioning Requests
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the ProvisioningRequest information and Store it ...
			$selProvisioningRequest = new StatementSelect ('Request', '*', 'Id = <Id>', null, 1);
			$selProvisioningRequest->useObLib (TRUE);
			$selProvisioningRequest->Execute (Array ('Id' => $intId));
			
			if ($selProvisioningRequest->Count () <> 1)
			{
				throw new Exception ('Provisioning Request does not exist.');
			}
			
			$selProvisioningRequest->Fetch ($this);
			
			// Name the Provisioning Request Type
			$this->Push (new ProvisioningRequestType ($this->Pull ('RequestType')->getValue ()));
			$this->Push (new ProvisioningRequestResponse ($this->Pull ('Status')->getValue ()));
			
			// Name the Carrier
			$this->Push (new Carrier ($this->Pop ('Carrier')->getValue ()));
			
			// Construct the object
			parent::__construct ('ProvisioningRequest', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Cancel
		//------------------------------------------------------------------------//
		/**
		 * Cancel()
		 *
		 * Cancels the Provisioning Request
		 *
		 * Cancels the Provisioning Request
		 *
		 * @return	Boolean		[TRUE/FALSE] Depending on whether the Status allows us to cancel the Request
		 *
		 * @method
		 */
		 
		public function Cancel ()
		{
			if ($this->Pull ('Status')->getValue () != REQUEST_STATUS_WAITING)
			{
				return false;
			}
			
			$arrNewStatus = Array (
				"Status"		=> REQUEST_STATUS_CANCELLED
			);
			
			$updUpdate = new StatementUpdate ('Request', 'Id = <Id> AND Status = <Status>', $arrNewStatus);
			$updUpdate->Execute ($arrNewStatus, Array ('Id' => $this->Pull ('Id')->getValue (), 'Status' => REQUEST_STATUS_WAITING));
			
			return true;
		}
		
		//------------------------------------------------------------------------//
		// Service
		//------------------------------------------------------------------------//
		/**
		 * Service()
		 *
		 * Gets the Associated Service
		 *
		 * Gets the Associated Service
		 *
		 * @return	Service
		 *
		 * @method
		 */
		 
		public function Service ()
		{
			if (!$this->_srvService)
			{
				$this->_srvService = new Service ($this->Pull ('Service')->getValue ());
			}
			
			return $this->_srvService;
		}
	}
	
?>
