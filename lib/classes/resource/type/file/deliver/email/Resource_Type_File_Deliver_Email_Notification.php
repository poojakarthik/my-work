<?php
class Resource_Type_File_Deliver_Email_Notification extends Resource_Type_File_Deliver
{
	const 		RESOURCE_TYPE 			= RESOURCE_TYPE_FILE_DELIVERER_EMAIL_NOTIFICATION;
	const		VARIABLE_CUSTOMER_GROUP	= 'customer_group';
	const		VARIABLE_CARRIER_MODULE	= 'carrier_module';
	
	protected 			$_sWrapper;
	protected static 	$_aVariables =	array(
											self::VARIABLE_CUSTOMER_GROUP => 'Name of the Customer Group associated with the Carrier Module that is delivering the file',
											self::VARIABLE_CARRIER_MODULE => 'Description of the Carrier Module that is delivering the file'
										);
	
	public function connect()
	{
		return $this;
	}
	
	protected function _deliver($sLocalPath, $mCarrierModule=null)
	{
		$aStringVariables 	= array();
		$iCustomerGroupId	= null;
		if ($mCarrierModule !== null)
		{
			$oCarrierModule = ($mCarrierModule instanceof Carrier_Module ? $mCarrierModule : Carrier_Module::getForId($mCarrierModule));
			if (!$oCarrierModule)
			{
				throw new Exception("Invalid Carrier Module supplied");
			}
			$iCustomerGroupId	= $oCarrierModule->customer_group;
			$aStringVariables 	= self::_getStringVariables($oCarrierModule);
		}
		
		$oEmailNotification =	Email_Notification::factory(
									$this->getConfig()->EmailNotificationId, 
									$iCustomerGroupId, 
									null, 
									self::_getCombinedString($this->getConfig()->EmailSubject, $aStringVariables), 
									null, 
									self::_getCombinedString($this->getConfig()->EmailBody, $aStringVariables), 
									array(
										array(
											Email_Notification::EMAIL_ATTACHMENT_CONTENT	=> file_get_contents($sLocalPath), 
											Email_Notification::EMAIL_ATTACHMENT_NAME		=> basename($sLocalPath), 
											Email_Notification::EMAIL_ATTACHMENT_MIME_TYPE	=> @mime_content_type($sLocalPath)
										)
									), 
									false
								);
		$oEmailNotification->send();
		return $this;
	}
	
	public function getDebugEmailContent($sLocalPath, $mCarrierModule=null)
	{
		$aStringVariables = array();
		if ($mCarrierModule !== null)
		{
			$oCarrierModule = ($mCarrierModule instanceof Carrier_Module ? $mCarrierModule : Carrier_Module::getForId($mCarrierModule));
			if (!$oCarrierModule)
			{
				throw new Exception("Invalid Carrier Module supplied");
			}
			$aStringVariables = self::_getStringVariables($oCarrierModule);
		}
		return self::_getCombinedString($this->getConfig()->EmailBody, $aStringVariables);
	}

	public function disconnect()
	{
		return $this;
	}

	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClass=__CLASS__) {
		parent::createCarrierModule($iCarrier, $iCustomerGroup, $sClass, self::RESOURCE_TYPE);
	}

	static public function defineCarrierModuleConfig()
	{
		// Build variable info string
		$aVariableDescriptions = array();
		foreach (self::$_aVariables as $sVariable => $sDescription)
		{
			$aVariableDescriptions[] = "<{$sVariable}>: {$sDescription}";
		}
		$sVariableDescriptions = implode(', ', $aVariableDescriptions);
		
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'EmailNotificationId'	=> array('Description' => 'Email notification to use to deliver the file', 'Type' => DATA_TYPE_INTEGER),
			'EmailSubject'			=> array('Description' => "Subject of the delivered email (Allowed variables are {$sVariableDescriptions})"),
			'EmailBody'				=> array('Description' => "The body text of the delivered email (Allowed variables are {$sVariableDescriptions})")
		));
	}
	
	protected static function _getStringVariables($oCarrierModule)
	{
		$sCustomerGroup = null;
		if ($oCarrierModule->customer_group !== null)
		{
			$sCustomerGroup = Customer_Group::getForId($oCarrierModule->customer_group)->external_name;
		}
		
		$sCarrierModule = null;
		if ($oCarrierModule->description !== null)
		{
			$sCarrierModule = $oCarrierModule->description;
		}
		
		return	array(
					self::VARIABLE_CUSTOMER_GROUP	=> $sCustomerGroup,
					self::VARIABLE_CARRIER_MODULE	=> $oCarrierModule->description
				);
	}
	
	protected static function _getCombinedString($sString, $aVariableValues)
	{
		$sCombinedString = $sString;
		foreach ($aVariableValues as $sVariableName => $mValue)
		{
			if ($mValue !== null)
			{
				Log::getLog()->log("<{$sVariableName}> -> {$mValue}");
				$sCombinedString = preg_replace("/<{$sVariableName}>/", $mValue, $sCombinedString);
			}
		}
		return $sCombinedString;
	}
}
?>