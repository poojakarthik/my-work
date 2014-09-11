<?php
class Dummy_Collectable_Payment extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collectable_payment';
	protected static 	$_sStaticTableName	= 	'collectable_payment';
	protected 			$_aProperties		= 	array(
													'payment_id'		=> null,
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