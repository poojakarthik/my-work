<?php

	//----------------------------------------------------------------------------//
	// Payments.php
	//----------------------------------------------------------------------------//
	/**
	 * Payments.php
	 *
	 * Contains the Class that Controls Payment Searching
	 *
	 * Contains the Class that Controls Payment Searching
	 *
	 * @file		Payments.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Payments
	//----------------------------------------------------------------------------//
	/**
	 * Payments
	 *
	 * Controls Searching for an existing Payment
	 *
	 * Controls Searching for an existing Payment
	 *
	 *
	 * @prefix		pay
	 *
	 * @package		intranet_app
	 * @class		Payments
	 * @extends		Search
	 */
	
	class Payments extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an Payment Searching Routine
		 *
		 * Constructs an Payment Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Payments', 'Payment', 'Payment');
			$this->Order ('CreatedOn', FALSE);
		}
	}
	
?>
