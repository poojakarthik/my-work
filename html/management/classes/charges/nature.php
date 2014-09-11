<?php

	//----------------------------------------------------------------------------//
	// nature.php
	//----------------------------------------------------------------------------//
	/**
	 * nature.php
	 *
	 * Contains the nature object
	 *
	 * Contains the nature object
	 *
	 * @file		nature.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// nature
	//----------------------------------------------------------------------------//
	/**
	 * nature
	 *
	 * Allows Textual (named) Representation of the Constants which form a Nature (DR/CR)
	 *
	 * Allows Textual (named) Representation of the Constants which form a Nature (DR/CR)
	 *
	 *
	 * @prefix	nat
	 *
	 * @package	intranet_app
	 * @class	nature
	 * @extends	dataEnumerative
	 */
	
	class nature extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblstrType
		//------------------------------------------------------------------------//
		/**
		 * _oblstrType
		 *
		 * The Id of the Nature
		 *
		 * The Id of the Nature
		 *
		 * @type	dataInteger
		 *
		 * @property
		 */
		
		private $_oblstrType;
		
		//------------------------------------------------------------------------//
		// _oblstrName
		//------------------------------------------------------------------------//
		/**
		 * _oblstrName
		 *
		 * The name of the Nature Type
		 *
		 * The name of the Nature Type
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrName;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Holds Nature Information
		 *
		 * Holds Nature Information
		 *
		 * @param	String		$strNature			The Id of the Nature
		 *
		 * @method
		 */
		
		function __construct ($strNature)
		{
			parent::__construct ('Nature');
			
			$strName = 'Unknown';
			
			switch ($strNature)
			{
				case NATURE_CR:
					$strName = 'Credit';
					break;
					
				case NATURE_DR:
					$strName = 'Debit';
					break;
			}
			
			$this->oblstrType		= $this->Push (new dataString	('Id',		$strNature));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>
