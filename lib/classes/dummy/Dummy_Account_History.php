<?php
class Dummy_Account_History extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'account_history';
	protected static 	$_sStaticTableName	= 	'account_history';
	protected 			$_aProperties		= 	array(
													'account_id' 		=> null,
													'account_class_id'	=> null
												);
	
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