<?php
class Dummy_Customer_Group extends Dummy
{
	protected 			$_sIdField			= 	'Id';
	protected 			$_sTableName		= 	'CustomerGroup';
	protected static 	$_sStaticTableName	= 	'CustomerGroup';
	protected 			$_aProperties		= 	array(
													'name'						=> null,
													'default_account_class_id' 	=> null
												);
        public $bConfig = true;
	
	public function __construct($aProperties=array(), $bLoadById=false)
	{
		parent::__construct($aProperties, $bLoadById);
	}
	
	// START: REQUIRED FUNCTIONS
	public static function getForId($iId)
	{
		return Dummy::getForId(get_class(), $iId);
	}
	
	public static function getAll()
	{
		return Dummy::getAll(get_class());
	}
	// END: REQUIRED FUNCTIONS
}
?>