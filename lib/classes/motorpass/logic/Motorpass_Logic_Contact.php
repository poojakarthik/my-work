<?php
class Motorpass_Logic_Contact extends Motorpass_Logic_LogicClass
{

	public function __construct($mDetails)
	{
		$this->aUneditable = array('modified', 'modified_dealer_id');
		parent::__construct($mDetails, 'Motorpass_Contact');
		if ($this->oDO->id == null)
		{
			$this->oDO->modified = Data_Source_Time::currentTimestamp();
			$this->oDO->modified_dealer_id = Flex::getUserId();;
		}

		return $this->id;
	}

	public function validate()
	{
		parent::validate();
		if ($this->oDO->landline_number && !Motorpass_Logic_Validation::isValidLandlineFNN($this->oDO->landline_number))
					$this->aErrors[] = $this->oDO->landline_number." is not a valid landline number for contact.";
		if ($this->oDO->dob  && ($this->oDO->dob > Data_Source_Time::currentDate()))
			$this->aErrors[] = "Contact D.O.B should not be in the future";

		return $this->aErrors;
	}

	public function _save()
	{
		if($this->oDO->hasUnsavedChanges())
		{
			$this->oDO->modified = Data_Source_Time::currentTimestamp();
			$this->oDO->modified_dealer_id = Flex::getUserId();;
			$this->oDO->save();
			//create the contact history record
			$aContactData = $this->oDO->toArray();
			$aContactData['contact_id'] = $aContactData['id'];
			$aContactData['id'] = null;
			$oHistory = new DO_Spmotorpass_Spmotorpass_ContactHistory($aContactData);
			$oHistory->save();
		}
		return $this->id;
	}

}