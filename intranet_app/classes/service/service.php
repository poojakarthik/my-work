<?php
	
	//----------------------------------------------------------------------------//
	// service.php
	//----------------------------------------------------------------------------//
	/**
	 * service.php
	 *
	 * File containing Service Class
	 *
	 * File containing Service Class
	 *
	 * @file		service.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Service
	//----------------------------------------------------------------------------//
	/**
	 * Service
	 *
	 * A service in the Database
	 *
	 * A service in the Database
	 *
	 *
	 * @prefix	srv
	 *
	 * @package		intranet_app
	 * @class		Service
	 * @extends		dataObject
	 */
	
	class Service extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Service
		 *
		 * Constructor for a new Service
		 *
		 * @param	Integer		$intId		The Id of the Service being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Service information and Store it ...
			$selService = new StatementSelect ('Service', '*', 'Id = <Id>', null, '1');
			$selService->useObLib (TRUE);
			$selService->Execute (Array ('Id' => $intId));
			
			if ($selService->Count () <> 1)
			{
				throw new Exception ('Service Not Found');
			}
			
			$selService->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Service', $this->Pull ('Id')->getValue ());
			
			// Pull the Service Type(s)
			$this->Push (new ServiceTypes ($this->Pull ('ServiceType')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// getAccount
		//------------------------------------------------------------------------//
		/**
		 * getAccount()
		 *
		 * Get Associated Account
		 *
		 * Get an Account Object which is the Associated Accound for this Service
		 *
		 * @return	Account
		 *
		 * @method
		 */
		 
		public function getAccount ()
		{
			return new Account ($this->Pull ('Account')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// ServiceAddress
		//------------------------------------------------------------------------//
		/**
		 * ServiceAddress()
		 *
		 * Pull the Associated Service Address Information
		 *
		 * Pull the Associated Service Address Information. This is a seperate function
		 * because of memory leakings that have been occurring
		 *
		 * @return	ServiceAddress
		 *
		 * @method
		 */
		 
		public function ServiceAddress ()
		{
			$oblintServiceAddress = $this->Pop ('ServiceAddress');
			
			if ($oblintServiceAddress->getValue () != null)
			{
				return $this->Push (new ServiceAddress ($oblintServiceAddress->getValue ()));
			}
			
			return null;
		}
		 
		//------------------------------------------------------------------------//
		// CreateNewProvisioningRequest
		//------------------------------------------------------------------------//
		/**
		 * CreateNewProvisioningRequest()
		 *
		 * Create a new Provisioning Request
		 *
		 * Create a new Provisioning Request to be Processed Later
		 *
		 * @param	AuthenticatedEmployee	$aemAuthenticatedEmployee		The Current Authenticated Employee
		 * @param	Integer					$intCarrier						The Carrier Constant Value where the Request is routed to
		 * @param	Integer					$intProvisioningRequestType		The Provisioning Request Type Constant Value which is being requested
		 * @return	Void
		 *
		 * @method
		 */
		 
		public function CreateNewProvisioningRequest ($aemAuthenticatedEmployee, $intCarrier, $intProvisioningRequestType)
		{
			$insProvisioningRequest = new StatementInsert ('Request');
			$insProvisioningRequest->Execute (
				Array (
			 		'Carrier'			=> $intCarrier,
			 		'Service'			=> $this->Pull ('Id')->getValue (),
			 		'Employee'			=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
			 		'RequestType'		=> $intProvisioningRequestType,
			 		'RequestDateTime'	=> date ('Y-m-d H:i:s'),
			 		'Status'			=> REQUEST_STATUS_WAITING
			 	)
			);
		}
	}
	
?>
