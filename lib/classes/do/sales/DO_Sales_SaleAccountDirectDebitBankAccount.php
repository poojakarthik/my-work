<?php

class DO_Sales_SaleAccountDirectDebitBankAccount extends DO_Sales_Base_SaleAccountDirectDebitBankAccount
{
	public function __get($name)
	{
		$value = parent::__get($name);
		switch ($name)
		{
			case 'accountNumber':
				$value = Application::decrypt($value);
				$value = substr($value,0,2) 
						. str_repeat('#',  (strlen($value) < 6) ? 0 : (strlen($value) - 6)) 
						. substr($value,-4);
				$value = implode(' ', str_split($value, 4));
				break;
			
			case 'bankBsb':
				$value = Application::decrypt($value);
				break;
			
		}
		
		return $value;
	}
	
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'accountNumber':
			case 'bankBsb':
				$value = preg_replace("/[^0-9]+/", "", $value);
				$value = Application::encrypt($value);
				break;
			
		}

		return parent::__set($name, $value);
	}
}

?>