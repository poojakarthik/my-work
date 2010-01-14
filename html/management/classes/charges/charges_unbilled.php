<?php

	//----------------------------------------------------------------------------//
	// charges_unbilled.php
	//----------------------------------------------------------------------------//
	/**
	 * charges_unbilled.php
	 *
	 * Contains Unbilled Charges
	 *
	 * Contains Unbilled Charges
	 *
	 * @file		charges_unbilled.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.12
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// charges_unbilled
	//----------------------------------------------------------------------------//
	/**
	 * charges_unbilled
	 *
	 * Holds a collation of Unbilled Charges
	 *
	 * Holds a collation of Unbilled Charges
	 *
	 *
	 * @prefix		cub
	 *
	 * @package		intranet_app
	 * @class		charges_unbilled
	 * @extends		Search
	 */
	
	class charges_unbilled extends Search
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
		 * @param	Service		$srvService		The Service we are requested Charges for
		 *
		 * @method
		 */
		
		function __construct (Service $srvService=NULL)
		{
			parent::__construct ('Charges-Unbilled', 'Charge', 'Charge');
			
			$this->Constrain ('Status',		'NOT EQUAL',	CHARGE_DECLINED);
			$this->Constrain ('Status',		'NOT EQUAL',	CHARGE_INVOICED);
			$this->Constrain ('Status',		'NOT EQUAL',	CHARGE_DELETED);
			
			if ($srvService)
			{
				$this->Constrain ('Service',	'EQUALS',		$srvService->Pull ('Id')->getValue ());
			}
		}
	}
	
?>
