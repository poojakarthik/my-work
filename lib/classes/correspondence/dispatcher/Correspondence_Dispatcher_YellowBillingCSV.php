<?php
class Correspondence_Dispatcher_YellowBillingCSV extends Correspondence_Dispatcher
{

	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_EXPORT_CORRESPONDENCE_YELLOWBILLINGCSV;
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
	protected $_aDetailColumns 	= array(
												array('field'=> 'record_type',										'data_type'=>'string',	'mandatory'=>true, 		'length'=>1, 	'default'=>'D'),
												array('field'=> 'id',												'data_type'=>'numeric',						'mandatory'=>true, 		'length'=>null, 'default'=>null),
												array('field'=> 'customer_group_id', 								'data_type'=>'numeric',	'mandatory'=>true,		'length'=>null, 'default'=>null),
												array('field'=> 'account_id', 										'data_type'=>'numeric', 'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'account_name', 									'data_type'=>'string', 	'mandatory'=>true, 		'length'=>null, 'default'=>null),
												array('field'=> 'title', 											'data_type'=>'string', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'first_name', 										'data_type'=>'string', 	'mandatory'=>true, 		'length'=>null, 'default'=>null),
												array('field'=> 'last_name', 										'data_type'=>'string', 	'mandatory'=>true, 		'length'=>null, 'default'=>null),
												array('field'=> 'address_line_1', 									'data_type'=>'string', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'address_line2', 									'data_type'=>'string', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'suburb', 											'data_type'=>'string', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'postcode', 										'data_type'=>'numeric', 'mandatory'=>false, 	'length'=>4, 	'default'=>null),
												array('field'=> 'state', 											'data_type'=>'string', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'email', 											'data_type'=>'string', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'mobile', 											'data_type'=>'fnn', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'landline', 										'data_type'=>'fnn', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'correspondence_delivery_method', 					'data_type'=>'string', 	'mandatory'=>true, 		'length'=>null, 'default'=>null)
											);
	protected  $_aHeaderColumns 	= array(
												array('field'=>'record_type'										,'data_type'=>'string'		,'default'=>'H'),
												array('field'=>'letter_code'										,'data_type'=>'string'		,'default'=>null),
												array('field'=>'correspondence_run_id'								,'data_type'=>'numeric'		,'default'=>null),
												array('field'=>'created_timestamp'									,'data_type'=>'timestamp'	,'default'=>null),
												array('field'=>'data_file_name'										,'data_type'=>'string'		,'default'=>null),
												array('field'=>'tar_file_name'										,'data_type'=>'string'		,'default'=>null)
											);
	protected  $_aFooterColumns 	= array(
												array('field'=>'record_type'										,'data_type'=>'string'	,'default'=>'F'),
												array('field'=>'correspondence_item_count'							,'data_type'=>'numeric'	,'default'=>null)
											);


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


		// Render and write to disk
		$this->_oFileExporterCSV->renderToFile($this->_sFilePath);

		if ($this->_bPreprinted)
		{
			// Create tar file
			require_once("Archive/Tar.php");

			$oTar		= new Archive_Tar($this->_aTARFilePath);
			if (!$oTar->createModify($this->_aPDFFilenames, '', $this->_sInvoiceRunPDFBasePath))
			{
				 throw new Exception("Failed to create tar file for invoice run {$this->Id}. Files = ".print_r($aFiles, true));
			}

			foreach($this->_aPDFFilenames as $sFile)
			{
				unlink($sFile);
			}
		}

		// TODO: Do we need to return anything special?
		return $this;
	}

	public function deliver()
	{
		$this->_oFileDeliver->connect()->deliver($this->_sFilePath)->disconnect();
		if ($this->_bPreprinted)
			$this->_oFileDeliver->connect()->deliver($this->_aTARFilePath)->disconnect();
		return $this;
	}



	public function addRecord($mRecord)
	{
		$oRecord	= $this->_oFileExporterCSV->getRecordType(self::RECORD_TYPE_DETAIL)->newRecord();
		$oRecord->record_type = self::RECORD_TYPE_DETAIL_CODE;
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
				.$this->_oRun->id
				;

		$this->_sFilePath = $this->_sFileDirectoryPath.$this->_sFilename.'.csv';
		$this->_aTARFilePath = $this->_sFileDirectoryPath.$this->_sFilename.'.tar';



		$aColumns = array_merge($this->_aDetailColumns, $this->_oRun->getAdditionalColumns(count($this->_aDetailColumns)));

		foreach ($aColumns as $key => $mColumn)
		{
			if (!is_array($mColumn))
			{
				$aColumns[$key] = array('field'=>$mColumn);
			}
		}

		$this->_bPreprinted = $this->_oRun->preprinted==1?true:false;


		$this->_configureFileExporter($aColumns);

		$this->setHeaderAndFooter();

		foreach ($this->_oRun->getCorrespondence() as $oCorrespondence)
		{
			$this->addRecord($oCorrespondence->toArray(true));
			if ($this->_bPreprinted)
			{
				if ($this->_sInvoiceRunPDFBasePath == null)
					$this->_sInvoiceRunPDFBasePath = substr ($oCorrespondence->pdf_file_path , 0 , strrpos ( $oCorrespondence->pdf_file_path, "/" )+1 );

				$sTempPdfName = $this->_sInvoiceRunPDFBasePath.$oCorrespondence->id.'.pdf';
				copy ( $oCorrespondence->pdf_file_path , $sTempPdfName );
				$this->_aPDFFilenames[]=$sTempPdfName;
			}

		}






	}

	public function setHeaderAndFooter()
	{
		$oHeaderRecord								= 	$this->_oFileExporterCSV->getRecordType(self::RECORD_TYPE_HEADER)->newRecord();
		$oFooterRecord								= 	$this->_oFileExporterCSV->getRecordType(self::RECORD_TYPE_FOOTER)->newRecord();

		$oHeaderRecord->record_type 				= 	self::RECORD_TYPE_HEADER_CODE;
		$oHeaderRecord->letter_code 				= 	$this->_oRun->getCorrespondenceCode();
		$oHeaderRecord->correspondence_run_id 		= 	$this->_oRun->id;
		$oHeaderRecord->created_timestamp			= 	$this->_sTimeStamp;
		$oHeaderRecord->data_file_name 				= 	$this->_sFilename.'.csv';
		$oHeaderRecord->tar_file_name 				= 	$this->_bPreprinted?$this->_sFilename.'.tar':null;

		$oFooterRecord->record_type 				= 	self::RECORD_TYPE_FOOTER_CODE;
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
		//File_Exporter_Field::factory()->setDefaultValue(date('d/m/Y', $this->_iTimestamp)
		foreach($aColumns as $aColumn)
		{
			$oRecordType->addField($aColumn['field'], File_Exporter_Field::factory());
		}

		$this->_oFileExporterCSV->registerRecordType(self::RECORD_TYPE_DETAIL, $oRecordType);

		$oRecordType = File_Exporter_RecordType::factory();

		foreach($this->_aHeaderColumns as $aColumn)
		{
			$oRecordType->addField($aColumn['field'], File_Exporter_Field::factory());
		}

		$this->_oFileExporterCSV->registerRecordType(self::RECORD_TYPE_HEADER, $oRecordType);

		$oRecordType = File_Exporter_RecordType::factory();

		foreach($this->_aFooterColumns as $aColumn)
		{
			$oRecordType->addField($aColumn['field'], File_Exporter_Field::factory());
		}

		$this->_oFileExporterCSV->registerRecordType(self::RECORD_TYPE_FOOTER, $oRecordType);
	}


}