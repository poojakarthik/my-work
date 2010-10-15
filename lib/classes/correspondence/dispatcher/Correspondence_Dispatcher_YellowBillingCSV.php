<?php
class Correspondence_Dispatcher_YellowBillingCSV extends Correspondence_Dispatcher
{

	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_EXPORT_CORRESPONDENCE_YELLOWBILLING_CSV;
	const	RECORD_TYPE_DETAIL	= 'detail';
	const	RECORD_TYPE_HEADER	= 'header';
	const	RECORD_TYPE_FOOTER	= 'footer';

	const RECORD_TYPE_FOOTER_CODE = 'F';
	const RECORD_TYPE_DETAIL_CODE = 'D';
	const RECORD_TYPE_HEADER_CODE = 'H';


	const	NEW_LINE_DELIMITER	= "\n";
	const	FIELD_DELIMITER		= ',';
	const	FIELD_ENCAPSULATOR	= '"';
	const	ESCAPE_CHARACTER	= '\\';

	protected $_aDetailColumns 				= array(
												array('field'=> 'record_type'										,'data_type'=>'string'		,'pad'=>false											,'length'=>1		,'default'=>self::RECORD_TYPE_DETAIL_CODE),
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

	protected  $_aHeaderColumns 			= array(
												array('field'=>'record_type'										,'data_type'=>'string'		,'default'=>self::RECORD_TYPE_HEADER_CODE),
												array('field'=>'letter_code'										,'data_type'=>'string'		,'default'=>null),
												array('field'=>'correspondence_run_id'								,'data_type'=>'numeric'		,'default'=>null),
												array('field'=>'created_timestamp'									,'data_type'=>'timestamp'	,'default'=>null),
												array('field'=>'data_file_name'										,'data_type'=>'string'		,'default'=>null),
												array('field'=>'tar_file_name'										,'data_type'=>'string'		,'default'=>null)
											);

	protected  $_aFooterColumns 		= array(
												array('field'=>'record_type'										,'data_type'=>'string'		,'default'=>self::RECORD_TYPE_FOOTER_CODE),
												array('field'=>'correspondence_item_count'							,'data_type'=>'numeric'		,'default'=>null)
											);

	/*Correspondence data members that are not included in the csv correspondence records*/
	protected $_aCorrespondenceFieldsNotIncluded = array('pdf_file_path', 'correspondence_run_id');
	protected	$_oFileExporterCSV;
	protected	$_iTimestamp;

	protected $_sFilename;
	protected $_sFileDirectoryPath;
	protected $_sTimeStamp;



	public function __construct($mCarrierModule)
	{
		parent::__construct($mCarrierModule);

		$this->_oTARFileExport	= new File_Export();
		$this->_iTimestamp	= time();
		$this->_oFileExporterCSV	= new File_Exporter_CSV();
		$this->_oFileExporterCSV->setDelimiter(self::FIELD_DELIMITER);
		$this->_oFileExporterCSV->setQuote(self::FIELD_ENCAPSULATOR);
		$this->_oFileExporterCSV->setQuoteMode(File_Exporter_CSV::QUOTE_MODE_ALWAYS);
		$this->_oFileExporterCSV->setEscape(self::ESCAPE_CHARACTER);
		$this->_oFileExporterCSV->setNewLine(self::NEW_LINE_DELIMITER);


	}

	public function render()
	{
		try
		{
			$this->_oFileExporterCSV->renderToFile($this->_sFilePath);
		}
		catch(Exception $e)
		{
			throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::EXPORT_FILE_SAVE, $e );
		}

		if ($this->_bPreprinted)
		{
			try
			{
				require_once("Archive/Tar.php");
				$oTar		= new Archive_Tar($this->_aTARFilePath);
				if (!$oTar->createModify($this->_aPDFFilenames, '', $this->_sInvoiceRunPDFBasePath))
				{
					 throw new Exception("Failed to create tar file for Files = ".print_r($this->_aPDFFilenames, true) );
				}
			}
			catch(Exception $e)
			{
				throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::EXPORT_TAR_FILE_SAVE, $e);
			}

			foreach($this->_aPDFFilenames as $sFile)
			{
				unlink($sFile);
			}
		}


		return $this;
	}

	public function deliver()
	{
		try
		{
			$this->_oFileDeliver->connect()->deliver($this->_sFilePath)->disconnect();
		}
		catch(Exception $e)
		{
			throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::FILE_DELIVER, $e);
		}

		try
		{
		if ($this->_bPreprinted)
			$this->_oFileDeliver->connect()->deliver($this->_aTARFilePath)->disconnect();
		}
		catch (Exception $e)
		{
			throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::TAR_FILE_DELIVER, $e);
		}
		return $this;
	}



	public function addRecord($mRecord)
	{
		$oRecord	= $this->_oFileExporterCSV->getRecordType(self::RECORD_TYPE_DETAIL)->newRecord();

		foreach ($mRecord as $sField=>$mValue)
		{
			if ($sField == 'correspondence_delivery_method_id')
			{
				$oRecord->correspondence_delivery_method =Correspondence_Delivery_Method::getSystemNameForId($mValue);
			}
			else if (!in_array($sField, $this->_aCorrespondenceFieldsNotIncluded))
			{
				$oRecord->$sField = $mValue;
			}
		}
		$this->_oFileExporterCSV->addRecord($oRecord, File_Exporter_CSV::RECORD_GROUP_BODY);
	}

	public function export()
	{
		$this->_sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());

		$this->_sFileDirectoryPath	= self::getExportPath($this->getCarrierModule()->Carrier, __CLASS__);

		$this->_sFilename	= $this->_oRun->getCorrespondenceCode()
				.'.'
				.$this->_sTimeStamp
				.'.'
				. str_pad($this->_oRun->id, 10, "0", STR_PAD_LEFT);
				;

		$this->_sFilePath = $this->_sFileDirectoryPath.$this->_sFilename.'.csv';
		$this->_aTARFilePath = $this->_sFileDirectoryPath.$this->_sFilename.'.tar';

		$aColumns = array_merge($this->_aDetailColumns, $this->_oRun->getAdditionalColumns(count($this->_aDetailColumns)));

		foreach ($aColumns as $key => $mColumn)
		{
			if (!is_array($mColumn))
			{
				$aColumns[$key] = array('field'=>$mColumn, 'pad'=>false, 'length'=>null, 'default'=>null);
			}
		}

		$this->_bPreprinted = $this->_oRun->preprinted==1?true:false;

		$this->_configureFileExporter($aColumns);

		$this->setHeaderAndFooter();

		foreach ($this->_oRun->getCorrespondence() as $oCorrespondence)
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
				if ($this->_sInvoiceRunPDFBasePath == null)
					$this->_sInvoiceRunPDFBasePath = substr ($oCorrespondence->pdf_file_path , 0 , strrpos ( $oCorrespondence->pdf_file_path, "/" )+1 );

				$sTempPdfName = $this->_sInvoiceRunPDFBasePath.str_pad($oCorrespondence->id, 10, "0", STR_PAD_LEFT).'.pdf';
				$aLastError = error_get_last();
				@copy ( $oCorrespondence->pdf_file_path , $sTempPdfName );
				$aError = error_get_last();
				if ($aLastError!=$aError)
				{
					throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::PDF_FILE_COPY, "Message: ".$aError['message']." File: ".$aError['file']." Line: ".$aError['line'] );
				}
				$this->_aPDFFilenames[]=$sTempPdfName;
			}
		}
	}

	public function setHeaderAndFooter()
	{
		$oHeaderRecord								= 	$this->_oFileExporterCSV->getRecordType(self::RECORD_TYPE_HEADER)->newRecord();
		$oFooterRecord								= 	$this->_oFileExporterCSV->getRecordType(self::RECORD_TYPE_FOOTER)->newRecord();

		$oHeaderRecord->letter_code 				= 	$this->_oRun->getCorrespondenceCode();
		$oHeaderRecord->correspondence_run_id 		= 	$this->_oRun->id;
		$oHeaderRecord->created_timestamp			= 	$this->_sTimeStamp;
		$oHeaderRecord->data_file_name 				= 	$this->_sFilename.'.csv';
		$oHeaderRecord->tar_file_name 				= 	$this->_bPreprinted?$this->_sFilename.'.tar':null;

		$oFooterRecord->correspondence_item_count 	= 	$this->_oRun->count();

		$this->_oFileExporterCSV->addRecord($oHeaderRecord, File_Exporter_CSV::RECORD_GROUP_HEADER);
		$this->_oFileExporterCSV->addRecord($oFooterRecord, File_Exporter_CSV::RECORD_GROUP_FOOTER);
	}


	public static function getExportPath($iCarrier, $sClass)
	{
		return parent::getExportPath()."$iCarrier/$sClass/";
	}


	static public function createCarrierModule($iCarrier, $sClass=__CLASS__)
	{
		parent::createCarrierModule($iCarrier, $sClass, self::RESOURCE_TYPE);
	}

	public static function create()
	{
		return new self();
	}

	protected function _configureFileExporter($aColumns)
	{
		$this->_iTimestamp	= time();
		$oRecordType = File_Exporter_RecordType::factory();

		foreach($aColumns as $aColumn)
		{
			$oField = File_Exporter_Field::factory();
			$aColumn['length']!=null&&$aColumn['pad']?$oField->setMinimumLength($aColumn['length'])->setMaximumLength($aColumn['length'])->setPaddingString($aColumn['pad']['string'])->setPaddingStyle($aColumn['pad']['style']):null;
			$aColumn['default']!=null?$oField->setDefaultValue($aColumn['default']):null;
			$oRecordType->addField($aColumn['field'], $oField);
		}

		$this->_oFileExporterCSV->registerRecordType(self::RECORD_TYPE_DETAIL, $oRecordType);

		$oRecordType = File_Exporter_RecordType::factory();

		foreach($this->_aHeaderColumns as $aColumn)
		{
			$oField = File_Exporter_Field::factory();
			$aColumn['default']!=null?$oField->setDefaultValue($aColumn['default']):null;
			$oRecordType->addField($aColumn['field'], $oField);
		}

		$this->_oFileExporterCSV->registerRecordType(self::RECORD_TYPE_HEADER, $oRecordType);

		$oRecordType = File_Exporter_RecordType::factory();

		foreach($this->_aFooterColumns as $aColumn)
		{
			$oField = File_Exporter_Field::factory();
			$aColumn['default']!=null?$oField->setDefaultValue($aColumn['default']):null;
			$oRecordType->addField($aColumn['field'], $oField);
		}

		$this->_oFileExporterCSV->registerRecordType(self::RECORD_TYPE_FOOTER, $oRecordType);
	}


}