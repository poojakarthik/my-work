<?php
class Dummy_Collection_Scenario_Collection_Event extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collection_scenario_collection_event';
	protected static 	$_sStaticTableName	= 	'collection_scenario_collection_event';
	protected 			$_aProperties		= 	array(
													'collection_scenario_id'								=> null,
													'collection_event_id'									=> null,
													'collection_event_invocation_id'						=> null,
													'day_offset'											=> null,
													'prerequisite_collection_scenario_collection_event_id'	=> null
												);
         public  $bConfig= true;
	
	public function __construct($aProperties=array(), $bLoadById=false)
	{
		parent::__construct($aProperties, $bLoadById);
	}
	
	public static function getForScenarioId($iScenarioId)
	{
		return self::getFor(array('collection_scenario_id' => $iScenarioId));
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