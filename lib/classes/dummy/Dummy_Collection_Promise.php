<?php
class Dummy_Collection_Promise extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collection_promise';
	protected static 	$_sStaticTableName	= 	'collection_promise';
	protected 			$_aProperties		= 	array(

													'account_id'						=> null,
													'collection_scenario_id'			=> null,
													'created_datetime'					=> null,
													'created_employee_id'				=> null,
													'completed_datetime'				=> null,
													'collection_promise_completion_id'	=> null,
													'completed_employee_id'				=> null
												);
	
	public static function getActivePromises() 
        {
            return self::getFor(array('completed_datetime' => null));
        }

        public function __construct($aProperties=array(), $bLoadById=false)
	{
		parent::__construct($aProperties, $bLoadById);
	}
	
	public function getForAccountId($iAccountId)
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