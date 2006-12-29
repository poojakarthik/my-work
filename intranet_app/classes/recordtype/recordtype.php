<?php
	
	//----------------------------------------------------------------------------//
	// recordtype.php
	//----------------------------------------------------------------------------//
	/**
	 * recordtype.php
	 *
	 * File for RecordType Class
	 *
	 * File for RecordType Class
	 *
	 * @file		recordtype.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	 
	//----------------------------------------------------------------------------//
	// RecordType
	//----------------------------------------------------------------------------//
	/**
	 * RecordType
	 *
	 * Contains information reguarding Record Types
	 *
	 * Contains information reguarding Record Types
	 *
	 *
	 * @prefix	rty
	 *
	 * @package		intranet_app
	 * @class		RecordType
	 * @extends		dataObject
	 */
	
	class RecordType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs RecordType information from the Database
		 *
		 * Constructs RecordType information from the Database
		 *
		 * @param	Integer		$intId		The Id of the RecordType being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			parent::__construct ('RecordType', $intId);
			
			// Pull the RecordType information and attach it to the Object
			$selRecordType = new StatementSelect ('RecordType', '*', 'Id = <Id>');
			$selRecordType->useObLib (TRUE);
			$selRecordType->Execute (Array ('Id' => $intId));
			$selRecordType->Fetch ($this);
		}
	}
	
?>
