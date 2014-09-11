<?php

class DO_Sales_ContactMethod extends DO_Sales_Base_ContactMethod
{
	private $_doContactMethodType = null;
	
	public function __set($propertyName, $value)
	{
		if ($value !== null)
		{
			// Only string values need to be sanitized at this high a level.  Everything else is done at a lower level
			switch ($propertyName)
			{
				case 'details':
					$objContactMethodType = $this->getContactMethodType();
					if ($objContactMethodType === null)
					{
						// The contact method type has not yet been established.  Do generic sanitation
						$value = DO_SalesSanitation::removeExcessWhitespace($value);
					}
					else
					{
						$value = $objContactMethodType->cleanContactMethodDetails($value);
					}
					break;
			}
		}

		return parent::__set($propertyName, $value);
	}

	public function isValid($bolThrowException=false)
	{
		// Make sure the contact method type exists
		$objContactMethodType = $this->getContactMethodType();
		if ($objContactMethodType == null)
		{
			if (!$bolThrowException)
			{
				return false;
			}
			else 
			{
				throw new DO_Exception_Validation($this->getObjectLabel(), "Unknown contact method type.");
			}
		}
		
		// Run the standard validation
		try
		{
			return parent::isValid($bolThrowException);
		}
		catch (DO_Exception_Validation $e)
		{
			// Mention the type of contact method
			throw new DO_Exception_Validation($this->getObjectLabel() ." ". $objContactMethodType->name, $e->errors); 
		}
	}
	
	protected function _isValidValue($propertyName, $value)
	{
		// This bit does low-level validation based on the associated field of the database table that the class represents.
		// It handles things such as string length, data type and nullability constraints
		if (!parent::_isValidValue($propertyName, $value))
		{
			return false;
		}

		if ($value === null)
		{
			// We have already done the low level check to see if the field is manditory, so if the value is still set to null, then it should be considered valid.
			// Although this doesn't take into account scenarios where a value can only be set to null, when some other value is set to a specific value.
			// Validation rules of that nature should be declared in the class' isValid() method
			return true;
		}

		switch ($propertyName)
		{
			case 'details':
				$objContactMethodType = $this->getContactMethodType();
				if ($objContactMethodType === null)
				{
					// The contact method type has not been established.  I should probably throw an exception here, but instead I'll just return false
					return false;
				}
				else
				{
					return $objContactMethodType->isValidContactMethodDetails($value);
				}
				break;
		}
		
		// No extra validation - assume is correct
		return true;
	}
	
	public function getContactMethodType()
	{
		if ($this->contactMethodTypeId === null)
		{
			$this->_doContactMethodType = null;
			return $this->_doContactMethodType;
		}
		else
		{
			// See if we already have it cached
			if ($this->_doContactMethodType !== null && $this->_doContactMethodType->id == $this->contactMethodTypeId)
			{
				// The object is already loaded
				return $this->_doContactMethodType;
			}
			
			// Retrieve the object
			$this->_doContactMethodType = parent::getContactMethodType();
			if ($this->_doContactMethodType === null)
			{
				throw new Exception("Could not retrieve ContactMethodType object with id: {$this->contactMethodTypeId}");
			}
			return $this->_doContactMethodType;
		}
	}
}

?>
