<?php
abstract class Motorpass_Logic_ManyToOneLogicClass extends Motorpass_Logic_LogicClass
{

	protected $oParent;

	abstract public static function getIdForObject($aArgs);

	public static function toStdClassForParent($aInstances)
	{
		$aStdClasses = array();
		foreach($aInstances as $oInstance)
		{
			$aStdClasses[]=$oInstance->toStdClass();
		}
		return $aStdClasses;
	}

	public static function setUnsavedForParent($aInstances)
	{
		foreach ($aInstances as $oInstance)
		{
			$oInstance->setUnsavedChangesFlag();
		}
	}

	public static function createFromStd($sLO, $mStd, $oParent, $sFKField)
	{
		if (is_array($mStd))
		{
			$aInstances = array();
			foreach($mStd as $oObject)
			{
				$aInstances[]=self::createFromStd($sLO, $oObject, $oParent, $sFKField);
			}
			return $aInstances;
		}
		else
		{
			$mStd->{$sFKField} = $oParent->id;
			//self::getId($sLO,$mStd, $oParent,$sFKField );
			$oInstance = new $sLO($mStd, $oParent);
			return $oInstance;
		}
	}

	public static function getId ($sclass, $oObject, $oParent, $sFKField)
	{

		$oObject->id = call_user_func ( array($sclass, 'getIdForObject') , (array)$oObject);

	}



	public static function validateForParent($aInstances)
	{
		$aErrors = array();
		foreach ($aInstances as $oInstance)
		{
			$aErrors =array_merge($aErrors ,$oInstance->validate());
		}
		return $aErrors;
	}



}