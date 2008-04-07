<?php

	//----------------------------------------------------------------------------//
	// TitleTypes.php
	//----------------------------------------------------------------------------//
	/**
	 * TitleTypes.php
	 *
	 * Contains the ServiceType object
	 *
	 * Contains the ServiceType object
	 *
	 * @file		TitleTypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// TitleTypes
	//----------------------------------------------------------------------------//
	/**
	 * TitleTypes
	 *
	 * Textual Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form TitleTypes
	 *
	 * @prefix	tts
	 *
	 * @package	intranet_app
	 * @class	TitleTypes
	 * @extends	dataEnumerative
	 */
	
	class TitleTypes extends dataEnumerative
	{
		

		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of TitleTypes
		 *
		 * Controls a List of TitleTypes
		 *
		 * @param	String		$strId			[Optional] A representation of a TitleType which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($strId=null)
		{
			parent::__construct ('TitleTypes');
			
			// Instantiate the Variable Values for possible selection
			$this->_MASTER		= $this->Push (new TitleType (END_USER_TITLE_TYPE_MASTER));
			$this->_MISTER		= $this->Push (new TitleType (END_USER_TITLE_TYPE_MISTER));
			$this->_MRS			= $this->Push (new TitleType (END_USER_TITLE_TYPE_MRS));
			$this->_MS			= $this->Push (new TitleType (END_USER_TITLE_TYPE_MS));
			$this->_MISS		= $this->Push (new TitleType (END_USER_TITLE_TYPE_MISS));
			$this->_DOCTOR		= $this->Push (new TitleType (END_USER_TITLE_TYPE_DOCTOR));
			$this->_PROFESSOR	= $this->Push (new TitleType (END_USER_TITLE_TYPE_PROFESSOR));
			
			if ($strId !== null)
			{
				$this->setValue ($strId);
			}
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected Service Type
		 *
		 * Change the Selected Service Type to another Service Type
		 *
		 * @param	String		$strId		The value of the TitleType Constant wishing to be set
		 * @return	Boolean					Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($strId)
		{
			// Select the value
			switch ($strId)
			{
				case END_USER_TITLE_TYPE_MASTER:		$this->Select ($this->_MASTER);			return true;
				case END_USER_TITLE_TYPE_MISTER:		$this->Select ($this->_MISTER);			return true;
				case END_USER_TITLE_TYPE_MRS:			$this->Select ($this->_MRS);			return true;
				case END_USER_TITLE_TYPE_MS:			$this->Select ($this->_MS);				return true;
				case END_USER_TITLE_TYPE_MISS:			$this->Select ($this->_MISS);			return true;
				case END_USER_TITLE_TYPE_DOCTOR:		$this->Select ($this->_DOCTOR);			return true;
				case END_USER_TITLE_TYPE_PROFESSOR:		$this->Select ($this->_PROFESSOR);		return true;
				default:																		return false;
			}
		}
	}
	
?>
