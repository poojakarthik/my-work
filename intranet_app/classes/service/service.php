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
			
			// Set a Archived Boolean
			$this->Push (
				new dataBoolean (
					'Available', 
					$this->Pull ('ClosedOn')->Pull ('month') && 
					$this->Pull ('ClosedOn')->Pull ('day') && 
					$this->Pull ('ClosedOn')->Pull ('year') &&
					mktime (0, 0, 0) <= mktime (
						23, 59, 59,
						$this->Pull ('ClosedOn')->Pull ('month')->getValue (),
						$this->Pull ('ClosedOn')->Pull ('day')->getValue (),
						$this->Pull ('ClosedOn')->Pull ('year')->getValue ()
					)
				)
			);
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
			
			$this->Push (new dataFloat ('UnbilledCharges-Cost-Current', $arrCost ['totalCost'] != "" ? $arrCost ['totalCost'] : 0));
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
				"CreatedBy"				=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				"CreatedOn"				=> date ('Y-m-d'),
				"ChargeType"			=> $chgChargeType->Pull ('ChargeType')->getValue (),
				"Description"			=> $chgChargeType->Pull ('Description')->getValue (),
				"Nature"				=> $chgChargeType->Pull ('Nature')->getValue (),
				"Amount"				=> $fltAmount,
				"Status"				=> CHARGE_WAITING
			);
			
			$insCharge = new StatementInsert ('Charge');
			$insCharge->Execute ($arrCharge);
		}
		
		//------------------------------------------------------------------------//
		// RecurringChargeAdd
		//------------------------------------------------------------------------//
		/**
		 * RecurringChargeAdd()
		 *
		 * Add a RecurringCharge against a Service
		 *
		 * Add a RecurringCharge against a Service
		 *
		 * @param	AuthenticatedEmployee	$aemAuthenticatedEmploee	The person who is adding this charge to the database
		 * @param	ChargeType				$chgChargeType				The Type of RecurringCharge to Assign
		 * @param	String					$strAmount					The amount to charge against. If the charge type is fixed, this value is ignored
		 * @return	Void
		 *
		 * @method
		 */
		
		public function RecurringChargeAdd (AuthenticatedEmployee $aemAuthenticatedEmployee, RecurringChargeType $rctRecurringChargeType, $strAmount)
		{
			$fltAmount = 0;
			
			if ($rctRecurringChargeType->Pull ('Fixed')->isTrue ())
			{
				$fltAmount = $rctRecurringChargeType->Pull ('RecursionCharge')->getValue ();
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
			
			$arrRecurringCharge = Array (
				"AccountGroup"			=> $this->Pull ('AccountGroup')->getValue (),
				"Account"				=> $this->Pull ('Account')->getValue (),
				"Service"				=> $this->Pull ('Id')->getValue (),
				"CreatedBy"				=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				"CreatedOn"				=> date ('Y-m-d'),
				"ChargeType"			=> $rctRecurringChargeType->Pull ('ChargeType')->getValue (),
				"Description"			=> $rctRecurringChargeType->Pull ('Description')->getValue (),
				"Nature"				=> $rctRecurringChargeType->Pull ('Nature')->getValue (),
				"RecurringFreqType"		=> $rctRecurringChargeType->Pull ('RecurringFreqType')->getValue (),
				"RecurringDate"			=> $rctRecurringChargeType->Pull ('RecurringDate')->getValue (),
				"MinCharge"				=> $rctRecurringChargeType->Pull ('MinCharge')->getValue (),
				"RecursionCharge"		=> $fltAmount,
				"CancellationFee"		=> $rctRecurringChargeType->Pull ('CancellationFee')->getValue (),
				"Continuable"			=> $rctRecurringChargeType->Pull ('Continuable')->getValue (),
				"TotalPaid"				=> 0,
				"TotalRecursions"		=> 0,
				"Status"				=> CHARGE_WAITING
			);
			
			$insRecurringCharge = new StatementInsert ('RecurringCharge');
			$insRecurringCharge->Execute ($arrRecurringCharge);
		}
		
		//------------------------------------------------------------------------//
		// Plan
		//------------------------------------------------------------------------//
		/**
		 * Plan()
		 *
		 * Determine the current Plan
		 *
		 * Determine the current Plan
		 *
		 * @return	Void
		 *
		 * @method
		 */
		
		public function Plan ()
		{
			$selCurrentPlan = new StatementSelect (
				'ServiceRatePlan', 
				'RatePlan', 
				'Service = <Service> AND Now() BETWEEN StartDatetime AND EndDatetime', 
				'CreatedOn DESC',
				1
			);
			
			$selCurrentPlan->Execute (Array ('Service' => $this->Pull ('Id')->getValue ()));
			
			if ($selCurrentPlan->Count () == 1)
			{
				$arrPlan = $selCurrentPlan->Fetch ();
				
				$this->Push (new RatePlan ($arrPlan ['RatePlan']));
			}
		}
		
		//------------------------------------------------------------------------//
		// PlanSelect
		//------------------------------------------------------------------------//
		/**
		 * PlanSelect()
		 *
		 * Change the Plan
		 *
		 * Change the Plan for the Service
		 * TODO: In the future, add implementation for time constraints
		 *
		 * @param	AuthenticatedEmployee	$aemAuthenticatedEmployee		The person making the Change
		 * @param	RatePlan				$rplRatePlan					The new [unarchived] Rate Plan to attach to
		 * @return	Void
		 *
		 * @method
		 */
		
		public function PlanSelect (AuthenticatedEmployee $aemAuthenticatedEmployee, RatePlan $rplRatePlan)
		{
			// Prepare each Rate Group
			$insServiceRateGroup = new StatementInsert ('ServiceRateGroup');
			
			// Start the Skeleton
			$arrServiceRateGroup = Array (
				'Service'			=> $this->Pull ('Id')->getValue (),
				'RateGroup'			=> '',
				'CreatedBy'			=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'CreatedOn'			=> date ('Y-m-d H:i:s'),
				'StartDatetime'	=> date ('Y-m-d H:i:s'),
				'EndDatetime'		=> '9999-12-31 23:59:59'
			);
			
			// Loop through each of the Rate Groups and add them against the Service
			foreach ($rplRatePlan->RateGroups () as $rgrRateGroup)
			{
				// Assign the appropriate Rate Group
				$arrServiceRateGroup ['RateGroup'] = $rgrRateGroup->Pull ('Id')->getValue ();
				
				// Insert the Rate Group
				$insServiceRateGroup->Execute ($arrServiceRateGroup);
			}
			
			
			
			
			
			// Start the Rate Plan Skeleton
			$arrServiceRatePlan = Array (
				'Service'			=> $this->Pull ('Id')->getValue (),
				'RatePlan'			=> $rplRatePlan->Pull ('Id')->getValue (),
				'CreatedBy'			=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'CreatedOn'			=> date ('Y-m-d H:i:s'),
				'StartDatetime'	=> date ('Y-m-d H:i:s'),
				'EndDatetime'		=> '9999-12-31 23:59:59'
			);
			
			// Insert the Rate Plan against the Service
			$insServiceRatePlan = new StatementInsert ('ServiceRatePlan');
			$insServiceRatePlan->Execute ($arrServiceRatePlan);
		}
		
		//------------------------------------------------------------------------//
		// Update
		//------------------------------------------------------------------------//
		/**
		 * Update()
		 *
		 * Update Information (e.g.: FNN)
		 *
		 * Update Information (e.g.: FNN)
		 *
		 * @param	Array			$arrDetails		Associative array of possibly tainted service details
		 * @return	Void
		 *
		 * @method
		 */
		
		public function Update ($arrDetails)
		{
			$strFNN = $arrDetails ['FNN'];
			$strFNN = preg_replace ('/\s/', '', $strFNN);
			
			$arrData = Array (
				'FNN'			=> $strFNN
			);
			
			$updService = new StatementUpdate ('Service', 'Id = <Id>', $arrData);
			$updService->Execute ($arrData, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// ArchiveStatus
		//------------------------------------------------------------------------//
		/**
		 * ArchiveStatus()
		 *
		 * Update Service Archive Status
		 *
		 * Update Service Archive Status.
		 *
		 * @param	Boolean		$bolArchive		TRUE:	Archive this Service
		 *										FALSE:	Nothing Happens
		 * @return	Void
		 *
		 * @method
		 */
		
		public function ArchiveStatus ($bolArchive)
		{
			// Archive must be true
			if ($bolArchive !== true)
			{
				return;
			}
			
			// Set up an Archive SET clause
			$arrArchive = Array (
				"ClosedOn"	=>	($bolArchive == true) ? date ('Y-m-d') : null
			);
			
			// Cascade down to include the Services
			$updService = new StatementUpdate ('Service', 'Id = <Id>', $arrArchive);
			$updService->Execute ($arrArchive, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// LesseePassthrough
		//------------------------------------------------------------------------//
		/**
		 * LesseePassthrough()
		 *
		 * Revokes the Service from the Account and Passes the Line to another Account
		 *
		 * Revokes the Service from the Account and Passes the Line to another Account
		 *
		 * @param	Account		$actAccount			The account to receive the Line
		 * @param	Array		$arrDetailsDate		The day which the service will come to the new person
		 * @return	Void
		 *
		 * @method
		 */
		
		public function LesseePassthrough (Account $actAccount, $arrDetailsDate)
		{
			$intDate = mktime (0, 0, 0, $arrDetailsDate ['month'], $arrDetailsDate ['day'], $arrDetailsDate ['year']);
			
			// Cancel the Service on this specific date
			$arrClose = Array (
				"ClosedOn"		=>	date ("Y-m-d", strtotime ("-1 day", $intDate))
			);
			
			$updService = new StatementUpdate ('Service', 'Id = <Id>', $arrClose);
			$updService->Execute ($arrClose, Array ('Id' => $this->Pull ('Id')->getValue ()));
			
			return $actAccount->LesseeReceive ($this, $arrDetailsDate);
		}
	}
	
?>
