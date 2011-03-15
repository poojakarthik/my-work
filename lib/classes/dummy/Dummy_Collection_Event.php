<?php
class Dummy_Collection_Event extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collection_event';
	protected static 	$_sStaticTableName	= 	'collection_event';
	protected 			$_aProperties		= 	array(


													'name'								=> null,
													'description'						=> null,
													'collection_event_type_id'			=> null,
													'collection_event_invocation_id'	=> null
												);
	
	public $bConfig= true;

        public function __construct($aProperties=array(), $bLoadById=false)
	{
		parent::__construct($aProperties, $bLoadById);
	}

        public static function getForType($iTypeId, $bOneOnly = false)
        {
            $x = self::getFor(array('collection_event_type_id'=>$iTypeId));
            return $bOneOnly ? $x[0] : $x;

        }

        public static function getForName($sName)
        {
            $x = self::getFor(array('name'=>$sName));
            return $x[0];

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