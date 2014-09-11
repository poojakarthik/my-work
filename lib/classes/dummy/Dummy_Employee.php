<?php
class Dummy_Employee extends Dummy
{
	const 				SYSTEM_EMPLOYEE_ID	= 0;
	
	protected 			$_sIdField			= 	'Id';
	protected 			$_sTableName		= 	'Employee';
	protected static 	$_sStaticTableName	= 	'Employee';
	protected 			$_bAllowZeroId		= 	true;
	protected 			$_aProperties		= 	array(
													'FirstName'	=> null,
													'LastName'	=> null,
													'UserName'	=> null
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