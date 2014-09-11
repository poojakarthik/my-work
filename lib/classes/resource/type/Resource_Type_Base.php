<?php
abstract class Resource_Type_Base
{
	protected	$_oCarrierModule;

	public function __construct($mCarrierModule)
	{
		$this->_oCarrierModule	= ($mCarrierModule instanceof Carrier_Module) ? $mCarrierModule : Carrier_Module::getForId(ORM::extractId($mCarrierModule));
	}

	public function getCarrierModule()
	{
		return $this->_oCarrierModule;
	}

	public function getConfig()
	{
		return $this->_oCarrierModule->getConfig();
	}

	public function getCarrier()
	{
		return $this->_oCarrierModule->Carrier;
	}

	public function getResourceType()
	{
		// This is kind of a hack until we can implement a static::getResourceType()
		return $this->_oCarrierModule->FileType;
	}

	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClassName, $iResourceType, $iCarrierModuleType)
	{
		if (!is_subclass_of($sClassName, __CLASS__))
		{
			throw new Exception("Carrier Module Class '{$sClassName}' does not extend ".__CLASS__);
		}
		if (!Carrier::getForId($iCarrier, true))
		{
			throw new Exception("Carrier '{$iCarrier}' can not be found");
		}
		if (!Resource_Type::getForId($iResourceType, false))
		{
			throw new Exception("Resource Type '{$iResourceType}' can not be found");
		}
		if (!Carrier_Module_Type::getForId($iCarrierModuleType, false))
		{
			throw new Exception("Carrier Module Type '{$iCarrierModuleType}' can not be found");
		}
		if ($iCustomerGroup !== null && !Customer_Group::getForId($iCustomerGroup)) {
			throw new Exception("Customer Group '{$iCustomerGroup}' can not be found");
		}

		// Carrier Module
		$oCarrierModule	= new Carrier_Module();
		$oCarrierModule->Carrier			= $iCarrier;
		$oCarrierModule->customer_group		= $iCustomerGroup;
		$oCarrierModule->Type				= $iCarrierModuleType;
		$oCarrierModule->Module				= $sClassName;
		$oCarrierModule->FileType			= $iResourceType;
		$oCarrierModule->FrequencyType		= FREQUENCY_DAY;
		$oCarrierModule->Frequency			= 1;
		$oCarrierModule->EarliestDelivery	= 0;
		$oCarrierModule->LastSentOn			= Data_Source_Time::START_OF_TIME;
		$oCarrierModule->Active				= 0;
		$oCarrierModule->save();

		// Carrier Module Config
		$oCarrierModule->getConfig()->define(Callback::create('defineCarrierModuleConfig', $sClassName)->invoke());
	}

	static public function defineCarrierModuleConfig()
	{
		return array();
	}
}
?>