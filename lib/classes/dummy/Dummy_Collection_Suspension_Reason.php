<?php
class Dummy_Collection_Suspension_Reason extends Dummy
{
	protected 		$_sIdField		= 	'id';
	protected 		$_sTableName		= 	'collection_suspension_type';
	protected static 	$_sStaticTableName	= 	'collection_suspension_type';
	protected               $_aProperties		= 	array(
                                                                        'name'						=> null,
                                                                        'description'					=> null,
                                                                        'system_name'					=> null,
                                                                        'collection_scenario_id'			=> null
                                                                        );
         protected  $bConfig= true;
	
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
	
	public static function getFor($aCriteria)
	{
		return Dummy::getFor(get_class(), $aCriteria);
	}
	// END: REQUIRED FUNCTIONS
}
?>