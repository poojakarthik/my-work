<?php
class Dummy_Collectable_Transfer extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collectable_transfer';
	protected static 	$_sStaticTableName	= 	'collectable_transfer';
	protected 			$_aProperties		= 	array(
													'from_collectable_id'			=> null,
													'to_collectable_id'				=> null,
													'created_datetime'				=> null,
													'amount'						=> null,
													'balance'						=> null,
													'collectable_transfer_type_id'	=> null
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