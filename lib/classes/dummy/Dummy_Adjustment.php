<?php
class Dummy_Adjustment extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'adjustment';
	protected static 	$_sStaticTableName	= 	'adjustment';
	protected 			$_aProperties		= 	array(
													'adjustment_type_id'	=> null,
													'amount'				=> null,
													'effective_date'		=> null,
													'created_employee_id'	=> null,
													'created_datetime'		=> null,
													'approved_employee_id'	=> null,
													'approved_datetime'		=> null
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