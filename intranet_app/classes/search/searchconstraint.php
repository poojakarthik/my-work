<?php
	
	class SearchConstraint extends dataObject
	{
		
		private $_oblstrConstraintName;
		private $_oblstrConstraintType;
		private $_oblobjConstraintValue;
		
		function __construct ($strConstraintName, $strConstraintType, $strConstraintObject, $strObjectValue)
		{
			switch ($strConstraintType)
			{
				case 'LIKE':
					break;
				case '=':
				case 'EQUALS':
					$strConstraintType = '=';
					break;
				default:
					throw new Exception (
						'The Constraint Operator that you wished to search with is Invalid.'
					);
			}
			
			$this->_oblstrConstraintName = $this->Push (new dataString ('Name', $strConstraintName));
			$this->_oblstrConstraintType = $this->Push (new dataString ('Operator', $strConstraintType));
			$this->_oblobjConstraintValue = $this->Push (new $strConstraintObject ('Value', $strObjectValue));
			
			parent::__construct ('Constraint');
		}
		
		public function getName ()
		{
			return $this->_oblstrConstraintName->getValue ();
		}
		
		public function getOperator ()
		{
			return $this->_oblstrConstraintType->getValue ();
		}
		
		public function getValue ()
		{
			switch ($this->getOperator ())
			{
				case 'LIKE':
					return $this->_processLIKE ();
				case '=':
					return $this->_processEQUALS ();
			}
		}
		
		private function _processLIKE ()
		{
			return '%' . $this->_oblobjConstraintValue->getValue () . '%';
		}
		
		private function _processEQUALS ()
		{
			return $this->_oblobjConstraintValue->getValue ();
		}
	}
	
?>
