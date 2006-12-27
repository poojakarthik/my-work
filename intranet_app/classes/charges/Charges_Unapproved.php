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
	 * @extends		dataCollation
	 */
	
	class CDRs_Unbilled extends dataCollation
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
			
			$selUnbilledCalls = new StatementSelect(
				'CDR', 
				'count(*) AS collationLength', 
				'Service = <Service> AND (Status = <Status1> OR Status = <Status2>)'
			);
			
			$selUnbilledCalls->Execute(
				Array(
					'Service'	=> $this->_srvService->Pull ('Id')->getValue (),
					'Status1'	=> CDR_RATED,
					'Status2'	=> CDR_TEMP_INVOICE
				)
			);
			
			$arrLength = $selUnbilledCalls->Fetch ();
			
			// Construct the collation with the number of CDRs that are unbilled
			parent::__construct ('CDRs-Unbilled', 'CDR', $arrLength ['collationLength']);
		}
		
		//------------------------------------------------------------------------//
		// ItemId
		//------------------------------------------------------------------------//
		/**
		 * ItemId()
		 *
		 * Shortcut for Getting CDRs
		 *
		 * A shortcut method to easily get a new CDR object with record information
		 *
		 * @param	Integer		$intId		The Id of the CDR wishing to be retrieved
		 *
		 * @return	CDR
		 *
		 * @method
		 */

		public function ItemId ($intId)
		{
			return new CDR ($intId);
		}
		
		//------------------------------------------------------------------------//
		// ItemIndex
		//------------------------------------------------------------------------//
		/**
		 * ItemIndex()
		 *
		 * Get an item (Identified by its Index)
		 *
		 * Get a CDR record that is Unbilled (Identified by its Index)
		 *
		 * @param	Integer		$intIndex	The Index of the CDR wishing to be retrieved
		 *
		 * @return	CDR
		 *
		 * @method
		 */
		
		public function ItemIndex ($intIndex)
		{
			// Get the Actual Id of the CDR, rather than an Index
			
			$selCDR = new StatementSelect (
				'CDR', 
				'Id', 
				'Service = <Service> AND (Status = <Status1> OR Status = <Status2>)',
				'StartDatetime DESC', 
				$intIndex . ', 1'
			);
			
			$selCDR->Execute(
				Array( 
					'Service'	=> $this->_srvService->Pull ('Id')->getValue (),
					'Status1'	=> CDR_RATED,
					'Status2'	=> CDR_TEMP_INVOICE
				)
			);
			
			// If the CDR could not be found by Index, we've reached past the end of the list. So return null.
			if (!$arrCDR = $selCDR->Fetch ())
			{
				return null;
			}
			
			return $this->ItemId ($arrCDR ['Id']);
		}
		
		
		//------------------------------------------------------------------------//
		// ItemList
		//------------------------------------------------------------------------//
		/**
		 * ItemList()
		 *
		 * Return a list of results
		 *
		 * Return a list of results that are pagination controlled
		 *
		 * @param	Integer		$intStart 		The number of the Starting Index
		 * @param	Integer		$intLength 		The number of results to return
		 * @return	Array
		 *
		 * @method
		 */
		
		public function ItemList ($intStart, $intLength)
		{
			$_DATA = Array ();
			
			// Pull all Id values which match against the Constraints
			// that are within the page limit
			$selCDRS = new StatementSelect (
				'CDR', 
				'Id', 
				'Service = <Service> AND (Status = <Status1> OR Status = <Status2>)',
				'StartDatetime DESC', 
				$intStart . ', ' . $intLength
			);
			
			$selCDRS->Execute (
				Array( 
					'Service'	=> $this->_srvService->Pull ('Id')->getValue (),
					'Status1'	=> CDR_RATED,
					'Status2'	=> CDR_TEMP_INVOICE
				)
			);
			
			// Store the Results as Objects in an array
			while ($Item = $selCDRS->Fetch ())
			{
				$_DATA [] = $this->Push ($this->ItemId ($Item ['Id']));
			}
			
			return $_DATA;
		}
	}
	
?>
