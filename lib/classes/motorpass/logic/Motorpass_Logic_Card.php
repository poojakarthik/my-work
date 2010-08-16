<?php
class Motorpass_Logic_Card extends Motorpass_Logic_LogicClass
{


	public function __construct($mDetails)
	{
		parent::__construct($mDetails, 'Motorpass_Card');
		if ($this->oDO->id == null)
			$this->oDO->modified = Data_Source_Time::currentTimestamp();
		$this->oDO->modified_employee_id = Flex::getUserId();

	}

	public function _save()
	{
			if($this->oDO->hasUnsavedChanges())
			{
				$this->oDO->modified = Data_Source_Time::currentTimestamp();
				$this->oDO->modified_employee_id = Flex::getUserId();;
				$this->oDO->save();
				/*//create the card history record
				$aCardData = $this->oDO->toArray();
				$aCardData['card_id'] = $aCardData['id'];
				$aCardData['id'] = null;
				$oHistory = new DO_Spmotorpass_Spmotorpass_CardHistory($aCardData);
				$oHistory->save();*/

			}
		return $this->id;
	}


}