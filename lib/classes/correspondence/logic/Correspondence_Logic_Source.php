<?php

abstract class Correspondence_Logic_Source
{
	protected $_oDO;
	protected $_bValidationFailed = false;


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

	public function __construct( $iSourceType, $iId = null)
	{
		$this->_oDO = $iId ==null?new Correspondence_Source(array('correspondence_source_type_id'=>$iSourceType)):Correspondence_Source::getForId($iId);
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
	public function validateDataRecord($aRecord)
	{
		$aData = $aRecord['standard_fields'];
		$aErrors = array();
		$bAccountNull = $aData['account_id']==null?true:false;
		$bAllOthersNull = true;
		foreach ($aData as $sField=>$mValue)
		{
			if ($mValue!=null && $bAllOthersNull)
				$bAllOthersNull = false;
		}

		//Validation Group A - the overlapping set of validations for categories 1 and 3 (see block comment above this method)
		if (!(!$bAccountNull && $bAllOthersNull))
		{
			if ($aData['account_name'] == null)
				$aErrors[] ='Mandatory field Account Name was not provided.';
			if ($aData['first_name'] == null)
				$aErrors[] ='Mandatory field Contact First Name was not provided.';
			if ($aData['last_name'] == null)
				$aErrors[] ='Mandatory field Contact Last Name was not provided.';
			//regardless of delivery method, the postal address must be supplied, this is not explicitly stated in the spec, but must be concluded from REQ03
			if ($aData['address_line_1']== null)
				$aErrors[] ='No street address provided.';
			if ($aData['suburb']== null)
				$aErrors[] ='No suburb provided.';
			if ($aData['postcode']== null)
				$aErrors[] ='No postcode provided';
			if ($aData['state']== null)
				$aErrors[] ='No state provided.';
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
				$aData['last_name'] = $oContact->LatName;
				$aData['address_line_1'] = $oAccount->Address1;
				$aData['address_line2'] = $oAccount->Address2;
				$aData['suburb'] = $oAccount->Suburb;
				$aData['postcode'] = $oAccount->PostCode;
				$aData['state'] = $oAccount->State;
				$aData['email'] = $oContact->Email;
				$aData['mobile'] = $oContact->Mobile;
				$aData['landline'] = $oContact->Phone;
				$aData['correspondence_delivery_method'] = $oAccount->BillingMethod == DELIVERY_METHOD_EMAIL?CORRESPONDENCE_DELIVERY_METHOD_EMAIL:CORRESPONDENCE_DELIVERY_METHOD_POST;
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
					$aErrors[] = 'Incorrect Customergroup: provided value is \''.$oAccount->CustomerGroup.'\' but must be \''.$aData['customer_group_id'];
				}

				//if delivery method is null: derive delivery method
				if ($aData['correspondence_delivery_method'] == null)
					$aData['correspondence_delivery_method'] = $oAccount->BillingMethod == DELIVERY_METHOD_EMAIL?CORRESPONDENCE_DELIVERY_METHOD_EMAIL:CORRESPONDENCE_DELIVERY_METHOD_POST;


				//if delivery method is email: check required fields
				if ($aData['correspondence_delivery_method'] == CORRESPONDENCE_DELIVERY_METHOD_EMAIL && $aData['email']== null)//add validation of email address
					$aErrors[] ='Delivery Method is Email, but no email address supplied.';
			}

		}
		//Validation Group D - validations that are unique to category 1 (see block comment above this method)
		else //account number was not supplied
		{
			//customer group
			if ($aData['customer_group_id']==null)
				$aErrors[] = 'No Account ID and no Customer Group ID provided.';

			//check that delivery method is not null
			if ($aData['correspondence_delivery_method'] == null)
				$aErrors[] = 'No Account ID and no Delivery Method provided.';
			if ($aData['correspondence_delivery_method'] == CORRESPONDENCE_DELIVERY_METHOD_EMAIL && $aData['email']== null)
				$aErrors[] ='Delivery Method is Email, but no email address supplied.';

			//if delivery method is email: check required fields
			if ($aData['correspondence_delivery_method'] == CORRESPONDENCE_DELIVERY_METHOD_EMAIL && $aData['email']== null) //add validation of email address
				$aErrors[] ='Delivery Method is Email, but no email address supplied.';

		}

		$aRecord['standard_fields'] = $aData;
		$aRecord['validation_errors'] = $aErrors;

		return count($aErrors)>0;

	}

}


?>