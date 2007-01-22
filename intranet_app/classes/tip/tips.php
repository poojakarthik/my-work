<?php

	//----------------------------------------------------------------------------//
	// tips.php
	//----------------------------------------------------------------------------//
	/**
	 * tips.php
	 *
	 * Contains the Class that Controls Tip Searching
	 *
	 * Contains the Class that Controls Tip Searching
	 *
	 * @file		tips.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Tips
	//----------------------------------------------------------------------------//
	/**
	 * Tips
	 *
	 * Controls Searching for an existing Tip
	 *
	 * Controls Searching for an existing Tip
	 *
	 *
	 * @prefix		tip
	 *
	 * @package		intranet_app
	 * @class		Tips
	 * @extends		dataObject
	 */
	
	class Tips extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs an Tip Searching Routine
		 *
		 * Constructs an Tip Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('Tips', 'Tip', 'Tip');
		}
		
		//------------------------------------------------------------------------//
		// FindRandom
		//------------------------------------------------------------------------//
		/**
		 * FindRandom()
		 *
		 * Get a random tip from the database
		 *
		 * Get a random tip from the database
		 *
		 * @param	Integer		$intType		The Type of Tip which relates to the PABLO_TIP_ constants
		 * @return	Tip
		 *
		 * @method
		 */
		
		public function FindRandom ($intType)
		{
			$selTip = new StatementSelect ('Tip', 'Id', 'TipType = <TipType>', 'RAND()', 1);
			$selTip->Execute (Array ('TipType' => $intType));
			
			if ($selTip->Count () <> 1)
			{
				throw new Exception ('No Tip Found');
			}
			
			$arrTip = $selTip->Fetch ();
			
			return new Tip ($arrTip ['Id']);
		}
	}
	
?>
