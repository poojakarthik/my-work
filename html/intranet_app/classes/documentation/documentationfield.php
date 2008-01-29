<?php
	
	//----------------------------------------------------------------------------//
	// documentationfield.php
	//----------------------------------------------------------------------------//
	/**
	 * documentationfield.php
	 *
	 * Documentation Information for an Entity's Field
	 *
	 * Contains information about a particular Entity's Field
	 *
	 * @file	documentationfield.php
	 * @language	PHP
	 * @package	intranet_app
	 * @author	Bashkim 'bash' Isai
	 * @version	6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license	NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// DocumentationField
	//----------------------------------------------------------------------------//
	/**
	 * DocumentationField
	 *
	 * DocumentationField Controller
	 *
	 * Retrieves information from the Database which contains data about a 
	 * particular entity's field.
	 *
	 *
	 * @prefix	dfl
	 *
	 * @package	intranet_app
	 * @class	DocumentationField
	 * @extends	dataObject
	 */
	
	class DocumentationField extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// DocumentationEntity
		//------------------------------------------------------------------------//
		/**
		 * DocumentationEntity()
		 *
		 * Constructs a DocumentationEntity Container
		 *
		 * Holds the Fields that belong to a particular Documentation Entity.
		 *
		 * @param	String		$arrDetails		The details of the Entity
		 *
		 * @method
		 */
		
		function __construct ($arrDetails)
		{
			parent::__construct ('Field');
			
			$this->Push (new dataInteger	('Id',			$arrDetails ['Id']));
			$this->Push (new dataString		('Entity',		$arrDetails ['Entity']));
			$this->Push (new dataString		('Field',		$arrDetails ['Field']));
			$this->Push (new dataString		('Label',		$arrDetails ['Label']));
			$this->Push (new dataString		('Title',		$arrDetails ['Title']));
			$this->Push (new dataString		('Description',	nl2br ($arrDetails ['Description'])));
		}
		
	}
	
?>
