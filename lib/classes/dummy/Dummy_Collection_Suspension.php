<?php
class Dummy_Collection_Suspension extends Dummy
{
	protected 			$_sIdField			= 	'id';
	protected 			$_sTableName		= 	'collection_suspension';
	protected static 	$_sStaticTableName	= 	'collection_suspension';
	protected 			$_aProperties		= 	array(


													'account_id'							=> null,
													'start_datetime'						=> null,
													'proposed_end_datetime'					=> null,
													'start_employee_id'						=> null,
													'collection_suspension_type_id'			=> null,
													'effective_end_datetime'				=> null,
													'end_employee_id'						=> null,
													'effective_end_datetime'				=> null,
													'collection_suspension_end_reason_id'	=> null
													
												);
	
	public function __construct($aProperties=array(), $bLoadById=false)
	{
		parent::__construct($aProperties, $bLoadById);
	}


        public static function getActive()
        {
            return self::getFor(array('effective_end_datetime' => null));
        }

        public static function getActiveForAccount($iAccountId) {
            $aResult = self::getFor(array('effective_end_datetime' => null, 'account_id'=>$iAccountId));
            return count($aResult)>0 ? $aResult[0] : null;

        }

	
	public static function getForAccountId($iAccountId)
	{
		return self::getFor(array('account_id' => $iAccountId));
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