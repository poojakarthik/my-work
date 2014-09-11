<?php
class Dummy_Collection_Event_Report extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collection_event_report';
	protected static 	$_sStaticTableName	= 	'collection_event_report';
	protected 			$_aProperties		= 	array(

													'collection_event_id'	=> null,
                                                                                                        'sql'                   =>null,
													'email_notification_id'	=> null
												);
        public  $bConfig= true;
	
	public function __construct($aProperties=array(), $bLoadById=false)
	{
		parent::__construct($aProperties, $bLoadById);
	}


        public static function getForCollectionEventId($iCollectionEventId)
        {
          $aResult =  self::getFor(array('collection_event_id'=>$iCollectionEventId));
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