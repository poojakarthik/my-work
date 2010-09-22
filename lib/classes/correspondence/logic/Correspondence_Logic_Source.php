<?php

abstract class Correspondence_Logic_Source
{
	protected $_oDO;
	protected $_bValidationFailed = false;
	protected $_aColumns;
	protected $_aAdditionalColumns;
	protected $_aCorrespondence = array();
	protected $_aLines = array();
	protected $_errorReport;

	protected $_bPreprinted;

	protected $_oTemplate;

	protected $_oRun;

	protected $iLineNumber;

	protected $_aInputColumns =  array(	'customer_group_id',
										'account_id',
										'account_name',
										'title',
										'first_name',
										'last_name',
										'address_line_1',
										'address_line_2',
										'suburb',
										'postcode',
										'state',
										'email',
										'mobile',
										'landline',
										'correspondence_delivery_method_id'
									);
	protected $_aReport = array (
									'success'						=>array(),
									'customer_group_account_id'		=>array(),
									'account_name'					=>array(),
									'first_name'					=>array(),
									'last_name'						=>array(),
									'address_line_1'				=>array(),
									'suburb'						=>array(),
									'postcode'						=>array(),
									'state'							=>array(),
									'customer_group_conflict'		=>array(),
									'email'							=>array(),
									'delivery_method_account_id'	=>array(),
									'invalid account id'			=>array(),
									'column_count'					=>array(),
									'delivery_method'				=>array(),
								);
	protected $_aNullableFields = array (
											'account_id',
											'title',
											'address_line_2',
											'email',
											'mobile',
											'landline',
											'pdf_file_path'
										);
	protected $_aValidCustomerGroups;
	protected $_aValidDeliveryMethods;

	public function __construct($oDataObject, $oTemplate)
	{
		$this->_oDataObject = $oDataObject;// $iId ==null?new Correspondence_Source(array('correspondence_source_type_id'=>$iSourceType)):Correspondence_Source::getForId($iId);
		$this->_oTemplate = $oTemplate;
		$this->_errorReport = new File_Exporter_CSV();
		$this->_errorReport->setDelimiter(Correspondence_Dispatcher_YellowBillingCSV::FIELD_DELIMITER);
		$this->_errorReport->setQuote(Correspondence_Dispatcher_YellowBillingCSV::FIELD_ENCAPSULATOR);
		$this->_errorReport->setQuoteMode(File_Exporter_CSV::QUOTE_MODE_ALWAYS);
		$this->_errorReport->setEscape(Correspondence_Dispatcher_YellowBillingCSV::ESCAPE_CHARACTER);
		$this->_errorReport->setNewLine(Correspondence_Dispatcher_YellowBillingCSV::NEW_LINE_DELIMITER);
	}


	final public function getCorrespondence($bPreprinted, $oRun)
	{
		$this->_oRun = $oRun;
		$this->_bPreprinted = $bPreprinted;
		$this->_aColumns = Correspondence_Logic::getStandardColumns($bPreprinted);
		$this->_aAdditionalColumns = $this->_oTemplate->getAdditionalColumnSet(Correspondence_Logic::getStandardColumnCount($bPreprinted));
		$this->iLineNumber = 1;
		$this->_getCorrespondence();
		$this->_bValidationFailed?$this->processValidationErrors():null;
		return $this->_aCorrespondence;
	}

	abstract protected function _getCorrespondence();

	abstract public function setData($mData);

	public function save()
	{
		if (isset($this->_oDataObject))
			$this->_oDataObject->save();
	}

	/*
	 * Validates a data record and where needed, adds missing data to the data record.
	 * adds an array to the $aRecord parameter passed in with key value 'validation_errors'
	 * Returns true when no errors were found, false if errors were found
	 *
	 * In terms of validating records, there are three categories:
	 * 1 - Records with no Account ID
	 * 2 - Records with an Account ID but no other data
	 * 3 - Records with both an Account ID and other data
	 *
	 * This leads to the following validation groups:
	 *
	 * A - the overlapping set of validations for categories 1 and 3
	 * B - validations that are unique to category 2
	 * C - validations that are unique to category 3
	 * D - validations that are unique to category 1
	 */
	public function validateDataRecord(&$aRecord)
	{

		$aData = $aRecord['standard_fields'];
		$aErrors = array();


		$sEmailDelivery = Correspondence_Delivery_Method::getForId(CORRESPONDENCE_DELIVERY_METHOD_EMAIL)->system_name;
		$sPostDelivery = Correspondence_Delivery_Method::getForId(CORRESPONDENCE_DELIVERY_METHOD_POST)->system_name;


		$bAccountNull = $aData['account_id']==null?true:false;
		$bAllOthersNull = true;
		foreach ($aData as $sField=>$mValue)
		{
			if ($sField!= 'account_id' && $mValue!=null && $bAllOthersNull)
				$bAllOthersNull = false;
		}

		//Validation Group A - the overlapping set of validations for categories 1 and 3 (see block comment above this method)
		if (!(!$bAccountNull && $bAllOthersNull))
		{
			if ($aData['account_name'] == null)
				$aErrors['account_name'] ='Mandatory field Account Name was not provided.';
			if ($aData['first_name'] == null)
				$aErrors['first_name'] ='Mandatory field Contact First Name was not provided.';
			if ($aData['last_name'] == null)
				$aErrors['last_name'] ='Mandatory field Contact Last Name was not provided.';
			//regardless of delivery method, the postal address must be supplied, this is not explicitly stated in the spec, but must be concluded from REQ03
			if ($aData['address_line_1']== null)
				$aErrors['street_address'] ='Mandatory field Addressline 1 was not provided.';
			if ($aData['suburb']== null)
				$aErrors['suburb'] ='Mandatory field Suburb was not provided.';
			if ($aData['postcode']== null)
				$aErrors['postcode'] ='Mandatory field Postcode was notprovided.';
			if ($aData['state']== null)
				$aErrors['state'] ='Mandatory field State was notprovided.';
		}

		if (!$bAccountNull)
		{
			$oAccount = Account::getForId($aData['account_id']);
			if ($oAccount == null)
				$aErrors['invalid_account_id'] = 'Invalid account ID provided. No account with ID \''.$aData['account_id'].'\' exists.';
			$oContact = Contact::getForId($oAccount->PrimaryContact);
			//Validation Group B - validations that are unique to category 2 (see block comment above this method)
			if ($bAllOthersNull && !(array_key_exists('invalid_account_id', $aErrors)))//account number only supplied
			{

					$aData['customer_group_id'] = $oAccount->CustomerGroup;
					$aData['account_name'] = $oAccount->BusinessName;
					$aData['title'] = $oContact->Title;
					$aData['first_name'] = $oContact->FirstName;
					$aData['last_name'] = $oContact->LastName;
					$aData['address_line_1'] = $oAccount->Address1;
					$aData['address_line_2'] = $oAccount->Address2;
					$aData['suburb'] = $oAccount->Suburb;
					$aData['postcode'] = $oAccount->Postcode;
					$aData['state'] = $oAccount->State;
					$aData['email'] = $oContact->Email;
					$aData['mobile'] = $oContact->Mobile;
					$aData['landline'] = $oContact->Phone;
					$aData['correspondence_delivery_method_id'] = $oAccount->BillingMethod == DELIVERY_METHOD_EMAIL?$sEmailDelivery:$sPostDelivery;
					$aRecord['standard_fields'] = $aData;
				}


			//Validation Group C - validations that are unique to category 3 (see block comment above this method)
			else //account number and a number of other values were supplied
			{
				//check customer group
				if ($aData['customer_group_id']==null)
				{
					if (array_key_exists('invalid_account_id', $aErrors))
					{
						$aErrors['customer_group_conflict'] = 'The Customer Group cannot be determined because an incorrect account ID was supplied';
					}
					else
					{
						$aData['customer_group_id'] = $oAccount->CustomerGroup;
					}
				}
				else if ($oAccount->CustomerGroup != $aData['customer_group_id'] && !(array_key_exists('invalid_account_id', $aErrors)))
				{
					$aErrors['customer_group_conflict'] = 'Incorrect Customergroup: provided value must be \''.$oAccount->CustomerGroup.'\' but is \''.$aData['customer_group_id'].'\'';
				}

				//if delivery method is null: derive delivery method
				if ($aData['correspondence_delivery_method_id'] == null)
				{
					if (array_key_exists('invalid_account_id', $aErrors))
					{
						$aErrors['delivery_method_account_id'] = 'Delivery method cannot be determined because an incorrect account ID was supplied';
					}
					else
					{
						$aData['correspondence_delivery_method_id'] = $oAccount->BillingMethod == DELIVERY_METHOD_EMAIL?$sEmailDelivery:$sPostDelivery;
					}
				}

				//check that a valid delivery method was selected
				if ($aData['correspondence_delivery_method_id'] != $sEmailDelivery && $aData['correspondence_delivery_method_id'] != $sPostDelivery)
					$aErrors['delivery_method'] = 'Invalid Delivery Method selected';

				//if delivery method is email: check required fields
				if ($aData['correspondence_delivery_method_id'] == $sEmailDelivery)
				{
					if ($aData['email']== null)
					{
						$aErrors['email'] ='Delivery Method is Email but no email address supplied.';
					}
					else if (!DO_SalesValidation::isValidEmailAddress($aData['email']))
					{
						$aErrors['email'] ='Invalid email address supplied.';
					}

				}
			}
		}
		//Validation Group D - validations that are unique to category 1 (see block comment above this method)
		else //account number was not supplied
		{
			//customer group
			$this->_aValidCustomerGroups = $this->_aValidCustomerGroups==null?array_keys(Customer_Group::getAll()):$this->_aValidCustomerGroups;
			if ($aData['customer_group_id']==null)
			{
				$aErrors['customer_group_account_id'] = 'No Account ID and no Customer Group ID provided.';
			}
			else if (!in_array($aData['customer_group_id'], $this->_aValidCustomerGroups))
			{
				$aErrors['customer_group_account_id'] = 'Selected Customer Group does not exist.';
			}

			//check that delivery method is not null
			if ($aData['correspondence_delivery_method_id'] == null)
				$aErrors['delivery_method_account_id'] = 'No Account ID and no Delivery Method provided.';

			//check that a valid delivery method was selected
			if ($aData['correspondence_delivery_method_id'] != $sEmailDelivery && $aData['correspondence_delivery_method_id'] != $sPostDelivery)
				$aErrors['delivery_method'] = 'Invalid Delivery Method selected';


			//if delivery method is email: check required fields
			if ($aData['correspondence_delivery_method_id'] == $sEmailDelivery)
			{
				if ($aData['email']== null)
				{
					$aErrors['email'] ='Delivery Method is Email but no email address supplied.';
				}
				else if (!DO_SalesValidation::isValidEmailAddress($aData['email']))
				{
					$aErrors['email'] ='Invalid email address supplied.';
				}
			}
		}

		$aRecord['standard_fields'] = $aData;

		//column count validation
		if ($this->getColumnCount()!= (count($aRecord['standard_fields']) + count($aRecord['additional_fields'])))
		{
			$aErrors['column_count'] = $this->getColumnCount()." columns required - ".(count($aRecord['standard_fields']) + count($aRecord['additional_fields']))." columns provided.";
		}
		$aRecord['validation_errors'] = $aErrors;
		return count($aErrors)==0;
	}

	public function getColumnCount()
	{
		return (count($this->_aColumns) + count($this->_aAdditionalColumns));
	}


	protected function processCorrespondenceRecord($aRecord)
	{
		$bValid = $this->validateDataRecord($aRecord);
		if (!$bValid)
			$this->_bValidationFailed = true;

		$aRecord = $this->_sanitize($aRecord);
		if (!$this->_bValidationFailed)
		{
			$oCorrespondence = new Correspondence_Logic($aRecord);
			$oCorrespondence->_oCorrespondenceRun = $this->_oRun;
			$this->_aCorrespondence[] = $oCorrespondence;
		}

		$this->_aLines[]= $aRecord;
		foreach ($aRecord['validation_errors'] as $sErrorType=>$sMessage)
		{
			$this->_aReport[$sErrorType][$this->iLineNumber]=$sMessage;
		}
		if (count($aRecord['validation_errors'])==0)
			$this->_aReport['success'][]= $this->iLineNumber;
	}

	protected function _sanitize($aRecord)
	{
		foreach ($this->_aNullableFields as $sField)
		{
			if (isset($aRecord['standard_fields'][$sField]))
				$aRecord['standard_fields'][$sField] = $aRecord['standard_fields'][$sField]==""?null:$aRecord['standard_fields'][$sField];
		}
		return $aRecord;
	}

	protected function processValidationErrors()
	{
		//create data file with error messages
		$oRecordType = File_Exporter_RecordType::factory();

		$aCols = $this->getColumnsForErrorReport();
		foreach($aCols as $sColumn)
		{
			$oRecordType->addField($sColumn, File_Exporter_Field::factory());
		}

		$this->_errorReport->registerRecordType('detail', $oRecordType);

		$oRecord	= $this->_errorReport->getRecordType('detail')->newRecord();
		foreach($aCols as $sColumn)
		{
			$oRecord->$sColumn = $sColumn;
		}
		$this->_errorReport->addRecord($oRecord, File_Exporter_CSV::RECORD_GROUP_BODY);
		foreach($this->_aLines as $aLine)
		{
			$this->addErrorRecord($aLine);
		}
		$sPath = FILES_BASE_PATH.'temp/';
		$sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());


		$sFilename	= $this->_oTemplate->template_code
		.'.'
		.$sTimeStamp
		.'.'
		.'error_report'
		.'.csv'
		;
		$this->_errorReport->renderToFile($sPath.$sFilename);
		throw new Correspondence_DataValidation_Exception(Correspondence_DataValidation_Exception::DATAERROR, $this->_aReport, $sPath.$sFilename);

	}

	public function getColumnsForErrorReport()
	{
		$aAdditionalColumns = $this->_aAdditionalColumns;
		$iMaxColCount = count($this->_aAdditionalColumns);
		//find the line with the most number of columns
		foreach ($this->_aLines as $aLine)
		{
			$iColumnCount = count($aLine['additional_fields']);
			if ($iColumnCount>$iMaxColCount)
			{
				$aAdditionalColumns = array_keys($aLine['additional_fields']);
				$iMaxColCount = $iColumnCount;
			}

		}
		$aStandardColumns = $this->_bPreprinted?array_merge($this->_aInputColumns , array('pdf_file_path')):$this->_aInputColumns;

		return array_merge(array('validation_errors'),$aStandardColumns, $aAdditionalColumns);
	}

	public function addErrorRecord($aLine)
	{
		$oRecord	= $this->_errorReport->getRecordType('detail')->newRecord();

		foreach ($aLine as $sField=>$aValue)
		{

			if ($sField == 'validation_errors')
			{
				$oRecord->$sField = implode(';', $aValue);

			}
			else
			{
				foreach ($aValue as $key=>$mValue)
				{
					$oRecord->$key = $mValue;
				}
			}
		}
		$this->_errorReport->addRecord($oRecord, File_Exporter_CSV::RECORD_GROUP_BODY);

	}

	public function import()
	{
		return null;
	}


	public static function factory($oTemplate)
	{
			$oCorrespondenceSource = Correspondence_Source::getForId($oTemplate->correspondence_source_id);
			$iSourceTypeId = $oCorrespondenceSource!=null?$oCorrespondenceSource->correspondence_source_type_id:null;
			switch($iSourceTypeId)
			{
				case(CORRESPONDENCE_SOURCE_TYPE_CSV):
										return new Correspondence_Logic_Source_Csv($oTemplate);
										break;
				case (CORRESPONDENCE_SOURCE_TYPE_SQL):
										return new Correspondence_Logic_Source_Sql($oTemplate);
										break;
				case (CORRESPONDENCE_SOURCE_TYPE_SYSTEM):
										return new Correspondence_Logic_Source_System($oTemplate);
										break;
				default:
										return null;
			}
	}

	public function  __get($sField)
	{
		return $this->_oDataObject->$sField;
	}


}


class Correspondence_DataValidation_Exception extends Exception
{
	public $aReport;
	public $sFileName;
	public $iError;

	const NODATA = CORRESPONDENCE_RUN_ERROR_NO_DATA;
	const SQLERROR = CORRESPONDENCE_RUN_ERROR_SQL_SYNTAX;
	const DATAERROR = CORRESPONDENCE_RUN_ERROR_MALFORMED_INPUT;
	const DUPLICATE_FILE = 4;


	public function __construct($iError, $aReport = null, $sFileName = null)
	{
		parent::__construct();
		$this->aReport 		= $aReport	;
		$this->sFileName 	= $sFileName;
		$this->iError		= $iError;
	}

	public function failureReasonToString()
	{

		return $this->iError==null?null:($this->iError==CORRESPONDENCE_RUN_ERROR_NO_DATA?"No Data":($this->iError==CORRESPONDENCE_RUN_ERROR_MALFORMED_INPUT?"Invalid Data":($this->iError==CORRESPONDENCE_RUN_ERROR_SQL_SYNTAX?"Invalid SQL":null)));

	}


}



?>