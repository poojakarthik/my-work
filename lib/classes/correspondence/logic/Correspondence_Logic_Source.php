<?php

abstract class Correspondence_Logic_Source
{
	protected $_oDO;
	protected $_bValidationFailed = false;
	protected $_aColumns;
	protected $_aCorrespondence = array();
	protected $_aLines = array();
	protected $_errorReport;

	protected $_oTemplate;


	protected $iLineNumber;

	protected $_aInputColumns =  array(	'customer_group_id',
										'account_id',
										'account_name',
										'title',
										'first_name',
										'last_name',
										'address_line_1',
										'address_line2',
										'suburb',
										'postcode',
										'state',
										'email',
										'mobile',
										'landline',
										'correspondence_delivery_method'
									);
	protected $_aReport = array (
									'success'						=>array(),
									'customer_group_account_id'		=>array(),
									'account_name'					=>array(),
									'first_name'					=>array(),
									'last_name'						=>array(),
									'suburb'						=>array(),
									'postcode'						=>array(),
									'state'							=>array(),
									'customer_group_conflict'		=>array(),
									'email'							=>array(),
									'delivery_method_account_id'	=>array(),
									'column_count'					=>array('required'=>null, 'supplied'=>null)
								);

	public function __construct( $iSourceType= null, $iId = null)
	{
		$this->_oDO = $iId ==null?new Correspondence_Source(array('correspondence_source_type_id'=>$iSourceType)):Correspondence_Source::getForId($iId);
		$this->_errorReport = new File_Exporter_CSV();
	}


	/*
	 * to be implemented by each child class
	 * every implementation of this method must return data in the same format
	  */
	abstract public function getData($aColumns);

	public function save()
	{
		if (isset($this->_oDO))
			$this->_oDO->save();
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
				$aErrors['street_address'] ='Mandatory field Street Address was not provided.';
			if ($aData['suburb']== null)
				$aErrors['suburub'] ='Mandatory field Suburb was not provided.';
			if ($aData['postcode']== null)
				$aErrors['postcode'] ='Mandatory field Postcode was notprovided.';
			if ($aData['state']== null)
				$aErrors['state'] ='Mandatory field State was notprovided.';
		}



		if (!$bAccountNull)
		{
			$oAccount = Account::getForId($aData['account_id']);
			$oContact = Contact::getForId($oAccount->PrimaryContact);
			//Validation Group B - validations that are unique to category 2 (see block comment above this method)
			if ($bAllOthersNull)//account number only supplied
			{
				$aData['customer_group_id'] = $oAccount->CustomerGroup;
				$aData['account_name'] = $oAccount->BusinessName;
				$aData['title'] = $oContact->Title;
				$aData['first_name'] = $oContact->FirstName;
				$aData['last_name'] = $oContact->LastName;
				$aData['address_line_1'] = $oAccount->Address1;
				$aData['address_line2'] = $oAccount->Address2;
				$aData['suburb'] = $oAccount->Suburb;
				$aData['postcode'] = $oAccount->Postcode;
				$aData['state'] = $oAccount->State;
				$aData['email'] = $oContact->Email;
				$aData['mobile'] = $oContact->Mobile;
				$aData['landline'] = $oContact->Phone;
				$aData['correspondence_delivery_method'] = $oAccount->BillingMethod == DELIVERY_METHOD_EMAIL?$sEmailDelivery:$sPostDelivery;
				$aRecord['standard_fields'] = $aData;

			}
			//Validation Group C - validations that are unique to category 3 (see block comment above this method)
			else //account number and a number of other values were supplied
			{
				//check customer group
				if ($aData['customer_group_id']==null)
				{
					$aData['customer_group_id'] = $oAccount->CustomerGroup;
				}
				else if ($oAccount->CustomerGroup != $aData['customer_group_id'])
				{
					$aErrors['customer_group_conflict'] = 'Incorrect Customergroup: provided value must be \''.$oAccount->CustomerGroup.'\' but is \''.$aData['customer_group_id'].'\'';
				}

				//if delivery method is null: derive delivery method
				if ($aData['correspondence_delivery_method'] == null)
					$aData['correspondence_delivery_method'] = $oAccount->BillingMethod == DELIVERY_METHOD_EMAIL?$sEmailDelivery:$sPostDelivery;


				//if delivery method is email: check required fields
				if ($aData['correspondence_delivery_method'] == $sEmailDelivery && $aData['email']== null)//add validation of email address
					$aErrors['email'] ='Delivery Method is Email but no email address supplied.';
			}

		}
		//Validation Group D - validations that are unique to category 1 (see block comment above this method)
		else //account number was not supplied
		{
			//customer group
			if ($aData['customer_group_id']==null)
				$aErrors['customer_group_account_id'] = 'No Account ID and no Customer Group ID provided.';

			//check that delivery method is not null
			if ($aData['correspondence_delivery_method'] == null)
				$aErrors['delivery_method_account_id'] = 'No Account ID and no Delivery Method provided.';
			if ($aData['correspondence_delivery_method'] == $sEmailDelivery && $aData['email']== null)//add email address validation
				$aErrors['email'] ='Delivery Method is Email but no email address supplied.';

		}

		$aRecord['standard_fields'] = $aData;
		$aRecord['validation_errors'] = $aErrors;

		return count($aErrors)==0;

	}

	public function columnCountValidation($aAdditionalColumns, $mRecord)
	{
		if (count($this->_aColumns) + count($aAdditionalColumns)!= $this->columnCount($mRecord))
			{
				$this->_bValidationFailed = true;
			}
			$this->_aReport['column_count']['required']	= count($this->_aColumns) + count($aAdditionalColumns);
			$this->_aReport['column_count']['supplied']	= $this->columnCount($mRecord);

			return $this->_bValidationFailed;
	}

	abstract public function columnCount($mDataRecord);


	protected function processCorrespondenceRecord($aRecord)
	{
		$bValid = $this->validateDataRecord($aRecord);
		if (!$bValid)
			$this->_bValidationFailed = true;
		if (!$this->_bValidationFailed)
		{
			$this->_aCorrespondence[] = new Correspondence_Logic($aRecord);
		}

		$this->_aLines[]=$aRecord;
		foreach ($aRecord['validation_errors'] as $sErrorType=>$sMessage)
		{
			$this->_aReport[$sErrorType][]=$iLineNumber;
		}
		if (count($aRecord['validation_errors'])==0)
			$this->_aReport['success'][]= $this->iLineNumber;
	}

	protected function processValidationErrors()
	{
		//create data file with error messages
		$oRecordType = File_Exporter_RecordType::factory();

		foreach($this->_aLines[0] as $key =>$aLinePart)
		{
			if ($key == 'validation_errors')
			{
				$oRecordType->addField($key, File_Exporter_Field::factory());
			}
			else
			{
				foreach($aLinePart as $key2=>$value)
					$oRecordType->addField($key2, File_Exporter_Field::factory());
			}
		}

		$this->_errorReport->registerRecordType('detail', $oRecordType);

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
		throw new Correspondence_DataValidation_Exception($this->_aReport, $sPath.$sFilename);
		//generate email
		//return a summary error message and url for the error file
	}

	public function setTemplate($oTemplate)
	{
		$this->_oTemplate = $oTemplate;
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


}



class Correspondence_DataValidation_Exception extends Exception
{
	public $aReport;
	public $sFileName;
	public $bNoData;
	public $bSqlError;

	public function __construct($aReport = null, $sFileName = null, $bNoData = false, $bSqlError = false)
	{
		parent::__construct();
		$this->aReport 		= $aReport	;
		$this->sFileName 	= $sFileName;
		$this->bNodata		= $bNoData	;
		$this->bSqlError	= $bSqlError;
	}
}


?>