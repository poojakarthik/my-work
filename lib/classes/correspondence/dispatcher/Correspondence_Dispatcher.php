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
					$oDispatcher = $oRun->getCarrierModule();
					$oDispatcher->sendRun($oRun, $oBatch->id);
					$oRun->save();
					$oRun->sendDispatchEmail();
				}
			}

			//send the batch report email
			self::sendBatchDeliveryEmail($oBatch, $aRuns);
		}

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
			$td->div(0)->setValue("Post:".$aCount['post']);
			$td->div(1)->setValue("Email:".$aCount['email']);
			$td->div(2)->setValue("Total: ".$aCount['total']);
			$tr->td(4)->style = $sTableStyle;

			$tr->td(5)->setValue($oRun->getExportFileName());
			$tr->td(5)->style = $sTableStyle;

			$sStatus = $oRun->getDataValidationException()?"Processing Failed":"Dispatched";
			$oException = $oRun->getDataValidationException();
			$sFailureReason = $oException==null?null:$oException->iError==CORRESPONDENCE_RUN_ERROR_NO_DATA?"(No Data)":($oException->iError==CORRESPONDENCE_RUN_ERROR_MALFORMED_INPUT?"(Invalid Data, see attched error report)":($oException->iError==CORRESPONDENCE_RUN_ERROR_SQL_SYNTAX?"(Invalid SQL)":null));
			$tr->td(6)->setValue($sStatus." ".$sFailureReason);
			$tr->td(6)->style = $sTableStyle;
			if ($oException!=null && $oException->sFileName!=null)
			{
				$sFile = file_get_contents($oException->sFileName);
				$sFileName = substr($oException->sFileName, strrpos($oException->sFileName , "/" )+1);
				$oEmail->addAttachment($sFile, $sFileName, 'text/csv');
			}
			$sTableStyle = $sTableStyle==$strTDStyle?$strTDStyleTwo:$strTDStyle;
		}

		$body->div();
		$div = $body->div();
		$div->setValue("Regards");
		$div->style = $sStyle;
		$div = $body->div();
		$div->setValue("Flexor");
		$div->style = $sBoldStyle;
		$sHtml = $body->saveHTML();
		/*//For query debug purpose
		  $myFile = "c:/wamp/www/email text.html";
			$fh = fopen($myFile, 'w') or die("can't open file");
			fwrite($fh, $sHtml);
			fclose($fh);*/

		$oEmail->setBodyHTML($sHtml);
		$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);
		$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
		$oEmail->send();
	}



}



