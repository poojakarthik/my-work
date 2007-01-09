<?php
	
	//----------------------------------------------------------------------------//
	// chargetype.php
	//----------------------------------------------------------------------------//
	/**
	 * chargetype.php
	 *
	 * File containing ChargeType Class
	 *
	 * File containing ChargeType Class
	 *
	 * @file		chargetype.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ChargeType
	//----------------------------------------------------------------------------//
	/**
	 * ChargeType
	 *
	 * A ChargeType in the Database
	 *
	 * A ChargeType in the Database
	 *
	 *
	 * @prefix	oct
	 *
	 * @package		intranet_app
	 * @class		ChargeType
	 * @extends		dataObject
	 */
	
	class ChargeType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new ChargeType
		 *
		 * Constructor for a new ChargeType
		 *
		 * @param	Integer		$intId		The Id of the ChargeType being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the ChargeType information and Store it ...
			$selChargeType = new StatementSelect ('ChargeType', '*', 'Id = <Id>', null, 1);
			$selChargeType->useObLib (TRUE);
			$selChargeType->Execute (Array ('Id' => $intId));
			
			if ($selChargeType->Count () <> 1)
			{
				throw new Exception ('ChargeType not found');
			}
			
			$selChargeType->Fetch ($this);
			
			// Construct the object
			parent::__construct ('ChargeType', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
