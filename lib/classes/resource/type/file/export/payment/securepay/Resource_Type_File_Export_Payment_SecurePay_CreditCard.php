<?php
/**
 * Resource_Type_File_Export_Payment_SecurePay_CreditCard
 *
 * @class	Resource_Type_File_Export_Payment_SecurePay_CreditCard
 */
class Resource_Type_File_Export_Payment_SecurePay_CreditCard extends Resource_Type_File_Export_Payment
{
	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_EXPORT_SECUREPAY_CREDIT_CARD_FILE;
	
	const	RECORD_TYPE_TRANSACTION	= 'TRANSACTION';
	
	const	NEW_LINE_DELIMITER	= "\n";
	const	FIELD_DELIMITER		= ',';
	const	FIELD_ENCAPSULATOR	= '';
	const	ESCAPE_CHARACTER	= '\\';
	
	protected	$_oFileExport;
	protected	$_oFileExporter;
	protected	$_iTimestamp;
	
	public function __construct($mCarrierModule)
	{
		parent::__construct($mCarrierModule);
		
		$this->_iTimestamp	= time();
		
		$this->_oFileExporter	= new File_Exporter_CSV();
		$this->_configureFileExporter();
	}
	
	public function addRecord($mPaymentRequest)
	{
		$oRecord	= $this->_oFileExporter->getRecordType(self::RECORD_TYPE_TRANSACTION)->newRecord();
		
		$oPaymentRequest	= Payment_Request::getForId(ORM::extractId($mPaymentRequest));
		$oAccount			= Account::getForId($oPaymentRequest->account_id);
		//$oAccountHistory	= Account_History::getForAccountAndEffectiveDatetime($oAccount->Id, $oPaymentRequest->created_datetime);
		$oPayment			= Payment::getForId($oPaymentRequest->payment_id);
		$aAccountHistory	= Account_History::getForAccountAndEffectiveDatetime($oPaymentRequest->account_id, $oPaymentRequest->created_datetime);
		$oCreditCard		= Credit_Card::getForId($aAccountHistory['credit_card_id']);
		
		// Verify that the payment type is correct
		Flex::assert($oPaymentRequest->payment_type_id === PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD, "Non Credit Card Payment Request sent to SecurePay Direct Debit via Credit Card Export File", print_r($oPaymentRequest->toStdClass(), true));
		
		// Verify that the payment hasn't been reversed
		Flex::assert($oPayment->Status !== PAYMENT_REVERSED, "A Payment Request that is tied to a reversed payment was sent to SecurePay Direct Debit via Credit Card Export File", print_r($oPaymentRequest->toStdClass(), true));
		
		$iExpiryMonth	= (int)$oCreditCard->ExpMonth;
		$iExpiryYear	= (int)$oCreditCard->ExpYear;
		$iExpiryYear	= ($iExpiryYear > 99) ? (int)substr($iExpiryYear, -2) : $iExpiryYear;
		if ($iExpiryYear > 99)
		{
			// Use 2038 (UNIX Timestamp end-of-time) as our axis
			$iExpiryYear	+= ($iExpiryYear <= 38) ? 2000 : 1900;
		}
		$sExpiryMonth	= str_pad($iExpiryMonth, 2, '0', STR_PAD_LEFT);
		$sExpiryYear	= substr($iExpiryYear, -2);
		
		if ((int)date('ym', $this->_iTimestamp) > (int)("{$sExpiryYear}{$sExpiryMonth}"))
		{
			throw new Exception("Credit Card Expired: '{$sExpiryYear}/{$sExpiryMonth}'");
		}
		
		$oRecord->CCNumber		= preg_replace('/[^\d]+/', '', Decrypt($oCreditCard->CardNumber));
		$oRecord->ExpiryDate	= "{$sExpiryMonth}/{$sExpiryYear}";
		$oRecord->AmountCharged	= $oPaymentRequest->amount;
		$oRecord->FlexAccount	= $oPaymentRequest->account_id/*.'R'.$oPaymentRequest->id/**/;
		$oRecord->CustomerName	= $oAccount->BusinessName;
		
		// TODO: Perhaps use the FlexAccount field as a Reference?
		Flex::assert(false, "DEBUG: [Account.Id]R[payment_request.id] Transaction Reference not implemented yet!");
		
		// Add to the file
		$this->_oFileExporter->addRecord($oRecord, File_Exporter_CSV::RECORD_GROUP_BODY);
		
		// TODO: Do we need to return anything special?
		return;
	}
	
	public function render()
	{
		// Filename
		$sFilename			= $this->getConfig()->FileNamePrefix.'0009'.'.csv';
		$this->_sFilePath	= self::getExportPath($this->getCarrierModule()->Carrier, __CLASS__).$sFilename;
		
		// Render and write to disk
		$this->_oFileExporter->renderToFile($this->_sFilePath);
		
		// TODO: Do we need to return anything special?
		return $this;
	}
	
	public function deliver()
	{
		$this->_oFileDeliver->connect()->deliver($this->_sFilePath)->disconnect();
		return $this;
	}
	
	protected function _configureFileExporter()
	{
		$this->_iTimestamp	= time();
		
		// Detail Record
		$this->_oFileExporter->registerRecordType(self::RECORD_TYPE_TRANSACTION,
			File_Exporter_RecordType::factory()
				->addField('CCNumber',
					File_Exporter_Field::factory()
				)->addField('ExpiryDate',
					File_Exporter_Field::factory()
				)->addField('AmountCharged',
					File_Exporter_Field::factory()
				)->addField('FlexAccount',
					File_Exporter_Field::factory()
				)->addField('CustomerName',
					File_Exporter_Field::factory()->setMaximumLength(32, true)
				)
		);
	}
	
	public static function getAssociatedPaymentType()
	{
		return PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD;
	}
	
	/***************************************************************************
	 * COMMON METHODS FOR ALL Resource_Type_Base CHILDREN
	 **************************************************************************/
	
	static public function createCarrierModule($iCarrier, $sClass=__CLASS__)
	{
		parent::createCarrierModule($iCarrier, $sClass, self::RESOURCE_TYPE);
	}
	
	static public function defineCarrierModuleConfig()
	{
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'FileNamePrefix'	=>	array('Description'=>'3-Character CustomerGroup Prefix for the FileName (eg. SAE, VOI)')
		));
	}
}