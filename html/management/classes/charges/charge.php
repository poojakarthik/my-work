<?php
	
	//----------------------------------------------------------------------------//
	// Charge.php
	//----------------------------------------------------------------------------//
	/**
	 * Charge.php
	 *
	 * File containing Charge Class
	 *
	 * File containing Charge Class
	 *
	 * @file		Charge.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Charge
	//----------------------------------------------------------------------------//
	/**
	 * Charge
	 *
	 * A Charge in the Database
	 *
	 * A Charge in the Database
	 *
	 *
	 * @prefix	crg
	 *
	 * @package		intranet_app
	 * @class		Charge
	 * @extends		dataObject
	 */
	
	class Charge extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Charge
		 *
		 * Constructor for a new Charge
		 *
		 * @param	Integer		$intId		The Id of the Charge being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Charge information and Store it ...
			$selCharge = new StatementSelect ('Charge', '*', 'Id = <Id>', null, 1);
			$selCharge->useObLib (TRUE);
			$selCharge->Execute (Array ('Id' => $intId));
			
			if ($selCharge->Count () <> 1)
			{
				throw new Exception ('Charge not found');
			}
			
			$selCharge->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Charge', $this->Pull ('Id')->getValue ());
			
			if ($this->Pull ('Service')->getValue () <> NULL)
			{
				$this->Push (new Service ($this->Pop ('Service')->getValue ()));
			}
		}
		
		//------------------------------------------------------------------------//
		// Approve
		//------------------------------------------------------------------------//
		/**
		 * Approve()
		 *
		 * Changes the Status to Approved
		 *
		 * Changes the Status to Approved, only if it is currently in a "Waiting" status
		 *
		 * @param		AuthenticatedEmployee		$aemAuthenticatedEmployee		The employee logged into the system
		 *
		 * @method
		 */
		
		public function Approve ($aemAuthenticatedEmployee)
		{
			$arrApproval = Array (
				'ApprovedBy'	=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'Status'		=> CHARGE_APPROVED
			);
			
			$updApproval = new StatementUpdate ('Charge', 'Id = <Id> AND Status = ' . CHARGE_WAITING, $arrApproval);
			$updApproval->Execute ($arrApproval, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// Decline
		//------------------------------------------------------------------------//
		/**
		 * Decline()
		 *
		 * Changes the Status to Decline
		 *
		 * Changes the Status to Decline, only if it is currently in a "Waiting" status
		 *
		 * @param		AuthenticatedEmployee		$aemAuthenticatedEmployee		The employee logged into the system
		 *
		 * @method
		 */
		
		public function Decline ($aemAuthenticatedEmployee)
		{
			$arrApproval = Array (
				'ApprovedBy'	=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
				'Status'		=> CHARGE_DECLINED
			);
			
			$updApproval = new StatementUpdate ('Charge', 'Id = <Id> AND Status = ' . CHARGE_WAITING, $arrApproval);
			$updApproval->Execute ($arrApproval, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
	}
	
?>
