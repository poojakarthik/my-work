<?php
/**
 * This class represents, for a correspondence template,  a carrier module configuration for a single delivery method.
 * This class does not straightforwardly represent a record in a database table:
 * it combines data from 'correspondence_template_correspondence_template_carrier_module' and 'correspondence_template_carrier_module'
 */
class Correspondence_Logic_Template_Carrier_Module
{
	protected $_oDO;
	protected $_oCarrierModule;
	protected $_iDeliveryMethod;
	protected $_iCorrespondenceTemplate;

	public function __construct($mDefinition, $iDeliveryMethod, $iCorrespondenceTemplate)
	{
		$this->_iDeliveryMethod	= $iDeliveryMethod;
		$this->_iCorrespondenceTemplate = $iCorrespondenceTemplate;
		if (is_numeric($mDefinition))
		{
			$this->_oDO	= Correspondence_Template_Carrier_Module::getForId($mDefinition);
		}
		else
		{
			$this->_oDO	= $mDefinition;
		}
		$this->_oDO->setSaved();
	}

	public function getCarrierModule()
	{
		if ($this->carrier_module_id == null)
		{
			throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::SYSTEM_CONFIG, "No Carrier module specified, cannot instantiate a carrier module class");
		}
		
		$oCarrierModule	= Carrier_Module::getForId($this->carrier_module_id);
		if ($oCarrierModule == null)
		{
			throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::SYSTEM_CONFIG, "No Carrier module found, cannot instantiate a carrier module class");
		}
		
		$sClassName 			= $oCarrierModule->Module;
		$this->_oCarrierModule	= new $sClassName($oCarrierModule);
		$this->_oCarrierModule->addDeliveryMethod($this->_iDeliveryMethod);
		$this->_oCarrierModule->setTemplateCarrierModule($this);
		
		return $this->_oCarrierModule;
	}

	public static function getForTemplateId($iTemplateId)
	{
		$aORMs 		= Correspondence_Template_Correspondence_Template_Carrier_Module::getForTemplateId($iTemplateId);
		$aResult 	= array();
		foreach ($aORMs as $oORM)
		{
			$aResult[]	= 	new self(
								Correspondence_Template_Carrier_Module::getForId($oORM->correspondence_template_carrier_module_id),
								$oORM->correspondence_delivery_method_id, $oORM->correspondence_template_id
							);
		}
		return $aResult;
	}

	public function __get($sField)
	{
		switch ($sField)
		{
			case '_iDeliveryMethod':
				return $this->_iDeliveryMethod;
			case '_iCorrespondenceTemplate':
				return $this->_iCorrespondenceTemplate;
			default:
				return $this->_oDO->$sField;
		}
		
	}

	public function __set($sField, $mValue)
	{
		$this->_oDO->$sField = $mValue;
	}
}
?>