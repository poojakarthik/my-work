<?php

class Sale extends LogicClass
{

	public $aServices = array();
	public $oAccount;

	public function __construct($mSaleDetails)
	{

		$this->aUneditable = array('dealer_id', 'created', 'created_dealer_id', 'modified', 'modified_dealer_id');
		if ($mSaleDetails && get_class($mSaleDetails)=='stdClass')
		{
			//before we create the sale object itself, delete what we don't want to be in the object
			$aServices = $mSaleDetails->services;
			$aTradeRefs = $mSaleDetails->trade_references;
			$oAccount = $mSaleDetails->account;
			unset($mSaleDetails->services);
			unset($mSaleDetails->trade_references);
			unset($mSaleDetails->account);

			//the tradereferences belong to the account,so add them to the account object
			$oAccount->trade_references = $aTradeRefs;
			$this->oAccount = new Account($oAccount);

			$mSaleDetails->account_id = $this->oAccount->id;
			if ($mSaleDetails->id == null)
				unset($mSaleDetails->created);




			parent::__construct($mSaleDetails, 'DO_Spmotorpass_Spmotorpass_Sale');
			//create the services and trade references
			$this->aServices = Service::createFromStd($aServices, $this, 'sale_id');

		}
		else if ($mSaleDetails && is_numeric($mSaleDetails))
		{
			$this->oDO = DO_Spmotorpass_Spmotorpass_Sale::getForId($mSaleDetails);
			$this->oAccount = new Account($this->oDO->getAccount());

			$this->aServices = Service::getForSale($this);
		}
		else
		{
			throw new Exception_InvalidJSONObject('The Sale data supplied does not represent a valid Sale.');
		}


		if ($this->oDO->id ==null)
		{
				$this->oDO->created_dealer_id = Flex::getUserId();;
			$this->oDO->modified_dealer_id = Flex::getUserId();;
		}
		return $this;

	}


	public function save()
	{
		$this->validate();
		if (count($this->aErrors)==0)
		{
		$oDbh = PDOx::get(Data_Source::getPrimaryDataSourceName());
		$mResult = $oDbh->processAsTransaction(new Callback('_save', $this));
		return $mResult;
		}
		else
		{
			return $this->aErrors;
		}

	}

	public function validate()
	{
		$this->aErrors =array_merge($this->aErrors, parent::validate());
		if ($this->id == null && ($this->account_id !=null || $this->oAccount->id!=null || $this->oAccount->card_id != null))
			throw new Exception_InvalidJSONObject('The sale has data integrity issues: sale id is null, which indicates a new sale, but the account or card is not null!');
		$this->aErrors =array_merge($this->aErrors, $this->oAccount->validate());
		$this->aErrors =array_merge($this->aErrors, Service::validateForParent($this->aServices));

		return $this->aErrors;
	}



	public function _save()
	{
		try
		{
			$this->oDO->account_id = $this->oAccount->_save();
			//save the sale

			if ($this->oDO->hasUnsavedChanges())
			{
				$this->oDO->modified = Data_Source_Time::currentTimestamp();
				$this->oDO->modified_dealer_id = Flex::getUserId();;
				if ($this->oDO->id == null)
				{
					$this->oDO->sale_status_id = DO_Spmotorpass_Spmotorpass_SaleStatus::SUBMITTED;
					$this->oDO->created_dealer_id = Flex::getUserId();;
					$this->oDO->created = Data_Source_Time::currentTimestamp();
					$this->oDO->dealer_id = Flex::getUserId();;
				}
				$this->oDO->save();
				//create the sale history record
				$aSaleData = $this->oDO->toArray();
				$aSaleData['sale_id'] = $aSaleData['id'];
				$aSaleData['id'] = null;
				unset($aSaleData['dealer_id']);
				unset($aSaleData['account_id']);
				unset($aSaleData['created']);
				unset($aSaleData['created_dealer_id']);
				$oHistory = new DO_Spmotorpass_Spmotorpass_SaleHistory($aSaleData);
				$oHistory->save();
			}
			//save the services
			Service::saveForSale($this->aServices);

		}
		catch(Exception $e)
		{
			$this->setUnSaved();
			//rethrow it so we get a rollback
			throw $e;
		}

		return $this->oDO->id;

	}

	//reset the save flags when exception has occurred.
	public function setUnSaved()
	{
		parent::setUnsaved();
		$this->oAccount->setUnsaved();
		Service::setUnsavedForParent($this->aServices);

	}


	public function toStdClass()
	{
		$oStdSale = parent::toStdClass();
		$oStdSale->account = $this->oAccount->toStdClass();
		$oStdSale->services = Service::toStdClassForParent($this->aServices);
		$oStdSale->trade_references = TradeReference::toStdClassForParent($this->oAccount->aTradeRefs);
		return $oStdSale;
	}


	public static function updateStatus($iSale, $iStatus)
	{
		$oSale = DO_Spmotorpass_Spmotorpass_Sale::getForId($iSale);
		$oSale->sale_status_id = $iStatus;
		$oSale->modified =  Data_Source_Time::currentTimestamp();
		$oSale->modified_dealer_id =  Flex::getUserId();;
		$oSale->save();

		//create the sale history record
				$aSaleData = $oSale->toArray();
				$aSaleData['sale_id'] = $aSaleData['id'];
				$aSaleData['id'] = null;
				unset($aSaleData['dealer_id']);
				unset($aSaleData['account_id']);
				unset($aSaleData['created']);
				unset($aSaleData['created_dealer_id']);
				$oHistory = new DO_Spmotorpass_Spmotorpass_SaleHistory($aSaleData);
				$oHistory->save();
	}

}
?>