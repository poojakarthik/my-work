<?php

class JSON_Handler_ProductTypeModule extends JSON_Handler
{

	function loadData($strModule)
	{
		$strFunctionName = __FUNCTION__;
		
		$objModule = DO_Sales_ProductType::getForModule($strModule);
		if ($objModule === null)
		{
			throw new Exception("Module '$strModule' does not exists.");
		}
		
		$strModuleClassName = 'Product_Type_Module_' . $strModule;
		
		if (!class_exists($strModuleClassName, true))
		{
			throw new Exception("Module class '$strModuleClassName' could not be found.");
		}
		
		if (!method_exists($strModuleClassName, $strFunctionName))
		{
			throw new Exception("Module does not support the requested funtion '$strFunctionName'.");
		}
		
		$instance = new $strModuleClassName();
		
		$args = func_get_args();
		array_shift($args);
		
		return call_user_func_array(array($instance, $strFunctionName), $args);
	}

}

?>
