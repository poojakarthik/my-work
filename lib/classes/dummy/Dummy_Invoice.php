<?php
class Dummy_Invoice extends Dummy
{
	protected 			$_sIdField			= 	'Id';
	protected 			$_sTableName		= 	'Invoice';
	protected static 	$_sStaticTableName	= 	'Invoice';
	protected 			$_aProperties		= 	array(
													'collectable_id' => null
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