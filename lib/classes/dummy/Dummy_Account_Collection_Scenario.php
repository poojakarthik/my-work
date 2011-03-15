<?php
class Dummy_Account_Collection_Scenario extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'account_collection_scenario';
	protected static 	$_sStaticTableName	= 	'account_collection_scenario';
	protected 			$_aProperties		= 	array(
													'account_id' 				=> null,
													'collection_scenario_id' 	=> null,
													'created_datetime'			=> null,
													'start_datetime'			=> null,
													'end_datetime'				=> null
												);
	
	public function __construct($aProperties=array(), $bLoadById=false)
	{
		parent::__construct($aProperties, $bLoadById);
	}
	
	public static function getForAccountId($iAccountId)
	{
		return self::getFor(array('account_id' => $iAccountId));
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
	
	public static function getFor($aCriteria)
	{
		return Dummy::getFor(get_class(), $aCriteria);
	}
	// END: REQUIRED FUNCTIONS
}
?>