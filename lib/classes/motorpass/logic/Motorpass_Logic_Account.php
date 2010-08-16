<?php

class Motorpass_Logic_Account extends Motorpass_Logic_LogicClass
{


	public $oStreetAddress;
	public $oPostalAddress;
	public $oContact;
	public $oDOReferrer;
	public $oCard;
	public $aTradeRefs = array();

	public function __construct($mAccountDetails)
	{
		$this->aUneditable = array('contact_id', 'card_id', 'modified','modified_dealer_id');


		if ($mAccountDetails && get_class($mAccountDetails)=='stdClass')
		{
			//create the objects that are part of the account
			$this->oCard = new Motorpass_Logic_Card($mAccountDetails->card);
			$this->oStreetAddress = new Motorpass_Logic_Address($mAccountDetails->street_address);
			$this->oPostalAddress = new Motorpass_Logic_Address($mAccountDetails->postal_address);
			//$this->oDOReferrer = new DO_Spmotorpass_Spmotorpass_Referrer((array)$mAccountDetails->referrer);
			//$this->oDOReferrer->setUnsavedChangesFlag();
			$this->oContact = new Motorpass_Logic_Contact($mAccountDetails->contact);
			//delete from the account object what we don't want to be in there
			$aTradeRefs = $mAccountDetails->trade_references;
			unset($mAccountDetails->trade_references);
			unset($mAccountDetails->card);
			unset($mAccountDetails->street_address);
			unset($mAccountDetails->postal_address);
			unset($mAccountDetails->referrer);
			unset($mAccountDetails->contact);

			//now create the account object itself
			$mAccountDetails->card_id = $this->oCard->id;
			$mAccountDetails->street_address_id = $this->oStreetAddress->id;
			$mAccountDetails->postal_address_id = $this->oPostalAddress->id;
			$mAccountDetails->referrer_id = $this->oDOReferrer->id;
			$mAccountDetails->contact_id = $this->oContact->id;

			$this->aTradeRefs = Motorpass_Logic_TradeReference::createFromStd($aTradeRefs, $this);


		}

		parent::__construct($mAccountDetails, 'Motorpass_Account');

		if (get_class($mAccountDetails)!='stdClass')
		{
			$this->oStreetAddress = new Motorpass_Logic_Address($this->oDO->getStreetAddress());
			$this->oPostalAddress = new Motorpass_Logic_Address($this->oDO->getPostalAddress());
			$this->oContact = new Motorpass_Logic_Contact($this->oDO->getContact());
			//$this->oDOReferrer = $this->oDO->getReferrer(true);
			//if (!isset($this->oDOReferrer))
			//	$this->oDOReferrer = new DO_Spmotorpass_Spmotorpass_Referrer();
			$this->oCard = new Motorpass_Logic_Card($this->oDO->getCard());
			$this->aTradeRefs = Motorpass_Logic_TradeReference::getForParent($this);
		}


		if ($this->oDO->id == null)
		{
			$this->oDO->modified_dealer_id = Flex::getUserId();;
			$this->oDO->modified = Data_Source_Time::currentTimestamp();
		}
		return $this->oDO->id;

	}


	public function save()
	{
		$this->validate();
		if (count($this->aErrors)==0)
		{

			// Start a new database transaction
			$oDataAccess	= DataAccess::getDataAccess();

			if (!$oDataAccess->TransactionStart())
			{
				// Failure!
				return 	array(
							"Success"	=> false,
							"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? 'Could not start database transaction.' : false,
						);
			}

			try
			{
				if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
				{
					throw new JSON_Handler_Account_Exception('You do not have permission to add a rebill');
				}

				$aErrors	= array();

				$this->_save();

				// Everything looks OK -- Commit!
				$oDataAccess->TransactionCommit();

				return 	array(
							"Success"	=> true,
							"oRebill"	=> $oStdClassRebill
						);
		}

		catch (Exception $e)
		{
			// Exception caught, rollback db transaction
			$oDataAccess->TransactionRollback();

			return 	array(
						"Success"	=> false,
						"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? $e->getMessage() : 'There was an error accessing the database'
					);
		}





		}
		else
		{
			return $this->aErrors;
		}

	}

	public function _save()
	{
			$this->oDO->card_id = $this->oCard->_save();
			$this->oDO->street_address_id = $this->oStreetAddress->_save();
			$this->oDO->postal_address_id = $this->oPostalAddress->_save();
			$this->oDO->contact_id = $this->oContact->_save();

			//save the referrer
			if($this->oDOReferrer->hasUnsavedChanges())
			{
				//create a new record to contain the new data
				$this->oDOReferrer->id = null;
				$this->oDOReferrer->save();
				//set the account referrer_id field to point to the new record
				$this->oDO->referrer_id = $this->oDOReferrer->id;
			}

			//save the account
			if ($this->oDO->hasUnsavedChanges())
			{
				$this->oDO->modified = Data_Source_Time::currentTimestamp();
				$this->oDO->modified_dealer_id = Flex::getUserId();;
				$this->oDO->save();
				$this->oDOSale->account_id = $this->oDO->id;
				//create the account history record
				$aAccountData = $this->oDO->toArray();
				$aAccountData['account_id'] = $aAccountData['id'];
				$aAccountData['id'] = null;
				$oHistory = new DO_Spmotorpass_Spmotorpass_AccountHistory($aAccountData);
				$oHistory->save();
			}

			Motorpass_Logic_TradeReference::saveForParent($this->aTradeRefs);

			return $this->id;

	}



	public function setUnsaved()
	{
		parent::setUnsaved();
		$this->oStreetAddress->setUnsaved();
		$this->oPostalAddress->setUnsaved();
		$this->oContact->setUnsaved();
		$this->oDOReferrer->setUnsavedChangesFlag();
		$this->oCard->setUnsaved();
		Motorpass_Logic_TradeReference::setUnsavedForParent($this->aTradeRefs);
	}

	public function hasPostalAddress()
	{
		return ($this->oPostalAddress->id == null && $this->oPostalAddress->line1==null && $this->oPostalAddress->line2==null && $this->oPostalAddress->suburb==null && $this->oPostalAddress->postcode == null && $this->oPostalAddress->state_id == null)?false:true;
	}

	public function validate()
	{
		$this->aErrors =array_merge($this->aErrors, parent::validate());
		$this->aErrors =array_merge($this->aErrors , $this->oStreetAddress->validate());
		if ($this->hasPostalAddress())
			$this->aErrors =array_merge($this->aErrors, $this->oPostalAddress->validate());
		$this->aErrors =array_merge($this->aErrors , $this->oContact->validate());
		//$this->aErrors =array_merge($this->aErrors , $this->oDOReferrer->preSaveValidation());
		$this->aErrors =array_merge($this->aErrors , $this->oCard->validate());
		$this->aErrors =array_merge($this->aErrors,Motorpass_Logic_TradeReference::validateForParent($this->aTradeRefs));

		if ($this->oDO->business_commencement_date && ($this->oDO->business_commencement_date > Data_Source_Time::currentDate()))
			$this->aErrors[] = "The business commencement date should not be in the future";
		if ($this->oDO->email_invoice && !$this->oDO->email_address)
			$this->aErrors[] = "When selecting 'email invoice', an email address must be supplied.";
		if ($this->oDO->abn && !Motorpass_Logic_Validation::isValidABN($this->oDO->abn))
			$this->aErrors[] = "The ABN you entered is incorrect.";
		if ($this->oDO->email && !Motorpass_Logic_Validation::isValidEmailAddress($this->oDO->email))
			$this->aErrors[] = "The email you entered is incorrect.";

		return $this->aErrors;
	}

	public function toStdClass()
	{
		$oStdAccount = parent::toStdClass();
		unset($oStdAccount->account_id);
		$oStdAccount->street_address = $this->oStreetAddress->toStdClass();
		unset($oStdAccount->street_address_id);
		$oStdAccount->postal_address =$this->oPostalAddress->toStdClass();
		unset($oStdAccount->postal_address_id);
		$oStdAccount->contact =$this->oContact->toStdClass();
		unset($oStdAccount->contact_id);
		$oStdAccount->referrer =$this->oDOReferrer->toStdClass();
		unset($oStdAccount->referrer_id);
		$oStdAccount->card =$this->oCard->toStdClass();
		unset($oStdAccount->card_id);

		//the trade references are added to the sale object, for historical reasons, but we have to unset them here

		return $oStdAccount;
	}

}
?>