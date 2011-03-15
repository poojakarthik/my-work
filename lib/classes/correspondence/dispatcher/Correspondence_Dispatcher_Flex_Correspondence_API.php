<?php
 class Correspondence_Dispatcher_Flex_Correspondence_API extends Resource_Type_Base implements Correspondence_Dispatcher
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_CORRESPONDENCE_EXPORT;
	const	RESOURCE_TYPE		= RESOURCE_TYPE_FLEX_CORRESPONDENCE_API;
	
	protected $_aDetailColumns 	= 	array(
										//array('field'=> 'record_type'										,'data_type'=>'string'		,'pad'=>false											,'length'=>1		,'default'=>self::RECORD_TYPE_DETAIL_CODE),
										array('field'=> 'id'												,'data_type'=>'numeric'		,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'customer_group_id' 								,'data_type'=>'numeric'		,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'account_id' 										,'data_type'=>'numeric'		,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'account_name'	 									,'data_type'=>'string'		,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'title' 											,'data_type'=>'string'		,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'first_name' 										,'data_type'=>'string'		,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'last_name' 										,'data_type'=>'string'		,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'address_line_1' 									,'data_type'=>'string'		,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'address_line_2' 									,'data_type'=>'string'		,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'suburb' 											,'data_type'=>'string'		,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'postcode'	 										,'data_type'=>'numeric'		,'pad'=>array('style'=>STR_PAD_LEFT, 'string'=>'0')		,'length'=>4		,'default'=>null),
										array('field'=> 'state' 											,'data_type'=>'string'		,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'email' 											,'data_type'=>'string'		,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'mobile' 											,'data_type'=>'fnn'			,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'landline'	 										,'data_type'=>'fnn'			,'pad'=>false											,'length'=>null		,'default'=>null),
										array('field'=> 'correspondence_delivery_method' 					,'data_type'=>'string'		,'pad'=>false											,'length'=>null		,'default'=>null)
									);
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
	protected $_aCorrespondenceExport = array();
	protected $_aPDFExport = array();
	protected $_aCorrespondenceFieldsNotIncluded = array('pdf_file_path', 'correspondence_run_id');

	public function getFileNameForCorrespondenceRunDispatch($oCorrespondenceRunDispatch)
	{
		return 'Delivered By Flex';
	}

	final public function exportRun($oRun, $iBatchId)
	{
		if ($oRun->getBatchIdForTemplateCarrierModule($this->oTemplateCarrierModuleObject) == null)
		{
			$this->_oRun = $oRun;
			$this->_iBatchId = $iBatchId;
			$this->export();
		}
	}

	final public function deliverRun()
	{
		if ($this->_oRun!= null)
		{
			try
			{
				$this->deliver();
				$this->_oRun->addFileDeliveryDetails(null,null, $this->oTemplateCarrierModuleObject->id, $this->_aDeliveryMethodsIncludedInDispatch, $this->_iBatchId);
				return Data_Source_Time::currentTimestamp();
			}
			catch (Correspondence_Dispatch_Exception $e)
			{
				$this->_oRun->addFileDeliveryDetails(null,null, $this->oTemplateCarrierModuleObject->id, $this->_aDeliveryMethodsIncludedInDispatch, $e);
			}
		}
	}

	public function export()
	{
		//throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::DATAFILEBUILD, 'testing exceptions');
		
		$this->_sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());

		$aColumns = array_merge($this->_aDetailColumns, $this->_oRun->getAdditionalColumns(count($this->_aDetailColumns)));

		foreach ($aColumns as $key => $mColumn)
		{
			if (!is_array($mColumn))
			{
				$aColumns[$key] = array('field'=>$mColumn, 'pad'=>false, 'length'=>null, 'default'=>null);
			}
		}

		$this->_bPreprinted = $this->_oRun->preprinted==1?true:false;

		foreach ($this->_oRun->getCorrespondence() as $iDeliveryMethod =>$aCorrespondence)
		{
			if (in_array($iDeliveryMethod, $this->_aDeliveryMethods))
			{
				$this->_aDeliveryMethodsIncludedInDispatch[] = $iDeliveryMethod;
				foreach ($aCorrespondence as $oCorrespondence)
				{
					try
					{
						$this->addRecord($oCorrespondence->toArray(true));
					}
					catch (Exception $e)
					{
						throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::DATAFILEBUILD, $e);
					}

					if ($this->_bPreprinted)
					{
						$aLastError = error_get_last();

						$this->_aPDFExport[$oCorrespondence->id] = $oCorrespondence->pdf_file_path;
						$aError = error_get_last();
						if ($aLastError != $aError)
						{
							throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::PDF_FILE_COPY, "Message: ".$aError['message']." File: ".$aError['file']." Line: ".$aError['line'] );
						}

					}
				}
			}
		}
	}

	public function deliver()
	{
		// Call the interface of the flex mailhouse system to send off the correspondence and pdf arrays
		//throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::MAILHOUSE_PROCESSING, "Mailhouse exception testing" );
		Email_Template_Logic_Correspondence::sendCorrespondenceEmails($this->_aCorrespondenceExport, $this->_aPDFExport);
	}

	public function addRecord($mRecord)
	{
		$oRecord	= new stdClass();
		foreach ($mRecord as $sField=>$mValue)
		{
			if ($sField == 'correspondence_delivery_method_id')
			{
				$oRecord->correspondence_delivery_method = Correspondence_Delivery_Method::getSystemNameForId($mValue);
			}
			else if (!in_array($sField, $this->_aCorrespondenceFieldsNotIncluded))
			{
				$oRecord->$sField = $mValue;
			}
		}
		$this->_aCorrespondenceExport[]=get_object_vars($oRecord);
	}

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

	static public function createCarrierModule($iCarrier, $sClass=__CLASS__)
	{
		parent::createCarrierModule($iCarrier, $sClass, self::RESOURCE_TYPE, self::CARRIER_MODULE_TYPE);
	}

	static public function defineCarrierModuleConfig()
	{
		return parent::defineCarrierModuleConfig();
	}
}
?>