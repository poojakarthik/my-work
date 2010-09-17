<?php
abstract class Correspondence_Dispatcher extends Resource_Type_File_Export
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_CORRESPONDENCE_EXPORT;

	protected $_oRun;
	protected $_iBatchId;

	protected $_oTARFileExport;

	protected 	$_aPDFFilenames = array();
	protected $_aTARFilePath = array();
	protected $_bPreprinted;
	protected $_sInvoiceRunPDFBasePath;

	final public function sendRun($oRun, $iBatchId)
	{

		$this->_oRun = $oRun;
		$this->_iBatchId = $iBatchId;
		$this->export();
		$this->render();
		$this->deliver();
		//create the file export record
		$this->_logToDatabase();
		//set the file export id of the run object and timestamp
		$oRun->setDeliveryDetails($this->_oFileExport->id,$this->_oFileExport->ExportedOn, $this->_oTARFileExport->id, $iBatchId);

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
			$this->_oTARFileExport->Status		= FILE_DELIVERED;	// FIXME: Is this correct?
			$this->_oTARFileExport->FileType	= RESOURCE_TYPE_FILE_EXPORT_CORRESPONDENCE_YELLOWBILLINGTAR;
			$this->_oTARFileExport->SHA1		= sha1_file($this->_oTARFileExport->Location);
			$this->_oTARFileExport->save();
		}
	}


	public static function sendWaitingRuns()
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
						$oDispatcher = $oRun->getCarrierModule();
						$oDispatcher->sendRun($oRun, $oBatch->id);
						$oRun->save();
						$oRun->sendDispatchEmail();
					}
					catch(Exception $e)
					{
						$oRun->setException($e);
						Log::getLog()->log("exception class: ".get_class($e)." Message: ".$e->getMessage());
						Log::getLog()->log($oRun->id.' could not be dispatched. Reason: '.$e->failureReasonToString()."(message: ".$e->getMessage().")");
						$oRun->sendDispatchEmail(true);
					}


				}
			}
			Log::getLog()->log('Sending Batch Delivery Email');
			//send the batch report email
			self::sendBatchDeliveryEmail($oBatch, $aRuns);
			Log::getLog()->log('Finished Batch '.$oBatch->id);
		}
		$sMessage = count($aRuns)==0?"There were no runs to process":(count($aRuns)==1?"Processed ".count($aRuns)." run":"Processed ".count($aRuns)." runs");
		Log::getLog()->log($sMessage);

	}


	public static  function sendBatchDeliveryEmail($oBatch, $aRuns)
	{
		$oEmail = new Email_Notification(EMAIL_NOTIFICATION_CORRESPONDENCE);
		$oEmail->setSubject("Correspondence Delivery Summary for Batch ID  '".$oBatch->id);

		$strTHStyle			= "text-align:left; color: #eee; background-color: #333;  width: 15em; padding-left:10px;";
		$strTDStyle			= "text-align:left; color: #333; background-color: #eee; padding-left:20px;font-size:90%;";
		$strTDStyleTwo			= "text-align:left; color: #333; background-color: #FFFFFF; padding-left:20px;font-size:90%;";
		$strTDAutoStyle		= "";
		$strTDWidthStyle	= "min-width: 15em; max-width: 15em;";
		$strTableStyle		= "font-family: Calibri, Arial, sans-serif; width:99%; border: .1em solid #333; border-spacing: 0; border-collapse: collapse;";
		$sStyle				= "font-family: Calibri, Arial, sans-serif;";
		$sBoldStyle			= "font-family: Calibri, Arial, sans-serif;font-weight:bold;";
		$body = new Flex_Dom_Document();
		$h3 = $body->html->body->h3();
		$h3->setValue ("Correspondence Delivery Summary for Batch ID ".$oBatch->id);
		$h3->style = $sStyle;
		//$body->html->body->h3->style = $sStyle;
		$h4 = $body->html->body->h4();
		$h4->setValue ("Batch ID ".$oBatch->id." was dispatched on ".date('d/m/Y', strtotime($oBatch->batch_datetime))." - ".date('h:i:s', strtotime($oBatch->batch_datetime))." Run details:");
		$h4->style = $sStyle;

		$table =& $body->html->body->table();
		$table->style = $strTableStyle;

		$table->tr(0)->th(0)->setValue('Processed');
		$table->tr(0)->th(0)->style = $strTHStyle;

		$table->tr(0)->th(1)->setValue('Source');
		$table->tr(0)->th(1)->style =$strTHStyle;

		$table->tr(0)->th(2)->setValue('Template');
		$table->tr(0)->th(2)->style = $strTHStyle;

		$table->tr(0)->th(3)->setValue('Created By');
		$table->tr(0)->th(3)->style = $strTHStyle;

		$table->tr(0)->th(4)->setValue('Items');
		$table->tr(0)->th(4)->style = $strTHStyle;

		$table->tr(0)->th(5)->setValue('Data File');
		$table->tr(0)->th(5)->style = $strTHStyle;

		$table->tr(0)->th(6)->setValue('Status');
		$table->tr(0)->th(6)->style = $strTHStyle;


		$sTableStyle = $strTDStyle;
		foreach ($aRuns as $oRun)
		{
			$tr =& $table->tr();
			$tr->td(0)->setValue(date('d/m/Y', strtotime($oRun->processed_datetime))." - ".date('h:i:s', strtotime($oRun->processed_datetime)));
			$tr->td(0)->style = $sTableStyle;

			$sSourceType = Correspondence_Source_Type::getForId($oRun->getSourceType())->name;
			$sSourceFile = $oRun->getSourceFileName()==null?null:" (".$oRun->getSourceFileName().")";
			$tr->td(1)->setValue($sSourceType.$sSourceFile);
			$tr->td(1)->style = $sTableStyle;

			$tr->td(2)->setValue($oRun->getTemplateName()."(".$oRun->getCorrespondenceCode().")");
			$tr->td(2)->style = $sTableStyle;

			$tr->td(3)->setValue($oRun->getCreatedEmployeeName());
			$tr->td(3)->style = $sTableStyle;

			$aCount = $oRun->getCorrespondenceCount();
			$td = $tr->td(4);
			$td->div(0)->setValue("Post : ".$aCount['post']);
			$td->div(1)->setValue("Email: ".$aCount['email']);
			$td->div(2)->setValue("Total: ".$aCount['total']);
			$tr->td(4)->style = $sTableStyle;

			$tr->td(5)->setValue($oRun->getExportFileName());
			$tr->td(5)->style = $sTableStyle;


			$oException = $oRun->getDataValidationException();
			$sStatus = $oException == null?"Dispatched":(get_class($oException) == 'Correspondence_Dispatch_Exception'?"Dispatch Failed":"Data Processing Failed");
			$sFailureReason = $oException==null?null:$oException->failureReasonToString();//iError==CORRESPONDENCE_RUN_ERROR_NO_DATA?"(No Data)":($oException->iError==CORRESPONDENCE_RUN_ERROR_MALFORMED_INPUT?"(Invalid Data, see attched error report)":($oException->iError==CORRESPONDENCE_RUN_ERROR_SQL_SYNTAX?"(Invalid SQL)":null));
			$sFailureReason .=$sFailureReason=="Invalid Data"?". See attached error report.":null;
			$tr->td(6)->setValue($sFailureReason == null?$sStatus:$sStatus." - ".$sFailureReason);
			$tr->td(6)->style = $sTableStyle;
			if ($oException!=null && $oException->sFileName!=null)
			{
				$sFile = file_get_contents($oException->sFileName);
				$sFileName = substr($oException->sFileName, strrpos($oException->sFileName , "/" )+1);
				$oEmail->addAttachment($sFile, $sFileName, 'text/csv');
			}
			$sTableStyle = $sTableStyle==$strTDStyle?$strTDStyleTwo:$strTDStyle;
		}

		Correspondence_Logic_Run::appendEmailSignature($body);

		$sHtml = $body->saveHTML();
		$oEmail->setBodyHTML($sHtml);
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
	}
}



