<?php

	//----------------------------------------------------------------------------//
	// recordtypes.php
	//----------------------------------------------------------------------------//
	/**
	 * recordtypes.php
	 *
	 * Searches for RecordType Information
	 *
	 * Searches for RecordType Information
	 *
	 * @file		recordtypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */

	//----------------------------------------------------------------------------//
	// RecordTypes
	//----------------------------------------------------------------------------//
	/**
	 * RecordTypes
	 *
	 * Class for Searching for Record Types
	 *
	 * Class for Searching for Record Types
	 *
	 *
	 * @prefix		rts
	 *
	 * @package		client_app
	 * @class		RecordTypes
	 * @extends		dataArray
	 */
	
	class RecordTypes extends dataArray
	{
	
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Construct a new Record Type Search
		 *
		 * Construct a new Record Type Search
		 *
		 * @method
		 */
		
		function __construct ()
		{
			parent::__construct ("RecordTypes");
			
			// Get all the invoices in the account
			$selRecordTypes = new StatementSelect ("RecordType", "Id");
			$selRecordTypes->Execute ();
			
			// Loop through all the invoices
			while ($arrRecordType = $selRecordTypes->Fetch ())
			{
				// Push the invoice into the ObLib object
				$this->Push (new RecordType ($arrRecordType ['Id']));
			}
		}
	}
	
?>
