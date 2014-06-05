<?php
class Resource_Type_File_Export_Payment_Westpac_RecurringBilling extends Resource_Type_File_Export_Payment {
	const RESOURCE_TYPE = RESOURCE_TYPE_FILE_EXPORT_DIRECT_DEBIT_WESTPAC_RECURRINGBILLING;

	const RECORD_TYPE_HEADER = 'HEADER';
	const RECORD_TYPE_TRANSACTION = 'TRANSACTION';

	const NEW_LINE_DELIMITER = "\n";
	const FIELD_DELIMITER = ',';
	const FIELD_ENCAPSULATOR = '"';
	const ESCAPE_CHARACTER = '"';

	protected $_oFileExport;
	protected $_oFileExporter;
	protected $_iTimestamp;

	public function __construct($mCarrierModule) {
		parent::__construct($mCarrierModule);

		$this->_iTimestamp = time();
		$this->_oFileExporter = new File_Exporter_CSV();
		$this->_configureFileExporter();
		$this->_addHeaderRecord();
	}

	protected function getCustomerGroups() {
		return is_array($this->getConfig()->CustomerGroups) ? $this->getConfig()->CustomerGroups : array($this->getCarrierModule()->customer_group);
	}

	public function addRecord($mPaymentRequest) {
		$oRecord = $this->_oFileExporter->getRecordType(self::RECORD_TYPE_TRANSACTION)->newRecord();

		$oPaymentRequest = Payment_Request::getForId(ORM::extractId($mPaymentRequest));
		$oPayment = Payment::getForId($oPaymentRequest->payment_id);
		$oAccount = Account::getForId($oPaymentRequest->account_id);
		$aAccountHistory = Account_History::getForAccountAndEffectiveDatetime($oPaymentRequest->account_id, $oPaymentRequest->created_datetime);
		$oBankAccount = DirectDebit::getForId($aAccountHistory['direct_debit_id']);

		// Verify that the payment hasn't been reversed
		Flex::assert(
			$oPayment->getReversal() === null,
			"A Payment Request that is tied to a reversed payment was added to an 'Westpac Recurring Billing' Export File",
			print_r($oPaymentRequest->toStdClass(), true)
		);

		// Ensure that the Amount is greater than zero (file format dictates that the value cannot be 0)
		Flex::assert(
			$oPayment->amount > 0,
			"$0.00-valued Payment requested in 'Westpac Recurring Billing' Export File",
			print_r($oPaymentRequest->toStdClass(), true)
		);

		$oRecord->CustomerNumber = $oAccount->Id;
		$oRecord->CustomerName = trim($oAccount->BusinessName);
		$oRecord->Amount = number_format(round($oPaymentRequest->amount, 2), 2, '.', '');
		$oRecord->OrderNumber = $oPayment->transaction_reference;

		// Add to the file
		$this->_oFileExporter->addRecord($oRecord, File_Exporter::RECORD_GROUP_BODY);

		return;
	}

	public function render() {
		// Filename
		$sFilename = sprintf('%s.%s.csv', $this->getConfig()->FileNamePrefix, date('Ymd', $this->_iTimestamp));
		$this->_sFilePath = self::getExportPath($this->getCarrierModule()->Carrier, __CLASS__).$sFilename;

		// Render and write to disk
		$this->_oFileExporter->renderToFile($this->_sFilePath);

		// TODO: Do we need to return anything special?
		return $this;
	}

	public function deliver() {
		$this->_oFileDeliver->connect()->deliver($this->_sFilePath)->disconnect();
		return $this;
	}

	protected function _addHeaderRecord() {
		$oRecord = $this->_oFileExporter->getRecordType(self::RECORD_TYPE_HEADER)->newRecord();

		// NOTE: The following fields have default values
		// - FileType
		// - FileTypeVersion
		$oRecord->SettlementDate = date('j M Y', $this->_iTimestamp);

		// Add to the file
		$this->_oFileExporter->addRecord($oRecord, File_Exporter::RECORD_GROUP_BODY);

		return;
	}

	protected function _configureFileExporter() {
		$this->_iTimestamp = time();

		$this->_oFileExporter->setDelimiter(self::FIELD_DELIMITER)
			->setNewLine(self::NEW_LINE_DELIMITER)
			->setQuote(self::FIELD_ENCAPSULATOR)
			->setEscape(self::ESCAPE_CHARACTER)
			->setQuoteMode(File_Exporter_CSV::QUOTE_MODE_REACTIVE)
			->setEscapeMode(File_Exporter_CSV::ESCAPE_MODE_RFC4180);

		// Header Record
		$this->_oFileExporter->registerRecordType(self::RECORD_TYPE_HEADER,
			File_Exporter_RecordType::factory()
				->addField('FileType',
					File_Exporter_Field::factory()
						->setDefaultValue('Recurring Billing Upload')
						->setValidationRegex('/^Recurring Billing Upload$/')
				)->addField('FileTypeVersion',
					File_Exporter_Field::factory()
						->setDefaultValue(' v1.00')
				)->addField('SettlementDate',
					File_Exporter_Field::factory()
						->setValidationRegex('/^([1-9]|[12][0-9]|3[01]) ([A-Z][a-z]+) (\d{4})$/')
				)
		);

		// Detail Record
		$this->_oFileExporter->registerRecordType(self::RECORD_TYPE_TRANSACTION,
			File_Exporter_RecordType::factory()
				->addField('CustomerNumber',
					File_Exporter_Field::factory()
				)->addField('CustomerName',
					File_Exporter_Field::factory()
				)->addField('Amount',
					File_Exporter_Field::factory()
						->setValidationRegex('/^\d+\.\d{2}$/')
				)->addField('OrderNumber',
					File_Exporter_Field::factory()
						->setMaximumLength(20)
						->setValidationRegex('/^(|\d{1,15}|[^&%+]{1,20})$/')
				)
		);
	}

	public function getAssociatedPaymentTypes() {
		return $this->getConfig()->PaymentTypes;
	}

	/***************************************************************************
	 * COMMON METHODS FOR ALL Resource_Type_Base CHILDREN
	 **************************************************************************/

	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClass=__CLASS__) {
		parent::createCarrierModule($iCarrier, $iCustomerGroup, $sClass, self::RESOURCE_TYPE);
	}

	static public function defineCarrierModuleConfig() {
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'FileNamePrefix' => array('Description' => 'Optional prefix to apply to the exported file for uniqueness and descriptiveness. Resulting filename will look like [prefix].[yyyymmdd].csv'),
			'PaymentTypes' => array(
				'Type' => DATA_TYPE_ARRAY,
				'Description' => 'Payment Types of Payment Requests to be associated with this module'
			),
			'CustomerGroups' => array(
				'Type' => DATA_TYPE_ARRAY,
				'Description' => 'Customer Groups allowed in this file',
				'Value' => array()
			)
		));
	}
}