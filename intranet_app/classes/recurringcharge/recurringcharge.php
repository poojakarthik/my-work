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
	}
	
?>
