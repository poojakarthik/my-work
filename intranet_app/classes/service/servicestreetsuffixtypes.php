<?php

	//----------------------------------------------------------------------------//
	// ServiceStreetSuffixTypes.php
	//----------------------------------------------------------------------------//
	/**
	 * ServiceStreetSuffixTypes.php
	 *
	 * Contains the ServiceType object
	 *
	 * Contains the ServiceType object
	 *
	 * @file		ServiceStreetSuffixTypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceStreetSuffixTypes
	//----------------------------------------------------------------------------//
	/**
	 * ServiceStreetSuffixTypes
	 *
	 * Textual Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Street Suffix Types
	 *
	 * @prefix	sft
	 *
	 * @package	intranet_app
	 * @class	ServiceStreetSuffixTypes
	 * @extends	dataEnumerative
	 */
	
	class ServiceStreetSuffixTypes extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of ServiceType
		 *
		 * Controls a List of ServiceType
		 *
		 * @param	String		$strId			[Optional] The representation of a Service Street Suffix Type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($strId=null)
		{
			parent::__construct ('ServiceStreetSuffixTypes');
			
			// Instantiate the Variable Values for possible selection
			$this->_CENTRAL		= $this->Push (new ServiceStreetSuffixType (SERVICE_STREET_SUFFIX_TYPE_CENTRAL));
			$this->_EAST		= $this->Push (new ServiceStreetSuffixType (SERVICE_STREET_SUFFIX_TYPE_EAST));
			$this->_EXTENSION	= $this->Push (new ServiceStreetSuffixType (SERVICE_STREET_SUFFIX_TYPE_EXTENSION));
			$this->_LOWER		= $this->Push (new ServiceStreetSuffixType (SERVICE_STREET_SUFFIX_TYPE_LOWER));
			$this->_NORTH		= $this->Push (new ServiceStreetSuffixType (SERVICE_STREET_SUFFIX_TYPE_NORTH));
			$this->_NORTH_EAST	= $this->Push (new ServiceStreetSuffixType (SERVICE_STREET_SUFFIX_TYPE_NORTH_EAST));
			$this->_NORTH_WEST	= $this->Push (new ServiceStreetSuffixType (SERVICE_STREET_SUFFIX_TYPE_NORTH_WEST));
			$this->_SOUTH		= $this->Push (new ServiceStreetSuffixType (SERVICE_STREET_SUFFIX_TYPE_SOUTH));
			$this->_SOUTH_EAST	= $this->Push (new ServiceStreetSuffixType (SERVICE_STREET_SUFFIX_TYPE_SOUTH_EAST));
			$this->_SOUTH_WEST	= $this->Push (new ServiceStreetSuffixType (SERVICE_STREET_SUFFIX_TYPE_SOUTH_WEST));
			$this->_UPPER		= $this->Push (new ServiceStreetSuffixType (SERVICE_STREET_SUFFIX_TYPE_UPPER));
			$this->_WEST		= $this->Push (new ServiceStreetSuffixType (SERVICE_STREET_SUFFIX_TYPE_WEST));
			
			$this->setValue ($strId);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected Service Street Suffix Type
		 *
		 * Change the Selected Service Street Suffix Type to another Service Type
		 *
		 * @param	String		$strId				The value of the ServiceStreetSuffixType Constant wishing to be set
		 * @return	Boolean							Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($strId)
		{
			// Select the value
			switch ($strId)
			{
				case SERVICE_STREET_SUFFIX_TYPE_CENTRAL:		$this->Select ($this->_CENTRAL);		return true;
				case SERVICE_STREET_SUFFIX_TYPE_EAST:			$this->Select ($this->_EAST);			return true;
				case SERVICE_STREET_SUFFIX_TYPE_EXTENSION:		$this->Select ($this->_EXTENSION);		return true;
				case SERVICE_STREET_SUFFIX_TYPE_LOWER:			$this->Select ($this->_LOWER);			return true;
				case SERVICE_STREET_SUFFIX_TYPE_NORTH:			$this->Select ($this->_NORTH);			return true;
				case SERVICE_STREET_SUFFIX_TYPE_NORTH_EAST:		$this->Select ($this->_NORTH_EAST);		return true;
				case SERVICE_STREET_SUFFIX_TYPE_NORTH_WEST:		$this->Select ($this->_NORTH_WEST);		return true;
				case SERVICE_STREET_SUFFIX_TYPE_SOUTH:			$this->Select ($this->_SOUTH);			return true;
				case SERVICE_STREET_SUFFIX_TYPE_SOUTH_EAST:		$this->Select ($this->_SOUTH_EAST);		return true;
				case SERVICE_STREET_SUFFIX_TYPE_SOUTH_WEST:		$this->Select ($this->_SOUTH_WEST);		return true;
				case SERVICE_STREET_SUFFIX_TYPE_UPPER:			$this->Select ($this->_UPPER);			return true;
				case SERVICE_STREET_SUFFIX_TYPE_WEST:			$this->Select ($this->_WEST);			return true;
				default:																				return false;
			}
		}
	}
	
?>
