<?php
class Dummy_Constant extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'';
	protected static 	$_sStaticTableName	= 	'';
	protected 			$_aProperties		= 	array(
													'name'			=> null,
													'description'	=> null,
													'const_name'	=> null,
													'system_name'	=> null
												);
	
	public function __construct($sTableName, $aProperties=array(), $bLoadById=false)
	{
		$this->_sTableName	= $sTableName;
		parent::__construct($aProperties, $bLoadById);
	}
	
	// Override
	public function save()
	{
		parent::save();
		
		if ($this->getId() !== null)
		{
			$GLOBALS['*arrConstant'][$this->_sTableName][$this->id]['Name']			= $this->name;
			$GLOBALS['*arrConstant'][$this->_sTableName][$this->id]['Description']	= $this->description;
			$GLOBALS['*arrConstant'][$this->_sTableName][$this->id]['Constant']		= $this->const_name;
			define($this->const_name, $this->id);
		}
		
		return $this->getId();
	}
	
	// Override
	protected function _getClassName()
	{
		return $this->_sTableName;
	}
}
?>