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
		 * @param	String		$strEntity		The name of the Entity for the Field
		 * @param	String		$strField		The name of the Field where we want Information for
		 *
		 * @method
		 */
		
		function __construct ($strEntity, $strField)
		{
			parent::__construct ('Field');
			
			$selFields = new StatementSelect (
				"Documentation",
				"*", 
				"Entity = <Entity> AND Field = <Field>",
				null,
				"1"
			);
			
			$selFields->UseObLib (TRUE);
			$selFields->Execute(Array("Entity" => $strEntity, "Field" => $strField));
			$selFields->Fetch ($this);
		}
		
	}
	
?>
