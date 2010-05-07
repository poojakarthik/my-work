<?php
class Carrier_Module_Config_Set
{
	private	$_oCarrierModule;
	
	private	$_aFields					= array();
	private	$_aParseDereferenceStack	= array();
	
	private function __construct($mCarrierModule)
	{
		$this->_oCarrierModule	= ($mCarrierModule instanceof Carrier_Module) ? $mCarrierModule : Carrier_Module::getForId(ORM::extractId($mCarrierModule));
	}
	
	private function _getPropertyParsed($sProperty)
	{
		// Add this Field to the dereference stack
		array_push($this->_aParseDereferenceStack, $sProperty);
		
		// If this field is in the stack twice, we have an infinite recursion issue
		Flex::assert(	count($this->_aParseDereferenceStack) === count(array_unique($this->_aParseDereferenceStack)),
						"Carrier Module Config Field '{$sProperty}' is infinitely recursive.",
						print_r(	array
									(
										'Carrier Module'	=> $this->_oCarrierModule->toArray(),
										'Fields'			=> $this->_toArrayRaw(),
										'Recursion Stack'	=> $this->_aParseDereferenceStack
									), true),
						'Carrier Module Config Field: Infinite Recursion');
		
		// Parse the Field
		$mParsedValue	= $this->_parseField($sProperty);
		
		// All done -- pop it off the stack
		array_pop($this->_aParseDereferenceStack);
		
		return $mParsedValue;
	}
	
	private function _parseField($sField)
	{
		$aPlaceholders	= array();
		preg_match_all("/<(?P<context>[A-Z]+)::(?P<action>[A-Za-z]+)>/i", $this->_getPropertyRaw($sField), $aPlaceholders, PREG_SET_ORDER);
		
		foreach ($aPlaceholders as $aPlaceholderSet)
		{
			$sTag		= $aPlaceholderSet[0];
			$sContext	= strtolower($aPlaceholderSet['context']);
			$sAction	= strtolower($aPlaceholderSet['action']);
			
			$sReplace	= null;
			switch ($sContext)
			{
				case 'config':
					$sReplace	= $this->$sField;
					break;
					
				case 'function':
					switch ($sAction)
					{
						case 'datetime':
							$sReplace	= date("Y-m-d H:i:s");
							break;
						
						case 'date':
							$sReplace	= date("Y-m-d");
							break;
					}
					break;
					
				case 'property':
					switch ($sAction)
					{
						case 'customergroup':
							$sReplace	= ($this->_oCarrierModule->customer_group) ? Customer_Group::getForId($this->_oCarrierModule->customer_group)->externalName : '';
							break;
						
						case 'carrier':
							$sReplace	= ($this->_oCarrierModule->Carrier) ? Carrier::getForId($this->_oCarrierModule->Carrier)->Name : '';
							break;
					}
					break;
			}
			
			if (isset($sReplace))
			{
				str_replace($sTag, $sReplace, $sField);
			}
		}
		
		return $sField;
	}
	
	public function toArray()
	{
		$aOuptut	= array();
		foreach ($this->_aFields as $sName=>$oCarrierModuleConfig)
		{
			$aOutput[$sName]	= $this->$sName;
		}
		return $aOutput;
	}
	
	private function _toArrayRaw()
	{
		$aOuptut	= array();
		foreach ($this->_aFields as $sName=>$oCarrierModuleConfig)
		{
			$aOutput[$sName]	= $oCarrierModuleConfig->Value;
		}
		return $aOutput;
	}
	
	private function _getPropertyRaw($sProperty)
	{
		if (array_key_exists($sProperty, $this->_aFields))
		{
			return $this->_aFields[$sProperty]->Value;
		}
	}
	
	public function __get($sProperty)
	{
		if (array_key_exists($sProperty, $this->_aFields))
		{
			return $this->_parseField($this->_aFields[$sProperty]->Value);
		}
	}
	
	public function __set($sProperty, $mValue)
	{
		if (array_key_exists($sProperty, $this->_aFields))
		{
			$this->_aFields[$sProperty]->Value	= $mValue;
		}
	}
	
	public function save()
	{
		foreach ($this->_aFields as $sName=>$oCarrierModuleConfig)
		{
			$oCarrierModuleConfig->save();
		}
	}
	
	public static function getForCarrierModule($mCarrierModule)
	{
		return new self($mCarrierModule);
	}
}
?>