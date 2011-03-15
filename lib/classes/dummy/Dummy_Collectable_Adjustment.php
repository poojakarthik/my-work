<?php
class Dummy_Collectable_Adjustment extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collectable_adjustment';
	protected static 	$_sStaticTableName	= 	'collectable_adjustment';
	protected 			$_aProperties		= 	array(
													'adjustment_id'		=> null,
													'collectable_id'	=> null,
													'amount'			=> null,
													'created_datetime'	=> null
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