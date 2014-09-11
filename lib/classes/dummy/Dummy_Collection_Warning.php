<?php
class Dummy_Collection_Warning extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collection_warning';
	protected static 	$_sStaticTableName	= 	'collection_warning';
	protected 			$_aProperties		= 	array(
													'message' => null
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