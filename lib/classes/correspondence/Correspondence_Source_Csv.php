<?php
class Correspondence_Source_Csv extends Correspondence_Source
{

	protected $_aCsv;

public function __construct($sCsv = null)
{

	$sCsv = file_get_contents(dirname(__FILE__).'/sample_csv.csv');
	$this->_aCsv = explode("\n",$sCsv);
}


function getData()
{
	$aCorrespondence = array();
	foreach($this->_aCsv as $sLine)
	{
		$aCorrespondence[] = new Correspondence(File_CSV::parseLine(rtrim($sLine,"\r\n"), $sDelimiter=',', $sQuote='"', $sEscape='\\'));
	}
	return $aCorrespondence;
}



}