<?php
abstract class Resource_Type_Base
{
	protected	$_oCarrierModule;
	
	public function __construct($mCarrierModule)
	{
		$this->_oCarrierModule	= Carrier_Module::getForId(ORM::extractId($mCarrierModule));
	}
	
	public function getCarrierModule()
	{
		return $this->_oCarrierModule;
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
}
?>