<?php
class Correspondence_Source_Csv extends Correspondence_Source
{

	protected $_aCsv;

public function __construct($sCsv = null)
{
	parent::__construct(Correspondence_Source::CSV);
	$this->_aCsv = explode("\n",$sCsv);

}


function getData($aColumns)
{



	$aCorrespondence = array();
	foreach($this->_aCsv as $sLine)
	{
		$aLine = File_CSV::parseLineHashed(rtrim($sLine,"\r\n"), $sDelimiter=',', $sQuote='"', $sEscape='\\', $aColumns);

		$aCorrespondence[] = new Correspondence();
	}
	return $aCorrespondence;
}





}