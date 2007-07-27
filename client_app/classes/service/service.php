<?php

//----------------------------------------------------------------------------//
// service.php
//----------------------------------------------------------------------------//
/**
 * service.php
 *
 * Contains the Class for an Individual Service
 *
 * Contains the Class for an Individual Service
 *
 * @file	service.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim 'Bash' Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// Service
	//----------------------------------------------------------------------------//
	/**
	 * Service
	 *
	 * Allows the Control of a Service in the Database
	 *
	 * Allows the Control of a Service in the Database
	 *
	 *
	 * @prefix	srv
	 *
	 * @package	client_app
	 * @class	Service
	 * @extends	dataObject
	 */
	
	class Service extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _cntContact
		//------------------------------------------------------------------------//
		/**
		 * _cntContact
		 *
		 * The AuthenticatedContact which the User currently Holds
		 *
		 * The AuthenticatedContact object which a user holds that can be used to
		 * identify their login status.
		 *
		 * @type	AuthenticatedContact
		 *
		 * @property
		 */
		
		private $_cntContact;
		
		//------------------------------------------------------------------------//
		// Service
		//------------------------------------------------------------------------//
		/**
		 * Service()
		 *
		 * The constructor for a Service object
		 *
		 * Contains information about a particular Service number in the Database.
		 *
		 * @param	AuthenticatedContact	$cntContact		The Authenticated Contact logged into the System
		 * @param	Integer			$intService		The Id of the Service being requested
		 *
		 * @method
		 */
		
		function __construct (&$cntContact, $intService)
		{
			parent::__construct ("Service");
			
			$this->_cntContact =& $cntContact;
			
			// If the AuthenticatedContact is a CustomerContact, Authenticate Against the AccountGroup
			// Otherwise Authenticate Against the Account
			if ($this->_cntContact->Pull ("CustomerContact")->isTrue ())
			{
				$selService = new StatementSelect ("Service", "*", "Id = <Id> AND AccountGroup = <AccountGroup>", null, "1");
				$selService->Execute(Array("Id" => $intService, "AccountGroup" => $this->_cntContact->Pull ("AccountGroup")->getValue ()));
			}
			else
			{
				$selService = new StatementSelect ("Service", "*", "Id = <Id> AND Account = <Account>", "1");
				$selService->Execute(Array("Id" => $intService, "Account" => $this->_cntContact->Pull ("Account")->getValue ()));
			}
			
			$selService->useObLib (TRUE);
			
			if ($selService->Count () <> 1)
			{
				throw new Exception ("Class Service could not be instantiated because its ID could not be found in the database");
			}
			
			$selService->Fetch ($this);
			
			$fltTotalCharge = 0;
			
			// Get the Current Plan
			$rrpPlan = $this->Plan ();
			
			// Calculate the Unbilled Charges - based on Caps and etc.
			
			// This has been commented out because an error has been occurring
			if ($rrpPlan->Pull ("ChargeCap")->getValue () > 0)
			{
				$fltTotalCharge = floatval (
					min ($this->Pull ("CappedCharge")->getValue (), $rrpPlan->Pull ("ChargeCap")->getValue ()) +
					$this->Pull ("UncappedCharge")->getValue ()
				);
				
				if ($rrpPlan->Pull ("UsageCap")->getValue () > 0 && $rrpPlan->Pull ("UsageCap")->getValue () < $this->Pull ("CappedCharge")->getValue ())
				{
					$fltTotalCharge += floatval ($this->Pull ("UncappedCharge")->getValue () - $rrpPlan->Pull ("UsageCap")->getValue ());
				}
			}
			else 
			{
				$fltTotalCharge = floatval ($this->Pull ("CappedCharge")->getValue () + $this->Pull ("UncappedCharge")->getValue ());
			}
			
			// Store the Total Charge
			$this->Push (new dataFloat ("TotalCharge", $fltTotalCharge));
			
			// Name the Service Type
			$this->Push (new ServiceType ("NamedServiceType", $this->Pull ("ServiceType")->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// getCharges
		//------------------------------------------------------------------------//
		/**
		 * getCharges()
		 *
		 * Attaches a list of Unbilled Charges put on a Service Number
		 *
		 * Holds a list of Unbilled Charges assigned to a Service Number. Because these
		 * lists won't usually be too big, this is not paginated. It's just a dataCollection.
		 *
		 * @return	UnbilledCharges
		 *
		 * @method
		 */
		
		public function getCharges ()
		{
			return $this->Push (new UnbilledCharges ($this->_cntContact, $this));
		}
		
		//------------------------------------------------------------------------//
		// getCalls
		//------------------------------------------------------------------------//
		/**
		 * getCalls()
		 *
		 * Attaches a list of Unbilled Calls
		 *
		 * Puts a [data]Sample list of calls on the current record which are unbilled.
		 *
		 * @param	Integer		$intPage	The page number being requested
		 * @param	Integer		$intLength	The number of Items per page that you would like
		 *
		 * @return	dataSample
		 *
		 * @method
		 */
		
		public function getCalls ($intPage=1, $intLength=10)
		{
			$oblcoaUnbilledCalls = new UnbilledCalls ($this->_cntContact, $this);
			return $this->Push ($oblcoaUnbilledCalls->Sample ($intPage, $intLength));
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
	}
	
?>
