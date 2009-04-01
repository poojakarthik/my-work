<?php

class JSON_Handler_ActionType extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getForId($intActionTypeId)
	{
		try
		{
			$objActionType			= new Action_Type(array('id'=>$intActionTypeId), true);
			$objActionTypeStdClass	= $objActionType->toStdClass();
			
			$objActionTypeStdClass->arrAssociationTypes	= array_keys($objActionType->getAllowableActionAssociationTypes());
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"		=> true,
							"objActionType"	=> $objActionTypeStdClass,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function save($objActionTypeStdClass)
	{
		try
		{
			if ((int)$objActionTypeStdClass->id)
			{
				// Edit
				$objActionType										= Action_Type::getForId((int)$objActionTypeStdClass->id);
				$objActionType->description							= $objActionTypeStdClass->description;
				$objActionType->action_type_detail_requirement_id	= $objActionTypeStdClass->action_type_detail_requirement_id;
				$objActionType->active_status_id					= $objActionTypeStdClass->active_status_id;
				$objActionType->save();
			}
			elseif (Action_Type::getForName($objActionTypeStdClass->name, true))
			{
				// Name is not unique
				throw new Exception("The Name '{$objActionTypeStdClass->name}' is already in use.  Please use another.");
			}
			else
			{
				// New
				$objActionType										= new Action_Type();
				$objActionType->name								= $objActionTypeStdClass->name;
				$objActionType->description							= $objActionTypeStdClass->description;
				$objActionType->action_type_detail_requirement_id	= $objActionTypeStdClass->action_type_detail_requirement_id;
				$objActionType->is_automatic_only					= 0;
				$objActionType->is_system							= 0;
				$objActionType->active_status_id					= ACTIVE_STATUS_ACTIVE;
				$objActionType->save();
				
				foreach ($objActionTypeStdClass->arrAssociationTypes as $intAssociationType)
				{
					$objActionTypeActionAssociationType								= new Action_TypeActionAssociationType();
					$objActionTypeActionAssociationType->action_type_id				= $objActionType->id;
					$objActionTypeActionAssociationType->action_association_type_id	= (int)$intAssociationType;
					$objActionTypeActionAssociationType->save();
				}
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"			=> true,
							"intActionTypeId"	=> $objActionType->id,
							"strDebug"			=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
}
?>