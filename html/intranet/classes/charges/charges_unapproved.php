<?php

	//----------------------------------------------------------------------------//
	// charges_unapproved.php
	//----------------------------------------------------------------------------//
	/**
	 * charges_unapproved.php
	 *
	 * Contains Unapproved Charges
	 *
	 * Contains Unapproved Charges
	 *
	 * @file		charges_unapproved.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.12
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// charges_unapproved
	//----------------------------------------------------------------------------//
	/**
	 * charges_unapproved
	 *
	 * Holds a collation of Unapproved Charges
	 *
	 * Holds a collation of Unapproved Charges
	 *
	 *
	 * @prefix		cua
	 *
	 * @package		intranet_app
	 * @class		charges_unapproved
	 * @extends		Search
	 */
	
	class charges_unapproved extends Search
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor to create a new UnbilledCalls collation
		 *
		 * Constructor to create a new UnbilledCalls collation
		 *
		 * @method
		 */
		
		function __construct ()
		{
			parent::__construct ('Charges-Unapproved', 'Charge', 'Charge');
			
			$this->Constrain ('Status', 'EQUALS', CHARGE_WAITING);
		}
	}
	
?>
