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
	const	FIELD_ENCAPSULATOR	= "";
	const	ESCAPE_CHARACTER	= '\\';

	protected final $_aDetailColumns 	= array(
												array('field'=> 'Record Type',						'data_type'=>'string',	'mandatory'=>true, 		'length'=>1, 	'default'=>'D'),
												array('field'=> 'Correspondence Unique Identifier',	'data_type'=>'numeric',	'mandatory'=>true, 		'length'=>null, 'default'=>null),
												array('field'=> 'Customer Group', 					'data_type'=>'numeric',	'mandatory'=>true,		'length'=>null, 'default'=>null),
												array('field'=> 'Account Identifier', 				'data_type'=>'numeric', 'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'Account Name', 					'data_type'=>'string', 	'mandatory'=>true, 		'length'=>null, 'default'=>null),
												array('field'=> 'Addressee Title', 					'data_type'=>'string', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'Addressee First Name', 			'data_type'=>'string', 	'mandatory'=>true, 		'length'=>null, 'default'=>null),
												array('field'=> 'Addressee Last Name', 				'data_type'=>'string', 	'mandatory'=>true, 		'length'=>null, 'default'=>null),
												array('field'=> 'Address Line 1', 					'data_type'=>'string', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'Address Line 2', 					'data_type'=>'string', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'Suburb', 							'data_type'=>'string', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'Postcode', 						'data_type'=>'numeric', 'mandatory'=>false, 	'length'=>4, 	'default'=>null),
												array('field'=> 'State', 							'data_type'=>'string', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'Email Address', 					'data_type'=>'string', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'Mobile', 							'data_type'=>'fnn', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'Landline', 						'data_type'=>'fnn', 	'mandatory'=>false, 	'length'=>null, 'default'=>null),
												array('field'=> 'Delivery Method', 					'data_type'=>'string', 	'mandatory'=>true, 		'length'=>null, 'default'=>null)
											);
	protected final $_aHeaderColumns 	= array(
												array('field'=>'Record Type'								,'data_type'=>'string'		,'default'=>'H'),
												array('field'=>'Letter Code'								,'data_type'=>'string'		,'default'=>null),
												array('field'=>'Correspondence Run Unique Identifier'		,'data_type'=>'numeric'		,'default'=>null),
												array('field'=>'Creation Timestamp'							,'data_type'=>'timestamp'	,'default'=>null),
												array('field'=>'Data File Name'								,'data_type'=>'string'		,'default'=>null),
												array('field'=>'TAR File Name'								,'data_type'=>'string'		,'default'=>null)
											);
	protected final $_aFooterColumns 	= array(
												array('field'=>'Record Type'				,'data_type'=>'string'	,'default'=>'F'),
												array('field'=>'Correspondence Item Count'	,'data_type'=>'numeric'	,'default'=>null)
											);


	protected	$_oFileExporterCSV;
	protected	$_iTimestamp;




	public function __construct($mCarrierModule)
	{
		parent::__construct($mCarrierModule);
		$this->_oTARFileExport	= new File_Export();
		$this->_iTimestamp	= time();
		$this->_oFileExporterCSV	= new File_Exporter_CSV();

	}

	public function render()
	{
		$sFileDirectoryPath	= self::getExportPath($this->getCarrierModule()->Carrier, __CLASS__);
		$sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());
		$sFilename	= $this->_oRun->getCorrespondenceCode()
				.'.'
				.$sTimeStamp
				.'.'
				.$this->_oRun->id
				;

		$this->_sFilePath = $sFileDirectoryPath.$sFilename.'.csv';

		// Render and write to disk
		$this->_oFileExporterCSV->renderToFile($this->_sFilePath);

		if ($this->_bPreprinted)
		{
			// Create tar file
			require_once("Archive/Tar.php");
			$this->_aTARFilePath = $sFileDirectoryPath.$sFilename.'.tar';
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
		foreach ($mRecord as $sField=>$mValue)
		{
			$oRecord->$sField = $mValue;
		}
		$this->_oFileExporterCSV->addRecord($oRecord, File_Exporter_CSV::RECORD_GROUP_BODY);
	}

	public function export()
	{
		$aColumns = $this->_oRun->getAllColumns();
		$this->_bPreprinted = $this->_oRun->preprinted==1?true:false;


		$this->_configureFileExporter($aColumns);

		foreach ($this->_oRun->getCorrespondence() as $oCorrespondence)
		{
			$this->addRecord($oCorrespondence->toArray());
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
		foreach($aColumns as $sColumn)
		{
			$oRecordType->addField($sColumn, File_Exporter_Field::factory());
		}

		$this->_oFileExporterCSV->registerRecordType(self::RECORD_TYPE_DETAIL, $oRecordType);
	}


}