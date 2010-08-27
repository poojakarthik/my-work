<?php
class Correspondence_Dispatcher
{
	protected $_aColumns = array("Account Number","EMAIL","Title","firstname","lastname","Address1","Address2","Suburb","State","Postcode","Email","Mobile","Phone","Business Name","Service Number","Rate Plan");
	protected $_sLocalFilePath;


	public function __construct($mConfigDetails = null)
	{
		$this->_sLocalFilePath = dirname(__FILE__).'\\outbox';
	}

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
			foreach ($oRun->getCorrespondence() as $oCorrespondence)
			{
				$oFile->addRow($oCorrespondence->toArray());
			}
			//save the file and send it off
			$sTimeStamp = Data_Source_Time::currentTimestamp();
			$oFile->saveToFile($this->_sLocalFilePath.'\\'.str_replace (' ','_',$oRun->getTemplateName()).'_'.$oRun->getTemplateId().'_'.str_replace ( ':' , '_' , $sTimeStamp ).'.csv');
			//set the file export id of the run object and timestamp
			$oRun->setDeliveryDetails(4,$sTimeStamp);
			//save the run object
			$oRun->save();
		}
	}


		public static function create($mConfigDetails = null)
		{
			return new self($mConfigDetails);
		}



	}



