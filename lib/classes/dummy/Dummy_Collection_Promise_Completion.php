<?php
class Dummy_Collection_Promise_Completion extends Dummy_Constant
{
	protected $_sIdField = 'id';
	
	public function __construct($aProperties=array(), $bLoadById=false)
	{
		// Add the class_name field
		$this->_aProperties['class_name']	= null;
		
		parent::__construct('collection_promise_completion', $aProperties, $bLoadById);
	}

        public static function getForId($iId)
	{
		return Dummy::getForId('collection_promise_completion', $iId);
	}
}
?>