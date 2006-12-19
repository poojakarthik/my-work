<?php

	//----------------------------------------------------------------------------//
	// ServiceEndUserTitleTypes.php
	//----------------------------------------------------------------------------//
	/**
	 * ServiceEndUserTitleTypes.php
	 *
	 * Contains the ServiceType object
	 *
	 * Contains the ServiceType object
	 *
	 * @file		ServiceEndUserTitleTypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceEndUserTitleTypes
	//----------------------------------------------------------------------------//
	/**
	 * ServiceEndUserTitleTypes
	 *
	 * Textual Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form ServiceEndUserTitleTypes
	 *
	 * @prefix	etl
	 *
	 * @package	intranet_app
	 * @class	ServiceEndUserTitleTypes
	 * @extends	dataEnumerative
	 */
	
	class ServiceEndUserTitleTypes extends dataEnumerative
	{
		

		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of ServiceEndUserTitleTypes
		 *
		 * Controls a List of ServiceEndUserTitleTypes
		 *
		 * @param	String		$strId			[Optional] A representation of a ServiceEndUserTitleType which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($strId=null)
		{
			parent::__construct ('ServiceEndUserTitleTypes');
			
			// Instantiate the Variable Values for possible selection
			$this->_MASTER		= $this->Push (new ServiceEndUserTitleType (END_USER_TITLE_TYPE_MASTER));
			$this->_MISTER		= $this->Push (new ServiceEndUserTitleType (END_USER_TITLE_TYPE_MISTER));
			$this->_MRS			= $this->Push (new ServiceEndUserTitleType (END_USER_TITLE_TYPE_MRS));
			$this->_MS			= $this->Push (new ServiceEndUserTitleType (END_USER_TITLE_TYPE_MS));
			$this->_MISS		= $this->Push (new ServiceEndUserTitleType (END_USER_TITLE_TYPE_MISS));
			$this->_DOCTOR		= $this->Push (new ServiceEndUserTitleType (END_USER_TITLE_TYPE_DOCTOR));
			$this->_PROFESSOR	= $this->Push (new ServiceEndUserTitleType (END_USER_TITLE_TYPE_PROFESSOR));
			
			$this->setValue ($strId);
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
		 * @param	String		$strId		The value of the ServiceEndUserTitleType Constant wishing to be set
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
