<?php
/**
 * Description of Collection_Logic_Event_Action
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Event_Charge extends Logic_Collection_Event
{
	protected $oDO;
	
	public function __construct($mDefinition)
	{	   
		if ($mDefinition instanceof Logic_Collection_Event_Instance)
		{
		   $this->oCollectionEventInstance = $mDefinition;
		   $this->oParentDO = Collection_Event::getForId($mDefinition->collection_event_id);
		   $this->oDO = Collection_Event_Charge::getForCollectionEventId($this->oParentDO->id);
		}
		else if (is_numeric($mDefinition))
		{
			$this->oParentDO = Collection_Event::getForId($mDefinition);
			$this->oDO = Collection_Event_Charge::getForCollectionEventId($this->oParentDO->id);
		}

		else
		{
		   throw new Exception ('Bad definition of Logic_Collection_Event_Charge, possibly a configuration error');
		}
	}

	protected function _invoke($aParameters = null)
	{	  
		$oAccount = $this->getAccount();
		
		// if allow_recharge != 1 then if there is an unbilled version of this charge type on this account, don't charge it again'
		if ($this->allow_recharge || Charge::getUnbilledForAccountAndType($oAccount->id, $this->charge_type_id)=== null)
		{
			//calculate the charge:
			//apply the percentage of outstanding debt, and 'clamp' this to the min/max amounts
			$fAmount					= max($oAccount->getOverdueCollectableBalance()*$this->percentage_outstanding_debt, $this->minimum_amount);
			$fChargeAmount				= $this->maximum_amount === null ? $fAmount :  min($fAmount, $this->maximum_amount);
			$oChargeType				= Charge_Type::getForId($this->charge_type_id);
			$oCharge					= new Charge();
			$oCharge->Account			= $oAccount->id;
			$oCharge->AccountGroup		= $oAccount->AccountGroup;
			$oCharge->CreatedBy			= Flex::getUserId()!=null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
			$oCharge->ApprovedBy		=   Flex::getUserId()!=null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
			$oCharge->ChargeType		= $oChargeType->ChargeType;
			$oCharge->charge_type_id	= $oChargeType->id;
			$oCharge->Description		= $oChargeType->Description;
			$oCharge->ChargedOn			= Data_Source_Time::currentDate();
			$oCharge->Nature			= $oChargeType->Nature;
			$oCharge->Amount			= $fChargeAmount;
			$oCharge->Notes				= "";
			$oCharge->Status			=  CHARGE_APPROVED;
			$oCharge->global_tax_exempt =  0;
			$oCharge->charge_model_id	=  $oChargeType->charge_model_id;
			$oCharge->CreatedOn			= Data_Source_Time::currentDate();

			$oCharge->save();
		   

		}
		
	}

	public static function complete($aEventInstances)
	{
		 foreach ($aEventInstances as $oInstance)
		{
			$oInstance->complete();
		}
	}

	 public function __get($sField)
	{
		switch ($sField)
		{
			case 'name':
			case 'collection_event_type_id':
				return $this->oParentDO->$sField;
			default:
				return $this->oDO->$sField;
		}
	}
}
?>
