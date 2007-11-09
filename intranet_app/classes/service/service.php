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
			$selService = new StatementSelect ('Service', '*', 'Service.Id = <Id>', null, '1');
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
			
			// Get ELB Status
			$selELB = new StatementSelect("ServiceExtension", "Id", "Service = <Service> AND Archived = 0", NULL, 1);
			$this->Push(new dataBoolean('ELB', (bool)$selELB->Execute(Array('Service' => $this->Pull('Id')->getValue()))));
			
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
			$arrRequest = Array (
		 		'Carrier'			=> $intCarrier,
		 		'Service'			=> $this->Pull ('Id')->getValue (),
		 		'Employee'			=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
		 		'RequestType'		=> $intProvisioningRequestType,
		 		'RequestDateTime'	=> new MySQLFunction ('NOW()'),
		 		'Status'			=> REQUEST_STATUS_WAITING
		 	);
			
			$insProvisioningRequest = new StatementInsert ('Request', $arrRequest);
			$insProvisioningRequest->Execute ($arrRequest);
		}
		
		//------------------------------------------------------------------------//
		// UnbilledCDRs
		//------------------------------------------------------------------------//
		/**
		 * UnbilledCDRs()
		 *
		 * List of unbilled CDRs
		 *
		 * Returns a list of all unbilled CDRs associated with this service
		 *
		 * @return	CDRs
		 *
		 * @method
		 */
		
		public function UnbilledCDRs ()
		{
			return new CDRs_Unbilled ($this);
		}
		
		//------------------------------------------------------------------------//
		// UnbilledCharges
		//------------------------------------------------------------------------//
		/**
		 * UnbilledCharges()
		 *
		 * List of Unbilled Charges
		 *
		 * Returns a list of all Unbilled Charges associated with this service
		 *
		 * @return	UnbilledCharges
		 *
		 * @method
		 */
		
		public function UnbilledCharges ()
		{
			return new Charges_Unbilled ($this);
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
			
			$this->Push (new dataFloat ('UnbilledCharges-Cost-Current', $arrCost ['totalCost'] != '' ? $arrCost ['totalCost'] : 0));
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
		
		public function ChargeAdd (AuthenticatedEmployee $aemAuthenticatedEmployee, ChargeType $chgChargeType, $strAmount, $intInvoice, $strNotes)
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
				'AccountGroup'			=> $this->Pull ('AccountGroup')->getValue (),
				'Account'				=> $this->Pull ('Account')->getValue (),
				'Service'				=> $this->Pull ('Id')->getValue (),
				'CreatedBy'				=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'CreatedOn'				=> new MySQLFunction ("NOW()"),
				'ChargedOn'				=> new MySQLFunction ("NOW()"),
				'ChargeType'			=> $chgChargeType->Pull ('ChargeType')->getValue (),
				'Description'			=> $chgChargeType->Pull ('Description')->getValue (),
				'Nature'				=> $chgChargeType->Pull ('Nature')->getValue (),
				'Amount'				=> $fltAmount,
				'Invoice'				=> $intInvoice,
				'Notes'					=> $strNotes,
				'Status'				=> CHARGE_WAITING
			);
			
			$insCharge = new StatementInsert ('Charge', $arrCharge);
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
				'AccountGroup'			=> $this->Pull ('AccountGroup')->getValue (),
				'Account'				=> $this->Pull ('Account')->getValue (),
				'Service'				=> $this->Pull ('Id')->getValue (),
				'CreatedBy'				=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'CreatedOn'				=> new MySQLFunction ("NOW()"),
				'StartedOn'				=> new MySQLFunction ("NOW()"),
				'LastChargedOn'			=> new MySQLFunction ("NOW()"),
				'ChargeType'			=> $rctRecurringChargeType->Pull ('ChargeType')->getValue (),
				'Description'			=> $rctRecurringChargeType->Pull ('Description')->getValue (),
				'Nature'				=> $rctRecurringChargeType->Pull ('Nature')->getValue (),
				'RecurringFreqType'		=> $rctRecurringChargeType->Pull ('RecurringFreqType')->getValue (),
				'RecurringFreq'			=> $rctRecurringChargeType->Pull ('RecurringFreq')->getValue (),
				'MinCharge'				=> $rctRecurringChargeType->Pull ('MinCharge')->getValue (),
				'RecursionCharge'		=> $fltAmount,
				'CancellationFee'		=> $rctRecurringChargeType->Pull ('CancellationFee')->getValue (),
				'Continuable'			=> $rctRecurringChargeType->Pull ('Continuable')->getValue (),
				'TotalCharged'			=> 0,
				'TotalRecursions'		=> 0,
				'Archived'				=> 0
			);
			
			$insRecurringCharge = new StatementInsert ('RecurringCharge', $arrRecurringCharge);
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
			if (!$this->_rrpRatePlan)
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
					$this->_rrpRatePlan = $this->Push (new RatePlan ($arrPlan ['RatePlan']));
				}
			}
			
			return $this->_rrpRatePlan;
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
			//TODO!flame! all current ServiceRateGroup and ServiceRatePlan records must have EndDatetime set to NOW()
		
			// Start the Skeleton
			$arrServiceRateGroup = Array (
				'Service'			=> $this->Pull ('Id')->getValue (),
				'RateGroup'			=> '',
				'CreatedBy'			=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'CreatedOn'			=> new MySQLFunction ("NOW()"),
				'StartDatetime'		=> new MySQLFunction ("NOW()"),
				'EndDatetime'		=> '9999-12-31 23:59:59'
			);
			
			// Prepare each Rate Group
			$insServiceRateGroup = new StatementInsert ('ServiceRateGroup', $arrServiceRateGroup);
			
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
				'CreatedOn'			=> new MySQLFunction ("NOW()"),
				'StartDatetime'		=> new MySQLFunction ("NOW()"),
				'EndDatetime'		=> '9999-12-31 23:59:59'
			);
			
			// Insert the Rate Plan against the Service
			$insServiceRatePlan = new StatementInsert ('ServiceRatePlan', $arrServiceRatePlan);
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
			
			if ($strFNN <> "")
			{
				if (!isValidFNN ($strFNN))
				{
					throw new Exception ("FNN Invalid");
				}
				
				if (ServiceType ($strFNN) <> $this->Pull ('ServiceType')->getValue ())
				{
					throw new Exception ("FNN Invalid");
				}
			}
			
			$arrData = Array (
				'FNN'			=> $strFNN,
				'CostCentre'	=> ($arrDetails ['CostCentre']) ? $arrDetails ['CostCentre'] : null
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
		 * Update Service Archive Status.
		 *
		 * Update Service Archive Status. Unarchiving a Service from the Database may
		 * incur a few different paths.
		 * CLAUSE 1.	If the FNN is in use by another active Service, this function will return false.
		 * CLAUSE 2.	If the FNN is not in use by another Service AND (XOR):
		 *		CLAUSE a.	If the FNN has been used by another Service but the other Service has been closed, a new Service will be created.
		 *		CLAUSE b.	If the FNN has not been used by another Service, the service is reopened.
		 *
		 * @param	Boolean					$bolArchive					TRUE / FALSE:	Depending on whether or not the service is to be Archived or Unarchived
		 * @param	AuthenticatedEmployee	$aemAuthenticatedEmployee	The current person logged in and performing this action
		 *
		 * @return	Void
		 *
		 * @method
		 */
		
		public function ArchiveStatus ($bolArchive, AuthenticatedEmployee $aemAuthenticatedEmployee)
		{
			// If we are choosing to Archive the Service - this is a simple proceedure.
			// It just sets the ClosedOn Date to the Current Date.
			if ($bolArchive == TRUE)
			{
				// Set up an Archive SET clause
				$arrArchive = Array (
					'ClosedOn'	=>	($bolArchive == TRUE) ? new MySQLFunction ("NOW()") : NULL,
					'ClosedBy'	=>	$aemAuthenticatedEmployee->Pull ('Id')->getValue ()
				);
				
				// Apply the Change
				$updService = new StatementUpdate ('Service', 'Id = <Id> AND IsNull(ClosedOn)', $arrArchive);
				$updService->Execute ($arrArchive, Array ('Id' => $this->Pull ('Id')->getValue ()));
				
				// Add a system note to track history
				$strContent	= "Service Archived on " .
								date("d F Y") .
								" by " .
								$aemAuthenticatedEmployee->Pull('FirstName')->getValue() . " " .
								$aemAuthenticatedEmployee->Pull('LastName')->getValue();
				$GLOBALS['fwkFramework']->AddNote(	$strContent,
													7, 
													$aemAuthenticatedEmployee->Pull('Id')->getValue(),
													$this->Pull('AccountGroup')->getValue(),
													$this->Pull('Account')->getValue(),
													$this->Pull('Id')->getValue());
				
				// We have done all we need to here. Therefore, break out
				return $this->Pull ('Id')->getValue ();
			}
			

			
			// If we're up to here, then we want to Reactivate the Service
			
			// Check that the Service is due to be (or is) closed. If it's not
			// then there's no point in Reactivating because it's already active.
			
			if ($this->Pull ('ClosedOn')->Pull ('year') == NULL)
			{
				throw new Exception ("Not Archived");
			}
			
			
			// Add a system note to track history
			$strContent	= "Service Unarchived on " .
							date("d F Y") .
							" by " .
							$aemAuthenticatedEmployee->Pull('FirstName')->getValue() . " " .
							$aemAuthenticatedEmployee->Pull('LastName')->getValue();
			$GLOBALS['fwkFramework']->AddNote(	$strContent,
												7, 
												$aemAuthenticatedEmployee->Pull('Id')->getValue(),
												$this->Pull('AccountGroup')->getValue(),
												$this->Pull('Account')->getValue(),
												$this->Pull('Id')->getValue());
			
			// Check if the FNN is used elsewhere [snatched] (since the date of Closure)
			$selSnatched = new StatementSelect ('Service', 'count(*) as snatchCount', 'FNN = <FNN> AND CreatedOn >= <ClosedOn>');
			$selSnatched->Execute (Array ('FNN' => $this->Pull ('FNN')->getValue (), 'ClosedOn' => $this->Pull ('ClosedOn')->getValue ()));
			$arrSnatched = $selSnatched->Fetch ();
			$bolSnatched = ($arrSnatched ['snatchCount'] != 0);
			
			// If it hasn't been used anywhere else - unarchive the service
			// This is CLAUSE 1
			if (!$bolSnatched)
			{
				// Set up an Archive SET clause
				$arrArchive = Array (
					'ClosedOn'	=>	NULL
				);
				
				// Apply the Change
				$updService = new StatementUpdate ('Service', 'Id = <Id>', $arrArchive);
				$updService->Execute ($arrArchive, Array ('Id' => $this->Pull ('Id')->getValue ()));
				
				// We have done all we need to here. Therefore, break out
				return $this->Pull ('Id')->getValue ();
			}
			
			// If we're up to here - then we will be snatching the FNN back from
			// someone else.
			
			// If the Service with the FNN we want is currently in use, then we want
			// to do a Change of Lessee (COfL)
			$selCOfL = new StatementSelect ('Service', 'Id', 'FNN = <FNN> AND (ClosedOn IS NULL OR ClosedOn > Now())', null, 1);
			$selCOfL->Execute (Array ('FNN' => $this->Pull ('FNN')->getValue ()));
			
			// This is CLAUSE 2.a.
			if ($selCOfL->Fetch ())
			{
				return FALSE;
				/*$srvService = new Service ($arrCOfL ['Id']);
				$intTomorrow = strtotime ("+1 day");
				
				return $srvService->LesseePassthrough (
					$this->getAccount (),
					$aemAuthenticatedEmployee,
					Array (
						"month"		=> date ("m", $intTomorrow),
						"year"		=> date ("Y", $intTomorrow),
						"day"		=> date ("d", $intTomorrow)
					)
				);*/
			}
			
			// We've failed our tests. Therefore we must create a New Service
			// This is CLAUSE 2.b.
			
			$srvService = Services::Add (
				$aemAuthenticatedEmployee, 
				$this->getAccount (), 
				$this->Plan (), 
				Array (
					'FNN'					=> $this->Pull ('FNN')->getValue (),
					'ServiceType'			=> $this->Pull ('ServiceType')->getValue (),
					'Indial100'				=> $this->Pull ('Indial100')->getValue ()
				)
			);
			//TODO!Sean! Need to copy service address details or additional services details
			//TODO!Sean! Need to copy ServiceRateGroup and ServiceRatePlan - see changeoflessee
			return $srvService->Pull ('Id')->getValue ();
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
		 * @param	Account					$actAccount					The account to receive the Line
		 * @param	AuthenticatedEmployee	$aemAuthenticatedEmployee	The person who is performing this request
		 * @param	Array					$arrDetailsDate				The day which the service will come to the new person
		 * @return	Void
		 *
		 * @method
		 */
		
		public function LesseePassthrough (Account $actAccount, AuthenticatedEmployee $aemAuthenticatedEmployee, $arrDetailsDate, $bolTransferUnbilled)
		{
			
			//Debug($this);die;
			$intDate = mktime (0, 0, 0, $arrDetailsDate ['month'], $arrDetailsDate ['day'], $arrDetailsDate ['year']);
			
			// Cancel the Service on this specific date
			$arrClose = Array (
				'ClosedOn'	=>	date ('Y-m-d', strtotime ('-1 day', $intDate)),
				'ClosedBy'	=>	$aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'Status'	=>	SERVICE_DISCONNECTED
			);
			
			$updService = new StatementUpdate ('Service', 'Id = <Id>', $arrClose);
			$updService->Execute ($arrClose, Array ('Id' => $this->Pull ('Id')->getValue ()));
			
			// Transfer the service
			$intService = $actAccount->LesseeReceive ($this, $aemAuthenticatedEmployee, $arrDetailsDate);			
			
			if ($bolTransferUnbilled)
			{	
				// Update the CDR table
				$arrUpdate = Array (
					'Status' 		=> CDR_READY,
					'AccountGroup' 	=> $actAccount->Pull ('AccountGroup')->getValue(),
					'Account' 		=> $actAccount->Pull ('Id')->getValue(),
					'Service'		=> $intService
					);
	
				$strStatus = CDR_RATED . ',' . CDR_NORMALISED . ',' . CDR_RATE_NOT_FOUND . ',' . CDR_RERATE;

				$transferCharges = new StatementUpdate ('CDR', 'Service = <ServiceId> AND Status IN ( '.$strStatus.' )', $arrUpdate);
				$intUpdated = $transferCharges->Execute ($arrUpdate, Array('ServiceId' => $this->Pull ('Id')->getValue ()));
			}
			return Array ($intService, $intUpdated);
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
			$selServiceAddress = new StatementSelect ("ServiceAddress", "Id", "Service = <Service>");
			$selServiceAddress->Execute (Array ("Service"=>$this->Pull ('Id')->getValue ()));
			
			if ($arrServiceAddress = $selServiceAddress->Fetch ())
			{
				return new ServiceAddress ($arrServiceAddress ['Id']);
			}
			
			throw new Exception ('Service Address Not Found');
		}
		
		//------------------------------------------------------------------------//
		// ServiceAddressUpdate
		//------------------------------------------------------------------------//
		/**
		 * ServiceAddressUpdate()
		 *
		 * Update Service Address Information
		 *
		 * Save the Service Address information to the Database
		 *
		 * @param	Array		$arrDetails		An associative array of Service Address Information
		 *
		 * @method
		 */
		
		public function ServiceAddressUpdate ($arrDetails)
		{
			$eutEndUserTitle			= new TitleTypes ();
			$bolEndUserTitle			= $eutEndUserTitle->setValue ($arrDetails ['EndUserTitle']);

			$satServiceAddressType		= new ServiceAddressTypes ();
			$bolServiceAddressType		= $satServiceAddressType->setValue ($arrDetails ['ServiceAddressType']);
			
			$sstServiceStreetType		= new ServiceStreetTypes ();
			$bolServiceStreetType		= $sstServiceStreetType->setValue ($arrDetails ['ServiceStreetType']);
			
			$sstServiceStreetSuffixType	= new ServiceStreetSuffixTypes ();
			$bolServiceStreetSuffixType	= $sstServiceStreetSuffixType->setValue ($arrDetails ['ServiceStreetTypeSuffix']);
			
			$staServiceStateType		= new ServiceStateTypes ();
			$bolServiceStateType		= $staServiceStateType->setValue ($arrDetails ['ServiceState']);

			$arrData = Array (
				'Residential'					=> $arrDetails ['Residential'],
				'BillName'						=> $arrDetails ['BillName'],
				'BillAddress1'					=> $arrDetails ['BillAddress1'],
				'BillAddress2'					=> $arrDetails ['BillAddress2'],
				'BillLocality'					=> $arrDetails ['BillLocality'],
				'BillPostcode'					=> $arrDetails ['BillPostcode'],
				'EndUserTitle'					=> (($bolEndUserTitle == true) ? $arrDetails ['EndUserTitle'] : ''),
				'EndUserGivenName'				=> $arrDetails ['EndUserGivenName'],
				'EndUserFamilyName'				=> $arrDetails ['EndUserFamilyName'],
				'EndUserCompanyName'			=> $arrDetails ['EndUserCompanyName'],
				'DateOfBirth'					=> sprintf ('%04d', $arrDetails ['DateOfBirth:year']) . 
												   sprintf ('%02d', $arrDetails ['DateOfBirth:month']) . 
												   sprintf ('%02d', $arrDetails ['DateOfBirth:day']),
				'Employer'						=> $arrDetails ['Employer'],
				'Occupation'					=> $arrDetails ['Occupation'],
				'ABN'							=> $arrDetails ['ABN'],
				'TradingName'					=> $arrDetails ['TradingName'],
				'ServiceAddressType'			=> (($bolServiceAddressType == true) ? $arrDetails ['ServiceAddressType'] : ''),
				'ServiceAddressTypeNumber'		=> $arrDetails ['ServiceAddressTypeNumber'],
				'ServiceAddressTypeSuffix'		=> $arrDetails ['ServiceAddressTypeSuffix'],
				'ServiceStreetNumberStart'		=> $arrDetails ['ServiceStreetNumberStart'],
				'ServiceStreetNumberEnd'		=> $arrDetails ['ServiceStreetNumberEnd'],
				'ServiceStreetNumberSuffix'		=> $arrDetails ['ServiceStreetNumberSuffix'],
				'ServiceStreetName'				=> $arrDetails ['ServiceStreetName'],
				'ServiceStreetType'				=> (($bolServiceStreetType == true) ? $arrDetails ['ServiceStreetType'] : ''),
				'ServiceStreetTypeSuffix'		=> (($bolServiceStreetSuffixType == true) ? $arrDetails ['ServiceStreetTypeSuffix'] : ''),
				'ServicePropertyName'			=> $arrDetails ['ServicePropertyName'],
				'ServiceLocality'				=> $arrDetails ['ServiceLocality'],
				'ServiceState'					=> (($bolServiceStateType == true) ? $arrDetails ['ServiceState'] : ''),
				'ServicePostcode'				=> sprintf ('%04d', $arrDetails ['ServicePostcode'])
			);
			
			
			// If Service Address Information currently exists for this Service
				// Update the Service Address Information
			// Otherwise
				// Create a new Service Address and Update the Service to reflect this Service Address
				
			try
			{
				$sadServiceAddress = $this->ServiceAddress ();
				$intId = $sadServiceAddress->Pull ('Id')->getValue ();	
				
				// Update Service Address
				$updServiceAddress = new StatementUpdate ('ServiceAddress', 'Id = <Id>', $arrData, 1);
				$updServiceAddress->Execute ($arrData, Array ('Id' => $intId));
				return TRUE;
			}
			catch (Exception $e)
			{
				$arrData ['AccountGroup']	= $this->Pull ('AccountGroup')->getValue ();
				$arrData ['Account']		= $this->Pull ('Account')->getValue ();
				$arrData ['Service']		= $this->Pull ('Id')->getValue ();
				
				// Insert Service Address
				$insServiceAddress = new StatementInsert ('ServiceAddress');
				$intServiceAddress = $insServiceAddress->Execute ($arrData);
				return ($intServiceAddress !== FALSE);
			}
		}
		
		//------------------------------------------------------------------------//
		// MobileDetail
		//------------------------------------------------------------------------//
		/**
		 * MobileDetail()
		 *
		 * Pull the Associated Mobile Detail Information
		 *
		 * Pull the Associated Mobile Detail Information. This is a seperate function
		 * to stop potential memory leakings
		 *
		 * @return	MobileDetail
		 *
		 * @method
		 */
		 
		public function MobileDetail ()
		{
			$selMobileDetail = new StatementSelect ("ServiceMobileDetail", "Id", "Service = <Service>");
			$selMobileDetail->Execute (Array ("Service"=>$this->Pull ('Id')->getValue ()));
			
			if ($arrMobileDetail = $selMobileDetail->Fetch ())
			{
				return new MobileDetail ($arrMobileDetail ['Id']);
			}
			
			throw new Exception ('Mobile Detail Not Found');
		}
		
		//------------------------------------------------------------------------//
		// MobileDetailUpdate
		//------------------------------------------------------------------------//
		/**
		 * MobileDetailUpdate()
		 *
		 * Update Service Address Information
		 *
		 * Save the Service Address information to the Database
		 *
		 * @param	Array		$arrDetails		An associative array of Mobile Details
		 *
		 * @return	MobileDetail
		 *
		 * @method
		 */
		 
		public function MobileDetailUpdate ($arrDetails)
		{
			$staServiceStateType		= new ServiceStateTypes;
			$bolServiceStateType		= $staServiceStateType->setValue ($arrDetails ['SimState']);
			
			$arrData = Array (
				'SimPUK'			=> $arrDetails ['SimPUK'],
				'SimESN'			=> $arrDetails ['SimESN'],
				'DOB'				=> sprintf ("%04d", $arrDetails ['DOB']['year']) . "-" .
									   sprintf ("%02d", $arrDetails ['DOB']['month']) . "-" .
									   sprintf ("%02d", $arrDetails ['DOB']['day']),
				'SimState'			=> (($bolServiceStateType == true) ? $arrDetails ['SimState'] : ''),
				'Comments'			=> $arrDetails ['Comments']
			);
			
			try
			{
				$mdeMobileDetail = $this->MobileDetail ();
				
				// Update Service Address
				$updMobileDetail = new StatementUpdate ('ServiceMobileDetail', 'Id = <Id>', $arrData, 1);
				$updMobileDetail->Execute ($arrData, Array ('Id' => $mdeMobileDetail->Pull ('Id')->getValue ()));
				
				return true;
			}
			catch (Exception $e)
			{
				$arrData ['AccountGroup']	= $this->Pull ('AccountGroup')->getValue ();
				$arrData ['Account']		= $this->Pull ('Account')->getValue ();
				$arrData ['Service']		= $this->Pull ('Id')->getValue ();
				
				// Insert Service Address
				$insMobileDetail = new StatementInsert ('ServiceMobileDetail');
				$insMobileDetail = $insMobileDetail->Execute ($arrData);
			}
		}

		//------------------------------------------------------------------------//
		// InboundDetailUpdate
		//------------------------------------------------------------------------//
		/**
		 * InboundDetailUpdate()
		 *
		 * Update information about Inbound call services
		 *
		 * Save the inbound information to the Database
		 *
		 * @param	Array		$arrDetails		An associative array of service Details
		 *
		 * @return	InboundDetail
		 *
		 * @method
		 */
		 
		public function InboundDetailUpdate ($arrDetails)
		{
			$staServiceStateType		= new ServiceStateTypes;
			$bolServiceStateType		= $staServiceStateType->setValue ($arrDetails ['SimState']);
			
			$arrData = Array (
				'AnswerPoint'			=> $arrDetails ['AnswerPoint'],
				'Configuration'			=> $arrDetails ['Configuration'],
			);
			
			try
			{
				$mdeInboundDetail = $this->InboundDetail ();
				//Debug($mdeInboundDetail->Pull ('Id')->getValue ());
				
				
				// Update Service Address
				$updInboundDetail = new StatementUpdate ('ServiceInboundDetail', 'Service = <Service>', $arrData, 1);
				$updInboundDetail->Execute ($arrData, Array ('Id' => $mdeInboundDetail->Pull ('Id')->getValue ()));
				
				return true;
			}
			catch (Exception $e)
			{
				$arrData ['AccountGroup']	= $this->Pull ('AccountGroup')->getValue ();
				$arrData ['Account']		= $this->Pull ('Account')->getValue ();
				$arrData ['Service']		= $this->Pull ('Id')->getValue ();
				
				// Insert Service Address
				$insMobileDetail = new StatementInsert ('ServiceInboundDetail');
				$insMobileDetail = $insMobileDetail->Execute ($arrData);
			}
		}	
		
		//------------------------------------------------------------------------//
		// InboundDetail
		//------------------------------------------------------------------------//
		/**
		 * InboundDetail()
		 *
		 * Pull the Associated Inbound call Information
		 *
		 * Pull the Associated Inbound call Information. This is a seperate function
		 * to stop potential memory leakings
		 *
		 * @return	InboundDetail
		 *
		 * @method
		 */
		 
		public function InboundDetail ()
		{
			$selInboundDetail = new StatementSelect ("ServiceInboundDetail", "Id", "Service = <Service>");
			$selInboundDetail->Execute (Array ("Service"=>$this->Pull ('Id')->getValue ()));
			if ($arrInboundDetail = $selInboundDetail->Fetch ())
			{
				return new InboundDetail ($arrInboundDetail ['Id']);
			}
			
			throw new Exception ('Inbound call information Not Found');
		}


	}
	
?>
