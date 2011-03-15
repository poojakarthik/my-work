<?php
class Dummy_Payment extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'payment';
	protected static 	$_sStaticTableName	= 	'payment';
	protected 			$_aProperties		= 	array(
													// None
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