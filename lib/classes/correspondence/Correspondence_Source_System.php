<?php

class Correspondence_Source_System extends Correspondence_Source
{

	protected $_aData;

	public function __construct($aData)
	{
		$this->_aData = $aData;

	}

function getData($aColumns)
{

	$aCorrespondence = array();
	foreach($this->_aData as $sLine)
	{
		$aLine = File_CSV::parseLineHashed(rtrim($sLine,"\r\n"), $sDelimiter=',', $sQuote='"', $sEscape='\\', $aColumns);

		$aCorrespondence[] = new Correspondence($aLine);
	}
	return $aCorrespondence;
}




}



?>