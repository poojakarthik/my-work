<?php
class Motorpass_Logic_Address extends Motorpass_Logic_LogicClass
{


	public function __construct($mDetails)
	{
		$this->aUneditable = array();
		parent::__construct($mDetails, 'Motorpass_Address');
	}

	public function validate()
	{
		parent::validate();
		if ( $this->postcode && !Motorpass_Logic_Validation::isValidPostcode($this->postcode))
			$this->aErrors[] = "The postcode you entered is not valid.";

		return $this->aErrors;
	}

	public function _save()
	{
		if($this->bUnsavedChanges)
		{
			//create a new record to contain the new data
			//$this->oDO->id = null;
			$this->oDO->save();

		}
		return $this->id;
	}

}