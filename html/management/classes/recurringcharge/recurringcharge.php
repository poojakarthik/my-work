<?php
	
	//----------------------------------------------------------------------------//
	// recurringcharge.php
	//----------------------------------------------------------------------------//
	/**
	 * recurringcharge.php
	 *
	 * File containing RecurringCharge Class
	 *
	 * File containing RecurringCharge Class
	 *
	 * @file		recurringcharge.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// RecurringCharge
	//----------------------------------------------------------------------------//
	/**
	 * RecurringCharge
	 *
	 * A RecurringCharge in the Database
	 *
	 * A RecurringCharge in the Database
	 *
	 *
	 * @prefix	rct
	 *
	 * @package		intranet_app
	 * @class		RecurringCharge
	 * @extends		dataObject
	 */
	
	class RecurringCharge extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new RecurringCharge
		 *
		 * Constructor for a new RecurringCharge
		 *
		 * @param	Integer		$intId		The Id of the RecurringCharge being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the RecurringCharge information and Store it ...
			$selRecurringCharge = new StatementSelect ('RecurringCharge', '*', 'Id = <Id>', null, 1);
			$selRecurringCharge->useObLib (TRUE);
			$selRecurringCharge->Execute (Array ('Id' => $intId));
			
			if ($selRecurringCharge->Count () <> 1)
			{
				throw new Exception ('RecurringCharge not found');
			}
			
			$selRecurringCharge->Fetch ($this);
			
			// Construct the object
			parent::__construct ('RecurringCharge', $this->Pull ('Id')->getValue ());
			
			$this->Push (new BillingFreqTypes ($this->Pull ('RecurringFreqType')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// Account
		//------------------------------------------------------------------------//
		/**
		 * Account()
		 *
		 * Pull the Account this Recurring Charge is attached to
		 *
		 * Pull the Account this Recurring Charge is attached to
		 *
		 * @return	Account
		 *
		 * @method
		 */
		
		public function Account ()
		{
			return $this->Push (new Account ($this->Pop ('Account')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// Service
		//------------------------------------------------------------------------//
		/**
		 * Service()
		 *
		 * Pull the Service this Recurring Charge is attached to
		 *
		 * Pull the Service this Recurring Charge is attached to
		 *
		 * @return	Service
		 *
		 * @method
		 */
		
		public function Service ()
		{
			$intService = $this->Pop ('Service')->getValue ();
			
			if ($intService != NULL)
			{
				return $this->Push (new Service ($intService));
			}
			
			return null;
		}
		
		//------------------------------------------------------------------------//
		// CancellationAmount
		//------------------------------------------------------------------------//
		/**
		 * CancellationAmount()
		 *
		 * How much it would cost to cancel this account
		 *
		 * How much it would cost to cancel this account
		 *
		 * @return	Integer
		 *
		 * @method
		 */
		
		public function CancellationAmount ()
		{
			$selCancellationAmount = new StatementSelect (
				'RecurringCharge', 
				'IF(TotalCharged < MinCharge, MinCharge - TotalCharged + CancellationFee, 0) AS CancellationAmount', 
				'Id = <Id> AND Nature = "' . NATURE_DR . '"', 
				null,
				1
			);
			
			$selCancellationAmount->Execute (Array ('Id' => $this->Pull ('Id')->getValue ()));
			
			if ($selCancellationAmount->Count () == 1)
			{
				$arrCharge = $selCancellationAmount->Fetch ();
			}
			else
			{
				$arrCharge ['CancellationAmount'] = 0;
			}
			
			if ($arrCharge ['CancellationAmount'] <> 0)
			{
				$this->Push (new dataFloat ('CancellationAmount', $arrCharge ['CancellationAmount']));
			}
			
			return $arrCharge ['CancellationAmount'];
		}
		
		//------------------------------------------------------------------------//
		// Cancel
		//------------------------------------------------------------------------//
		/**
		 * Cancel()
		 *
		 * Cancels the Recurring Charge
		 *
		 * Cancels the Recurring Charge + applies cancellation fees if any
		 *
		 * @return	Void
		 *
		 * @method
		 */
		
		public function Cancel (AuthenticatedEmployee $aemAuthenticatedEmployee)
		{
			if ($this->Pull ('Archived')->isTrue ())
			{
				return;
			}
			
			$fltCancellationAmount = $this->CancellationAmount ();
			
			if ($fltCancellationAmount <> 0)
			{
				// set service Id to NULL if oblib is stupid and returns a 0 for non existing service
				//TODO!nate! $this->Pull ('Service')->getValue () should return NULL if there is no service
				// either fix it (and all other objects that return 0 when they should return NULL) or just 
				// go ahead and replace oblib !
				$intService = $this->Pull ('Service')->getValue ();
				if (!$intService)
				{
					$intService = NULL;
				}
				
				$arrCharge = Array (
					"AccountGroup"			=> $this->Pull ('AccountGroup')->getValue (),
					"Account"				=> $this->Pull ('Account')->getValue (),
					"Service"				=> $intService,
					"CreatedBy"				=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
					"CreatedOn"				=> new MySQLFunction ("NOW()"),
					"ChargeType"			=> $this->Pull ('ChargeType')->getValue (),
					"Description"			=> "CANCELLATION: " . $this->Pull ('Description')->getValue (),
					"Nature"				=> NATURE_DR,
					"Amount"				=> $fltCancellationAmount,
					"Status"				=> CHARGE_APPROVED
				);
				
				$insCharge = new StatementInsert ('Charge', $arrCharge);
				$insCharge->Execute ($arrCharge);
			}
			
			$arrRecurringCharge = Array (
				"Archived"	=> TRUE
			);
			
			$updRecurringCharge = new StatementUpdate (
				'RecurringCharge', 
				'Id = <Id>',
				$arrRecurringCharge,
				1
			);
			
			$updRecurringCharge->Execute ($arrRecurringCharge, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
	}
	
?>
