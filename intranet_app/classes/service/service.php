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
		
		//------------------------------------------------------------------------//
		// UnbilledCharges
		//------------------------------------------------------------------------//
		/**
		 * UnbilledCharges()
		 *
		 * List of unbilled charges
		 *
		 * Returns a list of all unbilled charges associated with this service
		 *
		 * @return	CDRs
		 *
		 * @method
		 */
		
		public function UnbilledCharges ()
		{
			return new CDRs_Unbilled ($this);
		}
		
		//------------------------------------------------------------------------//
		// UnbilledChargeCostCurrent
		//------------------------------------------------------------------------//
		/**
		 * UnbilledChargeCostCurrent()
		 *
		 * How much is currently charges against this service?
		 *
		 * How much is currently charges against this service?
		 *
		 * @return	Float
		 *
		 * @method
		 */
		
		public function UnbilledChargeCostCurrent ()
		{
			$selCost = new StatementSelect (
				'CDR', 
				'SUM(Charge) AS totalCost',
				'Service = <Service> AND (Status = <Status1> OR Status = <Status2>)'
			);
			
			$selCost->Execute (
				Array (
					'Service'		=> $this->Pull ('Id')->getValue (),
					'Status1'		=> CDR_RATED,
					'Status2'		=> CDR_TEMP_INVOICE
				)
			);
			
			$arrCost = $selCost->Fetch ();
			
			$this->Push (new dataFloat ('UnbilledCharges-Cost-Current', $arrCost ['totalCost']));
		}
		
		//------------------------------------------------------------------------//
		// ChargeAdd
		//------------------------------------------------------------------------//
		/**
		 * ChargeAdd()
		 *
		 * Add a charge against a Service
		 *
		 * Add a charge against a Service
		 *
		 * @param	AuthenticatedEmployee	$aemAuthenticatedEmploee	The person who is adding this charge to the database
		 * @param	ChargeType				$chgChargeType				The Type of Charge to Assign
		 * @param	String					$strAmount					The amount to charge against. If the charge type is fixed, this value is ignored
		 * @return	Void
		 *
		 * @method
		 */
		
		public function ChargeAdd (AuthenticatedEmployee $aemAuthenticatedEmployee, ChargeType $chgChargeType, $strAmount)
		{
			$fltAmount = 0;
			
			if ($chgChargeType->Pull ('Fixed')->isTrue ())
			{
				$fltAmount = $chgChargeType->Pull ('Amount')->getValue ();
			}
			else
			{
				$fltAmount = $strAmount;
				$fltAmount = preg_replace ('/\$/', '', $fltAmount);
				$fltAmount = preg_replace ('/\s/', '', $fltAmount);
				$fltAmount = preg_replace ('/\,/', '', $fltAmount);
				
				if (!preg_match ('/^([\d]*)(\.[\d]+){0,1}$/', $fltAmount))
				{
					throw new Exception ('Invalid Amount');
				}
			}
			
			$arrCharge = Array (
				 "AccountGroup"			=> $this->Pull ('AccountGroup')->getValue (),
				 "Account"				=> $this->Pull ('Account')->getValue (),
				 "Service"				=> $this->Pull ('Id')->getValue (),
				 "CreatedBy"			=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				 "CreatedOn"			=> date ('Y-m-d'),
				 "ChargeType"			=> $chgChargeType->Pull ('ChargeType')->getValue (),
				 "Description"			=> $chgChargeType->Pull ('Description')->getValue (),
				 "Nature"				=> $chgChargeType->Pull ('Nature')->getValue (),
				 "Amount"				=> $fltAmount,
				 "Status"				=> CHARGE_WAITING
			);
			
			$insCharge = new StatementInsert ('Charge');
			$insCharge->Execute ($arrCharge);
		}
	}
	
?>
