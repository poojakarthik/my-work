<?php
class Correspondence_Dispatcher
{
	protected $_aColumns = array("Account Number","EMAIL","Title","firstname","lastname","Address1","Address2","Suburb","State","Postcode","Email","Mobile","Phone","Business Name","Service Number","Rate Plan");

	public function sendWaitingRuns()
	{
		//retrieve the set of correspondence runs that should be sent
		$aRuns = Correspondence_Run::getWaitingRuns();
		$aCsvFiles = array();
		foreach ($aRuns as $oRun)
		{

			//process the data into csv format
			$oFile = new File_CSV($sDelimiter=',', $sQuote='"', $sEscape='\\', $aColumns=$this->_aColumns);
			//get their data
		foreach ($oRun->getData() as $oCorrespondence)
		{
			$oFile->addRow($oCorrespondence->toArray());
		}
		//save the file and send it off
			$sTimeStamp = Data_Source_Time::currentTimestamp();
			$oFile->saveToFile(dirname(__FILE__).'/'.$oRun->name);
			//set the file export id of the run object

			//save the run object

		}






	}



}