<?php
class Dummy_Customer_Group_Collection_Scenario extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'customer_group_collection_scenario';
	protected static 	$_sStaticTableName	= 	'customer_group_collection_scenario';
	protected 			$_aProperties		= 	array(
													'collection_scenario_id'	=> null,
													'customer_group_id' 		=> null
												);
        public $bConfig = true;
	
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