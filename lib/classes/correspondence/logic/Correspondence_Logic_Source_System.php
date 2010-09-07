<?php

class Correspondence_Logic_Source_System extends Correspondence_Source
{

	protected $_aData;

	public function __construct($aData)
	{
		$this->_aData = $aData;

	}

function getData($bPreprinted, $aAdditionalColumns = array())
{

	$aColumns = Correspondence_Logic::getStandardColumns($bPreprinted);
	$aCorrespondence = array();
	foreach($this->_aData as $aRecord)
	{
		$aLine = array('standard_fields'=>array(), 'additional_fields'=>array());
		foreach ($aRecord as $sField => $mValue)
		{
			if (in_array($sField,$aColumns ))
			{

				$aLine['standard_fields'][$sField]	= $mValue;
			}
			else
			{
				$aLine['additional_fields'][$sField] = $mValue;
			}
		}
		$aCorrespondence[] = new Correspondence_Logic($aLine);
	}
	return $aCorrespondence;
}




}



?>