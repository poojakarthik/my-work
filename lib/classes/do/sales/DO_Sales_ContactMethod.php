<?php

class DO_Sales_ContactMethod extends DO_Sales_Base_ContactMethod
{
	public function isValid($bolThrowException=false)
	{
		$props = $this->getPropertyNames();
		$errors = array();

		$contactMethod = DO_Sales_ContactMethodType::getForId($this->contactMethodTypeId);
		if ($contactMethod == null)
		{
			if (!$bolThrowException) 
			{
				return false;
			}
			else 
			{
				throw new Exception($this->getObjectLabel() . " has an invalid contact method type.");
			}
		}
				
		for ($i = 0, $l = count($props); $i < $l; $i++)
		{
			if (!$this->_isValidValue($props[$i], $this->properties[$props[$i]]))
			{
				if (!$bolThrowException) 
				{
					return false;
				}
				$errors[] = "Invalid value specified for '" . $contactMethod->name . ' ' . $this->getPropertyLabel($props[$i]) . "'.";// . $this->{$props[$i]};
			}
		}
		if (count($errors))
		{
			throw new DO_Validation_Exception($this->getObjectLabel() . " is invalid:\n\t" . implode("\n\t", $errors));
		}
		return true;
	}
	
	protected function _isValidValue($propertyName, $value)
	{
		if (!parent::_isValidValue($propertyName, $value))
		{
			return false;
		}

		switch ($propertyName)
		{

			case 'details':
			
				switch ($this->contactMethodTypeId)
				{

					case 1: // WIP: Code this properly! 1 = Email
						return preg_match("/^[a-z0-9!#\$%&'\*\+\/=\?\^_`\{\|\}~\-]+(?:\.[a-z0-9!#\$%&'\*\+\/=\?\^_`\{\|\}~\-]+)*@(?:[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?$/", $value);
					
					case 2: // WIP: Code this properly! 2 = Fax
					case 3: // WIP: Code this properly! 3 = Phone
						return preg_match("/^0[12378]\\d{8}$/", $value);
					
					case 4: // WIP: Code this properly! 4 = Mobile
						return preg_match("/^04\\d{8}$/", $value);
				}

			default:
				// No extra validation - assume is correct
				return true;

		}
	}
}

?>
