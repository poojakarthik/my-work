<?php
class Exception_Rating_CDROutOfSequence extends Exception
{
	protected	$_iCDRId;
	
	public function __construct($iCDRId)
	{
		parent::__construct("Unable to Rate CDR with Id '{$iCDRId}' as it is older than the most recently rated CDR");
	}
	
	public function getCDRId()
	{
		return $this->_iCDRId;
	}
}
?>