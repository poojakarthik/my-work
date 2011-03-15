<?php
class Dummy_Account_Class extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'account_class';
	protected static 	$_sStaticTableName	= 	'account_class';
	protected 			$_aProperties		= 	array(
													'name' 						=> null,
													'description' 				=> null,
													'collection_scenario_id'	=> null
												);
       public  $bConfig= true;
	
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