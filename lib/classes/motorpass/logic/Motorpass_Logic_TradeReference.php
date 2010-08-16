<?php
class Motorpass_Logic_TradeReference extends Motorpass_Logic_ManyToOneLogicClass
{

	public static $sDO = 'Motorpass_Trade_Reference';
	public static $sFKField = 'account_id';


	public function __construct($mDetails, $oParent = null)
	{
		$this->aUneditable = array('status_id', 'account_id','created', 'created_dealer_id');
		parent::__construct($mDetails, 'Motorpass_Trade_Reference');
		$this->oParent = $oParent?$oParent:null;
		if ($this->id == null)
			$this->created_dealer_id = Flex::getUserId();;
		return $this->oDO->id;
	}


	public function validate()
	{
		parent::validate();
		if (!Motorpass_Logic_Validation::isValidLandlineFNN($this->oDO->phone_number))
					$this->aErrors[] = $this->oDO->landline_number." is not a valid landline number for contact.";
		return $this->aErrors;
	}

	public function _save()
	{
		if($this->oDO->hasUnsavedChanges())
		{
			$this->{self::$sFKField} = $this->oParent->id;
			if($this->id!=null)
			{
				//if there are, set the current one to inactive
				$oCurrentRererrerObject = DO_Spmotorpass_Spmotorpass_TradeReference::getForId($this->id);
				$oCurrentRererrerObject->status_id = DO_Spmotorpass_Spmotorpass_Status::INACTIVE;
				$oCurrentRererrerObject->save();
			}


			//create a new record to contain the new data
			$this->id = null; //make sure id is nulll
			$this->created = Data_Source_Time::currentTimestamp();
			$this->created_dealer_id = Flex::getUserId();;
			$this->oDO->status_id  = DO_Spmotorpass_Spmotorpass_Status::ACTIVE;
			$this->oDO->save();
		}
	}


	public static function getForParent($oParent)
	{
		$aDOObjects = DO_Spmotorpass_Spmotorpass_TradeReference::getFor(self::$sFKField."=".$oParent->id." AND status_id = ".DO_Spmotorpass_Spmotorpass_Status::ACTIVE, true);
		$aInstances = array();
		foreach ($aDOObjects as $oDOObject)
		{
			$aInstances[] = new self($oDOObject, $oParent);
		}

		return $aInstances;
	}



	public static function createFromStd($mStd, $oParent)
	{
		return parent::createFromStd('Motorpass_Logic_TradeReference',$mStd, $oParent, self::$sFKField);
	}

	public static function saveForParent($aTradeReferences)
	{
		$aAllTradeReferences= array();
		$bAllTradereferencesRetrieved = false;
		foreach ($aTradeReferences as $oTradeRef)
		{
			if (!bAllTradereferencesRetrieved)
			{
				$bAllTradereferencesRetrieved = true;
				$aAllTradeReferences= DO_Spmotorpass_Spmotorpass_TradeReference::getFor(self::$sFKField."=".$oTradeRef->oParent->id." AND status_id = ".DO_Spmotorpass_Spmotorpass_Status::ACTIVE, true);
			}

			//as we loop through the trade refs to be saved we delete the current trade ref from the array of trade refs currently stored in the database
			//all the trade refs that are still left in the array when we're done saving must be set to 'inactive'
			unset($aAllTradeReferences[$oTradeRef->id]);
			$oTradeRef->_save();
		}

		//now set any tradereferences that were deleted by the user to inactive
		foreach ($aAllTradeReferences as $oTradeRef)
		{
			$oTradeRef->status_id = DO_Spmotorpass_Spmotorpass_Status::INACTIVE;
			$oTradeRef->save();
		}

	}

	public static function validateForParent($aTradeRefs)
	{
		$aErrors = array();
		if (count($aTradeRefs)!=2)
			$aErrors[] = "You must specify two trade references.";

		$aErrors =array_merge($aErrors, parent::validateForParent($aTradeRefs));
		return $aErrors;
	}





}