<?php

	//----------------------------------------------------------------------------//
	// RecordDisplayTypes.php
	//----------------------------------------------------------------------------//
	/**
	 * RecordDisplayTypes.php
	 *
	 * Contains the RecordDisplayType object
	 *
	 * Contains the RecordDisplayType object
	 *
	 * @file		RecordDisplayTypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// RecordDisplayTypes
	//----------------------------------------------------------------------------//
	/**
	 * RecordDisplayTypes
	 *
	 * Textual Record Display Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Record Display Types
	 *
	 * @prefix	rdl
	 *
	 * @package	intranet_app
	 * @class	RecordDisplayTypes
	 * @extends	dataEnumerative
	 */
	
	class RecordDisplayTypes extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of Record Display Type
		 *
		 * Controls a List of Record Display Type
		 *
		 * @param	Integer		$intRecordDisplayType			[Optional] An Integer representation of a RecordDisplay type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($intRecordDisplayType=null)
		{
			parent::__construct ('RecordDisplayTypes');
			
			foreach ($GLOBALS['RecordDisplayRateName'] AS $intType => $strName)
			{
				$this->_arrTypes [$intType] = $this->Push (new RecordDisplayType ($intType));
			}
			
			$this->setValue ($intRecordDisplayType);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected RecordDisplay Type
		 *
		 * Change the Selected RecordDisplay Type to another RecordDisplay Type
		 *
		 * @param	Integer		$intRecordDisplayType		The value of the RecordDisplayType Constant wishing to be set
		 * @return	Boolean									Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($intRecordDisplayType)
		{
			if (isset ($this->_arrTypes [$intRecordDisplayType]))
			{
				$this->Select ($this->_arrTypes [$intRecordDisplayType]);
				return true;
			}
			
			return false;
		}
	}
	
?>
