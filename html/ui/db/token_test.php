<?php
// CULL :: This can't be being used (?) as it would have both function and class name conflicts with functions.php & framework.php
// Database Object
class Honda
{
	function __construct()
	{
		$this->_arrProperties = Array();
		$this->_arrProperties['Prelude']	['Type']	= "VTi-R";
		$this->_arrProperties['NSX']		['Type']	= "GT";
		$this->_arrProperties['Legend']		['Type']	= "4WSH";
	}
	
	function __get($strName)
	{
		if ($this->_arrProperties[$strName])
		{
			return Tokenise()->Property($this, $strName);
		}
	}
	
	/*function __set($strName, $mixValue)
	{
		$this->_arrProperties[$strName] = Array();
		return Tokenise()->Property($this, $strName);
	}*/
}

// Token Object
class Token
{
	public $c;
	public $_dboObject;
	public $_strProperty;
	
	function __construct()
	{
		$this->_dboObject	= NULL;
		$this->_strProperty	= NULL;
	}
	
	function Property($dboObject, $strName)
	{
		$this->_dboObject	= $dboObject;
		$this->_strProperty	= $strName;
		return $this;
	}
	
	function __get($strName)
	{
		echo "{$strName}\t= ";
		if (isset($this->_dboObject->_arrProperties[$this->_strProperty][$strName]))
		{
			return $this->_dboObject->_arrProperties[$this->_strProperty][$strName];
		}
	}
	
	function __set($strName, $mixValue)
	{		
		// TODO: Validate
		
		// Set the value
		$this->_dboObject->_arrProperties[$this->_strProperty][$strName] = $mixValue;
	}
	
	function __call($strFunction, $arrArguments)
	{
		// call private method
		$strPrivateMethod = "_$strFunction";
		if (method_exists($this, $strPrivateMethod))
		{
			$arrCallback = Array($this, $strPrivateMethod);
			return call_user_func_array($arrCallback, $arrArguments);
		}
		else
		{
			return FALSE;
		}
	}
	
	private function _Render()
	{
		echo "\n*** ***\n";
	}
}

// DBO
class DBO
{
	function __construct()
	{
		$this->_arrProperties = Array();
		$this->_arrProperties['Honda']	= new Honda();
	}
	
	function __get($strName)
	{
		return $this->_arrProperties[$strName];
	}
}

//----------------------------------------------------------------------------//

$GLOBALS['*tokTokenObject'] = new Token();
$GLOBALS['*dboDBO']			= new DBO();

function Tokenise()
{
	return $GLOBALS['*tokTokenObject'];
}


function DBO()
{
	return $GLOBALS['*dboDBO'];
}




echo (float)NULL + 100;
die;

echo DBO()->Honda->Prelude->Type ."\n";
DBO()->Honda->Prelude->Colour = "Black";
echo DBO()->Honda->Prelude->Colour."\n";

DBO()->Honda->Prelude->Render();

?>