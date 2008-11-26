<?php

class DO_Sales_SaleItemServiceMobile extends DO_Sales_Base_SaleItemServiceMobile
{
	protected function _isValidValue($propertyName, $value)
	{
		if (!parent::_isValidValue($propertyName, $value))
		{
			return false;
		}

		switch ($propertyName)
		{
			case 'currentAccountNumber':
				// Required for Existing Post-Paid (3)
				if ($value == null && $this->serviceMobileOriginId == 3) // WIP - Code this properly!!!
				{
					return false;
				}
				return true;

			case 'dob':
				// Required for Existing Pre-Paid (2)
				if ($value == null && $this->serviceMobileOriginId == 2) // WIP - Code this properly!!!
				{
					return false;
				}
				return true;
			
			case 'fnn':
				if ($value == null || $value == '') 
				{
					// Required for Existing Post-Paid (3) and Existing Pre-Paid (2)
					if ($this->serviceMobileOriginId == 2 || $this->serviceMobileOriginId == 3) // WIP - Code this properly!!!
					{
						return false;
					}
					
					$saleItem = $this->getSaleItem();
					if ($saleItem->saleItemStatusId == DO_Sales_SaleItemStatus::DISPATCHED || $saleItem->saleItemStatusId == DO_Sales_SaleItemStatus::COMPLETED)
					{
						return false;
					}
					return true;
				}
				return preg_match("/^04\\d{8}$/", $value);

			default:
				// No validation - assume is correct already as is not for data source
				return true;

		}
	}
}

?>
