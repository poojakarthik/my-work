<?php
class Dummy_Collection_Promise_Instalment extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collection_promise_instalment';
	protected static 	$_sStaticTableName	= 	'collection_promise_instalment';
	protected 			$_aProperties		= 	array(
													'collection_promise_id'	=> null,
													'due_date'				=> null,
													'amount'				=> null
												);
	
	public function __construct($aProperties=array(), $bLoadById=false)
	{
		parent::__construct($aProperties, $bLoadById);
	}
	
	public static function getForPromiseId($iPromiseId)
	{
		return self::getFor(array('collection_promise_id' => $iPromiseId));
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