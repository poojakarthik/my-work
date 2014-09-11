<?php

// This class should be instantiated using:
// $objAccount = new ModuleAccount();
// from anywhere within the ui_app framework and its various templates
// Or you could use the ApplicationTemplate->Modules->__get method to instantiate and manage the modules
class ModuleAccount
{
	private		$_intId;
	protected	$_arrProperties;
	private		$_strErrorMsg;
	
	private		$_bolLoaded;
	
	function __constructor($intAccountId)
	{
		$this->_intId = $intAccountId;
		$this->_LoadDetails();
	}
	
	function Loaded()
	{
		return $this->_bolLoaded;
	}
	
	function IsOk()
	{
		return isset($_strErrorMsg);
	}
	
	protected function _LoadDetails()
	{
		$intAccount			= $this->_intId;
		$this->_bolLoaded	= FALSE;
		$arrColumns = array("Id"			=> "Id",
							"BusinessName"	=> "BusinessName",
							"TradingName"	=> "TradingName",
							"Name"			=> "CASE WHEN BusinessName != \"\" THEN BusinessName WHEN TradingName != \"\" THEN TradingName ELSE NULL END",
							"AccountGroup"	=> "AccountGroup",
							"Status"		=> "Status",
							"CreatedOn"		=> "CreatedOn",
							"ClosedOn"		=> "ClosedOn",
							"CustomerGroup"	=> "CustomerGroup"
							);
		$strWhere	= "Id = <Id>";
		$selAccount = new StatementSelect("Account", $arrColumns, $strWhere);
		if ($selAccount->Execute(array("Id" => $intAccount)) === FALSE)
		{
			$this->_strErrorMsg	= "Retrieving the Account record failed unexpectedly";
			return FALSE;
		}
		if (($this->_arrProperties = $selAccount->Fetch()) === FALSE)
		{
			// Could not find the account
			$this->_strErrorMsg	= "Could not find account with Id: $intAccount";
			return FALSE;
		}
		
		$this->_bolLoaded = TRUE;
		return TRUE;
	}
	
	function __get($strProperty)
	{
		if (!is_array($this->_arrProperties))
		{
			throw new Exception("No properties are defined");
		}
		elseif (array_key_exists($strProperty, $this->_arrProperties))
		{
			return $this->_arrProperties[$strProperty];
		}
		else
		{
			throw new Exception("$strProperty is not a property of the Account object");
		}
	}
	
	static function GetAccountById($intAccountId)
	{
		//TODO
	}
	
}

?>
