<?php

	//----------------------------------------------------------------------------//
	// carriers.php
	//----------------------------------------------------------------------------//
	/**
	 * carriers.php
	 *
	 * Contains the Carriers object
	 *
	 * Contains the Carriers object
	 *
	 * @file		carriers.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Carriers
	//----------------------------------------------------------------------------//
	/**
	 * Carriers
	 *
	 * Textual Carriers Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Carrier Types
	 *
	 * @prefix	cas
	 *
	 * @package	intranet_app
	 * @class	Carriers
	 * @extends	dataEnumerative
	 */
	
	class Carriers extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// _UNITEL
		//------------------------------------------------------------------------//
		/**
		 * _UNITEL
		 *
		 * Used when the Carrier is Unitel
		 *
		 * Used when the Carrier is Unitel
		 *
		 * @type	Carrier
		 *
		 * @property
		 */
		
		private $_UNITEL;
		
		//------------------------------------------------------------------------//
		// _OPTUS
		//------------------------------------------------------------------------//
		/**
		 * _OPTUS
		 *
		 * Used when the Carrier is Optus
		 *
		 * Used when the Carrier is Optus
		 *
		 * @type	Carrier
		 *
		 * @property
		 */
		
		private $_OPTUS;
		
		//------------------------------------------------------------------------//
		// _AAPT
		//------------------------------------------------------------------------//
		/**
		 * _LAND_LINE
		 *
		 * Used when the Carrier is AAPT
		 *
		 * Used when the Carrier is AAPT
		 *
		 * @type	Carrier
		 *
		 * @property
		 */
		
		private $_AAPT;
		
		//------------------------------------------------------------------------//
		// _ISEEK
		//------------------------------------------------------------------------//
		/**
		 * _ISEEK
		 *
		 * Used when the Carrier is ISEEK
		 *
		 * Used when the Carrier is ISEEK
		 *
		 * @type	Carrier
		 *
		 * @property
		 */
		
		private $_ISEEK;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of Carrier Objects
		 *
		 * Controls a List of Carrier Objects
		 *
		 * @param	Integer		$intCarrier			[Optional] An Integer representation of the default Carrier
		 *
		 * @method
		 */
		
		function __construct ($intCarrier=null)
		{
			parent::__construct ('Carriers');
			
			// Instantiate the Variable Values for possible selection
			$this->_UNITEL		= $this->Push (new Carrier (CARRIER_UNITEL));
			$this->_OPTUS		= $this->Push (new Carrier (CARRIER_OPTUS));
			$this->_AAPT		= $this->Push (new Carrier (CARRIER_AAPT));
			$this->_ISEEK		= $this->Push (new Carrier (CARRIER_ISEEK));
			
			$this->setValue ($intCarrier);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected Carrier
		 *
		 * Change the Selected Carrier
		 *
		 * @param	Integer		$intCarrier			The value of the new Carrier Constant
		 * @return	Boolean
		 *
		 * @method
		 */
		
		public function setValue ($intCarrier)
		{
			// Select the value
			switch ($intCarrier)
			{
				case CARRIER_UNITEL:	$this->Select ($this->_UNITEL);	return true;
				case CARRIER_OPTUS:		$this->Select ($this->_OPTUS);	return true;
				case CARRIER_AAPT:		$this->Select ($this->_AAPT);	return true;
				case CARRIER_ISEEK:		$this->Select ($this->_ISEEK);	return true;
				default:												return false;
			}
		}
	}
	
?>
