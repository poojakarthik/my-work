<?php

	//----------------------------------------------------------------------------//
	// natures.php
	//----------------------------------------------------------------------------//
	/**
	 * natures.php
	 *
	 * Contains the nature object
	 *
	 * Contains the nature object
	 *
	 * @file		natures.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// natures
	//----------------------------------------------------------------------------//
	/**
	 * natures
	 *
	 * Textual Nature Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Nature
	 *
	 * @prefix	nat
	 *
	 * @package	intranet_app
	 * @class	natures
	 * @extends	dataEnumerative
	 */
	
	class natures extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of nature
		 *
		 * Controls a List of nature
		 *
		 * @param	Integer		$strNature			[Optional] A representation of a Nature (DR/CR)
		 *
		 * @method
		 */
		
		function __construct ($strNature=null)
		{
			parent::__construct ('Natures');
			
			// Instantiate the Variable Values for possible selection
			$this->_DR		= $this->Push (new Nature (NATURE_DR));
			$this->_CR		= $this->Push (new Nature (NATURE_CR));
			
			$this->setValue ($strNature);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected Nature Type
		 *
		 * Change the Selected Nature Type to another Nature Type
		 *
		 * @param	String		$strNature			The value of the nature Constant wishing to be set
		 * @return	Boolean							Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($strNature)
		{
			// Select the value
			switch ($strNature)
			{
				case NATURE_CR:		$this->Select ($this->_CR);		return true;
				case NATURE_DR:		$this->Select ($this->_DR);		return true;
				default:											return false;
			}
		}
	}
	
?>
