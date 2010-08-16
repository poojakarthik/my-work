<?php
class Service extends ManyToOneLogicClass
{

	public $oSale;


	public function __construct($mServiceDetails, $oSale = null)
	{
		$this->aUneditable = array('created', 'created_dealer_id', 'modified','modified_employee_id','status_id', 'sale_id');
		parent::__construct($mServiceDetails, 'DO_Spmotorpass_Spmotorpass_Service');
		$this->oSale = $oSale?$oSale:null;

		$this->checkEditedFields($this->oDO->setUnsavedChangesFlag(), $this->oDO->id == null);
		if ($this->id == null)
		{
			$this->created_dealer_id = Flex::getUserId();;
			$this->modified_employee_id = Flex::getUserId();;
		}

		return $this->oDO->id;

	}


	public function validate()
	{

		$aPlans = Plan::getForServiceType($this->oSale->id, $this->oSale->oAccount->customer_group_id, $this->oDO->service_type_id);
			if (!in_array($this->oDO->plan_id, array_keys($aPlans)))
					$this->aErrors[] = "the selected plan cannot be assigned to service with fnn: $oDOService->fnn by this dealer.";
		$this->aErrors =array_merge($this->aErrors, $this->oDO->preSaveValidation());

		if ($this->oDO->fnn)
		{
			switch($this->oDO->service_type_id)
			{
				case DO_Spmotorpass_Spmotorpass_ServiceType::LANDLINE:
					if (!Motorpass_Logic_Validation::isValidLandlineFNN($this->oDO->fnn))
						$this->aErrors[] = $this->oDO->fnn." is not a valid FNN for landline service";
					break;
				case DO_Spmotorpass_Spmotorpass_ServiceType::ADSL:
					if (!Motorpass_Logic_Validation::isValidLandlineFNN($this->oDO->fnn))
						$this->aErrors[] = $this->oDO->fnn." is not a valid FNN for ADSL Service";
					break;
				case DO_Spmotorpass_Spmotorpass_ServiceType::INBOUND:
					if (!Motorpass_Logic_Validation::isValidInboundFNN($this->oDO->fnn))
						$this->aErrors[] = $this->oDO->fnn." is not a valid FNN for inbound Service";
					break;
				case DO_Spmotorpass_Spmotorpass_ServiceType::MOBILE:
					if (!Motorpass_Logic_Validation::isValidMobileMSN($this->oDO->fnn))
						$this->aErrors[] = $this->oDO->fnn." is not a valid FNN for mobile Service";
					break;
				default:
					$this->aErrors[] = "Invalid plan type for service with the following FNN: ".$this->oDO->fnn;
			}
		}
		else
		{
			$this->aErrors[] = "No FNN entered for service";
		}
		return $this->aErrors;

	}

	public function _save()
	{
			if ($this-> $bUnsavedChanges)
			{
				$bNew = false;
				if ($this->oDO->id == null)
				{

					$this->oDO->created = Data_Source_Time::currentTimestamp();
					$this->oDO->created_dealer_id = Flex::getUserId();;
					$this->oDO->status_id  = DO_Spmotorpass_Spmotorpass_Status::ACTIVE;
					$bNew = true;
				}
				$this->oDO->sale_id = $this->oSale->id;
				$this->oDO->modified = Data_Source_Time::currentTimestamp();
				$this->oDO->modified_employee_id = Flex::getUserId();;
				$this->oDO->save();


				//create the service history record
				$aServiceData = $this->toArray();
				unset($aServiceData['created']);
				unset($aServiceData['created_dealer_id']);
				unset($aServiceData['sale_id']);
				$aServiceData['service_id'] = $aServiceData['id'];
				$aServiceData['id'] = null;
				$oHistory = new DO_Spmotorpass_Spmotorpass_ServiceHistory($aServiceData);
				$oHistory->save();

			}

	}






	public static function getForSale($oSale)
	{
		$aDOServices = DO_Spmotorpass_Spmotorpass_Service::getFor("sale_id=".$oSale->id." AND status_id = ".DO_Spmotorpass_Spmotorpass_Status::ACTIVE, true);
		$aServices = array();
		foreach ($aDOServices as $oDOService)
		{
			$aServices[] = new Service($oDOService, $oSale);
		}

		return $aServices;
	}

	public static function createFromStd($mStd, $oSale, $sFKField)
	{
		return parent::createFromStd('Service',$mStd, $oSale, $sFKField);
	}

	public static function saveForSale($aServices)
	{
		$aAllServices= array();
		$bAllServicesRetrieved = false;
		foreach ($aServices as $oService)
		{
			if (!$bAllServicesRetrieved)
			{

				$bAllServicesRetrieved = true;
				$aAllServices= DO_Spmotorpass_Spmotorpass_Service::getFor("sale_id=".$oService->oSale->id." AND status_id = ".DO_Spmotorpass_Spmotorpass_Status::ACTIVE, true);
			}

			//as we loop through the services to be saved we delete the current service from the array of services currently stored in the database
			//all the services that are still left in the array when we're done saving must be set to 'inactive'
			unset($aAllServices[$oService->id]);
			$oService->_save();
		}
		//now set any services that were deleted by the user to inactive
		foreach ($aAllServices as $oDOService)
		{
				$oDOService->status_id = DO_Spmotorpass_Spmotorpass_Status::INACTIVE;
				$oDOService->modified = Data_Source_Time::currentTimestamp();
				$oDOService->modified_employee_id = Flex::getUserId();;
				$oDOService->save();
				//create the service history record
				$aServiceData = $oDOService->toArray();
				unset($aServiceData['created']);
				unset($aServiceData['created_dealer_id']);
				unset($aServiceData['sale_id']);
				$aServiceData['service_id'] = $aServiceData['id'];
				$aServiceData['id'] = null;
				$oHistory = new DO_Spmotorpass_Spmotorpass_ServiceHistory($aServiceData);
				$oHistory->save();
		}
	}

	public static function validateForSale($aServices)
	{
		$aErrors = array();
		if (count($aServices)==0)
			$aErrors[] = "You must specify at least one service.";

		$aErrors =array_merge($aErrors, parent::validateForParent($aServices));
		return $aErrors;
	}

}