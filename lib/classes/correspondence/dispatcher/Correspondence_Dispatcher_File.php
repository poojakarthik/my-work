<?php
abstract class Correspondence_Dispatcher_File extends Resource_Type_File_Export implements Correspondence_Dispatcher
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_CORRESPONDENCE_EXPORT;

	protected $_oRun;
	protected $_iBatchId;

	protected $_oTARFileExport;

	protected $_aPDFFilenames = array();
	protected $_aTARFilePath = array();
	protected $_bPreprinted;
	protected $_sInvoiceRunPDFBasePath;
	protected $_aDeliveryMethods = array();
	protected $_aDeliveryMethodsIncludedInDispatch = array();

	protected $oTemplateCarrierModuleObject;

	protected $iCorrespondenceCount;

	public function getFileNameForCorrespondenceRunDispatch($oCorrespondenceRunDispatch)
	{
		if ($oCorrespondenceRunDispatch->data_file_export_id === null)
		{
			// No file was created
			return null;//'No File';
		}
		else
		{
			// Extract file export filename
			$oFileExport	= File_Export::getForId($oCorrespondenceRunDispatch->data_file_export_id);
			return $oFileExport->FileName;
		}
	}

	final public function exportRun($oRun, $iBatchId)
	{
		if ($oRun->getBatchIdForTemplateCarrierModule($this->oTemplateCarrierModuleObject) == null)
		{
			$this->_oRun 				= $oRun;
			$this->_iBatchId 			= $iBatchId;
			$this->iCorrespondenceCount	= $this->export();
			$this->render();
		}
	}

	final public function deliverRun()
	{
			if ($this->_oRun!= null)
			{
				try
				{
					if ($this->iCorrespondenceCount > 0)
					{

						$this->deliver();

						$this->_logToDatabase();
						//set the file export id of the run object and timestamp
						$this->_oRun->addFileDeliveryDetails($this->_oFileExport->id, $this->_oTARFileExport->id, $this->oTemplateCarrierModuleObject->id, $this->_aDeliveryMethodsIncludedInDispatch, $this->_iBatchId);
					}
					else
					{
						$this->_oRun->addFileDeliveryDetails(null, null, $this->oTemplateCarrierModuleObject->id, $this->_aDeliveryMethodsIncludedInDispatch, $this->_iBatchId);

					}
				}
				catch (Correspondence_Dispatch_Exception $e)
				{
					$this->_oRun->addFileDeliveryDetails(null,null, $this->oTemplateCarrierModuleObject->id, $this->_aDeliveryMethodsIncludedInDispatch, $e);
				}
			}
	}

/*	final public function sendRun($oRun, $iBatchId)
	{

		//if there is a batch id for this run/carrier combination, this means all correspondence in this run for this carrier has already been sent in a previous batch: nothing to do anymore.
		if ($oRun->getBatchIdForTemplateCarrierModule($this->oTemplateCarrierModuleObject) == null)
		{
			try
			{
				$this->_oRun = $oRun;
				$this->_iBatchId = $iBatchId;
				$iCount = $this->export();
				//only if there were correspondence records do we proceed to deliver to the carrier
				if ($iCount > 0)
				{
					$this->render();
					$this->deliver();
					//create the file export record
					$this->_logToDatabase();
					//set the file export id of the run object and timestamp
					$oRun->addFileDeliveryDetails($this->_oFileExport->id, $this->_oTARFileExport->id, $this->oTemplateCarrierModuleObject->id, $this->_aDeliveryMethodsIncludedInDispatch, $this->_iBatchId);

				}
				else
				{
					$oRun->addFileDeliveryDetails(null, null, $this->oTemplateCarrierModuleObject->id, $this->_aDeliveryMethodsIncludedInDispatch, $this->_iBatchId);

				}
			}
			catch (Correspondence_Dispatch_Exception $e)
			{
				$oRun->addFileDeliveryDetails($this->_oFileExport->id, $this->_oTARFileExport->id, $this->oTemplateCarrierModuleObject->id, $this->_aDeliveryMethodsIncludedInDispatch, null);
				throw $e;
			}

		}

		return Data_Source_Time::currentTimestamp();

	}*/

	public function assignDeliveryMethods($aDeliveryMethods)
	{
		$this->_aDeliveryMethods = $aDeliveryMethods;
	}

	public function getDeliveryMethods()
	{
		return $this->_aDeliveryMethods;
	}

	public function addDeliveryMethod($iDeliveryMethod)
	{
		$this->_aDeliveryMethods[] = $iDeliveryMethod;
	}

	public function setTemplateCarrierModule($oTemplateCarrierModuleObject)
	{
		$this->oTemplateCarrierModuleObject = $oTemplateCarrierModuleObject;
	}

	static public function createCarrierModule($iCarrier, $sClassName, $iResourceType)
	{
		parent::createCarrierModule($iCarrier, $sClassName, $iResourceType, self::CARRIER_MODULE_TYPE);
	}

	static public function defineCarrierModuleConfig()
	{
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'FileDeliverCarrierModuleId'	=>	array('Description'=>'CarrierModule.Id for the method of delivery','Type'=>DATA_TYPE_INTEGER)
		));
	}

	protected function _logToDatabase()
	{
		parent::_logToDatabase();
		if ($this->_bPreprinted)
		{
			$this->_oTARFileExport->FileName	= basename($this->_aTARFilePath);
			$this->_oTARFileExport->Location	= $this->_aTARFilePath;
			$this->_oTARFileExport->Carrier		= $this->getCarrier();
			$this->_oTARFileExport->ExportedOn	= $this->_oFileExport->ExportedOn;
			$this->_oTARFileExport->Status		= FILE_DELIVERED;
			$this->_oTARFileExport->FileType	= RESOURCE_TYPE_FILE_EXPORT_CORRESPONDENCE_YELLOWBILLINGTAR;
			$this->_oTARFileExport->SHA1		= sha1_file($this->_oTARFileExport->Location);
			$this->_oTARFileExport->save();
		}
	}

/*	public static function sendWaitingRuns()
	{
		//retrieve the set of correspondence runs that should be sent
		Log::getLog()->log('Retrieving Correspondence Runs');
		$aRuns = Correspondence_Logic_Run::getWaitingRuns();
		if (count($aRuns)>0)
		{
			$oBatch = new Correspondence_Run_Batch();
			$oBatch->batch_datetime = Data_Source_Time::currentTimestamp();
			$oBatch->save();

			foreach ($aRuns as $oRun)
			{
				if (!$oRun->getDataValidationException())
				{
					Log::getLog()->log('Dispatching Run '.$oRun->id);
					try
					{
						$aDispatchers = $oRun->getCarrierModules();
						foreach ($aDispatchers as $oDispatcher)
						{
							$sDeliveredDateTime = $oDispatcher->sendRun($oRun, $oBatch->id);
						}
						$oRun->setDeliveryDetails($sDeliveredDateTime,$oBatch->id );
						$oRun->save();
						//$oRun->sendDispatchEmail();
					}
					catch(Exception $e)
					{
						$oRun->setException($e);
						//Log::getLog()->log("exception class: ".get_class($e)." Message: ".$e->getMessage());
						Log::getLog()->log($oRun->id.' could not be dispatched. Reason: '.$e->failureReasonToString()."(message: ".$e->getMessage().")");
						$oRun->sendDispatchEmail(true);
					}
				}
			}
			Log::getLog()->log('Sending Batch Delivery Email');
			//send the batch report email
			//self::sendBatchDeliveryEmail($oBatch, $aRuns);
			Log::getLog()->log('Finished Batch '.$oBatch->id);
		}
		$sMessage = count($aRuns)==0?"There were no runs to process":(count($aRuns)==1?"Processed ".count($aRuns)." run":"Processed ".count($aRuns)." runs");
		Log::getLog()->log($sMessage);
	}


	public static  function sendBatchDeliveryEmail($oBatch, $aRuns)
	{
		$oEmail = Correspondence_Email::getForEmailNotificationSystemName('CORRESPONDENCE');
		$oEmail->setSubject("Correspondence Delivery Summary for Batch ID  ".$oBatch->id);


		$oEmail->addTextHeader(3, "Correspondence Delivery Summary for Batch ID ".$oBatch->id);
		$oEmail->addTextHeader(4, "Batch ID ".$oBatch->id." was dispatched on ".date('d/m/Y', strtotime($oBatch->batch_datetime))." - ".date('H:i:s', strtotime($oBatch->batch_datetime))." Run details:");



		$oEmail->setTable(array('Processed','Source','Template','Created By','Items','Data File','Status'));

		$sTableStyle = Correspondence_Email::TD_STYLE;
		foreach ($aRuns as $oRun)
		{
			$aRowData = array();

			$aRowData[]= date('d/m/Y', strtotime($oRun->processed_datetime))." - ".date('H:i:s', strtotime($oRun->processed_datetime));


			$sSourceType = Correspondence_Source_Type::getForId($oRun->getSourceType())->name;
			$sSourceFile = $oRun->getSourceFileName()==null?null:" (".$oRun->getSourceFileName().")";
			$aRowData[]=$sSourceType.$sSourceFile;

			$aRowData[]=$oRun->getTemplateName()."(".$oRun->getCorrespondenceCode().")";

			$aRowData[]=$oRun->getCreatedEmployeeName();

			$aCount = $oRun->getCorrespondenceCount();
			$aFormattedCount = array('Post'=>$aCount['post'], 'Email'=>$aCount['email'], 'Total'=>$aCount['total']);
			$aRowData[]=$aFormattedCount;

			$aRowData[]=$oRun->getExportFileName();


			$oException = $oRun->getDataValidationException();
			$sStatus = $oException == null?"Dispatched":(get_class($oException) == 'Correspondence_Dispatch_Exception'?"Dispatch Failed":"Data Processing Failed");
			$sFailureReason = $oException==null?null:$oException->failureReasonToString();
			$sFailureReason .=$sFailureReason=="Invalid Data"?". See attached error report.":null;

			$aRowData[]= $sFailureReason == null?$sStatus:$sStatus." - ".$sFailureReason;


			$tr = $oEmail-> addTableRow($aRowData, $sTableStyle);

			if ($oException!=null && $oException->sFileName!=null)
			{
				$sFile = file_get_contents($oException->sFileName);
				$sFileName = substr($oException->sFileName, strrpos($oException->sFileName , "/" )+1);
				$oEmail->addAttachment($sFile, $sFileName, 'text/csv');
			}
			$sTableStyle = $sTableStyle==Correspondence_Email::TD_STYLE?Correspondence_Email::TD_ALTERNATIVE_STYLE:Correspondence_Email::TD_STYLE;
		}

		$oEmail->appendSignature();
		$oEmail->setBodyHTML();
		$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);
		$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		$oEmail->send();

	}
}

class Correspondence_Dispatch_Exception extends Exception
{

	public $iError;

	const DATAFILEBUILD 		= 1;
	const PDF_FILE_COPY 		= 2;
	const EXPORT_FILE_SAVE		= 3;
	const EXPORT_TAR_FILE_SAVE 	= 4;
	const FILE_DELIVER 			= 5;
	const TAR_FILE_DELIVER 		= 6;


	public function __construct($iErrorCode, $mException = null)
	{
		parent::__construct($mException);
		$this->iError		= $iErrorCode;
	}

	public function failureReasonToString()
	{
		return $this->iError==null?null:($this->iError==self::DATAFILEBUILD?"Error adding records to export file":($this->iError==self::PDF_FILE_COPY?"Could not create PDF for TAR file":($this->iError==self::EXPORT_FILE_SAVE?"Failed to save export file":($this->iError==self:: EXPORT_TAR_FILE_SAVE?"Failed to create PDF TAR file":($this->iError==self::FILE_DELIVER?"Failed to deliver datafile to mailing house":"Failed to deliver PDF TAR file to mailing house")))));
	}*/
}



