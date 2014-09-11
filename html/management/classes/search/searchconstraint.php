<?php
	
	//----------------------------------------------------------------------------//
	// searchconstraint.php
	//----------------------------------------------------------------------------//
	/**
	 * searchconstraint.php
	 *
	 * File that holds a Search Constraint Class
	 *
	 * File that holds a Search Constraint Class
	 *
	 * @file		searchconstraint.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// SearchConstraint
	//----------------------------------------------------------------------------//
	/**
	 * SearchConstraint
	 *
	 * A Search Constraint
	 *
	 * A Class that is used by the Search class to store constraints against an item
	 *
	 *
	 * @prefix		sec
	 *
	 * @package		intranet_app
	 * @class		SearchConstraint
	 * @extends		dataObject
	 */
	
	class SearchConstraint extends dataObject
	{
		//------------------------------------------------------------------------//
		// _oblstrConstraintName
		//------------------------------------------------------------------------//
		/**
		 * _oblstrConstraintName
		 *
		 * The Field of the Constraint
		 *
		 * The Field of the Constraint
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrConstraintName;
		
		//------------------------------------------------------------------------//
		// _oblstrConstraintType
		//------------------------------------------------------------------------//
		/**
		 * _oblstrConstraintType
		 *
		 * The Constraint Type
		 *
		 * The Constraint Type (LIKE, SOUNDS LIKE, ... whatever)
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrConstraintType;
		
		//------------------------------------------------------------------------//
		// _oblobjConstraintValue
		//------------------------------------------------------------------------//
		/**
		 * _oblobjConstraintValue
		 *
		 * The Constraint Value
		 *
		 * The Constraint Type (LIKE, SOUNDS LIKE, ... whatever)
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblobjConstraintValue;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Construct a new Search Constraint
		 *
		 * Construct a new Search Constraint
		 *
		 * @param	String		$strConstraintName		The Field being Constrained (Antecedent)
		 * @param	String		$strConstraintType		The Type of Constraint (LIKE, ... ) (Operator)
		 * @param	String		$strConstraintObject	The Type of Object the Constraint is (dataInteger, ...)
		 * @param	String		$strConstraintValue		The Value of Object Constrained Against (Consequent)
		 *
		 * @method
		 */
		
		function __construct ($strConstraintName, $strConstraintType, $strConstraintObject, $strConstraintValue)
		{
			$this->_oblstrConstraintName = $this->Push (new dataString ('Name', $strConstraintName));
			$this->_oblstrConstraintType = $this->Push (new dataString ('Operator', $strConstraintType));
			$this->_oblobjConstraintValue = $this->Push (new $strConstraintObject ('Value', $strConstraintValue));
			
			parent::__construct ('Constraint');
		}
		
		//------------------------------------------------------------------------//
		// getName
		//------------------------------------------------------------------------//
		/**
		 * getName()
		 *
		 * Get the Name of the Field
		 *
		 * Get the Name of the Field
		 *
		 * @return	String
		 *
		 * @method
		 */
		
		public function getName ()
		{
			return $this->_oblstrConstraintName->getValue ();
		}
		
		//------------------------------------------------------------------------//
		// getOperator
		//------------------------------------------------------------------------//
		/**
		 * getOperator()
		 *
		 * Get the Operator of the Constraint
		 *
		 * Get the Operator of the Constraint
		 *
		 * @return	String
		 *
		 * @method
		 */
		
		public function getOperator ()
		{
			switch ($this->_oblstrConstraintType->getValue ())
			{
				case 'LIKE':
					return 'LIKE';
				case '=':
				case 'EQUALS':
					return '=';
				case '!=':
				case 'NOT EQUAL':
					return '!=';
				case 'AND':
				case '&':
					return '&'; 
				case 'OR':
				case '|':
					return '|'; 
				case 'XOR':
				case '^':
					return '^'; 
				default:
					throw new Exception (
						'The Constraint Operator that you wished to search with is Invalid.'
					);
			}
		}
		
		//------------------------------------------------------------------------//
		// getValue
		//------------------------------------------------------------------------//
		/**
		 * getValue()
		 *
		 * Get the Processed Value of the Constraint
		 *
		 * Get the Processed Value of the Constraint (LIKE constraints will have %...%) etc
		 *
		 * @return	String
		 *
		 * @method
		 */
		
		public function getValue ()
		{
			switch ($this->_oblstrConstraintType->getValue ())
			{
				case 'LIKE':
					return $this->_processLIKE ();
				default:
					return $this->_processEQUALS ();
			}
		}
		
		//------------------------------------------------------------------------//
		// _processLIKE
		//------------------------------------------------------------------------//
		/**
		 * _processLIKE()
		 *
		 * Process LIKE Constraints
		 *
		 * Process LIKE Constraints (adds %...%)
		 *
		 * @return	String
		 *
		 * @method
		 */
		
		private function _processLIKE ()
		{
			return '%' . $this->_oblobjConstraintValue->getValue () . '%';
		}
		
		//------------------------------------------------------------------------//
		// _processEQUALS
		//------------------------------------------------------------------------//
		/**
		 * _processEQUALS
		 *
		 * Process EQUALS Constraints
		 *
		 * Process EQUALS Constraints - doesn't do much - this is just for future expansion
		 *
		 * @return	Mixed
		 *
		 * @method
		 */
		
		private function _processEQUALS ()
		{
			return $this->_oblobjConstraintValue->getValue ();
		}
	}
	
?>
