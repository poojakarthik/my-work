<?php
	
	//----------------------------------------------------------------------------//
	// CDR.php
	//----------------------------------------------------------------------------//
	/**
	 * CDR.php
	 *
	 * File containing CDR Class
	 *
	 * File containing CDR Class
	 *
	 * @file		CDR.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// CDR
	//----------------------------------------------------------------------------//
	/**
	 * CDR
	 *
	 * A CDR in the Database
	 *
	 * A CDR in the Database
	 *
	 *
	 * @prefix	cdr
	 *
	 * @package		intranet_app
	 * @class		CDR
	 * @extends		dataObject
	 */
	
	class CDR extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new CDR
		 *
		 * Constructor for a new CDR
		 *
		 * @param	Integer		$intId		The Id of the CDR being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the CDR information and Store it ...
			$arrWhere = Array('Id' => $intId);
			$selCDR = new StatementSelect('CDR', '*', 'Id = <Id>', null, 1);
			$selCDR->useObLib(TRUE);
			$selCDR->Execute($arrWhere);
			
			if ($selCDR->Count() <> 1)
			{
				// The CDR was not present in the CDR table.  Try looking for it in the ARCHIVED CDRInvoiced table
				$selCDR = new StatementSelect('cdr_invoiced', '*', 'id = <Id>', null, 1, '', FLEX_DATABASE_CONNECTION_CDR);
				$selCDR->useObLib(TRUE);
				$selCDR->Execute($arrWhere);
				
				if ($selCDR->Count() <> 1)
				{
					// The CDR was not found in either of the CDR and CDRInvoiced tables
					throw new Exception ('CDR not found');
				}
			}
			
			$selCDR->Fetch($this);
			
			// Construct the object
			parent::__construct ('CDR', $this->Pull ('Id')->getValue ());
			
			// Add an extra field which calculates the duration
			$this->Push (
				new dataDuration (
					"Duration",
					$this->PUll ("EndDatetime")->getValue () - $this->PUll ("StartDatetime")->getValue ()
				)
			);
		}
	}
	
?>
