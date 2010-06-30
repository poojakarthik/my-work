<?php
class Carrier_Module_Config_Set
{
	private	$_oCarrierModule;
	
	private	$_aFields					= array();
	private	$_aParseDereferenceStack	= array();
	
	private function __construct($mCarrierModule)
	{
		$this->_oCarrierModule	= ($mCarrierModule instanceof Carrier_Module) ? $mCarrierModule : Carrier_Module::getForId(ORM::extractId($mCarrierModule));
		
		// Load Fields
		$this->reload();
	}
	
	public function reload()
	{
		$aFields	= Carrier_Module_Config::getForCarrierModule($this->_oCarrierModule);
		
		$this->_aFields	= array();
		foreach ($aFields as $oCarrierModuleConfig)
		{
			$this->_aFields[$oCarrierModuleConfig->Name]	= $oCarrierModuleConfig;
		}
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
		//Log::getLog()->log("_parseField()'ing property '{$sField}' for Carrier Module {$this->_oCarrierModule->Id}");
		
		$mValue	= $this->_getPropertyRaw($sField);
		
		if ($this->_aFields[$sField]->Type === DATA_TYPE_STRING)
		{
			$mValue	= (string)$mValue;
			
			$aPlaceholders	= array();
			preg_match_all("/<(?P<context>[A-Z]+)::(?P<action>[A-Za-z]+)>/i", $mValue, $aPlaceholders, PREG_SET_ORDER);
			
			Log::getLog()->log("Initial Value: {$mValue}");
			Log::getLog()->log("Replacement Tasks: ".print_r($aPlaceholders, true));
			
			foreach ($aPlaceholders as $aPlaceholderSet)
			{
				$sTag		= $aPlaceholderSet[0];
				$sContext	= $aPlaceholderSet['context'];
				$sAction	= $aPlaceholderSet['action'];
				
				$sReplace	= null;
				switch (strtolower($sContext))
				{
					case 'config':
						Log::getLog()->log("CONFIG Replacement: Config '{$sAction}' with value '{$this->$sAction}'");
						$sReplace	= $this->$sAction;
						break;
						
					case 'function':
						Log::getLog()->log("DATE Replacement: ", false);
						switch (strtolower($sAction))
						{
							case 'datetime':
								$sReplace	= date("Y-m-d H:i:s");
								Log::getLog()->log("Type '{$sAction}' with value '{$sReplace}'");
								break;
							
							case 'date':
								$sReplace	= date("Y-m-d");
								Log::getLog()->log("Type '{$sAction}' with value '{$sReplace}'");
								break;
								
							default:
								Log::getLog()->log("Unknown Type '{$sAction}'");
								break;
						}
						break;
						
					case 'property':
						Log::getLog()->log("PROPERTY Replacement: ", false);
						switch (strtolower($sAction))
						{
							case 'customergroup':
								$sReplace	= ($this->_oCarrierModule->customer_group) ? Customer_Group::getForId($this->_oCarrierModule->customer_group)->externalName : '';
								Log::getLog()->log("Property '{$sAction}' with value '{$sReplace}'");
								break;
							
							case 'carrier':
								$sReplace	= ($this->_oCarrierModule->Carrier) ? Carrier::getForId($this->_oCarrierModule->Carrier)->Name : '';
								Log::getLog()->log("Property '{$sAction}' with value '{$sReplace}'");
								break;
							
							default:
								Log::getLog()->log("Unknown Property '{$sAction}'");
								break;
						}
						break;
					
					default:
						Log::getLog()->log("Unknown Context '{$sContext}'");
						break;
				}
				
				if (isset($sReplace))
				{
					Log::getLog()->log("Replacing '{$sTag}' with '{$sReplace}'");
					$mValue	= str_replace($sTag, $sReplace, $mValue);
				}
			}
			
			Log::getLog()->log("Replaced Value: {$mValue}");
		}
		
		return $mValue;
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
			//Log::getLog()->log("_getPropertyRaw()'ing property '{$sProperty}' for Carrier Module {$this->_oCarrierModule->Id} as '{$this->_aFields[$sProperty]->Value}'");
			return $this->_aFields[$sProperty]->Value;
		}
	}
	
	public function __get($sProperty)
	{
		if (array_key_exists($sProperty, $this->_aFields))
		{
			//Log::getLog()->log("__get()'ing property '{$sProperty}' for Carrier Module {$this->_oCarrierModule->Id} as '".$this->_parseField($sProperty)."'");
			return $this->_parseField($sProperty);
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
	
	public function define($aDefinition)
	{
		if (count($this->_aFields) === 0)
		{
			if (DataAccess::getDataAccess()->TransactionStart())
			{
				try
				{
					foreach ($aDefinition as $sFieldName=>$aField)
					{
						$oCarrierModuleConfig	= new Carrier_Module_Config();
						
						$oCarrierModuleConfig->CarrierModule	= $this->_oCarrierModule->Id;
						$oCarrierModuleConfig->Name				= $sFieldName;
						$oCarrierModuleConfig->Description		= $aField['Description'];
						$oCarrierModuleConfig->Type				= (isset($aField['Type'])) ? $aField['Type'] : DATA_TYPE_STRING;
						
						$this->_aFields[$sFieldName]	= $oCarrierModuleConfig;
						
						$this->$sFieldName			= (isset($aField['Value'])) ? $aField['Value'] : null;
					}
					
					$this->save();
					
					DataAccess::getDataAccess()->TransactionCommit();
				}
				catch (Exception $oException)
				{
					DataAccess::getDataAccess()->TransactionRollback();
					throw $oException;
				}
			}
			else
			{
				throw new Exception("Unable to start a transaction");
			}
		}
		else
		{
			throw new Exception("Carrier Module Config has already been defined!");
		}
	}
}
?>