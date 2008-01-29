<?php

	//----------------------------------------------------------------------------//
	// costcentres.php
	//----------------------------------------------------------------------------//
	/**
	 * costcentres.php
	 *
	 * Searches for CostCentre Information
	 *
	 * Searches for CostCentre Information
	 *
	 * @file		costcentres.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */

	//----------------------------------------------------------------------------//
	// CostCentres
	//----------------------------------------------------------------------------//
	/**
	 * CostCentres
	 *
	 * Class for Searching for Cost Centres
	 *
	 * Class for Searching for Cost Centres
	 *
	 *
	 * @prefix		rts
	 *
	 * @package		intranet_app
	 * @class		CostCentres
	 * @extends		Search
	 */
	
	class CostCentres extends Search
	{
	
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Construct a new Cost Centre Search
		 *
		 * Construct a new Cost Centre Search
		 *
		 * @method
		 */
		
		function __construct ()
		{
			parent::__construct ('CostCentres', 'CostCentre', 'CostCentre');
		}
	}
	
?>
