<?php
	
	//----------------------------------------------------------------------------//
	// recurringchargetype.php
	//----------------------------------------------------------------------------//
	/**
	 * recurringchargetype.php
	 *
	 * File containing RecurringChargeType Class
	 *
	 * File containing RecurringChargeType Class
	 *
	 * @file		recurringchargetype.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// RecurringChargeType
	//----------------------------------------------------------------------------//
	/**
	 * RecurringChargeType
	 *
	 * A RecurringChargeType in the Database
	 *
	 * A RecurringChargeType in the Database
	 *
	 *
	 * @prefix	rct
	 *
	 * @package		intranet_app
	 * @class		RecurringChargeType
	 * @extends		dataObject
	 */
	
	class RecurringChargeType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new RecurringChargeType
		 *
		 * Constructor for a new RecurringChargeType
		 *
		 * @param	Integer		$intId		The Id of the RecurringChargeType being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the RecurringChargeType information and Store it ...
			$selRecurringChargeType = new StatementSelect ('RecurringChargeType', '*', 'Id = <Id>', null, 1);
			$selRecurringChargeType->useObLib (TRUE);
			$selRecurringChargeType->Execute (Array ('Id' => $intId));
			
			if ($selRecurringChargeType->Count () <> 1)
			{
				throw new Exception ('RecurringChargeType not found');
			}
			
			$selRecurringChargeType->Fetch ($this);
			
			// Construct the object
			parent::__construct ('RecurringChargeType', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
