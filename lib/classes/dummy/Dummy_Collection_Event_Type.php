<?php
class Dummy_Collection_Event_Type extends Dummy
{
	protected                           $_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collection_event_type';
	protected static 	$_sStaticTableName	= 	'collection_event_type';
	protected 			$_aProperties		= 	array(
                                                                                'name'						=> null,
                                                                                'description'			=> null,
                                                                                'system_name'					=> null,
                                                                                'collection_event_type_implementation_id'				=> null,
                                                                                'collection_event_invocation_id'	=> null
                                                                                );
       public  $bConfig= true;



	public function __construct($aProperties=array(), $bLoadById=false)
	{
		parent::__construct($aProperties, $bLoadById);
	}

        public static function getForSystmName($sSystemName)
        {
            $aResult = self::getFor(array('system_name'=>$sSystemName));
            return $aResult[0];

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