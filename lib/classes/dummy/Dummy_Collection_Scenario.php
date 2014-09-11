<?php
class Dummy_Collection_Scenario extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collection_scenario';
	protected static 	$_sStaticTableName	= 	'collection_scenario';
	protected 			$_aProperties		= 	array(
                                                                                'name'			=> null,
                                                                                'description'	=> null,
                                                                                'day_offset'	=> null,
                                                                                'status_id'		=> null,
                                                                                'entry_threshold_percentage' => null,
                                                                                'entry_threshold_amount' => null,
                                                                                'exit_threshold_percentage' => null,
                                                                                'exit_threshold_amount'=>null
                                                                            );
         public  $bConfig= true;
	
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