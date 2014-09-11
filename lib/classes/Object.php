<?php
class Object
{
	public static function factory($aProperties)
	{
		$oObject	= new Object();
		
		foreach ($aProperties as $sName=>$mValue)
		{
			$sName	= self::_sanitisePropertyName($sName);
			$oObject->{$sName}	= $mValue;
		}
		
		return $oObject;
	}
	
	private static function _sanitisePropertyName($sName)
	{
		return preg_replace("/\W+/", '', $sName);
	}
}
?>