<?php

	//----------------------------------------------------------------------------//
	// CDRs_Unbilled.php
	//----------------------------------------------------------------------------//
	/**
	 * CDRs_Unbilled.php
	 *
	 * Contains unbilled CDR Records for a Service
	 *
	 * Contains unbilled CDR Records for a Service
	 *
	 * @file		CDRs-Unbilled.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.12
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// CDRs_Unbilled
	//----------------------------------------------------------------------------//
	/**
	 * CDRs_Unbilled
	 *
	 * Holds a collation of Unbilled Calls
	 *
	 * Holds a collation of Unbilled Calls
	 *
	 *
	 * @prefix		ubc
	 *
	 * @package		intranet_app
	 * @class		CDRs_Unbilled
	 * @extends		Search
	 */
	
	class CDRs_Unbilled extends Search
	{
		
		//------------------------------------------------------------------------//
		// _srvService
		//------------------------------------------------------------------------//
		/**
		 * _srvService
		 *
		 * The Service object for CDRs that we wish to View
		 *
		 * The Service Object which tells us what CDRs to retrieve
		 *
		 * @type	Service
		 *
		 * @property
		 */
		
		private $_srvService;
		
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
		 * @param	Service			$srvService			A Service Object containing information about which calls to view
		 *
		 * @method
		 */
		
		function __construct (&$srvService)
		{
			$this->_srvService =& $srvService;
			
			parent::__construct ('CDRs-Unbilled', 'CDR', 'CDR');
			
			$this->Constrain	('Service',		'=',	$srvService->Pull ('Id')->getValue ());
			
			// These are the CDRs that are classed as "Unbilled"
			$arrValidStatus = Array (
				CDR_RATED			=> TRUE,
				CDR_TEMP_INVOICE	=> TRUE
			);
			
			// CDR Range is between 100 and 199
			for ($i=100; $i <= 299; ++$i)
			{
				// If this is not a Status we want to search for
				// Block it out
				if (!isset ($arrValidStatus [$i]))
				{
					$this->Constrain	('Status',		'NOT EQUAL',	$i);
				}
			}
			
			$this->Order		('StartDatetime', FALSE);
		}
	}
	
?>
