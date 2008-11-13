<?php

class DO_Sales_SaleItemServiceInbound extends DO_Sales_Base_SaleItemServiceInbound
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
				return preg_match("/^((13\\d{4})|(1[389]00\\d{6}))$/", $value);

			default:
				// No validation - assume is correct already as is not for data source
				return true;

		}
	}
}

?>