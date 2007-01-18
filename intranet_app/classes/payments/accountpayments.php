<?php
	
	//----------------------------------------------------------------------------//
	// AccountPayments.php
	//----------------------------------------------------------------------------//
	/**
	 * AccountPayments.php
	 *
	 * Contains the Class that Controls Searching for Payments made against an Account
	 *
	 * Contains the Class that Controls Searching for Payments made against an Account
	 *
	 * @file		AccountPayments.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// AccountPayments
	//----------------------------------------------------------------------------//
	/**
	 * AccountPayments
	 *
	 * Contains the Class that Controls Searching for Payments made against an Account
	 *
	 * Contains the Class that Controls Searching for Payments made against an Account
	 *
	 *
	 * @prefix		acp
	 *
	 * @package		intranet_app
	 * @class		AccountPayments
	 * @extends		Search
	 */
	
	class AccountPayments extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an AccountPayment Searching Routine
		 *
		 * Constructs an AccountPayment Searching Routine
		 *
		 * @param	Account			$actAccount			The account we are viewing Payments for
		 *
		 * @method
		 */
		
		function __construct (Account &$actAccount)
		{
			// Construct the collation with the number of CDRs that are unbilled
			parent::__construct ('AccountPayments', 'InvoicePayment', 'InvoicePayment');
			
			$this->Constrain ('Account', '=', $actAccount->Pull ('Id')->getValue ());
		}
	}
	
?>
