<?php

class DO_Sales_SaleItemServiceAdsl extends DO_Sales_Base_SaleItemServiceAdsl
{
	protected function _isValidValue($propertyName, $value)
	{
		if (!parent::_isValidValue($propertyName, $value))
		{
			return false;
		}

		switch ($propertyName)
		{

			case 'fnn':
				return preg_match("/^0[12378]\\d{8}$/", $value);

			case 'postcode':
				return preg_match("/^\\d{4}$/", $value);

			default:
				// No more validation - assume is correct
				return true;

		}
	}
}

?>