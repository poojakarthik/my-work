<?php
	
	//----------------------------------------------------------------------------//
	// documentationentity.php
	//----------------------------------------------------------------------------//
	/**
	 * documentationentity.php
	 *
	 * Documentation Information for an Entity
	 *
	 * Contains information about a particular entity
	 *
	 * @file	documentationentity.php
	 * @language	PHP
	 * @package	intranet_app
	 * @author	Bashkim 'bash' Isai
	 * @version	6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license	NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// DocumentationEntity
	//----------------------------------------------------------------------------//
	/**
	 * DocumentationEntity
	 *
	 * DocumentationEntity Controller
	 *
	 * Retrieves information from the Database which contains data about a 
	 * particular entity.
	 *
	 *
	 * @prefix	den
	 *
	 * @package	intranet_app
	 * @class	DocumentationEntity
	 * @extends	dataCollection
	 */
	
	class DocumentationEntity extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblstrName
		//------------------------------------------------------------------------//
		/**
		 * _oblstrName
		 *
		 * The name of the Entity
		 *
		 * The name of the Entity
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrName;
		
		//------------------------------------------------------------------------//
		// _oblarrFields
		//------------------------------------------------------------------------//
		/**
		 * _oblarrFields
		 *
		 * Fields associated with an Entity
		 *
		 * Fields associated with an Entity
		 *
		 * @type	dataArray
		 *
		 * @property
		 */
		 
		private $_oblarrFields;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a DocumentationEntity Container
		 *
		 * Holds the Fields that belong to a particular Documentation Entity.
		 *
		 * @param	String		$strEntity		The name of the Entity we wish to view Documentation for.
		 *
		 * @method
		 */
		
		function __construct ($strEntity)
		{
			parent::__construct ('Entity', $strEntity);
			
			$this->_oblstrName = $this->Push (new dataString ('Name', $strEntity));
			$this->_oblarrFields = $this->Push (new dataArray ('Fields', 'DocumentationField'));
			
			// Get each Field for this Entity
			$selEntities = new StatementSelect ('Documentation', '*', 'Entity = <Entity>');
			$selEntities->Execute(Array('Entity' => $strEntity));
			
			// Put all the related fields for this entity on the Object
			foreach ($selEntities->FetchAll () AS $arrEntity)
			{
				$this->_oblarrFields->Push (new DocumentationField ($arrEntity));
			}
		}
		
	}
	
?>
