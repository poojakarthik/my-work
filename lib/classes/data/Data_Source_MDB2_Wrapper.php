<?php
class Data_Source_MDB2_Wrapper extends stdClass
{
	private $_objMDB2;
	private $_strName;
	
	public function __construct($objMDB2, $strName=NULL)
	{
		$this->_objMDB2 = $objMDB2;
		
		// Give the datasource a unique id, if one isn't specified
		$this->_strName = ($strName === NULL)? uniqid() : $strName;
	}
	
	public function getName()
	{
		return $this->_strName;
	}
	
	public function __get($strProp)
	{
		return $this->_objMDB2->{$strProp};
	}
	
	public function __set($strProp, $mixValue)
	{
		$this->_objMDB2->{$strProp} = $mixValue;
	}
	
	public function __call($strFunc, $arrArgs)
	{
		return call_user_func_array(array($this->_objMDB2, $strFunc), $arrArgs);
	}
}

?>
