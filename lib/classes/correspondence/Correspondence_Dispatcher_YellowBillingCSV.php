<?php
class Correspondence_Dispatcher_YellowBillingCSV extends Correspondence_Dispatcher
{

	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_EXPORT_CORRESPONDENCE_YELLOWBILLINGCSV;

	protected function export()
	{
			//process the data into csv format
			$oFile = new File_CSV($sDelimiter=',', $sQuote='"', $sEscape='\\', $oRun->getAllColumns());
			//get their data
			foreach ($oRun->getCorrespondence() as $oCorrespondence)
			{
				$oFile->addRow($oCorrespondence->toArray());
			}
			//save the file and send it off
			$sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());
			$sFilename	= $oRun->getCorrespondenceCode()
					.'_'
					.$sTimeStamp
					.'_'
					.$oRun->id
					.'.csv';

			$this->_sFilePath = $sFileDirectoryPath.$sFilename;

			$oFile->saveToFile($this->_sFilePath);
			$this->_oFileDeliver->connect()->deliver($this->_sFilePath)->disconnect();

	}

	public static function getExportPath($iCarrier, $sClass)
	{
		return parent::getExportPath()."correspondence/{$iCarrier}/{$sClass}/";
	}


	static public function createCarrierModule($iCarrier, $sClass=__CLASS__)
	{
		parent::createCarrierModule($iCarrier, $sClass, self::RESOURCE_TYPE);
	}

		public static function create()
		{
			return new self();
		}




}